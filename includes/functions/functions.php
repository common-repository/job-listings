<?php

function jlt_get_endpoint_url( $endpoint, $value = '', $permalink = '' ) {
	if ( ! $permalink ) {
		$permalink = get_permalink();
	}

	if ( get_option( 'permalink_structure' ) ) {
		if ( strstr( $permalink, '?' ) ) {
			$query_string = '?' . parse_url( $permalink, PHP_URL_QUERY );
			$permalink    = current( explode( '?', $permalink ) );
		} else {
			$query_string = '';
		}
		$url = trailingslashit( $permalink ) . $endpoint . '/' . $value . $query_string;
	} else {
		$url = esc_url_raw( add_query_arg( $endpoint, $value, $permalink ) );
	}

	return apply_filters( 'jlt_get_endpoint_url', $url, $endpoint );
}

function jlt_current_url( $encoded = false ) {
	global $wp;
	$current_url = esc_url( add_query_arg( $_SERVER[ 'QUERY_STRING' ], '', home_url( $wp->request ) ) );
	if ( $encoded ) {
		return urlencode( $current_url );
	}

	return $current_url;
}

function jlt_address_to_lng_lat( $address ) {

	$geo = file_get_contents( 'http://maps.googleapis.com/maps/api/geocode/json?address=' . urlencode( $address ) . '&sensor=false' );
	$geo = json_decode( $geo, true );

	if ( $geo[ 'status' ] == 'OK' ) {
		$location[ 'lat' ]  = $geo[ 'results' ][ 0 ][ 'geometry' ][ 'location' ][ 'lat' ];
		$location[ 'long' ] = $geo[ 'results' ][ 0 ][ 'geometry' ][ 'location' ][ 'lng' ];

		return $location;
	}

	return '';
}

function jlt_get_post_views( $id ) {
	$key_meta = '_jlt_views_count';
	$count    = jlt_get_post_meta( $id, $key_meta );
	if ( $count == '' ) :
		delete_post_meta( $id, $key_meta );
		add_post_meta( $id, $key_meta, '0' );

		return 0;
	endif;

	return $count;
}

function jlt_get_page_id_by_template( $page_template = '' ) {
	global $page_id_by_template;
	if ( empty( $page_id_by_template ) || ! isset( $page_id_by_template[ $page_template ] ) ) {
		$pages = get_pages( array(
			'meta_key'   => '_wp_page_template',
			'meta_value' => $page_template,
		) );

		if ( $pages ) {
			$page_id                               = $pages[ 0 ]->ID;
			$page_id_by_template[ $page_template ] = $page_id;
		} else {
			$page_id_by_template[ $page_template ] = false;
		}
	}

	return $page_id_by_template[ $page_template ];
}

function jlt_mail_set_html_content() {
	return 'text/html';
}

function jlt_mail_do_not_reply() {
	$sitename = strtolower( $_SERVER[ 'SERVER_NAME' ] );
	if ( substr( $sitename, 0, 4 ) === 'www.' ) {
		$sitename = substr( $sitename, 4 );
	}

	return apply_filters( 'jlt_mail_do_not_reply', 'noreply@' . $sitename );
}

function jlt_check_woocommerce_active() {
	include_once( ABSPATH . 'wp-admin/includes/plugin.php' );

	return is_plugin_active( 'woocommerce/woocommerce.php' );
}

function jlt_check_allow_register() {

	return apply_filters( 'jlt_allow_register', JLT_Member::get_setting( 'allow_register', 'both' ) );
}

function jlt_term_of_use_link() {
	$term_page        = JLT_Member::get_setting( 'term_page_id' );
	$term_of_use_link = ! empty( $term_page ) ? esc_url( apply_filters( 'jlt_term_url', get_permalink( $term_page ) ) ) : '';

	return $term_of_use_link;
}

function jlt_list_pages( $args = array() ) {
	$pages      = get_pages( $args );
	$pages_list = array();
	foreach ( $pages as $page ) {
		$page_title = $page->post_title;
		if ( empty( $page->post_title ) ) {
			$page_title = __( 'Page #', 'job-listings' ) . $page->ID;
		}
		$pages_list[ $page->ID ] = $page_title;
	}

	return $pages_list;
}

function jlt_is_logged_in() {

	require_once( ABSPATH . "wp-includes/pluggable.php" );

	return is_user_logged_in();
}

function jlt_is_employer( $user_id = null ) {
	return JLT_Member::is_employer( $user_id );
}

function jlt_is_candidate( $user_id = '' ) {
	return JLT_Member::is_candidate( $user_id );
}

function jlt_mail_log( $mail_result ) {
	if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
		error_log( 'Email Log ' . $mail_result );
	}
}
function jlt_company_is_featured( $company_id = 0 ) {
	$company_id   = ! empty( $company_id ) ? $company_id : get_the_ID();
	$featured = jlt_get_post_meta( $company_id, '_company_featured' );
	if ( 'yes' == $featured ) {
		return true;
	} else {
		return false;
	}
}