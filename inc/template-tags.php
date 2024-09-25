<?php
/**
 * Template tags.
 *
 * @package GOAT PoL
 */


/**
 * Outputs the custom logo.
 */
function pol_the_custom_logo()
{
	echo esc_html(pol_get_custom_logo());
}


/**
 * Returns the custom logo.
 */
function pol_get_custom_logo()
{

	$has_logo = false;

	// Get the logo and the logotype.
	$logo_id = get_theme_mod('custom_logo', null);
	$map_logo_id = get_theme_mod('pol_map_logo', null);
	$logotype_id = get_theme_mod('pol_logotype', null);

	if (!$logo_id) {
		return;
	}

	// Build an array containing the regular and the logotype, if set.
	$logos = array();

	if ($logo_id) {
		$logos['regular'] = $logo_id;
	}

	if ($map_logo_id) {
		$logos['map'] = $map_logo_id;
	}

	if ($logotype_id) {
		$logos['logotype'] = $logotype_id;
	}

	// The regular logo is required for output.
	if (!isset($logos['regular'])) {
		return;
	}

	// Unset duplicates.
	if (isset($logos['logotype']) && $logos['logotype'] == $logos['regular']) {
		unset($logos['logotype']);
	}

	if (isset($logos['map']) && $logos['map'] == $logos['regular']) {
		unset($logos['map']);
	}

	if (isset($logos['map']) && isset($logos['logotype']) && $logos['map'] == $logos['logotype']) {
		unset($logos['map']);
	}

	// Record our output.
	ob_start();
	// var_dump(home_url($_SERVER['REQUEST_URI']));

	if (home_url($_SERVER['REQUEST_URI']) == home_url('/list/')) {
		$header_logo_redirect_url = home_url('/map');
	} else if (home_url($_SERVER['REQUEST_URI']) == home_url('/map/')) {
		$header_logo_redirect_url = home_url('/map');
	} else {
		if (($_SERVER['HTTP_REFERER'] == home_url('/map/')) || ($_SERVER['HTTP_REFERER'] == home_url('/list/'))) {
			$header_logo_redirect_url = $_SERVER['HTTP_REFERER'];
		} else {
			$header_logo_redirect_url = home_url('/map');
		}
	}

	?>

	<!--  esc_url( home_url( '/map' ) ) -->
	<a href="<?php echo esc_url($header_logo_redirect_url); ?>" rel="home" class="custom-logo-link custom-logo">
		<?php

		foreach ($logos as $slug => $logo_id):

			$logo = wp_get_attachment_image_src($logo_id, 'full');

			if (!$logo) {
				continue;
			}

			$has_logo = true;

			// For clarity.
			$logo_url = $logo[0];
			$logo_width = $logo[1];
			$logo_height = $logo[2];

			// Reduce the width and height by half for retina screens.
			$logo_width = floor($logo_width / 2);
			$logo_height = floor($logo_height / 2);

			// Check what kind of image it is.
			$file_type = wp_check_filetype($logo_url);

			// Get the meta value for the alt field or fallback to the site title.
			$logo_alt = get_post_meta($logo_id, '_wp_attachment_image_alt', TRUE) ?: get_bloginfo('name');

			if ('svg' === $file_type['ext']) {

				$svg_markup = file_get_contents($logo_url);

				if ($svg_markup) {
					$svg_markup = substr($svg_markup, strpos($svg_markup, '<svg '));
					$svg_attr = sprintf('<svg class="logo-%s" aria-hidden="true" role="img" focusable="false" ', $slug);
					$svg = preg_replace('/^<svg /', $svg_attr, trim($svg_markup));

					echo $svg;
				}
			} else {
				?>

				<img class="logo-<?php echo $slug; ?>" src="<?php echo esc_url($logo_url); ?>"
					width="<?php echo esc_attr($logo_width); ?>" height="<?php echo esc_attr($logo_height); ?>"
					alt="<?php echo esc_attr($logo_alt); ?>" />

			<?php }
		endforeach;
		?>
	</a>

	<?php

	// Return our output, if there's a logo to output.
	return $has_logo ? ob_get_clean() : '';

}


/**
 * Returns the array of icons for a given group.
 */
function pol_get_icon_group($group)
{
	return POL_SVG_Icons::get_svg_group($group);
}


