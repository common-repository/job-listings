<?php
if ( ! function_exists( 'jlt_render_field' ) ) :
	function jlt_render_field( $field = array(), $field_id = '', $value = '', $form_type = '', $object = array() ) {
		switch ( $field[ 'type' ] ) {
			case "textarea":
				jlt_render_textarea_field( $field, $field_id, $value, $form_type, $object );
				break;
			case "select":
			case "multiple_select":
				jlt_render_select_field( $field, $field_id, $value, $form_type, $object );
				break;
			case "radio" :
				jlt_render_radio_field( $field, $field_id, $value, $form_type, $object );
				break;
			case "checkbox" :
				jlt_render_checkbox_field( $field, $field_id, $value, $form_type, $object );
				break;
			case "text" :
				jlt_render_text_field( $field, $field_id, $value, $form_type, $object );
				break;
			case "password" :
				jlt_render_password_field( $field, $field_id, $value, $form_type, $object );
				break;
			case "number" :
				jlt_render_number_field( $field, $field_id, $value, $form_type, $object );
				break;
			case "email" :
				jlt_render_email_field( $field, $field_id, $value, $form_type, $object );
				break;
			case "url" :
				jlt_render_url_field( $field, $field_id, $value, $form_type, $object );
				break;
			case "hidden" :
				jlt_render_hidden_field( $field, $field_id, $value, $form_type, $object );
				break;
			case "datepicker" :
				jlt_render_datepicker_field( $field, $field_id, $value, $form_type, $object );
				break;
			case "single_image" :
				jlt_render_single_image_field( $field, $field_id, $value, $form_type, $object );
				break;
			case "image_gallery" :
				jlt_render_image_gallery_field( $field, $field_id, $value, $form_type, $object );
				break;
			case "file_upload" :
				jlt_render_file_upload_field( $field, $field_id, $value, $form_type, $object );
				break;
			case "embed_video" :
				jlt_render_embed_video_field( $field, $field_id, $value, $form_type, $object );
				break;
			default :
				do_action( 'jlt_render_field_' . $field[ 'type' ], $field, $field_id, $value, $form_type, $object );
				break;
		}

		if ( $form_type != 'search' && isset( $field[ 'desc' ] ) && ! empty( $field[ 'desc' ] ) ) : ?>
			<em><?php echo esc_html( $field[ 'desc' ] ); ?></em>
		<?php endif;

		do_action( 'jlt_after_render_field', $field, $field_id, $value, $form_type, $object );
	}
endif;
if ( ! function_exists( 'jlt_render_text_field' ) ) :
	function jlt_render_text_field( $field = array(), $field_id = '', $value = '', $form_type = '', $object = array() ) {
		$field_value = jlt_convert_custom_field_setting_value( $field );
		$input_id    = $form_type == 'search' ? 'search-' . $field_id : $field_id;
		$class       = isset( $field[ 'required' ] ) && $field[ 'required' ] ? 'data-validation="required" class="jlt-form-control input-text jlt-form-validate"' : ' class="jlt-form-control input-text"';
		$field_param = array(
			'input_id'    => $input_id,
			'value'       => $value,
			'class'       => $class,
			'field_id'    => $field_id,
			'field_value' => $field_value,
		);
		jlt_get_template( 'form_field/text.php', $field_param );
	}
endif;
if ( ! function_exists( 'jlt_render_password_field' ) ) :
	function jlt_render_password_field( $field = array(), $field_id = '', $value = '', $form_type = '', $object = array() ) {
		$field_value = jlt_convert_custom_field_setting_value( $field );
		$input_id    = $form_type == 'search' ? 'search-' . $field_id : $field_id;
		$class       = isset( $field[ 'required' ] ) && $field[ 'required' ] ? 'data-validation="required" class="jlt-form-control jlt-form-validate"' : ' class="jlt-form-control input-text"';
		$field_param = array(
			'input_id'    => $input_id,
			'value'       => $value,
			'class'       => $class,
			'field_id'    => $field_id,
			'field_value' => $field_value,
		);
		jlt_get_template( 'form_field/password.php', $field_param );
	}
