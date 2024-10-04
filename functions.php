<?php

function admin_script_enqueue()
{
	// Enqueue Select2 CSS
	wp_enqueue_style('select2-css', 'https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css');

	// Enqueue Select2 JS
	wp_enqueue_script('select2-js', 'https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js', array('jquery'), null, true);

	wp_enqueue_style('admin-styles', get_template_directory_uri() . '/admin_style.css', array(), time(), 'all');
	wp_enqueue_script('admin-custom-js', get_template_directory_uri() . '/admin_script.js', array('jquery'), time(), true);
}
add_action('admin_enqueue_scripts', 'admin_script_enqueue');

function goatpol_theme_conditions_check()
{
	if (is_front_page() && !is_user_logged_in()) {
		setcookie('frontpage_visited', 'yes', '/');
	}
}
add_action('wp', 'goatpol_theme_conditions_check');

function goatpol_theme_template_redirect()
{
	$frontpage_visited = isset($_COOKIE['frontpage_visited']) && $_COOKIE['frontpage_visited'] == 'yes';
	if (is_front_page() && (is_user_logged_in() || $frontpage_visited)) {
		wp_redirect(site_url('/map'));
	}
}
add_action('template_redirect', 'goatpol_theme_template_redirect');

/**
 * If the user is logged in, redirect them to the map page.
 */
add_action('wp_logout', 'auto_redirect_after_logout');
function auto_redirect_after_logout()
{
	wp_redirect(home_url('/map/'));
	exit();
}

/**
 * If the URL contains the parameters 'aid' and 'mad', then the function pol_make_user_logged_in() is
 * called with the values of those parameters as arguments.
 */
add_action('init', 'autologin_story_verification', 9999);

function autologin_story_verification()
{

	if (isset($_GET['aid']) && isset($_GET['mad'])) {
		pol_make_user_logged_in($_GET['aid'], $_GET['mad']);
	}
	if (isset($_GET['author-edit']) && isset($_GET['author-mail'])) {
		pol_make_user_logged_in($_GET['author-edit'], $_GET['authorr-mail']);
	}

	if (isset($_GET['eid'])) {
		$user = get_user_by('id', $_GET['eid']);
		pol_make_user_logged_in($_GET['eid'], $user->user_email);
	}
}



/**
 * It takes a user ID, email address, and password, decrypts them, and then checks if the user ID and
 * email address match the user ID and email address of the user with the given ID. If they do, it logs
 * the user in. If they don't, it logs the user out.
 * @param aid - The user ID of the user you want to log in.
 * @param emails - The email address of the user you want to log in.
 * @param [password] - The password of the user.
 */
function pol_make_user_logged_in($aid, $emails, $password = false)
{
	$aid = pol_encrypt_decrypt($aid, false);
	$mail = pol_encrypt_decrypt($emails, false);
	//$pass 	= pol_encrypt_decrypt($password,false);
	$user = get_user_by('id', $aid);
	//$verifypass = $pass == $user->user_pass ? true : false;
	$verifymail = $mail == $user->user_email ? true : false;
	$verifyId = $aid == $user->ID ? true : false;
	if (is_user_logged_in()) {
		$verify_logged_in = get_current_user_id() == $aid ? true : false;
		if (!$verify_logged_in) {
			$sessions = WP_Session_Tokens::get_instance(get_current_user_id());
			$sessions->destroy_all();
		}
	}
	if ($verifymail && $verifyId) {
		@wp_set_current_user($aid, $user->user_login);
		@wp_set_auth_cookie($aid, false, is_ssl());
		do_action('wp_login', $user->user_login, $user);
	} else {
		wp_logout();
	}
}



function pol_setup_theme()
{



	// Make the theme translation ready.

	load_theme_textdomain('pol', get_template_directory() . '/languages');



	// Add default posts and comments RSS feed links to head.

	add_theme_support('automatic-feed-links');



	// Let WordPress manage the document title.

	add_theme_support('title-tag');



	// Add support for video, audio and gallery post formats.

	add_theme_support('post-formats', array('video', 'audio', 'gallery'));



	// Switch default core markup to semantic HTML5.

	add_theme_support('html5', array('search-form', 'comment-form', 'comment-list', 'gallery', 'caption'));



	// Add support for excerpts on pages.

	add_post_type_support('page', 'excerpt');



	// Add support for post thumbnails.

	add_theme_support('post-thumbnails');



	// Set the post thumbnail size.

	set_post_thumbnail_size(1568, 1371);



	// Add support for wide and fullwidth block alignments.

	add_theme_support('align-wide');



	// Add support for styles.

	add_theme_support('editor-styles');



	// Enqueue block editor styles.

	add_editor_style(pol_editor_styles());



	// Add support for custom editor font sizes.

	add_theme_support('editor-font-sizes', pol_editor_font_sizes());



	// Add support for the custom editor color palette.

	add_theme_support('editor-color-palette', pol_editor_color_palette());



	// Disable custom gradients in the block editor.

	add_theme_support('disable-custom-gradients');



	// Remove core block patterns.

	remove_theme_support('core-block-patterns');



	// Custom background color.

	add_theme_support(
		'custom-background',
		array(

			'default-color' => 'FFFFFF'

		)
	);



	// Custom logo.

	add_theme_support(
		'custom-logo',
		array(

			'height' => 144,

			'width' => 192,

			'flex-height' => true,

			'flex-width' => true,

			'header-text' => array('site-title', 'site-description'),

		)
	);



	// Set content-width.

	global $content_width;

	if (!isset($content_width)) {

		$content_width = 652;
	}



	// Register navigation menus.

	register_nav_menus(
		array(

			'main' => esc_html__('Main Menu', 'pol'),

			'modal' => esc_html__('Modal Menu', 'pol'),

			'footer' => esc_html__('Footer Menu', 'pol'),

			'social' => esc_html__('Social Menu', 'pol'),

		)
	);
}

add_action('after_setup_theme', 'pol_setup_theme');




/* ------------------------------------------------------------------------------ /*

/*  ENQUEUE STYLES

/* ------------------------------------------------------------------------------ */



function pol_styles()
{


	$theme_version = wp_get_theme('pol')->get('Version');

	$css_dependencies = array();



	// Custom fonts.

	$custom_fonts_url = pol_custom_fonts_url();



	if ($custom_fonts_url) {

		wp_register_style('pol-custom-fonts', $custom_fonts_url, false, 1.0, 'all');

		$css_dependencies[] = 'pol-custom-fonts';
	}



	// ACF

	if (class_exists('ACF')) {

		$css_dependencies[] = 'acf-input';

		$css_dependencies[] = 'acf-global';
	}


	$wp_scripts = wp_scripts();
	wp_enqueue_style('pol-ui-css', 'https://ajax.googleapis.com/ajax/libs/jqueryui/' . $wp_scripts->registered['jquery-ui-core']->ver . '/themes/smoothness/jquery-ui.css', time(), false);


	// Theme styles.

	wp_enqueue_style('pol-style', get_template_directory_uri() . '/style.css', $css_dependencies, time(), 'all');

	wp_enqueue_style('pol-theme-styles', get_template_directory_uri() . '/assets/css/theme.css', array('pol-style'), $theme_version, 'all');

	wp_enqueue_style('custom-css', get_template_directory_uri() . '/assets/css/custom.css', 1.0, 'all');

	wp_enqueue_style('fontawesome-css', 'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css', 1.0, 'all');

	// Print styles.

	wp_enqueue_style('pol-print-styles', get_template_directory_uri() . '/assets/css/print.css', false, $theme_version, 'print');



	// Js
	wp_enqueue_script('jquery');
	$timestamp = time();
	wp_enqueue_script('custom-js', get_template_directory_uri() . '/assets/js/custom.js', array('jquery'), $timestamp, TRUE);

	wp_enqueue_script('jquery-ui-autocomplete');

	//wp_enqueue_script('infinite-js', 'https://unpkg.com/infinite-scroll@4/dist/infinite-scroll.pkgd.min.js', array('jquery'), 'all', TRUE);
	//wp_enqueue_script('slim-js', 'https://code.jquery.com/jquery-3.5.1.slim.min.js', array('jquery'), 'all', TRUE);

	//for submenu
	wp_enqueue_script('jquery-modal-js', 'https://cdnjs.cloudflare.com/ajax/libs/jquery-modal/0.9.1/jquery.modal.min.js', array('jquery'), '0.9.1', true);
	wp_enqueue_style('jquery-modal-css', 'https://cdnjs.cloudflare.com/ajax/libs/jquery-modal/0.9.1/jquery.modal.min.css', array(), '0.9.1', 'all');
	wp_enqueue_script('jquery', 'https://cdnjs.cloudflare.com/ajax/libs/jquery/3.0.0/jquery.min.js', array(), '3.0.0', true);


	wp_register_style('select2css', 'https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/css/select2.min.css');
	wp_register_script('select2', 'https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/js/select2.min.js', array('jquery'), '4.0.13', true);
	wp_enqueue_style('select2css');
	wp_enqueue_script('select2');


	// //!jquery-ui js/css used for tooltip in 'star' icon used to favorite stories -- additional --remove this any other jquery ui compoenent breaks
	// if(is_author()){

	// 	wp_register_style('jquery-ui-css', 'https://code.jquery.com/ui/1.10.4/themes/ui-lightness/jquery-ui.css', time(), true);
	// 	wp_enqueue_style('jquery-ui-css');
	// 	wp_register_script('jquery-ui-js', 'https://code.jquery.com/ui/1.10.4/jquery-ui.js', array('jquery'), time(), true);
	// 	wp_enqueue_script('jquery-ui-js');
	// }
}

add_action('wp_enqueue_scripts', 'pol_styles', 99);



function pol_force_jquery_on_footer()
{
	wp_dequeue_script('jquery');
	wp_dequeue_script('jquery-core');
	wp_dequeue_script('jquery-migrate');
	wp_enqueue_script('jquery', false, array(), false, true);
	wp_enqueue_script('jquery-core', false, array(), false, true);
	wp_enqueue_script('jquery-migrate', false, array(), false, true);
}
add_action('wp_enqueue_scripts', 'pol_force_jquery_on_footer', 10);


/**

 * Admin styles.

 */

function pol_admin_styles()
{



	$theme_version = wp_get_theme('pol')->get('Version');

	$css_dependencies = array();



	// ACF

	if (class_exists('ACF')) {

		$css_dependencies[] = 'acf-input';
	}



	wp_enqueue_style('pol-admin-styles', get_template_directory_uri() . '/assets/css/admin.css', $css_dependencies, time());
}

add_action('admin_enqueue_scripts', 'pol_admin_styles');


// Fetches Story Writer


function fetch_story_writer_name($story_id)
{

	$writer_name = get_field('story_nom_de_plume', $story_id);

	if (!empty($writer_name)) {

		$name = $writer_name;
	} else {
		$author_id = get_post_field('post_author', $story_id);
		$name = get_the_author_meta('display_name', $author_id);
	}

	$wrappped_name = !empty($name) ? "<h2 class='writers-name'><a>by " . $name . "</a></h2>" : '';

	return $wrappped_name;
}


/**

 * Login styles.

 */

function pol_login_styles()
{



	$theme_version = wp_get_theme('pol')->get('Version');

	$css_dependencies = array();



	wp_enqueue_style('pol-login-styles', get_template_directory_uri() . '/assets/css/login.css', $css_dependencies, $theme_version);
	wp_enqueue_script('pol-login-script', get_template_directory_uri() . '/assets/js/login.js', ['jquery'], $theme_version);
}

add_action('login_enqueue_scripts', 'pol_login_styles');





/* ------------------------------------------------------------------------------ /*

/*  ENQUEUE SCRIPTS

/* ------------------------------------------------------------------------------ */



function pol_scripts()
{



	$theme_version = wp_get_theme('pol')->get('Version');



	// Comments.

	if (is_singular() && comments_open() && get_option('thread_comments')) {

		wp_enqueue_script('comment-reply');
	}



	// Dependencies.

	$js_dependencies = array('jquery', 'imagesloaded');



	// CSS variables ponyfill.

	wp_register_script('pol-css-vars-ponyfill', get_template_directory_uri() . '/assets/js/vendor/css-vars-ponyfill.min.js', array(), '3.6.0');

	$js_dependencies[] = 'pol-css-vars-ponyfill';



	// Isotope JS.

	wp_register_script('isotope', get_template_directory_uri() . '/assets/js/vendor/isotope.pkgd.min.js', array(), '3.0.6');

	$js_dependencies[] = 'isotope';



	// Google Maps

	if (is_page_template('page-templates/template-map.php') || 'place' == get_post_type() || is_page_template('page-templates/template-custommap.php')) {

		$google_api_key = pol_google_api_key();

		$places = '&libraries=places';



		wp_register_script('google-maps', 'https://maps.googleapis.com/maps/api/js?key=' . $google_api_key . $places, array('jquery'), null);

		$js_dependencies[] = 'google-maps';
	}



	// Theme scripts.

	//wp_enqueue_script( 'pol-scripts', get_template_directory_uri() . '/assets/js/scripts.js', $js_dependencies, $theme_version );

	wp_enqueue_script('pol-scripts', get_template_directory_uri() . '/assets/js/scripts.js', $js_dependencies, time());



	// AJAX Load More.

	wp_localize_script(
		'pol-scripts',
		'pol_ajax_load_more',
		array(

			'ajaxurl' => esc_url(admin_url('admin-ajax.php')),

		)
	);



	// AJAX Filters.

	wp_localize_script(
		'pol-scripts',
		'pol_ajax_filters',
		array(

			'ajaxurl' => esc_url(admin_url('admin-ajax.php')),

		)
	);

	// Show explicit marker on load
	wp_localize_script(
		'pol-scripts',
		'pol_ajax_map_parameters',
		array(
			'place_id' => isset($_GET['pid']) ? intval($_GET['pid']) : null,
			'story_id' => isset($_GET['sid']) ? intval($_GET['sid']) : null,
			'show_large_story_popup' => get_field('show_large_story_popup', 'option') == 1,
			'show_stories_on_search' => get_field('show_stories_on_search', 'option') == 1,
		)
	);
}

add_action('wp_enqueue_scripts', 'pol_scripts');






add_action('admin_footer', 'pol_footer_script');

function pol_footer_script()
{
?>
	<script>
		jQuery(document).ready(function() {

			acf.add_filter('google_map_result', function(result, geocoderResult, map, field) {
				console.log(geocoderResult);
				var old_result = result;
				var oaddress = old_result.address;
				var new_address = oaddress.substring(oaddress.indexOf(",") + 1);
				result.address = new_address;
				return result;

			});
		});
	</script>
	<?php
}

/**

 * Admin scripts.

 */

function pol_admin_scripts()
{



	$theme_version = wp_get_theme('pol')->get('Version');



	// Dependencies.

	$js_dependencies = array('jquery', 'wp-tinymce');





	// Admin scripts.

	wp_enqueue_script('pol-admin-scripts', get_template_directory_uri() . '/assets/js/admin.js', $js_dependencies, time(), true);


	$screen = get_current_screen();
	if ($screen && $screen->base == 'post' && $screen->post_type == 'story') {

		// for story post type
		wp_enqueue_script('pol-admin-story-script', get_template_directory_uri() . '/assets/js/story-script.js', $js_dependencies, time(), true);
	}
}

add_action('admin_enqueue_scripts', 'pol_admin_scripts');





/**

 * Editor scripts.

 */

function pol_editor_scripts()
{



	$theme_version = wp_get_theme('pol')->get('Version');

	$js_dependencies = array('wp-blocks', 'wp-dom-ready', 'wp-edit-post');



	wp_enqueue_script('pol-editor-scripts', get_template_directory_uri() . '/assets/js/editor.js', $js_dependencies, $theme_version, true);
}

add_action('enqueue_block_editor_assets', 'pol_editor_scripts');





/* ------------------------------------------------------------------------------ /*

/*  REQUIRED FILES

/* ------------------------------------------------------------------------------ */



// Helpers.

require get_template_directory() . '/inc/helpers.php';



// Template functions.

require get_template_directory() . '/inc/template-functions.php';



// Template tags.

require get_template_directory() . '/inc/template-tags.php';



// Map functions.

require get_template_directory() . '/inc/map-functions.php';



// Options.

require get_template_directory() . '/inc/options.php';



// SVG Icons class.

require get_template_directory() . '/inc/classes/class-pol-svg-icons.php';



// Customizer Settings class.

require get_template_directory() . '/inc/classes/class-pol-customizer.php';



// Payment admin submenu page

require get_template_directory() . '/inc/story-payment.php';


// Commission admin submenu page

require get_template_directory() . '/inc/story-commission.php';



// for automated mail 

require get_template_directory() . '/inc/automated-mail.php';






// Login Check

if (is_user_logged_in()) {

	// var_dump($username);

	add_action('wp_body_open', 'add_custom_html');

	function add_custom_html()
	{

		$user_records = wp_get_current_user();

		// echo $user_records->user_login;

		$username = $user_records->user_login;

	?>

		<div id="loggedin_username" style="display: none;">
			<?php echo $username; ?>
		</div>

		<style>
			.loggedin.menu-item {

				display: block !important;

			}

			.signup.menu-item {

				display: none;

			}
		</style>

	<?php }
}



add_action('wp_footer', 'addition_load_script');

function addition_load_script()
{

	?>

	<script type="text/javascript">
		jQuery(() => {
			var username = jQuery("#loggedin_username").text();

			jQuery(".loggedin .ancestor-name").text(username);

		})
	</script>

	<?php }

//Infinite Scroll home page sidebar

function wp_infinitepaginate()
{

	$paged = $_POST['page'];

	$value = $_POST['value'];


	if ($value == 'quick-takes') {

		$args = array(

			'post_type' => 'story',

			'posts_per_page' => 10,

			'paged' => $paged,

			'post_status' => 'publish',

			'order' => 'desc',

			'meta_query' => array(

				array(

					'key' => 'story_type_labels',

					'value' => 'short-story',
				)
			)

		);
	} else if ($value == 'most-popular') {

		$args = array(

			'post_type' => 'story',

			'posts_per_page' => 10,

			'paged' => $paged,

			'post_status' => 'publish',

			'order' => 'desc',

			'suppress_filters' => false,

			'orderby' => 'post_views',

			'fields' => ''

		);
	} else if ($value == 'least-popular') {

		$args = array(

			'post_type' => 'story',

			'posts_per_page' => 10,

			'paged' => $paged,

			'post_status' => 'publish',

			'order' => 'asc',

			'suppress_filters' => false,

			'orderby' => 'post_views',

			'fields' => ''

		);
	} else if ($value == 'most-recent') {

		$args = array(

			'post_type' => 'story',

			'post_status' => 'publish',

			'paged' => $paged,

			'posts_per_page' => 11

		);
	} else if ($value == 'nearby') {

		$ip = $_SERVER['REMOTE_ADDR'];
		$location = unserialize(file_get_contents('http://www.geoplugin.net/php.gp?ip=' . $ip));
		$lat = $location['geoplugin_latitude'];
		$lng = $location['geoplugin_longitude'];
		$nearby = my_get_nearby_locations($lat, $lng);
		$nearby_placeID = wp_list_pluck($nearby, 'post_id');

		$merge = array();
		foreach ($nearby_placeID as $near) {

			$array_posts = get_post_meta($near, 'place_stories', true);
			foreach ($array_posts as $array_data) {

				$merge[] = $array_data;
			}
		}

		$args = array(

			'post_type' => 'story',

			'paged' => $paged,

			'posts_per_page' => 10,

			'post_status' => 'publish',

			'post__in' => $merge,

		);
	} else {
		echo 'No Posts Found';
	}
	$query = new WP_Query($args);

	if ($query->have_posts()) {

		while ($query->have_posts()) :
			$query->the_post();

			$location = get_post_meta(get_the_id(), 'story_place_name', true);

			$current_post_status = get_post_status(get_the_ID());
			$place_id = get_post_meta(get_the_ID(), 'stories_place', true);
			$locs = get_the_title($place_id);

	?>

			<li class=" infinite-post-id <?php echo $current_post_status . '-cpm'; ?>" data-postId="<?php echo get_the_ID(); ?>" data-id="<?= $place_id ?>">

				<?php

				$image = get_the_post_thumbnail_url();

				if ($image) { ?>

					<div class="image-warp">

						<a href="">

							<img src="<?php echo $image; ?>">

						</a>

					</div>

				<?php

				} else {
				?>
					<div class="image-warp">
						<a href="">
							<?php echo pol_get_random_goat(); ?>
							<!-- <img src="<?php // echo bloginfo('template_url'); 
											?>/assets/img/noimage.jpg"> -->

						</a>
					</div>

				<?php } ?>

				<div class="blog-article">

					<h3><a href="<?php echo add_query_arg('place', $place_id, site_url('/map')) ?>">
							<?php the_title(); ?>
						</a></h3>

					<?php echo fetch_story_writer_name(get_the_id()); ?>

					<?php if (!empty($locs)) : ?>

						<h2 class="writers-name writers-address">
							<span class="dashicons dashicons-location"></span><a href="<?php echo add_query_arg('place', $place_id, site_url('/map')) ?>">
								<?= $locs; ?>
							</a>
						</h2>

					<?php endif; ?>

				</div>

			</li>

	<?php



		endwhile;
	}

	wp_die();
}

