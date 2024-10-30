<?php
/**
 * Display single company detail.
 *
 * This template can be overridden by copying it to yourtheme/job-listings/templates/content-single-company.php.
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

?>
<div <?php post_class( 'jlt-single single-company' ); ?> id="company-<?php the_ID(); ?>">

	<?php do_action( 'jlt_single_company_before' ); ?>

	<div class="jlt-single-company-header">

		<?php
		/**
		 * @hook: jlt_single_company_logo - 5
		 * @hook: jlt_single_company_meta - 10
		 */
		do_action( 'jlt_single_company_header' );

		?>

	</div>

	<div class="jlt-single-content">

		<?php echo get_the_content(); ?>

		<?php

		do_action( 'jlt_single_company_content' );

		?>

	</div>

	<?php

	/**
	 * @hooked jlt_single_company_info - 5
	 * @hooked jlt_single_company_map - 10
	 * @hooked jlt_single_company_jobs - 15
	 */

	do_action( 'jlt_single_company_after' );

	?>
</div><!-- #company-<?php the_ID(); ?> -->