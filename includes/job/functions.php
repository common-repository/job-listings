<?php
/**
 * Job Functions.
 *
 * @since 1.0.0
 *
 */

function jlt_get_job_setting( $id = null, $default = null ) {
	return jlt_get_setting( 'jlt_job_general', $id, $default );
}

function jlt_get_application_setting( $id = null, $default = null ) {
	return jlt_get_setting( 'jlt_application', $id, $default );
}

function jlt_get_email_setting( $id = null, $default = null ) {
	return jlt_get_setting( 'jlt_email', $id, $default );
}

function jlt_get_employer_company( $employer_id = '' ) {
	if ( empty( $employer_id ) ) {
		$employer_id = get_current_user_id();
	}

	return get_user_meta( $employer_id, 'employer_company', true );
}

function jlt_get_job_company( $job = '' ) {
	$job_id = 0;
	if ( is_object( $job ) ) {
		$job_id = $job->ID;
	} elseif ( is_numeric( $job ) ) {
		$job_id = $job;
	}

	if ( empty( $job_id ) ) {
		$job_id = get_the_ID();
	}

	if ( 'job' != get_post_type( $job_id ) ) {
		return 0;
	}

	$company_id = jlt_get_post_meta( $job_id, '_company_id', '' );

	if ( empty( $company_id ) ) {
		$company_id = jlt_get_employer_company( get_post_field( 'post_author', $job_id ) );
	}

	return $company_id;
}

function jlt_get_job_type( $job = null ) {
	global $jlt_job_type;

	if ( is_int( $job ) ) {
		$job = get_post( $job );
	}

	if ( empty( $job->post_type ) || ! is_object( $job ) || $job->post_type !== 'job' ) {
		return;
	}

	if ( empty( $jlt_job_type ) ) {
		$jlt_job_type = array();
	}

	if ( ! isset( $jlt_job_type[ $job->ID ] ) ) {
		$types = get_the_terms( $job->ID, 'job_type' );
		$type  = false;

		if ( ! is_wp_error( $types ) && ! empty( $types ) ) {
			$type = current( $types );

			$type->color = jlt_get_job_type_color( $type->term_id );
		}

		$jlt_job_type[ $job->ID ] = $type;
	}

	return apply_filters( 'jlt_get_job_type', $jlt_job_type[ $job->ID ], $job );
}

function jlt_get_job_status() {
	return apply_filters( 'jlt_job_status', array(
		'publish'  => _x( 'Active', 'Job status', 'job-listings' ),
		'inactive' => _x( 'Inactive', 'Job status', 'job-listings' ),
		'pending'  => _x( 'Pending Approval', 'Job status', 'job-listings' ),
		//			'pending_payment' => _x( 'Pending Payment', 'Job status', 'job-listings' ),
		'expired'  => _x( 'Expired', 'Job status', 'job-listings' ),
		'draft'    => _x( 'Draft', 'Job status', 'job-listings' ),
	) );
}

function jlt_job_status( $post ) {
	$status      = $status_class = jlt_correct_job_status( $post->ID, $post->post_status );
	$statuses    = jlt_get_job_status();
	$status_text = '';
	if ( isset( $statuses[ $status ] ) ) {
		$status_text = $statuses[ $status ];
	} else {
		$status_text  = __( 'Inactive', 'job-listings' );
		$status_class = 'inactive';
	}
	$rs_status[ 'text' ]  = $status_text;
	$rs_status[ 'class' ] = $status_class;

	return $rs_status;
}

function jlt_job_bulk_actions() {
	$bulk_actions = array(
		'publish'   => __( 'Publish', 'job-listings' ),
		'unpublish' => __( 'Unpublish', 'job-listings' ),
		'delete'    => __( 'Delete', 'job-listings' ),
	);

	return apply_filters( 'jlt_job_bulk_actions', $bulk_actions );
}

function jlt_job_default_data( $post_ID = 0, $post = null, $update = false ) {

	if ( ! $update && ! empty( $post_ID ) && $post->post_type == 'job' ) {
		$featured = jlt_get_post_meta( $post_ID, '_featured' );
		if ( empty( $featured ) ) {
			update_post_meta( $post_ID, '_featured', 'no' );
		}
	}
}

add_filter( 'wp_insert_post', 'jlt_job_default_data', 10, 3 );

