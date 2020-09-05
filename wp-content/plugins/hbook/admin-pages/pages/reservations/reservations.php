<?php
class HbAdminPageReservations extends HbAdminPage {

	private $accom_list;
	private $email_templates;
	private $document_templates;
	private $is_site_multi_lang;
	private $site_langs;
	private $blocked_accom_displayer;
	private $resa_exporter_displayer;
	private $sync_errors_displayer;
	private $admin_add_resa_displayer;
	private $resa_display_helper;
	private $past_months_with_checkout;
	private $month_full_name;
	private $is_resa_archive;
	private $archived_year;
	private $archived_month;
	private $is_resa_customer;

	public function __construct( $page_id, $hbdb, $utils, $options_utils ) {
		$hb_text = array(
			'new' => esc_html__( 'New', 'hbook-admin' ),
			'pending' => esc_html__( 'Pending', 'hbook-admin' ),
			'confirmed' => esc_html__( 'Confirmed', 'hbook-admin' ),
			'cancelled' => esc_html__( 'Cancelled', 'hbook-admin' ),
			'processing' => esc_html__( 'Processing', 'hbook-admin' ),
			'not_allocated' => esc_html__( '(not allocated)', 'hbook-admin' ),
			'paid' => esc_html__( 'Paid', 'hbook-admin' ),
			'unpaid' => esc_html__( 'Unpaid', 'hbook-admin' ),
			'not_fully_paid' => esc_html__( 'Not fully paid', 'hbook-admin' ),
			'bond_not_paid' => esc_html__( 'Bond not paid', 'hbook-admin' ),
			'paid_details' => esc_html__( 'Paid:', 'hbook-admin' ),
			'to_be_paid_details' => esc_html__( 'Unpaid:', 'hbook-admin' ),
			'to_be_paid_bond_details' => esc_html__( 'Unpaid bond:', 'hbook-admin' ),
			'confirm_delete_resa' => esc_html__( 'Delete reservation?', 'hbook-admin' ),
			'confirm_cancel_resa' => esc_html__( 'Cancel reservation?', 'hbook-admin' ),
			'select_accom_num' => esc_html__( 'Select accommodation:', 'hbook-admin' ),
			'accom_not_selected' => esc_html__( 'Please select an accommodation.', 'hbook-admin' ),
			'customer_not_selected' => esc_html__( 'Please select a customer (or select "Enter customer details" and provide customer details).', 'hbook-admin' ),
			'select_accom_none' => esc_html__( 'No accommodation available.', 'hbook-admin' ),
			'info_adults' => esc_html__( 'Adults:', 'hbook-admin' ),
			'info_children' => esc_html__( 'Children:', 'hbook-admin' ),
			'invalid_price' => esc_html__( 'Invalid price.', 'hbook-admin' ),
			'customer_id' => esc_html__( 'Customer id:', 'hbook-admin' ),
			'customer_resa' => esc_html__( '%s reservations', 'hbook-admin' ),
			'more_info' => esc_html__( 'More information', 'hbook-admin' ),
			'less_info' => esc_html__( 'Less information', 'hbook-admin' ),
			'admin_comment' => esc_html__( 'Comment:', 'hbook-admin' ),
			'error' => esc_html__( 'Error:', 'hbook-admin' ),
			'no_accom_available_on_confirm' => esc_html__( 'The reservation could not be confirmed because there is no accommodation available for the reservation dates.', 'hbook-admin' ),
			'accom_num_not_available_on_confirm' => esc_html__( 'The reservation could not be confirmed because the accommodation number is not available for the reservation dates.', 'hbook-admin' ),
			'no_export_data_selected' => esc_html__( 'Please select the data you want to export.', 'hbook-admin' ),
			'no_export_accom_selected' => esc_html__( 'Please select which accommodation you want to export data for.', 'hbook-admin' ),
			'confirm_delete_blocked_accom' => esc_html__( 'Remove blocked dates?', 'hbook-admin' ),
			'all' => esc_html__( 'All', 'hbook-admin' ),
			'confirm_delete_sync_errors' => esc_html__( 'Delete synchronization errors messages?', 'hbook-admin' ),
			'charge_amount_too_high' => esc_html__( 'The charge amount can not be above %amount', 'hbook-admin' ),
			'charge_amount_negative' => esc_html__( 'The charge amount can not be below zero.', 'hbook-admin' ),
			'refund_amount_too_high' => esc_html__( 'The refund amount can not be above %amount', 'hbook-admin' ),
			'refund_amount_negative' => esc_html__( 'The refund amount can not be below zero.', 'hbook-admin' ),
			'resa_dates_not_modified' => esc_html__( 'Dates have not modified because the accommodation is not available for the new dates.', 'hbook-admin' ),
			'check_out_before_check_in' => esc_html__( 'Check-out must be after check-in.', 'hbook-admin' ),
			'invalid_date' => esc_html__( 'Invalid date.', 'hbook-admin' ),
			'customer_id_short' => esc_html__( 'Id:', 'hbook-admin' ),
			'email_templates_caption' => esc_html__( 'Templates...', 'hbook-admin' ),
			'to_refund' => esc_html__( 'To refund:', 'hbook-admin' ),
			'block_all' => esc_html__( 'Are you sure you want to block all accommodation for all days?', 'hbook-admin' ),
			'to_date_before_from_date' => esc_html__( 'The "To" date must be after the "From" date.', 'hbook-admin' ),
			'resa_lang' => esc_html__( 'Reservation language:', 'hbook-admin' ),
			'first_name' => esc_html__( 'First name', 'hbook-admin' ),
			'last_name' => esc_html__( 'Last name', 'hbook-admin' ),
			'email' => esc_html__( 'Email', 'hbook-admin' ),
			'price' => esc_html__( 'Price', 'hbook-admin' ),
			'price_with_bond' => esc_html__( 'Price with bond', 'hbook-admin' ),
			'previous_price' => esc_html__( 'Previous price', 'hbook-admin' ),
			'accom_discount' => esc_html__( 'Accom. discount:', 'hbook-admin' ),
			'global_discount' => esc_html__( 'Global discount:', 'hbook-admin' ),
			'select_attachments' => esc_html__( 'Select attachments', 'hbook-admin' ),
			'remove_all_attachments' => esc_html__( 'Remove all attachments?', 'hbook-admin' ),
			'fetching_options_editor' => esc_html( 'Fetching extras...', 'hbook-admin' ),

			'legend_select_check_in' => esc_html__( 'Select a check-in date.', 'hbook-admin' ),
			'legend_select_check_out' => esc_html__( 'Select a check-out date.', 'hbook-admin' ),
			'legend_past' => esc_html__( 'Past', 'hbook-admin' ),
			'connection_error' => esc_html__( 'There was a connection error.', 'hbook-admin' ),
			'no_check_in_out_date' => esc_html__( 'Please enter a check-in date and a check-out date.', 'hbook-admin' ),
			'no_check_in_date' => esc_html__( 'Please enter a check-in date.', 'hbook-admin' ),
			'no_check_out_date' => esc_html__( 'Please enter a check-out date.', 'hbook-admin' ),
			'no_adults' => esc_html( 'Please select the number of adults.', 'hbook-admin' ),
			'no_adults_children' => esc_html( 'Please select the number of adults and the number of children.', 'hbook-admin' ),
			'no_children' => esc_html( 'Please select the number of children.', 'hbook-admin' ),
			'check_out_before_check_in'  => esc_html( 'The check-out date must be after the check-in date.', 'hbook-admin' ),
		);

		$this->is_resa_customer = false;
		if ( isset( $_GET['customer_id'] ) ) {
			$this->is_resa_customer = true;
			$resa = $hbdb->get_all_resa_by_date_by_customer( $_GET['customer_id'] );
		} else {
			$resa = $hbdb->get_all_resa_by_date();
			$archived_resa = array();
			$this->is_resa_archive = false;
			$this->archived_year = '';
			$this->archived_month = '';
			if ( isset( $_GET['year_month'] ) ) {
				$this->is_resa_archive = true;
				$this->archived_year = substr( $_GET['year_month'], 0, 4 );
				$this->archived_month = substr( $_GET['year_month'], 5, 2 );
			}
			$this->past_months_with_checkout = array();
			$archive_limit_month = date( 'm' );
			$archive_limit_year = date( 'Y' );
			$archiving_delay = intval( get_option( 'hb_resa_archiving_delay' ) );
			if ( ! $archiving_delay ) {
				$archiving_delay = 12;
			}
			$archiving_delay_years = intval( $archiving_delay / 12 );
			$archiving_delay_months = $archiving_delay % 12;
			$archive_limit_month = $archive_limit_month - $archiving_delay_months;
			if ( $archive_limit_month < 1 ) {
				$archive_limit_month += 12;
				$archive_limit_year -= 1;
			}
			$archive_limit_year = $archive_limit_year - $archiving_delay_years;
		}

		foreach ( $resa as $key => $resa_data ) {
			$resa[ $key ]['old_currency'] = '';
			if ( $resa[ $key ]['currency'] != get_option( 'hb_currency' ) ) {
				$resa[ $key ]['old_currency'] = '(' . $resa[ $key ]['currency'] . ')';
			}
			if ( $resa[ $key ]['payment_status'] != '' ) {
				$resa[ $key ]['payment_status'] = '<div class="hb-payment-status" title="' . $resa[ $key ]['payment_status'] . '">' . $resa[ $key ]['payment_status'] . '</div>';
			}
			if ( $resa[ $key ]['payment_status_reason'] != '' ) {
				$resa[ $key ]['payment_status'] .= '<div class="hb-payment-status" title="' . ucfirst( $resa[ $key ]['payment_status_reason'] ) . '">' . ucfirst( $resa[ $key ]['payment_status_reason'] ) . '</div>';
			}
			$resa[ $key ]['options_info'] = $utils->resa_options_markup_admin( $resa_data['options'] );
			$resa[ $key ]['non_editable_info'] = $utils->resa_non_editable_info_markup( $resa_data );
			$resa[ $key ]['accom_discount_amount'] = '';
			$resa[ $key ]['accom_discount_amount_type'] = '';
			$resa[ $key ]['global_discount_amount'] = '';
			$resa[ $key ]['global_discount_amount_type'] = '';
			if ( $resa_data['discount'] ) {
				$discount = json_decode( $resa_data['discount'], true );
				if ( $discount['accom'] ) {
					$resa[ $key ]['accom_discount_amount'] = $discount['accom']['amount'];
					$resa[ $key ]['accom_discount_amount_type'] = $discount['accom']['amount_type'];
				}
				if ( $discount['global'] ) {
					$resa[ $key ]['global_discount_amount'] = $discount['global']['amount'];
					$resa[ $key ]['global_discount_amount_type'] = $discount['global']['amount_type'];
				}
			}
			$resa[ $key ]['received_on'] = $utils->get_blog_datetime( $resa[ $key ]['received_on'] );
			$resa[ $key ]['max_refundable'] = $utils->resa_max_refundable( $resa_data['payment_info'] );
			if ( ! $this->is_resa_customer ) {
				$resa_check_out_year = substr( $resa_data[ 'check_out' ], 0, 4 );
				$resa_check_out_month = substr( $resa_data[ 'check_out' ], 5, 2 );
				if ( ( $resa_check_out_year == $this->archived_year ) && ( $resa_check_out_month == $this->archived_month ) ) {
					$archived_resa[] = $resa[ $key ];
				}
				if (
					$resa_check_out_year < $archive_limit_year ||
					( ( $resa_check_out_year == $archive_limit_year ) && ( $resa_check_out_month < $archive_limit_month ) )
				) {
					if ( ! in_array( $resa_check_out_year . '-' . $resa_check_out_month, $this->past_months_with_checkout ) ) {
						$this->past_months_with_checkout[] = $resa_check_out_year . '-' . $resa_check_out_month;
					}
					unset( $resa[ $key ] );
				}
			}
		}
		if ( ! $this->is_resa_customer ) {
			arsort( $this->past_months_with_checkout );
			if ( $this->is_resa_archive ) {
				$resa = $archived_resa;
			} else {
				$resa = array_values( $resa );
			}
		}

		$this->accom_list = $hbdb->get_all_accom();
		$accom_tmp = array();
		foreach ( $this->accom_list as $accom_id => $accom_name ) {
			$accom_num_name = $hbdb->get_accom_num_name( $accom_id );
			$accom_tmp[ $accom_id ] = array(
				'name' => $accom_name,
				'short_name' => get_post_meta( $accom_id, 'accom_short_name', true ),
				'number' => get_post_meta( $accom_id, 'accom_quantity', true ),
				'num_name' => $accom_num_name
			);
		}
		$accom_info = $accom_tmp;

		$this->month_full_name = esc_html__( 'January,February,March,April,May,June,July,August,September,October,November,December', 'hbook-admin' );
		$this->month_full_name = explode( ',', $this->month_full_name );
		$month_short_name = esc_html__( 'Jan,Feb,Mar,Apr,May,Jun,Jul,Aug,Sep,Oct,Nov,Dec', 'hbook-admin' );
		$month_short_name = explode( ',', $month_short_name );
		$days_short_name = esc_html__( 'Sun,Mon,Tue,Wed,Thu,Fri,Sat', 'hbook-admin' );
		$days_short_name = explode( ',', $days_short_name );

		$customer_fields = $hbdb->get_customer_form_fields();
		$customer_fields_tmp = array();
		foreach ( $customer_fields as $field ) {
			$customer_fields_tmp[ $field['id'] ] = array(
				'name' => $field['name'],
				'type' => $field['type'],
			);
		}
		$customer_fields = $customer_fields_tmp;

		$additional_info_fields = $hbdb->get_additional_booking_info_form_fields();
		$additional_info_fields_tmp = array();
		foreach ( $additional_info_fields as $field ) {
			$additional_info_fields_tmp[ $field['id'] ] = array(
				'name' => $field['name'],
				'type' => $field['type'],
			);
		}
		$additional_info_fields = $additional_info_fields_tmp;

		$this->email_templates = array();
		$email_templates_tmp = $hbdb->get_all_email_templates();
		foreach ( $email_templates_tmp as $email_tmpl ) {
			$this->email_templates[ $email_tmpl['id'] ] = array(
				'name' => $email_tmpl['name'],
				'to_address' => $email_tmpl['to_address'],
				'subject' => $email_tmpl['subject'],
				'message' => $email_tmpl['message'],
				'lang' => $email_tmpl['lang'],
				'accom' => $email_tmpl['accom'],
				'all_accom' => $email_tmpl['all_accom'],
			);
		}

		$this->document_templates = $hbdb->get_document_templates();

		if ( get_option( 'hb_security_bond' ) == 'yes' ) {
			$security_bond = get_option( 'hb_security_bond_amount' );
		} else {
			$security_bond = '0';
		}

		$this->is_site_multi_lang = 'no';
		$this->site_langs = array();
		if ( $utils->is_site_multi_lang() ) {
			$this->is_site_multi_lang = 'yes';
			$this->site_langs = $utils->get_langs();
		}

		$stripe_active = get_option( 'hb_stripe_active' );
		if ( ! $stripe_active ) {
			$stripe_active = 'no';
		}

		$this->data = array(
			'resa' => $resa,
			'accoms' => $accom_info,
			'hb_text' => $hb_text,
			'month_short_name' => $month_short_name,
			'days_short_name' => $days_short_name,
			'hb_price_precision' => get_option( 'hb_price_precision' ),
			'hb_blocked_accom' => $hbdb->get_all_blocked_accom(),
			'hb_customer_fields' => $customer_fields,
			'hb_additional_info_fields' => $additional_info_fields,
			'hb_customers' => $hbdb->get_all_customers(),
			'hb_new_resa_status' => get_option( 'hb_resa_admin_status' ),
			'hb_stripe_active' => $stripe_active,
			'hb_email_templates' => $this->email_templates,
			'hb_document_templates' => $this->document_templates,
			'hb_admin_lang' => get_locale(),
			'hb_multi_lang_site' => $this->is_site_multi_lang,
			'hb_langs' => $this->site_langs,
			'hb_security_bond' => $security_bond,
			'hb_paid_security_bond' => get_option( 'hb_security_bond_online_payment' ),
			'hb_resa_customer_page_url' => admin_url( 'admin.php?page=hb_reservations&customer_id=' ),
			'hb_resa_document_page_url' => admin_url( 'admin.php?hbook_doc_id=' ),
			'hb_media_titles' => array(),
		);
		parent::__construct( $page_id, $hbdb, $utils, $options_utils );

		require_once $this->utils->plugin_directory . '/admin-pages/pages/reservations/blocked-accom-display.php';
		$this->blocked_accom_displayer = new HbAdminPageReservationsBlockedAccom( $this->accom_list );
		require_once $this->utils->plugin_directory . '/admin-pages/pages/reservations/resa-exporter-display.php';
		$this->resa_exporter_displayer = new HbAdminPageReservationsExport( $this->utils, $this->accom_list );
		require_once $this->utils->plugin_directory . '/admin-pages/pages/reservations/resa-sync-errors-display.php';
		$this->sync_errors_displayer = new HbAdminPageReservationsSyncErrors( $this->hbdb );

		require_once $this->utils->plugin_directory . '/front-end/renders/hbook-render.php';
		require_once $this->utils->plugin_directory . '/front-end/renders/booking-form-render.php';
		$booking_form_render = new HBookBookingForm( $this->hbdb, $this->utils );
		require_once $this->utils->plugin_directory . '/admin-pages/pages/reservations/admin-add-resa.php';
		$this->admin_add_resa_displayer = new HbAdminPageReservationsAdminAddResa( $this->hbdb, $this->utils, $booking_form_render );

		require_once $this->utils->plugin_directory . '/admin-pages/pages/reservations/resa-display.php';
		$this->resa_display_helper = new HbAdminPageReservationsDisplayHelper( $accom_info, $this->email_templates, $this->document_templates, $this->is_site_multi_lang, $this->site_langs, $this->is_resa_customer, $this->utils->get_currency_symbol(), $this->hbdb->site_has_extras() );
	}

