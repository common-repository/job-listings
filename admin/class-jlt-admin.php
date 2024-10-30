<?php

/**
 * The admin-specific functionality of the plugin.
 *
 * @link       http://nootheme.com
 * @since      0.1.0
 *
 * @package    Job_Listings
 * @subpackage Job_Listings/admin
 */

/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    Job_Listings
 * @subpackage Job_Listings/admin
 * @author     NooTheme <thinhnv@vietbrain.com>
 */
class Job_Listings_Admin {

	/**
	 * The ID of this plugin.
	 *
	 * @since    0.1.0
	 * @access   private
	 * @var      string $plugin_name The ID of this plugin.
	 */
	private $plugin_name;

	/**
	 * The version of this plugin.
	 *
	 * @since    0.1.0
	 * @access   private
	 * @var      string $version The current version of this plugin.
	 */
	private $version;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    0.1.0
	 *
	 * @param      string $plugin_name The name of this plugin.
	 * @param      string $version     The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {

		$this->plugin_name = $plugin_name;
		$this->version     = $version;
	}

	/**
	 * Register the stylesheets for the admin area.
	 *
	 * @since    0.1.0
	 */
	public function enqueue_styles() {

		wp_register_style( 'jquery-ui-slider', JLT_PLUGIN_URL . 'admin/css/jlt-jquery-ui.slider.css', null, '1.10.4', 'all' );
		wp_register_style( 'vendor-chosen-css', JLT_PLUGIN_URL . 'admin/css/jlt-chosen.css', null, null, 'all' );

		wp_register_style( 'vendor-datetimepicker', JLT_PLUGIN_URL . 'admin/css/jquery.datetimepicker.css', '2.4.5' );

		wp_register_style( 'addons', JLT_PLUGIN_URL . 'admin/css/addon.css', $this->version );

		wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/job-listings-admin.css', array( 'vendor-chosen-css' ), $this->version, 'all' );
	}

	/**
	 * Register the JavaScript for the admin area.
	 *
	 * @since    0.1.0
	 */
	public function enqueue_scripts() {

		wp_register_script( 'vendor-datetimepicker', JLT_PLUGIN_URL . 'admin/js/jquery.datetimepicker.js', array( 'jquery' ), '2.4.5', true );
		wp_register_script( 'vendor-chosen-js', JLT_PLUGIN_URL . 'admin/js/chosen.jquery.min.js', array( 'jquery' ), null, true );
		wp_register_script( 'addons', JLT_PLUGIN_URL . 'admin/js/addon.js', array( 'jquery' ), null, true );

		$datetimeL10n = array(
			'lang' => substr( get_bloginfo( 'language' ), 0, 2 ),
			'rtl'  => is_rtl(),

			'January'   => ucfirst( __( 'January' ) ),
			'February'  => ucfirst( __( 'February' ) ),
			'March'     => ucfirst( __( 'March' ) ),
			'April'     => ucfirst( __( 'April' ) ),
			'May'       => ucfirst( __( 'May' ) ),
			'June'      => ucfirst( __( 'June' ) ),
			'July'      => ucfirst( __( 'July' ) ),
			'August'    => ucfirst( __( 'August' ) ),
			'September' => ucfirst( __( 'September' ) ),
			'October'   => ucfirst( __( 'October' ) ),
			'November'  => ucfirst( __( 'November' ) ),
			'December'  => ucfirst( __( 'December' ) ),

			'Sunday'    => ucfirst( __( 'Sunday' ) ),
			'Monday'    => ucfirst( __( 'Monday' ) ),
			'Tuesday'   => ucfirst( __( 'Tuesday' ) ),
			'Wednesday' => ucfirst( __( 'Wednesday' ) ),
			'Thursday'  => ucfirst( __( 'Thursday' ) ),
			'Friday'    => ucfirst( __( 'Friday' ) ),
			'Saturday'  => ucfirst( __( 'Saturday' ) ),
		);
		wp_localize_script( 'vendor-datetimepicker', 'datetime', $datetimeL10n );

		// Main script
		wp_register_script( $this->plugin_name, JLT_PLUGIN_URL . 'admin/js/job-listings-admin.js', array(
			'jquery',
			'jquery-ui-slider',
			'vendor-chosen-js',
		), null, true );

		$jlt_admin = array(
			'title_wpmedia'            => __( 'Select Image', 'job-listings' ),
			'button_wpmedia'           => __( 'Insert image', 'job-listings' ),
			'ajax_url'                 => admin_url( 'admin-ajax.php', 'relative' ),
			'ajax_jlt_addons_security' => wp_create_nonce( 'jlt-addons-security' ),
		);

		wp_localize_script( $this->plugin_name, 'JLT_Admin_JS', $jlt_admin );
		wp_enqueue_script( $this->plugin_name );
	}

}
