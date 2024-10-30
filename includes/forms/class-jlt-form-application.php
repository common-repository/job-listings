<?php

class JLT_Form_Application {
	public function __construct() {
		add_action( 'init', array( __CLASS__, 'apply_job_action' ) );
		add_action( 'init', array( __CLASS__, 'manage_application_action' ) );
		add_action( 'wp_ajax_jlt_approve_reject_application_modal', array(
			__CLASS__,
			'approve_reject_application_modal',
		) );

		add_action( 'wp_ajax_jlt_employer_message_application_modal', array(
			__CLASS__,
			'employer_message_application_modal',
		) );

		add_action( 'wp_ajax_jlt_application_response_modal', array(
			__CLASS__,
			'jlt_employer_application_response_modal',
		) );

		add_action( 'init', array( __CLASS__, 'manage_job_applied_action' ) );
	}

	public static function apply_job_action() {
		if ( 'POST' !== strtoupper( $_SERVER[ 'REQUEST_METHOD' ] ) ) {
			return;
		}

		if ( empty( $_POST[ 'action' ] ) || 'apply_job' !== $_POST[ 'action' ] || empty( $_POST[ '_wpnonce' ] ) || ! wp_verify_nonce( $_POST[ '_wpnonce' ], 'jlt-apply-job' ) ) {
			return;
		}

		try {
			// Get data from the form
			$candidate_name  = sanitize_text_field( $_POST[ 'candidate_name' ] );
			$candidate_email = sanitize_email( $_POST[ 'candidate_email' ] );
			$job_id          = intval( sanitize_text_field( $_POST[ 'job_id' ] ) );
			$job             = get_post( $job_id );
			if ( empty( $job_id ) || ! $job || 'job' !== $job->post_type ) {
				jlt_message_add( __( 'Invalid job', 'job-listings' ), 'error' );
				wp_safe_redirect( get_permalink( $job_id ) );
				exit();
			}

			do_action( 'new_job_application_before', $job_id );

			if ( empty( $candidate_name ) ) {
				jlt_message_add( __( 'Please enter your name', 'job-listings' ), 'error' );
				wp_safe_redirect( esc_url_raw( get_permalink( $job_id ) ) );
				exit();
			}
			if ( empty( $candidate_email ) || ! is_email( $candidate_email ) ) {
				jlt_message_add( __( 'Please provide a valid email address', 'job-listings' ), 'error' );
				wp_safe_redirect( esc_url_raw( get_permalink( $job_id ) ) );
				exit();
			}

			$application_args = array(
				'post_type'      => 'application',
				'posts_per_page' => - 1,
				'post_status'    => array( 'publish', 'pending', 'rejected' ),
				'post_parent'    => $job_id,
				'meta_query'     => array(
					array(
						'key'   => '_candidate_email',
						'value' => $candidate_email,
					),
				),
			);

			$application = new WP_Query( $application_args );
			if ( $application->post_count ) {
				jlt_message_add( __( 'You have already applied for this job', 'job-listings' ), 'error' );
				wp_safe_redirect( esc_url_raw( get_permalink( $job_id ) ) );
				exit();
			}

			$meta   = array();
			$fields = jlt_get_application_custom_fields();

			if ( ! empty( $fields ) ) {
				foreach ( $fields as $field ) {

					$field_id = jlt_application_custom_fields_name( $field[ 'name' ], $field );

					if ( isset( $field[ 'required' ] ) && $field[ 'required' ] ) {
						if ( ! isset( $_POST[ $field_id ] ) or $_POST[ $field_id ] == '' ) {
							jlt_message_add( sprintf( __( '%s is required field.', 'job-listings' ), $field[ 'label' ] ), 'error' );
							wp_safe_redirect( get_permalink( $job_id ) );
							exit;
						}
					}
					if ( $field[ 'name' ] == 'application_message' ) {
						$application_message = '';
						if ( isset( $_POST[ 'application_message' ] ) ) {
							$application_message = wp_kses( $_POST[ 'application_message' ], jlt_html_allowed() );

							if ( isset( $fields[ 'application_message' ] ) ) {
								unset( $fields[ 'application_message' ] );
							}
						}
					}

					if ( isset( $_POST[ $field_id ] ) ) {
						$meta[ $field_id ] = jlt_sanitize_field( $_POST[ $field_id ], $field );
					}
				}
			}

			do_action( 'jlt_before_hander_application', $job_id );

			if ( ! $application_id = JLT_Application::new_job_application( $job_id, $candidate_name, $candidate_email, $application_message, $meta ) ) {
				jlt_message_add( __( 'Could not add a new job application', 'job-listings' ), 'error' );
				wp_safe_redirect( get_permalink( $job_id ) );
				exit();
			}

			do_action( 'jlt_after_hander_application', $application_id, $job_id );

			jlt_message_add( __( 'Your job application has been submitted successfully', 'job-listings' ) );
			wp_safe_redirect( get_permalink( $job_id ) );
			exit();
		} catch ( Exception $e ) {
			jlt_message_add( $e->getMessage(), 'error' );
			wp_safe_redirect( get_permalink( $job_id ) );
			exit();
		}

		return;
	}

