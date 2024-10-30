<?php
/**
 * Display Single Job
 *
 * This template can be overridden by copying it to yourtheme/job-listings/content-single-job.php.
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

/**
 * @hooked jlt_message_print - 5
 */
do_action( 'jlt_single_before' );

?>
	<div class="jlt-single">

		<?php
		/**
		 * @hooked jlt_job_single_title - 5
		 * @hooked jlt_job_single_company_info - 10
		 */
		do_action( 'jlt_single_job_before' );

		?>

		<div class="jlt-single-content">

			<?php echo $job->content(); ?>

			<?php

			do_action( 'jlt_single_job_content' );

			?>

		</div>

		<?php
		/**
		 * @hooked jlt_single_job_info - 5
		 * @hooked jlt_single_job_meta - 10
		 * @hooked jlt_single_job_map - 15
		 * @hooked jlt_single_job_apply - 20
		 * @hooked jlt_job_single_related - 25
		 */
		do_action( 'jlt_single_job_after' );
		?>
	</div>

<?php do_action( 'jlt_single_after' ); ?>