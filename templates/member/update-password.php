<?php do_action( 'jlt_form_update_password_before' ); ?>
<?php
/**
 * Update Password Form
 *
 * This template can be overridden by copying it to yourtheme/job-listings/member/update-password.php.
 *
 * HOWEVER, on occasion NooTheme will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @author      NooTheme
 * @version     0.1.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

?>
<?php do_action( 'jlt_form_update_password_before' ); ?>

<div class="jlt-form-title">
	<h3><?php _e( 'Change Password', 'job-listings' ); ?></h3>
</div>
<form method="post" id="jlt-update-password" class="jlt-form jlt-form-ajax">
	<div class="update-password-form">
		<div class="jlt-ajax-note"></div>
		<?php do_action( 'jlt_form_update_password_field_before' ); ?>
		<?php

		$fields = array(
			'old_pass'         => array(
				'name'  => 'old_pass',
				'label' => __( 'Current Password', 'job-listings' ),
				'type'  => 'password',
				'value' => '',
				'required'      => true,
			),
			'new_pass'         => array(
				'name'  => 'new_pass',
				'label' => __( 'New Password', 'job-listings' ),
				'type'  => 'password',
				'value' => '',
				'required'      => true,
			),
			'new_pass_confirm' => array(
				'name'  => 'new_pass_confirm',
				'label' => __( 'Confirm new password', 'job-listings' ),
				'type'  => 'password',
				'value' => '',
				'required'      => true,
			),
		);

		$fields = apply_filters( 'jlt_update_password_fields', $fields );

		foreach ( $fields as $field ) {
			jlt_render_form_field( $field );
		}

		?>
		<?php do_action( 'jlt_form_update_password_field_after' ); ?>
	</div>
	<div class="form-group">
		<button type="submit" class="jlt-btn"><?php _e( 'Save New Password', 'job-listings' ); ?></button>
		<input type="hidden" class="security" name="security"
		       value="<?php echo wp_create_nonce( 'update-password' ) ?>"/>
		<input type="hidden" name="action" value="jlt_update_password">
		<input type="hidden" name="user_id" value="<?php echo esc_attr( get_current_user_id() ) ?>">
	</div>
</form>
<?php do_action( 'jlt_form_update_password_after' ); ?>
