<?php

/**
 * The public-facing functionality of the plugin.
 *
 * @link       http://nootheme.com
 * @since      0.1.0
 *
 * @package    Job_Listings
 * @subpackage Job_Listings/public
 */

/**
 * The public-facing functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    Job_Listings
 * @subpackage Job_Listings/public
 * @author     NooTheme <thinhnv@vietbrain.com>
 */
class Job_Listings_Public {

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
	 * @param      string $plugin_name The name of the plugin.
	 * @param      string $version     The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {

		$this->plugin_name = $plugin_name;
		$this->version     = $version;
	}

	/**
	 * Register the stylesheets for the public-facing side of the site.
	 *
	 * @since    0.1.0
	 */
	public function enqueue_styles() {

		wp_register_style( 'vendor-chosen', plugin_dir_url( __FILE__ ) . 'vendor/chosen/chosen.min.css', array(), '1.6.2', 'all' );
		wp_register_style( 'vendor-magnific-popup', plugin_dir_url( __FILE__ ) . 'vendor/magnific-popup/magnific-popup.css', array(), '1.1.0', 'all' );
		wp_register_style( 'jlt-font-awesome', plugin_dir_url( __FILE__ ) . 'css/jlt-font-awesome.min.css', array(), '4.7.0', 'all' );
		wp_register_style( 'jlt-chosen-fronted', plugin_dir_url( __FILE__ ) . 'css/jlt-chosen.css', array(), '1.0', 'all' );

		wp_register_style( 'vendor-datetimepicker', plugin_dir_url( __FILE__ ) . 'vendor/datetimepicker/jquery.datetimepicker.css', array(), '2.5.4', 'all' );

		wp_register_style( 'vendor-nivo-lightbox-css', plugin_dir_url( __FILE__ ) . 'vendor/nivo-lightbox/nivo-lightbox.css', array(), null );
		wp_register_style( 'vendor-nivo-lightbox-default-css', plugin_dir_url( __FILE__ ) . 'vendor/nivo-lightbox/themes/default/default.css', array( 'vendor-nivo-lightbox-css' ), null );

		wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/job-listings.css', array(), $this->version, 'all' );

		wp_enqueue_style( 'vendor-chosen' );
		wp_enqueue_style( 'vendor-magnific-popup' );
		wp_enqueue_style( 'jlt-font-awesome' );
		wp_enqueue_style( 'jlt-chosen-fronted' );
		wp_enqueue_style( 'vendor-datetimepicker' );
	}

	/**
	 * Register the JavaScript for the public-facing side of the site.
	 *
	 * @since    0.1.0
	 */
	public function enqueue_scripts() {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Job_Listings_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Job_Listings_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		wp_register_script( 'vendor-chosen', plugin_dir_url( __FILE__ ) . 'vendor/chosen/chosen.jquery.min.js', array( 'jquery' ), '1.6.2', false );
		wp_register_script( 'vendor-jquery-validate', plugin_dir_url( __FILE__ ) . 'vendor/jquery-validation/jquery.validate.min.js', array( 'jquery' ), '1.15.0', true );
		wp_register_script( 'vendor-magnific-popup', plugin_dir_url( __FILE__ ) . 'vendor/magnific-popup/jquery.magnific-popup.min.js', array( 'jquery' ), '1.1.0', true );

		wp_register_script( 'vendor-datetimepicker', plugin_dir_url( __FILE__ ) . 'vendor/datetimepicker/jquery.datetimepicker.min.js', array( 'jquery' ), '2.5.4', true );

		$google_api = jlt_get_location_setting( 'google_api', '' );
		wp_register_script( 'google-map', 'http' . ( is_ssl() ? 's' : '' ) . '://maps.googleapis.com/maps/api/js?v=3.exp&language=' . get_locale() . '&libraries=places' . ( ! empty( $google_api ) ? '&key=' . $google_api : '' ), array( 'jquery' ), null, true );

		wp_register_script( 'google-map-custom', plugin_dir_url( __FILE__ ) . 'js/google-map-custom.js', array(
			'jquery',
			'google-map',
		), null, false );

		wp_enqueue_script( 'google-map' );

		wp_enqueue_script( 'vendor-chosen' );
		wp_enqueue_script( 'vendor-jquery-validate' );
		wp_enqueue_script( 'vendor-magnific-popup' );
		wp_enqueue_script( 'vendor-datetimepicker' );

		wp_register_script( 'vendor-nivo-lightbox-js', plugin_dir_url( __FILE__ ) . 'vendor/nivo-lightbox/nivo-lightbox.min.js', array( 'jquery' ), null, true );
		wp_enqueue_script( 'vendor-blockUI', plugin_dir_url( __FILE__ ) . 'vendor/jquery.blockUI.js', array( 'jquery' ), null, true );

		wp_enqueue_script( 'vendor-form-validator', plugin_dir_url( __FILE__ ) . 'vendor/form-validator/jquery.form-validator.min.js', array( 'jquery' ), '2.3.26', true );

		// Ajax
		$enable_ajax_filter_job = jlt_get_job_setting( 'enable_ajax_filter_job', true );
		$jobboard_ajax          = array(
			'ajaxurl'                => admin_url( 'admin-ajax.php' ),
			'enable_ajax_filter_job' => $enable_ajax_filter_job,
		);
		wp_register_script( $this->plugin_name . '-ajax', plugin_dir_url( __FILE__ ) . 'js/jlt-ajax.js', array( 'jquery' ), $this->version, true );
		wp_localize_script( $this->plugin_name . '-ajax', 'JLT_Ajax', $jobboard_ajax );
		wp_enqueue_script( $this->plugin_name . '-ajax' );

		$jobboard_i18n = array(
			'date_format' => get_option( 'date_format' ),
		);
		wp_register_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/job-listings.js', array( 'jquery' ), $this->version, true );
		wp_localize_script( $this->plugin_name, 'Jobboard_i18n', $jobboard_i18n );
		wp_enqueue_script( $this->plugin_name );
	}

}
