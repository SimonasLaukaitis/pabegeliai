<?php
/*
Plugin Name: Webrom Events Calendar
Description: Custom plugin to create and display events
Author: Webrom
Version: 1.0
Author URI: http://webrom.lt
 */

if (!defined('ABSPATH')) {
    exit;
}

/** Add CSS styles */
function wec_register_styles()
{
    // Main plugin styles
    wp_enqueue_style(
        'wec-main',
        plugin_dir_url(__FILE__) . 'assets/css/styles.css',
        array(),
        '1.1',
        'all'
    );

    // Plugin template styles
    wp_enqueue_style(
        'wec-template',
        plugin_dir_url(__FILE__) . 'assets/css/all-events.css',
        array(),
        '1.1',
        'all'
    );

    // All events styles
    wp_enqueue_style(
        'wec-all-events',
        plugin_dir_url(__FILE__) . 'assets/css/template.css',
        array(),
        '1.1',
        'all'
    );

    // Custom event template for single post styles
    wp_enqueue_style(
        'wec-single-template',
        get_template_directory_uri() . '/assets/css/custom-event-template.css',
        array(),
        '1.1',
        'all'
    );

    
}
add_action('wp_enqueue_scripts', 'wec_register_styles');

/** Add JS files */
function wec_register_scripts()
{
    // Home scripts
    wp_enqueue_script(
        'wec-main',
        plugin_dir_url(__FILE__) . 'assets/js/main.js',
        array('jquery'),
        '1.1',
        true
    );

    wp_localize_script(
        'wec-main',
        'ajax_object',
        array(
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('main_nonce'),
            // 'Message_sent' => __('Žinutė išsiųsta', "webrom-theme"),
        )
    );
}
add_action('wp_enqueue_scripts', 'wec_register_scripts');

/** Add post type */
function webrom_event_calendar_register_post_type()
{
    $args = array(
        'label' => 'Event calendar',
        'public' => true,
        'supports' => array('title', 'editor', 'thumbnail'), // Added 'thumbnail' support
        'thumbnail_size' => 'full', // Set the desired image size to 'full'
        'show_in_rest' => true,
        'menu_icon' => 'dashicons-format-status',
        'hierarchical' => false,
        'rewrite' => array('slug' => 'event-calendar'),
        'exclude_from_search' => true,
    );
    register_post_type('webrom_events', $args);
}
add_action('init', 'webrom_event_calendar_register_post_type');

/** Add custom meta fields for event date, location, price, and featured image */
function webrom_event_calendar_add_meta_fields()
{

    add_meta_box('webrom_event_date', 'Event Date', 'webrom_event_calendar_render_date_field', 'webrom_events', 'normal', 'high');
    add_meta_box('webrom_event_location', 'Event Location', 'webrom_event_calendar_render_location_field', 'webrom_events', 'normal', 'high');
    add_meta_box('webrom_event_registration_link', 'Event time', 'webrom_registration_link_field', 'webrom_events', 'normal', 'high');
}
add_action('add_meta_boxes', 'webrom_event_calendar_add_meta_fields');

/** Render event date meta field */
function webrom_event_calendar_render_date_field($post)
{
    $event_date = get_post_meta($post->ID, 'webrom_event_date', true);

    $today = date('Y-m-d');

    // If the event date is not set, use today's date as the default
    if (empty($event_date)) {
        $event_date = $today;
    }
?>
    <input type="date" name="webrom_event_date" id="webrom_event_date" value="<?php echo esc_attr($event_date); ?>" required>
<?php
}

/** Render event location meta field */
function webrom_event_calendar_render_location_field($post)
{
    $event_location = get_post_meta($post->ID, 'webrom_event_location', true);
?>
    <input type="text" name="webrom_event_location" id="webrom_event_location" value="<?php echo esc_attr($event_location); ?>" required>
<?php
}

/** Render event registration link field */
function webrom_registration_link_field($post)
{
    $event_link = get_post_meta($post->ID, 'webrom_event_registration_link', true);
?>
    <input type="text" name="webrom_event_registration_link" id="webrom_event_registration_link" placeholder="00:00" value="<?php echo esc_attr($event_link); ?>" required>
<?php
}

