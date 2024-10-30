<?php

function jlt_can_apply_job( $job_id = null ) {
	if ( empty( $job_id ) ) {
		return false;
	}

	$can_apply_job = false;

	if ( ! $can_apply_job ) {
		$apply_job_setting = jlt_get_action_control( 'apply_job' );
		switch ( $apply_job_setting ) {
			case 'public':
				$can_apply_job = true;
				break;
			case 'candidate':
				$can_apply_job = JLT_Member::is_candidate();
				break;
			case 'package':
				if ( JLT_Member::is_candidate() ) {
					$package       = jlt_get_resume_posting_info();
					$can_apply_job = ( isset( $package[ 'can_apply_job' ] ) && $package[ 'can_apply_job' ] === '1' ) && ( jlt_get_job_apply_remain() != 0 );
				}
				break;
		}
	}

	return apply_filters( 'jlt_can_apply_job', $can_apply_job, $job_id );
}

function jlt_get_cannot_apply_job_message( $job_id = 0 ) {
	$title = '';
	$link  = '';

	$apply_job_setting = jlt_get_action_control( 'apply_job' );
	switch ( $apply_job_setting ) {
		case 'public':
			$title = __( 'There\'s an unknown error. Please retry or contact Administrator.', 'job-listings' );
			break;
		case 'candidate':
			$title = __( 'Only candidates can apply for this job.', 'job-listings' );
			if ( ! JLT_Member::is_logged_in() ) {
				$link = JLT_Member::get_login_url();
				$link = '<a href="' . esc_url( $link ) . '" class="btn btn-primary member-login-link">' . __( 'Login as Candidate', 'job-listings' ) . '</a>';
			}
			break;
		case 'package':
			$title = __( 'Only paid candidates can apply for this job.', 'job-listings' );
			$link  = JLT_Member::get_endpoint_url( 'manage-plan' );

			if ( ! JLT_Member::is_logged_in() ) {
				$link = JLT_Member::get_login_url();
				$link = '<a href="' . esc_url( $link ) . '" class="btn btn-primary member-login-link">' . __( 'Login as Candidate', 'job-listings' ) . '</a>';
			} elseif ( ! JLT_Member::is_candidate() ) {
				$link = JLT_Member::get_logout_url();
				$link = '<a href="' . esc_url( $link ) . '" class="btn btn-primary">' . __( 'Logout', 'job-listings' ) . '</a>';
			} else {
				$title = __( 'Your membership doesn\'t allow you to apply for this job.', 'job-listings' );
				$link  = JLT_Member::get_endpoint_url( 'manage-plan' );
				$link  = '<a href="' . esc_url( $link ) . '" class="btn btn-primary">' . __( 'Upgrade your membership', 'job-listings' ) . '</a>';
			}
			break;
	}

	$params = apply_filters( 'jlt_cannot_apply_job_message', compact( $title, $link ), $job_id );
	extract( $params );

	$title = empty( $title ) ? __( 'You don\'t have permission to apply this job.', 'job-listings' ) : $title;

	return array( $title, $link );
}