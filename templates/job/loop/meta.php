<?php
/**
 * Display Job Category In Job Loop
 *
 * This template can be overridden by copying it to yourtheme/job-listings/job/loop/categories.php.
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
<div class="job-meta jlt-col-60">
	<?php do_action( 'jlt_job_loop_meta_before' ); ?>
	<h3 class="job-loop-title">
		<a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
	</h3>
	<a class="company-name" href="<?php echo $job->company_url; ?>"
	   title="<?php echo $job->company_name; ?>">
		<?php echo $job->company_name; ?>
	</a>
	<?php
	echo $job->get_category_html();
//	echo $job->get_tag_html();
//	echo $job->get_type_html();
	echo $job->get_location_html();
	?>
	<?php do_action( 'jlt_job_loop_meta_after' ); ?>
</div>