<?php
/**
 * NOO Meta Boxes Package
 *
 * Setup NOO Meta Boxes for Page
 * This file add Meta Boxes to WP Page edit page.
 *
 * @package    NOO Framework
 * @subpackage NOO Meta Boxes
 * @version    0.1.0
 * @author     NooTheme Team
 * @copyright  Copyright (c) 2014, NooTheme
 * @license    http://opensource.org/licenses/gpl-2.0.php GPL v2 or later
 * @link       https://www.nootheme.com
 */

if (!function_exists('jlt_page_meta_boxes')):
	function jlt_page_meta_boxes() {
		// Declare helper object
		$prefix = '_jlt_wp_page';
		$helper = new JLT_Meta_Boxes_Helper($prefix, array(
			'page' => 'page'
		));

		// Page Settings
		$meta_box = array(
			'id' => "{$prefix}_meta_box_page",
			'title' => __('Page Settings', 'job-listings') ,
			'description' => __('Choose various setting for your Page.', 'job-listings') ,
			'fields' => array(
				array(
					'label' => __('Body Custom CSS Class', 'job-listings') ,
					'id' => "_jlt_body_css",
					'type' => 'text',
				),
				array(
					'label' => __('Hide Page Title', 'job-listings') ,
					'id' => "{$prefix}_hide_page_title",
					'type' => 'checkbox',
					'child-fields' => array(
						'off'   => "_heading_image"
					),
				),
				array(
					'id'    => '_heading_image',
					'label' => __( 'Heading Background Image', 'job-listings' ),
					'desc'  => __( 'An unique heading image for this page', 'job-listings'),
					'type'  => 'image',
				)
			)
		);

		$helper->add_meta_box($meta_box);

		//
		// Revolution Sliders
		//
		// if ( class_exists( 'RevSlider' ) ) {
		// 	// Home Slider
		// 	$meta_box = array(
		// 		'id' => "{$prefix}_meta_box_home_slider",
		// 		'title' => __('Home Slider', 'job-listings'),
		// 		'fields' => array(
		// 			array(
		// 			'id'    => "{$prefix}_enable_home_slider",
		// 			'label' => __( 'Enable Home Slider' , 'job-listings' ),
		// 			'desc'  => __( 'Enable Home Slider which displayed on the top of your site, along with the Header.', 'job-listings' ),
		// 			'type'  => 'checkbox',
		// 			'std'   => 'off',
		// 			'child-fields' => array(
		// 					'on'   => "{$prefix}_slider_rev,{$prefix}_slider_position,{$prefix}_slider_custom_bg"
		// 				)
		// 			),
		// 			array(
		// 				'label' => __( 'Revolution Slider', 'job-listings' ),
		// 				'desc' => __( 'Select a Slider from Revolution Slider.', 'job-listings' ),
		// 				'id'   => "{$prefix}_slider_rev",
		// 				'type' => 'rev_slider',
		// 				'std'  => ''
		// 			),
		// 			array(
		// 				'label' => __('Slider Position', 'job-listings') ,
		// 				'id' => "{$prefix}_slider_position",
		// 				'type' => 'radio',
		// 				'std' => 'below',
		// 				'options' => array(
		// 					'above' => array(
		// 						'label' => __('Above Header', 'job-listings') ,
		// 						'value' => 'above',
		// 						) ,
		// 					'below' => array(
		// 						'label' => __('Below Header', 'job-listings') ,
		// 						'value' => 'below',
		// 						) ,
		// 					),
		// 				'child-fields' => array(
		// 					'above' => "{$prefix}_slider_above_scroll_bottom",
		// 				),
		// 			),
		// 			array(
		// 				'label' => __('Use custom background video?', 'job-listings') ,
		// 				'id' => "{$prefix}_slider_custom_bg",
		// 				'type' => 'checkbox',
		// 				'child-fields' => array(
		// 					'on' => "{$prefix}_slider_bg_video,{$prefix}_slider_bg_video_poster"
		// 					)
		// 			),
		// 			array(
		// 				'label' => __( 'Background Video', 'job-listings' ),
		// 				'desc' => __( 'Input the URL to your .mp4 video file.', 'job-listings' ),
		// 				'id'   => "{$prefix}_slider_bg_video",
		// 				'type' => 'text',
		// 				'std'  => ''
		// 			),
		// 			array(
		// 				'label' => __( 'Video Poster Image (on Mobile)', 'job-listings' ),
		// 				'desc' => __( 'The poster image is used on Mobile where the background video is not available', 'job-listings' ),
		// 				'id'   => "{$prefix}_slider_bg_video_poster",
		// 				'type' => 'image',
		// 				'std'  => ''
		// 				),
		// 			// array(
		// 			// 	'label' => __( 'Show Scroll Bottom Button', 'job-listings' ),
		// 			// 	'desc' => __( 'Show the scroll bottom button on the slider.', 'job-listings' ),
		// 			// 	'id'   => "{$prefix}_slider_above_scroll_bottom",
		// 			// 	'type' => 'checkbox',
		// 			// 	'std'  => '',
		// 			// 	// 'child-fields' => array(
		// 			// 	// 		'on' => "{$prefix}_slider_above_scroll_bottom_position,{$prefix}_slider_above_scroll_bottom_color,{$prefix}_slider_above_scroll_bottom_hover_color"
		// 			// 	// 	)
		// 			// 	),
		// 			// array(
		// 			// 	'name'    => __( 'Scroll Bottom Button Position', 'job-listings' ),
		// 			// 	'id'      => "{$prefix}_slider_above_scroll_bottom_position",
		// 			// 	'type'    => 'select',
		// 			// 	'std'     => 'top left',
		// 			// 	'options' => array( 'top left', 'top center', 'top right', 'bottom left', 'bottom center', 'bottom right' )
		// 			// 	),
		// 			// array(
		// 			// 	'name' => __( 'Scroll Bottom Button Color', 'job-listings' ),
		// 			// 	'desc' => __( 'The color of the scroll bottom button.', 'job-listings' ),
		// 			// 	'id'   => "{$prefix}_slider_above_scroll_bottom_color",
		// 			// 	'type' => 'color',
		// 			// 	'std'  => '#ffffff'
		// 			// 	),
		// 			// array(
		// 			// 	'name' => __( 'Scroll Bottom Button Hover Color', 'job-listings' ),
		// 			// 	'desc' => __( 'The hover color of the scroll bottom.', 'job-listings' ),
		// 			// 	'id'   => "{$prefix}_slider_above_scroll_bottom_hover_color",
		// 			// 	'type' => 'color',
		// 			// 	'std'  => '#ffffff'
		// 			// 	)
		// 		)
		// 	);

		// 	$helper->add_meta_box($meta_box);
		// }

		// Page Sidebar
		$meta_box = array(
			'id' => "{$prefix}_meta_box_sidebar",
			'title' => __('Sidebar', 'job-listings'),
			'context'      => 'side',
			'priority'     => 'default',
			'fields' => array(
				array(
					'label' => __('Page Sidebar', 'job-listings') ,
					'id' => "{$prefix}_sidebar",
					'type' => 'sidebars',
					'std' => 'sidebar-main'
				) ,
			)
		);

		$helper->add_meta_box( $meta_box );
	}
endif;

add_action('add_meta_boxes', 'jlt_page_meta_boxes');