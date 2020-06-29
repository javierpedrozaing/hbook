<?php
class HbOptionsForm {

	private $hbdb;
	private $utils;

	public function __construct( $hbdb, $utils ) {
		$this->hbdb = $hbdb;
		$this->utils = $utils;
	}

	public function get_options_form_markup_frontend( $adults, $children, $nb_nights ) {
		$chosen_options = array();
		if ( isset( $_POST['chosen_options'] ) ) {
			$chosen_options = json_decode( stripcslashes( $_POST['chosen_options'] ), true );
		}
		return $this->get_options_form_markup( $adults, $children, $nb_nights, false, $chosen_options );
	}

	public function get_options_form_markup_backend( $adults, $children, $nb_nights ) {
		return $this->get_options_form_markup( $adults, $children, $nb_nights, true, array() );
	}

	public function get_update_options_form_markup_backend( $adults, $children, $nb_nights, $chosen_options ) {
		return $this->get_options_form_markup( $adults, $children, $nb_nights, true, $chosen_options );
	}

	private function get_options_form_markup( $adults, $children, $nb_nights, $is_admin, $chosen_options ) {
		
		$output = '<form class="hb-options-form">';

		if ( $is_admin ) {
			$output .= '<p class="hb-admin-add-resa-section-title">';
			$output .= esc_html__( 'Extra services:', 'hbook-admin' );
			$output .= '</p>';
		} else {
			$output .= '<h3 class="hb-title hb-title-extra">';
			$output .= $this->hbdb->get_string( 'select_options_title' );
			$output .= '</h3>';
		}

		$output_options_quantity = '';
		$output_options_single = '';
		$output_options_multiple = '';
		$options = $this->hbdb->get_all_options_with_choices();
		$price_options = $this->utils->calculate_options_price( $adults, $children, $nb_nights, $options, true );
		// $options = array_reverse( $options );
		foreach ( $options as $option ) {
			$accom = explode( ',', $option['accom'] );
			$option_classes = '';
			foreach ( $accom as $accom_id ) {
				$option_classes .= ' hb-option-accom-' . $accom_id;
			}
			$option_classes .= ' hb-option';
			$option_markup_id = 'hb_option_' . $option['id'];
			if ( $option['apply_to_type'] == 'quantity' || $option['apply_to_type'] == 'quantity-per-day' ) {
				$option_max = -1;
				$option_max_markup = '';
				if ( $option['quantity_max_option'] == 'yes' ) {
					$option_max = $option['quantity_max'];
					$option_max_markup = 'max="' . $option_max . '" ';
				} else if ( $option['quantity_max_option'] == 'yes-per-person' ) {
					$option_max = $option['quantity_max'] * $adults + $option['quantity_max_child'] * $children;
					$option_max_markup = 'max="' . $option_max . '" ';
				}
				if ( isset( $chosen_options[ $option['id'] ] ) ) {
					$option_value = intval( $chosen_options[ $option['id'] ]['quantity'] );
				} else {
					$option_value = 0;
				}
				$output_options_quantity .= '
					<div class="hb-quantity-option' . $option_classes . '">
						<label for="' . $option_markup_id . '">' . $this->get_option_display_name( $option, $is_admin, $price_options[ 'option_' . $option['id'] ], false, $option_max ) . '</label><br/>
						<input type="number" min="0" ' . $option_max_markup . 'value="' . $option_value . '" data-price="' . $price_options[ 'option_' . $option['id'] ] . '" id="' . $option_markup_id . '" name="' . $option_markup_id . '" />
						<br/>
					</div>';
			} else if ( $option['choice_type'] == 'single' ) {
				$checked = '';
				if ( isset( $chosen_options[ $option['id'] ] ) ) {
					$checked = 'checked';
				}
				$output_options_single .= '
					<div class="hb-single-option' . $option_classes . '">
						<span class="hb-checkbox-wrapper">
							<input type="checkbox" data-price="' . $price_options[ 'option_' . $option['id'] ] . '" id="' . $option_markup_id . '" name="' . $option_markup_id . '" ' . $checked . '/>
							<label for="' . $option_markup_id . '">' . $this->get_option_display_name( $option, $is_admin, $price_options[ 'option_' . $option['id'] ] ) . '</label>
						</span>
					</div>';
			} else {
				$output_options_multiple .= '
					<div class="hb-multiple-option' . $option_classes . '">' . $this->get_option_display_name( $option, $is_admin ) . '<br/>';
				$choices = $option['choices'];
				// $choices = array_reverse( $choices );
				foreach ( $choices as $i => $choice ) {
					$option_choice_markup_id = 'hb_option_choice_' . $choice['id'];
					$checked = '';
					if ( isset( $chosen_options[ $option['id'] ] ) ) {
						if ( $chosen_options[ $option['id'] ]['chosen'] == $choice['id'] ) {
							$checked = 'checked';
						}
					} else if ( $i == 0 ) {
						$checked = 'checked';
					}
					$output_options_multiple .= '
						<span class="hb-radio-wrapper">
							<input type="radio" data-price="' . $price_options[ 'option_choice_' . $choice['id'] ] . '" id="' . $option_choice_markup_id . '" name="' . $option_markup_id . '" value="' . $choice['id'] . '" ' . $checked . '/>
							<label for="' . $option_choice_markup_id . '">' . $this->get_choice_option_display_name( $choice, $is_admin, $price_options[ 'option_choice_' . $choice['id'] ] ) . '</label>
						</span>
						<br/>';
				}
				$output_options_multiple .= '</div><br/>';
			}
		}

		$output .= $output_options_single;
		if ( $output_options_single != '' ) {
			$output .= '<br/>';
		}
		if ( $output_options_quantity ) {
			$output .= $output_options_quantity;
			$output .= '<br/>';
		}
		$output .= $output_options_multiple;

		if ( $is_admin || ( get_option( 'hb_display_price' ) != 'no' ) ) {
			$output .= '<p class="hb-options-total-price">';
			if ( $is_admin ) {
				$output .= esc_html__( 'Options total price:', 'hbook-admin' ) . ' ';
			} else {
				$output .= $this->hbdb->get_string( 'total_options_price' ) . ' ';
			}
			$output .= '<span class="hb-price-placeholder-minus">-</span>';
			$output .= $this->utils->price_placeholder();
			$output .= '</p>';
		}

		$output .= '<input class="hb-options-price-raw" type="hidden" value="0" />';
		$output .= '</form>';

		if ( ! $is_admin ) {
			$output = apply_filters( 'hb_extras_form_markup', $output );
		}

		return $output;
		
	}

