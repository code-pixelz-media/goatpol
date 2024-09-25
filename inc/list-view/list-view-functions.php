<?php

use ParagonIE\Sodium\Core\Curve25519\Ge\P2;


//==============HELPER FUNCTON STARTS================

add_action('wp_login', 'pol_redirect_to_last_visited_page');
function pol_redirect_to_last_visited_page()
{
    // if(!is_page(9212)){

        ?>
            <script>
                if (localStorage.getItem("last_visited_page") == "list-page") {
                    window.location.href = "<?php echo home_url('/list') ?>";
                } else if (localStorage.getItem("last_visited_page") == "map-page") {
                    window.location.href = "<?php echo home_url('/map') ?>";
                } else {
                    window.location.href = "<?php echo home_url('/map') ?>";
                }
            </script>

        <?php
    // }
}


function pol_get_random_goat_img_url_for_list_page($classes = '')
{

    // Get the goats.
    $goat_images = get_option('options_goat_images');

    if (!$goat_images) {
        return;
    }


    $image_count         = count($goat_images);
    $random_number     = rand(1, $image_count - 1);
    $image_id                = $goat_images[$random_number];
    $image                    = wp_get_attachment_image_url($image_id, 'thumbnail');
    // $image                    = wp_get_attachment_url($image_id, 'thumbnail'); 
    $class                    = 'goat';

    if ($classes) {
        $class .= ' ' . $classes;
    }

    if (!$image) {
        return;
    }

    return $image;
}


function pol_fetch_nom_de_plume_from_story_id($story_id)
{
    $writer_name    = get_field('story_nom_de_plume', $story_id);

    if (!empty($writer_name)) {

        $name =  $writer_name;
    } else {
        $author_id = get_post_field('post_author', $story_id);
        $name = get_the_author_meta('display_name', $author_id);
    }

    return $name;
}

function pol_fetch_all_authorID_and_authorNomDePlume($search_query)
{
    $args = [];
    $story_ids = [];
    $unique_author_ids = [];

    $authorID_and_auhtorPenname = [];

    if (!empty($search_query)) {
        $args = [
            'post_type' => 'story',
            'post_status' => 'publish', 
            'fields' => 'ids',
            'posts_per_page' => -1,
            'meta_query' => [
                [
                    'key' => 'story_nom_de_plume',
                    'value' => $search_query,
                    'compare' => 'LIKE'
                ]
            ]
        ];
    }else{
        $args = [
            'post_type' => 'story',
            'post_status' => 'publish', 
            'posts_per_page' => -1,
            'fields' => 'ids',
            'meta_query' => [
                [
                    'key' => 'story_nom_de_plume',
                ]
            ]
        ];
    }

    $nom_de_plume_query = new WP_Query($args);

    // return $args;

    if ($nom_de_plume_query->have_posts()) {

        while ($nom_de_plume_query->have_posts()) {
            $nom_de_plume_query->the_post();

            $curr_author_id = get_post_field( 'post_author', get_the_ID() ) ;

            
            //check if story from particular author is already present in array
            if( in_array( $curr_author_id, $unique_author_ids ) ){
                continue;
            }

            array_push( $unique_author_ids, $curr_author_id );

            // array_push($story_ids, get_the_ID());

            $curr_post_author_nom_de_plume = pol_fetch_nom_de_plume_from_story_id(get_the_ID());
            
            ///////////////
            $authorID_and_auhtorPenname += [$curr_author_id => $curr_post_author_nom_de_plume];
            // array_push($authorID_and_auhtorPenname, [$curr_author_id => $curr_post_author_nom_de_plume]);


        }
    }

    return $authorID_and_auhtorPenname;
}

//==============HELPER FUNCTON ENDS================



























//================INTERNET AND BOOK SECTION STARTS====================

