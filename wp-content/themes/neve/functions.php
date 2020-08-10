<?php
/**
 * Neve functions.php file
 *
 * Author:          Andrei Baicus <andrei@themeisle.com>
 * Created on:      17/08/2018
 *
 * @package Neve
 */

define( 'NEVE_VERSION', '2.7.5' );
define( 'NEVE_INC_DIR', trailingslashit( get_template_directory() ) . 'inc/' );
define( 'NEVE_ASSETS_URL', trailingslashit( get_template_directory_uri() ) . 'assets/' );

if ( ! defined( 'NEVE_DEBUG' ) ) {
	define( 'NEVE_DEBUG', false );
}
define( 'NEVE_NEW_DYNAMIC_STYLE', true );
/**
 * Themeisle SDK filter.
 *
 * @param array $products products array.
 *
 * @return array
 */
function neve_filter_sdk( $products ) {
	$products[] = get_template_directory() . '/style.css';

	return $products;
}

add_filter( 'themeisle_sdk_products', 'neve_filter_sdk' );

add_filter( 'themeisle_onboarding_phprequired_text', 'neve_get_php_notice_text' );

/**
 * Get php version notice text.
 *
 * @return string
 */
function neve_get_php_notice_text() {
	$message = sprintf(
	/* translators: %s message to upgrade PHP to the latest version */
		__( "Hey, we've noticed that you're running an outdated version of PHP which is no longer supported. Make sure your site is fast and secure, by %s. Neve's minimal requirement is PHP 5.4.0.", 'neve' ),
		sprintf(
		/* translators: %s message to upgrade PHP to the latest version */
			'<a href="https://wordpress.org/support/upgrade-php/">%s</a>',
			__( 'upgrading PHP to the latest version', 'neve' )
		)
	);

	return wp_kses_post( $message );
}

/**
 * Adds notice for PHP < 5.3.29 hosts.
 */