	private function get_choice_option_display_name( $option, $is_admin, $price ) {
		return $this->get_option_display_name( $option, $is_admin, $price, true );
	}

	private function get_option_display_name( $option, $is_admin, $price = '', $is_choice = false, $max = -1 ) {
		if ( $is_choice ) {
			$option_id = 'option_choice_' . $option['id'];
		} else {
			$option_id = 'option_' . $option['id'];
		}
		$display_name = $this->hbdb->get_string( $option_id );
		if ( $display_name ) {
			$display_name = str_replace( '%price', '', $display_name ); // Backward compatibility (there was a %price var in each option name)
		} else {
			$display_name = $option['name'];
		}

		if ( ! $is_choice ) {
			$display_name = '<b>' . $display_name . '</b>';
		}
		if ( $price !== '' ) {
			if ( $is_admin || ( get_option( 'hb_display_price' ) != 'no' ) ) {
				if ( $price == 0 ) {
					$display_price = $this->hbdb->get_string( 'free_option' );
				} else {
					$display_price = str_replace( '%price', $this->utils->price_with_symbol( $price ), $this->hbdb->get_string( 'price_option' ) );
					if ( isset( $option['apply_to_type'] ) && ( $option['apply_to_type'] == 'quantity' || $option['apply_to_type'] == 'quantity-per-day' ) ) {
						$display_price = str_replace( '%each', $this->hbdb->get_string( 'each_option' ), $display_price );
					} else {
						$display_price = str_replace( '%each', '', $display_price );
					}
				}
			} else if ( $max != -1 ) {
				$display_price = str_replace( '%price', '', $this->hbdb->get_string( 'price_option' ) );
				$display_price = str_replace( '%each', '', $display_price );
			} else {
				$display_price = '';
			}
			if ( $max != -1 ) {
				$display_price = str_replace( '%max', $this->hbdb->get_string( 'max_option' ), $display_price );
				$display_price = str_replace( '%max_value', $max, $display_price );
			} else {
				$display_price = str_replace( '%max', '', $display_price );
			}
			$display_name = $display_name . ' ' . $display_price;
		}
		$display_name = apply_filters( 'hb_extra_name', $display_name, $option, $price, $max );
		return $display_name;
	}

}