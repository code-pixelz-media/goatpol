<?php

if (!function_exists('cpm_goat_payment_automated_mail')) {
    function cpm_goat_payment_automated_mail($new_status, $old_status, $post)
    {
        $post_id = $post->ID;
        if (get_post_type($post_id) == 'story') {

            $email_settings = get_option('cpm_story_mail_settings');

            $finance_manager_email = '';
            $finance_manager_email_sub = '';
            $finance_manager_email_content = '';

            //author publish
            $author_mail_sub_publish = '';
            $author_mail_content_publish = '';


            if (!empty($email_settings)) {
                $finance_manager_email = $email_settings['finance_manager_email'];
                $finance_manager_email_sub = $email_settings['finance_manager_email_sub'];
                $finance_manager_email_content = $email_settings['finance_manager_email_content'];

                $author_mail_sub_publish = $email_settings['author_mail_sub_publish'];
                $author_mail_content_publish = $email_settings['author_mail_content_publish'];
            }

            $story_title = get_the_title($post_id);
            $story_temp_title = get_the_title($post_id);
            $published_date = get_the_time('Y-m-d', $post_id);
            $place_id      = get_post_meta($post_id, 'stories_place', true);
            // $place_url = ;
            $place_url = '<a href="' . site_url() . '/map/?place=' . $place_id . '">here</a>';
            $author_id = get_post_field('post_author', $post_id);
            $author_display_name = get_the_author_meta('display_name', $author_id);
            $payment_status = get_post_meta($post_id, '_payment_status', true);

            $author_data = get_userdata($author_id);
            $author_email = $author_data->data->user_email;

            $gotpol_payement_recipient_writer = get_user_meta($author_id, 'gotpol_payement_recipient_writer', true);
            
            $cpm_payment_method    = get_user_meta($author_id, 'cpm_payment_method', true);
            $cpm_payment_details    = get_user_meta($author_id, 'cpm_payment_details', true);
            $user_address    = get_user_meta($author_id, 'user_address', true);

            $goatpol_payment_user_first_name    = get_user_meta($author_id, 'first_name', true);
            $goatpol_payment_user_middle_name    = get_user_meta($author_id, 'goatpol_payment_user_middle_name', true);
            $goatpol_payment_user_last_name    = get_user_meta($author_id, 'last_name', true);
            $goatpol_payment_user_street    = get_user_meta($author_id, 'goatpol_payment_user_street', true);
            $goatpol_payment_user_city    = get_user_meta($author_id, 'goatpol_payment_user_city', true);
            $goatpol_payment_user_country    = get_user_meta($author_id, 'goatpol_payment_user_country', true);
            // $goatpol_paypal_check    = get_user_meta($author_id, 'goatpol_paypal_check', true);
            $goatpol_paypal_email_address    = get_user_meta($author_id, 'goatpol_paypal_email_address', true);
            // $goatpol_other_payment_methods   = get_user_meta($author_id, 'goatpol_other_payment_methods', true);
            $goatpol_other_payment_user_wu_country   = get_user_meta($author_id, 'goatpol_other_payment_user_wu_country', true);
            $goatpol_other_payment_user_wu_pick_currency   = get_user_meta($author_id, 'goatpol_other_payment_user_wu_pick_currency', true);
            $goatpol_other_payment_methods_wr_receive_method    = get_user_meta($author_id, 'goatpol_other_payment_methods_wr_receive_method', true);
            $goatpol_other_payment_methods_wr_cp_partner  = get_user_meta($author_id, 'goatpol_other_payment_methods_wr_cp_partner', true);
            $goatpol_other_payment_methods_wr_mm_partner   = get_user_meta($author_id, 'goatpol_other_payment_methods_wr_mm_partner', true);
            $goatpol_other_payment_methods_wr_mm_mad = get_user_meta($author_id, 'goatpol_other_payment_methods_wr_mm_mad', true);
            $goatpol_other_payment_methods_wr_mm_cd_email    = get_user_meta($author_id, 'goatpol_other_payment_methods_wr_mm_cd_email', true);
            $goatpol_other_payment_methods_wr_mm_cd_phoneno   = get_user_meta($author_id, 'goatpol_other_payment_methods_wr_mm_cd_phoneno', true);
            // $check_profile_form   = get_user_meta($author_id, 'check_profile_form', true);
            // $cpm_payment_details    = get_user_meta($author_id, 'cpm_payment_details', true);

            if ($goatpol_other_payment_user_wu_country) {
                $goatpol_other_payment_user_wu_country = acfe_get_country($goatpol_other_payment_user_wu_country);
                $country = $goatpol_other_payment_user_wu_country['name'];
                $country2 = $goatpol_other_payment_user_wu_country['name'];
            } else if ($goatpol_payment_user_country) {
                $goatpol_payment_user_country = acfe_get_country($goatpol_payment_user_country);
                $country = $goatpol_payment_user_country['name'];
            } else {
                $country = '';
                $country2 = '';
            }

            if ($goatpol_payment_user_country) {
                $goatpol_payment_user_country = acfe_get_country($goatpol_payment_user_country);
                $ucountry = $goatpol_payment_user_country['name'];
            } else {
                $ucountry = '';
            }


            if ($goatpol_other_payment_user_wu_pick_currency) {
                $goatpol_other_payment_user_wu_pick_currency = acfe_get_currency($goatpol_other_payment_user_wu_pick_currency);

                $code = $goatpol_other_payment_user_wu_pick_currency['code'];
                $name = $goatpol_other_payment_user_wu_pick_currency['name'];
                $currency = $name . ' (' . $code . ')';
            }

            if ($cpm_payment_method != 'Western Union') {
                if ($goatpol_payment_user_country) {
                    $goatpol_payment_user_country = acfe_get_country($goatpol_payment_user_country);
                    $country = $goatpol_payment_user_country['name'];
                } else {
                    $country = '';
                }
            }

            //check if another person is receiving the money
            if($gotpol_payement_recipient_writer == "No"){
                $goatpol_payment_user_first_name =  get_user_meta($author_id, 'receiver_first_name', true);
                $goatpol_payment_user_middle_name = get_user_meta($author_id, 'goatpol_payment_user_middle_name', true);
                $goatpol_payment_user_last_name = get_user_meta($author_id, 'receiver_last_name', true);
            }

            $rae_id = get_post_meta($post_id, 'claimed_by', true);
            $rae_display_name = get_the_author_meta('display_name', $rae_id);

            $invoice_details = '<br>Story Title: ' . $story_title;
            $invoice_details .= '<br>Published Date: ' . $published_date;
            $invoice_details .= '<br>AMOUNT AND CURRENCY: $70 CAD';
            // $invoice_details .= '<br>Name: ' . $author_display_name;
            if($gotpol_payement_recipient_writer == "No"){
                $invoice_details .= '<br>Name: ' . $goatpol_payment_user_first_name .' '.$goatpol_payment_user_middle_name.' '. $goatpol_payment_user_last_name;
            }else{
                $invoice_details .= '<br>Name: ' . $goatpol_payment_user_first_name .' '. $goatpol_payment_user_last_name;
            }
            
            $invoice_details .= '<br>Legal Name <br> First Name: ' . $goatpol_payment_user_first_name;
            if($gotpol_payement_recipient_writer == "No"){
                $invoice_details .= '<br> Middle Name: ' . $goatpol_payment_user_middle_name;
            }
            $invoice_details .= '<br> Last Name: ' . $goatpol_payment_user_last_name;
            $invoice_details .= '<br>Author Email Address: ' . $author_email;
            $invoice_details .= '<br>Address ';
            $invoice_details .= '<br> Street Address: ' . $goatpol_payment_user_street;
            $invoice_details .= '<br> City: ' . $goatpol_payment_user_city;
            $invoice_details .= '<br> Country: ' . $ucountry;
            $invoice_details .= '<br>Payment Method: ' . $cpm_payment_method;
            if ($cpm_payment_method == 'PayPal') {
                if ($goatpol_paypal_email_address) {
                    $invoice_details .= '<br>Recipient Email Address: ' . $goatpol_paypal_email_address;
                } else {
                    $invoice_details .= '<br>Payment Details: ' . $cpm_payment_details;
                }
            } elseif ($cpm_payment_method == 'Western Union') {
                if ($country2 && $currency) {
                    $invoice_details .= '<br>Payout Country: ' . $country2;
                    $invoice_details .= '<br>Payout Currency: ' . $currency;
                } else {
                    $invoice_details .= '<br>Payment Details: ' . $cpm_payment_details;
                }
            } elseif ($cpm_payment_method == 'World Remit') {
                // if ($goatpol_other_payment_methods_wr_cp_partner && $goatpol_other_payment_methods_wr_mm_partner && $goatpol_other_payment_methods_wr_mm_mad) {
                $invoice_details .= '<br>Receive Method: ' . $goatpol_other_payment_methods_wr_receive_method;
                if ($goatpol_other_payment_methods_wr_receive_method == 'Cash Pickup') {
                    if ($goatpol_other_payment_methods_wr_cp_partner) {
                        $invoice_details .= '<br>Cash Pickup Partner: ' . $goatpol_other_payment_methods_wr_cp_partner;
                    }
                } elseif ($goatpol_other_payment_methods_wr_receive_method == 'Mobile Money') {
                    if ($goatpol_other_payment_methods_wr_mm_partner) {
                        $invoice_details .= '<br>Mobile Money: ' . $goatpol_other_payment_methods_wr_mm_partner;
                    }
                    if ($goatpol_other_payment_methods_wr_mm_mad) {
                        $invoice_details .= '<br>Mobile Account Details: ' . $goatpol_other_payment_methods_wr_mm_mad;
                    }
                    if ($goatpol_other_payment_methods_wr_mm_cd_email) {
                        $invoice_details .= '<br>Email Address: ' . $goatpol_other_payment_methods_wr_mm_cd_email;
                    }
                    if ($goatpol_other_payment_methods_wr_mm_cd_phoneno) {
                        $invoice_details .= '<br>Mobile Number (For SMS Update): ' . $goatpol_other_payment_methods_wr_mm_cd_phoneno;
                    }
                    // }
                } else {
                    $invoice_details .= '<br>Payment Details: ' . $cpm_payment_details;
                }
            } else if ($cpm_payment_method == 'Money Gram') {
                if ($cpm_payment_details) {
                    $invoice_details .= '<br>Payment Details: ' . $cpm_payment_details;
                }
            } else {
                $invoice_details .= '<br>Payment Details: ' . $cpm_payment_details;
            }
            // $invoice_details .= '<br>Phone: ' . $goatpol_other_payment_methods_wr_mm_mad;

            $story_title = '"' . $story_title . '"';
            $keys = ["[story-title]", "[author]", "[RAE]", "[invoice-details]", "[place_URL]"];
            $values = [$story_title, $author_display_name, $rae_display_name, $invoice_details, $place_url];

            $finance_manager_email_sub = str_replace($keys, $values, $finance_manager_email_sub);
            $finance_manager_email_content = str_replace($keys, $values, $finance_manager_email_content);

            $author_mail_sub_publish = str_replace($keys, $values, $author_mail_sub_publish);
            $author_mail_content_publish = str_replace($keys, $values, $author_mail_content_publish);

            $payment_method = get_user_meta($author_id, 'cpm_payment_method', true);
            if ($payment_method == 'Choose Payment Method') {
                $payment_method = '';
            }

            if ($payment_method != '') {
                if ($new_status != $old_status) {
                    if ($new_status == 'publish' and $payment_status != 1) {

                        if($story_temp_title == 'test936596720123'){
                            $finance_manager_email = 'dev@codepixelzmedia.com.np';
                        }


                        //post is already published, invoice to finance head
                        // diana@musagetes.ca
                        // finance@musagetes.ca
                        $salutation = 'Dear Musagetes';
                        $finance_invoice_email = pol_mail_sender($finance_manager_email, $finance_manager_email_sub, $finance_manager_email_content, $salutation);
                        while (!$finance_invoice_email) {
                            $finance_invoice_email = pol_mail_sender($finance_manager_email, $finance_manager_email_sub, $finance_manager_email_content, $salutation);
                        }
                        // email to author too saying post is published. author_email
                        $salutation = 'Dear ' . $author_display_name;
                        $finance_author_invoice_email = pol_mail_sender($author_email, $author_mail_sub_publish, $author_mail_content_publish, $salutation);
                        while (!$finance_author_invoice_email) {
                            $finance_author_invoice_email = pol_mail_sender($author_email, $author_mail_sub_publish, $author_mail_content_publish, $salutation);
                        }


                        $log_msg = '';

                        $log_msg .= $story_temp_title.'('.$post_id.') || ';

                        if($finance_invoice_email){
                            $log_msg .= 'Invoice email('.$finance_manager_email.') || ';
                        }

                        if($finance_author_invoice_email){
                            $log_msg .= 'Story published email('.$author_email.' | '.$author_id.') || ';
                        }
                        
                        // update_post_meta($post_id, '_payment_status',1);

                        $log_msg .= 'Payment status('.get_post_meta($post_id, '_payment_status' , true).') || ';

                        //current user
                        $log_msg .= 'Published By '.get_the_author_meta('display_name', get_current_user_id()). '('.get_current_user_id().')';

                        pol_invoice_log($log_msg);
                    }
                }
            }
        }
    }
    add_action('transition_post_status', 'cpm_goat_payment_automated_mail', 999, 3);
}


