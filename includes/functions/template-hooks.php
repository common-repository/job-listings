<?php
/**
 * Body Class
 */
add_filter( 'body_class', 'jlt_body_class' );
/**
 * Add notice
 */

add_action( 'jlt_before_main_content', 'jlt_add_action_notice', 10 );
function jlt_add_action_notice() {
	do_action( 'jlt_add_action_notice' );
}

/**
 * Show message in single job.
 */

/**
 * Add PopUp Ajax Flag
 */
add_action( 'wp_footer', 'jlt_popup_ajax_flag' );

/**
 * Add Job tag Prefix Label
 */

add_filter( 'jlt_job_tag_html_prefix', 'jlt_job_tag_prefix' );

/**
 * Add Job Category Prefix Label
 */
add_filter( 'jlt_job_category_html_prefix', 'jlt_job_category_prefix' );

/**
 * Add Job Type Prefix Label
 */
add_filter( 'jlt_job_type_html_prefix', 'jlt_job_type_prefix' );

add_filter( 'jlt_job_location_html_prefix', 'jlt_job_location_prefix' );

/**
 * Show step list post job form
 */

add_action( 'before_job_submit_form', 'jlt_form_list_steps' );
add_action( 'before_job_submit_form', 'jlt_message_print', 5 );
add_action( 'jlt_single_before', 'jlt_message_print', 5 );


/**
 * Display single company header
 */

add_action( 'jlt_single_company_header', 'jlt_single_company_logo', 5 );
add_action( 'jlt_single_company_header', 'jlt_single_company_meta', 10 );

/**
 * Show Single Company Meta Info.
 */

//add_action( 'jlt_single_company_meta', 'jlt_single_company_title', 5 );
add_action( 'jlt_single_company_meta', 'jlt_single_company_social_list', 10 );

/**
 * Show Single Company Info
 */
add_action( 'jlt_single_company_after', 'jlt_single_company_info', 5 );
add_action( 'jlt_single_company_after', 'jlt_single_company_map', 10 );
add_action( 'jlt_single_company_after', 'jlt_single_company_jobs', 15 );

/**
 * Job loop
 */

add_action( 'jlt_job_loop_item', 'jlt_job_loop_company_logo', 5 );
add_action( 'jlt_job_loop_item', 'jlt_job_loop_meta', 10 );
add_action( 'jlt_job_loop_item', 'jlt_job_loop_action', 15 );

add_action( 'jlt_before_job_loop', 'jlt_job_search_form', 5 );

/**
 * Display Single Job
 */

//add_action( 'jlt_single_job_before', 'jlt_job_single_title', 5 );
add_action( 'jlt_single_job_before', 'jlt_job_single_company_info', 10 );

add_action( 'jlt_single_job_after', 'jlt_single_job_info', 5 );
add_action( 'jlt_single_job_after', 'jlt_single_job_meta', 10 );
add_action( 'jlt_single_job_after', 'jlt_single_job_map', 15 );

add_action( 'jlt_single_job_after', 'jlt_single_job_apply', 20 );

add_action( 'jlt_single_job_after', 'jlt_job_single_related', 25 );

/**
 * Member page
 */

add_action( 'jlt_member_header', 'jlt_member_navigation', 5 );
add_action( 'jlt_member_header', 'jlt_message_print', 10 );

/**
 * Company loop
 */

add_action( 'jlt_company_loop_item', 'jlt_company_loop_logo', 5 );
add_action( 'jlt_company_loop_item', 'jlt_company_loop_meta', 10 );
add_action( 'jlt_company_loop_item', 'jlt_company_loop_action', 15 );

/**
 * Company Profile Form
 */

add_action( 'jlt_company_profile_form_after', 'jlt_profile_email_form', 5 );
add_action( 'jlt_company_profile_form_after', 'jlt_profile_password_form', 10 );

/**
 * Candidate Prfile Form
 */

add_action( 'jlt_candidate_profile_form_after', 'jlt_profile_password_form', 10 );

/**
 * Re-title arichive, tax custom type
 */

add_filter( 'get_the_archive_title', 'jlt_archive_title' );

/**
 * Job Preview Form
 */
add_action( 'jlt_after_job_preview_form_content', 'jlt_single_job_info', 5 );
add_action( 'jlt_after_job_preview_form_content', 'jlt_single_job_meta', 10 );
add_action( 'jlt_after_job_preview_form_content', 'jlt_single_job_map', 15 );