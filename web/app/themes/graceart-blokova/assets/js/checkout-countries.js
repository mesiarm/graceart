/**
 * Checkout block: country handling.
 *
 * The country list holds only the countries the shop delivers to, so a
 * notice next to the country field sends someone from elsewhere to the
 * contact form, and a changed country is handed to the server at once so the
 * shipping options follow it (inc/shipping-countries.php).
 */
(function () {
    'use strict';

    var config = window.graceartCountries;
    var blocksCheckout = window.wc && window.wc.blocksCheckout;
    var plugins = window.wp && window.wp.plugins;
    var element = window.wp && window.wp.element;
    var data = window.wp && window.wp.data;

    if (!config || !blocksCheckout || !blocksCheckout.ExperimentalOrderShippingPackages || !plugins || !element || !data) {
        return;
    }

    var el = element.createElement;
    var strings = config.strings || {};

    /**
     * The checkout block sends a changed country to the server only once the
     * whole address validates (postcode, state), so until then the shipping
     * options stay those of the old country. When the country the customer
     * picked differs from the one the rates were calculated for, hand it to
     * the server directly so the options follow at once.
     */
    function useCountrySync() {
        var sync = data.useSelect(function (select) {
            var store = select('wc/store/cart');
            var customer = store.getCustomerData();
            var packages = store.getShippingRates() || [];

            return {
                country: customer && customer.shippingAddress ? customer.shippingAddress.country : '',
                rated: packages.length && packages[0].destination ? packages[0].destination.country : ''
            };
        }, []);
        var sent = element.useRef('');
        var pending = !!sync.country && !!sync.rated && sync.country !== sync.rated;

        // The old country's options stay on screen until the server answers;
        // dim them at once so they are not taken for the new ones.
        element.useEffect(function () {
            document.body.classList.toggle('graceart-shipping-pending', pending);

            return function () {
                document.body.classList.remove('graceart-shipping-pending');
            };
        }, [pending]);

        element.useEffect(function () {
            if (sync.country === sync.rated) {
                sent.current = '';
            }

            if (
                !sync.country || !sync.rated || sync.country === sync.rated
                || sent.current === sync.country
                || typeof blocksCheckout.extensionCartUpdate !== 'function'
            ) {
                return;
            }

            sent.current = sync.country;

            Promise.resolve(blocksCheckout.extensionCartUpdate({
                namespace: 'graceart-shipping',
                data: { country: sync.country }
            })).catch(function () {
                sent.current = '';
            });
        }, [sync.country, sync.rated]);
    }

    /**
     * The address form is not ours to render, so the notice gets a host node
     * placed right after the shipping country field and is portalled into it.
     * The form re-renders freely, hence the observer keeps the host in place.
     * Null until the field exists; the caller then falls back to inline.
     */
    function useCountryFieldHost() {
        var state = element.useState(null);
        var host = state[0];
        var setHost = state[1];

        element.useEffect(function () {
            var node = null;

            function sync() {
                var field = document.querySelector('#shipping .wc-block-components-address-form__country');

                if (!field) {
                    if (node && node.parentNode) {
                        node.parentNode.removeChild(node);
                    }

                    if (host) {
                        setHost(null);
                    }

                    return;
                }

                if (!node) {
                    node = document.createElement('div');
                    node.className = 'graceart-other-country-host';
                }

                if (field.nextSibling !== node) {
                    field.parentNode.insertBefore(node, field.nextSibling);
                }

                if (host !== node) {
                    setHost(node);
                }
            }

            sync();

            var root = document.querySelector('.wp-block-woocommerce-checkout') || document.body;
            var observer = new MutationObserver(sync);

            observer.observe(root, { childList: true, subtree: true });

            return function () {
                observer.disconnect();

                if (node && node.parentNode) {
                    node.parentNode.removeChild(node);
                }
            };
        }, []);

        return host;
    }

    /**
     * Shown next to the country field: the country list holds only the
     * Packeta countries, so someone from elsewhere is sent to the contact
     * form to ask.
     */
    function OtherCountryNotice() {
        useCountrySync();

        var host = useCountryFieldHost();

        if (!config.otherCountryUrl) {
            return null;
        }

        var notice = el(
            'p',
            { className: 'graceart-other-country' },
            (strings.otherCountry || '') + ' ',
            el('a', { href: config.otherCountryUrl }, strings.otherCountryLink || '')
        );

        return host ? element.createPortal(notice, host) : notice;
    }

    plugins.registerPlugin('graceart-countries', {
        scope: 'woocommerce-checkout',
        render: function () {
            return el(blocksCheckout.ExperimentalOrderShippingPackages, null, el(OtherCountryNotice, { key: 'other-country' }));
        }
    });
})();

/**
 * Packeta pickup point window and the Back button (1.7.19).
 *
 * The Packeta library opens the map as a layer over the checkout without a
 * history entry, so Back on a phone left the checkout for the page before
 * (the product). While the layer is open it gets its own history entry:
 * Back then only closes the layer, and once it is closed some other way
 * (a point picked, the cross) that entry is taken back off.
 */
(function () {
    'use strict';

    if (!window.history || typeof window.history.pushState !== 'function') {
        return;
    }

    var KEY = 'graceartPacketaOpen';
    var open = false;
    var closingByBack = false;
    var skipNextPop = false;

    function patch() {
        var widget = window.Packeta && window.Packeta.Widget;

        if (!widget || typeof widget.initIframe !== 'function' || widget.graceartPatched) {
            return;
        }

        var initIframe = widget.initIframe;

        widget.graceartPatched = true;
        widget.initIframe = function (apiKey, callback, opts, inElement) {
            var result = initIframe.apply(this, arguments);
            var inline = typeof inElement !== 'undefined' && inElement !== null;
            var close = widget.close;

            if (inline || typeof close !== 'function') {
                return result;
            }

            var state = {};
            state[KEY] = true;
            window.history.pushState(state, '');
            open = true;

            widget.close = function () {
                var wasOpen = open;

                open = false;
                widget.close = function () {};

                if (wasOpen && !closingByBack && window.history.state && window.history.state[KEY]) {
                    skipNextPop = true;
                    window.history.back();
                }

                closingByBack = false;

                return close.apply(this, arguments);
            };

            return result;
        };
    }

    window.addEventListener('popstate', function () {
        if (skipNextPop) {
            skipNextPop = false;
            return;
        }

        if (open && window.Packeta && window.Packeta.Widget) {
            closingByBack = true;
            window.Packeta.Widget.close();
        }
    });

    // The library may load before or after this file: patch now, once the
    // page is ready, and at the latest when the pickup point button is
    // pressed (capture phase, before the plugin's own click handler).
    patch();
    document.addEventListener('DOMContentLoaded', patch);
    window.addEventListener('load', patch);
    document.addEventListener('click', function (event) {
        if (event.target && event.target.closest && event.target.closest('.packetery-widget-button-wrapper')) {
            patch();
        }
    }, true);
})();
