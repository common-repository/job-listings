<?php
if ( ! class_exists( 'JLT_Application' ) ) :
	class JLT_Application {

		public function __construct() {
			add_action( 'init', array( $this, 'register_post_type' ), 0 );
			if ( is_admin() ) {
				add_action( 'admin_init', array( $this, 'admin_init' ) );

				add_filter( 'jlt_admin_settings_tabs_array', array( $this, 'add_setting_application_tab' ), 11 );
				add_action( 'jlt_admin_setting_application', array( $this, 'setting_application' ) );
				add_filter( 'manage_edit-application_columns', array( $this, 'columns' ) );
				add_action( 'manage_application_posts_custom_column', array( $this, 'custom_columns' ), 2 );
				add_filter( 'enter_title_here', array( $this, 'enter_title_here' ), 1, 2 );
				add_action( 'restrict_manage_posts', array( $this, 'restrict_manage_posts' ) );
				add_filter( 'parse_query', array( $this, 'posts_filter' ) );
				add_filter( 'post_updated_messages', array( $this, 'post_updated_messages' ) );

				add_action( 'add_meta_boxes', array( $this, 'add_meta_boxes' ), 30 );
				add_action( 'save_post', array( $this, 'process_application' ), 30 );

				add_filter( 'views_edit-application', array( $this, 'modified_views_status' ) );
				foreach ( array( 'post', 'post-new' ) as $hook ) {
					add_action( "admin_footer-{$hook}.php", array( $this, 'extend_application_status' ) );
				}
			}
		}

		public function admin_init() {
			register_setting( 'jlt_application', 'jlt_application' );
		}

		public function admin_menu() {
			global $submenu;
			$permalink = jlt_admin_setting_page_url( 'application' );

			$submenu[ 'edit.php?post_type=application' ][] = array( 'Settings', 'edit_theme_options', $permalink );
		}

		public function add_meta_boxes() {
			$helper = new JLT_Meta_Boxes_Helper( '', array( 'page' => 'application' ) );

			$meta_box = array(
				'id'       => "job_application_settings",
				'title'    => __( 'Application Information', 'job-listings' ),
				'page'     => 'application',
				'context'  => 'normal',
				'priority' => 'high',
				'fields'   => array(
					array(
						'id'       => 'post_author_override',
						'label'    => __( 'Candidate', 'noo' ),
						'type'     => 'applicant',
						'callback' => array( $this, 'meta_box_applicant' ),
					),
					array(
						'id'    => '_candidate_email',
						'label' => __( 'Contact Email', 'job-listings' ),
						'type'  => 'text',
					),
					array(
						'id'       => 'content',
						'label'    => __( 'Message', 'job-listings' ),
						'type'     => 'app_message',
						'callback' => array( $this, 'meta_box_app_message' ),
					),
				),
			);

			$fields = jlt_get_application_custom_fields();
			if ( $fields ) {
				foreach ( $fields as $field ) {
					if ( $field[ 'name' ] == 'application_message' ) {
						continue;
					}

					$id = jlt_application_custom_fields_name( $field[ 'name' ], $field );

					$new_field = jlt_custom_field_to_meta_box( $field, $id );

					$meta_box[ 'fields' ][] = $new_field;
				}
			}

			$helper->add_meta_box( $meta_box );

			$meta_box = array(
				'id'       => 'job',
				'title'    => __( 'Job', 'job-listings' ),
				'page'     => 'application',
				'context'  => 'side',
				'priority' => 'default',
				'fields'   => array(
					array(
						'id'       => 'parent_id',
						'label'    => __( 'Apply for Job', 'job-listings' ),
						'type'     => 'app_job',
						'callback' => array( $this, 'meta_box_app_job' ),
					),
				),
			);

			$helper->add_meta_box( $meta_box );
		}

		public function process_application( $post_id ) {
			if ( get_post_type( $post_id ) == 'application' ) {

				remove_action( 'save_post', array( $this, 'process_application' ), 30 );

				if ( ! empty( $_POST[ 'post_author_override' ] ) ) {
					$candidate = get_userdata( $_POST[ 'post_author_override' ] );
					wp_update_post( array(
						'ID'         => $post_id,
						'post_title' => $candidate->display_name,
					) );
					update_post_meta( $post_id, '_candidate_email', $candidate->user_email );
				}

				add_action( 'save_post', array( $this, 'process_application' ), 30 );
			}
		}

		public function meta_box_applicant( $post, $id, $type, $meta, $std, $field ) {
			$candidates = jlt_get_members( JLT_Member::CANDIDATE_ROLE );
			$options    = array();
			foreach ( $candidates as $candidate ) {
				$options[] = array(
					'value' => $candidate->ID,
					'label' => $candidate->display_name,
				);
			}
			$field[ 'options' ] = $options;

			$std             = '';
			$candidate_email = get_post_meta( $post->ID, '_candidate_email', true );
			$user            = ! empty( $candidate_email ) ? get_user_by( 'email', $candidate_email ) : false;
			$user_id         = ! empty( $user ) ? $user->ID : '';
			$meta            = $post->post_status == 'auto-draft' ? $std : $user_id;

			$chosen_class = ! is_rtl() ? 'jlt-admin-chosen' : 'jlt-admin-chosen chosen-rtl';
			?>
			<select id="<?php echo $id; ?>" name="<?php echo $id; ?>" class="<?php echo $chosen_class; ?>">
				<option value=""><?php echo __( 'Guest user', 'noo' ); ?></option>
				<?php foreach ( $candidates as $candidate ) : ?>
					<option
						value="<?php echo $candidate->ID; ?>" <?php selected( $meta, $candidate->ID ); ?>><?php echo $candidate->display_name; ?></option>
				<?php endforeach; ?>
			</select>
			<script>
				jQuery(document).ready(function ($) {
					var author_el = $('#post_author_override');

					author_el.change(function () {
						if (author_el.val() === "") {
							$(".post_title").show();
							$("._candidate_email").show();
						} else {
							$(".post_title").hide();
							$("._candidate_email").hide();
						}
					}).change();
				});
			</script>
			<?php
		}

		public function meta_box_app_job( $post, $id, $type, $meta, $std, $field ) {
			$jobs         = get_posts( array(
				'post_type'      => 'job',
				'post_status'    => array( 'publish', 'inactive', 'expired' ),
				'posts_per_page' => - 1,
			) );
			$chosen_class = ! is_rtl() ? 'jlt-admin-chosen' : 'jlt-admin-chosen chosen-rtl';
			$meta         = (int) $post->post_parent;
			?>
			<select id="<?php echo $id; ?>" name="<?php echo $id; ?>" class="<?php echo $chosen_class; ?>">
				<option value=""><?php echo __( 'Select Job', 'job-listings' ); ?></option>
				<?php foreach ( $jobs as $job ) : ?>
					<option value="<?php echo $job->ID; ?>" class="candidate_<?php echo $job->post_author; ?>"
					        data-permalink="<?php echo get_permalink( $job->ID ); ?>" <?php selected( $meta, $job->ID ); ?>><?php echo $job->post_title; ?></option>
				<?php endforeach; ?>
			</select>
			<?php
		}

		public function meta_box_app_message( $post, $id, $type, $meta, $std, $field ) {
			$configs = array(
				'media_buttons' => false,
				'textarea_name' => $id,
				'textarea_rows' => 15,
				'teeny'         => true,
			);

			wp_editor( $post->post_content, $id, $configs );
		}

		public function meta_box_author( $post, $id, $type, $meta, $std, $field ) {

			wp_dropdown_users( array(
				'who'              => '',
				'id'               => $id,
				'show_option_none' => __( 'Guest user', 'job-listings' ),
				'name'             => "jlt_meta_boxes[" . $id . "]",
				'selected'         => $post->post_author,
				'include_selected' => true,
			) );
		}

		public function enter_title_here( $text, $post ) {
			if ( $post->post_type == 'application' ) {
				return __( 'Candidate name', 'job-listings' );
			}

			return $text;
		}

		public function post_updated_messages( $messages ) {
			$messages[ 'application' ] = array(
				0  => '',
				1  => __( 'Job application updated.', 'job-listings' ),
				2  => __( 'Custom field updated.', 'job-listings' ),
				3  => __( 'Custom field deleted.', 'job-listings' ),
				4  => __( 'Job application updated.', 'job-listings' ),
				5  => '',
				6  => __( 'Job application published.', 'job-listings' ),
				7  => __( 'Job application saved.', 'job-listings' ),
				8  => __( 'Job application submitted.', 'job-listings' ),
				9  => '',
				10 => __( 'Job application draft updated.', 'job-listings' ),
			);

			return $messages;
		}

		public function modified_views_status( $views ) {
			if ( isset( $views[ 'publish' ] ) ) {
				$views[ 'publish' ] = str_replace( 'Published ', __( 'Approved', 'job-listings' ) . ' ', $views[ 'publish' ] );
			}

			return $views;
		}

		public function restrict_manage_posts() {
			global $typenow, $wp_query, $wpdb;

			if ( 'application' != $typenow ) {
				return;
			}

			?>
			<select id="dropdown_jlt_job" name="job">
				<option value=""><?php _e( 'All jobs', 'job-listings' ) ?></option>
				<?php
				$jobs_with_applications = $wpdb->get_col( "SELECT DISTINCT post_parent FROM {$wpdb->posts} WHERE post_type = 'application'" );
				$current                = isset( $_GET[ 'job' ] ) ? intval( $_GET[ 'job' ] ) : 0;
				foreach ( $jobs_with_applications as $job_id ) {
					if ( ( $title = get_the_title( $job_id ) ) && $job_id ) {
						echo '<option value="' . $job_id . '" ' . selected( $current, $job_id, false ) . '">' . $title . '</option>';
					}
				}
				?>
			</select>
			<?php
			// Candidate
			$candidates = jlt_get_members( JLT_Member::CANDIDATE_ROLE );
			?>
			<select name="candidate">
				<option value=""><?php _e( 'All Candidates', 'job-listings' ); ?></option>
				<?php
				$current_v = isset( $_GET[ 'candidate' ] ) ? intval(sanitize_text_field($_GET[ 'candidate' ])) : '';
				foreach ( $candidates as $candidate ) {
					printf( '<option value="%s"%s>%s</option>', $candidate->ID, $candidate->ID == $current_v ? ' selected="selected"' : '', empty( $candidate->display_name ) ? $candidate->login_name : $candidate->display_name );
				}
				?>
			</select>
			<?php
		}

		public function posts_filter( $query ) {
			global $pagenow;
			$type = 'post';
			if ( isset( $_GET[ 'post_type' ] ) ) {
				$type = sanitize_text_field( $_GET[ 'post_type' ] );
			}
			if ( 'application' == $type && is_admin() && $pagenow == 'edit.php' ) {
				if ( ! isset( $query->query_vars[ 'post_type' ] ) || $query->query_vars[ 'post_type' ] == 'application' ) {
					if ( isset( $_GET[ 'job' ] ) && $_GET[ 'job' ] != '' ) {
						$job_id = intval( sanitize_text_field( $_GET[ 'job' ] ) );

						$query->query_vars[ 'post_parent' ] = $job_id;
					}
					if ( isset( $_GET[ 'candidate' ] ) && $_GET[ 'candidate' ] != '' ) {

						$candidate_id   = intval( sanitize_text_field( $_GET[ 'candidate' ] ) );
						$candidate_info = get_userdata( $candidate_id );

						$query->query_vars[ 'meta_query' ][] = array(
							'key'   => '_candidate_email',
							'value' => $candidate_info->user_email,
						);
					}
				}
			}
		}

		public function columns( $columns ) {
			if ( ! is_array( $columns ) ) {
				$columns = array();
			}

			unset( $columns[ 'title' ], $columns[ 'date' ] );

			$columns[ "application_status" ]      = __( "Status", 'job-listings' );
			$columns[ "candidate" ]               = __( "Candidate", 'job-listings' );
			$columns[ "job" ]                     = __( "Job applied for", 'job-listings' );
			$columns[ "attachment" ]              = __( "Attachment", 'job-listings' );
			$columns[ "job_application_posted" ]  = __( "Posted", 'job-listings' );
			$columns[ 'job_application_actions' ] = __( "Actions", 'job-listings' );

			return $columns;
		}

		public function custom_columns( $column ) {
			global $post;

			switch ( $column ) {
				case "application_status" :
					$status   = $post->post_status;
					$statuses = self::get_application_status();
					if ( isset( $statuses[ $status ] ) ) {
						$status = $statuses[ $status ];
					} else {
						$status = __( 'Inactive', 'job-listings' );
					}
					echo '<span class="job-application-status job-application-status-' . sanitize_html_class( $status ) . '">';
					echo esc_html( $status );
					echo '</span>';
					break;
				case "candidate" :
					echo '<a href="' . admin_url( 'post.php?post=' . $post->ID . '&action=edit' ) . '" class="tips candidate_name" data-tip="' . sprintf( __( 'ID: %d', 'job-listings' ), $post->ID ) . '"><strong>' . $post->post_title . '</strong></a>';
					if ( $email = get_post_meta( $post->ID, '_candidate_email', true ) ) {
						echo '<br/><a href="mailto:' . esc_attr( $email ) . '">' . esc_attr( $email ) . '</a>';
					}
					break;
				case 'job' :
					$job = get_post( $post->post_parent );

					if ( $job && $job->post_type === 'job' ) {
						echo '<a href="' . get_permalink( $job->ID ) . '">' . $job->post_title . '</a>';
					} elseif ( $job = get_post_meta( $post->ID, '_job_applied_for', true ) ) {
						echo esc_html( $job );
					} else {
						echo '<span class="na">&ndash;</span>';
					}
					break;
				case 'attachment' :
					$attachment = jlt_correct_application_attachment( $post->ID );

					if ( ! empty( $attachment ) ) :
						if ( is_string( $attachment ) && strpos( $attachment, 'linkedin' ) ) : ?>
							<a href="<?php echo esc_url( $attachment ); ?>" target="_blank"><i
									class="dashicons dashicons-external"></i>&nbsp;<?php echo esc_html__( 'LinkedIn profile', 'job-listings' ); ?>
							</a><br/>
						<?php else :
							$attachment = ! is_array( $attachment ) ? array( $attachment ) : $attachment;
							foreach ( $attachment as $atm ) : ?>
								<?php
								$atm       = jlt_json_decode( $atm );
								$file      = $atm[ 0 ];
								$file_url  = jlt_get_file_upload( $file );
								$file_name = basename( $file );
								?>
								<a href="<?php echo esc_url( $file_url ); ?>" target="blank"><i
										class="dashicons dashicons-paperclip"></i>&nbsp;<?php echo esc_html( $file_name ); ?>
								</a>
								<br/>
							<?php endforeach;
						endif;
					endif;
					do_action( 'jlt_admin_list_application_attachment', $post->ID );
					break;
				case "job_application_posted" :
					echo '<span><strong>' . date_i18n( get_option( 'date_format' ), strtotime( $post->post_date ) ) . '</strong><span><br>';
					$email = jlt_get_post_meta( $post->ID, '_candidate_email', true );
					$user  = get_user_by( 'email', $email );
					echo ! $user ? __( 'by a Guest', 'job-listings' ) : sprintf( __( 'by %s', 'job-listings' ), '<a href="' . get_edit_user_link( $user->ID ) . '">' . $user->display_name . '</a>' );
					echo '</span>';
					break;
				case "job_application_actions" :
					echo '<div class="actions">';
					$admin_actions = array();
					if ( $post->post_status !== 'trash' ) {
						$admin_actions[ 'view' ]   = array(
							'action' => 'view',
							'name'   => __( 'View', 'job-listings' ),
							'url'    => get_edit_post_link( $post->ID ),
							'icon'   => 'visibility',
						);
						$admin_actions[ 'delete' ] = array(
							'action' => 'delete',
							'name'   => __( 'Delete', 'job-listings' ),
							'url'    => get_delete_post_link( $post->ID ),
							'icon'   => 'trash',
						);
					}

					$admin_actions = apply_filters( 'jlt_application_manager_admin_actions', $admin_actions, $post );

					foreach ( $admin_actions as $action ) {
						printf( '<a class="button tips icon-%1$s" href="%2$s" data-tip="%3$s">%4$s</a>', $action[ 'action' ], esc_url( $action[ 'url' ] ), esc_attr( $action[ 'name' ] ), '<i class="dashicons dashicons-' . $action[ 'icon' ] . '"></i>' );
					}

					echo '</div>';

					break;
			}
		}

		public function add_setting_application_tab( $tabs ) {
			$temp1 = array_slice( $tabs, 0, 2 );
			$temp2 = array_slice( $tabs, 2 );

			$application_tab = array( 'application' => __( 'Job Application', 'job-listings' ) );

			return array_merge( $temp1, $application_tab, $temp2 );
		}

		public function setting_application() {

			$custom_apply_link = jlt_get_application_setting( 'custom_apply_link' );

			$approve_title   = jlt_get_application_setting( 'approve_title', '' );
			$approve_message = jlt_get_application_setting( 'approve_message', '' );

			$reject_title   = jlt_get_application_setting( 'reject_title', '' );
			$reject_message = jlt_get_application_setting( 'reject_message', '' );

			?>
			<?php settings_fields( 'jlt_application' ); ?>
			<h3><?php _e( 'General Options', 'job-listings' ); ?></h3>
			<table class="form-table" cellpadding="0">
				<tbody>
				<tr>
					<th>
						<?php esc_html_e( 'Enable custom application link', 'job-listings' ) ?>
					</th>
					<td>
						<fieldset>
							<label><input type="radio" <?php checked( $custom_apply_link, '' ); ?>
							              name="jlt_application[custom_apply_link]"
							              value=""><?php _e( 'No', 'job-listings' ); ?>
							</label><br/>
							<label><input type="radio" <?php checked( $custom_apply_link, 'admin' ); ?>
							              name="jlt_application[custom_apply_link]"
							              value="admin"><?php _e( 'Yes, on the dashboard', 'job-listings' ); ?>
							</label><br/>
							<label><input type="radio" <?php checked( $custom_apply_link, 'employer' ); ?>
							              name="jlt_application[custom_apply_link]"
							              value="employer"><?php _e( 'Yes, on both dashboard and frontend', 'job-listings' ); ?>
							</label><br/>
						</fieldset>
					</td>
				</tr>
				<tr>
					<th>
						<?php _e( 'Application Approve Title', 'job-listings' ) ?>
					</th>
					<td>
						<input class="jlt-setting-control jlt-setting-text" type="text"
						       name="jlt_application[approve_title]" value="<?php echo $approve_title; ?>"/>
					</td>
				</tr>

				<tr>
					<th>
						<?php _e( 'Application Approve Message', 'job-listings' ) ?>
					</th>
					<td>
						<textarea class="jlt-setting-control jlt-setting-textarea" rows="5"
						          name="jlt_application[approve_message]"><?php echo $approve_message; ?></textarea>
					</td>
				</tr>

				<tr>
					<th>
						<?php _e( 'Application Reject Title', 'job-listings' ) ?>
					</th>
					<td>
						<input class="jlt-setting-control jlt-setting-text" type="text"
						       name="jlt_application[reject_title]" value="<?php echo $reject_title; ?>"/>
					</td>
				</tr>

				<tr>
					<th>
						<?php _e( 'Application Reject Message', 'job-listings' ) ?>
					</th>
					<td>
						<textarea class="jlt-setting-control jlt-setting-textarea" rows="5"
						          name="jlt_application[reject_message]"><?php echo $reject_message; ?></textarea>
					</td>
				</tr>

				<?php do_action( 'jlt_setting_application_general_fields' ); ?>
				</tbody>
			</table>
			<?php
		}

		public static function new_job_application( $job_id, $candidate_name, $candidate_email, $application_message, $meta = array(), $send_notification = true ) {
			$job = get_post( $job_id );
			if ( ! $job || $job->post_type !== 'job' ) {
				return false;
			}

			$post_data      = array(
				'post_title'     => wp_kses_post( $candidate_name ),
				'post_content'   => wp_kses_post( $application_message ),
				'post_status'    => 'pending',
				'post_type'      => 'application',
				'comment_status' => 'closed',
				'post_author'    => get_current_user_id(),
				'post_parent'    => $job_id,
			);
			$application_id = wp_insert_post( $post_data );
			if ( $application_id ) {
				update_post_meta( $application_id, '_job_applied_for', $job->post_title );
				update_post_meta( $application_id, '_candidate_email', $candidate_email );
				$_candidate_user_id = get_current_user_id();
				update_post_meta( $application_id, '_candidate_user_id', $_candidate_user_id );

				if ( $meta ) {
					foreach ( $meta as $key => $value ) {
						update_post_meta( $application_id, $key, $value );
					}
				}
				if ( $send_notification ) {
					//Send email
					self::send_notification( array(
						'job_id'              => $job_id,
						'application_id'      => $application_id,
						'candidate_email'     => $candidate_email,
						'candidate_name'      => $candidate_name,
						'application_message' => $application_message,
					) );
				}

				return $application_id;
			}

			return false;
		}

		public static function send_notification( $args = '' ) {
			$defaults = array(
				'job_id'              => '',
				'application_id'      => '',
				'candidate_email'     => '',
				'candidate_name'      => '',
				'application_message' => '',
			);

			$p = wp_parse_args( $args, $defaults );
			extract( $p );

			$job = get_post( $job_id );

			if ( ! $job || $job->post_type !== 'job' ) {
				return;
			}

			$blogname = get_bloginfo( 'name' );

			//employer email
			if ( jlt_email_get_setting( 'employer_job_application', 'active', 1 ) ) {
				$subject = jlt_email_get_setting( 'employer_job_application', 'subject' );

				$array_subject = array(
					'[job_title]'      => $job->post_title,
					'[candidate_name]' => $candidate_name,
					'[site_name]'      => $blogname,
					'[site_url]'       => esc_url( home_url( '' ) ),
				);

				$subject = str_replace( array_keys( $array_subject ), $array_subject, $subject );

				$notify_email = get_post_meta( $job_id, '_application_email', true );
				$employer     = get_userdata( $job->post_author );

				if ( ! empty( $notify_email ) && strstr( $notify_email, '@' ) && is_email( $notify_email ) ) {
					$to = $notify_email;
				} elseif ( $job->post_author ) {
					$to = $employer->user_email;
				} else {
					$to = '';
				}

				$attachment   = jlt_get_post_meta( $application_id, '_attachment', '' );
				$attach_files = array();
				if ( ! empty( $attachment ) ) {
					if ( is_string( $attachment ) && strpos( $attachment, 'linkedin' ) ) {
						$attachment = esc_url( $attachment );
					} else {
						$email_attachment = true;
						if ( $email_attachment ) {
							$upload_dir = wp_upload_dir();
							$attachment = ! is_array( $attachment ) ? array( $attachment ) : $attachment;
							foreach ( $attachment as $atm ) {
								if ( strpos( $atm, $upload_dir[ 'baseurl' ] ) === 0 ) {
									$attach_files[] = str_replace( $upload_dir[ 'baseurl' ], $upload_dir[ 'basedir' ], $atm );
								}
							}
						}
						$attachment = JLT_Member::get_endpoint_url( 'manage-application' );
					}
				}

				$resume = jlt_get_post_meta( $application_id, '_resume', '' );
				if ( ! empty( $resume ) ) {
					$attachment = add_query_arg( 'application_id', $application_id, get_permalink( $resume ) );
				}

				$application = get_post( $application_id );

				if ( $to && ! empty( $employer ) ) {

					$array_message = array(
						'[job_title]'              => $job->post_title,
						'[job_url]'                => get_permalink( $job ),
						'[job_company]'            => $employer->display_name,
						'[candidate_name]'         => $candidate_name,
						'[resume_url]'             => $attachment,
						'[application_message]'    => $application->post_content,
						'[application_manage_url]' => JLT_Member::get_endpoint_url( 'manage-application' ),
						'[site_name]'              => $blogname,
						'[site_url]'               => esc_url( home_url( '' ) ),
					);

					$message = jlt_email_get_setting( 'employer_job_application', 'content' );
					$message = str_replace( array_keys( $array_message ), $array_message, $message );

					$subject = jlt_et_custom_field( 'application', $application_id, $subject );
					$message = jlt_et_custom_field( 'application', $application_id, $message );

					jlt_mail( $to, $subject, $message, array(), 'jlt_notify_job_apply_employer', $attach_files );
				}
			}

			//candidate email
			if ( jlt_email_get_setting( 'candidate_job_application', 'active', 1 ) ) {

				$subject = jlt_email_get_setting( 'candidate_job_application', 'subject' );

				$array_subject = array(
					'[job_title]' => $job->post_title,
					'[site_name]' => $blogname,
					'[site_url]'  => esc_url( home_url( '' ) ),
				);

				$subject = str_replace( array_keys( $array_subject ), $array_subject, $subject );

				$to = $candidate_email;

				$application = get_post( $application_id );

				$array_message = array(
					'[job_title]'              => $job->post_title,
					'[job_url]'                => get_permalink( $job ),
					'[job_company]'            => $employer->display_name,
					'[candidate_name]'         => $candidate_name,
					'[resume_url]'             => $attachment,
					'[application_message]'    => $application->post_content,
					'[application_manage_url]' => JLT_Member::get_endpoint_url( 'manage-job-applied' ),
					'[site_name]'              => $blogname,
					'[site_url]'               => esc_url( home_url( '' ) ),
				);

				$message = jlt_email_get_setting( 'candidate_job_application', 'content' );
				$message = str_replace( array_keys( $array_message ), $array_message, $message );

				$subject = jlt_et_custom_field( 'application', $application_id, $subject );
				$message = jlt_et_custom_field( 'application', $application_id, $message );

				jlt_mail( $to, $subject, $message, array(), 'jlt_notify_job_apply_candidate' );
			}

			return;
		}

		public function register_post_type() {
			if ( post_type_exists( 'application' ) ) {
				return;
			}

			register_post_type( 'application', array(
				'labels'              => array(
					'name'               => __( 'Job Applications', 'job-listings' ),
					'singular_name'      => __( 'Job Application', 'job-listings' ),
					'add_new'            => __( 'Add New Application', 'job-listings' ),
					'add_new_item'       => __( 'Add Job Application', 'job-listings' ),
					'edit'               => __( 'Edit Job Application', 'job-listings' ),
					'edit_item'          => __( 'Edit Job Application', 'job-listings' ),
					'new_item'           => __( 'New Job Application', 'job-listings' ),
					'view'               => __( 'View Job Application', 'job-listings' ),
					'view_item'          => __( 'View Job Application', 'job-listings' ),
					'search_items'       => __( 'Search Job Application', 'job-listings' ),
					'not_found'          => __( 'No Job Applications found', 'job-listings' ),
					'not_found_in_trash' => __( 'No Job Applications found in Trash', 'job-listings' ),
					'parent'             => __( 'Parent Job Application', 'job-listings' ),
				),
				'description'         => __( 'This is the place where you can edit and view job applications.', 'job-listings' ),
				'menu_icon'           => 'dashicons-pressthis',
				'public'              => false,
				'show_ui'             => true,
				'publicly_queryable'  => false,
				'exclude_from_search' => true,
				'hierarchical'        => false,
				'rewrite'             => false,
				'query_var'           => false,
				'supports'            => false,
				'has_archive'         => false,
				'show_in_nav_menus'   => false,
				'delete_with_user'    => true,
			) );

			register_post_status( 'rejected', array(
				'label'                     => __( 'Rejected', 'job-listings' ),
				'public'                    => false,
				'exclude_from_search'       => true,
				'show_in_admin_all_list'    => true,
				'show_in_admin_status_list' => true,
				'label_count'               => _n_noop( 'Rejected <span class="count">(%s)</span>', 'Rejected <span class="count">(%s)</span>', 'job-listings' ),
			) );

			register_post_status( 'inactive', array(
				'label'                     => __( 'Inactive', 'job-listings' ),
				'public'                    => false,
				'exclude_from_search'       => true,
				'show_in_admin_all_list'    => true,
				'show_in_admin_status_list' => true,
				'label_count'               => _n_noop( 'Inactive <span class="count">(%s)</span>', 'Inactive <span class="count">(%s)</span>', 'job-listings' ),
			) );
		}

		public function extend_application_status() {
			global $post, $post_type;
			if ( $post_type === 'application' ) {
				$html = $selected_label = '';
				foreach ( (array) self::get_application_status() as $status => $label ) {
					$seleced = selected( $post->post_status, esc_attr( $status ), false );
					if ( $seleced ) {
						$selected_label = $label;
					}
					$html .= "<option " . $seleced . " value='" . esc_attr( $status ) . "'>" . $label . "</option>";
				}
				?>
				<script type="text/javascript">
					jQuery(document).ready(function ($) {
						<?php if ( ! empty( $selected_label ) ) : ?>
						jQuery('#post-status-display').html('<?php echo esc_js( $selected_label ); ?>');
						<?php endif; ?>
						var select = jQuery('#post-status-select').find('select');
						jQuery(select).html("<?php echo( $html ); ?>");
					});
				</script>
				<?php
			}
		}

		public static function get_application_status() {
			return apply_filters( 'jlt_application_status', array(
				'publish'  => __( 'Approved', 'job-listings' ),
				'rejected' => __( 'Rejected', 'job-listings' ),
				'pending'  => __( 'Pending', 'job-listings' ),
				'inactive' => __( 'Inactive', 'job-listings' ),
			) );
		}

		public static function can_edit_application( $user_id = 0, $application_id = 0 ) {
			if ( empty( $user_id ) ) {
				$user_id = get_current_user_id();
			}
			if ( empty( $user_id ) || empty( $application_id ) ) {
				return false;
			}

			if ( get_post_type( $application_id ) !== 'application' ) {
				return false;
			}

			if ( get_post_status( $application_id ) !== 'pending' ) {
				return false;
			}

			if ( ! JLT_Member::is_employer( $user_id ) ) {
				return false;
			}

			$job_id = get_post_field( 'post_parent', $application_id );

			if ( is_wp_error( $job_id ) ) {
				return false;
			}
			$employer_id = get_post_field( 'post_author', $job_id );

			return ( absint( $employer_id ) === absint( $user_id ) );
		}

		public static function can_trash_application( $user_id = 0, $application_id = 0 ) {
			if ( empty( $user_id ) ) {
				$user_id = get_current_user_id();
			}
			if ( empty( $user_id ) || empty( $application_id ) ) {
				return false;
			}

			if ( get_post_type( $application_id ) !== 'application' ) {
				return false;
			}

			if ( JLT_Member::is_employer( $user_id ) ) {
				$job_id = get_post_field( 'post_parent', $application_id );

				if ( is_wp_error( $job_id ) ) {
					return false;
				}
				$employer_id = get_post_field( 'post_author', $job_id );

				return ( absint( $employer_id ) === absint( $user_id ) );
			} elseif ( JLT_Member::is_candidate( $user_id ) ) {
				$status = get_post_field( 'post_status', $application_id );
				$user   = get_userdata( $user_id );
				$email  = jlt_get_post_meta( $application_id, '_candidate_email' );

				return ( $status === 'pending' && $user && $email == $user->user_email );
			}

			return false;
		}

		public static function can_delete_application( $user_id = 0, $application_id = 0 ) {
			if ( empty( $user_id ) ) {
				$user_id = get_current_user_id();
			}
			if ( empty( $user_id ) || empty( $application_id ) ) {
				return false;
			}

			if ( get_post_type( $application_id ) !== 'application' ) {
				return false;
			}

			if ( ! JLT_Member::is_candidate( $user_id ) ) {
				return false;
			}

			$status = get_post_field( 'post_status', $application_id );
			$user   = get_userdata( $user_id );
			$email  = jlt_get_post_meta( $application_id, '_candidate_email' );

			return ( $status === 'inactive' && $user && $email == $user->user_email );
		}

		public static function has_applied( $candidate_id = 0, $job_id = 0 ) {
			if ( empty( $candidate_id ) ) {
				$candidate_id = get_current_user_id();
			}
			if ( empty( $candidate_id ) || empty( $job_id ) ) {
				return false;
			}

			$candidate        = get_userdata( $candidate_id );
			$application_args = array(
				'post_type'      => 'application',
				'posts_per_page' => - 1,
				'post_status'    => array( 'publish', 'pending', 'rejected' ),
				'post_parent'    => absint( $job_id ),
				'meta_query'     => array(
					array(
						'key'   => '_candidate_email',
						'value' => $candidate->user_email,
					),
				),
			);
			$application      = new WP_Query( $application_args );
			if ( $application->post_count ) {
				return true;
			}

			return false;
		}
	}

	new JLT_Application();
endif;