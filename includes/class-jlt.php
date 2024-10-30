<?php

/**
 * The file that defines the core plugin class
 *
 * A class definition that includes attributes and functions used across both the
 * public-facing side of the site and the admin area.
 *
 * @link       http://nootheme.com
 * @since      0.1.0
 *
 * @package    Job_Listings
 * @subpackage Job_Listings/includes
 */

/**
 * The core plugin class.
 *
 * This is used to define internationalization, admin-specific hooks, and
 * public-facing site hooks.
 *
 * Also maintains the unique identifier of this plugin as well as the current
 * version of the plugin.
 *
 * @since      0.1.0
 * @package    Job_Listings
 * @subpackage Job_Listings/includes
 * @author     NooTheme <thinhnv@vietbrain.com>
 */
class Job_Listings
{

    /**
     * @since    0.1.0
     * @var null
     */
    protected static $_instance = null;

    /**
     * The loader that's responsible for maintaining and registering all hooks that power
     * the plugin.
     *
     * @since    0.1.0
     * @access   protected
     * @var      Job_Listings_Loader $loader Maintains and registers all hooks for the plugin.
     */
    protected $loader;

    /**
     * The unique identifier of this plugin.
     *
     * @since    0.1.0
     * @access   protected
     * @var      string $plugin_name The string used to uniquely identify this plugin.
     */
    protected $plugin_name;

    /**
     * Plugin url
     *
     * @since    0.1.0
     * @access   protected
     * @var string $plugin_url
     */
    protected $plugin_url;

    /**
     * Plugin real name
     *
     * @since    0.1.0
     * @access   protected
     * @var string $plugin_real_name
     */
    protected $plugin_real_name;

    /**
     * The current version of the plugin.
     *
     * @since    0.1.0
     * @access   protected
     * @var      string $version The current version of the plugin.
     */
    protected $version;

    public static function instance()
    {
        if (is_null(self::$_instance)) {
            self::$_instance = new self();
        }
        return self::$_instance;
    }

    /**
     * Define the core functionality of the plugin.
     *
     * Set the plugin name and the plugin version that can be used throughout the plugin.
     * Load the dependencies, define the locale, and set the hooks for the admin area and
     * the public-facing side of the site.
     *
     * @since    0.1.0
     */
    public function __construct()
    {
        ob_start();
        
        do_action('job_listings_before_load');
        $this->plugin_name = 'job-listings';
        $this->plugin_real_name = __('Job Listings', 'job-listings');
        $this->plugin_url = untrailingslashit(plugins_url('/', __FILE__));
        $this->version = '0.1.0';

        add_filter( 'plugin_action_links_' . JLT_PLUGIN_BASENAME, array($this, 'action_links'));

        $this->load_dependencies();
        $this->set_locale();
        $this->define_admin_hooks();
        $this->load_libs();
        $this->load_message();

        $this->load_admin();
        $this->load_member();
        $this->load_job();
        $this->load_application();
        $this->load_email();

        $this->load_form_hander();
        $this->load_shortcode();
        
        $this->load_addons();

        $this->define_public_hooks();


    }

    /**
     * Load the required dependencies for this plugin.
     *
     * Include the following files that make up the plugin:
     *
     * - Job_Listings_Loader. Orchestrates the hooks of the plugin.
     * - Job_Listings_i18n. Defines internationalization functionality.
     * - Job_Listings_Admin. Defines all hooks for the admin area.
     * - Job_Listings_Public. Defines all hooks for the public side of the site.
     *
     * Create an instance of the loader which will be used to register the hooks
     * with WordPress.
     *
     * @since    0.1.0
     * @access   private
     */
    private function load_dependencies()
    {

        /**
         * The class responsible for orchestrating the actions and filters of the
         * core plugin.
         */
        require_once plugin_dir_path(dirname(__FILE__)) . 'includes/class-jlt-loader.php';

        /**
         * The class responsible for defining internationalization functionality
         * of the plugin.
         */
        require_once plugin_dir_path(dirname(__FILE__)) . 'includes/class-jlt-i18n.php';

        /**
         * The class responsible for defining all actions that occur in the admin area.
         */
        require_once plugin_dir_path(dirname(__FILE__)) . 'admin/class-jlt-admin.php';

        /**
         * The class responsible for defining all actions that occur in the public-facing
         * side of the site.
         */
        require_once plugin_dir_path(dirname(__FILE__)) . 'public/class-jlt-public.php';

        /**
         * Load job
         */
        $this->load_common();

        require_once plugin_dir_path(dirname(__FILE__)) . 'includes/job/loader.php';

        $this->loader = new Job_Listings_Loader();

    }

    /**
     * Load library.
     *
     * @since    0.1.0
     * @access   private
     */
    private function load_libs()
    {
        require_once plugin_dir_path(dirname(__FILE__)) . 'includes/libs/loader.php';
        require_once plugin_dir_path(dirname(__FILE__)) . 'includes/class-jlt-template-loader.php';
    }

    /**
     * Load common.
     *
     * @since    0.1.0
     * @access   private
     */
    private function load_common()
    {
        require_once plugin_dir_path(dirname(__FILE__)) . 'includes/common/loader.php';
        require_once plugin_dir_path(dirname(__FILE__)) . 'includes/functions/loader.php';
    }

