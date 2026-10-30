<?php

// No "added to cart" notice: the header cart count already reflects the
// change. wc_add_notice() drops an empty message, so nothing is stored.
add_filter('wc_add_to_cart_message_html', '__return_empty_string');
