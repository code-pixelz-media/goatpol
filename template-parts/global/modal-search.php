<?php
/**
 * Displays the modal search.
 *
 * @package GOAT PoL
 */

// Return if search is disabled or if we're doing a blank canvas template.
if( ! get_theme_mod( 'pol_enable_search', true ) || pol_is_blank_canvas_template() ) {
	return;
}

// Generate a unique ID for each form.
$unique_id = esc_attr( uniqid( 'search-form-' ) );
?>

<div class="search-modal cover-modal" data-modal-target-string=".search-modal" aria-expanded="false">
	<div class="search-modal-inner modal-inner">
		<div class="section-inner">

			<form role="search" method="get" class="modal-search-form" action="<?php echo esc_url( home_url( '/' ) ); ?>">
				<input type="search" id="<?php echo esc_attr( $unique_id ); ?>" class="search-field" placeholder="<?php esc_attr_e( 'Search For&hellip;', 'pol' ); ?>" value="<?php echo get_search_query(); ?>" name="s" />
				<label class="search-label" for="<?php echo esc_attr( $unique_id ); ?>">
					<span class="screen-reader-text"><?php esc_html_e( 'Search For&hellip;', 'pol' ); ?></span>
					<?php pol_the_icon_svg( 'ui', 'search', 24, 24 ); ?>
				</label>
				<button type="submit" class="search-submit"><?php echo esc_html_x( 'Search', 'Submit button', 'pol' ); ?></button>
			</form><!-- .modal-search-form -->

			<a href="#" class="toggle search-untoggle" data-toggle-target=".search-modal" data-toggle-screen-lock="true" data-toggle-body-class="showing-search-modal" data-set-focus="#site-header .search-toggle">
				<span class="screen-reader-text"><?php esc_html_e( 'Close', 'pol' ); ?></span>
				<div class="search-untoggle-inner">
					<?php pol_the_icon_svg( 'ui', 'close', 18, 18 ); ?>
				</div><!-- .search-untoggle-inner -->
			</a><!-- .search-untoggle -->

		</div><!-- .section-inner -->
	</div><!-- .search-modal-inner -->
</div><!-- .search-modal -->