    /**
     * Load message.
     *
     * @since    0.1.0
     * @access   private
     */
    private  function load_message(){
        require_once plugin_dir_path(dirname(__FILE__)) . 'includes/class-jlt-message.php';
    }

    /**
     * Load post type.
     *
     * @since    0.1.0
     * @access   private
     */
    private function load_job()
    {
        require_once plugin_dir_path(dirname(__FILE__)) . 'includes/job/loader.php';
        require_once plugin_dir_path(dirname(__FILE__)) . 'includes/class-jlt-job-factory.php';
    }

    /**
     * Load member.
     *
     * @since    0.1.0
     * @access   private
     */
    private function load_member()
    {
        require_once plugin_dir_path(dirname(__FILE__)) . 'includes/member/loader.php';
        require_once plugin_dir_path(dirname(__FILE__)) . 'includes/class-jlt-member.php';
        require_once plugin_dir_path(dirname(__FILE__)) . 'includes/class-jlt-company-factory.php';
    }

    /**
     * Load application.
     *
     * @since    0.1.0
     * @access   private
     */
    private function load_application()
    {
        require_once plugin_dir_path(dirname(__FILE__)) . 'includes/application/loader.php';
    }

    /**
     *
     */
    private function load_email()
    {
        require_once plugin_dir_path(dirname(__FILE__)) . 'includes/email/loader.php';
    }

    /**
     * Load admin.
     *
     * @since    0.1.0
     * @access   private
     */
    private function load_admin()
    {
        require_once plugin_dir_path(dirname(__FILE__)) . 'includes/admin/loader.php';
    }

    /**
     * Load shortcode.
     *
     * @since    0.1.0
     * @access   private
     */
    private function load_shortcode()
    {
        require_once plugin_dir_path(dirname(__FILE__)) . 'includes/class-jlt-shortcode.php';
    }

    /**
     * Load form hander.
     *
     * @since    0.1.0
     * @access   private
     */
    private function load_form_hander()
    {
        require_once plugin_dir_path(dirname(__FILE__)) . 'includes/forms/loader.php';
    }

    private function load_addons(){
        require_once plugin_dir_path(dirname(__FILE__)) . 'includes/addons/loader.php';
    }

    /**
     * Show action links on the plugin screen.
     *
     * @param mixed $links
     *
     * @return array
     */
    public function action_links( $links ) {

        return array_merge( array(
            '<a href="' . admin_url( 'admin.php?page=jlt-setting' ) . '">' . __( 'Settings', 'job-listings' ) . '</a>',
            '<a href="' . apply_filters( 'jlt_docs_url', 'https://nootheme.com/forums/' ) . '">' . __( 'Docs & Support', 'job-listings' ) . '</a>',
        ), $links );
    }

    /**
     * Define the locale for this plugin for internationalization.
     *
     * Uses the Job_Listings_i18n class in order to set the domain and to register the hook
     * with WordPress.
     *
     * @since    0.1.0
     * @access   private
     */
    private function set_locale()
    {

        $plugin_i18n = new Job_Listings_i18n();

        $this->loader->add_action('plugins_loaded', $plugin_i18n, 'load_plugin_textdomain');

    }

    /**
     * Register all of the hooks related to the admin area functionality
     * of the plugin.
     *
     * @since    0.1.0
     * @access   private
     */
    private function define_admin_hooks()
    {

        $plugin_admin = new Job_Listings_Admin($this->get_plugin_name(), $this->get_version());

        $this->loader->add_action('admin_enqueue_scripts', $plugin_admin, 'enqueue_styles');
        $this->loader->add_action('admin_enqueue_scripts', $plugin_admin, 'enqueue_scripts');

    }

    /**
     * Register all of the hooks related to the public-facing functionality
     * of the plugin.
     *
     * @since    0.1.0
     * @access   private
     */
    private function define_public_hooks()
    {

        $plugin_public = new Job_Listings_Public($this->get_plugin_name(), $this->get_version());

        $this->loader->add_action('wp_enqueue_scripts', $plugin_public, 'enqueue_styles');
        $this->loader->add_action('wp_enqueue_scripts', $plugin_public, 'enqueue_scripts');

    }

    /**
     * Run the loader to execute all of the hooks with WordPress.
     *
     * @since    0.1.0
     */
    public function run()
    {
        $this->loader->run();
    }

    /**
     * The name of the plugin used to uniquely identify it within the context of
     * WordPress and to define internationalization functionality.
     *
     * @since     0.1.0
     * @return    string    The name of the plugin.
     */
    public function get_plugin_name()
    {
        return $this->plugin_name;
    }

    /**
     * The name of the plugin
     *
     * @since     0.1.0
     * @return    string    The real name of the plugin.
     */
    public function get_plugin_real_name()
    {
        return $this->plugin_real_name;
    }

    /**
     * Plugin url
     */

    public function get_plugin_url()
    {
        return $this->plugin_url;
    }

    /**
     * The reference to the class that orchestrates the hooks with the plugin.
     *
     * @since     0.1.0
     * @return    Job_Listings_Loader    Orchestrates the hooks of the plugin.
     */
    public function get_loader()
    {
        return $this->loader;
    }

    /**
     * Retrieve the version number of the plugin.
     *
     * @since     0.1.0
     * @return    string    The version number of the plugin.
     */
    public function get_version()
    {
        return $this->version;
    }

}

function job_listings()
{
    return Job_Listings::instance();
}