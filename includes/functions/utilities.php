<?php

function jlt_default_primary_color() {
	return apply_filters( 'jlt_default_primary_color', '#e6b706' );
}

function jlt_upload_dir_name() {
	return jlt_get_common_setting( 'upload_dir', 'job-listings' );
}

function jlt_upload_dir() {
	$upload_dir = wp_upload_dir();

	return $upload_dir[ 'basedir' ] . '/' . jlt_upload_dir_name() . '/';
}

function jlt_upload_url() {
	$upload_dir = wp_upload_dir();

	return $upload_dir[ 'baseurl' ] . '/' . jlt_upload_dir_name() . '/';
}

function jlt_create_upload_dir( $wp_filesystem = null ) {
	if ( empty( $wp_filesystem ) ) {
		return false;
	}

	$upload_dir = wp_upload_dir();
	global $wp_filesystem;

	$jlt_upload_dir = $wp_filesystem->find_folder( $upload_dir[ 'basedir' ] ) . jlt_upload_dir_name();
	if ( ! $wp_filesystem->is_dir( $jlt_upload_dir ) ) {
		if ( $wp_filesystem->mkdir( $jlt_upload_dir, 0777 ) ) {
			return $jlt_upload_dir;
		}

		return false;
	}

	return $jlt_upload_dir;
}

function jlt_mail( $to = '', $subject = '', $body = '', $headers = '', $key = '', $attachments = '' ) {

	if ( empty( $headers ) ) {
		$headers    = array();
		$from_name  = jlt_email_sender_get_setting( 'from_name', '' );
		$from_email = jlt_email_sender_get_setting( 'from_email', '' );

		if ( empty( $from_name ) ) {
			if ( is_multisite() ) {
				$from_name = $GLOBALS[ 'current_site' ]->site_name;
			} else {
				$from_name = wp_specialchars_decode( get_option( 'blogname' ), ENT_QUOTES );
			}
		}

		if ( ! empty( $from_name ) && ! empty( $from_email ) ) {
			$headers[] = 'From: ' . $from_name . ' <' . strtolower( $from_email ) . '>';
		}
	}

	$headers = apply_filters( $key . '_header', apply_filters( 'jlt_mail_header', $headers ) );

	if ( ! empty( $key ) ) {
		$subject = apply_filters( $key . '_subject', apply_filters( 'jlt_mail_subject', $subject ) );
		$body    = apply_filters( $key . '_body', apply_filters( 'jlt_mail_body', $body ) );
	}

	// RTL HTML email
	if ( is_rtl() ) {
		$body = '<div dir="rtl">' . $body . '</div>';
	}

	add_filter( 'wp_mail_content_type', 'jlt_mail_set_html_content' );

	$result = wp_mail( $to, $subject, $body, $headers, $attachments );

	// Reset content-type to avoid conflicts -- http://core.trac.wordpress.org/ticket/23578
	remove_filter( 'wp_mail_content_type', 'jlt_mail_set_html_content' );

	jlt_mail_log( $key . ': ' . $result );

	return $result;
}

function jlt_get_job_applications_count( $job_id ) {
	$key_meta = '_jlt_job_applications_count';
	$count    = jlt_get_post_meta( $job_id, $key_meta );
	if ( $count === '' || $count === null ) :
		global $wpdb;
		$count = $wpdb->get_var( "SELECT COUNT(*) FROM {$wpdb->posts} WHERE post_type = 'application' AND post_parent = {$job_id}" );
		update_post_meta( $job_id, $key_meta, absint( $count ) );

		return $count;
	endif;

	return $count;
}

function jlt_track_applications_post( $post_id = '', $post = null, $update = true ) {

	if ( $update || 'application' !== $post->post_type ) {
		return;
	}

	$job_id = $post->post_parent;
	if ( ! empty( $job_id ) ) {
		global $wpdb;
		$count = $wpdb->get_var( "SELECT COUNT(*) FROM {$wpdb->posts} WHERE post_type = 'application' AND post_parent = {$job_id}" );
		update_post_meta( $job_id, '_jlt_job_applications_count', absint( $count ) );
	}
}

add_action( 'wp_insert_post', 'jlt_track_applications_post', 10, 3 );

/* -------------------------------------------------------
 * Create functions unseen_applications_number
 * ------------------------------------------------------- */

