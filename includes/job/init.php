<?php
/**
 * Init job post type.
 *
 * @since 1.0.0
 *
 */
function jlt_register_job_post_type() {
	if ( post_type_exists( 'job' ) ) {
		return;
	}

	$job_slug    = jlt_get_job_setting( 'archive_slug', 'jobs' );
	$job_rewrite = $job_slug ? array(
		'slug'       => sanitize_title( $job_slug ),
		'with_front' => true,
		'feeds'      => true,
	) : false;

	register_post_type( 'job', array(
		'labels'              => array(
			'name'               => __( 'Jobs', 'job-listings' ),
			'singular_name'      => __( 'Job', 'job-listings' ),
			'add_new'            => __( 'Add New Job', 'job-listings' ),
			'add_new_item'       => __( 'Add Job', 'job-listings' ),
			'edit'               => __( 'Edit', 'job-listings' ),
			'edit_item'          => __( 'Edit Job', 'job-listings' ),
			'new_item'           => __( 'New Job', 'job-listings' ),
			'view'               => __( 'View', 'job-listings' ),
			'view_item'          => __( 'View Job', 'job-listings' ),
			'view_items'         => __( 'View Job Listings', 'job-listings' ),
			'search_items'       => __( 'Search Job', 'job-listings' ),
			'not_found'          => __( 'No Jobs found', 'job-listings' ),
			'not_found_in_trash' => __( 'No Jobs found in Trash', 'job-listings' ),
			'parent'             => __( 'Parent Job', 'job-listings' ),
			'all_items'          => __( 'All Jobs', 'job-listings' ),
		),
		'description'         => __( 'This is a place where you can add new job.', 'job-listings' ),
		'public'              => true,
		'menu_icon'           => 'dashicons-portfolio',
		'show_ui'             => true,
		'capability_type'     => 'post',
		'map_meta_cap'        => true,
		'publicly_queryable'  => true,
		'exclude_from_search' => false,
		'hierarchical'        => false, // Hierarchical jobs memory issues - WP loads all records!
		'rewrite'             => apply_filters( 'jlt_job_rewrite', $job_rewrite ),
		'query_var'           => true,
		'supports'            => jlt_get_setting( 'jlt_job_comment', false ) ? array(
			'title',
			'editor',
			'comments',
		) : array( 'title', 'editor' ),
		'has_archive'         => true,
		'show_in_nav_menus'   => true,
		'delete_with_user'    => true,
		'can_export'          => true,
	) );
	register_taxonomy( 'job_category', 'job', array(
		'labels'       => array(
			'name'          => __( 'Job Category', 'job-listings' ),
			'add_new_item'  => __( 'Add New Job Category', 'job-listings' ),
			'new_item_name' => __( 'New Job Category', 'job-listings' ),
		),
		'hierarchical' => true,
		'query_var'    => true,
		'rewrite'      => array( 'slug' => _x( 'job-category', 'slug', 'job-listings' ) ),
	) );
	register_taxonomy( 'job_type', 'job', array(
		'labels'       => array(
			'name'          => __( 'Job Type', 'job-listings' ),
			'add_new_item'  => __( 'Add New Job Type', 'job-listings' ),
			'new_item_name' => __( 'New Job Type', 'job-listings' ),
		),
		'hierarchical' => true,
		'query_var'    => true,
		'rewrite'      => array( 'slug' => _x( 'job-type', 'slug', 'job-listings' ) ),
	) );
	register_taxonomy( 'job_tag', 'job', array(
		'labels'       => array(
			'name'          => __( 'Job Tag', 'job-listings' ),
			'add_new_item'  => __( 'Add New Job Tag', 'job-listings' ),
			'new_item_name' => __( 'New Job Tag', 'job-listings' ),
		),
		'hierarchical' => false,
		'query_var'    => true,
		'rewrite'      => array( 'slug' => _x( 'job-tag', 'slug', 'job-listings' ) ),
	) );
	register_taxonomy( 'job_location', 'job', array(
		'labels'       => array(
			'name'          => __( 'Job Location', 'job-listings' ),
			'add_new_item'  => __( 'Add New Job Location', 'job-listings' ),
			'new_item_name' => __( 'New Job Location', 'job-listings' ),
		),
		'hierarchical' => true,
		'query_var'    => true,
		'rewrite'      => array( 'slug' => _x( 'job-location', 'slug', 'job-listings' ) ),
	) );

	register_post_status( 'expired', array(
		'label'                     => _x( 'Expired', 'Job status', 'job-listings' ),
		'public'                    => false,
		'exclude_from_search'       => true,
		'show_in_admin_all_list'    => false,
		'show_in_admin_status_list' => true,
		'label_count'               => _n_noop( 'Expired <span class="count">(%s)</span>', 'Expired <span class="count">(%s)</span>', 'job-listings' ),
	) );
	register_post_status( 'pending_payment', array(
		'label'                     => _x( 'Pending Payment', 'Job status', 'job-listings' ),
		'public'                    => false,
		'exclude_from_search'       => true,
		'show_in_admin_all_list'    => false,
		'show_in_admin_status_list' => true,
		'label_count'               => _n_noop( 'Pending Payment <span class="count">(%s)</span>', 'Pending Payment <span class="count">(%s)</span>', 'job-listings' ),
	) );
	register_post_status( 'inactive', array(
		'label'                     => __( 'Inactive', 'job-listings' ),
		'public'                    => false,
		'exclude_from_search'       => true,
		'show_in_admin_all_list'    => false,
		'show_in_admin_status_list' => true,
		'label_count'               => _n_noop( 'Inactive <span class="count">(%s)</span>', 'Inactive <span class="count">(%s)</span>', 'job-listings' ),
	) );
}

add_action( 'init', 'jlt_register_job_post_type', 0 );
