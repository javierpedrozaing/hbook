<?php
class HbFrontEndAjaxActions {

	private $hbdb;
	private $utils;

	public function __construct( $hbdb, $utils ) {
		$this->hbdb = $hbdb;
		$this->utils = $utils;
	}

	public function hb_get_available_accom() {
		require_once $this->utils->plugin_directory . '/front-end/booking-form/available-accom.php';
		require_once $this->utils->plugin_directory . '/utils/resa-options.php';
		require_once $this->utils->plugin_directory . '/utils/price-calc.php';
		$price_calc = new HbPriceCalc( $this->hbdb, $this->utils );
		$options_form = new HbOptionsForm( $this->hbdb, $this->utils );
		$strings = $this->hbdb->get_strings();
		$available_accom = new HbAvailableAccom( $this->hbdb, $this->utils, $strings, $price_calc, $options_form );

		$search_request = array(
			'check_in' => $_POST['check_in'],
			'check_out' => $_POST['check_out'],
			'adults' => $_POST['adults'],
			'children' => $_POST['children'],
			'page_accom_id' => $_POST['page_accom_id'],
			'current_page_id' => $_POST['current_page_id'],
			'exists_main_booking_form' => $_POST['exists_main_booking_form'],
			'force_display_thumb' => $_POST['force_display_thumb'],
			'force_display_desc' => $_POST['force_display_desc'],
			'is_admin' => $_POST['is_admin'],
			'admin_accom_id' => $_POST['admin_accom_id'],
		);
		$response = $available_accom->get_available_accom( $search_request );
		echo( json_encode( $response ) );
		die;
	}

	public function hb_get_details_form() {	
		require_once $this->utils->plugin_directory . '/front-end/booking-form/available-accom.php';
		require_once $this->utils->plugin_directory . '/utils/resa-options.php';
		require_once $this->utils->plugin_directory . '/utils/price-calc.php';	
		$options_form = new HbOptionsForm( $this->hbdb, $this->utils );
		$form_resa = $_POST['form'];
		
		$dataform = $options_form->create_form_customer($form_resa);
		echo json_encode($dataform);
		die;
	}


