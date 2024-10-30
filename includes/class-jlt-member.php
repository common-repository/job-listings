<?php
if ( ! class_exists( 'JLT_Member' ) ):
	class JLT_Member {

		const EMPLOYER_ROLE  = 'employer';
		const CANDIDATE_ROLE = 'candidate';

		protected $query_vars;

		protected static $_instance = null;

		protected function __construct() {
			$vars = array(
				'lost-password'  => 'lost-password',
				'reset-password' => 'reset-password',
				'edit-job'       => 'edit-job',

				'manage-application' => 'manage-application',
				'manage-plan'        => 'manage-plan',
				'manage-job'         => 'manage-job',

				'company-profile'    => 'company-profile',
				'package-checkout'   => 'package-checkout', // processing payment.
				// Candidate:
				'candidate-profile'  => 'candidate-profile',
				'manage-job-applied' => 'manage-job-applied',

			);

			$this->query_vars = apply_filters( 'jlt_member_list_query_vars', $vars );

			add_action( 'init', array( &$this, 'add_endpoints' ) );
			add_action( 'init', array( &$this, 'init' ) );

			add_filter( 'get_avatar', array( &$this, 'get_avatar' ), 100000, 6 );

			if( self::get_setting('use_custom_login', true) ) {
				add_filter('login_url', array(&$this,'login_url'),99999);
			}

			add_filter( 'logout_url', array( &$this, 'logout_url' ), 99999, 2 );
			add_filter( 'register_url', array( &$this, 'register_url' ), 99999 );
			add_filter( 'lostpassword_url', array( &$this, 'lostpassword_url' ), 99999 );

			if ( is_admin() ) {
				add_action( 'admin_init', array( &$this, 'admin_init' ) );

				add_action( 'user_new_form', array( &$this, 'user_profile' ) );
				add_action( 'show_user_profile', array( &$this, 'user_profile' ) );
				add_action( 'edit_user_profile', array( &$this, 'user_profile' ) );

				add_action( 'user_register', array( __CLASS__, 'save_user_profile' ) );
				add_action( 'personal_options_update', array( __CLASS__, 'save_user_profile' ) );
				add_action( 'edit_user_profile_update', array( __CLASS__, 'save_user_profile' ) );

				add_filter( 'jlt_admin_settings_tabs_array', array( &$this, 'add_seting_member_tab' ) );
				add_action( 'jlt_admin_setting_member', array( &$this, 'setting_page' ) );

				add_action( 'admin_enqueue_scripts', array( &$this, 'enqueue_style_script' ) );
			} else {
				add_filter( 'query_vars', array( &$this, 'add_query_vars' ), 0 );
				add_action( 'parse_request', array( $this, 'parse_request' ), 0 );
			}

			// Remove admin bar and redirect profile page to site interface

			if ( self::get_setting( 'hide_admin_bar', true ) ) {
				add_action( 'admin_init', array( $this, 'prevent_admin_access' ) );
				add_action( 'user_register', array( $this, 'hide_admin_bar_front' ) );
				add_action( 'wp_before_admin_bar_render', array( $this, 'stop_admin_bar_render' ) );

				// Stop WooCommerce redirect to My Account page.
				add_filter( 'woocommerce_prevent_admin_access', '__return_false' );
			}
		}

		public static function instance() {
			if ( is_null( self::$_instance ) ) {
				self::$_instance = new self();
			}

			return self::$_instance;
		}

		public function init() {
			$employer_role  = get_role( self::EMPLOYER_ROLE );
			$candidate_role = get_role( self::CANDIDATE_ROLE );
			if ( empty( $employer_role ) || empty( $candidate_role ) ) {
				$this->create_roles();
			}
			if ( jlt_check_woocommerce_active() ) {
				add_filter( 'woocommerce_disable_admin_bar', '__return_false' );
			}
		}

		public function add_endpoints() {

			foreach ( $this->get_query_vars() as $key => $var ) {
				add_rewrite_endpoint( $var, EP_ROOT | EP_PAGES );
			}
			flush_rewrite_rules();
		}

		public function add_query_vars( $vars ) {

			foreach ( $this->get_query_vars() as $key => $var ) {
				$vars[] = $var;
			}

			return $vars;
		}

		public function get_query_vars() {
			return apply_filters( 'jlt_member_page_endpoint', $this->query_vars );
		}

		public function parse_request() {
			global $wp;

			$list_endpoint = jlt_all_endpoints();

			foreach ( $list_endpoint as $var ) {

				if ( isset( $_GET[ $var ] ) ) {
					$wp->query_vars[ $var ] = $_GET[ $var ];
				} elseif ( isset( $wp->query_vars[ $var ] ) ) {
					$q = $wp->query_vars[ $var ];
					if ( strstr( $q, 'page' ) ) {
						$wp->query_vars[ $var ]    = '';
						$p                         = explode( '/', $q );
						$wp->query_vars[ 'paged' ] = absint( $p[ 1 ] );
					} else {
						$wp->query_vars[ $var ] = $wp->query_vars[ $var ];
					}
				}
			}
		}

		public function admin_init() {
			register_setting( 'jlt_member', 'jlt_member' );
		}

		public static function get_setting( $id = null, $default = null ) {
			global $jlt_member_setting;
			if ( ! isset( $jlt_member_setting ) || empty( $jlt_member_setting ) ) {
				$jlt_member_setting = get_option( 'jlt_member' );
			}
			if ( isset( $jlt_member_setting[ $id ] ) ) {
				return $jlt_member_setting[ $id ];
			}

			return $default;
		}

		public static function can_register() {
			$can_register   = get_option( 'users_can_register' );
			$allow_register = JLT_Member::get_setting( 'allow_register', 'both' );
			if ( $allow_register == 'none' ) {
				$can_register = false;
			} elseif ( $allow_register == 'employer' ) {
				$can_register = $can_register && ! jlt_is_resume_posting_page();
			} elseif ( $allow_register == 'candidate' ) {
				$can_register = $can_register && ! jlt_is_job_posting_page();
			}

			return $can_register;
		}

		public static function get_member_page_id() {
			$page_id = self::get_setting( 'manage_page_id' );
			$page_id = apply_filters( 'wpml_object_id', $page_id, 'page', true );

			return $page_id;
		}

		public static function get_member_page_url() {
			global $member_page_url;
			if ( empty( $member_page_url ) ) {
				$member_page_url = get_permalink( self::get_member_page_id() );
			}

			return $member_page_url;
		}

		public static function get_checkout_url( $product_id ) {
			if ( empty( $product_id ) ) {
				return self::get_member_page_url();
			}

			$checkout_url = self::get_endpoint_url( 'package-checkout' );

			return esc_url_raw( add_query_arg( 'product_id', $product_id, $checkout_url ) );
		}

		public static function get_login_url() {
			if ( $manage_page_url = self::get_member_page_url() ) {
				$login_url = esc_url_raw( add_query_arg( 'action', 'login', $manage_page_url ) );
			} else {
				$login_url = wp_login_url();
			}

			return $login_url;
		}

		public static function get_register_url() {
			if ( $manage_page_url = self::get_member_page_url() ) {
				$register_url = esc_url_raw( add_query_arg( array(
					'action' => 'login',
					'mode'   => 'register',
				), $manage_page_url ) );
			} else {
				$register_url = wp_registration_url();
			}

			return $register_url;
		}

		public static function get_logout_url() {
			$logout_url = wp_logout_url();

			return $logout_url;
		}

		public function login_url( $login_url ) {
			$basename   = basename( $_SERVER[ 'REQUEST_URI' ] );
			$user_login = self::get_member_page_id();
			if ( $user_login && strpos( $basename, 'wp-login.php' ) === false ) {
				$new_login_url = get_permalink( $user_login );
				if ( $new_login_url ) {
					// retain the redirect url
					if ( $var_pos = strpos( $login_url, '?' ) ) {
						$login_args = wp_parse_args( substr( $login_url, $var_pos + 1 ), array() );
						if ( isset( $login_args[ 'redirect_to' ] ) ) {
							$new_login_url = esc_url_raw( add_query_arg( array(
								'action'      => 'login',
								'redirect_to' => urlencode( $login_args[ 'redirect_to' ] ),
							), $new_login_url ) );
						}
					}

					return $new_login_url;
				}
			}

			return $login_url;
		}

		public function register_url( $register_url ) {
			$basename     = basename( $_SERVER[ 'REQUEST_URI' ] );
			$user_regiter = self::get_member_page_id();
			if ( $user_regiter && $basename != 'wp-login.php' ) {
				$register_url = get_permalink( $user_regiter );
			}

			return $register_url;
		}

		public function logout_url( $logout_url, $redirect = '' ) {
			$basename = basename( $_SERVER[ 'REQUEST_URI' ] );
			if ( strpos( $basename, 'wp-login.php' ) === false ) {
				$args                  = array();
				$redirect_to           = ! empty( $redirect ) ? $redirect : home_url( '/' );
				$args[ 'redirect_to' ] = urlencode( $redirect_to );

				return esc_url_raw( add_query_arg( $args, $logout_url ) );
			}

			return $logout_url;
		}

		public function lostpassword_url( $lostpassword_url ) {
			$user_forgotten = self::get_member_page_id();
			if ( $user_forgotten ) {
				$lostpassword_url = self::get_endpoint_url( 'lost-password' );
			}

			return $lostpassword_url;
		}

		public static function resetpassword_url() {
			$user_forgotten = self::get_member_page_id();
			if ( $user_forgotten ) {
				$resetpassword_url = self::get_endpoint_url( 'reset-password' );
			} else {
				$resetpassword_url = network_site_url( "wp-login.php?action=rp", 'login' );
			}

			return $resetpassword_url;
		}

		public function get_avatar( $avatar = '', $user_id = null, $size = 40, $default = '', $alt = '', $args = array() ) {
			$user_id = empty( $user_id ) ? get_current_user_id() : ( is_object( $user_id ) ? $user_id->ID : $user_id );

			if ( ! is_numeric( $user_id ) ) {
				$maybe_user = get_user_by( 'email', $user_id );
				if ( ! empty( $maybe_user ) ) {
					$user_id = $maybe_user->ID;
				}
			}

			if ( empty( $user_id ) || ! is_numeric( $user_id ) ) {
				return '';
			}

			if ( isset( $args[ 'force_default' ] ) && $args[ 'force_default' ] ) {
				return $avatar;
			}

			$new_avatar = '';
			if ( self::is_candidate( $user_id ) ) {
				$profile_image = get_user_meta( $user_id, 'profile_image', true );
				if ( ! empty( $profile_image ) ) {
					if ( is_numeric( $profile_image ) ) {
						$new_avatar = wp_get_attachment_image( $profile_image, array(
							$size,
							$size,
						), false, array( 'alt' => $alt ) );
					} else {
						$new_avatar = '<img alt="' . $alt . '" src="' . esc_url( $profile_image ) . '" class="avatar avatar-' . $size . '" height="' . $size . '" width="' . $size . '">';
					}
				}
			} elseif ( self::is_employer( $user_id ) ) {
				$company_id = jlt_get_employer_company( $user_id );
				$new_avatar = ! empty( $company_id ) ? JLT_Company::get_company_logo( $company_id, $size, $alt ) : $new_avatar;
			}
			if ( empty( $new_avatar ) ) {
				$id_facebook = get_user_meta( $user_id, 'id_facebook', true );
				$id_google   = get_user_meta( $user_id, 'id_google', true );
				if ( ! empty( $id_facebook ) ) {
					$new_avatar = '<img src="//graph.facebook.com/' . $id_facebook . '/picture?type=large" alt="' . $alt . '" />';
				} elseif ( ! empty( $id_google ) ) {
					$json_google = 'http://picasaweb.google.com/data/entry/api/user/' . $id_google . '?alt=json';
					$get_info    = file_get_contents( $json_google );
					preg_match( '/\"gphoto\$thumbnail\"\:\{\"\$t\"\:\"(.*?)\"\}/is', $get_info, $thumbnail );
					$new_avatar = '<img src="' . $thumbnail[ 1 ] . '" alt="' . $alt . '" />';
				}
			}

			return ( empty( $new_avatar ) ? $avatar : $new_avatar );
		}

		public static function get_display_name( $user_id = '' ) {
			$user_id = empty( $user_id ) ? get_current_user_id() : $user_id;

			if ( empty( $user_id ) ) {
				return '';
			}
			$user         = get_userdata( $user_id );
			$display_name = $user->display_name;
			if ( JLT_Member::is_employer( $user->ID ) ) {
				$company_id = get_user_meta( $user->ID, 'employer_company', true );
				if ( $company_id ) {
					$display_name = get_the_title( $company_id );

					// @TODO: Update the employer's display name. Should be removed after some version
					if ( $user->display_name != $display_name ) {
						wp_update_user( array( 'ID' => $user->ID, 'display_name' => $display_name ) );
					}
				}
			}

			return apply_filters( 'jlt_member_display_name', $display_name, $user_id );
		}

		public function create_roles() {
			global $wp_roles;

			if ( class_exists( 'WP_Roles' ) ) {
				if ( ! isset( $wp_roles ) ) {
					$wp_roles = new WP_Roles();
				}
			}

			if ( is_object( $wp_roles ) ) {

				add_role( self::EMPLOYER_ROLE, __( 'Employer', 'job-listings' ), array(
					'read'         => true,
					'edit_posts'   => false,
					'delete_posts' => false,
				) );

				add_role( self::CANDIDATE_ROLE, __( 'Candidate', 'job-listings' ), array(
					'read'         => true,
					'edit_posts'   => false,
					'delete_posts' => false,
				) );
			}
		}

		public function enqueue_style_script( $hook ) {
			global $post;

			if ( $hook == 'user-new.php' || $hook == 'user-edit.php' || $hook == 'profile.php' ) {
				wp_enqueue_media();
			}
		}

		public function user_profile( $user ) {
			$user_id       = is_object( $user ) && isset( $user->ID ) ? $user->ID : 0;

			?>
			<?php if ( ( defined( 'IS_PROFILE_PAGE' ) && IS_PROFILE_PAGE ) || is_network_admin() ) :
				$user_roles = array_intersect( array_values( $user->roles ), array_keys( get_editable_roles() ) );
				$user_role = reset( $user_roles );
				?>
				<input id="role" type="hidden" name="role" value="<?php echo $user_role; ?>"/>
			<?php endif; ?>
			<div id="candidate_profile" class="candidate_profile">
				<h3 class=""><?php _e( 'Candidate Basic Info', 'job-listings' ); ?></h3>

				<table class="form-table">
					<tr>
						<th><label for="profile_image"><?php _e( 'Profile Image', 'job-listings' ); ?></label></th>
						<td>
							<!-- Outputs the image after save -->
							<?php
							$profile_image = empty( $user_id ) ? '' : get_the_author_meta( 'profile_image', $user_id );
							if ( is_numeric( $profile_image ) ) {
								$profile_image = wp_get_attachment_url( $profile_image );
							}
							?>
							<img src="<?php echo esc_url( $profile_image ); ?>" style="width:150px;"><br/>
							<!-- Outputs the text field and displays the URL of the image retrieved by the media uploader -->
							<input type="hidden" name="profile_image" id="profile_image"
							       value="<?php echo esc_url( $profile_image ); ?>" class="regular-text"/>
							<!-- Outputs the save button -->
							<input type='button' class="additional-user-image button-primary"
							       value="<?php _e( 'Upload Image', 'job-listings' ); ?>" id="uploadimage"/><br/>
                            <span
	                            class="description"><?php _e( 'Upload an additional image for your user profile.', 'job-listings' ); ?></span>
						</td>
					</tr>
					<?php
					$fields = jlt_get_candidate_custom_fields();
					if ( ! empty( $fields ) ) :
						foreach ( $fields as $field ) :
							if ( isset( $field[ 'is_default' ] ) ) {
								if ( in_array( $field[ 'name' ], array(
									'first_name',
									'last_name',
									'full_name',
									'email',
								) ) ) {
									continue;
								} // don't display WordPress default user fields
							}

							$label    = isset( $field[ 'label_translated' ] ) ? $field[ 'label_translated' ] : $field[ 'label' ];
							$field_id = jlt_candidate_custom_fields_name( $field[ 'name' ], $field );
							?>
							<tr>
								<th><label for="<?php echo $field_id; ?>"><?php echo $label; ?></label></th>
								<td>
									<?php
									jlt_render_setting_field( array(
										'id'      => $field_id,
										'type'    => $field[ 'type' ],
										'options' => jlt_convert_custom_field_setting_value( $field ),
										'value'   => get_user_meta( $user_id, $field_id, true ),
										'class'   => 'regular-text',
										'echo'    => true,
									) );
									?>
								</td>
							</tr>
						<?php endforeach;
					endif;
					?>
				</table>
				<h3 id="candidate_social"
				    class="candidate_social"><?php _e( 'Candidate Social Profile', 'job-listings' ); ?></h3>

				<table class="form-table">
					<?php
					$socials     = jlt_get_candidate_socials();
					$all_socials = jlt_get_social_fields();
					if ( ! empty( $socials ) ) :
						foreach ( $socials as $social ) :
							if ( empty( $social ) || ! isset( $all_socials[ $social ] ) ) {
								continue;
							}
							?>
							<tr>
								<th><label
										for="<?php echo $social; ?>"><?php echo $all_socials[ $social ][ 'label' ]; ?></label>
								</th>
								<td>
									<?php
									jlt_render_setting_field( array(
										'id'    => $social,
										'type'  => 'text',
										'value' => get_user_meta( $user_id, $social, true ),
										'class' => 'regular-text',
										'echo'  => true,
									) );
									?>
								</td>
							</tr>
						<?php endforeach;
					endif;
					?>
				</table>
			</div>
			<div id="employer_profile" class="employer_profile">
				<h3 class=""><?php _e( 'Employer Information', 'job-listings' ); ?></h3>
				<table class="form-table">
					<tr>
						<th><label for="employer_company"><?php _e( 'Representative for Company', 'job-listings' ); ?></label>
						</th>
						<td>
							<?php
							$companies       = get_posts( array(
								'post_type'        => 'company',
								'posts_per_page'   => '-1',
								'suppress_filters' => false,
							) );
							$current_company = ( ! empty( $user ) && is_object( $user ) ) ? get_user_meta( $user_id, 'employer_company', true ) : '';
							?>
							<select id="employer_company" name="employer_company">
								<option value=""><?php _e( '- Select -', 'job-listings' ) ?></option>
								<?php if ( $companies ): ?>
									<?php foreach ( $companies as $company ): ?>
										<option <?php selected( $current_company, $company->ID ) ?>
											value="<?php echo esc_attr( $company->ID ) ?>"><?php echo esc_html( $company->post_title ); ?></option>
									<?php endforeach; ?>
								<?php endif; ?>
							</select>
						</td>
					</tr>
				</table>
			</div>
			<script>
				jQuery(document).ready(function ($) {
					var setOption = function () {
						var $roleEl = $("#role");
						var $selectedRole = $roleEl.prop('nodeName') === 'select' ? $("#role option:selected").val() : $("#role").val();

						if ($selectedRole !== '<?php echo self::CANDIDATE_ROLE; ?>') {
							$("#candidate_profile").hide();
						} else {
							$("#candidate_profile").show();
						}
						if (( $selectedRole !== '<?php echo self::EMPLOYER_ROLE; ?>' ) && ( $selectedRole !== 'administrator' )) {
							$("#employer_profile").hide();
						} else {
							$("#employer_profile").show();
						}
					};
					setOption();

					$("#role").change(setOption);

					// Uploading files
					var file_frame;

					$('.additional-user-image').on('click', function (event) {

						event.preventDefault();

						$this = $(this);

						// If the media frame already exists, reopen it.
						if (file_frame) {
							file_frame.open();
							return;
						}

						// Create the media frame.
						file_frame = wp.media.frames.file_frame = wp.media({
							title: $this.data('uploader_title'),
							button: {
								text: $this.data('uploader_button_text'),
							},
							multiple: false  // Set to true to allow multiple files to be selected
						});

						// When an image is selected, run a callback.
						file_frame.on('select', function () {
							// We set multiple to false so only get one image from the uploader
							attachment = file_frame.state().get('selection').first().toJSON();

							// Do something with attachment.id and/or attachment.url here
							$this.siblings('img').attr('src', attachment.url);
							$this.siblings('input#profile_image').val(attachment.id);
						});

						// Finally, open the modal
						file_frame.open();
					});

				});
			</script>
			<?php
		}

		public static function save_user_profile( $user_id ) {
			if ( empty( $user_id ) ) {
				return;
			}

			if ( isset( $_POST[ 'profile_image' ] ) ) {
				$old_profile_image = get_user_meta( $user_id, 'profile_image', true );
				if ( $old_profile_image != $_POST[ 'profile_image' ] ) {
					update_user_meta( $user_id, 'profile_image', sanitize_text_field( $_POST[ 'profile_image' ] ) );
					if ( is_numeric( $old_profile_image ) ) {
						wp_delete_attachment( $old_profile_image, true );
					}
				}
			}

			jlt_candidate_save_custom_fields( $user_id, $_POST );

			if ( isset( $_POST[ 'employer_company' ] ) ) {
				update_user_meta( $user_id, 'employer_company', sanitize_text_field( $_POST[ 'employer_company' ] ) );
			}

			do_action( 'jlt_saved_user_profile', $user_id );
		}

		public static function can_go_to_admin( $user_id = '' ) {
			$can_go_to_admin = false;

			if ( empty( $user_id ) ) {
				$can_go_to_admin = current_user_can( 'edit_posts' ) || current_user_can( 'activate_plugins' );
			} else {
				$can_go_to_admin = user_can( $user_id, 'edit_posts' ) || user_can( $user_id, 'activate_plugins' );
			}

			return apply_filters( 'jlt_can_go_to_admin', $can_go_to_admin );
		}

		public function hide_admin_bar_front( $user_ID ) {
			if ( ! self::can_go_to_admin( $user_ID ) ) {
				update_user_meta( $user_ID, 'show_admin_bar_front', 'false' );
			}
		}

		public function stop_admin_bar_render() {
			if ( ! self::can_go_to_admin() ) {
				global $wp_admin_bar;
				$wp_admin_bar->remove_menu( 'site-name' );
				$wp_admin_bar->remove_menu( 'dashboard' );
				$wp_admin_bar->remove_menu( 'edit-profile' );
				$wp_admin_bar->remove_menu( 'user-actions' );
			}
		}

		public function prevent_admin_access() {
			if ( ! ( defined( 'DOING_AJAX' ) && DOING_AJAX ) && basename( $_SERVER[ "SCRIPT_FILENAME" ] ) !== 'admin-post.php' && ! self::can_go_to_admin() ) {
				wp_safe_redirect( self::get_member_page_url() );
				exit;
			}
		}

		public static function get_endpoint_url( $endpoint, $value = '' ) {
			$slug = jlt_get_endpoints_setting( $endpoint, $endpoint );
			$url  = jlt_get_endpoint_url( $slug, $value, self::get_member_page_url() );

			return $url;
		}

		public static function get_company_profile_url() {

			$url = self::get_endpoint_url( 'company-profile' );

			return $url;
		}

		public static function get_candidate_profile_url() {
			$url = self::get_endpoint_url( 'candidate-profile' );

			return $url;
		}

		public function get_endpoint_key( $endpoint ) {
			$lists = $this->get_query_vars();

			return $lists[ $endpoint ];
		}

		private function _lost_password( $atts ) {
			ob_start();
			include( locate_template( "layouts/lost-password-form.php" ) );

			return ob_get_clean();
		}

		private function _reset_password( $atts ) {
			$rp_key   = isset( $_GET[ 'key' ] ) ? $_GET[ 'key' ] : '';
			$rp_login = isset( $_GET[ 'login' ] ) ? $_GET[ 'login' ] : '';

			if ( empty( $rp_key ) || empty( $rp_login ) ) {
				jlt_message_add( __( 'Reset link misses key or username.', 'job-listings' ), 'error' );
				wp_redirect( wp_lostpassword_url() );
				exit;
			}

			$user = check_password_reset_key( $rp_key, $rp_login );
			if ( ! $user || is_wp_error( $user ) ) {
				if ( $user && $user->get_error_code() === 'expired_key' ) {
					jlt_message_add( __( 'Your reset key is expired.', 'job-listings' ), 'error' );
					wp_redirect( wp_lostpassword_url() );
				} else {
					jlt_message_add( __( 'Invalid reset key.', 'job-listings' ), 'error' );
					wp_redirect( wp_lostpassword_url() );
				}
				exit;
			}

			ob_start();
			include( locate_template( "layouts/reset-password-form.php" ) );

			return ob_get_clean();
		}

		public static function modal_application( $application_id, $type = '' ) {

			$ids = array();
			if ( strpos( $application_id, ',' ) === false ) {
				$application = get_post( $application_id );
				if ( $application->post_type != 'application' || empty( $type ) ) {
					return '';
				}

				if ( $_candidate_user_id = jlt_get_post_meta( $application->ID, '_candidate_user_id' ) ) {
					$_candidate_userdata = get_userdata( $_candidate_user_id );
					$display             = ! empty( $_candidate_userdata ) ? $_candidate_userdata->display_name : '';
					$avatar              = jlt_get_avatar( $_candidate_user_id, 40 );
				} else {
					$display = $application->post_title;
				}
			} else {
				$display = __( 'your candidates', 'job-listings' );
			}

			$button = $type == 'approve' ? __( 'Approve', 'job-listings' ) : __( 'Reject', 'job-listings' );

			$approve_title   = jlt_get_application_setting( 'approve_title', '' );
			$approve_message = jlt_get_application_setting( 'approve_message', '' );

			$reject_title   = jlt_get_application_setting( 'reject_title', '' );
			$reject_message = jlt_get_application_setting( 'reject_message', '' );

			$title   = 'approve' == $type ? $approve_title : $reject_title;
			$message = 'approve' == $type ? $approve_message : $reject_message;

			$attrs = array(
				'type'           => $type,
				'name'           => $display,
				'application_id' => $application_id,
				'button'         => $button,
				'title'          => $title,
				'message'        => $message,
			);
			ob_start();
			jlt_get_template( 'member/manage-approve-reject.php', $attrs );
			echo ob_get_clean();
		}

		public static function get_update_email_form( $user_id = 0 ) {
			if ( empty( $user_id ) ) {
				$user_id = get_current_user_id();
			}
			ob_start();
			do_action( 'jlt_update_email_before' );
			jlt_get_template( 'member/update-email.php' );
			do_action( 'jlt_update_email_after' );

			return ob_get_clean();
		}

		public static function get_update_password_form( $user_id = 0 ) {
			if ( empty( $user_id ) ) {
				$user_id = get_current_user_id();
			}
			ob_start();
			jlt_get_template( 'member/update-password.php' );

			return ob_get_clean();
		}

		public static function is_logged_in() {
			require_once( ABSPATH . "wp-includes/pluggable.php" );

			return is_user_logged_in();
		}

		public static function is_candidate( $user_id = null ) {
			return self::CANDIDATE_ROLE == self::get_user_role( $user_id );
		}

		public static function is_employer( $user_id = null ) {
			$user_role = self::get_user_role( $user_id );

			return self::EMPLOYER_ROLE == $user_role || 'administrator' == $user_role;
		}

		public static function get_user_role( $user_id = null ) {
			if ( empty( $user_id ) ) {
				if ( ! self::is_logged_in() ) {
					return '';
				}
				$user = wp_get_current_user();
			} else {
				$user = get_userdata( $user_id );
			}

			if ( ! $user ) {
				return '';
			}

			if ( ! function_exists( 'get_editable_roles' ) ) {
				include_once( ABSPATH . 'wp-admin/includes/user.php' );
			}
			$editable_roles = array_keys( get_editable_roles() );
			if ( count( $user->roles ) <= 1 ) {
				$role = reset( $user->roles );
			} elseif ( $roles = array_intersect( array_values( $user->roles ), $editable_roles ) ) {
				$role = reset( $roles );
			} else {
				$role = reset( $user->roles );
			}

			return $role;
		}

		/*** Permission checking ***/
		/* ======================= */

		public static function can_edit_profile( $candidate_id = 0, $user_id = 0 ) {
			if ( ! self::is_logged_in() ) {
				return false;
			}
			if ( empty( $candidate_id ) ) {
				return true;
			}

			$user_id = empty( $user_id ) ? get_current_user_id() : 0;
			if ( empty( $user_id ) ) {
				return false;
			}

			return $candidate_id == $user_id;
		}

		public static function can_edit_job( $job_id = 0, $user_id = 0 ) {
			return jlt_can_edit_job( $job_id, $user_id );
		}

		public static function can_change_job_state( $job_id = 0, $user_id = 0 ) {
			return jlt_can_change_job_state( $job_id, $user_id );
		}

		public static function can_edit_company( $company_id = 0, $user_id = 0 ) {
			if ( ! self::is_employer() ) {
				return false;
			}
			if ( empty( $company_id ) ) {
				return true;
			}

			$user_id = empty( $user_id ) ? get_current_user_id() : 0;
			if ( empty( $user_id ) ) {
				return false;
			}

			return $company_id == jlt_get_employer_company( $user_id );
		}

		private function _create_cron_jobs() {
			if ( get_option( 'jlt_member_cron_job' ) == '1' ) {
				return;
			}

			$this->add_endpoints();
			flush_rewrite_rules();

			delete_option( 'jlt_member_cron_job' );
			update_option( 'jlt_member_cron_job', '1' );
		}

		public function add_seting_member_tab( $tabs ) {
			$temp1 = array_slice( $tabs, 0, 4 );
			$temp2 = array_slice( $tabs, 4 );

			$member_tab = array( 'member' => __( 'Member', 'job-listings' ) );

			return array_merge( $temp1, $member_tab, $temp2 );
		}

		public function setting_page() {
			if ( isset( $_GET[ 'settings-updated' ] ) && $_GET[ 'settings-updated' ] ) {
				flush_rewrite_rules();
			}
			?>
			<?php settings_fields( 'jlt_member' ); ?>
			<h3><?php echo __( 'Member Options', 'job-listings' ) ?></h3>
			<table class="form-table" cellspacing="0">
				<tbody>
				<tr>
					<th>
						<?php _e( 'Member Manage Page', 'job-listings' ) ?>
					</th>
					<td>
						<?php
						$args = array(
							'name'             => 'jlt_member[manage_page_id]',
							'id'               => 'manage_page_id',
							'sort_column'      => 'menu_order',
							'sort_order'       => 'ASC',
							'show_option_none' => ' ',
							'class'            => 'jlt-admin-chosen',
							'echo'             => false,
							'selected'         => self::get_member_page_id(),
						);
						?>
						<?php echo str_replace( ' id=', " data-placeholder='" . __( 'Select a page&hellip;', 'job-listings' ) . "' id=", wp_dropdown_pages( $args ) ); ?>
						<p>
							<small><?php _e( 'Select a page with shortcode [jlt_member]', 'job-listings' ); ?></small>
						</p>
					</td>
				</tr>
				<tr>
					<th>
						<?php _e( 'Use Member page as WordPress Login/Register', 'job-listings' ) ?>
					</th>
					<td>
						<input type="hidden" name="jlt_member[use_custom_login]" value="0"/>
						<input type="checkbox" name="jlt_member[use_custom_login]"
						       value="1" <?php checked( self::get_setting( 'use_custom_login', true ) ); ?> />
						<small><?php _e( 'If this option is enabled, all login links ( /wp-admin included ) will be redirect to the Member page', 'job-listings' ); ?>
					</td>
				</tr>
				<tr>
					<th>
						<?php _e( 'Who can Register?', 'job-listings' ) ?>
					</th>
					<td>
						<?php if ( get_option( 'users_can_register', true ) ) : ?>
							<?php $allow_register = self::get_setting( 'allow_register', 'both' );
							?>
							<select class="jlt-admin-chosen" name="jlt_member[allow_register]">
								<option
									value="both" <?php selected( $allow_register, 'both' ); ?> ><?php _e( 'Both Employer & Candidate', 'job-listings' ); ?></option>
								<option
									value="employer" <?php selected( $allow_register, 'employer' ); ?> ><?php _e( 'Only Employer', 'job-listings' ); ?></option>
								<option
									value="candidate" <?php selected( $allow_register, 'candidate' ); ?> ><?php _e( 'Only Candidate', 'job-listings' ); ?></option>
								<option
									value="none" <?php selected( $allow_register, 'none' ); ?> ><?php _e( 'Disable Register', 'job-listings' ); ?></option>
							</select>
						<?php else: ?>
							<h4><?php echo sprintf( __( 'Registration is not enabled on this site. To enable it please go to %s and allow Anyone can register.', 'job-listings' ), '<a href="' . admin_url( 'options-general.php' ) . '">' . __( 'General Setting', 'job-listings' ) . '</a>' ); ?></h4>
						<?php endif; ?>
					</td>
				</tr>
				<tr>
					<th>
						<?php _e( 'Show Member Page Menu', 'job-listings' ) ?>
					</th>
					<td>
						<input type="checkbox" name="jlt_member[show_member_menu]"
						       value="1" <?php checked( self::get_setting( 'show_member_menu', false ) ); ?> />
					</td>
				</tr>
				<tr>
					<th>
						<?php _e( 'Show Member Page on Menu', 'job-listings' ) ?>
					</th>
					<td>
						<?php $on_menu = self::get_setting( 'member_page_on_menu', '' ); ?>
						<select class="jlt-admin-chosen" name="jlt_member[member_page_on_menu]">
							<option value=""><?php _e( 'Select a menu' ); ?></option>
							<?php
							$nav_menus = wp_get_nav_menus();
							foreach ( $nav_menus as $menu ) {
								?>
								<option
									value="<?php echo $menu->term_id; ?>" <?php selected( $on_menu, $menu->term_id ); ?> ><?php echo $menu->name; ?></option>
								<?php
							}
							?>
						</select>
						<p><?php _e( 'Show member page on menu location.', 'job-listings' ); ?></p>
					</td>
				</tr>
				<tr>
					<th>
						<?php _e( 'Hide Admin Bar', 'job-listings' ) ?>
					</th>
					<td>
						<input type="hidden" name="jlt_member[hide_admin_bar]" value="0"/>
						<input type="checkbox" name="jlt_member[hide_admin_bar]"
						       value="1" <?php checked( self::get_setting( 'hide_admin_bar', true ) ); ?> />
						<small><?php _e( 'Hide Admin bar for users ( Candidates and Employeres ) registered from now.', 'job-listings' ); ?></small>
					</td>
				</tr>
				<tr>
					<th>
						<?php _e( 'Terms and Conditions Page', 'job-listings' ) ?>
					</th>
					<td>
						<?php
						$args = array(
							'name'             => 'jlt_member[term_page_id]',
							'id'               => 'term_page_id',
							'sort_column'      => 'menu_order',
							'sort_order'       => 'ASC',
							'show_option_none' => ' ',
							'class'            => 'jlt-admin-chosen',
							'echo'             => false,
							'selected'         => self::get_setting( 'term_page_id' ),
						);
						?>
						<?php echo str_replace( ' id=', " data-placeholder='" . __( 'Select a page&hellip;', 'job-listings' ) . "' id=", wp_dropdown_pages( $args ) ); ?>
						<small><?php _e( 'This page used for "I agree with the Terms of use" on Registration form', 'job-listings' ); ?></small>
					</td>
				</tr>

				<?php do_action( 'jlt_setting_member_fields' ); ?>

				</tbody>
			</table>
			<?php
		}
	}

	JLT_Member::instance();

endif;

if ( ! function_exists( 'jlt_get_avatar' ) ) :
	if ( get_option( 'show_avatars' ) && function_exists( 'get_avatar' ) ) :
		function jlt_get_avatar( $id_or_email = '', $size = '' ) {
			return get_avatar( $id_or_email, $size );
		}
	else :
		function jlt_get_avatar( $id_or_email = '', $size = '' ) {
			return JLT_Member::instance()->get_avatar( '', $id_or_email, $size );
		}
	endif;
endif;