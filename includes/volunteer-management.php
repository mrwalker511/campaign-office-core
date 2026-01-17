<?php
/**
 * Enhanced Volunteer Management
 *
 * Provides volunteer data capture, contact management, and basic CRM functionality for the free version.
 * Volunteers can sign up through forms, and campaign staff can manage contacts in the admin.
 *
 * @package CampaignPress_Core
 * @since 2.0.0
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Class CP_Volunteer_Manager
 *
 * Handles volunteer signup, data capture, and basic contact management
 */
class CP_Volunteer_Manager {

    /**
     * Database table name for volunteers
     *
     * @var string
     */
    private $table_name;

    /**
     * Constructor
     */
    public function __construct() {
        global $wpdb;
        $this->table_name = $wpdb->prefix . 'cp_volunteers';

        // Database setup - only create tables when needed
        add_action('admin_init', array($this, 'maybe_create_volunteer_table'));
        add_action('plugins_loaded', array($this, 'maybe_create_volunteer_table'));

        // Admin menu
        add_action('admin_menu', array($this, 'add_admin_menu'));

        // AJAX handlers for volunteer signups
        add_action('wp_ajax_cp_submit_volunteer_signup', array($this, 'handle_volunteer_signup'));
        add_action('wp_ajax_nopriv_cp_submit_volunteer_signup', array($this, 'handle_volunteer_signup'));

        // Shortcode for volunteer signup form
        add_shortcode('cp_volunteer_form', array($this, 'render_volunteer_form'));

        // Admin scripts and styles
        add_action('admin_enqueue_scripts', array($this, 'enqueue_admin_assets'));

        // Export functionality
        add_action('admin_post_cp_export_volunteers', array($this, 'export_volunteers_csv'));
    }

    /**
     * Maybe create volunteer table - only runs once
     * Prevents table creation on every page load
     */
    public function maybe_create_volunteer_table() {
        // Check if we've already created the table for this version
        $db_version = get_option('cp_volunteer_db_version', '0');
        $current_version = '2.0.0';

        if (version_compare($db_version, $current_version, '<')) {
            $this->create_volunteer_table();
            update_option('cp_volunteer_db_version', $current_version);
        }
    }

