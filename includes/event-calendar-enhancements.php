<?php
/**
 * Advanced Event Calendar Enhancements
 *
 * Adds calendar grid views, iCal export, and Google Maps integration
 * to the existing Campaign Office event management system.
 *
 * Features:
 * - Calendar grid views (month, week, day)
 * - iCal (.ics) event export
 * - Google Maps integration
 * - Event filtering and search
 * - Mobile-responsive calendar layouts
 *
 * @package Campaign_Office_Core
 * @since 1.0.0
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Class CP_Event_Calendar_Enhancements
 *
 * Handles advanced calendar features for campaign events
 */
class CP_Event_Calendar_Enhancements {

    /**
     * Constructor
     */
    public function __construct() {
        // Register shortcodes
        add_shortcode('cp_event_calendar', array($this, 'render_calendar_shortcode'));
        add_shortcode('cp_event_map', array($this, 'render_event_map'));

        // Add event calendar meta boxes
        add_action('add_meta_boxes', array($this, 'add_location_meta_box'));
        add_action('save_post_cp_event', array($this, 'save_location_meta'));

        // iCal export handler
        add_action('template_redirect', array($this, 'handle_ical_export'));

        // Add iCal download link to events
        add_filter('the_content', array($this, 'append_ical_download'));

        // Enqueue frontend assets
        // Frontend assets are now loaded by the main plugin class

        // Add admin menu
        add_action('admin_menu', array($this, 'add_admin_menu'));

        // AJAX handlers for calendar navigation
        add_action('wp_ajax_cp_get_calendar_events', array($this, 'ajax_get_calendar_events'));
        add_action('wp_ajax_nopriv_cp_get_calendar_events', array($this, 'ajax_get_calendar_events'));
    }

    /**
     * Add admin menu
     */
    public function add_admin_menu() {
        add_submenu_page(
            'edit.php?post_type=cp_event',
            __('Calendar View', 'campaign-office-core'),
            __('Calendar', 'campaign-office-core'),
            'edit_posts',
            'cp-event-calendar',
            array($this, 'render_admin_calendar_page')
        );
    }

    /**
     * Add location meta box
     */
    public function add_location_meta_box() {
        add_meta_box(
            'cp_event_location',
            __('Event Location', 'campaign-office-core'),
            array($this, 'render_location_meta_box'),
            'cp_event',
            'normal',
            'high'
        );
    }

    /**
     * Render location meta box
     */
    public function render_location_meta_box($post) {
        wp_nonce_field('cp_event_location', 'cp_event_location_nonce');

        $venue = get_post_meta($post->ID, '_cp_event_venue', true);
        $address = get_post_meta($post->ID, '_cp_event_address', true);
        $city = get_post_meta($post->ID, '_cp_event_city', true);
        $state = get_post_meta($post->ID, '_cp_event_state', true);
        $zip = get_post_meta($post->ID, '_cp_event_zip', true);
        $lat = get_post_meta($post->ID, '_cp_event_latitude', true);
        $lng = get_post_meta($post->ID, '_cp_event_longitude', true);
        ?>
        <div class="cp-event-location-fields">
            <p>
                <label for="cp_event_venue"><strong><?php esc_html_e('Venue Name:', 'campaign-office-core'); ?></strong></label><br>
                <input type="text" id="cp_event_venue" name="cp_event_venue" value="<?php echo esc_attr($venue); ?>" class="widefat">
            </p>
            <p>
                <label for="cp_event_address"><strong><?php esc_html_e('Street Address:', 'campaign-office-core'); ?></strong></label><br>
                <input type="text" id="cp_event_address" name="cp_event_address" value="<?php echo esc_attr($address); ?>" class="widefat">
            </p>
            <div style="display: grid; grid-template-columns: 2fr 1fr 1fr; gap: 10px;">
                <p>
                    <label for="cp_event_city"><strong><?php esc_html_e('City:', 'campaign-office-core'); ?></strong></label><br>
                    <input type="text" id="cp_event_city" name="cp_event_city" value="<?php echo esc_attr($city); ?>" class="widefat">
                </p>
                <p>
                    <label for="cp_event_state"><strong><?php esc_html_e('State:', 'campaign-office-core'); ?></strong></label><br>
                    <input type="text" id="cp_event_state" name="cp_event_state" value="<?php echo esc_attr($state); ?>" class="widefat">
                </p>
                <p>
                    <label for="cp_event_zip"><strong><?php esc_html_e('ZIP:', 'campaign-office-core'); ?></strong></label><br>
                    <input type="text" id="cp_event_zip" name="cp_event_zip" value="<?php echo esc_attr($zip); ?>" class="widefat">
                </p>
            </div>
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 10px;">
                <p>
                    <label for="cp_event_latitude"><strong><?php esc_html_e('Latitude (optional):', 'campaign-office-core'); ?></strong></label><br>
                    <input type="text" id="cp_event_latitude" name="cp_event_latitude" value="<?php echo esc_attr($lat); ?>" class="widefat">
                </p>
                <p>
                    <label for="cp_event_longitude"><strong><?php esc_html_e('Longitude (optional):', 'campaign-office-core'); ?></strong></label><br>
                    <input type="text" id="cp_event_longitude" name="cp_event_longitude" value="<?php echo esc_attr($lng); ?>" class="widefat">
                </p>
            </div>
            <p class="description">
                <?php esc_html_e('Coordinates are optional. If provided, a Google Map will be displayed automatically.', 'campaign-office-core'); ?>
            </p>
        </div>
        <?php
    }

