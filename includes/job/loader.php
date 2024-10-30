<?php

require_once plugin_dir_path( dirname( __FILE__ ) ) . '/job/functions.php';
require_once plugin_dir_path( dirname( __FILE__ ) ) . '/job/init.php';
if ( is_admin() ) {
	require_once plugin_dir_path( dirname( __FILE__ ) ) . '/job/admin.php';
	require_once plugin_dir_path( dirname( __FILE__ ) ) . '/job/admin-settings.php';
	require_once plugin_dir_path( dirname( __FILE__ ) ) . '/job/admin-job-list.php';
	require_once plugin_dir_path( dirname( __FILE__ ) ) . '/job/admin-job-edit.php';
}
require_once plugin_dir_path( dirname( __FILE__ ) ) . '/job/job_type.php';
require_once plugin_dir_path( dirname( __FILE__ ) ) . '/job/job_location.php';
require_once plugin_dir_path( dirname( __FILE__ ) ) . '/job/job-default-fields.php';
require_once plugin_dir_path( dirname( __FILE__ ) ) . '/job/job-custom-fields.php';
require_once plugin_dir_path( dirname( __FILE__ ) ) . '/job/job-custom-fields-package.php';
require_once plugin_dir_path( dirname( __FILE__ ) ) . '/job/job-expired.php';
require_once plugin_dir_path( dirname( __FILE__ ) ) . '/job/job-query.php';

require_once plugin_dir_path( dirname( __FILE__ ) ) . '/job/job-posting.php';
require_once plugin_dir_path( dirname( __FILE__ ) ) . '/job/job-posting-free.php';
require_once plugin_dir_path( dirname( __FILE__ ) ) . '/job/job-posting-action.php';
require_once plugin_dir_path( dirname( __FILE__ ) ) . '/job/job-viewable.php';
require_once plugin_dir_path( dirname( __FILE__ ) ) . '/job/job-apply-action.php';
require_once plugin_dir_path( dirname( __FILE__ ) ) . '/job/job-apply-resume-package.php';
require_once plugin_dir_path( dirname( __FILE__ ) ) . '/job/job-template.php';

require_once plugin_dir_path( dirname( __FILE__ ) ) . '/job/job-map.php';

require_once plugin_dir_path( dirname( __FILE__ ) ) . '/job/extra.php';
require_once plugin_dir_path( dirname( __FILE__ ) ) . '/job/job-shortcode.php';