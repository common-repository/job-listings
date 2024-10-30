<?php

function jlt_register_upload_script() {

	// -- js plupload
	wp_register_script( 'jlt_plupload', JLT_PLUGIN_URL . 'public/js/jlt-plupload.js', array(
		'jquery',
		'plupload-all',
	), null, true );

	$jlt_plupload = array(
		'ajaxurl'    => admin_url( 'admin-ajax.php' ),
		'remove'     => wp_create_nonce( 'jlt-plupload-remove' ),
		'confirmMsg' => __( 'Are you sure you want to delete this?', 'job-listings' ),
	);
	wp_localize_script( 'jlt_plupload', 'Jl_PluploadL10n', $jlt_plupload );

	// -- js upload
	wp_register_script( 'jlt-upload', JLT_PLUGIN_URL . 'public/js/jlt.function.upload.js', array(
		'jquery',
		'plupload-all',
	), null, true );

	$jltUpload = array(
		'url'           => esc_url_raw( add_query_arg( array(
			'action' => 'jlt_upload',
			'nonce'  => wp_create_nonce( 'aaiu_allow' ),
		), admin_url( 'admin-ajax.php' ) ) ),
		'delete_url'    => esc_url_raw( add_query_arg( array(
			'action' => 'jlt_delete_attachment',
			'nonce'  => wp_create_nonce( 'aaiu_remove' ),
		), admin_url( 'admin-ajax.php' ) ) ),
		'flash_swf_url' => includes_url( 'js/plupload/plupload.flash.swf' ),
		'remove_txt'    => __( 'Remove File', 'job-listings' ),
		'remove_icon'   => apply_filters( 'jlt_file_delete_icon', 'jlt-icon jltfa-trash-o' ),
	);
	wp_localize_script( 'jlt-upload', 'jltUpload', $jltUpload );

	wp_register_script( 'jlt-member', JLT_PLUGIN_URL . 'public/js/member.js', array( 'jquery' ), null, true );
	$jltMemberL10n = array(
		'ajax_security'          => wp_create_nonce( 'jlt-member-security' ),
		'ajax_url'               => admin_url( 'admin-ajax.php', 'relative' ),
		'confirm_not_agree_term' => __( 'Please agree with the Terms of use', 'job-listings' ),
		'confirm_delete'         => __( 'Are you sure to delete this job?', 'job-listings' ),
		'loadingmessage'         => '<i class="fa fa-spinner fa-spin"></i> ' . __( 'Sending info, please wait...', 'job-listings' ),
	);
	wp_localize_script( 'jlt-member', 'jltMemberL10n', $jltMemberL10n );
	wp_enqueue_script( 'jlt-member' );
}

add_action( 'wp_enqueue_scripts', 'jlt_register_upload_script' );

function jlt_image_upload_form_field( $field_name = '', $value = '', $is_multiple = false ) {
	if ( empty( $field_name ) ) {
		return;
	}
	wp_enqueue_script( 'jlt_plupload' );
	wp_enqueue_script( 'jlt-upload' );
	$uniqid = uniqid();
	?>
	<div class="upload-btn-wrap">
		<div id="jlt_upload-<?php echo $field_name; ?>-<?php echo $uniqid; ?>" class="jlt-upload-btn">
			<i class="jlt-icon jltfa-plus"></i>
			<span class="jlt-upload-btn-label"><?php _e( 'Browse', 'job-listings' ); ?></span>
		</div>
		<div class="jlt_upload-status"></div>
	</div>
	<div id="jlt_upload-<?php echo $field_name; ?>-preview" class="upload-preview-wrap">
		<?php
		jlt_show_list_image_uploaded( $value, $field_name );
		?>
	</div>
	<script>
		jQuery(document).ready(function ($) {
			$('#jlt_upload-<?php echo $field_name; ?>-<?php echo $uniqid; ?>').jlt_upload({
				input_name: '<?php echo $field_name; ?>',
				container: 'jlt_upload-<?php echo $field_name; ?>-wrap',
				browse_button: 'jlt_upload-<?php echo $field_name; ?>-<?php echo $uniqid; ?>',
				tag_thumb: 'jlt_upload-<?php echo $field_name; ?>-preview',
				multi_upload: <?php echo( $is_multiple ? "true" : "false" ); ?>
			});
		});
	</script>
	<?php
}

