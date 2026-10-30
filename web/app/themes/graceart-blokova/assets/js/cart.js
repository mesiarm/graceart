/* Grace Art – košík: "Do dopravy zadarmo vám chýba …" sa mení spolu s košíkom (bez obnovenia stránky). */
( function () {
	var box = document.querySelector( '.graceart-cart-info' );
	if ( ! box || ! window.wp || ! wp.data ) {
		return;
	}
	var min = parseFloat( box.dataset.min );
	var text = box.querySelector( '.graceart-cart-info-text' );
	var bar = box.querySelector( '.graceart-cart-info-bar span' );
	var last = null;

	function money( value ) {
		var whole = Math.round( value * 100 ) % 100 === 0;
		return value.toLocaleString( 'sk-SK', {
			minimumFractionDigits: whole ? 0 : 2,
			maximumFractionDigits: 2,
		} ) + ' €';
	}

	/* Skratka je v obsahu stránky až pod košíkom – presunúť ju nad súčet objednávky. */
	function place() {
		var side = document.querySelector( '.wc-block-cart__sidebar' );
		if ( side && box.parentNode !== side ) {
			side.insertBefore( box, side.firstChild );
		}
	}

	function update() {
		place();
		var store = wp.data.select( 'wc/store/cart' );
		if ( ! store || ! store.hasFinishedResolution( 'getCartData' ) ) {
			return;
		}
		var totals = store.getCartTotals();
		var unit = Math.pow( 10, totals.currency_minor_unit || 0 );
		var total = ( parseInt( totals.total_items, 10 ) - parseInt( totals.total_discount, 10 ) ) / unit;
		var count = store.getCartData().itemsCount;
		var key = total + '|' + count;
		if ( key === last ) {
			return;
		}
		last = key;
		box.hidden = ! count;
		text.innerHTML = total >= min
			? 'Máte <strong>dopravu zadarmo</strong>.'
			: 'Do dopravy zadarmo vám chýba <strong>' + money( min - total ) + '</strong>.';
		bar.style.width = Math.min( 100, Math.round( total / min * 100 ) ) + '%';
	}

	wp.data.subscribe( update );
	update();
} )();
