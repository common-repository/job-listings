<?php

/**
 * Define the internationalization functionality
 *
 * Loads and defines the internationalization files for this plugin
 * so that it is ready for translation.
 *
 * @link       http://nootheme.com
 * @since      0.1.0
 *
 * @package    Job_Listings
 * @subpackage Job_Listings/includes
 */

/**
 * Define the internationalization functionality.
 *
 * Loads and defines the internationalization files for this plugin
 * so that it is ready for translation.
 *
 * @since      0.1.0
 * @package    Job_Listings
 * @subpackage Job_Listings/includes
 * @author     NooTheme <thinhnv@vietbrain.com>
 */
class Job_Listings_i18n {

	/**
	 * Load the plugin text domain for translation.
	 *
	 * @since    0.1.0
	 */
	public function load_plugin_textdomain() {

		$locale = apply_filters( 'plugin_locale', get_locale(), 'job-listings' );

		load_textdomain( 'job-listings', WP_LANG_DIR . "/job-listings/job-listings-$locale.mo" );

		load_plugin_textdomain( 'job-listings', false, dirname( dirname( plugin_basename( __FILE__ ) ) ) . '/languages/' );
	}

}
