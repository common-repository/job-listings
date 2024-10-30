<?php
/**
 * Display form steps.
 *
 * This template can be overridden by copying it to yourtheme/job-listings/global/form-steps.php.
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
<ul class="jlt-list-steps">
	<?php
	foreach ( $steps as $key => $step ):
		$class = ( $key == $current ) ? 'jlt-step active' : 'jlt-step';
		?>
		<li class="<?php echo $class; ?>"><a href="<?php echo esc_url($step[ 'link' ]) ?>"><?php echo esc_html( $step[ 'title' ] ); ?></a></li>
	<?php endforeach; ?>
</ul>