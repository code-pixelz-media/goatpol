<?php
/**
 * Template functions.
 *
 * @package GOAT PoL
 */


/**
 * Adds custom classes to the <body> element.
 */
function pol_body_classes( $classes ) {

  global $post;
  $post_type = isset( $post ) ? $post->post_type : false;

  // Determine pagination type.
  $pagination_type = get_theme_mod( 'pol_pagination_type', 'button' );
  $classes[] = 'pagination-type-' . $pagination_type;

  // Desktop nav toggle.
  $desktop_nav_toggle = get_page_by_path( 'sidebar' );
  if( $desktop_nav_toggle && $desktop_nav_toggle->ID == get_the_ID() ) {
    $classes[] = 'has-desktop-nav-toggle';
  }

  // Check whether the current page only has content.
  if ( pol_is_content_only_template() ) {
    $classes[] = 'has-only-content';
  }

  // Check whether the current page is a blank canvas.
  if ( pol_is_blank_canvas_template() ) {
    $classes[] = 'is-blank-canvas-template';
  }

  // Check for disabled search.
  if ( ! get_theme_mod( 'pol_enable_search', true ) ) {
    $classes[] = 'disable-search-modal';
  }

  // Check for footer menu.
  if ( has_nav_menu( 'footer' ) ) {
    $classes[] = 'has-footer-menu';
  }

  // Check for social menu.
  $social_menu_args = pol_get_social_menu_args();
  if ( has_nav_menu( $social_menu_args['theme_location'] ) ) {
    $classes[] = 'has-social-menu';
  }

  // Check for disabled animations.
  $classes[] = get_theme_mod( 'pol_disable_animations', false ) ? 'no-anim' : 'has-anim';

  // Check for post thumbnail.
  if ( is_singular() && has_post_thumbnail() ) {
    $classes[] = 'has-post-thumbnail';
  } elseif ( is_singular() ) {
    $classes[] = 'missing-post-thumbnail';
  }

  // Check whether we're in the customizer preview.
  if ( is_customize_preview() ) {
    $classes[] = 'customizer-preview';
  }

  // Check if we're showing comments.
  if ( is_singular() && ( ( comments_open() || get_comments_number() ) && ! post_password_required() ) ) {
    $classes[] = 'showing-comments';
  } else if ( is_singular() ) {
    $classes[] = 'not-showing-comments';
  }

  // Shared archive page class.
  if ( is_archive() || is_search() || is_home() ) {
    $classes[] = 'archive-page';
  }

  // Slim page template class names (class = name - file suffix).
  if ( is_page_template() ) {
    $classes[] = basename( get_page_template_slug(), '.php' );
  }

  return $classes;

}
add_action( 'body_class', 'pol_body_classes' );


/**
 * Remove the 'no-js' class from body if JS is supported.
 */
function pol_no_js_class() {
  ?>
  <script>document.documentElement.className = document.documentElement.className.replace( 'no-js', 'js' );</script>
  <?php
}
add_action( 'wp_head', 'pol_no_js_class', 0 );


/**
 * Unset JS-triggered CSS animations to prevent FOUC with .no-js class.
 */
function pol_noscript_styles() {
  ?>
  <noscript>
    <style>
      .spot-fade-in-scale, .no-js .spot-fade-up { 
        opacity: 1.0 !important; 
        transform: none !important;
      }
    </style>
  </noscript>
  <?php
}
add_action( 'wp_head', 'pol_noscript_styles', 0 );




/**
 * Hide password-protected posts from the loop.
 */
function pol_filter_query( $query ) {

	if ( ! $query->is_singular() && ! is_admin() ) {
		$query->set( 'has_password', false );
		$query->set( 'post_status', 'publish' );
	}

  if($query->is_search() && !is_admin()){
    $query->set( 'post_type', array( 'story','place' ));
    $query->set( 'post_status', 'publish' );
    
  }

}
add_action( 'pre_get_posts', 'pol_filter_query' );


/**
 * Removes the prefix from password-protected posts.
 */
function pol_filter_protected_posts_prefix() {
  return '%s';
}
add_filter( 'protected_title_format', 'pol_filter_protected_posts_prefix' );


/**
 * Filter the prefix from private posts.
 */
function pol_filter_private_posts_prefix() {
  return '%s';
}
add_filter( 'protected_title_format', 'pol_filter_private_posts_prefix' );


/**
 * Disables the default archive title prefix.
 */
