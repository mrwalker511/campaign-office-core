<?php
/**
 * Plugin Name: Campaign Office Core
 * Plugin URI: https://github.com/mrwalker511/campaign-office-core
 * Description: Core functionality for the Campaign Office theme. Provides custom post types, volunteer management, event management, and campaign-specific features that persist across theme changes.
 * Version: 2.0.0
 * Author: Matt Walker
 * Author URI: https://github.com/mrwalker511
 * License: GPL v2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: campaign-office-core
 * Domain Path: /languages
 * Requires at least: 5.8
 * Requires PHP: 7.4
 *
 * @package Campaign_Office_Core
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Main Campaign Office Core Plugin Class
 *
 * @since 1.0.0
 */
class Campaign_Office_Core {

    /**
     * Plugin version
     *
     * @var string
     */
    const VERSION = '2.0.0';

    /**
     * Singleton instance
     *
     * @var Campaign_Office_Core
     */
    private static $instance = null;

    /**
     * Plugin directory path
     *
     * @var string
     */
    private $plugin_path;

    /**
     * Plugin directory URL
     *
     * @var string
     */
    private $plugin_url;

    /**
     * Get singleton instance
     *
     * @return Campaign_Office_Core
     */
    public static function instance() {
        if (is_null(self::$instance)) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Constructor
     */
    private function __construct() {
        $this->plugin_path = plugin_dir_path(__FILE__);
        $this->plugin_url = plugin_dir_url(__FILE__);

        $this->define_constants();
        $this->init_hooks();
        $this->includes();
    }

    /**
     * Define plugin constants
     */
    private function define_constants() {
        define('CAMPAIGN_OFFICE_CORE_VERSION', self::VERSION);
        define('CAMPAIGN_OFFICE_CORE_PLUGIN_DIR', $this->plugin_path);
        define('CAMPAIGN_OFFICE_CORE_PLUGIN_URL', $this->plugin_url);
        define('CAMPAIGN_OFFICE_CORE_INCLUDES_DIR', $this->plugin_path . 'includes/');
    }

    /**
     * Initialize hooks
     */
    private function init_hooks() {
        // Activation and deactivation hooks
        register_activation_hook(__FILE__, array($this, 'activate'));
        register_deactivation_hook(__FILE__, array($this, 'deactivate'));

        // Load plugin textdomain
        add_action('plugins_loaded', array($this, 'load_textdomain'));

        // Plugin integration (allows themes to hook into plugin functionality)
        add_action('plugins_loaded', array($this, 'theme_integration'), 20);

        // Enqueue frontend assets
        add_action('wp_enqueue_scripts', array($this, 'enqueue_frontend_assets'));
    }

    /**
     * Include required files
     */
    private function includes() {
        // Contact manager must load first (other modules depend on it)
        require_once CAMPAIGN_OFFICE_CORE_INCLUDES_DIR . 'contact-manager.php';

        // Core functionality files
        require_once CAMPAIGN_OFFICE_CORE_INCLUDES_DIR . 'custom-post-types.php';
        require_once CAMPAIGN_OFFICE_CORE_INCLUDES_DIR . 'volunteer-management.php';
        require_once CAMPAIGN_OFFICE_CORE_INCLUDES_DIR . 'event-management.php';
        require_once CAMPAIGN_OFFICE_CORE_INCLUDES_DIR . 'event-calendar-enhancements.php';
    }

    /**
     * Plugin activation
     *
     * Flush rewrite rules to register custom post types
     */
    public function activate() {
        // Include files needed for activation
        require_once CAMPAIGN_OFFICE_CORE_INCLUDES_DIR . 'custom-post-types.php';

        // Flush rewrite rules
        flush_rewrite_rules();

        // Set activation flag
        update_option('campaign_office_core_activated', true);
        update_option('campaign_office_core_version', self::VERSION);
    }

    /**
     * Plugin deactivation
     *
     * Flush rewrite rules
     */
    public function deactivate() {
        flush_rewrite_rules();
    }

    /**
     * Load plugin textdomain for translations
     */
    public function load_textdomain() {
        load_plugin_textdomain(
            'campaign-office-core',
            false,
            dirname(plugin_basename(__FILE__)) . '/languages/'
        );
    }

    /**
     * Theme integration
     *
     * Allows themes to detect and integrate with this plugin
     */
    public function theme_integration() {
        /**
         * Fires when Campaign Office Core plugin is loaded
         *
         * Allows themes to hook into plugin functionality
         *
         * @since 1.0.0
         */
        do_action('campaign_office_core_loaded');

        /**
         * Filter to allow themes to extend plugin functionality
         *
         * @since 1.0.0
         * @param array $features Array of feature flags
         */
        $features = apply_filters('campaign_office_core_features', array(
            'custom_post_types' => true,
            'volunteer_management' => true,
            'event_management' => true,
        ));

        // Make features available globally if needed
        $GLOBALS['campaign_office_core_features'] = $features;
    }

    /**
     * Get plugin path
     *
     * @return string
     */
    public function get_plugin_path() {
        return $this->plugin_path;
    }

    /**
     * Get plugin URL
     *
     * @return string
     */
    public function get_plugin_url() {
        return $this->plugin_url;
    }

    /**
     * Enqueue frontend assets
     * 
     * Only loads assets when needed (on CPT pages or when shortcodes are present)
     */
    public function enqueue_frontend_assets() {
        global $post;
        
        $should_load = false;
        
        // Load on single CPT pages
        if (is_singular(['cp_event', 'cp_volunteer', 'cp_issue', 'cp_endorsement', 'cp_team', 'cp_press_release'])) {
            $should_load = true;
        }
        
        // Load on CPT archives
        if (is_post_type_archive(['cp_event', 'cp_volunteer', 'cp_issue', 'cp_endorsement', 'cp_team', 'cp_press_release'])) {
            $should_load = true;
        }
        
        // Check for shortcodes in post content
        if (isset($post->post_content)) {
            $shortcodes = ['cp_volunteer_form', 'cp_event_rsvp', 'cp_event_calendar', 'cp_event_map'];
            foreach ($shortcodes as $shortcode) {
                if (has_shortcode($post->post_content, $shortcode)) {
                    $should_load = true;
                    break;
                }
            }
        }
        
        // Allow themes/plugins to force loading
        $should_load = apply_filters('campaign_office_core_load_assets', $should_load);
        
        if (!$should_load) {
            return;
        }
        
        wp_enqueue_style(
            'campaign-office-core',
            $this->plugin_url . 'assets/css/frontend.css',
            array(),
            self::VERSION
        );

        wp_enqueue_script(
            'campaign-office-core',
            $this->plugin_url . 'assets/js/frontend.js',
            array('jquery'),
            self::VERSION,
            true
        );

        wp_localize_script('campaign-office-core', 'campaignOfficeCore', array(
            'ajaxUrl' => admin_url('admin-ajax.php'),
            'calendarNonce' => wp_create_nonce('cp_calendar_events')
        ));
    }
}

/**
 * Initialize the plugin
 *
 * @return Campaign_Office_Core
 */
function campaign_office_core() {
    return Campaign_Office_Core::instance();
}

// Kick off the plugin
campaign_office_core();