/** Save meta field values */
function webrom_event_calendar_save_meta_fields($post_id)
{
    if (isset($_POST['webrom_event_date'])) {
        update_post_meta($post_id, 'webrom_event_date', sanitize_text_field($_POST['webrom_event_date']));
    }

    if (isset($_POST['webrom_event_location'])) {
        update_post_meta($post_id, 'webrom_event_location', sanitize_text_field($_POST['webrom_event_location']));
    }

    if (isset($_POST['webrom_event_registration_link'])) {
        update_post_meta($post_id, 'webrom_event_registration_link', sanitize_text_field($_POST['webrom_event_registration_link']));
    }
}
add_action('save_post', 'webrom_event_calendar_save_meta_fields');

/** Modify post content display on events page */
function webrom_event_calendar_modify_content($content)
{
    if (is_singular('webrom_events')) {
        $content = '<div class="main_class">' . $content . '</div>';
    }
    return $content;
}
add_filter('the_content', 'webrom_event_calendar_modify_content');

/** support for thumbnails */
add_theme_support('post-thumbnails');

/** Render calendar */

function renderCalendar($ajax_month, $ajax_year)
{
    $args = array(
        'post_type' => 'webrom_events',
        'posts_per_page' => -1, // Retrieve all posts
    );

    $query = new WP_Query($args);

    $event_dates = array(); // Array to store event dates

    if ($query->have_posts()) {
        while ($query->have_posts()) {
            $query->the_post();
            if (get_post_status() !== 'draft') {

                $event_date = get_post_meta(get_the_ID(), 'webrom_event_date', true);
                $event_dates[date('Y-m-d', strtotime($event_date))] = date('j', strtotime($event_date)); // Store the day of the event in the array
            }
        }
        wp_reset_postdata();
    }

    $current_date = date('Y-m-d');

    // Get the year and month from the current date
    $year = date('Y', strtotime($current_date));
    $month = date('m', strtotime($current_date));

    if ($ajax_month !== '') {
        $month = $ajax_month;
    }

    if ($ajax_year !== '') {
        $year = $ajax_year;
    }

    // Get the number of days in the current month
    $days_in_month = cal_days_in_month(CAL_GREGORIAN, $month, $year);

    // Get the first day of the month
    $first_day = date('N', strtotime($year . '-' . $month . '-01'));

    // Define an array of month names
    $month_names = array(
        __('Sausis', 'webrom-theme'),
        __('Vasaris', 'webrom-theme'),
        __('Kovas', 'webrom-theme'),
        __('Balandis', 'webrom-theme'),
        __('Gegužė', 'webrom-theme'),
        __('Birželis', 'webrom-theme'),
        __('Liepa', 'webrom-theme'),
        __('Rugpjūtis', 'webrom-theme'),
        __('Rugsėjis', 'webrom-theme'),
        __('Spalis', 'webrom-theme'),
        __('Lapkritis', 'webrom-theme'),
        __('Gruodis', 'webrom-theme'),
    );

    $html = "";

    // Display the dropdown menu for month and year
    $html .= '<div id="calendar-dropdowns">';

    $html .=  '<div aria-label="pasirinkti metus" class="calendar-year button3" id="calendar-year">';
    $html .=  $year;
    $html .=  '</div>';

    // TODO: month selector

    $html .=  '<div class="month-selector">';

    $html .=  '<button id="month-btn-left"><img src="/wp-content/plugins/webrom-events-calendar/assets/icons/calendar-arr-l.svg"></button>';

    $html .=  '<div aria-label="pasirinkti mėnesį" class="calendar-month button3" id="calendar-month">';

    // Generate month options
    for ($i = 1; $i <= 12; $i++) {

        if ($i == $month) {
            $html .=  '<div class="current-month"  value="' . $i . '">' . $month_names[$i - 1] . '</div>';
        } else {
            $html .=  '<div class="month-hide"  value="' . $i . '">' . $month_names[$i - 1] . '</div>';
        }
    }

    $html .=  '</div>';

    $html .=  '<button id="month-btn-right"><img src="/wp-content/plugins/webrom-events-calendar/assets/icons/calendar-arr-r.svg"></button>';
    $html .=  '</div>';

    $html .=  '</div>';

    $html .=  '<div id="table-plugin" class="table-div">';

    $date_posts = $month_names[$month - 1];

    $html .=  '<table id="teble-plugin-table" class="calendar-table button3">';
    $html .=  '<tr>';
    $html .=  '<th><div class="calendar-cell-th button3">' . __('Pr', 'webrom-theme') . '.</div></th>';
    $html .=  '<th><div class="calendar-cell-th button3">' . __('An', 'webrom-theme') . '.</div></th>';
    $html .=  '<th><div class="calendar-cell-th button3">' . __('Tr', 'webrom-theme') . '.</div></th>';
    $html .=  '<th><div class="calendar-cell-th button3">' . __('Kt', 'webrom-theme') . '.</div></th>';
    $html .=  '<th><div class="calendar-cell-th button3">' . __('Pn', 'webrom-theme') . '.</div></th>';
    $html .=  '<th><div class="calendar-cell-th button3">' . __('Št', 'webrom-theme') . '.</div></th>';
    $html .=  '<th><div class="calendar-cell-th button3">' . __('Sk', 'webrom-theme') . '.</div></th>';
    $html .=  '</tr>';

    // Start the first week
    $html .=  '<tr>';

    // Display blank cells for the days before the first day of the month
    for ($i = 1; $i < $first_day; $i++) {
        $html .=  '<td class="empty-cell"><div class="calendar-cell-empty-cell"><div></td>';
    }

    // Display the days of the month
    $day = 1;
    for ($i = $first_day; $i <= 7; $i++) {
        $class = ($day == date('j') && $month == date('m') && $year == date('Y')) ? 'current-day' : '';

        // Check if the current day is in the array of event dates
        $event_key = $year . '-' . sprintf('%02d', $month) . '-' . sprintf('%02d', $day);
        if (array_key_exists($event_key, $event_dates)) {
            $class .= ' event-day'; // Add CSS class for event day
            $html .=  '<td class="' . $class . ' button3"><div class="calendar-cell-current-day button3"><button class="event-day-btn button3"  data-item-key="' . $event_key . '"  aria-label="day ' . $day . '" >' . $day . '</button></div></td>';
        } else {
            $html .=  '<td class="' . $class . '"><div class="calendar-cell">' . $day . '</div></td>';
        }
        $day++;
    }

    $html .=  '</tr>';

    // Display the remaining days of the month
    while ($day <= $days_in_month) {
        $html .=  '<tr>';
        for ($i = 1; $i <= 7 && $day <= $days_in_month; $i++) {
            $class = ($day == date('j') && $month == date('m') && $year == date('Y')) ? 'current-day' : '';

            // Check if the current day is in the array of event dates
            $event_key = $year . '-' . sprintf('%02d', $month) . '-' . sprintf('%02d', $day);
            if (array_key_exists($event_key, $event_dates)) {
                $html .=  '<td class="event-day button3"><div class="calendar-cell-event-day button3"><button class="event-day-btn button3" id="" data-item-key="' . $event_key . '"  aria-label="day ' . $day . '" >' . $day . '</button></div></td>';
            } else {
                $html .=  '<td ><div class="calendar-cell ' . $class . '">' . $day . '</div></td>';
            }

            $day++;
        }
        $html .=  '</tr>';
    }

    $html .=  '</table>';
    $html .=  '</div>';

    return $html;
}

