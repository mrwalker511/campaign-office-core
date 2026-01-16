<?php
/**
 * Contact Manager
 *
 * Centralized contact management for all campaign interactions.
 * Handles volunteer signups, event RSVPs, donor information, etc.
 *
 * @package Campaign_Office_Core
 * @since 1.0.0
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Class CP_Contact_Manager
 *
 * Manages contacts in a centralized database table, preventing duplicates
 * and providing a single source of truth for all campaign contacts.
 */
class CP_Contact_Manager {

    /**
     * Singleton instance
     *
     * @var CP_Contact_Manager
     */
    private static $instance = null;

    /**
     * Database table name for contacts
     *
     * @var string
     */
    private $table_name;

    /**
     * Get singleton instance
     *
     * @return CP_Contact_Manager
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
        global $wpdb;
        $this->table_name = $wpdb->prefix . 'cp_contacts';

        // Database setup - only create tables when needed
        add_action('admin_init', array($this, 'maybe_create_contacts_table'));
        add_action('plugins_loaded', array($this, 'maybe_create_contacts_table'));
    }

    /**
     * Maybe create contacts table - only runs once per version
     */
    public function maybe_create_contacts_table() {
        $db_version = get_option('cp_contacts_db_version', '0');
        $current_version = '1.0.0';

        if (version_compare($db_version, $current_version, '<')) {
            $this->create_contacts_table();
            update_option('cp_contacts_db_version', $current_version);
        }
    }

