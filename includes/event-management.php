<?php
/**
 * Enhanced Event Management
 *
 * Provides RSVP capture, recurring events, and event analytics for the free version.
 *
 * @package Campaign_Office_Core
 * @since 2.0.0
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Class CP_Event_Manager
 *
 * Handles event RSVP, recurring events, and event management
 */
class CP_Event_Manager {

    /**
     * Database table name for event RSVPs
     *
     * @var string
     */
    private $rsvp_table_name;

    /**
     * Constructor
     */
    public function __construct() {
        global $wpdb;
        $this->rsvp_table_name = $wpdb->prefix . 'cp_event_rsvps';

        // Database setup
        add_action('after_setup_theme', array($this, 'create_rsvp_table'));

        // Meta boxes for event settings
        add_action('add_meta_boxes', array($this, 'add_event_meta_boxes'));
        add_action('save_post_cp_event', array($this, 'save_event_meta'), 10, 3);

        // AJAX handlers for RSVP
        add_action('wp_ajax_cp_submit_event_rsvp', array($this, 'handle_event_rsvp'));
        add_action('wp_ajax_nopriv_cp_submit_event_rsvp', array($this, 'handle_event_rsvp'));

        // Shortcode for RSVP form
        add_shortcode('cp_event_rsvp', array($this, 'render_rsvp_form'));

        // Admin menu for RSVPs
        add_action('admin_menu', array($this, 'add_admin_menu'));

        // Export functionality
        add_action('admin_post_cp_export_event_rsvps', array($this, 'export_rsvps_csv'));

        // Generate recurring events
        add_action('save_post_cp_event', array($this, 'generate_recurring_events'), 20, 3);
    }

