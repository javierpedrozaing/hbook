<?php
class HbAvailableAccom {

	private $hbdb;
	private $utils;
	private $strings;
	private $price_calc;
	private $options_form; 

	public function __construct( $hbdb, $utils, $strings, $price_calc, $options_form ) {
		$this->hbdb = $hbdb;
		$this->utils = $utils;
		$this->strings = $strings;
		$this->price_calc = $price_calc;
		$this->options_form = $options_form;
	}

	public function get_available_accom( $search_request ) {
		
		$hb_strings = $this->strings;
		$str_check_in = $search_request['check_in'];
		$str_check_out = $search_request['check_out'];
		$adults = $search_request['adults'];
		$children = $search_request['children'];
		$page_accom_id = $search_request['page_accom_id'];
		$current_page_id = $search_request['current_page_id'];
		$is_admin = $search_request['is_admin'];
		$admin_accom_id = $search_request['admin_accom_id'];

		if ( $this->utils->nb_accom() == 0 ) {
			return array(
				'success' => false,
				'msg' => 'Unexpected error (no accommodation defined).'
			);
		}

		$validation = $this->utils->validate_date_and_people( $str_check_in, $str_check_out, $adults, $children );
		if ( ! $validation['success'] ) {
			return array(
				'success' => false,
				'msg' => 'Error (' . $validation['error_msg'] . ').'
			);
		}

		$nb_nights = $this->utils->get_number_of_nights( $str_check_in, $str_check_out );
		$nb_people = $adults + $children;

		if ( $is_admin == 'yes' ) {
			if ( $admin_accom_id == 'all' ) {
				$accom = $this->hbdb->get_all_accom_ids();
			} else {
				$accom = array( $admin_accom_id );
			}
			$available_accom_dates = $this->hbdb->get_available_accom_per_dates( $str_check_in, $str_check_out );
			$accom_suit_people = $this->hbdb->get_accom_per_occupancy( $nb_people );

			$available_accom = array();
			$output = '';
			if ( $admin_accom_id == 'all' ) {
				if ( count( $accom ) != count( $available_accom_dates ) ) {
					$output .= '<p>';
					$output .= '<input id="hb-show-unavailable-accom" class="hb-filter-accom-list" type="checkbox" />';
					$output .= '<label for="hb-show-unavailable-accom"> ';
					$output .= esc_html__( 'Show unavailable accommodation at the chosen dates', 'hbook-admin' );
					$output .= '</label>';
					$output .= '</p>';
				}
				if ( count( $accom ) != count( $accom_suit_people ) ) {
					$output .= '<p>';
					$output .= '<input id="hb-show-people-unsuitable-accom" class="hb-filter-accom-list" type="checkbox" />';
					$output .= '<label for="hb-show-people-unsuitable-accom"> ';
					if ( $nb_people == 1 ) {
						$output .= sprintf( esc_html__( 'Show accommodation not suitable for 1 person', 'hbook-admin' ), $nb_people );
					} else {
						$output .= sprintf( esc_html__( 'Show accommodation not suitable for %s persons', 'hbook-admin' ), $nb_people );
					}
					$output .= '</label>';
					$output .= '</p>';
				}
			}
			foreach ( $accom as $accom_id ) {
				$price_breakdown = '';
				$prices = $this->price_calc->get_price( $accom_id, $str_check_in, $str_check_out, $adults, $children, $price_breakdown );
				if ( ! $prices['success'] ) {
					return array(
						'success' => false,
						'msg' => $prices['error']
					);
				} else {
					$price = $prices['prices']['accom_total'];
				}
				$accom_div_class = 'hb-accom hb-accom-id-' . $accom_id;
				if ( $admin_accom_id == 'all' ) {
					$accom_div_class .= ' hb-accom-multiple';
				}
				$not_available_text = '';
				$not_suits_people = '';
				if ( in_array( $accom_id, $available_accom_dates ) ) {
					$accom_div_class .= ' hb-accom-available';
				} else {
					$not_available_text = '<p><small>';
					$not_available_text .= esc_html__( 'Not available at the chosen dates.', 'hbook-admin' );
					$not_available_text .= '</small></p>';
				}
				if ( in_array( $accom_id, $accom_suit_people ) ) {
					$accom_div_class .= ' hb-accom-suits-people';
				} else {
					$not_suits_people = '<p><small>';
					if ( $nb_people == 1 ) {
						$not_suits_people .= esc_html__( 'Not suitable for 1 person.', 'hbook-admin' );
					} else {
						$not_suits_people .= sprintf( esc_html__( 'Not suitable for %s persons.', 'hbook-admin' ), $nb_people );
					}
					$not_suits_people .= '</small></p>';
				}
				$output .= '<div class="' . $accom_div_class . '" data-accom-id="' . $accom_id . '">';
				$output .= $not_available_text;
				$output .= $not_suits_people;
				$output .= '<p>';
				$output .= '<span class="hb-accom-title">' . $this->utils->get_accom_title( $accom_id ) . '</span>';
				$output .= '<span class="hb-accom-price"> - ' . $this->utils->price_with_symbol( $price ) . '</span>';
				$output .= '</p>';
				$output .= '<input type="hidden" class="hb-accom-price-raw" value="' . $price . '" />';
				$output .= '<p class="hb-price-breakdown">' . $price_breakdown . '</p>';
				if ( $admin_accom_id == 'all' ) {
					$output .= '<p class="hb-select-accom">';
					$output .= '<input type="button" class="button hb-select-accom-button" value="' . esc_attr( 'Select', 'hbook-admin' ) . '" />';
					$output .= '<input type="button" class="button hb-select-accom-button-disabled" value="' . esc_attr( 'Selected', 'hbook-admin' ) . '" disabled />';
					$output .= '</p>';
				}
				$output .= '</div>';
			}
			$output .= $this->get_available_accom_num( $available_accom_dates, $str_check_in, $str_check_out, $is_admin );
			$output .= $this->options_form->get_options_form_markup_backend( $adults, $children, $nb_nights );
			return array(
				'success' => true,
				'mark_up' => $output
			);
		}

		if ( $search_request['exists_main_booking_form'] == 'yes' ) {
			$exists_main_booking_form = true;
		} else {
			$exists_main_booking_form = false;
		}

		if ( $search_request['force_display_thumb'] == 'yes' ) {
			$force_display_thumb = true;
		} else {
			$force_display_thumb = false;
		}

		if ( $search_request['force_display_desc'] == 'yes' ) {
			$force_display_desc = true;
		} else {
			$force_display_desc = false;
		}

		$accom_name = '';
		$is_accom_page = false;
		if ( $page_accom_id ) {
			$accom_name = $this->utils->get_accom_title( $page_accom_id );
			$is_accom_page = true;
		}

		$suit_people_accom = $this->hbdb->get_accom_per_occupancy( $nb_people );
		if ( count( $suit_people_accom ) == 0 ) {
			if ( $is_accom_page ) {
				// Unfortunately the %1$s can not suit %2$s persons.
				if ( $nb_people == 1 ) {
					$msg = $hb_strings['accom_can_not_suit_one_person'];
				} else {
					$msg = $hb_strings['accom_can_not_suit_nb_people'];
					$msg = str_replace( '%persons_nb', $nb_people, $msg );
				}
				$msg = str_replace( '%accom_name', $accom_name, $msg );
				return array(
					'success' => false,
					'msg' => $msg
				);
			} else {
				// Unfortunately we could not find any accommodation that would suit %s persons.
				if ( $nb_people == 1 ) {
					$msg = $hb_strings['no_accom_can_suit_one_person'];
				} else {
					$msg = $hb_strings['no_accom_can_suit_nb_people'];
					$msg = str_replace( '%persons_nb', $nb_people, $msg );
				}
				return array(
					'success' => false,
					'msg' => $msg
				);
			}
		} else if (
			$is_accom_page &&
			(
				( $nb_people > get_post_meta( $page_accom_id, 'accom_max_occupancy', true ) ) ||
				( $nb_people < get_post_meta( $page_accom_id, 'accom_min_occupancy', true ) )
			)
		) {
			// Unfortunately the %1$s can not suit %2$s persons.
			if ( $nb_people == 1 ) {
				$msg_part1 = $hb_strings['accom_can_not_suit_one_person'];
			} else {
				$msg_part1 = $hb_strings['accom_can_not_suit_nb_people'];
				$msg_part1 = str_replace( '%persons_nb', $nb_people, $msg_part1 );
			}
			$msg_part1 = str_replace( '%accom_name', $accom_name, $msg_part1 );

			// View all available accommodation for %s persons.
			if ( $nb_people == 1 ) {
				$msg_part2 = $hb_strings['view_accom_for_one_person'];
			} else {
				$msg_part2 = $hb_strings['view_accom_for_persons'];
				$msg_part2 = str_replace( '%persons_nb', $nb_people, $msg_part2 );
			}

			$msg = $msg_part1;
			if ( $msg_part2 != '' && $exists_main_booking_form ) {
				$msg .= '<br/><a href="#" class="hb-other-search">' . $msg_part2 . '</a>';
			}
			return array(
				'success' => false,
				'msg' => $msg
			);
		}

		$available_accom = $this->hbdb->get_available_accom_per_people_dates( $nb_people, $str_check_in, $str_check_out );

		if ( ! $is_accom_page ) {
			foreach ( $available_accom as $key => $accom_id ) {
				if ( ! $this->accom_observes_rules( $accom_id, $str_check_in, $str_check_out, $nb_nights ) ) {
					unset( $available_accom[ $key ] );
				}
			}
		}

		if ( ( count( $available_accom ) == 0 ) && ! $is_accom_page ) {
			// Unfortunately we could not find any accommodation for the dates you entered.
			// Unfortunately we could not find any accommodation for the dates you entered. You might consider checking the availability page to enter search criteria that will match the rooms availability.
			$msg = $hb_strings['no_accom_at_chosen_dates'];
			return array(
				'success' => false,
				'msg' => $msg
			);
		}

		if ( $is_accom_page && ! in_array( $page_accom_id, $available_accom ) ) {
			if ( count( $available_accom ) == 0 || ! $exists_main_booking_form ) {
				// The %accom_name is not available at the chosen dates.
				$msg = $hb_strings['accom_not_available_at_chosen_dates'];
				$msg = str_replace( '%accom_name', $accom_name, $msg );
				return array(
					'success' => false,
					'msg' => $msg
				);
			} else {
				// The %accom_name is not available at the chosen dates.
				$msg_part1 = $hb_strings['accom_not_available_at_chosen_dates'];
				$msg_part1 = str_replace( '%accom_name', $accom_name, $msg_part1 );

				// View all available accommodation at the chosen dates.
				$msg_part2 = $hb_strings['view_accom_at_chosen_date'];

				$msg = $msg_part1;
				if ( $msg_part2 != '' ) {
					$msg .= '<br/><a href="#" class="hb-other-search">' . $msg_part2 . '</a>';
				}
				return array(
					'success' => false,
					'msg' => $msg
				);
			}
		}

		$output = '';

		$output .= '
				<div class="hb-booking-nb-nights">' . $this->utils->get_number_of_nights( $str_check_in, $str_check_out ) . '</div>';

		if ( $is_accom_page ) {
			$available_accom = array( $page_accom_id );
			$output .= '
				<div class="hb-accom-choice hb-accom-single-choice">';
		} else {
			if ( count( $available_accom ) > 1 ) {
				// We have found %s of accommodation that suit your needs.
				$msg1 = $hb_strings['several_types_of_accommodation_found'];
				$msg1 = str_replace( '%nb_types', count( $available_accom ), $msg1 );

				// Select your accommodation
				$msg2 = $hb_strings['select_accom_title'];

				$output .= '
				<div class="hb-search-result-title-section">
					<p>' . $msg1 . '</p>';
				if ( $msg2 != '' ) {
					$output .= '
					<h3 class="hb-title hb-title-select">' . $msg2 . '</h3>';
				}
				$output .= '
				</div><!-- end .hb-search-result-title-section -->
				<div class="hb-accom-choice hb-accom-multiple-choice">';
			} else {
				// We have found %s of accommodation that suit your needs.
				$msg = $hb_strings['one_type_of_accommodation_found'];
				$output .= '
				<div class="hb-search-result-title-section">
					<p>' . $msg . '</p>
				</div><!-- end .hb-search-result-title-section -->
				<div class="hb-accom-choice hb-accom-single-choice">';
			}
		}

		foreach ( $available_accom as $accom_id ) {
			$price_breakdown = '';
			$prices = $this->price_calc->get_price( $accom_id, $str_check_in, $str_check_out, $adults, $children, $price_breakdown );
			if ( ! $prices['success'] ) {
				return array(
					'success' => false,
					'msg' => $prices['error']
				);
			} else {
				$price = $prices['prices']['accom_total'];
			}

			$thumb_mark_up = '';
			if (
				( $force_display_thumb ) ||
				( ! $is_accom_page && ( get_option( 'hb_thumb_display' ) != 'no' ) )
			) {
				$thumb_width = intval( get_option( 'hb_search_accom_thumb_width', 100 ) );
				if ( ! $thumb_width ) {
					$thumb_width = 100;
				}
				$thumb_height = intval( get_option( 'hb_search_accom_thumb_height', 100 ) );
				if ( ! $thumb_height ) {
					$thumb_height = 100;
				}
				$thumb_mark_up = $this->utils->get_thumb_mark_up( $accom_id, $thumb_width, $thumb_height, 'hb-accom-img' );
				if (
					$thumb_mark_up &&
					( get_option( 'hb_thumb_accom_link' ) == 'yes' ) &&
					( $current_page_id != $page_accom_id )
				) {
					$thumb_mark_up = '<a target="_blank" href="' . $this->utils->get_accom_link( $accom_id ) . '">' . $thumb_mark_up . '</a>';
				}
			}

			$output .= '
				<div class="hb-accom hb-accom-id-' . $accom_id . '" data-accom-id="' . $accom_id . '">' .
					$thumb_mark_up;

			if ( ! $is_accom_page ) {
				$title = $this->utils->get_accom_title( $accom_id );
				if ( get_option( 'hb_title_accom_link' ) == 'yes' ) {
					$title = '<a target="_blank" href="' . $this->utils->get_accom_link( $accom_id ) . '">' . $title . '</a>';
				}
				$output .= '
					<div class="hb-accom-title">' . $title . '</div>';
			}
			if ( $is_accom_page ) {
				// The %accom_name is available at the chosen dates.
				$msg = $hb_strings['accom_available_at_chosen_dates'];
				$msg = str_replace( '%accom_name', $accom_name, $msg );
				$output .= '
					<div class="hb-accom-desc">' . $msg;
				if ( $force_display_desc ) {
					if ( $msg ) {
						$output .= '<br/>';
					}
					$output .= $this->utils->get_accom_search_desc( $accom_id );
				}
				$output .= '
					</div>';
			} else {
				$output .= '
					<div class="hb-accom-desc">' . $this->utils->get_accom_search_desc( $accom_id ) . '</div>';
			}

			// price for 1 night
			// price for %x nights
			if ( $nb_nights > 1 ) {
				$msg = $hb_strings['price_for_several_nights'];
				$msg = str_replace( '%nb_nights', $nb_nights, $msg );
			} else {
				$msg = $hb_strings['price_for_1_night'];
			}

			if ( get_option( 'hb_display_price' ) != 'no' ) {
				$output .= '
					<div class="hb-accom-price-total hb-clearfix">
						<input type="hidden" class="hb-accom-price-raw" value="' . $price . '" />
						<div class="hb-accom-price">' . $this->utils->price_with_symbol( $price ) . '</div>
						<div class="hb-accom-price-caption">' . $msg;
				if ( get_option( 'hb_display_price_breakdown' ) == 'yes' ) {
					// View price breakdown
					$msg1 = $hb_strings['view_price_breakdown'];
					// Hide price breakdown
					$msg2 = $hb_strings['hide_price_breakdown'];
					$output .= '
							<br/>
							<span class="hb-accom-price-caption-dash">&nbsp;-&nbsp;</span>
							<a class="hb-view-price-breakdown" href="#">
								<span class="hb-price-bd-show-text">' . $msg1 . '</span>
								<span class="hb-price-bd-hide-text">' . $msg2 . '</span>
							</a>
						</div>
					</div>
					<p class="hb-price-breakdown">' . $price_breakdown . '</p>';
				} else {
					$output .= '
							<p class="hb-hidden-price-breakdown">' . $price_breakdown . '</p>
						</div>
					</div>';
				}
			} else {
				$output .= '
					<br/>';
			}
			$output .= '
					<div class="hb-select-accom-wrapper hb-clearfix"><div class="new-custom-form"></div>';
			if ( $is_accom_page ) {
				// Book now!
				$msg = $hb_strings['accom_book_now_button'];
				$output .= '
						<p class="hb-select-accom"><input type="submit" value="' . $msg . '" /></p>
					</div>';
			} else {
				// Select this accommodation
				$msg1 = $hb_strings['select_accom_button'];
				// Book now!
				$msg2 = $hb_strings['accom_book_now_button'];
				// View this accommodation
				$msg3 = $hb_strings['view_accom_button'];
				$output .= '
						<p class="hb-select-accom hb-select-accom-multiple"><input type="submit" value="' . $msg1 . '" /></p>
						<p class="hb-select-accom hb-select-accom-single"><input type="submit" value="' . $msg2 . '" /></p>';
				if ( get_option( 'hb_button_accom_link' ) == 'yes' ) {
					$output .= '
						<p class="hb-view-accom"><input type="submit" data-accom-url="' . $this->utils->get_accom_link( $accom_id ) . '" value="' . $msg3 . '" /></p>';
				}
				// You have selected the %accom_name.
				$msg = $hb_strings['selected_accom'];
				$msg = $msg = str_replace( '%accom_name', $this->utils->get_accom_title( $accom_id ), $msg );
				$output .= '
					</div>
					<p class="hb-accom-selected-name">' . $msg . '</p>';
			}
			$output .= '
				</div><!-- end .hb-accom -->';
		}

		$output .= '
			</div><!-- end .hb-accom-choice -->';

		$output .= $this->get_available_accom_num( $available_accom, $str_check_in, $str_check_out, $is_admin );
		$output .= $this->options_form->get_options_form_markup_frontend( $adults, $children, $nb_nights );

		$output = apply_filters( 'hb_available_accommodation_markup', $output );

		return array(
			'success' => true,
			'mark_up' => $output
		);

	}

