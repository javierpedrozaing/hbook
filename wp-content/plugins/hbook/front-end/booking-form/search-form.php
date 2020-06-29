<?php
class HbSearchForm {

	private $utils;
	private $hbdb;
	private $hb_strings;

	public function __construct( $hbdb, $utils, $hb_strings ) {
		$this->utils = $utils;
		$this->hbdb = $hbdb;
		$this->hb_strings = $hb_strings;
	}

	public function get_search_form_markup( $form_id, $form_action, $search_only_data, $search_form_placeholder, $is_admin, $check_in = '' , $check_out = '', $adults = '', $children = '', $accom_id = '', $options = '', $accom_num = '' ) {

		$people_selects = array(
			'adults' => '',
			'children' => ''
		);
		foreach ( $people_selects as $key => $markup ) {
			$loop_start = '';
			$loop_end = '';
			if ( $key == 'adults' ) {
				if (
					( get_option( 'hb_display_children_field' ) == 'no' ) &&
					( get_option( 'hb_minimum_occupancy_search_fields_on_accom_page' ) == 'yes' )
				) {
					$loop_start = get_post_meta( $accom_id, 'accom_min_occupancy', true );
				}
				if ( ! $loop_start ) {
					$loop_start = get_option( 'hb_minimum_adults' );
				}
				if ( $accom_id && ( get_option( 'hb_maximum_occupancy_search_fields_on_accom_page' ) == 'yes' ) ) {
					$loop_end = get_post_meta( $accom_id, 'accom_max_occupancy', true );
				}
				if ( ! $loop_end ) {
					$loop_end = get_option( 'hb_maximum_adults' );
				}
			} else {
				$loop_start = 0;
				if ( $accom_id && ( get_option( 'hb_maximum_occupancy_search_fields_on_accom_page' ) == 'yes' ) ) {
					$loop_end = get_post_meta( $accom_id, 'accom_max_occupancy', true );
				}
				if ( ! $loop_end ) {
					$loop_end = get_option( 'hb_maximum_children' );
				} else {
					$loop_end = $loop_end - get_option( 'hb_minimum_adults' );
				}
			}
			$markup_options = '';
			if ( $search_form_placeholder && ( $is_admin != 'yes' ) ) {
				$markup_options = '<option selected disabled>' . $this->hb_strings[ $key ] . '</option>';
			}
			for ( $i = $loop_start; $i <= $loop_end; $i++ ) {
				$markup_options .= '<option value="' . $i . '">' . $i . '</option>';
			}
			$people_selects[ $key ] = '<select id="' . $key . '" name="hb-' . $key . '" class="hb-' . $key . '">' . $markup_options . '</select>';
		}

		if ( $accom_id ) {
			$form_title = str_replace( '%accom_name', $this->utils->get_accom_title( $accom_id ), $this->hb_strings['accom_page_form_title'] );
		} else {
			$form_title = $this->hb_strings['default_form_title'];
		}
		if ( $form_title != '' ) {
			$form_title = apply_filters( 'hb_search_form_title', '<h3 class="hb-title hb-title-search-form">' . $form_title . '</h3>' );
		}

		$form_class = 'hb-booking-search-form';
		if ( get_option( 'hb_display_adults_field' ) == 'no' ) {
			$form_class .= ' hb-search-form-no-people';
		} else if ( get_option( 'hb_display_children_field' ) == 'no' ) {
			$form_class .= ' hb-search-form-no-children';
		}

		$form_markup = '
			<form [form_id] class="[form_class]" method="POST" data-search-only="[search_only_data]" action="[form_action]">
				[form_title]
				<div class="hb-searched-summary hb-clearfix">
					<p class="hb-check-dates-wrapper hb-chosen-check-in-date">[string_chosen_check_in] <span></span></p>
					<p class="hb-check-dates-wrapper hb-chosen-check-out-date">[string_chosen_check_out] <span></span></p>
					<p class="hb-people-wrapper hb-chosen-adults">[string_chosen_adults] <span></span></p>
					<p class="hb-people-wrapper hb-chosen-children">[string_chosen_children] <span></span></p>
					[chosen_admin_accommodation_type]
					<p class="hb-change-search-wrapper hb-search-button-wrapper">
						<input type="submit" value="[string_change_search_button]" />
					</p>
				</div><!-- .hb-searched-summary -->
				<div class="hb-search-fields-and-submit">
					<div class="hb-search-fields hb-clearfix">
						<p class="hb-check-dates-wrapper">
							[check_in_label]
							<input id="check-in-date" name="hb-check-in-date" class="hb-input-datepicker hb-check-in-date" type="text" placeholder="[check_in_placeholder]" autocomplete="off" />
							<input class="hb-check-in-hidden" name="hb-check-in-hidden" type="hidden" value="[check_in]" />
							<span class="hb-datepick-check-in-out-mobile-trigger hb-datepick-check-in-mobile-trigger"></span>
							<span class="hb-datepick-check-in-out-trigger hb-datepick-check-in-trigger"></span>
						</p>
						<p class="hb-check-dates-wrapper">
							[check_out_label]
							<input id="check-out-date" name="hb-check-out-date" class="hb-input-datepicker hb-check-out-date" type="text" placeholder="[check_out_placeholder]" autocomplete="off" />
							<input class="hb-check-out-hidden" name="hb-check-out-hidden" type="hidden" value="[check_out]" />
							<span class="hb-datepick-check-in-out-mobile-trigger hb-datepick-check-out-mobile-trigger"></span>
							<span class="hb-datepick-check-in-out-trigger hb-datepick-check-out-trigger"></span>
						</p>
						<p class="hb-people-wrapper hb-people-wrapper-adults">
							[adults_label]
							[people_selects_adults]
							<input class="hb-adults-hidden" type="hidden" value="[adults]" />
						</p>
						<p class="hb-people-wrapper hb-people-wrapper-children hb-people-wrapper-last">
							[children_label]
							[people_selects_children]
							<input class="hb-children-hidden" type="hidden" value="[children]" />
						</p>
						[admin_accommodation_type]
						<p class="hb-search-submit-wrapper hb-search-button-wrapper">
							[search_label]
							<input type="submit" id="hb-search-form-submit" value="[string_search_button]" />
						</p>
					</div><!-- .hb-search-fields -->
					<p class="hb-search-error">&nbsp;</p>
					<p class="hb-search-no-result">&nbsp;</p>
					<p class="hb-booking-searching">[string_searching]</p>
				</div><!-- .hb-search-fields-and-submit -->
				<input type="hidden" class="hb-results-show-only-accom-id" name="hb-results-show-only-accom-id" />
				<input type="hidden" class="hb-chosen-options" name="hb-chosen-options" value=\'[options]\' />
				<input type="hidden" class="hb-chosen-accom-num" name="hb-chosen-accom-num" value=\'[accom_num]\' />
			</form><!-- end #hb-booking-search-form -->
			<div class="hb-accom-list"></div>';

		if ( $is_admin == 'yes' ) {
			$accom = $this->hbdb->get_all_accom();
			$accom_options_markup = '<option value="all">' . esc_html__( 'All', 'hbook-admin' ) . '</option>';
			foreach ( $accom as $accom_id => $accom_name ) {
				$accom_options_markup .= '<option value="' . $accom_id . '">' . $accom_name . '</option>';
			}
			$admin_accommodation_type = '<p class="hb-accom-wrapper">';
			$admin_accommodation_type .= '<label>';
			$admin_accommodation_type .= esc_html__( 'Accommodation type:', 'hbook-admin' );
			$admin_accommodation_type .= '</label>';
			$admin_accommodation_type .= '<br/>';
			$admin_accommodation_type .= '<select class="hb-accom">';
			$admin_accommodation_type .= $accom_options_markup;
			$admin_accommodation_type .= '</select>';
			$admin_accommodation_type .= '</p>';
			$chosen_admin_accommodation_type = '<p class="hb-accom-wrapper hb-chosen-accom">';
			$chosen_admin_accommodation_type .= esc_html__( 'Accommodation type:', 'hbook-admin' );
			$chosen_admin_accommodation_type .= ' <span></span>';
			$chosen_admin_accommodation_type .= '</p>';
			$form_markup = str_replace( 'input type="submit"', 'input type="submit" class="button-primary"', $form_markup );
			$form_markup = str_replace( '[string_search_button]', esc_attr( 'Check price and availability', 'hbook-admin' ), $form_markup );
			$form_markup = str_replace( '[string_searching]', '<span class="spinner"></span> ' . esc_html( 'Searching...', 'hbook-admin' ), $form_markup );
			$form_markup = str_replace( '[admin_accommodation_type]', $admin_accommodation_type, $form_markup );
			$form_markup = str_replace( '[chosen_admin_accommodation_type]', $chosen_admin_accommodation_type, $form_markup );
		} else {
			$form_markup = str_replace( '[admin_accommodation_type]', '', $form_markup );
			$form_markup = str_replace( '[chosen_admin_accommodation_type]', '', $form_markup );
			$form_markup = apply_filters( 'hb_search_form_markup', $form_markup, $form_id );
		}

		if ( $search_form_placeholder && ( $is_admin != 'yes' ) ) {
			$form_markup = str_replace( '[check_in_placeholder]', $this->hb_strings['check_in'], $form_markup );
			$form_markup = str_replace( '[check_out_placeholder]', $this->hb_strings['check_out'], $form_markup );
			$form_markup = str_replace( '[check_in_label]', '', $form_markup );
			$form_markup = str_replace( '[check_out_label]', '', $form_markup );
			$form_markup = str_replace( '[adults_label]', '', $form_markup );
			$form_markup = str_replace( '[children_label]', '', $form_markup );
			$form_markup = str_replace( '[search_label]', '', $form_markup );
		} else {
			$form_markup = str_replace( '[check_in_placeholder]', '', $form_markup );
			$form_markup = str_replace( '[check_out_placeholder]', '', $form_markup );
			if ( $is_admin == 'yes' ) {
				$form_markup = str_replace( '[check_in_label]', '<label for="check-in-date">' . esc_html__( 'Check-in date:', 'hbook-admin' ) . '</label>', $form_markup );
				$form_markup = str_replace( '[check_out_label]', '<label for="check-out-date">' . esc_html__( 'Check-out date:', 'hbook-admin' ) . '</label>', $form_markup );
				$form_markup = str_replace( '[adults_label]', '<label for="adults">' . esc_html__( 'Adults:', 'hbook-admin' ) . '</label>', $form_markup );
				$form_markup = str_replace( '[children_label]', '<label for="children">' . esc_html__( 'Children:', 'hbook-admin' ) . '</label>', $form_markup );
				$form_markup = str_replace( '[string_chosen_check_out]', esc_html__( 'Check-in date:', 'hbook-admin' ), $form_markup );
				$form_markup = str_replace( '[string_chosen_check_in]', esc_html__( 'Check-out date:', 'hbook-admin' ), $form_markup );
				$form_markup = str_replace( '[string_chosen_adults]', esc_html__( 'Adults:', 'hbook-admin' ), $form_markup );
				$form_markup = str_replace( '[string_chosen_children]', esc_html__( 'Children:', 'hbook-admin' ), $form_markup );
			} else {
				$form_markup = str_replace( '[check_in_label]', '<label for="check-in-date">' . $this->hb_strings['check_in'] . '</label>', $form_markup );
				$form_markup = str_replace( '[check_out_label]', '<label for="check-out-date">' . $this->hb_strings['check_out'] . '</label>', $form_markup );
				$form_markup = str_replace( '[adults_label]', '<label for="adults">' . $this->hb_strings['adults'] . '</label>', $form_markup );
				$form_markup = str_replace( '[children_label]', '<label for="children">' . $this->hb_strings['children'] . '</label>', $form_markup );
			}
			$form_markup = str_replace( '[search_label]', '<label for="hb-search-form-submit">&nbsp;</label>', $form_markup );
		}
		$form_markup = str_replace( '[people_selects_adults]', $people_selects['adults'], $form_markup );
		$form_markup = str_replace( '[people_selects_children]', $people_selects['children'], $form_markup );

		if ( $form_id ) {
			$form_id = 'id="' . $form_id . '"';
		}
		$form_vars = array( 'form_id', 'form_class', 'search_only_data' , 'form_action', 'form_title', 'check_in', 'check_out', 'adults', 'children', 'options', 'accom_num' );
		foreach ( $form_vars as $var ) {
			$form_markup = str_replace( "[$var]", $$var, $form_markup );
		}

		$form_strings = array(
			'chosen_check_in', 'chosen_check_out', 'chosen_adults', 'chosen_children', 'change_search_button', 'check_in',
			'check_out', 'adults', 'children', 'search_button', 'searching'
		);
		foreach ( $form_strings as $string ) {
			$form_markup = str_replace( "[string_$string]", $this->hb_strings[ $string ], $form_markup );
		}

		return $form_markup;
	}
}