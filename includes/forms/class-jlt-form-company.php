<?php

class JLT_Form_Company {
	public function __construct() {
		add_action( 'init', array( __CLASS__, 'edit_company_action' ) );
	}

	public static function edit_company_action() {
		if ( ! is_user_logged_in() ) {
			return;
		} else {
			$user_ID = get_current_user_id();
		}

		if ( 'POST' !== strtoupper( $_SERVER[ 'REQUEST_METHOD' ] ) ) {
			return;
		}

		if ( empty( $_POST[ 'action' ] ) || 'edit_company' !== $_POST[ 'action' ] || empty( $_POST[ '_wpnonce' ] ) || ! wp_verify_nonce( $_POST[ '_wpnonce' ], 'edit-company' ) ) {
			return;
		}

		$company_id = self::save_company( $_POST, $user_ID );
		if ( $company_id ) {
			jlt_message_add( __( 'Company updated', 'job-listings' ) );
		}
		wp_safe_redirect( JLT_Member::get_company_profile_url() );
		exit;
	}

	public static function save_company( $args = '', $user_id = null ) {
		$defaults = array(
			'company_id'   => '',
			'company_name' => '',
			'company_desc' => '',
			'_website'     => '',
			'_googleplus'  => '',
			'_twitter'     => '',
			'_facebook'    => '',
			'_linkedin'    => '',
			'_instagram'   => '',
		);
		$user_id  = empty( $user_id ) ? get_current_user_id() : intval( $user_id );

		$company_name = isset( $args[ 'company_name' ] ) ? $args[ 'company_name' ] : '';

		$args = wp_parse_args( $args, $defaults );

		$company_data = array(
			'post_title'     => $company_name,
			'post_content'   => wp_kses_post( $args[ 'company_desc' ] ),
			'post_type'      => 'company',
			'comment_status' => 'closed',
			'post_status'    => 'publish',
			'post_author'    => $user_id,
		);

		if ( ! empty( $args[ 'company_id' ] ) && 'company' == get_post_type( $args[ 'company_id' ] ) ) {
			$company_data[ 'ID' ] = intval( $args[ 'company_id' ] );
			$company_id           = wp_update_post( $company_data );
		} else {
			$company_id = wp_insert_post( $company_data );
		}

		if ( ! is_wp_error( $company_id ) ) {
			// delete the old logo & cover image
			if ( isset( $args[ '_logo' ] ) ) {
				$old_image = jlt_get_post_meta( $company_id, '_logo' );
				if ( $old_image != $args[ '_logo' ] ) {
					if ( is_numeric( $old_image ) ) {
						wp_delete_attachment( $old_image, true );
					}
				}
			}
			if ( isset( $args[ '_cover_image' ] ) ) {
				$old_image = jlt_get_post_meta( $company_id, '_cover_image' );
				if ( $old_image != $args[ '_cover_image' ] ) {
					if ( is_numeric( $old_image ) ) {
						wp_delete_attachment( $old_image, true );
					}
				}
			}

			jlt_company_save_custom_fields( $company_id, $args );
			jlt_company_save_complete_address_field( $company_id );

			if ( ! empty( $company_name ) ) {
				wp_update_user( array( 'ID' => $user_id, 'display_name' => $company_name ) );
			}

			if ( empty( $args[ 'company_id' ] ) ) {
				update_user_meta( $user_id, 'employer_company', $company_id );
				$package_data = jlt_get_job_posting_info( $user_id );
				if ( isset( $package_data[ 'company_featured' ] ) && $package_data[ 'company_featured' ] ) {
					update_post_meta( $company_id, '_company_featured', 'yes' );
				}

				do_action( 'jlt_new_company', $company_id, $args );
			}

			do_action( 'jlt_save_company', $company_id, $args );

			return $company_id;
		} else {
			jlt_message_add( $company_id->get_error_message(), 'error' );

			return false;
		}

		return $company_id;
	}
}

new JLT_Form_Company();