	public function display() {
	?>

	<div class="wrap">

		<h1>
			<?php esc_html_e( 'Reservations', 'hbook-admin' ); ?>
			<?php
			if ( $this->is_resa_customer ) {
				echo( '<span class="hb-resa-customer-title">' );
				echo( '(' );
				esc_html_e( 'Customer id:', 'hbook-admin' );
				echo( ' ' . intval( $_GET['customer_id'] ) . ')' );
				echo( '</span>' );
			} else if ( $this->is_resa_archive ) {
				echo( '<span class="hb-archive-year-month-title">' );
				printf(
					esc_html__( '(Check-out in %s)', 'hbook-admin' ),
					$this->month_full_name[ $this->archived_month - 1 ] . ' ' . $this->archived_year
				);
				echo( '</span>' );
			}
			?>
		</h1>

		<hr/>

		<?php
		if ( $this->is_resa_customer ) {
			$this->resa_display_helper->display_resa_list();
		} else if ( $this->is_resa_archive ) {
			$this->resa_display_helper->display_resa_list();
			$this->resa_display_helper->display_resa_archives_links( $this->past_months_with_checkout, $this->is_resa_archive, $this->month_full_name );
		} else {
			if ( ( get_option( 'hb_ical_notification_option' ) != 'no' ) && ( current_user_can( 'manage_resa' ) || current_user_can( 'manage_options' ) ) ) {
				$this->sync_errors_displayer->display();
			}
			$this->resa_display_helper->display_resa_details();
			$this->resa_display_helper->display_resa_calendar();
			if ( current_user_can( 'manage_resa' ) || current_user_can( 'manage_options' ) ) {
				$this->blocked_accom_displayer->display();
				$this->admin_add_resa_displayer->display();
			}
			$this->resa_display_helper->display_resa_list();
			$this->resa_display_helper->display_resa_archives_links( $this->past_months_with_checkout, $this->is_resa_archive, $this->month_full_name );
			if ( current_user_can( 'manage_resa' ) || current_user_can( 'manage_options' ) ) {
				$this->resa_exporter_displayer->display();
			}
		}
		?>

	</div><!-- end .wrap -->

	<?php
	}

}