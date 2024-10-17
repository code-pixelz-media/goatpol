<?php

/**
 * Template Name: Map
 * 
 * Displays the homepage map with place markers.
 * 
 * @package GOAT PoL
 */
get_header();

//store the last page of 
if (is_user_logged_in()) {
    ?>
    <script>
        localStorage.setItem("last_visited_page", "map-page");
    </script>
    <?php
}

pol_top_markers_arrangements();
pol_bottom_markers_arrangements();
$map_markers = pol_get_places_markers();

$map_id = get_field('google_map_id', 'option') ?: false;
$map_style = get_field('google_map_style', 'option') ?: 'roadmap';
$map_position = get_field('google_map_initial_position', 'option');
// pol_marker_uniformity();

$map_attr = array(
    'map-style' => $map_style,
);

if ($map_id) {
    $map_attr['map-id'] = $map_id;
}
if ($map_position) {
    $map_attr['map-lat'] = $map_position['lat'];
    $map_attr['map-lng'] = $map_position['lng'];
    $map_attr['map-zoom'] = 0;
}

$map_data_attr = '';
if ($map_attr) {
    foreach ($map_attr as $attr => $val) {
        $map_data_attr .= ' data-' . $attr . '="' . $val . '"';
    }
}

global $current_user;
$user_role = 'user';
if (get_user_meta($current_user->ID, 'rae_approved', true) == 1) {
    $user_role = 'rae';
} else if (in_array('administrator', (array) $current_user->roles)) {
    $user_role = 'admin';
}

