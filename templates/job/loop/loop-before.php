<?php
/**
 * Display before job loop
 *
 * This template can be overridden by copying it to yourtheme/job-listings/job/loop/loop-before.php.
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

$job_count_founds = $job_query->found_posts;

?>

<?php do_action( 'jlt_before_job_loop' ); ?>

<div class="jlt-job-archive-content">

	<div class="jlt-listing-before jlt-jobs-listing-before">

		<span class="job-count"><?php echo sprintf( _n( "%s job", "%s jobs", $job_count_founds, 'job-listings' ), $job_count_founds ) ?></span>

		<?php do_action( 'jlt_before_job_loop_content' ); ?>

	</div>

	<ul class="jlt-jobs-listing">