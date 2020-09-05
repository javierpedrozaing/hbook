<?php
class HbAdminAjaxActions {

	private $hbdb;
	private $utils;
	private $options_utils;
	private $stripe;
	private $resa;

	public function __construct( $db, $utils, $options_utils, $stripe, $resa ) {
		$this->hbdb = $db;
		$this->utils = $utils;
		$this->options_utils = $options_utils;
		$this->stripe = $stripe;
		$this->resa = $resa;
	}

	private function hb_verify_nonce() {
		if ( wp_verify_nonce( $_POST['nonce'], 'hb_nonce_update_db' ) ) {
			return true;
		} else {
			esc_html_e( 'Your session has expired. Please refresh the page.', 'hbook-admin' );
			return false;
		}
	}

	private function hb_user_can() {
		if ( current_user_can( 'manage_options' ) ) {
			return true;
		} else {
			echo( 'Not enough privileges to do this action.' );
			return false;
		}
	}

	private function hb_user_can_manage_resa() {
		if ( current_user_can( 'manage_options' ) || current_user_can( 'manage_resa' ) ) {
			return true;
		} else {
			echo( 'Not enough privileges to do this action.' );
			return false;
		}
	}

	public function hb_update_db() {
		if ( $this->hb_verify_nonce() && $this->hb_user_can() ) {
			echo( $this->hbdb->update_hb_setting( $_POST['db_action'], $_POST['object'] ) );
		}
		die;
	}

	public function hb_update_appearance_settings() {
		if ( $this->hb_verify_nonce() && $this->hb_user_can() ) {
			$settings = $this->options_utils->get_options_list( 'appearance_settings' );
			foreach ( $settings as $setting ) {
				if ( isset( $_POST[ $setting ] ) ) {
					update_option( $setting, wp_strip_all_tags( stripslashes( $_POST[ $setting ] ) ) );
				}
			}
			echo( 'settings saved' );
		}
		die;
	}

	public function hb_update_payment_settings() {
		if ( $this->hb_verify_nonce() && $this->hb_user_can() ) {
			$settings = $this->options_utils->get_options_list( 'payment_settings' );
			foreach ( $this->utils->get_payment_gateways() as $gateway ) {
				$settings[] = 'hb_' . $gateway->id .'_active';
				$gateway_admin_fields = $gateway->admin_fields();
				$gateway_options = $gateway_admin_fields['options'];
				foreach ( $gateway_options as $id => $option ) {
					$settings[] = $id;
				}
			}
			foreach ( $settings as $setting ) {
				if ( isset( $_POST[ $setting ] ) ) {
					if ( is_array( $_POST[ $setting ] ) ) {
						update_option( $setting, wp_strip_all_tags( json_encode( $_POST[ $setting ] ) ) );
					} else {
						update_option( $setting, wp_strip_all_tags( stripslashes( $_POST[ $setting ] ) ) );
					}
				}
			}
			echo( 'settings saved' );
		}
		die;
	}

	public function hb_update_misc_settings() {
		if ( $this->hb_verify_nonce() && $this->hb_user_can() ) {
			$settings = $this->options_utils->get_options_list( 'misc_settings' );
			if ( isset( $_POST[ 'hb_accommodation_slug' ] ) && $_POST[ 'hb_accommodation_slug' ] != get_option( 'hb_accommodation_slug' ) ) {
				update_option( 'hb_flush_rewrite', 'flush' );
			}
			foreach ( $settings as $setting ) {
				if ( isset( $_POST[ $setting ] ) ) {
					update_option( $setting, wp_strip_all_tags( trim( stripslashes( $_POST[ $setting ] ) ) ) );
				}
			}
			echo( 'settings saved' );
		}
		die;
	}

	public function hb_update_ical_settings() {
		if ( $this->hb_verify_nonce() && $this->hb_user_can() ) {
			$settings = $this->options_utils->get_options_list( 'ical_settings' );
			foreach ( $settings as $setting ) {
				if ( isset( $_POST[ $setting ] ) ) {
					update_option( $setting, wp_strip_all_tags( trim( stripslashes( $_POST[ $setting ] ) ) ) );
				}
			}
			echo( 'settings saved' );
		}
		die;
	}