/** Show calendar in main page */
function showCalendar()
{
    return renderCalendar('', '');
}


/** Calendar html for AJAX */
function showCalendar_ajax()
{
    check_ajax_referer('main_nonce', 'security');
    if (isset($_POST['calendarMonth'])) {
        $month = sanitize_text_field($_POST['calendarMonth']);
        $year = sanitize_text_field($_POST['calendarYear']);

        // TODO: echo left
        echo renderCalendar($month, $year);
    }
    wp_die();
}

add_action('wp_ajax_showCalendar_ajax', 'showCalendar_ajax');
add_action('wp_ajax_nopriv_showCalendar_ajax', 'showCalendar_ajax');

// Show posts in main page
function showPosts()
{
    return renderPosts('');
}

/** Posts html for AJAX */
function showPost_ajax()
{
    check_ajax_referer('main_nonce', 'security');

    if (isset($_POST['eventDate'])) {
        $date = sanitize_text_field($_POST['eventDate']);
        // TODO: echo left
        echo renderPosts($date);
    }
    wp_die();
}

add_action('wp_ajax_showPost_ajax', 'showPost_ajax');
add_action('wp_ajax_nopriv_showPost_ajax', 'showPost_ajax');

/** Render posts meta field */
function renderPosts($ajax_date = '')
{
    $month_names_posts = array(
        __('Sausio', 'webrom-theme'),
        __('Vasario', 'webrom-theme'),
        __('Kovo', 'webrom-theme'),
        __('Balandžio', 'webrom-theme'),
        __('Gegužės', 'webrom-theme'),
        __('Birželio', 'webrom-theme'),
        __('Liepos', 'webrom-theme'),
        __('Rugpjūčio', 'webrom-theme'),
        __('Rugsėjo', 'webrom-theme'),
        __('Spalio', 'webrom-theme'),
        __('Lapkričio', 'webrom-theme'),
        __('Gruodžio', 'webrom-theme'),
    );

    $weekday_names_posts = array(
        __('Pirmadienis', 'webrom-theme'),
        __('Antradienis', 'webrom-theme'),
        __('Trečiadienis', 'webrom-theme'),
        __('Ketvirtadienis', 'webrom-theme'),
        __('Penktadienis', 'webrom-theme'),
        __('Šeštadienis', 'webrom-theme'),
        __('Sekmadienis', 'webrom-theme'),
    );

    // event date
    $dateObj = new DateTime($ajax_date);
    $month = $dateObj->format('n');
    $day = $dateObj->format('j');
    $dayOfWeek = $dateObj->format('N');

    // curent date
    $dateObj_current = new DateTime();
    $month_current = $dateObj_current->format('n');
    $day_current = $dateObj_current->format('j');
    $dayOfWeek_current = $dateObj_current->format('N');

    //How much posts will be dispayed
    $displayPostCount = 6;

    //show events aligned by day if no events today
    $today = date('Y-m-d');

    $args = array(
        'post_type' => 'webrom_events',
        'posts_per_page' => $displayPostCount,
        'meta_query' => array(
            array(
                'key' => 'webrom_event_date',
                'value' => $today,
                'compare' => '>=', // Show posts with the 'webrom_event_date' greater than or equal to today
                'type' => 'DATE',
            ),
        ),
        'orderby' => 'meta_value', // Order by the value of the meta field (date)
        'meta_key' => 'webrom_event_date', // Use the 'webrom_event_date' meta field for ordering
        'order' => 'ASC', // Order in ascending order (earliest date first)
    );

    //Check if there is events today
    $args_today = array(
        'post_type' => 'webrom_events',
        'posts_per_page' => $displayPostCount,
        'meta_query' => array(
            array(
                'key' => 'webrom_event_date',
                'value' => $today,
                'compare' => '=', // Show posts with the 'webrom_event_date' equal to today
                'type' => 'DATE',
            ),
        ),
    );
    $events_today_query = new WP_Query($args_today);

    //Checking if there are upcoming events
    $args_upcoming = array(
        'post_type' => 'webrom_events',
        'posts_per_page' => $displayPostCount,
        'meta_query' => array(
            array(
                'key' => 'webrom_event_date',
                'value' => $today,
                'compare' => '>=',
                'type' => 'DATE',
            ),
        ),
    );

    $events_upcoming_query = new WP_Query($args_upcoming);

    $html = "";

    // Show day of events
    if ($ajax_date != '') {
        //Event date
        $html .= '<div class="posts-header" id="posts-header-id"><span class="subtitle1" >' . $month_names_posts[$month - 1] . ' ' . $day . '&nbspd.&nbsp<span class="week-day subtitle2">' . $weekday_names_posts[$dayOfWeek - 1] . '</span></div>';
    } else if ($events_today_query->found_posts > 0) {
        //current date
        $html .=  '<div class="posts-header"><span class="subtitle1" >' . $month_names_posts[$month_current - 1] . ' ' . $day_current . '&nbspd.&nbsp<span class="week-day subtitle2">' . $weekday_names_posts[$dayOfWeek_current - 1] . '</span></div>';
    } else if (!$events_upcoming_query->have_posts()) {
        //Checking if there are upcoming events, if not show past events
        $html .=  '<div class="posts-header subtitle1">' . __('RENGINIAI', 'webrom-theme') . '</div>';
    } else {
        // If no events
        $html .=  '<div class="posts-header subtitle1">' . __('RENGINIAI', 'webrom-theme') . '</div>';
    }

    //If there are events today - display them
    if ($events_today_query->found_posts > 0) {
        $args = array(
            'post_type' => 'webrom_events',
            'posts_per_page' => $displayPostCount,
            'meta_query' => array(
                array(
                    'key' => 'webrom_event_date',
                    'value' => $today,
                    'compare' => '=',
                    'type' => 'DATE',
                ),
            ),
            'orderby' => 'meta_value',
            'meta_key' => 'webrom_event_date',
            'order' => 'ASC', // Order in ascending order (earliest date first)
        );
    } else if (!$events_upcoming_query->have_posts()) {
        $args = array(
            'post_type' => 'webrom_events',
            'posts_per_page' => $displayPostCount, // Display posts
            'meta_query' => array(
                array(
                    'key' => 'webrom_event_date',
                    'value' => $today,
                    'compare' => '<=',
                    'type' => 'DATE',
                ),
            ),
            'orderby' => 'meta_value',
            'meta_key' => 'webrom_event_date',
            'order' => 'DSC', // Order in descending order
        );
    }
    if ($ajax_date !== '') {
        //Show specific date events if calendar day is clicked
        $args = array(
            'post_type' => 'webrom_events',
            'posts_per_page' => $displayPostCount,
            'meta_query' => array(
                array(
                    'key' => 'webrom_event_date',
                    'value' => $today,
                    'compare' => '>=',
                    'type' => 'DATE',
                ),
            ),
            'orderby' => 'meta_value',
            'meta_key' => 'webrom_event_date',
            'order' => 'ASC', // Order in ascending order (earliest date first)
        );
        $args['meta_query'] = array(
            array(
                'key' => 'webrom_event_date',
                'value' => date('Y-m-d', strtotime($ajax_date)),
                'compare' => '=',
                'type' => 'DATE',
            ),
        );
    }


    $html .= '<div class="events-calendar-posts" id="events-calendar-posts">';

    $query = new WP_Query($args);

    // variable for post id number
    $post_number = 0;



    if ($query->have_posts()) {
        while ($query->have_posts()) {
            $query->the_post();

            $post_number++;
            $event_date = get_post_meta(get_the_ID(), 'webrom_event_date', true);
            // Extract the day
            $event_day = date('d', strtotime($event_date));

            // Extract the month
            $event_month = date_i18n('F', strtotime($event_date));

            $event_time = get_post_meta(get_the_ID(), 'webrom_event_registration_link', true);
            $event_dates[] = date('j', strtotime($event_date));
            $event_location = get_post_meta(get_the_ID(), 'webrom_event_location', true);

            $image_url = '';

            $html .= '<div class="event-box post-' . $post_number . '" id="post-' . $post_number . '">';

            if (has_post_thumbnail()) {
                $thumbnail_url = wp_get_attachment_image_src(get_post_thumbnail_id(), 'full');
                $image_url = $thumbnail_url[0];
            }

            //Left section
            $html .= '<div class="featured-image">';
            //Desktop image
            if ($image_url != "") {
                $html .= '<img src="' . $image_url . '" alt="' . get_the_title() . '">';
            }

            $html .= '</div>';

            //Middle section
            $html .= '<div class="middle-section">';

            //Event title
            $html .= '<div  class="events-header subtitle3"><a href="' . get_permalink() . '" aria-label="' . get_the_title() . '">' . get_the_title() . '</a></div>';

            $html .= '<div class="events-time-location">';
            //Event time
            $html .= '<div class="events-time-cont">';
            $html .= '<div class="events-time-icon"><img src="/wp-content/plugins/webrom-events-calendar/assets/icons/clock.png"></div>';
            $html .= '<div class="caption3">' . $event_time . '</div>';
            $html .= '</div>';



            // Check if location exists
            if ($event_location !== '') {

                // Event location
                $html .= '<div class="events-location">';
                $html .= '<div class="events-location-icon"><img src="/wp-content/plugins/webrom-events-calendar/assets/icons/location.png"></div>';
                $html .= '<div class="caption3">' . $event_location . '</div>';
                $html .= '</div>';
            }
            $html .= '</div>';
            $html .= '</div>';

            //Right section

            $html .= '<div class="events-date">';
            $html .= '<div class="caption3">' . $event_day . '</div>';
            $html .= '<div class="caption3">' . $event_month . '</div>';
            $html .= '</div>';

            //Closing box
            $html .= '</div>';
        }
        wp_reset_postdata();
    } else {
        $html .= '<span class="event-box no-posts">';
        $html .=  '<p>' . __('Šią dieną renginių nėra', 'webrom-theme') . '</p>';
        $html .=  '<p>' . __('Jei turi idėjų ir minčių, kokius renginius galėtume suorganizuoti drauge, kviečiame prisidėti prie Pabėgėlių priėmimo centro renginių įgyvendinimo.', 'webrom-theme') . '</p>';
        $html .= '<p>' . __('Susidomėjus kreiptis', 'webrom-theme') . ' : <a href="tel:+37068497274">+370 684 97274</a> ' . __('arba', 'webrom-theme') . ' <a href="mailto:lina.gedmine@rppc.lt">lina.gedmine@rppc.lt</a></p>';

        $html .= '</span>';
    }

    $html .= '</div>';

    return $html;
}

