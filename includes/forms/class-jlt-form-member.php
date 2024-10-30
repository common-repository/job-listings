<?php

class JLT_Form_Member {
	public function __construct() {

		add_action( 'init', array( __CLASS__, 'edit_candidate_profile_action' ) );

		add_action( 'wp_ajax_nopriv_jlt_ajax_login', array( __CLASS__, 'ajax_login' ) );
		add_action( 'wp_ajax_jlt_ajax_login', array( __CLASS__, 'ajax_login_priv' ) );

		add_action( 'init', array( __CLASS__, 'register_action' ) );
		add_action( 'init', array( __CLASS__, 'login_action' ) );

		add_action( 'wp_ajax_jlt_update_password', array( __CLASS__, 'ajax_update_password' ) );
		add_action( 'wp_ajax_jlt_update_email', array( __CLASS__, 'ajax_update_email' ) );
	}

	public static function ajax_login() {
		check_ajax_referer( 'jlt-ajax-login', 'security' );
		$info                    = array();
		$info[ 'user_login' ]    = sanitize_text_field($_POST[ 'log' ]);
		$info[ 'user_password' ] = sanitize_text_field($_POST[ 'pwd' ]);

		$info[ 'remember' ] = ( isset( $_POST[ 'remember' ] ) && $_POST[ 'remember' ] === true ) ? true : false;
		$info               = apply_filters( 'jlt_ajax_login_info', $info );

		$secure_cookie = is_ssl() ? true : false;
		$user_signon   = wp_signon( $info, $secure_cookie );

		// it's possible that an old user used email instead of username
		if ( is_wp_error( $user_signon ) && JLT_Member::get_setting( 'register_using_email' ) && is_email( $info[ 'user_login' ] ) ) {
			$user = get_user_by( 'email', $info[ 'user_login' ] );
			if ( $user != false ) {
				$info[ 'user_login' ] = $user->user_login;
			}

			$user_signon = wp_signon( $info, $secure_cookie );
		}

		if ( is_wp_error( $user_signon ) ) {
			$error_msg = $user_signon->get_error_message();
			wp_send_json( array(
				'loggedin' => false,
				'message'  => '<span class="error-response">' . $error_msg . '</span>',
			) );
		} else {
			$redirecturl = isset( $_POST[ 'redirect_to' ] ) ? esc_url_raw($_POST[ 'redirect_to' ]) : '';
			$redirecturl = apply_filters( 'jlt_login_redirect', $redirecturl, $user_signon );
			$redirecturl = apply_filters( 'login_redirect', $redirecturl, $redirecturl, $user_signon ); // Enable redirect from some plugin
			wp_send_json( array(
				'loggedin'    => true,
				'redirecturl' => $redirecturl,
				'message'     => '<span class="success-response">' . __( 'Login successful, redirecting...', 'job-listings' ) . '</span>',
			) );
		}
		die;
	}

	public static function ajax_login_priv() {
		$link = "javascript:window.location.reload();return false;";
		wp_send_json( array(
			'loggedin' => false,
			'message'  => sprintf( __( 'You have already logged in. Please <a href="#" onclick="%s">refresh</a> page', 'job-listings' ), $link ),
		) );
		die();
	}

