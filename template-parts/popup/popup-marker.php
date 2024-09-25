<?php
/**
 * Displays the map marker popup.
 *
 * @package GOAT PoL
 */

$place_types = '';
if( isset( $args['place_type'] ) ) {
  $place_types = pol_get_top_values( get_field('place_type',$args['ID']), 3 );
}

$place_info = array();
if( isset( $args['place_physical_location'] ) && $args['place_physical_location'] ) {

  if( $args['place_location']['place_map']['address'] ) {
    $address = $args['place_location']['place_map']['address'];
  } elseif ( $args['place_location']['place_lat'] && $args['place_location']['place_lng'] ) {

    $latlng = array(
      'lat' => substr($args['place_location']['place_lat'], 0, 14),
      'lng' => substr($args['place_location']['place_lng'], 0, 14),
    );

    $address = join( ', ', $latlng );
  }

  if( $address ) {
    $place_info[] = sprintf( '<span class="icon">%s</span> %s', pol_get_icon_svg( 'ionicons', 'location-sharp', 20, 20 ), $address );
  }
}
if( isset( $args['place_has_website'] ) && $args['place_has_website'] && isset( $args['place_website'] ) && $args['place_website'] ) {
  $website = $args['place_website'];
  $place_info[] = sprintf( '<span class="icon">%s</span> <a href="%s" target="_blank">%s</a>', pol_get_icon_svg( 'ionicons', 'globe-sharp', 18, 18 ), esc_url( $website ), $website );
}
if( isset( $args['place_has_phone_number'] ) && $args['place_has_phone_number'] && isset( $args['place_phone'] ) && $args['place_phone'] ) {
  $phone = $args['place_phone'];
  $place_info[] = sprintf( '<span class="icon">%s</span> %s', pol_get_icon_svg( 'ionicons', 'call-sharp', 18, 18 ), $phone );
}

$place_languages = '';

if( isset( $args['place_languages'] ) ) {
   $place_languages = pol_get_top_values(  get_field('place_languages',$args['ID']), 3 );
  
}

$place_access = '';
if( isset( $args['place_access'] ) ) {
  if( in_array( 'None of the above', $args['place_access'] ) ) {
    $place_access = array( 'Not welcoming' );
    $icon         = pol_get_icon_svg( 'ionicons', 'close-circle', 12, 12 );
    $icon_color   = 'red';
  } else {
    $place_access = pol_get_top_values( get_field('place_access',$args['ID']), 3);
    $icon         = pol_get_icon_svg( 'ionicons', 'checkmark-circle', 12, 12 );
    $icon_color   = 'green';
  }
}

$place_attributes = '';
if( isset( $args['place_attributes'] ) ) {
  $place_attributes = pol_get_top_values(  get_field('place_attributes',$args['ID']), 3 );
}


// $place_stories = '';
// if( isset( $args['place_stories'] ) ) {
//   $place_stories = $args['place_stories'];
// }

// $story_types = get_terms( array(
//   'taxonomy' => 'story_type',
// ) );

// $story_list = array();

// if( $place_stories && $story_types ) {
//   foreach( $story_types as $story_type ) {
//     foreach( $place_stories[$story_type->slug] as $story ) {
//       $story_list[] = $story;
//     }
//   }
// }

// // Make sure the featured story is first.
// if( $story_list ) {
//   foreach( $story_list as $i => $story ) {

//     $is_featured = get_field( 'story_featured', $story->ID ) == 1 ? true : false;
    
//     if( $is_featured ) {
//       $featured_story = $story;
//       unset( $story_list[ $i ] );
//       array_unshift( $story_list, $story );
//     }
//   }
// }



$stories_all    = get_field('place_stories', $args['ID'], false);
$path           = get_page_by_path('add-story'); 
$add_story_url  = get_permalink( $path->ID ) . '?place_id=' . $args['ID'];
?>

