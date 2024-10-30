<?php
/**
 * Job Default Fields
 *
 * @since 1.0.0
 *
 */

function jlt_get_job_default_fields() {
	$default_fields = array(
		'job_category'            => array(
			'name'         => 'job_category',
			'label'        => __( 'Job Category', 'job-listings' ),
			'is_default'   => true,
			'is_tax'       => true,
			'type'         => 'multiple_select',
			'allowed_type' => array(
				'select'          => __( 'Select', 'job-listings' ),
				'multiple_select' => __( 'Multiple Select', 'job-listings' ),
				'radio'           => __( 'Radio', 'job-listings' ),
				'checkbox'        => __( 'Checkbox', 'job-listings' ),
			),
			'required'     => true,
		),
		'job_type'                => array(
			'name'         => 'job_type',
			'label'        => __( 'Job Type', 'job-listings' ),
			'is_default'   => true,
			'is_tax'       => true,
			'type'         => 'select',
			'allowed_type' => array(
				'select' => __( 'Select', 'job-listings' ),
				'radio'  => __( 'Radio', 'job-listings' ),
			),
			'required'     => true,
		),
		'job_location'            => array(
			'name'         => 'job_location',
			'label'        => __( 'Job Location', 'job-listings' ),
			'is_default'   => true,
			'is_tax'       => true,
			'type'         => 'multi_location_input',
			'allowed_type' => array(
				'multi_location_input'  => __( 'Multiple Location with Input', 'job-listings' ),
				'multi_location'        => __( 'Multiple Location', 'job-listings' ),
				'single_location_input' => __( 'Single Location with Input', 'job-listings' ),
				'single_location'       => __( 'Single Location', 'job-listings' ),
			),
			'required'     => true,
		),
		'_location_address'       => array(
			'name'         => '_location_address',
			'label'        => __( 'Complete Address', 'job-listings' ),
			'is_default'   => true,
			'type'         => 'text',
			'allowed_type' => array(
				'text' => __( 'Text', 'job-listings' ),
			),
			'required'     => true,
		),
		'job_tag'                 => array(
			'name'         => 'job_tag',
			'label'        => __( 'Job Tag', 'job-listings' ),
			'is_default'   => true,
			'is_tax'       => true,
			'type'         => 'multiple_select',
			'allowed_type' => array(
				'select'          => __( 'Select', 'job-listings' ),
				'multiple_select' => __( 'Multiple Select', 'job-listings' ),
				'radio'           => __( 'Radio', 'job-listings' ),
				'checkbox'        => __( 'Checkbox', 'job-listings' ),
			),
			'is_disabled'  => 'yes',
			'required'     => false,
		),
		'_closing'                => array(
			'name'         => '_closing',
			'label'        => __( 'Closing Date', 'job-listings' ),
			'desc'         => __( 'Set a date or leave blank to automatically use the Expired date', 'job-listings' ),
			'is_default'   => true,
			'type'         => 'datepicker',
			'allowed_type' => array(
				'datepicker' => __( 'Date Picker', 'job-listings' ),
			),
			'required'     => false,
		),
		'_custom_application_url' => array(
			'name'         => '_custom_application_url',
			'label'        => __( 'Custom Application link', 'job-listings' ),
			'desc'         => __( 'Custom link to redirect job seekers to when applying for this job.', 'job-listings' ),
			'is_default'   => true,
			'type'         => 'text',
			'allowed_type' => array(
				'text' => __( 'Text', 'job-listings' ),
			),
			'required'     => false,
		),
		'_application_email'      => array(
			'name'         => '_application_email',
			'label'        => __( 'Notification Email', 'job-listings' ),
			'desc'         => __( 'Email to receive application notification. Leave it blank to use Employer\'s profile email.', 'job-listings' ),
			'is_default'   => true,
			'type'         => 'text',
			'allowed_type' => array(
				'text' => __( 'Text', 'job-listings' ),
			),
			'required'     => false,
		),
	);

	return apply_filters( 'jlt_job_default_fields', $default_fields );
}

function jlt_get_job_taxonomies() {
	return apply_filters( 'jlt_job_taxonomies', array( 'job_category', 'job_location', 'job_type', 'job_tag' ) );
}

