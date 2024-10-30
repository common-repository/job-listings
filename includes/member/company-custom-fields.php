<?php

function jlt_get_company_custom_fields_option( $key = '', $default = null ) {
	$custom_fields = jlt_get_setting( 'jlt_company_custom_field', array() );

	if ( ! $custom_fields || ! is_array( $custom_fields ) ) {
		return $default;
	}

	if ( isset( $custom_fields[ '__options__' ] ) && isset( $custom_fields[ '__options__' ][ $key ] ) ) {

		return $custom_fields[ '__options__' ][ $key ];
	}

	return $default;
}

function jlt_get_company_custom_fields( $include_disabled_fields = false, $suppress_filters = false ) {
	$custom_fields = jlt_get_custom_fields( 'jlt_company_custom_field', 'jlt_company_field_' );

	if ( empty( $custom_fields ) ) {
		$custom_fields = jlt_get_custom_fields( 'company', 'jlt_company_field_' );
	}

	$default_fields = jlt_get_company_default_fields();

	$custom_fields = jlt_merge_custom_fields( $default_fields, $custom_fields, $include_disabled_fields );

	return $suppress_filters ? $custom_fields : apply_filters( 'jlt_company_custom_fields', $custom_fields );
}

function jlt_get_company_socials() {
	$socials = jlt_get_company_custom_fields_option( 'socials', 'website,facebook,twitter,linkedin,instagram,googleplus' );
	$socials = ! is_array( $socials ) ? explode( ',', $socials ) : $socials;

	return apply_filters( 'jlt_get_company_socials', $socials );
}

function jlt_company_cf_settings_tabs( $tabs = array() ) {
	$temp1 = array_slice( $tabs, 0, 2 );
	$temp2 = array_slice( $tabs, 2 );

	$resume_cf_tab = array( 'company' => __( 'Company ( Employer )', 'job-listings' ) );

	return array_merge( $temp1, $resume_cf_tab, $temp2 );
}

add_filter( 'jlt_custom_field_setting_tabs', 'jlt_company_cf_settings_tabs' );

function jlt_company_custom_fields_setting() {
	wp_enqueue_style( 'jlt-custom-fields' );
	wp_enqueue_script( 'jlt-custom-fields' );

	if ( function_exists( 'wp_enqueue_media' ) ) {
		wp_enqueue_media();
	} else {
		wp_enqueue_style( 'thickbox' );
		wp_enqueue_script( 'media-upload' );
		wp_enqueue_script( 'thickbox' );
	}
	wp_enqueue_style( 'vendor-chosen-css' );
	wp_enqueue_script( 'vendor-chosen-js' );

	$all_socials  = jlt_get_social_fields();
	$selected_arr = jlt_get_company_socials();

	jlt_custom_fields_setting( 'jlt_company_custom_field', 'jlt_company_field_', jlt_get_company_custom_fields( true ) );
	?>
	<h3><?php echo __( 'Social Fields', 'job-listings' ) ?></h3>
	<table class="form-table" cellspacing="0">
		<tbody>
		<tr>
			<th>
				<?php _e( 'Select Social Networks', 'job-listings' ) ?>
			</th>
			<td>
				<?php if ( $all_socials ): ?>
					<select class="social_list_field" name="jlt_company_custom_field[__options__][socials]"
					        multiple="multiple" style="width: 500px;max-width: 100%;">
						<?php if ( $selected_arr ): ?>
							<?php foreach ( (array) $selected_arr as $index => $key ): ?>
								<?php if ( isset( $all_socials[ $key ] ) ) : ?>
									<option value="<?php echo esc_attr( $key ) ?>"
									        selected><?php echo esc_html( $all_socials[ $key ][ 'label' ] ); ?></option>
									<?php unset( $all_socials[ $key ] ); ?>
								<?php else : unset( $selected_arr[ $index ] ); ?>
								<?php endif; ?>
							<?php endforeach; ?>
						<?php endif; ?>
						<?php foreach ( $all_socials as $key => $social ): ?>
							<option
								value="<?php echo esc_attr( $key ) ?>"><?php echo esc_html( $social[ 'label' ] ); ?></option>
						<?php endforeach; ?>
					</select>
					<input name="jlt_company_custom_field[__options__][socials]" type="hidden"
					       value="<?php echo implode( ',', $selected_arr ); ?>"/>
					<script type="text/javascript">
						jQuery(document).ready(function ($) {
							$("select.social_list_field").chosen({
								placeholder_text_multiple: "<?php echo __( 'Select social networks', 'job-listings' ); ?>"
							});

							$("select.social_list_field").change(function () {
								var $this = $(this);
								var chosen_choices = $this.siblings(".chosen-container");
								var values = [];
								chosen_choices.find('.search-choice > .search-choice-close').each(function (i, el) {
									var index = parseInt($(el).data('option-array-index')) + 1;
									values[i] = $this.find('option:nth-child(' + index + ')').val();
								});

								$(this).siblings('input').val(values.join());
							});
						});
					</script>
					<style type="text/css">
						.chosen-container input[type="text"] {
							height: auto !important;
						}
					</style>
				<?php endif; ?>
			</td>
		</tr>
		</tbody>
	</table>
	<?php do_action( 'jlt_company_custom_fields_setting_options' );
}

