<?php
/**
 * Show list job of company
 *
 * This template can be overridden by copying it to yourtheme/job-listings/employer/company-jobs.php.
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
global $company;
$jobs = $company->jobs();
if ( ! $jobs ) {
	return;
}

$paged = ( get_query_var( 'paged' ) ) ? get_query_var( 'paged' ) : 1;
$args  = array(
	'post_type'      => 'job',
	'post__in'       => $jobs,
	'paged'          => $paged,
	'posts_per_page' => $posts_per_page,
);

$jobs = new WP_Query( $args );
?>
	<div class="company-jobs">

		<h3><?php _e( 'Jobs of company', 'job-listings' ); ?></h3>

		<ul class="jlt-jobs-listing">

			<?php while ( $jobs->have_posts() ) : $jobs->the_post(); ?>

				<?php jlt_get_template_part( 'content', 'job' ); ?>

			<?php endwhile; ?>

		</ul>

		<?php jlt_show_paging( $jobs ) ?>

	</div>
<?php wp_reset_query(); ?>