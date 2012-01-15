<?php
/*
Plugin Name: CDBS 
Description: Stores CDBS data
Author: Benjamin Balter
Version: 1.0
Author URI: http://ben.balter.com/
*/

class CDBS {
	
	//array of fields to store as taxonomies (default is postmeta)
	public $facility_taxs = array( 'comm_city', 'comm_state', 'fac_service', 'fac_state', 'fac_type', 'fac_status', 'fac_zip', 'station_type', 'fac_city', 'fac_country' );
	
	/**
	 * Register hooks with WP
	 */
	function __construct() {
		add_action( 'init', array( &$this, 'register_facilities_cpt' ) );
		add_action( 'init', array( &$this, 'register_facilities_cts' ) );
		add_filter( 'the_content', array( &$this, 'content_filter' ) );
		add_filter( 'cdbs_postmeta_key', array( &$this, 'postmeta_key_filter' ) );
		add_action( 'wp_enqueue_scripts', array( &$this, 'enqueue_css' ) );
	}
	
	/**
	 * Creates facility custom post type
	 */
	function register_facilities_cpt() {
	
		$labels = array( 
    	    'name' => _x( 'Facilities', 'facility' ),
    	    'singular_name' => _x( 'Facility', 'facility' ),
    	    'add_new' => _x( 'Add New', 'facility' ),
    	    'add_new_item' => _x( 'Add New Facility', 'facility' ),
    	    'edit_item' => _x( 'Edit Facility', 'facility' ),
    	    'new_item' => _x( 'New Facility', 'facility' ),
    	    'view_item' => _x( 'View Facility', 'facility' ),
    	    'search_items' => _x( 'Search Facilities', 'facility' ),
    	    'not_found' => _x( 'No facilities found', 'facility' ),
    	    'not_found_in_trash' => _x( 'No facilities found in Trash', 'facility' ),
    	    'parent_item_colon' => _x( 'Parent Facility:', 'facility' ),
    	    'menu_name' => _x( 'Facilities', 'facility' ),
    	);
		
    	$args = array( 
    	    'labels' => $labels,
    	    'hierarchical' => true,
    	    'supports' => array( 'title', 'custom-fields', 'comments' ),
    	    'public' => true,
    	    'show_ui' => true,
    	    'show_in_menu' => true,
    	    'show_in_nav_menus' => true,
    	    'publicly_queryable' => true,
    	    'exclude_from_search' => false,
    	    'has_archive' => true,
    	    'query_var' => true,
    	    'can_export' => true,
    	    'rewrite' => true,
    	    'capability_type' => 'post'
    	);
		
    	register_post_type( 'facility', $args );
	
	}

	/**
	 * Registers custom taxonomies for facility post type
	 */
	function register_facilities_cts() {
 
 		foreach ( $this->facility_taxs as $tax ) {
 			$name = ucwords( str_replace( '_', ' ', $tax) );
			$labels = array(
			    'name' => $name . 's',
			    'singular_name' => $name,
			    'search_items' =>  'Search ' . $name . 's',
			    'all_items' => 'All ' . $name . 's',
			    'edit_item' => 'Edit ' . $name,
			    'update_item' => 'Update ' . $name,
			    'add_new_item' => 'Add New ' . $name,
			    'new_item_name' => 'New ' . $name . ' name',
			    'menu_name' => $name,
			  );
			  register_taxonomy( $tax, 'facility', array( 'labels' => $labels ) );
 		}
 	}
 	
 	function content_filter( $content ) {
 		global $post;
 		
 		$output = array();
 		
 		$taxs = get_taxonomies( array( 'object_type' => array( $post->post_type ) ) );

 		foreach ( $taxs as $tax ) { 
 			$tax = get_taxonomy( $tax );
			$terms = get_the_term_list( $post->ID, $tax->name, null, ', ' );
			$output[ $tax->labels->singular_name] = $terms;
 		}
 		
 		$postmeta = get_post_custom( $post->ID );
		foreach ( $postmeta as $key => $value ) {
			$key = apply_filters( 'cdbs_postmeta_key', $key, $value );
			$output[ $key ] = $value[0];
		}
		
		ksort( $output );
		
		$content .= '<div class="postmeta">';
		
		foreach ( $output as $label => $value ) {
			$content .= '<div class="postmeta-row">';
			$content .= '<div class="postmeta-label">' . $label . ':</div>';
			$content .= '<div class="postmeta-value">' . $value . '</div>';
			$content .= '</div>';
		}
		
		$content .= '<div class="postmeta-row">&nbsp;</div></div>';
		
 		return $content;
 	}
 	
 	/**
 	 * Tell WP to load our CSS file
 	 */
 	function enqueue_css() {
 		
 		wp_enqueue_style( 'cdbs', plugins_url( 'style.css', __FILE__ ) );
 		
 	}
 	
 	/**
 	 * Formats the postmeta key in a human-readable format
 	 */
 	function postmeta_key_filter( $key ) {
 		return ucwords( str_replace( '_', ' ', $key ) );
 	}
 	
}

$cdbs = new CDBS();