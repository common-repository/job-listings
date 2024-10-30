<?php
/**
 * Display job on map
 *
 * This template can be overridden by copying it to yourtheme/job-listings/job/job-listings-map.php.
 *
 * HOWEVER, on occasion NooTheme will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @author      NooTheme
 * @version     1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}
?>
<div class="job-map">
	<div class="gmap-loading">
		<span><?php _e( 'Map loading', 'job-listings' ); ?></span>
		<div class="gmap-loader">
			<div class="rect1"></div>
			<div class="rect2"></div>
			<div class="rect3"></div>
			<div class="rect4"></div>
			<div class="rect5"></div>
		</div>
	</div>
	<div id="gmap" data-map_style="<?php echo $map_style; ?>"
	     data-latitude="<?php echo esc_html( $lat ); ?>"
	     data-longitude="<?php echo esc_html( $long ); ?>" data-zoom="<?php echo esc_attr( $zoom ); ?>"
	     data-fit_bounds="<?php echo esc_html( $fit_bounds ); ?>"
	     style="height: <?php echo esc_attr( $map_height ); ?>px;">
	</div>
	<div class="container-map-location-search">
		<i class="jlt-icon jltfa-search job-map-search-icon"></i>
		<input type="text" class="jlt-form-control" id="map-location-search"
		       placeholder="<?php _e( 'Search for a location...', 'job-listings' ); ?>" autocomplete="off">
	</div>
</div>