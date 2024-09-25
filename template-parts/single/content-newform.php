<?php
/**
 * Displays the single post content on Form Page DR templates.
 *
 * @package GOAT PoL
 */
 
global $wp_query;
$form_id 				= get_field( 'form_id' );
$form_submit 		= get_field( 'form_submit_button' );
$form_action 		= get_field( 'form_submit_action' );
$form_attach_id = get_field( 'form_attach_id' ) == 1 ? true : false;
$form_redirect 	= 'redirect' == $form_action ? get_field( 'form_redirect_page' ) : false;
$place_id				= get_query_var( 'place_id' );
$new_place			= get_query_var( 'new_place' );

if( 'post' == $form_action ) {
	$form_redirect = 'post';
}

$form_args = array(
	'attach_id' 			=> false,
	'new_place'				=> false,
	'exclude_fields' 	=> array(  'place_stories',  'story_type', 'field_6234cd1e60635', 'field_6234e07760da0' ),
);

// Submit button text
if ( $form_submit ) {
	$form_args['submit_text'] = $form_submit;
}

// Custom redirect
if ( $form_redirect ) {
	$form_args['redirect'] = $form_redirect;
	$form_args['ajax'] 		 = false;

	// Pass the new post ID as a custom query arg.
	if( $form_attach_id ) {
		$form_args['attach_id'] = true;
	}
}

if( $new_place ) {
	$form_args['new_place'] = true;
}

// Check if we have a place_id query var passed along.
if( $place_id ) {
	$form_args['values']['story_place'] 		 = $place_id;
	$form_args['values']['story_place_name'] = html_entity_decode( get_the_title( $place_id ) );

	// Only pre-fill these values if it's a new place.
	if( $new_place ) {
		$form_args['values']['place_type']  		 = get_field( 'place_type', $place_id );
		$form_args['values']['place_access']  	 = get_field( 'place_access', $place_id );
		$form_args['values']['place_languages']  = get_field( 'place_languages', $place_id );
		$form_args['values']['place_attributes'] = get_field( 'place_attributes', $place_id );
    $form_args['values']['story_featured'] 	 = 1;
	}
}
?>

<article <?php post_class(); ?> id="post-<?php the_ID(); ?>">

	<?php get_template_part( 'template-parts/single/entry-header' ); ?>

	<div id="post-content" class="post-inner">
		<div class="section-inner do-spot spot-fade-up a-del-200">
			<div class="section-inner max-percentage no-margin mw-thin">

				<div class="entry-content">
					<div class="has-lg-font-size">
						<?php the_content(); ?>
					</div>

					<?php
					if( $form_id ) {
						advanced_form( $form_id, $form_args );
					}
					?>

				</div><!-- .entry-content -->

			</div>
		</div><!-- .section-inner -->
	</div><!-- .post-inner -->
	
</article><!-- .post -->