function jlt_job_is_featured( $job_id = 0 ) {
	$job_id   = ! empty( $job_id ) ? $job_id : get_the_ID();
	$featured = jlt_get_post_meta( $job_id, '_featured' );
	if ( 'yes' == $featured ) {
		return true;
	} else {
		return false;
	}
}

function jlt_get_job_category( $job_id ) {
	$categories = get_the_terms( $job_id, 'job_category' );
	if ( ! empty( $categories ) && ! is_wp_error( $categories ) ) {

		return $categories;
	}

	return array();
}

function jlt_is_woo_job_posting() {
	$job_package_actions = array(
		jlt_get_action_control( 'post_job' ),
		jlt_get_action_control( 'view_resume' ),
		jlt_get_action_control( 'view_candidate_profile' ),
	);

	return in_array( 'package', $job_package_actions );
}

function jlt_job_send_notification( $job_id = null, $user_id = 0 ) {
	if ( empty( $job_id ) ) {
		return false;
	}
	if ( empty( $user_id ) ) {
		$user_id = get_current_user_id();
	}
	$job = get_post( $job_id );
	if ( empty( $job ) ) {
		return;
	}

	$current_user = get_userdata( $user_id );
	if ( $current_user->ID != $job->post_author ) {
		return false;
	}

	$emailed = jlt_get_post_meta( $job_id, '_new_job_emailed', 0 );
	if ( $emailed ) {
		return false;
	}

	if ( is_multisite() ) {
		$blogname = $GLOBALS[ 'current_site' ]->site_name;
	} else {
		$blogname = wp_specialchars_decode( get_option( 'blogname' ), ENT_QUOTES );
	}

	$company = get_post( absint( jlt_get_employer_company( $current_user->ID ) ) );

	$job_link = get_permalink( $job_id );

	// admin email
	if ( jlt_email_get_setting( 'admin_job_submitted', 'active', 1 ) ) {

		$subject = jlt_email_get_setting( 'admin_job_submitted', 'subject' );

		$array_subject = array(
			'[site_name]' => $blogname,
			'[site_url]'  => esc_url( home_url( '' ) ),
			'[job_title]' => $job->post_title,
		);
		$subject       = str_replace( array_keys( $array_subject ), $array_subject, $subject );

		$to = get_option( 'admin_email' );

		$array_message = array(
			'[job_title]'   => $job->post_title,
			'[job_url]'     => $job_link,
			'[job_content]' => $job->post_content,
			'[job_company]' => $company->post_title,
			'[site_name]'   => $blogname,
			'[site_url]'    => esc_url( home_url( '' ) ),
		);

		$message = jlt_email_get_setting( 'admin_job_submitted', 'content' );
		$message = str_replace( array_keys( $array_message ), $array_message, $message );

		$subject = jlt_et_custom_field( 'job', $job_id, $subject );
		$message = jlt_et_custom_field( 'job', $job_id, $message );

		jlt_mail( $to, $subject, $message, array(), 'jlt_notify_job_submitted_admin' );
	}

	// employer email
	if ( jlt_email_get_setting( 'employer_job_submitted', 'active', 1 ) ) {
		$subject_employer = jlt_email_get_setting( 'employer_job_submitted', 'subject' );

		$array_subject = array(
			'[site_name]' => $blogname,
			'[job_title]' => $job->post_title,
			'[site_url]'  => esc_url( home_url( '' ) ),
		);

		$subject = str_replace( array_keys( $array_subject ), $array_subject, $subject_employer );

		$to = $current_user->user_email;

		$array_message = array(
			'[job_title]'      => $job->post_title,
			'[job_url]'        => $job_link,
			'[job_content]'    => $job->post_content,
			'[job_company]'    => $current_user->display_name,
			'[job_manage_url]' => JLT_Member::get_endpoint_url( 'manage-job' ),
			'[site_name]'      => $blogname,
			'[site_url]'       => esc_url( home_url( '' ) ),
		);

		$message = jlt_email_get_setting( 'employer_job_submitted', 'content' );
		$message = str_replace( array_keys( $array_message ), $array_message, $message );

		$subject = jlt_et_custom_field( 'job', $job_id, $subject );
		$message = jlt_et_custom_field( 'job', $job_id, $message );

		jlt_mail( $to, $subject, $message, 'jlt_notify_job_submitted_employer' );
	}

	update_post_meta( $job_id, '_new_job_emailed', 1 );
}