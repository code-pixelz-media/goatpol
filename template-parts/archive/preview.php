<?php
/**
 * Displays the post content in archives and search results.
 *
 * @package GOAT PoL
 */

?>

<article <?php post_class( 'preview do-spot spot-fade-up a-del-200 preview-' . get_post_type() ); ?> id="post-<?php the_ID(); ?>">

	<?php if ( has_post_thumbnail() && ! post_password_required() ) : ?>
		<figure class="preview-media">
			<a href="<?php the_permalink(); ?>" class="preview-media-link">
				<?php 
				the_post_thumbnail( 'large' );

				if ( is_sticky() ) {
					echo '<div class="sticky-note">' . esc_html__( 'Featured', 'pol' ) . '</div>';
				}
				?>
			</a><!-- .preview-media-link -->
		</figure><!-- .preview-media -->
	<?php endif; ?>

	<header class="preview-header">
		<h2 class="preview-title h4"><a href="<?php echo esc_url( get_permalink() ); ?>"><?php the_title(); ?></a></h2>
	</header><!-- .preview-header -->

	<?php if( has_excerpt() ) : ?>
		<div class="preview-excerpt">
			<?php the_excerpt(); ?>
		</div><!-- .preview-content -->
	<?php endif; ?>

	<?php pol_the_post_meta( 'archive' ); ?>

</article><!-- .preview -->