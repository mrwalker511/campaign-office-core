<?php
/**
 * Custom Post Types for CampaignPress
 *
 * Registers political-specific custom post types:
 * - Issues/Policy Positions
 * - Events
 * - Endorsements
 * - Team Members
 * - Volunteer Opportunities
 *
 * @package CampaignPress_Core
 * @since 1.0.0
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Register Issues Custom Post Type
 */
function cp_register_issues_post_type() {
    $labels = array(
        'name'                  => _x('Issues', 'Post Type General Name', 'campaignpress-core'),
        'singular_name'         => _x('Issue', 'Post Type Singular Name', 'campaignpress-core'),
        'menu_name'             => __('Issues', 'campaignpress-core'),
        'name_admin_bar'        => __('Issue', 'campaignpress-core'),
        'archives'              => __('Issue Archives', 'campaignpress-core'),
        'attributes'            => __('Issue Attributes', 'campaignpress-core'),
        'parent_item_colon'     => __('Parent Issue:', 'campaignpress-core'),
        'all_items'             => __('All Issues', 'campaignpress-core'),
        'add_new_item'          => __('Add New Issue', 'campaignpress-core'),
        'add_new'               => __('Add New', 'campaignpress-core'),
        'new_item'              => __('New Issue', 'campaignpress-core'),
        'edit_item'             => __('Edit Issue', 'campaignpress-core'),
        'update_item'           => __('Update Issue', 'campaignpress-core'),
        'view_item'             => __('View Issue', 'campaignpress-core'),
        'view_items'            => __('View Issues', 'campaignpress-core'),
        'search_items'          => __('Search Issue', 'campaignpress-core'),
        'not_found'             => __('Not found', 'campaignpress-core'),
        'not_found_in_trash'    => __('Not found in Trash', 'campaignpress-core'),
        'featured_image'        => __('Featured Image', 'campaignpress-core'),
        'set_featured_image'    => __('Set featured image', 'campaignpress-core'),
        'remove_featured_image' => __('Remove featured image', 'campaignpress-core'),
        'use_featured_image'    => __('Use as featured image', 'campaignpress-core'),
        'insert_into_item'      => __('Insert into issue', 'campaignpress-core'),
        'uploaded_to_this_item' => __('Uploaded to this issue', 'campaignpress-core'),
        'items_list'            => __('Issues list', 'campaignpress-core'),
        'items_list_navigation' => __('Issues list navigation', 'campaignpress-core'),
        'filter_items_list'     => __('Filter issues list', 'campaignpress-core'),
    );

    $args = array(
        'label'                 => __('Issue', 'campaignpress-core'),
        'description'           => __('Policy positions and campaign issues', 'campaignpress-core'),
        'labels'                => $labels,
        'supports'              => array('title', 'editor', 'thumbnail', 'excerpt', 'revisions', 'custom-fields'),
        'taxonomies'            => array('issue_category'),
        'hierarchical'          => false,
        'public'                => true,
        'show_ui'               => true,
        'show_in_menu'          => true,
        'menu_position'         => 20,
        'menu_icon'             => 'dashicons-megaphone',
        'show_in_admin_bar'     => true,
        'show_in_nav_menus'     => true,
        'can_export'            => true,
        'has_archive'           => true,
        'exclude_from_search'   => false,
        'publicly_queryable'    => true,
        'capability_type'       => 'post',
        'show_in_rest'          => true,
        'rest_base'             => 'issues',
        'rest_controller_class' => 'WP_REST_Posts_Controller',
    );

    register_post_type('cp_issue', $args);

    // Register Issue Categories taxonomy
    $tax_labels = array(
        'name'              => _x('Issue Categories', 'taxonomy general name', 'campaignpress-core'),
        'singular_name'     => _x('Issue Category', 'taxonomy singular name', 'campaignpress-core'),
        'search_items'      => __('Search Issue Categories', 'campaignpress-core'),
        'all_items'         => __('All Issue Categories', 'campaignpress-core'),
        'parent_item'       => __('Parent Issue Category', 'campaignpress-core'),
        'parent_item_colon' => __('Parent Issue Category:', 'campaignpress-core'),
        'edit_item'         => __('Edit Issue Category', 'campaignpress-core'),
        'update_item'       => __('Update Issue Category', 'campaignpress-core'),
        'add_new_item'      => __('Add New Issue Category', 'campaignpress-core'),
        'new_item_name'     => __('New Issue Category Name', 'campaignpress-core'),
        'menu_name'         => __('Categories', 'campaignpress-core'),
    );

    $tax_args = array(
        'hierarchical'      => true,
        'labels'            => $tax_labels,
        'show_ui'           => true,
        'show_admin_column' => true,
        'query_var'         => true,
        'rewrite'           => array('slug' => 'issue-category'),
        'show_in_rest'      => true,
    );

    register_taxonomy('issue_category', array('cp_issue'), $tax_args);
}
add_action('init', 'cp_register_issues_post_type', 0);

