<?php
/**
 * Display job category item before
 *
 * This template can be overridden by copying it to yourtheme/job-portal/shortcode/job-category-listings-before.php.
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

do_action( 'jlt_job_category_before' );

?>
<ul class="job-category-list job-category-list-col-<?php echo esc_attr($columns); ?>">
