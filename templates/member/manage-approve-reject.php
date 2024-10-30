<?php
/**
 * Popup Approve/Reject Application Form.
 *
 * This template can be overridden by copying it to yourtheme/job-listings/member/manage-approve-reject.php.
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
<div id="approve-reject-application-form" class="jlt-popup">
	<div class="jlt-popup-title"><?php _e( 'Response Application', 'job-listings' ); ?></div>
	<div class="jlt-popup-body">
		<form id="approve-reject-application-form" method="post" class="jlt-form">

			<fieldset class="fieldset required-field">
				<label for="name"><?php _e( 'Response to', 'job-listings' ); ?></label>
				<div class="field">
					<span><?php echo esc_html($name); ?></span>
				</div>
			</fieldset>

			<fieldset class="fieldset required-field">
				<label for="title"><?php _e( 'Title', 'job-listings' ) ?></label>
				<div class="field">
					<input type="text" class="jlt-form-control" id="title"
					       value="<?php echo esc_html($title); ?>"
					       name="title"/>
				</div>
			</fieldset>

			<fieldset class="fieldset required-field">
				<label for="message"><?php esc_attr_e( 'Message', 'job-listings' ) ?></label>
				<div class="field">
					<textarea id="message" class="jlt-form-control" name="message" rows="8"><?php echo esc_html($message); ?></textarea>
				</div>
			</fieldset>

			<input type="hidden" name="application_id" value="<?php echo esc_attr( $application_id ) ?>">
			<input type="hidden" name="action" value="<?php echo esc_attr( $type ) ?>">
			<?php jlt_form_nonce( 'application-manage-action' ); ?>
			<button class="jlt-btn" type="submit"><?php _e( $button ); ?></button>
		</form>
	</div>
</div>