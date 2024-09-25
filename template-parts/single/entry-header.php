<?php
/**
 * Displays the entry header.
 *
 * @package GOAT PoL
 */

// Return if we're doing a content-only template.
if ( pol_is_content_only_template() ) {
  return;
} 

$title = get_field( 'override_page_title' ) ?: get_the_title();
$nom_de_plume = get_field( 'story_nom_de_plume' );
$email_address = get_field( 'story_email_address' );
?>

<header class="entry-header">
  <div class="section-inner i-a a-fade-up">

    <?php if ( is_front_page() && is_home() ) : ?>
      <div class="entry-title h1"><?php echo $title; ?></div>
    <?php else : ?>
      <h1 class="entry-title" tabindex="2"><?php echo $title; ?></h1>
    <?php endif; ?>
    <?php if ( $nom_de_plume ) : ?>

      <div class="intro-text contain-margins has-secondary-color has-text-color cpm-autor-list-wrapper" style="cursor:pointer">
      <h6 tabindex="2" class="author-name"> <?php echo 'By ' . $nom_de_plume; ?></h6>
          
      </div><!-- .intro-text -->
      <div class="cpm-autor-option-wrapper" style="display: none;">
        <a href="mailto:<?php echo $email_address; ?>">Write <?php echo $nom_de_plume; ?> a fan letter</a><br>
        <a class="cpm-story-autor">Read more stories by  <?php echo $nom_de_plume; ?></a>
        <!-- <a class="cpm-story-autor">For more stories by <?php //the_author_posts_link(); ?> see their Contributor's Page</a> -->
        <?php /*
        <a href="<?php echo get_the_author_url(); ?>" class="cpm-story-autor">For more stories by <?php echo $nom_de_plume; ?> see their Contributor's Page</a>
        */ ?>
      </div>
    <?php endif; ?>

    <div class="logo-popup-wrapper">
      <?php get_template_part('template-parts/popup/story-popup'); ?>
    </div>
<?php get_template_part('template-parts/popup/menu');?>
  </div><!-- .section-inner -->
</header><!-- .entry-header -->