	public static function register_action() {

		if ( 'POST' !== strtoupper( $_SERVER[ 'REQUEST_METHOD' ] ) ) {
			return;
		}
		if ( empty( $_POST[ 'action' ] ) || 'jlt-register' !== $_POST[ 'action' ] || empty( $_POST[ '_wpnonce' ] ) || ! wp_verify_nonce( $_POST[ '_wpnonce' ], 'jlt-register' ) ) {
			return;
		}

		$user_login = sanitize_text_field($_POST[ 'user_login' ]);
		$user_email = sanitize_text_field($_POST[ 'user_email' ]);

		$user_password  = sanitize_text_field($_POST[ 'user_password' ]);
		$cuser_password = sanitize_text_field($_POST[ 'cuser_password' ]);

		$errors = new WP_Error();

		do_action( 'jlt_before_user_register', $_POST );

		if ( ! get_option( 'users_can_register' ) ) {

			$errors->add( 'users_can_register', __( 'This site does not allow registration.', 'job-listings' ) );
		}

		// Check user name
		if ( $user_login == '' ) {
			$errors->add( 'empty_user_login', __( 'Please type your user name.', 'job-listings' ) );
		} elseif ( username_exists( $user_login ) ) {
			$errors->add( 'username_exists', __( 'This user login was already registered, please choose another one.', 'job-listings' ) );
		}

		// Check the email address
		if ( $user_email == '' ) {
			$errors->add( 'empty_email', __( 'Please type your email address.', 'job-listings' ) );
		} elseif ( ! is_email( $user_email ) ) {
			$errors->add( 'invalid_email', __( 'The email address isn\'t correct.', 'job-listings' ) );
		} elseif ( email_exists( $user_email ) ) {
			$errors->add( 'email_exists', __( 'This email was already registered, please choose another one.', 'job-listings' ) );
		}

		// Check the password
		if ( strlen( $user_password ) < 6 ) {
			$errors->add( 'minlength_password', __( 'Password must be at least six characters long.', 'job-listings' ) );
		} elseif ( empty( $cuser_password ) ) {
			$errors->add( 'not_cpassword', __( 'Please enter the password confirmation.', 'job-listings' ) );
		} elseif ( $user_password != $cuser_password ) {
			$errors->add( 'unequal_password', __( 'Passwords do not match.', 'job-listings' ) );
		}

		// check role
		if ( isset( $_POST[ 'user_role' ] ) && empty( sanitize_text_field($_POST[ 'user_role' ]) ) ) {
			$errors->add( 'empty_role', __( 'Please select your role.', 'job-listings' ) );
		}

		if ( is_wp_error( $errors ) && $errors->get_error_code() ) {
			foreach ( $errors->errors as $k => $v ) {

				jlt_message_add( $v[ 0 ], 'error' );
			}
		} else {
			$user_data                   = array();
			$user_data[ 'user_email' ]   = $user_email;
			$user_data[ 'user_login' ]   = $user_login;
			$user_data[ 'user_pass' ]    = $user_password;
			$user_data[ 'display_name' ] = $user_login;

			$allow_register = JLT_Member::get_setting( 'allow_register', 'both' );
			switch ( $allow_register ) {
				case 'candidate':
					$user_data[ 'role' ] = JLT_Member::CANDIDATE_ROLE;
					break;
				case 'employer':
					$user_data[ 'role' ] = JLT_Member::EMPLOYER_ROLE;
					break;
				default:
					$user_data[ 'role' ] = isset( $_POST[ 'user_role' ] ) ? stripslashes( esc_html( $_POST[ 'user_role' ] ) ) : '';
					break;
			}

			$user_id = wp_insert_user( $user_data );

			if ( is_wp_error( $user_id ) ) {

				jlt_message_add( __( 'Error on user creation.', 'job-listings' ), 'error' );
			} else {

				if ( $user_data[ 'role' ] == JLT_Member::CANDIDATE_ROLE ) {

					jlt_candidate_save_custom_fields( $user_id, $_POST );
				} elseif ( $user_data[ 'role' ] == JLT_Member::EMPLOYER_ROLE ) {

					// Create new company
					$company_data = array(
						'post_title'     => $user_data[ 'display_name' ],
						'post_type'      => 'company',
						'comment_status' => 'closed',
						'post_status'    => 'publish',
						'post_author'    => $user_id,
					);

					do_action( 'jlt_user_register_before_auto_created_company', $company_data, $user_id );

					$company_id = wp_insert_post( $company_data );

					if ( ! is_wp_error( $company_id ) ) {

						update_user_meta( $user_id, 'employer_company', $company_id );

						do_action( 'jlt_user_register_after_auto_created_company', $company_id, $user_id );

						// Save custom fields
						jlt_company_save_custom_fields( $company_id, $_POST );
					}
				}

				do_action( 'jlt_user_register', $user_id );

				wp_new_user_notification( $user_id );

				self::new_user_send_email( $user_id, $user_data[ 'role' ] );

				$user                          = get_userdata( $user_id );
				$data_login[ 'user_login' ]    = $user->user_login;
				$data_login[ 'user_password' ] = $user_password;
				$secure_cookie                 = is_ssl() ? true : false;
				$user_login                    = wp_signon( $data_login, $secure_cookie );

				jlt_message_add( __( 'You\'re successfully register, you auto login to system.', 'job-listings' ) );

				$location = ! empty( $_POST[ 'redirect_to' ] ) ? esc_url_raw($_POST[ 'redirect_to' ]) : JLT_Member::get_member_page_url();

				wp_safe_redirect( $location );
				exit;
			}
		}
	}

