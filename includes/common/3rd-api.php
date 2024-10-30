<?php

function jlt_3rd_api_admin_init() {
	register_setting( 'jlt_3rd_api', 'jlt_3rd_api' );
}

add_filter( 'admin_init', 'jlt_3rd_api_admin_init' );

function jlt_3rd_api_settings_tabs( $tabs = array() ) {
	$tabs[ '3rd_api' ] = __( '3rd APIs', 'job-listings' );

	return $tabs;
}

add_filter( 'jlt_admin_settings_tabs_array', 'jlt_3rd_api_settings_tabs', 99 );

function jlt_get_3rd_api_setting( $id = null, $default = null ) {
	$api = jlt_get_setting( 'jlt_3rd_api', $id, $default );

	if ( empty( $api ) ) {
		// old option
		switch ( $id ) {
			case 'linkedin_app_id':
				return jlt_get_application_setting( 'api_key', $api );
			case 'linkedin_app_secret':
				return jlt_get_application_setting( 'api_secret', $api );
			case 'facebook_app_id':
				return JLT_Member::get_setting( 'id_facebook', $api );
			case 'facebook_app_secret':
				return JLT_Member::get_setting( 'secret_facebook', $api );
			case 'google_client_id':
				return JLT_Member::get_setting( 'google_client_id', $api );
			case 'google_client_secret':
				return JLT_Member::get_setting( 'google_client_secret', $api );
		}
	}

	return $api;
}

function jlt_3rd_api_settings_form() {

	?>
	<?php settings_fields( 'jlt_3rd_api' ); ?>
	<h3><?php echo __( 'APIs', 'job-listings' ) ?></h3>
	<table class="form-table" cellspacing="0">
		<tbody>
		<tr id="google-recaptcha-key">
			<th>
				<?php _e( 'Google reCaptcha Key', 'job-listings' ) ?>
			</th>
			<td>
				<input type="text" name="jlt_3rd_api[google_recaptcha_key]"
				       value="<?php echo jlt_get_3rd_api_setting( 'google_recaptcha_key', '' ); ?>"
				       placeholder="<?php _e( 'Google reCaptcha Public Key', 'job-listings' ) ?>" size="60"/>
				<input type="text" name="jlt_3rd_api[google_recaptcha_secret_key]"
				       value="<?php echo jlt_get_3rd_api_setting( 'google_recaptcha_secret_key', '' ); ?>"
				       placeholder="<?php _e( 'Google reCaptcha Secret key', 'job-listings' ) ?>" size="50"/>
				<p><?php _e( 'Please enter Google reCaptcha public and secret key', 'job-listings' ); ?></p>
				<p><a target="_blank"
				      href="https://www.google.com/recaptcha/admin#list"><?php _e( 'Click here to get Google reCaptcha Key ', 'job-listings' ); ?></a>
				</p>

			</td>
		</tr>
		<?php do_action( 'jlt_admin_setting_3rd_api_fields' ); ?>
		</tbody>
	</table>
	<?php
}

add_action( 'jlt_admin_setting_3rd_api', 'jlt_3rd_api_settings_form' );
