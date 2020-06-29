jQuery( document ).ready( function( $ ) {

	$( '.hb-remove-purchase-code' ).click( function() {
		if ( confirm( hb_text.remove_purchase_code ) ) {
			$( 'input[name="hb-purchase-code"]' ).val( '' );
			$( '#hb-verify-licence' ).submit();
		}
		return false;
	});
	$( '.hb-remove-addon-purchase-code' ).click( function() {
		if ( confirm( hb_text.remove_purchase_code ) ) {
			$( this ).parents( 'form' ).find( 'input[name="hb-addon-purchase-code"]' ).val( '' );
			$( this ).parents( 'form' ).submit();
		}
		return false;
	});

});

