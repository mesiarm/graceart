/* Grace Art – pevná hlavička a šípka „Späť hore“ (téma 1.7.20).
 * Po posunutí stránky dostane <body> triedu graceart-scrolled → hlavička sa zmenší (CSS v theme.json).
 * Dva prahy (80 / 20 px), aby hlavička neblikala pri malom posune. */
( function () {
	var body = document.body;
	var on = false;
	var ticking = false;

	function check() {
		ticking = false;
		var y = window.pageYOffset || document.documentElement.scrollTop;
		if ( ! on && y > 80 ) {
			on = true;
			body.classList.add( 'graceart-scrolled' );
		} else if ( on && y < 20 ) {
			on = false;
			body.classList.remove( 'graceart-scrolled' );
		}
	}

	window.addEventListener( 'scroll', function () {
		if ( ! ticking ) {
			ticking = true;
			window.requestAnimationFrame( check );
		}
	}, { passive: true } );
	check();

	document.addEventListener( 'click', function ( e ) {
		var link = e.target.closest && e.target.closest( '.graceart-hore' );
		if ( ! link ) {
			return;
		}
		e.preventDefault();
		var reduce = window.matchMedia && window.matchMedia( '(prefers-reduced-motion: reduce)' ).matches;
		window.scrollTo( { top: 0, behavior: reduce ? 'auto' : 'smooth' } );
		/* klávesnica: focus na začiatok stránky (odkaz „Preskočiť na obsah“ / logo) */
		var first = document.querySelector( '.wp-site-blocks a, .wp-site-blocks button' );
		if ( first ) {
			first.focus( { preventScroll: true } );
		}
	} );
}() );
