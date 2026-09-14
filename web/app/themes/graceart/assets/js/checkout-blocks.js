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

    /**
     * The order summary shows the quantity as a badge over the thumbnail.
     * Here it reads as a line under the product name instead; the badge is
     * hidden in CSS but still rendered, so its value is read from there.
     */
    function placeCheckoutSummaryQuantities(root) {
        var scope = root || document;
        var items = scope.querySelectorAll('.wc-block-components-order-summary-item');

        items.forEach(function (item) {
            var badge = item.querySelector('.wc-block-components-order-summary-item__quantity [aria-hidden="true"]');
            var name = item.querySelector('.wc-block-components-order-summary-item__description .wc-block-components-product-name');

            if (!badge || !name) {
                return;
            }

            var label = badge.textContent.trim() + ' ks';
            var line = name.nextElementSibling;

            if (!line || !line.classList.contains('graceart-order-summary-item__quantity')) {
                line = document.createElement('div');
                line.className = 'graceart-order-summary-item__quantity';
                name.parentNode.insertBefore(line, name.nextSibling);
            }

            if (line.textContent !== label) {
                line.textContent = label;
            }
        });
    }

    function watchCheckoutSummaryQuantities() {
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
                placeCheckoutSummaryQuantities(checkout);
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
        // Unit price in both the cart rows and the checkout order summary
        // (where CSS shows it in place of the line total).
        subtotalPriceFormat: function (defaultValue, extensions, args) {
            if (!args || (args.context !== 'cart' && args.context !== 'summary')) {
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
        document.addEventListener('DOMContentLoaded', watchCheckoutSummaryQuantities);
    } else {
        watchCheckoutSummaryQuantities();
    }
})();