function pol_get_stories_info_for_internet_and_book_section($section, $return)
{
    $meta_value = $section == "internet" ? "about_internet" : "about_books";
    $total_pages = 0;
    $total_stories = 0;
    $all_story_ids = [];

    $args = [
        'post_type' => 'place',
        'post_status' => 'publish',
        'posts_per_page' => -1,
        'order' => 'desc',
        // 'suppress_filters' => false,
        // 'fields' => '',
        'meta_query' => [
            [
                'key'           => 'where_does',
                'value'         => $meta_value,
                'compare' => '=',
            ]
        ],
    ];

    $query = new WP_Query($args);

    // var_dump('SQL:::'.$query->request);

    if ($query->have_posts()) {


        while ($query->have_posts()) {
            $query->the_post();

            $story_ids = get_post_meta(get_the_id(), 'place_stories', true);

            if (!is_array($story_ids) || empty($story_ids) || get_post_status($story_ids[0]) != "publish" || in_array($story_ids[0], $all_story_ids)) {
                continue;
            }

            $total_stories++;

            array_push($all_story_ids, $story_ids[0]);
        }
    }

    wp_reset_postdata();

    //replace 10 with the number of posts per page that you want.
    $total_pages = ceil($total_stories / 10);

    

    if ($return == 'story_ids') {
        return $all_story_ids;
    } else if ($return == 'max_pages') {
        return $total_pages;
    } else if ($return == 'all_info') {
        return [$all_story_ids, $total_pages];
    }
}


add_action('wp_ajax_pol_get_internet_and_book_stories', 'pol_get_internet_and_book_stories'); // for logged in user
add_action('wp_ajax_nopriv_pol_get_internet_and_book_stories', 'pol_get_internet_and_book_stories'); // if user not logged in
function pol_get_internet_and_book_stories()
{

    $paged = isset($_POST['page']) ? $_POST['page'] : 0;
    $section_name = $_POST['value'];
    $section_id = $_POST['section_id'];

    $all_story_info = pol_get_stories_info_for_internet_and_book_section($section_name, 'all_info');
    $all_story_ids = $all_story_info[0];
    $total_pages = $all_story_info[1];

    $paged_story_arr = array_chunk($all_story_ids, 10, true);
    $curr_page_stories = $paged_story_arr[(int)($paged)]; //(int)($paged - 1)

    foreach ($curr_page_stories as $curr_story_id) {
        $img_el = "";
        $story_title = get_the_title($curr_story_id);
        $story_author = pol_fetch_nom_de_plume_from_story_id($curr_story_id);
        $story_location = get_the_title(get_post_meta($curr_story_id, 'stories_place', true));
        $read_more_link = get_permalink($curr_story_id);
        $story_published_date = get_the_date($curr_story_id);

        $image = get_the_post_thumbnail_url($curr_story_id, 'thumbnail');
        if ($image) {
            $img_el = '<img src="' . $image . '" alt="goat" />';
        } else {
            $img_el = '<img src="' . pol_get_random_goat_img_url_for_list_page() . '" alt="goat" />';
        }

        get_template_part(
            'template-parts/list-view/template-single-generic-story',
            null,
            [
                'story_id' => $curr_story_id,
                'section' => $_POST['section'],
                'image_element' => $img_el,
                'story_title' => $story_title,
                'story_author' => $story_author,
                'location' => $story_location,
                'read_more_link' => $read_more_link,
                'publish_date' => $story_published_date
            ]
        );
    }

    ?>
    <script>
        //sets the max page on the load more button
        jQuery(document).ready(function() {
            var currSectionId = <?php echo $section_id; ?>;
            var maxPages = <?php echo sizeof($paged_story_arr); ?>;

            if (maxPages == 1) {
                jQuery('#load-more-stories-' + currSectionId).hide();
            } else {
                jQuery('#load-more-stories-' + currSectionId).attr('data-maxpage', maxPages);
            }
        });
    </script>
    <?php

    die();
}