	public function hb_update_forms_settings() {
		if ( $this->hb_verify_nonce() && $this->hb_user_can() ) {
			$options = $this->options_utils->get_options_list( 'search_form_options' );
			foreach ( $options as $option ) {
				update_option( $option, wp_strip_all_tags( stripslashes( $_POST[ $option ] ) ) );
			}
			$options = $this->options_utils->get_options_list( 'accom_selection_options' );
			foreach ( $options as $option ) {
				update_option( $option, wp_strip_all_tags( stripslashes( $_POST[ $option ] ) ) );
			}
			echo( 'settings saved' );
		}
		die;
	}

	public function hb_update_details_form_settings() {
		if ( $this->hb_verify_nonce() && $this->hb_user_can() ) {
			$this->hbdb->update_fields( wp_strip_all_tags( stripslashes( $_POST['hb_fields'] ) ) );
			echo( 'settings saved' );
		}
		die;
	}

	public function hb_update_strings() {
		if ( $this->hb_verify_nonce() && $this->hb_user_can() ) {
			$strings = $this->utils->get_string_list();
			$external_strings_sections = apply_filters( 'hb_strings', array() );
			foreach ( $external_strings_sections as $section ) {
				$strings = array_merge( $strings, $section['strings'] );
			}
			$langs = $this->utils->get_langs();
			$strings_to_update = array();
			foreach ( $strings as $string_id => $string_name ) {
				foreach ( $langs as $locale => $lang_name ) {
					$input_name = 'string-id-' . $string_id . '-in-' . $locale;
					if ( isset( $_POST[ $input_name ] ) ) {
						$strings_to_update[] = array(
							'id' => $string_id,
							'locale' => $locale,
							'value' => wp_kses_post( stripslashes( $_POST[ $input_name ] ) )
						);
					}
				}
			}
			$this->hbdb->update_strings( $strings_to_update );
			echo( 'form saved' );
		}
		die;
	}

	public function hb_update_rates() {
		if ( $this->hb_verify_nonce() && $this->hb_user_can() ) {
			if( $this->hbdb->update_rates( $_POST['rate_type'], $_POST['rates'] ) ) {
				echo( 'rates saved' );
			} else {
				echo( 'Database error.' );
			}
		}
		die;
	}

	public function hb_change_resa_status() {
		if ( $this->hb_verify_nonce() && $this->hb_user_can_manage_resa() ) {
			if ( $this->hbdb->update_resa_status( $_POST['resa_id'], $_POST['resa_status'] ) ) {
				switch ( $_POST['resa_status'] ) {
					case 'confirmed' : $this->utils->send_email( 'confirmation_resa', $_POST['resa_id'] ); break;
					case 'cancelled' : $this->utils->send_email( 'cancellation_resa', $_POST['resa_id'] ); break;
				}
				echo( 'resa updated' );
			} else {
				echo( 'Database error.' );
			}
		}
		die;
	}

	public function hb_confirm_resa() {
		if ( $this->hb_verify_nonce() && $this->hb_user_can_manage_resa() ) {
			$response = array();
			$resa = $this->hbdb->get_resa_by_id( $_POST['resa_id'] );
			if ( $resa['accom_num'] == 0 ) {
				$accom_num = $this->hbdb->get_first_available_accom_num( $resa['accom_id'], $resa['check_in'], $resa['check_out'] );
				if ( $accom_num ) {
					if ( $this->hbdb->update_resa_accom( $_POST['resa_id'], $resa['accom_id'], $accom_num ) === false ) {
						$response['status'] = 'db_error';
						echo( json_encode( $response ) );
						die;
					}
				} else {
					$response['status'] = 'no accom available';
				}
			} else {
				if ( ! $this->hbdb->is_available_accom_num( $resa['accom_id'], $resa['accom_num'], $resa['check_in'], $resa['check_out'] ) ) {
					$response['status'] = 'accom num not available';
					$accom_num = 0;
				} else {
					$accom_num = $resa['accom_num'];
				}
			}
			if ( $accom_num ) {
				if ( $this->hbdb->update_resa_status( $_POST['resa_id'], 'confirmed' ) ) {
					$response['status'] = 'confirmed';
					$response['accom_num'] = $accom_num;
					$response['automatic_blocked_accom'] = $this->hbdb->automatic_block_accom( $resa['accom_id'], $accom_num, $resa['check_in'], $resa['check_out'], $_POST['resa_id'] );
					$this->utils->send_email( 'confirmation_resa', $_POST['resa_id'] );
				} else {
					$response['status'] = 'db_error';
				}
			}
			echo( json_encode( $response ) );
		}
		die;
	}

