<?php
/**
 * Uninstall CampaignPress Core
 *
 * This file runs when the plugin is deleted via WordPress admin.
 * It cleans up all database tables and options created by the plugin.
 *
 * @package CampaignPress_Core
 * @since 1.0.0
 */

// Exit if not called by WordPress uninstall
if (!defined('WP_UNINSTALL_PLUGIN')) {
    exit;
}

global $wpdb;

/**
 * Remove all plugin database tables
 */
$tables_to_drop = array(
    $wpdb->prefix . 'cp_contacts',
    $wpdb->prefix . 'cp_volunteers',
    $wpdb->prefix . 'cp_event_rsvps',
);

foreach ($tables_to_drop as $table) {
    $wpdb->query("DROP TABLE IF EXISTS {$table}");
}

/**
 * Remove all plugin options
 */
$options_to_delete = array(
    'campaign_office_core_activated',
    'campaign_office_core_version',
    'cp_contacts_db_version',
    'cp_contacts_table_created',
    'cp_volunteer_db_version',
    'cp_volunteer_table_created',
    'cp_event_rsvp_table_created',
);

foreach ($options_to_delete as $option) {
    delete_option($option);
}

/**
 * Note: Custom post types and their content are NOT deleted.
 * This is intentional - users may want to keep their content
 * even after removing the plugin. The posts will simply become
 * inaccessible until the plugin is reinstalled or another
 * plugin registers the same post types.
 *
 * To fully remove all content, users should:
 * 1. Delete all posts of types: cp_issue, cp_event, cp_endorsement,
 *    cp_team, cp_volunteer, cp_press_release
 * 2. Then delete the plugin
 */
