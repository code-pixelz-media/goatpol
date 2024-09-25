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
<div id="story-popup-content"  class="adp-popup pop3 adp-popup-type-content adp-popup-location-center adp-preview-image-none adp-preview-image-no" data-limit-display="0" data-limit-lifetime="30" data-open-trigger="<?php echo esc_attr( $method ); ?>" data-open-delay-number="0" data-open-scroll-position="10" data-open-scroll-type="%" data-open-manual-selector="a[href^='#tellusyourstory']" data-close-trigger="none" data-close-delay-number="30" data-close-scroll-position="10" data-close-scroll-type="%" data-open-animation="popupOpenSlideFade" data-exit-animation="popupExitSlideFade" data-light-close="false" data-overlay="true" data-mobile-disable="false" data-body-scroll-disable="true" data-overlay-close="false" data-esc-close="<?php echo esc_attr( $close ); ?>" data-f4-close="false">
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
                        
						<?php
							global $current_user; 
							$nom_de_plume = get_field( 'story_nom_de_plume' );
							$author = get_post_field( 'post_author', get_the_ID());
							// var_dump($author);
							if ( is_user_logged_in() ) {
							$loggedin_user = get_current_user_id();   
							}
							
						?>
						<div class="has-lg-font-size">
							<?php 
								$args = array(
								'post_type' => 'story',
								'author'    =>  strval($author),
								'posts_per_page' => 101,
								'post_status' => array('draft','pending','publish'),
								);
								$query = new WP_Query( $args ); 
								//echo $wpdb->last_query;
							?>
							<ul class="four-col">
								<?php 
								if( $query->have_posts()) {
									while( $query->have_posts() ) : $query->the_post(); 
										if(get_the_author_ID() == strval($author)):
									$location = get_post_meta(get_the_id(),'story_place_name',true);
	                $place_id = get_post_meta(get_the_ID(), 'stories_place', true);
	                $locs = get_the_title($place_id);
								?>
								<li>
									<?php 
									$image = get_the_post_thumbnail_url();
									if($image){ ?>
										<div class="image-warp">
											<a href="<?php the_permalink(); ?>">
												<img src="<?php echo $image; ?>">
											</a>
									</div>
									<?php
									} else{
									?>
									<div class="image-warp">
										<a href="<?php the_permalink(); ?>">
										<?php echo pol_get_random_goat(); ?>
										</a>
									</div>
									<?php } ?>
									<div class="blog-article">
										<h5>
											<a href="<?php the_permalink(); ?>"><?php the_title(); ?> </a>
										</h5>
										<?php if(!empty($locs)) : ?>
											<a href="<?php the_permalink(); ?>"><?=$locs;?></a>
										<?php endif; ?>
									</div>
								</li>
								<?php 
								endif;
								endwhile;

								} 
								else {
									_e('Posts Not Found' ,'pol');
								}
								wp_reset_postdata(); 
								?>
							</ul>
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