	public function hb_update_resa_dates() {
		if ( $this->hb_verify_nonce() && $this->hb_user_can_manage_resa() ) {
			if ( $this->utils->can_update_resa_dates( $_POST['resa_id'], $_POST['new_check_in'], $_POST['new_check_out'] ) ) {
				if ( $this->hbdb->update_resa_dates( $_POST['resa_id'], $_POST['new_check_in'], $_POST['new_check_out'] ) ) {
					$this->resa->load( $_POST['resa_id'] );
					$automatic_blocked_accom = array();
					if ( ( $this->resa->status == 'new' ) || ( $this->resa->status == 'confirmed' ) ) {
						$automatic_blocked_accom = $this->hbdb->automatic_block_accom( $this->resa->accom_id, $this->resa->accom_num, $this->resa->check_in, $this->resa->check_out, $this->resa->id );
					}
					$new_price = -1;
					if ( $this->resa->accom_price != -1 ) {
						$this->resa->check_in = $_POST['new_check_in'];
						$this->resa->check_out = $_POST['new_check_out'];
						$this->resa->refresh_price();
						$new_price = $this->resa->total_price();
						if ( $new_price == $this->resa->price ) {
							$new_price = -1;
						}
					}
					$response = array(
						'status' => 'resa_dates_modified',
						'automatic_blocked_accom' => $automatic_blocked_accom,
						'new_price' => $new_price,
						'discounts' => $this->resa->discounts(),
					);
				} else {
					$response = array(
						'status' => 'db_error'
					);
				}
			} else {
				$response = array(
					'status' => 'resa_dates_not_modified'
				);
			}
			echo( json_encode( $response ) );
		}
		die;
	}

	public function hb_update_resa_info() {
		if ( $this->hb_verify_nonce() && $this->hb_user_can_manage_resa() ) {
			$this->resa->load( $_POST['resa_id'] );
			if ( $this->hbdb->update_resa_info( $_POST['resa_id'], $_POST['adults'], $_POST['children'], $_POST['lang'], stripslashes( $_POST['additional_info'] ) ) !== false ) {
				$new_price = -1;
				if (
					(
						( $_POST['adults'] != $this->resa->adults ) ||
						( $_POST['children'] != $this->resa->children )
					) &&
					( $this->resa->accom_price != -1 )
				) {
					$this->resa->adults = $_POST['adults'];
					$this->resa->children = $_POST['children'];
					$this->resa->refresh_price();
					$new_price = $this->resa->total_price();
					if ( $new_price == $this->resa->price ) {
						$new_price = -1;
					}
				}
				$response = array(
					'status' => 'resa_info_updated',
					'new_price' => $new_price,
				);
			} else {
				$response = array(
					'status' => 'db_error'
				);
			}
			echo( json_encode( $response ) );
		}
		die;
	}

	public function hb_edit_options_get_editor() {
		if ( $this->hb_verify_nonce() && $this->hb_user_can_manage_resa() ) {
			require_once $this->utils->plugin_directory . '/utils/resa-options.php';
			$options_form = new HbOptionsForm( $this->hbdb, $this->utils );
			$this->resa->load( $_POST['resa_id'] );
			$nb_nights = $this->utils->get_number_of_nights( $this->resa->check_in, $this->resa->check_out );
			echo( $options_form->get_update_options_form_markup_backend( $this->resa->adults, $this->resa->children, $nb_nights, $this->resa->extras ) );
		}
		die;
	}