/**
 * Register Events Custom Post Type
 */
function cp_register_events_post_type() {
    $labels = array(
        'name'                  => _x('Events', 'Post Type General Name', 'campaignpress-core'),
        'singular_name'         => _x('Event', 'Post Type Singular Name', 'campaignpress-core'),
        'menu_name'             => __('Events', 'campaignpress-core'),
        'name_admin_bar'        => __('Event', 'campaignpress-core'),
        'archives'              => __('Event Archives', 'campaignpress-core'),
        'attributes'            => __('Event Attributes', 'campaignpress-core'),
        'parent_item_colon'     => __('Parent Event:', 'campaignpress-core'),
        'all_items'             => __('All Events', 'campaignpress-core'),
        'add_new_item'          => __('Add New Event', 'campaignpress-core'),
        'add_new'               => __('Add New', 'campaignpress-core'),
        'new_item'              => __('New Event', 'campaignpress-core'),
        'edit_item'             => __('Edit Event', 'campaignpress-core'),
        'update_item'           => __('Update Event', 'campaignpress-core'),
        'view_item'             => __('View Event', 'campaignpress-core'),
        'view_items'            => __('View Events', 'campaignpress-core'),
        'search_items'          => __('Search Event', 'campaignpress-core'),
        'not_found'             => __('Not found', 'campaignpress-core'),
        'not_found_in_trash'    => __('Not found in Trash', 'campaignpress-core'),
        'featured_image'        => __('Event Image', 'campaignpress-core'),
        'set_featured_image'    => __('Set event image', 'campaignpress-core'),
        'remove_featured_image' => __('Remove event image', 'campaignpress-core'),
        'use_featured_image'    => __('Use as event image', 'campaignpress-core'),
        'insert_into_item'      => __('Insert into event', 'campaignpress-core'),
        'uploaded_to_this_item' => __('Uploaded to this event', 'campaignpress-core'),
        'items_list'            => __('Events list', 'campaignpress-core'),
        'items_list_navigation' => __('Events list navigation', 'campaignpress-core'),
        'filter_items_list'     => __('Filter events list', 'campaignpress-core'),
    );

    $args = array(
        'label'                 => __('Event', 'campaignpress-core'),
        'description'           => __('Campaign events and appearances', 'campaignpress-core'),
        'labels'                => $labels,
        'supports'              => array('title', 'editor', 'thumbnail', 'excerpt', 'custom-fields'),
        'taxonomies'            => array('event_type'),
        'hierarchical'          => false,
        'public'                => true,
        'show_ui'               => true,
        'show_in_menu'          => true,
        'menu_position'         => 21,
        'menu_icon'             => 'dashicons-calendar-alt',
        'show_in_admin_bar'     => true,
        'show_in_nav_menus'     => true,
        'can_export'            => true,
        'has_archive'           => true,
        'exclude_from_search'   => false,
        'publicly_queryable'    => true,
        'capability_type'       => 'post',
        'show_in_rest'          => true,
        'rest_base'             => 'events',
    );

    register_post_type('cp_event', $args);

    // Register Event Type taxonomy
    $tax_labels = array(
        'name'              => _x('Event Types', 'taxonomy general name', 'campaignpress-core'),
        'singular_name'     => _x('Event Type', 'taxonomy singular name', 'campaignpress-core'),
        'search_items'      => __('Search Event Types', 'campaignpress-core'),
        'all_items'         => __('All Event Types', 'campaignpress-core'),
        'edit_item'         => __('Edit Event Type', 'campaignpress-core'),
        'update_item'       => __('Update Event Type', 'campaignpress-core'),
        'add_new_item'      => __('Add New Event Type', 'campaignpress-core'),
        'new_item_name'     => __('New Event Type Name', 'campaignpress-core'),
        'menu_name'         => __('Event Types', 'campaignpress-core'),
    );

    $tax_args = array(
        'hierarchical'      => false,
        'labels'            => $tax_labels,
        'show_ui'           => true,
        'show_admin_column' => true,
        'query_var'         => true,
        'rewrite'           => array('slug' => 'event-type'),
        'show_in_rest'      => true,
    );

    register_taxonomy('event_type', array('cp_event'), $tax_args);
}
add_action('init', 'cp_register_events_post_type', 0);

