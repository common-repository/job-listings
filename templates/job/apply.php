<?php
/**
 * Display job apply button.
 *
 * This template can be overridden by copying it to yourtheme/job-listings/templates/job/apply.php.
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

global $job;

?>
<?php if ( $job->has_applied() ) : ?>

	<div class="jlt-applied">
		<?php _e( 'You have already applied for this job', 'job-listings' ); ?>
	</div>

<?php else: ?>
	<?php if ( ! $job->can_apply() ) : ?>

		<?php list( $title, $link ) = jlt_get_cannot_apply_job_message( $job->id ); ?>

		<?php if ( ! empty( $title ) ) {
			echo "<div><strong>$title</strong></div>";
		} ?>

		<?php if ( ! empty( $link ) ) {
			echo esc_url($link);
		} ?>

		<?php do_action( 'jlt_job_detail_cannot_apply', $job->id ); ?>

	<?php else : ?>

		<?php jlt_button_apply(); ?>

		<?php do_action( 'jlt_job_detail_apply', $job->id ); ?>

	<?php endif; ?>
<?php endif; ?>