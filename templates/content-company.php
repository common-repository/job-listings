<?php
/**
 * Display company item.
 *
 * This template can be overridden by copying it to yourtheme/job-listings/templates/content-company.php.
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

$featured = jlt_company_is_featured();

$class = $featured ? 'jlt-company-item jlt-is-featured' : 'jlt-company-item';

?>
<li <?php post_class( $class ); ?>>

	<div class="company-inner">

		<?php

		/**
		 * @hooked jlt_company_loop_logo - 5
		 * @hooked jlt_company_loop_meta - 10
		 * @hooked jlt_company_loop_action - 15
		 */
		do_action( 'jlt_company_loop_item' );

		?>

	</div>

</li>