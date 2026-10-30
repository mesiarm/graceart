/* Grace Art – stránka produktu: tlačidlá − a + pri množstve, cena variantu, zbalenie dlhého popisu. */
( function () {
	function enhance( wrap ) {
		var input = wrap.querySelector( 'input.qty' );
		if ( ! input || input.type === 'hidden' || wrap.dataset.gaQty ) {
			return;
		}
		wrap.dataset.gaQty = '1';
		wrap.classList.add( 'graceart-qty' );

		function make( label, text, dir ) {
			var b = document.createElement( 'button' );
			b.type = 'button';
			b.className = 'graceart-qty-btn';
			b.setAttribute( 'aria-label', label );
			b.textContent = text;
			b.addEventListener( 'click', function () {
				var step = parseFloat( input.step ) || 1;
				var min = input.min !== '' ? parseFloat( input.min ) : 1;
				var max = input.max !== '' ? parseFloat( input.max ) : Infinity;
				var val = parseFloat( input.value ) || min;
				val = Math.min( max, Math.max( min, val + dir * step ) );
				input.value = val;
				input.dispatchEvent( new Event( 'change', { bubbles: true } ) );
				input.dispatchEvent( new Event( 'input', { bubbles: true } ) );
			} );
			return b;
		}
		wrap.insertBefore( make( 'Menej', '−', -1 ), input );
		wrap.appendChild( make( 'Viac', '+', 1 ) );
	}
	/*
	 * Cena a sklad pod popisom (nad výberom variantu): najnižšia cena, po výbere
	 * variantu presná cena a sklad variantu na tom istom mieste, s krátkou animáciou.
	 */
	function variationPrice() {
		var box = document.querySelector( '.graceart-product-price .wc-block-components-product-price' );
		var stock = document.querySelector( '.graceart-buy-row .graceart-product-stock' );
		if ( ! box || ! window.jQuery ) {
			return;
		}
		var original = box.innerHTML;
		var stockText = stock ? stock.textContent : '';
		var stockClass = stock ? stock.className : '';

		function pulse() {
			box.classList.remove( 'is-updated' );
			void box.offsetWidth; // nové spustenie animácie
			box.classList.add( 'is-updated' );
		}
		function resetStock() {
			if ( stock ) {
				stock.className = stockClass;
				stock.textContent = stockText;
			}
		}
		function showStock( html ) {
			var tmp = document.createElement( 'div' );
			tmp.innerHTML = html || '';
			var p = tmp.querySelector( '.stock' );
			if ( ! stock || ! p || ! p.textContent.trim() ) {
				resetStock();
				return;
			}
			var state = p.classList.contains( 'out-of-stock' ) ? 'out-of-stock'
				: ( p.classList.contains( 'available-on-backorder' ) ? 'available-on-backorder' : 'in-stock' );
			stock.className = stockClass.replace( /wc-block-components-product-stock-indicator--[\w-]+/g, '' )
				+ ' wc-block-components-product-stock-indicator--' + state + ' is-variation';
			stock.textContent = p.textContent.trim();
		}

		window.jQuery( '.graceart-product-form .variations_form' )
			.on( 'found_variation', function ( e, variation ) {
				var html = variation.price_html
					? variation.price_html.replace( /^<span class="price">|<\/span>$/g, '' )
					: original;
				if ( box.innerHTML !== html ) {
					box.innerHTML = html;
					pulse();
				}
				showStock( variation.availability_html );
			} )
			.on( 'reset_data', function () {
				box.innerHTML = original;
				resetStock();
			} );
	}
	/*
	 * Dlhý popis v časti "O produkte": prázdne odseky preč, zobrazí sa začiatok
	 * a tlačidlo "Čítať ďalej". Krátke popisy ostanú celé.
	 */
	function longDescription() {
		var box = document.querySelector( '.graceart-product-info .wp-block-post-content' );
		if ( ! box ) {
			return;
		}
		box.querySelectorAll( 'p' ).forEach( function ( p ) {
			if ( ! p.textContent.trim() && ! p.querySelector( 'img, iframe, video' ) ) {
				p.remove();
			}
		} );
		var limit = window.matchMedia( '(max-width: 781px)' ).matches ? 300 : 360;
		if ( box.scrollHeight < limit * 1.5 ) {
			return;
		}
		box.classList.add( 'graceart-desc', 'is-collapsed' );
		var btn = document.createElement( 'button' );
		btn.type = 'button';
		btn.className = 'graceart-desc-toggle';
		btn.textContent = 'Čítať ďalej';
		btn.setAttribute( 'aria-expanded', 'false' );
		btn.addEventListener( 'click', function () {
			var collapsed = box.classList.toggle( 'is-collapsed' );
			btn.textContent = collapsed ? 'Čítať ďalej' : 'Zobraziť menej';
			btn.setAttribute( 'aria-expanded', collapsed ? 'false' : 'true' );
			if ( collapsed && box.getBoundingClientRect().top < 0 ) {
				box.scrollIntoView( { block: 'start' } );
			}
		} );
		box.parentNode.insertBefore( btn, box.nextSibling );
	}
	function run() {
		document.querySelectorAll( '.single-product .quantity' ).forEach( enhance );
		variationPrice();
		longDescription();
	}
	if ( document.readyState === 'loading' ) {
		document.addEventListener( 'DOMContentLoaded', run );
	} else {
		run();
	}
} )();
