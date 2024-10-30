<?php

function jlt_application_admin_init() {
	register_setting( 'jlt_application_custom_field', 'jlt_application_custom_field' );
}

add_filter( 'admin_init', 'jlt_application_admin_init' );

function jlt_get_application_custom_fields( $include_disabled_fields = false, $suppress_filters = false ) {
	$custom_fields = jlt_get_custom_fields( 'jlt_application_custom_field', 'jlt_application_field_' );

	if ( empty( $custom_fields ) ) {
		$custom_fields = jlt_get_custom_fields( 'application', 'jlt_application_field_' );
	}

	$default_fields = jlt_get_application_default_fields();

	$custom_fields = jlt_merge_custom_fields( $default_fields, $custom_fields, $include_disabled_fields );

	return $suppress_filters ? $custom_fields : apply_filters( 'jlt_application_custom_fields', $custom_fields );
}

function jlt_application_cf_settings_tabs( $tabs = array() ) {
	$tabs[ 'application' ] = __( 'Application', 'job-listings' );

	return $tabs;
}

// add to page Custom field (cf) tab.
add_filter( 'jlt_custom_field_setting_tabs', 'jlt_application_cf_settings_tabs' );

function jlt_application_custom_fields_setting() {
	wp_enqueue_style( 'jlt-custom-fields' );
	wp_enqueue_script( 'jlt-custom-fields' );

	if ( function_exists( 'wp_enqueue_media' ) ) {
		wp_enqueue_media();
	} else {
		wp_enqueue_style( 'thickbox' );
		wp_enqueue_script( 'media-upload' );
		wp_enqueue_script( 'thickbox' );
	}

	jlt_custom_fields_setting( 'jlt_application_custom_field', 'jlt_application_field_', jlt_get_application_custom_fields( true ) );
}

add_action( 'jlt_custom_field_setting_application', 'jlt_application_custom_fields_setting' );

function jlt_application_render_apply_form_field( $field = array() ) {
	$field_id = jlt_application_custom_fields_name( $field[ 'name' ], $field );

	$params = apply_filters( 'jlt_application_render_apply_form_field_params', compact( 'field', 'field_id' ) );
	extract( $params );

	?>
	<fieldset class="fieldset <?php jlt_custom_field_class( $field ); ?>">

		<label for="<?php echo esc_attr( $field_id ) ?>">
			<?php echo( isset( $field[ 'label_translated' ] ) ? $field[ 'label_translated' ] : $field[ 'label' ] ) ?>
			<?php echo isset( $field[ 'required' ] ) && $field[ 'required' ] ? '<span class="label-required">' . __( '*', 'job-listings' ) . '</span>' : ''; ?>
		</label>

		<div class="field">
			<?php jlt_render_field( $field, $field_id ); ?>
		</div>

	</fieldset>
	<?php
}

function jlt_application_custom_fields_prefix() {
	return apply_filters( 'jlt_application_custom_fields_prefix', '_jlt_application_field_' );
}

function jlt_application_custom_fields_name( $field_name = '', $field = array() ) {
	if ( empty( $field_name ) ) {
		return '';
	}

	$cf_name = jlt_application_custom_fields_prefix() . sanitize_title( $field_name );

	if ( ! empty( $field ) && isset( $field[ 'is_default' ] ) ) {
		$cf_name = $field[ 'name' ];
	}

	return apply_filters( 'jlt_application_custom_fields_name', $cf_name, $field_name, $field );
}

function jlt_get_application_field_value( $post_id, $field = array() ) {
	$field[ 'type' ] = isset( $field[ 'type' ] ) ? $field[ 'type' ] : 'text';

	$id = jlt_application_custom_fields_name( $field[ 'name' ], $field );

	$value = $post_id ? get_post_meta( $post_id, $id, true ) : '';
	$value = ! is_array( $value ) ? trim( $value ) : $value;
	if ( ! empty( $value ) ) {
		$value = jlt_convert_custom_field_value( $field, $value );
		if ( is_array( $value ) ) {
			$value = implode( ', ', $value );
		}
	}

	return $value;
}