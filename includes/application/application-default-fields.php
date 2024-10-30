<?php

function jlt_get_application_default_fields() {
	$default_fields = array(
		'application_message' => array(
			'name'         => 'application_message',
			'label'        => __( 'Message', 'job-listings' ),
			'type'         => 'textarea',
			'allowed_type' => array(
				'textarea' => __( 'Textarea', 'job-listings' ),
			),
			'value'        => __( 'Your cover letter/message sent to the employer', 'job-listings' ),
			'is_default'   => true,
			'required'     => true,
		),
		'_attachment'         => array(
			'name'         => '_attachment',
			'label'        => __( 'Attachment', 'job-listings' ),
			'type'         => 'file_upload',
			'allowed_type' => array(
				'file_upload' => __( 'File Upload', 'job-listings' ),
			),
			'value'        => '',
			'is_default'   => true,
			'required'     => false,
		),
	);

	return apply_filters( 'jlt_application_default_fields', $default_fields );
}