add_action('wp_ajax_pol_live_search_internet_and_book', 'pol_live_search_internet_and_book'); // for logged in user
add_action('wp_ajax_nopriv_pol_live_search_internet_and_book', 'pol_live_search_internet_and_book'); // if user not logged in
function pol_live_search_internet_and_book()
{
    $story_title_to_be_searched = $_POST['search_query'];
    $section_name = $_POST['section_name'];

    $all_stories_id = pol_get_stories_info_for_internet_and_book_section($section_name, 'story_ids');

    foreach ($all_stories_id as $curr_story_id) {
        if (str_contains(strtolower(get_the_title($curr_story_id)), strtolower($story_title_to_be_searched))) {
            $img_el = "";
            $story_title = get_the_title($curr_story_id);
            $story_author = pol_fetch_nom_de_plume_from_story_id($curr_story_id);
            $story_location = get_the_title(get_post_meta($curr_story_id, 'stories_place', true));
            $read_more_link = get_permalink($curr_story_id);
            $story_published_date = get_the_date($curr_story_id);

            $image = get_the_post_thumbnail_url($curr_story_id, 'thumbnail');
            if ($image) {
                $img_el = '<img src="' . $image . '" alt="goat" />';
            } else {
                $img_el = '<img src="' . pol_get_random_goat_img_url_for_list_page() . '" alt="goat" />';
            }

            get_template_part(
                'template-parts/list-view/template-single-generic-story',
                null,
                [
                    'story_id' => $curr_story_id,
                    'section' => $_POST['section'],
                    'image_element' => $img_el,
                    'story_title' => $story_title,
                    'story_author' => $story_author,
                    'location' => $story_location,
                    'read_more_link' => $read_more_link,
                    'publish_date' => $story_published_date
                ]
            );
        }
    }
    die();
}

//================INTERNET AND BOOK SECTION ENDS====================


































add_action('wp_ajax_pol_lists_view_individual_story_generator', 'pol_lists_view_individual_story_generator'); // for logged in user
add_action('wp_ajax_nopriv_pol_lists_view_individual_story_generator', 'pol_lists_view_individual_story_generator'); // if user not logged in
function pol_lists_view_individual_story_generator()
{
    $paged = isset($_POST['page']) ? $_POST['page'] : 1;
    $list_type = isset($_POST['value']) ? $_POST['value'] : '';

    if ($list_type == 'by author' || $list_type == 'by location') { //isset($_POST['author_id']) ||
        pol_get_stories_by_author_or_location($list_type, $_POST['author_id'], $paged);
    } else {
        pol_get_stories_for_generic_sections($list_type, $paged, $_POST['section_id']);
    }
}


//get stories for most recent, often read, nearby, random
function pol_get_stories_for_generic_sections($list_type, $paged, $section_id)
{

    $post_per_page = 4;
    $post_type = 'story';

    if ($list_type == 'random') {
        $post_per_page = 10;
    }

    if ($list_type == 'internet' || $list_type == 'book') {
        $post_type = 'place';
    }

    $args = [
        'post_type' => $post_type,
        'posts_per_page' => $post_per_page,
        'paged' => $paged,
        'post_status' => 'publish'
    ];

    if ($list_type == 'most recent') {

        $args = $args;
    } else if ($list_type == 'by author') {
        $args += [
            'order' => 'desc',
            'suppress_filters' => false,
            'fields' => '',
        ];
    } else if ($list_type == 'by location') {

        $args += [
            'order' => 'desc',
            'suppress_filters' => false,
            'fields' => '',
        ];
    } else if ($list_type == 'often read') {

        $args += [
            'order' => 'asc',
            'suppress_filters' => false,
            'orderby' => 'post_views',
            'fields' => ''
        ];
    } else if ($list_type == 'nearby') {

        $ip = $_SERVER['REMOTE_ADDR'];
        $location = unserialize(file_get_contents('http://www.geoplugin.net/php.gp?ip=' . $ip));
        $lat = $location['geoplugin_latitude'];
        $lng = $location['geoplugin_longitude'];
        $nearby = my_get_nearby_locations($lat, $lng);
        $nearby_placeID = wp_list_pluck($nearby, 'post_id');

        $merge = array();
        foreach ($nearby_placeID as $near) {

            $array_posts = get_post_meta($near, 'place_stories', true);

            foreach ($array_posts as $array_data) {
                $merge[] = $array_data;
            }
        }

        $args += [
            'post__in' => $merge,
        ];
    } else if ($list_type == 'random') {

        $args += [
            'order' => 'desc',
            'orderby' => 'rand',
            'suppress_filters' => false,
            'fields' => '',
        ];
    } else {
        echo '<p class="pol-err-msg">ERROR::No section with te specified list type type</p>';
    }
    $query = new WP_Query($args);

    if ($query->have_posts()) {

        $total_pages = $query->max_num_pages;

        while ($query->have_posts()) {
            $query->the_post();

            $story_id = get_the_ID();
            $img_el = "";
            $story_title = get_the_title();
            $story_author = pol_fetch_nom_de_plume_from_story_id(get_the_ID());
            $story_location = get_the_title(get_post_meta(get_the_ID(), 'stories_place', true));
            $read_more_link = get_permalink();
            $story_published_date = get_the_date();

            $image = get_the_post_thumbnail_url(get_the_ID(), 'thumbnail');
            if ($image) {
                $img_el = '<img src="' . $image . '" alt="goat" />';
            } else {
                $img_el = '<img src="' . pol_get_random_goat_img_url_for_list_page() . '" alt="goat" />';
            }

            get_template_part(
                'template-parts/list-view/template-single-generic-story',
                null,
                [
                    'story_id' => $story_id,
                    'section' => $list_type,
                    'image_element' => $img_el,
                    'story_title' => $story_title,
                    'story_author' => $story_author,
                    'location' => $story_location,
                    'read_more_link' => $read_more_link,
                    'publish_date' => $story_published_date
                ]
            );
        }

    ?>
        <script>
            //sets the max page on the load more button
            jQuery(document).ready(function() {
                var currSectionId = <?php echo $section_id; ?>;
                var maxPages = <?php echo $total_pages; ?>;

                if (maxPages == 1) {
                    jQuery('#load-more-stories-' + currSectionId).hide();
                } else {
                    jQuery('#load-more-stories-' + currSectionId).attr('data-maxpage', maxPages);
                }
            });
        </script>
    <?php

    } else {
        echo '<div>No tales discovered in the vicinity...</div>';
    ?>
        <script>
            jQuery(document).ready(function() {
                var currSectionId = <?php echo $section_id; ?>;
                jQuery('#load-more-stories-' + currSectionId).css('display', 'none');
            });
        </script>
        <?php
    }

    wp_reset_postdata();

    die();
}


