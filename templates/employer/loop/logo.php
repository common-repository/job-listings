<?php
/**
 * Display company logo in Job loop
 *
 * This template can be overridden by copying it to yourtheme/job-listings/job/loop/company-logo.php.
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

<div class="company-logo jlt-col-20">
	<?php if ( ! empty( $company->logo() ) ) : ?>
		<a href="<?php the_permalink() ?>" title="<?php the_title(); ?>">
			<?php echo $company->logo(); ?>
		</a>
	<?php endif; ?>
</div>