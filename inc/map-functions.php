<?php

/**
 * Map functions.
 *
 * @package GOAT PoL
 */

/**
 * Returns the Google API key.
 */
function pol_google_api_key()
{
	return get_field('google_maps_api_key', 'option');
}


/**
 * Updates ACF with the Google API key.
 */
function pol_update_acf_google_api()
{
	$google_api_key = pol_google_api_key();
	if ($google_api_key) {
		acf_update_setting('google_api_key', $google_api_key);
	}
}
add_action('acf/init', 'pol_update_acf_google_api');


/**
 * Filters the Google API key for use in admin.
 */
function pol_filter_google_api_key($api)
{
	$api['key'] = pol_google_api_key();
	return $api;
}
add_filter('acf/fields/google_map/api', 'pol_filter_google_api_key');


/**
 * Add custom query vars.
 */
function pol_custom_query_vars($qvars)
{
	$qvars[] = 'place_id';
	$qvars[] = 'new_place';
	return $qvars;
}
add_filter('query_vars', 'pol_custom_query_vars');


/**
 * Sets the custom map style.
 */
add_filter('acf/prepare_field/name=google_map_initial_position', function ($field) {

	$map_style = get_field('google_map_style', 'option');

	if ($map_style) {
		$field['acfe_google_map_type'] = $map_style;
	}

	return $field;
});




/**
 * If the post type is 'place', and the post is being saved, then update the post meta with the value
 * of the ACF field.
 * @param post_id - The ID of the post being saved.
 * @param post - The post object.
 * @param update - Whether this is an existing post being updated or not.
 * @returns The value of the field.
 */
add_action('save_post', 'pol_set_post_where_does', 999, 3);
function pol_set_post_where_does($post_id, $post, $update)
{
	// Only set for post_type = post!
	if ('place' !== $post->post_type) {
		return;
	}

	if (!is_admin()) {
		return;
	}
	// Get the default term using the slug, its more portable!
	update_post_meta($post_id, 'where_does', esc_html($_POST['acf']['field_623afede33e68']));
}



/**
 * Actions to run when creating a new story.
 */
function pol_new_story_visibility($post, $form, $args)
{

	$story_args = array();
	$redirect   = false;

	$story_writer = get_field('story_type_labels', $post);
	$story_type_label = get_field('story_type_labels', $post);

	if ($story_type_label) {
		$story_type = get_term_by('slug', $story_type_label, 'story_type');

		if ($story_type) {

			// Save the story type as a taxonomy term.
			$story_args['tax_input'] = array(
				'story_type'  => array($story_type->term_id)
			);

			if ('short-story' == $story_type->slug) {

				// Update the post status if it's a short story.
				$story_args['post_status'] = 'draft';

				// Set the url to redirect.
				$redirect = get_permalink($post->ID);

				// Update it's place.
				$story_place = get_field('story_place', $post);

				if ($story_place) {
					pol_update_place_data($story_place);
				}
			}
		}
	}

	if ($story_args) {

		// Add the ID.
		$story_args['ID'] = $post->ID;

		// Update it.
		wp_update_post($story_args);
	}
}
add_action('af/form/editing/post_created', 'pol_new_story_visibility', 10, 3);


/**
 * Filter which emails should be sent upon story submission.
 */
add_filter('af/form/email/recipient', function ($recipient, $email, $form, $fields) {

	$story_id = isset(AF()->submission['post']) ? AF()->submission['post'] : false;

	if (!$story_id || 'story' !== get_post_type($story_id)) {
		return $recipient;
	}

	if ('publish' == get_post_status($story_id)) {
		if ('user_pending' == $email['name']) {
			return false;
		}
	} else {
		if ('user_published' == $email['name']) {
			return false;
		}
	}

	return $recipient;
}, 10, 4);


/**
 * Handles various functions on submit.
 */
add_action('af/form/submission', function ($form, $field, $args) {

	if (isset(AF()->submission['post']) && AF()->submission['post']) {

		$post_id = AF()->submission['post'];

		$post_status  = get_post_status($post_id);
		$post_type    = get_post_type($post_id);
		$redirect_url = '';

		if ('drafts' == $post_type) {

			// FEATURED IMAGE
			$featured_image = get_field('story_featured_image', $post_id);

			if ($featured_image) {
				set_post_thumbnail($post_id, $featured_image);
			}

			// AUTHOR
			$writer_name    = get_field('story_nom_de_plume', $post_id);
			$writer_email   = get_field('story_email_address', $post_id);

			$user_id = '';

			$user = get_user_by('email', $writer_email);

			if ($user) {
				$user_id = $user->ID;
				$add_name = true;
				$previous_names = get_user_meta($user_id, 'nom_de_plume', false);

				// Make sure we don't add a duplicate.
				if ($previous_names) {
					foreach ($previous_names as $name) {
						if ($writer_name == $name) {
							$add_name = false;
						}
					}
				}

				// Add the nom de plume.
				if ($add_name) {
					add_user_meta($user_id, 'nom_de_plume', $writer_name, false);
				}

				// Add the user.
			} else {

				$new_userdata = array(
					'user_login'    => $writer_email,
					'user_email'    => $writer_email,
					'display_name'  => $writer_email,
					'role'          => 'author',
					'user_pass'     =>  NULL,
					'meta_input'    => array(
						'nom_de_plume' => $writer_name,
					)
				);

				$user_id = wp_insert_user($new_userdata);
			}

			// Update the story with the user ID.
			if ($user_id) {
				wp_update_post(array(
					'ID'          => $post_id,
					'post_author' => $user_id,
				));
			}
		}

		if ('publish' == $post_status) {

			// Redirect to story form.
			if (isset($args['redirect']) && $args['redirect'] && isset($args['attach_id']) && $args['attach_id']) {

				// Append the post ID to the URL.
				$redirect_url = $args['redirect'] . '?place_id=' . $post_id;

				// Add a special query var for new places so fields aren't repeated.
				if ($form['title'] === 'Add Place') {
					$redirect_url .= '&new_place=true';
					
				}

				// Published stories.
			} elseif ('story' == $post_type) {

				// Append the post ID to the URL.
				$redirect_url = get_permalink($post_id);
			}
		}
	}

	if ($redirect_url) {

		// Hi-jack the redirect.
		wp_redirect($redirect_url);
		exit;
	}
}, 20, 3);


/**
 * Saves a fallback marker location for places without a physical location.
 */
// add_action( 'af/form/editing/post_created', function ( $post, $form, $args ) {

// 	if( 'place' !== get_post_type( $post ) ) {
// 		return;
// 	}

// 	$place_physical_location = get_field( 'where_does',$post->ID);

// 	if( $place_physical_location == 'about_internet' ) {

// 		// Randomize the marker position.
// 		// $randLat = array( '73.2491942440695', '75.25053828430262', '75.58247454129716', '74.08926266934525', '75.38420039574834', '73.03877414502469', '77.79339258188217', '78.37401064746828', '77.414254284635', '76.46119424316281', '72.67102872297184', '72.99472137318647', '75.0597573613352', '74.22688877992472', '74.0224630667522', '81.36947723641337', '82.15794936718214', '83.61166909354854', '79.6128286167468', '83.82324512292166', '84.19909328880178', '72.9615250741092' );
// 		// $randLng = array( '32.07122528887685', '47.45208452705595', '10.801694228080626', '-8.006899354606942', '39.98138146851181', '-19.14361963554643', '54.70306129986926', '83.35540504126905', '-129.60357842754846', '175.7284529209631', '-16.99521357113673', '-60.82544783745442', '-75.23951037498247', '-134.48410337303596', '-157.18398209256702', '166.38535384688745', '127.14219013347616', '162.509703998814', '40.869079564861586', '19.60179828876451', '-55.378790186402945', '-163.6020918591303' );

// 		// $lat = $randLat[rand(1, count($randLat))];
// 		// $lng = $randLng[rand(1, count($randLng))];

// 		// $fallback_location = array(
// 		// 	'place_lat'	=> $lat,
// 		// 	'place_lng'	=> $lng,
// 		// 	'place_map'	=> array(
// 		// 		'address'		=> '',
// 		// 		'lat'				=> $lat,
// 		// 		'lng'				=> $lng,
// 		// 		'zoom'			=> '10'
// 		// 	)
// 		// );

// 		// update_field( 'place_location', $fallback_location, $post->ID );

// 	}

// }, 10, 3 );


/**
 * Attaches the newly created post ID to the form redirect URL, if enabled and available.
 */
function pol_attach_place_id_on_submit($form, $field, $args)
{

	if (isset($args['redirect']) && $args['redirect']) {

		$post_id = AF()->submission['post'];

		// Return if the post wasn't created.
		if (!$post_id) {
			return;
		}

		$post_status = get_post_status($post_id);

		if ('post' == $args['redirect'] && 'publish' == $post_status) {

			// Append the post ID to the URL.
			$redirect_url = get_permalink($post_id);
		} elseif (isset($args['attach_id']) && $args['attach_id']) {

			// Append the post ID to the URL.
			$redirect_url = $args['redirect'] . '?place_id=' . $post_id;

			// Add a special query var for new places so fields aren't repeated.
			if ($form['title'] === 'Add Place') {
				$redirect_url .= '&new_place=true';
				// $redirect_url .= '&commission='.$_POST['popup-commission'];
			}
		}

		if ($redirect_url) {

			// Hi-jack the redirect.
			wp_redirect($redirect_url);
			exit;
		}
	}
}
add_action('af/form/submission', 'pol_attach_place_id_on_submit', 20, 3);


