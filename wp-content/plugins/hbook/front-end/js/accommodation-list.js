jQuery( document ).ready( function( $ ) {
	$( '.hb-accom-list-booking-form' ).hide();
	$( '.hb-accom-list-view-accom' ).click( function() {
		if ( $( this ).parent( '.hb-accom-list-item' ).find( '.hb-accom-list-booking-form' ).is( ':hidden' ) ) {
			$( this ).parent( '.hb-accom-list-item' ).find( '.hb-accom-list-booking-form' ).slideDown();
		} else {
			$( this ).parent( '.hb-accom-list-item' ).find( '.hb-accom-list-booking-form' ).slideUp();
		}
		return false;
	});
	$( window ).resize( debouncer( function() {
		if ( $( '.hb-accom-list-row' ).width() < 650 ) {
			$( 'div.hb-accom-list-column' ).addClass( 'hb-accom-list-mobile-view');
		} else {
			$( 'div.hb-accom-list-column' ).removeClass( 'hb-accom-list-mobile-view');
		}
	}));

	function debouncer( func ) {
		var timeoutID,
			timeout = 50;
		return function () {
			var scope = this,
				args = arguments;
			clearTimeout( timeoutID );
			timeoutID = setTimeout( function () {
				func.apply( scope, Array.prototype.slice.call( args ) );
			}, timeout );
		}
	}

});