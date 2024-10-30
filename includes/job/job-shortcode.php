<?php
/**
 * Job Shortcode
 *
 * @since  : 0.1.0
 */

function jlt_job_shortcode_listings( $atts ) {

	$atts = shortcode_atts( array(), $atts, 'jlt_job_listings' );

	$job_query = jlt_job_listings( $atts );

	ob_start();

	jlt_get_template( 'job/loop/loop-before.php', compact( 'job_query' ) );

	if ( $job_query->have_posts() ) {

		while ( $job_query->have_posts() ) {

			// Setup listing data
			$job_query->the_post();

			global $post;
			jlt_setup_job_data( $post );
			jlt_get_template( 'content-job.php' );
		}
	} else {
		jlt_get_template( 'job/loop/not-founds.php' );
	}

	jlt_get_template( 'job/loop/loop-after.php', compact( 'job_query' ) );

	wp_reset_query();

	return ob_get_clean();
}

add_shortcode( 'jlt_job_listings', 'jlt_job_shortcode_listings' );

function jlt_job_shortcode_listings_map( $atts, $content = null ) {

	jlt_job_enqueue_map_script();

	$agrs = shortcode_atts( array(
		'map_height' => '500',
		'map_style'  => jlt_google_map_style(),
		'zoom'       => '12',
		'lat'        => '40.714398',
		'long'       => '-74.005279',
		'fit_bounds' => 'yes',
	), $atts );

	ob_start();

	jlt_get_template( 'job/job-listings-map.php', $agrs );

	return ob_get_clean();
}

add_shortcode( 'jlt_job_listings_map', 'jlt_job_shortcode_listings_map' );