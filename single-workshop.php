<?php

get_header();

$curr_user_id = get_current_user_id();

$user_data = get_userdata($curr_user_id);


$user_name = $user_data->user_login;
$user_email = $user_data->user_email;

global $current_user;
$post_id = get_the_ID();
$get_all_signups = get_post_meta($post_id, 'signups', true);
$get_all_workshops = get_user_meta($curr_user_id, 'workshops', true);
$has_user_signedup = (is_array($get_all_signups) && in_array($curr_user_id, $get_all_signups));

// var_dump($post_id);

$user_role = 'user';
if (get_user_meta($current_user_id, 'rae_approved', true) == 1) {
    $user_role = 'rae';
} else if (in_array('administrator', (array) $current_user->roles)) {
    $user_role = 'admin';
}


// if(get_current_user_id() == 14){

//     echo '<pre>';
//     var_dump($_POST);
//     echo '</pre>';
// }


if (isset($_POST['signup_for_workshop'])) {

    //add user to workshop meta
    if (is_array($get_all_signups) && !in_array($curr_user_id, $get_all_signups)) {
        array_push($get_all_signups, $curr_user_id);
        update_post_meta($post_id, 'signups', $get_all_signups);
    } else {
        update_post_meta($post_id, 'signups', [$curr_user_id]);
    }

    //add workshop to usermeta
    if (is_array($get_all_workshops) && !in_array($post_id, $get_all_workshops)) {
        array_push($get_all_workshops, $post_id);
        update_user_meta($curr_user_id, 'workshops', $get_all_workshops);
    } else {
        update_user_meta($curr_user_id, 'workshops', [$post_id]);
    }

    /* send user email functions */




    gotpol_email_template($user_email, $user_name, $post_id, $action = "signup_for_workshop");

    //!!!! dont change this echo statement href !!!!
    echo '<script> window.location.href = "?view=user_list";</script>';
}

$title = get_the_title();
$content = get_the_content();
$date_time = get_field('workshop-date-time');
$date_time_meta = new DateTime(get_post_meta($post_id, 'workshop-date-time', true));
$currentDate = new DateTime();
$is_past_workshop = true;
if ($date_time_meta < $currentDate) {
    $is_past_workshop = true;
} elseif ($date_time_meta > $currentDate) {
    $is_past_workshop = false;
}

$thumbnail = get_the_post_thumbnail_url();
$get_all_signups = get_post_meta($post_id, 'signups', true);
if (get_current_user_id() == 14) {
    //var_dump($get_all_signups);
}
?>
<div class="single-workshop-page section-inner single-workshop-new-header">
    <a href="/map" class="workshop-img single-wordkshop-header-img">
        <img src="<?php echo get_stylesheet_directory_uri(); ?>/assets/img/workshop2.jpeg" alt="Profile Image" style="width: 200px; object-fit: cover;">
    </a>
</div>

<div class="single-workshop-page section-inner">

    <h1>
        <?php echo $title; ?>
    </h1>
    <div class="workshop-main-content">
        <?php
        if ($thumbnail != '') {
            echo '<img src="' . $thumbnail . '" alt="post thumbnail">';
        } else {
            echo pol_get_random_goat();
        }
        ?>

        <p>
            <?php echo $date_time; ?> (CEST)
        </p>

        
            <?php the_content(); ?>
        

        <?php
        if (is_user_logged_in()) {
            if (!$is_past_workshop) {
                if ((!is_array($get_all_signups) || !in_array($curr_user_id, $get_all_signups))) {
        ?>
                    <p class="site-msg">

                    </p>
                    <form action="" method="post" id="add-to-signup">
                        <input type="submit" name="signup_for_workshop" class="signup_for_workshop" value="Sign up for this workshop">
                    </form>
        <?php } else {
                    echo '<p>You are signed up for this workshop !!</p>';
                }
            }
        }


        ?>

    </div>
</div>



<?php
//table of users who are participants
if (is_user_logged_in()) {

    if (is_array($get_all_signups) && !empty($get_all_signups)) {
        // echo 'Participants signed up for this workshop';
        echo '<table class ="single-workshop-participants" id="all-workshop-participants">';
        echo '<tr>';
        echo '<th>Participants signed up for this workshop</th>';
        if (($user_role == 'admin' || $user_role == 'rae') || (in_array(get_current_user_id(), $get_all_signups))) {
            echo '<th>Action</th>';
        }
        echo '</tr>';
        foreach ($get_all_signups as $signup) {
            $uu = get_user_by('id', $signup);
            // if (!$uu) {
            //     continue;
            // }
            echo '<tr>';
            echo '<td>' . $uu->display_name . '</td>';
            if ($user_role == 'admin' || $user_role == 'rae') {
                echo '<td>
                        <a href= "#" class="remove-participant" data-uid="' . $uu->id . '" data-workshop-id="' . $post_id . '">Remove User</a>
                    </td>';
            } else if (get_current_user_id() == (int)$uu->id && !$is_past_workshop) {
                echo '<td>
                        <a href= "#" class="remove-participant" data-uid="' . $uu->id . '" data-workshop-id="' . $post_id . '">Remove Me Please</a>
                    </td>';
            }
            echo '</tr>';
        }
        echo '</table>';
    } else {
        echo '<p class = "single-ws-no-participant">No participants till now.</p>';
    }
}

?>
<a href="/map" class="workshop-footer-redirect">
    <img src="<?php echo get_stylesheet_directory_uri(); ?>/assets/img/workshop1.jpeg" class="workshop-footer-img" alt="Profile Image" style="width: 350px; height: 100%; object-fit: cover;" /></a>
<?php

get_footer();
