<?php

function jlt_et_custom_field( $type, $id, $content ) {
	$fields = array();
	switch ( $type ) {
		case 'job' :
			$fields = jlt_et_job_cf( $id );
			break;
		//		case 'resume' :
		//			$fields = jlt_et_resume_cf( $id );
		//			break;
		case 'application' :
			$fields = jlt_et_application_cf( $id );
			break;
		case $type:
			$fields = apply_filters( 'jlt_et_custom_field_' . $type, $id );
			break;
	}

	return str_replace( array_keys( $fields ), $fields, $content );
}

function jlt_et_job_cf( $job_id ) {
	$fields = jlt_get_job_custom_fields( false, true );

	$arr = array();
	if ( ! empty( $fields ) ) {

		foreach ( $fields as $field ) {
			$value = '';
			if ( isset( $field[ 'is_tax' ] ) ) {
				$value = array();
				$terms = get_the_terms( $job_id, $field[ 'name' ] );
				if ( ! empty( $terms ) && ! is_wp_error( $terms ) ) {
					$terms = ! is_array( $terms ) ? array( $terms ) : $terms;
					foreach ( $terms as $term ) {
						$value[] = $term->name;
					}
				}
				$value           = implode( ', ', $value );
				$field[ 'type' ] = 'text';
			} else {
				$id    = jlt_job_custom_fields_name( $field[ 'name' ], $field );
				$value = jlt_get_post_meta( $job_id, $id, '' );
			}

			if ( ! empty( $value ) ) {
				$arr[ '[' . $field[ 'name' ] . ']' ] = jlt_et_convert_field_value( $field, $value );
			} else {
				$arr[ '[' . $field[ 'name' ] . ']' ] = '';
			}
		}
	}

	return $arr;
}

function jlt_et_application_cf( $application_id ) {
	$fields = jlt_get_application_custom_fields( false, true );

	$arr = array();
	if ( ! empty( $fields ) ) {

		foreach ( $fields as $field ) {
			$value = '';
			$id    = jlt_application_custom_fields_name( $field[ 'name' ], $field );
			if ( $id == 'application_message' ) {
				$value = get_post_field( 'post_content', $application_id );
			} else {
				$value = jlt_get_post_meta( $application_id, $id, '' );
			}

			if ( ! empty( $value ) ) {
				$arr[ '[' . $field[ 'name' ] . ']' ] = jlt_et_convert_field_value( $field, $value );
			} else {
				$arr[ '[' . $field[ 'name' ] . ']' ] = '';
			}
		}
	}

	return $arr;
}

function jlt_et_convert_field_value( $field = array(), $value = '' ) {

	$type      = isset( $field[ 'type' ] ) ? $field[ 'type' ] : '';
	$new_value = ! is_array( $value ) ? trim( $value ) : $value;

	if ( empty( $type ) || empty( $new_value ) ) {
		return '';
	}

	if ( in_array( $type, jlt_multiple_value_field_type() ) ) {
		$field_value = jlt_convert_custom_field_setting_value( $field );

		if ( in_array( $type, array( 'multiple_select', 'checkbox', 'radio' ) ) ) {
			$new_value = ! is_array( $new_value ) ? jlt_json_decode( $new_value ) : $new_value;

			foreach ( $new_value as $index => $v ) {
				if ( empty( $v ) ) {
					unset( $new_value[ $index ] );
				} elseif ( isset( $field_value[ $v ] ) ) {
					$new_value[ $index ] = $field_value[ $v ];
				}
			}
			$new_value = implode( ', ', $new_value );
		} else { // select
			$new_value = is_array( $new_value ) ? reset( $new_value ) : $new_value;
			if ( isset( $field_value[ $new_value ] ) ) {
				$new_value = $field_value[ $new_value ];
			}
		}
	} else {
		$new_value = is_array( $new_value ) ? reset( $new_value ) : $new_value;
	}

	if ( $type == 'datepicker' ) {
		$new_value = date_i18n( get_option( 'date_format' ), $new_value );
	}

	if ( $type == 'file_upload' ) {
		$files     = jlt_json_decode( $value );
		$new_value = array();
		foreach ( $files as $file ) {
			$file_url           = jlt_get_file_upload( $file );
			$new_value[ $file ] = "<a href='" . esc_url( $file_url ) . "' target='_blank' class='link-alt'>" . esc_html( $file ) . "</a>";
		}
	}

	if ( $type == 'single_image' ) {
		$img_tag    = wp_get_attachment_image( $value, $size = 'thumbnail' );
		$image_link = wp_get_attachment_url( $value );

		$new_value = '<a href="' . esc_url( $image_link ) . '" class="jlt-lightbox-item">' . $img_tag . '</a>';
	}

	if ( $type == 'image_gallery' ) {
		$images     = ! is_array( $value ) ? explode( ',', $value ) : $value;
		$new_value  = array();
		$gallery_id = uniqid();
		foreach ( $images as $image ) {
			$img_tag    = wp_get_attachment_image( $image, $size = 'thumbnail' );
			$image_link = wp_get_attachment_url( $image );

			$new_value[] = '<a href="' . esc_url( $image_link ) . '" class="jlt-lightbox-item" data-lightbox-gallery="' . $gallery_id . '" >' . $img_tag . '</a>';
		}
	}

	return apply_filters( 'jlt_et_convert_custom_field_value', $new_value, $field, $value );
}

function jlt_et_get_template( $template, $args = array() ) {
	ob_start();
	jlt_get_template( $template, $args );

	return ob_get_clean();
}

function jlt_et_email_status( $key ) {
	$setting_value = jlt_et_get_setting( $key, '' );

	return isset( $setting_value[ 'active' ] ) ? $setting_value[ 'active' ] : 1;
}