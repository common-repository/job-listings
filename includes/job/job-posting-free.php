<?php

function jlt_posting_free_admin_init() {
	add_action( 'jlt_job_reset_job_counter', 'jlt_job_reset_free_posting_counter' );
}

add_filter( 'admin_init', 'jlt_posting_free_admin_init' );

function jlt_job_reset_free_posting_counter() {
	update_option( 'jlt_time_reset_job_counter', time() );

	// Reset code
	$administrator_list = jlt_get_member_ids( 'administrator' );
	$employer_list      = jlt_get_member_ids( JLT_Member::EMPLOYER_ROLE );
	$employer_list      = array_merge( $administrator_list, $employer_list );
	foreach ( $employer_list as $user_id ) {
		if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
			$job_added    = get_user_meta( $user_id, '_job_added', true );
			$job_featured = get_user_meta( $user_id, '_job_featured', true );
			error_log( "User_ID: $user_id - Job Added: {$job_added} - Job Featured: {$job_featured}" );
		}
		update_user_meta( $user_id, '_job_added', '0' );
		update_user_meta( $user_id, '_job_featured', '0' );
	}

	$job_posting_reset = jlt_get_job_setting( 'job_posting_reset', 0 );
	$job_posting_reset = absint( $job_posting_reset );
	if ( ! empty( $job_posting_reset ) ) {
		$next_reset_time = strtotime( 'first day of this month 00:00:00', strtotime( '+' . $job_posting_reset . ' month', time() ) );
		wp_schedule_single_event( $next_reset_time, 'jlt_job_reset_job_counter' );
	}
}

function jlt_job_posting_free_setting_changed() {
	$job_posting_reset = jlt_get_job_setting( 'job_posting_reset', 0 );
	$job_posting_reset = absint( $job_posting_reset );

	if ( ! empty( $job_posting_reset ) ) {
		$time_reset_job_counter = get_option( 'jlt_time_reset_job_counter' );
		$time_reset_job_counter = empty( $time_reset_job_counter ) ? time() : $time_reset_job_counter;

		$next_reset_time = strtotime( 'first day of this month 00:00:00', strtotime( '+' . $job_posting_reset . ' month', $time_reset_job_counter ) );

		wp_clear_scheduled_hook( 'jlt_job_reset_job_counter' );
		wp_schedule_single_event( $next_reset_time, 'jlt_job_reset_job_counter' );
	} else {
		delete_option( 'jlt_time_reset_job_counter' );
		wp_clear_scheduled_hook( 'jlt_job_reset_job_counter' );
	}
}

add_action( 'jlt_job_setting_changed', 'jlt_job_posting_free_setting_changed' );
