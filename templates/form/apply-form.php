<?php
/**
 * Display apply form
 *
 * This template can be overridden by copying it to yourtheme/job-listings/templates/form/apply-form.php.
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

global $post;
if ( is_user_logged_in() ) {
	$user            = wp_get_current_user();
	$candidate_name  = $user->display_name;
	$candidate_email = $user->user_email;
} else {
	$candidate_name  = '';
	$candidate_email = '';
}

$message_opt = jlt_get_application_setting( 'application_message', 'required' );

?>
<div id="apply-job-form" class="jlt-popup mfp-hide">
	<div class="jlt-popup-body">

		<?php if ( jlt_application_can_apply() ) : ?>

			<form id="apply_job_form" class="jlt-form" method="post" enctype="multipart/form-data">

				<?php
				/**
				 * Application Main Field.
				 */

				$fields = array(
					'candidate_name'  => array(
						'name'     => 'candidate_name',
						'label'    => __( 'Name', 'job-listings' ),
						'type'     => 'text',
						'value'    => $candidate_name,
						'required' => true,
					),
					'candidate_email' => array(
						'name'     => 'candidate_email',
						'label'    => __( 'Email', 'job-listings' ),
						'type'     => 'email',
						'value'    => $candidate_email,
						'required' => true,
					),
				);

				$fields = apply_filters( 'jlt_apply_form_fields', $fields );

				foreach ( $fields as $field ) {
					jlt_render_form_field( $field );
				}

				/**
				 * Application Custom Field.
				 */

				$fields = jlt_get_application_custom_fields();
				if ( ! empty( $fields ) ) {
					foreach ( $fields as $field ) {
						jlt_application_render_apply_form_field( $field );
					}
				}

				?>

				<?php do_action( 'after_apply_job_form' ); ?>

				<button type="submit" class="jlt-btn"><?php _e( 'Send application', 'job-listings' ) ?></button>
				<input type="hidden" name="action" value="apply_job">
				<input type="hidden" name="job_id" value="<?php echo esc_attr( $post->ID ) ?>">
				<?php jlt_form_nonce( 'jlt-apply-job' ) ?>

			</form>
		<?php endif; ?>
	</div>
</div>