	public static function login_action() {
		if ( 'POST' !== strtoupper( $_SERVER[ 'REQUEST_METHOD' ] ) ) {
			return;
		}

		if ( empty( $_POST[ 'action' ] ) || 'jlt_login' !== $_POST[ 'action' ] || empty( $_POST[ '_wpnonce' ] ) || ! wp_verify_nonce( $_POST[ '_wpnonce' ], 'jlt-login' ) ) {
			return;
		}
		$info                    = array();
		$info[ 'user_login' ]    = sanitize_text_field($_POST[ 'log' ]);
		$info[ 'user_password' ] = sanitize_text_field($_POST[ 'pwd' ]);
		$info[ 'remember' ]      = ( isset( $_POST[ 'rememberme' ] ) && $_POST[ 'rememberme' ] === true ) ? true : false;

		$secure_cookie = is_ssl() ? true : false;
		$user_signon   = wp_signon( $info, $secure_cookie );

		// it's possible that an old user used email instead of username
		if ( is_wp_error( $user_signon ) && is_email( $info[ 'user_login' ] ) ) {
			$user = get_user_by( 'email', $info[ 'user_login' ] );
			if ( $user != false ) {
				$info[ 'user_login' ] = $user->user_login;
			}
			$user_signon = wp_signon( $info, $secure_cookie );
		}

		if ( ! is_wp_error( $user_signon ) ) {

			jlt_message_add( __( 'You\'re login to system.', 'job-listings' ) );

			$redirect_url = sanitize_text_field($_POST[ 'redirect_to' ]);
			jlt_force_redirect( $redirect_url );
		} else {
			jlt_message_add( __( 'Looks like these are not your correct details. Please try again.', 'job-listings' ), 'error' );
		}
	}

	public static function new_user_send_email( $user_id, $role ) {

		$user = get_userdata( $user_id );

		if ( is_multisite() ) {
			$blogname = $GLOBALS[ 'current_site' ]->site_name;
		} else {
			$blogname = wp_specialchars_decode( get_option( 'blogname' ), ENT_QUOTES );
		}

		// user email
		$to = $user->user_email;

		if ( $role == JLT_Member::CANDIDATE_ROLE ) {

			if ( jlt_email_get_setting( 'candidate_registration', 'active', 1 ) ) {

				$array_replace = array(
					'[user_name]'         => $user->user_login,
					'[user_display_name]' => $user->display_name,
					'[user_email]'        => $user->user_email,
					'[user_registered]'   => $user->user_registered,
					'[site_name]'         => $blogname,
					'[site_url]'          => esc_url( home_url( '' ) ),
				);

				$subject = jlt_email_get_setting( 'candidate_registration', 'subject' );
				$subject = str_replace( array_keys( $array_replace ), $array_replace, $subject );

				$message = jlt_email_get_setting( 'candidate_registration', 'content' );
				$message = str_replace( array_keys( $array_replace ), $array_replace, $message );

				jlt_mail( $to, $subject, $message, 'jlt_notify_register_candidate' );
			}
		} else if ( $role == JLT_Member::EMPLOYER_ROLE ) {

			if ( jlt_email_get_setting( 'employer_registration', 'active', 1 ) ) {

				$array_replace = array(
					'[user_name]'         => $user->user_login,
					'[user_display_name]' => $user->display_name,
					'[user_email]'        => $user->user_email,
					'[user_registered]'   => $user->user_registered,
					'[site_name]'         => $blogname,
					'[site_url]'          => esc_url( home_url( '' ) ),
				);

				$subject = jlt_email_get_setting( 'employer_registration', 'subject' );
				$subject = str_replace( array_keys( $array_replace ), $array_replace, $subject );

				$message = jlt_email_get_setting( 'employer_registration', 'content' );
				$message = str_replace( array_keys( $array_replace ), $array_replace, $message );

				jlt_mail( $to, $subject, $message, array(), 'jlt_notify_register_employer' );
			}
		}
	}

