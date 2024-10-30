<?php
/**
 * Popup Application Employer Responsive Info.
 *
 * This template can be overridden by copying it to yourtheme/job-listings/member/manage-application-response.php.
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
<div id="application-message" class="jlt-popup">
	<div class="jlt-popup-title"><?php _e( 'Employer\'s Message', 'job-listings' ); ?></div>
	<div class="jlt-popup-body">
		<ul class="application-info">
			<li><label><?php _e( 'Title: ', 'job-listings' ); ?></label><span><?php echo $title; ?></span></li>
			<li><label><?php _e( 'Status: ', 'job-listings' ); ?></label><span><?php echo $status; ?></span></li>
			<li class="application-message">

				<label><?php _e( 'Application Message: ', 'job-listings' ); ?></label>
				<div class="application-message-content">
					<?php echo $message; ?>
				</div>

			</li>
		</ul>
	</div>
</div>