function jlt_file_upload_form_field( $field_name = '', $extensions = array(), $value = '', $is_multiple = false, $field = array() ) {
	if ( ! wp_script_is( 'jlt-upload', 'registered' ) ) {
		jlt_register_upload_script();
	}
	wp_enqueue_script( 'jlt_plupload' );
	wp_enqueue_script( 'jlt-upload' );

	$id              = uniqid( 'plupload_' );
	$max_upload_size = wp_max_upload_size();
	if ( ! $max_upload_size ) {
		$max_upload_size = 0;
	}
	$plupload_init      = array(
		'runtimes'         => 'html5,flash,html4',
		'browse_button'    => $id . '_uploader-btn',
		'container'        => $id . '_upload-container',
		'file_data_name'   => 'file',
		'max_file_size'    => $max_upload_size,
		'url'              => esc_url_raw( add_query_arg( array(
			'action' => 'jlt_plupload',
			'nonce'  => wp_create_nonce( 'jlt-plupload' ),
		), admin_url( 'admin-ajax.php' ) ) ),
		'flash_swf_url'    => includes_url( 'js/plupload/plupload.flash.swf' ),
		'filters'          => array(
			array(
				'title'      => __( 'Allowed Files', 'job-listings' ),
				'extensions' => implode( ',', $extensions ),
			),
		),
		'multipart'        => true,
		'urlstream_upload' => true,
		'multi_selection'  => $is_multiple,
	);
	$plupload_init_json = htmlspecialchars( json_encode( $plupload_init ), ENT_QUOTES, 'UTF-8' );
	?>
	<div id="<?php echo esc_attr( $id . '_upload-container' ); ?>" class="jlt-plupload">
		<?php
		$upload_div  = is_admin() ? 'jlt-upload-field-admin' : 'jlt-upload-field';
		$upload_btn  = is_admin() ? 'jlt-btn-upload-admin' : 'jlt-btn-upload';
		$upload_icon = is_admin() ? 'dashicons dashicons-media-document' : 'jlt-icon jltfa-folder-open-o';
		?>
		<div class="jlt-upload <?php echo $upload_div; ?>"
		     data-settings="<?php echo esc_attr( $plupload_init_json ) ?>">
			<a href="#" class="<?php echo $upload_btn; ?>" id="<?php echo esc_attr( $id . '_uploader-btn' ); ?>">
				<span class="<?php echo $upload_icon; ?>"></span> <?php esc_html_e( 'Browse', 'job-listings' ) ?></a>
			<p class="help-block"><?php printf( __( 'Maximum upload file size: %s', 'job-listings' ), esc_html( size_format( $max_upload_size ) ) ); ?></p>
			<?php if ( ! empty( $extensions ) ) : ?>
				<p class="help-block"><?php echo sprintf( __( 'Allowed file: %s', 'job-listings' ), '.' . implode( ', .', $extensions ) ); ?></p>
			<?php endif; ?>
		</div>
		<div class="jlt-plupload-preview">
			<?php

			$file_name = ! empty( $value ) ? jlt_json_decode( $value ) : '';

			if ( ! empty( $file_name ) ) :

				$file_name = $file_name[ 0 ];

				$trash_icon = is_admin() ? 'dashicons dashicons-trash' : 'jlt-icon jltfa-trash-o';

				$download_icon = is_admin() ? 'dashicons dashicons-download' : 'jlt-icon jltfa-download';

				$upload_path = jlt_upload_url();
				$file_path   = $upload_path . $file_name;
				?>
				<a class="delete-pluploaded" data-toggle="tooltip"
				   data-filename="<?php echo esc_attr( $file_name ); ?>" href="#"
				   title="<?php _e( 'Delete File', 'job-listings' ); ?>">
					<i class="<?php echo $trash_icon; ?>"></i>
				</a>
				&nbsp;<strong><?php echo esc_html( $file_name ); ?></strong>&nbsp;

				<a href="<?php echo esc_url( $file_path ); ?>"><i class="<?php echo $download_icon; ?>"></i></a>
			<?php endif; ?>
		</div>
		<?php if ( isset( $field[ 'required' ] ) && $field[ 'required' ] ): ?>
			<input data-validation="length" data-validation-length="min3"
			       data-validation-error-msg-length="<?php _e( 'No file selected.', 'job-listings' ); ?>" type="hidden"
			       class="jlt-plupload-value" name="<?php echo esc_attr( $field_name ) ?>"
			       value="<?php echo esc_attr( $file_name ); ?>">
		<?php else: ?>
			<input type="hidden" class="jlt-plupload-value" name="<?php echo esc_attr( $field_name ) ?>"
			       value="<?php echo esc_attr( $file_name ); ?>">
		<?php endif; ?>

	</div>
	<?php
}

