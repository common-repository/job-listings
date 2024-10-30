<?php

function jlt_get_candidate_default_fields() {
	$default_fields = array(
		'full_name'       => array(
			'name'         => 'full_name',
			'label'        => __( 'Full Name', 'job-listings' ),
			'type'         => 'text',
			'allowed_type' => array(
				'text' => __( 'Text', 'job-listings' ),
			),
			'value'        => '',
			'is_default'   => true,
			'required'     => true,
		),
		'email'           => array(
			'name'         => 'email',
			'label'        => __( 'Email', 'job-listings' ),
			'type'         => 'text',
			'allowed_type' => array(
				'email' => __( 'Email', 'job-listings' ),
			),
			'value'        => '',
			'is_default'   => true,
			'required'     => true,
		),
		'current_job'     => array(
			'name'        => 'current_job',
			'label'       => __( 'Current Job', 'job-listings' ),
			'type'        => 'text',
			'value'       => '',
			'is_default'  => true,
			'is_disabled' => 'yes',
			'required'    => false,
		),
		'current_company' => array(
			'name'        => 'current_company',
			'label'       => __( 'Current Company', 'job-listings' ),
			'type'        => 'text',
			'value'       => '',
			'is_default'  => true,
			'is_disabled' => 'yes',
			'required'    => false,
		),
		'birthday'        => array(
			'name'         => 'birthday',
			'label'        => __( 'Birthday', 'job-listings' ),
			'type'         => 'datepicker',
			'allowed_type' => array(
				'datepicker' => __( 'Date Picker', 'job-listings' ),
			),
			'value'        => '',
			'is_default'   => true,
			'required'     => false,
		),
		'address'         => array(
			'name'       => 'address',
			'label'      => __( 'Address', 'job-listings' ),
			'type'       => 'text',
			'value'      => '',
			'is_default' => true,
			'required'   => false,
		),
		'phone'           => array(
			'name'       => 'phone',
			'label'      => __( 'Phone Number', 'job-listings' ),
			'type'       => 'text',
			'value'      => '',
			'is_default' => true,
			'required'   => false,
		),
		'first_name'      => array(
			'name'        => 'first_name',
			'label'       => __( 'First Name', 'job-listings' ),
			'type'        => 'text',
			'value'       => '',
			'is_default'  => true,
			'is_disabled' => 'yes',
			'required'    => true,
		),
		'last_name'       => array(
			'name'        => 'last_name',
			'label'       => __( 'Last Name', 'job-listings' ),
			'type'        => 'text',
			'value'       => '',
			'is_default'  => true,
			'is_disabled' => 'yes',
			'required'    => true,
		),
	);

	return apply_filters( 'jlt_candidate_default_fields', $default_fields );
}

function jlt_candidate_user_field_params( $args = array() ) {
	$current_user = wp_get_current_user();
	if ( empty( $current_user->ID ) ) {
		return $args;
	}

	extract( $args );

	if ( in_array( $field[ 'name' ], array( 'first_name', 'last_name', 'full_name', 'email' ) ) ) {

		if ( $field[ 'name' ] == 'first_name' ) {
			$value = $current_user->user_firstname;
		} elseif ( $field[ 'name' ] == 'last_name' ) {
			$value = $current_user->user_lastname;
		} elseif ( $field[ 'name' ] == 'email' ) {
			$value = $current_user->user_email;
		} elseif ( $field[ 'name' ] == 'full_name' ) {
			$value = $current_user->display_name;
		}

		$field[ 'no_translate' ] = true;

		if ( empty( $field[ 'type' ] ) || $field[ 'type' ] == 'text' ) {
			$default_fields  = jlt_get_candidate_default_fields();
			$field[ 'type' ] = $default_fields[ $field[ 'name' ] ][ 'type' ];
		}
	}

	return compact( 'field', 'field_id', 'value' );
}

add_filter( 'jlt_candidate_render_form_field_params', 'jlt_candidate_user_field_params', 10 );