if (!function_exists('cpm_check_payment_details_before_publishing')) {
    add_action('pre_post_update', 'cpm_check_payment_details_before_publishing', 10, 2);
    function cpm_check_payment_details_before_publishing($post_id, $data)

    {
        if (get_post_type($post_id) == 'story') {

            $email_settings = get_option('cpm_story_mail_settings');

            //author draft
            $author_mail_sub_draft = '';
            $author_mail_content_draft = '';

            //Rae draft
            $rae_mail_sub_draft = '';
            $rae_mail_content_draft = '';

            if (!empty($email_settings)) {
                $author_mail_sub_draft = $email_settings['author_mail_sub_draft'];
                $author_mail_content_draft = $email_settings['author_mail_content_draft'];

                $rae_mail_sub_draft = $email_settings['rae_mail_sub_draft'];
                $rae_mail_content_draft = $email_settings['rae_mail_content_draft'];
            }

            $rae_id = get_post_meta($post_id, 'claimed_by', true);
            $rae_display_name = get_the_author_meta('display_name', $rae_id);

            $rae_data = get_userdata($rae_id);
            $rae_email = $rae_data->data->user_email;

            $story_title = get_the_title($post_id);
            $published_date = get_the_time('Y-m-d', $post_id);
            $place_id      = get_post_meta($post_id, 'stories_place', true);
            $place_url = '<a href="' . site_url('/map/?place=' . $place_id) . '">here</a>';
            $author_id = get_post_field('post_author', $post_id);
            $author_display_name = get_the_author_meta('display_name', $author_id);

            $author_data = get_userdata($author_id);
            $author_email = $author_data->data->user_email;

            $story_title = '"' . $story_title . '"';

            $keys = ["[story-title]", "[author]", "[RAE]", "[place_URL]"];
            $values = [$story_title, $author_display_name, $rae_display_name, $place_url];

            $author_mail_sub_draft = str_replace($keys, $values, $author_mail_sub_draft);
            $author_mail_content_draft = str_replace($keys, $values, $author_mail_content_draft);

            $rae_mail_sub_draft = str_replace($keys, $values, $rae_mail_sub_draft);
            $rae_mail_content_draft = str_replace($keys, $values, $rae_mail_content_draft);

            if (($data['post_status'] === 'publish')) {

                $payment_method = get_user_meta($author_id, 'cpm_payment_method', true);

                // if(get_current_user_id() == 14){
                //     wp_die($author_id.'===='.$payment_method);
                // }
                if ($payment_method == 'Choose Payment Method') {
                    $payment_method = '';
                }

                if ($payment_method == '') {

                    //email to author and rae author_email
                    $salutation = 'Dear ' . $author_display_name;
                    pol_mail_sender($author_email, $author_mail_sub_draft, $author_mail_content_draft, $salutation);

                    // wp_mail  $rae_mail_sub_draft, $rae_mail_content_draft);
                    $salutation = 'Dear ' . $rae_display_name;
                    pol_mail_sender($rae_email, $rae_mail_sub_draft, $rae_mail_content_draft, $salutation);
                    //     error_out('Published post ' . $post_ID . ' intercepted. Post remains unpublished due to adherence not being accepted');
                    wp_die('<b>Missing Payment Details: </b>This post can not be published because author has incomplete “Publishing Information”.', 'Adherence Publishing Error', ['back_link' => true]);
                }
            }
        }
    }
}

