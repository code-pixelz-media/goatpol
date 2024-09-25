<?php
/**
 * Template Name: Archive
 * 
 * Matches the structure of index.php and used to output a custom archive.
 * 
 * @package GOAT PoL
 */

get_header(); 

$paged = get_query_var( 'paged' ) ? get_query_var( 'paged' ) : ( get_query_var( 'page' ) ? get_query_var( 'page' ) : 1 );

$custom_query = new WP_Query( array(
	'paged'			=> $paged,
	'post_type'	=> 'project',
) );
?>

<main id="site-content" role="main">
	<div class="site-content-inner">

		<?php get_template_part( 'template-parts/archive/archive-header' ); ?>

		<?php	if ( $custom_query->have_posts() ) : ?>

			<?php get_template_part( 'template-parts/archive/archive-posts', null, array( 'custom_query' => $custom_query ) ); ?>
			<?php get_template_part( 'template-parts/archive/pagination', null, array( 'custom_query' => $custom_query ) ); ?>

		<?php endif; ?>

	</div><!-- .site-content-inner -->
</main><!-- #site-content -->

<?php
get_footer();