/* Grace Art – pokladňa: názov produktu v súhrne objednávky je odkaz na produkt (téma 1.7.20).
 * WooCommerce súhrn v pokladni vypisuje názov len ako text. Adresu produktu vezmeme z košíka
 * (wp.data 'wc/store/cart', položka.permalink) a textové uzly názvu presunieme do <a> –
 * React si drží odkazy na tie isté textové uzly, takže prípadná zmena textu sa prejaví aj v odkaze. */
( function () {
	if ( ! window.wp || ! wp.data ) {
		return;
	}
	var decoder = document.createElement( 'textarea' );

	function clean( text ) {
		decoder.innerHTML = String( text || '' );
		return decoder.value.replace( /\s+/g, ' ' ).trim();
	}

	function run() {
		var store = wp.data.select( 'wc/store/cart' );
		if ( ! store || ! store.getCartData ) {
			return;
		}
		var items = ( store.getCartData() || {} ).items || [];
		if ( ! items.length ) {
			return;
		}
		var rows = document.querySelectorAll( '.wc-block-components-order-summary-item' );
		rows.forEach( function ( row, index ) {
			var name = row.querySelector( '.wc-block-components-product-name' );
			if ( ! name || name.querySelector( 'a.graceart-summary-link' ) ) {
				return;
			}
			var text = clean( name.textContent );
			var item = items.filter( function ( i ) {
				return clean( i.name ) === text;
			} )[ 0 ] || items[ index ];
			if ( ! item || ! item.permalink ) {
				return;
			}
			var link = document.createElement( 'a' );
			link.className = 'graceart-summary-link';
			link.href = item.permalink;
			while ( name.firstChild ) {
				link.appendChild( name.firstChild );
			}
			name.appendChild( link );
		} );
	}

	var timer = null;
	function later() {
		if ( ! timer ) {
			timer = window.setTimeout( function () {
				timer = null;
				run();
			}, 100 );
		}
	}

	wp.data.subscribe( later );
	new MutationObserver( later ).observe( document.body, { childList: true, subtree: true } );
	later();
}() );
