<?php
/**
 * Display related job in single job
 *
 * This template can be overridden by copying it to yourtheme/job-listings/job/related.php.
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

$args = jlt_related_jobs( $job->id );

$jobs = new WP_Query( $args );

if ( ! $jobs->have_posts() ) {
	return;
}

?>
	<div class="job-related">

		<h3><?php _e( 'Related Jobs', 'job-listings' ); ?></h3>

		<ul class="jlt-jobs-listing">

			<?php while ( $jobs->have_posts() ) : $jobs->the_post(); ?>

				<?php jlt_get_template_part( 'content', 'job' ); ?>

			<?php endwhile; ?>

		</ul>

	</div>

<?php
wp_reset_postdata();