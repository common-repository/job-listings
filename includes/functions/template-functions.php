<?php

if ( ! function_exists( 'jlt_get_template_part' ) ) {
	function jlt_get_template_part( $slug, $name = '' ) {
		$template = '';
		if ( $name ) {
			$template = locate_template( array(
				"{$slug}-{$name}.php",
				JLT_Template_Loader::template_path() . "{$slug}-{$name}.php",
			) );
		}

		if ( ! $template && $name && file_exists( JLT_PLUGIN_DIR . "templates/{$slug}-{$name}.php" ) ) {
			$template = JLT_PLUGIN_DIR . "templates/{$slug}-{$name}.php";
		}

		if ( ! $template ) {
			$template = locate_template( array( "{$slug}.php", JLT_Template_Loader::template_path() . "{$slug}.php" ) );
		}

		$template = apply_filters( 'jlt_get_template_part', $template, $slug, $name );

		if ( $template ) {
			load_template( $template, false );
		}
	}
}

//start setup post type
if ( ! function_exists( 'jlt_setup_job_data' ) ) {
	function jlt_setup_job_data( $post ) {
		unset( $GLOBALS[ 'job' ] );
		if ( is_int( $post ) ) {
			$post = get_post( $post );
		}

		if ( empty( $post->post_type ) || $post->post_type != 'job' ) {
			return;
		}

		$GLOBALS[ 'job' ] = jlt_get_job( $post );

		return $GLOBALS[ 'job' ];
	}

	add_action( 'the_post', 'jlt_setup_job_data' );
}

if ( ! function_exists( 'jlt_setup_company_data' ) ) {
	function jlt_setup_company_data( $post ) {

		unset( $GLOBALS[ 'company' ] );

		if ( is_int( $post ) ) {
			$post = get_post( $post );
		}

		if ( empty( $post->post_type ) || $post->post_type != 'company' ) {
			return;
		}

		$GLOBALS[ 'company' ] = jlt_get_company( $post );

		return $GLOBALS[ 'company' ];
	}

	add_action( 'the_post', 'jlt_setup_company_data' );
}

//end setup post type

if ( ! function_exists( 'jlt_get_template' ) ) {
	function jlt_get_template( $template_name, $args = array(), $template_path = '', $default_path = '' ) {
		if ( ! empty( $args ) && is_array( $args ) ) {
			extract( $args );
		}

		$located = jlt_locate_template( $template_name, $template_path, $default_path );

		$located = apply_filters( 'jlt_get_template', $located, $template_name, $args, $template_path, $default_path );

		include( $located );
	}
}

if ( ! function_exists( 'jlt_locate_template' ) ) {
	function jlt_locate_template( $template_name, $template_path = '', $default_path = '' ) {
		if ( ! $template_path ) {
			$template_path = JLT_Template_Loader::template_path();
		}

		if ( ! $default_path ) {
			$default_path = JLT_PLUGIN_DIR . 'templates/';
		}

		$template = locate_template( array(
			trailingslashit( $template_path ) . $template_name,
			$template_name,
		) );

		if ( ! $template ) {
			$template = $default_path . $template_name;
		}

		return apply_filters( 'jlt_locate_template', $template, $template_name, $template_path );
	}
}

//Body class
function jlt_body_class( $classes ) {

	$classes   = (array) $classes;
	$classes[] = 'jlt-active';
	$classes[] = 'job-listings';

	if ( is_singular( 'job' ) ) {
		$classes[] = 'jlt-single-job';
	}

	if ( is_singular( 'company' ) ) {
		$classes[] = 'jlt-single-company';
	}
	if ( is_post_type_archive( 'job' ) ) {
		$classes[] = 'jlt-archive';
		$classes[] = 'jlt-archive-job';
	}

	return array_unique( $classes );
}

if ( ! function_exists( 'jlt_is_job_taxonomy' ) ) {
	function jlt_is_job_taxonomy() {
		return is_tax( get_object_taxonomies( 'job' ) );
	}
}

if ( ! function_exists( 'jlt_job_single_title' ) ) {
	function jlt_job_single_title() {
		jlt_get_template( 'job/title.php' );
	}
}

if ( ! function_exists( 'jlt_job_single_company_info' ) ) :

	function jlt_job_single_company_info() {
		$job_company = jlt_get_job_setting( 'job_company', 'yes' );

		if ( 'yes' == $job_company ) {
			jlt_get_template( 'job/job-company.php' );
		}
	}

endif;

if ( ! function_exists( 'jlt_job_single_related' ) ) {
	function jlt_job_single_related() {
		$job_related = jlt_get_job_setting( 'job_related', 'yes' );
		if ( 'yes' == $job_related ) {
			jlt_get_template( 'job/related.php' );
		}
	}
}

if ( ! function_exists( 'jlt_job_single_apply' ) ) {
	function jlt_single_job_apply() {
		jlt_get_template( 'job/apply.php' );
	}
}

