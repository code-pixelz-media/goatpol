<?php

/**
 * Template Name: Complete Publishing
 *
 * Uses the same content structure as the default singular.php template,
 * but adds a form if selected.
 *
 * @package GOAT PoL
 */


get_header();


$form_id 				= get_field('form_id');
$form_submit 			= get_field('form_submit_button');
$form_action 			= get_field('form_submit_action');
$form_attach_id 		= get_field('form_attach_id') == 1 ? true : false;
$form_redirect 			= 'redirect' == $form_action ? get_field('form_redirect_page') : false;
// Submit button text
if ($form_submit) {
	$form_args['submit_text'] = $form_submit;
}

// Custom redirect
if ($form_redirect) {
	$form_args['redirect'] = $form_redirect;
	$form_args['ajax'] 		 = false;

	// Pass the new post ID as a custom query arg.
	if ($form_attach_id) {
		$form_args['attach_id'] = true;
	}
}

if ($new_place) {
	$form_args['new_place'] = true;
}

$author_id    = $_GET['author-edit'];
$author_email = $_GET['authorr-mail'];
$story_id     = $_GET['story-edit'];

// echo 'author id:::'.$author_id.'<br>';
// echo 'author email:::'.$author_email.'<br>';
// echo 'story id:::'.$story_id.'<br>';
// exit;

