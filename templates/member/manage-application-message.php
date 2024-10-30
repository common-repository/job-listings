<?php
/**
 * Popup Application Content Form.
 *
 * This template can be overridden by copying it to yourtheme/job-listings/member/manage-application-message.php.
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
$title   = get_the_title( $application );
$message = apply_filters( 'the_content', get_post_field( 'post_content', $application_id ) );
?>
<div id="application-message" class="jlt-popup">
	<div class="jlt-popup-title"><?php echo sprintf( __( '%s\'s Application', 'job-listings' ), $title ); ?></div>
	<div class="jlt-popup-body">
		<ul class="application-info">
			<li><label><?php _e( 'Candidate Email: ', 'job-listings' ); ?></label><span><?php echo $candidate_email; ?></span></li>
			<li class="application-message">

				<label><?php _e( 'Application Message: ', 'job-listings' ); ?></label>
				<div class="application-message-content">
					<?php echo $message; ?>
				</div>

			</li>
		</ul>
	</div>
</div>