/**
 * Register Endorsements Custom Post Type
 */
function cp_register_endorsements_post_type() {
    $labels = array(
        'name'                  => _x('Endorsements', 'Post Type General Name', 'campaignpress-core'),
        'singular_name'         => _x('Endorsement', 'Post Type Singular Name', 'campaignpress-core'),
        'menu_name'             => __('Endorsements', 'campaignpress-core'),
        'name_admin_bar'        => __('Endorsement', 'campaignpress-core'),
        'archives'              => __('Endorsement Archives', 'campaignpress-core'),
        'attributes'            => __('Endorsement Attributes', 'campaignpress-core'),
        'parent_item_colon'     => __('Parent Endorsement:', 'campaignpress-core'),
        'all_items'             => __('All Endorsements', 'campaignpress-core'),
        'add_new_item'          => __('Add New Endorsement', 'campaignpress-core'),
        'add_new'               => __('Add New', 'campaignpress-core'),
        'new_item'              => __('New Endorsement', 'campaignpress-core'),
        'edit_item'             => __('Edit Endorsement', 'campaignpress-core'),
        'update_item'           => __('Update Endorsement', 'campaignpress-core'),
        'view_item'             => __('View Endorsement', 'campaignpress-core'),
        'view_items'            => __('View Endorsements', 'campaignpress-core'),
        'search_items'          => __('Search Endorsement', 'campaignpress-core'),
        'not_found'             => __('Not found', 'campaignpress-core'),
        'not_found_in_trash'    => __('Not found in Trash', 'campaignpress-core'),
        'featured_image'        => __('Endorser Photo', 'campaignpress-core'),
        'set_featured_image'    => __('Set endorser photo', 'campaignpress-core'),
        'remove_featured_image' => __('Remove endorser photo', 'campaignpress-core'),
        'use_featured_image'    => __('Use as endorser photo', 'campaignpress-core'),
        'insert_into_item'      => __('Insert into endorsement', 'campaignpress-core'),
        'uploaded_to_this_item' => __('Uploaded to this endorsement', 'campaignpress-core'),
        'items_list'            => __('Endorsements list', 'campaignpress-core'),
        'items_list_navigation' => __('Endorsements list navigation', 'campaignpress-core'),
        'filter_items_list'     => __('Filter endorsements list', 'campaignpress-core'),
    );

    $args = array(
        'label'                 => __('Endorsement', 'campaignpress-core'),
        'description'           => __('Campaign endorsements', 'campaignpress-core'),
        'labels'                => $labels,
        'supports'              => array('title', 'editor', 'thumbnail', 'custom-fields'),
        'hierarchical'          => false,
        'public'                => true,
        'show_ui'               => true,
        'show_in_menu'          => true,
        'menu_position'         => 22,
        'menu_icon'             => 'dashicons-thumbs-up',
        'show_in_admin_bar'     => true,
        'show_in_nav_menus'     => true,
        'can_export'            => true,
        'has_archive'           => true,
        'exclude_from_search'   => false,
        'publicly_queryable'    => true,
        'capability_type'       => 'post',
        'show_in_rest'          => true,
        'rest_base'             => 'endorsements',
    );

    register_post_type('cp_endorsement', $args);
}
add_action('init', 'cp_register_endorsements_post_type', 0);

