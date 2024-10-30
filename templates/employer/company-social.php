<?php
/**
 * Display social list for single company
 *
 * This template can be overridden by copying it to yourtheme/job-listings/templates/employer/company-social.php.
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
<?php global $company; ?>
<ul class="jlt-social">
	<?php foreach ( $company->social() as $social ): ?>
		<li>
			<a href="<?php echo esc_url( $social[ 'link' ] ) ?>" target="_blank">
				<i class="jlt-icon <?php echo esc_attr( $social[ 'icon' ] ); ?>"></i>
				<span class="jlt-social-label"><?php echo esc_html($social[ 'label' ]); ?></span>
			</a>
		</li>
	<?php endforeach; ?>
</ul>