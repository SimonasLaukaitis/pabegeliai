<?php

/**
 * Template Name: Calendar
 * Template Post Type: page
 */





get_header();
?>

<style>
    :root {
        --grey-color1: #7e7f8b;
        --grey-color2: #f1f1f1;
        --grey-color3: #6c6d7b;
    }

    .container {
        max-width: 1440px;
        margin: auto;
        padding: 0 50px;
    }

    * {
        padding: 0;
        margin: 0;
        box-sizing: border-box;
    }



    /* -------------------------------- */

    @media screen and (max-width: 700px) {
        .container {
            padding: 0 20px;
        }
    }
</style>


<?php

$args = array(
    'post_type'      => 'renginiai',
    'posts_per_page' => 3,  // -1 to fetch all posts
    'post_status'    => 'publish'
);

$renginiai_query = new WP_Query($args);

?>
<main class="container">
    <section class="events-section">
        <?php
        if ($renginiai_query->have_posts()) {

            while ($renginiai_query->have_posts()) {
                $renginiai_query->the_post();

                $event_title = get_the_title();
                $event_date = get_post_meta(get_the_ID(), 'event_date', true);
                $event_time = get_post_meta(get_the_ID(), 'event_time', true);
                $event_location = get_post_meta(get_the_ID(), 'event_location', true);

                echo '<div class="event-box">';

                if (has_post_thumbnail()) {
                    echo '<div class="renginiai-thumbnail">';
                    the_post_thumbnail('medium'); // You can specify a thumbnail size instead of 'full'
                    echo '</div>';
                }

                if (!empty($event_title)) {
                    echo '<p>' . esc_html($event_title) . '</p>';
                }
                if (!empty($event_date)) {
                    echo '<p>' . esc_html($event_date) . '</p>';
                }
                if (!empty($event_time)) {
                    echo '<p>' . esc_html($event_time) . '</p>';
                }
                if (!empty($event_location)) {
                    echo '<p>' . esc_html($event_location) . '</p>';
                }

                echo '</div>';
            }
        }

        // Restore original Post Data
        wp_reset_postdata();
        ?>
    </section>
    <section class="calendar-section"></section>
</main>

<?php get_footer() ?>