	public static function upload_file( $field_key, $allowed_file_types = array(), $is_multiple = false ) {
		if ( isset( $_FILES[ $field_key ] ) && ! empty( $_FILES[ $field_key ] ) && ! empty( $_FILES[ $field_key ][ 'name' ] ) ) {
			include_once( ABSPATH . 'wp-admin/includes/file.php' );
			include_once( ABSPATH . 'wp-admin/includes/media.php' );

			$file               = $_FILES[ $field_key ];
			$all_mime_types     = get_allowed_mime_types();
			$allowed_mime_types = array();

			if ( ! empty( $allowed_file_types ) ) {
				foreach ( $allowed_file_types as $type ) {
					foreach ( $all_mime_types as $key => $value ) {
						if ( $type == $key || in_array( $type, explode( '|', $key ) ) ) {
							$allowed_mime_types[ $type ] = $all_mime_types[ $key ];
						}
					}
				}
			} else {
				$allowed_mime_types = $all_mime_types;
			}

			if ( $is_multiple && is_array( $file[ 'name' ] ) ) {
				$results = array();
				foreach ( $file[ 'name' ] as $index => $name ) {
					if ( empty( $name ) ) {
						continue;
					}
					$a_file = array(
						'name'     => $name,
						'type'     => $file[ 'type' ][ $index ],
						'tmp_name' => $file[ 'tmp_name' ][ $index ],
						'error'    => $file[ 'error' ][ $index ],
						'size'     => $file[ 'size' ][ $index ],
					);

					$result = self::_process_a_file( $a_file, $allowed_mime_types );
					if ( ! empty( $result ) ) {
						$results[] = $result;
					}
				}

				return ( empty( $results ) ? false : $results );
			} else {
				if ( ! empty( $file[ 'name' ] ) ) {
					return self::_process_a_file( $file, $allowed_mime_types );
				}
			}
		}

		return false;
	}

	private static function _process_a_file( $file, $allowed_mime_types = array() ) {
		if ( ! in_array( $file[ "type" ], $allowed_mime_types ) ) {
			throw new Exception( sprintf( __( 'Only the following file types are allowed: %s', 'job-listings' ), implode( ', ', array_keys( $allowed_mime_types ) ) ) );
		}

		add_filter( 'upload_dir', array( __CLASS__, 'upload_dir' ) );
		$upload = wp_handle_upload( $file, array( 'test_form' => false ) );
		remove_filter( 'upload_dir', array( __CLASS__, 'upload_dir' ) );

		if ( ! empty( $upload[ 'error' ] ) ) {
			return false;
		} else {
			return $upload[ 'url' ];
		}
	}