if ( ! function_exists( 'jlt_button_apply' ) ) {
	function jlt_button_apply() {
		jlt_get_template( 'job/apply-button.php' );
	}
}

function jlt_job_tag_prefix() {
	return '<label>' . __( 'Tags: ', 'job-listings' ) . '</label>';
}

if ( ! function_exists( 'jlt_job_category_prefix' ) ) {
	function jlt_job_category_prefix() {
		return '<label>' . __( 'Category: ', 'job-listings' ) . '</label>';
	}
}

if ( ! function_exists( 'jlt_job_type_prefix' ) ) {
	function jlt_job_type_prefix() {
		return '<label>' . __( 'Type: ', 'job-listings' ) . '</label>';
	}
}

if ( ! function_exists( 'jlt_job_location_prefix' ) ) {
	function jlt_job_location_prefix() {
		return '<label>' . __( 'Location: ', 'job-listings' ) . '</label>';
	}
}

if ( ! function_exists( 'jlt_form_list_steps' ) ) {
	function jlt_form_list_steps() {
		$steps        = [ ];
		$current_step = isset($_GET['action']) ? sanitize_text_field($_GET['action']) : '';
		if ( jlt_is_job_posting_page() ) {
			$steps        = jlt_get_page_post_job_steps();
		}
		$atts = array(
			'steps'   => $steps,
			'current' => $current_step,
		);
		jlt_get_template( 'global/form-steps.php', $atts );
	}
}

if ( ! function_exists( 'jlt_archive_title' ) ) {
	function jlt_archive_title( $title ) {

		if ( is_post_type_archive( 'job' ) ) {
			$title = apply_filters( 'jlt_job_archive_title', __( 'Job Listings', 'job-listings' ) );
		}

		if ( is_post_type_archive( 'company' ) ) {
			$title = apply_filters( 'jlt_company_archive_title', __( 'Company Listings', 'job-listings' ) );
		}

		if ( is_post_type_archive( 'resume' ) ) {
			$title = apply_filters( 'jlt_resume_archive_title', __( 'Resume Listings', 'job-listings' ) );
		}

		$title = apply_filters( 'jlt_archive_title', $title );

		return $title;
	}
}

if ( ! function_exists( 'jlt_single_company_title' ) ) :

	function jlt_single_company_title() {
		$atts = array(
			'tag' => apply_filters( 'jlt_single_company_title_tag', 'h3' ),
		);
		jlt_get_template( 'employer/company-title.php', $atts );
	}

endif;

if ( ! function_exists( 'jlt_single_company_social_list' ) ) :

	function jlt_single_company_social_list() {
		$atts = array();
		jlt_get_template( 'employer/company-social.php', $atts );
	}

endif;

if ( ! function_exists( 'jlt_single_company_logo' ) ) :

	function jlt_single_company_logo() {
		jlt_get_template( 'employer/company-logo.php' );
	}

endif;

if ( ! function_exists( 'jlt_single_company_meta' ) ) :

	function jlt_single_company_meta() {
		jlt_get_template( 'employer/company-meta.php' );
	}

endif;

if ( ! function_exists( 'jlt_single_company_jobs' ) ) :

	function jlt_single_company_jobs() {
		$per_page = JLT_Company::get_setting( 'number_job', 10 );
		$atts     = array(
			'posts_per_page' => $per_page,
		);
		jlt_get_template( 'employer/company-jobs.php', $atts );
	}

endif;

if ( ! function_exists( 'jlt_single_company_info' ) ) :

	function jlt_single_company_info() {
		$atts = array();
		jlt_get_template( 'employer/company-info.php', $atts );
	}

endif;

if ( ! function_exists( 'jlt_single_job_info' ) ) :

	function jlt_single_job_info() {

		jlt_get_template( 'job/job-fields.php' );
	}

endif;

if ( ! function_exists( 'jlt_single_job_meta' ) ) :

	function jlt_single_job_meta() {

		jlt_get_template( 'job/job-meta.php' );
	}

endif;

if ( ! function_exists( 'jlt_single_job_map' ) ) :

	function jlt_single_job_map() {
		$job_id       = get_the_ID();
		$full_address = get_post_meta( $job_id, '_location_address', true );

		$location_lat  = get_post_meta( $job_id, '_location_lat', true );
		$location_long = get_post_meta( $job_id, '_location_long', true );

		$location = array();

		if ( empty( $location_lat ) or empty( $location_long ) ) {
			if ( ! empty( $full_address ) ) {
				$location = jlt_location_geo( $full_address );
			} else {
				return;
			}
		} else {
			$location[ 'lat' ]  = ! empty( $location_lat ) ? $location_lat : '';
			$location[ 'long' ] = ! empty( $location_long ) ? $location_long : '';
		}

		$enable_location_map = jlt_get_job_setting( 'enable_location_map', 1 );
		$enable_location_map = !empty($location) && $enable_location_map;

		$attrs = array(
			'show_map'     => $enable_location_map,
			'location'     => $location,
			'full_address' => $full_address,
		);

		jlt_get_template( 'job/job-location-map.php', $attrs );
	}

