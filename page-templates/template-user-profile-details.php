<?php

/**
 * Template Name: Publishing User Information
 *
 * Displays The User Information.
 *
 * @package GOAT PoL
 */

acf_form_head();
get_header();

// if (get_current_user_id() == 14) {
//     echo 'hello00000';
//     $blogusers = get_users(array('fields' => array('id')));
//     global $wpdb;
//     $table_name = $wpdb->prefix . 'commission';
// $RR=  0;
//     // Array of stdClass objects.
//     foreach ($blogusers as $user) {
//         // echo "SELECT count(id) FROM {$table_name} WHERE current_owner = $user->id AND status != 2 <br>";
//         $current_commissions = $wpdb->get_var("SELECT count(id) FROM {$table_name} WHERE current_owner = $user->id AND status != 2");

//         if ($current_commissions >= 1) {
//             $RR++;
//             update_user_meta($user->id, 'currently_seeking_commission', [0, date("d/m/Y")]);
//         }
//     }
//     echo $RR;
// }

if (is_user_logged_in()) { ?>

    <?php if (get_current_user_id() == 14) { ?>
        <div class="registration-floating-menu">
            <ul>
                <a href="/registration/#cpm-update-user-information">
                    <li class='active'>Public Info</li>
                </a>
                <a href="/registration/#tab-available-works">
                    <li>Upload your writing</li>
                </a>
                <a href="/registration/#acf-form">
                    <li>Private Details</li>
                </a>
                <a href="/registration/#commissions-profile-page">
                    <li>Available Commissions</li>
                </a>
            </ul>
            <p>To save any changes first click <span class="update-user-info">Update</span> then click <span class="save-user-info">Save</span></p>
        </div>
    <?php } ?>

    <main id="site-content" role="main">
        <div id="post-content" class="section-inner">
            <div class="section-inner">
                <div class="entry-content edit-user-innfo">
                    <?php
                    $current_user = wp_get_current_user();
                    $current_user_id = get_current_user_id();

                    //get current user role
                    $user_role = 'user';
                    if (get_user_meta($current_user_id, 'rae_approved', true) == 1) {
                        $user_role = 'rae';
                    } else if (in_array('administrator', (array) $current_user->roles)) {
                        $user_role = 'admin';
                    }

                    if ($user_role == 'admin') {
                        if (isset($_GET['id']) && !empty($_GET['id'])) {
                            $user_role = 'user';
                            $current_user_id = (int) $_GET['id'];
                            $current_user = get_user_by('id', $current_user_id);
                            if (get_user_meta($current_user_id, 'rae_approved', true) == 1) {
                                $user_role = 'rae';
                            } else if (in_array('administrator', (array) $current_user->roles)) {
                                $user_role = 'admin';
                            }
                        }
                    }

                    $has_updated_contributors_page = get_user_meta($current_user_id, 'has_updated_contributors_page', true);
                    if (gettype($has_updated_contributors_page) == 'boolean' || (int) $has_updated_contributors_page != 1) {
                        $has_updated_contributors_page = false;
                    } else {
                        $has_updated_contributors_page = true;
                    } ?>

                    <div class="view-edit-info-wrapper">
                        <div class="view-edit-info">
                            <?php echo $has_updated_contributors_page ? '<a class="styled-button" href="' . get_author_posts_url($current_user_id) . '">VIEW</a>' : ''; ?>
                            <a class="styled-button active" href="#">EDIT</a>
                        </div>
                        <p>To see your payment details and <br> available commissions please choose “EDIT”</p>
                    </div>
                    <?php if (!$has_updated_contributors_page) { ?>
                        <p class="profile-paragraph">
                            Welcome to The GOAT PoL. By answering the questions below
                            you’ll create your own account and Contributors Page.
                            This allows you to take part in all of our <strong>free services</strong>:
                            including <strong>uploading your writing to share with others</strong>;
                            reading and <strong>selecting favorites</strong> from among the work uploaded by other writers;
                            <strong>joining our group workshops</strong> online with other writers from around the world;
                            and working one-on-one with one of our nine Reader/Advisor/Editors (RAEs)
                            to <strong>develop and publish your writing on The GOAT PoL</strong>, and be paid for it.
                            We also award money to support some writers working on books, and we’ll
                            <strong>publish several books a year</strong> from The GOAT PoL writers (see the menu on our
                            homepage).
                            Please be honest and thoughtful in your responses. All visitors to The GOAT PoL
                            will have access to your Contributor’s Page, so present yourself as you wish to be seen.
                           <!-- Please be honest and thoughtful in your responses, and <strong>don’t forget to upload some
                                of your writing</strong>—pieces you wish to share publicly. All visitors to The GOAT PoL
                            will have access to your Contributor’s Page, so present yourself as you wish to be seen.
                            -->
                        </p>
                        <h5>
                            <u>
                                Answer the questions below to create your Contributor’s Page:
                            </u>
                        </h5>
                    <?php } ?>

                    <?php

                    if (isset($_POST['cpm_update_user_info'])) {

                        // echo "<div style='display:none;'>";
                        // print_r($_POST);
                        // echo "</div>";

                        $first_name = $_POST['first_name'];
                        $last_name = $_POST['last_name'];
                        // $user_address = $_POST['user_address'];


                        $grew_up_location = $_POST['grew_up_location'];
                        // $grew_up_location_city = $_POST['grew_up_location_city'];
                        $grew_up_location_nation = $_POST['grew_up_location_nation'];
                        $current_location = $_POST['current_location'];
                        // $current_location_city = $_POST['current_location_city'];
                        $current_location_nation = $_POST['current_location_nation'];
                        $grew_up_languages = isset($_POST['grew_up_languages']) ? $_POST['grew_up_languages'] : [];
                        $write_read_languages = isset($_POST['write_read_languages']) ? $_POST['write_read_languages'] : [];
                        $write_genres = isset($_POST['write_genres']) && !empty($_POST['write_genres']) ? $_POST['write_genres'] : [];
                        $write_for = isset($_POST['write_for']) && !empty($_POST['write_for']) ? $_POST['write_for'] : [];
                        $reason_for_writing = isset($_POST['reason_for_writing']) && !empty($_POST['reason_for_writing']) ? $_POST['reason_for_writing'] : '';
                        $fav_authors = isset($_POST['fav_authors']) ? $_POST['fav_authors'] : [];
                        $fav_goatpol_stories = isset($_POST['fav_goatpol_stories']) ? $_POST['fav_goatpol_stories'] : [];
                        $fav_subject_to_write_about = isset($_POST['fav_subject_to_write_about']) ? $_POST['fav_subject_to_write_about'] : [];
                        $difficult_writing_part = $_POST['difficult_writing_part'];
                        $rewarding_moment = $_POST['rewarding_moment'];
                        //$profile_picture = $_POST['profile_picture'];
                        $file = 'profile_picture';
                        // $profile_picture_id = 0;

                        if (!function_exists('wp_generate_attachment_metadata')) {
                            require_once(ABSPATH . "wp-admin" . '/includes/image.php');
                            require_once(ABSPATH . "wp-admin" . '/includes/file.php');
                            require_once(ABSPATH . "wp-admin" . '/includes/media.php');
                        }
                        // var_dump($_FILES);
                        $profile_picture = '';
                        if (isset($_FILES['profile_picture']) && !empty($_FILES['profile_picture']) && $_FILES['profile_picture']['size'] != 0) {
                            foreach ($_FILES as $file => $array) {
                                if ($_FILES[$file]['error'] !== UPLOAD_ERR_OK) {
                                    wp_die("upload error : " . $_FILES[$file]['error']);
                                } //If upload error
                                $profile_picture_id = media_handle_upload($file, $new_post);
                                $profile_picture = wp_get_attachment_url($profile_picture_id); //upload file URL
                            }
                        }

                        // print_r('bbbb');
                        $user_id = $current_user->ID;
                        $user_data = wp_update_user([
                            'ID' => $user_id,
                            'first_name' => $first_name,
                            'last_name' => $last_name,
                            'display_name' => $first_name . ' ' . $last_name
                        ]);

                        // print_r('cccc');
                        if (!is_wp_error($user_data)) {
                            if ($profile_picture != '') {
                                update_user_meta($user_id, 'profile_picture', $profile_picture);
                            }
                            update_user_meta($user_id, 'grew_up_location', $grew_up_location);
                            // update_user_meta($user_id, 'grew_up_location_city', $grew_up_location_city);
                            update_user_meta($user_id, 'grew_up_location_nation', $grew_up_location_nation);
                            update_user_meta($user_id, 'current_location', $current_location);
                            // update_user_meta($user_id, 'current_location_city', $current_location_city);
                            update_user_meta($user_id, 'current_location_nation', $current_location_nation);
                            update_user_meta($user_id, 'grew_up_languages', $grew_up_languages);
                            update_user_meta($user_id, 'write_read_languages', $write_read_languages);
                            update_user_meta($user_id, 'write_genres', $write_genres);
                            update_user_meta($user_id, 'write_for', $write_for);
                            update_user_meta($user_id, 'reason_for_writing', $reason_for_writing);
                            update_user_meta($user_id, 'fav_authors', $fav_authors);
                            update_user_meta($user_id, 'fav_goatpol_stories', $fav_goatpol_stories);
                            update_user_meta($user_id, 'fav_subject_to_write_about', $fav_subject_to_write_about);
                            update_user_meta($user_id, 'difficult_writing_part', $difficult_writing_part);
                            update_user_meta($user_id, 'rewarding_moment', $rewarding_moment);
                            if (!$has_updated_contributors_page) {
                                cpm_send_cp_update_email($current_user->user_email, $user_name);
                            }
                            update_user_meta($user_id, 'has_updated_contributors_page', 1);

                            ?>
                            <div class="user_success_message">
                                Your information has been updated!
                            </div>
                            <script>
                                window.location.href = "<?php echo home_url('/registration#private-info-heading'); ?>"
                            </script>
                        <?php
                        } else {
                        ?>
                            <div class="user_unsuccess_message">
                                <?php
                                echo $user_data->get_error_message();
                                ?>
                            </div>
                    <?php
                        }
                    }
                    ?>


                    <div id="form-err"></div>
                    <form action="" method="POST" enctype="multipart/form-data" id="cpm-update-user-information" class="cpm-user-informations">
                        <?php
                        $current_user = get_user_by('id', $current_user_id);
                        $user_id = $current_user->ID;
                        /*
                        $profile_picture = get_user_meta($user_id, 'profile_picture', true);
                        $profile_picture_url = $profile_picture != '' ? wp_get_attachment_url((int) $profile_picture) : 'https://secure.gravatar.com/avatar/4eb8082fa51c1f2b580638fd1e3c68ae?s=96&d=mm&r=g';
                        */
                        // $avatar_img_url = '';
                        // $profile_picture = get_user_meta($user_id, 'profile_picture', true);
                        // if ($profile_picture != "") {
                        //     $avatar_img_url = wp_get_attachment_url($profile_picture);
                        // } else {
                        //     $gravatar = get_avatar($q);
                        //     if ($gravatar == 0) {
                        //         if ($profile_picture != '') {
                        //             $avatar_img_url = wp_get_attachment_url($profile_picture);
                        //         } else {
                        //             $avatar_img_url = get_avatar_url($current_user_id);
                        //         }
                        //     } else {
                        //         $avatar_img_url = get_avatar_url($current_user_id);
                        //     }
                        // }
                        $grew_up_location = get_user_meta($user_id, 'grew_up_location', true);
                        // $grew_up_location_city = get_user_meta($user_id, 'grew_up_location_city', true);
                        $grew_up_location_nation = get_user_meta($user_id, 'grew_up_location_nation', true);
                        $current_location = get_user_meta($user_id, 'current_location', true);
                        // $current_location_city = get_user_meta($user_id, 'current_location_city', true);
                        $current_location_nation = get_user_meta($user_id, 'current_location_nation', true);
                        $grew_up_languages = get_user_meta($user_id, 'grew_up_languages', true);
                        $write_read_languages = get_user_meta($user_id, 'write_read_languages', true);
                        $write_genres = get_user_meta($user_id, 'write_genres', true);
                        $write_genres = is_array($write_genres) ? $write_genres : [];
                        $write_for = get_user_meta($user_id, 'write_for', true);
                        $write_for = is_array($write_for) ? $write_for : [];
                        $reason_for_writing = get_user_meta($user_id, 'reason_for_writing', true);
                        $fav_authors = get_user_meta($user_id, 'fav_authors', true);
                        $fav_goatpol_stories = get_user_meta($user_id, 'fav_goatpol_stories', true);
                        $fav_subject_to_write_about = get_user_meta($user_id, 'fav_subject_to_write_about', true);
                        $difficult_writing_part = get_user_meta($user_id, 'difficult_writing_part', true);
                        $rewarding_moment = get_user_meta($user_id, 'rewarding_moment', true);



                        ?>
                        <div class=" form-group update_profile_picture">
                            <label for="profile_picture">Profile Picture</label>
                            <img src="<?php
                                        // if(get_current_user_id() == 14){
                                        // echo pol_get_user_profile_img_2((int) $user_id);
                                        // }else{
                                        echo pol_get_user_profile_img((int) $user_id);
                                        // }

                                        ?>" alt="profile picture" width="80" class="avatar avatar-80 photo">
                            <input type="file" class="profile_picture" name="profile_picture" accept="image/*">
                        </div>
                        <div class='form-infos'>
                            <div class="form-group">
                                <label for="first_name">First Name<span class="required-field">*</span></label>
                                <input type="text" class="first_name" name="first_name" value="<?php
                                                                                                if (!empty($current_user->first_name)) {
                                                                                                    echo $current_user->first_name;
                                                                                                }
                                                                                                ?>" required>
                            </div>
                            <div class="form-group">
                                <label for="last_name">Last Name<span class="required-field">*</span></label>
                                <input type="text" class="last_name" name="last_name" value="<?php
                                                                                                if (!empty($current_user->last_name)) {
                                                                                                    echo $current_user->last_name;
                                                                                                }
                                                                                                ?>" required>
                            </div>


                            <div class="form-group">
                                <label for="grew_up_location">Where did you grow up?</label>
                                <div class="user-full-location">
                                    <div class="user-full-location-fields">
                                        <div>
                                            <label for="grew_up_location_city">City, town, or place name:</label>
                                            <input type="text" class="grew_up_location" name="grew_up_location" value="<?php
                                                                                                                        if (!empty($grew_up_location)) {
                                                                                                                            echo ucwords($grew_up_location);
                                                                                                                        }
                                                                                                                        ?>">
                                        </div>

                                        <div>
                                            <label for="grew_up_location_nation">Nation:</label>
                                            <select name="grew_up_location_nation" class="current_location fill-nation-grewup ">
                                                <option value="">Select nation</option>
                                                <?php
                                                $nations = pol_get_nations();
                                                foreach ($nations as $nation) {
                                                    $is_selected = '';
                                                    if (!empty($grew_up_location_nation) && ucwords($nation) == ucwords($grew_up_location_nation)) {
                                                        $is_selected = 'selected';
                                                    }
                                                    echo '<option value="' . $nation . '" ' . $is_selected . '>' . $nation . '</option>';
                                                }
                                                ?>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="form-group ">
                                <label for="current_location">Where do you live now?</label>
                                <div class="user-full-location">
                                    <div class="user-full-location-fields">
                                        <div>
                                            <label for="current_location_city">City, town, or place name:</label>

                                            <input type="text" class="current_location" name="current_location" placeholder="" value="<?php
                                                                                                                                        if (!empty($current_location)) {
                                                                                                                                            echo ucwords($current_location);
                                                                                                                                        }
                                                                                                                                        ?>">
                                        </div>

                                        <div>
                                            <label for="current_location_nation">Nation:</label>
                                            <select name="current_location_nation" class="current_location fill-nation-now ">
                                                <option value="">Select nation</option>
                                                <?php
                                                foreach ($nations as $nation) {
                                                    $is_selected = '';
                                                    if (!empty($current_location_nation) && ucwords($nation) == ucwords($current_location_nation)) {
                                                        $is_selected = 'selected';
                                                    }
                                                    echo '<option value="' . $nation . '" ' . $is_selected . '>' . $nation . '</option>';
                                                }
                                                ?>
                                            </select>
                                        </div>
                                    </div>

                                </div>
                            </div>

                            <div class="form-group">

                                <label for="grew_up_languages">What languages did you grow up with?</label>
                                <select class="grew_up_languages" name="grew_up_languages[]" multiple="multiple">
                                    <?php
                                    $grew_up_languages = is_array($grew_up_languages) ? $grew_up_languages : [];
                                    foreach ($grew_up_languages as $lang) {
                                        echo '<option value="' . $lang . '" selected>' . ucwords($lang) . '</option>';
                                    }
                                    ?>
                                </select>
                            </div>

                            <div class="form-group">
                                <label for="write_read_languages">What languages do you write and read in?</label>

                                <select class="write_read_languages" name="write_read_languages[]" multiple="multiple">
                                    <?php
                                    $write_read_languages = is_array($write_read_languages) ? $write_read_languages : [];
                                    foreach ($write_read_languages as $lang) {
                                        echo '<option value="' . $lang . '" selected>' . ucwords($lang) . '</option>';
                                    }
                                    ?>
                                </select>
                            </div>

                            <div class="form-group">
                                <label for="write_genres">What genres do you write ? (choose as many as are true)</label>

                                <select name="write_genres[]" class="write_genres" multiple="multiple">
                                    <option value="poetry" <?php echo (in_array('poetry', $write_genres)) ? 'selected' : ''; ?>>
                                        Poetry</option>
                                    <option value="prose" <?php echo (in_array('prose', $write_genres)) ? 'selected' : ''; ?>>
                                        Prose</option>
                                    <option value="fiction" <?php echo (in_array('fiction', $write_genres)) ? 'selected' : ''; ?>>
                                        Fiction</option>
                                    <option value="novels" <?php echo (in_array('novels', $write_genres)) ? 'selected' : ''; ?>>
                                        Novels</option>
                                    <option value="short_stories" <?php echo (in_array('short_stories', $write_genres)) ? 'selected' : ''; ?>>Short Stories</option>
                                    <option value="diary" <?php echo (in_array('diary', $write_genres)) ? 'selected' : ''; ?>>
                                        Diary</option>
                                    <option value="journalism" <?php echo (in_array('journalism', $write_genres)) ? 'selected' : ''; ?>>
                                        Journalism</option>
                                    <option value="essays" <?php echo (in_array('essays', $write_genres)) ? 'selected' : ''; ?>>
                                        Long Form Essays</option>
                                    <option value="play_scripts" <?php echo (in_array('play_scripts', $write_genres)) ? 'selected' : ''; ?>>Play Scripts</option>
                                    <option value="film_scripts" <?php echo (in_array('film_scripts', $write_genres)) ? 'selected' : ''; ?>>Film Scripts</option>
                                    <option value="love_letters" <?php echo (in_array('love_letters', $write_genres)) ? 'selected' : ''; ?>>Love Letters</option>
                                    <option value="manifestos" <?php echo (in_array('manifestos', $write_genres)) ? 'selected' : ''; ?>>
                                        Manifestos</option>
                                    <option value="songs" <?php echo (in_array('songs', $write_genres)) ? 'selected' : ''; ?>>
                                        Songs</option>
                                    <option value="other" <?php echo (in_array('other', $write_genres)) ? 'selected' : ''; ?>>
                                        Other</option>
                                </select>
                            </div>

                            <div class="form-group">
                                <label for="write_for">Who do you write for, currently? (choose as many as are true)</label>
                                <select name="write_for[]" class="write_for" multiple="multiple">
                                    <option value="school" <?php echo (in_array('school', $write_for)) ? 'selected' : ''; ?>>I
                                        write for school</option>
                                    <option value="friends" <?php echo (in_array('friends', $write_for)) ? 'selected' : ''; ?>>I
                                        write for my friends</option>
                                    <option value="private" <?php echo (in_array('private', $write_for)) ? 'selected' : ''; ?>>I
                                        write only for myself, privately</option>
                                    <option value="public_online" <?php echo (in_array('public_online', $write_for)) ? 'selected' : ''; ?>>I write publicly online</option>
                                    <option value="paid_newspaper" <?php echo (in_array('paid_newspaper', $write_for)) ? 'selected' : ''; ?>>I'm paid to write for a newspaper or journal</option>
                                    <option value="paid_books" <?php echo (in_array('paid_books', $write_for)) ? 'selected' : ''; ?>>I'm
                                        paid to write books</option>
                                    <option value="children" <?php echo (in_array('children', $write_for)) ? 'selected' : ''; ?>>I write
                                        for children</option>
                                    <option value="unborn_readers" <?php echo (in_array('unborn_readers', $write_for)) ? 'selected' : ''; ?>>I write for readers as-yet unborn</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label for="reason_for_writing">Why do you write?</label>
                                <input type="text" class="reason_for_writing" name="reason_for_writing" value="<?php
                                                                                                                if (!empty($reason_for_writing)) {
                                                                                                                    echo $reason_for_writing;
                                                                                                                }
                                                                                                                ?>">
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="fav_authors">Which authors do you like to read?</label>
                            <select class="fav_authors" name="fav_authors[]" multiple="multiple">
                                <?php
                                $fav_authors = is_array($fav_authors) ? $fav_authors : [];
                                foreach ($fav_authors as $author) {
                                    echo '<option value="' . $author . '" selected>' . ucwords($author) . '</option>';
                                }
                                ?>
                            </select>
                        </div>


                        <div class="form-group">
                            <label for="fav_goatpol_stories">Which stories on The GOAT PoL did you enjoy reading most?
                                (choose up to four by title)</label>
                            <select class="fav_goatpol_stories" name="fav_goatpol_stories[]" multiple="multiple">
                                <?php
                                $all_goatpol_stories = pol_get_all_stories();
                                $all_goatpol_stories = is_array($all_goatpol_stories) ? $all_goatpol_stories : [];
                                foreach ($all_goatpol_stories as $story) {
                                    $is_selected = '';
                                    if (is_array($fav_goatpol_stories)) {
                                        $is_selected = in_array($story['sid'], $fav_goatpol_stories) ? 'selected' : '';
                                    }
                                    echo '<option value="' . $story['sid'] . '" ' . $is_selected . '>' . ucwords($story['label']) . '</option>';
                                }
                                ?>
                            </select>
                        </div>

                        <div class="form-group">
                            <label for="fav_subject_to_write_about">What subjects do you enjoy writing about?</label>
                            <select class="fav_subject_to_write_about" name="fav_subject_to_write_about[]" multiple="multiple">
                                <?php
                                $fav_subject_to_write_about = is_array($fav_subject_to_write_about) ? $fav_subject_to_write_about : [];
                                foreach ($fav_subject_to_write_about as $subject) {
                                    echo '<option value="' . $subject . '" selected>' . ucwords($subject) . '</option>';
                                }
                                ?>
                            </select>
                        </div>

                        <div class="form-group">
                            <label for="difficult_writing_part">What is the most difficult part about writing for
                                you?</label>
                            <input type="text" class="difficult_writing_part" name="difficult_writing_part" value="<?php
                                                                                                                    if (!empty($difficult_writing_part)) {
                                                                                                                        echo ($difficult_writing_part);
                                                                                                                    }
                                                                                                                    ?>">
                        </div>

                        <div class="form-group">
                            <label for="rewarding_moment">What was your favourite or most rewarding moment as a writer?
                            </label>
                            <input type="text" class="rewarding_moment" name="rewarding_moment" value="<?php
                                                                                                        // var_dump($rewarding_moment);
                                                                                                        if (!empty($rewarding_moment)) {
                                                                                                            // echo 'hello';
                                                                                                            echo wp_specialchars($rewarding_moment);
                                                                                                        }
                                                                                                        ?>">
                        </div>




                        <div class="wp-block-buttons styled-button-with-note" style="<?php
                                                                                        if (!$has_updated_contributors_page) {
                                                                                            echo 'display:none';
                                                                                        }
                                                                                        ?>">
                            <button type="submit" class="wp-block-button wp-block-button__link cpm_update_user_info" name="cpm_update_user_info">Save Public Profile</button>
                            <p class="info-to-save-cp">
                                <b>NOTE:</b> To update your Contributor's Page <br>
                                (1) save your public profile now; and <br>
                                (2) confirm your private information below, then press "Update" below
                            </p>
                        </div>
                    </form>
                    <?php if (!$has_updated_contributors_page) { ?>
                        <div class="wp-block-buttons">
                            <button class="wp-block-button wp-block-button__link cpm_update_user_open_ground_rules">Save public
                                profile</button>
                        </div>
                    <?php } ?>




                    <?php echo do_shortcode('[upload_stories user_id="' . $current_user_id . '" page="profile" ]'); ?>




                    <h4 style="margin-top: 10rem;" id="private-info-heading">The information you enter below is private. It will only be seen by you
                        and The GOAT PoL admin.</h4>
                    <p class="profile-paragraph">
                        When you work with us to publish your writing on The GOAT PoL we will pay you.
                        <strong>
                            NOTE: we do NOT pay for pieces that you upload on your Contributor’s Page (see “Upload Your
                            Work,” below). </strong>
                        But, <strong>when a RAE commissions you to work with us and publish on The GOAT PoL, we will pay you
                            $70 CAD.
                            The “payment details” you provide below show us how to pay you. Be sure to fill them in if you
                            want to be paid for your work.
                        </strong>
                    </p>

                    <?php if (have_posts()) {
                        // echo '<pre>';
                        // var_dump(acf_get_fields('group_62da1f1ae94a0'));
                        // echo '</pre>';
                        while (have_posts()) :
                            the_post(); ?>
                            <article id="post-<?php the_ID() ?>" <?php post_class(); ?>>
                                <?php the_content(''); ?>
                                <?php
                                if (is_user_logged_in()) {
                                    $current_user_id = get_current_user_id();
                                    // Define the field keys you want to exclude
                                    $excluded_fields = array('field_62da3f3892f02', 'field_643ec214b35d2');

                                    // Retrieve the fields of the field group
                                    $fields = acf_get_fields('group_62da1f1ae94a0');

                                    // Filter out the excluded fields
                                    $filtered_fields = array();
                                    if ($fields) {
                                        foreach ($fields as $field) {
                                            if (!in_array($field['key'], $excluded_fields)) {
                                                $filtered_fields[] = $field['key'];
                                            }
                                        }
                                    }

                                    // var_dump($filtered_fields);
                                    acf_form([
                                        'field_groups' => ['group_62da1f1ae94a0'],
                                        'post_id' => 'user_' . $current_user_id,
                                        'fields' => $filtered_fields,
                                    ]);
                                }
                                ?>
                            </article>
                    <?php endwhile;
                    }
                    ?>



                    <!-- Available commissions -->
                    <!-- <div id="commissions-profile-page"></div> -->
                    <h4 id="commissions-profile-page" style="margin-top: 3rem">Available Commissions</h4>
                    <p class="profile-available-commision-text profile-paragraph">

                        The short codes below are “commissions.” They are given by the RAEs to writers they want to work with.
                        One commission is required when you submit new work to develop it with a RAE for publication.
                        Highlight and “copy” the code, then paste it in where the submission form asks you for a commission.
                    </p>

                    <div id="commission-request-confirmation-popup" class="modal" style="position: fixed; top: 50%; left: 40%;">
                        <a href="#close-modal" rel="modal:close" class="close-modal">Close</a>
                        <div style="text-align:center;">
                            <span class="request-commission-msg"></span>
                        </div>
                    </div>

                    <?php

                    // $current_user_id = get_current_user_id();
                    // $current_user = wp_get_current_user();


                    // Get all users except the current user
                    $users = get_users(
                        array(
                            'exclude' => array($current_user_id)
                        )
                    );

                    $contributors_seeking_commission = get_users(
                        array(
                            'meta_query' => array(
                                array(
                                    'key' => 'currently_seeking_commission',
                                    'value' => '{i:0;i:1;',
                                    'compare' => 'LIKE'
                                )
                            )
                        )
                    );

                    // Get the current page number
                    if (get_query_var('paged')) {
                        $paged = get_query_var('paged');
                    } elseif (get_query_var('page')) {
                        $paged = get_query_var('page');
                    } else {
                        $paged = 1;
                    }

                    // Number of codes per page
                    $codes_per_page = 10;

                    // Calculate the offset
                    $offset = ($paged - 1) * $codes_per_page;

                    global $wpdb;
                    $table_name = $wpdb->prefix . 'commission';
                    // if ($user_role == "admin") {
                    //     $results = $wpdb->get_results($wpdb->prepare("SELECT * FROM `$table_name` ORDER BY id LIMIT %d OFFSET %d", $codes_per_page, $offset), ARRAY_A);
                    // } else {
                    //     $actual_results = $wpdb->get_results($wpdb->prepare("SELECT * FROM `$table_name` WHERE current_owner = $current_user_id"), ARRAY_A);
                    //     $results = $wpdb->get_results($wpdb->prepare("SELECT * FROM `$table_name` WHERE current_owner = $current_user_id LIMIT $codes_per_page OFFSET $offset"), ARRAY_A);
                    // }

                    $actual_results = $wpdb->get_results($wpdb->prepare("SELECT * FROM `$table_name` WHERE current_owner = $current_user_id"), ARRAY_A);
                    $results = $wpdb->get_results($wpdb->prepare("SELECT * FROM `$table_name` WHERE current_owner = $current_user_id LIMIT $codes_per_page OFFSET $offset"), ARRAY_A);
                    

                    $user_has_available_commissions = false;
                    if ($results) {


                        echo '<table class="profile-table profile-select-commission-table">';
                        echo '<tr>';
                        echo '<th>Select</th>';
                        echo '<th>Commission</th>';
                        echo '<th>Status</th>';
                        echo '</tr>';
     
                        foreach ($results as $row) {


                            $checkbox_status = '';
                            $status = 'Available';

                            if ($row['status'] == 1) {
                                if ($user_role != 'user') {
                                    $checkbox_status = 'disabled';
                                }
                                if ($user_role == 'user') {
                                    $status = 'Available';
                                    $user_has_available_commissions = true;
                                } else {
                                    $status = 'Allocated';
                                }
                            } else if ($row['status'] == 2) {
                                $checkbox_status = 'disabled';
                                $status = 'In use';
                            }

                            $status = pol_check_if_commission_is_of_published_post($row['code']) ? 'Published' : $status;

                            echo "
                                    <tr>
                                        <td>
                                            <input 
                                            type=\"checkbox\" 
                                            name=\"codes[]\" 
                                            data-id=\"" . esc_attr($row['id']) . "\" 
                                            data-code=\"" . esc_attr($row['code']) . "\"
                                            " . $checkbox_status . "
                                        >
                                        </td>
                                        <td>" . esc_html($row['code']) . "</td>
                                        <td>" . $status . "</td>
                                        
                                    </tr>
                                ";
                        }
                        echo '</table>';


                        // Pagination
                        // if ($user_role == "admin") {
                        //     $total_codes = $wpdb->get_var("SELECT COUNT(*) FROM `$table_name`");
                        //     // $total_codes = $wpdb->get_var("SELECT * FROM `$table_name` WHERE current_owner = $current_user_id");
                        // } else {
                        //     $total_codes = sizeof($actual_results);
                        // }
                        $total_codes = sizeof($actual_results);

                        $total_pages = ceil($total_codes / $codes_per_page);

                        echo '<div class="pagination">';

                        // "First" link
                        echo '<a href="' . add_query_arg('paged', 1) . '"><<</a> ';

                        // "Previous" link
                        if ($paged > 1) {
                            echo '<a href="' . add_query_arg('paged', $paged - 1) . '">' . esc_attr('<Prev') . '</a> ';
                        }

                        // Page numbers
                        for ($i = 1; $i <= $total_pages; $i++) {
                            if ($i == $paged) {
                                // Current page number, not a link
                                echo '<p class="active_page">' . $i . '</p> ';
                            } else {
                                // Page number link
                                echo '<a href="' . add_query_arg('paged', $i) . '">' . $i . '</a> ';
                            }
                        }

                        // "Next" link
                        if ($paged < $total_pages) {
                            echo '<a href="' . add_query_arg('paged', $paged + 1) . '">Next></a> ';
                        }

                        // "Last" link
                        echo '<a href="' . add_query_arg('paged', $total_pages) . '">>></a>';

                        echo '</div>';


                        if(!$user_has_available_commissions){
                            echo '<p class="profile-available-commision-text profile-paragraph profile-commission-not-available-notice"> 
                                Dear ' . $current_user->user_login . ', you currently have no commissions. 
                                New commissions are available from RAEs and sometimes from other writers who have extras. 
                            However, since we have over five-hundred writers and only nine RAEs, you might wait months before a commission is available. 
                            Please don\'t worry or despair—to receive commissions, keep being active—keep reading and taking part in group workshops, if you enjoy them. 
                            Keep writing and please post the writing that you are most proud of to your Contributor’s Page (NOTE: The GOAT PoL does not pay you for posts on the Contributor\'s Page). 
                            Read other writers and let them know if you enjoy their work. Commissions will come to active writers and readers.
                            </p>';
                        }
                    } else {
                        echo '<p class="profile-available-commision-text profile-paragraph profile-commission-not-available-notice"> 

                            Dear  ' . $current_user->user_login . ', you currently have no commissions. 
                            New commissions are available from RAEs and sometimes from other writers who have extras. 
                            However, since we have over five-hundred writers and only nine RAEs, you might wait months before a commission is available. 
                            Please don\'t worry or despair—to receive commissions, keep being active—keep reading and taking part in group workshops, if you enjoy them. 
                            Keep writing and please post the writing that you are most proud of to your Contributor’s Page (NOTE: The GOAT PoL does not pay you for posts on the Contributor\'s Page). 
                            Read other writers and let them know if you enjoy their work. Commissions will come to active writers and readers.
                        </p>';
                    }
                    ?>

                    <p id='edit-profile-submit-notice'>
                        You can also transfer your extra commissions to other writers.
                        Doing this gives the other writer a chance to submit new work.
                        To transfer a commission to another writer:<br>
                        (1) Click to select the commission you want to transfer<br>
                        (2) Select the writer’s name from the drop-down list (below)<br>
                        (3) Click on “transfer.”<br>
                    </p>

                    <div class="form-group profile-assing-to-form">
                        <input type="hidden" id="curr-user-role" value="<?php echo $user_role; ?>">

                        <button class="show-all-contributors active">ALL CONTRIBUTORS</button>
                        <button class="show-contributors-transfer-commmission">COMMISSIONS TRANSFERRED</button>
                        <?php if ($user_role == 'rae' || $user_role == 'admin') { ?>
                            <button class="show-contributors-seeking-commmission">CONTRIBUTORS SEEKING A COMMISSION</button>
                        <?php } ?>

                        <div class="all-contributors">
                            <label for="user_list">Transfer to:</label>
                            <select name="user" id="user_list">
                                <option value="default">Select a user</option>
                                <?php

                                foreach ($users as $user) {
                                    $nom_de_plume = get_user_meta($user->ID, 'nom_de_plume', true);
                                    $username = $user->user_login;
                                    // $full_name = get_user_meta($user->ID, 'first_name', true) . ' ' . get_user_meta($user->ID, 'last_name', true);
                                ?>
                                    <option value="<?php echo esc_attr($user->ID); ?>">
                                        <?php
                                        echo $user->display_name;
                                        ?>
                                    </option>
                                <?php } ?>
                            </select>
                        </div>

                        <?php
                        $gg = '';
                        if ($user_role != 'rae' || $user_role != 'admin') {
                            $gg = "display: none;";
                        }
                        ?>
                        <div class="all-contributors-seeking-commmission" style="<?php echo $gg; ?>">
                            <label for="seeking_commission_list">Contributors currently seeking a commission:</label>
                            <select name="user" id="seeking_commission_list" class="ppppp">
                                <option value="default">Select a user</option>
                                <?php foreach ($contributors_seeking_commission as $contributor) { ?>
                                    <option value="<?php echo esc_attr($contributor->ID); ?>">
                                        <?php
                                        // $nom_de_plume = get_user_meta($contributor->ID, 'nom_de_plume', true);
                                        // $username = $contributor->user_login;
                                        // echo $nom_de_plume != '' ? $nom_de_plume : $username;
                                        echo $contributor->display_name;
                                        ?>
                                    </option>
                                <?php } ?>
                            </select>
                        </div>

                        <button type="button" id="assign_commission">Transfer</button>

                        <div id="assignMessage-container"></div>
                        <div id="assignMessage-container-success"></div>


                        <div class="all-contributors-transfer-commmission" style="<?php //echo $gg; 
                                                                                    ?>">
                            <label for="seeking_commission_list">Contributors you have transferred commission to:</label>
                            <?php
                            // echo 'helloooooo';
                            echo list_transferred_commissions($current_user_id);
                            ?>
                        </div>
                    </div>

                    <?php //}
                    /*********** ******/ ?>
                </div>
            </div>
        </div>
    </main>
<?php } else {
    echo '<h1 class="add-place-no-login">Please login to view your account</h1>';
}
get_footer();