	public static function upload_dir( $pathdata ) {
		$subdir               = '/jobmonster/' . uniqid();
		$pathdata[ 'path' ]   = str_replace( $pathdata[ 'subdir' ], $subdir, $pathdata[ 'path' ] );
		$pathdata[ 'url' ]    = str_replace( $pathdata[ 'subdir' ], $subdir, $pathdata[ 'url' ] );
		$pathdata[ 'subdir' ] = str_replace( $pathdata[ 'subdir' ], $subdir, $pathdata[ 'subdir' ] );

		return $pathdata;
	}

	public static function current_action() {
		if ( isset( $_REQUEST[ 'action' ] ) && - 1 != $_REQUEST[ 'action' ] ) {
			return $_REQUEST[ 'action' ];
		}

		if ( isset( $_REQUEST[ 'action2' ] ) && - 1 != $_REQUEST[ 'action2' ] ) {
			return $_REQUEST[ 'action2' ];
		}
	}

	public static function manage_application_action() {
		if ( ! is_user_logged_in() ) {
			return;
		}
		$action = self::current_action();
		if ( ! empty( $action ) && ! empty( $_REQUEST[ '_wpnonce' ] ) && wp_verify_nonce( $_REQUEST[ '_wpnonce' ], 'application-manage-action' ) ) {
			if ( isset( $_REQUEST[ 'application_id' ] ) ) {
				$ids = explode( ',', $_REQUEST[ 'application_id' ] );
			} elseif ( ! empty( $_REQUEST[ 'ids' ] ) ) {
				$ids = array_map( 'intval', $_REQUEST[ 'ids' ] );
			}
			$msg_title = isset( $_REQUEST[ 'title' ] ) ? trim( $_REQUEST[ 'title' ] ) : '';
			$msg_body  = isset( $_REQUEST[ 'message' ] ) ? wp_kses_post( trim( stripslashes( $_REQUEST[ 'message' ] ) ) ) : '';
			$employer  = wp_get_current_user();

			if ( is_multisite() ) {
				$blogname = $GLOBALS[ 'current_site' ]->site_name;
			} else {
				$blogname = wp_specialchars_decode( get_option( 'blogname' ), ENT_QUOTES );
			}

			try {
				switch ( $action ) {
					case 'approve':
						$approved = 0;
						foreach ( (array) $ids as $application_id ) {
							if ( ! JLT_Application::can_edit_application( get_current_user_id(), $application_id ) ) {
								continue;
							}
							$application = get_post( $application_id );
							if ( ! $application || $application->post_status != 'pending' ) {
								continue;
							}
							$job_id        = $application->post_parent;
							$company_id    = jlt_get_job_company( $job_id );
							$company_title = ! empty( $company_id ) ? get_the_title( $company_id ) : $employer->display_name;

							if ( ! wp_update_post( array(
								'ID'          => $application_id,
								'post_status' => 'publish',
							) )
							) {
								wp_die( __( 'Error when approving application.', 'job-listings' ) );
							}
							update_post_meta( $application_id, '_employer_message_title', $msg_title );
							update_post_meta( $application_id, '_employer_message_body', $msg_body );

							do_action( 'manage_application_action_approve', $application_id );

							$to             = jlt_get_post_meta( $application_id, '_candidate_email' );
							$candidate_name = get_the_title( $application_id );
							if ( is_email( $to ) && jlt_email_get_setting( 'candidate_job_application_approved', 'active', 1 ) ) {
								//candidate email

								$subject = jlt_email_get_setting( 'candidate_job_application_approved', 'subject' );

								$array_subject = array(
									'[job_title]'       => get_the_title( $job_id ),
									'[job_company]'     => $company_title,
									'[responded_title]' => $msg_title,
									'[site_name]'       => $blogname,
									'[site_url]'        => esc_url( home_url( '' ) ),
								);

								$subject = str_replace( array_keys( $array_subject ), $array_subject, $subject );

								$array_message = array(
									'[job_title]'              => get_the_title( $job_id ),
									'[job_url]'                => get_permalink( $job_id ),
									'[job_company]'            => $company_title,
									'[candidate_name]'         => $candidate_name,
									'[responded_title]'        => $msg_title,
									'[responded]'              => $msg_body,
									'[application_manage_url]' => JLT_Member::get_endpoint_url( 'manage-job-applied' ),
									'[site_name]'              => $blogname,
									'[site_url]'               => esc_url( home_url( '' ) ),
								);

								$message = jlt_email_get_setting( 'candidate_job_application_approved', 'content' );
								$message = str_replace( array_keys( $array_message ), $array_message, $message );

								$subject = jlt_et_custom_field( 'job', $job_id, $subject );
								$message = jlt_et_custom_field( 'job', $job_id, $message );

								jlt_mail( $to, $subject, $message, array(), 'jlt_notify_job_apply_approve_candidate' );
							}
							$approved ++;
						}
						if ( $approved > 0 ) {
							jlt_message_add( sprintf( _n( 'Approved %s application', 'Approved %s applications', $approved, 'job-listings' ), $approved ) );
						} else {
							jlt_message_add( __( 'No application approved', 'job-listings' ) );
						}
						wp_safe_redirect( JLT_Member::get_endpoint_url( 'manage-application' ) );
						die;
						break;
					case 'reject':
						$rejected = 0;
						foreach ( (array) $ids as $application_id ) {
							if ( ! JLT_Application::can_edit_application( get_current_user_id(), $application_id ) ) {
								continue;
							}

							$application = get_post( $application_id );
							if ( ! $application || $application->post_status != 'pending' ) {
								continue;
							}
							$job_id        = $application->post_parent;
							$company_id    = jlt_get_job_company( $job_id );
							$company_title = ! empty( $company_id ) ? get_the_title( $company_id ) : $employer->display_name;
							if ( ! wp_update_post( array(
								'ID'          => $application_id,
								'post_status' => 'rejected',
							) )
							) {
								wp_die( __( 'Error when rejecting application.', 'job-listings' ) );
							}
							update_post_meta( $application_id, '_employer_message_title', $msg_title );
							update_post_meta( $application_id, '_employer_message_body', $msg_body );

							do_action( 'manage_application_action_reject', $application_id );

							$to             = jlt_get_post_meta( $application_id, '_candidate_email' );
							$candidate_name = get_the_title( $application_id );
							if ( is_email( $to ) && jlt_email_get_setting( 'candidate_job_application_rejected', 'active', 1 ) ) {
								//candidate email
								$subject = jlt_email_get_setting( 'candidate_job_application_rejected', 'subject' );

								$array_subject = array(
									'[job_title]'       => get_the_title( $job_id ),
									'[job_company]'     => $company_title,
									'[responded_title]' => $msg_title,
									'[site_name]'       => $blogname,
									'[site_url]'        => esc_url( home_url( '' ) ),
								);

								$subject = str_replace( array_keys( $array_subject ), $array_subject, $subject );

								$array_message = array(
									'[job_title]'              => get_the_title( $job_id ),
									'[job_url]'                => get_permalink( $job_id ),
									'[job_company]'            => $company_title,
									'[candidate_name]'         => $candidate_name,
									'[responded_title]'        => $msg_title,
									'[responded]'              => $msg_body,
									'[application_manage_url]' => JLT_Member::get_endpoint_url( 'manage-job-applied' ),
									'[site_name]'              => $blogname,
									'[site_url]'               => esc_url( home_url( '' ) ),
								);

								$message = jlt_email_get_setting( 'candidate_job_application_rejected', 'content' );
								$message = str_replace( array_keys( $array_message ), $array_message, $message );

								$subject = jlt_et_custom_field( 'job', $job_id, $subject );
								$message = jlt_et_custom_field( 'job', $job_id, $message );

								jlt_mail( $to, $subject, $message, array(), 'jlt_notify_job_apply_reject_candidate' );
							}
							$rejected ++;
						}
						if ( $rejected > 0 ) {
							jlt_message_add( sprintf( _n( 'Rejected %s application', 'Rejected %s applications', $rejected, 'job-listings' ), $rejected ) );
						} else {
							jlt_message_add( __( 'No application rejected', 'job-listings' ) );
						}
						wp_safe_redirect( JLT_Member::get_endpoint_url( 'manage-application' ) );
						die;
						break;
					case 'delete':
						$deleted = 0;
						foreach ( (array) $ids as $application_id ) {
							if ( ! JLT_Application::can_trash_application( get_current_user_id(), $application_id ) ) {
								continue;
							}

							// if ( !wp_delete_post($application_id) )
							// Version 2.7.0 Making application inactive instead of move to trash.
							if ( ! wp_update_post( array( 'ID' => $application_id, 'post_status' => 'inactive' ) ) ) {
								wp_die( __( 'Error when deleting application.', 'job-listings' ) );
							}

							$deleted ++;
						}
						if ( $deleted > 0 ) {
							jlt_message_add( sprintf( _n( 'Deleted %s application', 'Deleted %s applications', $deleted, 'job-listings' ), $deleted ) );
						} else {
							jlt_message_add( __( 'No application deleted', 'job-listings' ) );
						}
						do_action( 'manage_application_action_delete', $ids );
						wp_safe_redirect( JLT_Member::get_endpoint_url( 'manage-application' ) );
						die;
						break;
					default:
						break;
				}
			} catch ( Exception $e ) {
				throw new Exception( $e->getMessage() );
			}
		}
	}

