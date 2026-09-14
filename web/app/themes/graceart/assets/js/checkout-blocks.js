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

    function cartItemQuantityLabel(quantity) {
        var parsedQuantity = parseInt(quantity, 10);

        if (!parsedQuantity || parsedQuantity < 1) {
            parsedQuantity = 1;
        }

        return parsedQuantity + ' ks';
    }

    blocksCheckout.registerCheckoutFilters('graceart', {
        cartItemPrice: function (defaultValue, extensions, args) {
            if (!args || args.context !== 'summary') {
                return defaultValue;
            }

            return '<price/> · ' + cartItemQuantityLabel(args.cartItem && args.cartItem.quantity);
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
})();