/**
 * Register Team Members Custom Post Type
 */
function cp_register_team_post_type() {
    $labels = array(
        'name'                  => _x('Team Members', 'Post Type General Name', 'campaignpress-core'),
        'singular_name'         => _x('Team Member', 'Post Type Singular Name', 'campaignpress-core'),
        'menu_name'             => __('Team', 'campaignpress-core'),
        'name_admin_bar'        => __('Team Member', 'campaignpress-core'),
        'archives'              => __('Team Member Archives', 'campaignpress-core'),
        'attributes'            => __('Team Member Attributes', 'campaignpress-core'),
        'parent_item_colon'     => __('Parent Team Member:', 'campaignpress-core'),
        'all_items'             => __('All Team Members', 'campaignpress-core'),
        'add_new_item'          => __('Add New Team Member', 'campaignpress-core'),
        'add_new'               => __('Add New', 'campaignpress-core'),
        'new_item'              => __('New Team Member', 'campaignpress-core'),
        'edit_item'             => __('Edit Team Member', 'campaignpress-core'),
        'update_item'           => __('Update Team Member', 'campaignpress-core'),
        'view_item'             => __('View Team Member', 'campaignpress-core'),
        'view_items'            => __('View Team Members', 'campaignpress-core'),
        'search_items'          => __('Search Team Member', 'campaignpress-core'),
        'not_found'             => __('Not found', 'campaignpress-core'),
        'not_found_in_trash'    => __('Not found in Trash', 'campaignpress-core'),
        'featured_image'        => __('Team Member Photo', 'campaignpress-core'),
        'set_featured_image'    => __('Set photo', 'campaignpress-core'),
        'remove_featured_image' => __('Remove photo', 'campaignpress-core'),
        'use_featured_image'    => __('Use as photo', 'campaignpress-core'),
        'insert_into_item'      => __('Insert into team member', 'campaignpress-core'),
        'uploaded_to_this_item' => __('Uploaded to this team member', 'campaignpress-core'),
        'items_list'            => __('Team members list', 'campaignpress-core'),
        'items_list_navigation' => __('Team members list navigation', 'campaignpress-core'),
        'filter_items_list'     => __('Filter team members list', 'campaignpress-core'),
    );

    $args = array(
        'label'                 => __('Team Member', 'campaignpress-core'),
        'description'           => __('Campaign team members and staff', 'campaignpress-core'),
        'labels'                => $labels,
        'supports'              => array('title', 'editor', 'thumbnail', 'custom-fields'),
        'hierarchical'          => false,
        'public'                => true,
        'show_ui'               => true,
        'show_in_menu'          => true,
        'menu_position'         => 23,
        'menu_icon'             => 'dashicons-groups',
        'show_in_admin_bar'     => true,
        'show_in_nav_menus'     => true,
        'can_export'            => true,
        'has_archive'           => true,
        'exclude_from_search'   => false,
        'publicly_queryable'    => true,
        'capability_type'       => 'post',
        'show_in_rest'          => true,
        'rest_base'             => 'team',
    );

    register_post_type('cp_team', $args);
}
add_action('init', 'cp_register_team_post_type', 0);

/**
 * Register Volunteer Opportunities Custom Post Type
 */
