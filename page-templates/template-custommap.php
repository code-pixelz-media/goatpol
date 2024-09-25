<?php
/**
 * Template Name: Custom Map
 * 
 * Uses the same content structure as the default singular.php template, 
 * but adds a form if selected.
 * 
 * @package GOAT PoL
 */

get_header();

?>

<main id="site-content" role="main">
	<div class="site-content-inner">
		<?php
		while ( have_posts() ) {
			the_post();
			get_template_part( 'template-parts/single/content', 'custommap' );
		}
		?>
	</div><!-- .site-content-inner -->
</main><!-- #site-content -->

<?php 
get_footer();