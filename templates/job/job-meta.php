<?php
/**
 * Display Single Job Meta
 *
 * This template can be overridden by copying it to yourtheme/job-listings/job/job-meta.php.
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
<div class="job-meta">

	<?php echo $job->get_category_html(); ?>
	<?php echo $job->get_tag_html(); ?>
	<?php echo $job->get_type_html(); ?>
	<?php echo $job->get_address_html(); ?>
	<?php echo $job->get_location_html(); ?>

	<?php do_action( 'jlt_single_job_meta' ); ?>

</div>
