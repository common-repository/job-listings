<?php

function jlt_get_location_setting( $id = null, $default = null ) {
	return jlt_get_setting( 'jlt_location', $id, $default );
}

function jlt_location_admin_init() {
	register_setting( 'jlt_location', 'jlt_location' );
	add_action( 'jlt_admin_setting_location', 'jlt_location_settings_form' );
}

add_filter( 'admin_init', 'jlt_location_admin_init' );

function jlt_location_settings_tabs( $tabs = array() ) {
	$location_tab = array( 'location' => __( 'Location', 'job-listings' ) );

	return array_merge( $tabs, $location_tab );
}

add_filter( 'jlt_admin_settings_tabs_array', 'jlt_location_settings_tabs', 11 );
function jlt_location_settings_form() {
	wp_enqueue_style( 'vendor-chosen-css' );
	wp_enqueue_script( 'vendor-chosen-js' );
	?>
	<?php settings_fields( 'jlt_location' ); ?>
	<?php
	// setting value
	$location_mode        = jlt_get_location_setting( 'location_mode', 'taxonomy' );
	$allow_user_input     = jlt_get_location_setting( 'allow_user_input', 1 );
	$enable_auto_complete = jlt_get_location_setting( 'enable_auto_complete', 1 );
	$country_restriction  = jlt_get_location_setting( 'country_restriction', '' );

	$map_style     = jlt_get_location_setting( 'google_map_style', 'apple' );
	$map_height    = jlt_get_location_setting( 'google_map_height', '400' );
	$location_type = jlt_get_location_setting( 'location_type', '(regions)' );
	?>
	<h3><?php echo __( 'Google Map Location Settings', 'job-listings' ) ?></h3>
	<table class="form-table" cellspacing="0">
		<tbody>
		<tr>
			<th>
				<?php esc_html_e( 'Google Maps API Key', 'job-listings' ) ?>
			</th>
			<td>
				<input type="text" class="regular-text"
				       value="<?php echo jlt_get_location_setting( 'google_api', '' ) ?>"
				       name="jlt_location[google_api]">
				<p>
					<?php echo __( '<b>Google</b> requires that you register an API Key to display <b>Maps</b> on from your website.', 'job-listings' ); ?>
					<br/>
					<?php echo __( 'To know how to create this application,', 'job-listings' ); ?> <a
						href="javascript:void(0)"
						onClick="jQuery('#google-map-help').toggle();return false;"><?php _e( 'click here and follow the steps.', 'job-listings' ); ?></a>
				</p>
				<div id="google-map-help" class="jlt-setting-help" style="display: none; max-width: 1200px;">
					<hr/>
					<br/>
					<?php echo __( 'To register a new <b> Google Map API Key</b>, follow the steps', 'job-listings' ); ?>
					:
					<br/>
					<?php $setupsteps = 0; ?>
					<p><b><?php echo ++ $setupsteps; ?></b>. <?php _e( 'Go to', 'job-listings' ); ?>&nbsp;<a
							href="https://console.developers.google.com/flows/enableapi?apiid=maps_backend,geocoding_backend,directions_backend,distance_matrix_backend,elevation_backend,places_backend&keyType=CLIENT_SIDE&reusekey=true&pli=1"
							target="_blank">
							<?php echo __( 'Google Developers Console', 'job-listings' ); ?></a>. <?php echo __( 'Login to your Google account if needed', 'job-listings' ); ?>
						.</p>
					<p>
						<b><?php echo ++ $setupsteps; ?></b>. <?php _e( 'Agree with the service Terms of Service ( only the first time ).', 'job-listings' ) ?>
						.</p>
					<p>
						<b><?php echo ++ $setupsteps; ?></b>. <?php _e( 'Select an existed project or Create new, then click Continue', 'job-listings' ) ?>
						.</p>
					<p>
						<b><?php echo ++ $setupsteps; ?></b>. <?php _e( 'Fill in your domain. You can use multiple domains, but one should match the current domain.', 'job-listings' ) ?>
						<em><?php echo '*' . $_SERVER[ "SERVER_NAME" ] . '/*'; ?></em></p>
					<p><b><?php echo ++ $setupsteps; ?></b>. <?php _e( 'Click <b>Create</b>', 'job-listings' ) ?>.</p>
					<p>
						<b><?php echo ++ $setupsteps; ?></b>. <?php _e( 'Copy the <em>API Key</em> then paste into the setting above', 'job-listings' ) ?>
						.</p>
					<p>
						<b><?php _e( "And that's it!", 'job-listings' ) ?></b>
						<br/>
						<?php echo __( 'For more reference, you can see: ', 'job-listings' ); ?><a
							href="https://developers.google.com/maps/documentation/javascript/get-api-key"
							target="_blank"><?php echo __( 'Official Document', 'job-listings' ) ?></a>, <a
							href="http://googlegeodevelopers.blogspot.com.au/2016/06/building-for-scale-updates-to-google.html"
							target="_blank"><?php echo __( 'Google Blog', 'job-listings' ) ?></a>
					</p>
					<div style="margin-bottom:12px;" class="jlt-thumb-wrapper">

						<a href="<?php echo esc_url( JLT_PLUGIN_URL . 'admin/images/map_api_0.jpg' ); ?>"
						   target="_blank"><img
								src="<?php echo esc_url( JLT_PLUGIN_URL . 'admin/images/map_api_0.jpg' ); ?>">
						</a>

						<a href="<?php echo esc_url( JLT_PLUGIN_URL . 'admin/images/map_api_1.png' ); ?>"
						   target="_blank"><img
								src="<?php echo esc_url( JLT_PLUGIN_URL . 'admin/images/map_api_1.png' ); ?>">
						</a>

						<a href="<?php echo esc_url( JLT_PLUGIN_URL . 'admin/images/map_api_2.png' ); ?>"
						   target="_blank"><img
								src="<?php echo esc_url( JLT_PLUGIN_URL . 'admin/images/map_api_2.png' ); ?>"></a>

						<a href="<?php echo esc_url( JLT_PLUGIN_URL . 'admin/images/map_api_3.png' ); ?>"
						   target="_blank"><img
								src="<?php echo esc_url( JLT_PLUGIN_URL . 'admin/images/map_api_3.png' ); ?>"></a>
					</div>
					<br/>
					<hr/>
				</div>
			</td>
		</tr>
		<tr>
			<th>
				<?php _e( 'Allow User Input', 'job-listings' ) ?>
			</th>
			<td>
				<input type="hidden" name="jlt_location[allow_user_input]" value="0">
				<label><input type="checkbox" <?php checked( $allow_user_input, true ); ?>
				              name="jlt_location[allow_user_input]"
				              value="1"><?php _e( 'Users can input new locations when posting  jobs', 'job-listings' ); ?>
				</label>
			</td>
		</tr>
		<tr>
			<th>
				<?php _e( 'Enable Google Auto-Complete', 'job-listings' ) ?>
			</th>
			<td>
				<input type="hidden" name="jlt_location[enable_auto_complete]" value="0">
				<label><input type="checkbox" <?php checked( $enable_auto_complete, true ); ?>
				              name="jlt_location[enable_auto_complete]"
				              value="1"><?php _e( 'Using Auto-Complete from Google Map for your location input', 'job-listings' ); ?>
				</label>
			</td>
		</tr>
		<tr>
			<th>
				<?php _e( 'Country Restriction', 'job-listings' ) ?>
				<p>
					<small><?php _e( 'Select your country will limit all suggestions to your local locations. Leave it blank to use all the locations around the world.', 'job-listings' ); ?></small>
				</p>
			</th>
			<td>
				<select name="jlt_location[country_restriction]" data-placeholder="Select your country"
				        class="jlt-setting-chosen">
					<option value=""></option>
					<?php $country_list = jlt_get_country_ISO_code(); ?>
					<?php if ( ! empty( $country_list ) ) : ?>
						<?php foreach ( $country_list as $country ) : ?>
							<option
								value="<?php echo $country->Code; ?>" <?php selected( $country->Code, $country_restriction ); ?>><?php echo $country->Name; ?></option>
						<?php endforeach; ?>
					<?php endif; ?>
				</select>

			</td>
		</tr>
		<tr>
			<th>
				<?php esc_html_e( 'Google Map Height', 'job-listings' ) ?>
			</th>
			<td>
				<input type="text" name="jlt_location[google_map_height]"
				       value="<?php echo $map_height; ?>">
				<span><?php _e( 'px', 'job-listings' ); ?></span>
			</td>
		</tr>
		<tr>
			<th>
				<?php esc_html_e( 'Google Map Style', 'job-listings' ) ?>
			</th>
			<td>
				<select name="jlt_location[google_map_style]">
					<?php
					foreach ( jlt_map_style() as $k => $v ):
						?>
						<option
							value="<?php echo $k ?>" <?php selected( $map_style, $k, true ); ?>><?php echo $v; ?></option>
						<?php
					endforeach;
					?>
				</select>
			</td>
		</tr>
		<tr>
			<th>
				<?php _e( 'Location Type', 'job-listings' ) ?>
			</th>
			<td>
				<fieldset>
					<label><input type="radio" <?php checked( $location_type, '(regions)' ); ?>
					              name="jlt_location[location_type]"
					              value="(regions)"><?php _e( 'Administrative Regions', 'job-listings' ); ?>
					</label><br/>
					<label><input type="radio" <?php checked( $location_type, '(cities)' ); ?>
					              name="jlt_location[location_type]"
					              value="(cities)"><?php _e( 'Cities', 'job-listings' ); ?>
					</label><br/>
					<label><input type="radio" <?php checked( $location_type, 'establishment' ); ?>
					              name="jlt_location[location_type]"
					              value="establishment"><?php _e( 'Establishment ( Business location )', 'job-listings' ); ?>
					</label><br/>
					<label><input type="radio" <?php checked( $location_type, 'geocode' ); ?>
					              name="jlt_location[location_type]"
					              value="geocode"><?php _e( 'Full address', 'job-listings' ); ?></label><br/>
				</fieldset>
				<p>
					<small><?php _e( 'Select the location type that matches your business.', 'job-listings' ); ?></small>
				</p>
			</td>
		</tr>
		<script>
			jQuery(document).ready(function ($) {
				// Font functions
				$('select.jlt-setting-chosen').chosen({
					allow_single_deselect: true,
					width: '240px'
				});

				$('input[name="jlt_location[enable_auto_complete]"]').change(function (event) {
					var $input = $(this);
					if ($input.is(":checked")) {
						$('.enable_auto_complete-child').show();
					} else {
						$('.enable_auto_complete-child').hide();
					}
				});

				$('input[name="jlt_location[enable_auto_complete]"]').change();
			});
		</script>
		<?php do_action( 'jlt_admin_setting_location_fields' ); ?>
		</tbody>
	</table>
	<?php
}

function jlt_get_country_ISO_code() {
	$dataFile = dirname( __FILE__ ) . '/data.json';
	$content  = json_decode( file_get_contents( $dataFile ) );

	$coutries = array();
	if ( ! empty( $content ) ) {
		$coutries = $content;
	}

	return apply_filters( 'jlt_location_country_list', $coutries );
}

function jlt_map_style() {
	$map_style = array(
		'dark'   => 'Dark',
		'light'  => 'Light',
		'nature' => 'Nature',
		'apple'  => 'Apple',
	);

	return $map_style;
}