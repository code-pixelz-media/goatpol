<?php

/**
 * Template Name: Contributors
 *
 * Displays list of all writers
 *
 * @package GOAT PoL
 */
get_header();


// if(get_current_user_id() == 14){
//     $user_comm_seeking_args = array(
// 		'fields' => array( 'ID', 'display_name' ),
// 		'meta_query' => array(
// 			array(
// 				'key'     => 'currently_seeking_commission',
// 				'value'   => 'a:2:{i:0;i:1;i:1',
// 				'compare' => 'REGEXP',
// 			),
// 		),
// 	);
	
// 	// Get all users into an array
// 	$user_infos = get_users($user_comm_seeking_args);

// 	// Define a function to compare the dates
// 	function compare_by_date($a, $b) {
// 		$date_a = unserialize($a->currently_seeking_commission)['1'];
// 		$date_b = unserialize($b->currently_seeking_commission)['1'];
// 		return strtotime($date_a) - strtotime($date_b);
// 	}

// 	// Sort the users by the date in the 'currently_seeking_commission' meta key
// 	usort($user_infos, 'compare_by_date');

//     foreach ($user_infos as $uu) {
//         echo $uu->ID.'<br>';
//     }
// }


global $current_user;


$author = get_user_by('slug', get_query_var('author_name'));
$current_author_id = $author->ID;
$current_author = $author;

$user_data = get_userdata($current_author_id);
$display_name = $user_data->display_name;
global $wp;
// echo home_url( $wp->request );



// could be almost anything but I don't recommend to use 'page' or 'paged'
$query_arg = 'current_page';

// URL of your search page
$page_url = get_permalink(); // or get_pagenum_link() or something custom
// var_dump($page_url);

$current_page = (isset($_GET[$query_arg]) && $_GET[$query_arg]) ? absint($_GET[$query_arg]) : 1;

$has_updated_contributors_page = get_user_meta($current_author_id, 'has_updated_contributors_page', true);
if (gettype($has_updated_contributors_page) == 'boolean' || (int) $has_updated_contributors_page != 1) {
    $has_updated_contributors_page = false;
} else {
    $has_updated_contributors_page = true;
}


if ($current_user->ID == $current_author_id) {

    if (!$has_updated_contributors_page) {
        echo '<script>window.location.href = "' . home_url('/registration') . '";</script>';
    }
}


$args = array(
    'post_type' => 'story',
    'post_status' => 'publish',
    'posts_per_page' => 10,
    'author' => $current_author_id,
    'paged' => $current_page
);

$wp_author_stories_query = new WP_Query($args);

function get_write_for($for)
{
    switch ($for) {
        case 'school':
            return 'my school, ';
            break;

        case 'friends':
            return 'my friends, ';
            break;

        case 'private':
            return 'myself, privately, ';
            break;

        case 'public_online':
            return 'publicly online, ';
            break;

        case 'paid_newspaper':
            return 'to be paid by newspaper or journal, ';
            break;

        case 'paid_books':
            return 'to be paid for my books, ';
            break;

        case 'children':
            return 'for children, ';
            break;

        case 'unborn_readers':
            return 'for readers as-yet unborn, ';
            break;

        case 'other':
            return 'Other, ';
            break;

        default:
            return '';
            break;
    }
}

function get_genre($genre)
{
    switch ($genre) {
        case 'poetry':
            return 'Poetry, ';
            break;

        case 'prose':
            return 'Prose, ';
            break;

        case 'fiction':
            return 'Fiction, ';
            break;

        case 'novels':
            return 'Novels, ';
            break;

        case 'short_stories':
            return 'Short Stories, ';
            break;

        case 'diary':
            return 'Diary, ';
            break;

        case 'journalism':
            return 'Journalism, ';
            break;

        case 'essays':
            return 'Long Form Essays, ';
            break;

        case 'play_scripts':
            return 'Play Scripts, ';
            break;

        case 'film_scripts':
            return 'Film Scripts, ';
            break;

        case 'love_letters':
            return 'Love Letters, ';
            break;

        case 'manifestos':
            return 'Manifestos, ';
            break;

        case 'songs':
            return 'Songs, ';
            break;

        case 'other':
            return 'Other, ';
            break;

        default:
            return '';
            break;
    }
}
?>