/**
 * Sets fields with `readonly` class to readonly status.
 */
add_filter('acf/prepare_field', function ($field) {

	if (str_contains($field['wrapper']['class'], 'readonly') && $field['value']) {
		$field['readonly'] = true;
	}

	return $field;
});


/**
 * Filters field attributes in admin area.
 */
add_filter('acf/prepare_field', function ($field) {

	if (is_admin()) {

		// Birdirectional story place.
		if ('story_place' == $field['_name']) {
			$field['wrapper']['class'] = str_replace('acf-hidden', '', $field['wrapper']['class']);
		}
		if ('place_physical_location' == $field['_name']) {
			$field['wrapper']['class'] = 'acf-hidden';
		}
		// Convert to multi-select
		$multi_select_fields = array(
			'place_type',
			'place_access',
			'place_languages',
			'place_attributes'
		);

		if (in_array($field['_name'], $multi_select_fields)) {
			$field['instructions'] 	= '';
			$field['required'] 			= false;
			$field['type'] 					= 'select';
			$field['multiple'] 	 		= true;
			$field['ui'] 				 		= true;
			$field['ajax'] 			 		= false;
			$field['allow_null'] 		= true;
		}

		// Writer's Email.
		if ('story_email_address' == $field['_name']) {
			$field['required'] = false;
		}

		// Place type.
		if ('place_type' == $field['_name']) {
			$field['label'] = esc_html('Place Type');
		}

		// Place acessibility.
		if ('place_access' == $field['_name']) {
			$field['label'] = esc_html('Place Access');
		}

		// Place languages.
		if ('place_languages' == $field['_name']) {
			$field['label'] 				= esc_html('Languages');
			$field['instructions']	= '';
			$field['required'] 			= false;
		}

		// Place attributes.
		if ('place_attributes' == $field['_name']) {
			$field['label'] = esc_html('This place is helpful if...');
		}

		// Place physical location.
		if ('place_physical_location' == $field['_name']) {
			$field['label'] = esc_html('Physical location?');
		}

		// Place has phone number.
		if ('place_has_phone_number' == $field['_name']) {
			$field['label'] = esc_html('Phone Number?');
		}

		// Place has website.
		if ('place_has_website' == $field['_name']) {
			$field['label'] = esc_html('Website?');
		}

		// Place location.
		// if( 'place_location' == $field['_name'] ) {
		// 	$field['conditional_logic'] = null;
		// }

		// Google Map fields.
		if ('google_map' == $field['type']) {
			$field['height'] 				= 200;
			$field['instructions'] 	= '';
			$field['required'] 			= false;
		}
	}

	return $field;
});


/**
 * Filters form fields before they are rendered to hide duplicates.
 */
add_filter('af/field/before_render', function ($field, $form, $args) {

	if ('story_featured' == $field['_name']) {
		$field['wrapper']['class'] .= ' acf-hidden';
	}

	if (isset($args['new_place']) && $args['new_place']) {

		$hide_fields = array(
			'place_type',
			'place_access',
			'place_languages',
			'place_attributes',
		);

		// Add the acf-hidden class to each field.
		if (in_array($field['_name'], $hide_fields)) {
			$field['wrapper']['class'] .= ' acf-hidden';
		}
	} else {

		$add_story_page = get_page_by_path('add-story');
		$place_id = get_query_var('place_id');

		if ($add_story_page->ID == get_the_ID() && isset($place_id) && $place_id) {
			if ('story_place_name' == $field['_name']) {
				$field['instructions'] = '<span class="wrong-location"><span class="has-sm-font-size">Not the right location? <a href="#tellusyourstory">Click here</a>.</span>';
			}
		}
	}

	return $field;
}, 10, 3);



/**
 * Updates a place's data on save and/or when one of its' stories is saved.
 */
/* Running the function pol_update_place_data() when a post is saved. */
add_action('acf/save_post', function ($post_id) {
	if (!is_admin()) return;

	$post_type = get_post_type($post_id);

	// Only run for places and posts.
	if ('place' == $post_type) {

		pol_update_place_map($post_id);
	} elseif ('story' == $post_type) {

		// Get newly saved values.
		$values = get_fields($post_id);

		if (isset($values['story_place']) && $values['story_place']) {
			//pol_update_place_data( $values['story_place'] );
		}
	}
});


/**
 * If the place has a location, update the post title to the address and update the lat/lng meta
 * fields.
 * @param [place_id] - The ID of the place you want to update.
 */
function pol_update_place_map($place_id = '')
{

	if (!$place_id) {
		return;
	}

	// Get all attached story IDs.
	$place_stories = get_field('place_stories', $place_id);

	$where_does  = get_field('where_does', $place_id);
	update_post_meta($place_id, 'where_does', $where_does);

	if ($where_does == 'geo_loc') {
		$location_details = get_field('place_location', $place_id);

		if (!empty($location_details['address'])) {
			//wp_update_post( ['ID'=> $place_id,'post_title'   => $location_details['address']] );
			update_post_meta($place_id, 'where_does', 'geo_loc');
			update_post_meta($place_id, 'place_location_place_lat', $location_details['lat']);
			update_post_meta($place_id, 'place_location_place_lng',  $location_details['lng']);
		}
	}
}


/**
 * Combines all of the data attributes from a place's stories and saves them.
 */
function pol_update_place_data($place_id = '')
{

	if (!$place_id) {
		return;
	}

	// Get all attached story IDs.
	$place_stories = get_field('place_stories', $place_id);


	$location_details = get_field('place_location', $place_id);

	if (!empty($location_details['address'])) {
		wp_update_post(['ID' => $place_id, 'post_title'   => $location_details['address']]);
		update_post_meta($place_id, 'where_does', 'geo_loc');
		update_post_meta($place_id, 'place_location_place_lat', $location_details['lat']);
		update_post_meta($place_id, 'place_location_place_lng',  $location_details['lng']);
	}


	// Get all story/place fields.
	$story_place_fields = acf_get_fields('group_6218d70ee54bf');

	if (!$story_place_fields) {
		return;
	}

	// Build an array of each shared key name.
	$field_names = array();

	foreach ($story_place_fields as $field) {
		$field_names[] = $field['name'];
	}

	// Compare the keys for each field value.
	if ($field_names) {

		// Remove the existing values so only current stories will count.
		foreach ($field_names as $key) {
			delete_field($key, $place_id);

			// Then loop over for each story and merge their values.
			if ($place_stories) {
				foreach ($place_stories as $story_id) {
					$story_value = get_field($key, $story_id) ?: array();
					$place_value = get_field($key, $place_id) ?: array();

					// If they aren't the same, update the place with the new values.
					if ($story_value !== $place_value) {
						$new_value = array_unique(array_merge($story_value, $place_value));
						update_field($key, $new_value, $place_id);
					}
				}

				// Get the new values.
				$new_key_values = get_field($key, $place_id);

				if ($new_key_values) {

					$key_values = array();

					// Find out how many stories have that value.
					foreach ($new_key_values as $value) {
						$key_val_count = 0;

						foreach ($place_stories as $story_id) {
							$story_values = get_field($key, $story_id) ?: array();

							// Increase the vote.
							if (in_array($value, $story_values)) {
								$key_val_count++;
							}
						}

						$key_values[$value] = $key_val_count;
					}

					// Sort by value.
					arsort($key_values);

					// Format the array.
					$ordered_values = array_keys($key_values);

					// Finally update the field in new order.
					update_field($key, $ordered_values, $place_id);
				}
			}
		}
	}
}


/**
 * Returns an array of all published places.
 */
function pol_get_places()
{

	$place_args = array(
		'post_type'		=> 'place',
		'post_status'	=> 'publish',
		'numberposts'	=> -1,
		//'meta_key'		=> 'place_stories'
	);

	$places = get_posts($place_args);

	if ($places) {
		return $places;
	}
}


/**
 * It takes a place ID and returns an array of story IDs or story objects.
 *
 * @param place_id The ID of the place you want to get the stories for.
 * @param return_ids If true, the function will return an array of story IDs. If false, it will return
 * an array of story objects.
 *
 * @return An array of post objects.
 */
function pol_get_place_stories($place_id = '', $return_ids = true)
{

	if (!$place_id) {
		return;
	}

	$place_stories = get_field('place_stories', $place_id);

	if (is_array($place_stories)) {

		// Return the ids by default
		if ($return_ids) {
			return $place_stories;
		}

		$story_obj = array();

		// Or get the story objects.
		foreach ($place_stories as $story_id) {
			$story = get_post($story_id);

			if (!empty($story) && $story->post_status == 'publish') {
				$story_obj[] = $story;
			}
		}

		return $story_obj;
	}
}


