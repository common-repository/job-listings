<?php
/**
 * Email Template Fields
 *
 * @since 1.0.0
 */

function jlt_et_default_field() {
	$et_default_field = array(
		'[site_name]' => __( 'inserting your site name', 'job-listings' ),
		'[site_url]'  => __( 'inserting your site URL', 'job-listings' ),
	);

	return apply_filters( 'jlt_email_template_default_field', $et_default_field );
}

function jlt_email_template_field() {

	$fields = array(
		'admin_job_submitted'                => array(
			'title'     => __( 'Admin Job Submit', 'job-listings' ),
			'desc'      => __( 'Email to admin when job submit', 'job-listings' ),
			'recipient' => __( 'Administrator', 'job-listings' ),
			'post_type' => 'job',
			'subject'   => __( '[site_name] New job posted: [job_title]', 'job-listings' ),
			'content'   => __( '[job_company] has just submitted a job:<br/></br><a href="[job_url]">View Job</a>.<br/><br/>Best regards,<br/>[site_name]', 'job-listings' ),
			'fields'    => array(
				'[job_title]'   => __( 'inserting job title', 'job-listings' ),
				'[job_url]'     => __( 'inserting job URL', 'job-listings' ),
				'[job_content]' => __( 'inserting job content', 'job-listings' ),
				'[job_company]' => __( 'inserting job company name', 'job-listings' ),
			),
		),
		'employer_registration'              => array(
			'title'     => __( 'Employer registration', 'job-listings' ),
			'desc'      => __( 'Email to employer after registration', 'job-listings' ),
			'recipient' => __( 'Employer', 'job-listings' ),
			'post_type' => '',
			'subject'   => __( 'Congratulation! You\'ve successfully created an account on [[site_name]]', 'job-listings' ),
			'content'   => __( 'Dear [user_name],<br/>Thank you for registering an account on [site_name] as an employer. You can start posting jobs or search for your potential candidates now.<br/><br/>Best regards,<br/>[site_name]', 'job-listings' ),
			'fields'    => array(
				'[user_name]'       => __( 'inserting username', 'job-listings' ),
				'[user_email]'      => __( 'inserting user email', 'job-listings' ),
				'[user_registered]' => __( 'inserting user registered time', 'job-listings' ),
			),
		),
		'candidate_registration'             => array(
			'title'     => __( 'Candidate registration', 'job-listings' ),
			'desc'      => __( 'Email to candidate after registration', 'job-listings' ),
			'recipient' => __( 'Candidate', 'job-listings' ),
			'post_type' => '',
			'subject'   => __( 'Congratulation! You\'ve successfully created an account on [[site_name]]', 'job-listings' ),
			'content'   => __( 'Dear [user_name],<br/>Thank you for registering an account on [site_name] as an employer. You can start posting jobs or search for your potential candidates now.<br/><br/>Best regards,<br/>[site_name]', 'job-listings' ),
			'fields'    => array(
				'[user_name]'       => __( 'inserting username', 'job-listings' ),
				'[user_email]'      => __( 'inserting user email', 'job-listings' ),
				'[user_registered]' => __( 'inserting user registered time', 'job-listings' ),
			),
		),
		'employer_job_submitted'             => array(
			'title'     => __( 'Employer job submitted', 'job-listings' ),
			'desc'      => __( 'Email to employer posted job', 'job-listings' ),
			'recipient' => __( 'Employer', 'job-listings' ),
			'post_type' => 'job',
			'subject'   => __( '[[site_name]] You\'ve successfully posted a job [job_title]', 'job-listings' ),
			'content'   => __( 'Hi [job_company],<br/><br/>You\'ve successfully post a new job:<br/><a href="[job_url]">View Job Detail</a>.<br/><br/>You can manage your jobs in <a href="[job_manage_url]">Manage Jobs</a><br/><br/>Best regards,<br/>[site_name]', 'job-listings' ),
			'fields'    => array(
				'[job_title]'      => __( 'inserting job title', 'job-listings' ),
				'[job_url]'        => __( 'inserting job URL', 'job-listings' ),
				'[job_content]'    => __( 'inserting job content', 'job-listings' ),
				'[job_company]'    => __( 'inserting job company name', 'job-listings' ),
				'[job_manage_url]' => __( 'inserting job manage url', 'job-listings' ),
			),
		),
		'employer_job_approved'              => array(
			'title'     => __( 'Employer job approved', 'job-listings' ),
			'desc'      => __( 'Email to employer after approved job', 'job-listings' ),
			'recipient' => __( 'Employer', 'job-listings' ),
			'post_type' => 'job',
			'subject'   => __( '[[site_name]] Your job [job_title] has been approved and published', 'job-listings' ),
			'content'   => __( 'Hi [job_company],<br/><br/>Your submitted job [job_title] has been approved and published now on [site_name]:<br/><a href="[job_url]">View Job Detail</a>.<br/><br/>You can manage your jobs in <a href="[job_manage_url]">Manage Jobs</a><br/><br/>Best regards,<br/>[site_name]', 'job-listings' ),
			'fields'    => array(
				'[job_title]'      => __( 'inserting job title', 'job-listings' ),
				'[job_url]'        => __( 'inserting job URL', 'job-listings' ),
				'[job_content]'    => __( 'inserting job content', 'job-listings' ),
				'[job_company]'    => __( 'inserting job company name', 'job-listings' ),
				'[job_manage_url]' => __( 'inserting job manage url', 'job-listings' ),
			),
		),
		'employer_job_rejected'              => array(
			'title'     => __( 'Employer job rejected', 'job-listings' ),
			'desc'      => __( 'Email to employer after rejected job', 'job-listings' ),
			'recipient' => __( 'Employer', 'job-listings' ),
			'post_type' => 'job',
			'subject'   => __( '[[site_name]] Your job [job_title] can\'t be published', 'job-listings' ),
			'content'   => __( 'Hi [job_company],<br/><br/>Your submitted job [job_title] can not be published and has been deleted. You will have to submit another job.<br/><br/>You can manage your jobs in <a href="[job_manage_url]">Manage Jobs</a><br/><br/>Best regards,<br/>[site_name]', 'job-listings' ),
			'fields'    => array(
				'[job_title]'      => __( 'inserting job title', 'job-listings' ),
				'[job_url]'        => __( 'inserting job URL', 'job-listings' ),
				'[job_content]'    => __( 'inserting job content', 'job-listings' ),
				'[job_company]'    => __( 'inserting job company name', 'job-listings' ),
				'[job_manage_url]' => __( 'inserting job manage url', 'job-listings' ),
			),
		),
		'employer_job_application'           => array(
			'title'     => __( 'Employer has new application', 'job-listings' ),
			'desc'      => __( 'Email to employer when has new apply', 'job-listings' ),
			'recipient' => __( 'Employer', 'job-listings' ),
			'post_type' => 'application',
			'subject'   => __( '[[site_name]] [candidate_name] applied for [job_title]', 'job-listings' ),
			'content'   => __( 'Hi [job_company],<br/><br/>[candidate_name]\'ve just applied for [job_title].<br/><br/>You can manage applications for your jobs in <a href="[application_manage_url]">Manage Application</a>.<br/><br/>Best regards,<br/>[site_name]', 'job-listings' ),
			'fields'    => array(
				'[job_title]'              => __( 'inserting job title', 'job-listings' ),
				'[job_url]'                => __( 'inserting job URL', 'job-listings' ),
				'[job_company]'            => __( 'inserting job company name', 'job-listings' ),
				'[candidate_name]'         => __( 'inserting candidate name', 'job-listings' ),
				'[application_message]'    => __( 'inserting application message content', 'job-listings' ),
				'[application_manage_url]' => __( 'inserting application manage Url', 'job-listings' ),
			),
		),
		'candidate_job_application'          => array(
			'title'     => __( 'Candidate new application', 'job-listings' ),
			'desc'      => __( 'Email to candidate after apply to job', 'job-listings' ),
			'recipient' => __( 'Candidate', 'job-listings' ),
			'post_type' => 'application',
			'subject'   => __( 'You have successfully applied for [job_title]', 'job-listings' ),
			'content'   => __( 'Congratulation [candidate_name],<br/><br/>You\'ve successfully applied for [job_title].<br/><a href="[job_url]">View Job Detail</a><br/>You can manage and follow status of your applied jobs and applications in <a href="[application_manage_url]">My Applications</a>.<br/><br/>Note: Due to high application volume, employers may not be able to respond to all the application.<br/><br/>Good luck on your future career path!<br/><br/>Best regards,<br/>[site_name]', 'job-listings' ),
			'fields'    => array(
				'[job_title]'              => __( 'inserting job title', 'job-listings' ),
				'[job_url]'                => __( 'inserting job URL', 'job-listings' ),
				'[job_company]'            => __( 'inserting job company name', 'job-listings' ),
				'[candidate_name]'         => __( 'inserting candidate name', 'job-listings' ),
				'[application_message]'    => __( 'inserting application message content', 'job-listings' ),
				'[application_manage_url]' => __( 'inserting application manage Url', 'job-listings' ),
			),
		),
		'candidate_job_application_approved' => array(
			'title'     => __( 'Candidate application approved', 'job-listings' ),
			'desc'      => __( 'Email to candidate after employer approved application', 'job-listings' ),
			'recipient' => __( 'Candidate', 'job-listings' ),
			'post_type' => 'application',
			'subject'   => __( '[job_company] has responded to your application', 'job-listings' ),
			'content'   => __( 'Hi [candidate_name],<br/>[job_company] has just responded to your application for job  <a href="[job_url]">[job_title]</a> with message: <br/><em>[responded]</em><br/>You can manage your applications in <a href="[application_manage_url]">Manage Application</a>.<br/>Best regards,<br/>[site_name]', 'job-listings' ),
			'fields'    => array(
				'[job_title]'              => __( 'inserting job title', 'job-listings' ),
				'[job_url]'                => __( 'inserting job URL', 'job-listings' ),
				'[job_company]'            => __( 'inserting job company name', 'job-listings' ),
				'[candidate_name]'         => __( 'inserting candidate name', 'job-listings' ),
				'[application_manage_url]' => __( 'inserting application manage Url', 'job-listings' ),
				'[responded]'              => __( 'inserting approved apply responded message', 'job-listings' ),
				'[responded_title]'        => __( 'inserting approved apply title responded message', 'job-listings' ),
			),
		),
		'candidate_job_application_rejected' => array(
			'title'     => __( 'Candidate application rejected', 'job-listings' ),
			'desc'      => __( 'Email to candidate after employer rejected application', 'job-listings' ),
			'recipient' => __( 'Candidate', 'job-listings' ),
			'post_type' => 'application',
			'subject'   => __( '[job_company] has responded to your application', 'job-listings' ),
			'content'   => __( 'Hi [candidate_name],<br/>[job_company] has just responded to your application for job  <a href="[job_url]">[job_title]</a> with message: <br/><em>[responded]</em><br/>You can manage your applications in <a href="[application_manage_url]">Manage Application</a>.<br/>Best regards,<br/>[site_name]', 'job-listings' ),
			'fields'    => array(
				'[job_title]'              => __( 'inserting job title', 'job-listings' ),
				'[job_url]'                => __( 'inserting job URL', 'job-listings' ),
				'[job_company]'            => __( 'inserting job company name', 'job-listings' ),
				'[candidate_name]'         => __( 'inserting candidate name', 'job-listings' ),
				//					'[resume_url]'             => __( 'inserting resume Url', 'job-listings' ),
				'[application_manage_url]' => __( 'inserting application manage Url', 'job-listings' ),
			),
		),

	);

	return apply_filters( 'jlt_email_template_field', $fields );
}

