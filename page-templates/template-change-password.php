<?php
/**
 * Template Name: Change User Password
 * 
 * Displays the homepage map with place markers.
 * 
 * @package GOAT PoL
 */
get_header();
?>
<main id="site-content" role="main">
    <div id="post-content" class="section-inner">
        <div class="section-inner">
            <div class="entry-content">
                <?php
                    global $wpdb;
                    $user_data = wp_get_current_user();
                    $user_records = $wpdb->get_results( $wpdb->prepare("SELECT * FROM {$wpdb->prefix}users WHERE id= $user_data->ID"));
                    // $sql = "SELECT * FROM wp_users WHERE ID = $user_data->ID";
                    // $user_records = $wpdb->get_results($sql);
                    if(isset($_POST['submit_change_password'])){
                        // echo "Test";
                        $old_password = $_POST['old_password'];
                        $new_password = $_POST['new_password'];
                        $confirm_password = $_POST['confirm_password'];
                        $hash_old_password = wp_hash_password($old_password);
                        // echo $hash_old_password . '<br>';
                        
                        foreach($user_records as $user_record){
                            if(wp_check_password($old_password,$user_record->user_pass,$user_record->ID)){
                                if($new_password == $confirm_password){
                                    // var_dump($user_record->user_pass);
                                    wp_set_password($new_password, $user_record->ID);
                                    echo '<p class="rs-password-match">Your new password is set. Now you can check.</p>';

                                    // send mail
                                        // var_dump($user_record->user_nicename);
                                        ob_start();
                                        add_filter( 'wp_mail_content_type','bls_set_content_type' );
                                        // echo $user_record->user_login;
                                        // mail send process
                                        $to = $user_record->user_login;
                                        $subject = "Password Reset";
                                        ?>
                                        <!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
                                <html xmlns="http://www.w3.org/1999/xhtml" xmlns="http://www.w3.org/1999/xhtml" style="font-family: 'Helvetica Neue', 'Helvetica', Helvetica, Arial, sans-serif; font-size: 100%; line-height: 1.6; margin: 0; padding: 0;">
                                <head>
                                    <meta name="viewport" content="width=device-width" />

                                    <meta http-equiv=3D"Content-Type" content=3D"text/html; charset=3Dutf-8" = />
                                    <title>An update was made to the task you are involvd in.</title>
                                </head>
                                <body bgcolor="#f6f6f6" style="font-family: 'Helvetica Neue', 'Helvetica', Helvetica, Arial, sans-serif; font-size: 100%; line-height: 1.6; -webkit-font-smoothing: antialiased; -webkit-text-size-adjust: none; width: 100% !important; height: 100%; margin: 0; padding: 0; ">

                                <!-- body -->
                                <table class="body-wrap" bgcolor="#f6f6f6" style="font-family: 'Helvetica Neue', 'Helvetica', Helvetica, Arial, sans-serif; font-size: 100%; line-height: 1.6; width: 100%; margin: 0; padding: 20px;"><tr style="font-family: 'Helvetica Neue', 'Helvetica', Helvetica, Arial, sans-serif; font-size: 100%; line-height: 1.6; margin: 0; padding: 0;"><td style="font-family: 'Helvetica Neue', 'Helvetica', Helvetica, Arial, sans-serif; font-size: 100%; line-height: 1.6; margin: 0; padding: 0;"></td>
                                        <td class="container" bgcolor="#FFFFFF" style="font-family: 'Helvetica Neue', 'Helvetica', Helvetica, Arial, sans-serif; font-size: 100%; line-height: 1.6; display: block !important; max-width: 600px !important; clear: both !important; margin: 0 auto; padding: 20px; border: 1px solid #f0f0f0;">

                                            <!-- content -->
                                            <div class="content" style="font-family: 'Helvetica Neue', 'Helvetica', Helvetica, Arial, sans-serif; font-size: 100%; line-height: 1.6; max-width: 600px; display: block; margin: 0 auto; padding: 0;">
                                            <table style="font-family: 'Helvetica Neue', 'Helvetica', Helvetica, Arial, sans-serif; font-size: 100%; line-height: 1.6; width: 100%; margin: 0; padding: 0;"><tr style="font-family: 'Helvetica Neue', 'Helvetica', Helvetica, Arial, sans-serif; font-size: 100%; line-height: 1.6; margin: 0; padding: 0;"><td style="font-family: 'Helvetica Neue', 'Helvetica', Helvetica, Arial, sans-serif; font-size: 100%; line-height: 1.6; margin: 0; padding: 0;">
                                                        <p><img src="http://codepixelz.tech/goat/wp-content/uploads/2022/04/logo.jpg" width="150px;" /></p>
                                                
                                    <p style="margin-top:50px;">
                                            Dear <?php echo $user_record->user_nicename; ?>,<br> 
                                                    At your request we have just confirmed or changed your login password. If you did not press "Confirm (new or
                                                    old) password" on your login screen, please contact us immediately at thegoatpol@tutanota.com.
                                            </p><br>
                                            Thank you,<br/>
                                            The GOAT PoL<br/>
                                    </p>                        
                                                        
                                                        <table style="font-family: 'Helvetica Neue', 'Helvetica', Helvetica, Arial, sans-serif; font-size: 100%; text-align:right; line-height: 1.6; width: 100%; margin: 0; padding: 0;"><tr style="font-family: 'Helvetica Neue', 'Helvetica', Helvetica, Arial, sans-serif; font-size: 100%; line-height: 1.6; margin: 0; padding: 0;"><td class="padding" style="font-family: 'Helvetica Neue', 'Helvetica', Helvetica, Arial, sans-serif; font-size: 100%; line-height: 1.6; margin: 0; padding: 10px 0;">
                                                        <img src="http://codepixelz.tech/goat/wp-content/uploads/2022/02/GOAT_21-scaled.jpg" width="150px;" />				
                                                        </td>
                                                            </tr></table>
                                                            <img style="margin-top:30px;" src="http://codepixelz.tech/goat/wp-content/themes/goatpol/assets/img/FullTitle_transparent.png" width="580px"/>
                                                    </td>
                                                </tr></table></div>
                                            <!-- /content -->
                                            
                                        </td>
                                        <td style="font-family: 'Helvetica Neue', 'Helvetica', Helvetica, Arial, sans-serif; font-size: 100%; line-height: 1.6; margin: 0; padding: 0;"></td>
                                    </tr></table><!-- /body --><!-- footer --><table class="footer-wrap" style="font-family: 'Helvetica Neue', 'Helvetica', Helvetica, Arial, sans-serif; font-size: 100%; line-height: 1.6; width: 100%; clear: both !important; margin: 0; padding: 0;"><tr style="font-family: 'Helvetica Neue', 'Helvetica', Helvetica, Arial, sans-serif; font-size: 100%; line-height: 1.6; margin: 0; padding: 0;"><td style="font-family: 'Helvetica Neue', 'Helvetica', Helvetica, Arial, sans-serif; font-size: 100%; line-height: 1.6; margin: 0; padding: 0;"></td>
                                        <td class="container" style="font-family: 'Helvetica Neue', 'Helvetica', Helvetica, Arial, sans-serif; font-size: 100%; line-height: 1.6; display: block !important; max-width: 600px !important; clear: both !important; margin: 0 auto; padding: 0;">
                                            
                                            <!-- content -->
                                            <div class="content" style="font-family: 'Helvetica Neue', 'Helvetica', Helvetica, Arial, sans-serif; font-size: 100%; line-height: 1.6; max-width: 600px; display: block; margin: 0 auto; padding: 0;">
                                                <table style="font-family: 'Helvetica Neue', 'Helvetica', Helvetica, Arial, sans-serif; font-size: 100%; line-height: 1.6; width: 100%; margin: 0; padding: 0;"><tr style="font-family: 'Helvetica Neue', 'Helvetica', Helvetica, Arial, sans-serif; font-size: 100%; line-height: 1.6; margin: 0; padding: 0;"><td align="center" style="font-family: 'Helvetica Neue', 'Helvetica', Helvetica, Arial, sans-serif; font-size: 100%; line-height: 1.6; margin: 0; padding: 0;">
                                                            
                                                        </td>
                                                    </tr></table></div>
                                            <!-- /content -->
                                            
                                        </td>
                                        <td style="font-family: 'Helvetica Neue', 'Helvetica', Helvetica, Arial, sans-serif; font-size: 100%; line-height: 1.6; margin: 0; padding: 0;"></td>
                                    </tr></table><!-- /footer --></body>

                                </html>
                                <?php 
                                    $message_new = ob_get_contents();

                                    ob_end_clean();
                                    $send_from = 'thegoatpol@tutanota.com';
                                        $headers = array('Content-Type: text/html; charset=UTF-8');
                                    $headers .= 'From: '. $send_from . "\r\n";

                                    $sent = wp_mail($to,$subject, $message_new,$headers);
                                }else{
                                    echo '<p class="rs-old-error-password">Your New and Confirm Password are not matched.</p>';
                                }
                                
                            }else{
                                echo '<p class="rs-old-error-password">Your old password is not matched with existing one.</p>';
                            }
                            
                        }
                    }
                ?>
                <form action="" method="POST" enctype="multipart/form-data" class="rs-change-password-wrapper">
                    <h4 class="rs-change-password-title">Setup Your New Password</h4>
                    <div class="form-group">
                        <label for="old_password">Old Password</label>
                        <input type="password" class="old_password" name="old_password" required>
                    </div>
                    <div class="form-group">
                        <label for="new_password">New Password</label>
                        <input type="password" class="new_password" name="new_password" required>
                    </div>
                    <div class="form-group">
                        <label for="confirm_password">Confirm Password</label>
                        <input type="password" class="confirm_password" name="confirm_password" required>
                    </div>
                    <div class="wp-block-buttons">
                        <button class="wp-block-button wp-block-button__link" name="submit_change_password" type="submit">Save</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</main>
<?php
get_footer();