/*
 * Okienko „Pridané do košíka“ (functions.php: graceart_pridane_html).
 * Stránka produktu: okienko je už v stránke (data-otvorit), otvorí sa po načítaní.
 * Zoznamy produktov: WooCommerce ho vloží ako fragment a otvorí sa po udalosti added_to_cart.
 */
( function () {
	function otvor( dialog ) {
		if ( ! dialog ) {
			return;
		}
		if ( typeof dialog.showModal === 'function' ) {
			if ( dialog.open ) {
				dialog.close();
			}
			dialog.showModal();
		} else {
			dialog.setAttribute( 'open', '' );
		}
		var kosik = dialog.querySelector( '.graceart-pridane-do-kosika' );
		if ( kosik ) {
			kosik.focus();
		}
	}

	function zavri( dialog ) {
		if ( typeof dialog.close === 'function' ) {
			dialog.close();
		} else {
			dialog.removeAttribute( 'open' );
		}
	}

	document.addEventListener( 'click', function ( e ) {
		var t = e.target;
		if ( ! t.closest ) {
			return;
		}
		// klik na tmavé pozadie mimo okienka
		if ( t.matches( 'dialog.graceart-pridane' ) ) {
			zavri( t );
			return;
		}
		if ( t.closest( '.graceart-pridane-zavriet, .graceart-pridane-pokracovat' ) ) {
			zavri( t.closest( 'dialog.graceart-pridane' ) );
		}
	} );

	function start() {
		otvor( document.querySelector( 'dialog.graceart-pridane[data-otvorit]' ) );
	}
	if ( document.readyState === 'loading' ) {
		document.addEventListener( 'DOMContentLoaded', start );
	} else {
		start();
	}

	if ( window.jQuery ) {
		window.jQuery( document.body ).on( 'added_to_cart', function () {
			// fragmenty WooCommerce sa vymenia skôr ako sa zavolá táto funkcia
			window.setTimeout( function () {
				otvor( document.querySelector( '.graceart-pridane-miesto dialog.graceart-pridane' ) );
			}, 0 );
		} );
	}
}() );
