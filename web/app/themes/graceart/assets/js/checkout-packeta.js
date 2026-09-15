/**
 * Packeta pickup point picker for the checkout block.
 *
 * Rendered as a fill of the shipping options slot, so it sits right under
 * the carrier radios and reacts to the selected rate through the cart data
 * store. The Packeta widget itself is fetched on the first click only. The
 * chosen point goes to the session through extensionCartUpdate; the server
 * (inc/woocommerce/packeta.php) refuses the order without one. Until a
 * point is picked the checkout carries a validation error, which the place
 * order button surfaces here.
 */
(function () {
    'use strict';

    var config = window.graceartPacketa;
    var blocksCheckout = window.wc && window.wc.blocksCheckout;
    var plugins = window.wp && window.wp.plugins;
    var element = window.wp && window.wp.element;
    var data = window.wp && window.wp.data;

    if (
        !config || !config.apiKey || !config.rateIds || !config.rateIds.length
        || !blocksCheckout || !blocksCheckout.ExperimentalOrderShippingPackages
        || typeof blocksCheckout.extensionCartUpdate !== 'function'
        || !plugins || !element || !data
    ) {
        return;
    }

    var el = element.createElement;
    var strings = config.strings || {};
    var ERROR_ID = 'graceart-packeta';
    var WIDGET_URL = 'https://widget.packeta.com/v6/www/js/library.js';
    var widgetLoading = null;

    function loadWidget() {
        if (window.Packeta && window.Packeta.Widget) {
            return Promise.resolve(window.Packeta.Widget);
        }

        if (!widgetLoading) {
            widgetLoading = new Promise(function (resolve, reject) {
                var script = document.createElement('script');

                script.src = WIDGET_URL;
                script.async = true;
                script.onload = function () {
                    if (window.Packeta && window.Packeta.Widget) {
                        resolve(window.Packeta.Widget);
                    } else {
                        widgetLoading = null;
                        reject(new Error('Packeta widget unavailable'));
                    }
                };
                script.onerror = function () {
                    widgetLoading = null;
                    reject(new Error('Packeta widget failed to load'));
                };

                document.head.appendChild(script);
            });
        }

        return widgetLoading;
    }

    function selectedRateId(packages) {
        var i;
        var j;

        for (i = 0; i < (packages || []).length; i++) {
            var rates = packages[i].shipping_rates || [];

            for (j = 0; j < rates.length; j++) {
                if (rates[j].selected) {
                    return rates[j].rate_id;
                }
            }
        }

        return '';
    }

    /**
     * Only what the shop stores; the widget returns far more.
     */
    function pointData(point) {
        var keys = ['id', 'name', 'place', 'street', 'city', 'zip', 'country', 'carrierId', 'carrierPickupPointId', 'pickupPointType', 'url'];
        var stored = {};

        keys.forEach(function (key) {
            if (point[key] !== undefined && point[key] !== null) {
                stored[key] = String(point[key]);
            }
        });

        return stored;
    }

    function pointAddress(point) {
        return [point.street, [point.zip, point.city].filter(Boolean).join(' ')].filter(Boolean).join(', ');
    }

    function PacketaPickupPoint() {
        var cart = data.useSelect(function (select) {
            var store = select('wc/store/cart');
            var customer = store.getCustomerData();
            var validationError = select('wc/store/validation').getValidationError(ERROR_ID);

            return {
                rateId: selectedRateId(store.getShippingRates()),
                country: customer && customer.shippingAddress ? customer.shippingAddress.country : '',
                errorShown: !!validationError && !validationError.hidden
            };
        }, []);
        var container = element.useRef(null);

        var rateId = cart.rateId;
        var isPacketa = config.rateIds.indexOf(rateId) !== -1;

        var savedState = element.useState(config.point || null);
        var saved = savedState[0];
        var setSaved = savedState[1];
        var busyState = element.useState(false);
        var busy = busyState[0];
        var setBusy = busyState[1];
        var errorState = element.useState('');
        var error = errorState[0];
        var setError = errorState[1];

        // A point only counts for the rate it was picked for: another
        // country (a different zone, so a different rate) starts over.
        var point = saved && saved.rateId === rateId ? saved.point : null;

        element.useEffect(function () {
            var validation = data.dispatch('wc/store/validation');

            if (isPacketa && !point) {
                var errors = {};

                errors[ERROR_ID] = { message: strings.required || '', hidden: true };
                validation.setValidationErrors(errors);
            } else {
                validation.clearValidationError(ERROR_ID);
            }

            return function () {
                validation.clearValidationError(ERROR_ID);
            };
        }, [isPacketa, point]);

        // The place order button reveals the error; the checkout only scrolls
        // to inputs, and there is none here.
        element.useEffect(function () {
            if (cart.errorShown && container.current && typeof container.current.scrollIntoView === 'function') {
                container.current.scrollIntoView({ block: 'center', behavior: 'smooth' });
            }
        }, [cart.errorShown]);

        function choose() {
            var options = { language: config.language || 'sk' };

            if (cart.country) {
                options.country = String(cart.country).toLowerCase();
            }

            setError('');
            setBusy(true);

            loadWidget().then(function (widget) {
                setBusy(false);

                widget.pick(config.apiKey, function (chosen) {
                    if (!chosen) {
                        return;
                    }

                    var stored = pointData(chosen);

                    setSaved({ rateId: rateId, point: stored });

                    Promise.resolve(blocksCheckout.extensionCartUpdate({
                        namespace: 'graceart-packeta',
                        data: { rate_id: rateId, point: stored }
                    })).catch(function () {
                        setSaved(null);
                        setError(strings.saveFailed || '');
                    });
                }, options);
            }).catch(function () {
                setBusy(false);
                setError(strings.widgetFailed || '');
            });
        }

        if (!isPacketa) {
            return null;
        }

        return el(
            'div',
            {
                className: 'graceart-packeta' + (point ? ' graceart-packeta--selected' : '') + (cart.errorShown ? ' graceart-packeta--error' : ''),
                ref: container
            },
            el('div', { className: 'graceart-packeta__label' }, strings.label || ''),
            point
                ? el(
                    'div',
                    { className: 'graceart-packeta__point' },
                    el('strong', { className: 'graceart-packeta__name' }, point.name),
                    pointAddress(point) ? el('span', { className: 'graceart-packeta__address' }, pointAddress(point)) : null
                )
                : el('p', { className: 'graceart-packeta__hint' }, strings.hint || ''),
            el(
                blocksCheckout.Button,
                {
                    className: 'graceart-packeta__button',
                    variant: point ? 'outlined' : 'contained',
                    onClick: choose,
                    disabled: busy,
                    showSpinner: busy
                },
                point ? (strings.change || '') : (strings.choose || '')
            ),
            error ? el('div', { className: 'graceart-packeta__error', role: 'alert' }, error) : null,
            blocksCheckout.ValidationInputError
                ? el(blocksCheckout.ValidationInputError, { propertyName: ERROR_ID })
                : null
        );
    }

    plugins.registerPlugin('graceart-packeta', {
        scope: 'woocommerce-checkout',
        render: function () {
            return el(
                blocksCheckout.ExperimentalOrderShippingPackages,
                null,
                el(PacketaPickupPoint)
            );
        }
    });
})();
