<?php
/**
 * Email Template Settings.
 *
 * @since 1.0.0
 *
 */
function jlt_email_template_settings() {
	register_setting( 'jlt_email_template', 'jlt_email_template' );
	register_setting( 'jlt_email_sender', 'jlt_email_sender' );
}

add_filter( 'admin_init', 'jlt_email_template_settings' );

// Append data to save option
function jlt_email_template_option_update( $new_value, $old_value ) {
	if ( empty( $old_value ) ) {
		$old_value = [ ];
	}
	$data = array_merge( $old_value, $new_value );

	return $data;
}

function jlt_email_template_option_init() {
	add_filter( 'pre_update_option_jlt_email_template', 'jlt_email_template_option_update', 10, 2 );
}

add_action( 'init', 'jlt_email_template_option_init' );
// End Append data to save option

function jlt_email_template_settings_tabs( $tabs = array() ) {
	return array_merge( array(
		'email_template' => __( 'Email Templates', 'job-listings' ),
	), $tabs );
}

add_filter( 'jlt_admin_settings_tabs_array', 'jlt_email_template_settings_tabs' );

function jlt_email_sender_get_setting( $id = null, $default = null ) {
	$email_template_field = jlt_email_template_field();
	$default              = ( $default === null && isset( $email_template_field[ $id . '_default' ] ) ) ? $email_template_field[ $id . '_default' ] : $default;

	return jlt_get_setting( 'jlt_email_sender', $id, $default );
}

function jlt_et_get_setting( $id = null, $default = null ) {
	$email_template_field = jlt_email_template_field();
	$default              = ( $default === null && isset( $email_template_field[ $id ] ) ) ? $email_template_field[ $id ] : $default;

	return jlt_get_setting( 'jlt_email_template', $id, $default );
}

function jlt_email_get_setting( $id = null, $section = null, $default = null ) {
	$option = jlt_et_get_setting( $id );

	$value = isset( $option[ $section ] ) ? $option[ $section ] : $default;

	if ( $value != '' or ! is_null( $value ) ) {
		return $value;
	} else {
		if ( $section == 'active' ) {
			return $default;
		} else {
			$fields = jlt_email_template_field();
			$field  = $fields[ $id ];

			return ! empty( $default ) ? $default : $field[ $section ];
		}
	}
}