	private function accom_observes_rules( $accom_id, $str_check_in, $str_check_out, $nb_nights ) {
		$rules = $this->hbdb->get_accom_booking_rules( $accom_id );
		if ( $rules ) {
			$check_in_day = $this->utils->get_day_num( $str_check_in );
			$check_out_day = $this->utils->get_day_num( $str_check_out );
			$check_in_season = $this->hbdb->get_season( $str_check_in );
			$check_out_season = $this->hbdb->get_season( $str_check_out );
			foreach ( $rules as $rule ) {
				$allowed_check_in_days = explode( ',', $rule['check_in_days'] );
				$allowed_check_out_days = explode( ',', $rule['check_out_days'] );
				$rule_seasons = explode( ',', $rule['seasons'] );
				if (
					$rule['type'] == 'check_in_days' &&
					! in_array( $check_in_day, $allowed_check_in_days ) &&
					( $rule['all_seasons'] || in_array( $check_in_season, $rule_seasons ) )
				) {
					return false;
				} else if (
					$rule['type'] == 'check_out_days' &&
					! in_array( $check_out_day, $allowed_check_out_days ) &&
					( $rule['all_seasons'] || in_array( $check_out_season, $rule_seasons ) )
				) {
					return false;
				} else if (
					$rule['conditional_type'] != 'discount' &&
					$rule['conditional_type'] != 'special_rate' &&
					$rule['conditional_type'] != 'coupon' &&
					in_array( $check_in_day, $allowed_check_in_days ) &&
					( $rule['all_seasons'] || in_array( $check_in_season, $rule_seasons ) )
				) {
					if (
						! in_array( $check_out_day, $allowed_check_out_days ) &&
						( $rule['type'] == 'conditional' && ( $rule['conditional_type'] == 'compulsory' || $rule['conditional_type'] == 'comp_and_rate' ) )
					) {
						return false;
					} else if ( $nb_nights < $rule['minimum_stay'] ) {
						return false;
					} else if ( $nb_nights > $rule['maximum_stay'] ) {
						return false;
					}
				}
			}
		}
		return true;
	}