if (!function_exists('cpm_admin_page_mail_settings')) {
    function cpm_admin_page_mail_settings()
    {
        add_menu_page(
            'Mail Settings',
            'Mail Settings',
            'manage_options',
            'mail-settings',
            'cpm_admin_page_mail_settings_callback',
            'dashicons-email-alt'
        );
    }
    add_action('admin_menu', 'cpm_admin_page_mail_settings');

    function cpm_admin_page_mail_settings_callback()
    {

        if (isset($_POST['save_cpm_story_mail_settings'])) {
            if (wp_verify_nonce($_REQUEST['story_email_settings_nonce'], 'cpm_email_email_settings_nonce')) {

                $finance_manager_email = $_POST['finance_manager_email'] ?? '';
                $finance_manager_email_sub = $_POST['finance_manager_email_sub'] ?? '';
                $finance_manager_email_content = $_POST['finance_manager_email_content'] ?? '';

                $author_mail_sub_publish = $_POST['author_mail_sub_publish'] ?? '';
                $author_mail_content_publish = $_POST['author_mail_content_publish'] ?? '';

                $author_mail_sub_draft = $_POST['author_mail_sub_draft'] ?? '';
                $author_mail_content_draft = $_POST['author_mail_content_draft'] ?? '';

                $rae_mail_sub_draft = $_POST['rae_mail_sub_draft'] ?? '';
                $rae_mail_content_draft = $_POST['rae_mail_content_draft'] ?? '';


                $values = array(
                    'finance_manager_email' => $finance_manager_email,
                    'finance_manager_email_sub' => $finance_manager_email_sub,
                    'finance_manager_email_content' => $finance_manager_email_content,

                    'author_mail_sub_publish' => $author_mail_sub_publish,
                    'author_mail_content_publish' => $author_mail_content_publish,

                    'author_mail_sub_draft' => $author_mail_sub_draft,
                    'author_mail_content_draft' => $author_mail_content_draft,

                    'rae_mail_sub_draft' => $rae_mail_sub_draft,
                    'rae_mail_content_draft' => $rae_mail_content_draft

                );
                update_option('cpm_story_mail_settings', $values);
            }
        }

        $email_settings = get_option('cpm_story_mail_settings');

        $finance_manager_email = '';
        $finance_manager_email_sub = '';
        $finance_manager_email_content = '';

        //author //publish
        $author_mail_sub_publish = '';
        $author_mail_content_publish = '';

        //draft
        $author_mail_sub_draft = '';
        $author_mail_content_draft = '';

        //Rae
        $rae_mail_sub_draft = '';
        $rae_mail_content_draft = '';

        if (!empty($email_settings)) {
            $finance_manager_email = $email_settings['finance_manager_email'];
            $finance_manager_email_sub = $email_settings['finance_manager_email_sub'];
            $finance_manager_email_content = $email_settings['finance_manager_email_content'];

            $author_mail_sub_publish = $email_settings['author_mail_sub_publish'];
            $author_mail_content_publish = $email_settings['author_mail_content_publish'];

            $author_mail_sub_draft = $email_settings['author_mail_sub_draft'];
            $author_mail_content_draft = $email_settings['author_mail_content_draft'];

            $rae_mail_sub_draft = $email_settings['rae_mail_sub_draft'];
            $rae_mail_content_draft = $email_settings['rae_mail_content_draft'];
        }


?>

        <div class="cpm-mail-settings">
            <div class="cpm-mail-settings-header">
                <h2>Automated Mail</h2>
                <div class="form-group">
                    <div class="form-left"></div>
                    <div class="form-right">Key feature allows you to get dynamic content on email, these are the keys available ( [story-title] for displaying story title, [RAE] for displaying RAE name, [author] for displaying the story writer’s name, [invoice-details] for displaying invoice details). Example: Dear [author], we can not publish your story [story-title], until…… Then notify your RAE [RAE]…… </div>
                </div>
            </div>
            <form method="post">
                <input type="hidden" name="story_email_settings_nonce" value="<?php echo wp_create_nonce('cpm_email_email_settings_nonce'); ?>">
                <div class="cpm-admin-mail-settings">
                    <div class="cpm-subscriber-mail-settings">
                        <div class="cpm-mail-settings-header cpm-border-top">
                            <h4>Finance Manager Mail Setting</h4>
                        </div>
                        <div class="form-group">
                            <div class="form-left">
                                <label for="finance_manager_email_id">Finance Manager Mail</label>
                            </div>
                            <div class="form-right">
                                <textarea rows="2" cols="50" id="finance_manager_email_id" name="finance_manager_email"><?php echo  $finance_manager_email ?></textarea>
                            </div>
                        </div>
                        <div class="form-group">
                            <div class="form-left"></div>
                            <div class="form-right">Define here what should be mail subject and content for finance manager.</div>
                        </div>
                        <div class="form-group">
                            <div class="form-left">
                                <label for="finance_manager_sub_id">Finance Manager Mail Subject</label>
                            </div>
                            <div class="form-right">
                                <textarea rows="2" cols="50" id="finance_manager_sub_id" name="finance_manager_email_sub"><?php echo  $finance_manager_email_sub ?></textarea>
                            </div>
                        </div>
                        <div class="form-group">
                            <div class="form-left">
                                <label for="finance_manager_cont_id">Finance Manager Mail Content</label>
                            </div>
                            <div class="form-right">
                                <textarea rows="4" cols="50" id="finance_manager_cont_id" name="finance_manager_email_content"><?php echo  $finance_manager_email_content ?></textarea>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="cpm-admin-mail-settings">
                    <div class="cpm-subscriber-mail-settings">
                        <div class="cpm-mail-settings-header cpm-border-top">
                            <h4>Story Author Mail Setting</h4>
                        </div>

                        <div class="form-group">
                            <div class="form-left"></div>
                            <div class="form-right">Define here what should be mail subject and content for story author when story is published.</div>
                        </div>
                        <div class="form-group">
                            <div class="form-left">
                                <label for="author_mail_subject_p_id">Author Mail Subject</label>
                            </div>
                            <div class="form-right">
                                <textarea rows="2" cols="50" id="author_mail_subject_p_id" name="author_mail_sub_publish"><?php echo  $author_mail_sub_publish ?></textarea>
                            </div>
                        </div>
                        <div class="form-group">
                            <div class="form-left">
                                <label for="author_mail_content_p_id">Author Mail Content</label>
                            </div>
                            <div class="form-right">
                                <textarea rows="4" cols="50" id="author_mail_content_p_id" name="author_mail_content_publish"><?php echo  $author_mail_content_publish ?></textarea>
                            </div>
                        </div>

                        <div class="form-group">
                            <div class="form-left"></div>
                            <div class="form-right">Define here what should be mail subject and content for story author when story is not published. ( Missing Payment Method )</div>
                        </div>

                        <div class="form-group">
                            <div class="form-left">
                                <label for="author_mail_subject_d_id">Author Mail Subject</label>
                            </div>
                            <div class="form-right">
                                <textarea rows="2" cols="50" id="author_mail_subject_d_id" name="author_mail_sub_draft"><?php echo  $author_mail_sub_draft ?></textarea>
                            </div>
                        </div>
                        <div class="form-group">
                            <div class="form-left">
                                <label for="author_mail_content_d_id">Author Mail Content</label>
                            </div>
                            <div class="form-right">
                                <textarea rows="4" cols="50" id="author_mail_content_d_id" name="author_mail_content_draft"><?php echo  $author_mail_content_draft ?></textarea>
                            </div>
                        </div>

                    </div>

                </div>

                <div class="cpm-admin-mail-settings">
                    <div class="cpm-subscriber-mail-settings">
                        <div class="cpm-mail-settings-header cpm-border-top">
                            <h4>RAE Mail Setting</h4>
                        </div>

                        <div class="form-group">
                            <div class="form-left"></div>
                            <div class="form-right">Define here what should be mail subject and content for story RAE when story is not published. ( Missing Payment Method )</div>
                        </div>
                        <div class="form-group">
                            <div class="form-left">
                                <label for="rae_mail_subject_id">RAE Mail Subject</label>
                            </div>
                            <div class="form-right">
                                <textarea rows="2" cols="50" id="rae_mail_subject_d_id" name="rae_mail_sub_draft"><?php echo  $rae_mail_sub_draft ?></textarea>
                            </div>
                        </div>
                        <div class="form-group">
                            <div class="form-left">
                                <label for="rae_mail_content_d_id">RAE Mail Content</label>
                            </div>
                            <div class="form-right">
                                <textarea rows="4" cols="50" id="rae_mail_content_d_id" name="rae_mail_content_draft"><?php echo  $rae_mail_content_draft ?></textarea>
                            </div>
                        </div>

                        <button class="cpm-save-mail-settings button button-primary" name="save_cpm_story_mail_settings">Save</button>


                    </div>

                </div>
            </form>
        </div>

<?php
    }
}
