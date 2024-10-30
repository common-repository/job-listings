<?php
/**
 * Display job submit form
 *
 * This template can be overridden by copying it to yourtheme/job-listings/templates/form/job-submit.php.
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
<?php do_action( 'before_job_submit_form' ); ?>
	<form method="post" id="job-submit-form" class="jlt-form" enctype="multipart/form-data">
		<h3 class="jlt-form-title"><?php echo esc_html( $form_title ); ?></h3>
		<?php

		$fields = array(
			'title_field'   => array(
				'name'          => 'position',
				'remove_prefix' => true,
				'label'         => __( 'Job Title', 'job-listings' ),
				'type'          => 'text',
				'value'         => $job_title,
				'required'      => true,
			),
			'content_field' => array(
				'name'     => 'desc',
				'label'    => __( 'Job Content', 'job-listings' ),
				'type'     => 'textarea',
				'value'    => $job_content,
				'tinymce'  => true,
				'required' => true,
			),
		);

		$fields = apply_filters( 'jlt_job_submit_fields', $fields );

		foreach ( $fields as $field ) {
			jlt_job_render_form_field( $field );
		}

		?>
		<?php
		$fields = jlt_get_job_custom_fields();

		$custom_apply_link = jlt_get_application_setting( 'custom_apply_link', '' );

		if ( 'employer' != $custom_apply_link ) {
			unset( $fields[ '_custom_application_url' ] );
		}

		if ( ! empty( $fields ) ) {
			foreach ( $fields as $field ) {
				jlt_job_render_form_field( $field, $job_id );
			}
		}
		?>
		<input type="hidden" name="action" value="job_form"/>
		<input type="hidden" name="job_id" value="<?php echo esc_html($job_id); ?>"/>
		<input type="hidden" name="job_action" value="<?php echo esc_attr($job_action); ?>"/>
		<?php jlt_form_nonce( 'edit-job' ) ?>

		<div class="job-form-button">
			<input type="submit" name="submit_job" class="jlt-btn" value="<?php echo esc_html( $button_text ); ?>"/>
		</div>
	</form>
<?php do_action( 'after_job_submit_form' ); ?>