function cp_register_volunteer_post_type() {
    $labels = array(
        'name'                  => _x('Volunteer Opportunities', 'Post Type General Name', 'campaignpress-core'),
        'singular_name'         => _x('Volunteer Opportunity', 'Post Type Singular Name', 'campaignpress-core'),
        'menu_name'             => __('Volunteer Opportunities', 'campaignpress-core'),
        'name_admin_bar'        => __('Volunteer Opportunity', 'campaignpress-core'),
        'archives'              => __('Volunteer Opportunity Archives', 'campaignpress-core'),
        'attributes'            => __('Volunteer Opportunity Attributes', 'campaignpress-core'),
        'parent_item_colon'     => __('Parent Opportunity:', 'campaignpress-core'),
        'all_items'             => __('All Opportunities', 'campaignpress-core'),
        'add_new_item'          => __('Add New Opportunity', 'campaignpress-core'),
        'add_new'               => __('Add New', 'campaignpress-core'),
        'new_item'              => __('New Opportunity', 'campaignpress-core'),
        'edit_item'             => __('Edit Opportunity', 'campaignpress-core'),
        'update_item'           => __('Update Opportunity', 'campaignpress-core'),
        'view_item'             => __('View Opportunity', 'campaignpress-core'),
        'view_items'            => __('View Opportunities', 'campaignpress-core'),
        'search_items'          => __('Search Opportunity', 'campaignpress-core'),
        'not_found'             => __('Not found', 'campaignpress-core'),
        'not_found_in_trash'    => __('Not found in Trash', 'campaignpress-core'),
        'featured_image'        => __('Opportunity Image', 'campaignpress-core'),
        'set_featured_image'    => __('Set opportunity image', 'campaignpress-core'),
        'remove_featured_image' => __('Remove opportunity image', 'campaignpress-core'),
        'use_featured_image'    => __('Use as opportunity image', 'campaignpress-core'),
        'insert_into_item'      => __('Insert into opportunity', 'campaignpress-core'),
        'uploaded_to_this_item' => __('Uploaded to this opportunity', 'campaignpress-core'),
        'items_list'            => __('Opportunities list', 'campaignpress-core'),
        'items_list_navigation' => __('Opportunities list navigation', 'campaignpress-core'),
        'filter_items_list'     => __('Filter opportunities list', 'campaignpress-core'),
    );

    $args = array(
        'label'                 => __('Volunteer Opportunity', 'campaignpress-core'),
        'description'           => __('Volunteer opportunities and positions', 'campaignpress-core'),
        'labels'                => $labels,
        'supports'              => array('title', 'editor', 'thumbnail', 'excerpt', 'custom-fields'),
        'hierarchical'          => false,
        'public'                => true,
        'show_ui'               => true,
        'show_in_menu'          => true,
        'menu_position'         => 24,
        'menu_icon'             => 'dashicons-heart',
        'show_in_admin_bar'     => true,
        'show_in_nav_menus'     => true,
        'can_export'            => true,
        'has_archive'           => true,
        'exclude_from_search'   => false,
        'publicly_queryable'    => true,
        'capability_type'       => 'post',
        'show_in_rest'          => true,
        'rest_base'             => 'volunteer-opportunities',
    );

    register_post_type('cp_volunteer', $args);
}
add_action('init', 'cp_register_volunteer_post_type', 0);

/**
 * Register Press Releases Custom Post Type
 */