add_filter( 'get_the_archive_title_prefix', '__return_false' );


/**
 * Returns the custom archive title prefix.
 */
function pol_get_the_archive_title_prefix() {

  $prefix = '';

  if ( is_search() ) {
    $prefix = esc_html_x( 'Search Results', 'search archive title prefix', 'pol' );

  } elseif ( is_category() ) {
    $prefix = esc_html_x( 'Category', 'category archive title prefix', 'pol' );

  } elseif ( is_tag() ) {
    $prefix = esc_html_x( 'Tag', 'tag archive title prefix', 'pol' );

  } elseif ( is_author() ) {
    $prefix = esc_html_x( 'Author', 'author archive title prefix', 'pol' );

  } elseif ( is_year() ) {
    $prefix = esc_html_x( 'Year', 'date archive title prefix', 'pol' );

  } elseif ( is_month() ) {
    $prefix = esc_html_x( 'Month', 'date archive title prefix', 'pol' );

  } elseif ( is_day() ) {
    $prefix = esc_html_x( 'Day', 'date archive title prefix', 'pol' );

  } elseif ( is_post_type_archive() ) {
    $prefix = '';

  } elseif ( is_tax( 'post_format' ) ) {
    $prefix = '';

  } elseif ( is_tax() ) {
    $queried_object = get_queried_object();

    if ( $queried_object ) {
      $tax    = get_taxonomy( $queried_object->taxonomy );
      $prefix = sprintf(
        /* translators: %s: Taxonomy singular name. */
        esc_html_x( '%s:', 'taxonomy term archive title prefix', 'pol' ),
        $tax->labels->singular_name
      );
    }

  } elseif ( is_home() && is_paged() ) {
    $prefix = esc_html_x( 'Archives', 'general archive title prefix', 'pol' );
  }

  return $prefix;

}


/**
 * Filters the archive title.
 */
function pol_filter_archive_title( $title ) {

  // Home: Get the Customizer option for post archive text.
  if ( is_home() && ! is_paged() ) {
    $title = get_theme_mod( 'pol_home_text', '' );
  }

  // Home and paged: Output page number.
  elseif ( is_home() && is_paged() ) {
    global $wp_query;
    $paged 	= get_query_var( 'paged' ) ? get_query_var( 'paged' ) : 1;
    $max 	  = isset( $wp_query->max_num_pages ) ? $wp_query->max_num_pages : 1;
    $title 	= sprintf( esc_html_x( 'Page %1$s of %2$s', '%1$s = Current page number, %2$s = Number of pages', 'pol' ), $paged, $max );
  }

  // Jetpack Portfolio archive: Get the Customizer option for the Jetpack Portfolio archive title, if it is set and isn't empty.
  elseif ( is_post_type_archive( 'jetpack-portfolio' ) && ! is_paged() && get_theme_mod( 'pol_jetpack_portfolio_archive_text', '' ) ) {
    $title = get_theme_mod( 'pol_jetpack_portfolio_archive_text', '' );
  }

  // On search, show the search query.
  elseif ( is_search() ) {
    $title = '&ldquo;' . get_search_query() . '&rdquo;';
  }

  return $title;

}
add_filter( 'get_the_archive_title', 'pol_filter_archive_title' );


/**
 * Filters the archive description.
 */
function pol_filter_archive_description( $description ) {

  // Home: Empty description.
  if ( is_home() ) {
    $description = '';
  }
  
  // On search, show a string describing the results of the search.
  elseif ( is_search() ) {
    global $wp_query;
    if ( $wp_query->found_posts ) {
      /* Translators: %s = Number of results */
      $description = esc_html( sprintf( _nx( 'We found %s result for your search.', 'We found %s results for your search.',  $wp_query->found_posts, '%s = Number of results', 'pol' ), $wp_query->found_posts ) );
    } else {
      $description = esc_html__( 'We could not find any results for your search. You can give it another try through the search form below.', 'pol' );
    }
  }

  return $description;

}
add_filter( 'get_the_archive_description', 'pol_filter_archive_description' );


/**
 * Replaces the default excerpt suffix [...] with a &hellip; (three dots)
 */
function pol_excerpt_more() {
  return '&hellip;';
}
add_filter( 'excerpt_more', 'pol_excerpt_more' );


/**
 * Sets the length of generated post excerpts.
 */
function pol_excerpt_length( $length ) {
  return 36;
}
add_filter( 'excerpt_length', 'pol_excerpt_length', 999 );


