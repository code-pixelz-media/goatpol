<?php
/**
 * Displays the archive header.
 *
 * @package GOAT PoL
 */ 

$archive_prefix 		  = pol_get_the_archive_title_prefix();
$archive_title 			  = get_the_archive_title();
$archive_description 	= get_the_archive_description( '<div>', '</div>' );

// Return if we have nothing to output.
if ( ! $archive_title && ! $archive_description && ! pol_show_post_archive_filters() ) {
  return;
}
?>

<header class="archive-header">
  <div class="section-inner">

    <?php if ( $archive_prefix ) : ?>
      <p class="archive-prefix i-a a-fade-up"><?php echo $archive_prefix; ?></p>
    <?php endif; ?>

    <?php if ( $archive_title ) : ?>
      <?php if ( ( is_home() && ! is_paged() ) || ( is_post_type_archive( 'jetpack-portfolio' ) && ! is_paged() && get_theme_mod( 'pol_jetpack_portfolio_archive_text', '' ) ) ) : ?>
        <div class="archive-title has-paragraphs contain-margins i-a a-fade-up"><?php echo wpautop( $archive_title ); ?></div>
      <?php else : ?>
        <h1 class="archive-title i-a a-fade-up"><?php echo $archive_title; ?></h1>
      <?php endif; ?>
    <?php endif; ?>

    <?php if ( $archive_description ) : ?>
      <div class="archive-description mw-small contain-margins i-a a-fade-up a-del-100"><?php echo wpautop( $archive_description ); ?></div>
    <?php endif; ?>

    <?php pol_the_post_archive_filters(); ?> 

  </div><!-- .section-inner -->
</header><!-- .archive-header -->