add_action( 'jlt_custom_field_setting_company', 'jlt_company_custom_fields_setting' );

function jlt_company_render_form_field( $field = array(), $company_id = 0 ) {
	$field_id = jlt_company_custom_fields_name( $field[ 'name' ], $field );

	$value = ! empty( $company_id ) ? get_post_meta( $company_id, $field_id, true ) : '';
	$value = ! is_array( $value ) ? trim( $value ) : $value;

	$params = apply_filters( 'jlt_company_render_form_field_params', compact( 'field', 'field_id', 'value' ), $company_id );
	extract( $params );
	$object = array( 'ID' => $company_id, 'type' => 'post' );

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

function jlt_company_save_custom_fields( $company_id = 0, $args = array() ) {
	if ( empty( $company_id ) ) {
		return;
	}

	$fields = jlt_get_company_custom_fields();
	if ( $fields ) {
		foreach ( $fields as $field ) {
			$id = jlt_company_custom_fields_name( $field[ 'name' ], $field );

			$value = isset( $args[ $id ] ) ? wp_kses( $args[ $id ], array() ) : '';

			update_post_meta( $company_id, $id, $value );
		}
	}

	$socials = jlt_get_company_socials();
	if ( ! empty( $socials ) ) {
		foreach ( $socials as $social ) {
			$field_id = "_{$social}";
			if ( isset( $args[ $field_id ] ) ) {
				$value = wp_kses( $args[ $field_id ], array() );
				update_post_meta( $company_id, $field_id, $value );
			}
		}
	}
}

function jlt_company_save_complete_address_field( $company_id ) {
	$_location_address = isset( $_POST[ '_location_address' ] ) ? wp_kses( $_POST[ '_location_address' ], array() ) : '';
	update_post_meta( $company_id, '_location_address', $_location_address );
}

function jlt_company_custom_fields_prefix() {
	return apply_filters( 'jlt_company_custom_fields_prefix', '_jlt_company_field_' );
}

function jlt_company_custom_fields_name( $field_name = '', $field = array() ) {
	if ( empty( $field_name ) ) {
		return '';
	}

	$cf_name = jlt_company_custom_fields_prefix() . sanitize_title( $field_name );

	if ( ! empty( $field ) && isset( $field[ 'is_default' ] ) ) {
		$cf_name = $field[ 'name' ];
	}

	return apply_filters( 'jlt_company_custom_fields_name', $cf_name, $field_name, $field );
}

function jlt_get_company_field( $field_name = '' ) {

	$custom_fields = jlt_get_company_custom_fields();
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

function jlt_get_company_field_value( $post_id, $field = array() ) {
	$field[ 'type' ] = isset( $field[ 'type' ] ) ? $field[ 'type' ] : 'text';

	$id = jlt_company_custom_fields_name( $field[ 'name' ], $field );

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