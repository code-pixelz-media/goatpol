<?php
/**
 * Displays the entry content.
 *
 * @package GOAT PoL
 */

?>

<div class="entry-content">

  <?php if ( has_post_thumbnail() && ! post_password_required() ) : ?>
    <figure class="featured-media section-inner i-a a-fade-up a-del-200">
      <div class="media-wrapper">
        <?php the_post_thumbnail(); ?>
      </div><!-- .media-wrapper -->
    </figure><!-- .featured-media -->
  <?php endif; ?>

  <?php 
  the_content();
  wp_link_pages( array(
    'before' => '<nav class="post-nav-links"><hr /><div class="post-nav-links-list">',
    'after'  => '</div></nav>',
  ) );
  ?>
</div><!-- .entry-content -->