endif;
if ( ! function_exists( 'jlt_render_number_field' ) ) :
	function jlt_render_number_field( $field = array(), $field_id = '', $value = '', $form_type = '', $object = array() ) {
		$field_value = jlt_convert_custom_field_setting_value( $field );
		$input_id    = $form_type == 'search' ? 'search-' . $field_id : $field_id;
		$class       = isset( $field[ 'required' ] ) && $field[ 'required' ] ? 'data-validation="number" class="jlt-form-control jlt-form-validate"' : ' class="jlt-form-control input-text"';
		$field_param = array(
			'input_id'    => $input_id,
			'value'       => $value,
			'class'       => $class,
			'field_id'    => $field_id,
			'field_value' => $field_value,
		);
		jlt_get_template( 'form_field/number.php', $field_param );
	}
endif;
if ( ! function_exists( 'jlt_render_email_field' ) ) :
	function jlt_render_email_field( $field = array(), $field_id = '', $value = '', $form_type = '', $object = array() ) {
		$field_value = jlt_convert_custom_field_setting_value( $field );
		$input_id    = $form_type == 'search' ? 'search-' . $field_id : $field_id;
		$class       = isset( $field[ 'required' ] ) && $field[ 'required' ] ? 'data-validation="email" class="jlt-form-control jlt-form-validate"' : 'class="jlt-form-control input-text"';
		$disabled    = isset( $field[ 'disabled' ] ) ? 'disabled' : '';
		$field_param = array(
			'input_id'    => $input_id,
			'value'       => $value,
			'class'       => $class,
			'field_id'    => $field_id,
			'disabled'    => $disabled,
			'field_value' => $field_value,
		);
		jlt_get_template( 'form_field/email.php', $field_param );
	}
endif;
if ( ! function_exists( 'jlt_render_url_field' ) ) :
	function jlt_render_url_field( $field = array(), $field_id = '', $value = '', $form_type = '', $object = array() ) {
		$field_value = jlt_convert_custom_field_setting_value( $field );
		$field_value = empty( $field_value ) ? '#' : $field_value;
		$input_id    = $form_type == 'search' ? 'search-' . $field_id : $field_id;
		$class       = isset( $field[ 'required' ] ) && $field[ 'required' ] ? 'data-validation="url" class="jlt-form-control jlt-form-validate"' : ' class="jlt-form-control input-text"';
		$field_param = array(
			'input_id'    => $input_id,
			'value'       => $value,
			'class'       => $class,
			'field_id'    => $field_id,
			'field_value' => $field_value,
		);
		jlt_get_template( 'form_field/url.php', $field_param );
	}
endif;
if ( ! function_exists( 'jlt_render_textarea_field' ) ) :
	function jlt_render_textarea_field( $field = array(), $field_id = '', $value = '', $form_type = '', $object = array() ) {
		$field_value = jlt_convert_custom_field_setting_value( $field );
		$input_id    = $form_type == 'search' ? 'search-' . $field_id : $field_id;
		$class       = isset( $field[ 'required' ] ) && $field[ 'required' ] ? 'data-validation="required" class="jlt-form-control jlt-form-validate"' : ' class="jlt-form-control"';
		$field_param = array(
			'input_id'    => $input_id,
			'value'       => $value,
			'class'       => $class,
			'field_id'    => $field_id,
			'field_value' => $field_value,
		);

		if ( ! empty( $field[ 'tinymce' ] ) ) {
			$class = isset( $field[ 'required' ] ) && $field[ 'required' ] ? 'jlt-editor jlt-editor-required' : 'jlt-editor';
			jlt_wp_editor( $field[ 'value' ], $field[ 'name' ], $field[ 'name' ], $class );
		} else {
			jlt_get_template( 'form_field/textarea.php', $field_param );
		}
	}

endif;
if ( ! function_exists( 'jlt_render_radio_field' ) ) :
	function jlt_render_radio_field( $field = array(), $field_id = '', $value = '', $form_type = '', $object = array() ) {
		$input_id = $form_type == 'search' ? 'search-' . $field_id : $field_id;
		$class    = isset( $field[ 'required' ] ) && $field[ 'required' ] ? 'data-validation="required" class="jlt-form-control jlt-form-validate"' : ' class="jlt-form-control"';

		$field_value = jlt_convert_custom_field_setting_value( $field );
		if ( $form_type == 'search' ) {
			$field_value + array( '' => __( 'All', 'job-listings' ) ) + $field_value;
		}
		$value = is_array( $value ) ? reset( $value ) : $value;
		foreach ( $field_value as $key => $label ) :
			$checked     = ( $key == $value ) ? 'checked="checked"' : '';
			$field_param = array(
				'input_id'    => $input_id,
				'value'       => $value,
				'class'       => $class,
				'field_id'    => $field_id,
				'field_value' => $field_value,
				'checked'     => $checked,
				'key'         => $key,
				'label'       => $label,
			);
			jlt_get_template( 'form_field/radio.php', $field_param );
		endforeach;
	}

