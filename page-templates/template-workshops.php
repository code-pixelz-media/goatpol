<?php

/**
 * Template Name: Workshops
 *
 * Hosts all past and upcoming workshops 
 *
 * @package GOAT PoL
 */
get_header();

/*
if (get_current_user_id() != 14) {
    get_footer();
    return;
}
*/

?>
<div class="section-inner page-workshop">
    <div class="workshop-page-header">
        <div class="workshop-img">
            <img src="<?php echo get_stylesheet_directory_uri(); ?>/assets/img/workshop2.jpeg" alt="Profile Image" style="width: 200px; height: 100%; object-fit: contain; cursor: pointer;" onclick="window.location.href='/map';">
            <div>
                <h1>Workshops at The GOAT PoL</h1>
                <p>
                    This year our Reader/Editor/Advisors (RAEs) will organize and lead free online workshops for any
                    interested writers at The GOAT PoL.
                    They'll be announced here (below). You can sign-up by clicking on "attend this workshop." These
                    gatherings are mostly a way for the
                    RAEs and writers from different places to meet each other and to have fun focusing on aspects of
                    writing and reading or themes in the
                    work that we like to read. If there's a theme or a focus you'd like to see in a workshop, email us
                    at <a href="mailto:thegoatpol.social@gmail.com">thegoatpol.social@gmail.com</a>
                    and we'll try to make it happen.
                </p>
            </div>
        </div>


    </div>
    <!-- <h2>What are workshops?</h2>
    <p>
        Lorem ipsum dolor sit, amet consectetur adipisicing elit. Distinctio alias itaque harum minus eligendi eius
        saepe natus dolorem pariatur,
        autem ad voluptatem deserunt aliquid praesentium commodi, reiciendis aperiam, error aut.
    </p> -->
    <?php
    $current_datetime = date('Y-m-d H:i:s'); // Get the current datetime

    $available_workshops_args = [
        'post_type' => 'workshop',
        'posts_per_page' => -1,
        'meta_query' => [
            [
                'key' => 'workshop-date-time',
                'value' => $current_datetime,
                'compare' => '<',
                'type' => 'DATETIME'
            ]
        ],
        'orderby' => 'meta_value',
        'meta_key' => 'workshop-date-time',
        'order' => 'ASC'
    ];

    $available_workshop_query = new WP_Query($available_workshops_args);


    if ($available_workshop_query->have_posts()) {
        echo '<div class="all-workshops past-workshops">';
        while ($available_workshop_query->have_posts()) {
            $available_workshop_query->the_post();
            $thumbnail = get_the_post_thumbnail_url();
            $the_title = get_the_title();

            if ((get_current_user_id() != 14 && get_current_user_id() != 778) && $the_title == 'test 99') {
                continue;
            }





            // //! this can be delete
            // if (get_current_user_id() == 14) {
            //     // echo '===============';
            //     $curr_wid = get_the_ID();
            //     // echo $curr_wid;
            //     $workshop_participants = get_post_meta((int)$curr_wid, 'signups', true);
            //     // var_dump($workshop_participants);
            //     foreach ($workshop_participants as $participant) {
            //         $user_exists = get_user_by('id', $participant);
            //         if ($user_exists === false) {
            //             continue;
            //         }
            //         // echo 'hiu';
            //         $user_w = get_user_meta((int)$participant, 'workshops', true);
            //         // var_dump($user_w);
            //         if (!empty($user_w) &&  !in_array($curr_wid, $user_w)) {
            //             array_push($user_w, $curr_wid);
            //             echo '<pre>';
            //             var_dump($user_w);
            //             echo '</pre>';
            //             // echo $participant.'<br>';
            //             update_user_meta((int)$participant, 'workshops', $user_w);
            //         }
            //     }
            // }






            echo '<div class="workshop-table">';
            if ($thumbnail != '') {
                echo '<img src="' . $thumbnail . '" alt="post thumbnail"/>';
            } else {
                echo pol_get_random_goat();
            }
            echo '<div class="workshop-card-contents">';
            echo '<div class="workshop-card-title"><a href="' . get_the_permalink() . '" target="_blank" class="workshop-title">' . get_the_title() . '</a></div>';
            echo '<p class="card-date">' . get_field('workshop-date-time') . ' (CEST)</p>';
            echo '<div class="workshop-card-detail-content">' . get_the_excerpt() . '</div>';
            echo '<a href="' . get_the_permalink() . '" target="_blank">Learn more about this workshop</a>';
            echo '</div>';
            // echo '<div><a href="' . get_the_permalink() . '" target="_blank">Sign up for this workshop</a></div>';
            // echo '<button></button>';
            echo '</div>';
        }
        echo '</div>';
    } else {
        // No posts found
        echo 'No upcoming workshops !!';
    }
    ?>
    <div id="workshop-navigate-test"></div>
    <h3 class="past-workshop-title" id="workshop-navigate"><i class="fa-solid fa-arrow-up-long"></i>Past Workshops
    </h3>
    <div class="workshoop-time-stamp">

        <?php echo date('Y-m-d H:i:s'); ?>
    </div>

    <h3 class="available-workshop-title"><i class="fa-solid fa-arrow-down-long"></i>Available Workshops</h3>
    <?php
    $current_datetime = date('Y-m-d H:i:s'); // Get the current datetime

    $available_workshops_args = [
        'post_type' => 'workshop',
        'posts_per_page' => -1,
        'meta_query' => [
            [
                'key' => 'workshop-date-time',
                'value' => $current_datetime,
                'compare' => '>',
                'type' => 'DATETIME'
            ]
        ],
        'orderby' => 'meta_value',
        'meta_key' => 'workshop-date-time',
        'order' => 'ASC'
    ];

    $available_workshop_query = new WP_Query($available_workshops_args);

    // echo $available_workshop_query->request;

    if ($available_workshop_query->have_posts()) {
        echo '<div class="all-workshops">';
        while ($available_workshop_query->have_posts()) {
            $available_workshop_query->the_post();
            $thumbnail = get_the_post_thumbnail_url();
            $the_title = get_the_title();
            // if ((get_current_user_id() != 14 && get_current_user_id() != 778) && $the_title == 'test 99') {
            //     continue;
            // }
            echo '<div class="workshop-table">';
            if ($thumbnail != '') {
                echo '<img src="' . $thumbnail . '" alt="post thumbnail"/>';
            } else {
                echo pol_get_random_goat();
            }


            echo '<div class ="workshop-card-contents">';
            echo '<div class="workshop-card-title"><a href="' . get_the_permalink() . '" target="_blank" class="workshop-title">' . get_the_title() . '</a></div>';
            echo '<p class="card-date">' . get_field('workshop-date-time') . ' (CEST)</p>';
            echo '<div class="workshop-card-detail-content">' . get_the_excerpt() . '</div>';
            echo '<a href="' . get_the_permalink() . '#all-workshop-participants" target="_blank">Learn more and sign-up here</a>';
            echo '</div>';
            echo '</div>';
        }
        echo '</div>';
    } else {
        // No posts found
        echo 'No upcoming workshops !!';
    }

    // Reset post data
    wp_reset_postdata();

    ?>
</div>
<a href="/map" class="workshop-footer-redirect">
    <img src="<?php echo get_stylesheet_directory_uri(); ?>/assets/img/workshop1.jpeg" class="workshop-footer-img" alt="Profile Image" style="width: 250px; height: 100%; object-fit: cover; margin-top: 0;" /></a>
<?php

wp_reset_postdata();
get_footer();



?>