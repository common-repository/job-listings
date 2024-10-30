<?php

class JLT_Job_Form_Hander {

	protected $job_id;

	protected static $_instance = null;

	public static function instance() {
		if ( is_null( self::$_instance ) ) {
			self::$_instance = new self();
		}

		return self::$_instance;
	}

	public function __construct() {
		add_action( 'init', array( $this, 'job_form_action' ), 20 );
		add_action( 'init', array( $this, 'job_publish_form_action' ), 20 );

		add_action( 'wp_ajax_add_new_job_location', array( __CLASS__, 'add_new_job_location_action' ) );

		$this->job_id = ! empty( $_REQUEST[ 'job_id' ] ) ? absint( $_REQUEST[ 'job_id' ] ) : 0;
	}

	public static function job_form_action() {
		if ( ! is_user_logged_in() ) {
			return;
		}
		if ( 'POST' !== strtoupper( $_SERVER[ 'REQUEST_METHOD' ] ) ) {
			return;
		}
		if ( empty( $_POST[ 'action' ] ) || 'job_form' !== $_POST[ 'action' ] || empty( $_POST[ '_wpnonce' ] ) || ! wp_verify_nonce( $_POST[ '_wpnonce' ], 'edit-job' ) ) {
			return;
		}

		$_POST[ 'post_status' ] = 'draft';

		$job_id = self::save_job( $_POST );

		do_action( 'jlt_after_save_job', $job_id );

		$job_action = sanitize_text_field( $_POST[ 'job_action' ] );

		if ( 'edit_job' == $job_action ) {
			jlt_message_add( __( 'Job saved', 'job-listings' ) );
		} else {
			if ( is_wp_error( $job_id ) ) {
				jlt_message_add( __( 'You can not post job', 'job-listings' ), 'error' );
				wp_safe_redirect( JLT_Member::get_member_page_url() );
				exit;
			} else {
				$location = array( 'action' => 'preview_job' );
				wp_safe_redirect( esc_url_raw( add_query_arg( $location ) ) );
				exit;
			}
		}
	}

	public static function job_publish_form_action() {
		if ( ! is_user_logged_in() ) {
			return;
		}
		if ( 'POST' !== strtoupper( $_SERVER[ 'REQUEST_METHOD' ] ) ) {
			return;
		}
		if ( empty( $_POST[ 'action' ] ) || 'preview_job' !== sanitize_text_field( $_POST[ 'action' ] ) || empty( $_POST[ '_wpnonce' ] ) || ! wp_verify_nonce( $_POST[ '_wpnonce' ], 'edit-job' ) ) {
			return;
		}
		if ( empty( $_POST[ 'job_id' ] ) ) {
			return;
		}

		$job_id           = intval( sanitize_text_field( $_POST[ 'job_id' ] ) );
		$job_need_approve = jlt_get_job_setting( 'job_approve', '' ) == 'yes';

		do_action( 'jlt_job_publish_form_action', $job_id, $job_need_approve );

		// Update job count for user
		jlt_increase_job_posting_count( get_current_user_id() );

		if ( ! $job_need_approve ) {
			wp_update_post( array(
				'ID'          => $job_id,
				'post_status' => 'publish',
			) );
			jlt_set_job_expired( $job_id );
		} else {
			wp_update_post( array(
				'ID'          => $job_id,
				'post_status' => 'pending',
			) );
			update_post_meta( $job_id, '_in_review', 1 );
		}

		jlt_message_add( __( 'Job successfully added', 'job-listings' ) );

		jlt_job_send_notification( $job_id );

		jlt_force_redirect( JLT_Member::get_endpoint_url( 'manage-job' ) );
	}