/**
 * If any of the stories have been published, show it.
 *
 * @return An array of places.
 */

function pol_get_map_places()
{

	$places =	pol_get_places();

	if (!$places) {
		return;
	}

	$map_places = array();

	// Only add places to map that have published stories.
	foreach ($places as $place) {

		$show_place 		= false;
		$place_stories 	= pol_get_place_stories($place->ID, false);


		if (!empty($place_stories)) {
			foreach ($place_stories as $story) {
				if ('publish' == $story->post_status) {
					$show_place = true;
				}
			}
		}

		if ($show_place) {
			$map_places[] = $place;
		}
	}

	return $map_places;
}


/**
 * Takes a value for a given field and returns its' label.
 */
function pol_convert_to_labels($field_group, $values, $post_id = '')
{

	$field = get_field_object($field_group, $post_id);

	if (!isset($field['choices'])) {
		//$ord = array_map('ucfirst',  $values);
		return array_map('ucfirst',  $values);
	}

	$labels = array();

	if (is_array($values)) {
		foreach ($values as $val) {
			$labels[] = $field['choices'][$val];
		}
	} else {
		return $field['choices'][$values];
	}

	return $labels;
}


/**
 * Returns an array of data fields for a given place ID.
 */
function pol_get_place_data($place_id = '')
{

	if (!$place_id) {
		return;
	}



	$place_data = array(
		'ID'		=> $place_id,
		'name' 		=> get_the_title($place_id),
		'url'		=> get_permalink($place_id),
	);

	// Place type
	$place_type = get_post_meta($place_id, 'place_type', true); //get_field( 'place_type', $place_id );
	if ($place_type) {
		$place_type_labels = pol_convert_to_labels('place_type', $place_type, $place_id);
		$place_data['place_type'] = $place_type_labels;
	}

	// Place access
	$place_access =  get_post_meta($place_id, 'place_access', true); //get_field( 'place_access', $place_id );
	if ($place_access) {
		$place_access_labels = pol_convert_to_labels('place_access', $place_access, $place_id);
		$place_data['place_access'] = $place_access_labels;
	}

	// Place languages
	$place_languages = get_post_meta($place_id, 'place_languages', true); //get_field( 'place_languages', $place_id );
	if ($place_languages) {
		$place_languages_labels = pol_convert_to_labels('place_languages', $place_languages, $place_id);
		$place_data['place_languages'] = $place_languages_labels;
	}

	// Place attributes
	$place_attributes = get_post_meta($place_id, 'place_attributes', true); // get_field( 'place_attributes', $place_id );
	if ($place_attributes) {
		$place_attributes_labels = pol_convert_to_labels('place_attributes', $place_attributes, $place_id);
		$place_data['place_attributes'] = $place_attributes_labels;
	}

	// Place physical location.
	//$place_data['place_physical_location'] = get_field( 'place_physical_location', $place_id ) == 1 ? true : false;

	// Place location.
	//$place_data['place_location'] = get_field( 'place_location', $place_id );
	$place_data['place_location'] = get_post_meta($place_id, 'place_location', true);

	// Place phone number.
	$place_has_phone_number = get_field('place_has_phone_number', $place_id) == 1 ? true : false;
	$place_data['place_has_phone_number'] = $place_has_phone_number;

	$place_phone = get_field('place_phone', $place_id);
	if ($place_has_phone_number && $place_phone) {
		$place_data['place_phone'] = $place_phone;
	}

	// Place website.
	$place_has_website = get_field('place_has_website', $place_id) == 1 ? true : false;
	$place_data['place_has_website'] = $place_has_website;

	$place_website = get_field('place_website', $place_id);
	if ($place_has_website && $place_website) {
		$place_data['place_website'] = $place_website;
	}

	// Place stories.
	$place_stories = get_field('place_stories', $place_id);
	if (!empty($place_stories)) {
		$stories = array(
			'short-story' 		 => array(),
			'long-story' 			 => array(),
			'long-story-short' => array(),
		);

		$featured = false;

		foreach ($place_stories as $i => $story_id) {
			if (get_post_status($story_id) == 'publish' && get_post_type($story_id) == 'story') {
				$story       = get_post($story_id);
				$story_type  = get_field('story_type', $story_id);
				$is_featured = get_field('story_featured', $story_id) == 1 ? true : false;

				if ($is_featured && is_array($is_featured)) {
					array_unshift($stories[$story_type->slug], $story);
				} else {
					$stories[$story_type->slug][] = $story;
				}
			}
		}

		$place_data['place_stories'] = $stories;
	}

	return $place_data;
}


//Returns Array for places stories for marker pop up on popup-marker.php
function pol_get_stories_places($place)
{

	if (!$place) return;
	$place_stories = get_post_meta($place, 'place_stories', true);

	$place_stories_array =  $place_stories;

	return array_unique($place_stories_array);
}



/**
 * It gets all the places from the database and returns them as an array of objects.
 *
 * @return An array of objects.
 */
function pol_get_places_markers()
{


	$map_places =	pol_get_map_places();

	if (!$map_places) {
		return;
	}

	$map_markers = array();

	foreach ($map_places as $p) {

		$place = pol_get_place_data($p->ID);

		if ($place) {
			$map_markers[] = $place;
		}
	}

	return $map_markers;
}


/**
 * Returns the map marker color for a given place, based on its' accessibility.
 */
function pol_get_place_marker_color2($place_id)
{

	if (!$place_id) {
		return;
	}

	$place_access = get_field('place_access', $place_id);
	$place_name = get_field('where_does', $place_id);

	if ($place_name == 'about_book') {

		$marker_color = 'aboutbook';
	} elseif ($place_name = 'about_internet') {
		$marker_color = 'aboutinternet';
	} else {
		if (!$place_access) {
			return 'grey';
		}

		$access_count = count($place_access);
		$marker_color = 'green';

		if ($access_count < 3) {
			$marker_color = 'yellow';
		}

		if (in_array('none', $place_access)) {
			$marker_color = 'red';
		}
	}
	return $marker_color;
}
function pol_get_marker_url($place_id)
{

	if (!$place_id) {
		return;
	}

	$default_marker			=  get_field('default_marker', 'option');
	$access_none_marker 	=  get_field('place_acess_none', 'option');
	$place_count_3 			=  get_field('place_count_3', 'option');
	$about_internet_marker 	=  get_field('about_internet_marker', 'option');
	$about_books_marker 	=  get_field('about_books_marker', 'option');
	$final_marker_url       =  $default_marker;

	$place_access 	= get_field('place_access', $place_id);
	$place_name 	= get_field('where_does', $place_id);
	$access_count 	= is_array($place_access) ? count($place_access) : false;

	if ($access_count && $access_count < 3) {
		$final_marker_url = $place_count_3;
	}

	if ($access_count && in_array('none', $place_access)) {
		$final_marker_url = $access_none_marker;
	}

	if ($place_name == 'about_books') {
		$final_marker_url =  $about_books_marker;
	}

	if ($place_name == 'about_internet') {
		$final_marker_url =  $about_internet_marker;
	}


	return $final_marker_url;
}

/**
 * Returns the map marker color for a given place, based on its' accessibility.
 */
function pol_get_place_marker_color($place_id)
{

	if (!$place_id) {
		return;
	}

	$place_access = get_field('place_access', $place_id);

	if (!$place_access) {
		return 'grey';
	}

	$access_count = count($place_access);
	$marker_color = 'green';

	if ($access_count < 3) {
		$marker_color = 'yellow';
	}

	if (in_array('none', $place_access)) {
		$marker_color = 'red';
	}

	return $marker_color;
}


//form_6258fbdf1988b


//Remove story_place_name from add story page

function pol_before_story_fields($form, $args)
{

	$place_id = get_query_var('place_id');
	$place_loc_type 	=  get_post_meta($place_id, 'where_does', true);

	if ($place_loc_type != 'geo_loc' && !empty($place_id)) {
?>
		<style>
			[data-name="story_place_name"] {
				display: none;
			}
		</style>
	<?php
	}
}
add_action('af/form/before_fields/key=form_624695227a534', 'pol_before_story_fields', 10, 2);