endif;
if ( ! function_exists( 'jlt_render_checkbox_field' ) ) :
	function jlt_render_checkbox_field( $field = array(), $field_id = '', $value = '', $form_type = '', $object = array() ) {
		$input_id = $form_type == 'search' ? 'search-' . $field_id : $field_id;
		$class    = isset( $field[ 'required' ] ) && $field[ 'required' ] ? 'data-validation="required" data-validation="checkbox_group" class="jlt-form-control jlt-checkbox jlt-form-validate"' : ' class="jlt-form-control jlt-checkbox"';

		if ( ! is_array( $value ) ) {
			$value = jlt_json_decode( $value );
		}
		$field_value = jlt_convert_custom_field_setting_value( $field );

		foreach ( $field_value as $key => $label ) :
			$checked     = in_array( $key, $value ) ? 'checked="checked"' : '';
			$field_param = array(
				'input_id'    => $input_id,
				'value'       => $value,
				'class'       => $class,
				'field_id'    => $field_id,
				'field_value' => $field_value,
				'checked'     => $checked,
				'key'         => $key,
				'label'       => $label,
			);
			jlt_get_template( 'form_field/checkbox.php', $field_param );
		endforeach;
	}

endif;
if ( ! function_exists( 'jlt_render_select_field' ) ) :
	function jlt_render_select_field( $field = array(), $field_id = '', $value = '', $form_type = '', $object = array() ) {
		$input_id           = $form_type == 'search' ? 'search-' . $field_id : $field_id;
		$is_multiple_select = isset( $field[ 'type' ] ) && $field[ 'type' ] === 'multiple_select';

		$label = isset( $field[ 'label_translated' ] ) ? $field[ 'label_translated' ] : $field[ 'label' ];
		$value = ( $is_multiple_select && ! is_array( $value ) ) ? jlt_json_decode( $value ) : $value;

		$field_value = jlt_convert_custom_field_setting_value( $field );
		$placeholder = $form_type != 'search' ? sprintf( __( "Select %s", 'job-listings' ), $label ) : sprintf( __( "All %s", 'job-listings' ), $label );
		if ( ! $is_multiple_select ) {
			$field_value = array( '' => $placeholder ) + $field_value;
		}

		$is_chosen = $is_multiple_select || ( count( $field_value ) > 10 );
		$is_chosen = apply_filters( 'jlt_select_field_is_chosen', $is_chosen, $field, $field_id );

		$rtl_class    = is_rtl() && $is_chosen ? ' chosen-rtl' : '';
		$chosen_class = $is_chosen ? ' jlt-form-control-chosen ignore-valid' : '';
		$chosen_class .= isset( $field[ 'required' ] ) && $field[ 'required' ] ? ' jform-chosen-validate' : '';

		$attrs = isset( $field[ 'required' ] ) && $field[ 'required' ] ? 'data-validation="required" class="jlt-form-control' . $rtl_class . $chosen_class . '" ' : ' class="jlt-form-control ' . $rtl_class . $chosen_class . '"';

		$field_param = array(
			'input_id'           => $input_id,
			'value'              => $value,
			'field_id'           => $field_id,
			'field_value'        => $field_value,
			'label'              => $label,
			'is_multiple_select' => $is_multiple_select,
			'placeholder'        => $placeholder,
			'attrs'              => $attrs,
		);
		jlt_get_template( 'form_field/select.php', $field_param );
	}
endif;
if ( ! function_exists( 'jlt_render_hidden_field' ) ) :
	function jlt_render_hidden_field( $field = array(), $field_id = '', $value = '', $form_type = '', $object = array() ) {
		$field_value = jlt_convert_custom_field_setting_value( $field );
		$input_id    = $form_type == 'search' ? 'search-' . $field_id : $field_id;
		$field_param = array(
			'input_id'    => $input_id,
			'value'       => $value,
			'field_id'    => $field_id,
			'field_value' => $field_value,
		);
		jlt_get_template( 'form_field/hidden.php', $field_param );
	}