function neve_php_support() {
	printf( '<div class="error"><p>%1$s</p></div>', neve_get_php_notice_text() ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
}

if ( version_compare( PHP_VERSION, '5.3.29' ) <= 0 ) {
	/**
	 * Add notice for PHP upgrade.
	 */
	add_filter( 'template_include', '__return_null', 99 );
	switch_theme( WP_DEFAULT_THEME );
	unset( $_GET['activated'] ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended
	add_action( 'admin_notices', 'neve_php_support' );

	return;
}

require_once 'globals/migrations.php';
require_once 'globals/utilities.php';
require_once 'globals/hooks.php';
require_once 'globals/sanitize-functions.php';
require_once get_template_directory() . '/start.php';


require_once get_template_directory() . '/header-footer-grid/loader.php';


	
function get_accommodations_current_cpt() {
	$id_array_in_cpt = array();
	$args = array(
		'post_type' 	 => 'hb_accommodation',
		'posts_per_page' => -1,			
	);

	$query = new WP_Query($args);
	while ($query->have_posts()) {
		$query->the_post();
		$id_array_in_cpt[] = get_post_meta(get_the_ID(), 'id', true);
	}

	return $id_array_in_cpt;
}

function query_accommodations_similar_ids($accom) {
	global $wpdb;
	$table_name = $wpdb->prefix . 'hb_accommodations';

	if ($accom == null | $accom == 0 || $accom == '0' || empty($accom) ) {
		$results = $wpdb->get_results("SELECT * FROM $table_name");		
		return $results;
	} else {			
		$ids = implode("," , $accom);
		$sql = "SELECT * FROM $table_name WHERE id NOT IN ($ids)";
		$results = $wpdb->get_results($sql);
					
		return $results;	
	}
	
}


function get_accommodations_ids_custom_table() {
	global $wpdb;
	$table_name = $wpdb->prefix . 'hb_accommodations';			
	$sql = "SELECT * FROM $table_name";
	$results = $wpdb->get_results($sql);						

	return $results;	
	
}


add_action( 'hb_insert_accommodations_from_custom_table', 'hb_insert_accommodations_from_custom_table' );

function hb_insert_accommodations_from_custom_table() {
		
	$accommodations = get_accommodations_current_cpt();
	$database_results = query_accommodations_similar_ids($accommodations);	
	
	if ($accommodations == null | $accommodations == 0 || $accommodations == '0' || empty($accommodations) ) {
		
		// if the custom post type hb_accommodations IDS is empty insert data			
		foreach ($database_results as $key => $result) {
			$accommodations = array(
				'post_title' => wp_strip_all_tags($result->name), 
				'meta_input' => array(
					'id' => $result->id,
					'name' => wp_strip_all_tags($result->name), 
					'accom_quantity' => $result->quantity,				
					'accom_occupancy' => $result->occupancy,
					'accom_max_occupancy' => $result->max_occupancy,
					'accom_min_occupancy' => $result->min_occupancy,
					'accom_search_result_desc' => $result->search_result_desc,
					'accom_list_desc' => $result->list_desc,
					'accom_short_name' => $result->short_name,
					'accom_starting_price' => $result->starting_price,
					'accom_preparation_time' => $result->preparation_time,				
				),
				'post_type' => 'hb_accommodation',
				'post_status' => 'publish',

			);
			$new_id_accom = wp_insert_post($accommodations);
			
			if ($new_id_accom) {
				update_field( 'id_accommodation', $result->id, $new_id_accom );            
			}
			
		}	
	}			
}


add_action( 'hb_update_accommodations_from_custom_table', 'hb_update_accommodations_from_custom_table' );

function hb_update_accommodations_from_custom_table() {
	$accommodations = get_accommodations_ids_custom_table();		
	
	$accom_ids = function($accom){
		return $accom->id;
	};

	$ids_accommodations = array_map($accom_ids, $accommodations);
	//print_r($ids_accommodations);exit;
	$my_acccom = new WP_Query(array(       
		'posts_per_page' =>  -1,
		'post_type'		=> 'hb_accommodation',      
		'post_status'  => 'publish',      			
		'meta_query'	=> array(
			array(
				'key' => 'id_accommodation',
				'value' => $ids_accommodations,
				'compare' => 'IN'
			),
		),
	));

	$my_acccom  = (array) $my_acccom;

		foreach ($my_acccom['posts'] as $key => $accom) {			
			if (isset( $accom->ID)) {
				$new_accom_post_id = wp_update_post(
					array(
						'ID'    =>   $accom->ID, 
						'comment_status'    =>  'closed',
						'ping_status'       =>  'closed',
						'post_content' =>  '',
						'post_author'       =>  1,                    
						'post_title'        => $accommodations[$key]->name,
						'post_status'       =>  'publish',
						'post_type'     =>  'hb_accommodation',                  
					)
				);
			}

			if ($new_accom_post_id) {				
				update_post_meta( $new_accom_post_id, '_visibility', 'visible' );   
				update_field( 'accom_quantity',  wp_strip_all_tags($accommodations[$key]->quantity),  $new_accom_post_id );
			} 
		}
	
}


//INSERT AND UPDATE CUSTOM TABLE FROM TYPE CONTENT
function get_relation_id_accommodation($ids_accom) {
	global $wpdb;
	$table_name = $wpdb->prefix . 'hb_accommodations';	
	$ids = implode("," , $ids_accom);		
	$sql = "SELECT * FROM $table_name WHERE id_hb_accommodation IN ($ids)";
	$results = $wpdb->get_results($sql);						

	return $results;
}

function get_all_current_accommodations() {
	$args = array(
		'post_type' 	 => 'hb_accommodation',
		'posts_per_page' => -1,		
		'post_status' => 'publish',	
	);

	$query = new WP_Query($args);
	return  (array) $query;
}
	
add_action( 'hb_update_accommodations_from_CPT', 'hb_update_accommodations_from_CPT' );

function hb_update_accommodations_from_CPT() {	
	global $wpdb;
	$get_accommodations = get_all_current_accommodations();
	
	$table_name = $wpdb->prefix . 'hb_accommodations';		
	$get_ids = function($accom){
		return $accom->ID;
	};
	
	$ids_accommodations = array_map($get_ids, $get_accommodations['posts']);
	
	$get_relation_in_custom_table = get_relation_id_accommodation(array_filter($ids_accommodations));

	$new_id_accom = [];
	
	if (!empty($get_relation_in_custom_table)) {
		foreach ($get_relation_in_custom_table as $key => $accom) {
			//echo get_post_meta($get_accommodations['posts'][$key]->ID, 'accom_quantity', true);exit;
			$accommodations = array(
				'id' => $get_accommodations['posts'][$key]->ID,					
				'name' => $get_accommodations['posts'][$key]->post_title, 
				'quantity' => get_post_meta($get_accommodations['posts'][$key]->ID, 'accom_quantity', true),			
				'occupancy' => get_post_meta($get_accommodations['posts'][$key]->ID, 'accom_occupancy', true),
				'max_occupancy' => get_post_meta($get_accommodations['posts'][$key]->ID, 'accom_max_occupancy', true),
				'min_occupancy' => get_post_meta($get_accommodations['posts'][$key]->ID, 'accom_min_occupancy', true),
				'search_result_desc' => get_post_meta($get_accommodations['posts'][$key]->ID, 'accom_search_result_desc', true),
				'list_desc' => get_post_meta($get_accommodations['posts'][$key]->ID, 'accom_list_desc', true),
				'short_name' => get_post_meta($get_accommodations['posts'][$key]->ID, 'accom_short_name', true),
				'starting_price' => get_post_meta($get_accommodations['posts'][$key]->ID, 'accom_starting_price', true),
				'preparation_time' => get_post_meta($get_accommodations['posts'][$key]->ID, 'accom_preparation_time', true),
			);	

			$where = ['id_hb_accommodation' =>$get_accommodations['posts'][$key]->ID];
			
			//$format = array('%s','%s','%s','%s','%s','%s','%s','%s','%s','%s');
			$new_id_accom[] = $wpdb->update($table_name,$accommodations, $where);			
		}

	} else {			
		$new_id_accom[] = hb_insert_accommodations_from_CPT($get_accommodations['posts'], $get_relation_in_custom_table);
		
	}

	var_dump($new_id_accom);
	
}

function hb_insert_accommodations_from_CPT($post_type_accom, $custom_table_accom) {
	
	global $wpdb;
	$table_name = $wpdb->prefix . 'hb_accommodations';	
	$new_id_accom = [];
	

	$get_ids = function($post_type_accom){
		return $post_type_accom->ID;
	};
	
	$get_current_ids = array_map($get_ids, $post_type_accom);
	$current_ids = implode("," , $get_current_ids);	
	$exists = $wpdb->get_var( $wpdb->prepare(
	  "SELECT count(*) FROM $table_name WHERE id_hb_accommodation IN  ($current_ids) "
	) );

	if ($exists) {
		return;
	} else {
		foreach ($post_type_accom as $key => $accom) {
			//echo get_post_meta($get_accommodations['posts'][$key]->ID, 'accom_quantity', true);exit;
			$accommodations = array(								
				'quantity' => get_post_meta($accom->ID, 'accom_quantity', true),			
				'occupancy' => get_post_meta($accom->ID, 'accom_occupancy', true),
				'max_occupancy' => get_post_meta($accom->ID, 'accom_max_occupancy', true),
				'min_occupancy' => get_post_meta($accom->ID, 'accom_min_occupancy', true),
				'search_result_desc' => get_post_meta($accom->ID, 'accom_search_result_desc', true),
				'list_desc' => get_post_meta($accom->ID, 'accom_list_desc', true),
				'short_name' => get_post_meta($accom->ID, 'accom_short_name', true),
				'starting_price' => get_post_meta($accom->ID, 'accom_starting_price', true),
				'preparation_time' => get_post_meta($accom->ID, 'accom_preparation_time', true),
				'id_hb_accommodation' => $accom->ID,
				'name' => $accom->post_title, 	
			);	
		
			$format = array('%d','%s','%s','%s','%s','%s','%s','%s','%s','%s','%s','%d');
			$new_id_accom[] = $wpdb->insert($table_name,$accommodations, $format);			
		}
	}

	return $new_id_accom;
}





