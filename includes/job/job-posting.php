<?php
/**
 * Job Posting.
 *
 * @since 1.0.0
 */

function jlt_is_job_posting_page( $page_id = '' ) {
	$page_id = empty( $page_id ) ? get_the_ID() : $page_id;
	if ( empty( $page_id ) ) {
		return false;
	}

	$page_setting = jlt_get_job_setting( 'job_post_page' );
	if ( empty( $page_setting ) ) {
		return false;
	}

	return $page_id == $page_setting;
}

function jlt_get_job_posting_added( $user_id = null ) {
	if ( $user_id === null ) {
		$user_id = get_current_user_id();
	}

	if ( empty( $user_id ) ) {
		return 0;
	}

	$job_added = get_user_meta( $user_id, '_job_added', true );

	return empty( $job_added ) ? 0 : absint( $job_added );
}

function jlt_get_job_posting_info( $user_id = null ) {
	if ( $user_id === null ) {
		$user_id = get_current_user_id();
	}

	if ( empty( $user_id ) ) {
		return null;
	}

	$posting_info = array(
		'job_duration'  => absint( jlt_get_job_setting( 'job_display_duration', 30 ) ),
		'job_limit'     => absint( jlt_get_job_setting( 'job_posting_limit', 5 ) ),
		'job_featured'  => absint( jlt_get_job_setting( 'job_feature_limit', 1 ) ),
		'counter_reset' => absint( jlt_get_job_setting( 'job_posting_reset', 0 ) ),
	);

	return apply_filters( 'jlt_job_posting_info', $posting_info, $user_id );
}

function jlt_increase_job_posting_count( $user_id = null ) {
	if ( $user_id === null ) {
		$user_id = get_current_user_id();
	}

	if ( empty( $user_id ) ) {
		return false;
	}

	$_count = jlt_get_job_posting_added( $user_id );
	update_user_meta( $user_id, '_job_added', $_count + 1 );
}

function jlt_decrease_job_posting_count( $user_id = null ) {
	if ( $user_id === null ) {
		$user_id = get_current_user_id();
	}

	if ( empty( $user_id ) ) {
		return false;
	}

	$_count = jlt_get_job_posting_added( $user_id );
	update_user_meta( $user_id, '_job_added', max( 0, $_count - 1 ) );
}

function jlt_get_feature_job_remain( $user_id = null ) {
	if ( $user_id === null ) {
		$user_id = get_current_user_id();
	}

	if ( empty( $user_id ) ) {
		return 0;
	}

	$current_feature_count = jlt_get_feature_job_added( $user_id );

	$package = jlt_get_job_posting_info( $user_id );
	if ( empty( $package ) || ! isset( $package[ 'job_featured' ] ) ) {
		return 0;
	}

	return max( absint( $package[ 'job_featured' ] ) - absint( $current_feature_count ), 0 );
}

function jlt_get_feature_job_added( $user_id = null ) {
	if ( $user_id === null ) {
		$user_id = get_current_user_id();
	}

	if ( empty( $user_id ) ) {
		return 0;
	}

	return absint( get_user_meta( $user_id, '_job_featured', true ) );
}

function jlt_can_post_job( $user_id = null ) {
	$result = true;
	if ( empty( $user_id ) ) {
		$user_id = get_current_user_id();
	}
	if ( ! JLT_Member::is_employer( $user_id ) ) {
		$result = false;
	}

	return apply_filters( 'jlt_can_post_job', $result, $user_id );
}

function jlt_can_set_feature_job( $user_id = null ) {
	return jlt_get_feature_job_remain( $user_id ) > 0;
}

function jlt_can_edit_job( $job_id = 0, $user_id = 0 ) {
	if ( empty( $job_id ) ) {
		return false;
	}

	$user_id = empty( $user_id ) ? get_current_user_id() : $user_id;
	if ( empty( $user_id ) ) {
		return false;
	}

	$job_status = get_post_status( $job_id );

	return ( $user_id == get_post_field( 'post_author', $job_id ) ) && ( $job_status != 'expired' );
}

function jlt_can_change_job_state( $job_id = 0, $user_id = 0 ) {
	$job_status = get_post_status( $job_id );

	return jlt_can_edit_job( $job_id, $user_id ) && ( $job_status == 'publish' || $job_status == 'inactive' );
}