	public function hb_create_resa() { // function for save forms data called from ajax
		$response = array();
		
		$is_admin = false;
		if ( $_POST['hb-details-is-admin'] == 'yes' ) {
			$is_admin = true;
		}
		$accom_id = intval( $_POST['hb-details-accom-id'] );
		$check_in = $_POST['hb-details-check-in'];
		$check_out = $_POST['hb-details-check-out'];
		$adults = intval( $_POST['hb-details-adults'] );
		$children = intval( $_POST['hb-details-children'] );

		$resa_info = array(
			'accom_id' => $accom_id,
			'check_in' => $check_in,
			'check_out' => $check_out,
			'adults' => $adults,
			'children' => $children,
		);

		if ( $is_admin || ( get_option( 'hb_select_accom_num' ) == 'yes' ) ) {
			$accom_num = $_POST['hb-accom-' . $accom_id . '-num'];
			if ( ! $this->hbdb->is_available_accom_num( $accom_id, $accom_num, $check_in, $check_out ) ) {
				$response['success'] = false;
				$accom_num_name = $this->hbdb->get_accom_num_name( $accom_id );
				if ( $is_admin ) {
					$response['error_msg'] = sprintf(
						esc_html__( 'The %s (%s) is no longer available.', 'hbook-admin' ),
						get_the_title( $accom_id ),
						$accom_num_name[ $accom_num ]
					);
				} else {
					$error_msg = $this->hbdb->get_string( 'accom_num_no_longer_available' );
					$error_msg = str_replace( '%accom_name', $this->utils->get_accom_title( $accom_id ), $error_msg );
					$error_msg = str_replace( '%accom_num', $accom_num_name[ $accom_num ], $error_msg );
					$response['error_msg'] = $error_msg;
				}
				echo( json_encode( $response ) );
				die;
			}
		} else {
			$accom_num = $this->hbdb->get_first_available_accom_num( $accom_id, $check_in, $check_out );
			if ( ! $accom_num ) {
				$response['success'] = false;
				$response['error_msg'] = $this->hbdb->get_string( 'accom_no_longer_available' );
				echo( json_encode( $response ) );
				die;
			}
		}

		$customer_id = 0;
		if ( $is_admin && ( $_POST['hb-admin-customer-type'] == 'id' ) ) {
			$customer_id = intval( $_POST['hb-customer-id'] );
		}
		if ( ! $customer_id ) {
			$customer_info = $this->utils->get_posted_customer_info(); // get info customer
			$customer_email = '';
			if ( isset( $_POST['hb_email'] ) ) {
				$customer_email = stripslashes( strip_tags( $_POST['hb_email'] ) );
			}

			$customer_id = $this->hbdb->get_customer_id( $customer_email );
			if ( $customer_id ) {
				$customer_id = $this->hbdb->update_customer_on_resa_creation( $customer_id, $customer_email, $customer_info );
			} else {
				$customer_id = $this->hbdb->create_customer( $customer_email, $customer_info ); //register data customer
			}
		}
		if ( ! $customer_id ) {
			$response['success'] = false;
			if ( $is_admin ) {
				$response['error_msg'] = $this->hbdb->last_query();
			} else {
				$response['error_msg'] = esc_html__( 'Error. Could not create customer.', 'hbook-admin' );
			}
			echo( json_encode( $response ) );
			die;
		}

		$customer_info['id'] = $customer_id;

		require_once $this->utils->plugin_directory . '/utils/price-calc.php';
		$price_calc = new HbPriceCalc( $this->hbdb, $this->utils );
		$prices = $price_calc->get_price( $accom_id, $check_in, $check_out, $adults, $children );		
		if ( ! $prices['success'] ) {
			$response['success'] = false;
			$response['error_msg'] = esc_html__( 'Error. Could not calculate price.', 'hbook-admin' );
			echo( json_encode( $response ) );
			die;
		} else {
			$prices = $prices['prices'];
		}

		$options = $this->hbdb->get_options_with_choices( $accom_id );
		$options_choices = $this->hbdb->get_all( 'options_choices' );
		$choice_name = array();
		foreach ( $options_choices as $choice ) {
			$choice_name[ $choice['id'] ] = $choice['name'];
		}
		$nb_nights = $this->utils->get_number_of_nights( $check_in, $check_out );
		$price_options = $this->utils->calculate_options_price( $adults, $children, $nb_nights, $options, false );
		$options_total_price = 0;
		$chosen_options = array();
		$extras_fees_rate = 1;
		$extras_fees_percentages = $this->hbdb->get_extras_fees_percentages();
		foreach ( $extras_fees_percentages as $extras_fee_percentage ) {
			$extras_fees_rate += $extras_fee_percentage / 100;
		}
		foreach ( $options as $option ) {
			$option_price = 0;
			$chosen_option = array(
				'name' => $option['name'],
				'amount' => $option['amount'],
				'amount_children' => $option['amount_children'],
				'apply_to_type' => $option['apply_to_type'],
			);
			if ( $option['apply_to_type'] == 'quantity' || $option['apply_to_type'] == 'quantity-per-day' ) {
				$quantity = intval( $_POST[ 'hb_option_' . $option['id'] ] );
				if ( $quantity ) {
					$option_price = $price_options[ 'option_' . $option['id'] ];
					$chosen_option['quantity'] = $quantity;
					$chosen_option['amount'] = $option['amount'];
					$options_total_price += $this->utils->round_price( $quantity * $option_price * $extras_fees_rate );
					$chosen_options[ $option['id'] ] = $chosen_option;
				}
			} else if ( $option['choice_type'] == 'single' ) {
				if ( isset( $_POST[ 'hb_option_' . $option['id'] ] ) ) {
					$option_price = $price_options[ 'option_' . $option['id'] ];
					$chosen_option['amount'] = $option['amount'];
					$chosen_option['amount_children'] = $option['amount_children'];
					$options_total_price += $this->utils->round_price( $option_price * $extras_fees_rate );
					$chosen_options[ $option['id'] ] = $chosen_option;
				}
			} else {
				foreach ( $option['choices'] as $choice ) {
					if ( $_POST[ 'hb_option_' . $option['id'] ] == $choice['id'] ) {
						$option_price = $price_options[ 'option_choice_' . $choice['id'] ];
						$chosen_option['chosen'] = $choice['id'];
						$chosen_option['choice_name'] = $choice_name[ $choice['id'] ];
						$chosen_option['amount'] = $choice['amount'];
						$chosen_option['amount_children'] = $choice['amount_children'];
						$options_total_price += $this->utils->round_price( $option_price * $extras_fees_rate );
					}
				}
				$chosen_options[ $option['id'] ] = $chosen_option;
			}
		}
		$chosen_options = json_encode( $chosen_options );
		$price = $options_total_price + $prices['accom_total'];

		$coupon_id = '';
		$coupon_value = 0;
		$coupon_code = '';
		if ( isset( $_POST['hb-pre-validated-coupon-id'] ) ) {
			$coupon_id = $_POST['hb-pre-validated-coupon-id'];
		}
		if ( $coupon_id ) {
			require_once $this->utils->plugin_directory . '/utils/resa-coupon.php';
			$coupon_info = $this->hbdb->get_coupon_info( $coupon_id );
			$coupon = new HbResaCoupon( $this->hbdb, $this->utils, $coupon_info );
			if ( ( $coupon->is_valid( $accom_id, $check_in, $check_out ) ) && ( $coupon->is_still_valid() ) ) {
				if ( $coupon_info['amount_type'] == 'percent' ) {
					$coupon_value = $this->utils->round_price( $price * $coupon_info['amount'] / 100 );
				} else {
					$coupon_value = $coupon_info['amount'];
				}
				$coupon_code = $coupon_info['code'];
				$this->hbdb->increment_coupon_use( $coupon_id );
			}
		}

		$total_discount_amount = 0;
		$global_discount = array();
		if ( $is_admin && is_admin() ) {
			$discount_amount = round( floatval( $_POST['hb-global-discount-amount'] ), 2 );
			if ( $discount_amount > 0 ) {
				if ( $_POST['hb-global-discount-amount-type'] == 'fixed' ) {
					$total_discount_amount = $discount_amount;
					$global_discount = array(
						'amount_type' => 'fixed',
						'amount' => '' . $total_discount_amount,
					);
				} else if ( $_POST['hb-global-discount-amount-type'] == 'percent' ) {
					$total_discount_amount = $this->utils->round_price( $discount_amount * $price / 100 );
					$global_discount = array(
						'amount_type' => 'percent',
						'amount' => '' . $discount_amount,
					);
				}
			}
		} else {
			$discounts = $this->utils->get_global_discount( $accom_id, $check_in, $check_out, $price );
			$global_discount = $discounts['discount_breakdown'];
			$total_discount_amount = $discounts['discount_amount'];
		}
		$discount = array(
			'accom' => $prices['discount'],
			'global' => $global_discount,
		);
		$discount_json = json_encode( $discount );

		$price -= $coupon_value;
		$price -= $total_discount_amount;

		$fees = $this->hbdb->get_accom_fees( $accom_id );
		$fees_value = 0;
		$prices['extras'] = $options_total_price;
		$prices['total'] = $price;
		$resa_fees = array();
		foreach ( $fees as $fee ) {
			if ( $fee['include_in_price'] == 0 ) {
				$fee_values = $this->utils->calculate_fees_extras_values( $resa_info, $prices, $fee );
				$price += $fee_values['price'];
			}
			unset( $fee['all_accom'] );
			unset( $fee['global'] );
			unset( $fee['fee_id'] );
			unset( $fee['accom_id'] );
			$resa_fees[] = $fee;
		}
		$resa_fees = json_encode( $resa_fees );

		$deposit = 0;
		if ( get_option( 'hb_deposit_type' ) == 'nb_night' ) {
			$deposit = ( $price / $nb_nights ) * get_option( 'hb_deposit_amount' );
		} else if ( get_option( 'hb_deposit_type' ) == 'fixed' ) {
			$deposit = get_option( 'hb_deposit_amount' );
		} else if ( get_option( 'hb_deposit_type' ) == 'percentage' ) {
			$deposit = $price * get_option( 'hb_deposit_amount' ) / 100;
		}
		if ( $deposit > $price ) {
			$deposit = $price;
		}

		$security_bond = 0;
		$security_bond_deposit = 0;
		if ( get_option( 'hb_security_bond_online_payment' ) == 'yes' ) {
			$security_bond = get_option( 'hb_security_bond_amount' );
			if ( get_option( 'hb_deposit_bond' ) == 'yes' ) {
				$security_bond_deposit = get_option( 'hb_security_bond_amount' );
			}
		}

		$currency_to_round = array( 'HUF', 'JPY', 'TWD' );
		if ( in_array( get_option( 'hb_currency' ), $currency_to_round ) || ( get_option( 'hb_price_precision' ) == 'no_decimals' ) ) {
			$price = round( $price );
			$deposit = round( $deposit );
		} else {
			$price = round( $price, 2 );
			$deposit = round( $deposit, 2 );
		}

		$booking_form_num = 0;
		$amount_to_pay = 0;
		$payment_type = '';
		$gateway_custom_info = '';
		if ( ! $is_admin ) {
			$booking_form_num = $_POST['hb-details-booking-form-num'];

			if ( $_POST['hb-payment-type'] == 'store_credit_card' && ( get_option( 'hb_resa_payment_store_credit_card' ) == 'yes' || get_option( 'hb_resa_payment' ) == 'store_credit_card' ) ) {
				$payment_type = 'store_credit_card';
			} else if ( $_POST['hb-payment-type'] == 'deposit' && ( get_option( 'hb_resa_payment_deposit' ) == 'yes' || get_option( 'hb_resa_payment' ) == 'deposit' ) ) {
				$amount_to_pay = $deposit + $security_bond_deposit;
				$payment_type = 'deposit';
			} else if ( $_POST['hb-payment-type'] == 'full' && ( get_option( 'hb_resa_payment_full' ) == 'yes' || get_option( 'hb_resa_payment' ) == 'full' ) ) {
				$amount_to_pay = $price + $security_bond;
				$payment_type = 'full';
			} else {
				$amount_to_pay = $price + $security_bond;
				$payment_type = 'offline';
			}

			if ( isset( $_POST['hb-gateway-custom-info'] ) ) {
				$gateway_custom_info = $_POST['hb-gateway-custom-info'];
			}
		}

		$resa_info['booking_form_num'] = $booking_form_num;
		$resa_info['accom_price'] = $prices['accom'];
		$resa_info['discount'] = $discount_json;
		$resa_info['price'] = $price;
		$resa_info['deposit'] = $deposit;
		$resa_info['payment_type'] = $payment_type;
		$resa_info['paid'] = 0;
		$resa_info['currency'] = get_option( 'hb_currency' );
		$resa_info['customer_id'] = $customer_id; // pass customer_id for save in table hbook.wp_hb_resa;
		$resa_info['additional_info'] = $this->utils->get_posted_additional_booking_info();
		$resa_info['options'] = $chosen_options;
		$resa_info['fees'] = $resa_fees;
		$resa_info['coupon'] = $coupon_code;
		$resa_info['coupon_value'] = $coupon_value;
		$resa_info['payment_token'] = '';
		$resa_info['origin'] = 'website';
		$resa_info['gateway_custom_info'] = $gateway_custom_info;
		$resa_info['payment_gateway'] = '';
		
		$status = '';
		$admin_comment = '';
		$lang = get_locale();
		if ( $is_admin ) {
			$status = get_option( 'hb_resa_admin_status' );
			$admin_comment = $_POST['hb-admin-comment'];
			$lang = $_POST['hb-resa-admin-lang'];
		} else {
			if ( $_POST['hb-payment-flag'] == 'yes' ) {
				$payment_gateway = $this->utils->get_payment_gateway( $_POST['hb-payment-gateway'] );
				if ( $payment_gateway ) {
					$resa_info['payment_gateway'] = $payment_gateway->name;
					$response = $payment_gateway->process_payment( $resa_info, $customer_info, $amount_to_pay );
				} else {
					$response['success'] = false;
					$response['error_msg'] = esc_html__( 'Error. Could not find payment gateway.', 'hbook-admin' );
				}
				if ( ! $response['success'] ) {
					echo( json_encode( $response ) );
					die;
				}
				if ( isset( $response['payment_info'] ) ) {
					$resa_info['payment_info'] = $response['payment_info'];
				}
				if ( $payment_gateway->has_redirection == 'no' ) {
					if ( get_option( 'hb_resa_paid_has_confirmation' ) == 'no' ) {
						$status = get_option( 'hb_resa_website_status' );
					} else {
						$status = 'pending';
						if ( get_option( 'hb_select_accom_num' ) != 'yes' ) {
							$accom_num = 0;
						}
					}
					$resa_info['paid'] = $amount_to_pay;
				} else {
					$status = 'waiting_payment';
					$resa_info['payment_token'] = $response['payment_token'];
					$resa_info['amount_to_pay'] = $amount_to_pay;
				}
			} else {
				$resa_info['payment_gateway'] = '';
				if ( get_option( 'hb_resa_unpaid_has_confirmation' ) == 'no' ) {
					$status = get_option( 'hb_resa_website_status' );
				} else {
					$status = 'pending';
					if ( get_option( 'hb_select_accom_num' ) != 'yes' ) {
						$accom_num = 0;
					}
				}
			}
		}

		$resa_info['accom_num'] = $accom_num;
		$resa_info['status'] = $status;
		$resa_info['admin_comment'] = $admin_comment;
		$resa_info['lang'] = $lang;
		unset( $resa_info['gateway_custom_info'] );

		$resa_id = $this->hbdb->create_resa( $resa_info ); // call to funtion in database-actions.php

		
		if ( ! $resa_id && ! $resa_info['paid'] ) {
			$response['success'] = false;
			$response['error_msg'] = esc_html__( 'Error. Could not create reservation.', 'hbook-admin' );
			echo( json_encode( $response ) );
			die;
		} else {
			if ( $is_admin ) {
				$customer = $this->hbdb->get_single( 'customers', $customer_id );
				$response = array(
					'resa_id' => $resa_id,
					'price' => $resa_info['price'],
					'accom_discount_amount' => '',
					'accom_discount_amount_type' => '',
					'global_discount_amount' => '',
					'global_discount_amount_type' => '',
					'customer' => array(
						'id' => $customer['id'],
						'info' => $customer['info'],
					),
					'options_info' => $this->utils->resa_options_markup_admin( $resa_info['options'] ),
					'non_editable_info' => $this->utils->resa_non_editable_info_markup( $resa_info ),
					'received_on' => $this->utils->get_blog_datetime( current_time( 'mysql', 1 ) ),
					'additional_info' => json_encode( $resa_info['additional_info'] ),
					'automatic_blocked_accom' => $this->hbdb->automatic_block_accom( $resa_info['accom_id'], $resa_info['accom_num'], $resa_info['check_in'], $resa_info['check_out'], $resa_id ),
				);
				if ( $discount['accom'] ) {
					$response['accom_discount_amount'] = $discount['accom']['amount'];
					$response['accom_discount_amount_type'] = $discount['accom']['amount_type'];
				}
				if ( $discount['global'] ) {
					$response['global_discount_amount'] = $discount['global']['amount'];
					$response['global_discount_amount_type'] = $discount['global']['amount_type'];
				}
				$this->utils->send_email( 'new_resa_admin', $resa_id );
			} else {
				if ( $status == 'waiting_payment' ) {
					$response['resa_id'] = $resa_id;
				} else {
					if ( ( $status == 'new' ) || ( $status == 'confirmed' ) ) {
						$this->hbdb->automatic_block_accom( $resa_info['accom_id'], $resa_info['accom_num'], $resa_info['check_in'], $resa_info['check_out'], $resa_id );
					}
					$this->utils->send_email( 'new_resa', $resa_id );
				}
			}
		}


		$response['success'] = true;
		$response['resa_id'] = $resa_id;
		echo( json_encode( $response ) );
		die;
	}

