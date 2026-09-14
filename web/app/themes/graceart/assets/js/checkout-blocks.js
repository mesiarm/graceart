/**
 * Cart & checkout block tweaks.
 *
 * These button labels live in the blocks' React UI, so PHP filters such as
 * woocommerce_order_button_text do not reach them — they have to be set through
 * the blocks checkout filter registry.
 */
(function () {
    'use strict';

    var blocksCheckout = window.wc && window.wc.blocksCheckout;

    if (!blocksCheckout || typeof blocksCheckout.registerCheckoutFilters !== 'function') {
        return;
    }

    var strings = window.graceartCheckoutStrings || {};

    function placeCheckoutSummaryUnitPrices(root) {
        var scope = root || document;
        var items = scope.querySelectorAll('.wc-block-components-order-summary-item');

        items.forEach(function (item) {
            var source = item.querySelector('.wc-block-components-order-summary-item__individual-price');
            var target = item.querySelector('.wc-block-components-order-summary-item__total-price .wc-block-components-product-price');

            if (!source || !target) {
                return;
            }

            var price = source.textContent.trim();
            var label = price + ' / kus';

            if (target.textContent.trim() !== label) {
                target.textContent = label;
            }
        });
    }

    function watchCheckoutSummaryUnitPrices() {
        var checkout = document.querySelector('.wp-block-woocommerce-checkout');

        if (!checkout) {
            return;
        }

        var scheduled = false;

        function schedulePlacement() {
            if (scheduled) {
                return;
            }

            scheduled = true;
            window.requestAnimationFrame(function () {
                scheduled = false;
                placeCheckoutSummaryUnitPrices(checkout);
            });
        }

        schedulePlacement();

        if (!window.MutationObserver) {
            return;
        }

        var observer = new MutationObserver(schedulePlacement);

        observer.observe(checkout, {
            childList: true,
            subtree: true
        });
    }

    blocksCheckout.registerCheckoutFilters('graceart', {
        cartItemPrice: function (defaultValue, extensions, args) {
            if (!args || args.context !== 'summary') {
                return defaultValue;
            }

            return '<price/> / kus';
        },
        subtotalPriceFormat: function (defaultValue, extensions, args) {
            if (!args || args.context !== 'cart') {
                return defaultValue;
            }

            return '<price/> / kus';
        },
        placeOrderButtonLabel: function (defaultLabel) {
            return strings.placeOrder || defaultLabel;
        },
        proceedToCheckoutButtonLabel: function (defaultLabel) {
            return strings.proceedToCheckout || defaultLabel;
        },
        totalLabel: function (defaultLabel) {
            return strings.totalLabel || defaultLabel;
        }
    });

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', watchCheckoutSummaryUnitPrices);
    } else {
        watchCheckoutSummaryUnitPrices();
    }
})();
