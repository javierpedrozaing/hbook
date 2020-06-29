jQuery( document ).ready( function( $ ) {

	function toggle_notification_option() {
		if ( $( 'input[name="hb_ical_record_sync_errors"]:checked' ).val() == 'yes' ) {
			$( '.hb-ical-notification-option' ).slideDown();
		} else {
			$( '.hb-ical-notification-option' ).slideUp();
		}
	}

	toggle_notification_option();

	$( '.hb-ical-record-sync-errors' ).change( function() {
		toggle_notification_option();
	});

	$( '.ical-export-url' ).click( function() {
		$( this ).parents( 'tr' ).find( '.ical-export-url-value' ).slideToggle();
		return false;
	});

	$( '.all-icals-export-url' ).click( function() {
		$( '.all-icals-export-url-value' ).slideToggle();
		return false;
	});

	$( '.ical-upload' ).click( function() {
		$( this ).parents( 'td' ).find( 'form' ).slideDown();
		$( this ).slideUp();
		return false;
	});

	$( '.ical-upload-cancel' ).click( function() {
		$( this ).parents( 'tr' ).find( 'td .import-ical-form' ).slideUp();
		$( this ).parents( 'tr' ).find( '.ical-upload' ).slideDown();
	});

	$( '.ical-synchro' ).click( function() {
		$( this ).parents( 'td' ).find( '.save-changes' ).hide();
		$( this ).parents( 'td' ).find( '.add-calendar' ).show();
		$( this ).parents( 'td' ).find( '.hb-import-calendar-name' ).val( '' );
		$( this ).parents( 'td' ).find( '.hb-import-calendar-url' ).val( '' );
		$( this ).parents( 'td' ).find( 'form' ).slideDown();
		$( this ).slideUp();
		$( this ).parents( 'td' ).find( '.ical-url-form-action' ).val( 'new-calendar' );
		return false;
	});

	$( '.ical-url-cancel' ).click( function() {
		$( this ).parents( 'tr' ).find( 'td .import-url-form' ).slideUp();
		$( this ).parents( 'tr' ).find( '.ical-synchro' ).slideDown();
	});

	$( '.ical-synchro-delete' ).click( function() {
		if ( confirm( hb_text.confirm_delete ) ) {
			$( this ).parents( 'form' ).submit();
		};
		return false;
	});

	$( '.ical-synchro-edit' ).click( function() {
		$( this ).parents( 'tr' ).find( 'td .add-calendar' ).hide();
		$( this ).parents( 'tr' ).find( 'td .save-changes' ).show();
		$( this ).parents( 'tr' ).find( 'td .import-url-form' ).slideDown();
		$( this ).parents( 'tr' ).find( 'td .ical-synchro' ).slideUp();
		$( this ).parents( 'tr' ).find( 'td .ical-url-form-action' ).val( 'edit-calendar' );
		var calendarName = $( this ).parent().find( '.ical-synchro-calendar-name' ).html();
		$( this ).parents( 'tr' ).find( 'td .hb-import-calendar-name' ).val( calendarName );
		var calendarUrl = $( this ).parent().find( '.ical-calendar-url' ).val();
		$( this ).parents( 'tr' ).find( 'td .hb-import-calendar-url' ).val( calendarUrl );
		$( this ).parents( 'tr' ).find( 'td .edit-calendar-url' ).val( calendarUrl );
		var calendarId = $( this ).parent().find( '.ical-calendar-id' ).val();
		$( this ).parents( 'tr' ).find( 'td .edit-calendar-id' ).val( calendarId );
		return false;
	});

});