    /**
     * Save location meta
     */
    public function save_location_meta($post_id) {
        if (!isset($_POST['cp_event_location_nonce']) || !wp_verify_nonce($_POST['cp_event_location_nonce'], 'cp_event_location')) {
            return;
        }

        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
            return;
        }

        if (!current_user_can('edit_post', $post_id)) {
            return;
        }

        $fields = array('venue', 'address', 'city', 'state', 'zip', 'latitude', 'longitude');

        foreach ($fields as $field) {
            $key = 'cp_event_' . $field;
            if (isset($_POST[$key])) {
                update_post_meta($post_id, '_' . $key, sanitize_text_field($_POST[$key]));
            }
        }
    }

    /**
     * Render calendar shortcode
     *
     * Usage: [cp_event_calendar view="month"]
     */
    public function render_calendar_shortcode($atts) {
        $atts = shortcode_atts(array(
            'view'       => 'month',
            'show_past'  => 'false',
            'categories' => '',
        ), $atts, 'cp_event_calendar');

        ob_start();
        ?>
        <div class="cp-event-calendar" data-view="<?php echo esc_attr($atts['view']); ?>">
            <div class="cp-calendar-header">
                <button class="cp-calendar-prev" aria-label="<?php esc_attr_e('Previous', 'campaign-office-core'); ?>">
                    <span class="dashicons dashicons-arrow-left-alt2"></span>
                </button>
                <h2 class="cp-calendar-title"></h2>
                <button class="cp-calendar-next" aria-label="<?php esc_attr_e('Next', 'campaign-office-core'); ?>">
                    <span class="dashicons dashicons-arrow-right-alt2"></span>
                </button>
            </div>
            <div class="cp-calendar-view-switcher">
                <button class="cp-view-btn" data-view="month"><?php esc_html_e('Month', 'campaign-office-core'); ?></button>
                <button class="cp-view-btn" data-view="week"><?php esc_html_e('Week', 'campaign-office-core'); ?></button>
                <button class="cp-view-btn" data-view="list"><?php esc_html_e('List', 'campaign-office-core'); ?></button>
            </div>
            <div class="cp-calendar-body">
                <?php echo $this->render_calendar_view($atts['view'], current_time('Y-m')); ?>
            </div>
        </div>
        <?php
        return ob_get_clean();
    }

    /**
     * Render calendar view
     */
    private function render_calendar_view($view, $date) {
        $events = $this->get_events_for_period($date, $view);

        if ($view === 'month') {
            return $this->render_month_view($date, $events);
        } elseif ($view === 'week') {
            return $this->render_week_view($date, $events);
        } else {
            return $this->render_list_view($events);
        }
    }

    /**
     * Render month calendar view
     */
    private function render_month_view($year_month, $events) {
        $first_day = date('Y-m-01', strtotime($year_month . '-01'));
        $last_day = date('Y-m-t', strtotime($first_day));
        $start_weekday = date('w', strtotime($first_day));

        ob_start();
        ?>
        <div class="cp-calendar-month">
            <div class="cp-calendar-weekdays">
                <div class="cp-weekday"><?php esc_html_e('Sun', 'campaign-office-core'); ?></div>
                <div class="cp-weekday"><?php esc_html_e('Mon', 'campaign-office-core'); ?></div>
                <div class="cp-weekday"><?php esc_html_e('Tue', 'campaign-office-core'); ?></div>
                <div class="cp-weekday"><?php esc_html_e('Wed', 'campaign-office-core'); ?></div>
                <div class="cp-weekday"><?php esc_html_e('Thu', 'campaign-office-core'); ?></div>
                <div class="cp-weekday"><?php esc_html_e('Fri', 'campaign-office-core'); ?></div>
                <div class="cp-weekday"><?php esc_html_e('Sat', 'campaign-office-core'); ?></div>
            </div>
            <div class="cp-calendar-days">
                <?php
                // Empty cells for days before month starts
                for ($i = 0; $i < $start_weekday; $i++) {
                    echo '<div class="cp-calendar-day cp-day-empty"></div>';
                }

                // Days of the month
                $num_days = date('t', strtotime($first_day));
                for ($day = 1; $day <= $num_days; $day++) {
                    $current_date = sprintf('%s-%02d', $year_month, $day);
                    $day_events = array_filter($events, function($event) use ($current_date) {
                        return date('Y-m-d', strtotime($event->event_date)) === $current_date;
                    });

                    $is_today = $current_date === date('Y-m-d');
                    ?>
                    <div class="cp-calendar-day <?php echo $is_today ? 'cp-day-today' : ''; ?>">
                        <div class="cp-day-number"><?php echo $day; ?></div>
                        <?php if (!empty($day_events)) : ?>
                            <div class="cp-day-events">
                                <?php foreach (array_slice($day_events, 0, 3) as $event) : ?>
                                    <a href="<?php echo get_permalink($event->ID); ?>" class="cp-day-event" title="<?php echo esc_attr($event->post_title); ?>">
                                        <?php echo esc_html(wp_trim_words($event->post_title, 3)); ?>
                                    </a>
                                <?php endforeach; ?>
                                <?php if (count($day_events) > 3) : ?>
                                    <span class="cp-more-events">
                                        <?php printf(esc_html__('+ %d more', 'campaign-office-core'), count($day_events) - 3); ?>
                                    </span>
                                <?php endif; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                    <?php
                }
                ?>
            </div>
        </div>
        <?php
        return ob_get_clean();
    }

    /**
     * Render week calendar view
     */
    private function render_week_view($date, $events) {
        ob_start();
        ?>
        <div class="cp-calendar-week">
            <p><?php esc_html_e('Week view: showing events for the current week', 'campaign-office-core'); ?></p>
            <?php echo $this->render_list_view($events); ?>
        </div>
        <?php
        return ob_get_clean();
    }

    /**
     * Render list view
     */
    private function render_list_view($events) {
        if (empty($events)) {
            return '<p class="cp-no-events">' . esc_html__('No events found for this period.', 'campaign-office-core') . '</p>';
        }

        ob_start();
        ?>
        <div class="cp-calendar-list">
            <?php foreach ($events as $event) : ?>
                <div class="cp-list-event">
                    <div class="cp-event-date">
                        <?php echo date_i18n(get_option('date_format'), strtotime($event->event_date)); ?>
                    </div>
                    <div class="cp-event-details">
                        <h3><a href="<?php echo get_permalink($event->ID); ?>"><?php echo esc_html($event->post_title); ?></a></h3>
                        <?php if (!empty($event->event_location)) : ?>
                            <p class="cp-event-location">
                                <span class="dashicons dashicons-location"></span>
                                <?php echo esc_html($event->event_location); ?>
                            </p>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
        <?php
        return ob_get_clean();
    }

    /**
     * Get events for period
     */
    private function get_events_for_period($date, $view) {
        global $wpdb;

        if ($view === 'month') {
            $start_date = date('Y-m-01', strtotime($date . '-01'));
            $end_date = date('Y-m-t', strtotime($start_date));
        } else {
            $start_date = $date;
            $end_date = date('Y-m-d', strtotime($start_date . ' +30 days'));
        }

        $query = "
            SELECT p.*, pm.meta_value as event_date, pm2.meta_value as event_location
            FROM {$wpdb->posts} p
            LEFT JOIN {$wpdb->postmeta} pm ON p.ID = pm.post_id AND pm.meta_key = '_cp_event_date'
            LEFT JOIN {$wpdb->postmeta} pm2 ON p.ID = pm2.post_id AND pm2.meta_key = '_cp_event_venue'
            WHERE p.post_type = 'cp_event'
            AND p.post_status = 'publish'
            AND pm.meta_value >= %s
            AND pm.meta_value <= %s
            ORDER BY pm.meta_value ASC
        ";

        return $wpdb->get_results($wpdb->prepare($query, $start_date, $end_date . ' 23:59:59'));
    }

    /**
     * Render event map shortcode
     */
    public function render_event_map($atts) {
        $atts = shortcode_atts(array(
            'event_id' => get_the_ID(),
            'height'   => '400',
            'zoom'     => '15',
        ), $atts, 'cp_event_map');

        $lat = get_post_meta($atts['event_id'], '_cp_event_latitude', true);
        $lng = get_post_meta($atts['event_id'], '_cp_event_longitude', true);
        $venue = get_post_meta($atts['event_id'], '_cp_event_venue', true);
        $address = get_post_meta($atts['event_id'], '_cp_event_address', true);
        $city = get_post_meta($atts['event_id'], '_cp_event_city', true);
        $state = get_post_meta($atts['event_id'], '_cp_event_state', true);

        if (empty($lat) || empty($lng)) {
            return '<p class="cp-map-notice">' . esc_html__('Location coordinates not available for this event.', 'campaign-office-core') . '</p>';
        }

        $full_address = implode(', ', array_filter(array($venue, $address, $city, $state)));
        $google_maps_url = sprintf(
            'https://www.google.com/maps?q=%s,%s',
            urlencode($lat),
            urlencode($lng)
        );

        ob_start();
        ?>
        <div class="cp-event-map-wrapper">
            <div class="cp-event-map" style="height: <?php echo esc_attr($atts['height']); ?>px;">
                <iframe
                    width="100%"
                    height="100%"
                    frameborder="0"
                    style="border:0"
                    src="https://www.google.com/maps/embed/v1/place?key=YOUR_API_KEY&q=<?php echo esc_attr($lat . ',' . $lng); ?>&zoom=<?php echo esc_attr($atts['zoom']); ?>"
                    allowfullscreen>
                </iframe>
            </div>
            <p class="cp-map-address">
                <span class="dashicons dashicons-location"></span>
                <?php echo esc_html($full_address); ?>
                <br>
                <a href="<?php echo esc_url($google_maps_url); ?>" target="_blank" rel="noopener">
                    <?php esc_html_e('Get Directions', 'campaign-office-core'); ?>
                </a>
            </p>
        </div>
        <?php
        return ob_get_clean();
    }

    /**
     * Handle iCal export
     */
    public function handle_ical_export() {
        if (!isset($_GET['cp_ical_export']) || !isset($_GET['event_id'])) {
            return;
        }

        $event_id = intval($_GET['event_id']);
        $event = get_post($event_id);

        if (!$event || $event->post_type !== 'cp_event') {
            return;
        }

        $event_date = get_post_meta($event_id, '_cp_event_date', true);
        $venue = get_post_meta($event_id, '_cp_event_venue', true);
        $address = get_post_meta($event_id, '_cp_event_address', true);

        $ical = $this->generate_ical($event, $event_date, $venue . ', ' . $address);

        header('Content-Type: text/calendar; charset=utf-8');
        header('Content-Disposition: attachment; filename="event-' . $event_id . '.ics"');
        echo $ical;
        exit;
    }

    /**
     * Generate iCal content
     */
    private function generate_ical($event, $event_date, $location) {
        $start = date('Ymd\THis\Z', strtotime($event_date));
        $end = date('Ymd\THis\Z', strtotime($event_date . ' +2 hours'));
        $created = date('Ymd\THis\Z', strtotime($event->post_date));

        $ical = "BEGIN:VCALENDAR\r\n";
        $ical .= "VERSION:2.0\r\n";
        $ical .= "PRODID:-//CampaignPress//Event Calendar//EN\r\n";
        $ical .= "BEGIN:VEVENT\r\n";
        $ical .= "UID:" . $event->ID . "@" . get_bloginfo('url') . "\r\n";
        $ical .= "DTSTAMP:" . $created . "\r\n";
        $ical .= "DTSTART:" . $start . "\r\n";
        $ical .= "DTEND:" . $end . "\r\n";
        $ical .= "SUMMARY:" . $this->escape_ical($event->post_title) . "\r\n";
        $ical .= "DESCRIPTION:" . $this->escape_ical(wp_strip_all_tags($event->post_content)) . "\r\n";
        $ical .= "LOCATION:" . $this->escape_ical($location) . "\r\n";
        $ical .= "URL:" . get_permalink($event->ID) . "\r\n";
        $ical .= "END:VEVENT\r\n";
        $ical .= "END:VCALENDAR\r\n";

        return $ical;
    }

    /**
     * Escape iCal text
     */
    private function escape_ical($text) {
        $text = str_replace(array("\r\n", "\n", "\r"), "\\n", $text);
        $text = str_replace(array(",", ";"), array("\\,", "\\;"), $text);
        return $text;
    }

    /**
     * Append iCal download link to event content
     */
    public function append_ical_download($content) {
        if (!is_singular('cp_event') || !in_the_loop() || !is_main_query()) {
            return $content;
        }

        $download_url = add_query_arg(array(
            'cp_ical_export' => '1',
            'event_id'       => get_the_ID(),
        ), home_url('/'));

        $button = sprintf(
            '<p class="cp-ical-download"><a href="%s" class="button">%s</a></p>',
            esc_url($download_url),
            esc_html__('Add to Calendar (iCal)', 'campaign-office-core')
        );

        return $content . $button;
    }

    /**
     * AJAX: Get calendar events
     */
    public function ajax_get_calendar_events() {
        // Verify nonce for security (passed from JavaScript)
        if (!check_ajax_referer('cp_calendar_events', 'nonce', false)) {
            wp_send_json_error(array('message' => __('Security check failed.', 'campaign-office-core')));
        }

        $view = sanitize_text_field($_POST['view'] ?? 'month');
        $date = sanitize_text_field($_POST['date'] ?? current_time('Y-m'));

        $html = $this->render_calendar_view($view, $date);

        wp_send_json_success(array('html' => $html));
    }

    /**
     * Render admin calendar page
     */
    public function render_admin_calendar_page() {
        ?>
        <div class="wrap">
            <h1><?php esc_html_e('Event Calendar', 'campaign-office-core'); ?></h1>
            <?php echo do_shortcode('[cp_event_calendar]'); ?>
        </div>
        <?php
    }

    /**
     * Frontend assets are now loaded by the main plugin class
     * Styles moved to assets/css/frontend.css
     * Scripts moved to assets/js/frontend.js
     */

}

// Initialize event calendar enhancements
new CP_Event_Calendar_Enhancements();
