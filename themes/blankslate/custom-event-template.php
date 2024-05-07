<?php

/**
 * Template Name: Custom Event Template
 * Template Post Type: webrom_template
 */
get_header();

echo '<main class="container events-template">';

while (have_posts()) : the_post();

	$event_date = get_post_meta(get_the_ID(), 'webrom_event_date', true);
	//Extract day
	$event_day = date('d', strtotime($event_date));
	// Extract the month
	$event_month = date_i18n('F', strtotime($event_date));
	$dateObj = new DateTime($event_date);
	$year = $dateObj->format('Y');
	$month = $dateObj->format('n');
	$day = $dateObj->format('j');

	$event_location = get_post_meta(get_the_ID(), 'webrom_event_location', true);
	$event_time = get_post_meta(get_the_ID(), 'webrom_event_registration_link', true);
	$featured_image = get_the_post_thumbnail(get_the_ID(), 'full');
	$post_title = get_the_title();

	$image_url = '';

	if (has_post_thumbnail()) {
		$thumbnail_url = wp_get_attachment_image_src(get_post_thumbnail_id(), 'full');
		$image_url = $thumbnail_url[0];
	}

	echo '<div class="left-screen">';

	//Title
	echo '<div class="template-header subtitle3"><a href="' . get_permalink() . '" aria-label="' . get_the_title() . '">' . get_the_title() . '</a></div>';

	echo '<div class="template-date-time-location">';

	echo '<div class="template-date-month">';

	//Date
	echo '<div class="template-date">';
	echo $event_day;
	echo '</div>';

	//Month
	echo '<div class="template-month">';
	echo $event_month;
	echo '</div>';

	echo '</div>';

	echo '<div class="templatetime-location">';

	// Check if date exists
	if ($event_date !== '') {
		echo '<div class="template-time">';
		echo '<div class="template-time-icon"><img src="/wp-content/plugins/webrom-events-calendar/assets/icons/clock.png"></div>';
		echo '<div class="caption3">' . $event_time . '</div>';
		echo '</div>';
	}

	// Check if location exists
	if ($event_location !== '') {
		echo '<div class="template-location">';
		echo '<div class="template-location-icon"><img src="/wp-content/plugins/webrom-events-calendar/assets/icons/location.png"></div>';
		echo '<div class="caption3">' . $event_location . '</div>';
		echo '</div>';
	}

	echo '</div>';

	echo '</div>';

	//Content
	echo '<div class="template-content">';
	the_content();
	echo '</div>';


	echo '</div>';

	//Righter screen
	echo '<div class="right-screen">';

	echo '<div class="template-featured-image">';
	echo '<img src="' . $image_url . '" alt="' . get_the_title() . '">';
	echo '</div>';

	echo '</div>';


endwhile;

echo '</main>';

get_footer();
