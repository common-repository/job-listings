<?php
/**
 * Display before company item loop
 *
 * This template can be overridden by copying it to yourtheme/job-listings/employer/loop/loop-before.php.
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

do_action( 'jlt_before_company_loop' );

?>

<div class="jlt-company-archive-content">

	<div class="jlt-listing-before jlt-company-listing-before">

		<?php do_action( 'jlt_before_company_loop_content' ); ?>

	</div>

	<ul class="jlt-company-listing">