<?php

function jlt_action_control_admin_init() {
	register_setting( 'jlt_action_control', 'jlt_action_control' );
}

add_filter( 'admin_init', 'jlt_action_control_admin_init' );

function jlt_get_action_list() {
	$actions = array(
		'post_job'  => array(
			'label'   => __( 'Post Job', 'job-listings' ),
			'default' => 'employer',
			'options' => apply_filters( 'jlt_post_job_action_options', array(
				'employer' => __( 'Employers', 'job-listings' ),
			) ),
		),
		'view_job'  => array(
			'label'   => __( 'View Job Detail', 'job-listings' ),
			'default' => 'public',
			'options' => apply_filters( 'jlt_view_job_detail_action_options', array(
				'public'    => __( 'Public', 'job-listings' ),
				'user'      => __( 'Logged-in Users', 'job-listings' ),
				'candidate' => __( 'Candidates', 'job-listings' ),
			) ),
		),
		'apply_job' => array(
			'label'   => __( 'Apply Job', 'job-listings' ),
			'default' => 'public',
			'options' => apply_filters( 'jlt_apply_job_action_options', array(
				'public'    => __( 'Public', 'job-listings' ),
				'candidate' => __( 'Candidates', 'job-listings' ),
			) ),
		),
	);

	return apply_filters( 'jlt_action_control_list', $actions );
}

function jlt_get_action_control( $action = '' ) {
	$actions = jlt_get_action_list();
	if ( ! array_key_exists( $action, $actions ) ) {
		return null;
	}

	return jlt_get_setting( 'jlt_action_control', $action, $actions[ $action ][ 'default' ] );
}

function jlt_action_control_settings_tabs( $tabs = array() ) {
	$index  = 0; //array_search('job_package', array_keys( $tabs ) ) + 1;
	$before = array_slice( $tabs, 0, $index );
	$after  = array_slice( $tabs, $index );

	$action_control_tab = array( 'action_control' => __( 'Action Control', 'job-listings' ) );

	return array_merge( $before, $action_control_tab, $after );
}

add_filter( 'jlt_admin_settings_tabs_array', 'jlt_action_control_settings_tabs', 99 );

function jlt_action_control_setting_form() {

	$actions = jlt_get_action_list();

	?>
	<?php settings_fields( 'jlt_action_control' ); ?>
	<h3><?php echo __( 'Action and Permissions', 'job-listings' ) ?></h3>
	<p><?php echo __( 'This page consists of setting related to the main actions of users on your site. Depending on the actions, you can select if the user is allowed freely or require purchasing the packages.', 'job-listings' ); ?></p>
	<p><?php echo __( 'With action requires buying packages, you will see proper settings on the package product edit page.', 'job-listings' ); ?></p>
	<table class="form-table" cellspacing="0">
		<tbody>
		<?php if ( ! empty( $actions ) ) : foreach ( $actions as $key => $action ) : ?>
			<tr>
				<th>
					<?php echo $action[ 'label' ]; ?>
				</th>
				<td>
					<select class="jlt-admin-chosen" name="jlt_action_control[<?php echo $key; ?>]">
						<?php $setting = jlt_get_action_control( $key ); ?>
						<?php foreach ( $action[ 'options' ] as $opt_key => $opt_label ) : ?>
							<option <?php selected( $setting, $opt_key ); ?>
								value="<?php echo $opt_key; ?>"><?php echo $opt_label; ?></option>
						<?php endforeach; ?>
					</select>
					<?php if ( isset( $action[ 'desc' ] ) ) : ?>
						<p><?php echo $action[ 'desc' ]; ?></p>
					<?php endif; ?>
				</td>
			</tr>
		<?php endforeach; endif; ?>
		</tbody>
	</table>
	<?php
}

add_action( 'jlt_admin_setting_action_control', 'jlt_action_control_setting_form' );
