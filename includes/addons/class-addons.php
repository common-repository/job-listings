<?php

class JLT_Addons {

	public function __construct() {
		add_action( 'wp_ajax_jlt_install_plugin', 'JLT_Addons::install_plugin' );
		add_action( 'wp_ajax_nopriv_jlt_install_plugin', 'JLT_Addons::install_plugin' );

		add_action( 'wp_ajax_jlt_activate_plugin', 'JLT_Addons::activate_plugin' );
		add_action( 'wp_ajax_nopriv_jlt_activate_plugin', 'JLT_Addons::activate_plugin' );

		add_action( 'wp_ajax_jlt_deactivate_plugin', 'JLT_Addons::deactivate_plugin' );
		add_action( 'wp_ajax_nopriv_jlt_deactivate_plugin', 'JLT_Addons::deactivate_plugin' );

		add_action( 'admin_init', array( $this, 'force_load_addons' ) );
	}

	public static function display() {
		require 'addon-display.php';
	}

	/**
	 * Fecth addons from server.
	 *
	 * @since 0.1.0
	 *
	 * @return bool
	 */
	public static function get_addons() {

		if ( false === ( $addons = get_transient( 'jlt_fecth_addons' ) ) ) {

			global $wp_version;

			$remote_url = 'http://update.nootheme.com/';

			$response = wp_remote_post( $remote_url, array(
				'user-agent' => 'WordPress/' . $wp_version . '; ' . get_bloginfo( 'url' ),
				'body'       => array(
					'item'    => urlencode( 'job-listings' ),
					'version' => urlencode( JLT_VERSION ),
				),
			) );

			$response_code    = wp_remote_retrieve_response_code( $response );
			$response_content = wp_remote_retrieve_body( $response );

			if ( $response_code != 200 || is_wp_error( $response_content ) ) {
				return array();
			} else {
				$response_content = json_decode( $response_content );

				if ( isset( $response_content->addons ) ) {
					$addons = $response_content->addons;
					set_transient( 'jlt_fecth_addons', $addons, 2 * HOUR_IN_SECONDS );
				}
			}
		}

		return $addons;
	}

	/**
	 * force_load_addons
	 *
	 * @since 0.1.0
	 *
	 */

	public function force_load_addons() {
		if ( isset( $_GET[ 'force_load' ] ) && true == $_GET[ 'force_load' ] ) {
			delete_transient( 'jlt_fecth_addons' );
		}
	}

	/**
	 * Install Add-On/Plugin
	 *
	 * @since    0.1
	 */
	public static function install_plugin() {

		check_ajax_referer( 'jlt-addons-security', 'security', esc_html__( 'Security Breach! Please contact admin!', 'job-listings' ) );

		if ( isset( $_REQUEST[ 'plugin' ] ) ) {
			global $wp_version;
			$plugin_slug = basename( $_REQUEST[ 'plugin' ] );

			$addons = (array) self::get_addons();

			if ( isset( $addons[ $plugin_slug ] ) && ! empty( $addons[ $plugin_slug ] ) ) {

				$remote_post = wp_remote_post( $addons[ $plugin_slug ]->download, array(
					'user-agent' => 'WordPress/' . $wp_version . '; ' . get_bloginfo( 'url' ),
					'body'       => '',
					'timeout'    => 45,
				) );

				if ( $remote_post && '200' == $remote_post[ 'response' ][ 'code' ] ) {
					define( 'FS_METHOD', 'direct' );
					global $wp_filesystem;

					WP_Filesystem();

					$upload_dir = wp_upload_dir();
					$file       = $upload_dir[ 'basedir' ] . '/' . $plugin_slug . '.zip';
					$ret        = @file_put_contents( $file, $remote_post[ 'body' ] );

					$d_path    = WP_PLUGIN_DIR;
					$unzipfile = unzip_file( $file, $d_path );

					if ( is_wp_error( $unzipfile ) ) {
						die( '-1' );
					}
					@unlink( $file );
					die( '1' );
				}
			}
			die( '-1' );
		}
		die( '0' );
	}

	/**
	 * Activates Installed Add-On/Plugin
	 *
	 * @since    0.1
	 */
	public static function activate_plugin() {
		check_ajax_referer( 'jlt-addons-security', 'security', esc_html__( 'Security Breach! Please contact admin!', 'job-listings' ) );

		if ( isset( $_REQUEST[ 'plugin' ] ) ) {

			$result = activate_plugin( $_REQUEST[ 'plugin' ] );

			if ( is_wp_error( $result ) ) {
				die( '0' );
			}
			die( '1' );
		}

		die( '0' );
	}

	/**
	 * Deactivates Installed Add-On/Plugin
	 *
	 * @since    0.1
	 */
	public static function deactivate_plugin() {

		check_ajax_referer( 'jlt-addons-security', 'security', esc_html__( 'Security Breach! Please contact admin!', 'job-listings' ) );

		if ( isset( $_REQUEST[ 'plugin' ] ) ) {
			$result = deactivate_plugins( $_REQUEST[ 'plugin' ] );
			if ( is_wp_error( $result ) ) {
				die( '0' );
			}
			die( '1' );
		}
		die( '0' );
	}

}

new JLT_Addons();