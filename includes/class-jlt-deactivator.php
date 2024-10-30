<?php

/**
 * Fired during plugin deactivation
 *
 * @link       http://nootheme.com
 * @since      0.1.0
 *
 * @package    Job_Listings
 * @subpackage Job_Listings/includes
 */

/**
 * Fired during plugin deactivation.
 *
 * This class defines all code necessary to run during the plugin's deactivation.
 *
 * @since      0.1.0
 * @package    Job_Listings
 * @subpackage Job_Listings/includes
 * @author     NooTheme <thinhnv@vietbrain.com>
 */
class Job_Listings_Deactivator {

	/**
	 * Short Description. (use period)
	 *
	 * Long Description.
	 *
	 * @since    0.1.0
	 */
	public static function deactivate() {
		self::remove_role();
	}

	public static function remove_role() {
		remove_role( 'employer' );
		remove_role( 'candidate' );
	}

}
