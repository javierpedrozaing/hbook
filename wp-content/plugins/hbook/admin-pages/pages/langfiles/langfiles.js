jQuery( document ).ready( function( $ ) {

	$( '#hb-import-file-form' ).submit( function() {
		$( '#hb-import-lang-submit' ).blur();
		if ( $( '#hb-import-lang-code' ).val() == '' ) {
			alert( hb_text.select_language );
			return false;
		}
		if ( $( '#hb-import-lang-file' ).val() == '' ) {
			alert( hb_text.choose_file );
			return false;
		}
	});

	$( '.hb-export-lang-file' ).click( function() {
		$( this ).blur();
		$( '#hb-locale-export' ).val( $( this ).data( 'locale' ) );
		$( '#hb-export-lang-form' ).submit();
		return false;
	});

} );