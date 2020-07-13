<?php
class HbOptionsForm {

	private $hbdb;
	private $utils;	

	public function __construct( $hbdb, $utils ) {
		$this->hbdb = $hbdb;
		$this->utils = $utils;
	}
	

	public function create_form_customer($resa,  $booking_form_num = 0) {	 // fix second parameter, get dinamically número del alojamiento
		$adults = $resa[4]['value'];		
		$output = '';
		$adultsArray =  array_fill(0, $adults, 'cliente');
		$output .=  '<h2 class="note-reservation">' . "Por favor diligencia la información respectiva para cada adulto." . '</h2>';	
		foreach ($adultsArray as $key => $value) {
			$num = $key+1;			
			$output .=  '<h3 class="adulto">Adulto ' . $num . '</h3>';	
			if ($key <= 0) {
				$output .=  '<p><i class="note-reservation">' . "La información de la persona registrada aquí será la persona encargada del pago y con quien nos comunicaremos para establecer detalles de la reserva." . '</i></p>';	
			}			
			$output .= '<div "content-form form-'.$num.'">';
			$output .= '<form class="hb-booking-details-custom-form">' .
			$this->get_details_fields( $resa ) .							
			$this->get_hidden_fields( $booking_form_num );

			if ($key <= 0) {
				// show info only for first customer
				$output .=	$this->get_payment_fields();
				$output .= $this->get_policies_area();
			}
			if ($key <= 0) {
				// create resa for first register
				$output .= '<input type="hidden" name="action" value="hb_create_resa" />';
			} else {
				// create info additional customer
				$output .= '<input type="hidden" name="action" value="hb_create_additional_customers" />';			
			}
						
			$output .= '<button class="save-customer" data-customer="'.$num.'">Gurdar</button>' .
			'<p class="hb-booking-searching">Registrando información...</p>' .		
			'</form><!-- end .hb-booking-details-form -->' ;
			
			$output .= '<div>';
		}		

		return array(
			'success' => true,
			'data' => $adults,
			'mark_up' => $output
		);
	}

	public function get_details_fields( $resa = array() ) {
		$fields = $this->hbdb->get_details_form_fields();
		$output = '';
		$nb_columns = 0;
		$current_columns_wrapper = 0;
		$column_num = 0;
		foreach ( $fields as $field ) {
			$output = apply_filters( 'hb_details_form_markup_before_field', $output, $field );
			if ( $field['displayed'] == 'yes' ) {
				if ( $field['column_width'] == 'half' ) {
					$nb_columns = 2;
				} else if ( $field['column_width'] == 'third' ) {
					$nb_columns = 3;
				} else {
					$nb_columns = 0;
				}
				if ( $nb_columns ) {
					if ( $column_num && ( $current_columns_wrapper != $nb_columns ) ) {
						$column_num = 0;
						$current_columns_wrapper = 0;
						$output .= '</div><!-- end .hb-clearfix -->';
					}
					if ( ! $column_num ) {
						$column_num = 1;
						$current_columns_wrapper = $nb_columns;
						$output .= '<div class="hb-clearfix">';
					} else {
						$column_num++;
					}
				} else if ( $column_num != 0 ) {
					$column_num = 0;
					$nb_columns = 0;
					$current_columns_wrapper = 0;
					$output .= '</div><!-- end .hb-clearfix -->';
				}

				$output .= $this->get_field_mark_up( $field, $resa );

				if ( $current_columns_wrapper && ( $current_columns_wrapper == $column_num ) ) {
					$column_num = 0;
					$nb_columns = 0;
					$current_columns_wrapper = 0;
					$output .= '</div><!-- end .hb-clearfix -->';
				}
			}
			$output = apply_filters( 'hb_details_form_markup_after_field', $output, $field );
		}
		if ( $current_columns_wrapper ) {
			$output .= '</div><!-- end .hb-clearfix -->';
		}
		$output = '<div class="hb-details-fields">' . $output . '</div>';
		$output = apply_filters( 'hb_details_form_markup', $output );
		return $output;
	}


	private function get_field_display_name( $field ) {
		$display_name = '';
		if ( isset( $this->hbdb->get_strings()[ $field['id'] ] ) ) {
			$display_name = $this->hbdb->get_strings()[ $field['id'] ];
		}
		if ( $display_name != '' ) {
			return $display_name;
		} else {
			return $field['name'];
		}
	}