	public static function ajax_update_email() {
		if ( ! is_user_logged_in() ) {
			$result = array(
				'success' => false,
				'message' => '<span class="error-response">' . __( 'You have not logged in yet', 'job-listings' ) . '</span>',
			);

			wp_send_json( $result );

			return;
		}

		if ( ! check_ajax_referer( 'update-email', 'security', false ) ) {
			$result = array(
				'success' => false,
				'message' => '<span class="error-response">' . __( 'Your session has expired or you have submitted an invalid form.', 'job-listings' ),
			);

			wp_send_json( $result );

			return;
		}

		$current_user = wp_get_current_user();

		$user_id        = $current_user->ID;
		$submit_user_id = intval( sanitize_text_field($_POST[ 'user_id' ]));
		if ( $user_id != $submit_user_id ) {
			$result = array(
				'success' => false,
				'message' => '<span class="error-response">' . __( 'There\'s an unknown error. Please retry or contact Administrator.', 'job-listings' ) . '</span>',
			);
		} else {
			$no_html           = array();
			$new_email         = wp_kses( $_POST[ 'new_email' ], $no_html );
			$new_email_confirm = wp_kses( $_POST[ 'new_email_confirm' ], $no_html );

			if ( empty( $new_email ) || empty( $new_email_confirm ) ) {
				$result = array(
					'success' => false,
					'message' => '<span class="error-response">' . __( 'The new email is blank.', 'job-listings' ) . '</span>',
				);
			} elseif ( $new_email != $new_email_confirm ) {
				$result = array(
					'success' => false,
					'message' => '<span class="error-response">' . __( 'Emails do not match.', 'job-listings' ) . '</span>',
				);
			} else {
				$user = array(
					'ID'         => $user_id,
					'user_email' => $new_email,
				);

				$update_result = wp_update_user( $user );

				if ( is_wp_error( $update_result ) && $update_result->get_error_code() ) {
					$result = array(
						'success' => false,
						'message' => '<span class="error-response">' . $update_result->get_error_message() . '</span>',
					);
				} else {
					$result = array(
						'success'     => true,
						'message'     => '<span class="success-response">' . __( 'Email updated successfully.', 'job-listings' ) . '</span>',
						'redirecturl' => apply_filters( 'jlt_update_password_redirect', '' ),
					);
				}
			}
		}

		wp_send_json( $result );
	}

	public static function ajax_update_password() {
		if ( ! is_user_logged_in() ) {
			$result = array(
				'success' => false,
				'message' => '<span class="error-response">' . __( 'You have not logged in yet', 'job-listings' ) . '</span>',
			);

			wp_send_json( $result );

			return;
		}

		if ( ! check_ajax_referer( 'update-password', 'security', false ) ) {
			$result = array(
				'success' => false,
				'message' => '<span class="error-response">' . __( 'Your session has expired or you have submitted an invalid form.', 'job-listings' ),
			);

			wp_send_json( $result );

			return;
		}

		$current_user = wp_get_current_user();

		$user_id        = $current_user->ID;
		$submit_user_id = intval( sanitize_text_field($_POST[ 'user_id' ]) );
		if ( $user_id != $submit_user_id ) {
			$result = array(
				'success' => false,
				'message' => '<span class="error-response">' . __( 'There\'s an unknown error. Please retry or contact Administrator.', 'job-listings' ) . '</span>',
			);
		} else {
			$no_html          = array();
			$old_pass         = wp_kses( $_POST[ 'old_pass' ], $no_html );
			$new_pass         = wp_kses( $_POST[ 'new_pass' ], $no_html );
			$new_pass_confirm = wp_kses( $_POST[ 'new_pass_confirm' ], $no_html );

			if ( empty( $new_pass ) || empty( $new_pass_confirm ) ) {
				$result = array(
					'success' => false,
					'message' => '<span class="error-response">' . __( 'The new password is blank.', 'job-listings' ) . '</span>',
				);
			} elseif ( $new_pass != $new_pass_confirm ) {
				$result = array(
					'success' => false,
					'message' => '<span class="error-response">' . __( 'Passwords do not match.', 'job-listings' ) . '</span>',
				);
			} else {
				$user = get_user_by( 'id', $user_id );
				if ( $user && wp_check_password( $old_pass, $user->data->user_pass, $user->ID ) ) {
					wp_set_password( $new_pass, $user->ID );

					$result = array(
						'success'     => true,
						'message'     => '<span class="success-response">' . __( 'Password updated successfully.', 'job-listings' ) . '</span>',
						'redirecturl' => apply_filters( 'jlt_update_password_redirect', '' ),
					);
				} else {
					$result = array(
						'success' => false,
						'message' => '<span class="error-response">' . __( 'Old password is not correct.', 'job-listings' ) . '</span>',
					);
				}
			}
		}

		wp_send_json( $result );
	}

