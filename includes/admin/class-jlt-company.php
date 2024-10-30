<?php
if ( ! class_exists( 'JLT_Company' ) ):
	if ( ! class_exists( 'JLT_CPT' ) ) {
		require_once dirname( __FILE__ ) . '/class-jlt-cpt.php';
	}

	class JLT_Company extends JLT_CPT {

		static $instance  = false;
		static $employers = array();
		static $companies = array();

		public function __construct() {

			$this->post_type  = 'company';
			$this->slug       = 'companies';
			$this->prefix     = 'company';
			$this->option_key = 'jlt_company';

			add_action( 'init', array( $this, 'register_post_type' ), 0 );
			add_action( 'pre_get_posts', array( $this, 'pre_get_posts' ), 1 );
			add_filter( 'posts_search', array( $this, 'search_by_title_only' ), 500, 2 );
			
			add_filter( 'redirect_canonical', array( $this, 'custom_disable_redirect_canonical' ) );

			if ( is_admin() ) {
				add_action( 'admin_init', array( $this, 'admin_init' ) );
				add_action( 'wp_ajax_jlt_set_company_featured', array( $this, 'ajax_set_company_featured' ) );

				add_filter( 'admin_enqueue_scripts', array( $this, 'admin_enqueue_scripts' ), 10, 2 );
				add_filter( 'display_post_states', array( $this, 'admin_page_state' ), 10, 2 );
				add_action( 'add_meta_boxes', array( $this, 'companies_page_notice' ), 10, 2 );
				add_action( 'add_meta_boxes', array( $this, 'remove_meta_boxes' ), 20 );
				add_action( 'add_meta_boxes', array( $this, 'add_meta_boxes' ), 30 );
				add_action( 'add_meta_boxes', array( $this, 'add_meta_boxes_membership' ), 40 );
				add_filter( 'enter_title_here', array( $this, 'custom_enter_title' ) );

				add_filter( 'manage_edit-' . $this->post_type . '_columns', array( $this, 'manage_edit_columns' ) );
				add_action( 'manage_posts_custom_column', array( $this, 'manage_posts_custom_column' ) );

				add_filter( 'jlt_admin_settings_tabs_array', array( $this, 'add_seting_company_tab' ) );
				add_action( 'jlt_admin_setting_company', array( $this, 'setting_page' ) );

				add_filter( 'wp_insert_post', array( $this, 'default_company_data' ), 10, 3 );

				// add_filter('months_dropdown_results', '__return_empty_array');
				// add_action( 'restrict_manage_posts', array($this, 'restrict_manage_posts') );
				// add_filter( 'parse_query', array($this, 'posts_filter') );
			}
		}

		public static function get_setting( $id = null, $default = null ) {
			global $jlt_company_setting;
			if ( ! isset( $jlt_company_setting ) || empty( $jlt_company_setting ) ) {
				$jlt_company_setting = get_option( 'jlt_company' );
			}
			if ( isset( $jlt_company_setting[ $id ] ) ) {
				return $jlt_company_setting[ $id ];
			}

			return $default;
		}

		public function admin_init() {
			register_setting( 'jlt_company', 'jlt_company' );
		}

		public function register_post_type() {
			// Sample register post type
			$archive_slug = self::get_setting( 'archive_slug', 'companies' );
			
			$archive_slug = empty( $archive_slug ) ? 'companies' : $archive_slug;

			register_post_type( $this->post_type, array(
				'labels'       => array(
					'name'               => __( 'Companies', 'job-listings' ),
					'singular_name'      => __( 'Company', 'job-listings' ),
					'menu_name'          => __( 'Companies', 'job-listings' ),
					'all_items'          => __( 'Companies', 'job-listings' ),
					'add_new'            => __( 'Add New', 'job-listings' ),
					'add_new_item'       => __( 'Add Company', 'job-listings' ),
					'edit'               => __( 'Edit', 'job-listings' ),
					'edit_item'          => __( 'Edit Company', 'job-listings' ),
					'new_item'           => __( 'New Company', 'job-listings' ),
					'view'               => __( 'View', 'job-listings' ),
					'view_item'          => __( 'View Company', 'job-listings' ),
					'view_items'         => __( 'View Company Listings', 'job-listings' ),
					'search_items'       => __( 'Search Company', 'job-listings' ),
					'not_found'          => __( 'No Companies found', 'job-listings' ),
					'not_found_in_trash' => __( 'No Companies found in Trash', 'job-listings' ),
				),
				'public'       => true,
				'has_archive'  => true,
				'show_in_menu' => 'edit.php?post_type=job',
				'rewrite'      => array( 'slug' => $archive_slug, 'with_front' => false ),
				'supports'     => array(
					'title',
					'editor',
				),
			) );
		}

		public function admin_page_state( $states = array(), $post = null ) {
			if ( ! empty( $post ) && is_object( $post ) ) {
				$archive_slug = self::get_setting( 'archive_slug', 'companies' );
				if ( ! empty( $archive_slug ) && $archive_slug == $post->post_name ) {
					$states[ 'companies_page' ] = __( 'Companies Page', 'job-listings' );
				}
			}

			return $states;
		}

		public function companies_page_notice( $post_type = '', $post = null ) {
			if ( ! empty( $post ) && is_object( $post ) ) {
				$archive_slug = self::get_setting( 'archive_slug', 'companies' );
				if ( ! empty( $archive_slug ) && $archive_slug == $post->post_name && empty( $post->post_content ) ) {
					add_action( 'edit_form_after_title', array( $this, '_companies_page_notice' ) );
					remove_post_type_support( $post_type, 'editor' );
				}
			}
		}

		public function _companies_page_notice() {
			echo '<div class="notice notice-warning inline"><p>' . __( 'You are currently editing the page that shows all your companies.', 'job-listings' ) . '</p></div>';
		}

		public function admin_enqueue_scripts() {
			if ( get_post_type() === 'company' ) {
				wp_enqueue_style( 'jlt-company-admin', JLT_PLUGIN_URL . 'admin/css/jlt-company-admin.css' );
			}
		}

		public function remove_meta_boxes() {
			// Remove slug and revolution slider
			remove_meta_box( 'mymetabox_revslider_0', $this->post_type, 'normal' );
		}

		public function add_meta_boxes() {
			// Declare helper object
			$prefix = '';
			$helper = new JLT_Meta_Boxes_Helper( $prefix, array( 'page' => $this->post_type ) );

			// General Info
			$meta_box = array(
				'id'          => '_general_info',
				'title'       => __( 'Company Information', 'job-listings' ),
				'context'     => 'normal',
				'priority'    => 'core',
				'description' => '',
				'fields'      => array(),
			);

			$fields = jlt_get_company_custom_fields();
			if ( $fields ) {
				foreach ( $fields as $field ) {
					$id = jlt_company_custom_fields_name( $field[ 'name' ], $field );

					$new_field = jlt_custom_field_to_meta_box( $field, $id );
//
//					if ( $field[ 'name' ] == '_address' ) {
//						$new_field[ 'type' ] = 'select';
//						$job_locations       = array();
//						// $job_locations[] = array('value'=>'','label'=>__('- Select a location -','job-listings'));
//						$job_locations_terms = (array) get_terms( 'job_location', array( 'hide_empty' => 0 ) );
//
//						if ( ! empty( $job_locations_terms ) ) {
//							foreach ( $job_locations_terms as $location ) {
//								$job_locations[] = array( 'value' => $location->term_id, 'label' => $location->name );
//							}
//						}
//
//						$new_field[ 'options' ] = $job_locations;
//					}

					$meta_box[ 'fields' ][] = $new_field;
				}
			}

			$all_socials = jlt_get_social_fields();
			$socials     = jlt_get_company_socials();

			if ( $socials ) {

				foreach ( $socials as $social ) {
					if ( ! isset( $all_socials[ $social ] ) ) {
						continue;
					}

					$new_field              = array(
						'label' => $all_socials[ $social ][ 'label' ],
						'id'    => $prefix . '_' . $social,
						'type'  => 'text',
						'std'   => '',
					);
					$meta_box[ 'fields' ][] = $new_field;
				}
			}

			$helper->add_meta_box( $meta_box );
		}

		public function add_meta_boxes_membership() {
			// Declare helper object
			$prefix = '';
			$helper = new JLT_Meta_Boxes_Helper( $prefix, array( 'page' => 'company' ) );

			// General Info
			$meta_box = array(
				'id'          => 'employer_membership',
				'title'       => __( 'Job Package', 'job-listings' ),
				'context'     => 'side',
				'priority'    => 'default',
				'description' => '',
				'fields'      => array(
					array(
						'id'       => '_employer_membership',
						'label'    => '',
						'type'     => 'job_package_info',
						'std'      => '',
						'callback' => array( $this, 'company_job_package_info' ),
					),
				),
			);

			$helper->add_meta_box( $meta_box );
		}

		public function company_job_package_info( $post, $id, $type, $meta, $std, $field ) {
			$employer_id = $this->get_employer_id( $post->ID );
			$package     = ! empty( $employer_id ) ? jlt_get_job_posting_info( $employer_id ) : null;
			if ( empty( $package ) ) {
				echo __( 'N/A', 'job-listings' );

				return;
			}

			if ( isset( $package[ 'product_id' ] ) && ! empty( $package[ 'product_id' ] ) ) : ?>
				<div class="company-package-info">
					<label><?php echo __( 'Plan', 'job-listings' ); ?></label>
					<strong><a
							href="<?php echo get_edit_post_link( $package[ 'product_id' ] ); ?>"><?php echo get_the_title( $package[ 'product_id' ] ); ?></a></strong>
				</div>
				<?php
			endif;

			$is_unlimited       = $package[ 'job_limit' ] >= 99999999;
			$job_limit_text     = $is_unlimited ? __( 'Unlimited', 'job-listings' ) : sprintf( _n( '%d job', '%d jobs', $package[ 'job_limit' ], 'job-listings' ), number_format_i18n( $package[ 'job_limit' ] ) );
			$job_added          = jlt_get_job_posting_added( $employer_id );
			$feature_job_remain = jlt_get_feature_job_remain( $employer_id );
			if ( $is_unlimited || $package[ 'job_limit' ] > 0 ) :
				?>
				<div class="company-package-info">
					<label><?php _e( 'Job Limit', 'job-listings' ) ?></label>
					<strong><?php echo $job_limit_text; ?></strong>
				</div>
				<div class="company-package-info">
					<label><?php _e( 'Job Added', 'job-listings' ) ?></label>
					<strong><?php echo $job_added > 0 ? sprintf( _n( '%d job', '%d jobs', $job_added, 'job-listings' ), number_format_i18n( $job_added ) ) : __( '0 job', 'job-listings' ); ?></strong>
				</div>
				<div class="company-package-info">
					<label><?php _e( 'Job Duration', 'job-listings' ) ?></label>
					<strong><?php echo sprintf( _n( '%s day', '%s days', $package[ 'job_duration' ], 'job-listings' ), number_format_i18n( $package[ 'job_duration' ] ) ); ?></strong>
				</div>
			<?php endif; ?>
			<?php if ( isset( $package[ 'job_featured' ] ) && ! empty( $package[ 'job_featured' ] ) ) : ?>
				<div class="company-package-info">
					<label><?php _e( 'Featured Job limit', 'job-listings' ) ?></label>
					<strong><?php echo sprintf( _n( '%d job', '%d jobs', $package[ 'job_featured' ], 'job-listings' ), number_format_i18n( $package[ 'job_featured' ] ) ); ?></strong><br/>
					<?php if ( $feature_job_remain < $package[ 'job_featured' ] ) {
						echo '&nbsp;' . sprintf( __( '( %d remain )', 'job-listings' ), $feature_job_remain );
					} ?>
				</div>
			<?php endif;
		}

		public function custom_enter_title( $input ) {
			global $post_type;

			if ( $this->post_type == $post_type ) {
				return __( 'Company Name', 'job-listings' );
			}

			return $input;
		}

		public function manage_edit_columns( $columns ) {

			if ( ! is_array( $columns ) ) {
				$columns = array();
			}

			$before = array_slice( $columns, 0, 2 );
			$after  = array_slice( $columns, 2 );

			$new_columns = array(
				'company_featured' => '<span class="tips" data-tip="' . __( 'Is Company Featured?', 'job-listings' ) . '">' . __( 'Featured?', 'job-listings' ) . '</span>',
				'employer_package' => __( 'Membership Package', 'job-listings' ),
				'job_count'        => __( 'Job Count', 'job-listings' ),
			);

			$columns = array_merge( $before, $new_columns, $after );

			return $columns;
		}

		public function manage_posts_custom_column( $column ) {
			global $post;
			switch ( $column ) {
				case "company_featured" :
					$featured = jlt_get_post_meta( $post->ID, '_company_featured' );
					// Update old data
					if ( empty( $featured ) ) {
						update_post_meta( $post->ID, '_company_featured', 'no' );
					}

					echo '<a href="javascript:void(0)" class="jlt-ajax-btn" title="' . __( 'Toggle featured', 'job-listings' ) . '" data-action="jlt_set_company_featured" data-company_id=
					"' . $post->ID . '" data-nonce="' . wp_create_nonce( 'set-company-featured' ) . '">';
					if ( 'yes' === $featured ) {
						echo '<span class="jlt-company-feature" title="' . esc_attr__( 'Yes', 'job-listings' ) . '"><i class="dashicons dashicons-star-filled "></i></span>';
					} else {
						echo '<span class="jlt-company-feature not-featured"  title="' . esc_attr__( 'No', 'job-listings' ) . '"><i class="dashicons dashicons-star-empty"></i></span>';
					}
					echo '</a>';

					break;
				case 'employer_package':
					$employer_id = $this->get_employer_id( $post->ID );
					$package     = ! empty( $employer_id ) ? jlt_get_job_posting_info( $employer_id ) : null;
					if ( empty( $package ) || ! isset( $package[ 'product_id' ] ) ) {
						echo __( 'N/A', 'job-listings' );
					} else {
						$product_id = absint( $package[ 'product_id' ] );
						echo '<a href="' . get_edit_post_link( $product_id ) . '">' . get_the_title( $product_id ) . '</a>';
					}

					break;
				case 'job_count':
					$employer_id = $this->get_employer_id( $post->ID );
					$package     = ! empty( $employer_id ) ? jlt_get_job_posting_info( $employer_id ) : null;
					if ( empty( $package ) || ! isset( $package[ 'job_limit' ] ) ) {
						echo __( 'N/A', 'job-listings' );
					} else {
						$is_unlimited = $package[ 'job_limit' ] >= 99999999;
						$job_added    = jlt_get_job_posting_added( $employer_id );
						echo sprintf( __( '%s of %s', 'job-listings' ), $job_added, ( $is_unlimited ? __( 'Unlimited', 'job-listings' ) : absint( $package[ 'job_limit' ] ) ) );
					}

					break;
			}
		}

		public function add_seting_company_tab( $tabs ) {
			$temp1 = array_slice( $tabs, 0, 3 );
			$temp2 = array_slice( $tabs, 3 );

			$company_tab = array( 'company' => __( 'Company', 'job-listings' ) );

			return array_merge( $temp1, $company_tab, $temp2 );
		}

		public function setting_page() {
			if ( isset( $_GET[ 'settings-updated' ] ) && $_GET[ 'settings-updated' ] ) {
				flush_rewrite_rules();
			}
			?>
			<?php settings_fields( 'jlt_company' ); ?>
			<h3><?php echo __( 'Company Options', 'job-listings' ) ?></h3>
			<table class="form-table" cellspacing="0">
				<tbody>
				<tr>
					<th>
						<?php esc_html_e( 'Companies Archive base (slug)', 'job-listings' ) ?>
					</th>
					<td>
						<?php $archive_slug = self::get_setting( 'archive_slug', 'companies' ); ?>
						<input type="text" name="jlt_company[archive_slug]"
						       value="<?php echo( $archive_slug ? $archive_slug : 'companies' ) ?>">
					</td>
				</tr>
				<tr>
					<th>
						<?php esc_html_e( 'Number Job Per Page', 'job-listings' ) ?>
					</th>
					<td>
						<?php $number_job = self::get_setting( 'number_job', 10 ); ?>
						<input type="text" name="jlt_company[number_job]"
						       value="<?php echo( $number_job ? $number_job : 10 ) ?>">
						<p><?php _e( 'Enter -1 to show all job without pagination.' ); ?></p>
					</td>
				</tr>
				<tr>
					<th>
						<?php esc_html_e( 'Show Company Map', 'job-listings' ) ?>
					</th>
					<td>
						<?php $show_no_jobs = self::get_setting( 'show_map', 1 ); ?>
						<input type="hidden" name="jlt_company[show_map]" value="">
						<input type="checkbox" <?php checked( $show_no_jobs, '1' ); ?> name="jlt_company[show_map]"
						       value="1"/>
					</td>
				</tr>

				<?php do_action( 'jlt_setting_company_fields' ); ?>

				</tbody>
			</table>
			<?php
		}

		public function default_company_data( $post_ID = 0, $post = null, $update = false ) {

			if ( ! $update && ! empty( $post_ID ) && $post->post_type == 'company' ) {
				update_post_meta( $post_ID, '_company_featured', 'no' );
			}
		}

		public function pre_get_posts( $query ) {
			if ( is_admin() || $query->is_singular ) {
				return $query;
			}

			//if is querying company

			if ( isset( $query->query_vars[ 'post_type' ] ) && $query->query_vars[ 'post_type' ] == 'company' ) {

				add_filter( 'posts_where', array( $this, 'posts_where' ) );

				if ( is_post_type_archive( 'company' ) ) {
				} else {
				}
			}

			return $query;
		}

		public function posts_where( $where ) {
			remove_filter( current_filter(), __FUNCTION__ );
			global $wpdb;
			if ( isset( $_GET[ 'key' ] ) && ! empty( $_GET[ 'key' ] ) ) {
				$first_char = esc_attr( $_GET[ 'key' ] );
				$where .= sprintf( " AND LOWER( SUBSTR( %s.post_title, 1, 1 ) ) = '%s' ", $wpdb->posts, strtolower( $first_char ) );
			} else {
				$where .= sprintf( " AND %s.post_title <> '' ", $wpdb->posts );
			}

			return $where;
		}

		public function search_by_title_only( $search, &$wp_query ) {
			//if is querying company
			if ( isset( $_GET[ 'post_type' ] ) && sanitize_text_field($_GET[ 'post_type' ]) == 'company' ) {
				if ( ! empty( $search ) && ! empty( $wp_query->query_vars[ 'search_terms' ] ) ) {
					global $wpdb;

					$q = $wp_query->query_vars;
					$n = ! empty( $q[ 'exact' ] ) ? '' : '%';

					$search = array();

					foreach ( ( array ) $q[ 'search_terms' ] as $term ) {
						$search[] = $wpdb->prepare( "$wpdb->posts.post_title LIKE %s", $n . $wpdb->esc_like( $term ) . $n );
					}

					$search = ' AND ' . implode( ' AND ', $search );
				}
			}

			return $search;
		}

		public static function get_employer_id( $company_id = null ) {
			if ( empty( $company_id ) ) {
				return 0;
			}

			if ( isset( self::$employers[ $company_id ] ) ) {
				return self::$employers[ $company_id ];
			}

			$employers = get_users( array(
				'meta_key'   => 'employer_company',
				'meta_value' => $company_id,
				'fields'     => 'id',
			) );

			if ( empty( $employers ) && defined( 'ICL_SITEPRESS_VERSION' ) ) {
				// Try to get the employer from the translated company
				$trid         = apply_filters( 'wpml_element_trid', '', $company_id, 'post_jlt_company' );
				$translations = apply_filters( 'wpml_get_element_translations_filter', $company_id, $trid, 'post_jlt_company' );

				if ( ! empty( $translations ) ) {
					foreach ( $translations as $lang => $tran_obj ) {
						$maybe_empl = get_users( array(
							'meta_key'   => 'employer_company',
							'meta_value' => $tran_obj->element_id,
							'fields'     => 'id',
						) );
						if ( ! empty( $maybe_empl ) ) {
							$employers = $maybe_empl;
							break;
						}
					}
				}
			}

			if ( empty( $employers ) ) {
				self::$employers[ $company_id ] = 0;
			} else {
				self::$employers[ $company_id ] = $employers[ 0 ];
			}

			return self::$employers[ $company_id ];
		}

		public static function count_jobs( $company_id = null ) {
			if ( empty( $company_id ) ) {
				return 0;
			}
			global $wpdb;

			$employer = self::get_employer_id( $company_id );

			if ( ! defined( 'ICL_SITEPRESS_VERSION' ) ) {
				$company_jobs = $wpdb->get_var( "SELECT COUNT( DISTINCT p.ID ) FROM {$wpdb->posts} AS p 
					LEFT JOIN {$wpdb->postmeta} AS pm
					ON p.ID = pm.post_id AND pm.meta_key = '_company_id'
					WHERE p.post_type = 'job' AND p.post_status = 'publish'
						AND ( pm.meta_value = '{$company_id}'
							OR (
								p.post_author = {$employer}
								AND ( pm.meta_value IS NULL OR pm.meta_value = '' )
							)
						)" );
			} else {
				$company_jobs = $wpdb->get_var( "SELECT COUNT( DISTINCT p.ID ) FROM {$wpdb->posts} AS p 
					LEFT JOIN {$wpdb->postmeta} AS pm
					ON p.ID = pm.post_id AND pm.meta_key = '_company_id'
					LEFT JOIN {$wpdb->prefix}icl_translations AS wpml
					ON p.ID = wpml.element_id
					WHERE p.post_type = 'job' AND p.post_status = 'publish'
						AND ( pm.meta_value = '{$company_id}'
							OR (
								p.post_author = {$employer}
								AND ( pm.meta_value IS NULL OR pm.meta_value = '' )
							)
						)
						AND wpml.language_code = '" . ICL_LANGUAGE_CODE . "'" );
			}

			return absint( $company_jobs );
		}

		public static function get_company_jobs(
			$company_id = null, $exclude_job_ids = array(), $number_of_jobs = - 1, $status = 'publish'
		) {
			if ( empty( $company_id ) ) {
				return array();
			}
			global $wpdb;

			$employer = self::get_employer_id( $company_id );

			if ( ! defined( 'ICL_SITEPRESS_VERSION' ) ) {
				$query = "SELECT DISTINCT p.ID FROM {$wpdb->posts} AS p 
					LEFT JOIN {$wpdb->postmeta} AS pm
					ON p.ID = pm.post_id AND pm.meta_key = '_company_id'
					WHERE p.post_type = 'job' AND p.post_status = 'publish'
						AND ( pm.meta_value = '{$company_id}'
							OR (
								p.post_author = {$employer}
								AND ( pm.meta_value IS NULL OR pm.meta_value = '' )
							)
						)";
			} else {
				$query = "SELECT DISTINCT p.ID FROM {$wpdb->posts} AS p 
					LEFT JOIN {$wpdb->postmeta} AS pm
					ON p.ID = pm.post_id AND pm.meta_key = '_company_id'
					LEFT JOIN {$wpdb->prefix}icl_translations AS wpml
					ON p.ID = wpml.element_id
					WHERE p.post_type = 'job' AND p.post_status = 'publish'
						AND ( pm.meta_value = '{$company_id}'
							OR (
								p.post_author = {$employer}
								AND ( pm.meta_value IS NULL OR pm.meta_value = '' )
							)
						)
						AND wpml.language_code = '" . ICL_LANGUAGE_CODE . "'";
			}

			if ( ! empty( $status ) && $status != 'all' ) {
				$query .= " AND p.post_status = '{$status}'";
			}

			if ( ! empty( $exclude_job_ids ) ) {
				$query .= " AND p.ID NOT IN ( " . implode( ',', $exclude_job_ids ) . " )";
			}

			$query .= "  ORDER BY p.post_date DESC";

			if ( $number_of_jobs > 0 ) {
				$query .= " LIMIT 0, {$number_of_jobs}";
			}

			return $company_jobs = $wpdb->get_col( $query );
		}

		public static function get_more_jobs( $company_id = null, $exclude_job_ids = array(), $number_of_jobs = 5 ) {
			self::get_company_jobs( $company_id, $exclude_job_ids, $number_of_jobs );
		}

		public function custom_disable_redirect_canonical( $redirect_url ) {
			global $post;
			$ptype = get_post_type( $post );
			if ( $ptype == 'company' ) {
				$redirect_url = false;
			}

			return $redirect_url;
		}

		public static function get_company_logo( $company_id = 0, $size = 'company-logo', $alt = '', $args = array() ) {
			if ( empty( $company_id ) ) {
				return '';
			}

			$size_key = is_array( $size ) ? implode( '_', $size ) : $size;
			if ( ! isset( self::$companies[ $company_id ] ) ) {
				self::$companies[ $company_id ] = array();
			}

			if ( ! isset( self::$companies[ $company_id ][ $size_key ] ) ) {
				$class           = apply_filters( 'jlt_company_logo_class', '', $company_id );
				$thumbnail_id    = jlt_get_post_meta( $company_id, '_logo', '' );
				$size            = is_numeric( $size ) ? array( $size, $size ) : $size;
				$args[ 'alt' ]   = $alt;
				$args[ 'class' ] = isset( $args[ 'class' ] ) ? $args[ 'class' ] . ' ' . $class : $class;
				$company_logo    = wp_get_attachment_image( $thumbnail_id, $size, false, $args );
				if ( empty( $company_logo ) ) {
					$img_size = '';
					if ( is_array( $size ) ) {
						$size[ 1 ] = count( $size ) > 1 ? $size[ 1 ] : $size[ 0 ];
						$img_size  = 'width="' . $size[ 0 ] . 'px" height="' . $size[ 1 ] . 'px"';
					}

					$company_logo = '<img src="' . JLT_PLUGIN_URL . 'public/images/company-logo.png" ' . $img_size . ' class="' . $args[ 'class' ] . '" alt="' . $args[ 'alt' ] . '">';
				}

				self::$companies[ $company_id ][ $size_key ] = $company_logo;
			}

			return apply_filters( 'jlt_company_logo', self::$companies[ $company_id ][ $size_key ], $company_id );
		}


		public function ajax_set_company_featured() {
			$result = check_ajax_referer( 'set-company-featured', 'nonce', false );

			if ( $result ) {
				$post_id = ! empty( $_POST[ 'company_id' ] ) ? intval($_POST[ 'company_id' ]) : '';

				if ( ! $post_id || get_post_type( $post_id ) !== 'company' ) {
					jlt_ajax_exit();
				}

				$featured = jlt_get_post_meta( $post_id, '_company_featured' );

				if ( 'yes' === $featured ) {
					update_post_meta( $post_id, '_company_featured', 'no' );
				} else {
					update_post_meta( $post_id, '_company_featured', 'yes' );
				}
			}

			jlt_ajax_exit( '', true );
		}

	}

	new JLT_Company();
endif;