	public static function approve_reject_application_modal() {

		if ( ! is_user_logged_in() ) {
			die( - 1 );
		}

		check_ajax_referer( 'jlt-member-security', 'security' );

		$application_id = isset( $_POST[ 'application_id' ] ) ? intval( sanitize_text_field( $_POST[ 'application_id' ] ) ) : 0;
		$hander         = isset( $_POST[ 'hander' ] ) ? sanitize_text_field( $_POST[ 'hander' ] ) : '';
		ob_start();
		JLT_Member::modal_application( $application_id, $hander );
		$output = ob_get_clean();
		if ( empty( $output ) ) {
			die( - 1 );
		} else {
			echo trim( $output );
		}
		die();
	}

	public static function employer_message_application_modal() {

		if ( ! is_user_logged_in() ) {
			die( - 1 );
		}

		check_ajax_referer( 'jlt-member-security', 'security' );

		$application_id = isset( $_POST[ 'application_id' ] ) ? absint( $_POST[ 'application_id' ] ) : 0;

		echo self::modal_employer_message( $application_id );
		die();
	}

	public static function modal_employer_message( $application_id ) {
		$application = get_post( $application_id );
		if ( $application->post_type != 'application' ) {
			return '';
		}

		// -- get id candidate
		$user = wp_get_current_user();
		// -- default meta
		$key_meta = '_check_view_applied';
		// get value in meta -> array
		$check_view = get_user_meta( $user->ID, $key_meta, true ) ? (array) get_user_meta( $user->ID, $key_meta, true ) : array();

		$arr_value = array_merge( $check_view, array( $application_id ) );

		if ( ! in_array( $application_id, $check_view ) ):
			update_user_meta( $user->ID, $key_meta, $arr_value );
		endif;

		// Candidate Email

		$candidate_email = jlt_get_post_meta( $application_id, '_candidate_email' );

		$attrs = array(
			'application_id'  => $application_id,
			'application'     => $application,
			'candidate_email' => $candidate_email,
		);

		ob_start();
		jlt_get_template( 'member/manage-application-message.php', $attrs );

		return ob_get_clean();
	}