/**
 * Outputs the SVG code for a given icon.
 */
function pol_the_icon_svg($group, $icon, $width = 24, $height = 24, $stroke = 0)
{
	echo pol_get_icon_svg($group, $icon, $width, $height, $stroke);
}


/**
 * Returns the SVG code for a given icon.
 */
function pol_get_icon_svg($group, $icon, $width = 24, $height = 24, $stroke = 0)
{
	return POL_SVG_Icons::get_svg($group, $icon, $width, $height, $stroke);
}


/**
 * Detects the social network from a URL and returns the SVG code for its icon.
 */
function pol_get_social_link_svg($uri, $width = 24, $height = 24)
{
	return POL_SVG_Icons::get_social_link_svg($uri, $width, $height);
}


/**
 * Replaces the social menu links with SVG icons.
 */
function pol_nav_menu_social_icons($item_output, $item, $depth, $args)
{

	// Change SVG icon inside social menu if there is a supported URL.
	$social_menu_args = pol_get_social_menu_args();

	if ($social_menu_args['theme_location'] === $args->theme_location) {
		$svg = pol_get_social_link_svg($item->url, 24, 24);

		if (!empty($svg)) {
			$item_output = str_replace($args->link_before, $svg, $item_output);
		}
	}

	return $item_output;

}
add_filter('walker_nav_menu_start_el', 'pol_nav_menu_social_icons', 10, 4);


/**
 * Outputs the social menu, if there is one.
 */
function pol_the_social_menu($args = array())
{

	// Return if we don't have a social menu.
	if (!has_nav_menu('social')) {
		return;
	}

	// Get our custom social menu args.
	$social_args = pol_get_social_menu_args($args);

	wp_nav_menu($social_args);

}


/**
 * Outputs the footer menu, if there is one.
 */
function pol_the_footer_menu()
{

	// Return if we don't have a footer menu.
	if (!has_nav_menu('footer')) {
		return;
	}

	wp_nav_menu(
		array(
			'container' => '',
			'container_class' => '',
			'depth' => 1,
			'fallback_cb' => '',
			'menu_class' => 'footer-menu reset-list-style',
			'theme_location' => 'footer',
		));

}


/**
 * Returns the post archive column classes.
 */
function pol_get_archive_columns_classes()
{

	$classes = array();

	// Get the array holding all of the columns options.
	$archive_columns_options = POL_Customizer::get_archive_columns_options();

	// Loop over the array, and class value of each one to the array.
	foreach ($archive_columns_options as $setting_name => $setting_data) {

		// Get the value of the setting, or the default if none is set.
		$value = get_theme_mod($setting_name, $setting_data['default']);

		// Convert the number in the setting (1/2/3/4) to the class names used in our twelve column grid.
		switch ($setting_name) {
			case 'pol_post_grid_columns_mobile':
				$classes['mobile'] = 'cols-' . (12 / $value);
				break;

			case 'pol_post_grid_columns_tablet':
				$classes['tablet'] = 'cols-t-' . (12 / $value);
				break;

			case 'pol_post_grid_columns_laptop':
				$classes['laptop'] = 'cols-l-' . (12 / $value);
				break;

			case 'pol_post_grid_columns_desktop':
				$classes['desktop'] = 'cols-d-' . (12 / $value);
				break;

			case 'pol_post_grid_columns_desktop_xl':
				$classes['desktop_xl'] = 'cols-dxl-' . (12 / $value);
				break;

		}
	}

	return $classes;

}


/**
 * Outputs the post archive filters, if enabled.
 */