endif;
if ( ! function_exists( 'jlt_render_datepicker_field' ) ) :
	function jlt_render_datepicker_field( $field = array(), $field_id = '', $value = '', $form_type = '', $object = array() ) {
		$class = isset( $field[ 'required' ] ) && $field[ 'required' ] ? ' class="jlt-form-control jlt-form-datepicker input-text jlt-form-validate" readonly ' : ' class="jlt-form-control jlt-form-datepicker input-text"';

		if ( $form_type != 'search' ) :
			$date_value  = is_numeric( $value ) ? date_i18n( get_option( 'date_format' ), $value ) : $value;
			$value       = is_numeric( $value ) ? $value : strtotime( $value );
			$field_param = array(
				'value'      => $value,
				'field_id'   => $field_id,
				'date_value' => $date_value,
				'class'      => $class,
			);
			jlt_get_template( 'form_field/datepicker.php', $field_param );
		else :
			$_start      = isset( $_GET[ $field_id . '_start' ] ) ? $_GET[ $field_id . '_start' ] : '';
			$_start_date = is_numeric( $_start ) ? date_i18n( get_option( 'date_format' ), $_start ) : $_start;
			$_start      = is_numeric( $_start ) ? $_start : strtotime( $_start );

			$_end      = isset( $_GET[ $field_id . '_end' ] ) ? $_GET[ $field_id . '_end' ] : '';
			$_end_date = is_numeric( $_end ) ? date_i18n( get_option( 'date_format' ), $_end ) : $_end;
			$_end      = is_numeric( $_end ) ? $_end : strtotime( $_end );

			$field_param = array(
				'field_id'    => $field_id,
				'_start_date' => $_start_date,
				'_end_date'   => $_end_date,
				'_start'      => $_start,
				'_end'        => $_end,
			);
			jlt_get_template( 'form_field/datepicker_search.php', $field_param );
		endif;
	}
endif;
if ( ! function_exists( 'jlt_render_embed_video_field' ) ) :
	function jlt_render_embed_video_field( $field = array(), $field_id = '', $value = '', $form_type = '', $object = array() ) {
		$field_value = jlt_convert_custom_field_setting_value( $field );
		$input_id    = $form_type == 'search' ? 'search-' . $field_id : $field_id;
		$class       = isset( $field[ 'required' ] ) && $field[ 'required' ] ? ' class="jlt-form-control jlt-form-validate"' : ' class="jlt-form-control"';
		$placeholder = ! empty( $field_value ) ? $field_value : __( 'Youtube or Vimeo link', 'job-listings' );
		$field_param = array(
			'input_id'    => $input_id,
			'value'       => $value,
			'class'       => $class,
			'field_id'    => $field_id,
			'field_value' => $field_value,
			'placeholder' => $placeholder,
		);
		jlt_get_template( 'form_field/embed_video.php', $field_param );
	}
endif;
if ( ! function_exists( 'jlt_render_single_image_field' ) ) :
	function jlt_render_single_image_field( $field = array(), $field_id = '', $value = '', $form_type = '', $object = array() ) {
		jlt_image_upload_form_field( $field_id, $value, false );
	}
endif;
if ( ! function_exists( 'jlt_render_image_gallery_field' ) ) :
	function jlt_render_image_gallery_field( $field = array(), $field_id = '', $value = '', $form_type = '', $object = array() ) {
		jlt_image_upload_form_field( $field_id, $value, true );
	}
endif;
if ( ! function_exists( 'jlt_render_file_upload_field' ) ) :
	function jlt_render_file_upload_field( $field = array(), $field_id = '', $value = '', $form_type = '', $object = array() ) {
		$file_exts    = ! empty( $field[ 'value' ] ) ? $field[ 'value' ] : 'pdf,doc,docx';
		$allowed_exts = jlt_upload_convert_extension_list( $file_exts );
		?>
		<div class="jlt-form-control-file">
			<div class="jlt-upload">
				<?php jlt_file_upload_form_field( $field_id, $allowed_exts, $value, false, $field ) ?>
			</div>
		</div>
		<?php
	}
endif;

if ( ! function_exists( 'jlt_render_form_field' ) ) :
	function jlt_render_form_field( $field = array(), $job_id = 0 ) {
		$field_id    = esc_attr( $field[ 'name' ] );
		$field_value = $field[ 'value' ];

		$value = ! empty( $field_value ) ? $field_value : '';
		$value = ! is_array( $value ) ? trim( $value ) : $value;

		$params = apply_filters( 'jlt_job_render_form_field_params', compact( 'field', 'field_id', 'value' ), $job_id );
		extract( $params );
		$object = array( 'ID' => $job_id, 'type' => 'post' );

		?>
		<fieldset class="fieldset">

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
endif;