<div id="infowindow" class="marker-popup">
  <div class="marker-popup-inner">
    <?php 
    $lat      = get_post_meta($args['ID'] , 'place_location_place_lat' , true);
    $lng      = get_post_meta($args['ID'] , 'place_location_place_lng' , true);

    // echo  get_the_title($args['ID']);

    // echo get_post_status($args['ID']);

    // echo  get_post_type($args['ID']);

    // var_dump($lat , $lng , $args['ID'])
    ?>
    <?php if( isset( $args['name'] ) ) : ?>
      <div class="place-title">
        <h2 class="h4 place-name"><?php echo $args['name']; ?></h2>
      </div><!-- .place-title -->
    <?php endif; ?>

    <?php if( $place_types ) : ?>
      <div class="place-type has-secondary-color">
        <?php echo join( ' • ', $place_types ); ?>
      </div><!-- .place-type -->
    <?php endif; ?>

    <?php if( $place_access ) : ?>
      <div class="place-access">
        <ul class="reset-list-style">
          <li class="<?php echo esc_attr( $icon_color ); ?>"><?php echo $icon . join( '</li><li class="' . esc_attr( $icon_color ) . '">' . $icon, $place_access ); ?></li>
        </ul>
      </div><!-- .place-access -->
    <?php endif; ?>

    <div class="row popup-row">
      <div class="row-inner">

        <?php if( $place_info || $place_attributes || $place_languages ) : ?>
          <div class="place-info-group">
            <div class="place-group-inner place-info-inner">
              <div class="place-info-items">

                <?php if( $place_info ) : ?>
                  <div class="place-info-item place-location">
                    <h3 class="is-style-prefix-small"><?php echo esc_html( 'Location' ); ?></h3>
                    <ul class="reset-list-style">
                      <li><?php echo join( '</li><li>', $place_info ); ?></li>
                    </ul>
                  </div><!-- .place-location -->
                <?php endif; ?>

                <?php if( $place_languages ) : ?>
                  <div class="place-info-item place-languages">
                    <h3 class="is-style-prefix-small"><?php echo esc_html( 'Languages' ); ?></h3>
                    <div class="languages">
                      <?php echo join( ', ', $place_languages ); ?>
                    </div>
                  </div><!-- .place-languages -->
                <?php endif; ?>

                <?php if( $place_attributes ) : ?>
                  <div class="place-info-item place-attributes">
                    <h3 class="is-style-prefix-small"><?php echo esc_html( 'This place is good if...' ); ?></h3>
                    <ul>
                      <li><?php echo join( '</li><li>', $place_attributes ); ?></li>
                    </ul>
                  </div><!-- .place-attributes -->
                <?php endif; ?>

              </div><!-- .place-info-items  -->
            </div><!-- .place-group-inner -->
          </div><!-- .place-info-group -->
        <?php endif; ?>

        <div class="place-story-group">
          <div class="place-group-inner place-story-inner test-1231">
                 
            <?php if( $stories_all ) :
               $all_stories = array();
               foreach ($stories_all as $story) {
                 $date = get_the_time('Y-m-d H:i', $story);
                 array_push($all_stories, array('ID' => $story, 'date' => $date));
               }
 
               usort($all_stories, function ($a, $b) {
                 if ($a['date'] == $b['date']) return 0;
                 return $a['date'] < $b['date'] ? 1 : -1;
               });
 
              //  echo '<div style="display:none;">';
              //  var_dump($all_stories);
              //  echo '</div>';

                ?>
              <div class="place-stories">

                <?php
                // $stories_reversed = array_reverse($stories_all);
                $stories_all = $all_stories;
                foreach( $stories_all as $story ) : 
                  $story = $story["ID"];
                  if (empty(get_the_title($story))) {
                    continue;
                  }

                  if(get_post_status($story) == 'publish' && get_post_type($story) == 'story'){

                  
                  $nom_de_plume = get_field( 'story_nom_de_plume', $story );
                  $is_featured  = get_field( 'story_featured', $story ) == 1 ? true : false;
                  $story_type   = get_the_terms( $story, 'story_type' );

                  // Pills
                  $pills = array();
                  if( $is_featured ) {
                    //$pills[] = '<li class="pill featured">' . esc_html( 'Featured Story' ) . '</li>';

                  } elseif( $story_type ) {
                    //$pills[] = '<li class="pill">' . $story_type[0]->name . '</li>';
                  }
                  ?>
                  
                  <div class="place-story" data-id="<?php echo $story; ?>" data-url="<?php echo esc_url( get_permalink( $story) ); ?>">
                    <a class="place-story-link" href="<?php echo esc_url( get_permalink( $story) ); ?>">

                      <div class="place-story-title">
                      
                        <h3 class="h6"><?php echo get_the_title( $story ); ?></h3>

                        <?php if( $nom_de_plume ) : ?>
                          <p class="nom-de-plume"><?php printf( 'By %s', $nom_de_plume ); ?></p>
                        <?php endif; ?>
                      </div><!-- .place-story-title -->

                      <?php if( $pills ) : ?>
                        <div class="place-story-pills">
                          <ul class="pill-list reset-list-style">
                            <?php echo join( '', $pills  ); ?>
                          </ul>
                        </div><!-- .place-story-pills -->
                      <?php endif; ?>

                    </a><!-- .place-story-inner -->
                  </div><!-- .place-story -->

                <?php } endforeach; ?>

              </div><!-- .place-stories -->
            <?php endif; ?>

            <div class="place-add-story">
              <a href="<?php echo esc_url( $add_story_url ); ?>" class="button add-story-button"><?php echo esc_html( 'Add a New Story About This Place' ); ?></a>
            </div><!-- .place-story-button -->
          
          </div><!-- .place-group-inner -->
        </div><!-- .place-story-group -->

      </div><!-- .row-inner -->
    </div><!-- .row -->

  </div>
</div>
