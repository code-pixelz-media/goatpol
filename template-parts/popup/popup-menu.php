<?php
/**
 * Displays the popup logo.
 *
 * @package GOAT PoL
 */

// Close
$close = true;

?>
<style>
	#story-popup-content p{
		margin-top: 0 !important;
	}
</style>
<div id="menu-popup-content"  class="adp-popup pop1 adp-popup-type-content adp-popup-location-center adp-preview-image-none adp-preview-image-no" data-limit-display="0" data-limit-lifetime="30" data-open-trigger="<?php echo esc_attr( $method ); ?>" data-open-delay-number="0" data-open-scroll-position="10" data-open-scroll-type="%" data-open-manual-selector="a[href^='#tellusyourstory']" data-close-trigger="none" data-close-delay-number="30" data-close-scroll-position="10" data-close-scroll-type="%" data-open-animation="popupOpenSlideFade" data-exit-animation="popupExitSlideFade" data-light-close="false" data-overlay="true" data-mobile-disable="false" data-body-scroll-disable="true" data-overlay-close="false" data-esc-close="<?php echo esc_attr( $close ); ?>" data-f4-close="false">
	<div class="adp-popup-wrap">
		<div class="adp-popup-container">
			<div class="adp-popup-outer">
				<div class="adp-popup-content" style="padding: 50px 24px;">
					<div class="adp-popup-inner">

						<div class="wp-block-image is-style-no-vertical-margin" style="font-size: 26px;">
							<?php
								$nom_de_plume = get_field( 'story_nom_de_plume' );
								echo $nom_de_plume;
							?>'s stories
						</div>
                        
	
						<div class="has-lg-font-size">

<?php echo 'Ground Rules: We welcome every writer. We don’t know if you feel disenfranchised or not. We welcome everyone, please try us. If you submit a story asking “Work with me first…” we must finish and publish that story before you can submit a new one. One story at a time.'; ?>
						</div>			
					</div>

					<?php if( $close ) : ?>
						<button type="button" class="adp-popup-close logo-popup-close"></button>
					<?php endif; ?>

				</div>
			</div>
			
		</div>
	</div>
</div>
<div class="adp-popup-overlay"></div>