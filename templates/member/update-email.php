<?php
/**
 * Update Email Form
 *
 * This template can be overridden by copying it to yourtheme/job-listings/member/update-email.php.
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

$user = wp_get_current_user();

?>
<?php do_action( 'jlt_form_update_email_before' ); ?>

	<div class="form-title">
		<h3><?php _e( 'Change Email', 'job-listings' ); ?></h3>
	</div>
	<form method="post" id="jlt-update-email" class="jlt-form jlt-form-ajax">
		<div class="update-email-form">
			<div class="jlt-ajax-note"></div>
			<?php do_action( 'jlt_form_update_email_field_before' ); ?>
			<?php

			$fields = array(
				'old_email'         => array(
					'name'     => 'old_email',
					'label'    => __( 'Current Email', 'job-listings' ),
					'type'     => 'email',
					'disabled' => 'disabled',
					'value'    => $user->user_email,
				),
				'new_email'         => array(
					'name'     => 'new_email',
					'label'    => __( 'New Email', 'job-listings' ),
					'type'     => 'email',
					'value'    => '',
					'required' => true,
				),
				'new_email_confirm' => array(
					'name'     => 'new_email_confirm',
					'label'    => __( 'Confirm new email', 'job-listings' ),
					'type'     => 'email',
					'value'    => '',
					'required' => true,
				),
			);

			$fields = apply_filters( 'jlt_update_email_fields', $fields );

			foreach ( $fields as $field ) {
				jlt_render_form_field( $field );
			}

			?>
			<?php do_action( 'jlt_form_update_email_field_after' ); ?>
		</div>
		<div class="form-group">
			<button type="submit" class="jlt-btn jlt-btn-update-email"><?php _e( 'Save New Email', 'job-listings' ); ?></button>
			<input type="hidden" class="security" name="security"
			       value="<?php echo wp_create_nonce( 'update-email' ) ?>"/>
			<input type="hidden" name="action" value="jlt_update_email">
			<input type="hidden" name="user_id" value="<?php echo esc_attr( get_current_user_id() ) ?>">
		</div>
	</form>
<?php do_action( 'jlt_form_update_email_after' ); ?>