<?php

/**
 * Template Name: Calendar
 * Template Post Type: page
 */





get_header();
?>

<style>



</style>

<main>
    <?php
    
    $args = array(
        'post_type'      => 'renginiai',
        'posts_per_page' => 3,  // -1 to fetch all posts
        'post_status'    => 'publish'
    );

    $renginiai_query = new WP_Query($args);

    if ($renginiai_query->have_posts()) {

        while ($renginiai_query->have_posts()) {
            $renginiai_query->the_post();

            $title = get_the_title();
            $event_date = get_post_meta(get_the_ID(), 'event_date', true);
            $event_time = get_post_meta(get_the_ID(), 'event_time', true);
            $event_location = get_post_meta(get_the_ID(), 'event_location', true);

            if (has_post_thumbnail()) {
                echo '<div class="renginiai-thumbnail">';
                the_post_thumbnail('full'); // You can specify a thumbnail size instead of 'full'
                echo '</div>';
            }

            // Check if the custom fields are not empty
            if (!empty($event_date)) {
                echo '<p>' . esc_html($event_date) . '</p>';
            }
            if (!empty($event_time)) {
                echo '<p>' . esc_html($event_time) . '</p>';
            } 
            if (!empty($event_location)) {
                echo '<p>' . esc_html($event_location) . '</p>';
            }

        }

     
    }

    // Restore original Post Data
    wp_reset_postdata();
    ?>

</main>

<?php get_footer() ?>