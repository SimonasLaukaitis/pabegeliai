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

/** Add theme menus */
function euhor_register_menus()
{
    // Footer menu navigation
    register_nav_menus(array(
        'footer-menu-navigation' => __('Footer Menu Navigation'),
    ));

    // Header menu
    register_nav_menus(array(
        'header-menu' => __('Header Menu'),
    ));
}
add_action('init', 'euhor_register_menus');

