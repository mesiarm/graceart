/**
 * Zoznamy produktov (Produkty, kategórie, vyhľadávanie): po zmene triedenia,
 * po kliknutí na kategóriu v prepínači (pilulky) a pri prechode na ďalšiu
 * stranu sa nová stránka plynulo posunie k produktom (<main id="produkty">).
 * Zámer si pamätáme v sessionStorage, aby prehliadač neskočil na kotvu naraz.
 * Ak sessionStorage nefunguje, použije sa kotva #produkty (skok bez animácie).
 */
( function () {
	'use strict';
	var KEY = 'graceart-scroll-produkty';

	function remember( link ) {
		try {
			window.sessionStorage.setItem( KEY, '1' );
		} catch ( e ) {
			if ( link && link.href.indexOf( '#' ) === -1 ) {
				link.href += '#produkty';
			}
		}
	}

	function takeFlag() {
		try {
			var flag = window.sessionStorage.getItem( KEY );
			window.sessionStorage.removeItem( KEY );
			return '1' === flag;
		} catch ( e ) {
			return false;
		}
	}

	document.addEventListener( 'DOMContentLoaded', function () {
		var target = document.getElementById( 'produkty' );
		if ( ! target ) {
			return;
		}

		// Triedenie: WooCommerce odošle formulár hneď po zmene výberu.
		document.addEventListener( 'change', function ( e ) {
			if ( e.target.matches && e.target.matches( 'form.woocommerce-ordering select' ) ) {
				remember( null );
			}
		}, true );

		// Pilulky kategórií a strany výsledkov (1, 2, 3 …).
		document.addEventListener( 'click', function ( e ) {
			var link = e.target.closest && e.target.closest( '.graceart-cat-switch a, .woocommerce-pagination a.page-numbers' );
			if ( link ) {
				remember( link );
			}
		}, true );

		if ( takeFlag() ) {
			var reduce = window.matchMedia && window.matchMedia( '(prefers-reduced-motion: reduce)' ).matches;
			window.setTimeout( function () {
				window.scrollTo( {
					top: target.getBoundingClientRect().top + window.pageYOffset,
					behavior: reduce ? 'auto' : 'smooth'
				} );
			}, 150 );
		}
	} );
}() );