    /**
     * Create contacts database table
     */
    public function create_contacts_table() {
        global $wpdb;

        $charset_collate = $wpdb->get_charset_collate();

        $sql = "CREATE TABLE IF NOT EXISTS {$this->table_name} (
            id bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
            first_name varchar(100) NOT NULL,
            last_name varchar(100) NOT NULL,
            email varchar(255) NOT NULL,
            phone varchar(50) DEFAULT NULL,
            address_line1 varchar(255) DEFAULT NULL,
            address_line2 varchar(255) DEFAULT NULL,
            city varchar(100) DEFAULT NULL,
            state varchar(50) DEFAULT NULL,
            zip_code varchar(20) DEFAULT NULL,
            country varchar(100) DEFAULT 'US',
            source varchar(100) DEFAULT NULL,
            tags text DEFAULT NULL,
            notes text DEFAULT NULL,
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            UNIQUE KEY email (email),
            KEY last_name (last_name),
            KEY city (city),
            KEY state (state),
            KEY created_at (created_at)
        ) $charset_collate;";

        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);

        update_option('cp_contacts_table_created', true);
    }

    /**
     * Find existing contact or create new one
     *
     * Deduplicates by email address. If a contact with the email exists,
     * updates their information and returns the ID. Otherwise creates new.
     *
     * @param array $data Contact data array
     * @return int|WP_Error Contact ID on success, WP_Error on failure
     */
    public function find_or_create($data) {
        global $wpdb;

        // Validate required fields
        if (empty($data['email'])) {
            return new WP_Error('missing_email', __('Email address is required.', 'campaign-office-core'));
        }

        $email = sanitize_email($data['email']);
        if (!is_email($email)) {
            return new WP_Error('invalid_email', __('Please provide a valid email address.', 'campaign-office-core'));
        }

        // Check for existing contact
        $existing = $wpdb->get_row($wpdb->prepare(
            "SELECT id FROM {$this->table_name} WHERE email = %s",
            $email
        ));

        // Sanitize all input data
        $contact_data = array(
            'first_name'    => sanitize_text_field($data['first_name'] ?? ''),
            'last_name'     => sanitize_text_field($data['last_name'] ?? ''),
            'email'         => $email,
            'phone'         => sanitize_text_field($data['phone'] ?? ''),
            'address_line1' => sanitize_text_field($data['address_line1'] ?? ''),
            'address_line2' => sanitize_text_field($data['address_line2'] ?? ''),
            'city'          => sanitize_text_field($data['city'] ?? ''),
            'state'         => sanitize_text_field($data['state'] ?? ''),
            'zip_code'      => sanitize_text_field($data['zip_code'] ?? ''),
            'country'       => sanitize_text_field($data['country'] ?? 'US'),
            'source'        => sanitize_text_field($data['source'] ?? ''),
        );

        if ($existing) {
            // Update existing contact (don't overwrite with empty values)
            $update_data = array_filter($contact_data, function($value) {
                return !empty($value);
            });

            // Always keep email
            $update_data['email'] = $email;

            $wpdb->update(
                $this->table_name,
                $update_data,
                array('id' => $existing->id),
                array_fill(0, count($update_data), '%s'),
                array('%d')
            );

            return (int) $existing->id;
        } else {
            // Create new contact
            $result = $wpdb->insert(
                $this->table_name,
                $contact_data,
                array('%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s')
            );

            if ($result) {
                return (int) $wpdb->insert_id;
            } else {
                return new WP_Error('db_error', __('Failed to save contact information.', 'campaign-office-core'));
            }
        }
    }

    /**
     * Get contact by ID
     *
     * @param int $id Contact ID
     * @return object|null Contact object or null
     */
    public function get($id) {
        global $wpdb;
        return $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM {$this->table_name} WHERE id = %d",
            $id
        ));
    }

    /**
     * Get contact by email
     *
     * @param string $email Email address
     * @return object|null Contact object or null
     */
    public function get_by_email($email) {
        global $wpdb;
        return $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM {$this->table_name} WHERE email = %s",
            sanitize_email($email)
        ));
    }

    /**
     * Update contact
     *
     * @param int   $id   Contact ID
     * @param array $data Data to update
     * @return bool True on success
     */
    public function update($id, $data) {
        global $wpdb;

        // Sanitize data
        $update_data = array();
        $allowed_fields = array('first_name', 'last_name', 'email', 'phone', 
            'address_line1', 'address_line2', 'city', 'state', 'zip_code', 
            'country', 'source', 'tags', 'notes');

        foreach ($allowed_fields as $field) {
            if (isset($data[$field])) {
                if ($field === 'email') {
                    $update_data[$field] = sanitize_email($data[$field]);
                } elseif (in_array($field, array('tags', 'notes'), true)) {
                    $update_data[$field] = sanitize_textarea_field($data[$field]);
                } else {
                    $update_data[$field] = sanitize_text_field($data[$field]);
                }
            }
        }

        if (empty($update_data)) {
            return false;
        }

        return (bool) $wpdb->update(
            $this->table_name,
            $update_data,
            array('id' => $id),
            array_fill(0, count($update_data), '%s'),
            array('%d')
        );
    }

    /**
     * Delete contact
     *
     * @param int $id Contact ID
     * @return bool True on success
     */
    public function delete($id) {
        global $wpdb;
        return (bool) $wpdb->delete(
            $this->table_name,
            array('id' => $id),
            array('%d')
        );
    }

    /**
     * Search contacts
     *
     * @param string $search Search query
     * @param int    $limit  Max results (default 50)
     * @return array Array of contact objects
     */
    public function search($search, $limit = 50) {
        global $wpdb;

        $search_term = '%' . $wpdb->esc_like($search) . '%';

        return $wpdb->get_results($wpdb->prepare(
            "SELECT * FROM {$this->table_name} 
             WHERE first_name LIKE %s 
                OR last_name LIKE %s 
                OR email LIKE %s 
                OR city LIKE %s
             ORDER BY last_name, first_name 
             LIMIT %d",
            $search_term,
            $search_term,
            $search_term,
            $search_term,
            $limit
        ));
    }

    /**
     * Get all contacts with pagination
     *
     * @param int $page     Page number (1-indexed)
     * @param int $per_page Items per page
     * @return array Array with 'contacts' and 'total'
     */
    public function get_all($page = 1, $per_page = 20) {
        global $wpdb;

        $offset = ($page - 1) * $per_page;

        $contacts = $wpdb->get_results($wpdb->prepare(
            "SELECT * FROM {$this->table_name} ORDER BY created_at DESC LIMIT %d OFFSET %d",
            $per_page,
            $offset
        ));

        $total = (int) $wpdb->get_var("SELECT COUNT(*) FROM {$this->table_name}");

        return array(
            'contacts' => $contacts,
            'total'    => $total,
            'pages'    => ceil($total / $per_page),
        );
    }

    /**
     * Get table name
     *
     * @return string Table name with prefix
     */
    public function get_table_name() {
        return $this->table_name;
    }

    /**
     * Get total contact count
     *
     * @return int Total number of contacts
     */
    public function get_count() {
        global $wpdb;
        return (int) $wpdb->get_var("SELECT COUNT(*) FROM {$this->table_name}");
    }
}

// Initialize singleton instance and make globally accessible
global $cp_contact_manager;
$cp_contact_manager = CP_Contact_Manager::instance();

/**
 * Helper function to get contact manager instance
 *
 * @return CP_Contact_Manager
 */
function cp_contact_manager() {
    return CP_Contact_Manager::instance();
}
