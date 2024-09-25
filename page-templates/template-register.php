<?php

/**
 * Template Name: Register
 *
 * Allows user to register
 *
 * @package GOAT PoL
 */
get_header();

if (is_user_logged_in()) {
    echo '<script>window.location.href = "/registration";</script>';
}

// if(!isset($_GET['email']) || empty($_GET['email'])){
//     echo '<script>window.location.href = "/map";</script>';
// }

?>

<main id="site-content" role="main">
    <div id="post-content" class="section-inner">
        <div class="section-inner">
            <div class="entry-content">
                <?php

                if (isset($_POST['cpm_create_user'])) {

                    if (
                        isset($_POST['first_name']) && !empty($_POST['first_name']) &&
                        isset($_POST['last_name']) && !empty($_POST['last_name']) &&
                        isset($_POST['user_name']) && !empty($_POST['user_name']) &&
                        isset($_POST['password']) && !empty($_POST['password']) &&
                        isset($_POST['email']) && !empty($_POST['email'])
                    ) {

                        // echo '<pre>';
                        // var_dump($_POST);
                        // echo '</pre>';

                        $first_name = $_POST['first_name'];
                        $last_name = $_POST['last_name'];
                        $user_name = $_POST['user_name'];
                        $password = $_POST['password'];
                        $email = $_POST['email'];

                        $grew_up_location = $_POST['grew_up_location'];
                        // $grew_up_location_city = $_POST['grew_up_location_city'];
                        $grew_up_location_nation = $_POST['grew_up_location_nation'];
                        $current_location = $_POST['current_location'];
                        // $current_location_city = $_POST['current_location_city'];
                        $current_location_nation = $_POST['current_location_nation'];
                        $grew_up_languages = $_POST['grew_up_languages'];
                        $write_read_languages = $_POST['write_read_languages'];
                        $write_genres = isset($_POST['write_genres']) && !empty($_POST['write_genres']) ? $_POST['write_genres'] : [];
                        $write_for = isset($_POST['write_for']) && !empty($_POST['write_for']) ? $_POST['write_for'] : [];
                        $reason_for_writing = isset($_POST['reason_for_writing']) && !empty($_POST['reason_for_writing']) ? $_POST['reason_for_writing'] : '';
                        $fav_authors = $_POST['fav_authors'];
                        $fav_goatpol_stories = $_POST['fav_goatpol_stories'];
                        $fav_subject_to_write_about = $_POST['fav_subject_to_write_about'];
                        $difficult_writing_part = $_POST['difficult_writing_part'];
                        $rewarding_moment = $_POST['rewarding_moment'];

                        //$user_address = $_POST['user_address'];
                        // $cpm_payment_method = $_POST['cpm_payment_method'];
                        // $cpm_payment_details = $_POST['cpm_payment_details'];

                        $uploaded_picture_id = $_POST['uploaded_picture_id'];
                        // var_dump($uploaded_picture_id);

                        $file = 'profile_picture';
                        // $profile_picture_id = 0;

                        if (!function_exists('wp_generate_attachment_metadata')) {
                            require_once(ABSPATH . "wp-admin" . '/includes/image.php');
                            require_once(ABSPATH . "wp-admin" . '/includes/file.php');
                            require_once(ABSPATH . "wp-admin" . '/includes/media.php');
                        }
                        // die('aaaa');
                        if (isset($_FILES[$file]) && !empty($_FILES[$file]) && $_FILES['profile_picture']['size'] != 0) {
                            foreach ($_FILES as $file => $array) {
                                if ($_FILES[$file]['error'] !== UPLOAD_ERR_OK) {
                                    return "upload error : " . $_FILES[$file]['error'];
                                } //If upload error
                                $profile_picture_id = media_handle_upload($file, $new_post);
                                $profile_picture = wp_get_attachment_url($profile_picture_id); //upload file URL
                            }
                        }

                        // Prepare the JSON payload
                        $jsonData = json_encode([
                            "email" => $email,
                            "firstname" => $first_name,
                            "lastname" => $last_name,
                            // "groups" => ["eZVD4w", "b2vAR1"],
                            // "fields" => [
                            //     '{$test_text}' => "Documentation example",
                            //     '{$test_num}' => 8
                            // ],
                            // "phone" => "+370XXXXXXXX",
                            "trigger_automation" => false
                        ]);

                        if (!email_exists($email)) {
                            if (!username_exists($user_name)) {

                                $new_user_id = wp_create_user($user_name, $password, $email);

                                if (!is_wp_error($new_user_id)) {
                                    wp_update_user([
                                        'ID' => $new_user_id,
                                        'first_name' => $first_name,
                                        'last_name' => $last_name
                                    ]);
                                    //  update_user_meta($new_user_id, 'user_address', $user_address);
                                    //  update_user_meta($new_user_id, 'cpm_payment_method', $cpm_payment_method);
                                    //  update_user_meta($new_user_id, 'cpm_payment_details', $cpm_payment_details);

                                    update_user_meta($new_user_id, 'grew_up_location', $grew_up_location);
                                    // update_user_meta($new_user_id, 'grew_up_location_city', $grew_up_location_city);
                                    update_user_meta($new_user_id, 'grew_up_location_nation', $grew_up_location_nation);
                                    update_user_meta($new_user_id, 'current_location', $current_location);
                                    // update_user_meta($new_user_id, 'current_location_city', $current_location_city);
                                    update_user_meta($new_user_id, 'current_location_nation', $current_location_nation);
                                    update_user_meta($new_user_id, 'grew_up_languages', $grew_up_languages);
                                    update_user_meta($new_user_id, 'write_read_languages', $write_read_languages);
                                    update_user_meta($new_user_id, 'write_genres', $write_genres);
                                    update_user_meta($new_user_id, 'write_for', $write_for);
                                    update_user_meta($new_user_id, 'reason_for_writing', $reason_for_writing);
                                    update_user_meta($new_user_id, 'fav_authors', $fav_authors);
                                    update_user_meta($new_user_id, 'fav_goatpol_stories', $fav_goatpol_stories);
                                    update_user_meta($new_user_id, 'fav_subject_to_write_about', $fav_subject_to_write_about);
                                    update_user_meta($new_user_id, 'difficult_writing_part', $difficult_writing_part);
                                    update_user_meta($new_user_id, 'rewarding_moment', $rewarding_moment);
                                    update_user_meta($new_user_id, 'has_updated_contributors_page', 1);

                                    update_user_meta($new_user_id, 'profile_picture', $profile_picture);

                                    cpm_send_cp_update_email($email, $user_name);

                                    // Initialize cURL
                                    $ch = curl_init();

                                    // Set the URL and other options
                                    curl_setopt($ch, CURLOPT_URL, "https://api.sender.net/v2/subscribers");
                                    curl_setopt($ch, CURLOPT_POST, true);  // Set method to POST
                                    curl_setopt($ch, CURLOPT_HTTPHEADER, [
                                        'Authorization: Bearer eyJ0eXAiOiJKV1QiLCJhbGciOiJSUzI1NiJ9.eyJhdWQiOiIxIiwianRpIjoiZjcxNzAwMDhlZjY5MmQ1OGJlM2IzMzk1NDZkODMyMjllZmFjOWQ0MDIxMjEyZjZhOGEzYmZhMDNjMGVlOTYyYzk1YTZjNDJiY2ZmZWE0Y2IiLCJpYXQiOiIxNzI2MTk3MjQ0Ljc4OTMxNyIsIm5iZiI6IjE3MjYxOTcyNDQuNzg5MzIyIiwiZXhwIjoiNDg3OTc5NzI0NC43ODY5NjciLCJzdWIiOiI5MTA2MzAiLCJzY29wZXMiOltdfQ.HydK3VbIeaH38NIWebHf2hWDjc6hVjdAzhopOhukfUXLAEMxQ7OE4sHF-fgoNTP3RGAyHFnq1tg4XkVexloD6A',  // Your token
                                        'Content-Type: application/json',
                                        'Accept: application/json'
                                    ]);
                                    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);  // Get response back as a string
                                    curl_setopt($ch, CURLOPT_POSTFIELDS, $jsonData);  // Attach the JSON data

                                    // Execute the cURL request and fetch the response
                                    $response = curl_exec($ch);

                                    // Check if any error occurred
                                    if (curl_errno($ch)) {
                                        $error_msg = curl_error($ch);
                                        echo "cURL error: $error_msg";
                                    } else {
                                        // Decode the response if successful
                                        $responseData = json_decode($response, true);
                                        if ($responseData['success']) { ?>
                                            <div class="user_success_message">
                                                Your account has been created !!
                                            </div>
                                    <?php }
                                    }

                                    // Close cURL resource
                                    curl_close($ch); ?>
                                    <script>
                                        window.location.href = '/login';
                                    </script>
                                <?php
                                } else {
                                ?>
                                    <div class="user_unsuccess_message">Something went wrong please try agina later..</div>
                                <?php
                                }
                            } else {
                                ?>
                                <div class="user_unsuccess_message">The username already exists try another username.</div>
                            <?php
                            }
                        } else {
                            ?>
                            <div class="user_unsuccess_message">The email has already been registered</div>
                        <?php
                        }
                    } else {
                        ?>
                        <div class="user_unsuccess_message">Please fill all the required fields</div>
                <?php
                    }
                }
                ?>
                <form action="" method="POST" enctype="multipart/form-data" id="cpm-publish-user-information" class="cpm-user-informations">

                    <!-- <h4 class="rs-change-password-title">Register Information</h4> -->
                    <p class='registering-information'>
                        Welcome to The GOAT PoL. By answering the questions below
                        you’ll create your own account and Contributors Page.
                        This allows you to take part in all of our <strong>free services</strong>:
                        including <strong>uploading your writing to share with others</strong>;
                        reading and <strong>selecting favorites</strong> from among the work uploaded by other writers;
                        <strong>joining our group workshops</strong> online with other writers from around the world;
                        and working one-on-one with one of our ten Reader/Advisor/Editors (RAEs)
                        to <strong>develop and publish your writing on The GOAT PoL</strong>, and be paid for it.
                        We also award money to support some writers working on books, and we’ll
                        <strong>publish several books a year</strong> from The GOAT PoL writers (see the menu on our homepage).
                        Please be honest and thoughtful in your responses, and <strong>don’t forget to upload some
                            of your writing</strong>—pieces you wish to share publicly. All visitors to The GOAT PoL
                        will have access to your Contributor’s Page, so present yourself as you wish to be seen.
                    </p>
                    <h5>
                        <u>
                            Answer the questions below to create your Contributor’s Page:
                        </u>
                    </h5>

                    <div class="form-group">
                        <label for="profile_picture">Profile Picture</label>
                        <input type="file" class="profile_picture" name="profile_picture" accept="image/*">
                        <input type="hidden" class="uploaded_picture_id" name="uploaded_picture_id" value="" />
                    </div>
                    <div class='form-infos'>
                        <div class="form-group">
                            <label for="first_name">First Name<span class="required-field">*</span></label>
                            <input type="text" class="first_name" name="first_name" value="<?php echo !empty($_POST['first_name']) ? $_POST['first_name'] : ''; ?>" required>
                        </div>
                        <div class="form-group">
                            <label for="last_name">Last Name<span class="required-field">*</span></label>
                            <input type="text" class="last_name" name="last_name" value="<?php echo !empty($_POST['last_name']) ? $_POST['last_name'] : ''; ?>" required>
                        </div>
                        <div class="form-group">
                            <label for="user_name">Username<span class="required-field">*</span></label>
                            <input type="text" class="user_name" name="user_name" value="<?php echo !empty($_POST['user_name']) ? $_POST['user_name'] : ''; ?>" required>
                        </div>
                        <div class="form-group form-group-password">
                            <label for="password">Password<span class="required-field">*</span></label>
                            <input type="password" class="password" name="password" required>
                            <i class="toggle-password fas fa-eye-slash"></i>
                        </div>
                        <div class="form-group">
                            <label for="email">Email<span class="required-field">*</span></label>
                            <input type="email" name="email" value="<?php echo !empty($_POST['email']) ? $_POST['email'] : ''; ?>" required>
                        </div>

                        <div class="form-group">
                            <label for="grew_up_location">Where did you grow up?</label>
                            <div class="user-full-location">
                                <!-- <input type="text" class="grew_up_location" name="grew_up_location" 
                                value="<?php
                                        // if (!empty($grew_up_location)) {
                                        //     echo ucwords($grew_up_location);
                                        // }
                                        ?>" > -->
                                <!-- <select id="fill-city-nation-grewup">
                                    <option value="city">City</option>
                                    <option value="nation">Nation</option>
                                </select> -->
                                <div>

                                    <label for="current_location_city">City, town, or place name:</label>
                                    <!-- <input type="text" class="current_location fill-city-grewup" name="grew_up_location_city" placeholder="City" value="<?php
                                                                                                                                                                // if (!empty($grew_up_location_city)) {
                                                                                                                                                                //     echo ucwords($grew_up_location_city);
                                                                                                                                                                // }
                                                                                                                                                                ?>"> -->
                                    <input type="text" class="grew_up_location" name="grew_up_location" placeholder="City, town, or place name" value="<?php
                                                                                                                                                        if (!empty($grew_up_location)) {
                                                                                                                                                            echo ucwords($grew_up_location);
                                                                                                                                                        }
                                                                                                                                                        ?>">
                                </div>
                                <div>

                                    <label for="current_location_nation">Nation:</label>
                                    <select name="grew_up_location_nation" class="current_location fill-nation-grewup ">
                                        <option value="">Select nation</option>
                                        <?php
                                        $nations =
                                            [
                                                'Afghanistan',
                                                'Albania',
                                                'Algeria',
                                                'Andorra',
                                                'Angola',
                                                'Antigua and Barbuda',
                                                'Argentina',
                                                'Armenia',
                                                'Australia',
                                                'Austria',
                                                'Azerbaijan',
                                                'The Bahamas',
                                                'Bahrain',
                                                'Bangladesh',
                                                'Barbados',
                                                'Belarus',
                                                'Belgium',
                                                'Belize',
                                                'Benin',
                                                'Bhutan',
                                                'Bolivia',
                                                'Bosnia and Herzegovina',
                                                'Botswana',
                                                'Brazil',
                                                'Brunei',
                                                'Bulgaria',
                                                'Burkina Faso',
                                                'Burundi',
                                                'Cabo Verde',
                                                'Cambodia',
                                                'Cameroon',
                                                'Canada',
                                                'Central African Republic',
                                                'Chad',
                                                'Chile',
                                                'China',
                                                'Colombia',
                                                'Comoros',
                                                'Congo, Democratic Republic of the',
                                                'Congo, Republic of the',
                                                'Costa Rica',
                                                'Côte d’Ivoire',
                                                'Croatia',
                                                'Cuba',
                                                'Cyprus',
                                                'Czech Republic',
                                                'Denmark',
                                                'Djibouti',
                                                'Dominica',
                                                'Dominican Republic',
                                                'East Timor (Timor-Leste)',
                                                'Ecuador',
                                                'Egypt',
                                                'El Salvador',
                                                'Equatorial Guinea',
                                                'Eritrea',
                                                'Estonia',
                                                'Eswatini',
                                                'Ethiopia',
                                                'Fiji',
                                                'Finland',
                                                'France',
                                                'Gabon',
                                                'The Gambia',
                                                'Georgia',
                                                'Germany',
                                                'Ghana',
                                                'Greece',
                                                'Grenada',
                                                'Guatemala',
                                                'Guinea',
                                                'Guinea-Bissau',
                                                'Guyana',
                                                'Haiti',
                                                'Honduras',
                                                'Hungary',
                                                'Iceland',
                                                'India',
                                                'Indonesia',
                                                'Iran',
                                                'Iraq',
                                                'Ireland',
                                                'Israel',
                                                'Italy',
                                                'Jamaica',
                                                'Japan',
                                                'Jordan',
                                                'Kazakhstan',
                                                'Kenya',
                                                'Kiribati',
                                                'Korea, North',
                                                'Korea, South',
                                                'Kosovo',
                                                'Kuwait',
                                                'Kyrgyzstan',
                                                'Laos',
                                                'Latvia',
                                                'Lebanon',
                                                'Lesotho',
                                                'Liberia',
                                                'Libya',
                                                'Liechtenstein',
                                                'Lithuania',
                                                'Luxembourg',
                                                'Madagascar',
                                                'Malawi',
                                                'Malaysia',
                                                'Maldives',
                                                'Mali',
                                                'Malta',
                                                'Marshall Islands',
                                                'Mauritania',
                                                'Mauritius',
                                                'Mexico',
                                                'Micronesia, Federated States of',
                                                'Moldova',
                                                'Monaco',
                                                'Mongolia',
                                                'Montenegro',
                                                'Morocco',
                                                'Mozambique',
                                                'Myanmar (Burma)',
                                                'Namibia',
                                                'Nauru',
                                                'Nepal',
                                                'Netherlands',
                                                'New Zealand',
                                                'Nicaragua',
                                                'Niger',
                                                'Nigeria',
                                                'North Macedonia',
                                                'Norway',
                                                'Oman',
                                                'Pakistan',
                                                'Palau',
                                                'Panama',
                                                'Papua New Guinea',
                                                'Paraguay',
                                                'Peru',
                                                'Philippines',
                                                'Poland',
                                                'Portugal',
                                                'Qatar',
                                                'Romania',
                                                'Russia',
                                                'Rwanda',
                                                'Saint Kitts and Nevis',
                                                'Saint Lucia',
                                                'Saint Vincent and the Grenadines',
                                                'Samoa',
                                                'San Marino',
                                                'Sao Tome and Principe',
                                                'Saudi Arabia',
                                                'Senegal',
                                                'Serbia',
                                                'Seychelles',
                                                'Sierra Leone',
                                                'Singapore',
                                                'Slovakia',
                                                'Slovenia',
                                                'Solomon Islands',
                                                'Somalia',
                                                'South Africa',
                                                'Spain',
                                                'Sri Lanka',
                                                'Sudan',
                                                'Sudan, South',
                                                'Suriname',
                                                'Sweden',
                                                'Switzerland',
                                                'Syria',
                                                'Taiwan',
                                                'Tajikistan',
                                                'Tanzania',
                                                'Thailand',
                                                'Togo',
                                                'Tonga',
                                                'Trinidad and Tobago',
                                                'Tunisia',
                                                'Turkey',
                                                'Turkmenistan',
                                                'Tuvalu',
                                                'Uganda',
                                                'Ukraine',
                                                'United Arab Emirates',
                                                'United Kingdom',
                                                'United States',
                                                'Uruguay',
                                                'Uzbekistan',
                                                'Vanuatu',
                                                'Vatican City',
                                                'Venezuela',
                                                'Vietnam',
                                                'Yemen',
                                                'Zambia',
                                                'Zimbabwe'
                                            ];
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

                        <div class="form-group ">
                            <label for="current_location">Where do you live now?</label>
                            <div class="user-full-location">
                                <!-- <input type="text" class="current_location" name="current_location" placeholder="Location" 
                                value="<?php
                                        // if (!empty($current_location)) {
                                        //     echo ucwords($current_location);
                                        // }
                                        ?>" > -->

                                <!-- <select id="fill-city-nation-now">
                                    <option value="city">City</option>
                                    <option value="nation">Nation</option>
                                </select> -->
                                <div>

                                    <label for="current_location_city">City, town, or place name:</label>
                                    <!-- <input type="text" class="current_location fill-city-now" name="current_location_city" placeholder="City" 
                                value="<?php
                                        // if (!empty($current_location_city)) {
                                        //     echo ucwords($current_location_city);
                                        // }
                                        ?>"> -->
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

                        <div class="form-group">

                            <label for="grew_up_languages">What languages did you grow up with?</label>
                            <select class="grew_up_languages" name="grew_up_languages[]">
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

                            <select class="write_read_languages" name="write_read_languages[]">
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
                                <option value="poetry">Poetry</option>
                                <option value="prose">Prose</option>
                                <option value="fiction">Fiction</option>
                                <option value="novels">Novels</option>
                                <option value="short_stories">Short Stories</option>
                                <option value="diary">Diary</option>
                                <option value="journalism">Journalism</option>
                                <option value="essays">Long Form Essays</option>
                                <option value="play_scripts">Play Scripts</option>
                                <option value="film_scripts">Film Scripts</option>
                                <option value="love_letters">Love Letters</option>
                                <option value="manifestos">Manifestos</option>
                                <option value="songs">Songs</option>
                                <option value="other">Other</option>
                            </select>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="write_for">Who do you write for, currently? (choose as many as are true)</label>
                        <select name="write_for[]" class="write_for" multiple="multiple">
                            <option value="school">I write for school</option>
                            <option value="friends">I write for my friends</option>
                            <option value="private">I write only for myself, privately</option>
                            <option value="public_online">I write publicly online</option>
                            <option value="paid_newspaper">I'm paid to write for a newspaper or journal</option>
                            <option value="paid_books">I'm paid to write books</option>
                            <option value="children">I write for children</option>
                            <option value="unborn_readers">I write for readers as-yet unborn</option>
                            <!-- <option value="other">Other</option> -->
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
                    <div class="form-group">
                        <label for="fav_authors">Which authors do you like to read?</label>
                        <select class="fav_authors" name="fav_authors[]">
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
                        <select class="fav_subject_to_write_about" name="fav_subject_to_write_about[]">
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
                                                                                                                    echo $difficult_writing_part;
                                                                                                                }
                                                                                                                ?>">
                    </div>

                    <div class="form-group">
                        <label for="rewarding_moment">What was your favourite or most rewarding moment as a writer?
                        </label>
                        <input type="text" class="rewarding_moment" name="rewarding_moment" value="<?php
                                                                                                    if (!empty($rewarding_moment)) {
                                                                                                        echo $rewarding_moment;
                                                                                                    }
                                                                                                    ?>">
                    </div>

                    <p class="profile-paragraph">
                        This information will appear in your public profile, a unique Contributor's Page that can be viewed by anyone visiting The GOAT PoL. Your Contributor's Page will also display the stories that you publish at The GOAT PoL and any other writing of yours that you want to upload and share with readers. <strong>To complete your Contributor's Page, please press "save."</strong> The site will take you to your public page. To add stories to your Contributor's Page and to complete the private information needed so that we can work with you and pay you, please choose "Edit" at the top of your screen and scroll down to the "Upload" and "Private Information" sections.



                    </p>

                    <?php /*
                    <h4 style="margin-top: 10rem;">The information you enter below is private. It will only be seen by you
                        and The GOAT PoL admin.</h4>
                    <p class="profile-paragraph">
                        When you work with us to publish your writing on The GOAT PoL we will pay you.
                        NOTE: you can upload any writing you wish to share on your Contributor’s Page
                        (see “Upload Your Work,” below) for free and without payment.
                        But <strong>when you receive a commission from The GOAT PoL and work with us to publish on our site,
                            we will pay you $70 CAD</strong>. The “payment details” you provide below show us how to pay
                        you.
                        Be sure to fill them in if you want to be paid for your work.
                    </p>

                    <div class="form-group">
                        <label for="cpm_payment_method">Payment Method<span class="required-field">*</span></label>
                        <select name="cpm_payment_method" class="cpm_payment_method" required>
                            <option value="">Choose Payment Method</option>
                            <option value="PayPal" <?php if ($_POST['cpm_payment_method'] == "PayPal") {
                                                        echo 'selected="selected"';
                                                    } ?>>PayPal</option>
                            <option value="Western Union" <?php if ($_POST['cpm_payment_method'] == "Western Union") {
                                                                echo 'selected="selected"';
                                                            } ?>>Western Union</option>
                            <option value="World Remit" <?php if ($_POST['cpm_payment_method'] == "World Remit") {
                                                            echo 'selected="selected"';
                                                        } ?>>World Remit</option>
                            <option value="Money Gram" <?php if ($_POST['cpm_payment_method'] == "Money Gram") {
                                                            echo 'selected="selected"';
                                                        } ?>>Money Gram</option>
                            <option value="Other" <?php if ($_POST['cpm_payment_method'] == "Other") {
                                                        echo 'selected="selected"';
                                                    } ?>>Other</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="cpm_payment_details">Payment Details<span class="required-field">*</span></label>
                        <textarea class="cpm_payment_details" name="cpm_payment_details" required><?php echo !empty($_POST['cpm_payment_details']) ? $_POST['cpm_payment_details'] : ''; ?></textarea>
                    </div>
                    <div class="form-group">
                        <label for="user_address">Address</label>
                        <textarea class="user_address" name="user_address"><?php echo !empty($_POST['user_address']) ? $_POST['user_address'] : ''; ?></textarea>
                    </div>



                    <div class="form-group">
                        <h5>Upload Your Writing To Share With Others</h5>
                        <p>
                            Your Contributor’s Page will include links to any original writing that you want to share with the public.
                            The stories will be available to read and for others to select as a “favorite,” which they can link to on
                            their Contributor’s Page. <strong>To upload the stories you want to share, please complete this registration form
                                and press “save” below.</strong> Then open your Contributor’s Page in the “edit” mode (at top right of the Contributor’s Page)
                            and scroll down to “Upload Your Writing To Share With Others”
                        </p>
                    </div>
*/ ?>
                    <div class="wp-block-buttons" style="display: none;">
                        <button class="wp-block-button wp-block-button__link cpm_create_user" name="cpm_create_user">Save</button>
                    </div>
                </form>
                <div class="wp-block-buttons">
                    <button class="wp-block-button wp-block-button__link cpm_create_user_open_ground_rules">Save</button>
                </div>
            </div>
        </div>
    </div>
</main>
<?php
get_footer();
