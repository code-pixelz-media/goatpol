<?php 
/**
 * Template Name: Finish Publishing
 * 
 * Uses the same content structure as the default singular.php template, 
 * but adds a form if selected.
 * 
 * @package GOAT PoL
 */

get_header();
$form_id 				= get_field( 'form_id' );
$form_args['exclude_fields']= array(  'where_does',  'place_physical_location', 'place_location' );
$form_submit 		= get_field( 'form_submit_button' );
$form_action 		= get_field( 'form_submit_action' );
$form_redirect 		= 'redirect' == $form_action ? get_field( 'form_redirect_page' ) : false;

if ( $form_redirect ) {

   
    $form_args['redirect']   = $form_redirect;
    $form_args['ajax'] 		 = false;

    // Pass the new post ID as a custom query arg.
    // if( $form_attach_id ) {
    // 	$form_args['attach_id'] = true;
    // }
}
?>
<article <?php post_class(); ?> id="post-<?php the_ID(); ?>">

<?php get_template_part( 'template-parts/single/entry-header' ); ?>

<div id="post-content" class="post-inner">
	<div class="section-inner do-spot spot-fade-up a-del-200">
		<div class="section-inner max-percentage no-margin mw-thin">
			<div class="entry-content">
				<div class="tets">
					<b>Here is the story you've asked us to publish. To complete the process please <?php if( $form_id && $place_loc_type=='geo_loc' ) { ?>answer any of the questions you can, listed below your story. Then<?php } ?> click 'FINISHED!' If you need to edit or change your story, click on 'RETURN TO EDIT STORY.' Thanks! The GOAT</b>
				</div>
				<br>
				<div class="has-lg-font-size">
					<?php echo $story;?>
				</div>

				<?php
				
				if( $form_id ) {

					advanced_form( $form_id, $form_args );
				}


				//$actual_link = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
				?>
			</div><!-- .entry-content -->
		</div>
	</div><!-- .section-inner -->
</div><!-- .post-inner -->

</article><!-- .post -->

<?php


get_footer();