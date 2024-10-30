<?php

function jlt_can_view_job( $job_id = null ) {

	if ( empty( $job_id ) ) {
		return false;
	}
	$can_view_job = false;

	// Job's author can view his/her job
	$employer_id = get_post_field( 'post_author', $job_id );
	if ( $employer_id == get_current_user_id() ) {
		return true;
	}
	// Administrator can view all jobs
	if ( 'administrator' == JLT_Member::get_user_role( get_current_user_id() ) ) {
		return true;
	}
	if ( ! $can_view_job ) {
		$view_job_setting = jlt_get_action_control( 'view_job', 'public' );
		switch ( $view_job_setting ) {
			case 'public':
				$can_view_job = true;
				break;
			case 'user':
				$can_view_job = JLT_Member::is_logged_in();
				break;
			case 'candidate':
				$can_view_job = JLT_Member::is_candidate();
				break;
			default:
				$can_view_job = true;
				break;
		}
	}

	return apply_filters( 'jlt_can_view_job', $can_view_job, $job_id );
}

function jlt_job_not_view_html( $job_id = null ) {

	$title            = '';
	$link             = '';
	$login_link       = JLT_Member::get_login_url();
	$logout_link      = JLT_Member::get_logout_url();
	$view_job_setting = jlt_get_action_control( 'view_job', 'public' );

	switch ( $view_job_setting ) {
		case 'public':
			$title = __( 'There\'s an unknown error. Please retry or contact Administrator.', 'job-listings' );
			break;
		case 'user':

			$title = __( 'Only logged in users can view job details.', 'job-listings' );

			if ( ! jlt_is_logged_in() ) {
				$link = $login_link;
				$link = '<a href="' . esc_url( $link ) . '" class="jlt-btn">' . __( 'Login', 'job-listings' ) . '</a>';
			}

			break;
		case 'candidate':
			$title = __( 'Only candidates can view this job.', 'job-listings' );
			if ( ! jlt_is_logged_in() ) {
				$link = $login_link;
				$link = '<a href="' . esc_url( $link ) . '" class="jlt-btn">' . __( 'Login as Candidate', 'job-listings' ) . '</a>';
			} elseif ( ! jlt_is_candidate() ) {
				$link = $logout_link;
				$link = '<a href="' . esc_url( $link ) . '" class="jlt-btn">' . __( 'Logout', 'job-listings' ) . '</a>';
			}

			break;
	}
	$result = array( 'title' => $title, 'link' => $link );

	return apply_filters( 'jlt_job_not_view_html', $result, $view_job_setting, $job_id );
}