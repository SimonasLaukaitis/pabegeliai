<?php

/**
 * Template Name: Custom Event Template
 * Template Post Type: webrom_events
 */
get_header();



while (have_posts()): the_post();
    $month_names = array(
        __('sausio', 'webrom-theme'),
        __('vasario', 'webrom-theme'),
        __('kovo', 'webrom-theme'),
        __('balandžio', 'webrom-theme'),
        __('gegužės', 'webrom-theme'),
        __('birželio', 'webrom-theme'),
        __('liepos', 'webrom-theme'),
        __('rugpjūčio', 'webrom-theme'),
        __('rugsėjo', 'webrom-theme'),
        __('spalio', 'webrom-theme'),
        __('lapkričio', 'webrom-theme'),
        __('gruodžio', 'webrom-theme'),
    );

    $event_date = get_post_meta(get_the_ID(), 'webrom_event_date', true);

    $dateObj = new DateTime($event_date);
    $year = $dateObj->format('Y');
    $month = $dateObj->format('n');
    $day = $dateObj->format('j');
    ?>

						<main class="container container-single-correction">

							<!-- Event info block -->
							<div class="event-info body2">
								<div class="event-small-header subtitle3">
									<?php _e('Renginio informacija', 'webrom-theme');?>
								</div>

								<div class="event-info-line"></div>

								<div class="event-sm-date">

									<div class="event-sm-icon">
										<img src="/wp-content/plugins/webrom-events-calendar/assets/icons/post-date.svg">
									</div>
									<div class="event-sm-date-date">
										<?php

    //date passing to personal calendar
    echo '<div class="date-not-visible" id="id-calendar-date">' . $event_date . '</div>';

    echo $year . ' ' . __('m', 'webrom-theme') . '. ' . $month_names[$month - 1] . ' ' . $day . ' d.';

    ?>
							</div>
						</div>

						<!-- Check if location exist -->
						<?php if (get_post_meta(get_the_ID(), 'webrom_event_location', true) != '') {?>
							<!-- Location -->
							<div class="event-sm-location">
								<div class="event-sm-icon">
									<img src="/wp-content/plugins/webrom-events-calendar/assets/icons/post-location.svg">
								</div>
								<div class="event-sm-location-location" id="id-event-location">
									<?php echo $event_location = get_post_meta(get_the_ID(), 'webrom_event_location', true); ?>
								</div>
							</div>

						<?php }?>

						<!-- Check if link exist -->
						<?php if (get_post_meta(get_the_ID(), 'webrom_event_registration_link', true) != '') {?>

							<!-- Event link -->
							<div class="event-info-link">

								<div class="event-sm-icon">
									<img src="/wp-content/plugins/webrom-events-calendar/assets/icons/post-registration.svg">
								</div>
								<div class="event-info-link-link ">
									<a href="<?php echo get_post_meta(get_the_ID(), 'webrom_event_registration_link', true); ?>" aria-placeholder="renginio registracija"><?php _e('Registracija į renginį', 'webrom-theme');?></a>
								</div>

							</div>

						<?php }?>

						<!-- Button component -->
						<div class="event-btn">
							<?php get_template_part('components/gradient-button', null, array('title' => __('Įsidėti į kalendorių', 'webrom-theme'), 'link' => '', 'img' => '/wp-content/themes/europosHorizontas/assets/icons/arrowblack.svg', 'hover-img' => '/wp-content/themes/europosHorizontas/assets/icons/arrowwhite.svg', 'ID' => 'to-calendar'))?>
						</div>

					</div>

					<!-- Event content -->
					<div class="event body3">
						<!-- Display the Featured Image -->
						<?php
    $featured_image = get_the_post_thumbnail(get_the_ID(), 'full');
    if ($featured_image !== '') {
        echo '<div class="post-featured-img">';
        echo $featured_image;
        echo ' </div>';

        echo '<div class="featured-img-date body2">';

        // Display the Post Date
        _e('Paskelbimo data: ', 'webrom-theme');

        $formatted_date = get_the_date('Y m d');
        echo $formatted_date;

        echo '</div>';
    }
    ?>


						<!-- Display the Post Title -->
						<h2 class="post-title subtitle3" id="id-post-title">
							<?php
    $post_title = get_the_title();
    echo $post_title;
    ?>
								</h2>

								<div class="post-content body2">

									<!-- Display the post content -->
									<?php the_content();?>
								</div>
								<?php get_template_part('components/social-share', null)?>
							</div>

							<!-- Background grid -->
							<?php get_template_part('components/background-grid', null, array('side' => 'left'));?>
						</main>



					<?php endwhile;

get_footer();