	public static function save_job( $args = array() ) {
		try {
			$defaults = array(
				'job_id'                => '',
				'position'              => '',
				'desc'                  => '',
				'feature'               => 'no',
				//isset($args['feature']) && $args['feature'] == 'yes' ? $args['feature'] : 'no',
				'location'              => '',
				'type'                  => '',
				'category'              => '',
				'cover_image'           => '',
				'_use_company_location' => true,
				'post_status'           => 'draft',
			);
			$args     = wp_parse_args( $args, $defaults );

			$job_data = array(
				'post_title'     => jlt_kses( $args[ 'position' ] ),
				'post_content'   => jlt_kses( $args[ 'desc' ], true ),
				'post_type'      => 'job',
				'comment_status' => 'closed',
			);
			$job_id   = new WP_Error();

			if ( ! empty( $args[ 'job_id' ] ) && $args[ 'job_id' ] > 0 ) {
				$job_data[ 'ID' ] = $args[ 'job_id' ];
				$job_id           = wp_update_post( $job_data );
			} else {
				$job_id         = wp_insert_post( $job_data );
				$submitting_key = uniqid();
				setcookie( 'jlt-submitting-job-id', $job_id, false, COOKIEPATH, COOKIE_DOMAIN, false );
				setcookie( 'jlt-submitting-job-key', $submitting_key, false, COOKIEPATH, COOKIE_DOMAIN, false );
				update_post_meta( $job_id, '_submitting_key', $submitting_key );
			}
			if ( ! is_wp_error( $job_id ) ) {
				if ( isset( $args[ 'type' ] ) && ! empty( $args[ 'type' ] ) ) {
					$types = is_array( $args[ 'type' ] ) ? $args[ 'type' ] : array( $args[ 'type' ] );
					foreach ( $types as $cat ) {
						$types_validated[] = jlt_kses( $cat );
					}
					wp_set_post_terms( $job_id, $types_validated, 'job_type', false );
				}

				if ( isset( $args[ 'category' ] ) && ! empty( $args[ 'category' ] ) ) {
					$categories           = is_array( $args[ 'category' ] ) ? $args[ 'category' ] : array( $args[ 'category' ] );
					$categories_validated = array();
					foreach ( $categories as $cat ) {
						$categories_validated[] = jlt_kses( $cat );
					}
					wp_set_post_terms( $job_id, $categories_validated, 'job_category', false );
				}

				if ( isset( $args[ 'tag' ] ) && ! empty( $args[ 'tag' ] ) ) {
					$tags           = is_array( $args[ 'tag' ] ) ? $args[ 'tag' ] : array( $args[ 'tag' ] );
					$tags_validated = array();
					foreach ( $tags as $tag ) {
						$tags_validated[] = jlt_kses( $tag );
					}
					wp_set_post_terms( $job_id, $tags_validated, 'job_tag', false );
				}

				if ( ! empty( $args[ 'location' ] ) ) {
					$location_arr = array();
					foreach ( (array) $args[ 'location' ] as $l ) {
						$slug = trim( $l );
						if ( ( $t = get_term_by( 'slug', sanitize_title( $slug ), 'job_location' ) ) ) {
							$location_arr[] = $t->term_id;
						} else {
							$n_l = wp_insert_term( $slug, 'job_location' );
							if ( $n_l && ! is_wp_error( $n_l ) && ( $loca = get_term( absint( $n_l[ 'term_id' ] ), 'job_location' ) ) ) {
								$location_arr[] = $loca->term_id;
							}
						}
					}
					if ( ! empty( $location_arr ) ) {
						wp_set_post_terms( $job_id, $location_arr, 'job_location', false );
					}
				}

				if ( isset( $args[ '_use_company_location' ] ) && ! empty( $args[ '_use_company_location' ] ) ) {
					$use_company_location = strtotime( $args[ '_use_company_location' ] );
					if ( ! $use_company_location ) {
						$use_company_location = jlt_kses( $args[ '_use_company_location' ] );
					}
					update_post_meta( $job_id, '_use_company_location', $use_company_location );
				}

				if ( isset( $args[ 'cover_image' ] ) ) {
					$old_image = jlt_get_post_meta( $job_id, '_cover_image' );
					if ( $old_image != $args[ 'cover_image' ] ) {
						update_post_meta( $job_id, '_cover_image', jlt_kses( $args[ 'cover_image' ] ) );
						if ( is_numeric( $old_image ) ) {
							wp_delete_attachment( $old_image, true );
						}
					}
				}

				$custom_apply_link = jlt_get_setting( 'jlt_job_linkedin', 'custom_apply_link' );
				if ( $custom_apply_link == 'employer' ) {
					update_post_meta( $job_id, '_custom_application_url', jlt_kses( $args[ 'custom_application_url' ] ) );
				}

				// Update custom fields
				jlt_job_save_custom_fields( $job_id, $args );

				//
				do_action( 'jlt_save_job', $job_id );
			}

			return $job_id;
		} catch ( Exception $e ) {
			throw new Exception( $e->getMessage() );
		}
	}

