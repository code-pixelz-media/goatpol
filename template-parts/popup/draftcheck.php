<?php
/**
 * Displays the popup for menu.
 *
 * @package GOAT PoL
 */

// Close
$close = true;

?>
<style>
	#menu-popup-content p{
		margin-top: 0 !important;
	}
</style>
<div id="menu-popup-check-draft"  class="adp-popup pop2 adp-popup-type-content adp-popup-location-center adp-preview-image-none adp-preview-image-no" data-limit-display="0" data-limit-lifetime="30" data-open-trigger="<?php echo esc_attr( $method ); ?>" data-open-delay-number="0" data-open-scroll-position="10" data-open-scroll-type="%" data-open-manual-selector="a[href^='#tellusyourstory']" data-close-trigger="none" data-close-delay-number="30" data-close-scroll-position="10" data-close-scroll-type="%" data-open-animation="popupOpenSlideFade" data-exit-animation="popupExitSlideFade" data-light-close="false" data-overlay="true" data-mobile-disable="false" data-body-scroll-disable="true" data-overlay-close="false" data-esc-close="<?php echo esc_attr( $close ); ?>" data-f4-close="false">
	<div class="adp-popup-wrap" style="height: 40%;">
		<div class="adp-popup-container">
			<div class="adp-popup-outer">
				<div class="adp-popup-content" style="padding: 50px 24px;">
					<div class="adp-popup-inner">
						<div class="has-text-align-center wp-block-image is-style-no-vertical-margin" style="font-size: 26px;text-align:center">
								<!-- <h2>Ground Rules</h2> -->
						</div>
						<div class="wp-block-image is-style-no-vertical-margin">
								<?php pol_the_random_goat( 'popup-goat' ); ?>
						</div>

						<p class="has-text-align-center has-h5-font-size has-h6-line-height"><?php echo pol_check_users_drafts()[1];?></p>
		
					</div>

					<?php if( $close ) : ?>
						<button type="button" class="adp-popup-close menu-popup-check-draft-close"></button>
					<?php endif; ?>

				</div>
			</div>
			
		</div>
	</div>
</div>
<div class="adp-popup-overlay"></div>