	public function hb_update_resa_options() {
		if ( $this->hb_verify_nonce() && $this->hb_user_can_manage_resa() ) {
			$options = $this->hbdb->get_options_with_choices( $_POST['accom_id'] );
			$options_choices = $this->hbdb->get_all( 'options_choices' );
			$choice_name = array();
			foreach ( $options_choices as $choice ) {
				$choice_name[ $choice['id'] ] = $choice['name'];
			}
			$chosen_options = array();
			foreach ( $options as $option ) {
				$chosen_option = array(
					'name' => $option['name'],
					'amount' => $option['amount'],
					'amount_children' => $option['amount_children'],
					'apply_to_type' => $option['apply_to_type'],
				);
				if ( $option['apply_to_type'] == 'quantity' || $option['apply_to_type'] == 'quantity-per-day' ) {
					$quantity = intval( $_POST[ 'hb_option_' . $option['id'] ] );
					if ( $quantity ) {
						$chosen_option['quantity'] = $quantity;
						$chosen_option['amount'] = $option['amount'];
						$chosen_options[ $option['id'] ] = $chosen_option;
					}
				} else if ( $option['choice_type'] == 'single' ) {
					if ( isset( $_POST[ 'hb_option_' . $option['id'] ] ) ) {
						$chosen_option['amount'] = $option['amount'];
						$chosen_option['amount_children'] = $option['amount_children'];
						$chosen_options[ $option['id'] ] = $chosen_option;
					}
				} else {
					foreach ( $option['choices'] as $choice ) {
						if ( $_POST[ 'hb_option_' . $option['id'] ] == $choice['id'] ) {
							$chosen_option['chosen'] = $choice['id'];
							$chosen_option['choice_name'] = $choice_name[ $choice['id'] ];
							$chosen_option['amount'] = $choice['amount'];
							$chosen_option['amount_children'] = $choice['amount_children'];
						}
					}
					$chosen_options[ $option['id'] ] = $chosen_option;
				}
			}
			$chosen_options = json_encode( $chosen_options );

			if ( $this->hbdb->update_resa_options( $_POST['resa_id'], $chosen_options ) !== false ) {
				$new_price = -1;
				$this->resa->load( $_POST['resa_id'] );
				if ( $this->resa->accom_price != -1 ) {
					$new_price = $this->resa->total_price();
					if ( $new_price == $this->resa->price ) {
						$new_price = -1;
					} else {
						$this->hbdb->update_resa_price( $_POST['resa_id'], $this->resa->price, $new_price );
					}
				}
				$response = array(
					'status' => 'resa_options_updated',
					'new_price' => $new_price,
					'options_info' => $this->utils->resa_options_markup_admin( $chosen_options ),
				);
			} else {
				$response = array(
					'status' => 'db_error'
				);
			}
			echo( json_encode( $response ) );
		}
		die;
	}

	public function hb_update_resa_comment() {
		if ( $this->hb_verify_nonce() && $this->hb_user_can_manage_resa() ) {
			if ( $this->hbdb->update_resa_comment( $_POST['resa_id'], stripslashes( $_POST['resa_comment'] ) ) !== false ) {
				echo( 'admin comment updated' );
			} else {
				echo( 'Database error.' );
			}
		}
		die;
	}

	public function hb_edit_accom_get_avai() {
		if ( $this->hb_verify_nonce() && $this->hb_user_can_manage_resa() ) {
			$accom = $this->hbdb->get_all_accom_ids();
			$all_avai_accom_with_num = array();
			$avai_accom = false;
			foreach ( $accom as $accom_id ) {
				$accom_num = array_keys( $this->hbdb->get_accom_num_name( $accom_id ) );
				$unavai_accom_num = $this->hbdb->get_unavailable_accom_num_per_date( $accom_id, $_POST['check_in'], $_POST['check_out'] );
				$avai_accom_num = array_values( array_diff( $accom_num, $unavai_accom_num ) );
				if ( $avai_accom_num ) {
					$avai_accom = true;
				}
				$all_avai_accom_with_num[] = array(
					'accom_id' => $accom_id,
					'accom_num' => $avai_accom_num
				);
			}
			if ( $avai_accom ) {
				echo( json_encode( $all_avai_accom_with_num ) );
			} else {
				echo( json_encode( array() ) );
			}
		}
		die;
	}

