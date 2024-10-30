<?php
/**
 * Display single company meta
 *
 * This template can be overridden by copying it to yourtheme/job-listings/employer/company-meta.php.
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
<div class="jlt-col-70">

	<div class="company-meta">

		<?php
		/**
		 * @hook: //jlt_single_company_title - 5
		 * @hook: jlt_single_company_social_list - 10
		 */
		do_action( 'jlt_single_company_meta' );
		?>

	</div>

</div>