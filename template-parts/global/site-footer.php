<?php
/**
 * Displays the site footer.
 *
 * @package GOAT PoL
 */

// Return if we're doing a blank canvas template.
if ( pol_is_blank_canvas_template() ) {
  return;
} 

$footer_logo = pol_get_custom_logo();

if( home_url($_SERVER['REQUEST_URI']) == home_url('/list/') ){
  $footer_logo_redirect_url = home_url('/map');
}else if(home_url($_SERVER['REQUEST_URI']) == home_url('/map/')){
  $footer_logo_redirect_url = home_url('/map');
}else{
  if( ($_SERVER['HTTP_REFERER'] == home_url('/map/')) || ($_SERVER['HTTP_REFERER'] == home_url('/list/') ) ){
    $footer_logo_redirect_url = $_SERVER['HTTP_REFERER'];
  }else{
    $footer_logo_redirect_url = home_url('/map');
  }
}
?>


<?php
if(is_singular( 'workshop' )){
?>
<footer id="site-footer" style="margin-top:0px !important; "> 
<div class="footer-goat-container section-inner" >
    <a href="<?php echo esc_url( $footer_logo_redirect_url ); //echo home_url('/map'); ?>"><?php echo pol_get_random_workshop_goat(); ?></a>
  </div> 
<?php
}else{ ?>
<footer id="site-footer"> 
  <div class="footer-goat-container section-inner">
    <a href="<?php echo esc_url( $footer_logo_redirect_url ); //echo home_url('/map'); ?>"><?php pol_the_random_goat( 'footer-goat' ); ?></a>
  </div>  
<?php } ?>
  <div class="footer-inner section-inner">

    <?php if( $footer_logo ) : ?>
      <div class="footer-logo">
        <?php echo $footer_logo; ?>
      </div><!-- .footer-logo -->
    <?php endif; ?>

    <div class="footer-credits">
      <p class="footer-copyright"><?php echo esc_html__( 'Copyright', 'pol' ); ?> &copy; <?php echo esc_html( date_i18n( esc_html__( 'Y', 'pol' ) ) ); ?></p>
      <?php pol_the_footer_menu(); ?>
    </div><!-- .footer-credits -->

  </div><!-- .footer-inner -->
</footer><!-- #site-footer -->