function pol_get_stories_by_author_or_location($list_type, $curr_author_id, $paged)
{
    if ($list_type == 'by author') {
        $args = [
            'post_type' => 'story',
            'posts_per_page' => 4,
            'paged' => $paged,
            'post_status' => 'publish',
            'author' => (int)$curr_author_id,
        ];
    } else {
        $args = [
            'post_type' => 'story',
            'posts_per_page' => 4,
            'paged' => $paged,
            'post_status' => 'publish',
            'meta_query' => [
                [
                    'key'     => 'stories_place',
                    'value' => $curr_author_id,
                    'compare' => '=',
                ]
            ]
        ];
    }

    $query = new WP_Query($args);

    if ($query->have_posts()) {

        $total_pages = $query->max_num_pages;

        while ($query->have_posts()) {
            $query->the_post();

            $story_id = get_the_ID();
            $img_el = "";
            $story_title = get_the_title();
            $story_author = pol_fetch_nom_de_plume_from_story_id(get_the_ID());
            $story_location = get_the_title(get_post_meta(get_the_ID(), 'stories_place', true));
            $read_more_link = get_permalink();

            $image = get_the_post_thumbnail_url(get_the_ID(), 'thumbnail');
            if ($image) {
                $img_el = '<img src="' . $image . '" alt="goat" />';
            } else {
                $img_el = '<img src="' . pol_get_random_goat_img_url_for_list_page() . '" alt="goat" />';
            }

        ?>

            <div class="gp-story-list-row">
                <div class="gp-story-img">
                    <?php echo $img_el; ?>
                </div>
                <div class="gp-story-body">
                    <a href="<?php echo $read_more_link; ?>">
                        <h4><?php echo $story_title; ?></h4>
                    </a>
                    <p> by <?php echo $story_author; ?></p>
                    <p>
                        <span class="dashicons dashicons-location"></span><?php echo $story_location; ?>
                    </p>
                </div>
            </div>

        <?php
        }

        ?>
        <script>
            //sets the max page on the load more button in
            jQuery(document).ready(function() {
                var currSectionId = <?php echo $_POST['author_id']; ?>;
                var maxPages = <?php echo $total_pages; ?>;
                if (maxPages == 1) {
                    jQuery('#load-more-author-stories-' + currSectionId).hide();
                } else {
                    jQuery('#load-more-author-stories-' + currSectionId).attr('data-maxpage', maxPages);
                }
            });
        </script>
        <?php
    }

    die();
}


