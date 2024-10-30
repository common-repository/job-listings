<?php

function jlt_correct_job_status( $job_id = null, $job_status = 'pending' ) {
	if ( empty( $job_id ) ) {
		return;
	}
	$corrected_status = '';
	if ( $job_status == 'pending' ) {
		$in_review       = (bool) jlt_get_post_meta( $job_id, '_in_review', '' );
		$waiting_payment = (bool) jlt_get_post_meta( $job_id, '_waiting_payment', '' );
		if ( ! $in_review && ! $waiting_payment ) {
			$corrected_status = 'inactive';
		} elseif ( $waiting_payment ) {
			delete_post_meta( $job_id, '_waiting_payment' );
			$corrected_status = 'pending_payment';
		}
	}

	// Correct for version 2.10.1 or below
	if ( ! empty( $corrected_status ) ) {
		wp_update_post( array(
			'ID'          => $job_id,
			'post_status' => $corrected_status,
		) );

		return $corrected_status;
	}

	return $job_status;
}

function jlt_correct_application_attachment( $application_id = 0 ) {
	$application_id = ! empty( $application_id ) ? $application_id : get_the_ID();
	$attachment     = jlt_get_post_meta( $application_id, '_attachment', '' );

	return $attachment;
}

function jlt_remove_frontend_editor_pluginss( $plugins ) {
	if ( ! is_admin() ) {
		$unuse_plugins = array( 'wpeditimage', 'wplink' );
		foreach ( $unuse_plugins as $plugin ) {
			if ( ( $index = array_search( $plugin, $plugins ) ) !== false ) {
				unset( $plugins[ $index ] );
			}
		}
	}

	return $plugins;
}

function jlt_remove_frontend_editor_buttons( $buttons ) {
	if ( ! is_admin() ) {
		$unuse_buttons = array( 'wp_more' );
		foreach ( $unuse_buttons as $button ) {
			if ( ( $index = array_search( $button, $buttons ) ) !== false ) {
				unset( $buttons[ $index ] );
			}
		}
	}

	return $buttons;
}

add_filter( 'mce_buttons', 'jlt_remove_frontend_editor_buttons' );

function jlt_remove_frontend_editor_buttons_2( $buttons ) {
	if ( ! is_admin() ) {
		$unuse_buttons = array( 'wp_help' );
		foreach ( $unuse_buttons as $button ) {
			if ( ( $index = array_search( $button, $buttons ) ) !== false ) {
				unset( $buttons[ $index ] );
			}
		}
	}

	return $buttons;
}

add_filter( 'mce_buttons_2', 'jlt_remove_frontend_editor_buttons_2' );

function jlt_wp_link_query_args( $query ) {
	$user_id = get_current_user_id();
	if ( JLT_Member::get_user_role( $user_id ) != 'administrator' ) {
		$query[ 'author' ] = $user_id;
	}

	return $query;
}

add_filter( 'wp_link_query_args', 'jlt_wp_link_query_args' );