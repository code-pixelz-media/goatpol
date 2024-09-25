<?php

/**
 * Displays the modal menu.
 *
 * @package GOAT PoL
 */

// Return if we're doing a blank canvas.
if (is_page_template('page-templates/template-blank-canvas.php')) {
	return;
}



$cover_untoggle_overlay = false;
?>

<div class="menu-modal cover-modal" data-modal-target-string=".menu-modal" aria-expanded="false">

	<?php if ($cover_untoggle_overlay) : ?>
		<div class="menu-modal-cover-untoggle" data-toggle-target=".menu-modal" data-toggle-screen-lock="true" data-toggle-body-class="showing-menu-modal" data-set-focus="#site-header .nav-toggle"></div>
	<?php endif;
 ?>

	<div class="menu-modal-inner modal-inner">
		<div class="modal-menu-wrapper">

			<div class="menu-modal-toggles">
				<a href="#" class="toggle nav-untoggle" data-toggle-target=".menu-modal" data-toggle-unlock-screen="true" data-toggle-body-class="showing-menu-modal" aria-pressed="false" role="button" data-set-focus="#site-header .nav-toggle">

					<?php if (get_theme_mod('pol_enable_menu_button_labels', false)) : ?>
						<span class="nav-untoggle-text"><?php esc_html_e('Close', 'pol'); ?></span>
					<?php else : ?>
						<span class="screen-reader-text"><?php esc_html_e('Close', 'pol'); ?></span>
					<?php endif; ?>

					<?php //pol_the_icon_svg( 'ui', 'close', 18, 18 ); 
					?>

				</a><!-- .nav-untoggle -->
			</div><!-- .menu-modal-toggles -->
			<?php


			if (is_page_template('page-templates/template-map.php') || is_page_template('page-templates/template-list-view.php')) { ?>

				<input id="pol-map-search-input" class="controls" type="text" placeholder="<?php _e('Search The GOAT PoL', 'pol'); ?>" />

			<?php } ?>
			<div class="menu-top">

				<?php if (has_nav_menu('modal')) : ?>
					<ul class="modal-menu reset-list-style">
						<?php
						wp_nav_menu(array(
							'container'      		=> '',
							'items_wrap'     		=> '%3$s',
							'show_toggles'   		=> true,
							'theme_location' 		=> 'modal',
						));
						?>
					</ul><!-- .modal-menu -->
				<?php endif; ?>

				<?php if (get_theme_mod('pol_enable_search', true)) : ?>
					<div class="menu-modal-search">
						<?php get_search_form(); ?>
					</div><!-- .menu-modal-search -->
				<?php endif; ?>

			</div><!-- .menu-top -->

			<div class="menu-bottom">
				<?php
				pol_the_social_menu(array(
					'menu_class'	=> 'social-menu reset-list-style social-icons circular',
				));
				?>
			</div><!-- .menu-bottom -->

		</div><!-- .menu-wrapper -->
	</div><!-- .menu-modal-inner -->
</div><!-- .menu-modal -->
<div class="inputcontainer" style="display: none;">
	<div class="icon-container">
		<i class="loader"></i>
	</div>
</div>