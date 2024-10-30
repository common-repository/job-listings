<?php
/**
 * Display job search form
 *
 * This template can be overridden by copying it to yourtheme/job-listings/job/search-form.php.
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

$search_keyword = get_query_var( 'keyword' ) ? get_query_var( 'keyword' ) : '';
$search_fields  = jlt_job_custom_fields_search();
?>

<form method="get" class="jlt-job-search" action="<?php echo get_post_type_archive_link( 'job' ); ?>">

	<?php do_action( 'jlt_search_form_field_before' ); ?>
	<div class="jlt-search-field">
		<input type="text" name="keyword" placeholder="<?php echo esc_html( 'Enter keyword...', 'job-listings' ); ?>"
		       class="jlt-form-control jlt-search-keyword" value="<?php echo esc_html($search_keyword); ?>"/>

		<?php do_action( 'jlt_search_keyword_field' ); ?>
	</div>
	<div class="jlt-advanced-search">
		<?php foreach ( $search_fields as $field ): ?>
			<?php jlt_job_advanced_search_field( $field ); ?>
		<?php endforeach; ?>

		<?php do_action( 'jlt_search_field' ); ?>
	</div>

	<div class="jlt-search-btn">
		<button type="submit" class="jlt-btn"><?php _e( 'Search Job', 'job-listings' ); ?></button>
	</div>

</form>