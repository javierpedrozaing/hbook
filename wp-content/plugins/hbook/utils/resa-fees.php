<?php
class HbResaFees {

	private $hbdb;
	private $utils;

	public function __construct( $hbdb, $utils ) {
		$this->hbdb = $hbdb;
		$this->utils = $utils;
	}

	public function get_fees_markup_frontend() {
		return $this->get_fees_markup( false );
	}

	public function get_fees_markup_backend() {
		return $this->get_fees_markup( true );
	}

	private function get_fees_markup( $is_admin ) {
		$output = '';
		$fees = $this->hbdb->get_final_fees();
		foreach ( $fees as $fee ) {
			$output .= $this->get_fee_markup( $fee, $is_admin );
		}
		if ( ! $is_admin && $output ) {
			$output = '<br/>' . $output;
		}
		$output .= '<div class="hb-fee-accom-not-included-wrapper"></div>';
		return $output;
	}

	public function get_accom_included_fees_markup() {
		$output = '<div class="hb-fee-accom-included-wrapper">';
		$output .= $this->get_included_fee_intro_text( 'accom' );
		$output .= '<div class="hb-fee-accom-included-subwrapper">';
		$output .= '</div>';
		$output .= '</div>';
		return $output;
	}

	public function get_extras_included_fees_markup() {
		$output = '';
		$fees = $this->hbdb->get_extras_included_fees();
		foreach ( $fees as $fee ) {
			$output .= $this->get_fee_markup( $fee, false );
		}
		if ( $output ) {
			$output = $this->get_included_fee_intro_text( 'extras' ) . $output;
		}
		return $output;
	}

	public function get_global_included_fees_markup() {
		$output = '';
		$fees = $this->hbdb->get_global_included_fees();
		foreach ( $fees as $fee ) {
			$output .= $this->get_fee_markup( $fee, false );
		}
		if ( $output ) {
			$output = $this->get_included_fee_intro_text( 'global' ) . $output;
		}
		return $output;
	}

	private function get_included_fee_intro_text( $type ) {
		$output = '<div class="hb-included-fees-title hb-included-fees-title-' . $type . '">';
		$output .= '<small>';
		$output .= $this->hbdb->get_string( 'summary_included_fees' );
		$output .= '</small>';
		$output .= '</div>';
		return $output;
	}

	private function get_fee_markup( $fee, $is_admin ) {
		$output = '';
		$fee_display_name = '';
		if ( ! $is_admin ) {
			$fee_display_name = $this->hbdb->get_string( 'fee_' . $fee['id'] );
		}
		if ( ! $fee_display_name ) {
			$fee_display_name = $fee['name'];
		}
		$fee_display_name = str_replace( ':', '', $fee_display_name );
		if (
			( $fee['apply_to_type'] == 'global-percentage' ) ||
			( $fee['apply_to_type'] == 'accom-percentage' ) ||
			( $fee['apply_to_type'] == 'extras-percentage' )
		) {
			$fee_display_name .=' (' . $fee['amount'] . '%';
			$fee_display_name .=' x ';
			$fee_display_name .= $this->utils->price_placeholder( 'hb-fee-base' );
			$fee_display_name .= ') :';
		} else {
			$fee_display_name .= ':';
		}
		$fee_tag_begin = '';
		$fee_tag_end = '';
		$fee_class = 'hb-fee';
		if ( $fee['include_in_price'] == 2 ) {
			$fee_class .= ' hb-fee-included';
			$fee_tag_begin = '<small>';
			$fee_tag_end = '</small>';
		} else {
			$fee_class .= ' hb-fee-not-included';
		}
		if ( $fee['apply_to_type'] == 'global-percentage' ) {
			$fee_class .= ' hb-fee-percentage';
		} else if ( $fee['apply_to_type'] == 'accom-percentage' ) {
			$fee_class .= ' hb-fee-accom-percentage';
			if ( $fee['all_accom'] ) {
				$fee_class .= ' hb-fee-accom-percentage-all-accom';
			} else if ( $fee['accom'] ) {
				$fee_accom = explode( ',', $fee['accom'] );
				foreach ( $fee_accom as $accom_id ) {
					$fee_class .= ' hb-fee-accom-percentage-accom-' . $accom_id;
				}
			}
		} else if ( $fee['apply_to_type'] == 'extras-percentage' ) {
			$fee_class .= ' hb-fee-extras-percentage';
		}
		$output = '<div class="' . $fee_class . '" data-price="' . $fee['amount'] . '">';
		$output .= $fee_tag_begin;
		$output .=  $fee_display_name;
		$output .= ' ';
		$output .= $this->utils->price_placeholder( 'hb-fee-price' );
		$output .= $fee_tag_end;
		$output .= '</div>';
		return $output;
	}
}