<main id="site-content" role="main">
    <div id="post-content" class="section-inner contributors-author-profile">
        <div class="section-inner">
            <div class="entry-content">

                <?php if ((int) $current_author_id == (int) $current_user->ID) { ?>
                    <div class="view-edit-info-wrapper">
                        <div class="view-edit-info">
                            <a class="styled-button active" href="#">VIEW</a>
                            <a class="styled-button" href="/registration">EDIT</a>
                        </div>
                        <p>To see your payment details and <br> available commissions please choose “EDIT”</p>
                    </div>
                <?php } ?>

                <h1 class="contributor-page-profile-img">
                    <?php
                    // $profile_picture = get_user_meta($current_author_id, 'profile_picture', true);
                    // $profile_picture_url = $profile_picture != '' ? wp_get_attachment_url((int) $profile_picture) : 'https://secure.gravatar.com/avatar/4eb8082fa51c1f2b580638fd1e3c68ae?s=96&d=mm&r=g';
                    /*
                    $avatar_img_url = '';
                    $profile_picture = get_user_meta($current_author_id, 'profile_picture', true);
                    $gravatar = get_avatar($current_author_id);
                    // var_dump(gettype($gravatar));
                    if (gettype($gravatar) != 'string') {
                        $avatar_img_url = wp_get_attachment_url($profile_picture);
                    } else {
                        // var_dump(get_avatar_url($current_author_id));
                        $avatar_img_url = get_avatar_url($current_author_id);
                    }
                    */



                    // $avatar_img_url = '';
                    // $profile_picture = get_user_meta($current_author_id, 'profile_picture', true);
                    // if($profile_picture != ""){
                    //     $avatar_img_url = $profile_picture;
                    // }else{
                    //     $avatar_img_url = get_avatar_url($current_author_id);
                    // }
                    
                    $avatar_img_url = pol_get_user_profile_img($current_author_id);

                    echo '<img src="' . $avatar_img_url . '" width="80" height="80" class="avatar avatar-80 photo" />';
                    echo $display_name; //$current_author->first_name . ' ' . $current_author->last_name;
                    if (get_user_meta($current_author_id, 'rae_approved', true) == 1) {
                        echo "— Reader/Advisor/Editor (RAE)";
                    } ?>
                </h1>

                <?php
                if (!$has_updated_contributors_page) {
                    $msg_link = '<u><strong><a href="/registration">here</a></strong></u> ';

                    if ((int) $current_author_id == (int) $current_user->ID) {
                        ?>
                        <p class="profile-paragraph">
                            Welcome to The GOAT PoL. By answering the questions
                            <?php echo $msg_link; ?>
                            you’ll create your own account and Contributors Page.
                            This allows you to take part in all of our <strong>free services</strong>:
                            including <strong>uploading your writing to share with others</strong>;
                            reading and <strong>selecting favourites</strong> from among the work uploaded by other writers;
                            <strong>joining our group workshops</strong> online with other writers from around the world;
                            and working one-on-one with one of our ten Reader/Advisor/Editors (RAEs)
                            to <strong>develop and publish your writing on The GOAT PoL</strong>, and be paid for it.
                            We also award money to support some writers working on books, and we’ll
                            <strong>publish several books a year</strong> from The GOAT PoL writers (see the menu on our
                            homepage).
                            Please be honest and thoughtful in your responses, and <strong>don’t forget to upload some
                                of your writing</strong>—pieces you wish to share publicly. All visitors to The GOAT PoL
                            will have access to your Contributor’s Page, so present yourself as you wish to be seen.
                        </p>
                        <?php
                    }
                }
                ?>

                <?php
                $grewup_location = get_user_meta($current_author_id, 'grew_up_location', true);
                $grewup_location = $grewup_location && $grewup_location != '' ? $grewup_location . ',' : '';
                // $grewup_location_city = get_user_meta($current_author_id, 'grew_up_location_city', true);
                // $grewup_location_city = $grewup_location_city && $grewup_location_city != '' ? ', ' . $grewup_location_city : '';
                $grewup_location_nation = get_user_meta($current_author_id, 'grew_up_location_nation', true);
                $grewup_location_nation = $grewup_location_nation && $grewup_location_nation != '' ? ' ' . $grewup_location_nation : '';

                $grewup_full_location = $grewup_location . $grewup_location_nation;

                if ($grewup_full_location != '') {
                    echo '<p><strong>I grew up in </strong>';
                    echo ucwords($grewup_full_location);
                    echo '</p>';
                }



                $current_location = get_user_meta($current_author_id, 'current_location', true);
                $current_location = $current_location && $current_location != '' ? $current_location . ',' : '';
                // $current_location_city = get_user_meta($current_author_id, 'current_location_city', true);
                // $current_location_city = $current_location_city && $current_location_city != '' ? ', ' . $current_location_city : '';
                $current_location_nation = get_user_meta($current_author_id, 'current_location_nation', true);
                $current_location_nation = $current_location_nation && $current_location_nation != '' ? ' ' . $current_location_nation : '';
                $current_full_location = $current_location . $current_location_nation;
                if ($current_full_location != '') {
                    echo '<p><strong>I currently live in </strong>
                    ' . ucwords($current_full_location) . '
                    </p>';
                }


                $grew_up_languages = get_user_meta($current_author_id, 'grew_up_languages', true);
                $grew_up_languages = is_array($grew_up_languages) ? $grew_up_languages : [];
                $grew_up_languages = array_map('ucfirst', $grew_up_languages);
                $grew_up_languages = implode(', ', $grew_up_languages);
                if ($grew_up_languages != '') {

                    echo '<p>
                        <strong>I was raised in </strong>' . $grew_up_languages . '. <strong> (languages)</strong>
                    </p>';
                }





                $write_read_languages = get_user_meta($current_author_id, 'write_read_languages', true);
                $write_read_languages = is_array($write_read_languages) ? $write_read_languages : [];
                $write_read_languages = array_map('ucfirst', $write_read_languages);
                $write_read_languages = implode(', ', $write_read_languages);
                if ($write_read_languages != '') {
                    echo '<p><strong>I read and write in </strong>' . $write_read_languages . '. <strong> (languages)</strong></p>';
                }






                $genres = get_user_meta($current_author_id, 'write_genres', true);
                $genres = is_array($genres) ? $genres : [];
                $genres = array_map('ucfirst', $genres);
                $genres = implode(', ', $genres);
                if ($genres != '') {
                    echo '<p><strong>I write </strong>' . $genres . '. <strong> (genres)</strong></p>';
                }






                $write_for = get_user_meta($current_author_id, 'write_for', true);
                // $write_for = array_map('ucfirst', $write_for);
                $write_for = implode(', ', $write_for);
                if ($write_for != '') {

                    echo '<p>
                        <strong>I write for </strong>
                        ' . str_replace('_', ' ', $write_for) . '. 
                    </p>';
                }






                $reason_for_writing = get_user_meta($current_author_id, 'reason_for_writing', true);
                // echo $reason_for_writing;
                
                if ($reason_for_writing != '') {
                    echo '<p>
                            <strong>Reason why I write: </strong>' . $reason_for_writing . '
                        </p>';
                }





                $fav_authors = get_user_meta($current_author_id, 'fav_authors', true);
                $fav_authors = is_array($fav_authors) ? $fav_authors : [];
                $fav_authors = array_map('ucfirst', $fav_authors);
                $fav_authors = implode(', ', $fav_authors);

                if ($fav_authors != '') {
                    echo '<p><strong>My favourite authors are </strong>' . $fav_authors . '. </p>';
                }





                $fav_goatpol_stories = get_user_meta($current_author_id, 'fav_goatpol_stories', true);
                $all_gotpol_stories = '';
                if (is_array($fav_goatpol_stories)) {
                    foreach ($fav_goatpol_stories as $story) {
                        $all_gotpol_stories .= '<a href="' . get_permalink($story) . '">' . get_the_title($story) . '</a> , ';
                    }
                }

                if ($all_gotpol_stories != '') {
                    echo '<p> <strong>My favourite stories on The GOAT PoL include </strong>' . $all_gotpol_stories . '</p>';
                }



                $fav_subject_to_write_about = get_user_meta($current_author_id, 'fav_subject_to_write_about', true);
                $fav_subject_to_write_about = is_array($fav_subject_to_write_about) ? $fav_subject_to_write_about : [];
                $fav_subject_to_write_about = array_map('ucfirst', $fav_subject_to_write_about);
                $fav_subject_to_write_about = implode(', ', $fav_subject_to_write_about);

                if ($fav_subject_to_write_about != '') {
                    echo '<p><strong>My favourite subject(s) to write about include </strong>' . $fav_subject_to_write_about . '. </p>';
                }




                $diff_part_about_writing = (get_user_meta($current_author_id, 'difficult_writing_part', true));
                if ($diff_part_about_writing != '') {
                    echo '<p><strong>The most difficult part I find about writing is </strong>' . $diff_part_about_writing . '</p>';
                }






                $rewarding_moment = (get_user_meta($current_author_id, 'rewarding_moment', true));
                if ($rewarding_moment != '') {
                    echo '<p><strong>Most rewarding moment as a writer for me is </strong>' . $rewarding_moment . '</p>';
                }

                ?>




                <?php if (get_the_author_meta('description', $current_author_id) != ''): ?>
                    <h5>Bio:</h5>
                    <p>
                        <?php echo get_the_author_meta('description', $current_author_id); ?>
                    </p>
                <?php endif; ?>

                <div class="tabs-container">
                    <div id="tab-stories" class="author-tab-content active">
                        <h1>Work from
                            <?php echo $display_name; //$current_author->first_name . ' ' . $current_author->last_name; ?>
                            published on
                            The GOAT PoL
                        </h1>
                        <ul>
                            <?php

                            $all_liked_uploaded_stories = get_user_meta(get_current_user_id(), 'liked_uploaded_stories', true);
                            
                            if ($wp_author_stories_query->have_posts()) {
                                while ($wp_author_stories_query->have_posts()) {

                                    $wp_author_stories_query->the_post();
                                    $story_id = get_the_ID();
                                    $post_title = get_the_title();
                                    $post_description = get_the_content();
                                    $post_excerpt = wp_trim_words($post_description, 15, '...');
                                    $post_featured_image = has_post_thumbnail() ? get_the_post_thumbnail_url($story_id, 'post-thumbnail') : '';
                                    $post_permalink = get_permalink();


                                    echo '<li>';
                                    
                                    
                                    if (is_user_logged_in() && ((int) $current_user->ID != (int) $current_author_id)) {
                                        $action = '<i class="fa-regular fa-star"></i>';
                                        $button_title = 'Choose this story as one of your favourites';
                                        if (is_array($all_liked_uploaded_stories) && in_array((int) $story_id, $all_liked_uploaded_stories)) {
                                            $action = '<i class="fa-solid fa-star"></i>';
                                            $button_title = 'Remove this story from your favourites';
                                        }
                                        echo '<button class="like-uploaded-story tooltip" data-story-id="' . $story_id . '" data-author-id="' . $current_author_id . '" data-type="story">
                                                ' . $action . '
                                                <span class="tooltiptext" >' . $button_title . '</span>
                                            </button>';

                                    } else if (is_user_logged_in() && ((int) $current_user->ID == (int) $current_author_id)) {
                                        echo '<button class="remove-uploaded-story tooltip" data-story-id="' . $story_id . '" style="display:none">
                                                <i class="fa-solid fa-trash"></i>
                                                <span class="tooltiptext" >Remove this story from my Contributor’s Page</span>
                                            </button>';
                                    }

                                    if ($post_featured_image != '') {
                                        echo '<img style="height: 100px;" src="' . $post_featured_image . '" alt="' . $post_title . '">';
                                    } else {
                                        echo pol_get_random_goat();
                                    }
                                    echo '<p class=" open-story-popup-author-page-title" data-title="' . $post_title . '" data-story-id="' . $story_id . '">' . $post_title . '</p>';
                                    echo '<p>' . $post_excerpt . '</p>';

                                    // echo '<a href="#" class="open-story-popup-author-page open-story-popup-author-page-content" data-title="' . $post_title . '" data-story-id="' . $story_id . '">
                                    //             <span class="dkpdf-button-icon">
                                    //                 <i class="fa-brands fa-readme"></i>
                                    //             </span>
                                    //             &nbsp;&nbsp;&nbsp;Read this story
                                    //         </a>';
                                    echo '<a href="'.$post_permalink.'" target="_blank" class="open-story-popup-author-page open-story-popup-author-page-content dont-show-popup">
                                            <span class="dkpdf-button-icon">
                                                <i class="fa-brands fa-readme"></i>
                                            </span>
                                            &nbsp;&nbsp;&nbsp;Read this story
                                        </a>';
                                    echo '</li>';
                                }

                            } else {
                                echo '<h6>No stories found</h6>';
                            }

                            wp_reset_postdata();

                            ?>

                            <div id="confirm-delete-story-modal" class="modal">
                                <div class="container">
                                    <div class="row">
                                        <div class="col-lg-12">
                                            <h3>Are you sure, you want to remove this story from your Contributor’s
                                                Page?</h3>
                                            <button class="confirm">Yes</button>
                                            <button class="cancel">Cancel</button>
                                        </div>
                                    </div>
                                </div>
                                <a href="#close-modal" rel="modal:close" class="close-modal edit-modal-close ">Close</a>
                            </div>

                        </ul>
                        <div class='author-pagination'>
                            <?php
                            echo paginate_links(
                                array(
                                    'base' => home_url($wp->request) . '%_%',
                                    'format' => '?' . $query_arg . '=%#%',
                                    'current' => $current_page,
                                    'total' => $wp_author_stories_query->max_num_pages,
                                    'prev_next' => true,
                                )
                            ); ?>
                        </div>
                    </div>

                    <!-- show story in popup -->
                    <div id="author-page-story-popup" class="modal">
                        <a href="" rel="modal:close" class="close-modal">Close</a>
                        <div class="has-text-align-center wp-block-image is-style-no-vertical-margin"
                            style="font-size: 26px;text-align:center">
                            <h2>

                            </h2>
                        </div>
                        <div class="author-page-story-popup-contents">
                            <div class="wp-block-image is-style-no-vertical-masrgin">
                                <?php //echo pol_get_random_goat(); ?>
                                <img src="" alt="image">
                            </div>
                            <div class="story-content">

                            </div>
                        </div>
                    </div>

                    <div class="author-stories-favorites">
                        <?php echo do_shortcode('[upload_stories user_id="' . $current_author_id . '" page="author" ]'); ?>

                        <div class="contributors-author-profile fav-uploaded-stories">
                            <div id="tab-fav-uploaded-works" class="author-tab-content">
                                <h1><i class="fa-solid fa-star">&nbsp;</i>Favourites by others</h1>
                                <?php

                                $liked_uploaded_stories = get_user_meta($current_author_id, 'liked_uploaded_stories', true);

                                if (is_array($liked_uploaded_stories) && !empty($liked_uploaded_stories)) {
                                    echo '<ul>';
                                    foreach ($liked_uploaded_stories as $story) {
                                        if (get_post_type($story) != 'story') {

                                            $story_title = get_post_meta($story, 'story_title', true);
                                            $story_desc = get_post_meta($story, 'story_desc', true);
                                            $story_thumbnail_id = get_post_meta($story, 'story_thumbnail', true);
                                            $story_author_id = get_post_field('post_author', (int) $story);
                                            $thumbnail_id = get_post_thumbnail_id((int) $story);
                                            $story_author = get_user_by('id', $story_author_id);
                                            $story_author_posts_url = get_author_posts_url($story_author_id);
                                            // $story_author_name = $story_author->first_name . ' ' . $story_author->last_name;
                                            $story_author_nom_de_plume = get_user_meta($story_author_id, 'nom_de_plume', true);
                                            $story_author_name = $story_author_nom_de_plume == '' ? $story_author->user_login : $story_author_nom_de_plume;

                                            echo '<li>';
                                            if ($thumbnail_id != 0) {
                                                echo '<img style="height: 100px;" src="' . wp_get_attachment_url($thumbnail_id) . '" alt="' . $story_title . '">';
                                            } else if ($story_thumbnail_id != '') {
                                                echo '<img style="height: 100px;" src="' . wp_get_attachment_url($story_thumbnail_id) . '" alt="' . $story_title . '">';
                                            } else {
                                                echo '<img style="height: 100px;" src="' . pol_get_random_goat_img_url_for_list_page() . '" alt="' . $story_title . '">';
                                            }
                                            echo '<h4>' . $story_title . '</h4>';
                                            echo '<h5>' . $story_desc . '</h5>';
                                            echo '<a href="' . wp_get_attachment_url($story) . '" target="_blank">
                                                        <span class="dkpdf-button-icon">
                                                            <i class="fa-brands fa-readme"></i>
                                                        </span>
                                                        &nbsp;&nbsp;&nbsp;Read this story
                                                    </a>';

                                            echo '<p><a href="' . $story_author_posts_url . '"><m>By: <em>' . ucwords($story_author_name) . '</em></m></a></p>';

                                            echo '</li>';
                                        } else {
                                            $story_title = get_the_title($story);
                                            $story_desc = get_post_meta($story, 'story_desc', true);
                                            $story_thumbnail_id = get_post_meta($story, 'story_thumbnail', true);
                                            $story_author_id = get_post_field('post_author', (int) $story);
                                            $thumbnail_id = get_post_thumbnail_id((int) $story);
                                            $story_author = get_user_by('id', $story_author_id);
                                            $story_author_posts_url = get_author_posts_url($story_author_id);
                                            // $story_author_name = $story_author->first_name . ' ' . $story_author->last_name;
                                            $story_author_nom_de_plume = get_user_meta($story_author_id, 'nom_de_plume', true);
                                            $story_author_name = $story_author_nom_de_plume == '' ? $story_author->user_login : $story_author_nom_de_plume;

                                            echo '<li>';
                                            if ($thumbnail_id != 0) {
                                                echo '<img style="height: 100px;" src="' . wp_get_attachment_url($thumbnail_id) . '" alt="' . $story_title . '">';
                                            } else if ($story_thumbnail_id != '') {
                                                echo '<img style="height: 100px;" src="' . wp_get_attachment_url($story_thumbnail_id) . '" alt="' . $story_title . '">';
                                            } else {
                                                echo '<img style="height: 100px;" src="' . pol_get_random_goat_img_url_for_list_page() . '" alt="' . $story_title . '">';
                                            }
                                            echo '<h4>' . $story_title . '</h4>';
                                            echo '<h5>' . $story_desc . '</h5>';

                                            echo '<a href="#" class="open-story-popup-author-page open-story-popup-author-page-content" data-title="' . $story_title . '" data-story-id="' . $story . '">
                                                    <span class="dkpdf-button-icon">
                                                        <i class="fa-brands fa-readme"></i>
                                                    </span>
                                                    &nbsp;&nbsp;&nbsp;Read this story
                                                </a>';

                                            echo '<p><a href="' . $story_author_posts_url . '"><m>By: <em>' . ucwords($story_author_name) . '</em></m></a></p>';

                                            echo '</li>';
                                        }
                                    }
                                    echo '</ul>';
                                } else {
                                    echo '<h6>No stories found</h6>';
                                }

                                ?>
                            </div>
                        </div>
                    </div>


                    <div id="tab-workshops" class="author-tab-content">
                        <h1>Workshops</h1>

                        <?php


                        $cust_args = array(
                            'post_type' => 'workshop',
                            'meta_query' => array(
                                array(
                                    'key' => 'signups',
                                    'value' => '%:' . $current_author_id . ';%',
                                    'compare' => 'LIKE'
                                )
                            )
                        );
                        $cust_wkp = new WP_Query($cust_args);
                        // echo "<div style='display:none;'>------------------------------";
                        // print_r($cust_wkp);
                        $curr_user_is_admin = in_array('administrator', wp_get_current_user()->roles);
                        // echo "</div>";
                        if ($curr_user_is_admin) { ?>

                            <!-- <button class="show-hide-workshop-form" data-visibility="hidden">Add new workshop</button> -->

                            <input id="author-page-curr-user" type="hidden"
                                value="<?php echo wp_get_current_user()->ID; ?>">
                            <input id="author-page-curr-author" type="hidden" value="<?php echo $current_author_id; ?>">

                            <!-- form that adds new workshop to usermeta -->
                            <!-- <form id="add-new-workshop">
                                <input type="text" id="workshop-title" name="workshop_title" placeholder="Workshop Title"
                                    required>
                                <textarea id="workshop-details" name="workshop_details"> </textarea>
                                <input type="text" id="workshop-link" name="workshop_link" placeholder="Link" required>
                                <input type="submit" id="workshop-form" value="Add Workshop">
                            </form> -->
                            <?php
                        }

                        //display all worshops the user is a part of
                        $workshops = get_user_meta($current_author_id, 'workshops', true);
                        $workshops = is_array($workshops) ? $workshops : [];

                        echo '<span class="workshop-msg"></span>';
                        // echo '<ca id="worshop-table">';
                        // echo '<tr>';
                        // echo '<th>Title</th>';
                        // echo '<th>Details</th>';
                        // echo '<th>Link</th>';
                        // echo '<th>Actions</th>';
                        // echo '</tr>';
                        
                        if (sizeof($workshops) > 0) {

                            

                            echo '<div class="all-workshops">';
                            foreach ($workshops as $workshop) {

                                // if(get_current_user_id() == 14){  var_dump($workshop); }
                                
                                if(!get_post_status((int)$workshop)){
                                    continue;
                                }

                                $date_time_meta = new DateTime(get_post_meta((int)$workshop, 'workshop-date-time', true));
                                $currentDate = new DateTime();

                                // if(get_current_user_id() == 14){
                                //     echo '<br>';
                                //     echo '<br>';
                                //     var_dump($date_time_meta);
                                //     echo '<br>';
                                //     echo '<br>';
                                //     var_dump($currentDate);
                                //     echo '<br>';
                                //     echo '<br>';
                                //     var_dump($date_time_meta->diff($currentDate));
                                //     echo '<br>';
                                //     echo '<br>';
                                //     // echo $workshop.',';
                                // }
                                
                                //skip if the workshop has not happened yet
                                if ($date_time_meta > $currentDate) {
                                    continue;
                                }

                                // if(get_current_user_id() == 14){
                                //     echo $workshop.',';
                                //     echo '=======';
                                // }

                                //skip if the time interval between current date and the date of the workshop 
                                //is not greater than 3 hours
                                $interval = $date_time_meta->diff($currentDate);
                                // if(get_current_user_id() == 14){
                                //     echo $workshop.':::';
                                //     var_dump($currentDate);
                                //     echo '<br>';
                                //     var_dump( $date_time_meta);
                                //     echo '<br>';
                                //     // var_dump($currentDate->diff($date_time_meta));
                                //     echo $interval->h + ($interval->days*24);
                                //     echo '<br>';
                                // }
                                $hours_interval = $interval->h + ($interval->days*24);
                                if ($hours_interval < 3) {
                                    continue;
                                }
                                
                                if(get_post_field('post_status', $workshop) != 'publish'){
                                    continue;
                                }
                                
                                // if(get_current_user_id() == 14){
                                //     echo $workshop.',';
                                // }

                                $title = get_post_field('post_title', (int) $workshop);
                                $excerpt = get_post_field('post_excerpt', (int) $workshop);
                                $permalink = get_the_permalink((int) $workshop);
                                echo '<div class="workshop-table">';
                                echo '<div class="workshop-card-title">' . $title . '</div>';
                                echo '<div class="workshop-card-detail-content">' . $excerpt . '</div>';
                                echo '<div><a href="' . $permalink . '" target="_blank">View Workshop</a></div>';
                                // if ($curr_user_is_admin) {
                                //     echo '<div><button class="delete-workshop" data-title="' . $title . '">Delete</button></div>';
                                // }
                                echo '</div>';
                            }
                            echo '</div>';
                        } else {
                            echo 'The author has not yet been a part of any workshops';
                        }

                        ?>

                    </div>
                    <?php
                    $curr_user_is_admin = in_array('administrator', wp_get_current_user()->roles);

                    $user_role = 'user';
                    if (get_user_meta($current_user->ID, 'rae_approved', true) == 1) {
                        $user_role = 'rae';
                    } else if (in_array('administrator', (array) $current_user->roles)) {
                        $user_role = 'admin';
                    }

                    // if ($curr_user_is_admin || $current_author_id == $current_user->ID) { 
                    if ( $user_role == 'rae') {

                        echo '<h1 id="commissions-profile-page" style="margin-top: 3rem">Author\'s Commissions</h1>';
                        echo '<p class="profile-available-commision-text profile-paragraph">These are commissions currently allocated to you.</p>';
                        if ($current_user->ID != $current_author_id) {
                            global $wpdb;  // Access the WordPress database object
                            // Replace with your dynamic value
                            $table_name = $wpdb->prefix . 'commission';  // Table name with prefix

                            // Prepare the SQL query
                            $query = $wpdb->prepare(
                                "SELECT COUNT(*) FROM $table_name WHERE `status` = %d AND `current_owner` = %d",
                                0,  // status
                                $current_author_id  // current_owner
                            );

                            // Execute the query and get the count
                            $commission_count = (int)($wpdb->get_var($query));
                            if ($commission_count <= 0) {
                                echo '<button 
                                        type="button" 
                                        class="styled-button transfer-single-commission"
                                        data-rae-id="' . $current_user->ID . '" 
                                        data-author-id="' . $current_author_id . '"
                                    >
                                        Transfer a commission to this writer
                                </button>';
                            }
                        }
                        echo '<div id="comission_table">';
                        echo list_user_commisions($current_author_id);
                        echo '</div>';
                     } ?>
                </div>
            </div>
        </div>
    </div>
</main>

<?php 




get_footer();