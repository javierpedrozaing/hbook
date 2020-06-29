jQuery( document ).ready( function( $ ) {

	$( '.hb-import-settings' ).click( function() {
		$( '.hb-import-settings' ).blur();
		if ( $( '#hb-import-settings-file' ).val() == '' ) {
			alert( hb_text.choose_file );
			return false;
		}
		if ( confirm( hb_text.import_confirm_text ) ) {
			$( '#hb-import-export-action' ).val( 'import-settings' );
			$( '#hb-settings-form' ).submit();
		} else {
			return false;
		}
	});

	$( '.hb-export-settings' ).click( function() {
		$( this ).blur();
		$( '#hb-import-export-action' ).val( 'export-settings' );
		$( '#hb-settings-form' ).submit();
		$( '#hb-import-export-action' ).val( '' );
		return false;
	});
});