	private function get_field_attributes( $field ) {
		$data_validation = '';
		if ( $field['required'] == 'yes' ) {
			$data_validation = 'required';
		}
		if ( $field['type'] == 'email' ) {
			$data_validation .= ' email';
		}
		if ( $field['type'] == 'number' ) {
			$data_validation .= ' number';
		}
		return 'id="' . $field['id'] . '" name="hb_' . $field['id'] . '" class="hb-detail-field" data-validation="' . $data_validation . '"';
	}

	private function get_hidden_fields( $booking_form_num ) {
		$output = '
			<input type="hidden" class="hb-details-check-in" name="hb-details-check-in" />
			<input type="hidden" class="hb-details-check-out" name="hb-details-check-out" />
			<input type="hidden" class="hb-details-adults" name="hb-details-adults" />
			<input type="hidden" class="hb-details-children" name="hb-details-children" />
			<input type="hidden" class="hb-details-accom-id" name="hb-details-accom-id" />
			<input type="hidden" class="hb-details-is-admin" name="hb-details-is-admin" />
			<input type="hidden" name="hb-details-booking-form-num" value="' . $booking_form_num . '"/>';
		return $output;
	}

	private function get_payment_fields() {
		$output = '';
		$display_payment_title = false;
		$payment_type = '';
		$payment_type_text = '';
		$payment_type_explanation = '';
		$amount_types = array( 'full_amount', 'deposit_amount', 'full_minus_deposit_amount' );
		if ( get_option( 'hb_resa_payment_multiple_choice' ) == 'yes' ) {
			$display_payment_title = true;
			$payment_choice_text = '<p class="hb-payment-type-multiple-choice">';
			$payment_choice_text .= '<b>' . $this->hbdb->get_strings()['payment_type'] . '</b><br/>';
			$payment_types = apply_filters( 'hb_payment_types', array( 'offline', 'store_credit_card', 'deposit', 'full' ) );
			foreach ( $payment_types as $payment_type ) {
				if ( get_option( 'hb_resa_payment_' . $payment_type ) == 'yes' ) {
					$payment_choice_text .= '<input type="radio" id="hb-payment-type-' . $payment_type . '" name="hb-payment-type" value="' . $payment_type . '" />';
					$payment_choice_text .= ' <label for="hb-payment-type-' . $payment_type . '">' . $this->hbdb->get_strings()[ 'payment_type_' . $payment_type ] . '</label><br/>';
					$explanation = '';
					if ( isset( $this->hbdb->get_strings()[ 'payment_type_explanation_' . $payment_type ] ) && $this->hbdb->get_strings()[ 'payment_type_explanation_' . $payment_type ] ) {
						$explanation = $this->hbdb->get_strings()[ 'payment_type_explanation_' . $payment_type ];
						foreach ( $amount_types as $amount_type ) {
							$price_placeholder = '<span class="hb-payment-type-explanation-' . $amount_type . '">' . $this->utils->price_placeholder() . '</span>';
							$explanation = str_replace( '%' . $amount_type, $price_placeholder, $explanation );
						}
						$explanation = '<p class="hb-payment-type-explanation hb-payment-type-explanation-' . $payment_type . '">' . $explanation . '</p>';
					}
					$payment_type_explanation .= $explanation;
				}
			}
			$payment_choice_text .= '<input class="hb-payment-type-null-price" type="radio" name="hb-payment-type" value="offline" />';
			$payment_choice_text .= '</p>';
		} else {
			$payment_type = get_option( 'hb_resa_payment' );
			$payment_choice_text = '<input class="hb-payment-type-hidden" type="radio" name="hb-payment-type" value="' . $payment_type . '" />';
			if ( $payment_type != 'offline' ) {
				$payment_choice_text .= '<input class="hb-payment-type-null-price" type="radio" name="hb-payment-type" value="offline" />';
			}
			if ( $payment_type == 'deposit' || $payment_type == 'full' ) {
				$display_payment_title = true;
			}
			if ( isset( $this->hbdb->get_strings()['payment_type_explanation_' . $payment_type ] ) && $this->hbdb->get_strings()['payment_type_explanation_' . $payment_type ] ) {
				$explanation = $this->hbdb->get_strings()[ 'payment_type_explanation_' . $payment_type ];
				foreach ( $amount_types as $amount_type ) {
					$price_placeholder = '<span class="hb-payment-type-explanation-' . $amount_type . '">' . $this->utils->price_placeholder() . '</span>';
					$explanation = str_replace( '%' . $amount_type, $price_placeholder, $explanation );
				}
				$payment_type_explanation = '<p class="hb-payment-type-explanation hb-payment-type-explanation-' . $payment_type . '">' . $explanation . '</p>';
			}
		}
		$output .= $payment_choice_text;
		$output .= $payment_type_explanation;

		$output .= '<div class="hb-payment-method-wrapper">';

		$payment_gateways = $this->utils->get_active_payment_gateways();
		$payment_gateways_text = esc_html__( 'There is no active payment gateways. Please activate at least one payment gateway in HBook settings (Hbook > Payment).', 'hbook-admin' );
		if ( count( $payment_gateways ) == 1 ) {
			$payment_gateways_text = '<input class="hb-payment-method-hidden" type="radio" name="hb-payment-gateway" value="' . $payment_gateways[0]->id . '" data-has-redirection="' . $payment_gateways[0]->has_redirection . '" />';
		} else if ( count( $payment_gateways ) > 1 ) {
			$payment_gateways_text = '<p class="hb-payment-method"><b>' . $this->hbdb->get_strings()['payment_method'] . '</b><br/>';
			foreach ( $payment_gateways as $gateway ) {
				$payment_gateways_text .= '<input type="radio" id="hb-payment-gateway-' . $gateway->id . '" name="hb-payment-gateway" value="' . $gateway->id . '" data-has-redirection="' . $gateway->has_redirection . '" />';
				$payment_gateways_text .= ' <label class="hb-payment-gateway-label-' . $gateway->id . '" for="hb-payment-gateway-' . $gateway->id . '">' . $gateway->get_payment_method_label() . '</label><br/>';
			}
			$payment_gateways_text .= '</p>';
		}
		$output .= $payment_gateways_text;

		$payment_forms = '';
		$bottom_areas = '';
		foreach ( $payment_gateways as $gateway ) {
			if ( $gateway->payment_form() ) {
				$payment_forms .= '<div class="hb-payment-form hb-payment-form-' . $gateway->id . '">' . $gateway->payment_form() . '</div>';
			}
			if ( $gateway->bottom_area() ) {
				$bottom_areas .= '<div class="hb-bottom-area-content-' . $gateway->id . '">' . $gateway->bottom_area() . '</div>';
			}
		}
		$output .= $payment_forms;
		$output .= '<div class="hb-bottom-area-content">' . $bottom_areas . '</div>';

		$output .= '</div>';

		$output .= '<input type="hidden" name="hb-payment-flag" class="hb-payment-flag" />';

		if ( $display_payment_title ) {
			$payment_section_title = $this->hbdb->get_strings()['payment_section_title'];
			if ( $payment_section_title ) {
				$output = '<h3 class="hb-title hb-title-payment">' . $payment_section_title . '</h3>' . $output;
			}
		}

		$output = '<div class="hb-payment-info-wrapper">' . $output . '</div>';
		return $output;
	}