endif;

if ( ! function_exists( 'jlt_single_company_map' ) ) :

	function jlt_single_company_map() {
		$show_map = JLT_Company::get_setting( 'show_map', 1 );
		$address  = jlt_get_post_meta( get_the_ID(), '_address', true );

//		$location_term = ! empty( $address ) ? get_term_by( 'id', $address, 'job_location' ) : '';
//		$location      = ! empty( $location_term ) ? $location_term->name : '';
//
//		$complete_address = jlt_get_post_meta( get_the_ID(), '_location_address', '' );
//		if ( ! empty( $complete_address ) ) {
//			$location = $complete_address . ', ' . $location;
//		}
//		$location = jlt_location_geo( $location );
		$location = jlt_location_geo( $address );

		$atts = array(
			'address'  => $address,
			'location' => $location,
			'show_map' => $show_map,
		);
		jlt_get_template( 'employer/company-map.php', $atts );
	}

endif;

if ( ! function_exists( 'jlt_google_map_icon_maker' ) ) :

	function jlt_google_map_icon_maker() {
		$icon_image = JLT_PLUGIN_URL . 'public/images/map-marker-icon.png';

		return apply_filters( 'jlt_google_map_icon_maker', $icon_image );
	}

endif;

if ( ! function_exists( 'jlt_google_map_style' ) ) :

	function jlt_google_map_style() {
		$map_style = jlt_get_location_setting( 'google_map_style', 'apple' );

		return $map_style;
	}

endif;

if ( ! function_exists( 'jlt_google_map_height' ) ) :

	function jlt_google_map_height() {
		$map_height = jlt_get_location_setting( 'google_map_height', '400' );

		return $map_height;
	}

endif;

//Job Loop

if ( ! function_exists( 'jlt_job_loop_meta' ) ) :

	function jlt_job_loop_meta() {
		jlt_get_template( 'job/loop/meta.php' );
	}

endif;

if ( ! function_exists( 'jlt_job_loop_company_logo' ) ) :

	function jlt_job_loop_company_logo() {
		jlt_get_template( 'job/loop/company-logo.php' );
	}

endif;
if ( ! function_exists( 'jlt_job_loop_action' ) ) :

	function jlt_job_loop_action() {
		jlt_get_template( 'job/loop/action.php' );
	}

endif;

//Company loop


if ( ! function_exists( 'jlt_company_loop_meta' ) ) :

	function jlt_company_loop_meta() {
		jlt_get_template( 'employer/loop/meta.php' );
	}

endif;

if ( ! function_exists( 'jlt_company_loop_logo' ) ) :

	function jlt_company_loop_logo() {
		jlt_get_template( 'employer/loop/logo.php' );
	}

endif;
if ( ! function_exists( 'jlt_company_loop_action' ) ) :

	function jlt_company_loop_action() {
		jlt_get_template( 'employer/loop/action.php' );
	}

endif;

//Profile

if ( ! function_exists( 'jlt_profile_email_form' ) ) :

	function jlt_profile_email_form() {
		echo JLT_Member::get_update_email_form();
	}

endif;

if ( ! function_exists( 'jlt_profile_password_form' ) ) :

	function jlt_profile_password_form() {
		echo JLT_Member::get_update_password_form();
	}

endif;

if ( ! function_exists( 'jlt_popup_ajax_flag' ) ) :

	function jlt_popup_ajax_flag() {
		echo '<div id="jlt-popup-ajax" class="mfp-hide"></div>';
	}

endif;
//Job search

if ( ! function_exists( 'jlt_job_search_form' ) ) :

	function jlt_job_search_form() {
		$args = array();

		jlt_get_template( 'job/search-form.php', $args );
	}

endif;

/**
 * Show paging
 */
if ( ! function_exists( 'jlt_show_paging' ) ) :

	function jlt_show_paging( $query ) {
		jlt_pagination( array(), $query );
	}
endif;

/**
 * Member Menu
 */
if ( ! function_exists( 'jlt_get_member_menu_html' ) ) :

	function jlt_get_member_menu_html( $items, $args ) {

		$show_member_menu = JLT_Member::get_setting( 'show_member_menu', false );
		$on_menu          = JLT_Member::get_setting( 'member_page_on_menu', '' );

		if ( $show_member_menu && ! empty( $on_menu ) ) {
			if ( $args->menu->term_id == $on_menu ) {
				ob_start();
				jlt_get_template( 'member/user-menu.php' );
				$items .= ob_get_clean();
			}
		}

		return $items;
	}

	add_filter( 'wp_nav_menu_items', 'jlt_get_member_menu_html', 10, 2 );
endif;