function pol_get_random_latitude_and_longitudes($location_type)
{

	//about_books //about_internet
	if ($location_type == 'about_internet') {


		//top
		$randLat = ['84.399864', '83.05960026998314', '82.8875868132583', '82.34568802529935', '83.85949538515763', '83.5511898401778', '82.88758681325841', '82.88758681325841', '82.43877414231196', '83.39130465266068', '84.00809700470194', '83.62968209003449', '82.88758681325845', '83.62968209003452', '83.93424954069654'];
		$randLng = ['111.196706', '23.807125471651958', '39.97900047165196', '54.04150047165196', '-57.05224952834804', '20.291500471651958', '55.44775047165196', '77.94775047165196', '100.44775047165196', '132.08837547165194', '154.58837547165194', '169.35400047165194', '84.97900047165196', '115.91650047165196', '130.68212547165194'];
	} elseif ($location_type == 'about_books') {

		//bottom
		$randLat = ['-81.42886875788872', '-82.32228109625488', '-82.32228109625488', '-82.13210175679825', '-81.98640185911117', '-75.43405677276884', '-74.42906232707563', '-74.04708595501117', '-74.89400468609085', '-75.43405677276881', '-74.52315429174666', '-71.44506488645372', '-71.66751189705867', '-74.52315429174665', '-78.51051276920603'];
		$randLng = ['-163.366946793973', '-173.210696793973', '-166.531009293973', '-157.038821793973', '-134.187259293973', '-97.273196793973', '-75.476321793973', '-55.437259293973', '-36.804446793973', '-2.7028842939730024', '26.828365706026997', '79.562740706027', '99.601803206027', '118.23461570602699', '116.47680320602699'];
	} else {
		$randLat = array('73.2491942440695', '75.25053828430262', '75.58247454129716', '74.08926266934525', '75.38420039574834', '73.03877414502469', '77.79339258188217', '78.37401064746828', '77.414254284635', '76.46119424316281', '72.67102872297184', '72.99472137318647', '75.0597573613352', '74.22688877992472', '74.0224630667522', '81.36947723641337', '82.15794936718214', '83.61166909354854', '79.6128286167468', '83.82324512292166', '84.19909328880178', '72.9615250741092');
		$randLng = array('32.07122528887685', '47.45208452705595', '10.801694228080626', '-8.006899354606942', '39.98138146851181', '-19.14361963554643', '54.70306129986926', '83.35540504126905', '-129.60357842754846', '175.7284529209631', '-16.99521357113673', '-60.82544783745442', '-75.23951037498247', '-134.48410337303596', '-157.18398209256702', '166.38535384688745', '127.14219013347616', '162.509703998814', '40.869079564861586', '19.60179828876451', '-55.378790186402945', '-163.6020918591303');
	}

	return array('lat' => $randLat[rand(1, count($randLat))], 'lng' => $randLng[rand(1, count($randLng))]);
}


/**
 * If the user's display name is not the same as their first and last name, then update the display
 * name to be their first and last name.
 * @param user_login - The user's login name.
 * @param user - The user object.
 */
function pol_force_pretty_displaynames($user_login, $user)
{

	$outcome = trim(get_user_meta($user->ID, 'first_name', true) . " " . get_user_meta($user->ID, 'last_name', true));
	if (!empty($outcome) && ($user->data->display_name != $outcome)) {
		wp_update_user(array('ID' => $user->ID, 'display_name' => $outcome));
	}
}
add_action('wp_login', 'pol_force_pretty_displaynames', 10, 2);

/* The above code is updating the post meta of the post that is created by the form. */
add_action('af/form/editing/post_created', function ($post, $form, $args) {

	if ($form['key'] !== 'form_624695227a534') {
		return;
	}
	$story_author_id 	=  get_post_field('post_author', $post->ID);
	$place_id 			= sanitize_text_field($_GET['place_id']);
	$story_labels 		= get_field('story_type_labels', $post->ID);
	$story_writer       = get_field('story_nom_de_plume', $post->ID);

	update_post_meta($post->ID, 'claimed_by', "0");
	// update_user_meta($story_author_id,'first_name' , $story_writer);
	if ($story_labels == 'short-story') {
		wp_update_post(array('ID' => $post->ID, 'post_status' => 'publish'));
	} else {
		wp_update_post(array('ID' => $post->ID, 'post_status' => 'draft'));
	}
	if (isset($place_id) && !is_wp_error($place_id)) {
		update_post_meta($post->ID, 'stories_place', $place_id);
		update_user_meta($story_author_id, 'current_editing_place', $place_id);
		update_post_meta($place_id, 'place_author', $story_author_id);
		// get current value
		$related_stories = get_field('place_stories', $place_id, false);
		if (!empty($related_stories)) {
			$related_stories[] = $post->ID;
		} else {
			$related_stories = array($post->ID);
		}
		update_field('place_stories', $related_stories, $place_id);

		if (isset($_GET['new_place']) && $_GET['new_place'] == true) {
			update_post_meta($place_id, 'place_author', $story_author_id);
		}
		$where_story = get_field('where_does', $place_id);
		if ($where_story == 'about_internet') {
			$about_internet_url = get_field('web_url', $post->ID);
			if (!empty($about_internet_url)) {
				update_post_meta($post->ID, 'web_url', $about_internet_url);
			}
			update_post_meta($post->ID, 'story_place_name', 'About the Internet');
			update_field('where_does', 'about_internet', $place_id);
			//wp_update_post(['ID' => $place_id , 'post_title'=> '']);

		} elseif ($where_story == 'about_books') {
			update_post_meta($post->ID, 'story_place_name', 'About a book');
			//wp_update_post(['ID' => $place_id , 'post_title'=> get_the_title($post->ID)]);
			update_field('where_does', 'about_books', $place_id);
		} elseif ($where_story == 'geo_loc') {
			update_post_meta($post->ID, 'story_place_name', get_the_title($place_id));
		}


		update_post_meta($place_id, 'place_physical_location', 1);
		$check_if_place_story = get_post_meta($place_id, 'place_stories', true);
		wp_update_post(array('ID'    =>  $place_id, 'post_status'   =>  'publish'));
	}
}, 999, 3);

/**
 * It sends an email to all users with the role of editor, with a link to claim the story.
 * Not used anywhere for now
 * @param post The post object.
 */
function pol_send_mail_editors($post)
{
	$story_author =  get_post_field('post_author', $post->ID);
	$author_data  = get_userdata($story_author);
	$author_name  = $author_data->user_nicename;
	$author_email = $author_data->user_email;
	$subject 	  = __('New Story Submitted', 'pol');
	$claim_link   = site_url() . '/wp-admin/edit.php?post_type=story&story_id=' . $post->ID . '&claim=1';
	$msg          = '<p>New story is published by ' . $author_email . ' </p><a href="' . $claim_link . '">Follow this link to claim this story</a>';
	$args 		  = array('role'    => 'editor', 'orderby' => 'user_nicename', 'order'   => 'ASC');
	$users 		  = get_users($args);
	foreach ($users as $u) {
		$editor_email = $u->user_email;
		if (pol_mail_sender($editor_email, $subject, $msg));
	}
}


/**
 * If the post type is story, then get the web_url field and if it's not empty, then add it to the
 * content.
 *
 * @param content The content of the post.
 *
 * @return The content of the post.
 */
function pol_insert_website_url($content)
{

	if (get_post_type(get_the_ID()) == 'story') {
		$place = get_field('stories_place', get_the_ID());
		$where_does = get_field('where_does', $place);

		if ($where_does == 'about_internet') {
			$web_url = get_field('web_url', get_the_ID());
			if (!empty($web_url)) {
				$content .= '<br/><br/>You can visit the website at <a href="' . $web_url . '">' . $web_url . '</a><br/>';
			}
		}
	}
	return $content;
}
add_filter('the_content', 'pol_insert_website_url');


/* Updating the user meta data after the password is reset. */
add_action('after_password_reset', function ($user, $new_pass) {

	$pwr = get_user_meta($user->ID, 'password_reset_count', true);
	$count = !$pwr ? 0 : $pwr + 1;
	update_user_meta($user->ID, 'password_reset_count', $count);
	update_user_meta($user->ID, 'verified_user', true);
}, 10, 2);

/**
 * When a user logs out, redirect them to the map page.
 */
// add_action('wp_logout', 'redirect_to_homepage_after_logout', 9999);

function redirect_to_homepage_after_logout()
{
	wp_safe_redirect(home_url('/map/'));
	exit;
}

/**
 * If the user is on the "Add Place" page, then when the user clicks on the "Submit" button, if the
 * user has selected the "Geo Location" radio button, then the user will be taken to the "Tell Us Your
 * Story" section of the page.
 *
 * @param form The form object
 * @param args
 */
function after_place_fields($form, $args)
{
	$add_place =  get_page_by_path('add-place');
	if ($add_place->ID == get_the_id()) :
	?>
		<script>
			jQuery(document).ready(function() {

				var checkedval = acf.getFields({
					name: 'where_does'
				}).shift().val();
				jQuery('.acf-radio-list').on('change', function() {
					checkedval = acf.getFields({
						name: 'where_does'
					}).shift().val();

					var wrapper = jQuery(".af-submit-button");
					if (checkedval === 'geo_loc') {

						wrapper.wrap("<a href='#tellusyourstory'></a>");

					} else {
						if (wrapper.parent().is("a")) {
							wrapper.unwrap();
						}
					}
				});
				jQuery('.acf-radio-list').trigger('change');
				jQuery('.af-submit-button').click(function(e) {
					window.onbeforeunload = null;
				});
			});
		</script>

	<?php endif; ?>

	<script>
		jQuery('[data-name="place_stories"]').hide();
	</script>
<?php
}
add_action('af/form/after_fields/key=form_624c4c5950521', 'after_place_fields', 10, 2);