	private function get_policies_area() {
		$policies = '';
		if ( get_option( 'hb_display_terms_and_cond' ) == 'yes' ) {
			$policies .=
				'<p>' .
					'<input type="checkbox" id="terms-and-cond" name="hb_terms_and_cond" />' .
					'<label for="terms-and-cond" class="hb-terms-and-cond"> ' . $this->hbdb->get_strings()['terms_and_cond_text'] . '</label>' .
				'</p>';
		}
		if ( get_option( 'hb_display_privacy_policy' ) == 'yes' ) {
			$policies .=
				'<p>' .
					'<input type="checkbox" id="privacy-policy" name="hb_privacy_policy" />' .
					'<label for="privacy-policy" class="hb-privacy-policy"> ' . $this->hbdb->get_strings()['privacy_policy_text'] . '</label>' .
				'</p>';
		}
		if ( $policies && ( $this->hbdb->get_strings()['terms_and_cond_title'] ) ) {
			$policies =
				'<h3 class="hb-title hb-title-terms">' . $this->hbdb->get_strings()['terms_and_cond_title'] . '</h3>' .
				$policies;
		}
		$output = '<div class="hb-policies-area">';
		$output .= $policies;
		$output .= '<p class="hb-policies-error"></p>';
		$output .= '</div>';
		return apply_filters( 'hb_policies_area_markup', $output );
	}


