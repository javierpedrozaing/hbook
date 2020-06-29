<?php
class HBookAccomList extends HBookRender {

	public function render( $atts ) {
		$this->utils->load_jquery();
		$this->utils->load_front_end_script( 'accommodation-list' );
		$output = '<div class="hb-accom-list-shortcode-wrapper">';
		$accom = $this->hbdb->get_all_accom_ids();
		if ( $atts['accom_ids'] != '' ) {
			$accom_type_ids = explode( ',', $atts['accom_ids'] );
			foreach ( $accom as $key => $accom_id ) {
				if ( false === array_search( $accom_id, $accom_type_ids ) ) {
					unset( $accom[ $key ] );
				}
			}
		}
		$nb_columns = intval( $atts['nb_columns'] );
		$percent = round( 100 / $nb_columns );
		$i = 1;
		$output .= '<div class= "hb-accom-list-row">';
		foreach( $accom as $accom_id ) {
			$output .= '<div class ="hb-accom-list-column" style = "max-width: ' . $percent . '%; min-width: 300px;">';
			$output .= '<div class="hb-accom-list-item hb-accom-list-item-' . $accom_id . '">';
			if ( $atts['show_thumb'] ) {
				$thumb_mark_up = $this->utils->get_thumb_mark_up( $accom_id, $atts['thumb_width'], $atts['thumb_height'], 'hb-accom-list-thumb alignleft' );
				if ( $thumb_mark_up && $atts['link_thumb'] ) {
					$output .= '<a class="hb-thumbnail-link" href="' . $this->utils->get_accom_link( $accom_id ) . '">' . $thumb_mark_up . '</a>';
				} else {
					$output .= $thumb_mark_up;
				}
			}
			if ( in_array( $atts['title_tag'], array( 'h1', 'h2', 'h3', 'h4', 'h5', 'h6' ) ) ) {
				$title_tag = $atts['title_tag'];
			} else {
				$title_tag = 'h2';
			}
			$output .= '<' . $title_tag . '>';
			$output .= '<a href="' . $this->utils->get_accom_link( $accom_id ) . '">';
			$output .= $this->utils->get_accom_title ( $accom_id );
			$output .= '</a>';
			$output .= '</' . $title_tag . '>';
			$starting_price = get_post_meta( $accom_id, 'accom_starting_price', true );
			if ( $starting_price ) {
				$starting_price_text = str_replace( '%price', $this->utils->price_with_symbol( $starting_price ), $this->strings['accom_starting_price'] );
				$starting_price_text .= ' ' . $this->strings['accom_starting_price_duration_unit'];
				$output .=  '<p><small>' . $starting_price_text . '</small></p>';
			}
			$output .= '<p>' . $this->utils->get_accom_list_desc( $accom_id ) . '</p>';
			if ( $atts['book_button'] ) {
				$output .= '<p class="hb-accom-list-view-accom"><input type="submit" value="' . $this->strings['accom_book_now_button'] . '" /></p>';
				if ( $atts['redirection_url'] != '#' ) {
					$booking_form_shortcode = '[hb_booking_form accom_id="' . $accom_id . '" redirection_url="' . $atts['redirection_url'] . '"]';
				} else {
					$booking_form_shortcode = '[hb_booking_form accom_id="' . $accom_id . '"]';
				}
				$output .= '<div class="hb-clearfix" /></div>';
				$output .= '<div class="hb-accom-list-booking-form list-booking-form-' . $accom_id . '">' . do_shortcode( $booking_form_shortcode ) . '</div>';
			} else {
				$output .= '<div class="hb-clearfix" /></div>';
			}
			$output .= '</div></div>';

			if ( $i == count( $accom ) ) {
				$output .= '</div>';
			}
			$i++;
		}

		$output .= '</div>';

		$output = apply_filters( 'hb_accommodation_list_markup', $output );

		return $output;
	}
}