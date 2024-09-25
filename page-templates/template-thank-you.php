<?php
/**
* Template Name: Thank You Page
* Description: Check, either user verified or not
* @package Codehelp
*
*/
get_header();

if (empty($_GET['place_id']) || !is_user_logged_in()) {

    ?>
    <script>
        window.location.href = '/add-place';
    </script>
    <?php

} 
?>
<main id="site-content" role="main">
    <div id="post-content" class="section-inner">
        <div class="section-inner max-percentage no-margin mw-thin">
            <div class="entry-content">
                <?php
                    $submission_log = '';

                    $post_id = isset( $_GET['place_id']) ? $_GET['place_id'] : false;
                    $enc_p_id =  pol_encrypt_decrypt($post_id);
                    $curr_user_id = get_current_user_id();

                    if(isset($post_id) && !is_wp_error($post_id)){
                        the_content();
                        $story_labels           = get_field('story_type_labels', $post_id);
                        $author_id              = get_post_field( 'post_author', $post_id );
                        $author 		        = get_userdata($author_id);
                        $author_email 	        = $author->user_email;
                        $author_name 	        = $author->display_name;
                        $encrypted_email        = pol_encrypt_decrypt($author_email);
                        $encrypted_author_id    = pol_encrypt_decrypt($author_id);
                        $finish_sory_path       = get_page_by_path('complete-story');
                        $is_verified            = get_user_meta($author_id , 'verified_user' , true);
                        $current_story          = get_user_meta($author_id,'current_editing_place',true);
                        $passnew                = wp_generate_password( 8, true, false );
                        $story_writer           = get_field('story_nom_de_plume' , $post_id);
	                    $explod_space           =  explode(" " , $story_writer);

                        if(array_key_exists(1 , $explod_space)) {
                            $last_name = $explod_space[1];
                        }

                        if(array_key_exists(2 , $explod_space)){
                            $last_name .= ' ' . $explod_space[2];
                        }

                        wp_update_user([
                            'ID' => $author_id, // this is the ID of the user you want to update.
                            'first_name' => $explod_space[0] ,
                            'last_name'  => $last_name,
                            'display_name' => $explod_space[0] . ' ' . $last_name
                        ]);

                        //auto claim this story using the rae's id from the current users user meta
                        $commission_inuse = $_COOKIE['popup-commission'];
                        $current_owner = $wpdb->get_var($wpdb->prepare("SELECT current_owner from {$wpdb->prefix}commission WHERE code = '$commission_inuse' "));

                        $rae_name = '---';
                        $rae_email = '---';

                        if((int)$current_owner == (int)$curr_user_id){

                            $rae_that_transferred_the_commission = $wpdb->get_var($wpdb->prepare("SELECT org_rae from {$wpdb->prefix}commission WHERE code = '$commission_inuse' "));
                            $rae_user = get_user_by('id', $rae_that_transferred_the_commission);
                            $rae_name = $rae_user->display_name;
                            $rae_email = $rae_user->user_email;
                            if((int)$rae_that_transferred_the_commission != 0){
                                update_post_meta($post_id, 'claimed_by', $rae_that_transferred_the_commission);



                                $pw = new \PhpOffice\PhpWord\PhpWord();

                                // ADD HTML CONTENT
                                $section = $pw->addSection();
                                $content = get_the_content(null, false, $post_id);
                                $stripped_content = strip_tags($content, '<p><b><em>');
                                $html = $stripped_content;
                                \PhpOffice\PhpWord\Shared\Html::addHtml($section, $html, false, false);

                                // SAVE TO DOCX ON SERVER
                                $title = get_post_field('post_title', $post_id);
                                $res = preg_replace('/[^a-zA-Z0-9_ -]/s', '', $title);
                                $name = preg_replace('/\s*/', '', strtolower($res));
                                $file_loc = get_template_directory() . DIRECTORY_SEPARATOR . 'inc' . DIRECTORY_SEPARATOR . 'rtfs' . DIRECTORY_SEPARATOR;
                                $pw->save($file_loc . $name . ".rtf", "RTF");

                                $new_file = $file_loc . $name . '.rtf';
                                $email_file_link = get_site_url() . '/wp-content/themes/goatpol/inc/rtfs/' . $name . '.rtf';
                                if (file_exists($new_file)) {
                                    $attach = array($new_file);
                                }

                                //send email to rae thanking them for claiming this story
                                $subj = __('Story Claimed', 'pol');
                                $sal = __('Dear Editor', 'pol');
                                $writer_name = (get_userdata(get_current_user_id()))->display_name;
                                $writer_email = (get_userdata(get_current_user_id()))->user_email;
                                $msg = "Thank you " . $rae_name . ", you've claimed " . get_the_title($post_id) . " and will contact " 
                                . $writer_name . " at " . $writer_email . " within the next 72 hours.
                                You can download " . get_the_title($post_id) . " as an RTF file <a href='" . $email_file_link . "'>here</a>.
                                We hope you enjoy working on it,
                                The GOAT PoL";
                                $rae_email_ = pol_mail_sender($rae_email, $subj, $msg, $sal);

                                //send mail to writer confirmming that their story was claimed
                                // pol_send_rtf_mail_to_claimed_editor((int)$rae_that_transferred_the_commission, (int)$post_id);

                                update_post_meta($post_id, 'commission_used', $commission_inuse);
                                $submission_log .= get_the_title($post_id).'('.$post_id.') || RAE claimaed email('.($rae_email_ ? $rae_email : 'failed').') || ';
                            }
                        }

                        update_post_meta($post_id, '_payment_status', 0);

                        ?>
                        <script>
                            document.cookie = "popup-commission=; expires=Thu, 01 Jan 1970 00:00:00 UTC; path=/;";
                        </script>
                        <?php

                        //!!!just for testing
                        $is_verified = 1; 
                        //!!!just for testing

                        if($is_verified != 1 ){

                            $user_pass = wp_generate_password( 20, true,  false );
                            update_user_meta($author_id , 'temp_pass',$user_pass);
                            $hash = pol_hash_password($user_pass);
                            global $wpdb;
                            $wpdb->update($wpdb->users,array(
                                    'user_pass'           => $hash,
                                    'user_activation_key' => '',
                                ),
                                array( 'ID' => $author_id )
                            );

                            //clean_user_cache( $author_id );
                            $links = array(
                                'aid' => $encrypted_author_id,
                                'mad' => $encrypted_email,
                                'sid' => $enc_p_id,
                                'author-edit' => $encrypted_author_id,
                                'authorr-mail' => $encrypted_email,
                                'story-edit' => $enc_p_id,
                            );
                        }else{
                            $links = array(
                                'author-edit' => $encrypted_author_id,
                                'authorr-mail' => $encrypted_email,
                                'story-edit' => $enc_p_id,
                            );
                        }


                        $author_id_new          = get_post_field( 'post_author', $post_id );
                        $story_title = get_post_field( 'post_title', $post_id );
                        $author_new		        = get_userdata($author_id_new);
                        $finish_sory_path       = get_page_by_path('complete-story');
                        $link                   = get_the_permalink($finish_sory_path->ID);
                        $updated_link           = add_query_arg( $links,$link );
                        $linkText = $is_verified==1 ? 'Agree and continue publishing my story' : 'I agree to be a citizen in this polity of literature, please publish my story';
                        $message = '
                        Thank you for sending us your work. 
                        We promise to read it and help you improve it by acting as reader and editor. 
                        We will work with you until your story is ready to publish, then we\'ll publish it and pay you $70 (CAD). 
                        You will retain all rights to publish the story with others later, or include it in a book, 
                        or do anything you want with it. Our fee pays for the right to publish it on The GOAT PoL. That\'s our promise.
                        <br><br><i>Now please think carefully about our ground rules</i>: by submitting this story you agree to abide by the rules you read and agreed to a few screens ago: 
                        (1) Everyone is honest in the work place. No lying. No hiding the actions you take. No false claims about yourself or your work. 
                        (2) One-commission-at-a-time. Every writer is limited to working on one-commission-at-a-time. If you have several pseudonyms 
                        (made-up names you write under) don\'t pursue commissions for more than one-at-a-time. 
                        (3) One account only: do not open multiple accounts at The GOAT PoL. Use one account only for all your writing and reading. 
                        (4) In the work place we cultivate and maintain mutual respect: ask, don\'t demand; disagree with respect, don\'t belittle or dismiss; 
                        listen and try to understand, even if you disagree. 
                        (5) No stealing—if you submit work written by someone else and claim it is your own work, we can\'t work with you. 
                        (6) Please be patient—we realize every writer is eager for payment after completing a commission; you will be paid as soon as possible. 
                        You don\'t need to inquire or ask us when. Do you agree, and do you promise? <b><i>—clicking the link at the bottom of ths email will confirm your 
                        agreement—</i></b>
                        <br/><br/><br/>
                        <a href="'.$updated_link.'">'.$linkText.'</a>
                        <br/><br/>  If the link does not work for you, you can copy this URL and paste it in your browser. {{'.$updated_link.'}}';

                        
                        // $message = 'Dear '.$author_name.',Thank you for submitting your story, '.$story_title.', to The GOAT PoL. 
                        // You will hear from your Reader-Advisor-Editor (RAE), '.$rae_name.' who will work with you and prepare the story for publication. 
                        // We work with dozens of writers on hundreds of stories, so please be patient with us. If you don\'t hear back from your RAE within 
                        // ten days, send an email to '.$rae_email.' to inquire.';

                        $subject = __('Email Verification' ,'pol');

                        $check_author_mail = get_post_meta($post_id,'thank-mail' , true);
                        if($check_author_mail != '1'){
                            if(pol_mail_sender($author_email,$subject ,$message)){
                                update_post_meta($post_id,'thank-mail', '1');
                                $ver_email_sent = 1;
                            }else{
                                $ver_email_sent = 0;
                            }
                            $submission_log .= 'Verification email('.($ver_email_sent == 1 ? $author_email : 'failed').')';
                        }
                    }
                    pol_story_submission_log($submission_log);

                ?>
            </div>
        </div>
    </div>
    <div class="rs-story-sumitted-wrapper">
    </div>


</main>
<?php
get_footer();