function jlt_email_template_settings_form() {

	$section = isset( $_GET[ 'section' ] ) ? $_GET[ 'section' ] : false;
	?>
	<?php if ( ! $section ): ?>
		<?php

		$blogname   = get_option( 'blogname' );
		$from_name  = jlt_email_sender_get_setting( 'from_name', $blogname );
		$from_email = jlt_email_sender_get_setting( 'from_email', '' );
		$from_email = strtolower( $from_email );
		?>
		<?php settings_fields( 'jlt_email_sender' ); ?>
		<h3><?php _e( 'Email Configuration', 'job-listings' ); ?></h3>
		<table class="form-table" cellspacing="0">
			<tbody>
			<tr>
				<th>
					<?php _e( 'From Email', 'job-listings' ) ?>
				</th>
				<td>
					<input type="text" name="jlt_email_sender[from_email]"
					       placeholder="<?php echo jlt_mail_do_not_reply(); ?>" size="40"
					       value="<?php echo esc_attr( $from_email ); ?>">
					<p>
						<small><?php _e( 'The email address that emails on your site should be sent from. You should leave it blank if you used a 3rd plugin for sending email.', 'job-listings' ); ?></small>
					</p>
				</td>
			</tr>
			<tr>
				<th>
					<?php _e( 'From Name', 'job-listings' ) ?>
				</th>
				<td>
					<input type="text" name="jlt_email_sender[from_name]"
					       placeholder="<?php echo get_option( 'blogname' ); ?>" size="40"
					       value="<?php echo esc_attr( $from_name ); ?>">
					<p>
						<small><?php _e( 'The name that emails on your site should be sent from. You should leave it blank if you used a 3rd plugin for sending email.', 'job-listings' ); ?></small>
					</p>
				</td>
			</tr>
			</tbody>
		</table>
		<?php do_action( 'jlt_admin_setting_email_sender' ); ?>
		<h3><?php echo __( 'Email Templates Options', 'job-listings' ) ?></h3>
		<p><?php _e( 'Email notifications list. Click on an email to configure it.', 'job-listings' ); ?></p>

		<table class="jlt-email-table widefat striped">
			<thead>
			<tr>
				<th><?php _e( 'Email', 'job-listings' ); ?></th>
				<th><?php _e( 'Status', 'job-listings' ); ?></th>
				<th><?php _e( 'Recipient', 'job-listings' ); ?></th>
				<th><?php _e( 'Description', 'job-listings' ); ?></th>
				<th class="jlt-admin-col-actions"><?php _e( 'Action', 'job-listings' ); ?></th>
			</tr>
			</thead>
			<tbody>
			<?php
			$fields = jlt_email_template_field();
			foreach ( $fields as $field_key => $field ) :

				$config_url = add_query_arg( 'section', $field_key );

				?>
				<tr class="jlt-email-<?php echo $field_key; ?>">
					<td>
						<a href="<?php echo $config_url; ?>">
							<?php echo $field[ 'title' ]; ?>
						</a>
					</td>
					<td><span
							class="dashicons dashicons-<?php echo( jlt_et_email_status( $field_key ) == 0 ? 'no' : 'yes' ); ?>"></span>
					</td>
					<td>
						<?php echo $field[ 'recipient' ]; ?>
					</td>
					<td><?php echo $field[ 'desc' ]; ?></td>
					<td class="jlt-admin-col-actions">
						<a class="button button-email-template" href="<?php echo $config_url; ?>"><i
								class="dashicons dashicons-admin-generic"></i></a>
					</td>
				</tr>
			<?php endforeach; ?>
			</tbody>
		</table>
	<?php else: ?>
		<?php

		settings_fields( 'jlt_email_template' );

		$fields = jlt_email_template_field();
		$field  = $fields[ $section ];

		if ( $field != null ):
			$setting_value = jlt_et_get_setting( $section, '' );
			?>
			<h3><?php echo $field[ 'title' ]; ?></h3>
			<p>
				<?php echo $field[ 'desc' ]; ?>
			</p>
			<table class="form-table email-template-setting" cellspacing="0">
				<tbody>
				<tr>
					<th>
						<?php esc_html_e( 'Activate', 'job-listings' ) ?>
					</th>
					<td>
						<?php
						$active = jlt_et_email_status( $section );
						?>
						<input type="hidden" name="jlt_email_template[<?php echo $section; ?>][active]" value="0"/>
						<input type="checkbox"
						       name="jlt_email_template[<?php echo $section; ?>][active]" <?php checked( $active ); ?>
						       value="1"/>
					</td>
				</tr>
				<tr>
					<th>
						<?php esc_html_e( 'Subject', 'job-listings' ) ?>
					</th>
					<td>
						<input type="text" name="jlt_email_template[<?php echo $section; ?>][subject]"
						       class="large-text"
						       placeholder="<?php _e( 'Enter Your Subject', 'job-listings' ); ?>"
						       value="<?php echo ! empty( $setting_value[ 'subject' ] ) ? $setting_value[ 'subject' ] : $field[ 'subject' ]; ?>">
					</td>
				</tr>
				<tr>
					<th>
						<?php esc_html_e( 'Email Content', 'job-listings' ) ?>
					</th>
					<td>
						<?php

						$editor_id = 'textblock' . uniqid();
						$content   = ! empty( $setting_value[ 'content' ] ) ? $setting_value[ 'content' ] : $field[ 'content' ];
						wp_editor( $content, $editor_id, array(
							'media_buttons' => false,
							'quicktags'     => true,
							'textarea_rows' => 10,
							'textarea_name' => 'jlt_email_template[' . $section . '][content]',
							'wpautop'       => false,
						) ); ?>
						<?php jlt_et_render_field( $section, true, false, $field[ 'post_type' ] ); ?>
					</td>
				</tr>
				</tbody>
			</table>

		<?php endif; ?>
	<?php endif; ?>
	<?php
}

add_action( 'jlt_admin_setting_email_template', 'jlt_email_template_settings_form' );