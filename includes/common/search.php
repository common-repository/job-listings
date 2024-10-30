<?php

function jlt_job_search_ajax() {

	global $wp;

	$args[ 'keyword' ]     = $_GET[ 'keyword' ];
	$args[ 'post_status' ] = 'publish';

	$args[ 'is_job_ajax_search' ] = true;

	$args[ 'paged' ] = $_REQUEST[ 'page' ];

	$job_query = jlt_job_listings( $wp->query_vars, $args );

	// Job list
	ob_start();
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

	$list_job = ob_get_clean();

	// Paging
	ob_start();
	jlt_show_paging( $job_query );
	$paging = ob_get_clean();

	$job_count_founds = $job_query->found_posts;
	$job_count        = sprintf( _n( "%s job", "%s jobs", $job_count_founds, 'job-listings' ), $job_count_founds );
	$rs               = array(
		'list_job'  => $list_job,
		'job_count' => $job_count,
		'paging'    => $paging,
	);

	wp_reset_query();
	wp_send_json( $rs );
}

add_action( 'wp_ajax_nopriv_jlt_job_search_ajax', 'jlt_job_search_ajax' );
add_action( 'wp_ajax_jlt_job_search_ajax', 'jlt_job_search_ajax' );