function unseen_applications_number( $html = true ) {
	global $wpdb;
	$count_view = 0;

	if ( JLT_Member::is_employer() ) {
		$count_view = jlt_employer_unseen_application_count();
	} elseif ( JLT_Member::is_candidate() ) {
		$user              = wp_get_current_user();
		$total_applied     = $wpdb->get_var( "SELECT COUNT(ID) FROM {$wpdb->posts} 
				INNER JOIN {$wpdb->postmeta} ON {$wpdb->posts}.ID = {$wpdb->postmeta}.post_id
				WHERE post_type = 'application' AND (post_status = 'publish' OR post_status = 'rejected')
					AND {$wpdb->postmeta}.meta_key = '_candidate_email'
					AND {$wpdb->postmeta}.meta_value = '{$user->user_email}'" );
		$view_applications = count( get_user_meta( $user->ID, '_check_view_applied', true ) );

		$count_view = $total_applied - $view_applications;
	}

	$count_view = apply_filters( 'jlt-unseen-applications-number', $count_view );

	if ( $count_view > 0 ) {
		return $html ? '<span class="badge">' . $count_view . '</span>' : absint( $count_view );
	} else {
		return $html ? '' : 0;
	}
}

//Process Unseen Application

function jlt_employer_unseen_application_count() {

	$key_meta = '_jlt_applications_unseen_count';
	$user_id  = get_current_user_id();
	$count    = get_user_meta( $user_id, $key_meta, true );
	if ( $count === '' || $count === null ) :
		$pending_applications = jlt_employer_unseen_application_updating_count();

		return $pending_applications;
	endif;

	return $count;
}

function jlt_employer_unseen_application_updating_count() {
	global $wpdb;
	$key_meta             = '_jlt_applications_unseen_count';
	$user_id              = get_current_user_id();
	$job_ids              = get_posts( array(
		'post_type'        => 'job',
		'post_status'      => array( 'publish', 'expired', 'inactive' ),
		'author'           => get_current_user_id(),
		'posts_per_page'   => - 1,
		'fields'           => 'ids',
		'suppress_filters' => false,
	) );
	$pending_applications = 0;
	if ( ! empty( $job_ids ) ) {
		$job_ids              = array_merge( $job_ids, array( 0 ) );
		$job_ids_where        = implode( ', ', $job_ids );
		$pending_applications = $wpdb->get_var( "SELECT COUNT(ID) FROM {$wpdb->posts} WHERE post_type = 'application' AND post_parent IN ( {$job_ids_where} ) AND post_status = 'pending'" );
	}
	update_user_meta( $user_id, $key_meta, absint( $pending_applications ) );

	return $pending_applications;
}

add_action( 'transition_post_status', 'jlt_employer_unseen_application_updating_count' );

/** ====== END unseen_applications_number ====== **/

/* -------------------------------------------------------
 * Create functions user_notifications_number
 * ------------------------------------------------------- */

function user_notifications_number( $html = true ) {
	$count_view = unseen_applications_number( false );
	$count_view = apply_filters( 'jlt-user-notifications-number', $count_view );

	if ( $count_view > 0 ) {
		return $html ? '<span class="badge">' . $count_view . '</span>' : $count_view;
	} else {
		return $html ? '' : 0;
	}
}

/** ====== END user_notifications_number ====== **/

function jlt_wp_editor( $content, $editor_id, $editor_name = '', $class = '' ) {
	$configs = array(
		'editor_class'  => $class,
		'media_buttons' => false,
	);
	if ( ! empty( $editor_name ) ) {
		$configs[ 'textarea_name' ] = $editor_name;
	}

	$configs = apply_filters( 'jlt_editor_config', $configs );

	return wp_editor( $content, $editor_id, $configs );
}

function jlt_form_nonce( $action ) {
	$nonce = wp_create_nonce( $action );
	echo '<input type="hidden" id="_wpnonce" name="_wpnonce" value="' . $nonce . '">';
}

function jlt_company_job_count( $company_id ) {
	$key_meta = '_jlt_job_count';
	$count    = jlt_get_post_meta( $company_id, $key_meta, '' );
	if ( empty( $count ) && ! is_numeric( $count ) ) {
		$count = JLT_Company::count_jobs( $company_id );
		update_post_meta( $company_id, $key_meta, $count );
	}

	return $count;
}

function jlt_company_address( $company_id = null ) {
	
	$company_id = empty( $company_id ) ? get_the_ID() : $company_id;

	return get_post_meta( $company_id, '_address', true );
}

function jlt_update_company_job_count( $new_status, $old_status, $post ) {
	$company_id = '';
	if ( $post->post_type == 'job' ) {
		$company_id = jlt_get_job_company( $post->ID );
	} elseif ( $post->post_type == 'company' ) {
		$company_id = $post->ID;
	}
	if ( ! empty( $company_id ) ) {
		$key_meta = '_jlt_job_count';
		$count    = JLT_Company::count_jobs( $company_id );
		update_post_meta( $company_id, $key_meta, $count );
	}
}

add_action( 'transition_post_status', 'jlt_update_company_job_count', 10, 3 );