/**
 * It adds a hidden field to the form with the value of the current post ID.
 *
 * @param form The form object
 * @param args An array of arguments that are passed to the form.
 */
function before_fields_finish($form, $args)
{
	$finish_sory_path       = get_page_by_path('complete-story');
	if (get_the_id() == 	$finish_sory_path->ID) {
		$story_id_enc 		= sanitize_text_field($_GET['update-sid']);
		$story_id 			= pol_encrypt_decrypt($story_id_enc, false);
		$story_place_id 	= get_post_meta($story_id, 'stories_place', true);
		echo '<input type="hidden" value="' . $story_place_id . '" name="pace_update_id">';
		echo '<input type="hidden" value="' . $story_id . '" name="story_update_id">';
	}
}
add_action('af/form/before_fields/key=form_624c4c5950521', 'before_fields_finish', 10, 2);


/**
 * It checks if the form is the edit form, and if it is, it updates the post meta for the story and
 * place.
 *
 * @param form The form object.
 * @param fields The fields that were submitted.
 * @param args An array of arguments that will be passed to the form.
 */
function pol_handle_editform_submission($form, $fields, $args)
{

	// die('========');
	$checkform = af_get_field('check_form');
	$storyIds = isset($_GET['story-edit'])  ? pol_encrypt_decrypt($_GET['story-edit'], false) : false;
	$place_id = isset($_GET['place-edit']) ? pol_encrypt_decrypt($_GET['place-edit'], false) : false;

	if (!empty($checkform) && $checkform == 'edit-form') {

		if (!empty($storyIds)) :
			//get form values
			$story_title 	= af_get_field('story_title');
			$story_type     = af_get_field('story_type');
			$story_labels   = af_get_field('story_type_labels');
			$story_image    = af_get_field('story_featured_image');
			$story     		= af_get_field('story');
			$name     		= af_get_field('story_nom_de_plume');
			$email    		= af_get_field('story_email_address');
			//update meta data
			update_post_meta($storyIds, 'story_title', $story_title);
			update_post_meta($storyIds, 'story_type_labels', $story_labels);
			update_post_meta($storyIds, 'story_featured_image', $story_image);
			update_post_meta($storyIds, 'story', $story);
			update_post_meta($storyIds, 'story_nom_de_plume', $name);
			update_post_meta($storyIds, 'story_email_address', $email);
			$af_content = array(
				'ID'           => $storyIds,
				'post_title'   => $story_title,
				'post_content' => $story,
			);

			// Update the post af content into the database
			wp_update_post($af_content);
			wp_update_post(array('ID' => $storyIds, 'post_type' => 'story'));

		endif;
		//places fields
		if (!empty($place_id)) :
			//get form values for places fields
			$map 				= af_get_field('place_location');
			$place_type  		= af_get_field('place_type');
			$place_access 		= af_get_field('place_access');
			$place_languages 	= af_get_field('place_languages');
			$place_attributes 	= af_get_field('place_attributes');
			//update meta data for places fields
			update_post_meta($place_id, 'place_access', $place_access);
			update_post_meta($place_id, 'place_type', $place_type);
			update_post_meta($place_id, 'place_languages', $place_languages);
			update_post_meta($place_id, 'place_attributes', $place_attributes);
			//update lat and lngs meta and address
			if (!empty($map) && !empty($map['address'])) {
				update_post_meta($place_id, 'place_location', $map);
				update_post_meta($place_id, 'place_location_place_lat', $map['lat']);
				update_post_meta($place_id, 'place_location_place_lng',  $map['lng']);
				wp_update_post(['ID' => $place_id, 'post_title'   => $map['address']]);
			}
		endif;
	}
}
add_action('af/form/submission', 'pol_handle_editform_submission', 10, 3);

/**
 * If the form is submitted, and the check field is set to "complete-submission", then update the post
 * meta for the place.
 *
 * @param form The form object
 * @param fields The fields that are being submitted.
 * @param args An array of arguments that will be passed to the form.
 */
add_action('af/form/submission', 'pol_handle_update_form_submission', 10, 3);
function pol_handle_update_form_submission($form, $fields, $args)
{
	$checkField = af_get_field('check_field');
	if ($checkField == 'complete-submission') {
		$author_id 		= $_GET['author-edit'];
		$story_id  		= $_GET['story-edit'];
		$author_mail 	= $_GET['authorr-mail'];
		if (isset($author_id) && isset($story_id) && isset($author_mail)) {
			$story_id_dec   = pol_encrypt_decrypt($story_id, false);
			$story_place_id = get_post_meta($story_id_dec, 'stories_place', true);
			/* Getting the value of the fields  from the Advanced Custom Fields plugin. */
			$place_type 		= af_get_field('place_type');
			$place_access 	= af_get_field('place_access');
			$place_lang 		= af_get_field('place_languages');
			$place_attr 		= af_get_field('place_attributes');
			$map 						= af_get_field('place_location');
			if (get_post_type($story_place_id)) {
				wp_update_post(array('ID' => $story_place_id, 'post_status' => 'publish'));
				update_post_meta($story_place_id, 'place_access', $place_access);
				update_post_meta($story_place_id, 'place_type', $place_type);
				update_post_meta($story_place_id, 'place_languages', $place_lang);
				update_post_meta($story_place_id, 'place_attributes', $place_attr);
				update_post_meta($story_place_id, 'place_location', $map);
				update_post_meta($story_place_id, 'place_location_place_lat', $map['lat']);
				update_post_meta($story_place_id, 'place_location_place_lng',  $map['lng']);
				wp_update_post(['ID' => $story_place_id, 'post_title'   => $map['address']]);
				wp_update_post(array('ID' => $story_id_dec, 'post_type' => 'story'));
			}
		}
	}
}


/**
 * If the longitude is greater than or equal to 160, add 4 to the latitude. Otherwise, add 5 to the
 * longitude .Unused for now because the marker arrangement functionality is done on the runtime
 * @param type The type of map to display. Can be roadmap, satellite, hybrid, or terrain.
 */
function pol_get_required_pos($type)
{
	$place_args_top = array(
		'post_type' => 'place',
		'posts_per_page' => 3,
		'post_status' => 'publish',
		'meta_query' => array(
			array(
				'key'           => 'where_does',
				'value'         => $type,
				'compare' => '=',
			)
		)
	);


	// $datas = get_posts($place_args_top);
	// $id = $datas[1]->ID;

	// if($type == 'about_internet') {

	// 	$lat      = get_post_meta($id , 'place_location_place_lat' , true);
	// 	$lng      = get_post_meta($id , 'place_location_place_lng' , true);
	// 	if(!empty($lat) && !empty($lng) || $lng == 0){
	// 		$latitude =  $lng <= -170 ? $lat+3 : $lat;
	// 		if($lng >= 170){
	// 			$longitude = '0';
	// 		}elseif($lng == '0'){
	// 			$longitude = '-10';
	// 		}elseif($lng < '0'){
	// 			$longitude = strval($lng) - '10';
	// 		}

	// 	}else{
	// 		$latitude = 80;
	// 		$longitude = 55 ;

	// 		update_post_meta($id,'need_internet_marker_update', true);
	// 	}
	// }elseif($type == 'about_books'){

	// 	$lat      = get_post_meta($id , 'place_location_place_lat' , true);
	// 	$lng      = get_post_meta($id , 'place_location_place_lng' , true);
	// 	if(!empty($lat) && !empty($lng) || $lng == 0){
	// 		$latitude =   $lng >= -175 ? $lat-1: $lat ;
	// 		if($lng >= 175){
	// 			$longitude = '0';
	// 		}elseif($lng == '0'){
	// 			$longitude = '-5';
	// 		}elseif($lng < '0'){
	// 			$longitude = strval($lng) - '5';
	// 		}
	// 	}else{
	// 		$latitude = -60;
	// 		$longitude = -65 ;
	// 		update_post_meta($id,'need_book_marker_update', true);
	// 	}

	// }

	// return array(strval($latitude), strval($longitude));

	return true;
}



/* Setting the post title to the address of the place. */

add_action('af/form/editing/post_created', function ($post, $form, $args) {

	if ('place' !== get_post_type($post)) {
		return;
	}
	$titles_loc_type = get_post_meta($post->ID, 'where_does', true);
	if ($titles_loc_type == 'about_books') {
		$pos = pol_get_required_pos('about_books');
		update_post_meta($post->ID, 'place_location_place_lat', $pos[0]);
		update_post_meta($post->ID, 'place_location_place_lng', $pos[1]);
		update_post_meta($post->ID, 'place_location_place_lng', $pos[1]);
		wp_update_post(['ID' => $post->ID, 'post_title'   => 'About Books']);
	} elseif ($titles_loc_type == 'about_internet') {
		$required = pol_get_required_pos('about_internet');
		update_post_meta($post->ID, 'place_location_place_lat', $required[0]);
		update_post_meta($post->ID, 'place_location_place_lng', $required[1]);
		wp_update_post(['ID' => $post->ID, 'post_title'   => 'About The Internet']);
	}

	$location_details = get_field('place_location', $post->ID);

	if (!empty($location_details['address'])) {
		wp_update_post(['ID' => $post->ID, 'post_title'   => $location_details['address']]);
		update_post_meta($post->ID, 'where_does', 'geo_loc');
		update_post_meta($post->ID, 'place_location_place_lat', $location_details['lat']);
		update_post_meta($post->ID, 'place_location_place_lng',  $location_details['lng']);
	}
}, 10, 3);


