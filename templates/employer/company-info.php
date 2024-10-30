<?php
/**
 * Display company info
 *
 * This template can be overridden by copying it to yourtheme/job-listings/employer/company-info.php.
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
$info = $company->info();
if ( ! $info ) {
	return;
}

?>
<div class="company-info">

	<h3><?php _e( 'Company infomation', 'job-listings' ); ?></h3>

	<ul class="company-info-list">

		<?php foreach ( $info as $data ): ?>

			<li class="jlt-custom-field jlt-custom-field-company jlt-custom-field-<?php echo esc_attr( $data['field'][ 'type' ] ); ?> comany-info-field company-info_<?php echo esc_attr( $data[ 'id' ] ); ?>">

				<?php
				echo jlt_display_field( $data[ 'field' ], $data[ 'id' ], $data[ 'value' ], array(
					'label_tag'   => 'div',
					'label_class' => 'jlt-custom-field-label company-cf',
					'value_tag'   => 'div',
				), false ) ?>

			</li>

		<?php endforeach; ?>

	</ul>

</div>