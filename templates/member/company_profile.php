<?php
/**
 * Company Profile Page.
 *
 * This template can be overridden by copying it to yourtheme/job-listings/member/company_profile.php.
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

<?php do_action( 'jlt_company_profile_form_before' ); ?>

	<div class="jlt-form-title">
		<h3><?php _e( 'Company Profile', 'job-listings' ); ?></h3>
	</div>
	<form method="post" id="company_profile_form" class="jlt-form">

		<div class="company-profile-form">
			<?php

			/**
			 * Main fields
			 */

			$fields = array(
				'company_name' => array(
					'name'     => 'company_name',
					'label'    => __( 'Company Name', 'job-listings' ),
					'type'     => 'text',
					'value'    => $company_name,
					'required' => true,
				),
				'company_desc' => array(
					'name'    => 'company_desc',
					'label'   => __( 'Company Description', 'job-listings' ),
					'type'    => 'textarea',
					'value'   => $company_content,
					'tinymce' => true,
				),
			);

			$fields = apply_filters( 'jlt_company_profile_form_fields', $fields );

			foreach ( $fields as $field ) {
				jlt_render_form_field( $field );
			}

			/**
			 * Custom fields
			 */

			$fields = jlt_get_company_custom_fields();
			if ( ! empty( $fields ) ) {
				foreach ( $fields as $field ) {
					jlt_company_render_form_field( $field, $company_id );
				}
			}
			?>

			<?php
			/**
			 * Social fields
			 */

			$socials = jlt_get_company_socials();
			if ( ! empty( $socials ) ) {
				foreach ( $socials as $social ) {
					jlt_company_render_social_field( $social, $company_id );
				}
			}
			?>
		</div>

		<div class="form-group">
			<button type="submit" class="jlt-btn"><?php _e( 'Save', 'job-listings' ); ?></button>
			<?php jlt_form_nonce( 'edit-company' ) ?>
			<input type="hidden" name="action" value="edit_company">
			<input type="hidden" name="company_id" value="<?php echo esc_attr( $company_id ); ?>">
		</div>
	</form>
<?php
/**
 * @hooked jlt_profile_email_form - 5
 * @hooked jlt_profile_password_form - 10
 */
do_action( 'jlt_company_profile_form_after' );
?>