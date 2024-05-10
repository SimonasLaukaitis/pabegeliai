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

    <section class="events-calendar-section">

        <!-- plugin div -->
        <div class="events-calendar-plugin">

            <!-- Posts desktop -->
            <div class="events-calendar-posts" id="events-calendar-posts">
                <?php echo showPosts(); ?>
            </div>


            <!-- Calendar -->
            <div class="events-calendar-calendar" id="events-calendar-calendar">
                <?php echo showCalendar(); ?>
            </div>

        </div>

        <div class="all-events-div">
            <a class="all-events-btn" href=""><?php _e('Visi renginiai', 'webrom-theme'); ?></a>
        </div>

    </section>

</main>

<?php get_footer() ?>