/**
 * Remove shortcodes from excerpts.
 */
function pol_remove_shortcodes_from_excerpt( $content ) { 
	return strip_shortcodes( $content );
}
add_filter( 'the_excerpt', 'pol_remove_shortcodes_from_excerpt' );


/**
 * Returns the social menu arguments for wp_nav_menu().
 */
function pol_get_social_menu_args( $args = array() ) {

  return wp_parse_args( $args, array(
    'container'			  => '',
    'container_class'	=> '',
    'depth'			  	  => 1,
    'fallback_cb'		  => '',
    'link_before'		  => '<span class="screen-reader-text">',
    'link_after'		  => '</span>',
    'menu_class'		  => 'social-menu reset-list-style social-icons',
    'theme_location'	=> 'social',
  ) );

}


/**
 * Filters menu item classes for wp_list_pages() to match menu styles.
 */
function pol_filter_wp_list_pages_item_classes( $css_class, $item, $depth, $args, $current_page ) {

  // Only apply to wp_list_pages() calls with match_menu_classes set to true.
  $match_menu_classes = isset( $args['match_menu_classes'] );

  if ( ! $match_menu_classes ) {
    return $css_class;
  }

  // Add current menu item class.
  if ( in_array( 'current_page_item', $css_class ) ) {
    $css_class[] = 'current-menu-item';
  }

  // Add menu item has children class.
  if ( in_array( 'page_item_has_children', $css_class ) ) {
    $css_class[] = 'menu-item-has-children';
  }

  return $css_class;

}
add_filter( 'page_css_class', 'pol_filter_wp_list_pages_item_classes', 10, 5 );


/**
 * Filters nav menu arguments to add a submenu toggle.
 */
function pol_filter_nav_menu_item_args( $args, $item, $depth ) {

  // Add sub menu toggles to the main menu with toggles.
  if ( isset( $args->show_toggles ) && $args->show_toggles ) {

    // Wrap the menu item link contents in a div, used for positioning.
    $args->before = '<div class="ancestor-wrapper">';
    $args->after  = '';

    // Add a toggle to items with children.
    if ( in_array( 'menu-item-has-children', $item->classes ) ) {

      // Add the sub menu toggle.
      if( $args->theme_location == 'modal' ) {

        $toggle_target_string = '.menu-modal .menu-item-' . $item->ID . ' &gt; .sub-menu';

        $args->after .= '<div class="sub-menu-toggle-wrapper"><a href="#" class="toggle sub-menu-toggle" data-toggle-target="' . $toggle_target_string . '" data-toggle-type="slidetoggle" data-toggle-duration="250"><span class="screen-reader-text">' . esc_html__( 'Show sub menu', 'pol' ) . '</span>' . pol_get_icon_svg( 'ionicons', 'chevron-down-outline', 18, 18 ) . '</a></div>';
  
      } else {

        $args->after .= '<div class="sub-menu-toggle-wrapper">' . pol_get_icon_svg( 'ionicons', 'caret-down', 12, 12 ) . '</div>';

      }
    }

    // Close the wrapper.
    $args->after .= '</div><!-- .ancestor-wrapper -->';

  }

  return $args;

}
add_filter( 'nav_menu_item_args', 'pol_filter_nav_menu_item_args', 10, 3 );


/**
 * Add each menu item's title as an `alt` attribute
 */
function pol_add_menu_item_attr( $atts, $item, $args, $depth ){

  if( isset( $args->alt_title_attr ) && $args->alt_title_attr ){
    $atts['alt'] = $item->title;
  }

  return $atts;

}
add_filter( 'nav_menu_link_attributes', 'pol_add_menu_item_attr', 10, 4 );


/**
 * Adds a badge to comments made by the post's author.
 */
function pol_filter_comment_text( $comment_text, $comment, $args ) {

  if ( is_object( $comment ) && $comment->user_id > 0 ) {
    $user = get_userdata( $comment->user_id );
    $post = get_post( $comment->comment_post_ID );

    if ( ! empty( $user ) && ! empty( $post ) ) {
      if ( $comment->user_id === $post->post_author ) {
        $comment_text .= '<p class="by-post-author">' . esc_html__( 'Post Author', 'pol' ) . '</p>';
      }
    }
  }

  return $comment_text;

}
add_filter( 'comment_text', 'pol_filter_comment_text', 10, 3 );


/**
 * AJAX Load More.
 */
