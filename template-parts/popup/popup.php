<?php
/**
 * Displays the popup guides.
 *
 * @package GOAT PoL
 */

// Messages
$map_message  		= get_field( 'map_message', 'options' );
$wrong_location   = get_field( 'wrong_location_message', 'options' );
$choose_location  = get_field( 'choose_location_message', 'options' );
$new_location  		= get_field( 'new_location_message', 'options' );
$default_message  = get_field( 'fallback_message', 'options' );

// Method
$method = 'manual';

// Close
$close = true;

$add_story  = get_page_by_path( 'add-story' );
$add_loc    = get_page_by_path('add-location');
$add_place  = get_page_by_path( 'add-place' );
$place_id	  = get_query_var( 'place_id' );
$new_place  = get_query_var( 'new_place' );
$geo_loc  =  get_page_by_path( 'geographical-location' );

// $message = 'If your Story is about a place already marked on this map, Click on it's marker '

// if( $add_story->ID == get_the_ID() ) {
//   if( isset( $place_id ) && $place_id ) {
//     $message = $wrong_location;
//   } else {
//     $message = $choose_location;
// 		$method  = 'delay';
// 		$close = false;
//   }
// } elseif( $add_place->ID == get_the_ID() ) {
// 	$message = $new_location;
// } elseif( is_page_template( 'page-templates/template-map.php' ) ) {
// 	$message = $map_message;
// } else {
// 	$message = $default_message;
// }

?>
<div class="adp-popup adp-popup-type-content adp-popup-location-center adp-preview-image-none adp-preview-image-no" data-limit-display="0" data-limit-lifetime="30" data-open-trigger="<?php echo esc_attr( $method ); ?>" data-open-delay-number="0" data-open-scroll-position="10" data-open-scroll-type="%" data-open-manual-selector="a[href^='#tellusyourstory']" data-close-trigger="none" data-close-delay-number="30" data-close-scroll-position="10" data-close-scroll-type="%" data-open-animation="popupOpenSlideFade" data-exit-animation="popupExitSlideFade" data-light-close="false" data-overlay="true" data-mobile-disable="false" data-body-scroll-disable="true" data-overlay-close="false" data-esc-close="<?php echo esc_attr( $close ); ?>" data-f4-close="false">
	<div class="adp-popup-wrap">
		<div class="adp-popup-container">
			<div class="adp-popup-outer">
				<div class="adp-popup-content">
					<div class="adp-popup-inner">

						<div class="wp-block-image is-style-no-vertical-margin">
							<?php pol_the_random_goat( 'popup-goat' ); ?>
						</div>

						<p class="has-text-align-center has-h5-font-size has-h6-line-height"><?php echo 'If your story is about a place already marked on this map,
click on "Go back to the map" and click on the marker, then select “Add a new story about this place”.
Otherwise, you can start by adding a new location.';?></p>
						<div class="wp-block-buttons flex align-items-center justify-content-center">

<?php if( is_page_template( 'page-templates/template-map.php' ) ) : ?>
                                <div class="wp-block-button">
                                    <button class="button wp-block-button__link adp-popup-close" href="<?php echo esc_url( home_url( '/' ) ); ?>">Go Back to Map</button>
                                </div>
                            <?php else : ?>
                                <div class="wp-block-button">
                                    <a class="wp-block-button__link" href="<?php echo esc_url( home_url( '/' ) ); ?>">Go Back To Map</a>
                                </div>
                            <?php endif; ?>

							<?php if( $add_place || $add_story ) : ?>
								<?php if( $add_place->ID == get_the_ID() || $add_story->ID == get_the_id() ) : ?>
									<div class="wp-block-button set-cookie">
										<a class="wp-block-button__link" href="<?php echo esc_url( get_permalink( $add_loc->ID ) ); ?>">Add a New Location</a>
									</div>
								<?php else : ?>
									
								<?php endif; ?>
							<?php endif; ?>

						</div>				
					</div>

					<?php if( $close ) : ?>
						<button type="button" class="adp-popup-close"></button>
					<?php endif; ?>

				</div>
			</div>
			
		</div>
	</div>
</div>
<div class="adp-popup-overlay"></div>
