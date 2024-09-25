<?php

/**
 * Displays the single post content on Form Page templates.
 *
 * @package GOAT PoL
 */

global $wp_query;
global $current_user;
$messages = '';

$form_id 				= get_field( 'form_id' );
$form_submit 			= get_field( 'form_submit_button' );
$form_action 			= get_field( 'form_submit_action' );
$form_attach_id 		= get_field( 'form_attach_id' ) == 1 ? true : false;
$form_redirect 			= 'redirect' == $form_action ? get_field( 'form_redirect_page' ) : false;
$place_id				= get_query_var( 'place_id' );
$new_place				= get_query_var( 'new_place' );

//pages paths
$add_story_path     	= get_page_by_path('add-story');
$add_place_path  		= get_page_by_path('add-place');
$add_new_location_path	= get_page_by_path('add-location');
$finish_sory_path       = get_page_by_path('complete-story');
$fl 					= get_field( 'place_location',$place_id);


$location_fl     = ($add_story_path->ID == get_the_id() && !empty($fl)) ? $fl['place_map']['address'] : false;

if( 'post' == $form_action ) {
	$form_redirect = 'post';
}

$form_args = array(
	'attach_id' 			=> false,
	'new_place'				=> false,

);

if(get_the_ID() == $add_place_path->ID){
	$exc_list = array(
		'place_type', 
		'place_fields',  
		'place_physical_location', 
		'place_location' ,
		'place_type',
		'place_access',
		'place_languages',
		'place_attributes'
	);
	// if(is_user_logged_in()) {
	// 	global $wpdb;
	// 	$show_form = false;
		

	// 	// Get the user object.
	// 	$user = get_userdata( $current_user->ID);

	// 	// Get all the user roles as an array.
	// 	$user_roles = $user->roles;

	// 	// Check if the User is Author
	// 	if ( in_array( 'author', $user_roles, true ) ) {
	// 		$query = $wpdb->get_results( "SELECT ID FROM $wpdb->posts WHERE post_type = 'story' AND post_status = 'draft' AND post_author = ".$current_user->ID." ORDER BY post_modified DESC" );
	// 		if(!empty($query)){
	// 			$messages .= '<br/><div class="has-lg-font-size" style="margin-bottom:20px;">';
	// 			$messages .= '<b><i>Dear '.$current_user->first_name .' , We are still working on your story, '.get_the_title($query[0]->ID).'. Please wait until that story has been published before submitting more work to us. We welcome your new work after we have completed the publication of '.get_the_title($query[0]->ID).' .</i></b>';
	// 			$messages .= '</div><br />';
	// 		}else{
	// 			$show_form = true;
	// 		}
			
	// 	}else{
	// 		$show_form = true;
	// 	}		
	// }
	$form_args['exclude_fields'] = $exc_list;
}elseif( get_the_ID() == $add_new_location_path->ID){
	$exc_list = array(
		'place_type', 
		'place_fields',  
		'place_physical_location', 
		'where_does',
		'place_type',
		'place_access',
		'place_languages',
		'place_attributes'
	);
	$form_args['exclude_fields'] = $exc_list;
}elseif(get_the_ID() == $add_story_path->ID){
	
	if(!isset($fl))
		$form_args['exclude_fields'] = array('story_place_name'  );

	$where =  get_field('where_does' ,$place_id);
	if($where != 'about_internet'){
		$form_args['exclude_fields'] = array('web_url' );
	}

	
}else{
	$form_args['exclude_fields'] = array( );
}

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
	if(!empty($location_fl)) :
		$form_args['values']['story_place_name'] =$location_fl;
	else:
		$form_args['values']['story_place_name'] = html_entity_decode( get_the_title( $place_id ) );
	endif;

	// Only pre-fill these values if it's a new place.
	if( $new_place ) {
		$form_args['values']['place_type']  	 = get_field( 'place_type', $place_id );
		$form_args['values']['place_access']  	 = get_field( 'place_access', $place_id );
		$form_args['values']['place_languages']  = get_field( 'place_languages', $place_id );
		$form_args['values']['place_attributes'] = get_field( 'place_attributes', $place_id );
    	$form_args['values']['story_featured'] 	 = 1;
	}
}



if(is_user_logged_in()){
	
	$form_args['values']['story_email_address'] =  $current_user->user_email;
	$form_args['values']['story_nom_de_plume']  = $current_user->first_name . ' ' . $current_user->last_name;

}
?>

<article <?php post_class(); ?> id="post-<?php the_ID(); ?>">

	<?php get_template_part( 'template-parts/single/entry-header' ); ?>
	<div id="post-content" class="post-inner">
		<div class="section-inner do-spot spot-fade-up a-del-200">
			<div class="section-inner max-percentage no-margin mw-thin">
				<div class="entry-content">

					<?php
					if(  $show_form == false ) {

						echo $messages;

					}
					?>
					<div class="has-lg-font-size">
						<?php the_content(); ?>
					</div>

					<?php
					if( $form_id) {
						advanced_form( $form_id, $form_args );
					}
					?>
				</div><!-- .entry-content -->
			</div>
		</div><!-- .section-inner -->
	</div><!-- .post-inner -->
	
</article><!-- .post -->