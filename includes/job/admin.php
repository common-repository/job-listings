<?php

/**
 * Job Admin Settings.
 *
 * @since 1.0.0
 *
 */

function jlt_job_admin_init() {
	register_setting( 'jlt_job_general', 'jlt_job_general' );
	register_setting( 'jlt_job_custom_field', 'jlt_job_custom_field' );
	register_setting( 'jlt_job_custom_field_display', 'jlt_job_custom_field_display' );
	register_setting( 'jlt_job_linkedin', 'jlt_job_linkedin' );
	add_action( 'jlt_admin_setting_general', 'jlt_job_settings_form' );
	add_action( 'jlt_admin_setting_email', 'jlt_email_settings_form' );
	if ( class_exists( 'TablePress' ) ) {
		add_action( "load-jlt_job_page_manage_jlt_job", array( TablePress::$controller, 'add_editor_buttons' ) );
	}
}

add_filter( 'admin_init', 'jlt_job_admin_init' );

function jlt_job_admin_enqueue_scripts() {
	if ( get_post_type() === 'job' || get_post_type() === 'application' || get_post_type() === 'resume' || get_post_type() == 'company' ) {

		wp_enqueue_style( 'jlt-job-admin', JLT_PLUGIN_URL . 'admin/css/jlt-job-admin.css' );
		wp_enqueue_script( 'jlt-job-admin', JLT_PLUGIN_URL . 'admin/js/jlt-job-admin.js', array( 'jquery' ), null, true );
	}
}

add_filter( 'admin_enqueue_scripts', 'jlt_job_admin_enqueue_scripts', 10, 2 );

function jlt_admin_jobs_page_state( $states = array(), $post = null ) {
	if ( ! empty( $post ) && is_object( $post ) ) {
		$archive_slug = jlt_get_job_setting( 'archive_slug' );
		if ( ! empty( $archive_slug ) && $archive_slug == $post->post_name ) {
			$states[ 'job_page' ] = __( 'Jobs Page', 'job-listings' );
		}
	}

	return $states;
}

add_filter( 'display_post_states', 'jlt_admin_jobs_page_state', 10, 2 );

function jlt_admin_jobs_page_notice( $post_type = '', $post = null ) {
	if ( ! empty( $post ) && is_object( $post ) ) {
		$archive_slug = jlt_get_job_setting( 'archive_slug' );
		if ( ! empty( $archive_slug ) && $archive_slug == $post->post_name && empty( $post->post_content ) ) {
			add_action( 'edit_form_after_title', '_jlt_admin_jobs_page_notice' );
			remove_post_type_support( $post_type, 'editor' );
		}
	}
}

add_action( 'add_meta_boxes', 'jlt_admin_jobs_page_notice', 10, 2 );

function _jlt_admin_jobs_page_notice() {
	echo '<div class="notice notice-warning inline"><p>' . __( 'You are currently editing the page that shows all your jobs.', 'job-listings' ) . '</p></div>';
}
