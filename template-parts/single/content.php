<?php
/**
 * Displays the single post content.
 *
 * @package GOAT PoL
 */

?>

<article tabindex="1" <?php post_class(); ?> id="post-<?php the_ID(); ?>">
	<?php get_template_part( 'template-parts/single/entry-header', get_post_type() ); ?>

	<div id="post-content" class="post-inner">
		<div class="section-inner do-spot spot-fade-up a-del-200">
			<div class="section-inner max-percentage no-margin mw-thin">
				<?php get_template_part( 'template-parts/single/entry-content', get_post_type() ); ?>
				<?php get_template_part( 'template-parts/single/entry-footer', get_post_type() ); ?>
			</div>
		</div><!-- .section-inner -->
	</div><!-- .post-inner -->
	
</article><!-- .post -->