add_action('wp_ajax_pol_live_search_author_and_location', 'pol_live_search_author_and_location'); // for logged in user
add_action('wp_ajax_nopriv_pol_live_search_author_and_location', 'pol_live_search_author_and_location'); // if user not logged in
function pol_live_search_author_and_location()
{
    if ($_POST['search_type'] == 'author') {
        $nom_de_plume_to_be_searched = $_POST['search_query'];
        $author_section_author_arr =  pol_fetch_all_authorID_and_authorNomDePlume($nom_de_plume_to_be_searched);

        foreach ($author_section_author_arr as $auth_id => $auth_nom_de_plume) {
            // echo $auth_id.'<br>';
            // echo $auth_nom_de_plume.'<br>';
            ?>
            <li class="gp-story-author">
                <a class="gp-author-name gp-author-name-<?php echo $auth_id; ?>" href="#gp-story-popup-<?php echo $auth_id; ?>"><?php echo $auth_nom_de_plume; ?></a>
                <?php get_template_part('template-parts/list-view/template-filter-specific-story-popup', null, ['section' => $_POST['section_name'], 'popup_id' => $auth_id, 'author_id' => $auth_id, 'author_name' => $auth_nom_de_plume]); ?>
            </li>
            <?php
        }

    } else {
        $location_to_be_searched = $_POST['search_query'];
        $all_places = pol_get_map_places();

        foreach ($all_places as $place) {
            if (str_contains(strtolower($place->post_title), strtolower($location_to_be_searched))) {
            ?>
                <li class="gp-story-author">
                    <a class="gp-author-name gp-author-name-<?php echo $place->ID; ?>" href="#gp-story-popup-<?php echo $place->ID; ?>"><?php echo $place->post_title; ?></a>
                    <?php get_template_part('template-parts/list-view/template-filter-specific-story-popup', null, ['section' => $_POST['section_name'], 'popup_id' => $place->ID, 'author_id' => $place->ID, 'author_name' => $place->post_title]); ?>
                </li>
    <?php
            }
        }
    }

    die();
}


add_action('wp_ajax_pol_refresh_random_stories', 'pol_refresh_random_stories'); // for logged in user
add_action('wp_ajax_nopriv_pol_refresh_random_stories', 'pol_refresh_random_stories'); // if user not logged in
function pol_refresh_random_stories()
{
    $random_story_args = [
        'post_type' => 'story',
        'posts_per_page' => 10,
        'post_status' => 'publish',
        'order' => 'desc',
        'orderby' => 'rand',
        'suppress_filters' => false,
        'fields' => '',
    ];

    $random_story_query = new WP_Query($random_story_args);

    if ($random_story_query->have_posts()) {

        while ($random_story_query->have_posts()) {
            $random_story_query->the_post();

            $story_id = get_the_ID();
            $img_el = "";
            $story_title = get_the_title();
            $story_author = pol_fetch_nom_de_plume_from_story_id(get_the_ID());
            $story_location = get_the_title(get_post_meta(get_the_ID(), 'stories_place', true));
            $read_more_link = get_permalink();
            $story_published_date = get_the_date();

            $image = get_the_post_thumbnail_url(get_the_ID(), 'thumbnail');
            if ($image) {
                $img_el = '<img src="' . $image . '" alt="goat" />';
            } else {
                $img_el = '<img src="' . pol_get_random_goat_img_url_for_list_page() . '" alt="goat" />';
            }

            get_template_part(
                'template-parts/list-view/template-single-generic-story',
                null,
                [
                    'story_id' => $story_id,
                    'section' => 'random',
                    'image_element' => $img_el,
                    'story_title' => $story_title,
                    'story_author' => $story_author,
                    'location' => $story_location,
                    'read_more_link' => $read_more_link,
                    'publish_date' => $story_published_date
                ]
            );
        }
    }

    die();
}