	public static function display( $action, $next_step ) {
		if ( ! is_user_logged_in() ) {
			jlt_get_template( 'form/login.php' );

			return;
		}
		$company_id = jlt_get_employer_company();
		if ( empty( $company_id ) ) {
			jlt_message_add( __( 'You must have a previous profile company.', 'job-listings' ) );
			wp_safe_redirect( JLT_Member::get_company_profile_url() );
			exit;
		}

		$job_id = self::get_job_id();

		switch ( $action ) {
			case 'login':
				if ( JLT_Member::is_logged_in() ) {
					jlt_posting_job_redirect_next_step( $next_step );
				}
				break;
			case 'post_job':
				do_action( 'jlt_posting_job_action_post_job', $action, $next_step, $job_id );
				self::step_submit( $job_id );
				break;
			case 'preview_job':
				do_action( 'jlt_posting_job_action_step_preview', $action, $next_step, $job_id );
				self::step_preview( $job_id );
				break;
			case $action:
				do_action( 'jlt_posting_job_action_' . $action, $action, $next_step, $job_id );
				break;
		}
	}

	public static function get_job_id() {
		$job_id = 0;
		if ( ! empty( $_COOKIE[ 'jlt-submitting-job-id' ] ) && ! empty( $_COOKIE[ 'jlt-submitting-job-key' ] ) ) {
			$job_id_cc  = intval( $_COOKIE[ 'jlt-submitting-job-id' ] );
			$job_status = get_post_status( $job_id_cc );

			if ( ( $job_status === 'draft' ) && get_post_meta( $job_id_cc, '_submitting_key', true ) === $_COOKIE[ 'jlt-submitting-job-key' ] ) {
				$job_id = $job_id_cc;
			}
		}

		return $job_id;
	}

	public static function step_submit( $job_id ) {

		$job       = $job_id > 0 ? get_post( $job_id ) : null;
		$job_title = ! empty( $job ) ? $job->post_title : '';

		$default_job_content = jlt_get_job_setting( 'default_job_content', '' );
		$job_content         = ! empty( $job ) ? $job->post_content : $default_job_content;

		$form_param = array(
			'job'         => $job,
			'job_id'      => $job_id,
			'job_title'   => $job_title,
			'job_content' => $job_content,
			'button_text' => __( 'Preview Job', 'job-listings' ),
			'form_title'  => __( 'Submit Job', 'job-listings' ),
			'job_action'  => 'submit_job',
		);
		jlt_get_template( 'form/job-submit.php', $form_param );
	}

	public static function step_edit() {

		$job_id = isset( $_GET[ 'job_id' ] ) ? intval( $_GET[ 'job_id' ] ) : 0;

		if ( empty( $job_id ) ) {
			jlt_message_add( __( 'Mising Job ID.', 'job-listings' ), 'error' );
			wp_safe_redirect( JLT_Member::get_endpoint_url( 'manage-job' ) );
			exit;
		}

		if ( ! JLT_Member::can_edit_job( $job_id ) ) {
			jlt_message_add( __( 'You can\'t edit this job.', 'job-listings' ), 'error' );
			wp_safe_redirect( JLT_Member::get_endpoint_url( 'manage-job' ) );
			exit;
		}

		$job       = $job_id > 0 ? get_post( $job_id ) : null;
		$job_title = ! empty( $job ) ? $job->post_title : '';

		$default_job_content = jlt_get_job_setting( 'default_job_content', '' );
		$job_content         = ! empty( $job ) ? $job->post_content : $default_job_content;

		$atts = array(
			'job'         => $job,
			'job_id'      => $job_id,
			'job_title'   => $job_title,
			'job_content' => $job_content,
			'button_text' => __( 'Save Job', 'job-listings' ),
			'form_title'  => __( 'Edit Job', 'job-listings' ),
			'job_action'  => 'edit_job',
		);

		jlt_get_template( 'form/job-submit.php', $atts );
	}