function showDate()
{
    return render_Date_Only('');
}

/** MOBILE date showing html for AJAX */
function showDate_ajax()
{
    check_ajax_referer('main_nonce', 'security');

    if (isset($_POST['eventDate'])) {
        $date = sanitize_text_field($_POST['eventDate']);

        //TODO: echo left
        echo render_Date_Only($date);
    }
    wp_die();
}

add_action('wp_ajax_showDate_ajax', 'showDate_ajax');
add_action('wp_ajax_nopriv_showDate_ajax', 'showDate_ajax');

/** MOBILE date rener */
function render_Date_Only($ajax_date = '')
{

    $month_names_posts = array(
        __('Sausio', 'webrom-theme'),
        __('Vasario', 'webrom-theme'),
        __('Kovo', 'webrom-theme'),
        __('Balandžio', 'webrom-theme'),
        __('Gegužės', 'webrom-theme'),
        __('Birželio', 'webrom-theme'),
        __('Liepos', 'webrom-theme'),
        __('Rugpjūčio', 'webrom-theme'),
        __('Rugsėjo', 'webrom-theme'),
        __('Spalio', 'webrom-theme'),
        __('Lapkričio', 'webrom-theme'),
        __('Gruodžio', 'webrom-theme'),
    );

    $weekday_names_posts = array(
        __('Pirmadienis', 'webrom-theme'),
        __('Antradienis', 'webrom-theme'),
        __('Trečiadienis', 'webrom-theme'),
        __('Ketvirtadienis', 'webrom-theme'),
        __('Penktadienis', 'webrom-theme'),
        __('Šeštadienis', 'webrom-theme'),
        __('Sekmadienis', 'webrom-theme'),
    );

    // event date
    $dateObj = new DateTime($ajax_date);
    $month = $dateObj->format('n');
    $day = $dateObj->format('j');
    $dayOfWeek = $dateObj->format('N');

    // curent date
    $dateObj_current = new DateTime();
    $month_current = $dateObj_current->format('n');
    $day_current = $dateObj_current->format('j');
    $dayOfWeek_current = $dateObj_current->format('N');

    $html = "";

    // Show day of events
    if ($ajax_date != '') {
        //Event date
        $html .= '<div class="mobile-date-spacing subtitle3" >' . $month_names_posts[$month - 1] . ' ' . $day . '&nbspd.</div><div class="week-day subtitle4">' . $weekday_names_posts[$dayOfWeek - 1] . '</div>';
    } else {
        //current date
        $html .=  '<div class="mobile-date-spacing subtitle3" >' . $month_names_posts[$month_current - 1] . ' ' . $day_current . '&nbspd.</div><div class="week-day subtitle4">' . $weekday_names_posts[$dayOfWeek_current - 1] . '</div>';
    }

    return $html;
}


