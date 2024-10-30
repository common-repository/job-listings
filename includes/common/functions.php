<?php

function jlt_force_redirect( $location, $status = 302 ) {
	wp_safe_redirect( $location, $status );
	exit;
}

function jlt_get_post_meta( $post_ID = null, $meta_key, $default = null ) {
	$post_ID = empty( $post_ID ) ? get_the_ID() : $post_ID;

	$value = get_post_meta( $post_ID, $meta_key, true );

	// Sanitize for on/off checkbox
	$value = ( $value == 'off' ? false : $value );
	$value = ( $value == 'on' ? true : $value );
	if ( ( $value === null || $value === '' ) && ( $default != null && $default != '' ) ) {
		$value = $default;
	}

	return apply_filters( 'jlt_get_post_meta', $value, $post_ID, $meta_key, $default );
}

function jlt_html_allowed() {
	$allowed_html = array(
		'a'          => array(
			'href'   => array(),
			'target' => array(),
			'title'  => array(),
			'rel'    => array(),
			'class'  => array(),
		),
		'img'        => array(
			'src'   => array(),
			'class' => array(),
		),
		'h1'         => array(
			'class' => array(),
		),
		'h2'         => array(
			'class' => array(),
		),
		'h3'         => array(
			'class' => array(),
		),
		'h4'         => array(
			'class' => array(),
		),
		'h5'         => array(
			'class' => array(),
		),
		'p'          => array(
			'class' => array(),
		),
		'br'         => array(
			'class' => array(),
		),
		'hr'         => array(
			'class' => array(),
		),
		'span'       => array(
			'class' => array(),
		),
		'br'         => array(),
		'em'         => array(),
		'strong'     => array(),
		'small'      => array(),
		'b'          => array(),
		'i'          => array(),
		'u'          => array(),
		'ul'         => array(),
		'ol'         => array(),
		'li'         => array(),
		'blockquote' => array(
			'class' => array(),
		),
		'iframe'     => array(
			'src'   => array(),
			'width' => array(),
		),
	);

	return $allowed_html;
}

function jlt_kses( $name, $is_editor = false ) {
	if ( $is_editor ) {
		$value = wp_kses_post( $name );
	} else {
		$value = wp_kses( $name, jlt_html_allowed() );
	}

	return $value;
}