function jlt_get_file_upload_types() {
	$extensions_upload_file = jlt_get_common_setting( 'extensions_upload_file', 'pdf,doc,docx' );

	return apply_filters( 'jlt_extensions_upload_file', $extensions_upload_file );
}

function jlt_get_allowed_attach_file_types() {
	$extensions_upload_file = jlt_get_file_upload_types();

	return jlt_upload_convert_extension_list( $extensions_upload_file );
}

function jlt_show_list_image_uploaded( $thumb_id, $input_name ) {
	echo '<input type="hidden" name="' . $input_name . '" value="" />'; // blank input to make sure you always have the submitted images
	if ( ! empty( $thumb_id ) ) {
		if ( is_array( $thumb_id ) ) {
			foreach ( $thumb_id as $img ) :
				$img     = trim( $img );
				$img_src = wp_get_attachment_image_src( $img, 'thumbnail' );
				if ( ! empty( $img ) && ! empty( $img_src ) ) :
					echo '<div class="image-upload-thumb">';
					echo "<img src='{$img_src[0]}' alt='*' />";
					echo '<input type="hidden" name="' . $input_name . '[]" value="' . $img . '" class="img-' . $img . '"/>';
					echo '<a class="delete-uploaded" data-fileid="' . $img . '" href="#" title="' . __( 'Remove', 'job-listings' ) . '"><i class="jlt-icon jltfa-trash-o"></i></a></p>';
					echo '</div>';
				endif;
			endforeach;
		} else {
			$img_src = is_numeric( $thumb_id ) ? wp_get_attachment_image_src( $thumb_id, 'thumbnail' ) : '';
			$img_src = ! empty( $img_src ) ? $img_src[ 0 ] : $thumb_id;
			echo '<div class="image-upload-thumb">';
			echo "<img src='{$img_src}' alt='*' />";
			echo '<input type="hidden" name="' . $input_name . '" value="' . $thumb_id . '" class="img-' . $thumb_id . '"/>';
			echo '<a class="delete-uploaded" data-fileid="' . $thumb_id . '" href="#" title="' . __( 'Remove', 'job-listings' ) . '"><i class="jlt-icon jltfa-trash-o"></i></a></p>';
			echo '</div>';
		}
	}
}

function jlt_upload_convert_extension_list( $exts = 'pdf,doc,docx' ) {
	$exts         = ! empty( $exts ) ? explode( ',', $exts ) : array();
	$allowed_exts = array();
	foreach ( $exts as $type ) {
		$type = trim( $type );
		if ( empty( $type ) || $type === '.' ) {
			continue;
		}
		$type           = $type[ 0 ] === '.' ? substr( $type, 1 ) : $type;
		$allowed_exts[] = $type;
	}

	return $allowed_exts;
}

function jlt_meta_box_field_attachment( $post, $id, $type, $meta, $std = null, $field = null ) {
	$extensions = isset( $field[ 'options' ][ 'extensions' ] ) && ! empty( $field[ 'options' ][ 'extensions' ] ) ? $field[ 'options' ][ 'extensions' ] : 'pdf,doc,docx';
	?>
	<div class="clearfix">
		<?php jlt_file_upload_form_field( 'jlt_meta_boxes[' . $id . ']', jlt_upload_convert_extension_list( $extensions ), $meta ) ?>
	</div>
	<?php
}

add_action( 'jlt_meta_box_field_attachment', 'jlt_meta_box_field_attachment', 10, 6 );