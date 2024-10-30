<?php
/**
 * Display dashboard member
 *
 * This template can be overridden by copying it to yourtheme/job-listings/member/dashboard.php.
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
<?php
/**
 * @hooked jlt_member_navigation - 5
 * @hooked jlt_message_print - 10
 */
do_action( 'jlt_member_header' );
?>
<div class="jlt-dashboad">

	<?php do_action( 'jlt_member_dashboard_content' ); ?>

</div>