	public static function step_preview( $job_id ) {
		global $post;

		if ( $job_id && $job_id > 0 ) {
			$job_edit_url = add_query_arg( 'action', 'post_job' );
			$form_param   = array(
				'job_edit_url' => $job_edit_url,
				'job_id'       => $job_id,
			);

			$post = get_post( $job_id );

			setup_postdata( $post );
			jlt_get_template( 'form/job-preview.php', $form_param );

			wp_reset_postdata();
		}
	}

	public static function submit_hander( $job_id ) {
		if ( ! empty( $_POST[ 'submit_job_submit' ] ) ) {
			$job = get_post( $job_id );
			if ( in_array( $job->post_status, array( 'draft' ) ) ) {

				$update_job                    = array();
				$update_job[ 'ID' ]            = $job->ID;
				$update_job[ 'post_status' ]   = apply_filters( 'jlt_submit_job_post_status', get_option( 'jlt_job_submission_requires_approval' ) ? 'pending' : 'publish', $job );
				$update_job[ 'post_date' ]     = current_time( 'mysql' );
				$update_job[ 'post_date_gmt' ] = current_time( 'mysql', 1 );
				$update_job[ 'post_author' ]   = get_current_user_id();

				return wp_update_post( $update_job );
			}
		}
	}

	public static function current_action() {
		if ( isset( $_REQUEST[ 'action' ] ) && - 1 != $_REQUEST[ 'action' ] ) {
			return $_REQUEST[ 'action' ];
		}

		if ( isset( $_REQUEST[ 'action2' ] ) && - 1 != $_REQUEST[ 'action2' ] ) {
			return $_REQUEST[ 'action2' ];
		}
	}

	public static function add_new_job_location_action() {
		if ( ! is_user_logged_in() ) {
			$result[ 'success' ] = false;
		}

		if ( 'POST' !== strtoupper( $_SERVER[ 'REQUEST_METHOD' ] ) ) {
			$result[ 'success' ] = false;
		}

		if ( ! is_user_logged_in() ) {
			$result[ 'success' ] = false;
		}

		check_ajax_referer( 'jlt-member-security', 'security' );
		$new_location = isset( $_POST[ 'location' ] ) ? trim( stripslashes( $_POST[ 'location' ] ) ) : '';
		if ( ! empty( $new_location ) ) {
			$result = array();
			if ( ( $t = get_term_by( 'slug', sanitize_text_field( $new_location ), 'job_location' ) ) ) {
				$result[ 'success' ]        = true;
				$result[ 'location_id' ]    = $t->term_id;
				$result[ 'location_slug' ]  = $t->slug;
				$result[ 'location_value' ] = $t->slug;
				$result[ 'location_title' ] = $t->name;
			} else {
				$n_l = wp_insert_term( $new_location, 'job_location' );
				if ( $n_l && ! is_wp_error( $n_l ) && ( $loca = get_term( absint( $n_l[ 'term_id' ] ), 'job_location' ) ) ) {
					$result[ 'success' ]        = true;
					$result[ 'location_id' ]    = $loca->term_id;
					$result[ 'location_slug' ]  = $loca->slug;
					$result[ 'location_value' ] = $loca->slug;
					$result[ 'location_title' ] = $loca->name;
				}
			}
		}
		wp_send_json( $result );
	}

}

JLT_Job_Form_Hander::instance();