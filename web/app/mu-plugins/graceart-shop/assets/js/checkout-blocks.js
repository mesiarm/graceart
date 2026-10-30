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

    /**
     * One shipping choice at a time: while the chosen rate is being saved the
     * options cannot be clicked, so quick clicks cannot cross each other (a
     * slower answer arriving last would put the choice back on an earlier
     * option).
     */
    function lockShippingWhileSaving() {
        if (!window.wp || !window.wp.data) {
            return;
        }

        var style = document.createElement('style');

        style.textContent = '.graceart-shipping-saving .wc-block-components-shipping-rates-control{pointer-events:none;opacity:.7}';
        document.head.appendChild(style);

        var saving = false;

        window.wp.data.subscribe(function () {
            var store = window.wp.data.select('wc/store/cart');
            var now = !!(store && typeof store.isShippingRateBeingSelected === 'function' && store.isShippingRateBeingSelected());

            if (now !== saving) {
                saving = now;
                document.body.classList.toggle('graceart-shipping-saving', now);
            }
        });
    }

    lockShippingWhileSaving();

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
