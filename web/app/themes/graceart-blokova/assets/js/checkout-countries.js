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