	public function hb_update_resa_accom() {
		if ( $this->hb_verify_nonce() && $this->hb_user_can_manage_resa() ) {
			if ( $this->hbdb->is_available_accom_num( $_POST['accom_id'], $_POST['accom_num'], $_POST['check_in'], $_POST['check_out'] ) ) {
				$this->resa->load( $_POST['resa_id'] );
				$this->resa->accom_id = $_POST['accom_id'];
				if ( $this->hbdb->update_resa_accom( $_POST['resa_id'], $_POST['accom_id'], $_POST['accom_num'] ) ) {
					$automatic_blocked_accom = array();
					if ( ( $this->resa->status == 'new' ) || ( $this->resa->status == 'confirmed' ) ) {
						$automatic_blocked_accom = $this->hbdb->automatic_block_accom( $_POST['accom_id'], $_POST['accom_num'], $_POST['check_in'], $_POST['check_out'], $_POST['resa_id'] );
					}
					$new_price = -1;
					if ( $this->resa->accom_price != -1 ) {
						$this->resa->refresh_price();
						$new_price = $this->resa->total_price();
						if ( $new_price == $this->resa->price ) {
							$new_price = -1;
						}
					}
					$response = array(
						'status' => 'accom_updated',
						'automatic_blocked_accom' => $automatic_blocked_accom,
						'new_price' => $new_price,
						'discounts' => $this->resa->discounts(),
					);
				} else {
					$response = array(
						'status' => 'db_error'
					);
				}
			} else {
				$accom_num_name = $this->hbdb->get_accom_num_name( $_POST['accom_id'] );
				$response = array(
					'status' => 'accom_no_longer_available',
					'msg' => sprintf( esc_html__( 'The %s (%s) is no longer available.', 'hbook-admin' ), get_the_title( $_POST['accom_id'] ), $accom_num_name[ $_POST['accom_num'] ] ),
				);
			}
			echo( json_encode( $response ) );
		}
		die;
	}

	public function hb_update_resa_price() {
		if ( $this->hb_verify_nonce() && $this->hb_user_can_manage_resa() ) {
			$this->resa->load( $_POST['resa_id'] );
			$new_price = floatval( $_POST['new_price'] );
			if ( $this->resa->accom_price == -1 ) {
				$this->hbdb->update_resa_price( $_POST['resa_id'], $this->resa->price, $new_price );
				$response = array(
					'status' => 'price_updated',
					'discount_status' => 'not_updated',
				);
			} else {
				$total_fee_percent = 0;
				foreach ( $this->resa->final_added_fees as $fee ) {
					if ( $fee['apply_to_type'] == 'global-percentage' ) {
						$total_fee_percent += $fee['amount'];
					}
				}
				$new_discount = round( ( $this->resa->price - $new_price ) / ( 1 + ( $total_fee_percent / 100 ) ), 2 );
				$discount_amount = $new_discount + $this->resa->global_discount_amount();
				$this->resa->global_discount = array(
					'amount' => $discount_amount,
					'amount_type' => 'fixed'
				);
				if ( $this->hbdb->update_resa_prices_info( $_POST['resa_id'], $this->resa->accom_price, json_encode( $this->resa->discounts() ), $this->resa->deposit(), $this->resa->price, $this->resa->total_price() ) !== false ) {
					$response = array(
						'status' => 'price_updated',
						'discount_status' => 'updated',
						'discount_amount' => $this->resa->global_discount['amount'],
					);
				} else {
					$response = array(
						'status' => 'db_error',
					);
				}
			}
			echo( json_encode( $response ) );
		}
		die;
	}