add_action('wp_ajax_wp_infinitepaginate', 'wp_infinitepaginate'); // for logged in user

add_action('wp_ajax_nopriv_wp_infinitepaginate', 'wp_infinitepaginate'); // if user not logged in



// For redegin reset password mail template

function pol_set_content_type()
{

	return "text/html";
}

add_filter('retrieve_password_message', 'my_retrieve_password_message', 10, 4);

function my_retrieve_password_message($message, $key, $user_login, $user_data)
{



	add_filter('wp_mail_content_type', 'pol_set_content_type');

	// Start with the default content.

	$site_name = wp_specialchars_decode(get_option('blogname'), ENT_QUOTES);

	$message = __('

	<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">

	<html xmlns="http://www.w3.org/1999/xhtml" xmlns="http://www.w3.org/1999/xhtml" style="font-family: "Helvetica Neue", "Helvetica", Helvetica, Arial, sans-serif; font-size: 100%; line-height: 1.6; margin: 0; padding: 0;">

	<head>

		<meta name="viewport" content="width=device-width" />



		<meta http-equiv=3D"Content-Type" content=3D"text/html; charset=3Dutf-8" = />

		<title>An update was made to the task you are involvd in.</title>

	</head>

	<body bgcolor="#f6f6f6" style="font-family: ' . 'Helvetica Neue' . ', ' . 'Helvetica' . ', Helvetica, Arial, sans-serif; font-size: 100%; line-height: 1.6; -webkit-font-smoothing: antialiased; -webkit-text-size-adjust: none; width: 100% !important; height: 100%; margin: 0; padding: 0; ">



	<!-- body -->

	<table class="body-wrap" bgcolor="#f6f6f6" style="font-family: ' . 'Helvetica Neue' . ', ' . 'Helvetica' . ', Helvetica, Arial, sans-serif; font-size: 100%; line-height: 1.6; width: 100%; margin: 0; padding: 20px;"><tr style="font-family: ' . 'Helvetica Neue' . ', ' . 'Helvetica' . ', Helvetica, Arial, sans-serif; font-size: 100%; line-height: 1.6; margin: 0; padding: 0;"><td style="font-family: ' . 'Helvetica Neue' . ', ' . 'Helvetica' . ', Helvetica, Arial, sans-serif; font-size: 100%; line-height: 1.6; margin: 0; padding: 0;"></td>

			<td class="container" bgcolor="#FFFFFF" style="font-family: ' . 'Helvetica Neue' . ', ' . 'Helvetica' . ', Helvetica, Arial, sans-serif; font-size: 100%; line-height: 1.6; display: block !important; max-width: 600px !important; clear: both !important; margin: 0 auto; padding: 20px; border: 1px solid #f0f0f0;">



				<!-- content -->

				<div class="content" style="font-family: ' . 'Helvetica Neue' . ', ' . 'Helvetica' . ', Helvetica, Arial, sans-serif; font-size: 100%; line-height: 1.6; max-width: 600px; display: block; margin: 0 auto; padding: 0;">

				<table style="font-family: ' . 'Helvetica Neue' . ', ' . 'Helvetica' . ', Helvetica, Arial, sans-serif; font-size: 100%; line-height: 1.6; width: 100%; margin: 0; padding: 0;"><tr style="font-family: ' . 'Helvetica Neue' . ', ' . 'Helvetica' . ', Helvetica, Arial, sans-serif; font-size: 100%; line-height: 1.6; margin: 0; padding: 0;"><td style="font-family: ' . 'Helvetica Neue' . ', ' . 'Helvetica' . ', Helvetica, Arial, sans-serif; font-size: 100%; line-height: 1.6; margin: 0; padding: 0;">

							<p><img src="' . site_url() . '/wp-content/uploads/2022/04/logo.jpg" width="150px;" /></p>

	') . "\r\n\r\n";

	$message .= __('

		<p style="margin-top:50px;">

		Dear ' . $user_login . ',<br>

				At your request we have just confirmed or changed your login password. If you did not press "Confirm (new or

				old) password" on your login screen, please contact us immediately at thegoatpol@tutanota.com.

		</p>

	') . "<br>";



	$message .= __('To reset your password, visit the following address:') . "<br>";

	$message .= '<a href="';

	$message .= network_site_url("wp-login.php?action=rp&key=$key&login=" . rawurlencode($user_login), 'login');

	$message .= '">"';

	$message .= network_site_url("wp-login.php?action=rp&key=$key&login=" . rawurlencode($user_login), 'login');

	$message .= '"</a>"<br>';

	$message .= __('

		<p>Thank you,<br/>

		The GOAT PoL<br/></p>

	') . "<br>";

	$message .= __('

		<table style="font-family: ' . 'Helvetica Neue' . ', ' . 'Helvetica' . ', Helvetica, Arial, sans-serif; font-size: 100%; text-align:right; line-height: 1.6; width: 100%; margin: 0; padding: 0;"><tr style="font-family: ' . 'Helvetica Neue' . ', ' . 'Helvetica' . ', Helvetica, Arial, sans-serif; font-size: 100%; line-height: 1.6; margin: 0; padding: 0;"><td class="padding" style="font-family: ' . 'Helvetica Neue' . ', ' . 'Helvetica' . ', Helvetica, Arial, sans-serif; font-size: 100%; line-height: 1.6; margin: 0; padding: 10px 0;">

		<img src="' . site_url() . '/wp-content/uploads/2022/02/GOAT_21-scaled.jpg" width="150px;" />

		</td>

			</tr></table>

			<img style="margin-top:30px;" src="' . site_url() . '/wp-content/themes/goatpol/assets/img/FullTitle_transparent.png" width="580px"/>

	</td>

	</tr></table></div>

	<!-- /content -->



	</td>

	<td style="font-family: ' . 'Helvetica Neue' . ', ' . 'Helvetica' . ', Helvetica, Arial, sans-serif; font-size: 100%; line-height: 1.6; margin: 0; padding: 0;"></td>

	</tr></table><!-- /body --><!-- footer --><table class="footer-wrap" style="font-family: ' . 'Helvetica Neue' . ', ' . 'Helvetica' . ', Helvetica, Arial, sans-serif; font-size: 100%; line-height: 1.6; width: 100%; clear: both !important; margin: 0; padding: 0;"><tr style="font-family: ' . 'Helvetica Neue' . ', ' . 'Helvetica' . ', Helvetica, Arial, sans-serif; font-size: 100%; line-height: 1.6; margin: 0; padding: 0;"><td style="font-family: ' . 'Helvetica Neue' . ', ' . 'Helvetica' . ', Helvetica, Arial, sans-serif; font-size: 100%; line-height: 1.6; margin: 0; padding: 0;"></td>

	<td class="container" style="font-family: ' . 'Helvetica Neue' . ', ' . 'Helvetica' . ', Helvetica, Arial, sans-serif; font-size: 100%; line-height: 1.6; display: block !important; max-width: 600px !important; clear: both !important; margin: 0 auto; padding: 0;">



	<!-- content -->

	<div class="content" style="font-family: ' . 'Helvetica Neue' . ', ' . 'Helvetica' . ', Helvetica, Arial, sans-serif; font-size: 100%; line-height: 1.6; max-width: 600px; display: block; margin: 0 auto; padding: 0;">

	<table style="font-family: ' . 'Helvetica Neue' . ', ' . 'Helvetica' . ', Helvetica, Arial, sans-serif; font-size: 100%; line-height: 1.6; width: 100%; margin: 0; padding: 0;"><tr style="font-family: ' . 'Helvetica Neue' . ', ' . 'Helvetica' . ', Helvetica, Arial, sans-serif; font-size: 100%; line-height: 1.6; margin: 0; padding: 0;"><td align="center" style="font-family: ' . 'Helvetica Neue' . ', ' . 'Helvetica' . ', Helvetica, Arial, sans-serif; font-size: 100%; line-height: 1.6; margin: 0; padding: 0;">



		</td>

	</tr></table></div>

	<!-- /content -->



	</td>

	<td style="font-family: ' . 'Helvetica Neue' . ', ' . 'Helvetica' . ', Helvetica, Arial, sans-serif; font-size: 100%; line-height: 1.6; margin: 0; padding: 0;"></td>

	</tr></table><!-- /footer --></body>



	</html>

	') . "<br>";

	return $message;
}


// Sidebar Ajax Functions

function wp_select_ajax_filter()
{

	//$paged = $_POST['page'];

	$value = $_POST['value'];

	if ($value == 'quick-takes') {

		$args = array(

			'post_type' => 'story',

			'posts_per_page' => 10,

			'post_status' => 'publish',

			'order' => 'desc',

			'meta_query' => array(

				array(

					'key' => 'story_type_labels',

					'value' => 'short-story',
				)
			)

		);
	} else if ($value == 'most-popular') {

		$args = array(

			'post_type' => 'story',

			'posts_per_page' => 10,

			'post_status' => 'publish',

			'order' => 'desc',

			'suppress_filters' => false,

			'orderby' => 'post_views',

			// 'fields' 		   => ''

		);
	} else if ($value == 'least-popular') {

		$args = array(

			'post_type' => 'story',

			'posts_per_page' => 10,

			'post_status' => 'publish',

			'order' => 'asc',

			'suppress_filters' => false,

			'orderby' => 'post_views',

			'fields' => ''

		);
	} else if ($value == 'most-recent') {

		$args = array(

			'post_type' => 'story',

			'posts_per_page' => 10,

			//'paged'			 => 1,

			'post_status' => 'publish',

		);
	} else if ($value == 'nearby') {

		$ip = $_SERVER['REMOTE_ADDR'];
		$location = unserialize(file_get_contents('http://www.geoplugin.net/php.gp?ip=' . $ip));
		$lat = $location['geoplugin_latitude'];
		$lng = $location['geoplugin_longitude'];
		$nearby = my_get_nearby_locations($lat, $lng);
		$nearby_placeID = wp_list_pluck($nearby, 'post_id');

		$merge = array();
		foreach ($nearby_placeID as $near) {

			$array_posts = get_post_meta($near, 'place_stories', true);
			foreach ($array_posts as $array_data) {

				$merge[] = $array_data;
			}
		}

		$args = array(

			'post_type' => 'story',

			'posts_per_page' => 10,

			'post_status' => 'publish',

			'post__in' => $merge,

		);
	}
	$most_viewed = new WP_Query($args);

	$max_num_page = $most_viewed->max_num_pages;


	?>
	<span style="display:hidden ;" id="select-infinite-list" data-maxpage=<?php echo $max_num_page; ?>></span>
	<?php

	if ($most_viewed->have_posts()) {

		while ($most_viewed->have_posts()) :
			$most_viewed->the_post();

			$location = get_post_meta(get_the_id(), 'story_place_name', true);

			//foreach($most_viewed as $single_most_viewed){

			$current_post_status = get_post_status(get_the_ID());
			$place_id = get_post_meta(get_the_ID(), 'stories_place', true);
			$locs = get_the_title($place_id);


	?>

			<li class="infinite-post-id <?php echo $current_post_status . '-cpm'; ?>" data-postId="<?php echo get_the_ID(); ?>" data-id="<?php echo $place_id; ?>">

				<?php

				$image = get_the_post_thumbnail_url();

				if ($image) { ?>

					<div class="image-warp">

						<a href="#">

							<img src="<?php echo $image; ?>">

						</a>

					</div>

				<?php

				} else {

				?>

					<div class="image-warp">

						<a href="#">
							<?php echo pol_get_random_goat(); ?>
							<!-- <img src="<?php // echo bloginfo('template_url'); 
											?>/assets/img/noimage.jpg"> -->

						</a>

					</div>

				<?php } ?>

				<div class="blog-article">

					<h3><a href="<?php echo add_query_arg('place', $place_id, site_url('/map')) ?>">
							<?php the_title(); ?>
						</a></h3>
					<?php echo fetch_story_writer_name(get_the_id()); ?>

					<?php if (!empty($locs)) : ?>
						<h2 class="writers-name writers-address">
							<span class="dashicons dashicons-location"></span><a href="<?php echo add_query_arg('place', $place_id, site_url('/map')) ?>">
								<?= $locs; ?>
							</a>
						</h2>

					<?php endif; ?>

				</div>

			</li>

			<?php

		//}

		endwhile;
	}

	die;
}


// add_action('wp_ajax_wp_select_ajax_filter', 'wp_select_ajax_filter'); // for logged in user
// add_action('wp_ajax_nopriv_wp_select_ajax_filter', 'wp_select_ajax_filter'); // if user not logged in
// Daily RAE Emails.6
function cpm_draft_email($post_id)
{

	global $wpdb;
	$option_data = get_option('last_run_date'); //$wpdb->get_results("SELECT * FROM `wp_options` WHERE option_name = 'last_run_date'");

	$present_day = date("Y-m-d");
	// update_option('last_run_date', "2022-06-30");

	// var_dump($option_data);


	// $time = date("H:i");
	$date = new DateTime("now", new DateTimeZone('Europe/Amsterdam'));
	$time = $date->format('H:i');
	// echo $time;

	$next_time = '17:00';
	$get_time = strtotime($next_time);


	$current_time = strtotime($time);

	$get_date = $option_data;
	/* $post_sql = "SELECT * FROM {$wpdb->prefix}posts WHERE post_status='draft' limit 1";
																	  $result_posts = $wpdb->get_results($post_sql);
																	  var_dump($result_posts); */
	$draft_post_sql = "SELECT {$wpdb->prefix}posts.post_status, {$wpdb->prefix}postmeta.meta_value FROM {$wpdb->prefix}posts INNER JOIN {$wpdb->prefix}postmeta ON {$wpdb->prefix}posts.ID={$wpdb->prefix}postmeta WHERE ({$wpdb->prefix}posts.post_status = 'draft' AND {$wpdb->prefix}postmeta.meta_value = 'short-story')
	 OR ({$wpdb->prefix}posts.post_status = 'draft' AND {$wpdb->prefix}postmeta.meta_value = 'long-story ')";
	$result_draft_posts = $wpdb->get_results($draft_post_sql);

	$draft_list_sql = "SELECT *  from {$wpdb->prefix}posts WHERE {$wpdb->prefix}posts.post_status = 'draft' AND {$wpdb->prefix}posts.post_date > DATE_SUB(NOW(), INTERVAL 1 DAY) ORDER BY {$wpdb->prefix}posts.ID DESC";
	$result_draft_post_lists = $wpdb->get_results($draft_list_sql);

	$contributors_seeking_commission = get_users(
		array(
			'meta_query' => array(
				array(
					'key' => 'currently_seeking_commission',
					'value' => 'i:0;i:1;',
					'compare' => 'LIKE'
				)
			)
		)
	);

	$editors2 = array((object) array('user_email' => 'kanxo.stha1998@gmail.com'), (object) array('user_email' => 'utsavsinghrathour@gmail.com'));


	if ($current_time >= $get_time && $get_date < $present_day) {
		// if ($current_time != $get_time) {

		// $args = array(
		// 	'role' => 'editor',
		// 	'orderby' => 'user_nicename',
		// 	'order' => 'ASC'
		//    );
		$args = array(
			'orderby' => 'user_nicename',
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
		$post_sql = "SELECT * FROM {$wpdb->prefix}posts WHERE post_status='draft' limit 1";
		$result_posts = $wpdb->get_results($post_sql);
		$draft_post = $result_posts[0]->post_status;



		// if ($draft_post == 'draft' && !empty($result_draft_posts)) {
		if ($draft_post == 'draft') {
			foreach ($editors as $editor) {
				ob_start();

				add_filter('wp_mail_content_type', 'pol_set_content_type');

				// echo $user_record->user_login;

				// mail send process

				$to = $editor->user_email;


				$subject = "Email for Editors";

			?>



				<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">

				<html xmlns="http://www.w3.org/1999/xhtml" xmlns="http://www.w3.org/1999/xhtml" style="font-family: 'Helvetica Neue', 'Helvetica', Helvetica, Arial, sans-serif; font-size: 100%; line-height: 1.6; margin: 0; padding: 0;">

				<head>

					<meta name="viewport" content="width=device-width" />



					<meta http-equiv=3D"Content-Type" content=3D"text/html; charset=3Dutf-8"= />

					<title>An update was made to the task you are involvd in.</title>

				</head>

				<body bgcolor="#f6f6f6" style="font-family: 'Helvetica Neue', 'Helvetica', Helvetica, Arial, sans-serif; font-size: 100%; line-height: 1.6; -webkit-font-smoothing: antialiased; -webkit-text-size-adjust: none; width: 100% !important; height: 100%; margin: 0; padding: 0; ">



					<!-- body -->

					<table class="body-wrap" bgcolor="#f6f6f6" style="font-family: 'Helvetica Neue', 'Helvetica', Helvetica, Arial, sans-serif; font-size: 100%; line-height: 1.6; width: 100%; margin: 0; padding: 20px;">
						<tr style="font-family: 'Helvetica Neue', 'Helvetica', Helvetica, Arial, sans-serif; font-size: 100%; line-height: 1.6; margin: 0; padding: 0;">
							<td style="font-family: 'Helvetica Neue', 'Helvetica', Helvetica, Arial, sans-serif; font-size: 100%; line-height: 1.6; margin: 0; padding: 0;">
							</td>

							<td class="container" bgcolor="#FFFFFF" style="font-family: 'Helvetica Neue', 'Helvetica', Helvetica, Arial, sans-serif; font-size: 100%; line-height: 1.6; display: block !important; max-width: 600px !important; clear: both !important; margin: 0 auto; padding: 20px; border: 1px solid #f0f0f0;">



								<!-- content -->

								<div class="content" style="font-family: 'Helvetica Neue', 'Helvetica', Helvetica, Arial, sans-serif; font-size: 100%; line-height: 1.6; max-width: 600px; display: block; margin: 0 auto; padding: 0;">


									<?php
									if (!empty($result_draft_post_lists)) { ?>
										<table style="font-family: 'Helvetica Neue', 'Helvetica', Helvetica, Arial, sans-serif; font-size: 100%; line-height: 1.6; width: 100%; margin: 0; padding: 0;">
											<tr style="font-family: 'Helvetica Neue', 'Helvetica', Helvetica, Arial, sans-serif; font-size: 100%; line-height: 1.6; margin: 0; padding: 0;">
												<td style="font-family: 'Helvetica Neue', 'Helvetica', Helvetica, Arial, sans-serif; font-size: 100%; line-height: 1.6; margin: 0; padding: 0;">

													<p><img src="<?php echo site_url(); ?>/wp-content/uploads/2022/06/logo.jpg" width="150px" /></p>
													<p style="margin-top:50px;">

														Dear RAEs, There are stories that need a RAE. You can claim any of them by clicking
														on "Claim this story," at the right-hand side of the listings, below. Or, you can
														login to thegoatpol.org and open the dashboard's backend to open the list of
														"stories" there. In this list you can click on "claim this story" for whichever
														stories you want to claim as a RAE.

														<?php echo home_url('/wp-admin/edit.php?post_type=story'); ?>

													</p>

												</td>

											</tr>
										</table>

										<table style="border-collapse: collapse; font-family: 'Helvetica Neue', 'Helvetica', Helvetica, Arial, sans-serif; font-size: 100%; line-height: 1.6; margin: 0; padding: 0;">
											<tr style="background-color: #f6f6f6; text-align: left; font-family: 'Helvetica Neue', 'Helvetica', Helvetica, Arial, sans-serif; font-size: 100%; line-height: 1.6; margin: 0; padding: 0;">
												<th style="padding: 10px; border: 1px solid black;">SNO</th>
												<th style="padding: 10px; border: 1px solid black;">TITLE</th>
												<th style="padding: 10px; border: 1px solid black;">LOCATION</th>
												<th style="padding: 10px; border: 1px solid black;">AUTHOR</th>
												<th style="padding: 10px; border: 1px solid black;">Story Claim Status</th>
											</tr>
											<?php
											$i = 1;
											foreach ($result_draft_post_lists as $index => $result_draft_post_list) {
												$post_title = $result_draft_post_list->post_title;
												$post_id = $result_draft_post_list->ID;
												$location = get_post_meta($post_id, 'story_place_name', true);
												$author = get_post_meta($post_id, 'story_nom_de_plume', true);
												$maybeClaimed = get_post_meta($post_id, 'claimed_by', true);
											?>
												<tr style="text-align: left; font-family: 'Helvetica Neue', 'Helvetica', Helvetica, Arial, sans-serif; font-size: 100%; line-height: 1.6; margin: 0; padding: 0;">
													<td style="padding: 10px; border: 1px solid black; width: 4%">
														<?php echo $index + 1; ?>
													</td>
													<td style="padding: 10px; border: 1px solid black;"><a href="<?php the_permalink($post_id); ?>" target="_blank">
															<?php echo $post_title; ?>
														</a></td>
													<td style="padding: 10px; border: 1px solid black;">
														<a href="<?php echo home_url(); ?>/wp-admin/post.php?post=<?php echo $post_id; ?>&action=edit">
															<?php echo $location ?>
														</a>
													</td>
													<td style="padding: 10px; border: 1px solid black;">
														<?php echo $author; ?>
													</td>
													<td style="padding: 10px; border: 1px solid black;">
														<?php
														if (!empty($maybeClaimed)) { ?>
															<a href="<?php echo home_url(); ?>/wp-admin/edit.php?post_type=story&story_id=<?php echo $post_id ?>&claim=1">Claim
																this Story</a>
														<?php } else { ?>
															<a href="<?php echo home_url(); ?>/wp-admin/edit.php?post_type=story&story_id=<?php echo $post_id ?>&claim=1">Claim
																this Story</a>
														<?php }
														?>
													</td>
												</tr>


											<?php

											} ?>
										</table>
									<?php } else { ?>
										<table style="font-family: 'Helvetica Neue', 'Helvetica', Helvetica, Arial, sans-serif; font-size: 100%; line-height: 1.6; width: 100%; margin: 0; padding: 0;">
											<tr style="font-family: 'Helvetica Neue', 'Helvetica', Helvetica, Arial, sans-serif; font-size: 100%; line-height: 1.6; margin: 0; padding: 0;">
												<td style="font-family: 'Helvetica Neue', 'Helvetica', Helvetica, Arial, sans-serif; font-size: 100%; line-height: 1.6; margin: 0; padding: 0;">

													<p><img src="<?php echo site_url(); ?>/wp-content/uploads/2022/06/logo.jpg" width="150px" /></p>
													<p style="margin-top:50px;">
														There weren't any new stories submitted to the site in the last 24 hours.
													</p>

												</td>

											</tr>
										</table>
									<?php }
									?>

									<?php if (sizeof($contributors_seeking_commission) > 0) { ?>
										<h3>Contributors Currently Seeking Commission</h3>
										<table style="font-family: 'Helvetica Neue', 'Helvetica', Helvetica, Arial, sans-serif; font-size: 100%; line-height: 1.6; width: 100%; margin: 0; padding: 0;">
											<tr>
												<th>Date</th>
												<th>Name</th>
											</tr>
											<?php
											$contributors_id_seeking_commission = [];
											foreach ($contributors_seeking_commission as $contributor) {
												if (in_array($contributor->ID, $contributors_id_seeking_commission)) {
													continue;
												}
												array_push($contributors_id_seeking_commission, $contributor->ID);
											?>
												<tr>
													<td>
														<?php echo (get_user_meta($contributor->ID, 'currently_seeking_commission', true))[1]; ?>
													</td>
													<td>
														<?php
														echo '<a href="' . get_author_posts_url($contributor->ID) . '">' .
															get_user_meta($contributor->ID, 'nom_de_plume', true) != ''
															? get_user_meta($contributor->ID, 'nom_de_plume', true)
															: $contributor->user_login
															. '</a>';
														?>
													</td>
												</tr>
											<?php } ?>
										</table>
									<?php } ?>

									<table style="font-family: 'Helvetica Neue', 'Helvetica', Helvetica, Arial, sans-serif; font-size: 100%; text-align:right; line-height: 1.6; width: 100%; margin: 0; padding: 0;">
										<tr style="font-family: 'Helvetica Neue', 'Helvetica', Helvetica, Arial, sans-serif; font-size: 100%; line-height: 1.6; margin: 0; padding: 0;">
											<td style="font-family: 'Helvetica Neue', 'Helvetica', Helvetica, Arial, sans-serif; font-size: 100%; line-height: 1.6; margin: 0; padding: 0;">
												<br>

												<br />

												Thanks!<br />

												The GOAT PoL<br />

												</p>
											</td>
										</tr>
										<tr style="font-family: 'Helvetica Neue', 'Helvetica', Helvetica, Arial, sans-serif; font-size: 100%; line-height: 1.6; margin: 0; padding: 0;">
											<td class="padding" style="font-family: 'Helvetica Neue', 'Helvetica', Helvetica, Arial, sans-serif; font-size: 100%; line-height: 1.6; margin: 0; padding: 10px 0;">

												<img src="<?php echo site_url(); ?>/wp-content/uploads/2022/02/GOAT_21-scaled.jpg" width="150px;" />

											</td>
										</tr>
										<tr style="font-family: 'Helvetica Neue', 'Helvetica', Helvetica, Arial, sans-serif; font-size: 100%; line-height: 1.6; margin: 0; padding: 0;">
											<td>
												<img style="margin-top:30px;" src="<?php echo site_url(); ?>/wp-content/themes/goatpol/assets/img/FullTitle_transparent.png" width="580px" />
											</td>

										</tr>
									</table>
								</div>

								<!-- /content -->



							</td>

							<td style="font-family: 'Helvetica Neue', 'Helvetica', Helvetica, Arial, sans-serif; font-size: 100%; line-height: 1.6; margin: 0; padding: 0;">
							</td>

						</tr>
					</table><!-- /body -->
					<!-- footer -->
					<table class="footer-wrap" style="font-family: 'Helvetica Neue', 'Helvetica', Helvetica, Arial, sans-serif; font-size: 100%; line-height: 1.6; width: 100%; clear: both !important; margin: 0; padding: 0;">
						<tr style="font-family: 'Helvetica Neue', 'Helvetica', Helvetica, Arial, sans-serif; font-size: 100%; line-height: 1.6; margin: 0; padding: 0;">
							<td style="font-family: 'Helvetica Neue', 'Helvetica', Helvetica, Arial, sans-serif; font-size: 100%; line-height: 1.6; margin: 0; padding: 0;">
							</td>

							<td class="container" style="font-family: 'Helvetica Neue', 'Helvetica', Helvetica, Arial, sans-serif; font-size: 100%; line-height: 1.6; display: block !important; max-width: 600px !important; clear: both !important; margin: 0 auto; padding: 0;">



								<!-- content -->

								<div class="content" style="font-family: 'Helvetica Neue', 'Helvetica', Helvetica, Arial, sans-serif; font-size: 100%; line-height: 1.6; max-width: 600px; display: block; margin: 0 auto; padding: 0;">

									<table style="font-family: 'Helvetica Neue', 'Helvetica', Helvetica, Arial, sans-serif; font-size: 100%; line-height: 1.6; width: 100%; margin: 0; padding: 0;">
										<tr style="font-family: 'Helvetica Neue', 'Helvetica', Helvetica, Arial, sans-serif; font-size: 100%; line-height: 1.6; margin: 0; padding: 0;">
											<td align="center" style="font-family: 'Helvetica Neue', 'Helvetica', Helvetica, Arial, sans-serif; font-size: 100%; line-height: 1.6; margin: 0; padding: 0;">



											</td>

										</tr>
									</table>
								</div>

								<!-- /content -->



							</td>

							<td style="font-family: 'Helvetica Neue', 'Helvetica', Helvetica, Arial, sans-serif; font-size: 100%; line-height: 1.6; margin: 0; padding: 0;">
							</td>

						</tr>
					</table><!-- /footer -->
				</body>



				</html>



			<?php

				$message_new = ob_get_contents();


				ob_end_clean();

				$send_from = 'thegoatpol@tutanota.com';

				$headers = array('Content-Type: text/html; charset=UTF-8');

				$headers .= 'From: ' . $send_from . "\r\n";



				$sent = wp_mail($to, $subject, $message_new, $headers);

				update_option('last_run_date', $present_day);
			}
		} else {

			foreach ($editors as $editor) {
				ob_start();

				add_filter('wp_mail_content_type', 'pol_set_content_type');


				// mail send process

				$to = $editor->user_email;

				$subject = "Email for Editors";

			?>



				<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">

				<html xmlns="http://www.w3.org/1999/xhtml" xmlns="http://www.w3.org/1999/xhtml" style="font-family: 'Helvetica Neue', 'Helvetica', Helvetica, Arial, sans-serif; font-size: 100%; line-height: 1.6; margin: 0; padding: 0;">

				<head>

					<meta name="viewport" content="width=device-width" />



					<meta http-equiv=3D"Content-Type" content=3D"text/html; charset=3Dutf-8"= />

					<title>An update was made to the task you are involvd in.</title>

				</head>

				<body bgcolor="#f6f6f6" style="font-family: 'Helvetica Neue', 'Helvetica', Helvetica, Arial, sans-serif; font-size: 100%; line-height: 1.6; -webkit-font-smoothing: antialiased; -webkit-text-size-adjust: none; width: 100% !important; height: 100%; margin: 0; padding: 0; ">



					<!-- body -->

					<table class="body-wrap" bgcolor="#f6f6f6" style="font-family: 'Helvetica Neue', 'Helvetica', Helvetica, Arial, sans-serif; font-size: 100%; line-height: 1.6; width: 100%; margin: 0; padding: 20px;">
						<tr style="font-family: 'Helvetica Neue', 'Helvetica', Helvetica, Arial, sans-serif; font-size: 100%; line-height: 1.6; margin: 0; padding: 0;">
							<td style="font-family: 'Helvetica Neue', 'Helvetica', Helvetica, Arial, sans-serif; font-size: 100%; line-height: 1.6; margin: 0; padding: 0;">
							</td>

							<td class="container" bgcolor="#FFFFFF" style="font-family: 'Helvetica Neue', 'Helvetica', Helvetica, Arial, sans-serif; font-size: 100%; line-height: 1.6; display: block !important; max-width: 600px !important; clear: both !important; margin: 0 auto; padding: 20px; border: 1px solid #f0f0f0;">



								<!-- content -->

								<div class="content" style="font-family: 'Helvetica Neue', 'Helvetica', Helvetica, Arial, sans-serif; font-size: 100%; line-height: 1.6; max-width: 600px; display: block; margin: 0 auto; padding: 0;">

									<table style="font-family: 'Helvetica Neue', 'Helvetica', Helvetica, Arial, sans-serif; font-size: 100%; line-height: 1.6; width: 100%; margin: 0; padding: 0;">
										<tr style="font-family: 'Helvetica Neue', 'Helvetica', Helvetica, Arial, sans-serif; font-size: 100%; line-height: 1.6; margin: 0; padding: 0;">
											<td style="font-family: 'Helvetica Neue', 'Helvetica', Helvetica, Arial, sans-serif; font-size: 100%; line-height: 1.6; margin: 0; padding: 0;">

												<p><img src="<?php echo site_url(); ?>/wp-content/uploads/2022/06/logo.jpg" width="150px" /></p>
												<p style="margin-top:50px;">
													There weren't any new stories submitted to the site in the last 24 hours.
												</p>

											</td>

										</tr>
									</table>

									<table style="font-family: 'Helvetica Neue', 'Helvetica', Helvetica, Arial, sans-serif; font-size: 100%; text-align:right; line-height: 1.6; width: 100%; margin: 0; padding: 0;">
										<tr style="font-family: 'Helvetica Neue', 'Helvetica', Helvetica, Arial, sans-serif; font-size: 100%; line-height: 1.6; margin: 0; padding: 0;">
											<td style="font-family: 'Helvetica Neue', 'Helvetica', Helvetica, Arial, sans-serif; font-size: 100%; line-height: 1.6; margin: 0; padding: 0;">
												<br>

												<br />

												Thank you,<br />

												The GOAT PoL<br />

												</p>
											</td>
										</tr>
										<tr style="font-family: 'Helvetica Neue', 'Helvetica', Helvetica, Arial, sans-serif; font-size: 100%; line-height: 1.6; margin: 0; padding: 0;">
											<td class="padding" style="font-family: 'Helvetica Neue', 'Helvetica', Helvetica, Arial, sans-serif; font-size: 100%; line-height: 1.6; margin: 0; padding: 10px 0;">

												<img src="<?php echo site_url(); ?>/wp-content/uploads/2022/02/GOAT_21-scaled.jpg" width="150px;" />

											</td>
										</tr>
										<tr style="font-family: 'Helvetica Neue', 'Helvetica', Helvetica, Arial, sans-serif; font-size: 100%; line-height: 1.6; margin: 0; padding: 0;">
											<td>
												<img style="margin-top:30px;" src="<?php echo site_url(); ?>/wp-content/themes/goatpol/assets/img/FullTitle_transparent.png" width="580px" />
											</td>

										</tr>
									</table>
								</div>

								<!-- /content -->



							</td>

							<td style="font-family: 'Helvetica Neue', 'Helvetica', Helvetica, Arial, sans-serif; font-size: 100%; line-height: 1.6; margin: 0; padding: 0;">
							</td>

						</tr>
					</table><!-- /body -->
					<!-- footer -->
					<table class="footer-wrap" style="font-family: 'Helvetica Neue', 'Helvetica', Helvetica, Arial, sans-serif; font-size: 100%; line-height: 1.6; width: 100%; clear: both !important; margin: 0; padding: 0;">
						<tr style="font-family: 'Helvetica Neue', 'Helvetica', Helvetica, Arial, sans-serif; font-size: 100%; line-height: 1.6; margin: 0; padding: 0;">
							<td style="font-family: 'Helvetica Neue', 'Helvetica', Helvetica, Arial, sans-serif; font-size: 100%; line-height: 1.6; margin: 0; padding: 0;">
							</td>

							<td class="container" style="font-family: 'Helvetica Neue', 'Helvetica', Helvetica, Arial, sans-serif; font-size: 100%; line-height: 1.6; display: block !important; max-width: 600px !important; clear: both !important; margin: 0 auto; padding: 0;">



								<!-- content -->

								<div class="content" style="font-family: 'Helvetica Neue', 'Helvetica', Helvetica, Arial, sans-serif; font-size: 100%; line-height: 1.6; max-width: 600px; display: block; margin: 0 auto; padding: 0;">

									<table style="font-family: 'Helvetica Neue', 'Helvetica', Helvetica, Arial, sans-serif; font-size: 100%; line-height: 1.6; width: 100%; margin: 0; padding: 0;">
										<tr style="font-family: 'Helvetica Neue', 'Helvetica', Helvetica, Arial, sans-serif; font-size: 100%; line-height: 1.6; margin: 0; padding: 0;">
											<td align="center" style="font-family: 'Helvetica Neue', 'Helvetica', Helvetica, Arial, sans-serif; font-size: 100%; line-height: 1.6; margin: 0; padding: 0;">



											</td>

										</tr>
									</table>
								</div>

								<!-- /content -->



							</td>

							<td style="font-family: 'Helvetica Neue', 'Helvetica', Helvetica, Arial, sans-serif; font-size: 100%; line-height: 1.6; margin: 0; padding: 0;">
							</td>

						</tr>
					</table><!-- /footer -->
				</body>



				</html>



	<?php

				$message_new = ob_get_contents();


				ob_end_clean();

				$send_from = 'thegoatpol@tutanota.com';

				$headers = array('Content-Type: text/html; charset=UTF-8');

				$headers .= 'From: ' . $send_from . "\r\n";



				$sent = wp_mail($to, $subject, $message_new, $headers);

				update_option('last_run_date', $present_day);
			}
		}
	}
}


//send email to new user after registration or to existing user after they update their CP

function cpm_send_cp_update_email($user_email, $user_name)
{
	ob_start();

	add_filter('wp_mail_content_type', 'pol_set_content_type');

	// mail send process
	$to = $user_email;

	$subject = "The GOAT PoL Contributors";

	?>
	<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">

	<html xmlns="http://www.w3.org/1999/xhtml" xmlns="http://www.w3.org/1999/xhtml" style="font-family: 'Helvetica Neue', 'Helvetica', Helvetica, Arial, sans-serif; font-size: 100%; line-height: 1.6; margin: 0; padding: 0;">

	<head>

		<meta name="viewport" content="width=device-width" />



		<meta http-equiv=3D"Content-Type" content=3D"text/html; charset=3Dutf-8"= />

		<!-- <title>An update was made to the task you are involvd in.</title> -->

	</head>

	<body bgcolor="#f6f6f6" style="font-family: 'Helvetica Neue', 'Helvetica', Helvetica, Arial, sans-serif; font-size: 100%; line-height: 1.6; -webkit-font-smoothing: antialiased; -webkit-text-size-adjust: none; width: 100% !important; height: 100%; margin: 0; padding: 0; ">



		<!-- body -->

		<table class="body-wrap" bgcolor="#f6f6f6" style="font-family: 'Helvetica Neue', 'Helvetica', Helvetica, Arial, sans-serif; font-size: 100%; line-height: 1.6; width: 100%; margin: 0; padding: 20px;">
			<tr style="font-family: 'Helvetica Neue', 'Helvetica', Helvetica, Arial, sans-serif; font-size: 100%; line-height: 1.6; margin: 0; padding: 0;">
				<td style="font-family: 'Helvetica Neue', 'Helvetica', Helvetica, Arial, sans-serif; font-size: 100%; line-height: 1.6; margin: 0; padding: 0;">
				</td>

				<td class="container" bgcolor="#FFFFFF" style="font-family: 'Helvetica Neue', 'Helvetica', Helvetica, Arial, sans-serif; font-size: 100%; line-height: 1.6; display: block !important; max-width: 600px !important; clear: both !important; margin: 0 auto; padding: 20px; border: 1px solid #f0f0f0;">



					<!-- content -->

					<div class="content" style="font-family: 'Helvetica Neue', 'Helvetica', Helvetica, Arial, sans-serif; font-size: 100%; line-height: 1.6; max-width: 600px; display: block; margin: 0 auto; padding: 0;">

						<table style="font-family: 'Helvetica Neue', 'Helvetica', Helvetica, Arial, sans-serif; font-size: 100%; line-height: 1.6; width: 100%; margin: 0; padding: 0;">
							<tr style="font-family: 'Helvetica Neue', 'Helvetica', Helvetica, Arial, sans-serif; font-size: 100%; line-height: 1.6; margin: 0; padding: 0;">
								<td style="font-family: 'Helvetica Neue', 'Helvetica', Helvetica, Arial, sans-serif; font-size: 100%; line-height: 1.6; margin: 0; padding: 0;">

									<p><img src="<?php echo site_url(); ?>/wp-content/uploads/2022/06/logo.jpg" width="150px" /></p>
									<p style="margin-top:50px;">
										Dear <?php echo $user_name; ?>, congratulations, you have successfully created your new Contributor's Page for The GOAT PoL.
										You can login to the site and update your information anytime. Just click on the menu item "See your contributor's page"
										(in "hamburger" menu at upper right), to add to or correct any of your information. To see what other Contributor's Pages
										look like, click on the "The GOAT PoL Contributors" lozenge at the bottom of the screen and search for other writers.
										Thanks for reading and writing at The GOAT PoL.
									</p>

								</td>

							</tr>
						</table>

						<table style="font-family: 'Helvetica Neue', 'Helvetica', Helvetica, Arial, sans-serif; font-size: 100%; text-align:right; line-height: 1.6; width: 100%; margin: 0; padding: 0;">
							<tr style="font-family: 'Helvetica Neue', 'Helvetica', Helvetica, Arial, sans-serif; font-size: 100%; line-height: 1.6; margin: 0; padding: 0;">
								<td style="font-family: 'Helvetica Neue', 'Helvetica', Helvetica, Arial, sans-serif; font-size: 100%; line-height: 1.6; margin: 0; padding: 0;">
									<br>

									<br />

									Thank you,<br />

									The GOAT PoL<br />

									</p>
								</td>
							</tr>
							<tr style="font-family: 'Helvetica Neue', 'Helvetica', Helvetica, Arial, sans-serif; font-size: 100%; line-height: 1.6; margin: 0; padding: 0;">
								<td class="padding" style="font-family: 'Helvetica Neue', 'Helvetica', Helvetica, Arial, sans-serif; font-size: 100%; line-height: 1.6; margin: 0; padding: 10px 0;">

									<img src="<?php echo site_url(); ?>/wp-content/uploads/2022/02/GOAT_21-scaled.jpg" width="150px;" />

								</td>
							</tr>
							<tr style="font-family: 'Helvetica Neue', 'Helvetica', Helvetica, Arial, sans-serif; font-size: 100%; line-height: 1.6; margin: 0; padding: 0;">
								<td>
									<img style="margin-top:30px;" src="<?php echo site_url(); ?>/wp-content/themes/goatpol/assets/img/FullTitle_transparent.png" width="580px" />
								</td>

							</tr>
						</table>
					</div>

					<!-- /content -->



				</td>

				<td style="font-family: 'Helvetica Neue', 'Helvetica', Helvetica, Arial, sans-serif; font-size: 100%; line-height: 1.6; margin: 0; padding: 0;">
				</td>

			</tr>
		</table><!-- /body -->
		<!-- footer -->
		<table class="footer-wrap" style="font-family: 'Helvetica Neue', 'Helvetica', Helvetica, Arial, sans-serif; font-size: 100%; line-height: 1.6; width: 100%; clear: both !important; margin: 0; padding: 0;">
			<tr style="font-family: 'Helvetica Neue', 'Helvetica', Helvetica, Arial, sans-serif; font-size: 100%; line-height: 1.6; margin: 0; padding: 0;">
				<td style="font-family: 'Helvetica Neue', 'Helvetica', Helvetica, Arial, sans-serif; font-size: 100%; line-height: 1.6; margin: 0; padding: 0;">
				</td>

				<td class="container" style="font-family: 'Helvetica Neue', 'Helvetica', Helvetica, Arial, sans-serif; font-size: 100%; line-height: 1.6; display: block !important; max-width: 600px !important; clear: both !important; margin: 0 auto; padding: 0;">



					<!-- content -->

					<div class="content" style="font-family: 'Helvetica Neue', 'Helvetica', Helvetica, Arial, sans-serif; font-size: 100%; line-height: 1.6; max-width: 600px; display: block; margin: 0 auto; padding: 0;">

						<table style="font-family: 'Helvetica Neue', 'Helvetica', Helvetica, Arial, sans-serif; font-size: 100%; line-height: 1.6; width: 100%; margin: 0; padding: 0;">
							<tr style="font-family: 'Helvetica Neue', 'Helvetica', Helvetica, Arial, sans-serif; font-size: 100%; line-height: 1.6; margin: 0; padding: 0;">
								<td align="center" style="font-family: 'Helvetica Neue', 'Helvetica', Helvetica, Arial, sans-serif; font-size: 100%; line-height: 1.6; margin: 0; padding: 0;">



								</td>

							</tr>
						</table>
					</div>

					<!-- /content -->

				</td>

				<td style="font-family: 'Helvetica Neue', 'Helvetica', Helvetica, Arial, sans-serif; font-size: 100%; line-height: 1.6; margin: 0; padding: 0;">
				</td>

			</tr>
		</table><!-- /footer -->
	</body>

	</html>

<?php

	$message_new = ob_get_contents();


	ob_end_clean();

	$send_from = 'thegoatpol@tutanota.com';

	$headers = array('Content-Type: text/html; charset=UTF-8');

	$headers .= 'From: ' . $send_from . "\r\n";

	$sent = wp_mail($to, $subject, $message_new, $headers);
}



//send email to user if someone likes their story
add_action('wp_ajax_cpm_send_story_liked_email', 'cpm_send_story_liked_email');
function cpm_send_story_liked_email()
{
	ob_start();

	add_filter('wp_mail_content_type', 'pol_set_content_type');

	$curr_author_id = $_POST['authorID'];
	$curr_author = get_user_by('id', $curr_author_id);
	$curr_author_email = $curr_author->user_email;
	$curr_author_name = ucwords($curr_author->display_name);

	$curr_user_id = get_current_user_id();
	$curr_user = get_user_by('id', $curr_user_id);
	$curr_user_name = ucwords($curr_user->display_name);
	$curr_user_cp = get_author_posts_url($curr_user_id);

	$story_type = $_POST['storyType'];
	$story_id = $_POST['storyID'];

	$story_title = get_post_field('post_title', $story_id);

	if ($story_type == 'pdf') {
		$story_title = get_post_meta((int) $story_id, 'story_title', true);
	}

	// mail send process
	$to = $curr_author_email;
	$subject = $curr_user_name . " Liked Your Story !!";

	// wp_send_json_success([ $curr_author_email, $curr_author_name, $curr_user_name, $curr_user_cp, $story_title ]);

?>
	<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
	<html xmlns="http://www.w3.org/1999/xhtml" xmlns="http://www.w3.org/1999/xhtml" style="font-family: 'Helvetica Neue', 'Helvetica', Helvetica, Arial, sans-serif; font-size: 100%; line-height: 1.6; margin: 0; padding: 0;">

	<head>
		<meta name="viewport" content="width=device-width" />
		<meta http-equiv=3D"Content-Type" content=3D"text/html; charset=3Dutf-8"= />
	</head>

	<body bgcolor="#f6f6f6" style="font-family: 'Helvetica Neue', 'Helvetica', Helvetica, Arial, sans-serif; font-size: 100%; line-height: 1.6; -webkit-font-smoothing: antialiased; -webkit-text-size-adjust: none; width: 100% !important; height: 100%; margin: 0; padding: 0; ">

		<!-- body -->
		<table class="body-wrap" bgcolor="#f6f6f6" style="font-family: 'Helvetica Neue', 'Helvetica', Helvetica, Arial, sans-serif; font-size: 100%; line-height: 1.6; width: 100%; margin: 0; padding: 20px;">
			<tr style="font-family: 'Helvetica Neue', 'Helvetica', Helvetica, Arial, sans-serif; font-size: 100%; line-height: 1.6; margin: 0; padding: 0;">
				<td style="font-family: 'Helvetica Neue', 'Helvetica', Helvetica, Arial, sans-serif; font-size: 100%; line-height: 1.6; margin: 0; padding: 0;">
				</td>
				<td class="container" bgcolor="#FFFFFF" style="font-family: 'Helvetica Neue', 'Helvetica', Helvetica, Arial, sans-serif; font-size: 100%; line-height: 1.6; display: block !important; max-width: 600px !important; clear: both !important; margin: 0 auto; padding: 20px; border: 1px solid #f0f0f0;">

					<!-- content -->
					<div class="content" style="font-family: 'Helvetica Neue', 'Helvetica', Helvetica, Arial, sans-serif; font-size: 100%; line-height: 1.6; max-width: 600px; display: block; margin: 0 auto; padding: 0;">

						<table style="font-family: 'Helvetica Neue', 'Helvetica', Helvetica, Arial, sans-serif; font-size: 100%; line-height: 1.6; width: 100%; margin: 0; padding: 0;">
							<tr style="font-family: 'Helvetica Neue', 'Helvetica', Helvetica, Arial, sans-serif; font-size: 100%; line-height: 1.6; margin: 0; padding: 0;">
								<td style="font-family: 'Helvetica Neue', 'Helvetica', Helvetica, Arial, sans-serif; font-size: 100%; line-height: 1.6; margin: 0; padding: 0;">

									<p><img src="<?php echo site_url(); ?>/wp-content/uploads/2022/06/logo.jpg" width="150px" /></p>
									<p style="margin-top:50px;">

										Dear <?php echo ucfirst($curr_author_name); ?>, congratulations, your story, <?php echo ucwords($story_title); ?>, has been selected by <?php echo $curr_user_name; ?>
										as one of their favourite stories on The GOAT PoL. To learn more about <?php echo $curr_user_name; ?> take a
										look at their <a href="<?php echo $curr_user_cp; ?>">Contributor’s Page</a>, or contact them by sending fan mail (by clicking on their name at the top of one of the
										stories that they published)
									</p>

								</td>

							</tr>
						</table>

						<table style="font-family: 'Helvetica Neue', 'Helvetica', Helvetica, Arial, sans-serif; font-size: 100%; text-align:right; line-height: 1.6; width: 100%; margin: 0; padding: 0;">
							<tr style="font-family: 'Helvetica Neue', 'Helvetica', Helvetica, Arial, sans-serif; font-size: 100%; line-height: 1.6; margin: 0; padding: 0;">
								<td style="font-family: 'Helvetica Neue', 'Helvetica', Helvetica, Arial, sans-serif; font-size: 100%; line-height: 1.6; margin: 0; padding: 0;">
									<br>
									<br />
									Take care,<br />
									The GOAT PoL<br />

									</p>
								</td>
							</tr>
							<tr style="font-family: 'Helvetica Neue', 'Helvetica', Helvetica, Arial, sans-serif; font-size: 100%; line-height: 1.6; margin: 0; padding: 0;">
								<td class="padding" style="font-family: 'Helvetica Neue', 'Helvetica', Helvetica, Arial, sans-serif; font-size: 100%; line-height: 1.6; margin: 0; padding: 10px 0;">

									<img src="<?php echo site_url(); ?>/wp-content/uploads/2022/02/GOAT_21-scaled.jpg" width="150px;" />
								</td>
							</tr>
							<tr style="font-family: 'Helvetica Neue', 'Helvetica', Helvetica, Arial, sans-serif; font-size: 100%; line-height: 1.6; margin: 0; padding: 0;">
								<td>
									<img style="margin-top:30px;" src="<?php echo site_url(); ?>/wp-content/themes/goatpol/assets/img/FullTitle_transparent.png" width="580px" />
								</td>
							</tr>
						</table>
					</div>

					<!-- /content -->
				</td>
				<td style="font-family: 'Helvetica Neue', 'Helvetica', Helvetica, Arial, sans-serif; font-size: 100%; line-height: 1.6; margin: 0; padding: 0;">
				</td>
			</tr>
		</table><!-- /body -->
		<!-- footer -->
		<table class="footer-wrap" style="font-family: 'Helvetica Neue', 'Helvetica', Helvetica, Arial, sans-serif; font-size: 100%; line-height: 1.6; width: 100%; clear: both !important; margin: 0; padding: 0;">
			<tr style="font-family: 'Helvetica Neue', 'Helvetica', Helvetica, Arial, sans-serif; font-size: 100%; line-height: 1.6; margin: 0; padding: 0;">
				<td style="font-family: 'Helvetica Neue', 'Helvetica', Helvetica, Arial, sans-serif; font-size: 100%; line-height: 1.6; margin: 0; padding: 0;">
				</td>

				<td class="container" style="font-family: 'Helvetica Neue', 'Helvetica', Helvetica, Arial, sans-serif; font-size: 100%; line-height: 1.6; display: block !important; max-width: 600px !important; clear: both !important; margin: 0 auto; padding: 0;">

					<!-- content -->
					<div class="content" style="font-family: 'Helvetica Neue', 'Helvetica', Helvetica, Arial, sans-serif; font-size: 100%; line-height: 1.6; max-width: 600px; display: block; margin: 0 auto; padding: 0;">

						<table style="font-family: 'Helvetica Neue', 'Helvetica', Helvetica, Arial, sans-serif; font-size: 100%; line-height: 1.6; width: 100%; margin: 0; padding: 0;">
							<tr style="font-family: 'Helvetica Neue', 'Helvetica', Helvetica, Arial, sans-serif; font-size: 100%; line-height: 1.6; margin: 0; padding: 0;">
								<td align="center" style="font-family: 'Helvetica Neue', 'Helvetica', Helvetica, Arial, sans-serif; font-size: 100%; line-height: 1.6; margin: 0; padding: 0;">
								</td>

							</tr>
						</table>
					</div>
					<!-- /content -->
				</td>
				<td style="font-family: 'Helvetica Neue', 'Helvetica', Helvetica, Arial, sans-serif; font-size: 100%; line-height: 1.6; margin: 0; padding: 0;">
				</td>

			</tr>
		</table><!-- /footer -->
	</body>

	</html>

<?php

	$message_new = ob_get_contents();

	ob_end_clean();

	$send_from = 'thegoatpol@tutanota.com';
	$headers = array('Content-Type: text/html; charset=UTF-8');
	$headers .= 'From: ' . $send_from . "\r\n";
	$sent = wp_mail($to, $subject, $message_new, $headers);
}



//send email to user when someone transfers them a commission
// add_action('wp_ajax_cpm_send_commission_transfer_email', 'cpm_send_commission_transfer_email');
function cpm_send_commission_transfer_email($receiverId, $transfer_info, $commission, $org_rae_name)
{
	ob_start();

	add_filter('wp_mail_content_type', 'pol_set_content_type');

	$sender_id = get_current_user_id();
	$sender = get_user_by('id', $sender_id);
	$sender_name = ucwords($sender->display_name);
	$sender_cp = get_author_posts_url($sender_id);

	$receiver_id = isset($_POST['receiverId']) ? $_POST['receiverId'] : $receiverId;
	$receiver = get_user_by('id', $receiver_id);
	$receiver_email = $receiver->user_email;
	$receiver_name = ucwords($receiver->display_name);


	// mail send process
	$to = $receiver_email;
	$subject = "New Commission Received";

	if ($transfer_info == 'user_rae') {
		$subject = "A commission has been returned to you";
	}

	// wp_send_json_success([ $curr_author_email, $curr_author_name, $curr_user_name, $curr_user_cp, $story_title ]);

?>
	<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
	<html xmlns="http://www.w3.org/1999/xhtml" xmlns="http://www.w3.org/1999/xhtml" style="font-family: 'Helvetica Neue', 'Helvetica', Helvetica, Arial, sans-serif; font-size: 100%; line-height: 1.6; margin: 0; padding: 0;">

	<head>
		<meta name="viewport" content="width=device-width" />
		<meta http-equiv=3D"Content-Type" content=3D"text/html; charset=3Dutf-8"= />
	</head>

	<body bgcolor="#f6f6f6" style="font-family: 'Helvetica Neue', 'Helvetica', Helvetica, Arial, sans-serif; font-size: 100%; line-height: 1.6; -webkit-font-smoothing: antialiased; -webkit-text-size-adjust: none; width: 100% !important; height: 100%; margin: 0; padding: 0; ">

		<!-- body -->
		<table class="body-wrap" bgcolor="#f6f6f6" style="font-family: 'Helvetica Neue', 'Helvetica', Helvetica, Arial, sans-serif; font-size: 100%; line-height: 1.6; width: 100%; margin: 0; padding: 20px;">
			<tr style="font-family: 'Helvetica Neue', 'Helvetica', Helvetica, Arial, sans-serif; font-size: 100%; line-height: 1.6; margin: 0; padding: 0;">
				<td style="font-family: 'Helvetica Neue', 'Helvetica', Helvetica, Arial, sans-serif; font-size: 100%; line-height: 1.6; margin: 0; padding: 0;">
				</td>
				<td class="container" bgcolor="#FFFFFF" style="font-family: 'Helvetica Neue', 'Helvetica', Helvetica, Arial, sans-serif; font-size: 100%; line-height: 1.6; display: block !important; max-width: 600px !important; clear: both !important; margin: 0 auto; padding: 20px; border: 1px solid #f0f0f0;">

					<!-- content -->
					<div class="content" style="font-family: 'Helvetica Neue', 'Helvetica', Helvetica, Arial, sans-serif; font-size: 100%; line-height: 1.6; max-width: 600px; display: block; margin: 0 auto; padding: 0;">

						<table style="font-family: 'Helvetica Neue', 'Helvetica', Helvetica, Arial, sans-serif; font-size: 100%; line-height: 1.6; width: 100%; margin: 0; padding: 0;">
							<tr style="font-family: 'Helvetica Neue', 'Helvetica', Helvetica, Arial, sans-serif; font-size: 100%; line-height: 1.6; margin: 0; padding: 0;">
								<td style="font-family: 'Helvetica Neue', 'Helvetica', Helvetica, Arial, sans-serif; font-size: 100%; line-height: 1.6; margin: 0; padding: 0;">

									<p><img src="<?php echo site_url(); ?>/wp-content/uploads/2022/06/logo.jpg" width="150px" /></p>
									<p style="margin-top:50px;">

										<?php
										if ($transfer_info == 'rae_rae') {

											echo 'Dear ' . $receiver_name . ', your colleague, ' . $sender_name . ', has transferred a commission to you, 
												' . $commission . '. You can save it for later or transfer it to any writer you\'d like to work with.';
										} else if ($transfer_info == 'rae_user') {

											echo 'Dear ' . $receiver_name . ', one of our Reader/Advisor/Editors, ' . $sender_name . ', 
												has transfered a commission, ' . $commission . ', to your account. 
												You may use it to submit new writing that ' . $sender_name . ' will work on with you and publish. 
												Or, you can transfer it to another writer, anyone who you believe ' . $sender_name . ' will enjoy working with.';
										} else if ($transfer_info == 'user_rae') {

											echo 'Dear ' . $receiver_name . ', one of our writers, ' . $sender_name . ', has returned an unused commission to you. 
												You can use it to commission new writing from any author you\'d like to work with. Or transfer it to another RAE';
										} else if ($transfer_info == 'user_user') {

											echo 'Dear ' . $receiver_name . ', one of your colleagues, ' . $sender_name . ', has transfered a commission, 
												' . $commission . ', to your account. You may use it to submit new writing that our RAE, ' . $org_rae_name . ' 
												will work on with you and publish. Or you can transfer it to another writer, anyone who you believe 
												' . $org_rae_name . ' will enjoy working with.';
										}
										?>
									</p>

								</td>

							</tr>
						</table>

						<table style="font-family: 'Helvetica Neue', 'Helvetica', Helvetica, Arial, sans-serif; font-size: 100%; text-align:right; line-height: 1.6; width: 100%; margin: 0; padding: 0;">
							<tr style="font-family: 'Helvetica Neue', 'Helvetica', Helvetica, Arial, sans-serif; font-size: 100%; line-height: 1.6; margin: 0; padding: 0;">
								<td style="font-family: 'Helvetica Neue', 'Helvetica', Helvetica, Arial, sans-serif; font-size: 100%; line-height: 1.6; margin: 0; padding: 0;">
									<br>
									<br />
									Thank you for working with us,<br />
									The GOAT PoL<br />
									</p>
								</td>
							</tr>
							<tr style="font-family: 'Helvetica Neue', 'Helvetica', Helvetica, Arial, sans-serif; font-size: 100%; line-height: 1.6; margin: 0; padding: 0;">
								<td class="padding" style="font-family: 'Helvetica Neue', 'Helvetica', Helvetica, Arial, sans-serif; font-size: 100%; line-height: 1.6; margin: 0; padding: 10px 0;">

									<img src="<?php echo site_url(); ?>/wp-content/uploads/2022/02/GOAT_21-scaled.jpg" width="150px;" />
								</td>
							</tr>
							<tr style="font-family: 'Helvetica Neue', 'Helvetica', Helvetica, Arial, sans-serif; font-size: 100%; line-height: 1.6; margin: 0; padding: 0;">
								<td>
									<img style="margin-top:30px;" src="<?php echo site_url(); ?>/wp-content/themes/goatpol/assets/img/FullTitle_transparent.png" width="580px" />
								</td>
							</tr>
						</table>
					</div>

					<!-- /content -->
				</td>
				<td style="font-family: 'Helvetica Neue', 'Helvetica', Helvetica, Arial, sans-serif; font-size: 100%; line-height: 1.6; margin: 0; padding: 0;">
				</td>
			</tr>
		</table><!-- /body -->
		<!-- footer -->
		<table class="footer-wrap" style="font-family: 'Helvetica Neue', 'Helvetica', Helvetica, Arial, sans-serif; font-size: 100%; line-height: 1.6; width: 100%; clear: both !important; margin: 0; padding: 0;">
			<tr style="font-family: 'Helvetica Neue', 'Helvetica', Helvetica, Arial, sans-serif; font-size: 100%; line-height: 1.6; margin: 0; padding: 0;">
				<td style="font-family: 'Helvetica Neue', 'Helvetica', Helvetica, Arial, sans-serif; font-size: 100%; line-height: 1.6; margin: 0; padding: 0;">
				</td>

				<td class="container" style="font-family: 'Helvetica Neue', 'Helvetica', Helvetica, Arial, sans-serif; font-size: 100%; line-height: 1.6; display: block !important; max-width: 600px !important; clear: both !important; margin: 0 auto; padding: 0;">

					<!-- content -->
					<div class="content" style="font-family: 'Helvetica Neue', 'Helvetica', Helvetica, Arial, sans-serif; font-size: 100%; line-height: 1.6; max-width: 600px; display: block; margin: 0 auto; padding: 0;">

						<table style="font-family: 'Helvetica Neue', 'Helvetica', Helvetica, Arial, sans-serif; font-size: 100%; line-height: 1.6; width: 100%; margin: 0; padding: 0;">
							<tr style="font-family: 'Helvetica Neue', 'Helvetica', Helvetica, Arial, sans-serif; font-size: 100%; line-height: 1.6; margin: 0; padding: 0;">
								<td align="center" style="font-family: 'Helvetica Neue', 'Helvetica', Helvetica, Arial, sans-serif; font-size: 100%; line-height: 1.6; margin: 0; padding: 0;">
								</td>

							</tr>
						</table>
					</div>
					<!-- /content -->
				</td>
				<td style="font-family: 'Helvetica Neue', 'Helvetica', Helvetica, Arial, sans-serif; font-size: 100%; line-height: 1.6; margin: 0; padding: 0;">
				</td>

			</tr>
		</table><!-- /footer -->
	</body>

	</html>

<?php

	$message_new = ob_get_contents();

	ob_end_clean();

	$send_from = 'thegoatpol@tutanota.com';
	$headers = array('Content-Type: text/html; charset=UTF-8');
	$headers .= 'From: ' . $send_from . "\r\n";
	$sent = wp_mail($to, $subject, $message_new, $headers);
}




















add_action('wp_ajax_cpm_send_email_to_user_seeking_commission', 'cpm_send_email_to_user_seeking_commission');
function cpm_send_email_to_user_seeking_commission()
{
	ob_start();

	add_filter('wp_mail_content_type', 'pol_set_content_type');

	$sender_id = get_current_user_id();
	$sender = get_user_by('id', $sender_id);
	$sender_name = ucwords($sender->display_name);
	$sender_email = $sender->user_email;
	$sender_cp = get_author_posts_url($sender_id);

	// mail send process
	$to = $sender_email;
	$subject = "Request for Commission";

?>
	<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
	<html xmlns="http://www.w3.org/1999/xhtml" xmlns="http://www.w3.org/1999/xhtml" style="font-family: 'Helvetica Neue', 'Helvetica', Helvetica, Arial, sans-serif; font-size: 100%; line-height: 1.6; margin: 0; padding: 0;">

	<head>
		<meta name="viewport" content="width=device-width" />
		<meta http-equiv=3D"Content-Type" content=3D"text/html; charset=3Dutf-8"= />
	</head>

	<body bgcolor="#f6f6f6" style="font-family: 'Helvetica Neue', 'Helvetica', Helvetica, Arial, sans-serif; font-size: 100%; line-height: 1.6; -webkit-font-smoothing: antialiased; -webkit-text-size-adjust: none; width: 100% !important; height: 100%; margin: 0; padding: 0; ">

		<!-- body -->
		<table class="body-wrap" bgcolor="#f6f6f6" style="font-family: 'Helvetica Neue', 'Helvetica', Helvetica, Arial, sans-serif; font-size: 100%; line-height: 1.6; width: 100%; margin: 0; padding: 20px;">
			<tr style="font-family: 'Helvetica Neue', 'Helvetica', Helvetica, Arial, sans-serif; font-size: 100%; line-height: 1.6; margin: 0; padding: 0;">
				<td style="font-family: 'Helvetica Neue', 'Helvetica', Helvetica, Arial, sans-serif; font-size: 100%; line-height: 1.6; margin: 0; padding: 0;">
				</td>
				<td class="container" bgcolor="#FFFFFF" style="font-family: 'Helvetica Neue', 'Helvetica', Helvetica, Arial, sans-serif; font-size: 100%; line-height: 1.6; display: block !important; max-width: 600px !important; clear: both !important; margin: 0 auto; padding: 20px; border: 1px solid #f0f0f0;">

					<!-- content -->
					<div class="content" style="font-family: 'Helvetica Neue', 'Helvetica', Helvetica, Arial, sans-serif; font-size: 100%; line-height: 1.6; max-width: 600px; display: block; margin: 0 auto; padding: 0;">

						<table style="font-family: 'Helvetica Neue', 'Helvetica', Helvetica, Arial, sans-serif; font-size: 100%; line-height: 1.6; width: 100%; margin: 0; padding: 0;">
							<tr style="font-family: 'Helvetica Neue', 'Helvetica', Helvetica, Arial, sans-serif; font-size: 100%; line-height: 1.6; margin: 0; padding: 0;">
								<td style="font-family: 'Helvetica Neue', 'Helvetica', Helvetica, Arial, sans-serif; font-size: 100%; line-height: 1.6; margin: 0; padding: 0;">

									<p><img src="<?php echo site_url(); ?>/wp-content/uploads/2022/06/logo.jpg" width="150px" /></p>
									<p style="margin-top:50px;">

										<?php
										echo 'Dear ' . $sender_name . ', thank you for adding your name to our list of writers who are looking for commissions. 
											Please be assured that your name will remain on this list until a commission is available and has been transferred to you. 
											It might take a long time; we are grateful for your patience if it does. (Please note that the list that RAEs see every 
											day features the oldest entries at the top.) While you wait for a commission, we encourage you to spend time reading 
											others and taking free workhops, where you can meet new colleagues from around the world of The GOAT PoL. 
											And of course you can continue sharing your new writing with readers by posting it to your Contributor\'s Page. 
											Thank you so much for working with us and contributing to The GOAT PoL.';

										?>
									</p>

								</td>

							</tr>
						</table>

						<table style="font-family: 'Helvetica Neue', 'Helvetica', Helvetica, Arial, sans-serif; font-size: 100%; text-align:right; line-height: 1.6; width: 100%; margin: 0; padding: 0;">
							<tr style="font-family: 'Helvetica Neue', 'Helvetica', Helvetica, Arial, sans-serif; font-size: 100%; line-height: 1.6; margin: 0; padding: 0;">
								<td style="font-family: 'Helvetica Neue', 'Helvetica', Helvetica, Arial, sans-serif; font-size: 100%; line-height: 1.6; margin: 0; padding: 0;">
									<br>
									<br />
									Take care,<br />
									The GOAT PoL<br />
									</p>
								</td>
							</tr>
							<tr style="font-family: 'Helvetica Neue', 'Helvetica', Helvetica, Arial, sans-serif; font-size: 100%; line-height: 1.6; margin: 0; padding: 0;">
								<td class="padding" style="font-family: 'Helvetica Neue', 'Helvetica', Helvetica, Arial, sans-serif; font-size: 100%; line-height: 1.6; margin: 0; padding: 10px 0;">

									<img src="<?php echo site_url(); ?>/wp-content/uploads/2022/02/GOAT_21-scaled.jpg" width="150px;" />
								</td>
							</tr>
							<tr style="font-family: 'Helvetica Neue', 'Helvetica', Helvetica, Arial, sans-serif; font-size: 100%; line-height: 1.6; margin: 0; padding: 0;">
								<td>
									<img style="margin-top:30px;" src="<?php echo site_url(); ?>/wp-content/themes/goatpol/assets/img/FullTitle_transparent.png" width="580px" />
								</td>
							</tr>
						</table>
					</div>

					<!-- /content -->
				</td>
				<td style="font-family: 'Helvetica Neue', 'Helvetica', Helvetica, Arial, sans-serif; font-size: 100%; line-height: 1.6; margin: 0; padding: 0;">
				</td>
			</tr>
		</table><!-- /body -->
		<!-- footer -->
		<table class="footer-wrap" style="font-family: 'Helvetica Neue', 'Helvetica', Helvetica, Arial, sans-serif; font-size: 100%; line-height: 1.6; width: 100%; clear: both !important; margin: 0; padding: 0;">
			<tr style="font-family: 'Helvetica Neue', 'Helvetica', Helvetica, Arial, sans-serif; font-size: 100%; line-height: 1.6; margin: 0; padding: 0;">
				<td style="font-family: 'Helvetica Neue', 'Helvetica', Helvetica, Arial, sans-serif; font-size: 100%; line-height: 1.6; margin: 0; padding: 0;">
				</td>

				<td class="container" style="font-family: 'Helvetica Neue', 'Helvetica', Helvetica, Arial, sans-serif; font-size: 100%; line-height: 1.6; display: block !important; max-width: 600px !important; clear: both !important; margin: 0 auto; padding: 0;">

					<!-- content -->
					<div class="content" style="font-family: 'Helvetica Neue', 'Helvetica', Helvetica, Arial, sans-serif; font-size: 100%; line-height: 1.6; max-width: 600px; display: block; margin: 0 auto; padding: 0;">

						<table style="font-family: 'Helvetica Neue', 'Helvetica', Helvetica, Arial, sans-serif; font-size: 100%; line-height: 1.6; width: 100%; margin: 0; padding: 0;">
							<tr style="font-family: 'Helvetica Neue', 'Helvetica', Helvetica, Arial, sans-serif; font-size: 100%; line-height: 1.6; margin: 0; padding: 0;">
								<td align="center" style="font-family: 'Helvetica Neue', 'Helvetica', Helvetica, Arial, sans-serif; font-size: 100%; line-height: 1.6; margin: 0; padding: 0;">
								</td>

							</tr>
						</table>
					</div>
					<!-- /content -->
				</td>
				<td style="font-family: 'Helvetica Neue', 'Helvetica', Helvetica, Arial, sans-serif; font-size: 100%; line-height: 1.6; margin: 0; padding: 0;">
				</td>

			</tr>
		</table><!-- /footer -->
	</body>

	</html>

	<?php

	$message_new = ob_get_contents();

	ob_end_clean();

	$send_from = 'thegoatpol@tutanota.com';
	$headers = array('Content-Type: text/html; charset=UTF-8');
	$headers .= 'From: ' . $send_from . "\r\n";
	$sent = wp_mail($to, $subject, $message_new, $headers);
}





















































function get_nearby_cities($lat, $long, $distance)
{
	global $wpdb;
	$nearbyCities = $wpdb->get_results(
		"SELECT DISTINCT
        city_latitude.post_id,
        city_latitude.meta_key,
        city_latitude.meta_value as cityLat,
        city_longitude.meta_value as cityLong,
        ((ACOS(SIN($lat * PI() / 180) * SIN(city_latitude.meta_value * PI() / 180) + COS($lat * PI() / 180) * COS(city_latitude.meta_value * PI() / 180) * COS(($long - city_longitude.meta_value) * PI() / 180)) * 180 / PI()) * 60 * 1.1515) AS distance,
        wp_posts.post_title
    FROM
        wp_postmeta AS city_latitude
        LEFT JOIN wp_postmeta as city_longitude ON city_latitude.post_id = city_longitude.post_id
        INNER JOIN wp_posts ON wp_posts.ID = city_latitude.post_id
    WHERE city_latitude.meta_key = 'city_latitude' AND city_longitude.meta_key = 'city_longitude'
    HAVING distance < $distance
    ORDER BY distance ASC;"
	);

	if ($nearbyCities) {
		return $nearbyCities;
	}
}


function my_get_nearby_locations($latitude, $longitude, $min_distance = 0, $max_distance = 400, $limit = 20, $post_type = 'place', $use_miles = false)
// function my_get_nearby_locations($latitude, $longitude, $min_distance = 0, $max_distance = 400, $post_type = 'place', $use_miles = false)
{
	global $wpdb;

	$meta_key_latitude = 'place_location_place_lat';
	$meta_key_longitude = 'place_location_place_lng';

	$miles_to_km = $use_miles ? 1 : 1.609344;

	$query = "SELECT DISTINCT
            t_lat.post_id,
            -- t_post.post_title,
            -- t_lat.meta_value as latitude,
            -- t_long.meta_value as longitude,
            ((ACOS(SIN(%f * PI() / 180) * SIN(t_lat.meta_value * PI() / 180) + COS(%f * PI() / 180) * COS(t_lat.meta_value * PI() / 180) * COS((%f - t_long.meta_value) * PI() / 180)) * 180 / PI()) * 60 * 1.1515 * {$miles_to_km}) AS distance
            FROM {$wpdb->postmeta} AS t_lat
            LEFT JOIN {$wpdb->postmeta} as t_long ON t_lat.post_id = t_long.post_id
            INNER JOIN {$wpdb->posts} as t_post ON t_post.ID = t_lat.post_id
            WHERE t_lat.meta_key = %s AND t_long.meta_key = %s AND t_post.post_type = %s AND t_post.post_status = 'publish'
            HAVING distance > %d AND distance < %d ORDER BY distance ASC LIMIT %d;";


	$prepared_query = $wpdb->prepare($query, [$latitude, $latitude, $longitude, $meta_key_latitude, $meta_key_longitude, $post_type, $min_distance, $max_distance, $limit]);
	// $prepared_query = $wpdb->prepare($query, [$latitude, $latitude, $longitude, $meta_key_latitude, $meta_key_longitude, $post_type, $min_distance, $max_distance]);

	return $wpdb->get_results($prepared_query, ARRAY_A);
}




// Admin and User rediection after login
// add_filter('login_redirect', 'cpm_login_redirect', 10, 3);

function cpm_login_redirect($redirect_to, $request, $user)
{
	// if(!is_page(9212)){
	// Is there a user?
	// if (is_array($user->roles)) {
	// 	// Is it an administrator?
	// 	if (in_array('administrator', $user->roles) || (get_user_meta(get_current_user_id(), 'rae_approved', true) == 1))
	// 		return home_url('/wp-admin/');
	// 	else
	// 		return home_url('/map/');
	// }
	// }
}


function draft_story()
{
	$labels = array(
		'name' => _x('Draft Stories', 'Post type general name', 'Draft Story'),
		'singular_name' => _x('Draft Story', 'Post type singular name', 'Draft Story'),
		'menu_name' => _x('Draft Stories', 'Admin Menu text', 'Draft Story'),
		'name_admin_bar' => _x('Draft Story', 'Add New on Toolbar', 'Draft Story'),
		'add_new' => __('Add New', 'Draft Story'),
		'add_new_item' => __('Add New Draft Story', 'Draft Story'),
		'new_item' => __('New Draft Story', 'Draft Story'),
		'edit_item' => __('Edit Draft Story', 'Draft Story'),
		'view_item' => __('View Draft Story', 'Draft Story'),
		'all_items' => __('All Draft Stories', 'Draft Story'),
		'search_items' => __('Search Draft Stories', 'Draft Story'),
		'parent_item_colon' => __('Parent Draft Stories:', 'Draft Story'),
		'not_found' => __('No Draft Stories found.', 'Draft Story'),
		'not_found_in_trash' => __('No Draft Stories found in Trash.', 'Draft Story'),
		'featured_image' => _x('Draft Story Cover Image', 'Overrides the “Featured Image” phrase for this post type. Added in 4.3', 'Draft Story'),
		'set_featured_image' => _x('Set cover image', 'Overrides the “Set featured image” phrase for this post type. Added in 4.3', 'Draft Story'),
		'remove_featured_image' => _x('Remove cover image', 'Overrides the “Remove featured image” phrase for this post type. Added in 4.3', 'Draft Story'),
		'use_featured_image' => _x('Use as cover image', 'Overrides the “Use as featured image” phrase for this post type. Added in 4.3', 'Draft Story'),
		'archives' => _x('Draft Story archives', 'The post type archive label used in nav menus. Default “Post Archives”. Added in 4.4', 'Draft Story'),
		'insert_into_item' => _x('Insert into Draft Story', 'Overrides the “Insert into post”/”Insert into page” phrase (used when inserting media into a post). Added in 4.4', 'Draft Story'),
		'uploaded_to_this_item' => _x('Uploaded to this Draft Story', 'Overrides the “Uploaded to this post”/”Uploaded to this page” phrase (used when viewing media attached to a post). Added in 4.4', 'Draft Story'),
		'filter_items_list' => _x('Filter Draft Stories list', 'Screen reader text for the filter links heading on the post type listing screen. Default “Filter posts list”/”Filter pages list”. Added in 4.4', 'Draft Story'),
		'items_list_navigation' => _x('Draft Stories list navigation', 'Screen reader text for the pagination heading on the post type listing screen. Default “Posts list navigation”/”Pages list navigation”. Added in 4.4', 'Draft Story'),
		'items_list' => _x('Draft Stories list', 'Screen reader text for the items list heading on the post type listing screen. Default “Posts list”/”Pages list”. Added in 4.4', 'Draft Story'),
	);
	$args = array(
		'labels' => $labels,
		'description' => 'Draft Story custom post type.',
		'public' => false,
		'publicly_queryable' => false,
		'show_ui' => false,
		'show_in_menu' => false,
		'query_var' => true,
		'rewrite' => array('slug' => 'draft_story'),
		'capability_type' => 'post',
		'has_archive' => false,
		'hierarchical' => false,
		'menu_position' => 20,
		'supports' => array('title', 'editor', 'author', 'thumbnail'),
		'taxonomies' => array('story_type'),
		'show_in_rest' => false
	);

	register_post_type('Draft Story', $args);
}
add_action('init', 'draft_story');

// Display user first name on story page
add_action('wp_footer', 'cpm_display_user_first_name_on_story');
function cpm_display_user_first_name_on_story()
{
	$current_user = wp_get_current_user();
	$first_name = $current_user->first_name;
	if (isset($first_name) && !empty($first_name)) {
	?>
		<script>
			jQuery(document).ready(function() {
				jQuery('.page-template-template-my_stories h1.entry-title').html("<?php echo $first_name; ?>'s Stories");
			})
		</script>
	<?php
	}
}


//Code to change your email address:

add_filter('wp_mail_from', 'sender_email');
function sender_email($original_email_address)
{
	return 'thegoatpol@tutanota.com';
}

//Code to change your sender name:

add_filter('wp_mail_from_name', 'sender_name');
function sender_name($original_email_from)
{
	return 'The GOAT PoL';
}


// submission off 

if (!function_exists('cpm_submission_page_redirect')) {

	function cpm_submission_page_redirect()
	{
		$submission = get_field('submissions_off', 'option');
		if ($submission == true) {
			// if (is_page('/add-place/')) {
			if (is_page('5102') or is_page('5439')) {
				wp_redirect(home_url('/submissions-closed-temporarily/'));
				die;
			}
		}
	}
	add_action('template_redirect', 'cpm_submission_page_redirect');

	function cpm_submission_page_redirect2()
	{
		// var_dump("here");
		$submission = get_field('submissions_off', 'option');
		if ($submission == true) {
			global $pagenow;
			if ($pagenow === 'post-new.php' && isset($_GET['post_type']) && $_GET['post_type'] === 'story') {
				$current_user = wp_get_current_user();
				// if (current_user_can('edit_others_posts') || current_user_can('manage_options')) {
				if (in_array('editor', (array) $current_user->roles) || in_array('administrator', (array) $current_user->roles)) {
					// exit;
				} else {
					wp_redirect(home_url('/submissions-closed-temporarily/'));
					exit;
				}
			}
		}
	}
	add_action('load-post-new.php', 'cpm_submission_page_redirect2');


	// function cpm_remove_menu_item_story_non_admin_submission_off()
	// {
	// $submission = get_field('submissions_off', 'option');
	// if ($submission == true) {
	// 	$user = wp_get_current_user();
	// 	$allowed_roles = array('editor', 'administrator');
	// 	if (!array_intersect($allowed_roles, $user->roles)) {

	// 		remove_menu_page('edit.php?post_type=story');
	// 	}
	// }
	// }
	// add_action('admin_menu', 'cpm_remove_menu_item_story_non_admin_submission_off');
}


function admin_toggle_payment_status()
{
	?>
	<script type="text/javascript">
		jQuery(document).ready(function() {
			if (window.location.href.indexOf("post_type=story") > -1) {

				var a = jQuery("#the-list tr td.column-6412eef9722d10")

				a.each(function() {
					if (a.data("colname") == "Payment Status") {

						var b = jQuery(this.children);

						if (b.hasClass('dashicons-no-alt')) {
							jQuery("<span style='color:#dc3232;display: block;'>Unpaid</span>").insertAfter(b);

						} else if (b.hasClass('dashicons-yes')) {
							jQuery("<span style='color:#46b450;display: block;'>Paid</span>").insertAfter(b);

						}

					}
				})
			}

		});
	</script>
	<?php
}
add_action('admin_footer', 'admin_toggle_payment_status');


// User Profile submission Hook
if (!function_exists('cpm_pol_handle_profileform_submission')) {
	// add_action('af/form/submission', 'cpm_pol_handle_profileform_submission', 10, 3);
	function cpm_pol_handle_profileform_submission($form, $fields, $args)
	{
		$cpm_payment_method = af_get_field('cpm_payment_method');
		$cpm_payment_details = af_get_field('cpm_payment_details');
		$user_address = af_get_field('user_address');
		$gotpol_payement_recipient_writer = af_get_field('gotpol_payement_recipient_writer');
		$goatpol_payment_user_first_name = af_get_field('first_name');
		$goatpol_payment_user_middle_name = af_get_field('goatpol_payment_user_middle_name');
		$goatpol_payment_user_last_name = af_get_field('last_name');
		$goatpol_payment_user_street = af_get_field('goatpol_payment_user_street');
		$goatpol_payment_user_city = af_get_field('goatpol_user_city');
		$goatpol_payment_user_country = af_get_field('goatpol_payment_user_country');
		$goatpol_paypal_email_address = af_get_field('goatpol_paypal_email_address');
		$goatpol_other_payment_user_wu_country = af_get_field('goatpol_other_payment_user_wu_country');
		$goatpol_other_payment_user_wu_pick_currency = af_get_field('goatpol_other_payment_user_wu_pick_currency');
		$goatpol_other_payment_methods_wr_receive_method = af_get_field('goatpol_other_payment_methods_wr_receive_method');
		$goatpol_other_payment_methods_wr_cp_partner = af_get_field('goatpol_other_payment_methods_wr_cp_partner');
		$goatpol_other_payment_methods_wr_mm_partner = af_get_field('goatpol_other_payment_methods_wr_mm_partner');
		$goatpol_other_payment_methods_wr_mm_mad = af_get_field('goatpol_other_payment_methods_wr_mm_mad');
		$goatpol_other_payment_methods_wr_mm_cd_email = af_get_field('goatpol_other_payment_methods_wr_mm_cd_email');
		$goatpol_other_payment_methods_wr_mm_cd_phoneno = af_get_field('goatpol_other_payment_methods_wr_mm_cd_phoneno');
		$check_profile_form = af_get_field('check_profile_form');

		if ($cpm_payment_method != '') {
			update_user_meta(get_current_user_id(), 'cpm_payment_method', $cpm_payment_method);
		}
		if ($cpm_payment_details != '') {
			update_user_meta(get_current_user_id(), 'cpm_payment_details', $cpm_payment_details);
		}
		if ($user_address != '') {
			update_user_meta(get_current_user_id(), 'user_address', $cpm_payment_method);
		}
		if ($gotpol_payement_recipient_writer) {
			update_user_meta(get_current_user_id(), 'gotpol_payement_recipient_writer', $gotpol_payement_recipient_writer);
		}
		if ($goatpol_payment_user_first_name) {
			update_user_meta(get_current_user_id(), 'first_name', $goatpol_payment_user_first_name);
		}
		if ($goatpol_payment_user_middle_name) {
			update_user_meta(get_current_user_id(), 'goatpol_payment_user_middle_name', $goatpol_payment_user_middle_name);
		}
		if ($goatpol_payment_user_last_name) {
			update_user_meta(get_current_user_id(), 'last_name', $goatpol_payment_user_last_name);
		}
		if ($goatpol_payment_user_street) {
			update_user_meta(get_current_user_id(), 'goatpol_payment_user_street', $goatpol_payment_user_street);
		}
		if ($goatpol_payment_user_city) {
			update_user_meta(get_current_user_id(), 'goatpol_user_city', $goatpol_payment_user_city);
		}
		if ($goatpol_payment_user_country) {
			update_user_meta(get_current_user_id(), 'goatpol_payment_user_country', $goatpol_payment_user_country);
		}
		if ($goatpol_paypal_email_address) {
			update_user_meta(get_current_user_id(), 'goatpol_paypal_email_address', $goatpol_paypal_email_address);
		}
		if ($goatpol_other_payment_user_wu_country) {
			update_user_meta(get_current_user_id(), 'goatpol_other_payment_user_wu_country', $goatpol_other_payment_user_wu_country);
		}
		if ($goatpol_other_payment_user_wu_pick_currency) {
			update_user_meta(get_current_user_id(), 'goatpol_other_payment_user_wu_pick_currency', $goatpol_other_payment_user_wu_pick_currency);
		}
		if ($goatpol_other_payment_methods_wr_receive_method) {
			update_user_meta(get_current_user_id(), 'goatpol_other_payment_methods_wr_receive_method', $goatpol_other_payment_methods_wr_receive_method);
		}
		if ($goatpol_other_payment_methods_wr_cp_partner) {
			update_user_meta(get_current_user_id(), 'goatpol_other_payment_methods_wr_cp_partner', $goatpol_other_payment_methods_wr_cp_partner);
		}
		if ($goatpol_other_payment_methods_wr_mm_partner) {
			update_user_meta(get_current_user_id(), 'goatpol_other_payment_methods_wr_mm_partner', $goatpol_other_payment_methods_wr_mm_partner);
		}
		if ($goatpol_other_payment_methods_wr_mm_mad) {
			update_user_meta(get_current_user_id(), 'goatpol_other_payment_methods_wr_mm_mad', $goatpol_other_payment_methods_wr_mm_mad);
		}
		if ($goatpol_other_payment_methods_wr_mm_cd_email) {
			update_user_meta(get_current_user_id(), 'goatpol_other_payment_methods_wr_mm_cd_email', $goatpol_other_payment_methods_wr_mm_cd_email);
		}
		if ($goatpol_other_payment_methods_wr_mm_cd_phoneno) {
			update_user_meta(get_current_user_id(), 'goatpol_other_payment_methods_wr_mm_cd_phoneno', $goatpol_other_payment_methods_wr_mm_cd_phoneno);
		}
		if ($check_profile_form) {
			update_user_meta(get_current_user_id(), 'check_profile_form', $check_profile_form);
		}
	}
}


add_filter('gettext', 'cpm_change_user_email_label', 20, 3);
function cpm_change_user_email_label($translated_text, $text, $domain)
{
	if ($text === 'Email') {
		$translated_text = 'Recipient Email';
	}
	return $translated_text;
}



//ad table "commissions"
add_action('after_setup_theme', 'cpm_create_commission_table');
function cpm_create_commission_table()
{
	global $wpdb;
	$charset_collate = $wpdb->get_charset_collate();
	$table_name = $wpdb->prefix . 'commission';

	if ($wpdb->get_var("SHOW TABLES LIKE '$table_name'") != $table_name) {
		$sql = "CREATE TABLE `$table_name` (
            `id` mediumint(9) NOT NULL AUTO_INCREMENT,
            `code` varchar(12) NOT NULL,
            `status` tinyint(1) NOT NULL DEFAULT 0,
			`org_rae` int(11) NOT NULL DEFAULT 0,
            `current_owner` int(11) NOT NULL DEFAULT 0,
            PRIMARY KEY (`id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";

		require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
		dbDelta($sql);

		for ($i = 0; $i < 720; $i++) {
			$random_code = wp_generate_password(12, false, false);
			$wpdb->insert(
				$table_name,
				array(
					'code' => $random_code,
					'status' => 0
				)
			);
		}
	}
}


add_filter('query_vars', 'add_nom_de_plume_to_query_vars');
function add_nom_de_plume_to_query_vars($vars)
{
	$vars[] = 'nom_de_plume';
	return $vars;
}


add_filter('query_vars', 'add_commission_to_query_vars');
function add_commission_to_query_vars($vars)
{
	$vars[] = 'commission';
	return $vars;
}



add_action('admin_init', 'pol_restrict_admin_access',  1);
function pol_restrict_admin_access()
{
	// Do not run on AJAX requests
	if (defined('DOING_AJAX') && DOING_AJAX) {
		return;
	}

	// Get the current user
	$user = wp_get_current_user();
	$roles = (array) $user->roles;

	// Check if the user has 'administrator' or 'editor' role
	if (!in_array('administrator', $roles) && !in_array('editor', $roles)) {
		// If the user is not an administrator or editor, redirect them to the homepage
		wp_redirect(home_url());
		exit;
	}
}



add_action('after_setup_theme', 'pol_remove_admin_bar');
function pol_remove_admin_bar()
{
	// $user_role = 'user';
	// if (get_user_meta(get_current_user_id(), 'rae_approved', true) == 1) {
	// 	$user_role = 'rae';
	// }
	if ((!current_user_can('administrator') && !is_admin()) && (get_user_meta(get_current_user_id(), 'rae_approved', true) != 1)) {
		show_admin_bar(false);
	}
}


add_action('wp_login', 'pol_check_for_updated_contributors_page', 10, 2);
function pol_check_for_updated_contributors_page($user_login, $user)
{

	$curr_user_id = $user->ID;

	$has_updated_profile = get_user_meta($curr_user_id, 'has_updated_contributors_page', true);

	if (gettype($has_updated_profile) == 'boolean' || (int) $has_updated_profile != 1) { ?>
		<script>
			window.location.href = '<?php echo home_url('/registration'); ?>'
		</script>
	<?php }
}



add_action('init', 'pol_workshops_post_type');
function pol_workshops_post_type()
{
	$labels = array(
		'name' => _x('Workshops', 'Post type general name', 'Workshops'),
		'singular_name' => _x('Workshop', 'Post type singular name', 'Workshop'),
		'menu_name' => _x('Workshops', 'Admin Menu text', 'Workshop'),
		'menu_icon' => 'dashicons-megaphone',
		'name_admin_bar' => _x('Workshop', 'Add New on Toolbar', 'Workshop'),
		'add_new' => __('Add New', 'Workshop'),
		'add_new_item' => __('Add New Workshop', 'Workshop'),
		'new_item' => __('New Workshop', 'Workshop'),
		'edit_item' => __('Edit Workshop', 'Workshop'),
		'view_item' => __('View Workshop', 'Workshop'),
		'all_items' => __('All Workshops', 'Workshop'),
		'search_items' => __('Search Workshops', 'Workshop'),
		'parent_item_colon' => __('Parent Workshops:', 'Workshop'),
		'not_found' => __('No Workshops found.', 'Workshop'),
		'not_found_in_trash' => __('No Workshops found in Trash.', 'Workshop'),
		'featured_image' => _x('Workshop Cover Image', 'Overrides the “Featured Image” phrase for this post type. Added in 4.3', 'Workshop'),
		'set_featured_image' => _x('Set cover image', 'Overrides the “Set featured image” phrase for this post type. Added in 4.3', 'Workshop'),
		'remove_featured_image' => _x('Remove cover image', 'Overrides the “Remove featured image” phrase for this post type. Added in 4.3', 'Workshop'),
		'use_featured_image' => _x('Use as cover image', 'Overrides the “Use as featured image” phrase for this post type. Added in 4.3', 'Workshop'),
		'archives' => _x('Workshop archives', 'The post type archive label used in nav menus. Default “Post Archives”. Added in 4.4', 'Workshop'),
		'insert_into_item' => _x('Insert into Workshop', 'Overrides the “Insert into post”/”Insert into page” phrase (used when inserting media into a post). Added in 4.4', 'Workshop'),
		'uploaded_to_this_item' => _x('Uploaded to this Workshop', 'Overrides the “Uploaded to this post”/”Uploaded to this page” phrase (used when viewing media attached to a post). Added in 4.4', 'Workshop'),
		'filter_items_list' => _x('Filter Workshops list', 'Screen reader text for the filter links heading on the post type listing screen. Default “Filter posts list”/”Filter pages list”. Added in 4.4', 'Workshop'),
		'items_list_navigation' => _x('Workshops list navigation', 'Screen reader text for the pagination heading on the post type listing screen. Default “Posts list navigation”/”Pages list navigation”. Added in 4.4', 'Workshop'),
		'items_list' => _x('Workshops list', 'Screen reader text for the items list heading on the post type listing screen. Default “Posts list”/”Pages list”. Added in 4.4', 'Workshop'),
	);
	$args = array(
		'labels' => $labels,
		'description' => 'Workshops for authors',
		'public' => false,
		'publicly_queryable' => true,
		'show_ui' => true,
		'show_in_menu' => true,
		'query_var' => true,
		'rewrite' => array('slug' => 'workshops'),
		'capability_type' => 'post',
		'has_archive' => false,
		'hierarchical' => false,
		'menu_position' => 10,
		'supports' => array('title', 'editor', 'excerpt', 'thumbnail'),
	);

	register_post_type('workshop', $args);
}


// add_action('admin_menu', 'custom_book_submenu');

// function custom_book_submenu() {
//     // Add submenu page to the "Books" custom post type
//     add_submenu_page(
//         'edit.php?post_type=workshop', // Parent menu slug (edit.php?post_type=book for "Books" custom post type)
//         'Workshop Participants',             // Submenu page title
//         'Participants',             // Submenu title
//         'manage_options',           // Capability required to access the submenu
//         'participant_page',        // Submenu slug
//         'participant_callback'     // Callback function to display the content of the submenu
//     );
// }

// // Callback function to display the content of the submenu
// function participant_callback() {
//     // Output your submenu content here
//     echo '<div class="wrap"><h2>Book Submenu</h2><p>This is the content of your submenu.</p></div>';
// }




// add_filter('map_meta_cap', 'pol_restrict_workshop', 10, 4);
// function pol_restrict_workshop($caps, $cap, $user_id, $args) {
//     if ($args[0]->post_type == 'Workshop' && $cap == 'publish_posts') {
//         $user = get_userdata($user_id);
//         if (!empty($user->roles) && is_array($user->roles)) {
//             foreach ($user->roles as $role) {
//                 if ($role == 'editor') {
//                     return array('read');
//                 }
//             }
//         }
//     }
//     return $caps;
// }


//======================lists page starts =================

require get_template_directory() . '/inc/list-view/list-view-functions.php';

//======================lists page ends =================




function pol_check_if_commission_is_of_published_post($commission)
{
	$post_of_commission_args = array(
		'post_type'      => 'story', // The post type
		'post_status'    => 'publish', // The post status
		'posts_per_page' => -1, // Get all posts
		'meta_query'     => array(
			array(
				'key'     => 'commission_used', // Replace 'your_meta_key' with the actual meta key
				'value'   => $commission, // The meta value to match
				'compare' => '=', // Exact match
			),
		),
	);
	$post_of_commission = new WP_Query($post_of_commission_args);
	$curr_comm_post_id = 0;

	if ($post_of_commission->have_posts()) {
		while ($post_of_commission->have_posts()) {
			$post_of_commission->the_post();
			$curr_comm_post_id = get_the_ID();
		}
	}

	// return $curr_comm_post_id;

	return ($curr_comm_post_id == 0) ? false : true;
}

function pol_get_commission_post_info($commission)
{
	global $wpdb;
	$table_prefix = $wpdb->prefix;
	$results = $wpdb->get_results($wpdb->prepare("
		SELECT " . $table_prefix . "posts.* FROM " . $table_prefix . "posts INNER JOIN " . $table_prefix . "postmeta ON ( " . $table_prefix . "posts.ID = " . $table_prefix . "postmeta.post_id ) WHERE 1=1 
		AND ( ( " . $table_prefix . "postmeta.meta_key = 'commission_used' 
		AND " . $table_prefix . "postmeta.meta_value = '" . $commission . "' ) ) 
		AND " . $table_prefix . "posts.post_type IN ('drafts', 'story') 
		AND " . $table_prefix . "posts.post_status IN ('publish', 'draft', 'pending', 'future', 'private', 'inherit', 'trash')
		GROUP BY " . $table_prefix . "posts.ID 
		ORDER BY " . $table_prefix . "posts.post_date DESC
	"), ARRAY_A);

	$post_info = [];
	if (!empty($results)) {
		foreach ($results as $row) {
			array_push($post_info, $row['ID'], $row['post_title']);
			return $post_info;
		}
	}
	return false;
}






function list_transferred_commissions($user)
{

	global $wpdb;
	$table_name = $wpdb->prefix . 'commission';




	$results = $wpdb->get_results($wpdb->prepare("SELECT * FROM `$table_name` WHERE org_rae = $user AND current_owner != $user"), ARRAY_A);

	if ($results) {
		$result .= '<table class="profile-table profile-select-commission-table">';
		$result .= '<tr>';

		$result .= '<th>Commission</th>';
		$result .= '<th>Transferred To</th>';
		$result .= '<th>Status</th>';
		$result .= '</tr>';

		foreach ($results as $row) {

			$status = 'Available';

			if ($row['status'] == 1) {
				if ($user_role != 'user') {
					$checkbox_status = 'disabled';
				}
				$status = 'Allocated';
			} else if ($row['status'] == 2) {
				$checkbox_status = 'disabled';
				$status = 'In use';
			}

			$status = pol_check_if_commission_is_of_published_post($row['code']) ? 'Published' : $status;

			$result .= "
                        <tr>
                                        
                            <td>" . esc_html($row['code']) . "</td>";


			$user_detail = get_userdata($row['current_owner']);
			$result .= "<td>" . $user_detail->display_name . "</td>";


			$result .= "<td>" . $status . "</td>
                                        
                                    </tr>
                                ";
		}
		$result .= "</table>";
	}
	return $result;
}














// Function to list out all user commisions based on status
add_action('wp_ajax_list_user_commisions', 'list_user_commisions');
function list_user_commisions($user, $status = "", $sort = "")
{
	$is_ajax_call = defined('DOING_AJAX') && DOING_AJAX;
	global $wpdb;
	$sort_by = isset($_POST['sort_by']) ? $_POST['sort_by'] : $sort;
	$user = isset($_POST['current_author_id']) ? $_POST['current_author_id'] : $user;
	$table_name = $wpdb->prefix . 'commission';
	$author_user = get_user_by('id', $user);
	$current_logged_in_user = get_current_user_id();
	$current_logged_in_user_meta = get_userdata($current_logged_in_user);
	$is_current_logged_in_user_rae = get_user_meta($current_logged_in_user, 'rae_approved', true);


	$user_role = 'user';

	if (get_user_meta($author_user->ID, 'rae_approved', true) == 1) {
		$user_role = 'rae';
	} else if (in_array('administrator', (array) $author_user->roles)) {
		$user_role = 'admin';
	}

	$curr_author = get_userdata($user);
	$curr_author_display_name = $curr_author->display_name;
	$sql = '';

	if ($user_role == 'rae') {
		if ($sort_by != '') {
			$sql = "SELECT * FROM `$table_name` WHERE org_rae = $user ORDER BY status $sort_by";
		} else {
			$sql = "SELECT * FROM `$table_name` WHERE org_rae = $user ORDER BY last_transfer DESC";
		}
	} else {
		if ($sort_by != '') {
			$sql = "SELECT * FROM `$table_name` WHERE current_owner = $user ORDER BY status $sort_by";
		} else {
			$sql = "SELECT * FROM `$table_name` WHERE current_owner = $user ORDER BY last_transfer DESC";
		}
	}


	$results = $wpdb->get_results($wpdb->prepare($sql), ARRAY_A);


	$result = '';
	if ($results) {
		$result .= '<table class="profile-table profile-select-commission-table">';
		$result .= '<tr>';

		$result .= '<th>Commission</th>';
		$result .= '<th>Originating RAE</th>';
		$result .= '<th>Story Title</th>';
		$result .= '<th>Status <span class="sort_comission" data-current_author_id="' . $user . '">⇅</span></th>';
		$result .= '<th>Last Action</th>';
		if ($is_current_logged_in_user_rae && $user_role == 'user') {
			$result .= '<th>Action</th>';
		}

		$result .= '</tr>';

		foreach ($results as $row) {


			$status = 'Available';

			if ($row['status'] == 1) {
				$status = 'Allocated';
			} else if ($row['status'] == 2) {
				$status = 'In use';
			}

			$status = pol_check_if_commission_is_of_published_post($row['code']) ? 'Published' : $status;

			$commission_post_title = '';
			if ($status == 'Published' || $status == 'In use') {
				// var_dump(pol_get_commission_post_info($row['code']));
				$commission_post_title =  pol_get_commission_post_info($row['code']) == false ? '' : pol_get_commission_post_info($row['code'])[1];
			} else {
				$commission_post_title = '';
			}

			$result .= "<tr><td>" . esc_html($row['code']) . "</td>";

			if ($user_role == 'admin' ||  $user_role == 'rae') {
				$result .= "<td>" . $curr_author_display_name . "</td>";
			} else {
				if ($row['org_rae'] == $row['current_owner']) {
					$result .= "<td></td>";
				} else {
					$user_detail = get_userdata($row['org_rae']);
					$result .= "<td>" . $user_detail->display_name . "</td>";
				}
			}

			//display story title
			$result .= "<td>" . $commission_post_title . "</td>";
			$result .= "<td>" . $status . "</td>";
			$result .= "<td>" . $row['last_transfer'] . " <br>
				<small class='log-" . $row['code'] . " commission-log-open'>Log</small>
				<div class='log-popup-" . $row['code'] . "' style='display:none;'>" . pol_decode_commission_action_history($row['code']) . "</div>
				</td>";

			if ($is_current_logged_in_user_rae && $user_role == 'user') {
				if ($row['status'] == 1  && $row['org_rae'] == $current_logged_in_user) {
					$result .= "<td> <a href='javascript:void(0);' data-comission_id='" . $row['id'] . "' data-org_rae='" . $row['org_rae'] . "' data-current_owner='" . $row['current_owner'] . "' class ='revoke-button' id ='revoke-button'><i class='fa-regular fa-circle-xmark'></i><span>Revoke commission</span></a></td></tr>";
				} else {
					$result .= "<td></td>";
				}
			}
		}
		$result .= '</table>';
	}
	if ($is_ajax_call) {
		wp_send_json_success($result);
		wp_die();
	} else {
		return $result;
	}
}




/* email template function */
if (!function_exists('gotpol_email_template')) {
	function gotpol_email_template($user_email, $user_name, $post_id, $action)
	{

		ob_start();
		add_filter('wp_mail_content_type', 'pol_set_content_type');

		$to = $user_email;
		$email_action = $action;
		$workshop_title = get_the_title($post_id);
		$workshop_url = get_permalink($post_id);
		$workshop_time_date = get_field('workshop-date-time', $post_id);
		$workshop_online_link = get_field('online_link', $post_id);


	?>
		<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">

		<html xmlns="http://www.w3.org/1999/xhtml" xmlns="http://www.w3.org/1999/xhtml" style="font-family: 'Helvetica Neue', 'Helvetica', Helvetica, Arial, sans-serif; font-size: 100%; line-height: 1.6; margin: 0; padding: 0;">

		<head>

			<meta name="viewport" content="width=device-width" />



			<meta http-equiv=3D"Content-Type" content=3D"text/html; charset=3Dutf-8"= />

			<!-- <title>An update was made to the task you are involvd in.</title> -->

		</head>

		<body bgcolor="#f6f6f6" style="font-family: 'Helvetica Neue', 'Helvetica', Helvetica, Arial, sans-serif; font-size: 100%; line-height: 1.6; -webkit-font-smoothing: antialiased; -webkit-text-size-adjust: none; width: 100% !important; height: 100%; margin: 0; padding: 0; ">



			<!-- body -->

			<table class="body-wrap" bgcolor="#f6f6f6" style="font-family: 'Helvetica Neue', 'Helvetica', Helvetica, Arial, sans-serif; font-size: 100%; line-height: 1.6; width: 100%; margin: 0; padding: 20px;">
				<tr style="font-family: 'Helvetica Neue', 'Helvetica', Helvetica, Arial, sans-serif; font-size: 100%; line-height: 1.6; margin: 0; padding: 0;">
					<td style="font-family: 'Helvetica Neue', 'Helvetica', Helvetica, Arial, sans-serif; font-size: 100%; line-height: 1.6; margin: 0; padding: 0;">
					</td>

					<td class="container" bgcolor="#FFFFFF" style="font-family: 'Helvetica Neue', 'Helvetica', Helvetica, Arial, sans-serif; font-size: 100%; line-height: 1.6; display: block !important; max-width: 600px !important; clear: both !important; margin: 0 auto; padding: 20px; border: 1px solid #f0f0f0;">



						<!-- content -->

						<div class="content" style="font-family: 'Helvetica Neue', 'Helvetica', Helvetica, Arial, sans-serif; font-size: 100%; line-height: 1.6; max-width: 600px; display: block; margin: 0 auto; padding: 0;">

							<table style="font-family: 'Helvetica Neue', 'Helvetica', Helvetica, Arial, sans-serif; font-size: 100%; line-height: 1.6; width: 100%; margin: 0; padding: 0;">
								<tr style="font-family: 'Helvetica Neue', 'Helvetica', Helvetica, Arial, sans-serif; font-size: 100%; line-height: 1.6; margin: 0; padding: 0;">
									<td style="font-family: 'Helvetica Neue', 'Helvetica', Helvetica, Arial, sans-serif; font-size: 100%; line-height: 1.6; margin: 0; padding: 0;">

										<p><img src="<?php echo site_url(); ?>/wp-content/uploads/2022/06/logo.jpg" width="150px" /></p>
										<p style="margin-top:50px;">
											<?php
											// When a writer signs-up to a workshop, sent to writer:
											if ($action == 'signup_for_workshop') {
												goat_pol_email_content_when_writer_signs_up_workshop($user_name, $post_id);
												$subject = "You have signed up for " . get_post_field('post_title', (int)$post_id);
												// When a writer removes himself from a workshop’s sign-up list:
											} elseif ($action == 'remove_from_workshop_sign_up_list') {
												goat_pol_email_content_when_writer_removes_himself_from_workshop($user_name, $post_id);
												$subject = "You have been removed from workshop sign-up list";
											}
											// 24-hours before starting time of workshop, sent to all authors signed-up for it:

											// The day after a workshop has happened, send to all authors who were in attendance (all whose names are still listed in the sign-up):
											?>
										</p>

									</td>

								</tr>
							</table>

							<table style="font-family: 'Helvetica Neue', 'Helvetica', Helvetica, Arial, sans-serif; font-size: 100%; text-align:right; line-height: 1.6; width: 100%; margin: 0; padding: 0;">
								<tr style="font-family: 'Helvetica Neue', 'Helvetica', Helvetica, Arial, sans-serif; font-size: 100%; line-height: 1.6; margin: 0; padding: 0;">
									<td style="font-family: 'Helvetica Neue', 'Helvetica', Helvetica, Arial, sans-serif; font-size: 100%; line-height: 1.6; margin: 0; padding: 0;">
										<br>

										<br />

										Thank you,<br />

										The GOAT PoL<br />

										</p>
									</td>
								</tr>
								<tr style="font-family: 'Helvetica Neue', 'Helvetica', Helvetica, Arial, sans-serif; font-size: 100%; line-height: 1.6; margin: 0; padding: 0;">
									<td class="padding" style="font-family: 'Helvetica Neue', 'Helvetica', Helvetica, Arial, sans-serif; font-size: 100%; line-height: 1.6; margin: 0; padding: 10px 0;">

										<img src="<?php echo site_url(); ?>/wp-content/uploads/2022/02/GOAT_21-scaled.jpg" width="150px;" />

									</td>
								</tr>
								<tr style="font-family: 'Helvetica Neue', 'Helvetica', Helvetica, Arial, sans-serif; font-size: 100%; line-height: 1.6; margin: 0; padding: 0;">
									<td>
										<img style="margin-top:30px;" src="<?php echo site_url(); ?>/wp-content/themes/goatpol/assets/img/FullTitle_transparent.png" width="580px" />
									</td>

								</tr>
							</table>
						</div>

						<!-- /content -->



					</td>

					<td style="font-family: 'Helvetica Neue', 'Helvetica', Helvetica, Arial, sans-serif; font-size: 100%; line-height: 1.6; margin: 0; padding: 0;">
					</td>

				</tr>
			</table><!-- /body -->
			<!-- footer -->
			<table class="footer-wrap" style="font-family: 'Helvetica Neue', 'Helvetica', Helvetica, Arial, sans-serif; font-size: 100%; line-height: 1.6; width: 100%; clear: both !important; margin: 0; padding: 0;">
				<tr style="font-family: 'Helvetica Neue', 'Helvetica', Helvetica, Arial, sans-serif; font-size: 100%; line-height: 1.6; margin: 0; padding: 0;">
					<td style="font-family: 'Helvetica Neue', 'Helvetica', Helvetica, Arial, sans-serif; font-size: 100%; line-height: 1.6; margin: 0; padding: 0;">
					</td>

					<td class="container" style="font-family: 'Helvetica Neue', 'Helvetica', Helvetica, Arial, sans-serif; font-size: 100%; line-height: 1.6; display: block !important; max-width: 600px !important; clear: both !important; margin: 0 auto; padding: 0;">



						<!-- content -->

						<div class="content" style="font-family: 'Helvetica Neue', 'Helvetica', Helvetica, Arial, sans-serif; font-size: 100%; line-height: 1.6; max-width: 600px; display: block; margin: 0 auto; padding: 0;">

							<table style="font-family: 'Helvetica Neue', 'Helvetica', Helvetica, Arial, sans-serif; font-size: 100%; line-height: 1.6; width: 100%; margin: 0; padding: 0;">
								<tr style="font-family: 'Helvetica Neue', 'Helvetica', Helvetica, Arial, sans-serif; font-size: 100%; line-height: 1.6; margin: 0; padding: 0;">
									<td align="center" style="font-family: 'Helvetica Neue', 'Helvetica', Helvetica, Arial, sans-serif; font-size: 100%; line-height: 1.6; margin: 0; padding: 0;">



									</td>

								</tr>
							</table>
						</div>

						<!-- /content -->

					</td>

					<td style="font-family: 'Helvetica Neue', 'Helvetica', Helvetica, Arial, sans-serif; font-size: 100%; line-height: 1.6; margin: 0; padding: 0;">
					</td>

				</tr>
			</table><!-- /footer -->
		</body>

		</html>


	<?php

		$message_new = ob_get_contents();


		ob_end_clean();

		$send_from = 'thegoatpol@tutanota.com';

		$headers = array('Content-Type: text/html; charset=UTF-8');

		$headers .= 'From: ' . $send_from . "\r\n";

		$sent = wp_mail($to, $subject, $message_new, $headers);
	}
}


if (!function_exists('goat_pol_email_content_when_writer_signs_up_workshop')) {
	function goat_pol_email_content_when_writer_signs_up_workshop($user_name, $post_id)
	{

		$workshop_title = get_the_title($post_id);
		$workshop_url = get_permalink($post_id);
		$workshop_time_date = get_field('workshop-date-time', $post_id);
		$workshop_online_link = get_field('online_link', $post_id);
	?>
		<p>

			Dear <?php echo $user_name; ?>, thank you for signing up to attend <?php echo $workshop_title; ?>,
			which will meet online at <?php echo $workshop_time_date; ?>.
			You can read more about the workshop, or remove yourself from the list of those attending,
			at its <a href="<?php echo $workshop_url; ?>">listing page</a>
			The online link for attending the workshop is <a href="<?php echo $workshop_online_link; ?>"><?php echo $workshop_online_link; ?></a>.
			Please be prompt and arrive on time.
		</p>
	<?php
	}
}


/* When a writer signs-up to a workshop, sent to writer: */


if (!function_exists('goat_pol_email_content_when_writer_signs_up_workshop')) {
	function goat_pol_email_content_when_writer_signs_up_workshop($user_name, $post_id)
	{

		$workshop_title = get_the_title($post_id);
		$workshop_url = get_permalink($post_id);
		$workshop_time_date = get_field('workshop-date-time', $post_id);
		$workshop_online_link = get_field('online_link', $post_id);
	?>
		<p>

			Dear <?php echo $user_name; ?>, thank you for signing up to attend <?php echo $workshop_title; ?>, which will meet online at <?php echo $workshop_time_date; ?>. You can read more about the workshop, or remove yourself from the list of those attending, at its <a href="<?php echo $workshop_url; ?>">listing page</a>. We’ll send you a follow-up email with the online link for attending one-day before the event. If you don’t see that announcement in your inbox, please check for it in your “spam” or “junkmail” folders.
		</p>


	<?php
	}
}

/* When a writer removes himself from a workshop’s sign-up list: */

if (!function_exists('goat_pol_email_content_when_writer_removes_himself_from_workshop')) {
	function goat_pol_email_content_when_writer_removes_himself_from_workshop($user_name, $post_id)
	{

		$workshop_title = get_the_title($post_id);
		$workshop_url = get_permalink($post_id);
		$workshop_time_date = get_field('workshop-date-time', $post_id);
		$workshop_online_link = get_field('online_link', $post_id);
	?>
		<p>
			Dear <?php echo $user_name; ?>, your name has been removed from the sign-up list for <?php echo $workshop_title; ?>, which will meet online at <?php echo $workshop_time_date; ?>. If this was done by mistake, or if you change your mind, you can sign-up again for free at the <a href="<?php echo $workshop_url; ?>">listing page</a>
		</p>


	<?php
	}
}


/* 24-hours before starting time of workshop, sent to all authors signed-up for it: */

if (!function_exists('goat_pol_email_content_24hour_before_starting_time_of_workshop')) {
	function goat_pol_email_content_24hour_before_starting_time_of_workshop($user_name, $post_id)
	{

		$workshop_title = get_the_title($post_id);
		$workshop_url = get_permalink($post_id);
		$workshop_time_date = get_field('workshop-date-time', $post_id);
		$workshop_online_link = get_field('online_link', $post_id);
	?>
		<p>
			Dear <?php echo $user_name; ?>, thank you for signing up to attend <?php echo $workshop_title; ?>, which will meet online tomorrow at <?php echo $workshop_time_date; ?>. To attend please use the following link:
			<a href="<?php echo $workshop_online_link; ?>">Online Link</a>
			Please be prompt: the convening RAE will remove your name from the list of participants if you do not actually attend.
		</p>


	<?php
	}
}



// The day after a workshop has happened, send to all authors who were in attendance (all whose names are still listed in the sign-up):


if (!function_exists('goat_pol_email_content_the_day_after_workshop_has_happened')) {
	function goat_pol_email_content_the_day_after_workshop_has_happened($user_name, $post_id)
	{

		$workshop_title = get_the_title($post_id);
		$workshop_url = get_permalink($post_id);
		$workshop_time_date = get_field('workshop-date-time', $post_id);
		$workshop_online_link = get_field('online_link', $post_id);
	?>
		<p>

			Dear <?php echo $user_name; ?>, thank you for attending <?php echo $workshop_title; ?>, which met online yesterday. We hope you had an enjoyable time and that you’ll participate again soon. You’ve received credit for attending, and will find the workshop listed on your Contributor’s Page as one of the workshops that you took part in.
		</p>


	<?php
	}
}
function generateToggleTabs()
{
	?>
	<div class="gp-toggle-wrapper list-page-toggle">
		<input class="gp-radio" id="gp-toggle-left" name="group" type="radio">
		<input class="gp-radio" id="gp-toggle-right" name="group" type="radio">
		<input class="gp-radio" id="gp-toggle-middle" name="group" type="radio">
		<div class="gp-toggle-tabs">
			<!-- <label class="gp-toggle-tab" id="gp-tab-left" for="gp-toggle-left">
		<?php // _e('The GOAT PoL READING LISTS', 'pol') 
		?>
	  </label> -->
			<label class="gp-toggle-tab" id="gp-tab-middle" for="gp-toggle-middle">
				<?php _e('The GOAT PoL Reading Lists', 'pol') ?>
			</label>
			<label class="gp-toggle-tab" id="gp-tab-right" for="gp-toggle-right">
				<?php _e('The GOAT PoL MAP', 'pol') ?>
			</label>
		</div>
	</div>
	<?php
}


/***** Change User Permalink to take Nickname instead of username. **

add_filter( 'request', 'wpse5742_request' );
function wpse5742_request( $query_vars )
{
	if ( array_key_exists( 'author_name', $query_vars ) ) {
		global $wpdb;
		$author_id = $wpdb->get_var( $wpdb->prepare( "SELECT user_id FROM {$wpdb->usermeta} WHERE meta_key='nickname' AND meta_value = %s", $query_vars['author_name'] ) );
		if ( $author_id ) {
			$query_vars['author'] = $author_id;
			unset( $query_vars['author_name'] );    
		}
	}
	return $query_vars;
}

add_filter( 'author_link', 'wpse5742_author_link', 10, 3 );
function wpse5742_author_link( $link, $author_id, $author_nicename )
{
	$author_nickname = get_user_meta( $author_id, 'nickname', true );
	if ( $author_nickname ) {
		$author_nickname = strtolower($author_nickname);
		$author_nickname = str_replace(" ", "-", $author_nickname);
		$link = str_replace( $author_nicename, $author_nickname, $link );
	}
	return $link;
}

add_action( 'user_profile_update_errors', 'wpse5742_set_user_nicename_to_nickname', 10, 3 );
function wpse5742_set_user_nicename_to_nickname( &$errors, $update, &$user )
{
	if ( ! empty( $user->nickname ) ) {
		$user->user_nicename = sanitize_title( $user->nickname, $user->display_name );
	}
}

/***********. Permalink changes for Author ends here ******/


/**
 * Register a custom menu page.
 */
// function wpdocs_register_my_custom_menu_page(){
// 	add_menu_page( 
// 		__( 'Custom Menu Title', 'textdomain' ),
// 		'custom menu',
// 		'manage_options',
// 		'custompage',
// 		'my_custom_menu_page',
// 		'',
// 		6
// 	); 
// }
// // add_action( 'admin_menu', 'wpdocs_register_my_custom_menu_page' );

// /**
//  * Display a custom menu page
//  */
// function my_custom_menu_page(){
// 	esc_html_e( 'Admin Page Test', 'textdomain' );	
// }






add_action('admin_enqueue_scripts', 'pol_amdin_scripts');
function pol_amdin_scripts()
{
	wp_localize_script(
		'pol-admin-scripts',
		'pol_admin_ajax_filters',
		array(
			'ajaxurl' => esc_url(admin_url('admin-ajax.php')),
		)
	);
}


//add list of participants in the workshop custom post type in the admin side
add_action('add_meta_boxes', 'add_workshop_meta_box');
function add_workshop_meta_box()
{
	// Check if the current post type is 'workshop'
	if (get_post_type() == 'workshop' && get_current_user_id() == 14) {
		// Add a custom meta box
		add_meta_box(
			'workshop_meta_box',
			'Attendees',
			'workshop_participants_meta_box',
			'workshop',
			'normal',
			'high'
		);
	}
}

// Callback function to display the content of the meta box
function workshop_participants_meta_box($post)
{
	// Use nonce for verification
	wp_nonce_field('save_workshop_meta', 'workshop_meta_nonce');

	// Display the current value of the custom field
	$signups = get_post_meta($post->ID, 'signups', true);
	foreach ($signups as $signup) {
		$user_workshops = get_user_meta($signup, 'workshops', true);
		$user = get_user_by('id', $signup);
		if (!$user) {
			continue;
		}
		$user_display_name = $user->display_name;
		//$user_display_name = ($user_display_name == '') ? $user->login : $user_display_name;
		$has_workshop_in_usermeta = is_array($user_workshops) && in_array($post->ID, $user_workshops);

		$checked = $has_workshop_in_usermeta ? 'checked' : '';
		//echo '<input class="show-hide-workshop" type="checkbox" data-workshop-id="' . $post->ID . '" data-user-id="' . $signup . '" ' . $checked . '>';
		echo '<label>' . $user_display_name . '</label> <br><br>';
	?>
		<script>
			jQuery(document).ready(function() {
				jQuery('.show-hide-workshop').off('change');

				jQuery('.show-hide-workshop').change(function() {
					let add_remove = '';
					let workshop_id = jQuery(this).attr('data-workshop-id');
					let author_id = jQuery(this).attr('data-user-id');
					if (this.checked) {
						add_remove = 'add';
					} else {
						add_remove = 'remove';
					}

					jQuery.ajax({
						url: pol_admin_ajax_filters.ajaxurl,
						type: "POST",
						data: {
							action: "pol_add_or_remove_users_from_workshop",
							workshop_id: workshop_id,
							author_id: author_id,
							workshop_action: add_remove
						},
						success: function(response) {
							// Remove the row from the table
							$(this).closest(".workshop-table").remove();
						},
					});
				});
			});
		</script>
	<?php
	}



	// // Define arguments for the workshop query
	// $current_datetime = date('Y-m-d H:i:s');
	// $args = array(
	// 	'post_type' => 'workshop',
	// 	'posts_per_page' => -1, // Retrieve all workshops
	// 	'meta_query' => [
	//         [
	//             'key' => 'workshop-date-time',
	//             'value' => $current_datetime,
	//             'compare' => '<',
	//             'type' => 'DATETIME'
	//         ]
	//     ],
	// );

	// // Query the workshops
	// $workshops_query = new WP_Query($args);

	// // Check if workshops were found
	// if ($workshops_query->have_posts()) {
	// 	// Start the loop
	// 	while ($workshops_query->have_posts()) {
	// 		$workshops_query->the_post();
	// 		// Get the post title
	// 		$workshop_title = get_the_title();

	// 		// Get the signups meta data (assuming 'signups' is a serialized array)
	// 		$signups_data = get_post_meta(get_the_ID(), 'signups', true);

	// 		// Check if signups data is not empty and is an array
	// 		if (!empty($signups_data) && is_array($signups_data)) {
	// 			echo '<h2>' . esc_html($workshop_title) . '</h2>';
	// 			echo '<ol>';
	// 			// Loop through signups array and display each item
	// 			foreach ($signups_data as $signup_item) {
	// 				// Get user data using the user ID stored in $signup_item
	// 				$user_data = get_userdata($signup_item);
	// 				if ($user_data) {
	// 					$user_display_name = $user_data->display_name;
	// 					echo '<li>' . esc_html($user_display_name) . '</li>';
	// 				}
	// 			}
	// 			echo '</ol>';
	// 		}
	// 	}
	// 	// Restore original post data
	// 	wp_reset_postdata();
	// } else {
	// 	echo 'No workshops found.';
	// }
}

//check if the same place already exists then dont create another place with the same name
function pol_place_already_exists($title)
{
	$args = array(
		'post_type' => 'place',
		'post_status' => 'publish',
		'posts_per_page' => 1,
		'title' => $title,
	);

	$posts = get_posts($args);

	if ($posts) return true;

	return false;
}




//create new location post type
function pol_create_new_place_post_type($map_array, $story_id)
{

	$post = array(
		'post_title'    => $map_array['address'],
		'post_status'   => 'publish',
		'post_author'   => get_current_user_id(),
		'post_type'     => 'place', // Change this if you want to create a different post type
	);

	$place_id = wp_insert_post($post);

	if (!is_wp_error($place_id)) {
		$meta_fields = array(
			'place_location_place_lat' => $map_array['lat'],
			'place_location_place_lng' => $map_array['lng'],
			'place_location' => $map_array,
			'_place_location' => 'field_62566328a51c9',
			'place_physical_location' => '1',
			'where_does' => 'geo_loc',
			'place_author' => get_current_user_id(),
			'place_stories' => [$story_id],
			'_place_stories' => 'field_6218d3d8944ab'
		);

		foreach ($meta_fields as $meta_key => $meta_value) {
			update_post_meta($place_id, $meta_key, $meta_value);
		}

		//add the place to story meta
		update_post_meta($story_id, 'stories_place', $place_id);

		//add story id to place meta
		update_post_meta($story_id, 'place_stories', [$place_id]);
		update_post_meta($story_id, '_place_stories', 'field_6218d3d8944ab');

		return true;
	} else {
		return false;
	}
}



// Add meta box for commission meta field
function add_commission_meta_box()
{
	add_meta_box(
		'commission_meta_box',
		'Commission',
		'render_commission_meta_box',
		'story',
		'normal',
		'high'
	);
}
add_action('add_meta_boxes', 'add_commission_meta_box');

// Render meta box content
function render_commission_meta_box($post)
{
	// Get existing meta value
	$commission = get_post_meta($post->ID, 'commission_used', true);
	$is_edit_page = isset($_GET['action']) && $_GET['action'] === 'edit';
	if (!$is_edit_page) { ?>
		<input type="text" id="commission_field" name="commission_field" value="<?php echo esc_attr($commission); ?>" />
		<button id="check-commission-availability" class="button button-primary button-large" data-author="" data-commission="">Check</button>
		<span class="invalid-commission"></span>
	<?php } else { ?>
		<label for="commission_field">Commission: <?php echo esc_attr($commission); ?></label>
	<?php
	}
}

// Save meta box data
function so_save_commission_meta($post_id)
{

	// echo '<pre>';
	// var_dump($_POST);
	// echo '</pre>';
	if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) return;
	// if (isset($_GET['action']) && ('editpost' == $_GET['action'] || 'newpost' == $_GET['action'])) return;

	if (!current_user_can('edit_post', $post_id)) return;

	if ((isset($_POST['commission_field']) && !empty($_POST['commission_field']))) {
		$commission = sanitize_text_field($_POST['commission_field']);
		so_update_story_meta_after_adding_commission($post_id, $commission);
	}

	if ((isset($_POST['acf']['field_663cf35777ff1']) && !empty($_POST['acf']['field_663cf35777ff1']))) {
		$map_array_acf = stripslashes($_POST['acf']['field_663cf35777ff1']);
		$map_array = json_decode($map_array_acf, true);

		if (pol_place_already_exists($map_array['address'])) return;

		if (isset($_POST['acf']['field_663cf35777ff1'])) {
			unset($_POST['acf']['field_62e039dfaecbb']);
		}

		pol_create_new_place_post_type($map_array, $post_id);
	}
}
add_action('save_post_story', 'so_save_commission_meta');



function so_update_story_meta_after_adding_commission($post_id, $commission)
{
	global $wpdb;
	$table_name = $wpdb->prefix . 'commission';

	//add the commission to meta
	update_post_meta($post_id, 'commission_used', $commission);

	//add payment meta to story
	update_post_meta($post_id, '_payment_status', 0);


	//add RAE to meta
	$commission_rae = $wpdb->get_var("SELECT org_rae FROM $table_name WHERE code = '" . $commission . "'");
	update_post_meta($post_id, 'claimed_by', $commission_rae);

	//update the commission status
	$update_sql = $wpdb->get_results("UPDATE $table_name SET status = 2, last_transfer = CURRENT_TIMESTAMP WHERE code = '" . $commission . "'");

	//udpate commission history
	$post_author_id = get_post_field('post_author', $post_id);
	pol_update_commission_action($commission, 'sc', $post_author_id, '', $post_id);
}


function date_commentaire()
{

	$comments = get_comments(array('post_id' => get_the_ID()));

	if (doing_action('wp_ajax_get-comments'))
		foreach ($comments as $comment) :
			$a = $comment->comment_ID;
			$b = get_comment_ID();
			$date_comment = get_comment_date();
			$time_comment = get_comment_time();
			$date = new DateTime($date_comment);   // Create a DateTime object
			$formatted_date = $date->format('j F Y');

			$time_24hr_format = date("H:i", strtotime($time_comment));

			if ($a == $b)
				echo '<p class="commentaire-date">' . $formatted_date . ', ' . $time_24hr_format . '</p>';
		endforeach;

	// return $comment_author_url; 
}
add_filter('get_comment_author_url', 'date_commentaire');

add_action('admin_footer', function () {
	?>
	<style>
		.wp-admin .column-author a {
			display: none;
		}

		.wp-admin tr.comment:hover .row-actions * {
			display: none;
		}
	</style>
<?php
});

add_action('wp_ajax_get_commission_details', 'get_commission_details');

function get_commission_details()
{
	// ob_start();
	global $wpdb;

	// Get the commission ID from the AJAX request
	$commission_id = $_POST['commission_id'] ? $_POST['commission_id'] : '';
	$action_id = $_POST['action_type'];
	$commission_table_name = $wpdb->prefix . 'commission';
	$commission = [];
	if (!empty($commission_id)) {
		// Query the wp_commission table to get details of the commission
		$commission = $wpdb->get_row(
			$wpdb->prepare(
				"SELECT * FROM $commission_table_name WHERE id = %d",
				$commission_id
			),
			ARRAY_A
		);
	}

	$post_id = isset($_POST['post_id']) ? $_POST['post_id'] : '';
	$msg = '';
	if ($post_id != '') {
		$post_title = get_the_title($post_id);
		$author_id = get_post_field('post_author', $post_id);
		$edit_profile_link = get_edit_user_link($author_id);
		$post_status = get_post_status($post_id);
		$author_display_name = get_the_author_meta('display_name', $author_id);

		if (!empty($post_id)) {
			$msg = '
			<span>There is a story <a href="' . esc_url(get_edit_post_link($post_id)) . '">
				<strong>' . esc_html($post_title) . '</strong>
				</a> by <a href="' . esc_url($edit_profile_link) . '">
				<strong>' . esc_html($author_display_name) . '</strong>
				</a> with status <strong>' . esc_html($post_status) . '</strong> using this commission.
			</span>';
		}
	}

	wp_send_json_success([$commission['org_rae'], $commission['current_owner'], $msg]);
	wp_die();
}


add_action('wp_ajax_delete_commission_details', 'delete_commission_details');
function delete_commission_details()
{
	global $wpdb;
	$commission_id = $_POST['commission_id'] ? $_POST['commission_id'] : '';
	$post_id = $_POST['post_id'] ? $_POST['post_id'] : '';
	$commission_table_name = $wpdb->prefix . 'commission';
	$postmeta_table_name = $wpdb->prefix . 'postmeta';
	$wpdb->delete($commission_table_name, array('id' => $commission_id));
	if (!empty($post_id) && get_post($post_id)) {
		$wpdb->delete($postmeta_table_name, array('post_id' => $post_id));
		wp_delete_post($post_id, true);
	}
	wp_send_json_success(array('message' => 'Commission deleted'));
	wp_die();
}

function generate_unique_alphanumeric_string()
{
	global $wpdb;
	$table_name = $wpdb->prefix . 'commission'; // Table name (replace 'commission' with your actual table name)

	do {
		// Generate a random alphanumeric string of 12 characters
		$random_string = substr(str_shuffle('0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ'), 0, 12);

		// Query to check if the string exists in the table
		$exists = $wpdb->get_var($wpdb->prepare(
			"SELECT COUNT(*) FROM $table_name WHERE your_column_name = %s",
			$random_string
		));
	} while ($exists > 0); // Keep generating new strings until a unique one is found

	return $random_string;
}

function get_meta_on_story_status_change($new_status, $old_status, $post)
{
	global $wpdb;
	// Check if the post type is 'story'
	if ('story' === $post->post_type && $new_status == 'pending') {
		// Retrieve the post meta
		$meta_value = get_post_meta($post->ID, 'commission_used', true);
		$author_id = get_post_field('post_author', $post->ID);
		$rae_approved = get_user_meta($author_id, 'rae_approved', true);
		// Update the commission table
		$table_name = $wpdb->prefix . 'commission'; // Assuming the table name is 'wp_commission'


		if ($rae_approved == 1 && !empty($meta_value)) {
			$updated = $wpdb->update(
				$table_name,
				array('status' => 0), // Data to update
				array('code' => $meta_value),         // WHERE clause
				array('%s'),                    // Data format (status is a string)
				array('%s')                     // WHERE format (code is a string)
			);
			if ($updated) {
				delete_post_meta($post->ID, 'commission_used');
			}
		} else {
			$updated = $wpdb->update(
				$table_name,
				array('status' => 1), // Data to update
				array('code' => $meta_value),         // WHERE clause
				array('%s'),                    // Data format (status is a string)
				array('%s')                     // WHERE format (code is a string)
			);
			if ($updated) {
				delete_post_meta($post->ID, 'commission_used');
			}
		}
	}
}
add_action('transition_post_status', 'get_meta_on_story_status_change', 10, 3);

add_action('wp_trash_post', 'get_custom_post_meta_on_trash', 10, 1);

function get_custom_post_meta_on_trash($post_id)
{
	global $wpdb;
	$post_type = get_post_type($post_id);
	$author_id = get_post_field('post_author', $post_id);
	$meta_value = get_post_meta($post_id, 'commission_used', true);
	$table_name = $wpdb->prefix . 'commission'; // Assuming the table name is 'wp_commission'
	$rae_approved = get_user_meta($author_id, 'rae_approved', true);
	if ($post_type === 'story') {

		if ($rae_approved == 1 && !empty($meta_value)) {
			$updated = $wpdb->update(
				$table_name,
				array('status' => 0), // Data to update
				array('code' => $meta_value),         // WHERE clause
				array('%s'),                    // Data format (status is a string)
				array('%s')                     // WHERE format (code is a string)
			);
			if ($updated) {
				delete_post_meta($post->ID, 'commission_used');
			}
		} else {
			$updated = $wpdb->update(
				$table_name,
				array('status' => 1), // Data to update
				array('code' => $meta_value),         // WHERE clause
				array('%s'),                    // Data format (status is a string)
				array('%s')                     // WHERE format (code is a string)
			);
			if ($updated) {
				delete_post_meta($post->ID, 'commission_used');
			}
		}
	}
}