/**
 * If the user is logged in and the story-id is set and not empty, then get the story id, check if the
 * story exists, get the user id, get the user email, get the story title, get the writer's name, set
 * the salutation, set the message, set the subject, check if the mail has been sent, send the mail, if
 * the mail is sent, update the post meta and update the post type.
 */
// add_action('init', 'pol_send_mail_story_finished');
// function pol_send_mail_story_finished()
// {
// 	global $wpdb;

// 	if (isset($_GET['story-id']) &&  !empty($_GET['story-id'] && is_user_logged_in())) {
// 		$story_id = pol_encrypt_decrypt($_GET['story-id'], false);
// 		if (get_post_status($story_id)) {
// 			$u_id 					= get_current_user_id();
// 			$author_obj 		= get_user_by('id', $u_id);
// 			$to 						= $author_obj->user_email;
// 			$story_title 		= get_the_title($story_id);
// 			$writers_name   = get_field('story_nom_de_plume', $story_id, true);
// 			$sal 						=  !empty($writers_name) ?  'Dear ' . $writers_name : 'Dear writer';
// 			// 			$msg 						= 'Thank you for submitting your story, ' . get_the_title($story_id) . ', to The GOAT PoL. If it\'s "Ready to publish now" you should see it on our map very soon. If you selected "Work with me first and publish later" you will hear from one of our RAEs (Reader-Advisor-Editors) who will work with you and prepare the story for publication.
// 			// We work with dozens of writers on hundreds of stories. Please be patient with us. One of our Reader/Advisor/Editors (RAEs) will reply to you as soon as they have time to work on your story. If you don\'t hear back from anyone within a week, you can email us at thegoatpol@tutanota.com to inquire.';
						


// 			$commission_inuse = get_post_meta($story_id, 'commission_used', true);
// 			$current_owner = $wpdb->get_var($wpdb->prepare("SELECT current_owner from {$wpdb->prefix}commission WHERE code = '$commission_inuse' "));

// 			$rae_name = '---';
// 			$rae_email = '---';

// 			if((int)$current_owner == get_current_user_id()){

// 				$rae_that_transferred_the_commission = $wpdb->get_var($wpdb->prepare("SELECT org_rae from {$wpdb->prefix}commission WHERE code = '$commission_inuse' "));
// 				$rae_user = get_user_by('id', $rae_that_transferred_the_commission);
// 				$rae_name = $rae_user->display_name;
// 				$rae_email = $rae_user->user_email;
// 				if((int)$rae_that_transferred_the_commission != 0){
// 					update_post_meta($story_id, 'claimed_by', $rae_that_transferred_the_commission);
// 					update_post_meta($story_id, 'commission_used', $commission_inuse);
// 				}
// 			}


// 			$msg = 'Thank you for submitting your story, '.$story_title.', to The GOAT PoL. 
// 			You will hear from your Reader-Advisor-Editor (RAE), '.$rae_name.' who will work with you and prepare the story for publication. 
// 			We work with dozens of writers on hundreds of stories, so please be patient with us. If you don\'t hear back from your RAE within 
// 			ten days, send an email to '.$rae_email.' to inquire.';
			
// 			$subj = __('Story Submitted', 'pol');
// 			$confirm_mail   = get_post_meta($story_id, 'mail-sent-final', true);
// 			if ($confirm_mail != 1) $mail = pol_mail_sender($to, $subj, $msg, $sal);
// 			if ($mail) {
// 				update_post_meta($story_id, 'mail-sent-final', true);
// 				wp_update_post(array('ID' => $story_id, 'post_type' => 'story'));
// 			}
// 		}
// 	}
// }





add_action('init', 'pol_send_mail_story_finished');
function pol_send_mail_story_finished()
{
	global $wpdb;
	$submission_log = '';

	if (isset($_GET['story-id']) &&  !empty($_GET['story-id'])) {
		// var_dump('uoooooooo');
		$story_id = pol_encrypt_decrypt($_GET['story-id'], false);
		if (get_post_status($story_id)) {
			$story_author_id = (int)get_post_field('post_author', (int)$story_id);
			$u_id 					= $story_author_id;
			$author_obj 		= get_user_by('id', $u_id);
			$to 						= $author_obj->user_email;
			$story_title 		= get_the_title($story_id);
			$writers_name   = get_field('story_nom_de_plume', $story_id, true);
			$sal 						=  !empty($writers_name) ?  'Dear ' . $writers_name : 'Dear writer';
			// $msg = 'Thank you for submitting your story, ' . get_the_title($story_id) . ', to The GOAT PoL. If it\'s "Ready to publish now" you should see it on our map very soon. If you selected "Work with me first and publish later" you will hear from one of our RAEs (Reader-Advisor-Editors) who will work with you and prepare the story for publication.
			// We work with dozens of writers on hundreds of stories. Please be patient with us. One of our Reader/Advisor/Editors (RAEs) will reply to you as soon as they have time to work on your story. If you don\'t hear back from anyone within a week, you can email us at thegoatpol@tutanota.com to inquire.';
						


			$commission_inuse = get_post_meta($story_id, 'commission_used', true);
			$current_owner = $wpdb->get_var($wpdb->prepare("SELECT current_owner from {$wpdb->prefix}commission WHERE code = '$commission_inuse' "));

			$rae_name = '---';
			$rae_email = '---';

			if((int)$current_owner == (int)$story_author_id){

				$rae_that_transferred_the_commission = $wpdb->get_var($wpdb->prepare("SELECT org_rae from {$wpdb->prefix}commission WHERE code = '$commission_inuse' "));
				$rae_user = get_user_by('id', $rae_that_transferred_the_commission);
				$rae_name = $rae_user->display_name;
				$rae_email = $rae_user->user_email;
				if((int)$rae_that_transferred_the_commission != 0){
					update_post_meta($story_id, 'claimed_by', $rae_that_transferred_the_commission);
					update_post_meta($story_id, 'commission_used', $commission_inuse);
				}
			}


			$msg = 'Thank you for submitting your story, '.$story_title.', to The GOAT PoL. 
			You will hear from your Reader-Advisor-Editor (RAE), '.$rae_name.' who will work with you and prepare the story for publication. 
			We work with dozens of writers on hundreds of stories, so please be patient with us. If you don\'t hear back from your RAE within 
			ten days, send an email to '.$rae_email.' to inquire.';
			
			$subj = __('Story Submitted', 'pol');
			$confirm_mail   = get_post_meta($story_id, 'mail-sent-final', true);

			// $mail = pol_mail_sender($to, $subj, $msg, $sal);

			if ($confirm_mail != 1) {

				//continue trying to send the mail until it return true
				$mail = pol_mail_sender($to, $subj, $msg, $sal);
                if (!$mail) {
                    $mail = pol_mail_sender($to, $subj, $msg, $sal);
                }

				//only after the mail function returns true update the meta
				if ($mail) {
					$submission_log .= $story_title.'('.$story_id.') || Submission verification email('.($mail ? $to : 'failed').') || ';
					update_post_meta($story_id, 'mail-sent-final', true);
					wp_update_post(array('ID' => $story_id, 'post_type' => 'story'));
					$submission_log .= 'Post type updated(story)';
				}
				pol_story_submission_log($submission_log);
			}
		}
	}
}


/**
 * It takes a position and a first degree and returns an array of coordinates.
 * @param pos - The position of the array you want to get.
 * @param first_degree - The first degree of the coordinate.
 * @returns An array of arrays.
 */
function pol_get_coordinates($pos, $first_degree)
{

	$items = array();

	for ($i = 5; $i <= 180; $i += 10) {
		$x = array($first_degree, strval($i));
		$y = array($first_degree, '-' . $i);
		array_push($items, $x, $y);
	}

	return $items[$pos];
}


/**
 * Arrange Markers for About the internet stories on the top
 */
function pol_top_markers_arrangements()
{
	$place_args_top = array(
		'post_type' => 'place',
		'posts_per_page' => -1,
		'post_status' => 'publish',

		'meta_query' => array(
			array(
				'key'           => 'where_does',
				'value'         => 'about_internet',
				'compare' => '=',
			)
		)
	);

	$data = get_posts($place_args_top);



	$i = 1;
	$k = 0;
	$first_degree = 75;
	foreach (array_reverse($data) as $d) {
		$place_stories = get_post_meta($d->ID, 'place_stories', true);
		if (is_array($place_stories)) {
			$id = $place_stories[0];
		} else {
			$id = $place_stories;
		}

		if (get_post_type($id) == 'drafts') {
			//wp_delete_post( $id, true );
		}

		if (empty(get_the_title($id))) {
			//wp_delete_post($id , true);
		}


		if (!empty($place_stories) && get_post_status($id) == 'publish') :


			if ($k > 35) {
				$k = 0;
				$first_degree += 3;
			}
			/* Getting the coordinates of the first degree of the k-th polygon. */
			$coordinate = pol_get_coordinates($k, $first_degree);
			update_post_meta($d->ID, 'place_location_place_lat', $coordinate[0]);
			update_post_meta($d->ID, 'place_location_place_lng', $coordinate[1]);
			wp_update_post(['ID' => $d->ID, 'post_title' => esc_html('About The Internet ' . $i)]);
			$k++;
			$i += 1;

		endif;
	}
}