	public static function edit_candidate_profile_action() {
		if ( 'POST' !== strtoupper( $_SERVER[ 'REQUEST_METHOD' ] ) ) {
			return;
		}

		if ( empty( $_POST[ 'action' ] ) || 'edit_candidate' !== $_POST[ 'action' ] || empty( $_POST[ '_wpnonce' ] ) || ! wp_verify_nonce( $_POST[ '_wpnonce' ], 'edit-candidate' ) ) {
			return;
		}

		if ( ! is_user_logged_in() ) {
			return;
		}

		$candidate_id = isset( $_POST[ 'candidate_id' ] ) ? absint( sanitize_text_field($_POST[ 'candidate_id' ]) ) : '';
		if ( empty( $candidate_id ) ) {
			jlt_message_add( __( 'Missing Candidate ID.', 'job-listings' ) );
		} elseif ( $candidate_id != get_current_user_id() ) {
			jlt_message_add( __( 'You can not edit someone else\'s profile.', 'job-listings' ) );
		} else {
			$no_html = jlt_html_allowed();

			$name  = isset( $_POST[ 'full_name' ] ) ? wp_kses( $_POST[ 'full_name' ], $no_html ) : '';
			$email = isset( $_POST[ 'email' ] ) ? wp_kses( $_POST[ 'email' ], $no_html ) : '';
			$desc  = isset( $_POST[ 'description' ] ) ? wp_kses_post( $_POST[ 'description' ] ) : '';

			$candidate = array(
				'ID'          => $candidate_id,
				'description' => $desc,
			);

			if ( ! empty( $name ) ) {
				$splitted_name = explode( ' ', $name, 2 );
				$first_name    = $splitted_name[ 0 ];
				$last_name     = ! empty( $splitted_name[ 1 ] ) ? $splitted_name[ 1 ] : '';
			} else {
				$first_name = isset( $_POST[ 'first_name' ] ) && ! empty( $_POST[ 'first_name' ] ) ? wp_kses( $_POST[ 'first_name' ], $no_html ) : '';
				$last_name  = isset( $_POST[ 'last_name' ] ) && ! empty( $_POST[ 'last_name' ] ) ? wp_kses( $_POST[ 'last_name' ], $no_html ) : '';
			}

			if ( ! empty( $first_name ) or ! empty( $last_name ) ) {
				$candidate[ 'first_name' ]   = $first_name;
				$candidate[ 'last_name' ]    = $last_name;
				$candidate[ 'display_name' ] = $first_name . ' ' . $last_name;
			}

			if ( ! empty( $email ) && is_email( $email ) ) {
				$candidate[ 'user_email' ] = $email;
			}

			$user_id = wp_update_user( $candidate );

			if ( is_wp_error( $user_id ) && $user_id->get_error_code() ) {
				jlt_message_add( $user_id->get_error_message(), 'error' );
			} elseif ( $user_id != $candidate_id ) {
				jlt_message_add( __( 'There\'s an unknown error. Please retry or contact Administrator.', 'job-listings' ) );
			} else {

				if ( isset( $_POST[ 'profile_image' ] ) ) {
					$old_profile_image = get_user_meta( $user_id, 'profile_image', true );
					if ( $old_profile_image != $_POST[ 'profile_image' ] ) {
						update_user_meta( $user_id, 'profile_image', sanitize_text_field( $_POST[ 'profile_image' ] ) );
						if ( is_numeric( $old_profile_image ) ) {
							wp_delete_attachment( $old_profile_image, true );
						}
					}
				}

				jlt_candidate_save_custom_fields( $candidate_id, $_POST );
				jlt_message_add( __( 'Your profile is updated successfully', 'job-listings' ) );

				do_action( 'jlt_save_candidate_profile', $candidate_id, $_POST );
			}
		}
		wp_safe_redirect( JLT_Member::get_candidate_profile_url() );
		die;
	}
}

new JLT_Form_Member();