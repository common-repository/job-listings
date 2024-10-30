<?php

function jlt_job_expired_set_schedule() {
	if ( get_option( 'jlt_job_cron_jobs' ) == '1' && ( wp_get_schedule( 'jlt_job_check_expired_jobs' ) !== false ) ) {
		return;
	}
	wp_clear_scheduled_hook( 'jlt_job_check_expired_jobs' );
	wp_schedule_event( time(), 'hourly', 'jlt_job_check_expired_jobs' );

	delete_option( 'jlt_job_cron_jobs' );
	update_option( 'jlt_job_cron_jobs', '1' );
}

add_action( 'admin_init', 'jlt_job_expired_set_schedule' );

function jlt_job_expired_cron_action() {
	global $wpdb;

	// Change status to expired
	$job_ids = $wpdb->get_col( $wpdb->prepare( "
			SELECT postmeta.post_id FROM {$wpdb->postmeta} as postmeta
			LEFT JOIN {$wpdb->posts} as posts ON postmeta.post_id = posts.ID
			WHERE postmeta.meta_key = '_expires'
			AND postmeta.meta_value > 0
			AND postmeta.meta_value < %s
			AND posts.post_status IN ( 'publish', 'inactive' )
			AND posts.post_type = 'job'
			", current_time( 'timestamp' ) ) );

	if ( $job_ids ) {
		foreach ( $job_ids as $job_id ) {
			$job_data                  = array();
			$job_data[ 'ID' ]          = $job_id;
			$job_data[ 'post_status' ] = 'expired';
			wp_update_post( $job_data );
		}
	}
}

add_action( 'jlt_job_check_expired_jobs', 'jlt_job_expired_cron_action' );

function jlt_set_job_expired( $job_id = '' ) {
	if ( empty( $job_id ) ) {
		return false;
	}

	$_ex         = jlt_get_post_meta( $job_id, '_expires' );
	$employer_id = get_post_field( 'post_author', $job_id );
	if ( empty( $_ex ) && $package = jlt_get_job_posting_info( $employer_id ) ) {
		$_expires = strtotime( '+' . absint( @$package[ 'job_duration' ] ) . ' day' );
		update_post_meta( $job_id, '_expires', $_expires );
		$closing = jlt_get_post_meta( $job_id, '_closing' );
		if ( empty( $closing ) ) {
			$closing = $_expires;
			update_post_meta( $job_id, '_closing', $_expires );
		}
	}
}