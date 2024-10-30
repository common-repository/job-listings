<?php

function jlt_admin_setting_page_url( $tab = '' ) {
	return jlt_dashboard_page_url( 'jlt-setting', $tab );
}

function jlt_admin_setting_menu() {
	add_menu_page( job_listings()->get_plugin_real_name(), job_listings()->get_plugin_real_name(), 'manage_job', job_listings()->get_plugin_name(), null, 'dashicons-menu', 28 );
	add_submenu_page( job_listings()->get_plugin_name(), __( 'Settings', 'job-listings' ), __( 'Settings', 'job-listings' ), 'manage_options', 'jlt-setting', 'jlt_admin_setting_page' );
	add_submenu_page( job_listings()->get_plugin_name(), __( 'Custom Fields', 'job-listings' ), __( 'Custom Fields', 'job-listings' ), 'manage_options', 'jlt-custom-field-setting', 'jlt_custom_field_setting_page' );
	add_submenu_page( job_listings()->get_plugin_name(), __( 'Quick Setup', 'job-listings' ), __( 'Quick Setup', 'job-listings' ), 'manage_options', 'jlt-basic-setup', 'jlt_basic_setup_content' );
	add_submenu_page( job_listings()->get_plugin_name(), __( 'Addons', 'job-listings' ), __( 'Addons', 'job-listings' ), 'manage_options', 'job-listings-addons', 'JLT_Addons::display' );
}

add_action( 'admin_menu', 'jlt_admin_setting_menu', 99 );

function jlt_admin_setting_page() {
	$tabs        = apply_filters( 'jlt_admin_settings_tabs_array', array() );
	$tab_keys    = array_keys( $tabs );
	$current_tab = empty( $_GET[ 'tab' ] ) ? reset( $tab_keys ) : sanitize_title( $_GET[ 'tab' ] );
	?>
	<div class="wrap">
		<form action="options.php" method="post">
			<h2 class="nav-tab-wrapper">
				<?php
				foreach ( $tabs as $name => $label ) {
					echo '<a href="' . jlt_admin_setting_page_url( $name ) . '" class="nav-tab ' . ( $current_tab == $name ? 'nav-tab-active' : '' ) . '">' . $label . '</a>';
				}
				?>
			</h2>
			<?php if ( isset( $_GET[ 'settings-updated' ] ) && $_GET[ 'settings-updated' ] ) : ?>
				<div id="message" class="updated inline">
					<p><strong><?php _e( 'Your settings have been saved.', 'job-listings' ); ?></strong></p>
				</div>
			<?php endif; ?>
			<?php
			do_action( 'jlt_admin_setting_' . $current_tab );
			submit_button( __( 'Save Changes', 'job-listings' ) );
			?>
		</form>
	</div>
	<?php
}

function jlt_custom_field_setting_page() {
	$tabs = apply_filters( 'jlt_custom_field_setting_tabs', array() );

	$tab_keys    = array_keys( $tabs );
	$current_tab = empty( $_GET[ 'tab' ] ) ? reset( $tab_keys ) : sanitize_title( $_GET[ 'tab' ] );
	?>
	<div class="wrap">
		<form action="options.php" method="post">
			<h2 class="nav-tab-wrapper">
				<?php
				foreach ( $tabs as $name => $label ) {
					echo '<a href="' . jlt_custom_field_setting_page_url( $name ) . '" class="nav-tab ' . ( $current_tab == $name ? 'nav-tab-active' : '' ) . '">' . $label . '</a>';
				}
				?>
			</h2>
			<?php if ( isset( $_GET[ 'settings-updated' ] ) && $_GET[ 'settings-updated' ] ) : ?>
				<div id="message" class="updated inline">
					<p><strong><?php _e( 'Your settings have been saved.', 'job-listings' ); ?></strong></p>
				</div>
			<?php endif; ?>
			<?php
			do_action( 'jlt_custom_field_setting_' . $current_tab );
			submit_button( __( 'Save Changes', 'job-listings' ) );
			?>
		</form>
	</div>
	<?php
}

function jlt_basic_setup_content() {

	JLT_Notice_Install::jlt_tab_menu( 'general' );
	JLT_Notice_Install::jlt_general_options();
}

function jlt_dashboard_page_url( $page = '', $tab = '' ) {
	$args = array(
		'page' => $page,
		'tab'  => $tab,
	);

	return esc_url( add_query_arg( $args, admin_url( 'admin.php' ) ) );
}

function jlt_custom_field_setting_page_url( $tab = '' ) {
	return jlt_dashboard_page_url( 'jlt-custom-field-setting', $tab );
}

function jlt_addons_page() {
	?>
	<div class="wrap jlt_addons_wrap">
		<h1><?php _e( 'Job Listings Addons' ); ?></h1>

		<?php do_action( 'jlt_addons_page' ); ?>
	</div>
	<?php
}