	public static function jlt_employer_application_response_modal( $application_id ) {

		if ( ! is_user_logged_in() ) {
			die( - 1 );
		}

		check_ajax_referer( 'jlt-member-security', 'security' );

		$application_id = isset( $_POST[ 'application_id' ] ) ? intval( sanitize_text_field( $_POST[ 'application_id' ] ) ) : 0;

		$application = get_post( $application_id );
		if ( $application->post_type != 'application' ) {
			return '';
		}

		$status = $application->post_status;

		$statuses = JLT_Application::get_application_status();
		if ( isset( $statuses[ $status ] ) ) {
			$status = $statuses[ $status ];
		} else {
			$status = __( 'Inactive', 'job-listings' );
		}

		$attrs = array(
			'title'   => jlt_get_post_meta( $application_id, '_employer_message_title', '' ),
			'message' => jlt_get_post_meta( $application_id, '_employer_message_body', '' ),
			'status'  => $status,
		);

		ob_start();
		jlt_get_template( 'member/manage-application-response.php', $attrs );
		echo ob_get_clean();
		die();
	}

	public static function manage_job_applied_action() {
		if ( ! is_user_logged_in() ) {
			return;
		}
		$action = self::current_action();
		if ( ! empty( $action ) && ! empty( $_REQUEST[ '_wpnonce' ] ) && wp_verify_nonce( $_REQUEST[ '_wpnonce' ], 'job-applied-manage-action' ) ) {
			if ( isset( $_REQUEST[ 'application_id' ] ) ) {
				$ids = explode( ',', $_REQUEST[ 'application_id' ] );
			} elseif ( ! empty( $_REQUEST[ 'ids' ] ) ) {
				$ids = array_map( 'intval', $_REQUEST[ 'ids' ] );
			}
			try {
				switch ( $action ) {
					case 'withdraw':
						$withdrawn = 0;
						foreach ( (array) $ids as $application_id ) {
							if ( ! JLT_Application::can_trash_application( get_current_user_id(), $application_id ) ) {
								continue;
							}

							if ( ! wp_update_post( array( 'ID' => $application_id, 'post_status' => 'inactive' ) ) ) {
								wp_die( __( 'Error when withdrawing application.', 'job-listings' ) );
							}

							$withdrawn ++;
						}
						if ( $withdrawn > 0 ) {
							jlt_message_add( sprintf( _n( 'Withdrawn %s application', 'Withdrawn %s applications', $withdrawn, 'job-listings' ), $withdrawn ) );
						} else {
							jlt_message_add( __( 'No application withdrawn', 'job-listings' ) );
						}
						do_action( 'manage_application_action_withdraw', $ids );

						wp_safe_redirect( JLT_Member::get_endpoint_url( 'manage-job-applied' ) );
						exit;

						break;
					case 'delete':
						$deleted = 0;
						foreach ( (array) $ids as $application_id ) {
							if ( ! JLT_Application::can_delete_application( get_current_user_id(), $application_id ) ) {
								continue;
							}

							if ( ! wp_delete_post( $application_id ) ) {
								wp_die( __( 'Error when deleting application.', 'job-listings' ) );
							}

							$deleted ++;
						}
						if ( $deleted > 0 ) {
							jlt_message_add( sprintf( _n( 'Deleted %s application', 'Deleted %s applications', $deleted, 'job-listings' ), $deleted ) );
						} else {
							jlt_message_add( __( 'No application deleted', 'job-listings' ) );
						}

						do_action( 'manage_application_action_delete', $ids );

						wp_safe_redirect( JLT_Member::get_endpoint_url( 'manage-job-applied' ) );
						exit;

						break;
					default:
						break;
				}
			} catch ( Exception $e ) {
				throw new Exception( $e->getMessage() );
			}
		}
	}

}

new JLT_Form_Application();