function pol_ajax_load_more() {

  $query_args = json_decode( wp_unslash( $_POST['json_data'] ), true );

  $ajax_query = new WP_Query( $query_args );

  // Determine which preview to use based on the post_type.
  $post_type = $ajax_query->get( 'post_type' );

  // Default to the "post" post type for mixed content.
  if ( ! $post_type || is_array( $post_type ) ) {
    $post_type = 'post';
  }

  if ( $ajax_query->have_posts() ) :
    while ( $ajax_query->have_posts() ) : 
      $ajax_query->the_post();
      global $post;
      ?>

      <div class="article-wrapper col">
        <?php get_template_part( 'template-parts/archive/preview', $post_type ); ?>
      </div>

      <?php 
    endwhile;
  endif;

  wp_die();

}
add_action( 'wp_ajax_nopriv_pol_ajax_load_more', 'pol_ajax_load_more' );
add_action( 'wp_ajax_pol_ajax_load_more', 'pol_ajax_load_more' );


/**
 * AJAX Filters.
 */
function pol_ajax_filters() {

  // Get the filters from AJAX.
  $term_id 	  = isset( $_POST['term_id'] ) ? $_POST['term_id'] : null;
  $taxonomy 	= isset( $_POST['taxonomy'] ) ? $_POST['taxonomy'] : '';
  $post_type 	= isset( $_POST['post_type'] ) ? $_POST['post_type'] : '';

  $args = array(
    'post_status'	=> 'publish',
    'post_type'		=> $post_type,
  );

  // Get the posts per page setting for Jetpack Portfolio.
  if ( $post_type == 'jetpack-portfolio' ) {
    $args['posts_per_page'] = get_option( 'jetpack_portfolio_posts_per_page', get_option( 'posts_per_page', 10 ) );
  }

  // Add the tax query, if set.
  if ( $term_id && $taxonomy ) {
    $args['tax_query'] = array( array(
      'taxonomy'  => $taxonomy,
      'terms'		  => $term_id,
    ) );

  // If a taxonomy isn't set, and we're loading posts, make sure we include the sticky post in the results.
  // The custom argument is used to prepend the latest sticky post with pol_filter_posts_results().
  } elseif ( $post_type == 'post' ) {
    $args['pol_prepend_sticky_post'] = true;
  }

  $custom_query = new WP_Query( $args );

  // Combine the query with the query_vars into a single array.
  $query_args = array_merge( $custom_query->query, $custom_query->query_vars );

  // If max_num_pages is not already set, add it.
  if ( ! array_key_exists( 'max_num_pages', $query_args ) ) {
    $query_args['max_num_pages'] = $custom_query->max_num_pages;
  }

  // Format and return the query arguments.
  echo json_encode( $query_args );

  wp_die();

}
add_action( 'wp_ajax_nopriv_pol_ajax_filters', 'pol_ajax_filters' );
add_action( 'wp_ajax_pol_ajax_filters', 'pol_ajax_filters' );


/**
 * Appends sticky posts to post results when the "Show All" taxonomy filter is active.
 */
function pol_filter_posts_results( $posts, $query ) {

  /**
   * If the custom pol_prepend_sticky_post argument is present (added by pol_ajax_filters()), 
   * and we're showing the first page, prepend the sticky post to the array of post objects.
   * This is done to include the sticky post when the "Show All" link is clicked in the taxonomy filter.
   */
  if ( isset( $query->query['pol_prepend_sticky_post'] ) && ! empty( $query->query_vars['paged'] ) && $query->query_vars['paged'] == 1 ) {
    $sticky = get_option( 'sticky_posts' );

    if ( $sticky ) {
      $sticky_post = get_post( $sticky[0] );
    
      if ( $sticky_post ) {
        array_unshift( $posts, $sticky_post );
      }
    }
  }

  return $posts;
  
}
add_filter( 'posts_results', 'pol_filter_posts_results', 10, 2 );


/**
 * Outputs a meta tag for theme color (used on Android devices and Apple Safari 15)
 */
function pol_meta_theme_color() {
  
  $meta_color = '#' . get_theme_mod( 'background_color', 'ffffff' );

  if ( ! $meta_color ) {
    return;
  }

  echo '<meta name="theme-color" content="' . esc_attr( $meta_color ) . '">';

}
add_action( 'wp_head', 'pol_meta_theme_color' );


/**
 * Returns the custom fonts URL.
 */
