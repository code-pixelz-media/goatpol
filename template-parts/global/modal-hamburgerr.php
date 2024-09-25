<?php

global $current_user;
$user_role = 'user';
if (get_user_meta($current_user->ID, 'rae_approved', true) == 1) {
    $user_role = 'rae';
} else if (in_array('administrator', (array) $current_user->roles)) {
    $user_role = 'admin';
}

?>

<div class="side-head-col pol-hamburger">
    <a href="#" class="hamburgerr active parent-hamburgerr" id="button-menu">
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
                //   'meta_query' => array(
                //       array(
                //         'key' 	=> 'story_type_labels',
                //         'value' => 'short-story',
                //         )
                //   )
            );
            $query = new WP_Query($args);
            $max_num_page = $query->max_num_pages;

            ?>
            <ul id="infinite-list" class="menu" data-runajax="" data-maxpage=<?php echo $max_num_page; ?>
                class="list-menu" data-paged="1" data-selected="most-recent" data-scrollLimit="600">

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
                <?php endif; ?>
                <!-- Login Options Ends -->
                <!-- Latest Stories -->
                <!-- <li class="item-submenu sidebar-menu-item" menu="1"> -->
                <!-- <a href="#"><span class="fa fa-book-open icon-menu"></span><?php //_e('Recent Stories','pol'); ?></a> -->
                <ul class="submenu">
                    <li class="title-menu">
                        <span class="fa fa-book-open icon-menu"></span>
                        <?php _e('Recent Stories', 'pol'); ?>
                    </li>
                    <li class="go-back">Back</li>
                    <select class="story-ajax-filter">

                        <option value="most-recent">Most Recent</option>
                        <option value="least-popular">Least Popular</option>
                        <option value="most-popular">Most Popular</option>
                        <option value="nearby">Nearby</option>
                    </select>
                    <div class="pol-ajelms">
                        <?php
                        if ($query->have_posts()) {
                            while ($query->have_posts()):
                                $query->the_post();
                                $location = get_post_meta(get_the_id(), 'story_place_name', true);
                                $place_id = get_post_meta(get_the_ID(), 'stories_place', true);
                                $lat = get_post_meta($place_id, 'place_location_place_lat', true);
                                $lng = get_post_meta($place_id, 'place_location_place_lng', true);
                                $current_post_status = get_post_status(get_the_ID());
                                $locs = get_the_title($place_id);




                                ?>
                                <li class="infinite-post-id <?php echo $current_post_status . '-cpm'; ?>"
                                    data-id="<?php echo $place_id; ?> " data-postId="<?php echo get_the_ID(); ?>">
                                    <?php
                                    $image = get_the_post_thumbnail_url();
                                    if ($image) { ?>
                                        <div class="image-warp">
                                            <a href="#">
                                                <img src="<?php echo $image; ?>">
                                            </a>
                                        </div>
                                        <?php
                                    } else {
                                        ?>
                                        <div class="image-warp">
                                            <a href="#">
                                                <?php echo pol_get_random_goat(); ?>

                                            </a>
                                        </div>
                                    <?php } ?>
                                    <div class="blog-article">
                                        <h3><a href="<?php echo add_query_arg('place', $place_id, site_url('/map')) ?>">
                                                <?php the_title(); ?>
                                            </a></h3>
                                        <?php echo fetch_story_writer_name(get_the_id()); ?>
                                        <?php if (!empty($locs)): ?>
                                            <h2 class="writers-name writers-address">
                                                <span class="dashicons dashicons-location"></span>
                                                <a href="<?php echo add_query_arg('place', $place_id, site_url('/map')) ?>">
                                                    <?= $locs; ?>
                                                </a>
                                            </h2>
                                        <?php endif; ?>
                                    </div>
                                    <!-- </li> -->
                                    <?php

                            endwhile;
                            ?>

                                <?php
                        } else {
                            echo "There is no posts";
                        }
                        ?>
                    </div>
                </ul>
                </li>
                <!-- Latest Stories Ends -->
                <!-- Add Place Link -->
                <?php
                $add_place = get_page_by_path('add-place');
                ?>

                <?php if (is_user_logged_in()) {
                    $current_user_id = get_current_user_id();
                    global $current_user;
                    $avatar_img_url = '';
                    $profile_picture = get_user_meta($current_user_id, 'profile_picture', true);
                    $gravatar = get_avatar((int) $current_user_id, 40, pol_get_random_goat_img_url_for_list_page());
                    $use_gravatar = false;
                    // var_dump($gravatar);
                
                    // if (gettype($profile_picture) != 'boolean' && $profile_picture != '') {
                    //     // echo '222<br>';
                    //     $avatar_img_url = wp_get_attachment_url($profile_picture);
                    // } else {
                    //     // echo '333<br>';
                    //     $use_gravatar = true;
                    // }
                
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
                            <img src="<?php echo pol_get_user_profile_img((int) $current_user_id); ?>" alt="profile picture"
                                class="avatar avatar-80 photo">
                            <p>
                                <?php echo $current_user->user_login; ?>
                            </p>
                        </a>
                    </li>
                <?php } ?>

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
                <!-- Add place link ends -->



                <?php
                // if(is_user_logged_in() && ( $user_role =='admin' || $user_role=='rae')){ 
                // if (is_user_logged_in()) {
                

                //     ?>
                <li class="sidebar-menu-item">
                    <?php if (is_user_logged_in()) { ?>
                        <a href="<?php echo home_url('/published_books'); ?>"><span class="fa fa-file-pen icon-menu"></span>
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
                    const hamburgerr = document.querySelector(".hamburgerr");
                    const sidebarContent = document.querySelector(".sidebar-content");
                    const col80 = document.querySelector(".col-80");
                    hamburgerr.addEventListener("click", () => {
                        hamburgerr.classList.toggle("active");
                        sidebarContent.classList.toggle("active");
                        if (col80)
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