	public function hb_update_resa_paid() {
		if ( $this->hb_verify_nonce() && $this->hb_user_can_manage_resa() ) {
			if ( $this->hbdb->update_resa_paid( $_POST['resa_id'], $_POST['resa_paid'] ) !== false ) {
				echo( 'paid updated' );
			} else {
				echo( 'Database error.' );
			}
		}
		die;
	}

	public function hb_update_resa_discount() {
		if ( $this->hb_verify_nonce() && $this->hb_user_can_manage_resa() ) {
			$this->resa->load( $_POST['resa_id'] );
			$accom_discount_amount = $this->utils->round_price( floatval( $_POST['accom_discount_amount'] ) );
			$global_discount_amount = $this->utils->round_price( floatval( $_POST['global_discount_amount'] ) );
			$this->resa->accom_discount = array(
				'amount_type' => $_POST['accom_discount_amount_type'],
				'amount' => $accom_discount_amount,
			);
			$this->resa->global_discount = array(
				'amount_type' => $_POST['global_discount_amount_type'],
				'amount' => $global_discount_amount,
			);
			$new_price = $this->resa->total_price();
			if ( $this->hbdb->update_resa_prices_info( $_POST['resa_id'], $this->resa->accom_price, json_encode( $this->resa->discounts() ), $this->resa->deposit(), $this->resa->price, $new_price ) !== false ) {
				$response = array(
					'status' => 'discount updated',
					'new_price' => $new_price,
				);
			} else {
				$response = array(
					'status' => 'db_error',
				);
			}
			echo( json_encode( $response ) );
		}
		die;
	}

	public function hb_update_customer() {
		if ( $this->hb_verify_nonce() && $this->hb_user_can_manage_resa() ) {
			$customer_id = intval( $_POST['customer_id'] );
			$customer_email = wp_strip_all_tags( $_POST['email'] );
			$customer_info = json_decode( wp_strip_all_tags( stripslashes( $_POST['info'] ) ), true );
			if ( $this->hbdb->update_customer( $customer_id, $customer_email, $customer_info ) !== false ) {
				echo( 'customer updated' );
			} else {
				echo( 'Database error.' );
			}
		}
		die;
	}

	public function hb_delete_resa() {
		if ( $this->hb_verify_nonce() && $this->hb_user_can_manage_resa() ) {
			if( $this->hbdb->delete_resa( $_POST['resa_id'] ) ) {
				echo( 'resa deleted' );
			} else {
				echo( 'Database error.' );
			}
		}
		die;
	}

	public function hb_delete_customer() {
		if ( $this->hb_verify_nonce() && $this->hb_user_can_manage_resa() ) {
			if( $this->hbdb->delete_customer( $_POST['customer_id'] ) ) {
				echo( 'customer_deleted' );
			} else {
				echo( 'Database error.' );
			}
		}
		die;
	}

	public function hb_add_blocked_accom() {
		if ( $this->hb_verify_nonce() && $this->hb_user_can_manage_resa() ) {
			if ( $this->hbdb->add_blocked_accom( $_POST['accom_id'], $_POST['accom_num'], $_POST['accom_all_ids'], $_POST['accom_all_num'], $_POST['from_date'], $_POST['to_date'], $_POST['comment'] ) ) {
				echo( 'blocked accom added' );
			} else {
				echo( 'Database error.' );
			}
		}
		die;
	}

	public function hb_delete_blocked_accom() {
		if ( $this->hb_verify_nonce() && $this->hb_user_can_manage_resa() ) {
			if ( $this->hbdb->delete_blocked_accom( $_POST['date_from'], $_POST['date_to'], $_POST['accom_id'], $_POST['accom_num'], $_POST['accom_all_ids'], $_POST['accom_all_num'] ) ) {
				echo( 'blocked accom deleted' );
			} else {
				echo( 'Database error.' );
			}
		}
		die;
	}

	public function hb_delete_sync_errors() {
		if ( $this->hb_verify_nonce() && $this->hb_user_can_manage_resa() ) {
			$this->hbdb->delete_sync_errors();
		}
		die;
	}

