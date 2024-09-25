<?php
/**
 * Displays the site header.
 *
 * @package GOAT PoL
 */

// Return if we're doing a blank canvas template.
if ( pol_is_blank_canvas_template() ) {
	return;
}

$enable_search      = get_theme_mod( 'pol_enable_search', true );
$sticky_header      = get_theme_mod( 'pol_enable_sticky_header', true );
$logo 			        = pol_get_custom_logo();
$site_title         = wp_kses_post( get_bloginfo( 'name' ) );
$header_classes     = array();

if( $sticky_header ) {
  $header_classes[] = 'stick-me';
}

if( ! is_page_template( 'page-templates/template-map.php' ) ) {
  $header_classes[] = 'header-logotype';
}

$header_class_attr  = $header_classes ? ' class="' . esc_attr( join( ' ', $header_classes ) ) . '"': '';

if ( $logo ) {
  $site_title_class   = 'site-logo';
  $home_link_contents = $logo . '<span class="screen-reader-text">' . $site_title . '</span>';
} else {
  $site_title_class   = 'site-title';
  $home_link_contents = '<a href="' . esc_url('sdf') . '" rel="home">' . $site_title . '</a>';

  if( home_url($_SERVER['REQUEST_URI']) == home_url('/list/') ){
		$header_logo_redirect_url = home_url('/map');
	}else if(home_url($_SERVER['REQUEST_URI']) == home_url('/map/')){
		$header_logo_redirect_url = home_url('/map');
	}else{
		if( ($_SERVER['HTTP_REFERER'] == home_url('/map/')) || ($_SERVER['HTTP_REFERER'] == home_url('/list/') ) ){
			$header_logo_redirect_url = $_SERVER['HTTP_REFERER'];
		}else{
			$header_logo_redirect_url = home_url('/map');
		}
	}

  $home_link_contents = '<a href="' . esc_url( $header_logo_redirect_url ) . '" rel="home">' . $site_title . '</a>';
}
?>
<header  id="site-header"<?php echo $header_class_attr; ?> >
  <div class="header-inner section-inner">

    <div class="header-titles">
      <?php if ( is_front_page() && is_home() && ! is_paged() ) : ?>
        <h1 class="<?php echo $site_title_class; ?>"><?php echo $home_link_contents; ?></h1>
      <?php else : ?>
        <div class="<?php echo $site_title_class; ?>"><?php echo $home_link_contents; ?></div>
      <?php endif; ?>

    </div><!-- .header-titles -->

    <?php 
    //if(!is_home() && !is_front_page()){
    ?>
    <nav class="header-navigation">
      <?php if ( has_nav_menu( 'main' ) ) : ?>
        <ul class="main-menu dropdown-menu reset-list-style">
          <?php
          wp_nav_menu( array(
            'container'      => '',
            'items_wrap'     => '%3$s',
            'show_toggles'   => true,
            'theme_location' => 'main',
          ) );
          ?>
        </ul><!-- .main-menu -->
      <?php endif; ?>

      <div class="header-toggles">
        <?php pol_the_social_menu(); ?>

        <?php if ( $enable_search ) : ?>
          <a href="#" class="search-toggle toggle" data-toggle-target=".search-modal" data-toggle-screen-lock="true" data-toggle-body-class="showing-search-modal" data-set-focus=".search-modal .search-field" aria-pressed="false" role="button" role="button" data-untoggle-below="700">
            <span class="screen-reader-text"><?php esc_html_e( 'Search', 'pol' ); ?></span>
            <?php pol_the_icon_svg( 'ui', 'search', 18, 18 ); ?>
          </a>
        <?php endif; ?>

        <?php
        $nav_toggle_class         = $enable_search ? ' icon-menu-search' : ' icon-menu';
        $show_menu_button_labels  = get_theme_mod( 'pol_enable_menu_button_labels', false );
        $mobile_toggle_text_class = $show_menu_button_labels ? 'mobile-nav-toggle-text' : 'screen-reader-text';
        $mobile_toggle_icon       = $enable_search ? pol_get_icon_svg( 'ui', 'menu-search', 26, 24 ) : pol_get_icon_svg( 'ui', 'menu', 24, 24 );
        ?>

        <a href="#" class="nav-toggle mobile-nav-toggle toggle<?php echo $nav_toggle_class; ?>" data-toggle-target=".menu-modal" data-toggle-screen-lock="false" data-toggle-body-class="showing-menu-modal" data-set-focus=".menu-modal .nav-untoggle" aria-pressed="false" role="button">
          <span class="<?php echo esc_attr( $mobile_toggle_text_class ); ?>"><?php esc_html_e( 'Menu', 'pol' ); ?></span>
          <?php echo $mobile_toggle_icon; ?>
        </a>
      </div><!-- .header-toggles -->
      
    </nav>
    


    
  </div><!-- .header-inner -->
    <?php
        $page = get_page_by_title('Map');
        if(!is_page('Map')){
          get_template_part( 'template-parts/global/modal-hamburgerr' ); 
        }
    ?>

  
</header><!-- #site-header -->


