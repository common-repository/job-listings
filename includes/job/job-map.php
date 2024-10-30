<?php
/**
 * job-map.php
 *
 * @since  : 0.1.0
 * @version: 0.1.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

function jlt_job_enqueue_map_script() {

	// prevent conflict with Ultimate VC Add-ons
	define( 'DISABLE_ULTIMATE_GOOGLE_MAP_API', true );

	wp_register_script( 'google-map-infobox', JLT_PLUGIN_URL . 'public/js/gmap_infobox.js', array( 'jquery' , 'google-map' ), null, true );
	wp_register_script( 'google-map-markerclusterer',JLT_PLUGIN_URL . 'public/js/gmap_markerclusterer.js', array( 'jquery' , 'google-map' ), null, true );
	
	wp_register_script( 'job-map', JLT_PLUGIN_URL . 'public/js/job-map.js', array(
		'google-map-infobox',
		'google-map-markerclusterer',
	), null, true );
	$job_gmap = array(
		'zoom'          => 10,
		'latitude'      => 40.714398,
		'longitude'     => - 74.005279,
		'draggable'     => 0,
		'marker_icon'   => JLT_PLUGIN_URL . 'public/images/map-marker-icon.png',
		'marker_cloud'   => JLT_PLUGIN_URL . 'public/images/map-cloud.png',
		'marker_data'   => jlt_job_build_map_data(),
		'primary_color' => jlt_default_primary_color(),
	);
	wp_localize_script( 'job-map', 'JLT_JobGmap', $job_gmap );

	wp_enqueue_script( 'job-map' );
	wp_enqueue_script( 'google-map-custom' );
}