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



?>
<div class="">

    <div class="events-calendar-section">

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

    </div>

</div>

<?php get_footer() ?>