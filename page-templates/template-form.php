<?php

/**
 * Template Name: Form Page
 * 
 * Uses the same content structure as the default singular.php template, 
 * but adds a form if selected.
 * 
 * @package GOAT PoL
 */



$commission = (isset($_POST['popup-commission']) && !empty($_POST['popup-commission'])) ? $_POST['popup-commission'] : '';

// var_dump($commission);
if (!empty($_POST['popup-commission'])) {
	setcookie('popup-commission', $commission, time() + (86400 * 30), "/");
}
if (empty($commission)) {
	$commission = isset($_COOKIE['popup-commission']) && $_COOKIE['popup-commission'] != '' ? $_COOKIE['popup-commission'] : '';
}

get_header();


if (!is_user_logged_in()) {
	echo '<h3 class="add-place-no-login">Please <a href="/login">login</a> to start submitting your story</h3>';
} else {

	global $wp;

	$current_url = home_url(add_query_arg(array(), $wp->request));
	$is_add_story_page = false;
	$is_add_place_page = false;

	if (strpos($current_url, 'add-story') !== false) {
		$is_add_story_page = true;
	} else if (strpos($current_url, 'add-place') !== false) {
		$is_add_place_page = true;
	}

	if ((empty($_GET['place_id']) || !isset($_COOKIE['popup-commission']) || empty($_COOKIE['popup-commission'])) && $is_add_story_page) {

		//if user tries to directly go to the '/add-story' page send them to the '/add-place' page
		?>
		<script>
			window.location.href = '/add-place';
		</script>
		<?php

	} else if ((empty($commission) && $is_add_place_page)) {

		//if 'commission' query var is empty then open popup and ask the user for commission
		?>
			<script>
				var customMessage = '<h5>Please <a id="commission-try-agai n">enter a commission</a> to submit your story</h5>';

				// Create a new div element
				var messageDiv = document.createElement('div');
				messageDiv.innerHTML = customMessage;

				// Append the div to the body
				document.body.appendChild(messageDiv);

				// open the modal to enter the commission
				jQuery(document).ready(function ($) {
					jQuery('.getPassport-modal').click();
					jQuery('.cpm-popup-overlay').hide();
					jQuery('#menu-popup-content').hide();
				});
			</script>
		<?php
	} else {

		global $wpdb;
		$curr_user_id = get_current_user_id();
		$commission_is_valid = false;

		//get user commissions
		// $user_commissions = get_user_meta($curr_user_id, 'commissions', true);

		// if (is_array($user_commissions)) { //check if user has commissions
			// if (array_key_exists($commission, $user_commissions)) { //check if user has this particular commission

				//check if commission is not 'in-use' i.e status should not be equal to '2'
				// echo "SELECT status from {$wpdb->prefix}commission WHERE code = '$commission' AND current_owner = $curr_user_id ";
				$current_owner = $wpdb->get_var($wpdb->prepare("SELECT current_owner from {$wpdb->prefix}commission WHERE code = '$commission' AND current_owner = $curr_user_id AND status != 2"));

				if ((int)$current_owner == (int)$curr_user_id ) {
					$commission_is_valid = true;
				}
			// }
		// }

		//! just for testing
		// if ($is_add_story_page) {
		// 	$commission_is_valid = true;
		// }


		if (!$commission_is_valid) { //if commission not valid

			echo '<div class="commision-not-valid-notice"><h5>This commission is not valid, it may already be in use or may not exist, 
			please check commission status from your <a href="/registration" target="_blank">profile</a> and try again !!</h5></div>';
		} else { //if commission valid
			?>
				<main id="site-content" role="main">
					<div class="site-content-inner">
						<?php
						while (have_posts()) {
							the_post();
							get_template_part('template-parts/single/content', 'form');
						}
						?>
					</div>
				</main>

			<?php
		}
	}
}


if ($is_add_story_page) {

	?>
	<script>
		// open the modal to enter the commission
		jQuery(document).ready(function ($) {
			jQuery(document).on('submit', '.af-form.acf-form', function () {
				$.ajax({
					url: pol_ajax_filters.ajaxurl,
					type: 'POST',
					data: {
						action: 'pol_update_commission_status',
						commission: '<?php echo $commission; ?>',
					},
					success: function (response) {
						// document.cookie = "popup-commission=; expires=Thu, 01 Jan 1970 00:00:00 UTC; path=/;";
					}
				});
			});
		});
	</script>
	<?php


}



get_footer();
