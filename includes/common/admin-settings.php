<?php

function jlt_common_admin_init() {
	register_setting( 'jlt_common', 'jlt_common' );
}

add_filter( 'admin_init', 'jlt_common_admin_init' );

function jlt_common_settings_tabs( $tabs = array() ) {
	$temp1 = array_slice( $tabs, 0, 1 );
	$temp2 = array_slice( $tabs, 1 );

	$resume_tab = array( 'common' => __( 'Common', 'job-listings' ) );

	return array_merge( $temp1, $resume_tab, $temp2 );
}

add_filter( 'jlt_admin_settings_tabs_array', 'jlt_common_settings_tabs', 11 );

function jlt_common_setting_form() {

	$fields = array(
		array(
			'id'      => 'extensions_upload_file',
			'label'   => __( 'Allowed Upload File Types', 'job-listings' ),
			'desc'    => __( 'File types that are allowed for upload. Default only allows Word and PDF files', 'job-listings' ),
			'type'    => 'text',
			'default' => 'doc,docx,pdf',
			'class'   => 'jlt-setting-control jlt-setting-text',
		),
		array(
			'id'      => 'upload_dir',
			'label'   => __( 'Upload directory', 'job-listings' ),
			'desc'    => __( 'Directory for upload. Default file will upload to <b>job-listings</b> : wp-content/uploads/job-listings. <b>Warning: The site has data, the old data will be wrong file path.</b>', 'job-listings' ),
			'type'    => 'text',
			'default' => 'job-listings',
			'class'   => 'jlt-setting-control jlt-setting-text',
		),
	);
	$fields = apply_filters( 'jlt_common_setting_fields', $fields );
	jlt_render_setting_form( $fields, 'jlt_common', __( 'Common settings', 'job-listings' ) );
}

add_action( 'jlt_admin_setting_common', 'jlt_common_setting_form' );

function jlt_get_common_setting( $id = null, $default = null ) {
	return jlt_get_setting( 'jlt_common', $id, $default );
}