    /**
     * Create volunteer database table
     *
     * Stores volunteer contact information, interests, and availability
     */
    public function create_volunteer_table() {
        global $wpdb;

        $charset_collate = $wpdb->get_charset_collate();

        $sql = "CREATE TABLE IF NOT EXISTS {$this->table_name} (
            id bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
            contact_id bigint(20) UNSIGNED DEFAULT NULL,
            skills text DEFAULT NULL,
            interests text DEFAULT NULL,
            availability text DEFAULT NULL,
            volunteer_type varchar(50) DEFAULT 'general',
            status varchar(20) DEFAULT 'new',
            notes text DEFAULT NULL,
            source varchar(100) DEFAULT NULL,
            opportunity_id bigint(20) DEFAULT NULL,
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            KEY contact_id (contact_id),
            KEY status (status),
            KEY created_at (created_at)
        ) $charset_collate;";

        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);

        // Set table creation flag
        update_option('cp_volunteer_table_created', true);
    }

    /**
     * Add admin menu for volunteer management with enhanced security
     */
    public function add_admin_menu() {
        // Check if user has required capabilities
        if (!current_user_can('edit_posts')) {
            return;
        }

        add_submenu_page(
            'edit.php?post_type=cp_volunteer',
            __('Volunteer Signups', 'campaignpress-core'),
            __('Signups', 'campaignpress-core'),
            'edit_posts',
            'cp-volunteer-signups',
            array($this, 'render_admin_page')
        );
    }

    /**
     * Enqueue admin assets
     *
     * @param string $hook Current admin page hook
     */
    public function enqueue_admin_assets($hook) {
        if ($hook !== 'cp_volunteer_page_cp-volunteer-signups') {
            return;
        }

        // Use plugin directory for assets
        $css_file = CAMPAIGN_OFFICE_CORE_PLUGIN_DIR . 'assets/css/volunteer-admin.css';
        $js_file = CAMPAIGN_OFFICE_CORE_PLUGIN_DIR . 'assets/js/volunteer-admin.js';

        if (file_exists($css_file)) {
            wp_enqueue_style('cp-volunteer-admin', CAMPAIGN_OFFICE_CORE_PLUGIN_URL . 'assets/css/volunteer-admin.css', array(), CAMPAIGN_OFFICE_CORE_VERSION);
        }

        if (file_exists($js_file)) {
            wp_enqueue_script('cp-volunteer-admin', CAMPAIGN_OFFICE_CORE_PLUGIN_URL . 'assets/js/volunteer-admin.js', array('jquery'), CAMPAIGN_OFFICE_CORE_VERSION, true);
        }
    }

    /**
     * Render volunteer signup form shortcode
     *
     * Usage: [cp_volunteer_form opportunity_id="123"]
     *
     * @param array $atts Shortcode attributes
     * @return string Form HTML
     */
    public function render_volunteer_form($atts) {
        $atts = shortcode_atts(array(
            'opportunity_id' => '',
            'title' => __('Volunteer Sign Up', 'campaignpress-core'),
            'submit_text' => __('Sign Me Up!', 'campaignpress-core'),
        ), $atts);

        // Allow themes/plugins to modify shortcode attributes
        $atts = apply_filters('cp_volunteer_form_atts', $atts);

        // Action hook before form rendering
        do_action('cp_before_volunteer_form_render', $atts);

        ob_start();
        ?>
        <div class="cp-volunteer-form-wrapper">
            <h3><?php echo esc_html($atts['title']); ?></h3>
            <form class="cp-volunteer-signup-form" data-opportunity-id="<?php echo esc_attr($atts['opportunity_id']); ?>">
                <?php wp_nonce_field('cp_volunteer_signup', 'cp_volunteer_nonce'); ?>

                <div class="cp-form-row">
                    <div class="cp-form-group">
                        <label for="cp_volunteer_first_name"><?php esc_html_e('First Name', 'campaignpress-core'); ?> <span class="required">*</span></label>
                        <input type="text" id="cp_volunteer_first_name" name="first_name" required>
                    </div>

                    <div class="cp-form-group">
                        <label for="cp_volunteer_last_name"><?php esc_html_e('Last Name', 'campaignpress-core'); ?> <span class="required">*</span></label>
                        <input type="text" id="cp_volunteer_last_name" name="last_name" required>
                    </div>
                </div>

                <div class="cp-form-row">
                    <div class="cp-form-group">
                        <label for="cp_volunteer_email"><?php esc_html_e('Email', 'campaignpress-core'); ?> <span class="required">*</span></label>
                        <input type="email" id="cp_volunteer_email" name="email" required>
                    </div>

                    <div class="cp-form-group">
                        <label for="cp_volunteer_phone"><?php esc_html_e('Phone', 'campaignpress-core'); ?></label>
                        <input type="tel" id="cp_volunteer_phone" name="phone">
                    </div>
                </div>

                <div class="cp-form-group">
                    <label for="cp_volunteer_address"><?php esc_html_e('Street Address', 'campaignpress-core'); ?></label>
                    <input type="text" id="cp_volunteer_address" name="address">
                </div>

                <div class="cp-form-row">
                    <div class="cp-form-group">
                        <label for="cp_volunteer_city"><?php esc_html_e('City', 'campaignpress-core'); ?></label>
                        <input type="text" id="cp_volunteer_city" name="city">
                    </div>

                    <div class="cp-form-group cp-form-group-small">
                        <label for="cp_volunteer_state"><?php esc_html_e('State', 'campaignpress-core'); ?></label>
                        <input type="text" id="cp_volunteer_state" name="state" maxlength="2" placeholder="CA">
                    </div>

                    <div class="cp-form-group cp-form-group-small">
                        <label for="cp_volunteer_zip"><?php esc_html_e('ZIP', 'campaignpress-core'); ?></label>
                        <input type="text" id="cp_volunteer_zip" name="zip" maxlength="10">
                    </div>
                </div>

                <div class="cp-form-group">
                    <label><?php esc_html_e('I am interested in:', 'campaignpress-core'); ?></label>
                    <div class="cp-checkbox-group">
                        <label><input type="checkbox" name="interests[]" value="canvassing"> <?php esc_html_e('Door-to-door canvassing', 'campaignpress-core'); ?></label>
                        <label><input type="checkbox" name="interests[]" value="phone_banking"> <?php esc_html_e('Phone banking', 'campaignpress-core'); ?></label>
                        <label><input type="checkbox" name="interests[]" value="event_support"> <?php esc_html_e('Event support', 'campaignpress-core'); ?></label>
                        <label><input type="checkbox" name="interests[]" value="data_entry"> <?php esc_html_e('Data entry', 'campaignpress-core'); ?></label>
                        <label><input type="checkbox" name="interests[]" value="social_media"> <?php esc_html_e('Social media outreach', 'campaignpress-core'); ?></label>
                        <label><input type="checkbox" name="interests[]" value="fundraising"> <?php esc_html_e('Fundraising', 'campaignpress-core'); ?></label>
                    </div>
                </div>

                <div class="cp-form-group">
                    <label><?php esc_html_e('Availability:', 'campaignpress-core'); ?></label>
                    <div class="cp-checkbox-group">
                        <label><input type="checkbox" name="availability[]" value="weekday_mornings"> <?php esc_html_e('Weekday mornings', 'campaignpress-core'); ?></label>
                        <label><input type="checkbox" name="availability[]" value="weekday_afternoons"> <?php esc_html_e('Weekday afternoons', 'campaignpress-core'); ?></label>
                        <label><input type="checkbox" name="availability[]" value="weekday_evenings"> <?php esc_html_e('Weekday evenings', 'campaignpress-core'); ?></label>
                        <label><input type="checkbox" name="availability[]" value="weekends"> <?php esc_html_e('Weekends', 'campaignpress-core'); ?></label>
                    </div>
                </div>

                <div class="cp-form-group">
                    <label for="cp_volunteer_skills"><?php esc_html_e('Skills/Experience (optional)', 'campaignpress-core'); ?></label>
                    <textarea id="cp_volunteer_skills" name="skills" rows="3" placeholder="<?php esc_attr_e('e.g., graphic design, Spanish speaker, social media marketing', 'campaignpress-core'); ?>"></textarea>
                </div>

                <div class="cp-form-message"></div>

                <button type="submit" class="cp-volunteer-submit-btn"><?php echo esc_html($atts['submit_text']); ?></button>
            </form>
        </div>

        // Script moved to assets/js/frontend.js
        <?php

        // Action hook after form rendering
        do_action('cp_after_volunteer_form_render', $atts);

        return ob_get_clean();
    }

    /**
     * Handle volunteer signup AJAX submission
     */
    public function handle_volunteer_signup() {
        // Rate limiting - maximum 3 submissions per hour per IP
        $ip = $this->get_client_ip();
        $rate_limit_key = 'cp_volunteer_rate_limit_' . md5($ip);
        $submissions = get_transient($rate_limit_key);
        
        if ($submissions && $submissions >= 3) {
            wp_send_json_error(array('message' => __('Too many submissions. Please try again later.', 'campaignpress-core')));
        }

        // Verify nonce
        if (!isset($_POST['cp_volunteer_nonce']) || !wp_verify_nonce($_POST['cp_volunteer_nonce'], 'cp_volunteer_signup')) {
            wp_send_json_error(array('message' => __('Security verification failed.', 'campaignpress-core')));
        }

        // Input validation and sanitization
        $first_name = sanitize_text_field($_POST['first_name'] ?? '');
        $last_name = sanitize_text_field($_POST['last_name'] ?? '');
        $email = sanitize_email($_POST['email'] ?? '');
        
        if (empty($first_name) || empty($last_name) || empty($email)) {
            wp_send_json_error(array('message' => __('Please fill in all required fields.', 'campaignpress-core')));
        }
        
        // Email validation
        if (!is_email($email)) {
            wp_send_json_error(array('message' => __('Please provide a valid email address.', 'campaignpress-core')));
        }
        
        // Length validation
        if (strlen($first_name) > 100 || strlen($last_name) > 100) {
            wp_send_json_error(array('message' => __('Name fields are too long.', 'campaignpress-core')));
        }
        
        // Update rate limit
        if ($submissions) {
            set_transient($rate_limit_key, $submissions + 1, HOUR_IN_SECONDS);
        } else {
            set_transient($rate_limit_key, 1, HOUR_IN_SECONDS);
        }

        // Identify or Create Contact
        global $cp_contact_manager;
        $contact_id = null;

        if ($cp_contact_manager && method_exists($cp_contact_manager, 'find_or_create')) {
            $contact_id = $cp_contact_manager->find_or_create(array(
                'first_name'    => sanitize_text_field($_POST['first_name']),
                'last_name'     => sanitize_text_field($_POST['last_name']),
                'email'         => sanitize_email($_POST['email']),
                'phone'         => isset($_POST['phone']) ? sanitize_text_field($_POST['phone']) : '',
                'address_line1' => isset($_POST['address']) ? sanitize_text_field($_POST['address']) : '',
                'city'          => isset($_POST['city']) ? sanitize_text_field($_POST['city']) : '',
                'state'         => isset($_POST['state']) ? strtoupper(sanitize_text_field($_POST['state'])) : '',
                'zip_code'      => isset($_POST['zip']) ? sanitize_text_field($_POST['zip']) : '',
            ));

            if (is_wp_error($contact_id)) {
                wp_send_json_error(array('message' => $contact_id->get_error_message()));
            }
        }

        // Sanitize volunteer-specific data with enhanced validation
        $skills = '';
        if (isset($_POST['skills'])) {
            $skills_text = sanitize_textarea_field($_POST['skills']);
            if (strlen($skills_text) > 1000) {
                wp_send_json_error(array('message' => __('Skills field is too long.', 'campaignpress-core')));
            }
            $skills = $skills_text;
        }
        
        $interests = '';
        if (isset($_POST['interests']) && is_array($_POST['interests'])) {
            $clean_interests = array();
            foreach ($_POST['interests'] as $interest) {
                $clean_interest = sanitize_text_field($interest);
                if (!empty($clean_interest) && strlen($clean_interest) <= 100) {
                    $clean_interests[] = $clean_interest;
                }
            }
            $interests = wp_json_encode($clean_interests);
        }
        
        $availability = '';
        if (isset($_POST['availability']) && is_array($_POST['availability'])) {
            $clean_availability = array();
            foreach ($_POST['availability'] as $slot) {
                $clean_slot = sanitize_text_field($slot);
                if (!empty($clean_slot) && strlen($clean_slot) <= 100) {
                    $clean_availability[] = $clean_slot;
                }
            }
            $availability = wp_json_encode($clean_availability);
        }
        
        $opportunity_id = null;
        if (isset($_POST['opportunity_id'])) {
            $opp_id = absint($_POST['opportunity_id']);
            if ($opp_id > 0) {
                $opportunity_id = $opp_id;
            }
        }

        $volunteer_data = array(
            'contact_id'     => $contact_id,
            'skills'         => $skills,
            'interests'      => $interests,
            'availability'   => $availability,
            'opportunity_id' => $opportunity_id,
            'source'         => 'website_form',
            'status'         => 'new',
        );

        // Enhanced database insert with prepared statements
        global $wpdb;
        $result = $wpdb->insert($this->table_name, $volunteer_data, array(
            '%d', '%s', '%s', '%s', '%d', '%s', '%s'
        ));

        if ($result === false) {
            // Log database error for debugging (in production, log to error log)
            error_log('Campaign Office Core: Database error in volunteer signup - ' . $wpdb->last_error);
            wp_send_json_error(array('message' => __('Failed to save volunteer information. Please try again.', 'campaignpress-core')));
        }

        // Log successful volunteer signup for security audit
        $this->log_security_event('volunteer_signup_success', array(
            'contact_id' => $contact_id,
            'opportunity_id' => $opportunity_id,
            'ip' => $ip
        ));

        if ($result) {
            // Allow other plugins to hook into volunteer signup
            do_action('cp_volunteer_signup_success', $wpdb->insert_id, $volunteer_data);

            wp_send_json_success(array(
                'message' => __('Thank you for signing up! We\'ll be in touch soon.', 'campaignpress-core'),
                'volunteer_id' => $wpdb->insert_id
            ));
        } else {
            wp_send_json_error(array('message' => __('Failed to save volunteer information. Please try again.', 'campaignpress-core')));
        }
    }

    /**
     * Render admin page for volunteer management
     */
    public function render_admin_page() {
        // Security: Check permissions
        if (!current_user_can('edit_posts')) {
            wp_die(esc_html__('You do not have permission to access this page.', 'campaignpress-core'));
        }

        // Handle bulk actions with enhanced security
        if (isset($_POST['cp_bulk_action']) && isset($_POST['volunteer_ids']) && is_array($_POST['volunteer_ids'])) {
            // Verify nonce and capabilities
            if (!check_admin_referer('cp_volunteer_bulk_action', 'cp_volunteer_bulk_nonce') || 
                !current_user_can('delete_posts')) {
                wp_die(esc_html__('Security verification failed.', 'campaignpress-core'));
            }
            $this->handle_bulk_actions();
        }

        // Handle individual volunteer deletion with enhanced security
        if (isset($_GET['action']) && $_GET['action'] === 'delete' && isset($_GET['volunteer_id'])) {
            // Verify nonce and capabilities
            $nonce_key = 'cp_delete_volunteer_' . absint($_GET['volunteer_id']);
            if (!check_admin_referer($nonce_key) || !current_user_can('delete_posts')) {
                wp_die(esc_html__('Security verification failed.', 'campaignpress-core'));
            }
            $this->delete_volunteer(absint($_GET['volunteer_id']));
        }

        // Get filter parameters with sanitization
        $status_filter = isset($_GET['status']) ? sanitize_text_field($_GET['status']) : '';
        $search = isset($_GET['s']) ? sanitize_text_field($_GET['s']) : '';

        // Additional search validation
        if (!empty($search) && strlen($search) > 100) {
            wp_die(esc_html__('Search query too long.', 'campaignpress-core'));
        }

        // Build query
        $contacts_table = $wpdb->prefix . 'cp_contacts';
        $where = array('1=1');
        if ($status_filter) {
            $where[] = $wpdb->prepare('v.status = %s', $status_filter);
        }
        if ($search) {
            $where[] = $wpdb->prepare(
                '(c.first_name LIKE %s OR c.last_name LIKE %s OR c.email LIKE %s)',
                '%' . $wpdb->esc_like($search) . '%',
                '%' . $wpdb->esc_like($search) . '%',
                '%' . $wpdb->esc_like($search) . '%'
            );
        }

        $where_clause = implode(' AND ', $where);

        // Pagination
        $per_page = 20;
        $page = isset($_GET['paged']) ? max(1, absint($_GET['paged'])) : 1;
        $offset = ($page - 1) * $per_page;

        // Get total count
        $total_volunteers = $wpdb->get_var("SELECT COUNT(*) FROM {$this->table_name} v JOIN {$contacts_table} c ON v.contact_id = c.id WHERE {$where_clause}");
        $total_pages = ceil($total_volunteers / $per_page);

        // Get volunteers
        $volunteers = $wpdb->get_results(
            $wpdb->prepare(
                "SELECT v.*, c.first_name, c.last_name, c.email, c.phone, c.city, c.state 
                 FROM {$this->table_name} v 
                 JOIN {$contacts_table} c ON v.contact_id = c.id 
                 WHERE {$where_clause} 
                 ORDER BY v.created_at DESC LIMIT %d OFFSET %d",
                $per_page,
                $offset
            )
        );

        // Get status counts
        $status_counts = $wpdb->get_results(
            "SELECT status, COUNT(*) as count FROM {$this->table_name} GROUP BY status",
            OBJECT_K
        );

        ?>
        <div class="wrap">
            <h1 class="wp-heading-inline"><?php esc_html_e('Volunteer Signups', 'campaignpress-core'); ?></h1>
            <a href="<?php echo esc_url(admin_url('post-new.php?post_type=cp_volunteer')); ?>" class="page-title-action"><?php esc_html_e('Add Volunteer Opportunity', 'campaignpress-core'); ?></a>
            <a href="<?php echo esc_url(wp_nonce_url(admin_url('admin-post.php?action=cp_export_volunteers'), 'cp_export_volunteers')); ?>" class="page-title-action"><?php esc_html_e('Export to CSV', 'campaignpress-core'); ?></a>

            <hr class="wp-header-end">

            <ul class="subsubsub">
                <li><a href="<?php echo esc_url(remove_query_arg('status')); ?>" <?php echo empty($status_filter) ? 'class="current"' : ''; ?>><?php esc_html_e('All', 'campaignpress-core'); ?> <span class="count">(<?php echo esc_html($total_volunteers); ?>)</span></a> |</li>
                <li><a href="<?php echo esc_url(add_query_arg('status', 'new')); ?>" <?php echo $status_filter === 'new' ? 'class="current"' : ''; ?>><?php esc_html_e('New', 'campaignpress-core'); ?> <span class="count">(<?php echo isset($status_counts['new']) ? esc_html($status_counts['new']->count) : '0'; ?>)</span></a> |</li>
                <li><a href="<?php echo esc_url(add_query_arg('status', 'contacted')); ?>" <?php echo $status_filter === 'contacted' ? 'class="current"' : ''; ?>><?php esc_html_e('Contacted', 'campaignpress-core'); ?> <span class="count">(<?php echo isset($status_counts['contacted']) ? esc_html($status_counts['contacted']->count) : '0'; ?>)</span></a> |</li>
                <li><a href="<?php echo esc_url(add_query_arg('status', 'active')); ?>" <?php echo $status_filter === 'active' ? 'class="current"' : ''; ?>><?php esc_html_e('Active', 'campaignpress-core'); ?> <span class="count">(<?php echo isset($status_counts['active']) ? esc_html($status_counts['active']->count) : '0'; ?>)</span></a></li>
            </ul>

            <form method="get">
                <input type="hidden" name="post_type" value="cp_volunteer">
                <input type="hidden" name="page" value="cp-volunteer-signups">
                <?php if ($status_filter) : ?>
                    <input type="hidden" name="status" value="<?php echo esc_attr($status_filter); ?>">
                <?php endif; ?>

                <p class="search-box">
                    <label class="screen-reader-text" for="volunteer-search-input"><?php esc_html_e('Search Volunteers:', 'campaignpress-core'); ?></label>
                    <input type="search" id="volunteer-search-input" name="s" value="<?php echo esc_attr($search); ?>">
                    <input type="submit" class="button" value="<?php esc_attr_e('Search Volunteers', 'campaignpress-core'); ?>">
                </p>
            </form>

            <form method="post">
                <?php wp_nonce_field('cp_volunteer_bulk_action', 'cp_volunteer_bulk_nonce'); ?>

                <div class="tablenav top">
                    <div class="alignleft actions bulkactions">
                        <select name="cp_bulk_action">
                            <option value=""><?php esc_html_e('Bulk Actions', 'campaignpress-core'); ?></option>
                            <option value="contacted"><?php esc_html_e('Mark as Contacted', 'campaignpress-core'); ?></option>
                            <option value="active"><?php esc_html_e('Mark as Active', 'campaignpress-core'); ?></option>
                            <option value="delete"><?php esc_html_e('Delete', 'campaignpress-core'); ?></option>
                        </select>
                        <input type="submit" class="button action" value="<?php esc_attr_e('Apply', 'campaignpress-core'); ?>">
                    </div>
                </div>

                <table class="wp-list-table widefat fixed striped">
                    <thead>
                        <tr>
                            <td class="manage-column column-cb check-column">
                                <input type="checkbox" id="cb-select-all">
                            </td>
                            <th><?php esc_html_e('Name', 'campaignpress-core'); ?></th>
                            <th><?php esc_html_e('Email', 'campaignpress-core'); ?></th>
                            <th><?php esc_html_e('Phone', 'campaignpress-core'); ?></th>
                            <th><?php esc_html_e('Location', 'campaignpress-core'); ?></th>
                            <th><?php esc_html_e('Interests', 'campaignpress-core'); ?></th>
                            <th><?php esc_html_e('Status', 'campaignpress-core'); ?></th>
                            <th><?php esc_html_e('Date', 'campaignpress-core'); ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($volunteers)) : ?>
                            <tr>
                                <td colspan="8"><?php esc_html_e('No volunteers found.', 'campaignpress-core'); ?></td>
                            </tr>
                        <?php else : ?>
                            <?php foreach ($volunteers as $volunteer) : ?>
                                <tr>
                                    <th scope="row" class="check-column">
                                        <input type="checkbox" name="volunteer_ids[]" value="<?php echo esc_attr($volunteer->id); ?>">
                                    </th>
                                    <td>
                                        <strong><?php echo esc_html($volunteer->first_name . ' ' . $volunteer->last_name); ?></strong>
                                        <div class="row-actions">
                                            <span><a href="mailto:<?php echo esc_attr($volunteer->email); ?>"><?php esc_html_e('Email', 'campaignpress-core'); ?></a> | </span>
                                            <span class="trash"><a href="<?php echo esc_url(wp_nonce_url(add_query_arg(array('action' => 'delete', 'volunteer_id' => $volunteer->id)), 'cp_delete_volunteer_' . $volunteer->id)); ?>" class="submitdelete"><?php esc_html_e('Delete', 'campaignpress-core'); ?></a></span>
                                        </div>
                                    </td>
                                    <td><?php echo esc_html($volunteer->email); ?></td>
                                    <td><?php echo esc_html($volunteer->phone); ?></td>
                                    <td><?php echo esc_html($volunteer->city ? $volunteer->city . ', ' . $volunteer->state : '—'); ?></td>
                                    <td><?php
                                        $interests = json_decode($volunteer->interests, true);
                                        echo $interests ? esc_html(implode(', ', $interests)) : '—';
                                    ?></td>
                                    <td><span class="cp-status-badge cp-status-<?php echo esc_attr($volunteer->status); ?>"><?php echo esc_html(ucfirst($volunteer->status)); ?></span></td>
                                    <td><?php echo esc_html(date_i18n(get_option('date_format'), strtotime($volunteer->created_at))); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>

                <?php if ($total_pages > 1) : ?>
                    <div class="tablenav bottom">
                        <div class="tablenav-pages">
                            <?php
                            echo paginate_links(array(
                                'base' => add_query_arg('paged', '%#%'),
                                'format' => '',
                                'prev_text' => '&laquo;',
                                'next_text' => '&raquo;',
                                'total' => $total_pages,
                                'current' => $page,
                            ));
                            ?>
                        </div>
                    </div>
                <?php endif; ?>
            </form>
        </div>

        <style>
            .cp-status-badge {
                display: inline-block;
                padding: 3px 8px;
                border-radius: 3px;
                font-size: 12px;
                font-weight: 600;
            }
            .cp-status-new { background: #e5f5ff; color: #0073aa; }
            .cp-status-contacted { background: #fff4e5; color: #f56e28; }
            .cp-status-active { background: #e8f5e9; color: #46b450; }
        </style>
        <?php
    }

    /**
     * Handle bulk actions on volunteers
     */
    private function handle_bulk_actions() {
        if (empty($_POST['volunteer_ids']) || empty($_POST['cp_bulk_action'])) {
            return;
        }

        global $wpdb;
        $action = sanitize_text_field($_POST['cp_bulk_action']);
        $volunteer_ids = array_map('absint', $_POST['volunteer_ids']);

        if ($action === 'delete') {
            foreach ($volunteer_ids as $id) {
                $wpdb->delete($this->table_name, array('id' => $id), array('%d'));
            }
            echo '<div class="notice notice-success"><p>' . esc_html__('Volunteers deleted.', 'campaignpress-core') . '</p></div>';
        } elseif (in_array($action, array('contacted', 'active'), true)) {
            foreach ($volunteer_ids as $id) {
                $wpdb->update(
                    $this->table_name,
                    array('status' => $action),
                    array('id' => $id),
                    array('%s'),
                    array('%d')
                );
            }
            echo '<div class="notice notice-success"><p>' . esc_html__('Volunteers updated.', 'campaignpress-core') . '</p></div>';
        }
    }

    /**
     * Delete a single volunteer
     *
     * @param int $volunteer_id Volunteer ID
     */
    private function delete_volunteer($volunteer_id) {
        global $wpdb;
        $wpdb->delete($this->table_name, array('id' => $volunteer_id), array('%d'));

        echo '<div class="notice notice-success"><p>' . esc_html__('Volunteer deleted.', 'campaignpress-core') . '</p></div>';
    }

    /**
     * Get client IP address for rate limiting
     *
     * @return string Client IP address
     */
    private function get_client_ip() {
        $ip_keys = array('HTTP_X_FORWARDED_FOR', 'HTTP_X_REAL_IP', 'HTTP_CLIENT_IP', 'REMOTE_ADDR');
        
        foreach ($ip_keys as $key) {
            if (!empty($_SERVER[$key])) {
                $ip = $_SERVER[$key];
                // Handle comma-separated IPs (proxies)
                if (strpos($ip, ',') !== false) {
                    $ip = trim(explode(',', $ip)[0]);
                }
                // Validate IP address format
                if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE)) {
                    return $ip;
                }
            }
        }
        
        // Fallback to REMOTE_ADDR if no valid IP found
        return $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';
    }

    /**
     * Log security events for audit trail
     *
     * @param string $event_type Type of security event
     * @param array  $data       Event data
     */
    private function log_security_event($event_type, $data = array()) {
        // Only log in production environment
        if (defined('WP_DEBUG') && WP_DEBUG) {
            return;
        }
        
        $log_entry = array(
            'timestamp' => current_time('mysql'),
            'event_type' => $event_type,
            'user_id' => get_current_user_id(),
            'ip_address' => $this->get_client_ip(),
            'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? '',
            'data' => $data
        );
        
        // Log to WordPress error log
        error_log('Campaign Office Core Security Event: ' . wp_json_encode($log_entry));
        
        // Store critical events in database for admin review
        if (in_array($event_type, array('volunteer_signup_success', 'volunteer_signup_failed', 'bulk_delete', 'export_data'), true)) {
            $this->store_security_log($log_entry);
        }
    }

    /**
     * Store security log in database for admin review
     *
     * @param array $log_entry Security log entry
     */
    private function store_security_log($log_entry) {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'cp_security_logs';
        
        // Create table if it doesn't exist
        $this->create_security_logs_table();
        
        $wpdb->insert($table_name, array(
            'event_type' => $log_entry['event_type'],
            'user_id' => $log_entry['user_id'],
            'ip_address' => $log_entry['ip_address'],
            'user_agent' => substr($log_entry['user_agent'], 0, 500), // Limit length
            'event_data' => wp_json_encode($log_entry['data']),
            'created_at' => $log_entry['timestamp']
        ), array('%s', '%d', '%s', '%s', '%s', '%s'));
    }

    /**
     * Create security logs table
     */
    private function create_security_logs_table() {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'cp_security_logs';
        $charset_collate = $wpdb->get_charset_collate();

        $sql = "CREATE TABLE IF NOT EXISTS {$table_name} (
            id bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
            event_type varchar(50) NOT NULL,
            user_id bigint(20) UNSIGNED DEFAULT 0,
            ip_address varchar(45) NOT NULL,
            user_agent text DEFAULT NULL,
            event_data text DEFAULT NULL,
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            KEY event_type (event_type),
            KEY user_id (user_id),
            KEY created_at (created_at)
        ) $charset_collate;";

        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);
    }

    /**
     * Export volunteers to CSV with enhanced security
     */
    public function export_volunteers_csv() {
        // Verify nonce
        if (!isset($_GET['_wpnonce']) || !wp_verify_nonce($_GET['_wpnonce'], 'cp_export_volunteers')) {
            wp_die(esc_html__('Security verification failed.', 'campaignpress-core'));
        }

        // Check permissions with enhanced security
        if (!current_user_can('edit_posts')) {
            wp_die(esc_html__('You do not have permission to export volunteers.', 'campaignpress-core'));
        }

        // Log export activity for security audit
        $this->log_security_event('export_data', array(
            'data_type' => 'volunteers',
            'export_time' => current_time('mysql')
        ));

        global $wpdb;
        $contacts_table = $wpdb->prefix . 'cp_contacts';
        
        // Use prepared statement to prevent SQL injection
        $query = "
            SELECT v.*, c.first_name, c.last_name, c.email, c.phone, c.address_line1 as address, c.city, c.state, c.zip_code as zip
            FROM {$this->table_name} v
            JOIN {$contacts_table} c ON v.contact_id = c.id
            ORDER BY v.created_at DESC
        ";
        
        $volunteers = $wpdb->get_results($query, ARRAY_A);

        // Set headers for CSV download with security headers
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename=volunteers-' . date('Y-m-d-H-i-s') . '.csv');
        header('X-Content-Type-Options: nosniff');
        header('X-Frame-Options: DENY');

        $output = fopen('php://output', 'w');

        // Add BOM for Excel UTF-8 compatibility
        fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));

        // Add column headers
        fputcsv($output, array(
            'ID', 'First Name', 'Last Name', 'Email', 'Phone', 'Address', 'City', 'State', 'ZIP',
            'Skills', 'Interests', 'Availability', 'Status', 'Source', 'Created Date'
        ));

        // Add data rows with proper escaping
        foreach ($volunteers as $volunteer) {
            fputcsv($output, array(
                $volunteer['id'],
                $volunteer['first_name'],
                $volunteer['last_name'],
                $volunteer['email'],
                $volunteer['phone'],
                $volunteer['address'],
                $volunteer['city'],
                $volunteer['state'],
                $volunteer['zip'],
                $volunteer['skills'],
                $volunteer['interests'],
                $volunteer['availability'],
                $volunteer['status'],
                $volunteer['source'],
                $volunteer['created_at'],
            ));
        }

        fclose($output);
        exit;
    }
}

// Initialize volunteer manager
new CP_Volunteer_Manager();
