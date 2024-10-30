<?php
/**
 * Job Custom Field
 *
 * @since 1.0.0
 *
 */

function jlt_get_job_custom_fields( $include_disabled_fields = false, $suppress_filters = false ) {
	$custom_fields = jlt_get_custom_fields( 'jlt_job_custom_field', 'jlt_job_field_' );

	$default_fields = jlt_get_job_default_fields();

	$custom_fields = jlt_merge_custom_fields( $default_fields, $custom_fields, $include_disabled_fields );

	return $suppress_filters ? $custom_fields : apply_filters( 'jlt_job_custom_fields', $custom_fields );
}

function jlt_get_job_search_custom_fields() {
	$custom_fields   = jlt_get_job_custom_fields();
	$date_field      = array(
		'name'       => 'date',
		'type'       => 'datepicker',
		'label'      => __( 'Publishing Date', 'job-listings' ),
		'is_default' => true,
	);
	$custom_fields[] = $date_field;
	$not_searchable  = jlt_not_searchable_custom_fields_type();
	foreach ( $custom_fields as $key => $field ) {
		if ( in_array( $field[ 'type' ], $not_searchable ) ) {
			unset( $custom_fields[ $key ] );
		}
	}

	return apply_filters( 'jlt_job_search_custom_fields', $custom_fields );
}

function jlt_job_custom_fields_prefix() {
	return apply_filters( 'jlt_job_custom_fields_prefix', '_jlt_job_field_' );
}

function jlt_job_custom_fields_name( $field_name = '', $field = array() ) {
	if ( empty( $field_name ) ) {
		return '';
	}

	$cf_name = jlt_job_custom_fields_prefix() . sanitize_title( $field_name );

	if ( ! empty( $field ) && isset( $field[ 'is_default' ] ) ) {
		$cf_name = $field[ 'name' ];
	}

	return apply_filters( 'jlt_job_custom_fields_name', $cf_name, $field_name, $field );
}

function jlt_get_job_field( $field_name = '' ) {

	$custom_fields = jlt_get_job_custom_fields();
	if ( isset( $custom_fields[ $field_name ] ) ) {
		return $custom_fields[ $field_name ];
	}

	foreach ( $custom_fields as $field ) {
		if ( $field_name == $field[ 'name' ] ) {
			return $field;
		}
	}

	return array();
}

function jlt_get_job_custom_fields_option( $key = '', $default = null ) {
	$custom_fields = jlt_get_setting( 'jlt_job_custom_field', array() );

	if ( ! $custom_fields || ! is_array( $custom_fields ) ) {
		return $default;
	}

	if ( isset( $custom_fields[ '__options__' ] ) && isset( $custom_fields[ '__options__' ][ $key ] ) ) {

		return $custom_fields[ '__options__' ][ $key ];
	}

	return $default;
}

function jlt_job_cf_settings_tabs( $tabs = array() ) {
	$temp1 = array_slice( $tabs, 0, 1 );
	$temp2 = array_slice( $tabs, 1 );

	$job_cf_tab = array( 'job' => __( 'Job', 'job-listings' ) );

	return array_merge( $temp1, $job_cf_tab, $temp2 );
}

add_filter( 'jlt_custom_field_setting_tabs', 'jlt_job_cf_settings_tabs', 5 );

function jlt_job_custom_fields_setting() {
	wp_enqueue_style( 'jlt-custom-fields' );
	wp_enqueue_script( 'jlt-custom-fields' );

	jlt_custom_fields_setting( 'jlt_job_custom_field', 'jlt_job_field_', jlt_get_job_custom_fields( true ) );
	?>
	<?php do_action( 'jlt_job_custom_fields_setting_options' );
}

add_action( 'jlt_custom_field_setting_job', 'jlt_job_custom_fields_setting' );

function jlt_job_render_form_field( $field = array(), $job_id = 0 ) {

	$field_id = jlt_job_custom_fields_name( $field[ 'name' ], $field );
	if ( ! empty( $field[ 'remove_prefix' ] ) ) {
		$field_id = esc_attr( $field[ 'name' ] );
	}
	if ( in_array( $field[ 'name' ], array( 'position' ) ) ) {
		$value = $field[ 'value' ];
	} else {
		$value = ! empty( $job_id ) ? jlt_get_post_meta( $job_id, $field_id, '' ) : '';
		$value = isset( $_REQUEST[ $field_id ] ) ? $_REQUEST[ $field_id ] : $value;
		$value = ! is_array( $value ) ? trim( $value ) : $value;
	}

	$params = apply_filters( 'jlt_job_render_form_field_params', compact( 'field', 'field_id', 'value' ), $job_id );
	extract( $params );
	$object = array( 'ID' => $job_id, 'type' => 'post' );

	?>
	<fieldset class="fieldset <?php jlt_custom_field_class( $field, $object ); ?>">

		<label for="<?php echo esc_attr( $field_id ) ?>">
			<?php echo( isset( $field[ 'label_translated' ] ) ? $field[ 'label_translated' ] : $field[ 'label' ] ) ?>
			<?php echo isset( $field[ 'required' ] ) && $field[ 'required' ] ? '<span class="label-required">' . __( '*', 'job-listings' ) . '</span>' : ''; ?>
		</label>

		<div class="field">
			<?php jlt_render_field( $field, $field_id, $value, '', $object ); ?>
		</div>

	</fieldset>
	<?php
}

