<?php
/**
 * Displays the entry footer.
 *
 * @package GOAT PoL
 */

// Only output on single posts.
if( ! is_singular( 'post' ) ) {
  return;
}
?>

<footer class="entry-footer">
  <?php pol_the_post_meta( 'single' ); ?>
</footer><!-- .entry-footer -->