if (isset($author_id) && isset($author_email)  && isset($story_id)) {
	$story_id_dec    	= pol_encrypt_decrypt($story_id, false);
	if ($form_redirect) {
		$form_args['redirect'] = $form_redirect . '?story-id=' . $story_id;
		$form_args['ajax'] 		 = false;

		// Pass the new post ID as a custom query arg.
		if ($form_attach_id) {
			$form_args['attach_id'] = true;
		}
	}

	$author_id_dec    	= pol_encrypt_decrypt($author_id, false);
	$author_email_dec 	= pol_encrypt_decrypt($author_email, false);


	$author_pass_dec  	= pol_encrypt_decrypt($author_pass, false);
	$edit_palce         = get_user_meta($author_id_dec, 'current_editing_place', true);
	$redirect_url       = !empty($edit_palce) ? false : true;
	$user_info          = get_userdata($author_id_dec);
	$user_name          = $user_info->display_name;
	$story_place 		= get_post_meta($story_id_dec, 'stories_place', true);



	$story_place_id		= is_array($story_place) ? $story_place[0] : $story_place;
	$place_loc_type 	=  get_post_meta($story_place_id, 'where_does', true); //get_post_meta($story_place_id,'where_does',true); //get_field('where_does' , $story_place_id);
	$story  			=   apply_filters('the_content', get_the_content(null, false, $story_id_dec));

	$form_args['values']['place_type']           	= get_field('place_type', $story_place_id);

	$form_args['values']['place_access']           	= get_field('place_access', $story_place_id);

	$form_args['values']['place_languages']         = get_field('place_languages', $story_place_id);

	$form_args['values']['place_attributes']        = get_field('place_attributes', $story_place_id);

	$form_args['values']['place_location']			= get_field('place_location', $story_place_id);

	//sending email about their account
	$author_id    = $_GET['aid'];
	$author_email = $_GET['mad'];
	$story_id     = $_GET['sid'];


	if (isset($author_id) && isset($author_email)) {
		$author_id_dec      = pol_encrypt_decrypt($author_id, false);
		$edit_palce         = get_user_meta($author_id_dec, 'current_editing_place', true);
		$redirect_url       = !empty($edit_palce) ? false : true;
		$user_info          = get_userdata($author_id_dec);
		$user_name          = $user_info->display_name;
		$author_email_dec   = pol_encrypt_decrypt($author_email, false);
		$story_id_dec       = pol_encrypt_decrypt($story_id, false);
		// wp_update_post( array('ID' => $story_id_dec, 'post_type'=> 'drafts'));
		$author_pass_dec    = pol_encrypt_decrypt($author_pass, false);
		$is_verified        = get_user_meta($author_id, 'verified_user', true);

		$email_link = add_query_arg(
			array(
				'aid' => $author_id,
				'mad' => $author_email,
				'sid' => $story_id,
				'ps'  => isset($_GET['ps']) ? true : false,
			),
			get_the_permalink(get_the_id())
		);

		if ($is_verified != 1) {
			$author_obj = get_user_by('id', $author_id_dec);
			$to = $author_obj->user_email;
			$pass = get_user_meta($author_id_dec, 'temp_pass', true);
			if (!empty($pass)) {
				$subject = __('GOAt Pol credentials', 'pol');
				$message = '<p>Congratulations, you are now a passport-holding member of The GOAT PoL. We are grateful for your writing and are excited to work with you.<br><br>To set up your account so that we can pay you for your work, please login using the credentials below. Look for the link at top right that says, "Howdy [user name]." Click on it and select "user profile" from the dropdown menu. In the user profile you can add all of your necessary information—please complete the section called "Publishing Information," so that we can pay you—and save the changes by scrolling to the bottom of the profile page to click on "Update." On the user profile you can also change your password and manage the account, as you please. <br><br>You can change your password anytime when you are logged into the site just click on your user name and look for "Change Password."You can change your password anytime when you are logged into the site just click on your user name and look for "Change Password."</p> Login : ' . $to . '<br/> Password : ' . $pass . '<br/>';
				if (pol_mail_sender($to, $subject, $message));
			}
			//pol_mail_sender($to,$subject , $message)
		}
	}
	if ($author_id_dec) update_user_meta($author_id_dec, 'current_editing_place', $story_id_dec);
	if ($author_id_dec) update_user_meta($author_id_dec, 'verified_user', true);
	update_post_meta($story_id_dec, 'story_verified', '1');



?>
	<article <?php post_class(); ?> id="post-<?php the_ID(); ?>">

		<?php get_template_part('template-parts/single/entry-header'); ?>

		<div id="post-content" class="post-inner">
			<div class="section-inner do-spot spot-fade-up a-del-200">
				<div class="section-inner max-percentage no-margin mw-thin">
					<div class="entry-content">
						<div class="tets">
							Here is the story you've asked us to publish. To complete the process please <?php if ($form_id && $place_loc_type == 'geo_loc') { ?>answer any of the questions you can, listed below your story. Then<?php } ?> click 'FINISHED!!!' If you need to edit or change your story, click on 'RETURN TO EDIT STORY.' Thanks! The GOAT
						</div>
						<br>
						<div class="has-lg-font-size">
							<?php echo $story; ?>
						</div>

						<?php
						if ($form_id && $place_loc_type == 'geo_loc') {
							advanced_form($form_id, $form_args);
						}
						$edit_story_page = get_page_by_path('story-edit');
						$edit_story_url  = get_permalink($edit_story_page->ID);
						$edit_query_args  = add_query_arg(array(
							'story-edit' => $_GET['story-edit'],
							'place-edit' => pol_encrypt_decrypt($story_place_id)
						), $edit_story_url);
						?>
						<div class="wp-block-button">
							<?php if ($place_loc_type != 'geo_loc') :
								$finish_query_args  = add_query_arg(array(
									'story-edit' => $_GET['story-edit'],
									'place-edit' => pol_encrypt_decrypt($story_place_id)
								), site_url());
							?>
								<a id="finish-abt-int" class="wp-block-button__link story-finis-btn" href="<?php echo $form_args['redirect'] ?>">FINISHED!!!</a>


							<?php endif; ?>
							<a class="wp-block-button__link" href="<?php echo $edit_query_args; ?>">RETURN TO EDIT STORY</a>
						</div>

					</div><!-- .entry-content -->
				</div>
			</div><!-- .section-inner -->
		</div><!-- .post-inner -->

	</article><!-- .post -->
	<?php if (is_user_logged_in()) : ?>
		<script>
			jQuery('.signup').css('display', 'none');
			jQuery('.loggedin').css('display', 'block');
			var username = jQuery("#loggedin_username").text();
			jQuery(".loggedin .ancestor-name").text(username);
		</script>
	<?php endif; ?>
<?php
}
get_footer();