	public function get_field_mark_up($field, $form_data = array(), $show_required = true, $display_column = true){
		if ( $field['type'] == 'column_break' ) {
			return '';
		}
		$output = '';
		$field_display_name = $this->get_field_display_name( $field );
		if ( $display_column && $field['column_width'] ) {
			$output .= '<div class="hb-column-' . $field['column_width'] . '">';
		}
		if ( ( $field['type'] == 'title' ) || ( $field['type'] == 'sub_title' ) || ( $field['type'] == 'explanation' ) || ( $field['type'] == 'separator' ) ) {
			if ( $field['type'] == 'title' ) {
				$output .= '<h3 class="hb-title">' . $field_display_name . '</h3>';
			} else if ( $field['type'] == 'sub_title' ) {
				$output .= '<h4>' . $field_display_name . '</h4>';
			} else if ( $field['type'] == 'explanation' ) {
				$output .= '<p class="hb-explanation">' . $field_display_name . '</p>';
			} else if ( $field['type'] == 'separator' ) {
				$output .= '<hr/>';
			}
			if ( $display_column && $field['column_width'] ) {
				$output .= '</div><!-- end .hb-column-' . $field['column_width'] . ' -->';
			}
			$output = apply_filters( 'hb_details_form_markup_field', $output, $field );
			return $output;
		}
		$required_text = '';
		if ( $show_required && $field['required'] == 'yes' ) {
			$required_text = '*';
		}
		$output .= '<p>';
		$output .= '<label for="' . $field['id'] . '">' . $field_display_name . $required_text . '</label>';
		$field_attributes = $this->get_field_attributes( $field );
		if ( $field['type'] == 'text' || $field['type'] == 'email' || $field['type'] == 'number' ) {
			$field_value = '';
			if ( isset( $form_data[ $field['id'] ] ) ) {
				$field_value = esc_attr( $form_data[ $field['id'] ] );
			}
			$output .= '<input ' . $field_attributes . ' type="text"  value="' . $field_value . '" />';
		} else if ( $field['type'] == 'textarea' ) {
			$field_value = '';
			if ( isset( $form_data[ $field['id'] ] ) ) {
				$field_value = esc_textarea( $form_data[ $field['id'] ] );
			}
			$output .= '<textarea ' . $field_attributes . '>';
			$output .= $field_value;
			$output .= '</textarea>';
		} else if ( $field['type'] == 'select' || $field['type'] == 'radio' || $field['type'] == 'checkbox' ) {
			$choices_mark_up = '';
			if ( ( $field['type'] == 'radio' ) || ( $field['type'] == 'checkbox' ) ) {
				if ( isset( $form_data[ $field['id'] ] ) && $form_data[ $field['id'] ] != '' ) {
					$checked_choices = array_map( 'trim' , explode( ',', $form_data[ $field['id'] ] ) );
				} else {
					$checked_choices = array();
				}
			}
			foreach ( $field['choices'] as $i => $choice ) {
				$choice_display_name = $this->get_field_display_name( $choice );
				if ( $field['type'] == 'select' ) {
					$choices_mark_up .= '<option value="' . $choice['name'] . '"';
					if ( isset( $form_data[ $field['id'] ] ) && $form_data[ $field['id'] ] == $choice['name'] ) {
						$choices_mark_up .= ' selected';
					}
					$choices_mark_up .= '>' . $choice_display_name . '</option>';
				} else if ( ( $field['type'] == 'radio' ) || ( $field['type'] == 'checkbox' ) ) {
					$choices_mark_up .= '<span class="hb-' . $field['type'] . '-wrapper">';
					$choices_mark_up .= '<input type="' . $field['type'] . '"';
					$field_name = 'hb_' . $field['id'];
					if ( $field['type'] == 'checkbox' ) {
						$field_name .= '[]';
						if ( $field['required'] == 'yes' ) {
							$choices_mark_up .= ' data-validation="checkbox_group" data-validation-qty="min1"';
						}
					}
					if ( in_array( $choice['name'], $checked_choices ) ) {
						$choices_mark_up .= ' checked';
					} else if ( $field['type'] == 'radio' && $i == 0 && count( $checked_choices ) == 0 ) {
						$choices_mark_up .= ' checked';
					}
					$choices_mark_up .= ' id="' . $field['id'] . '-' . $choice['id'] . '" name="' . $field_name . '" value="' . $choice['name'] . '">';
					$choices_mark_up .= '<label for="' . $field['id'] . '-' . $choice['id'] . '" class="hb-label-choice"> ' . $choice_display_name . '</label>';
					$choices_mark_up .= '</span>';
					$choices_mark_up .= '<br/>';
				}
			}
			if ( $field['type'] == 'select' ) {
				$output .= '<select ' . $field_attributes . '>';
				$output .= $choices_mark_up;
				$output .= '</select>';
			}
			if ( $field['type'] == 'radio' || $field['type'] == 'checkbox' ) {
				$output .= $choices_mark_up;
			}
		}
		$output .= '</p>';
		if ( $display_column && $field['column_width'] ) {
			$output .= '</div><!-- end .hb-column-' . $field['column_width'] . ' -->';
		}
		$output = apply_filters( 'hb_details_form_markup_field', $output, $field );
		return $output;
		
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