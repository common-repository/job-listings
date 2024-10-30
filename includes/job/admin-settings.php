<?php
/**
 * Job Admin Settings Form.
 */

function jlt_job_settings_tabs( $tabs = array() ) {
	return array_merge( array(
		'general' => __( 'Jobs', 'job-listings' ),
	), $tabs );
}

add_filter( 'jlt_admin_settings_tabs_array', 'jlt_job_settings_tabs' );

function jlt_job_settings_form() {
	if ( isset( $_GET[ 'settings-updated' ] ) && $_GET[ 'settings-updated' ] ) {
		flush_rewrite_rules();
		do_action( 'jlt_job_setting_changed' );
	}
	$job_post_page = jlt_get_job_setting( 'job_post_page' );
	$archive_slug  = jlt_get_job_setting( 'archive_slug' );

	$job_enable_ajax_filter_job = jlt_get_job_setting( 'enable_ajax_filter_job', 1 );

	$job_related        = jlt_get_job_setting( 'job_related', 'yes' );
	$job_related_number = jlt_get_job_setting( 'job_related_number', 5 );

	$job_company = jlt_get_job_setting( 'job_company', 'yes' );

	$enable_location_map = jlt_get_job_setting( 'enable_location_map', 1 );

	$job_approve          = jlt_get_job_setting( 'job_approve', '' );
	$default_job_content  = jlt_get_job_setting( 'default_job_content', '' );
	$submit_agreement     = jlt_get_job_setting( 'submit_agreement', null );
	$job_posting_limit    = jlt_get_job_setting( 'job_posting_limit', 5 );
	$job_display_duration = jlt_get_job_setting( 'job_display_duration', 30 );
	$job_feature_limit    = jlt_get_job_setting( 'job_feature_limit', 1 );
	$job_posting_reset    = jlt_get_job_setting( 'job_posting_reset', 0 );

	?>
	<?php settings_fields( 'jlt_job_general' ); ?>
	<h3><?php echo __( 'Job Display', 'job-listings' ) ?></h3>
	<table class="form-table" cellspacing="0">
		<tbody>
		<tr>
			<th>
				<?php esc_html_e( 'Job Archive base (slug)', 'job-listings' ) ?>
			</th>
			<td>
				<input type="text" name="jlt_job_general[archive_slug]"
				       value="<?php echo( $archive_slug ? sanitize_title( $archive_slug ) : 'jobs' ) ?>">
			</td>
		</tr>
		<tr>
			<th>
				<?php esc_html_e( 'Ajax Filter Job', 'job-listings' ) ?>
			</th>
			<td>
				<input type="hidden" name="jlt_job_general[enable_ajax_filter_job]" value="">
				<label>
					<input type="checkbox" <?php checked( $job_enable_ajax_filter_job, 1 ); ?>
					       name="jlt_job_general[enable_ajax_filter_job]"
					       value="1">
					<?php _e( 'Enable ajax filter and paging job listings.', 'job-listings' ); ?>
				</label>
			</td>
		</tr>
		<tr>
			<th>
				<?php esc_html_e( 'Job Related', 'job-listings' ) ?>
			</th>
			<td>
				<input type="hidden" name="jlt_job_general[job_related]" value="">
				<label>
					<input type="checkbox" <?php checked( $job_related, 'yes' ); ?>
					       name="jlt_job_general[job_related]"
					       value="yes">
					<?php _e( 'Show related job on single job page.', 'job-listings' ); ?>
				</label>
			</td>
		</tr>
		<tr>
			<th>
				<?php esc_html_e( 'Job Company', 'job-listings' ) ?>
			</th>
			<td>
				<input type="hidden" name="jlt_job_general[job_company]" value="">
				<label>
					<input type="checkbox" <?php checked( $job_company, 'yes' ); ?>
					       name="jlt_job_general[job_company]"
					       value="yes">
					<?php _e( 'Show company info on single job page.', 'job-listings' ); ?>
				</label>
			</td>
		</tr>
		<tr>
			<th>
				<?php esc_html_e( 'Job Related Number', 'job-listings' ) ?>
			</th>
			<td>
				<input type="text" name="jlt_job_general[job_related_number]"
				       value="<?php echo $job_related_number; ?>">
			</td>
		</tr>
		<tr>
			<th>
				<?php _e( 'Single Job Map', 'job-listings' ) ?>
			</th>
			<td>
				<input type="hidden" name="jlt_job_general[enable_location_map]" value="0">

				<label>
					<input type="checkbox" <?php checked( $enable_location_map, true ); ?>
					       name="jlt_job_general[enable_location_map]"
					       value="1">
					<?php _e( 'Show job location google maps on single job page.', 'job-listings' ); ?>
				</label>
			</td>
		</tr>
		<?php do_action( 'jlt_setting_job_display_fields' ); ?>
		</tbody>
	</table>
	<br/>
	<hr/><br/>
	<h3><?php echo __( 'Job Posting', 'job-listings' ) ?></h3>
	<table class="form-table" cellspacing="0">
		<tbody>
		<tr>
			<th>
				<?php _e( 'Post Job Page', 'job-listings' ) ?>
			</th>
			<td>
				<?php
				$args = array(
					'name'             => 'jlt_job_general[job_post_page]',
					'id'               => 'job_post_page',
					'sort_column'      => 'menu_order',
					'sort_order'       => 'ASC',
					'show_option_none' => ' ',
					'class'            => 'jlt-admin-chosen',
					'echo'             => false,
					'selected'         => $job_post_page,
				);
				?>
				<?php echo str_replace( ' id=', " data-placeholder='" . __( 'Select a page&hellip;', 'job-listings' ) . "' id=", wp_dropdown_pages( $args ) ); ?>
				<p>
					<small><?php _e( 'Select a page with shortcode [job_submit_form]', 'job-listings' ); ?></small>
				</p>
			</td>
		</tr>
		<tr>
			<th>
				<?php esc_html_e( 'Job Approval', 'job-listings' ) ?>
			</th>
			<td>
				<input type="hidden" name="jlt_job_general[job_approve]" value="">
				<input type="checkbox" <?php checked( $job_approve, 'yes' ); ?> name="jlt_job_general[job_approve]"
				       value="yes">
				<p>
					<small><?php echo __( 'Each newly submitted job needs the manual approval of Admin.', 'job-listings' ) ?></small>
				</p>
			</td>
		</tr>
		<tr>
			<th>
				<?php esc_html_e( 'Default Job Content', 'job-listings' ) ?>
				<p>
					<small><?php echo __( 'Default content that will auto populated when Employers post new Jobs.', 'job-listings' ) ?></small>
				</p>
			</th>
			<td>
				<?php
				$default_text = __( '<h3>Job Description</h3><p>What is the job about? Enter the overall description of your job.</p>', 'job-listings' );
				$default_text .= __( '<h3>Benefits</h3><ul><li>What can candidates get from the position?</li><li>What can candidates get from the position?</li><li>What can candidates get from the position?</li></ul>', 'job-listings' );
				$default_text .= __( '<h3>Job Requirements</h3><ol><li>Detailed requirement for the vacancy.?</li><li>Detailed requirement for the vacancy.?</li><li>Detailed requirement for the vacancy.?</li></ol>', 'job-listings' );
				$default_text .= __( '<h3>How To Apply</h3><p>How candidate can apply for your job. You can leave your contact information to receive hard copy application or any detailed guide for application.</p>', 'job-listings' );

				$text = ! empty( $default_job_content ) ? $default_job_content : $default_text;

				$editor_id = 'textblock' . uniqid();
				// add_filter( 'wp_default_editor', create_function('', 'return "tinymce";') );
				wp_editor( $text, $editor_id, array(
					'media_buttons' => false,
					'quicktags'     => true,
					'textarea_rows' => 15,
					'textarea_cols' => 80,
					'textarea_name' => 'jlt_job_general[default_job_content]',
					'wpautop'       => false,
				) ); ?>
			</td>
		</tr>
		<tr>
			<th>
				<?php esc_html_e( 'Job submission condition', 'job-listings' ) ?>
				<p>
					<small><?php echo __( 'The condition that employers must agree to before submitting a new job. Leave it blank for no condition.', 'job-listings' ) ?></small>
				</p>
			</th>
			<td>
				<?php
				$submit_agreement = ! is_null( $submit_agreement ) ? $submit_agreement : sprintf( __( 'Job seekers can find your job and contact you via email or %s regarding your application options. Preview all information thoroughly before submitting your job for approval.', 'job-listings' ), get_bloginfo( 'name' ) );
				?>
				<textarea name="jlt_job_general[submit_agreement]" rows="5"
				          cols="80"><?php echo esc_html( $submit_agreement ); ?></textarea>
			</td>
		</tr>
		<tr>
			<th>
				<?php esc_html_e( 'Job Limit', 'job-listings' ) ?>
			</th>
			<td>
				<input type="text" name="jlt_job_general[job_posting_limit]"
				       value="<?php echo absint( $job_posting_limit ) ?>">
				<p>
					<small><?php echo __( 'You can use this setting for limiting the number of jobs per employer.', 'job-listings' ) ?></small>
				</p>
			</td>
		</tr>
		<tr>
			<th>
				<?php esc_html_e( 'Job Duration (day)', 'job-listings' ) ?>
			</th>
			<td>
				<input type="text" name="jlt_job_general[job_display_duration]"
				       value="<?php echo absint( $job_display_duration ) ?>">
				<p>
					<small><?php echo __( 'You can use this setting for job duration.', 'job-listings' ) ?></small>
				</p>
			</td>
		</tr>
		<tr>
			<th>
				<?php esc_html_e( 'Featured Job Limit', 'job-listings' ) ?>
			</th>
			<td>
				<input type="text" name="jlt_job_general[job_feature_limit]"
				       value="<?php echo absint( $job_feature_limit ) ?>">
				<p>
					<small><?php echo __( 'You can use this setting for limiting the number of featured jobs per employer.', 'job-listings' ) ?></small>
				</p>
			</td>
		</tr>
		<tr>
			<th>
				<?php esc_html_e( 'Reset counter every', 'job-listings' ) ?>
			</th>
			<td>
				<input type="text" name="jlt_job_general[job_posting_reset]"
				       value="<?php echo absint( $job_posting_reset ) ?>">&nbsp;<?php echo __( 'Month', 'job-listings' ); ?>
				<p>
					<small><?php echo __( 'Reset the counter will allow Employers to re-post jobs after using up the limitation. Input zero for no reset.', 'job-listings' ) ?></small>
				</p>
			</td>
		</tr>
		<?php do_action( 'jlt_setting_job_submission_fields' ); ?>
		</tbody>
	</table>
	<?php
}