/** Show all posts */

/** Show posts in main page */
function showAllPosts()
{
    return renderAllPosts('');
}

/** Posts html for AJAX */
function showAllPost_ajax()
{
    check_ajax_referer('main_nonce', 'security');

    if (isset($_POST['eventDate'])) {
        $date = sanitize_text_field($_POST['eventDate']);
        // TODO: left echo
        echo renderAllPosts($date);
    }
    wp_die();
}

add_action('wp_ajax_showAllPost_ajax', 'showAllPost_ajax');
add_action('wp_ajax_nopriv_showAllPost_ajax', 'showAllPost_ajax');

/** Render posts meta field */
function renderAllPosts($ajax_date = '')
{
    $month_names_posts = array(
        __('Sausio', 'webrom-theme'),
        __('Vasario', 'webrom-theme'),
        __('Kovo', 'webrom-theme'),
        __('Balandžio', 'webrom-theme'),
        __('Gegužės', 'webrom-theme'),
        __('Birželio', 'webrom-theme'),
        __('Liepos', 'webrom-theme'),
        __('Rugpjūčio', 'webrom-theme'),
        __('Rugsėjo', 'webrom-theme'),
        __('Spalio', 'webrom-theme'),
        __('Lapkričio', 'webrom-theme'),
        __('Gruodžio', 'webrom-theme'),
    );

    $weekday_names_posts = array(
        __('Pirmadienis', 'webrom-theme'),
        __('Antradienis', 'webrom-theme'),
        __('Trečiadienis', 'webrom-theme'),
        __('Ketvirtadienis', 'webrom-theme'),
        __('Penktadienis', 'webrom-theme'),
        __('Šeštadienis', 'webrom-theme'),
        __('Sekmadienis', 'webrom-theme'),
    );

    // event date
    $dateObj = new DateTime($ajax_date);
    $month = $dateObj->format('n');
    $day = $dateObj->format('j');
    $dayOfWeek = $dateObj->format('N');

    // curent date
    $dateObj_current = new DateTime();
    $month_current = $dateObj_current->format('n');
    $day_current = $dateObj_current->format('j');
    $dayOfWeek_current = $dateObj_current->format('N');
    $today = date('Y-m-d');

    //Checking if there are upcoming events queeey
    $args_upcoming = array(
        'post_type' => 'webrom_events',
        'posts_per_page' => -1,
        'meta_query' => array(
            array(
                'key' => 'webrom_event_date',
                'value' => $today,
                'compare' => '>=',
                'type' => 'DATE',
            ),
        ),
    );

    $events_upcoming_query = new WP_Query($args_upcoming);

    $html = "";

    // Show day of events
    if ($ajax_date != '') {
        //Event date
        $html .= '<div class="posts-header" id="posts-header-id"><span class="subtitle1" >' . $month_names_posts[$month - 1] . ' ' . $day . '&nbspd.&nbsp<span class="week-day subtitle2">' . $weekday_names_posts[$dayOfWeek - 1] . '</span></div>';
    } else if (!$events_upcoming_query->have_posts()) {
        //Checking if there are upcoming events, if not show past events
        $html .= '<div class="posts-header subtitle1">' . __('Praėję renginiai', 'webrom-theme') . '</div>';
    } else {
        //Upcomming events date
        $html .= '<div class="posts-header subtitle1">' . __('Artimiausi renginiai', 'webrom-theme') . '</div>';
    }

    $args = array(
        'post_type' => 'webrom_events',
        'posts_per_page' => -1, // Display  posts
        'meta_query' => array(
            array(
                'key' => 'webrom_event_date',
                'value' => $today,
                'compare' => '>=', // Show posts with the 'webrom_event_date' greater than or equal to today
                'type' => 'DATE',
            ),
        ),
        'orderby' => 'meta_value',
        'meta_key' => 'webrom_event_date',
        'order' => 'ASC', // Order in ascending order (earliest date first)
    );

    if ($ajax_date !== '') {
        $args['meta_query'] = array(
            array(
                'key' => 'webrom_event_date',
                'value' => date('Y-m-d', strtotime($ajax_date)),
                'compare' => '=',
                'type' => 'DATE',
            ),
        );
    } else if (!$events_upcoming_query->have_posts()) {
        //Checking if there are upcoming events, if not show past events
        $args = array(
            'post_type' => 'webrom_events',
            'posts_per_page' => -1,
            'meta_query' => array(
                array(
                    'key' => 'webrom_event_date',
                    'value' => $today,
                    'compare' => '<=',
                    'type' => 'DATE',
                ),
            ),
            'orderby' => 'meta_value',
            'meta_key' => 'webrom_event_date',
            'order' => 'DSC', // Order in descending order
        );
    }

    $html .= '<div class="events-calendar-posts-All main" id="events-calendar-posts-All">';

    $query = new WP_Query($args);

    // variable for post id number
    $post_number = 0;

    if ($query->have_posts()) {
        while ($query->have_posts()) {
            $query->the_post();

            $post_number++;

            $image_url = '';

            if (has_post_thumbnail()) {
                $thumbnail_url = wp_get_attachment_image_src(get_post_thumbnail_id(), 'full');
                $image_url = $thumbnail_url[0];
            }

            $html .= '<div  class="event-box post id="post-' . $post_number . '">';

            $html .= '<div class="featured-image">';

            //Desktop image
            if ($image_url != "") {
                $html .= '<img src="' . $image_url . '" alt="' . get_the_title() . '">';
            }

            $html .= '</div>';
            $html .= '<div class="events-header subtitle3"><a href="' . get_permalink() . '" aria-label="' . get_the_title() . '">' . get_the_title() . '</a></div>';

            $event_date = get_post_meta(get_the_ID(), 'webrom_event_date', true);
            $event_dates[] = date('j', strtotime($event_date));

            $html .= '<div class="events-date">';
            $html .= '<div class="events-date-icon"><img src="/wp-content/plugins/webrom-events-calendar/assets/icons/date.svg"></div>';
            $html .= '<div class="caption3">' . $event_date . '</div>';
            $html .= '</div>';

            $event_location = get_post_meta(get_the_ID(), 'webrom_event_location', true);

            // Check if location exists
            if ($event_location !== '') {

                $html .= '<div class="events-location">';
                $html .= '<div class="events-location-icon"><img src="/wp-content/plugins/webrom-events-calendar/assets/icons/location.svg"></div>';
                $html .= '<div class="caption3">' . $event_location . '</div>';
                $html .= '</div>';
            }

            $html .= '</div>';
        }
        wp_reset_postdata();
    }

    // Pagination container
    $html .= '<div id="pagination">
        <div id="pages"></div>
    </div>';

    $html .= '</div>';

    return $html;
}

