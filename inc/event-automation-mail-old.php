<?php
/**
 * Workshop Automation email before event start and after event.
 *
 * @package GOAT PoL
 */

function goat_send_email_before_event()
{

    //  $future_time = date('Y-m-d H:i:s', strtotime('+25 hours', strtotime($current_time)));
    // Retrieve the events that are 24 hours away

    /*
     $args = array(
        'post_type'      => 'workshop',
        'post_status'    => 'publish',
        'posts_per_page' => -1
     );
     */
    $args = array(
        'post_type'      => 'workshop',
        'post_status'    => 'publish',
        'posts_per_page' => -1,
        'meta_query'     => array(
            'relation' => 'AND',
            array(
                'key'     => 'workshop-date-time',
                'value'   => date('Y-m-d H:i:s', strtotime('+24 hours')),
                'compare' => '<', // Events that are 24 hours away or less
                'type'    => 'DATETIME',
            ),
            array(
                'key'     => '24_sent',
                'value'   => '1',
                'compare' => '!=',
            ),
        ),
    );

    $events_query = new WP_Query($args);


    if ($events_query->have_posts()) {
        while ($events_query->have_posts()) {
            $events_query->the_post();

            // Get event details
            $event_title = get_the_title();
            $event_date = get_post_meta(get_the_ID(), 'workshop-date-time', true);
            $post_id = get_the_ID();

			update_post_meta($post_id,'24_sent','1');
            // Prepare and send the email
            $to = 'utsavsinghrathour@gmail.com'; // Replace with the recipient's email address
            $subject = 'Reminder: Event Tomorrow';
            $message = "Don't forget about the event '$event_title' scheduled for tomorrow, on $event_date.";

            wp_mail($to, $subject, $message);
        }

        // Restore original post data
        wp_reset_postdata();
    }
}
