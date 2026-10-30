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
     * Whole amounts read "12 €", not "12,00 €" (the classic pages already
     * trim the zeros; the blocks format prices in React, so it is done here).
     */
    function trimWholeAmounts() {
        document.querySelectorAll('.wc-block-formatted-money-amount, .wc-block-components-formatted-money-amount').forEach(function (amount) {
            var text = amount.textContent;

            if (/[,.]00(?!\d)/.test(text)) {
                amount.textContent = text.replace(/([,.])00(?!\d)/, '');
            }
        });
    }

    function watchAmounts() {
        var scheduled = false;

        function schedule() {
            if (scheduled) {
                return;
            }

            scheduled = true;
            window.requestAnimationFrame(function () {
                scheduled = false;
                trimWholeAmounts();
            });
        }

        schedule();

        if (window.MutationObserver) {
            new MutationObserver(schedule).observe(document.body, {
                childList: true,
                subtree: true
            });
        }
    }

    blocksCheckout.registerCheckoutFilters('graceart', {
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
        document.addEventListener('DOMContentLoaded', watchAmounts);
    } else {
        watchAmounts();
    }
})();