function jlt_email_template_field_keys() {
	$fields = jlt_email_template_field();

	return array_keys( $fields );
}

function jlt_et_get_field( $field_name ) {
	$fields = jlt_email_template_field();

	return $fields[ $field_name ];
}

function jlt_et_get_list_field( $group ) {
	$fields = jlt_email_template_field();
	$fields = $fields[ $group ];

	$default_field = jlt_et_default_field();

	return array_keys( array_merge( $fields, $default_field ) );
}

function jlt_et_get_default_value( $field_name ) {
	$fields = jlt_email_template_field();

	return $fields[ $field_name . '_default' ];
}

function jlt_et_render_default_field() {
	$fields = jlt_et_default_field();
	foreach ( $fields as $k => $v ) {
		echo '<p class="description"><code>' . $k . '</code> - ' . $v . ' </p>';
	}
}

function jlt_et_render_field( $section, $placeholders = false, $show_default = false, $custom_field_post_type = '' ) {
	$list_fields = jlt_email_template_field();
	$fields      = $list_fields[ $section ][ 'fields' ];

	echo '<div class="jlt-help-message" style="margin-top:10px;">';

	// show default value des

	if ( $show_default ) {
		$value_default = $list_fields[ $section ][ 'subject' ];
		echo '<p class="description"><strong>' . __( 'Default content', 'job-listings' ) . '</strong> <code>' . $value_default . '</code></p>';
	}

	if ( $placeholders ) {
		echo '<p class="description"><strong>' . __( 'You can use the following placeholders in content of this email:', 'job-listings' ) . '</strong></p>';

		$fields = ! empty( $fields ) ? $fields : array();
		foreach ( $fields as $k => $v ) {
			echo '<p class="description"><code>' . $k . '</code> - ' . $v . ' </p>';
		}
		jlt_et_render_default_field();
	}

	if ( ! empty( $custom_field_post_type ) ) {
		switch ( $custom_field_post_type ) {
			case 'job':
				$custom_fields = jlt_get_job_custom_fields();
				break;
			case 'application':
				$custom_fields = jlt_get_application_custom_fields();
				break;
			case $custom_field_post_type:
				$custom_fields = apply_filters( 'jlt_email_template_custom_field_' . $custom_field_post_type, array() );
				break;
		}

		if ( is_array( $custom_fields ) ) {
			echo '<p class="description"><strong>' . __( 'You can also use the custom fields:', 'job-listings' ) . '</strong></p>';
			echo '<p class="description">';
			foreach ( $custom_fields as $field ) {
				echo '<code>[' . $field[ 'name' ] . ']</code>';
			}
			echo '</p>';
		}
	}
	echo '</div>';
}