/**
 * Arrange About The Book Markers on the buttom
 */
function pol_bottom_markers_arrangements()
{
	$place_args_top = array(
		'post_type' => 'place',
		'posts_per_page' => -1,
		'post_status' => 'publish',
		'meta_query' => array(
			array(
				'key'           => 'where_does',
				'value'         => 'about_books',
				'compare' => '=',
			)
		)
	);

	$data = get_posts($place_args_top);
	$i = 1;
	$k = 0;
	$first_degree = -60;
	foreach (array_reverse($data) as $d) {
		$place_stories = get_post_meta($d->ID, 'place_stories', true);
		if (is_array($place_stories)) {
			$id = $place_stories[0];
		} else {
			$id = $place_stories;
		}

		// if(get_post_type($id) == 'drafts'){
		// 	wp_delete_post( $id, true );
		// }

		// if(empty(get_the_title($id))){
		// 	wp_delete_post($id , true);
		// }


		if (!empty($place_stories) && get_post_status($id) == 'publish') :


			if ($k > 35) {
				$k = 0;
				$first_degree -= 3;
			}
			$coordinate = pol_get_coordinates($k, $first_degree);
			update_post_meta($d->ID, 'place_location_place_lat', $coordinate[0]);
			update_post_meta($d->ID, 'place_location_place_lng', $coordinate[1]);
			wp_update_post(['ID' => $d->ID, 'post_title' => esc_html('About Books ' . $i)]);

			$k++;
			$i += 1;

		endif;
	}
}


/**
 * It searches only the post title, and not the post content
 * @param search - The search string.
 * @param wp_query - The query object.
 * @returns The search query.
 */
function pol_search_by_title_only($search, $wp_query)
{
	global $wpdb;
	if (empty($search))
		return $search; // skip processing – no search term in query
	$q = $wp_query->query_vars;
	$n = !empty($q['exact']) ? '' : '%';
	$search =
		$searchand = '';
	foreach ((array) $q['search_terms'] as $term) {
		$term = esc_sql(esc_sql($term));
		$search .= "{$searchand}($wpdb->posts.post_title LIKE '{$n}{$term}{$n}')";
		$searchand = ' AND ';
	}
	if (!empty($search)) {
		$search = " AND ({$search}) ";
		if (!is_user_logged_in())
			$search .= " AND ($wpdb->posts.post_password = '') ";
	}
	return $search;
}
add_filter('posts_search', 'pol_search_by_title_only', 500, 2);




/**
 * Unused for now..It takes a search term, searches for posts, and returns a list of post titles and IDs.
 */
add_action('wp_ajax_nopriv_pol_search_data_fetch', 'pol_search_data_fetch');
add_action('wp_ajax_pol_search_data_fetch', 'pol_search_data_fetch');


