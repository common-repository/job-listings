<?php
/**
 * Member Shortcode Content
 *
 * @since  : 0.1.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

add_shortcode( 'jlt_member', 'jlt_dashboard_page_shortcode' );

function jlt_dashboard_page_shortcode() {
	ob_start();

	jlt_get_template( 'member/dashboard.php' );

	return ob_get_clean();
}

function jlt_member_dashboard_content() {
	global $wp;

	if ( ! jlt_is_logged_in() ) :

		if ( JLT_Member::can_register() && isset( $_GET[ 'action' ] ) && ( $_GET[ 'action' ] === 'register' || ( $_GET[ 'action' ] === 'login' && isset( $_GET[ 'mode' ] ) && $_GET[ 'mode' ] === 'register' ) ) ) {

			$redirect_to = isset( $_GET[ 'redirect_to' ] ) && ! empty( $_GET[ 'redirect_to' ] ) ? $_GET[ 'redirect_to' ] : '';
			$role        = isset( $_GET[ 'role' ] ) && ! empty( $_GET[ 'role' ] ) ? $_GET[ 'role' ] : '';

			$atts = array(
				'redirect_to' => $redirect_to,
			);
			if ( 'none' != jlt_check_allow_register() ) {
				jlt_get_template( 'form/register.php', $atts );
			}
		} else {
			jlt_get_template( 'form/login.php' );
		}

	else:
		$list_endpoint = jlt_all_endpoints();
		foreach ( $wp->query_vars as $key => $value ) {

			// Ignore pagename param.
			if ( 'pagename' === $key ) {
				continue;
			}

			$action = array_search( $key, $list_endpoint );

			if ( has_action( 'jlt_account_' . $action . '_endpoint' ) ) {
				do_action( 'jlt_account_' . $action . '_endpoint', $value );

				return;
			}
		}
		// Default Content
		if ( JLT_Member::is_employer() ) {
			jlt_member_manage_job();
		} else {
			jlt_member_manage_job_applied();
		}
	endif;
}

add_action( 'jlt_member_dashboard_content', 'jlt_member_dashboard_content' );

function jlt_member_manage_job() {

	$paged = jlt_member_get_paged();

	$current_user = wp_get_current_user();

	$filter_status = isset( $_GET[ 'status' ] ) ? esc_attr( $_GET[ 'status' ] ) : '';

	$all_statuses = jlt_get_job_status();
	unset( $all_statuses[ 'draft' ] );

	$job_need_approve = jlt_get_job_setting( 'job_approve', '' ) == 'yes';

	if ( ! $job_need_approve ) {
		unset( $all_statuses[ 'pending' ] );
	}

	// Query Job
	$args = array(
		'post_type' => 'job',
		'author'    => $current_user->ID,
		'paged'     => $paged,
	);

	if ( ! empty( $filter_status ) ) {
		$args[ 'post_status' ] = $filter_status;
	} else {
		$args[ 'post_status' ] = array( 'publish', 'pending', 'pending_payment', 'expired', 'inactive' );
	}

	$list_jobs = new WP_Query( $args );

	wp_reset_query();

	$args = array(
		'list_jobs'      => $list_jobs,
		'list_status'    => $all_statuses,
		'current_user'   => $current_user,
		'current_status' => $filter_status,
	);

	jlt_get_template( 'member/manage-job.php', $args );
}

add_action( 'jlt_account_manage-job_endpoint', 'jlt_member_manage_job' );

function jlt_member_edit_job() {
	JLT_Job_Form_Hander::step_edit();
}

add_action( 'jlt_account_edit-job_endpoint', 'jlt_member_edit_job' );

function jlt_member_candidate_profile() {

	$current_user = wp_get_current_user();

	$user_content = $current_user->description ? $current_user->description : '';
	$args         = array(
		'current_user' => $current_user,
		'user_content' => $user_content,
	);

	jlt_get_template( 'member/candidate_profile.php', $args );
}

add_action( 'jlt_account_candidate-profile_endpoint', 'jlt_member_candidate_profile' );

function jlt_member_manage_job_applied() {

	$paged = jlt_member_get_paged();

	$current_user    = wp_get_current_user();
	$viewed_messages = get_user_meta( $current_user->ID, '_check_view_applied', true );
	$viewed_messages = empty( $viewed_messages ) || ! is_array( $viewed_messages ) ? array() : $viewed_messages;

	$args = array(
		'post_type'   => 'application',
		'paged'       => $paged,
		'post_status' => array( 'publish', 'pending', 'rejected', 'inactive' ),
		'meta_query'  => array(
			array(
				'key'   => '_candidate_email',
				'value' => $current_user->user_email,
			),
		),
	);

	$list_jobs = new WP_Query( $args );
	$job_count = $list_jobs->found_posts;
	wp_reset_query();
	$args = array(
		'list_jobs'       => $list_jobs,
		'count_jobs'      => $job_count,
		'viewed_messages' => $viewed_messages,
		'current_user'    => $current_user,
	);

	jlt_get_template( 'member/manage-job-applied.php', $args );
}

add_action( 'jlt_account_manage-job-applied_endpoint', 'jlt_member_manage_job_applied' );

function jlt_member_manage_application() {
	jlt_get_template( 'member/manage-application.php' );
}

add_action( 'jlt_account_manage-application_endpoint', 'jlt_member_manage_application' );

function jlt_member_company_profile() {

	$user_ID    = get_current_user_id();
	$company_id = ! empty( jlt_get_employer_company( $user_ID ) ) ? jlt_get_employer_company( $user_ID ) : 0;

	if ( ! JLT_Member::can_edit_company( $company_id ) ) {
		return '<p>' . __( 'You can\'t edit this company', 'job-listings' ) . '</p>';
	}

	$company         = get_post( $company_id );
	$company_name    = ( $company_id ? $company->post_title : '' );
	$company_content = $company_id ? $company->post_content : '';

	$atts = array(
		'company_id'      => $company_id,
		'company_name'    => $company_name,
		'company_content' => $company_content,
		'user_ID'         => $user_ID,
	);

	jlt_get_template( 'member/company_profile.php', $atts );
}

add_action( 'jlt_account_company-profile_endpoint', 'jlt_member_company_profile' );