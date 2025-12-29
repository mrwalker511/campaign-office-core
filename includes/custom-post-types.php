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
 * @package CampaignPress
 * @since 1.0.0
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Register Issues Custom Post Type
 */
function campaignpress_register_issues_post_type() {
    $labels = array(
        'name'                  => _x('Issues', 'Post Type General Name', 'campaign-office-core'),
        'singular_name'         => _x('Issue', 'Post Type Singular Name', 'campaign-office-core'),
        'menu_name'             => __('Issues', 'campaign-office-core'),
        'name_admin_bar'        => __('Issue', 'campaign-office-core'),
        'archives'              => __('Issue Archives', 'campaign-office-core'),
        'attributes'            => __('Issue Attributes', 'campaign-office-core'),
        'parent_item_colon'     => __('Parent Issue:', 'campaign-office-core'),
        'all_items'             => __('All Issues', 'campaign-office-core'),
        'add_new_item'          => __('Add New Issue', 'campaign-office-core'),
        'add_new'               => __('Add New', 'campaign-office-core'),
        'new_item'              => __('New Issue', 'campaign-office-core'),
        'edit_item'             => __('Edit Issue', 'campaign-office-core'),
        'update_item'           => __('Update Issue', 'campaign-office-core'),
        'view_item'             => __('View Issue', 'campaign-office-core'),
        'view_items'            => __('View Issues', 'campaign-office-core'),
        'search_items'          => __('Search Issue', 'campaign-office-core'),
        'not_found'             => __('Not found', 'campaign-office-core'),
        'not_found_in_trash'    => __('Not found in Trash', 'campaign-office-core'),
        'featured_image'        => __('Featured Image', 'campaign-office-core'),
        'set_featured_image'    => __('Set featured image', 'campaign-office-core'),
        'remove_featured_image' => __('Remove featured image', 'campaign-office-core'),
        'use_featured_image'    => __('Use as featured image', 'campaign-office-core'),
        'insert_into_item'      => __('Insert into issue', 'campaign-office-core'),
        'uploaded_to_this_item' => __('Uploaded to this issue', 'campaign-office-core'),
        'items_list'            => __('Issues list', 'campaign-office-core'),
        'items_list_navigation' => __('Issues list navigation', 'campaign-office-core'),
        'filter_items_list'     => __('Filter issues list', 'campaign-office-core'),
    );

    $args = array(
        'label'                 => __('Issue', 'campaign-office-core'),
        'description'           => __('Policy positions and campaign issues', 'campaign-office-core'),
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
        'name'              => _x('Issue Categories', 'taxonomy general name', 'campaign-office-core'),
        'singular_name'     => _x('Issue Category', 'taxonomy singular name', 'campaign-office-core'),
        'search_items'      => __('Search Issue Categories', 'campaign-office-core'),
        'all_items'         => __('All Issue Categories', 'campaign-office-core'),
        'parent_item'       => __('Parent Issue Category', 'campaign-office-core'),
        'parent_item_colon' => __('Parent Issue Category:', 'campaign-office-core'),
        'edit_item'         => __('Edit Issue Category', 'campaign-office-core'),
        'update_item'       => __('Update Issue Category', 'campaign-office-core'),
        'add_new_item'      => __('Add New Issue Category', 'campaign-office-core'),
        'new_item_name'     => __('New Issue Category Name', 'campaign-office-core'),
        'menu_name'         => __('Categories', 'campaign-office-core'),
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
add_action('init', 'campaignpress_register_issues_post_type', 0);

/**
 * Register Events Custom Post Type
 */
function campaignpress_register_events_post_type() {
    $labels = array(
        'name'                  => _x('Events', 'Post Type General Name', 'campaign-office-core'),
        'singular_name'         => _x('Event', 'Post Type Singular Name', 'campaign-office-core'),
        'menu_name'             => __('Events', 'campaign-office-core'),
        'name_admin_bar'        => __('Event', 'campaign-office-core'),
        'archives'              => __('Event Archives', 'campaign-office-core'),
        'attributes'            => __('Event Attributes', 'campaign-office-core'),
        'parent_item_colon'     => __('Parent Event:', 'campaign-office-core'),
        'all_items'             => __('All Events', 'campaign-office-core'),
        'add_new_item'          => __('Add New Event', 'campaign-office-core'),
        'add_new'               => __('Add New', 'campaign-office-core'),
        'new_item'              => __('New Event', 'campaign-office-core'),
        'edit_item'             => __('Edit Event', 'campaign-office-core'),
        'update_item'           => __('Update Event', 'campaign-office-core'),
        'view_item'             => __('View Event', 'campaign-office-core'),
        'view_items'            => __('View Events', 'campaign-office-core'),
        'search_items'          => __('Search Event', 'campaign-office-core'),
        'not_found'             => __('Not found', 'campaign-office-core'),
        'not_found_in_trash'    => __('Not found in Trash', 'campaign-office-core'),
        'featured_image'        => __('Event Image', 'campaign-office-core'),
        'set_featured_image'    => __('Set event image', 'campaign-office-core'),
        'remove_featured_image' => __('Remove event image', 'campaign-office-core'),
        'use_featured_image'    => __('Use as event image', 'campaign-office-core'),
        'insert_into_item'      => __('Insert into event', 'campaign-office-core'),
        'uploaded_to_this_item' => __('Uploaded to this event', 'campaign-office-core'),
        'items_list'            => __('Events list', 'campaign-office-core'),
        'items_list_navigation' => __('Events list navigation', 'campaign-office-core'),
        'filter_items_list'     => __('Filter events list', 'campaign-office-core'),
    );

    $args = array(
        'label'                 => __('Event', 'campaign-office-core'),
        'description'           => __('Campaign events and appearances', 'campaign-office-core'),
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
        'name'              => _x('Event Types', 'taxonomy general name', 'campaign-office-core'),
        'singular_name'     => _x('Event Type', 'taxonomy singular name', 'campaign-office-core'),
        'search_items'      => __('Search Event Types', 'campaign-office-core'),
        'all_items'         => __('All Event Types', 'campaign-office-core'),
        'edit_item'         => __('Edit Event Type', 'campaign-office-core'),
        'update_item'       => __('Update Event Type', 'campaign-office-core'),
        'add_new_item'      => __('Add New Event Type', 'campaign-office-core'),
        'new_item_name'     => __('New Event Type Name', 'campaign-office-core'),
        'menu_name'         => __('Event Types', 'campaign-office-core'),
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
add_action('init', 'campaignpress_register_events_post_type', 0);

/**
 * Register Endorsements Custom Post Type
 */
function campaignpress_register_endorsements_post_type() {
    $labels = array(
        'name'                  => _x('Endorsements', 'Post Type General Name', 'campaign-office-core'),
        'singular_name'         => _x('Endorsement', 'Post Type Singular Name', 'campaign-office-core'),
        'menu_name'             => __('Endorsements', 'campaign-office-core'),
        'name_admin_bar'        => __('Endorsement', 'campaign-office-core'),
        'archives'              => __('Endorsement Archives', 'campaign-office-core'),
        'attributes'            => __('Endorsement Attributes', 'campaign-office-core'),
        'parent_item_colon'     => __('Parent Endorsement:', 'campaign-office-core'),
        'all_items'             => __('All Endorsements', 'campaign-office-core'),
        'add_new_item'          => __('Add New Endorsement', 'campaign-office-core'),
        'add_new'               => __('Add New', 'campaign-office-core'),
        'new_item'              => __('New Endorsement', 'campaign-office-core'),
        'edit_item'             => __('Edit Endorsement', 'campaign-office-core'),
        'update_item'           => __('Update Endorsement', 'campaign-office-core'),
        'view_item'             => __('View Endorsement', 'campaign-office-core'),
        'view_items'            => __('View Endorsements', 'campaign-office-core'),
        'search_items'          => __('Search Endorsement', 'campaign-office-core'),
        'not_found'             => __('Not found', 'campaign-office-core'),
        'not_found_in_trash'    => __('Not found in Trash', 'campaign-office-core'),
        'featured_image'        => __('Endorser Photo', 'campaign-office-core'),
        'set_featured_image'    => __('Set endorser photo', 'campaign-office-core'),
        'remove_featured_image' => __('Remove endorser photo', 'campaign-office-core'),
        'use_featured_image'    => __('Use as endorser photo', 'campaign-office-core'),
        'insert_into_item'      => __('Insert into endorsement', 'campaign-office-core'),
        'uploaded_to_this_item' => __('Uploaded to this endorsement', 'campaign-office-core'),
        'items_list'            => __('Endorsements list', 'campaign-office-core'),
        'items_list_navigation' => __('Endorsements list navigation', 'campaign-office-core'),
        'filter_items_list'     => __('Filter endorsements list', 'campaign-office-core'),
    );

    $args = array(
        'label'                 => __('Endorsement', 'campaign-office-core'),
        'description'           => __('Campaign endorsements', 'campaign-office-core'),
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
add_action('init', 'campaignpress_register_endorsements_post_type', 0);

/**
 * Register Team Members Custom Post Type
 */
function campaignpress_register_team_post_type() {
    $labels = array(
        'name'                  => _x('Team Members', 'Post Type General Name', 'campaign-office-core'),
        'singular_name'         => _x('Team Member', 'Post Type Singular Name', 'campaign-office-core'),
        'menu_name'             => __('Team', 'campaign-office-core'),
        'name_admin_bar'        => __('Team Member', 'campaign-office-core'),
        'archives'              => __('Team Member Archives', 'campaign-office-core'),
        'attributes'            => __('Team Member Attributes', 'campaign-office-core'),
        'parent_item_colon'     => __('Parent Team Member:', 'campaign-office-core'),
        'all_items'             => __('All Team Members', 'campaign-office-core'),
        'add_new_item'          => __('Add New Team Member', 'campaign-office-core'),
        'add_new'               => __('Add New', 'campaign-office-core'),
        'new_item'              => __('New Team Member', 'campaign-office-core'),
        'edit_item'             => __('Edit Team Member', 'campaign-office-core'),
        'update_item'           => __('Update Team Member', 'campaign-office-core'),
        'view_item'             => __('View Team Member', 'campaign-office-core'),
        'view_items'            => __('View Team Members', 'campaign-office-core'),
        'search_items'          => __('Search Team Member', 'campaign-office-core'),
        'not_found'             => __('Not found', 'campaign-office-core'),
        'not_found_in_trash'    => __('Not found in Trash', 'campaign-office-core'),
        'featured_image'        => __('Team Member Photo', 'campaign-office-core'),
        'set_featured_image'    => __('Set photo', 'campaign-office-core'),
        'remove_featured_image' => __('Remove photo', 'campaign-office-core'),
        'use_featured_image'    => __('Use as photo', 'campaign-office-core'),
        'insert_into_item'      => __('Insert into team member', 'campaign-office-core'),
        'uploaded_to_this_item' => __('Uploaded to this team member', 'campaign-office-core'),
        'items_list'            => __('Team members list', 'campaign-office-core'),
        'items_list_navigation' => __('Team members list navigation', 'campaign-office-core'),
        'filter_items_list'     => __('Filter team members list', 'campaign-office-core'),
    );

    $args = array(
        'label'                 => __('Team Member', 'campaign-office-core'),
        'description'           => __('Campaign team members and staff', 'campaign-office-core'),
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
add_action('init', 'campaignpress_register_team_post_type', 0);

/**
 * Register Volunteer Opportunities Custom Post Type
 */
function campaignpress_register_volunteer_post_type() {
    $labels = array(
        'name'                  => _x('Volunteer Opportunities', 'Post Type General Name', 'campaign-office-core'),
        'singular_name'         => _x('Volunteer Opportunity', 'Post Type Singular Name', 'campaign-office-core'),
        'menu_name'             => __('Volunteer Opportunities', 'campaign-office-core'),
        'name_admin_bar'        => __('Volunteer Opportunity', 'campaign-office-core'),
        'archives'              => __('Volunteer Opportunity Archives', 'campaign-office-core'),
        'attributes'            => __('Volunteer Opportunity Attributes', 'campaign-office-core'),
        'parent_item_colon'     => __('Parent Opportunity:', 'campaign-office-core'),
        'all_items'             => __('All Opportunities', 'campaign-office-core'),
        'add_new_item'          => __('Add New Opportunity', 'campaign-office-core'),
        'add_new'               => __('Add New', 'campaign-office-core'),
        'new_item'              => __('New Opportunity', 'campaign-office-core'),
        'edit_item'             => __('Edit Opportunity', 'campaign-office-core'),
        'update_item'           => __('Update Opportunity', 'campaign-office-core'),
        'view_item'             => __('View Opportunity', 'campaign-office-core'),
        'view_items'            => __('View Opportunities', 'campaign-office-core'),
        'search_items'          => __('Search Opportunity', 'campaign-office-core'),
        'not_found'             => __('Not found', 'campaign-office-core'),
        'not_found_in_trash'    => __('Not found in Trash', 'campaign-office-core'),
        'featured_image'        => __('Opportunity Image', 'campaign-office-core'),
        'set_featured_image'    => __('Set opportunity image', 'campaign-office-core'),
        'remove_featured_image' => __('Remove opportunity image', 'campaign-office-core'),
        'use_featured_image'    => __('Use as opportunity image', 'campaign-office-core'),
        'insert_into_item'      => __('Insert into opportunity', 'campaign-office-core'),
        'uploaded_to_this_item' => __('Uploaded to this opportunity', 'campaign-office-core'),
        'items_list'            => __('Opportunities list', 'campaign-office-core'),
        'items_list_navigation' => __('Opportunities list navigation', 'campaign-office-core'),
        'filter_items_list'     => __('Filter opportunities list', 'campaign-office-core'),
    );

    $args = array(
        'label'                 => __('Volunteer Opportunity', 'campaign-office-core'),
        'description'           => __('Volunteer opportunities and positions', 'campaign-office-core'),
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
add_action('init', 'campaignpress_register_volunteer_post_type', 0);

/**
 * Register Press Releases Custom Post Type
 */
function campaignpress_register_press_release_post_type() {
    $labels = array(
        'name'                  => _x('Press Releases', 'Post Type General Name', 'campaign-office-core'),
        'singular_name'         => _x('Press Release', 'Post Type Singular Name', 'campaign-office-core'),
        'menu_name'             => __('Press Releases', 'campaign-office-core'),
        'name_admin_bar'        => __('Press Release', 'campaign-office-core'),
        'archives'              => __('Press Release Archives', 'campaign-office-core'),
        'attributes'            => __('Press Release Attributes', 'campaign-office-core'),
        'parent_item_colon'     => __('Parent Press Release:', 'campaign-office-core'),
        'all_items'             => __('All Press Releases', 'campaign-office-core'),
        'add_new_item'          => __('Add New Press Release', 'campaign-office-core'),
        'add_new'               => __('Add New', 'campaign-office-core'),
        'new_item'              => __('New Press Release', 'campaign-office-core'),
        'edit_item'             => __('Edit Press Release', 'campaign-office-core'),
        'update_item'           => __('Update Press Release', 'campaign-office-core'),
        'view_item'             => __('View Press Release', 'campaign-office-core'),
        'view_items'            => __('View Press Releases', 'campaign-office-core'),
        'search_items'          => __('Search Press Release', 'campaign-office-core'),
        'not_found'             => __('Not found', 'campaign-office-core'),
        'not_found_in_trash'    => __('Not found in Trash', 'campaign-office-core'),
        'featured_image'        => __('Featured Image', 'campaign-office-core'),
        'set_featured_image'    => __('Set featured image', 'campaign-office-core'),
        'remove_featured_image' => __('Remove featured image', 'campaign-office-core'),
        'use_featured_image'    => __('Use as featured image', 'campaign-office-core'),
        'insert_into_item'      => __('Insert into press release', 'campaign-office-core'),
        'uploaded_to_this_item' => __('Uploaded to this press release', 'campaign-office-core'),
        'items_list'            => __('Press releases list', 'campaign-office-core'),
        'items_list_navigation' => __('Press releases list navigation', 'campaign-office-core'),
        'filter_items_list'     => __('Filter press releases list', 'campaign-office-core'),
    );

    $args = array(
        'label'                 => __('Press Release', 'campaign-office-core'),
        'description'           => __('Campaign press releases and statements', 'campaign-office-core'),
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
add_action('init', 'campaignpress_register_press_release_post_type', 0);

/**
 * Add custom meta boxes for event details
 */
function campaignpress_add_event_meta_boxes() {
    add_meta_box(
        'cp_event_details',
        __('Event Details', 'campaign-office-core'),
        'campaignpress_event_details_callback',
        'cp_event',
        'normal',
        'high'
    );
}
add_action('add_meta_boxes', 'campaignpress_add_event_meta_boxes');

/**
 * Event details meta box callback
 */
function campaignpress_event_details_callback($post) {
    wp_nonce_field('campaignpress_event_details_nonce', 'campaignpress_event_details_nonce_field');

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
        <label for="cp_event_date"><strong><?php esc_html_e('Event Date:', 'campaign-office-core'); ?></strong></label><br>
        <input type="date" id="cp_event_date" name="cp_event_date" value="<?php echo esc_attr($event_date); ?>" style="width: 100%; max-width: 300px;">
    </p>
    <p>
        <label for="cp_event_time"><strong><?php esc_html_e('Event Time:', 'campaign-office-core'); ?></strong></label><br>
        <input type="time" id="cp_event_time" name="cp_event_time" value="<?php echo esc_attr($event_time); ?>" style="width: 100%; max-width: 300px;">
    </p>
    <p>
        <label for="cp_event_location"><strong><?php esc_html_e('Location Name:', 'campaign-office-core'); ?></strong></label><br>
        <input type="text" id="cp_event_location" name="cp_event_location" value="<?php echo esc_attr($event_location); ?>" style="width: 100%;">
    </p>
    <p>
        <label for="cp_event_address"><strong><?php esc_html_e('Street Address:', 'campaign-office-core'); ?></strong></label><br>
        <input type="text" id="cp_event_address" name="cp_event_address" value="<?php echo esc_attr($event_address); ?>" style="width: 100%;">
    </p>
    <p>
        <label for="cp_event_city"><strong><?php esc_html_e('City:', 'campaign-office-core'); ?></strong></label><br>
        <input type="text" id="cp_event_city" name="cp_event_city" value="<?php echo esc_attr($event_city); ?>" style="width: 100%;">
    </p>
    <p>
        <label for="cp_event_state"><strong><?php esc_html_e('State:', 'campaign-office-core'); ?></strong></label><br>
        <input type="text" id="cp_event_state" name="cp_event_state" value="<?php echo esc_attr($event_state); ?>" maxlength="2" style="width: 100px;">
    </p>
    <p>
        <label for="cp_event_zip"><strong><?php esc_html_e('ZIP Code:', 'campaign-office-core'); ?></strong></label><br>
        <input type="text" id="cp_event_zip" name="cp_event_zip" value="<?php echo esc_attr($event_zip); ?>" style="width: 150px;">
    </p>
    <p>
        <label for="cp_event_rsvp_link"><strong><?php esc_html_e('RSVP Link:', 'campaign-office-core'); ?></strong></label><br>
        <input type="url" id="cp_event_rsvp_link" name="cp_event_rsvp_link" value="<?php echo esc_url($event_rsvp_link); ?>" style="width: 100%;" placeholder="https://">
    </p>
    <?php
}

/**
 * Save event meta data
 */
function campaignpress_save_event_meta($post_id) {
    // Check nonce
    if (!isset($_POST['campaignpress_event_details_nonce_field']) ||
        !wp_verify_nonce($_POST['campaignpress_event_details_nonce_field'], 'campaignpress_event_details_nonce')) {
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
add_action('save_post_cp_event', 'campaignpress_save_event_meta');

/**
 * Flush rewrite rules on theme activation
 *
 * Note: Moved to functions.php using after_setup_theme hook
 * because register_activation_hook() doesn't work in themes.
 */
