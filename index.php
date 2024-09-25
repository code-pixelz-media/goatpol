<?php
/**
 * The main template file.
 *
 * @package GOAT PoL
 */

get_header(); 
?>

<main id="site-content" role="main">
	<div class="site-content-inner">

		<?php get_template_part( 'template-parts/archive/archive-header' ); ?>

		<?php if ( have_posts() ) : ?>

			<?php get_template_part( 'template-parts/archive/archive-posts' ); ?>
			<?php get_template_part( 'template-parts/archive/pagination' ); ?>
			
		<?php elseif ( is_search() ) : ?>

			<?php get_template_part( 'template-parts/archive/no-results' ); ?>

		<?php endif; ?>

	</div><!-- .site-content-inner -->
</main><!-- #site-content -->

<?php
get_footer();