function pol_the_post_archive_filters()
{

	// Return if we're not showing the post archive filters.
	if (!pol_show_post_archive_filters()) {
		return;
	}

	$filter_taxonomy = 'category';

	// Get our filter taxonomy terms.
	$terms = get_terms(
		array(
			'depth' => 1,
			'taxonomy' => $filter_taxonomy,
		));

	if (is_wp_error($terms) || !$terms) {
		return;
	}

	$home_url = '';
	$post_type = '';

	// Determine the correct home URL to link to.
	if (is_home()) {
		$post_type = 'post';
		$home_url = home_url();
	} elseif (is_post_type_archive()) {
		$post_type = get_post_type();
		$home_url = get_post_type_archive_link($post_type);
	} else if (is_page_template('page-templates/template-archive.php')) {
		$post_type = get_post_type();
		$home_url = get_permalink();
	}
	?>

	<div class="filter-wrapper i-a a-fade-up a-del-200">
		<ul class="filter-list reset-list-style">

			<?php if ($home_url): ?>
				<li class="filter-show-all"><a class="filter-link active"
						data-filter-post-type="<?php echo esc_attr($post_type); ?>"
						href="<?php echo esc_url($home_url); ?>">
						<?php esc_html_e('Show All', 'pol'); ?>
					</a></li>
			<?php endif; ?>

			<?php foreach ($terms as $term): ?>
				<li class="filter-term-<?php echo esc_attr($term->slug); ?>"><a class="filter-link"
						data-filter-term-id="<?php echo esc_attr($term->term_id); ?>"
						data-filter-taxonomy="<?php echo esc_attr($term->taxonomy); ?>"
						data-filter-post-type="<?php echo esc_attr($post_type); ?>"
						href="<?php echo esc_url(get_term_link($term)); ?>">
						<?php echo $term->name; ?>
					</a></li>
			<?php endforeach; ?>

		</ul><!-- .filter-list -->
	</div><!-- .filter-wrapper -->

<?php

}


/**
 * Outputs the post meta for a given post and context.
 */
function pol_the_post_meta($context = 'archive')
{

	global $post;

	// Escaped in pol_get_post_meta().
	echo pol_get_post_meta($post->ID, $context);

}


/**
 * Returns the post meta for a given post and context.
 */
