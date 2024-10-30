<?php
/**
 * Display job item.
 *
 * This template can be overridden by copying it to yourtheme/job-listings/templates/content-job.php.
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

$featured = jlt_job_is_featured();

$class = $featured ? 'jlt-job-item jlt-is-featured' : 'jlt-job-item';

?>
<li <?php post_class( $class ); ?>>

	<div class="job-inner">

		<?php

		/**
		 * @hooked jlt_job_loop_company_logo - 5
		 * @hooked jlt_job_loop_meta - 10
		 * @hooked jlt_job_loop_action - 15
		 */
		do_action( 'jlt_job_loop_item' );

		?>

	</div>

</li>