	public function hb_resa_create_new_customer() {
		if ( $this->hb_verify_nonce() && $this->hb_user_can_manage_resa() ) {
			$response = array();
			$customer_id = $this->hbdb->create_customer( '', array() );
			if ( $customer_id ) {
				if ( $this->hbdb->resa_update_customer_id( $_POST['resa_id'], $customer_id ) ) {
					$response['customer_id'] = $customer_id;
				}
			}
			echo( json_encode( $response ) );
		}
		die;
	}

	public function hb_save_selected_customer() {
		if ( $this->hb_verify_nonce() && $this->hb_user_can_manage_resa() ) {
			$response = array();
			if ( $this->hbdb->resa_update_customer_id( $_POST['resa_id'], $_POST['customer_id'] ) ) {
				$response['customer_id'] = $_POST['customer_id'];
			} else {
				$response['customer_id'] = 0;
			}
			echo( json_encode( $response ) );
		}
		die;
	}

	public function hb_resa_charging() {
		if ( $this->hb_verify_nonce() && $this->hb_user_can_manage_resa() ) {
			$resa = $this->hbdb->get_resa_by_id( $_POST['resa_id'] );
			$customer_payment_id = $this->hbdb->get_customer_payment_id( $resa['customer_id'] );
			$customer_info = $this->hbdb->get_customer_info( $resa['customer_id'] );

			if ( get_option( 'hb_security_bond' ) == 'yes' ) {
				$security_bond = get_option( 'hb_security_bond_amount' );
			} else {
				$security_bond = 0;
			}
			$charge_amount = floatval( $_POST['charge_amount'] );
			if ( $charge_amount <= 0 || $charge_amount > round( $resa['price'] - $resa['paid'], 2 ) + $security_bond ) {
				echo( 'Invalid amount.' );
				die;
			}

			$currency = get_option( 'hb_currency' );
			if ( $currency != $resa['currency'] ) {
				echo( 'Currency error.' );
				die;
			}

			$customer_email = '';
			$customer_first_name = '';
			$customer_last_name = '';
			if ( isset( $customer_info['email'] ) ) {
				$customer_email = $customer_info['email'];
			}
			if ( isset( $customer_info['first_name'] ) ) {
				$customer_first_name = $customer_info['first_name'];
			}
			if ( isset( $customer_info['last_name'] ) ) {
				$customer_last_name = $customer_info['last_name'];
			}
			$payment_description = $customer_email;
			if ( $customer_first_name || $customer_last_name ) {
				$payment_description .= ' (' . $customer_first_name . ' ' . $customer_last_name . ')';
			}
			if ( $payment_description ) {
				$payment_description .= ' - ';
			}
			$payment_description .= get_the_title( $resa['accom_id'] );
			$payment_description .= ' (' . esc_html__( 'from', 'hbook-admin' ) . ' ' . $resa['check_in'] . ' ' . esc_html__( 'to', 'hbook-admin' ) . ' ' . $resa['check_out'] . ')';

			if ( substr( $customer_payment_id, 0, 4 ) == 'cus_' ) {
				$response = $this->stripe->remote_post_to_stripe( 'https://api.stripe.com/v1/customers/' . $customer_payment_id, array() );
				if ( $response['success'] ) {
					$response['info'] = json_decode( $response['info'], true );
					$payment_method = $response['info']['default_source'];
					if ( ! $payment_method ) {
						echo( 'Unexpected error (no payment source).' );
						die;
					}
				} else {
					echo( $response['error_msg'] );
					die;
				}
			} else {
				$customer_payment_info = json_decode( $customer_payment_id, true );
				$payment_method = $customer_payment_info['payment_method_id'];
				$customer_payment_id = $customer_payment_info['customer_id'];
			}

			$post_args = array(
				'amount' => $charge_amount,
				'currency' => $currency,
				'customer' => $customer_payment_id,
				'description' => $payment_description,
				'payment_method' => $payment_method,
				'off_session' => 'true',
				'confirm' => 'true',
			);

			$response = $this->stripe->remote_post_to_stripe( 'https://api.stripe.com/v1/payment_intents', $post_args );
			if ( $response['success'] ) {
				$this->hbdb->update_resa_paid( $_POST['resa_id'], $resa['paid'] + $charge_amount );
				$response['info'] = json_decode( $response['info'], true );
				$stripe_charge = array(
					'id' => $response['info']['charges']['data'][0]['id'],
					'amount' => $charge_amount,
				);
				$resa_has_payment_info = false;
				if ( $resa['payment_info'] ) {
					$resa['payment_info'] = json_decode( $resa['payment_info'], true );
					if ( $resa['payment_info'] ) {
						$resa_has_payment_info = true;
					}
				}
				if ( $resa_has_payment_info ) {
					$resa['payment_info']['stripe_charges'][] = $stripe_charge;
				} else {
					$resa['payment_info'] = array( 'stripe_charges' => array( $stripe_charge ) );
				}
				$this->hbdb->update_resa_payment_info( $_POST['resa_id'], json_encode( $resa['payment_info'] ) );
				echo( 'charge_done' );
			} else {
				echo( $response['error_msg'] );
			}
		}
		die;
	}

