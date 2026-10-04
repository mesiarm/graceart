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
    /**
     * The order summary prints the product name as plain text. The permalinks
     * come from the cart store (same order as the summary rows); the name
     * stays the blocks' own element and is only made to act as a link.
     */
    function cartPermalinks() {
        try {
            var cart = window.wp.data.select('wc/store/cart').getCartData();

            return (cart.items || []).map(function (cartItem) {
                return cartItem.permalink || '';
            });
        } catch (e) {
            return [];
        }
    }

    function placeCheckoutSummaryQuantities(root) {
        var scope = root || document;
        var items = scope.querySelectorAll('.wc-block-components-order-summary-item');
        var permalinks = cartPermalinks();

        items.forEach(function (item, index) {
            var nameElement = item.querySelector('.wc-block-components-order-summary-item__description .wc-block-components-product-name');
            var permalink = permalinks[index % Math.max(permalinks.length, 1)];

            if (nameElement && permalink && nameElement.tagName !== 'A' && !nameElement.closest('a') && nameElement.getAttribute('data-graceart-href') !== permalink) {
                nameElement.setAttribute('data-graceart-href', permalink);
                nameElement.setAttribute('role', 'link');
                nameElement.setAttribute('tabindex', '0');
            }

            var imageElement = item.querySelector('.wc-block-components-order-summary-item__image');

            if (imageElement && permalink && !imageElement.closest('a') && imageElement.getAttribute('data-graceart-href') !== permalink) {
                imageElement.setAttribute('data-graceart-href', permalink);
            }

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

    function followSummaryName(event) {
        var nameElement = event.target.closest && event.target.closest('[data-graceart-href]');

        if (!nameElement || (event.type === 'keydown' && event.key !== 'Enter')) {
            return;
        }

        window.location.href = nameElement.getAttribute('data-graceart-href');
    }

    document.addEventListener('click', followSummaryName);
    document.addEventListener('keydown', followSummaryName);

    function watchCheckoutSummaryQuantities() {
        var scheduled = false;

        // The blocks may replace their container while hydrating, so the
        // observer sits on the body and the container is looked up each time.
        function schedulePlacement() {
            if (scheduled) {
                return;
            }

            scheduled = true;
            window.requestAnimationFrame(function () {
                scheduled = false;
                trimWholeAmounts();

                var checkout = document.querySelector('.wp-block-woocommerce-checkout');

                if (checkout) {
                    placeCheckoutSummaryQuantities(checkout);
                }
            });
        }

        schedulePlacement();

        if (!window.MutationObserver) {
            return;
        }

        new MutationObserver(schedulePlacement).observe(document.body, {
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

            return '<price/> / ks';
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
