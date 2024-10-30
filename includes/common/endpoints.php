<?php

function jlt_endpoints_admin_init() {
	register_setting( 'jlt_endpoints', 'jlt_endpoints' );
}

add_filter( 'admin_init', 'jlt_endpoints_admin_init' );

function jlt_endpoints_settings_tabs( $tabs = array() ) {
	$tabs[ 'jlt_endpoints' ] = __( 'Endpoints', 'job-listings' );

	return $tabs;
}

add_filter( 'jlt_admin_settings_tabs_array', 'jlt_endpoints_settings_tabs', 99 );
function jlt_get_endpoints_setting( $id = null, $default = null ) {
	$endpoint = jlt_get_setting( 'jlt_endpoints', $id, $default );
	if ( empty( $endpoint ) ) {
		return $default;
	}

	return $endpoint;
}

function jlt_endpoints_settings_form() {
	if ( isset( $_GET[ 'settings-updated' ] ) && $_GET[ 'settings-updated' ] ) {
		flush_rewrite_rules();
	}

	$endpoints_employer  = jlt_list_endpoints_employer();
	$endpoints_candidate = jlt_list_endpoints_candidate();

	?>
	<?php settings_fields( 'jlt_endpoints' ); ?>
	<h3><?php echo __( 'Employer Endpoints Options', 'job-listings' ) ?></h3>
	<p><?php _e( 'Endpoints are appended to your page URLs to handle specific actions on the member pages. They should be unique and can be left blank to disable the endpoint.', 'job-listings' ) ?></p>
	<table class="form-table" cellspacing="0">
		<tbody>
		<?php
		foreach ( $endpoints_employer as $endpoint ):
			?>
			<tr>
				<th>
					<?php echo $endpoint[ 'text' ] ?>
				</th>
				<td>
					<?php
					$value = jlt_get_endpoints_setting( $endpoint[ 'key' ], $endpoint[ 'value' ] );
					?>
					<input type="text" name="jlt_endpoints[<?php echo $endpoint[ 'key' ]; ?>]"
					       value="<?php echo $value; ?>">
				</td>
			</tr>
			<?php
		endforeach;
		?>
		<?php do_action( 'jlt_endpoints_settings_form' ); ?>
		</tbody>
	</table>
	<h3><?php echo __( 'Candidate Endpoints Options', 'job-listings' ) ?></h3>
	<table class="form-table" cellspacing="0">
		<tbody>
		<?php
		foreach ( $endpoints_candidate as $endpoint ):
			?>
			<tr>
				<th>
					<?php echo $endpoint[ 'text' ] ?>
				</th>
				<td>
					<?php
					$value = jlt_get_endpoints_setting( $endpoint[ 'key' ], $endpoint[ 'key' ] );
					?>
					<p>
						<input type="text" name="jlt_endpoints[<?php echo $endpoint[ 'key' ]; ?>]"
						       value="<?php echo $value; ?>">
					</p>
				</td>
			</tr>
			<?php
		endforeach;
		?>
		<?php do_action( 'jlt_endpoints_settings_form' ); ?>
		</tbody>
	</table>
	<?php
}

add_action( 'jlt_admin_setting_jlt_endpoints', 'jlt_endpoints_settings_form' );