function pol_get_post_meta($post_id, $context = 'archive')
{

	// Get our post type.
	$post_type = get_post_type($post_id);

	// Get the list of the post types that support post meta, and only proceed if the current post type is supported.
	$post_type_has_post_meta = false;
	$post_types_with_post_meta = POL_Customizer::get_post_types_with_post_meta();

	foreach ($post_types_with_post_meta as $post_type_with_post_meta => $data) {
		if ($post_type == $post_type_with_post_meta) {
			$post_type_has_post_meta = true;
			break;
		}
	}

	if (!$post_type_has_post_meta) {
		return;
	}

	// Get the default post meta for this post type.
	$post_meta_default = isset($post_types_with_post_meta[$post_type]['default'][$context]) ? $post_types_with_post_meta[$post_type]['default'][$context] : array();

	// Determine the Customizer setting name based on post type and context.
	$theme_mod_name = 'pol_post_meta_' . $post_type;
	if ($context == 'single') {
		$theme_mod_name .= '_single';
	}

	// Get the post meta for this post type from the Customizer setting.
	$post_meta = get_theme_mod($theme_mod_name, $post_meta_default);

	// If we have post meta, sort it.
	if ($post_meta && !in_array('empty', $post_meta)) {

		// Set the output order of the post meta.
		$post_meta_order = array('date', 'author', 'categories', 'tags', 'comments', 'edit-link');

		// Store any custom post meta items in a separate array, so we can append them after sorting.
		$post_meta_custom = array_diff($post_meta, $post_meta_order);

		// Loop over the intended order, and sort $post_meta_reordered accordingly.
		$post_meta_reordered = array();
		foreach ($post_meta_order as $i => $post_meta_name) {
			$original_i = array_search($post_meta_name, $post_meta);
			if ($original_i === false) {
				continue;
			}
			$post_meta_reordered[$i] = $post_meta[$original_i];
		}

		// Reassign the reordered post meta with custom post meta items appended, and update the indexes.
		$post_meta = array_values(array_merge($post_meta_reordered, $post_meta_custom));

	}

	// If the post meta setting has the value 'empty' at this point, it's explicitly empty and the default post meta shouldn't be output.
	if (!$post_meta || ($post_meta && in_array('empty', $post_meta))) {
		return;
	}

	// Enable the $pol_has_meta variable to be modified in actions.
	global $pol_has_meta;

	// Default it to false, to make sure we don't output an empty container.
	$pol_has_meta = false;

	global $post;
	$post = get_post($post_id);
	setup_postdata($post);

	// Record out output.
	ob_start();
	?>

	<div class="post-meta-wrapper">
		<ul class="post-meta">
			<?php
			foreach ($post_meta as $post_meta_item) {
				switch ($post_meta_item) {

					// DATE
					case 'date':
						$pol_has_meta = true;
						$entry_time = get_the_time(get_option('date_format'));
						$date_link = get_month_link(get_the_time('Y'), get_the_time('m'));
						$entry_time_str = '<time><a href="' . esc_url($date_link) . '">' . $entry_time . '</a></time>';
						?>
						<li class="date">
							<?php
							if ($context == 'single') {
								printf(esc_html_x('Published %s', '%s = The date of the post', 'pol'), $entry_time_str);
							} else {
								echo $entry_time_str;
							}
							?>
						</li>
						<?php
						break;

					// AUTHOR
					case 'author':
						$pol_has_meta = true;
						?>
						<li class="author">
							<?php
							// Translators: %s = the author name
							printf(esc_html_x('By %s', '%s = author name', 'pol'), '<a href="' . esc_url(get_author_posts_url(get_the_author_meta('ID'))) . '">' . esc_html(get_the_author_meta('display_name')) . '</a>'); ?>
						</li>
						<?php
						break;

					// CATEGORIES
					case 'categories':
						$category_taxonomy = ($post_type == 'jetpack-portfolio') ? 'jetpack-portfolio-type' : 'category';
						if (!has_term('', $category_taxonomy, $post_id)) {
							break;
						}
						$pol_has_meta = true;
						$prefix = ($context == 'single') ? esc_html__('Posted in', 'pol') : esc_html__('In', 'pol');
						?>
						<li class="categories">
							<?php the_terms($post_id, $category_taxonomy, $prefix . ' ', ', '); ?>
						</li>
						<?php
						break;

					// TAGS
					case 'tags':
						$tag_taxonomy = 'post_tag';
						if (!has_term('', $tag_taxonomy, $post_id)) {
							break;
						}
						$pol_has_meta = true;
						?>
						<li class="tags">
							<?php the_terms($post_id, $tag_taxonomy, '<span class="tag">#', '</span> <span class="tag">#', '</span>'); ?>
						</li>
						<?php
						break;

					// COMMENTS
					case 'comments':
						if (post_password_required() || !comments_open() || !get_comments_number()) {
							break;
						}
						$pol_has_meta = true;
						?>
						<li class="comments">
							<?php comments_popup_link(); ?>
						</li>
						<?php
						break;

					// EDIT LINK
					case 'edit-link':
						if (!current_user_can('edit_post', $post_id)) {
							break;
						}
						$pol_has_meta = true;
						?>
						<li class="edit">
							<a href="<?php echo esc_url(get_edit_post_link()); ?>">
								<?php esc_html_e('Edit', 'pol'); ?>
							</a>
						</li>
						<?php
						break;

				}
			}
			?>
		</ul>
	</div>
	<?php

	wp_reset_postdata();

	// Get the recorded output.
	$meta_output = ob_get_clean();

	// If there is post meta, return it.
	return ($pol_has_meta && $meta_output) ? $meta_output : '';

}


/**
 * Outputs a random goat image.
 */
function pol_the_random_goat($classes = '')
{
	echo pol_get_random_goat($classes);
}


/**
 * Returns a random goat image.
 */
function pol_get_random_goat($classes = '')
{

	// Get the goats.
	$goat_images = get_option('options_goat_images');

	if (!$goat_images) {
		return;
	}

	$image_count = count($goat_images);
	$random_number = rand(1, $image_count - 1);
	$image_id = $goat_images[$random_number];
	$image = wp_get_attachment_image($image_id, 'large');
	$class = 'goat';

	if ($classes) {
		$class .= ' ' . $classes;
	}

	if (!$image) {
		return;
	}

	return '<figure class="' . esc_attr($class) . '">' . $image . '</figure>';

}

// Random goat for Workshops
function pol_get_random_workshop_goat($classes = '')
{

	// Get the goats.
	$goat_images = array('workshop1.jpeg', 'workshop2.jpeg');

	$rand = rand(0, 1);
	//echo $input[$rand_keys[0]] 
//return $goat_images[$rand_goat[0]];
	return '<img  style="width:300px; float:right;" src="https://thegoatpol.org/wp-content/uploads/2024/01/' . $goat_images[$rand] . '" />';

}