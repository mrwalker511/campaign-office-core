<?php
/**
 * Enhanced Volunteer Management
 *
 * Provides volunteer data capture, contact management, and basic CRM functionality for the free version.
 * Volunteers can sign up through forms, and campaign staff can manage contacts in the admin.
 *
 * @package Campaign_Office_Core
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
     * Add admin menu for volunteer management
     */
    public function add_admin_menu() {
        add_submenu_page(
            'edit.php?post_type=cp_volunteer',
            __('Volunteer Signups', 'campaign-office-core'),
            __('Signups', 'campaign-office-core'),
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
            'title' => __('Volunteer Sign Up', 'campaign-office-core'),
            'submit_text' => __('Sign Me Up!', 'campaign-office-core'),
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
                        <label for="cp_volunteer_first_name"><?php esc_html_e('First Name', 'campaign-office-core'); ?> <span class="required">*</span></label>
                        <input type="text" id="cp_volunteer_first_name" name="first_name" required>
                    </div>

                    <div class="cp-form-group">
                        <label for="cp_volunteer_last_name"><?php esc_html_e('Last Name', 'campaign-office-core'); ?> <span class="required">*</span></label>
                        <input type="text" id="cp_volunteer_last_name" name="last_name" required>
                    </div>
                </div>

                <div class="cp-form-row">
                    <div class="cp-form-group">
                        <label for="cp_volunteer_email"><?php esc_html_e('Email', 'campaign-office-core'); ?> <span class="required">*</span></label>
                        <input type="email" id="cp_volunteer_email" name="email" required>
                    </div>

                    <div class="cp-form-group">
                        <label for="cp_volunteer_phone"><?php esc_html_e('Phone', 'campaign-office-core'); ?></label>
                        <input type="tel" id="cp_volunteer_phone" name="phone">
                    </div>
                </div>

                <div class="cp-form-group">
                    <label for="cp_volunteer_address"><?php esc_html_e('Street Address', 'campaign-office-core'); ?></label>
                    <input type="text" id="cp_volunteer_address" name="address">
                </div>

                <div class="cp-form-row">
                    <div class="cp-form-group">
                        <label for="cp_volunteer_city"><?php esc_html_e('City', 'campaign-office-core'); ?></label>
                        <input type="text" id="cp_volunteer_city" name="city">
                    </div>

                    <div class="cp-form-group cp-form-group-small">
                        <label for="cp_volunteer_state"><?php esc_html_e('State', 'campaign-office-core'); ?></label>
                        <input type="text" id="cp_volunteer_state" name="state" maxlength="2" placeholder="CA">
                    </div>

                    <div class="cp-form-group cp-form-group-small">
                        <label for="cp_volunteer_zip"><?php esc_html_e('ZIP', 'campaign-office-core'); ?></label>
                        <input type="text" id="cp_volunteer_zip" name="zip" maxlength="10">
                    </div>
                </div>

                <div class="cp-form-group">
                    <label><?php esc_html_e('I am interested in:', 'campaign-office-core'); ?></label>
                    <div class="cp-checkbox-group">
                        <label><input type="checkbox" name="interests[]" value="canvassing"> <?php esc_html_e('Door-to-door canvassing', 'campaign-office-core'); ?></label>
                        <label><input type="checkbox" name="interests[]" value="phone_banking"> <?php esc_html_e('Phone banking', 'campaign-office-core'); ?></label>
                        <label><input type="checkbox" name="interests[]" value="event_support"> <?php esc_html_e('Event support', 'campaign-office-core'); ?></label>
                        <label><input type="checkbox" name="interests[]" value="data_entry"> <?php esc_html_e('Data entry', 'campaign-office-core'); ?></label>
                        <label><input type="checkbox" name="interests[]" value="social_media"> <?php esc_html_e('Social media outreach', 'campaign-office-core'); ?></label>
                        <label><input type="checkbox" name="interests[]" value="fundraising"> <?php esc_html_e('Fundraising', 'campaign-office-core'); ?></label>
                    </div>
                </div>

                <div class="cp-form-group">
                    <label><?php esc_html_e('Availability:', 'campaign-office-core'); ?></label>
                    <div class="cp-checkbox-group">
                        <label><input type="checkbox" name="availability[]" value="weekday_mornings"> <?php esc_html_e('Weekday mornings', 'campaign-office-core'); ?></label>
                        <label><input type="checkbox" name="availability[]" value="weekday_afternoons"> <?php esc_html_e('Weekday afternoons', 'campaign-office-core'); ?></label>
                        <label><input type="checkbox" name="availability[]" value="weekday_evenings"> <?php esc_html_e('Weekday evenings', 'campaign-office-core'); ?></label>
                        <label><input type="checkbox" name="availability[]" value="weekends"> <?php esc_html_e('Weekends', 'campaign-office-core'); ?></label>
                    </div>
                </div>

                <div class="cp-form-group">
                    <label for="cp_volunteer_skills"><?php esc_html_e('Skills/Experience (optional)', 'campaign-office-core'); ?></label>
                    <textarea id="cp_volunteer_skills" name="skills" rows="3" placeholder="<?php esc_attr_e('e.g., graphic design, Spanish speaker, social media marketing', 'campaign-office-core'); ?>"></textarea>
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
        // Verify nonce
        if (!isset($_POST['cp_volunteer_nonce']) || !wp_verify_nonce($_POST['cp_volunteer_nonce'], 'cp_volunteer_signup')) {
            wp_send_json_error(array('message' => __('Security verification failed.', 'campaign-office-core')));
        }

        // Rate limiting: 5 submissions per hour per IP
        if (function_exists('campaignpress_is_rate_limited') && campaignpress_is_rate_limited('volunteer_signup', 5, 3600)) {
            wp_send_json_error(array('message' => __('Too many submissions. Please try again later.', 'campaign-office-core')));
        }

        // Validate required fields
        if (empty($_POST['first_name']) || empty($_POST['last_name']) || empty($_POST['email'])) {
            wp_send_json_error(array('message' => __('Please fill in all required fields.', 'campaign-office-core')));
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

        // Sanitize volunteer-specific data
        $volunteer_data = array(
            'contact_id'     => $contact_id,
            'skills'         => isset($_POST['skills']) ? sanitize_textarea_field($_POST['skills']) : '',
            'interests'      => isset($_POST['interests']) ? wp_json_encode(array_map('sanitize_text_field', $_POST['interests'])) : '',
            'availability'   => isset($_POST['availability']) ? wp_json_encode(array_map('sanitize_text_field', $_POST['availability'])) : '',
            'opportunity_id' => isset($_POST['opportunity_id']) ? absint($_POST['opportunity_id']) : null,
            'source'         => 'website_form',
            'status'         => 'new',
        );

        // Insert into database
        global $wpdb;
        $result = $wpdb->insert($this->table_name, $volunteer_data, array(
            '%d', '%s', '%s', '%s', '%d', '%s', '%s'
        ));

        if ($result) {
            // Allow other plugins to hook into volunteer signup
            do_action('cp_volunteer_signup_success', $wpdb->insert_id, $volunteer_data);

            wp_send_json_success(array(
                'message' => __('Thank you for signing up! We\'ll be in touch soon.', 'campaign-office-core'),
                'volunteer_id' => $wpdb->insert_id
            ));
        } else {
            wp_send_json_error(array('message' => __('Failed to save volunteer information. Please try again.', 'campaign-office-core')));
        }
    }

    /**
     * Render admin page for volunteer management
     */
    public function render_admin_page() {
        global $wpdb;

        // Handle bulk actions
        if (isset($_POST['cp_bulk_action']) && check_admin_referer('cp_volunteer_bulk_action', 'cp_volunteer_bulk_nonce')) {
            $this->handle_bulk_actions();
        }

        // Handle individual volunteer deletion
        if (isset($_GET['action']) && $_GET['action'] === 'delete' && isset($_GET['volunteer_id']) && check_admin_referer('cp_delete_volunteer_' . absint($_GET['volunteer_id']))) {
            $this->delete_volunteer(absint($_GET['volunteer_id']));
        }

        // Get filter parameters
        $status_filter = isset($_GET['status']) ? sanitize_text_field($_GET['status']) : '';
        $search = isset($_GET['s']) ? sanitize_text_field($_GET['s']) : '';

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
            <h1 class="wp-heading-inline"><?php esc_html_e('Volunteer Signups', 'campaign-office-core'); ?></h1>
            <a href="<?php echo esc_url(admin_url('post-new.php?post_type=cp_volunteer')); ?>" class="page-title-action"><?php esc_html_e('Add Volunteer Opportunity', 'campaign-office-core'); ?></a>
            <a href="<?php echo esc_url(wp_nonce_url(admin_url('admin-post.php?action=cp_export_volunteers'), 'cp_export_volunteers')); ?>" class="page-title-action"><?php esc_html_e('Export to CSV', 'campaign-office-core'); ?></a>

            <hr class="wp-header-end">

            <ul class="subsubsub">
                <li><a href="<?php echo esc_url(remove_query_arg('status')); ?>" <?php echo empty($status_filter) ? 'class="current"' : ''; ?>><?php esc_html_e('All', 'campaign-office-core'); ?> <span class="count">(<?php echo esc_html($total_volunteers); ?>)</span></a> |</li>
                <li><a href="<?php echo esc_url(add_query_arg('status', 'new')); ?>" <?php echo $status_filter === 'new' ? 'class="current"' : ''; ?>><?php esc_html_e('New', 'campaign-office-core'); ?> <span class="count">(<?php echo isset($status_counts['new']) ? esc_html($status_counts['new']->count) : '0'; ?>)</span></a> |</li>
                <li><a href="<?php echo esc_url(add_query_arg('status', 'contacted')); ?>" <?php echo $status_filter === 'contacted' ? 'class="current"' : ''; ?>><?php esc_html_e('Contacted', 'campaign-office-core'); ?> <span class="count">(<?php echo isset($status_counts['contacted']) ? esc_html($status_counts['contacted']->count) : '0'; ?>)</span></a> |</li>
                <li><a href="<?php echo esc_url(add_query_arg('status', 'active')); ?>" <?php echo $status_filter === 'active' ? 'class="current"' : ''; ?>><?php esc_html_e('Active', 'campaign-office-core'); ?> <span class="count">(<?php echo isset($status_counts['active']) ? esc_html($status_counts['active']->count) : '0'; ?>)</span></a></li>
            </ul>

            <form method="get">
                <input type="hidden" name="post_type" value="cp_volunteer">
                <input type="hidden" name="page" value="cp-volunteer-signups">
                <?php if ($status_filter) : ?>
                    <input type="hidden" name="status" value="<?php echo esc_attr($status_filter); ?>">
                <?php endif; ?>

                <p class="search-box">
                    <label class="screen-reader-text" for="volunteer-search-input"><?php esc_html_e('Search Volunteers:', 'campaign-office-core'); ?></label>
                    <input type="search" id="volunteer-search-input" name="s" value="<?php echo esc_attr($search); ?>">
                    <input type="submit" class="button" value="<?php esc_attr_e('Search Volunteers', 'campaign-office-core'); ?>">
                </p>
            </form>

            <form method="post">
                <?php wp_nonce_field('cp_volunteer_bulk_action', 'cp_volunteer_bulk_nonce'); ?>

                <div class="tablenav top">
                    <div class="alignleft actions bulkactions">
                        <select name="cp_bulk_action">
                            <option value=""><?php esc_html_e('Bulk Actions', 'campaign-office-core'); ?></option>
                            <option value="contacted"><?php esc_html_e('Mark as Contacted', 'campaign-office-core'); ?></option>
                            <option value="active"><?php esc_html_e('Mark as Active', 'campaign-office-core'); ?></option>
                            <option value="delete"><?php esc_html_e('Delete', 'campaign-office-core'); ?></option>
                        </select>
                        <input type="submit" class="button action" value="<?php esc_attr_e('Apply', 'campaign-office-core'); ?>">
                    </div>
                </div>

                <table class="wp-list-table widefat fixed striped">
                    <thead>
                        <tr>
                            <td class="manage-column column-cb check-column">
                                <input type="checkbox" id="cb-select-all">
                            </td>
                            <th><?php esc_html_e('Name', 'campaign-office-core'); ?></th>
                            <th><?php esc_html_e('Email', 'campaign-office-core'); ?></th>
                            <th><?php esc_html_e('Phone', 'campaign-office-core'); ?></th>
                            <th><?php esc_html_e('Location', 'campaign-office-core'); ?></th>
                            <th><?php esc_html_e('Interests', 'campaign-office-core'); ?></th>
                            <th><?php esc_html_e('Status', 'campaign-office-core'); ?></th>
                            <th><?php esc_html_e('Date', 'campaign-office-core'); ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($volunteers)) : ?>
                            <tr>
                                <td colspan="8"><?php esc_html_e('No volunteers found.', 'campaign-office-core'); ?></td>
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
                                            <span><a href="mailto:<?php echo esc_attr($volunteer->email); ?>"><?php esc_html_e('Email', 'campaign-office-core'); ?></a> | </span>
                                            <span class="trash"><a href="<?php echo esc_url(wp_nonce_url(add_query_arg(array('action' => 'delete', 'volunteer_id' => $volunteer->id)), 'cp_delete_volunteer_' . $volunteer->id)); ?>" class="submitdelete"><?php esc_html_e('Delete', 'campaign-office-core'); ?></a></span>
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
            echo '<div class="notice notice-success"><p>' . esc_html__('Volunteers deleted.', 'campaign-office-core') . '</p></div>';
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
            echo '<div class="notice notice-success"><p>' . esc_html__('Volunteers updated.', 'campaign-office-core') . '</p></div>';
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

        echo '<div class="notice notice-success"><p>' . esc_html__('Volunteer deleted.', 'campaign-office-core') . '</p></div>';
    }

    /**
     * Export volunteers to CSV
     */
    public function export_volunteers_csv() {
        // Verify nonce
        if (!isset($_GET['_wpnonce']) || !wp_verify_nonce($_GET['_wpnonce'], 'cp_export_volunteers')) {
            wp_die(esc_html__('Security verification failed.', 'campaign-office-core'));
        }

        // Check permissions
        if (!current_user_can('edit_posts')) {
            wp_die(esc_html__('You do not have permission to export volunteers.', 'campaign-office-core'));
        }

        global $wpdb;
        $contacts_table = $wpdb->prefix . 'cp_contacts';
        $volunteers = $wpdb->get_results("
            SELECT v.*, c.first_name, c.last_name, c.email, c.phone, c.address_line1 as address, c.city, c.state, c.zip_code as zip
            FROM {$this->table_name} v
            JOIN {$contacts_table} c ON v.contact_id = c.id
            ORDER BY v.created_at DESC
        ", ARRAY_A);

        // Set headers for CSV download
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename=volunteers-' . date('Y-m-d') . '.csv');

        $output = fopen('php://output', 'w');

        // Add BOM for Excel UTF-8 compatibility
        fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));

        // Add column headers
        fputcsv($output, array(
            'ID', 'First Name', 'Last Name', 'Email', 'Phone', 'Address', 'City', 'State', 'ZIP',
            'Skills', 'Interests', 'Availability', 'Status', 'Source', 'Created Date'
        ));

        // Add data rows
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