function pol_custom_fonts_url() {

  $custom_fonts_url = 'https://fonts.googleapis.com/css2?family=Public+Sans:ital,wght@0,400;0,500;0,600;0,700;1,400;1,700&display=swap';

  // Return false if custom fonts are disabled.
  if ( get_theme_mod( 'pol_disable_google_fonts', false ) ) {
    return false;
  }
  
  return $custom_fonts_url;

}


/**
 * Returns an array containing the editor styles to enqueue.
 */
function pol_editor_styles() {

  $pol_editor_styles = array();
  
  // Custom fonts.
  $custom_fonts_url = pol_custom_fonts_url();

  if( $custom_fonts_url ) {
    $pol_editor_styles[] = $custom_fonts_url;
  }

  // Editor styles.
  $pol_editor_styles[] = './assets/css/editor.css';

  return $pol_editor_styles;

}


/**
 * Returns the custom editor font sizes.
 */
function pol_editor_font_sizes() {

  return array(
    array(
      'name'      => esc_html__( 'X-Small', 'pol' ),
      'shortName' => esc_html__( 'XS', 'pol' ),
      'size'      => 14,
      'slug'      => 'xs',
    ),
    array(
      'name'      => esc_html__( 'Small', 'pol' ),
      'shortName' => esc_html__( 'SM', 'pol' ),
      'size'      => 16,
      'slug'      => 'sm',
    ),
    array(
      'name'      => esc_html__( 'Large', 'pol' ),
      'shortName' => esc_html__( 'LG', 'pol' ),
      'size'      => 20,
      'slug'      => 'lg',
    ),
    array(
      'name'      => esc_html__( 'X-Large', 'pol' ),
      'shortName' => esc_html__( 'XL', 'pol' ),
      'size'      => 24,
      'slug'      => 'xl',
    ),
  );

}


/**
 * Returns the custom editor color palette.
 */
function pol_editor_color_palette() {

  $editor_color_palette = array();
  $color_options 			  = POL_Customizer::get_color_options();
  
  if ( $color_options ) {

    // Add the background option.
    $background_color = '#' . get_theme_mod( 'background_color', 'ffffff' );
    $editor_color_palette[] = array(
      'name'  => esc_html__( 'Background Color', 'pol' ),
      'slug'  => 'background',
      'color' => $background_color,
    );

    // Loop over them and construct an array for the editor-color-palette.
    foreach ( $color_options as $color_option_name => $color_option ) {

      // Only add the colors set to be included in the color palette
      if ( ! isset( $color_option['palette'] ) || ! $color_option['palette'] ) {
        continue;
      }

      $editor_color_palette[] = array(
        'name'  => $color_option['label'],
        'slug'  => $color_option['slug'],
        'color' => get_theme_mod( $color_option_name, $color_option['default'] ),
      );
    }
  }

  return $editor_color_palette;
  
}


/**
 * Replaces menu items with a pre-selected icon on mobile screens.
 */
function pol_mobile_menu_item_icon( $items, $args ) {
	
  if( 'main' == $args->theme_location ) {
    foreach( $items as $item ) {

      $icon = get_field( 'mobile_icon', $item->ID );

      if( $icon ) {
        $item->classes[] = 'has-mobile-icon';
        $item->title     = sprintf( '<span class="item-title">%s</span>%s', $item->title, pol_get_icon_svg( 'ionicons', $icon, 24, 24 ) );
      }

    }
  }
	
	return $items;
	
}
add_filter( 'wp_nav_menu_objects', 'pol_mobile_menu_item_icon', 10, 2 );


/**
 * Returns the top values for a given array.
 */
function pol_get_top_values( $array, $count ) {

  $length = count( $array );

  for($i = 0; $i < $length; $i++) {
        //$val = str_replace('-', ' ', $array[$i]);
    $val = $array[$i];

    if( ! $val ) {
      unset( $array[$i] );
    }
  }

  $array = array_values( $array );

  if( ! $array || empty( $array ) ) {
    return;
  }

  if( count( $array ) > $count ) {
    $array = array_splice( $array, $count );
  }
  
  return $array;

}


/**
 * Remove application passwords.
 */
add_filter( 'wp_is_application_passwords_available', '__return_false' );


add_action('init', 'redirect_to_continue_edit_story');


function redirect_to_continue_edit_story(){

 if(!is_user_logged_in() )  return;

 $user_id = get_current_user_id();
  //$verification = get_user_meta($user_id,)

}