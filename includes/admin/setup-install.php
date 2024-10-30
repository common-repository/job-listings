<?php
/**
 * Display notices in admin.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class JLT_Notice_Install {

	const product_id   = 'job-listings';
	const product_name = 'Job Listings';

	private $install_option_group = 'jlt_option_install_group';

	private $install_option_name = 'jlt_option_install_name';

	private $install_section_id = 'jlt_option_section_id';

	private $option_metabox = array();

	protected static $_instance = null;

	public function __construct() {

		if ( is_admin() ) {

			add_action( 'admin_notices', array( $this, 'notice_html_install' ) );

			add_action( 'admin_init', array( $this, 'jlt_settings_fields' ) );

			add_action( 'wp_ajax_jlt_setup_page', array( $this, 'setup_page' ) );

			if ( isset( $_GET[ 'page' ] ) == 'jlt-basic-setup' ) :
				add_action( 'admin_enqueue_scripts', array( $this, 'load_enqueue_script_setup' ) );
			endif;
		}
	}

	public static function instance() {
		if ( is_null( self::$_instance ) ) {
			self::$_instance = new self();
		}

		return self::$_instance;
	}

	public function setup_page() {
		global $wpdb;

		$page[ 'title' ]         = sanitize_title( $_POST[ 'title' ] );
		$page[ 'content' ]       = isset( $_POST[ 'content' ] ) && ! empty( $_POST[ 'content' ] ) ? sanitize_text_field( $_POST[ 'content' ] ) : '';
		$page[ 'page_template' ] = isset( $_POST[ 'page_template' ] ) && ! empty( $_POST[ 'page_template' ] ) ? sanitize_text_field( $_POST[ 'page_template' ] ) : 'default';
		$page[ 'setting_group' ] = isset( $_POST[ 'setting_group' ] ) && ! empty( $_POST[ 'setting_group' ] ) ? sanitize_text_field( $_POST[ 'setting_group' ] ) : '';
		$page[ 'setting_key' ]   = isset( $_POST[ 'setting_key' ] ) && ! empty( $_POST[ 'setting_key' ] ) ? sanitize_text_field( $_POST[ 'setting_key' ] ) : '';

		$post_data = array(
			'post_title'   => $page[ 'title' ],
			'post_content' => $page[ 'content' ],
			'post_status'  => 'publish',
			'post_type'    => 'page',
			'post_author'  => get_current_user_id(),
		);

		$id_page = wp_insert_post( $post_data ); // -- Insert post

		if ( ! is_wp_error( $id_page ) ) {
			update_post_meta( $id_page, '_wp_page_template', $page[ 'page_template' ] ); //- Set page template
			if ( ! empty( $page[ 'setting_key' ] ) ) {
				if ( ! empty( $page[ 'setting_group' ] ) ) {
					$setting_group                           = get_option( $page[ 'setting_group' ] );
					$setting_group[ $page[ 'setting_key' ] ] = $id_page;
					update_option( $page[ 'setting_group' ], $setting_group );
				} else {
					$setting_value = get_option( $page[ 'setting_key' ] );
					update_option( $page[ 'setting_key' ], $id_page );
				}
			}

			$post = get_post( $id_page );
			echo json_encode( array( 'id' => __( 'Done', 'job-listings' ), 'slug' => $post->post_name ) );
		}
		exit;
	}

	public function load_enqueue_script_setup() {

		if ( isset( $_GET[ 'page' ] ) && $_GET[ 'page' ] == 'jlt-basic-setup' ) :
			wp_register_script( 'setup-install', JLT_PLUGIN_URL . 'admin/js/setup.install.js', array(
				'jquery',
				'jquery-ui-tooltip',
			), null, true );
			wp_enqueue_script( 'setup-install' );

			wp_register_style( 'setup-style', JLT_PLUGIN_URL . 'admin/css/jlt-setup.css', array( 'jquery-ui' ) );
			wp_enqueue_style( 'setup-style' );

			wp_localize_script( 'setup-install', 'jltSetup', array( 'ajax_url' => admin_url( 'admin-ajax.php' ) ) );

		endif;
	}

	// -- Notice html

	public function notice_html_install() {
		if ( ! $this->jlt_get_option( 'disable_notice_install' ) && ! isset( $_GET[ 'page' ] ) == 'jlt-basic-setup' ) :

			?>
			<div id="message" class="updated notice is-dismissible">
				<p>
					<strong><?php echo sprintf( __( 'Welcome to %s,', 'job-listings' ), JLT_Notice_Install::product_name ); ?></strong>
				</p>
				<p><?php _e( 'If it is the first time you install this plugin, you should go and check the basic setting.', 'job-listings' ); ?></p>
				<p class="submit">
					<a href="<?php echo esc_url( admin_url( 'admin.php?page=jlt-basic-setup' ) ); ?>"
					   class="button-primary">
						<?php _e( 'Go to Quick Setup', 'job-listings' ); ?>
					</a>
					<a href="<?php echo esc_url( admin_url( 'admin.php?page=jlt-basic-setup&action=skip' ) ); ?>"
					   class="button">
						<?php _e( 'Skip Setup', 'job-listings' ); ?>
					</a>
				</p>
			</div>
			<?php
		endif;
	}

	// -- Add option
	public static function jlt_add_option( $name ) {
		$options = array_merge( get_option( 'jlt_setup', array() ), $name );
		update_option( 'jlt_setup', $options );
	}

	// -- Get option
	public function jlt_get_option( $name ) {
		$options = get_option( 'jlt_setup' );

		return $options[ $name ];
	}

	// -- Tab menu
	public static function jlt_tab_menu( $current = 'general' ) {

		$tabs = array(
			'general' => __( "Quick Setup", 'job-listings' ),
		);
		$html = '<h2 class="nav-tab-wrapper">';
		foreach ( $tabs as $tab => $name ) :

			$class = ( $tab == $current ) ? 'nav-tab-active' : '';
			$html .= '<a class="nav-tab ' . $class . '" href="?page=jlt-basic-setup&tab=' . $tab . '">' . $name . '</a>';

		endforeach;
		$html .= '</h2>';
		echo $html;
	}

	// -- General options
	public static function jlt_general_options() {
		if ( isset( $_GET[ 'action' ] ) == 'skip' ) :
			JLT_Notice_Install::jlt_add_option( array( 'disable_notice_install' => true ) );
			wp_redirect( admin_url() );
			die;
		endif;
		echo '<div class="wrap">';
		echo '<form method="post">';
		JLT_Notice_Install::jlt_option_page();

		echo '<p>';
		submit_button( '', 'primary', '', false );
		echo '&nbsp;&nbsp;';
		echo '<a href="' . esc_url( admin_url( 'admin.php?page=jlt-basic-setup&action=skip' ) ) . '" class="button">';
		_e( 'Cancel', 'job-listings' );
		echo '</a>';
		echo '</p>';

		echo '</form>';
		echo '</div>';
	}

	// -- Option page
	public static function jlt_option_page() {

		?>
		<table class="widefat jlt-setup-page-table" cellspacing="0">
			<thead>
			<tr>
				<th colspan="5"
				    data-export-label="<?php _e( 'Required Pages', 'job-listings' ); ?>">
					<strong><?php _e( 'Required Pages', 'job-listings' ); ?></strong></th>
			</tr>
			</thead>
			<tbody>
			<?php
			global $wpdb;

			$list_pages = array(
				array(
					'title'         => __( 'Member', 'job-listings' ),
					'content'       => '[jlt_member',
					'shortcode'     => '[jlt_member]',
					'page_template' => '',
					'help'          => __( 'The main page for all the action of Employer and Candidate', 'job-listings' ),
					'setting'       => array(
						'group' => 'jlt_member',
						'key'   => 'manage_page_id',
						'url'   => jlt_admin_setting_page_url( 'member' ),
					),
				),
				array(
					'title'         => __( 'Post Job', 'job-listings' ),
					'content'       => '[job_submit_form]',
					'shortcode'     => '[job_submit_form]',
					'page_template' => '',
					'help'          => __( 'The page for Job posting', 'job-listings' ),
					'setting'       => array(
						'group' => 'jlt_job_general',
						'key'   => 'job_post_page',
						'url'   => jlt_admin_setting_page_url( 'general' ),
					),
				),

			);

			$list_pages = apply_filters( 'jlt_setup_page', $list_pages );

			foreach ( $list_pages as $list_page => $page ) :
				echo '<tr>';

				$get_id   = null;
				$get_page = null;

				// Check the setting first
				$setting        = $page[ 'setting' ];
				$setting_value  = null;
				$setting_result = '';
				if ( isset( $setting ) && ! empty( $setting ) && is_array( $setting ) && ! empty( $setting[ 'key' ] ) ) :
					if ( ! empty( $setting[ 'group' ] ) ) {
						$setting_group = get_option( $setting[ 'group' ] );
						$setting_value = is_array( $setting_group ) && isset( $setting_group[ $setting[ 'key' ] ] ) ? $setting_group[ $setting[ 'key' ] ] : null;
					} else {
						$setting_value = get_option( $setting[ 'key' ] );
					}

					if ( $setting_value == null ) :
						$setting_result = 'missing';
					else :
						$setting_result = 'false';
						if ( isset( $setting[ 'value' ] ) && $setting[ 'value' ] == $setting_value ) {
							$setting_result = 'true';
						} elseif ( is_numeric( $setting_value ) ) {
							$get_id   = absint( $setting_value );
							$get_page = get_post( $get_id );
							if ( ! empty( $get_page ) && $get_page->post_type == 'page' ) {

								// There's setting, check if the setting satisfy the other condition: page template and page content.
								if ( isset( $page[ 'page_template' ] ) && ! empty( $page[ 'page_template' ] ) ) {
									$setting_result = $page[ 'page_template' ] == jlt_get_post_meta( $get_id, '_wp_page_template' ) ? 'true' : 'wrong_template';
								} elseif ( isset( $page[ 'content' ] ) && ! empty( $page[ 'content' ] ) ) {
									$setting_result = ( strpos( $get_page->post_content, $page[ 'content' ] ) !== false ) ? 'true' : 'missing_content';
								}
							}
						}
					endif;

					if ( ! empty( $get_id ) ) {
						echo "<td data-export-label='{$page['title']}'>";
						echo "<a href='" . get_edit_post_link( $get_id ) . "' title='{$page['title']}' target='_blank'>{$page['title']}</a>";
						echo "</td>";
					} else {
						echo "<td data-export-label='{$page['title']}'>{$page['title']}</td>";
					}
					if ( isset( $page[ 'help' ] ) && ! empty( $page[ 'help' ] ) ) {
						echo '<td class="help">';
						echo '	<a href="#" class="help_tip" title="' . $page[ 'help' ] . '"><span class="dashicons dashicons-editor-help"></span></a>';
						echo '</td>';
					}

					echo '<td>';
					if ( $setting_result == 'missing' ) {
						echo "	<span class='error'>" . __( 'Missing setting', 'job-listings' ) . "</span>";
					} elseif ( $setting_result == 'missing_content' ) {
						echo "<span class='error'>" . __( 'Wrong', 'job-listings' );
						if ( ! empty( $page[ 'shortcode' ] ) ) {
							echo ' - ' . sprintf( __( 'Page should contains %s', 'job-listings' ), $page[ 'shortcode' ] );
						} else {
							echo ' - ' . sprintf( __( 'Page should contains %s', 'job-listings' ), $page[ 'content' ] );
						}
						echo "</span>";
					} elseif ( $setting_result == 'wrong_template' ) {
						echo "<span class='error'>" . __( 'Wrong', 'job-listings' );
						echo ' - ' . sprintf( __( 'Page should have template %s', 'job-listings' ), $page[ 'page_template' ] );
						echo "</span>";
					} elseif ( $setting_result == 'false' ) {
						echo "<span class='error'>" . __( 'Wrong Page', 'job-listings' );
						echo "</span>";
					} elseif ( $setting_result == 'true' ) {
						echo "<span class='yes'>" . __( 'Done', 'job-listings' );
						if ( ! empty( $get_id ) && ! empty( $get_page ) ) {
							echo " - /{$get_page->post_name}/";
						}
						echo '</span>';
					}
					echo '</td>';

					echo '<td>';
					if ( $setting_result !== 'true' && ( ! empty( $page[ 'content' ] ) || ! empty( $page[ 'page_template' ] ) ) ) {
						echo '	<div class="button button-primary">';
						echo '		<span class="correct-setting" data-title="' . $page[ 'title' ] . '" data-content="' . $page[ 'content' ] . '" data-page-template="' . $page[ 'page_template' ] . '" data-setting-group="' . $setting[ 'group' ] . '" data-setting-key="' . $setting[ 'key' ] . '">' . __( 'Correct now', 'job-listings' ) . '</span>';
						echo '	</div>';
					}
					echo '</td>';
					echo '<td>';
					if ( $setting_result != 'true' && isset( $setting[ 'url' ] ) && ! empty( $setting[ 'url' ] ) ) {
						echo '	<div class="button">';
						echo "		<a href='{$setting['url']}' title='{$page['title']}' target='_blank'>" . __( "Edit setting", "jlt" ) . "</a>";
						echo '	</div>';
					}
					echo '</td>';

				else :
					if ( ! empty( $page[ 'page_template' ] ) ) :
						$get_id   = jlt_get_page_id_by_template( $page[ 'page_template' ] );
						$get_page = ! empty( $get_id ) ? get_post( $get_id ) : null;

						if ( ! empty( $get_id ) && ! empty( $get_page ) ) {

							echo "<td data-export-label='{$page['title']}'>";
							echo "<a href='" . get_edit_post_link( $get_id ) . "' title='{$page['title']}' target='_blank'>{$page['title']}</a>";
							echo "</td>";
							if ( isset( $page[ 'help' ] ) && ! empty( $page[ 'help' ] ) ) {
								echo '<td class="help">';
								echo '	<a href="#" class="help_tip" title="' . $page[ 'help' ] . '"><span class="dashicons dashicons-editor-help"></span></a>';
								echo '</td>';
							}
							echo '<td>';
							echo "	<span class='yes'>" . __( 'Done', 'job-listings' ) . " - /{$get_page->post_name}/</span>";
							echo '</td>';
						} else {
							echo "<td data-export-label='{$page['title']}'>{$page['title']}</td>";
							if ( isset( $page[ 'help' ] ) && ! empty( $page[ 'help' ] ) ) {
								echo '<td class="help">';
								echo '	<a href="#" class="help_tip" title="' . $page[ 'help' ] . '"><span class="dashicons dashicons-editor-help"></span></a>';
								echo '</td>';
							}
							echo '<td>';
							echo "	<span class='error'>" . sprintf( __( 'You need to create a page with template %s', 'job-listings' ), $page[ 'page_template' ] ) . "</span>";
							echo '</td>';
							echo '<td>';
							echo '	<div class="button button-primary">';
							echo '		<span class="correct-setting" data-title="' . $page[ 'title' ] . '" data-content="' . $page[ 'content' ] . '" data-page-template="' . $page[ 'page_template' ] . '">' . __( 'Correct now', 'job-listings' ) . '</span>';
							echo '	</div>';
							echo '</td>';
							echo '<td></td>';
						}
					else :
						echo '<td colspan="4"></td>';
					endif;
				endif;

				echo '</tr>';
			endforeach;

			?>
			</tbody>
		</table>
		<?php
	}

	public function jlt_settings_fields() {

		register_setting( $this->install_option_group, $this->install_option_name );

		add_settings_section( $this->install_section_id, 'Title Section', '', $this->install_option_group );
		add_settings_field( self::product_id . '-page', 'Page', array(
			$this,
			'render_settings_section',
		), $this->install_option_group, $this->install_section_id );
	}

	/**
	 * Renders the description for the settings section.
	 */
	public function render_settings_section() {
	}

	// -- Options fields
	public function jlt_options_fields() {

		$this->option_metabox[] = array(

			'id'    => 'general_options',
			'title' => _e( 'General Options', 'job-listings' ),

		);

		return $this->option_metabox;
	}

}

JLT_Notice_Install::instance();