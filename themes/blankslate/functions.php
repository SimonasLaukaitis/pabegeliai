<?php



if (!defined('ABSPATH')) {
    die('Access denied');
}

/** Add CSS styles */
function pc_register_styles()
{

    // Fonts
    wp_enqueue_style(
        'pc-fonts',
        get_template_directory_uri() . '/assets/css/fonts.css',
        array(),
        '1.2',
        'all'
    );

    // Main theme styles
    wp_enqueue_style(
        'pc-main',
        get_template_directory_uri() . '/style.css',
        array('pc-fonts'),
        '1.8',
        'all'
    );
    // Home page styles
    wp_enqueue_style(
        'pc-home',
        get_template_directory_uri() . '/assets/css/home.css',
        array('pc-main'),
        '1.4',
        'all'
    );
}
add_action('wp_enqueue_scripts', 'pc_register_styles');

/** Add JS files */
function pc_register_scripts()
{
    // Home
    wp_enqueue_script(
        'pc-main',
        get_template_directory_uri() . '/assets/js/main.js',
        array('jquery'),
        '1.3',
        true
    );
}
add_action('wp_enqueue_scripts', 'pc_register_scripts');