	private function get_available_accom_num( $accom, $check_in, $check_out, $is_admin ) {
		$output = '<form class="hb-select-accom-num-form">';

		if ( ( $is_admin != 'yes' ) && ( get_option( 'hb_select_accom_num' ) != 'yes' ) ) {
			$output .= '</form>';
			return $output;
		}

		if ( $is_admin == 'yes' ) {
			$output .= '<p class="hb-admin-add-resa-section-title">';
			$output .= esc_html__( 'Accommodation number:', 'hbook-admin' );
			$output .= '</p>';
			$radio_label_format = '%accom_name (%accom_num)';
		} else {
			$output .= '<h3 class="hb-title hb-title-select-accom-num">';
			$output .= $this->hbdb->get_string( 'select_accom_num_title' );
			$output .= '</h3>';
			$accom_num_text = $this->hbdb->get_string( 'select_accom_num_text' );
			if ( $accom_num_text ) {
				$output .= '<p>';
				$output .= $accom_num_text;
				$output .= '</p>';
			}
			$radio_label_format = $this->hbdb->get_string( 'select_accom_num_label' );
		}

		foreach ( $accom as $accom_id ) {
			$output .= '<div class="hb-select-accom-id-num hb-select-accom-' . $accom_id . '-num">';
			$avai_accom_num = array();
			$accom_num_name = $this->hbdb->get_accom_num_name( $accom_id );
			$accom_num = array_keys( $accom_num_name );
			$unavai_accom_num = $this->hbdb->get_unavailable_accom_num_per_date( $accom_id, $check_in, $check_out );
			$avai_accom_num = array_values( array_diff( $accom_num, $unavai_accom_num ) );
			if ( isset( $_POST['chosen_accom_num'] ) && $_POST['chosen_accom_num'] ) {
				$chosen_accom_num = $_POST['chosen_accom_num'];
			} else {
				$chosen_accom_num = $avai_accom_num[0];
			}
			foreach ( $avai_accom_num as $i => $num ) {
				$accom_num_id = 'hb-accom-' . $accom_id . '-' . $num;
				$output .= '<input type="radio" ';
				$output .= 'id="' . $accom_num_id . '" ';
				$output .= 'name="hb-accom-' . $accom_id . '-num" ';
				$output .= 'value="' . $num . '" ';
				if ( $num == $chosen_accom_num ) {
					$output .= 'checked ';
				}
				$output .= '/>';
				$radio_label_text = str_replace( '%accom_name', $this->utils->get_accom_title( $accom_id ), $radio_label_format );
				$radio_label_text = str_replace( '%accom_num', $accom_num_name[ $num ], $radio_label_text );
				$output .= '<label for="' . $accom_num_id . '"> ';
				$output .= $radio_label_text;
				$output .= '</label><br/>';
			}
			$output .= '</div>';
		}
		$output .= '</form>';
		return $output;
	}
}