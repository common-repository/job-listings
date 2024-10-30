<?php

function jlt_get_company_default_fields() {
	$default_fields = array(
		'_logo'    => array(
			'name'       => '_logo',
			'label'      => __( 'Company Logo', 'job-listings' ),
			'type'       => 'single_image',
			'value'      => __( 'Recommend size: 160x160px', 'job-listings' ),
			'is_default' => true,
			'required'   => false,
		),
		'_address' => array(
			'name'         => '_address',
			'label'        => __( 'Address', 'job-listings' ),
			'is_default'   => true,
			'type'         => 'text',
			'allowed_type' => array(
				'text' => __( 'Text', 'job-listings' ),
			),
			'required'     => true,
			'value'        => '',
		),
	);

	return apply_filters( 'jlt_company_default_fields', $default_fields );
}

function jlt_company_location_field_params( $args = array(), $company_id = 0 ) {
	extract( $args );
	$location_tax_field_types = array(
		'company_location',
		'single_tax_location',
		'single_tax_location_input',
		'multi_tax_location',
		'multi_tax_location_input',
	);
	if ( in_array( $field[ 'type' ], $location_tax_field_types ) ) {
		$field_id = $field[ 'name' ];

		$field_value = array();
		$terms       = get_terms( 'job_location', array( 'hide_empty' => 0 ) );
		foreach ( $terms as $term ) {
			$field_value[] = $term->term_id . '|' . $term->name;
		}
		$field[ 'value' ]        = $field_value;
		$field[ 'no_translate' ] = true;

		if ( ! empty( $company_id ) ) {
			//				$value = jlt_resume_get_tax_value( $company_id, $field_id );
		}
	}

	return compact( 'field', 'field_id', 'value' );
}

add_filter( 'jlt_company_render_form_field_params', 'jlt_company_location_field_params', 10, 2 );

function jlt_location_render_field_tax_location( $field = array(), $field_id = '', $value = array(), $form_type = '', $object = array() ) {
	?>
	<div id="job_location_field">
		<?php
		$allow_user_input = strpos( $field[ 'type' ], 'input' ) !== false || $field[ 'type' ] == 'company_location';

		$field[ 'type' ] = ( strpos( $field[ 'type' ], 'single' ) !== false || $field[ 'type' ] == 'company_location' ) ? 'select' : 'multiple_select';
		jlt_render_select_field( $field, $field_id, $value, $form_type );

		if ( $form_type != 'search' && $allow_user_input ) {
			jlt_job_add_new_location( 'id' );
		}

		?>
	</div>
	<?php
}

add_filter( 'jlt_render_field_company_location', 'jlt_location_render_field_tax_location', 10, 5 );
add_filter( 'jlt_render_field_single_tax_location', 'jlt_location_render_field_tax_location', 10, 5 );
add_filter( 'jlt_render_field_single_tax_location_input', 'jlt_location_render_field_tax_location', 10, 5 );
add_filter( 'jlt_render_field_multi_tax_location', 'jlt_location_render_field_tax_location', 10, 5 );
add_filter( 'jlt_render_field_multi_tax_location_input', 'jlt_location_render_field_tax_location', 10, 5 );