function pol_search_data_fetch()
{
	add_action('pre_get_posts', 'pol_show_published_posts_ajax_search');
	$search_term = $_REQUEST['search'];
	if (!isset($_REQUEST['search'])) {
		echo json_encode([]);
	}

	$suggestions = [];

	$pol_ids = [];
	$pol_tids = [];
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

	if (count($user_ids)  > 0) {
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
	$auth_args =  array(
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
	$author_args3 =  array(
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

	if (count($user_ids2)  > 0) {
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
					'perma_link' => get_the_permalink(),
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
			}
			 else {
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

	echo json_encode($suggestions);
	wp_die();
}


// function pol_search_data_fetch()
// {
// 	$search_term = $_REQUEST['search'];
// 	if (!isset($_REQUEST['search'])) {
// 		echo json_encode([]);
// 	}
// 	$suggestions = [];
// 	$query = new WP_Query([
// 		'post_type' => array('story'),
// 		's' => $search_term,
// 		'posts_per_page' => 20,
// 		'post_status' => 'publish'
// 	]);

// 	if ($query->have_posts()) {
// 		while ($query->have_posts()) : $query->the_post();


// 			if (get_post_type(get_the_ID()) == 'story') {
// 				$markerid = get_post_meta(get_the_ID(), 'stories_place', true);
// 			}
// 			// elseif(get_post_type(get_the_ID()) == 'place'){
// 			// 	$markerid = get_the_ID();
// 			// }
// 			else {

// 				$markerid = false;
// 			}

// 			// if( get_post_meta(get_the_ID(),'place_stories' , true) && is_array(get_post_meta(get_the_ID(),'place_stories' , true))) {
// 			// 	$place_story = get_post_meta(get_the_ID(),'place_stories' , true);
// 			// }else {
// 			// 	$place_story = ['10632'];
// 			// }

// 			if ($markerid && !is_wp_error($markerid)) {
// 				$suggestions[] = [
// 					'sid' => get_the_ID(),
// 					'id' => $markerid,
// 					'label' => get_the_title(),
// 					'value' => '',
// 					'pt' => get_post_type(get_the_ID()),
// 					'ps' => get_post_status(get_the_ID()),
// 					'e' => is_wp_error($markerid),

// 				];
// 			}

// 		endwhile;
// 		wp_reset_postdata();
// 	} else {
// 		$suggestions[] = [

// 			'label' => __('No results Found', 'pol'),
// 			'value' => ''
// 		];
// 	}
// 	echo json_encode($suggestions);
// 	wp_die();
// }


/**
 * It searches for posts and users, and then returns the post IDs of the posts that match the search
 * term.
 */
// function pol_search_data_fetch_alt()
// {
// 	add_action('pre_get_posts', 'pol_show_published_posts_ajax_search');
// 	$search_term = $_REQUEST['search'];
// 	if (!isset($_REQUEST['search'])) {
// 		echo json_encode([]);
// 	}

// 	$suggestions = [];

// 	$pol_ids = [];
// 	$pol_tids = [];
// 	$user_ids = [];
// 	$user_ids2 = [];
// 	$author_post_ids = [];
// 	$author_post_ids2 = [];

// 	// Create the WP_User_Query object
// 	$wp_user_query = new WP_User_Query(['search' => '*' . esc_attr($search_term) . '*']);

// 	// Get the results
// 	$authors = $wp_user_query->get_results();

// 	// Check for results
// 	if (!empty($authors)) {
// 		// loop through each author
// 		foreach ($authors as $author) {
// 			// get all the user's data
// 			$user_ids[] = $author->ID;
// 		}

// 		$user_ids = array_unique($user_ids);
// 	}

// 	if (count($user_ids)  > 0) {
// 		$author_args = array(
// 			'post_type' => array('story', 'place'),
// 			'post_status' => 'publish',
// 			'posts_per_page' => -1,
// 			'author__in' => $user_ids,
// 			'fields' => 'ids',
// 		);

// 		$author_post_ids = get_posts($author_args);
// 	}

// 	// WP_User_Query arguments
// 	$auth_args =  array(
// 		'meta_query' => array(
// 			'relation' => 'OR',
// 			array(
// 				'key' => 'first_name',
// 				'value' => '^' . $search_term,
// 				'compare' => 'REGEXP'
// 			),
// 			array(
// 				'key' => 'last_name',
// 				'value' => '^' . $search_term,
// 				'compare' => 'REGEXP'
// 			)
// 		)
// 	);

// 	// Create the WP_User_Query object
// 	$wp_user_query2 = new WP_User_Query($auth_args);

// 	// Get the results
// 	$authors2 = $wp_user_query2->get_results();


// 	// WP_User_Query arguments
// 	$author_args3 =  array(
// 		'post_type' => array('story'),
// 		'post_status' => 'publish',
// 		'posts_per_page' => -1,
// 		'meta_query' => array(
// 			'relation' => 'OR',
// 			array(
// 				'key' => 'story_nom_de_plume',
// 				'value' => $search_term,
// 				'compare' => 'REGEXP',
// 			)
// 		),
// 		'fields' => 'ids',
// 	);

// 	$author_post_ids3 = get_posts($author_args3);



// 	// Check for results
// 	if (!empty($authors2)) {
// 		// loop through each author
// 		foreach ($authors as $author2) {
// 			// get all the user's data
// 			$user_ids2[] = $author2->ID;
// 		}

// 		$user_ids2 = array_unique($user_ids2);
// 	}

// 	if (count($user_ids2)  > 0) {
// 		$author_args2 = array(
// 			'post_type' => array('story', 'place'),
// 			'post_status' => 'publish',
// 			'posts_per_page' => -1,
// 			'author__in' => $user_ids2,
// 			'fields' => 'ids',
// 		);

// 		$author_post_ids2 = get_posts($author_args2);
// 	}

// 	$pol_args = [
// 		'post_type' => array('story', 'place'),
// 		's' => $search_term,
// 		'posts_per_page' => -1,
// 		'post_status' => 'publish',
// 		'fields' => 'ids',
// 	];

// 	$pol_ids = get_posts($pol_args);

// 	$pol_args = [
// 		'post_type' => array('story'),
// 		'posts_per_page' => -1,
// 		'post_status' => 'publish',
// 		'fields' => 'ids',
// 		'meta_query' => array(
// 			'relation' => 'OR',
// 			array(
// 				'key' => 'story_place_name',
// 				'value' => $search_term,
// 				'compare' => 'LIKE',
// 			),
// 		)
// 	];

// 	$pol_ids = array_merge($pol_ids, get_posts($pol_args));

// 	$pol_args = [
// 		'post_type' => array('place'),
// 		'posts_per_page' => -1,
// 		'post_status' => 'publish',
// 		'fields' => 'ids',
// 		'meta_query' => array(
// 			'relation' => 'OR',
// 			array(
// 				'key' => 'place_type',
// 				'value' => $search_term,
// 				'compare' => 'LIKE',
// 			),
// 			array(
// 				'key' => 'place_languages',
// 				'value' => $search_term,
// 				'compare' => 'LIKE',
// 			),
// 			array(
// 				'key' => 'place_attributes',
// 				'value' => $search_term,
// 				'compare' => 'LIKE',
// 			),
// 		)
// 	];

// 	$pol_ids = array_merge($pol_ids, get_posts($pol_args));

// 	$pol_ids = array_merge($pol_ids, $author_post_ids);

// 	$pol_ids = array_merge($pol_ids, $author_post_ids2);

// 	$pol_ids = array_merge($pol_ids, $author_post_ids3);

// 	$pol_ids = array_unique($pol_ids);

// 	$result_count = count($pol_ids);
// 	$count = 0;
// 	global $post;

// 	if ($result_count > 0) {
// 		foreach ($pol_ids as $pol_id) {
// 			$post = get_post($pol_id);
// 			setup_postdata($post);

// 			//$markerid = false;
// 			$markerid = "";
// 			if (get_post_type(get_the_ID()) == 'story') {
// 				$temp_markerid = get_post_meta(get_the_ID(), 'stories_place', true);
// 				$suggestions[] = [
// 					'id' => $temp_markerid,
// 					'label' => get_the_title(),
// 					'value' => '',
// 					'post_id' => get_the_ID(),
// 					'pt' => get_post_type(get_the_ID()),
// 					'ps' => get_post_status(get_the_ID()),
// 					//'e' => is_wp_error($markerid),
// 					// 'ids' => $pol_ids,
// 					// 'place_str' => $place_stories,

// 				];
// 				$markerid = $temp_markerid;
// 				$count++;
// 			} elseif (get_post_type(get_the_ID()) == 'place') {
// 				$markerid = get_the_ID();

// 				$about_books = get_field('where_does', get_the_ID());

// 				$show_place 		= false;
// 				$place_stories 	= pol_get_place_stories(get_the_ID(), false);
// 				$counter = 0;

// 				//If any of the stories have been published, show it.
// 				if (!empty($place_stories)) {
// 					foreach ($place_stories as $story) {

// 						if ('publish' == $story->post_status) {

// 							$place_id = get_field('stories_place', $story->ID);

// 							$about_books = get_field('where_does', $story->ID);

// 							$suggestions[] = [
// 								'id' =>  $place_id,
// 								'label' => get_the_title($place_id),
// 								'value' => '',
// 								'post_id' => $place_id,
// 								'pt' => get_post_type($place_id),
// 								'ps' => get_post_status($place_id),
// 								'ids2' => $author_post_ids3,
// 								'ids3' => $author_post_ids,
// 								//'e' => is_wp_error($markerid),

// 							];
// 							$counter++;
// 							$count++;
// 						} elseif ($about_books == 'about_books' && 'publish' != $story->post_status) {
// 						} elseif ($about_books == 'about_internet' && 'publish' != $story->post_status) {
// 						}
// 					}
// 				}

// 				if ($about_books == 'about_books' && empty($place_stories)) {
// 					$markerid = "";
// 				}

// 				if ($about_books == 'about_internet' && empty($place_stories)) {
// 					$markerid = "";
// 				}

// 				if ($counter == 0) {
// 					$markerid = "";
// 				}
// 			} else {
// 				$markerid = "";
// 				$count++;
// 			}

// 			if (!empty($markerid) && $markerid != "" && !is_wp_error($markerid)) {
// 				$suggestions[] = [
// 					'id' => $markerid,
// 					'label' => get_the_title($markerid),
// 					'value' => '',
// 					'post_id' => $markerid,
// 					'pt' => get_post_type($markerid),
// 					'ps' => get_post_status($markerid),
// 					//'e' => is_wp_error($markerid),
// 					// 'ids' => $pol_ids,
// 					// 'place_str' => $place_stories,

// 				];
// 			}

// 			$count++;
// 		}

// 		wp_reset_postdata();
// 	}

// 	$suggestions = unique_multidim_array($suggestions, 'post_id');

// 	if (count($suggestions) < 1) {
// 		$suggestions[] = [
// 			'label' => __('No results Found', 'pol'),
// 			'value' => ''
// 		];
// 	}

// 	echo json_encode($suggestions);
// 	wp_die();
// }

/**
 * It takes an array of arrays, and returns a new array of arrays, with only the unique values of the
 * key you specify.
 * @param array - The array you want to filter
 * @param key - The key to search for in the array.
 * @returns An array of arrays.
 */
function unique_multidim_array($array, $key)
{
	$temp_array = array();
	$key_array = array();

	foreach ($array as $val) {
		if (!in_array($val[$key], $key_array)) {
			$key_array[] = $val[$key];
			$temp_array[] = $val;
		}
	}
	return $temp_array;
}
/**
 * If the user is logged in and is an author, then check if they have any drafts. If they do, then hide
 * the submit button and display a message. If they don't, then show the submit button and don't
 * display a message.
 * @returns An array of two values.
 */
function pol_check_users_drafts()
{
	global $wpdb;
	global $current_user;
	$hide = false;
	$messages = '';
	// Get the user object.
	$user = get_userdata($current_user->ID);

	// Get all the user roles as an array.
	$user_roles = $user->roles;

	// Check if the User is Author
	if (is_user_logged_in() && in_array('author', $user_roles, true) ) { //&&  in_array('author', $user_roles, true)
		$query = $wpdb->get_results("SELECT ID FROM $wpdb->posts WHERE  post_type = 'story' AND (post_status = 'draft' OR post_status = 'pending') AND post_author = " . $current_user->ID . " ORDER BY post_modified DESC ");
		if (!empty($query)) {
			$hide = true;
			$messages .= '<br/><div class="has-lg-font-size" style="margin-bottom:20px;">';
			$messages .= 'Dear ' . $current_user->display_name . ' , We are still working on your story, ' . get_the_title($query[0]->ID) . '.  Please wait until that story has been published before submitting more stories for us to work on. We welcome your new work after we have completed the publication of ' . get_the_title($query[0]->ID) . '. In the meantime, you can submit as many "Ready to Publish" stories as you wish. Thanks! The GOAT PoL';
			$messages .= '</div>';
		} else {
			$hide = false;
		}
	} else {
		$hide = false;
	}

	return array($hide, $messages);
}




/**
 * It checks if the user has a draft post and if they do, it disables the radio button and shows a
 * popup.
 * @param form - The form object.
 * @param args -
 */
function after_fields_story($form, $args)
{
	$check = pol_check_users_drafts();
	$hide = $check[0];
	$mesaage =  $check[1];
?>

	<style>
		/* Hiding the ACF field group from the post edit screen. */
		.acf-field-acfe-post-field {
			display: none !important;
		}
	</style>

	<?php if (!is_admin()) :
		/* Hiding the bidirectional button from the front end of the site. */
	?>
		<style>
			.pol-bidrection {
				display: none !important;
			}
		</style>
	<?php endif; ?>

	<?php
	/* Checking if it valid. If it is, it will run the script. */
	if ($hide) :
	?>
		<script>
			jQuery(document).ready(function() {
				//saugat changes
				// console.log('cpm::::1');
				jQuery('#form_624c4c5950521').hide();
				jQuery('.entry-content').append('<?php echo $mesaage; ?>');

				var disable_field = jQuery("[value=long-story-short]");
				disable_field.prop('disabled', true);
				disable_field.parent().on('click', function() {

					jQuery('#menu-popup-check-draft').toggle();
				});
				jQuery('.menu-popup-check-draft-close').click(function() {
					jQuery('#menu-popup-check-draft').hide();
				});
			});
		</script>
<?php

	endif;
}
add_action('af/form/after_fields/key=form_624c4c5950521', 'after_fields_story', 10, 2);
//form_624695227a534
