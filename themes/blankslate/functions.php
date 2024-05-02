<?php



if (!defined('ABSPATH')) {
    die('Access denied');
}

/** Add CSS styles */
function euhor_register_styles()
{


    // Home page styles
    wp_enqueue_style(
        'euhor-home',
        get_template_directory_uri() . '/assets/css/home.css',
        array('euhor-main'),
        '1.4',
        'all'
    );
}
add_action('wp_enqueue_scripts', 'euhor_register_styles');

/** Add JS files */
function euhor_register_scripts()
{
    // Home
    wp_enqueue_script(
        'euhor-main',
        get_template_directory_uri() . '/assets/js/main.js',
        array('jquery'),
        '1.3',
        true
    );
}
add_action('wp_enqueue_scripts', 'euhor_register_scripts');





function register_event_post_type()
{
    $labels = array(
        'name'               => 'Renginiai',
        'singular_name'      => 'Renginys',
        'menu_name'          => 'Renginiai',
        'name_admin_bar'     => 'Renginys',
        'description'        => 'Renginio aprašymas',
    );

    $args = array(
        'label'               => 'Renginiai',
        'labels'              => $labels,
        'supports'            => array('title', 'thumbnail'),
        'public'              => true,
        'show_ui'             => true,
        'show_in_menu'        => true,
        'show_in_admin_bar'   => true,
        'show_in_nav_menus'   => true,
        'can_export'          => true,
        'has_archive'         => true,
        'exclude_from_search' => false,
        'publicly_queryable'  => true,
        'capability_type'     => 'post',
    );

    register_post_type('renginiai', $args);
}
add_action('init', 'register_event_post_type');

// Add meta boxes for Date, Time and Location
function add_event_meta_boxes()
{
    add_meta_box('event_details', 'Event Details', 'event_meta_box_callback', 'renginiai', 'side', 'default');
}

function event_meta_box_callback($post)
{
    // Nonce field for security
    wp_nonce_field('event_details_data', 'event_details_nonce');

    // Get existing values
    $date = get_post_meta($post->ID, 'event_date', true);
    $time = get_post_meta($post->ID, 'event_time', true);
    $location = get_post_meta($post->ID, 'event_location', true);

    // Fields for input
    echo '<p><label for="event_date">Data:</label>
    <input type="date" id="event_date" name="event_date" value="' . esc_attr($date) . '" ></p>';

    echo '<p><label for="event_time">Laikas:</label>
    <input type="text" id="event_time" name="event_time" value="' . esc_attr($time) . '" ></p>';

    echo '<p><label for="event_location">Lokacija:</label>
    <input type="text" id="event_location" name="event_location" value="' . esc_attr($location) . '" ></p>';
}

// Save the meta box data
function save_event_meta_box_data($post_id)
{
    // Check save status and nonce
    if (!isset($_POST['event_details_nonce']) || !wp_verify_nonce($_POST['event_details_nonce'], 'event_details_data')) {
        return;
    }
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
        return;
    }

    // Save data
    update_post_meta($post_id, 'event_date', sanitize_text_field($_POST['event_date']));
    update_post_meta($post_id, 'event_time', sanitize_text_field($_POST['event_time']));
    update_post_meta($post_id, 'event_location', sanitize_text_field($_POST['event_location']));
}

add_action('add_meta_boxes', 'add_event_meta_boxes');
add_action('save_post', 'save_event_meta_box_data');

add_theme_support('post-thumbnails');
