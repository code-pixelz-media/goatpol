<?php
/**
 * Template Name: My Stories
 * 
 * Displays the stories of the logged in user.
 * 
 * @package GOAT PoL
 */

get_header();

global $current_user; 
if ( is_user_logged_in() ) {
$loggedin_user = get_current_user_id();   
}	
?>
<article <?php post_class(); ?> id="post-<?php the_ID(); ?>">
	<?php get_template_part( 'template-parts/single/entry-header' ); ?>
	<div id="post-content" class="post-inner">
		<div class="section-inner do-spot spot-fade-up a-del-200">
			<div class="section-inner max-percentage no-margin">
				<div class="entry-content">
					<div class="has-lg-font-size">
						<?php 
							$args = array(
							'post_type' => 'story',
							'author'    =>  $loggedin_user,
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
									if(get_the_author_ID() == $loggedin_user):
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
										<a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
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
				</div><!-- .entry-content -->
			</div>
		</div><!-- .section-inner -->
	</div><!-- .post-inner -->
</article><!-- .post -->


<?php 
get_footer();