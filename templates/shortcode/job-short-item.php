<?php
/**
 * Display job short information.
 *
 * This template can be overridden by copying it to yourtheme/job-portal/shortcode/job-short-item.php.
 *
 * HOWEVER, on occasion NooTheme will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @author      NooTheme
 * @version     1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

$job = jlt_get_job( $id );

?>
<div class="job-short-item">
	<div class="company-logo jlt-col-20">
		<?php if ( ! empty( $job->company_logo ) ) : ?>
			<a href="<?php the_permalink() ?>" title="<?php the_title(); ?>">
				<?php echo $job->company_logo; ?>
			</a>
		<?php endif; ?>
	</div>
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
		echo $job->get_location_html();
		?>
		<?php do_action( 'jlt_job_loop_meta_after' ); ?>
	</div>
</div>