function cp_register_press_release_post_type() {
    $labels = array(
        'name'                  => _x('Press Releases', 'Post Type General Name', 'campaignpress-core'),
        'singular_name'         => _x('Press Release', 'Post Type Singular Name', 'campaignpress-core'),
        'menu_name'             => __('Press Releases', 'campaignpress-core'),
        'name_admin_bar'        => __('Press Release', 'campaignpress-core'),
        'archives'              => __('Press Release Archives', 'campaignpress-core'),
        'attributes'            => __('Press Release Attributes', 'campaignpress-core'),
        'parent_item_colon'     => __('Parent Press Release:', 'campaignpress-core'),
        'all_items'             => __('All Press Releases', 'campaignpress-core'),
        'add_new_item'          => __('Add New Press Release', 'campaignpress-core'),
        'add_new'               => __('Add New', 'campaignpress-core'),
        'new_item'              => __('New Press Release', 'campaignpress-core'),
        'edit_item'             => __('Edit Press Release', 'campaignpress-core'),
        'update_item'           => __('Update Press Release', 'campaignpress-core'),
        'view_item'             => __('View Press Release', 'campaignpress-core'),
        'view_items'            => __('View Press Releases', 'campaignpress-core'),
        'search_items'          => __('Search Press Release', 'campaignpress-core'),
        'not_found'             => __('Not found', 'campaignpress-core'),
        'not_found_in_trash'    => __('Not found in Trash', 'campaignpress-core'),
        'featured_image'        => __('Featured Image', 'campaignpress-core'),
        'set_featured_image'    => __('Set featured image', 'campaignpress-core'),
        'remove_featured_image' => __('Remove featured image', 'campaignpress-core'),
        'use_featured_image'    => __('Use as featured image', 'campaignpress-core'),
        'insert_into_item'      => __('Insert into press release', 'campaignpress-core'),
        'uploaded_to_this_item' => __('Uploaded to this press release', 'campaignpress-core'),
        'items_list'            => __('Press releases list', 'campaignpress-core'),
        'items_list_navigation' => __('Press releases list navigation', 'campaignpress-core'),
        'filter_items_list'     => __('Filter press releases list', 'campaignpress-core'),
    );

    $args = array(
        'label'                 => __('Press Release', 'campaignpress-core'),
        'description'           => __('Campaign press releases and statements', 'campaignpress-core'),
        'labels'                => $labels,
        'supports'              => array('title', 'editor', 'thumbnail', 'excerpt', 'custom-fields', 'author'),
        'hierarchical'          => false,
        'public'                => true,
        'show_ui'               => true,
        'show_in_menu'          => true,
        'menu_position'         => 25,
        'menu_icon'             => 'dashicons-media-document',
        'show_in_admin_bar'     => true,
        'show_in_nav_menus'     => true,
        'can_export'            => true,
        'has_archive'           => true,
        'exclude_from_search'   => false,
        'publicly_queryable'    => true,
        'capability_type'       => 'post',
        'show_in_rest'          => true,
        'rest_base'             => 'press-releases',
    );

    register_post_type('cp_press_release', $args);
}
add_action('init', 'cp_register_press_release_post_type', 0);

/**
 * Add custom meta boxes for event details
 */
function cp_add_event_meta_boxes() {
    add_meta_box(
        'cp_event_details',
        __('Event Details', 'campaignpress-core'),
        'cp_event_details_callback',
        'cp_event',
        'normal',
        'high'
    );
}
add_action('add_meta_boxes', 'cp_add_event_meta_boxes');

/**
 * Event details meta box callback
 */
