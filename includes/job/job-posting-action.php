<?php
/**
 * Job Posting Action.
 *
 * @since 1.0.0
 */


function jlt_page_post_job_login_check( $action = '' ) {
	if ( ! JLT_Member::is_logged_in() ) {
		do_action( 'jlt_page_post_job_not_login', $action );
		switch ( $action ) {
			case 'login':
				break;
			default:
				jlt_force_redirect( esc_url_raw( add_query_arg( 'action', 'login' ) ) );
				break;
		}
	} elseif ( ! JLT_Member::is_employer() ) {
		do_action( 'jlt_page_post_job_not_employer', $action );
		jlt_message_add( __( 'You can not post job', 'job-listings' ), 'error' );
		wp_safe_redirect( JLT_Member::get_member_page_url() );
		exit;
	}
}

function jlt_get_page_post_job_steps() {
	$steps = array(
		'login'       => jlt_get_page_post_job_login_step(),
		'post_job'    => jlt_get_page_post_job_post_step(),
		'preview_job' => jlt_get_page_post_job_preview_step(),
	);

	return apply_filters( 'jlt_page_post_job_steps_list', $steps );
}

function jlt_get_page_post_job_login_step() {
	$title = __( 'Login', 'job-listings' );

	return apply_filters( 'jlt_page_post_job_login_step', array(
		'actions' => array( 'login', 'register' ),
		'title'   => $title,
		'link'    => 'javascript:void(0);',
	) );
}

function jlt_get_page_post_job_package_step() {
	$title = __( 'Package', 'job-listings' );
	if ( isset( $_REQUEST[ 'package_id' ] ) ) {
		$link = esc_url( remove_query_arg( 'package_id', add_query_arg( 'action', 'job_package' ) ) );
	} else {
		$link = 'javascript:void(0);';
	}

	return apply_filters( 'jlt_page_post_job_package_step', array(
		'actions' => array( 'job_package' ),
		'title'   => $title,
		'link'    => $link,
	) );
}

function jlt_get_page_post_job_post_step() {
	$title     = __( 'Job Detail', 'job-listings' );
	$link_args = array( 'action' => 'post_job' );
	$job_id    = isset( $_GET[ 'job_id' ] ) ? absint( $_GET[ 'job_id' ] ) : 0;
	if ( $job_id ) {
		$link_args[ 'job_id' ] = $job_id;
	}
	$link = esc_url( add_query_arg( $link_args ) );

	return apply_filters( 'jlt_page_post_job_post_step', array(
		'actions' => array( 'post_job' ),
		'title'   => $title,
		'link'    => $link,
	) );
}

function jlt_get_page_post_job_preview_step() {
	$title = __( 'Preview and Submit', 'job-listings' );

	return apply_filters( 'jlt_get_page_post_job_preview_step', array(
		'actions' => array( 'preview_job' ),
		'title'   => $title,
		'link'    => 'javascript:void(0);',
	) );
}

if ( ! isset( $_POST[ 'action' ] ) || empty( $_POST[ 'action' ] ) ) {
	if ( empty( $_GET[ 'action' ] ) ) {
		$GLOBALS[ 'action' ] = '';
	} else {
		$GLOBALS[ 'action' ] = sanitize_text_field( $_GET[ 'action' ] );
	}
} else {
	$GLOBALS[ 'action' ] = sanitize_text_field( $_POST[ 'action' ] );
}

return $GLOBALS[ 'action' ];

function jlt_posting_job_redirect_next_step( $next_step ) {
	jlt_force_redirect( esc_url_raw( add_query_arg( 'action', $next_step ) ) );
}