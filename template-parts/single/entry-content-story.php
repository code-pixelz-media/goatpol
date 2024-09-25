<?php
/**
 * Displays the entry content on story posts.
 *
 * @package GOAT PoL
 */

$content_class = 'entry-content';

$has_image = false;

if ( has_post_thumbnail() && ! post_password_required() ) {
  $has_image = true;
  $content_class .= ' has-featured-image';

  $img_id = get_post_thumbnail_id();
  $media_class = 'story-media i-a a-fade-up a-del-200';
  $img = wp_get_attachment_image_src( $img_id, 'full' );

  if( $img ) {
    if( $img[1] > $img[2] ) {
      $media_class   .= ' landscape';
      $content_class .= ' landscape-featured-image';
    } else {
      $media_class   .= ' portrait';
      $content_class .= ' portrait-featured-image';
    }
  }
}
?>

<div class="<?php echo esc_attr( $content_class ); ?>" tabindex="4">

  <?php if( $has_image ) :
    ?>
    <figure class="<?php echo esc_attr( $media_class ); ?>">
      <div class="media-wrapper">
        <?php the_post_thumbnail(); ?>
      </div><!-- .media-wrapper -->
    </figure><!-- .featured-media -->
    <?php elseif(empty(has_post_thumbnail())): ?>
      <figure class="story-media i-a a-fade-up a-del-200" style="min-height: 580.112px;">
      <div class="media-wrapper">
        <?php echo pol_get_random_goat(); ?>
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
  <p style="float: right;"><?php if( !is_front_page() ) { the_date('j F, Y'); } ?></p>



</div><!-- .entry-content -->


<script>

  jQuery(document).ready(function(){
    jQuery('.entry-content>p>a').each(function(i,e){
        jQuery(this).prop('target','_blank')
    });

    var iframe_url = jQuery('.entry-content').find('iframe').attr("src");
    iframe_url = iframe_url ? iframe_url.split('/embed')[0] : null;

    if (iframe_url) {
    jQuery('.entry-content').find('iframe').addClass("cpm");

    jQuery('.entry-content').find('iframe').parent('p').append('<a target="_blank" class="cpmIframeChild" href="'+ iframe_url +'" style="position:absolute; top:0; bottom: 0px; left:0; right: 0; max-width: 600px;"><div class="iframe-link"></div></a>');
    jQuery('.entry-content').find('iframe').parent('p').addClass('cpmIframe');
    jQuery('.cpmIframe').css('position','relative');
    }
  });
</script>
