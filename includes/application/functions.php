<?php
/**
 * Application Functions.
 *
 * @since  : 0.1.0
 */

function jlt_application_can_apply() {

	$application_attachment = jlt_get_application_setting( 'application_attachment', 'enabled' ) == 'enabled';
	$require_attachment     = jlt_get_application_setting( 'require_attachment', 'yes' ) == 'yes';
	$can_apply              = ! $require_attachment || $application_attachment;

	return $can_apply;
}

function jlt_job_apply_url( $job_id = '' ) {
	$job_id = ! empty( $job_id ) ? $job_id : get_the_ID();

	$custom_apply_link = jlt_get_application_setting( 'custom_apply_link', '' );
	$custom_apply_link = apply_filters( 'jlt_job_apply_url', $custom_apply_link );

	return ! empty( $custom_apply_link ) ? jlt_get_post_meta( $job_id, '_custom_application_url', '' ) : '';
}

function jlt_apply_withdraw_url() {
	return wp_nonce_url( add_query_arg( array(
		'action'         => 'withdraw',
		'application_id' => get_the_ID(),
	) ), 'job-applied-manage-action' );
}

function jlt_apply_delete_url() {
	return wp_nonce_url( add_query_arg( array(
		'action'         => 'delete',
		'application_id' => get_the_ID(),
	) ), 'job-applied-manage-action' );
}

function jlt_applied_job_title( $post ) {
	$parent_job = get_post( $post->post_parent );
	if ( $parent_job && $parent_job->post_type === 'job' ) {
		return ( '<a href="' . get_permalink( $parent_job->ID ) . '">' . $parent_job->post_title . '</a>' );
	} elseif ( $parent_job = jlt_get_post_meta( $post->ID, '_job_applied_for', true ) ) {
		return $parent_job;
	}

	return;
}

function job_applied_date( $post ) {
	return date_i18n( get_option( 'date_format' ), strtotime( $post->post_date ) );
}

function job_apply_candidate_email( $post ) {
	return jlt_get_post_meta( $post->ID, '_candidate_email' );
}

function jlt_application_delete_url( $post ) {
	return wp_nonce_url( add_query_arg( array(
		'action'         => 'delete',
		'application_id' => $post->ID,
	) ), 'application-manage-action' );
}

function jlt_application_status( $post ) {

	return $post->post_status;
}

function jlt_application_status_text( $post ) {
	$status = $post->post_status;

	$statuses = JLT_Application::get_application_status();
	if ( isset( $statuses[ $status ] ) ) {
		$status = $statuses[ $status ];
	} else {
		$status = __( 'Inactive', 'job-listings' );
	}

	return $status;
}