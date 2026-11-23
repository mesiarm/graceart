/*
 * Tlačidlá, ktoré vedú do košíka / pokladne, po kliknutí hneď ukážu načítavanie.
 * Ďalšia stránka sa na mobile načítava aj niekoľko sekúnd a bez tejto odozvy to vyzerá, že sa nič nedeje.
 */
( function () {
	var SELECTOR = [
		'.graceart-pridane-do-kosika',
		'.wc-block-cart__submit-button',
		'.wc-block-mini-cart__footer-checkout',
		'.wc-block-mini-cart__footer-cart',
	].join( ',' );

	document.addEventListener( 'click', function ( e ) {
		if ( e.defaultPrevented || e.button > 0 || e.metaKey || e.ctrlKey || e.shiftKey || ! e.target.closest ) {
			return;
		}
		var button = e.target.closest( SELECTOR );
		if ( ! button ) {
			return;
		}
		if ( button.classList.contains( 'graceart-prechod' ) ) {
			// druhý klik počas načítavania nerobí nič navyše
			e.preventDefault();
			return;
		}
		button.classList.add( 'graceart-prechod' );
		button.setAttribute( 'aria-busy', 'true' );
	}, true );

	// po návrate tlačidlom Späť (stránka z cache) nesmie ostať točiť
	window.addEventListener( 'pageshow', function ( e ) {
		if ( ! e.persisted ) {
			return;
		}
		document.querySelectorAll( '.graceart-prechod' ).forEach( function ( el ) {
			el.classList.remove( 'graceart-prechod' );
			el.removeAttribute( 'aria-busy' );
		} );
	} );
}() );
