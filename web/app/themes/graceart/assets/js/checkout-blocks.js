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

    blocksCheckout.registerCheckoutFilters('graceart', {
        placeOrderButtonLabel: function (defaultLabel) {
            return strings.placeOrder || defaultLabel;
        },
        proceedToCheckoutButtonLabel: function (defaultLabel) {
            return strings.proceedToCheckout || defaultLabel;
        }
    });
})();