?>
<main id="site-content" role="main">
    <div class="story-popup-template" style="display:none;">
        <div id="infowindow" class="marker-popup story-popup">
            <div class="marker-popup-inner">
                <div class="row popup-row">
                    <div class="row-inner">
                        <div class="place-story-group">
                            <div class="place-group-inner place-story-inner">
                                <div class="place-stories">
                                    <div class="place-story story-content-placeholder">
                                        <div class="place-story-title">
                                            <a class="place-story-link">
                                                <h3 class="h6 title-text"></h3>
                                            </a>
                                            <p class="nom-de-plume">By <a class="place-story-link story-author"></a></p>
                                        </div>
                                    </div>
                                    <div class="story-actions">
                                        <a class="read-more" href="javascript:void(0)">
                                            <?php _e('Read More...', 'pol'); ?>
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="site-content-inner">

        <div class="col-80 active map-container">

            <div id="map" class="acf-map" <?php echo $map_data_attr; ?>>

                <?php
                if ($map_markers) {

                    foreach ($map_markers as $marker) {


                        $lat = get_post_meta($marker['ID'], 'place_location_place_lat', true);
                        $long = get_post_meta($marker['ID'], 'place_location_place_lng', true);

                        if (get_field('where_does', $marker['ID']) != 'geo_loc') {
                            continue;
                        }

                        if (!isset($lat) || !isset($long)) {
                            continue;
                        }

                        $marker_lat = $lat;
                        $marker_lng = $long;
                        $marker_color = pol_get_place_marker_color($marker['ID']);
                        ?>

                        <div class="marker" data-color="<?php echo esc_attr($marker_color); ?>"
                            data-lat="<?php echo esc_attr($marker_lat); ?>" data-lng="<?php echo esc_attr($marker_lng); ?>"
                            data-mrkurl="<?php echo pol_get_marker_url($marker['ID']); ?>"
                            data-markid="<?php echo $marker['ID']; ?>">
                            <?php get_template_part('template-parts/popup/popup-marker', null, $marker); ?>
                        </div>

                        <?php

                    }
                }
                ?>
            </div>

        </div>

        <div class="side-head-col">
            <a href="#" class="hamburgerr active" id="button-menu">
                <span class="bar"></span>
                <span class="bar"></span>
                <span class="bar"></span>
            </a>

            <div class="sidebar-content col-20" id="ajaxlazyload">
                <div class="navegacion">
                    <?php
                    $args = array(
                        'post_type' => 'story',
                        'post_status' => 'publish',
                        'posts_per_page' => 10,
                        'paged' => 1,
                        'order' => 'desc',
                        'meta_query' => array(
                            array(
                                'key' => 'stories_place',
                                'value' => '',
                                'compare' => '!='
                            )
                        )
                    );
                    $query = new WP_Query($args);
                    $max_num_page = $query->max_num_pages;

                    ?>
                    <ul id="infinite-list" class="menu" data-runajax="<?php echo $run_ajax; ?>" data-maxpage=<?php echo $max_num_page; ?> class="list-menu" data-paged="1" data-selected="most-recent"
                        data-scrollLimit="600">

                        <!-- Login Options -->
                        <?php // if (!is_user_logged_in()): ?>
                        <!-- <li> -->
                        <!-- <a href="<?php // echo wp_login_url() ?>"><span class="fa fa-user icon-menu"> -->
                        <!-- </span> -->
                        <?php // _e('Already a GOAT? Log-in here', 'pol'); ?>
                        <!-- </a> -->
                        <!-- </li> -->
                        <?php // endif; ?>
                        <?php if (is_user_logged_in()):
                            $id = get_current_user_id();
                            $user_obj = get_user_by('id', $id);
                            $change_password = get_page_by_path('change-password');
                            $user_profile = get_page_by_path('profile');
                            $my_stories = get_page_by_path('see-my-stories');
                            ?>
                            <!-- <li class="sidebar-menu-item" menu="2">
                  <a href="<?php //echo  get_the_permalink($my_stories->ID); 
                      ?>"><span class="fa fa-user icon-menu">
                        </span>My Stories
                    </a>
                </li> -->
                            <?php /*
<li class="item-submenu sidebar-menu-item" menu="2">
<a href="#"><span class="fa fa-user icon-menu">
</span><?php echo 'Welcome ' . $user_obj->user_nicename; ?>
</a>
<ul class="submenu">
<li class="go-back"><?php _e('Back','pol'); ?></li>
<!-- <li><a href="<?php echo  get_the_permalink($user_profile->ID); ?>"><?php _e('Profile','pol') ;?></a></li> -->
<li><a
href="<?php echo  get_the_permalink($change_password->ID); ?>"><?php _e('Confirm or change password','pol') ;?></a>
</li>
<li><a
href="<?php echo  get_the_permalink($my_stories->ID); ?>"><?php _e('See My Stories','pol'); ?></a>
</li>
</ul>
</li> */?>
                        <?php endif; ?>
                        <!-- Login Options Ends -->
                        <!-- Latest Stories -->
                        <!-- <li class="item-submenu sidebar-menu-item" menu="1"> -->
                        <!-- <a href="#"><span class="fa fa-book-open icon-menu"></span><?php //_e('Recent Stories', 'pol'); 
                        ?></a> -->
                        </li>
                        <!-- Latest Stories Ends -->
                        <!-- Add Place Link -->
                        <?php
                        $add_place = get_page_by_path('add-place');
                        ?>
                        <?php if (is_user_logged_in()) {
                            $current_user_id = get_current_user_id();
                            global $current_user;

                            /*****
                            $avatar_img_url = '';
                            $profile_picture = get_user_meta($current_user_id, 'profile_picture', true);
                            $gravatar = get_avatar((int) $current_user_id, 40, pol_get_random_goat_img_url_for_list_page());
                            $use_gravatar = false;
                            // var_dump($gravatar);
                        
                            if (gettype($profile_picture) != 'boolean' && $profile_picture != '') {
                                // echo '222<br>';
                                $avatar_img_url = wp_get_attachment_url($profile_picture);
                            } else {
                                // echo '333<br>';
                                $use_gravatar = true;
                            }
                            */

                            // $avatar_img_url = '';
                            // $profile_picture = get_user_meta($current_user_id, 'profile_picture', true);
                            // if($profile_picture != ""){
                            //     $avatar_img_url = $profile_picture;
                            // }else{
                            //     $avatar_img_url = get_avatar_url($current_user_id);
                            // }
                            ?>
                            <li class="sidebar-menu-item sidebar-profile-content">
                                <a href="#">
                                    <img src="<?php echo pol_get_user_profile_img((int) $current_user_id); ?>"
                                        alt="profile picture" class="avatar avatar-80 photo">
                                    <p>
                                        <?php echo $current_user->user_login; ?>
                                    </p>
                                </a>
                            </li>
                        <?php } ?>
                        <?php /*******. Menu hidden until live -- utsav.
<li class="sidebar-menu-item">
<?php if (is_user_logged_in()) { ?>
<a href="<?php echo get_permalink($add_place->ID); ?>" class="getPassport-modal"><span
     class="fa fa-file-pen icon-menu"></span>
 <?php _e('Submit work for publication', 'pol');
} else { ?>

 <a href="<?php echo home_url('/login'); ?>" class="getPassport-modal"><span
         class="fa fa-file-pen icon-menu"></span>
     <?php _e('Submit work for publication', 'pol') ?>
 </a>
<?php } ?>
</li>
****************/?>
                        <?php if (is_user_logged_in()):
                            ?>
                            <li class="sidebar-menu-item">
                                <a href="<?php echo get_author_posts_url(get_current_user_id()); ?>"><span
                                        class="fa-solid fa-user icon-menu"></span>
                                    <?php _e('See your contributor’s page', 'pol') ?>
                                </a>
                            </li>
                            <?php // else: ?>
                            <!-- <li class="sidebar-menu-item">
                                <a href="<?php // echo home_url('/login'); ?>"><span class="fa-solid fa-user icon-menu"></span>
                                    <?php // _e('See your contributor’s page', 'pol') ?>
                                </a>
                            </li> -->
                        <?php endif; ?>
                        <li class="sidebar-menu-item">
                            <a href="<?php echo home_url('/about'); ?>"><span class="fa-solid fa-user icon-menu"></span>
                                <?php _e('About', 'pol') ?>
                            </a>
                        </li>






                        <?php
                        // if(is_user_logged_in()){ 
                        // if(is_user_logged_in() && ( $user_role =='admin' || $user_role=='rae')){ 
                        
                        ?>
                        <li class="sidebar-menu-item">
                            <?php if (is_user_logged_in()) { ?>
                                <a href="<?php echo home_url('/published_books'); ?>"><span
                                        class="fa fa-file-pen icon-menu"></span>
                                    <?php _e('Books published by The GOAT PoL', 'pol');
                            } else { ?>
                                    <a href="<?php echo home_url('/published_books'); ?>"><span
                                            class="fa  fa-file-pen icon-menu"></span>
                                        <?php _e('Books published by The GOAT PoL', 'pol') ?>
                                    </a>
                                <?php }
                            ?>
                        </li>

                        <li class="sidebar-menu-item">
                            <a href="<?php echo home_url('/workshops');
                            ?>">
                                <span class="fa fa-file-pen icon-menu"></span>
                                <?php _e('Workshops', 'pol'); ?>
                            </a>
                        </li>

                        <?php // } ?>



                        <!-- Log Out Link -->
                        <?php if (is_user_logged_in()) { ?>
                            <li class="sidebar-menu-item">
                                <a href="<?php echo wp_logout_url(); ?>">
                                    <span class="fa fa-arrow-right-from-bracket icon-menu"></span>
                                    <?php _e('Log Out', 'pol'); ?>
                                </a>
                            </li>
                        <?php } else { ?>
                            <li class="sidebar-menu-item">
                                <a href="/login">
                                    <span class="fa-solid fa-right-to-bracket icon-menu"></span>
                                    <?php _e('Sign up or login to The GOAT PoL', 'pol'); ?>
                                </a>
                            </li>
                        <?php } ?>
                        <!-- Log Out Link Ends -->
                        <script>
                            const hamburgerr = document.querySelector(".hamburgerr")
                            const sidebarContent = document.querySelector(".sidebar-content")
                            const col80 = document.querySelector(".col-80")
                            hamburgerr.addEventListener("click", () => {
                                hamburgerr.classList.toggle("active");
                                sidebarContent.classList.toggle("active");
                                col80.classList.toggle("active");

                            })
                        </script>
                        <?php


                        wp_reset_postdata();
                        ?>
                    </ul>
                    <div id="element-lazyload" data-elemtop="1"
                        style="margin-left:20px; height: 100px; width:auto;display:none">
                        Loading...</div>
                </div>
            </div>
        </div>
    </div><!-- .site-content-inner -->
</main><!-- #site-content -->
<!-- ------ TOGGLE ---------- -->
<!-- <div class="gp-toggle-wrapper map-page-toggle">
    <input class="gp-radio" id="gp-toggle-left" name="group" type="radio">
    <input class="gp-radio" id="gp-toggle-right" name="group" type="radio">
    <input class="gp-radio" id="gp-toggle-middle" name="group" type="radio">
    <div class="gp-toggle-tabs">
        <label class="gp-toggle-tab" id="gp-tab-left" for="gp-toggle-left"> The GOAT PoL READING LISTS</label>
        <label class="gp-toggle-tab" id="gp-tab-right" for="gp-toggle-right"> The GOAT PoL MAP</label>
        <label class="gp-toggle-tab" id="gp-tab-middle" for="gp-toggle-middle"> The GOAT PoL Contributors</label>
    </div>
</div> -->
<?php

get_footer();
