<?php
/**
 * Display job category item.
 *
 * This template can be overridden by copying it to yourtheme/job-listings/templates/shortcode/job-category-listings.php.
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
<li class="job-category-item">

	<?php do_action('jlt_job_category_item'); ?>

	<a class="job-category-title" href="<?php echo esc_url( $category_link ); ?>">
		<?php echo esc_html( $category_name ); ?>
		<span class="job-category-count-job"><?php echo esc_html( $job_count ); ?> <?php _e( 'Job(s)', 'job-listings' ); ?></span>
	</a>

	<?php do_action('jlt_job_category_item_after'); ?>

</li>