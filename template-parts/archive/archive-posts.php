<?php
/**
 * Displays the post archive grid.
 *
 * @package GOAT PoL
 */ 

// Use the custom query if it exists...
if ( isset( $args['custom_query'] ) ) {
	$query = $args['custom_query'];

// or the default $wp_query if it doesn't.
} else {
	global $wp_query;
	$query = $wp_query;

}

// Get the column classes, based on the settings in the Customizer.
$archive_columns_classes 	  = pol_get_archive_columns_classes();
$archive_columns_class_attr = $archive_columns_classes ? ' ' . implode( ' ', $archive_columns_classes ) : '';
?>

<div class="posts">
  <div class="section-inner">
    <div class="posts-grid grid load-more-target<?php echo esc_attr( $archive_columns_class_attr ); ?>">

      <div class="col grid-sizer"></div>
    
      <?php while ( $query->have_posts() ) : $query->the_post(); ?>

        <div class="article-wrapper col">
          <?php get_template_part( 'template-parts/archive/preview', get_post_type() ); ?>
        </div>

      <?php endwhile; ?>

    </div><!-- .posts-grid -->
  </div><!-- .section-inner -->
</div><!-- .posts -->