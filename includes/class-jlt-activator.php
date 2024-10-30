<?php

/**
 * Fired during plugin activation
 *
 * @link       http://nootheme.com
 * @since      0.1.0
 *
 * @package    Job_Listings
 * @subpackage Job_Listings/includes
 */

/**
 * Fired during plugin activation.
 *
 * This class defines all code necessary to run during the plugin's activation.
 *
 * @since      0.1.0
 * @package    Job_Listings
 * @subpackage Job_Listings/includes
 * @author     NooTheme <thinhnv@vietbrain.com>
 */
class Job_Listings_Activator {

	/**
	 * Short Description. (use period)
	 *
	 * Long Description.
	 *
	 * @since    0.1.0
	 */
	public static function activate() {
		self::create_role();
		self::insert_default_data();
		flush_rewrite_rules();
	}

	public static function create_role() {
		global $wp_roles;

		if ( class_exists( 'WP_Roles' ) ) {
			if ( ! isset( $wp_roles ) ) {
				$wp_roles = new WP_Roles();
			}
		}

		if ( is_object( $wp_roles ) ) {

			add_role( 'employer', __( 'Employer', 'job-listings' ), array(
				'read'         => true,
				'edit_posts'   => false,
				'delete_posts' => false,
			) );

			add_role( 'candidate', __( 'Candidate', 'job-listings' ), array(
				'read'         => true,
				'edit_posts'   => false,
				'delete_posts' => false,
			) );
		}
	}

	public static function insert_default_data() {

		if ( get_option( 'jlt_job_insert_default_data' ) == '1' ) {
			return;
		}
		$job_types = array(
			'Full Time',
			'Part Time',
			'Freelance',
			'Contract',
		);
		$default_colors = array( '#f14e3b', '#458cce', '#e6b707', '#578523' );

		foreach ( $job_types as $index => $term ) {
			if ( ! get_term_by( 'slug', sanitize_title( $term ), 'job_type' ) ) {
				error_log($index);
				$result = wp_insert_term( $term, 'job_type' );
				if ( ! is_wp_error( $result ) ) {
					if ( function_exists( 'update_term_meta' ) ) {
						update_term_meta( $result[ 'term_id' ], '_color', $default_colors[ $index ] );
					}
				}
			}
		}
		delete_option( 'jlt_job_insert_default_data' );
		update_option( 'jlt_job_insert_default_data', '1' );
	}

}