function cp_event_details_callback($post) {
    wp_nonce_field('cp_event_details_nonce', 'cp_event_details_nonce_field');

    $event_date = get_post_meta($post->ID, '_cp_event_date', true);
    $event_time = get_post_meta($post->ID, '_cp_event_time', true);
    $event_location = get_post_meta($post->ID, '_cp_event_location', true);
    $event_address = get_post_meta($post->ID, '_cp_event_address', true);
    $event_city = get_post_meta($post->ID, '_cp_event_city', true);
    $event_state = get_post_meta($post->ID, '_cp_event_state', true);
    $event_zip = get_post_meta($post->ID, '_cp_event_zip', true);
    $event_rsvp_link = get_post_meta($post->ID, '_cp_event_rsvp_link', true);

    ?>
    <p>
        <label for="cp_event_date"><strong><?php esc_html_e('Event Date:', 'campaignpress-core'); ?></strong></label><br>
        <input type="date" id="cp_event_date" name="cp_event_date" value="<?php echo esc_attr($event_date); ?>" style="width: 100%; max-width: 300px;">
    </p>
    <p>
        <label for="cp_event_time"><strong><?php esc_html_e('Event Time:', 'campaignpress-core'); ?></strong></label><br>
        <input type="time" id="cp_event_time" name="cp_event_time" value="<?php echo esc_attr($event_time); ?>" style="width: 100%; max-width: 300px;">
    </p>
    <p>
        <label for="cp_event_location"><strong><?php esc_html_e('Location Name:', 'campaignpress-core'); ?></strong></label><br>
        <input type="text" id="cp_event_location" name="cp_event_location" value="<?php echo esc_attr($event_location); ?>" style="width: 100%;">
    </p>
    <p>
        <label for="cp_event_address"><strong><?php esc_html_e('Street Address:', 'campaignpress-core'); ?></strong></label><br>
        <input type="text" id="cp_event_address" name="cp_event_address" value="<?php echo esc_attr($event_address); ?>" style="width: 100%;">
    </p>
    <p>
        <label for="cp_event_city"><strong><?php esc_html_e('City:', 'campaignpress-core'); ?></strong></label><br>
        <input type="text" id="cp_event_city" name="cp_event_city" value="<?php echo esc_attr($event_city); ?>" style="width: 100%;">
    </p>
    <p>
        <label for="cp_event_state"><strong><?php esc_html_e('State:', 'campaignpress-core'); ?></strong></label><br>
        <input type="text" id="cp_event_state" name="cp_event_state" value="<?php echo esc_attr($event_state); ?>" maxlength="2" style="width: 100px;">
    </p>
    <p>
        <label for="cp_event_zip"><strong><?php esc_html_e('ZIP Code:', 'campaignpress-core'); ?></strong></label><br>
        <input type="text" id="cp_event_zip" name="cp_event_zip" value="<?php echo esc_attr($event_zip); ?>" style="width: 150px;">
    </p>
    <p>
        <label for="cp_event_rsvp_link"><strong><?php esc_html_e('RSVP Link:', 'campaignpress-core'); ?></strong></label><br>
        <input type="url" id="cp_event_rsvp_link" name="cp_event_rsvp_link" value="<?php echo esc_url($event_rsvp_link); ?>" style="width: 100%;" placeholder="https://">
    </p>
    <?php
}

/**
 * Save event meta data
 */
function cp_save_event_meta($post_id) {
    // Check nonce
    if (!isset($_POST['cp_event_details_nonce_field']) ||
        !wp_verify_nonce($_POST['cp_event_details_nonce_field'], 'cp_event_details_nonce')) {
        return;
    }

    // Check autosave
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
        return;
    }

    // Check permissions
    if (!current_user_can('edit_post', $post_id)) {
        return;
    }

    // Define allowed sanitization callbacks (whitelist for security)
    $allowed_callbacks = array('sanitize_text_field', 'esc_url_raw');

    // Save fields with validated callbacks
    $fields = array(
        'cp_event_date' => 'sanitize_text_field',
        'cp_event_time' => 'sanitize_text_field',
        'cp_event_location' => 'sanitize_text_field',
        'cp_event_address' => 'sanitize_text_field',
        'cp_event_city' => 'sanitize_text_field',
        'cp_event_state' => 'sanitize_text_field',
        'cp_event_zip' => 'sanitize_text_field',
        'cp_event_rsvp_link' => 'esc_url_raw',
    );

    foreach ($fields as $field => $sanitize_callback) {
        if (isset($_POST[$field]) && in_array($sanitize_callback, $allowed_callbacks, true)) {
            update_post_meta($post_id, '_' . $field, call_user_func($sanitize_callback, $_POST[$field]));
        }
    }
}
add_action('save_post_cp_event', 'cp_save_event_meta');

/**
 * Flush rewrite rules
 *
 * Note: CPTs are automatically registered via register_post_type() calls
 * hooked to 'init'. Rewrite rules are flushed on plugin activation/deactivation.
 */