	public function hb_verify_coupon() {
		$response = array();
		$response['success'] = false;
		$response['msg'] = $this->hbdb->get_string( 'invalid_coupon' );
		$coupon_ids = $this->hbdb->get_coupon_ids_by_code( $_POST['coupon_code'] );
		if ( $coupon_ids ) {
			require_once $this->utils->plugin_directory . '/utils/resa-coupon.php';
			foreach ( $coupon_ids as $coupon_id ) {
				$coupon_info = $this->hbdb->get_coupon_info( $coupon_id );
				$coupon = new HbResaCoupon( $this->hbdb, $this->utils, $coupon_info );
				if ( $coupon->is_valid( $_POST['accom_id'], $_POST['check_in'], $_POST['check_out'] ) ) {
					if ( $coupon->is_still_valid() ) {
						$coupon_amount = $coupon_info['amount'];
						if ( $coupon_info['amount_type'] == 'percent' ) {
							if ( floor( $coupon_amount ) == $coupon_amount ) {
								$coupon_amount = number_format( $coupon_amount );
							}
							$coupon_amount_text = $coupon_amount . '%';
						} else {
							$coupon_amount_text = 	$this->utils->price_with_symbol( $coupon_amount );
						}
						$response['success'] = true;
						$response['msg'] = str_replace( '%amount', $coupon_amount_text, $this->hbdb->get_string( 'valid_coupon' ) );
						$response['coupon_id'] = $coupon_id;
						$response['coupon_amount'] = $coupon_amount;
						$response['coupon_type'] = $coupon_info['amount_type'];
						$response['coupon_amount_text'] = $coupon_amount_text;
						break;
					} else {
						$response['msg'] = $this->hbdb->get_string( 'coupon_no_longer_valid' );
					}
				}
			}
		}
		echo( json_encode( $response ) );
		die;
	}
}