// Register the template for webrom_events custom post type
function custom_webrom_events_template($template)
{
    if (is_singular('webrom_events')) {
        $new_template = locate_template(array('custom-event-template.php'));

        if (!empty($new_template)) {
            return $new_template;
        }
    }

    return $template;
}
add_filter('template_include', 'custom_webrom_events_template', 99);

function webromEventCalendar()
{

    $html = "";
    $html .= '<div class="events-calendar-section">';
    $html .= '<div class="events-calendar-plugin">';

    // Posts section
    $html .= '<div class="events-calendar-posts" id="events-calendar-posts">';

    // Todo: nera postu
    $html .= showPosts();

    $html .= '</div>';

    // Calendar section
    $html .= '<div class="events-calendar-calendar" id="events-calendar-calendar">';
    $html .= showCalendar();  // Assuming showCalendar() returns HTML as a string
    $html .= '</div>';

    $html .= '</div>'; // Close events-calendar-plugin
    $html .= '<div class="all-events-div">';
    $html .= '<a class="all-events-btn" href="' . esc_url(get_site_url()) . '/renginiai">' . __('Visi renginiai', 'webrom-theme') . '</a>';
    $html .= '</div>'; // Close all-events-div
    $html .= '</div>'; // Close events-calendar-section


    return $html;
}
add_shortcode('webromEventCalendar', 'webromEventCalendar');

function allEventsWithCalendar()
{

    $html = "";
    $html .= '<div class="events-calendar-section ">';
    $html .= '<div class="events-calendar-plugin all-events-page">';

    // Posts section
    $html .= '<div class="events-calendar-posts" id="events-calendar-posts">';

    // Todo: nera postu
    $html .= showPosts();

    $html .= '</div>';

    // Calendar section
    $html .= '<div class="events-calendar-calendar" id="events-calendar-calendar">';
    $html .= showCalendar();  // Assuming showCalendar() returns HTML as a string
    $html .= '</div>';

    $html .= '</div>'; // Close events-calendar-plugin
    $html .= '<div class="all-events-div">';
    $html .= '<a class="all-events-btn" href="' . esc_url(get_site_url()) . '/">' . __('Atgal', 'webrom-theme') . '</a>';
    $html .= '</div>'; // Close all-events-div
    $html .= '</div>'; // Close events-calendar-section


    return $html;
}
add_shortcode('allEventsWithCalendar', 'allEventsWithCalendar');
