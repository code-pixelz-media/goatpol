<?php
/**
 * Template Name: Update Story 
 * 
 * Displays the homepage map with place markers.
 * 
 * @package GOAT PoL
 */
get_header();
$form_id 			= get_field( 'form_id' );
$form_submit 		= get_field( 'form_submit_button' );
$form_action 		= get_field( 'form_submit_action' );
$form_attach_id 	= get_field( 'form_attach_id' ) == 1 ? true : false;
$form_redirect 		= 'redirect' == $form_action ? get_field( 'form_redirect_page' ) : false;



$storyIds = isset($_GET['story-edit']) ? pol_encrypt_decrypt($_GET['story-edit'] , false) : false;
$place_id = isset($_GET['place-edit']) ? pol_encrypt_decrypt($_GET['place-edit'] , false) : false;

if(!is_wp_error($storyIds) && !is_wp_error($place_id)){

	if ( $form_redirect ) {
		$form_args['redirect'] = $form_redirect.'?story-id='.$_GET['story-edit'];
		$form_args['ajax'] 		 = false;
	
		
	}
	//place name
    $form_args['values']['story_place_name']  		= get_the_title($place_id);

    $form_args['values']['story_title']             = get_field('story_title' ,$storyIds);
	//story type
    $form_args['values']['story_type']      		= get_field('story_type',$storyIds);
	//story_nom_de_plume writers name
	$form_args['values']['story_nom_de_plume']      = get_field('story_nom_de_plume',$storyIds);

	$form_args['values']['story_type_labels']       = get_field('story_type_labels',$storyIds);
	//email address
	$form_args['values']['story_email_address'] 	= get_field('story_email_address' , $storyIds);

	//terms and condition
	$form_args['values']['story_accept_terms'] 		= get_field('story_accept_terms', $storyIds);
	//whole story
	$form_args['values']['story'] 					= get_field('story',$storyIds);

	$form_args['values']['where_does']  			=  get_field('where_does',$place_id);

	//featured image
	$form_args['values']['story_featured_image'] 	= get_field('story_featured_image',$storyIds);

	$form_args['values']['place_type']           	= get_field('place_type',$place_id);

	$form_args['values']['place_access']           	= get_field('place_access',$place_id);

	$form_args['values']['place_languages']         = get_field('place_languages',$place_id);

	$form_args['values']['place_attributes']        = get_field('place_attributes',$place_id);

	$form_args['values']['place_location']			= get_field('place_location',$place_id);


	$where_story = get_field('where_does',$place_id);


	if($where_story != 'geo_loc' ){
		$form_args['exclude_fields'] = array('place_location','story_place_name' ,'story_accept_terms','place_type','place_access','place_languages','place_attributes' );
	}else{
		$form_args['exclude_fields'] = array('story_accept_terms');
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
<script>

	jQuery('.acf-radio-list').first().find('input[type=radio]').prop('disabled', true)
</script>
<?php
get_footer();