	public function hb_resa_refunding() {
		if ( $this->hb_verify_nonce() && $this->hb_user_can_manage_resa() ) {
			$resa = $this->hbdb->get_resa_by_id( $_POST['resa_id'] );
			$refund_amount = floatval( $_POST['refund_amount'] );
			if ( $refund_amount <= 0 || $refund_amount > $resa['paid'] ) {
				echo( 'Invalid amount.' );
				die;
			}

			$currency = get_option( 'hb_currency' );
			if ( $currency != $resa['currency'] ) {
				echo( 'Currency error.' );
				die;
			}

			$payment_info = json_decode( $resa['payment_info'], true );
			if ( ! $payment_info ) {
				echo( 'No payment info.' );
				die;
			}
			if ( ! isset( $payment_info['stripe_charges'] ) ) {
				echo( 'No Stripe charges.' );
				die;
			}

			$stripe_charges = $payment_info['stripe_charges'];
			$refunded = 0;
			$refunds_to_do = array();
			$new_stripe_charges = array();
			foreach ( $stripe_charges as $charge ) {
				if ( $refunded == $refund_amount ) {
					$new_stripe_charges[] = $charge;
				} else {
					if ( $charge['amount'] <= $refund_amount - $refunded ) {
						$refunds_to_do[] = array(
							'id' => $charge['id'],
							'amount' => $charge['amount']
						);
						$refunded += $charge['amount'];
					} else {
						$charge_refund_amount = $refund_amount - $refunded;
						$refunds_to_do[] = array(
							'id' => $charge['id'],
							'amount' => $charge_refund_amount
						);
						$new_stripe_charges[] = array(
							'id' => $charge['id'],
							'amount' => $charge['amount'] - $charge_refund_amount
						);
						$refunded += $charge_refund_amount;
					}
				}
			}

			if ( $refunded != $refund_amount ) {
				echo( 'Could not refund.' );
				die;
			}

			foreach ( $refunds_to_do as $refund ) {
				$post_args = array(
					'charge' => $refund['id'],
					'amount' => $refund['amount'],
					'currency' => $currency,
				);
				$response = $this->stripe->remote_post_to_stripe( 'https://api.stripe.com/v1/refunds', $post_args );
				if ( ! $response['success'] ) {
					echo( $response['error_msg'] );
					die;
				}
			}

			$payment_info = json_encode( array( 'stripe_charges' => $new_stripe_charges ) );
			$this->hbdb->update_resa_payment_info( $_POST['resa_id'], $payment_info );
			$this->hbdb->update_resa_paid( $_POST['resa_id'], $resa['paid'] - $refund_amount );
			echo( 'refund_done' );
		}
		die;
	}

	public function hb_send_email_customer() {
		if ( $this->hb_verify_nonce() && $this->hb_user_can_manage_resa() ) {
			if ( $this->utils->send_not_automatic_email() ) {
				echo( 'email_sent' );
			} else {
				echo( 'Email could not be sent.' );
			}
		}
		die;
	}

}