    /**
     * Create RSVP database table
     */
    public function create_rsvp_table() {
        global $wpdb;

        $charset_collate = $wpdb->get_charset_collate();

        $sql = "CREATE TABLE IF NOT EXISTS {$this->rsvp_table_name} (
            id bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
            event_id bigint(20) UNSIGNED NOT NULL,
            contact_id bigint(20) UNSIGNED DEFAULT NULL,
            guests int(11) DEFAULT 0,
            rsvp_status varchar(20) DEFAULT 'attending',
            dietary_restrictions text DEFAULT NULL,
            notes text DEFAULT NULL,
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            KEY event_id (event_id),
            KEY contact_id (contact_id),
            KEY rsvp_status (rsvp_status)
        ) $charset_collate;";

        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);

        update_option('cp_event_rsvp_table_created', true);
    }

    /**
     * Add meta boxes for event settings
     */
    public function add_event_meta_boxes() {
        add_meta_box(
            'cp_event_rsvp_settings',
            __('RSVP Settings', 'campaign-office-core'),
            array($this, 'render_rsvp_settings_meta_box'),
            'cp_event',
            'side',
            'default'
        );

        add_meta_box(
            'cp_event_recurring',
            __('Recurring Event Settings', 'campaign-office-core'),
            array($this, 'render_recurring_meta_box'),
            'cp_event',
            'side',
            'default'
        );

        add_meta_box(
            'cp_event_capacity',
            __('Event Capacity', 'campaign-office-core'),
            array($this, 'render_capacity_meta_box'),
            'cp_event',
            'side',
            'default'
        );
    }

    /**
     * Render RSVP settings meta box
     */
    public function render_rsvp_settings_meta_box($post) {
        wp_nonce_field('cp_event_rsvp_settings', 'cp_event_rsvp_settings_nonce');

        $rsvp_enabled = get_post_meta($post->ID, '_cp_rsvp_enabled', true);
        $rsvp_deadline = get_post_meta($post->ID, '_cp_rsvp_deadline', true);
        $collect_dietary = get_post_meta($post->ID, '_cp_collect_dietary', true);
        ?>
        <p>
            <label>
                <input type="checkbox" name="cp_rsvp_enabled" value="1" <?php checked($rsvp_enabled, '1'); ?>>
                <?php esc_html_e('Enable RSVP for this event', 'campaign-office-core'); ?>
            </label>
        </p>
        <p>
            <label for="cp_rsvp_deadline"><strong><?php esc_html_e('RSVP Deadline:', 'campaign-office-core'); ?></strong></label><br>
            <input type="datetime-local" id="cp_rsvp_deadline" name="cp_rsvp_deadline" value="<?php echo esc_attr($rsvp_deadline); ?>" style="width: 100%;">
        </p>
        <p>
            <label>
                <input type="checkbox" name="cp_collect_dietary" value="1" <?php checked($collect_dietary, '1'); ?>>
                <?php esc_html_e('Collect dietary restrictions', 'campaign-office-core'); ?>
            </label>
        </p>
        <?php
    }

    /**
     * Render recurring event meta box
     */
    public function render_recurring_meta_box($post) {
        wp_nonce_field('cp_event_recurring', 'cp_event_recurring_nonce');

        $is_recurring = get_post_meta($post->ID, '_cp_is_recurring', true);
        $recurrence_pattern = get_post_meta($post->ID, '_cp_recurrence_pattern', true);
        $recurrence_end_date = get_post_meta($post->ID, '_cp_recurrence_end_date', true);
        ?>
        <p>
            <label>
                <input type="checkbox" name="cp_is_recurring" value="1" <?php checked($is_recurring, '1'); ?>>
                <?php esc_html_e('This is a recurring event', 'campaign-office-core'); ?>
            </label>
        </p>
        <p>
            <label for="cp_recurrence_pattern"><strong><?php esc_html_e('Recurrence:', 'campaign-office-core'); ?></strong></label><br>
            <select id="cp_recurrence_pattern" name="cp_recurrence_pattern" style="width: 100%;">
                <option value="daily" <?php selected($recurrence_pattern, 'daily'); ?>><?php esc_html_e('Daily', 'campaign-office-core'); ?></option>
                <option value="weekly" <?php selected($recurrence_pattern, 'weekly'); ?>><?php esc_html_e('Weekly', 'campaign-office-core'); ?></option>
                <option value="biweekly" <?php selected($recurrence_pattern, 'biweekly'); ?>><?php esc_html_e('Bi-weekly', 'campaign-office-core'); ?></option>
                <option value="monthly" <?php selected($recurrence_pattern, 'monthly'); ?>><?php esc_html_e('Monthly', 'campaign-office-core'); ?></option>
            </select>
        </p>
        <p>
            <label for="cp_recurrence_end_date"><strong><?php esc_html_e('Repeat until:', 'campaign-office-core'); ?></strong></label><br>
            <input type="date" id="cp_recurrence_end_date" name="cp_recurrence_end_date" value="<?php echo esc_attr($recurrence_end_date); ?>" style="width: 100%;">
        </p>
        <p class="description">
            <?php esc_html_e('Note: Recurring events will be automatically generated when you save this event.', 'campaign-office-core'); ?>
        </p>
        <?php
    }

    /**
     * Render capacity meta box
     */
    public function render_capacity_meta_box($post) {
        wp_nonce_field('cp_event_capacity', 'cp_event_capacity_nonce');

        $max_capacity = get_post_meta($post->ID, '_cp_max_capacity', true);
        $current_rsvps = $this->get_event_rsvp_count($post->ID);
        ?>
        <p>
            <label for="cp_max_capacity"><strong><?php esc_html_e('Maximum Capacity:', 'campaign-office-core'); ?></strong></label><br>
            <input type="number" id="cp_max_capacity" name="cp_max_capacity" value="<?php echo esc_attr($max_capacity); ?>" min="0" style="width: 100%;">
        </p>
        <?php if ($current_rsvps > 0) : ?>
            <p>
                <strong><?php esc_html_e('Current RSVPs:', 'campaign-office-core'); ?></strong> <?php echo esc_html($current_rsvps); ?><br>
                <?php if ($max_capacity && $current_rsvps >= $max_capacity) : ?>
                    <span style="color: #dc3232;"><?php esc_html_e('Event is at capacity!', 'campaign-office-core'); ?></span>
                <?php endif; ?>
            </p>
        <?php endif; ?>
        <?php
    }

    /**
     * Save event meta data
     */
    public function save_event_meta($post_id, $post = null, $update = false) {
        // Check nonces
        if (isset($_POST['cp_event_rsvp_settings_nonce']) &&
            wp_verify_nonce($_POST['cp_event_rsvp_settings_nonce'], 'cp_event_rsvp_settings')) {

            update_post_meta($post_id, '_cp_rsvp_enabled', isset($_POST['cp_rsvp_enabled']) ? '1' : '0');
            update_post_meta($post_id, '_cp_rsvp_deadline', sanitize_text_field($_POST['cp_rsvp_deadline'] ?? ''));
            update_post_meta($post_id, '_cp_collect_dietary', isset($_POST['cp_collect_dietary']) ? '1' : '0');
        }

        if (isset($_POST['cp_event_recurring_nonce']) &&
            wp_verify_nonce($_POST['cp_event_recurring_nonce'], 'cp_event_recurring')) {

            update_post_meta($post_id, '_cp_is_recurring', isset($_POST['cp_is_recurring']) ? '1' : '0');
            update_post_meta($post_id, '_cp_recurrence_pattern', sanitize_text_field($_POST['cp_recurrence_pattern'] ?? 'weekly'));
            update_post_meta($post_id, '_cp_recurrence_end_date', sanitize_text_field($_POST['cp_recurrence_end_date'] ?? ''));
        }

        if (isset($_POST['cp_event_capacity_nonce']) &&
            wp_verify_nonce($_POST['cp_event_capacity_nonce'], 'cp_event_capacity')) {

            update_post_meta($post_id, '_cp_max_capacity', absint($_POST['cp_max_capacity'] ?? 0));
        }
    }

    /**
     * Generate recurring events
     */
    public function generate_recurring_events($post_id, $post, $update) {
        // Only generate for parent events, not for auto-generated ones
        if (get_post_meta($post_id, '_cp_parent_recurring_event', true)) {
            return;
        }

        $is_recurring = get_post_meta($post_id, '_cp_is_recurring', true);
        if ($is_recurring !== '1') {
            return;
        }

        $event_date = get_post_meta($post_id, '_cp_event_date', true);
        $event_time = get_post_meta($post_id, '_cp_event_time', true);
        $recurrence_pattern = get_post_meta($post_id, '_cp_recurrence_pattern', true);
        $recurrence_end_date = get_post_meta($post_id, '_cp_recurrence_end_date', true);

        if (empty($event_date) || empty($recurrence_end_date)) {
            return;
        }

        // Delete previously generated recurring events for this parent
        $existing_recurrences = get_posts(array(
            'post_type' => 'cp_event',
            'meta_key' => '_cp_parent_recurring_event',
            'meta_value' => $post_id,
            'posts_per_page' => -1,
            'post_status' => 'any',
        ));

        foreach ($existing_recurrences as $recurrence) {
            wp_delete_post($recurrence->ID, true);
        }

        // Generate new recurring events
        $start_date = new DateTime($event_date);
        $end_date = new DateTime($recurrence_end_date);
        $current_date = clone $start_date;

        $interval_map = array(
            'daily' => 'P1D',
            'weekly' => 'P7D',
            'biweekly' => 'P14D',
            'monthly' => 'P1M',
        );

        $interval = new DateInterval($interval_map[$recurrence_pattern] ?? 'P7D');

        // Limit to 52 occurrences to prevent runaway generation
        $max_occurrences = 52;
        $occurrence_count = 0;

        while ($current_date <= $end_date && $occurrence_count < $max_occurrences) {
            $current_date->add($interval);

            if ($current_date > $end_date) {
                break;
            }

            // Create new event post
            $new_event_id = wp_insert_post(array(
                'post_title' => $post->post_title,
                'post_content' => $post->post_content,
                'post_status' => $post->post_status,
                'post_type' => 'cp_event',
                'post_author' => $post->post_author,
            ));

            if ($new_event_id) {
                // Copy all meta data
                $meta_data = get_post_meta($post_id);
                foreach ($meta_data as $key => $values) {
                    if ($key === '_cp_event_date') {
                        update_post_meta($new_event_id, $key, $current_date->format('Y-m-d'));
                    } elseif (!in_array($key, array('_cp_is_recurring', '_cp_recurrence_pattern', '_cp_recurrence_end_date'), true)) {
                        foreach ($values as $value) {
                            update_post_meta($new_event_id, $key, maybe_unserialize($value));
                        }
                    }
                }

                // Mark as generated recurring event
                update_post_meta($new_event_id, '_cp_parent_recurring_event', $post_id);

                $occurrence_count++;
            }
        }
    }

    /**
     * Render RSVP form shortcode
     *
     * Usage: [cp_event_rsvp event_id="123"]
     */
    public function render_rsvp_form($atts) {
        $atts = shortcode_atts(array(
            'event_id' => get_the_ID(),
            'title' => __('RSVP for this Event', 'campaign-office-core'),
        ), $atts);

        $event_id = absint($atts['event_id']);

        // Check if RSVP is enabled
        $rsvp_enabled = get_post_meta($event_id, '_cp_rsvp_enabled', true);
        if ($rsvp_enabled !== '1') {
            return '<p>' . esc_html__('RSVP is not enabled for this event.', 'campaign-office-core') . '</p>';
        }

        // Check capacity
        $max_capacity = get_post_meta($event_id, '_cp_max_capacity', true);
        $current_rsvps = $this->get_event_rsvp_count($event_id);

        if ($max_capacity && $current_rsvps >= $max_capacity) {
            return '<p class="cp-event-full">' . esc_html__('This event is at full capacity.', 'campaign-office-core') . '</p>';
        }

        $collect_dietary = get_post_meta($event_id, '_cp_collect_dietary', true);

        ob_start();
        ?>
        <div class="cp-event-rsvp-wrapper">
            <h3><?php echo esc_html($atts['title']); ?></h3>

            <?php if ($max_capacity) : ?>
                <p class="cp-capacity-info">
                    <?php printf(
                        esc_html__('Spots remaining: %d of %d', 'campaign-office-core'),
                        max(0, $max_capacity - $current_rsvps),
                        $max_capacity
                    ); ?>
                </p>
            <?php endif; ?>

            <form class="cp-event-rsvp-form" data-event-id="<?php echo esc_attr($event_id); ?>">
                <?php wp_nonce_field('cp_event_rsvp', 'cp_event_rsvp_nonce'); ?>

                <div class="cp-form-row">
                    <div class="cp-form-group">
                        <label for="cp_rsvp_first_name"><?php esc_html_e('First Name', 'campaign-office-core'); ?> <span class="required">*</span></label>
                        <input type="text" id="cp_rsvp_first_name" name="first_name" required>
                    </div>

                    <div class="cp-form-group">
                        <label for="cp_rsvp_last_name"><?php esc_html_e('Last Name', 'campaign-office-core'); ?> <span class="required">*</span></label>
                        <input type="text" id="cp_rsvp_last_name" name="last_name" required>
                    </div>
                </div>

                <div class="cp-form-row">
                    <div class="cp-form-group">
                        <label for="cp_rsvp_email"><?php esc_html_e('Email', 'campaign-office-core'); ?> <span class="required">*</span></label>
                        <input type="email" id="cp_rsvp_email" name="email" required>
                    </div>

                    <div class="cp-form-group">
                        <label for="cp_rsvp_phone"><?php esc_html_e('Phone', 'campaign-office-core'); ?></label>
                        <input type="tel" id="cp_rsvp_phone" name="phone">
                    </div>
                </div>

                <div class="cp-form-group">
                    <label for="cp_rsvp_guests"><?php esc_html_e('Number of guests (including yourself):', 'campaign-office-core'); ?></label>
                    <input type="number" id="cp_rsvp_guests" name="guests" value="1" min="1" max="10">
                </div>

                <?php if ($collect_dietary === '1') : ?>
                    <div class="cp-form-group">
                        <label for="cp_rsvp_dietary"><?php esc_html_e('Dietary Restrictions:', 'campaign-office-core'); ?></label>
                        <textarea id="cp_rsvp_dietary" name="dietary_restrictions" rows="2"></textarea>
                    </div>
                <?php endif; ?>

                <div class="cp-form-message"></div>

                <button type="submit" class="cp-rsvp-submit-btn"><?php esc_html_e('Submit RSVP', 'campaign-office-core'); ?></button>
            </form>
        </div>

        // Script moved to assets/js/frontend.js
        <?php

        return ob_get_clean();
    }

    /**
     * Handle event RSVP submission
     */
    public function handle_event_rsvp() {
        // Verify nonce
        if (!isset($_POST['cp_event_rsvp_nonce']) || !wp_verify_nonce($_POST['cp_event_rsvp_nonce'], 'cp_event_rsvp')) {
            wp_send_json_error(array('message' => __('Security verification failed.', 'campaign-office-core')));
        }

        // Rate limiting: 10 RSVPs per hour per IP
        if (function_exists('campaignpress_is_rate_limited') && campaignpress_is_rate_limited('event_rsvp', 10, 3600)) {
            wp_send_json_error(array('message' => __('Too many RSVP submissions. Please try again later.', 'campaign-office-core')));
        }

        $event_id = absint($_POST['event_id'] ?? 0);

        // Validate event
        if (!$event_id || get_post_type($event_id) !== 'cp_event') {
            wp_send_json_error(array('message' => __('Invalid event.', 'campaign-office-core')));
        }

        // Check capacity
        $max_capacity = get_post_meta($event_id, '_cp_max_capacity', true);
        $current_rsvps = $this->get_event_rsvp_count($event_id);
        $guests = absint($_POST['guests'] ?? 1);

        if ($max_capacity && ($current_rsvps + $guests) > $max_capacity) {
            wp_send_json_error(array('message' => __('Sorry, this event is at full capacity.', 'campaign-office-core')));
        }

        $first_name = sanitize_text_field($_POST['first_name'] ?? '');
        $last_name = sanitize_text_field($_POST['last_name'] ?? '');
        $email = sanitize_email($_POST['email'] ?? '');

        // Validate required fields
        if (empty($first_name) || empty($last_name) || empty($email)) {
            wp_send_json_error(array('message' => __('Please fill in all required fields.', 'campaign-office-core')));
        }

        // Identify or Create Contact
        global $cp_contact_manager;
        $contact_id = null;

        if ($cp_contact_manager && method_exists($cp_contact_manager, 'find_or_create')) {
            $contact_id = $cp_contact_manager->find_or_create(array(
                'first_name' => $first_name,
                'last_name'  => $last_name,
                'email'      => $email,
                'phone'      => sanitize_text_field($_POST['phone'] ?? ''),
            ));

            if (is_wp_error($contact_id)) {
                wp_send_json_error(array('message' => $contact_id->get_error_message()));
            }
        }

        // Sanitize input data
        $rsvp_data = array(
            'event_id'             => $event_id,
            'contact_id'           => $contact_id,
            'guests'               => $guests,
            'dietary_restrictions' => sanitize_textarea_field($_POST['dietary_restrictions'] ?? ''),
            'rsvp_status'          => 'attending',
        );

        // Insert into database
        global $wpdb;
        $result = $wpdb->insert($this->rsvp_table_name, $rsvp_data, array(
            '%d', '%d', '%d', '%s', '%s'
        ));

        if ($result) {
            do_action('cp_event_rsvp_success', $wpdb->insert_id, $rsvp_data);

            wp_send_json_success(array(
                'message' => __('Thank you for your RSVP! We look forward to seeing you.', 'campaign-office-core'),
                'rsvp_id' => $wpdb->insert_id
            ));
        } else {
            wp_send_json_error(array('message' => __('Failed to save RSVP. Please try again.', 'campaign-office-core')));
        }
    }

    /**
     * Get RSVP count for an event
     */
    private function get_event_rsvp_count($event_id) {
        global $wpdb;
        return (int) $wpdb->get_var($wpdb->prepare(
            "SELECT SUM(guests) FROM {$this->rsvp_table_name} WHERE event_id = %d AND rsvp_status = 'attending'",
            $event_id
        ));
    }

    /**
     * Add admin menu for event RSVPs
     */
    public function add_admin_menu() {
        add_submenu_page(
            'edit.php?post_type=cp_event',
            __('Event RSVPs', 'campaign-office-core'),
            __('RSVPs', 'campaign-office-core'),
            'edit_posts',
            'cp-event-rsvps',
            array($this, 'render_rsvps_admin_page')
        );
    }

    /**
     * Render RSVPs admin page
     */
    public function render_rsvps_admin_page() {
        global $wpdb;

        $event_filter = isset($_GET['event_id']) ? absint($_GET['event_id']) : 0;

        // Build query
        $contacts_table = $wpdb->prefix . 'cp_contacts';
        $where = '1=1';
        if ($event_filter) {
            $where = $wpdb->prepare('r.event_id = %d', $event_filter);
        }

        $rsvps = $wpdb->get_results("
            SELECT r.*, c.first_name, c.last_name, c.email, c.phone 
            FROM {$this->rsvp_table_name} r 
            JOIN {$contacts_table} c ON r.contact_id = c.id 
            WHERE {$where} 
            ORDER BY r.created_at DESC
        ");

        // Get all events for filter dropdown
        $events = get_posts(array(
            'post_type' => 'cp_event',
            'posts_per_page' => -1,
            'orderby' => 'date',
            'order' => 'DESC',
        ));

        ?>
        <div class="wrap">
            <h1 class="wp-heading-inline"><?php esc_html_e('Event RSVPs', 'campaign-office-core'); ?></h1>
            <a href="<?php echo esc_url(wp_nonce_url(admin_url('admin-post.php?action=cp_export_event_rsvps&event_id=' . $event_filter), 'cp_export_event_rsvps')); ?>" class="page-title-action"><?php esc_html_e('Export to CSV', 'campaign-office-core'); ?></a>

            <hr class="wp-header-end">

            <form method="get" style="margin: 20px 0;">
                <input type="hidden" name="post_type" value="cp_event">
                <input type="hidden" name="page" value="cp-event-rsvps">
                <label for="event-filter"><?php esc_html_e('Filter by Event:', 'campaign-office-core'); ?></label>
                <select name="event_id" id="event-filter">
                    <option value="0"><?php esc_html_e('All Events', 'campaign-office-core'); ?></option>
                    <?php foreach ($events as $event) : ?>
                        <option value="<?php echo esc_attr($event->ID); ?>" <?php selected($event_filter, $event->ID); ?>>
                            <?php echo esc_html($event->post_title); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <input type="submit" class="button" value="<?php esc_attr_e('Filter', 'campaign-office-core'); ?>">
            </form>

            <table class="wp-list-table widefat fixed striped">
                <thead>
                    <tr>
                        <th><?php esc_html_e('Name', 'campaign-office-core'); ?></th>
                        <th><?php esc_html_e('Email', 'campaign-office-core'); ?></th>
                        <th><?php esc_html_e('Phone', 'campaign-office-core'); ?></th>
                        <th><?php esc_html_e('Event', 'campaign-office-core'); ?></th>
                        <th><?php esc_html_e('Guests', 'campaign-office-core'); ?></th>
                        <th><?php esc_html_e('Status', 'campaign-office-core'); ?></th>
                        <th><?php esc_html_e('Date', 'campaign-office-core'); ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($rsvps)) : ?>
                        <tr>
                            <td colspan="7"><?php esc_html_e('No RSVPs found.', 'campaign-office-core'); ?></td>
                        </tr>
                    <?php else : ?>
                        <?php foreach ($rsvps as $rsvp) : ?>
                            <tr>
                                <td><strong><?php echo esc_html($rsvp->first_name . ' ' . $rsvp->last_name); ?></strong></td>
                                <td><?php echo esc_html($rsvp->email); ?></td>
                                <td><?php echo esc_html($rsvp->phone); ?></td>
                                <td><?php echo esc_html(get_the_title($rsvp->event_id)); ?></td>
                                <td><?php echo esc_html($rsvp->guests); ?></td>
                                <td><span class="cp-status-badge"><?php echo esc_html(ucfirst($rsvp->rsvp_status)); ?></span></td>
                                <td><?php echo esc_html(date_i18n(get_option('date_format'), strtotime($rsvp->created_at))); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
        <?php
    }

    /**
     * Export RSVPs to CSV
     */
    public function export_rsvps_csv() {
        if (!isset($_GET['_wpnonce']) || !wp_verify_nonce($_GET['_wpnonce'], 'cp_export_event_rsvps')) {
            wp_die(esc_html__('Security verification failed.', 'campaign-office-core'));
        }

        if (!current_user_can('edit_posts')) {
            wp_die(esc_html__('You do not have permission to export RSVPs.', 'campaign-office-core'));
        }

        global $wpdb;
        $contacts_table = $wpdb->prefix . 'cp_contacts';

        $event_id = isset($_GET['event_id']) ? absint($_GET['event_id']) : 0;
        $where = $event_id ? $wpdb->prepare('WHERE r.event_id = %d', $event_id) : 'WHERE 1=1';

        $rsvps = $wpdb->get_results("
            SELECT r.*, c.first_name, c.last_name, c.email, c.phone 
            FROM {$this->rsvp_table_name} r 
            JOIN {$contacts_table} c ON r.contact_id = c.id 
            {$where} 
            ORDER BY r.created_at DESC
        ", ARRAY_A);

        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename=event-rsvps-' . date('Y-m-d') . '.csv');

        $output = fopen('php://output', 'w');
        fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));

        fputcsv($output, array('ID', 'Event', 'First Name', 'Last Name', 'Email', 'Phone', 'Guests', 'Dietary Restrictions', 'Status', 'Date'));

        foreach ($rsvps as $rsvp) {
            fputcsv($output, array(
                $rsvp['id'],
                get_the_title($rsvp['event_id']),
                $rsvp['first_name'],
                $rsvp['last_name'],
                $rsvp['email'],
                $rsvp['phone'],
                $rsvp['guests'],
                $rsvp['dietary_restrictions'],
                $rsvp['rsvp_status'],
                $rsvp['created_at'],
            ));
        }

        fclose($output);
        exit;
    }
}

// Initialize event manager
new CP_Event_Manager();
