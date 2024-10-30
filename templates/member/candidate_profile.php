<?php
/**
 * Candidate Profile Page.
 *
 * This template can be overridden by copying it to yourtheme/job-listings/member/candidate_profile.php.
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
<?php do_action( 'jlt_edit_candidate_before' ); ?>
	<div class="jlt-form-title">
		<h3><?php _e( 'Change Profile', 'job-listings' ); ?></h3>
	</div>
	<form method="post" id="candidate_profile_form" class="jlt-form" autocomplete="on">
		<div class="candidate-profile-form">
			<?php
			/**
			 * Custom fields
			 */

			$fields = jlt_get_candidate_custom_fields();
			if ( ! empty( $fields ) ) {
				foreach ( $fields as $field ) {
					jlt_candidate_render_form_field( $field, $current_user->ID );
				}
			}

			/**
			 * Social fields
			 */

			$socials = jlt_get_candidate_socials();
			if ( ! empty( $socials ) ) {
				foreach ( $socials as $social ) {
					jlt_candidate_render_social_field( $social, $current_user->ID );
				}
			}
			$content_field = array(
				'name'    => 'description',
				'label'   => __( 'Introduce Yourself', 'job-listings' ),
				'type'    => 'textarea',
				'value'   => $user_content,
				'tinymce' => true,
			);

			jlt_render_form_field( $content_field );
			?>
			<fieldset class="fieldset">
				<label for="profile_image"><?php _e( 'Profile Image', 'job-listings' ) ?></label>
				<div class="field">
					<?php
					$profile_image = ( $current_user->ID ? get_user_meta( $current_user->ID, 'profile_image', true ) : '' );
					jlt_image_upload_form_field( 'profile_image', $profile_image, false, __( 'Recommend size: 160x160px', 'job-listings' ) );
					?>
				</div>
			</fieldset>
		</div>
		<input type="hidden" name="action" value="edit_candidate"/>
		<input type="hidden" name="candidate_id" value="<?php echo $current_user->ID; ?>"/>
		<?php jlt_form_nonce( 'edit-candidate' ) ?>
		<button type="submit" class="jlt-btn"><?php _e( 'Save My Profile', 'job-listings' ); ?></button>
	</form>
<?php
/**
 * @hooked jlt_profile_password_form - 5
 */
do_action( 'jlt_candidate_profile_form_after' );
?>