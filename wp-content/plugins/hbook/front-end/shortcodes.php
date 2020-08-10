<?php
class HBookShortcodes {

	private $hbdb;
	private $utils;
	private $strings;

	public function __construct( $hbdb, $utils ) {
		$this->hbdb = $hbdb;
		$this->utils = $utils;
	}

	public function accommodation_list( $atts ) {
		$atts = shortcode_atts(
			array(
				'show_thumbnail' => 'yes',
				'thumbnail_link' => 'yes',
				'title_tag' => 'h2',
				'thumb_width' => 150,
				'thumb_height' => 150,
				'accom_ids' => '',
				'book_button' => 'yes',
				'redirection_url' => '#',
				'nb_columns' => 2
			),
			$atts,
			'hb_accommodation_list'
		);

		$render_atts = $atts;
		if ( $atts['show_thumbnail'] == 'yes' ) {
			$render_atts['show_thumb'] = true;
		} else {
			$render_atts['show_thumb'] = false;
		}
		if ( $atts['thumbnail_link'] == 'yes' ) {
			$render_atts['link_thumb'] = true;
		} else {
			$render_atts['link_thumb'] = false;
		}
		if ( $atts['book_button'] == 'yes' ) {
			$render_atts['book_button'] = true;
		} else {
			$render_atts['book_button'] = false;
		}

		require_once $this->utils->plugin_directory . '/front-end/renders/hbook-render.php';
		require_once $this->utils->plugin_directory . '/front-end/renders/accom-list-render.php';
		$accom_list = new HBookAccomList( $this->hbdb, $this->utils );
		return $accom_list->render( $render_atts );
	}

	public function availability( $atts ) {
		$atts = shortcode_atts(
			array(
				'accom_id' => '',
				'calendar_sizes' => '2x1,1x1'
			),
			$atts,
			'hb_availability'
		);

		require_once $this->utils->plugin_directory . '/front-end/renders/hbook-render.php';
		require_once $this->utils->plugin_directory . '/front-end/renders/availability-render.php';
		$availability = new HBookAvailability( $this->hbdb, $this->utils );
		return $availability->render( $atts );
	}

	public function rates( $atts ) {
		$atts = shortcode_atts(
			array(
				'accom_id' => '',
				'type' => 'normal', // 'normal', 'adult', 'child'
				'days' => '',
				'season' => '',
				'seasons' => '',
				'rule' => '',
				'show_global_price' => 'no',
				'nights' => '0',
				'custom_text_after_amount' => '',
				'show_season_name' => 'yes',
				'chronological' => 'no',
			),
			$atts,
			'hb_rates'
		);

		$render_atts = $atts;
		$render_atts['type'] = 'accom';
		if ( $atts['type'] == 'adult' ) {
			$render_atts['type'] = 'extra_adults';
		} else if ( $atts['type'] == 'child' ) {
			$render_atts['type'] = 'extra_children';
		}
		if ( $atts['chronological'] == 'yes' ) {
			$render_atts['chrono'] = true;
		} else {
			$render_atts['chrono'] = false;
		}
		if ( $atts['show_season_name'] == 'yes') {
			$render_atts['show_season_name'] = true;
		} else {
			$render_atts['show_season_name'] = false;
		}
		if ( $atts['show_global_price'] == 'yes') {
			$render_atts['show_global_price'] = true;
		} else {
			$render_atts['show_global_price'] = false;
		}

		require_once $this->utils->plugin_directory . '/front-end/renders/hbook-render.php';
		require_once $this->utils->plugin_directory . '/front-end/renders/rates-render.php';
		$rates = new HBookRates( $this->hbdb, $this->utils );
		return $rates->render( $render_atts );
	}

	public function booking_form( $atts ) {
		$atts = shortcode_atts(
			array(
				'form_id' => '',
				'all_accom' => '', // '', 'yes'
				'search_only' => 'no',
				'search_form_placeholder' => 'no',
				'accom_id' => '',
				'redirection_url' => '#',
				'force_display_thumb' => 'no',
				'force_display_desc' => 'no',
			),
			$atts,
			'hb_booking_form'
		);
		$atts['is_admin'] = 'no';

		require_once $this->utils->plugin_directory . '/front-end/renders/hbook-render.php';
		require_once $this->utils->plugin_directory . '/front-end/renders/booking-form-render.php';
		$booking_form = new HBookBookingForm( $this->hbdb, $this->utils );
		return $booking_form->render( $atts );
	}
}