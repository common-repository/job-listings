<?php
/**
 * Display job preview
 *
 * This template can be overridden by copying it to yourtheme/job-listings/templates/form/job-preview.php.
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
<?php do_action( 'jlt_before_job_preview_form' ); ?>

	<form method="post" id="job-preview-form" class="jlt-form jlt-job-preview-form" enctype="multipart/form-data">

		<h3><?php _e( 'Preview job', 'job-listings' ); ?></h3>

		<div <?php post_class( 'jlt-single single-job jlt-job-preview' ); ?> id="job-<?php the_ID(); ?>">

			<?php jlt_job_single_title(); ?>

			<div class="jlt-single-content">

				<?php the_content(); ?>

			</div>

			<?php
			/**
			 * @hooked jlt_single_job_info - 5
			 * @hooked jlt_single_job_meta - 10
			 * @hooked jlt_single_job_map - 15
			 */
			do_action( 'jlt_after_job_preview_form_content' );
			?>

		</div>

		<input type="hidden" name="action" value="preview_job"/>
		<input type="hidden" name="job_id" value="<?php echo esc_html($job_id); ?>"/>
		<?php jlt_form_nonce( 'edit-job' ) ?>

		<div class="job-form-button">
			<a href="<?php echo esc_url( $job_edit_url ); ?>" class="jlt-btn"><?php _e( 'Edit Job', 'job-listings' ); ?></a>
			<input type="submit" name="submit_job" class="jlt-btn" value="<?php _e( 'Submit Job', 'job-listings' ); ?>">
		</div>

	</form>

<?php do_action( 'jlt_after_job_preview_form' ); ?>