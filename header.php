<?php

/**
 * The header.
 *
 * @package GOAT PoL
 */




global $current_user;
$user_role = 'user';
if (get_user_meta($current_user->ID, 'rae_approved', true) == 1) {
    $user_role = 'rae';
} else if (in_array('administrator', (array) $current_user->roles)) {
    $user_role = 'admin';
}
?>
<!DOCTYPE html>
<html class="no-js" <?php language_attributes(); ?>>

<head>
    <meta http-equiv="content-type" content="<?php bloginfo('html_type'); ?>" charset="<?php bloginfo('charset'); ?>" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <link rel="profile" href="//gmpg.org/xfn/11">
    <?php wp_head(); ?>
    <script src="https://code.jquery.com/ui/1.12.1/jquery-ui.min.js"></script>
</head>

<body <?php body_class(); ?> data-uid="<?php echo get_current_user_id(); ?>">
    <?php wp_body_open(); ?>

    <a class="skip-link faux-button" href="#site-content">
        <?php esc_html_e('Skip to the content', 'pol'); ?>
    </a>

    <?php
    if (!is_page_template('page-templates/template-list-view.php')) {
        get_template_part('template-parts/global/modal-menu');
    }
    ?>

    <?php get_template_part('template-parts/global/site-header'); ?>


    <?php get_template_part('template-parts/global/modal-search'); ?>


    <?php

    // echo '<div class="oooooo" style="display:none">';
    // // Initialize the array to store unique contributor IDs
    // $contributors_id_seeking_commission = array();

    // // Get users with the 'currently_seeking_commission' meta key
    // $contributors_seeking_commission = get_users(
    //     array(
    //         'meta_query' => array(
    //             array(
    //                 'key' => 'currently_seeking_commission',
    //                 'value' => 'i:0;i:1;',
    //                 'compare' => 'LIKE'
    //             )
    //         )
    //     )
    // );

    // // Iterate over the users and check for duplicates
    // echo'=++=';
    // foreach ($contributors_seeking_commission as $contributor) {
    //     if (in_array($contributor->ID, $contributors_id_seeking_commission)) {
    //         continue;
    //     }
    //     array_push($contributors_id_seeking_commission, $contributor->ID);
    //     // Output the contributor ID
    //     echo $contributor->ID . '===';
    // }

    // echo '</div>';

    ?>


    <!-- sub-menu modal -->

    <!-- Login modal embedded in page -->
    <div id="getPassport-options" class="modal getPassport-options-modalBox">
        <div class="container">
            <div class="row">
                <div class="col-lg-12">
                    <?php
                    global $wpdb;
                    // $user_has_usable_commissions = false;
                    // $uid = get_current_user_id();
                    // $table_name = $wpdb->prefix . 'commission';
                    // $number_of_available_commissions = $wpdb->get_var("SELECT COUNT(code) FROM {$table_name} WHERE (status = 0 OR status = 1) AND current_owner = $uid");

                    if (is_user_logged_in()) {

                    ?>
                        <li id="commission-form">
                            <form action="/add-place" method="post">
                                <div class="wp-block-image is-style-no-vertical-margin">
                                    <?php pol_the_random_goat('popup-goat'); ?>
                                </div>
                                <!-- <p>
                                    To submit new work for publication on The GOAT PoL,
                                    use one of the commissions (short codes) listed in the 'available commissions' field of your <a href="/registration#commissions-profile-page"> contributor’s page.</a> <br>
                                    Insert commission here: <input type="text" name="popup-commission"
                                        id="popup-commission">, and click
                                    <input class="open-ground-rules-2" type="button" value="here">
                                    <input class="use-commission" type="submit" value="" style="display: none;">
                                    to submit.
                                </p> -->

                                <p>
                                    To submit new work for publication on The GOAT PoL,
                                    use one of the commissions (short codes) listed in the 'available commissions' field of
                                    your
                                    <a href="/registration#commissions-profile-page"> contributor’s page.</a> <br>
                                    <br>
                                    <span>
                                        Insert commission here: <input type="text" name="popup-commission"
                                            id="popup-commission">, and click below to submit.</span>
                                </p>
                                <?php
                                if ($number_of_available_commissions == 0) { ?>
                                    <!-- <p>
                                        If you have no available commissions currently, click on
                                        <u class="request-commission" style="cursor:pointer">I’d like to have a commission to
                                            write for The GOAT PoL.</u>
                                    </p> -->
                                <?php } ?>
                                <span class="getPassport-options-question-modal">
                                    <!-- <a href="/registration#commissions-profile-page">What are commissions, and where can I
                                        find them?</a> -->
                                    <a href="#" class="open-ground-rules-2">Here's the commission for my new writing</a>
                                    <input class="use-commission" type="submit" value="" style="display: none;">
                                </span>
                                <span class="request-commission-msg"></span>
                            </form>
                        </li>
                    <?php } else { ?>
                        <li id="email-form">
                            <form method="post" id="check-email-form">
                                <input type="email" name="popup-email" id="popup-email" placeholder="Enter email" required>
                                <input class="popup-email-check" type="submit" value="Submit">
                            </form>
                        </li>
                    <?php } ?>
                </div>
            </div>
        </div>
    </div>







    <!-- // change the header menu text when loginnn -->
    <script>
        <?php if (is_user_logged_in()): ?>

            // Use jQuery to change the text only when the user is logged in
            // jQuery(document).ready(function () {
            //     jQuery("#menu-item-16010 a").text("Write and Read at The GOAT PoL");
            // });

            /***** Menu item hidden until we go live. Utsav ******/
            jQuery(document).ready(function() {
                jQuery(".pol-hamburger .getPassport-modal p").text("Submit work for publication");
            });
            jQuery(document).ready(function() {
                jQuery(".sidebar-content .getPassport-modal a ").text("Submit New Writing to Publish on The GOAT PoL");
            }); /******  Menu hidden until live *********/
        <?php endif; ?>

        <?php
        $curr_uid = get_current_user_id();
        // if (($user_role == 'user')) { 
        // if (!is_user_logged_in()) { 
        ?>
        // jQuery("#menu-item-18186").hide();
        <?php //}

        ?>

        var get_passport_options_2 =
            `
            <div id="getPassport-options-2" title="Welcome to The GOAT PoL" style="display: none;">
                <div class="container">
                    <div class="row">
                        <div class="col-lg-12">
                            <div class="wp-block-image is-style-no-vertical-margin">
                                <figure class="goat popup-goat">
                                    <img width="652" height="490" src="https://staging12.thegoatpol.org/wp-content/uploads/2022/02/GOAT_23-1024x770.jpg" alt="" loading="lazy">
                                </figure>
                            </div>
                            <h4>Welcome to The GOAT PoL—The Geographical Open Atlas of The Polity of Literature.</h4>
                            <p>The GOAT PoL is free and open to everyone who wants to read and write. You can post your own writing, read work by others, take part in free group workshops, or work one-on-one with one of our ten Reader/Advisor/Editors (RAEs) to develop and publish your writing. We pay writers to work with us, $70 (Canadian) for each piece we publish. To learn more, read our <a href="/about">About Page.</a></p>
                            <span class="getPassport-options-2-links">
                                <a href="#" class="option2-close-modal">No thanks, I'm just looking</a> <br>
                                <a href="#" class="option2-open-nextPopup">Yes, I want to participate</a>
                            </span>
                        </div>
                    </div>
                </div>
            </div>
            `;

        jQuery(document).ready(function() {
            //new
            jQuery(document).on('click', '#getPassport-options-2 .close-modal', function(e) {
                e.preventDefault();
                jQuery("#getPassport-options-2").remove();
            });


            // Close modal and redirect to /home
            jQuery(document).on('click', '#getPassport-options-2 .option2-close-modal', function(e) {
                e.preventDefault();
                jQuery("#getPassport-options-2").modal("close");
                jQuery(this).closest('.jquery-modal').remove();
            });

            jQuery(document).on('click', '.option2-open-nextPopup', function(e) {
                e.preventDefault();
                // Close the current modal
                // jQuery('#getPassport-options-2').hide();
                jQuery("#getPassport-options-2").modal("close");
                jQuery(this).closest('.jquery-modal').remove();
                // Show the next popup with id 'menu-popup-content'
                jQuery('#menu-popup-content').show();
            });
        });


        jQuery(document).ready(function() {

            <?php if (!is_user_logged_in()): ?>
                // Add click event for close button
                jQuery('.menu-popup-close').on('click', function() {
                    // Redirect to /register page
                    window.location.href = '/register';
                });
            <?php endif; ?>

        });
        jQuery(document).ready(function() {
            // Check if the user is logged in
            var isLoggedIn = '<?php echo is_user_logged_in() ? 'true' : 'false'; ?>';
            var modalSelector = isLoggedIn == 'true' ? "#getPassport-options" : "#getPassport-options-2";
            jQuery(".getPassport-modal").on("click", function() {

                if (modalSelector == "#getPassport-options-2") {
                    jQuery("body").append(get_passport_options_2);
                }


                // Open the selected modal
                jQuery(modalSelector).modal({
                    fadeDuration: 200,
                    closeOnEscape: false, // Prevents closing on pressing escape
                    clickClose: false
                });
                return false;
            });
            jQuery(".gp-menu-suyw").on("click", function() {

                if (modalSelector == "#getPassport-options-2") {
                    jQuery("body").append(get_passport_options_2);
                }

                // Open the selected modal
                jQuery(modalSelector).modal({
                    fadeDuration: 200,
                    closeOnEscape: false, // Prevents closing on pressing escape
                    clickClose: false
                });
                return false;
            });
        });
    </script>