function jlt_job_tax_field_params( $args = array(), $job_id = 0 ) {
	extract( $args );

	if ( in_array( $field[ 'name' ], array( 'job_category', 'job_location', 'job_type', 'job_tag' ) ) ) {
		$field_id    = str_replace( 'job_', '', $field[ 'name' ] );
		$field_value = array();
		$terms       = get_terms( $field[ 'name' ], array( 'hide_empty' => 0 ) );
		foreach ( $terms as $term ) {
			if ( $field[ 'name' ] == 'job_category' || $field[ 'name' ] == 'job_type' ) {
				$field_value[] = $term->term_id . '|' . $term->name;
			} else {
				$field_value[] = $term->slug . '|' . $term->name;
			}
		}

		$field[ 'value' ]        = $field_value;
		$field[ 'no_translate' ] = true;

		$value = array();
		if ( ! empty( $job_id ) ) {
			if ( $field[ 'name' ] == 'job_category' || $field[ 'name' ] == 'job_type' ) {
				$value = wp_get_object_terms( $job_id, $field[ 'name' ], array( 'fields' => 'ids' ) );
			} else {
				$value = wp_get_object_terms( $job_id, $field[ 'name' ], array( 'fields' => 'slugs' ) );
			}
		}

		if ( empty( $field[ 'type' ] ) || $field[ 'type' ] == 'text' ) {
			$default_fields  = jlt_get_job_default_fields();
			$field[ 'type' ] = $default_fields[ $field[ 'name' ] ][ 'type' ];
		}
	}

	return compact( 'field', 'field_id', 'value' );
}

add_filter( 'jlt_job_render_form_field_params', 'jlt_job_tax_field_params', 10, 2 );

function jlt_job_tax_search_field_params( $args = array(), $job_id = 0 ) {
	extract( $args );

	if ( in_array( $field[ 'name' ], array( 'job_category', 'job_location', 'job_type', 'job_tag' ) ) ) {
		$field_id    = str_replace( 'job_', '', $field[ 'name' ] );
		$field_value = array();
		$terms       = get_terms( $field[ 'name' ], array( 'hide_empty' => 1 ) );

		foreach ( $terms as $term ) {

			$tax_query[] = array(
				'taxonomy' => $field[ 'name' ],
				'field'    => 'slug',
				'terms'    => $term->slug,
			);

			//				Query job count

			//				$args_count[ 'tax_query' ]      = $tax_query;
			//				$args_count[ 'posts_per_page' ] = - 1;

			//				$rs = jlt_get_query_found_posts( $args_count );
			//				wp_reset_query();

			//				Default term count param
			$rs = $term->count;

			$field_value[] = $term->slug . '|' . $term->name . '(' . $rs . ')';
		}

		$field[ 'value' ]        = $field_value;
		$field[ 'no_translate' ] = true;

		if ( isset( $_GET[ $field_id ] ) && ! empty( $_GET[ $field_id ] ) ) {
			$value = $_GET[ $field_id ];
		} else {
			if ( is_tax( $field[ 'name' ] ) ) {
				global $wp_query;
				$term_id = $wp_query->get_queried_object_id();
				$term    = get_term( $term_id, $field[ 'name' ] );
				$value   = ! empty( $term ) && ! is_wp_error( $term ) ? $term->slug : '';
			}
		}

		$value = ! is_array( $value ) ? trim( $value ) : $value;

		//			var_dump($field[ 'value' ]);

		if ( empty( $field[ 'type' ] ) || $field[ 'type' ] == 'text' ) {
			$default_fields  = jlt_get_job_default_fields();
			$field[ 'type' ] = $default_fields[ $field[ 'name' ] ][ 'type' ];
		}
	}

	return compact( 'field', 'field_id', 'value' );
}

add_filter( 'jlt_job_render_search_field_params', 'jlt_job_tax_search_field_params' );

function jlt_job_get_tax_value( $job_id = 0, $field_id = 'job_category' ) {
	if ( empty( $job_id ) ) {
		return array();
	}

	$value = array();
	$terms = get_the_terms( $job_id, $field_id );
	if ( ! empty( $terms ) && ! is_wp_error( $terms ) ) {
		foreach ( $terms as $term ) {
			$value[] = '<a href="' . get_term_link( $term->term_id, $field_id ) . '"><em>' . $term->name . '</em></a>';
		}
	}

	return $value;
}
