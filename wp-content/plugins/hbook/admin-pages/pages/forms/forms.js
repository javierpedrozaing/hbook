jQuery( document ).ready( function( $ ) {

	$( 'input[name="hb_display_adults_field"], input[name="hb_display_children_field"]' ).change( function() {
		people_fields();
	});

	people_fields();

	function people_fields() {
		if ( $( 'input[name="hb_display_adults_field"]:checked' ).val() == 'no' ) {
			$( '#hb_display_children_field_no' ).prop( 'checked', true );
			$( '#hb_display_children_field_yes' ).prop( 'disabled', true );
			$(
				'.hb-maximum-adults-option-wrapper,' +
				'.hb-minimum-adults-option-wrapper,' +
				'.hb-maximum-children-option-wrapper,' +
				'.hb-maximum-occupancy-search-fields-option-wrapper,' +
				'.hb-minimum-occupancy-search-fields-option-wrapper'
			).slideUp();
		} else {
			$( '#hb_display_children_field_yes' ).prop( 'disabled', false );
			$(
				'.hb-maximum-adults-option-wrapper,' +
				'.hb-minimum-adults-option-wrapper,' +
				'.hb-maximum-occupancy-search-fields-option-wrapper,' +
				'.hb-minimum-occupancy-search-fields-option-wrapper'
			).slideDown();
			if ( $( 'input[name="hb_display_children_field"]:checked' ).val() == 'yes' ) {
				$( '.hb-maximum-children-option-wrapper' ).slideDown();
				$( '.hb-minimum-occupancy-search-fields-option-wrapper' ).slideUp();
			} else {
				$( '.hb-maximum-children-option-wrapper' ).slideUp();
				$( '.hb-minimum-occupancy-search-fields-option-wrapper' ).slideDown();
			}
		}
	}

	$( 'input[name="hb_thumb_display"]' ).change( function() {
		accom_thumb_options();
	});

	accom_thumb_options();

	function accom_thumb_options() {
		if ( $( 'input[name="hb_thumb_display"]:checked' ).val() == 'no' ) {
			$( '.hb-accom-thumb-options-wrapper' ).slideUp();
		} else {
			$( '.hb-accom-thumb-options-wrapper' ).slideDown();
		}
	}

	$( 'input[name="hb_display_price"]' ).change( function() {
		display_price_options();
	});

	display_price_options();

	function display_price_options() {
		if ( $( 'input[name="hb_display_price"]:checked' ).val() == 'no' ) {
			$( '.hb-price-options-wrapper' ).slideUp();
		} else {
			$( '.hb-price-options-wrapper' ).slideDown();
		}
	}

	$( 'input[name="hb_display_price_breakdown"]' ).change( function() {
		display_price_breakdown_options();
	});

	display_price_breakdown_options();

	function display_price_breakdown_options() {
		if ( $( 'input[name="hb_display_price_breakdown"]:checked' ).val() == 'no' ) {
			$( '.hb-price-breakdown-options-wrapper' ).slideUp();
		} else {
			$( '.hb-price-breakdown-options-wrapper' ).slideDown();
		}
	}

});