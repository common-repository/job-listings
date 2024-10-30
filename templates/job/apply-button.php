<?php
/**
 * Display Apply Button
 *
 * This template can be overridden by copying it to yourtheme/job-listings/job/apply-button.php.
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
<?php

$apply_url = jlt_job_apply_url();

if ( empty( $apply_url ) ) :
	?>

	<a class="jlt-btn jlt-btn-apply-form" href="#apply-job-form"><?php _e( 'Apply for this job', 'job-listings' ); ?></a>

	<?php jlt_get_template( "form/apply-form.php" ); ?>

<?php else : ?>
	<a class="jlt-btn jlt-btn-apply-link" href="<?php echo esc_url( $apply_url ); ?>" target="_blank">
		<?php _e( 'Apply for this job', 'job-listings' ); ?>
	</a>
<?php endif; ?>



