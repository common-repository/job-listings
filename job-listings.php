<?php

/**
 * The plugin bootstrap file
 *
 * This file is read by WordPress to generate the plugin information in the plugin
 * admin area. This file also includes all of the dependencies used by the plugin,
 * registers the activation and deactivation functions, and defines a function
 * that starts the plugin.
 *
 * @link              https://nootheme.com
 * @since             0.1.0
 * @package           Job_Listings
 *
 * @wordpress-plugin
 * Plugin Name:       Job Listings
 * Plugin URI:        https://nootheme.com
 * Description:       This is a short description of what the plugin does. It's displayed in the WordPress admin area.
 * Version:           0.1.1
 * Author:            NooTheme
 * Author URI:        https://nootheme.com/
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       jlt
 * Domain Path:       /languages
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

define( 'JLT_PLUGIN_URL', plugins_url( '/', __FILE__ ) );
define( 'JLT_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'JLT_PLUGIN_BASENAME', plugin_basename( __FILE__ ) );
define( 'JLT_VERSION', '0.1.0' );
/**
 * The code that runs during plugin activation.
 * This action is documented in includes/class-jlt-activator.php
 */
function activate_job_listings() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-jlt-activator.php';
	Job_Listings_Activator::activate();
}

/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/class-jlt-deactivator.php
 */
function deactivate_job_listings() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-jlt-deactivator.php';
	Job_Listings_Deactivator::deactivate();
}

register_activation_hook( __FILE__, 'activate_job_listings' );
register_deactivation_hook( __FILE__, 'deactivate_job_listings' );

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require plugin_dir_path( __FILE__ ) . 'includes/class-jlt.php';

/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    0.1.0
 */
function run_job_listings() {

	job_listings()->run();
	do_action( 'job_listings_loaded' );
}

add_action( 'plugins_loaded', 'run_job_listings', 100 );