function jlt_job_render_search_field( $field = array() ) {
	$field_id = jlt_job_custom_fields_name( $field[ 'name' ], $field );

	$field[ 'required' ] = ''; // no need for required fields in search form

	$value = isset( $_GET[ $field_id ] ) ? $_GET[ $field_id ] : '';
	$value = ! is_array( $value ) ? trim( $value ) : $value;

	$params = apply_filters( 'jlt_job_render_search_field_params', compact( 'field', 'field_id', 'value' ) );
	extract( $params );
	?>
	<div class="jlt-search-field <?php echo 'search-' . esc_attr( $field_id ) ?>">
		<label for="<?php echo 'search-' . esc_attr( $field_id ) ?>"
		       class="control-label"><?php echo( isset( $field[ 'label_translated' ] ) ? $field[ 'label_translated' ] : $field[ 'label' ] ) ?></label>
		<div class="jlt-search-control">
			<?php
			jlt_render_field( $field, $field_id, $value, 'search' );
			?>
		</div>
	</div>
	<?php
}

function jlt_job_advanced_search_field( $field_val = '' ) {
	if ( empty( $field_val ) || $field_val == 'no' ) {
		return '';
	}

	$field_arr = explode( '|', $field_val );
	$field_id  = isset( $field_arr[ 0 ] ) ? $field_arr[ 0 ] : '';

	if ( empty( $field_id ) ) {
		return '';
	}

	$fields = jlt_get_job_search_custom_fields();

	$field_prefix = jlt_job_custom_fields_prefix();
	$field_id     = str_replace( $field_prefix, '', $field_id );

	foreach ( $fields as $field ) {
		if ( sanitize_title( $field[ 'name' ] ) == str_replace( $field_prefix, '', $field_id ) ) {
			jlt_job_render_search_field( $field );
			break;
		}
	}

	return '';
}

function jlt_job_save_custom_fields( $post_id = 0, $args = array() ) {
	if ( empty( $post_id ) ) {
		return;
	}

	// Update custom fields
	$fields = jlt_get_job_custom_fields();
	if ( ! empty( $fields ) ) {
		foreach ( $fields as $field ) {
			if ( isset( $field[ 'is_tax' ] ) && $field[ 'is_tax' ] ) {
				continue;
			}

			$id = jlt_job_custom_fields_name( $field[ 'name' ], $field );
			if ( isset( $args[ $id ] ) ) {
				jlt_save_field( $post_id, $id, $args[ $id ], $field );
			}
		}
	}
}

function jlt_job_search_cf_options() {

	$job_search_fields = jlt_get_job_custom_fields_option( 'job_search_fields', array() );

	?>
	<table class="form-table" cellspacing="0">
		<tbody>
		<tr class="job-job-search-cf">
			<th>
				<?php _e( 'Custom field include in search', 'job-listings' ); ?>
			</th>
			<td>
				<?php
				$list_fields = jlt_get_job_search_custom_fields(); ?>

				<select class="jlt-admin-chosen" name="jlt_job_custom_field[__options__][job_search_fields][]"
				        multiple="multiple" style="width: 500px;max-width: 100%;">
					<?php foreach ( $list_fields as $key => $field ) :
						?>

						<option <?php selected( in_array( $field[ 'name' ], $job_search_fields ), true ); ?>
							value="<?php echo $field[ 'name' ]; ?>"><?php echo $field[ 'label' ]; ?></option>
					<?php endforeach; ?>
				</select>
				<br/><em><?php echo __( 'Fields selected here will required buying specific Job Package. Please continue edit on Job Package Products.', 'job-listings' ); ?></em>
			</td>
		</tr>
		</tbody>
	</table>
	<?php
}

add_action( 'jlt_job_custom_fields_setting_options', 'jlt_job_search_cf_options' );

function jlt_job_custom_fields_search() {
	$job_search_fields = jlt_get_job_custom_fields_option( 'job_search_fields', array() );

	return apply_filters( 'jlt_job_custom_fields_search', $job_search_fields );
}