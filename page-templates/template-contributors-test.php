<?php

/**
 * Template Name: Contributors Test
 *
 * Displays list of all writers
 *
 * @package GOAT PoL
 */

get_header();

function pol_mail_sender_temp($to, $subject, $message, $salutation = '', $attach = array())
{
	$args = array('salutation' => $salutation, 'message' => $message);
	ob_start();
	add_filter('wp_mail_content_type', 'pol_set_mail_content');
	get_template_part('mail-templates/pol', 'mail', $args);
	$message = ob_get_contents();
	ob_end_clean();
	$send_from = 'thegoatpol@tutanota.com';
	$headers = array('Content-Type: text/html; charset=UTF-8');
	$headers .= 'From: ' . $send_from . "\r\n";
	$subject = $subject;
	return '=='.$to;
}

function pol_send_email_24_hours_before_workshop_test()
{
    $workshops = [20625];

    foreach ($workshops as $wid) {
        $workshop_title = get_the_title((int)$wid);
        $workshop_link = get_the_permalink((int)$wid);
        $workshop_lnk = get_post_meta((int)$wid, 'online_link', true);
        $workshop_date_time_cet = get_post_meta((int)$wid, 'workshop-date-time', true);
        $workshop_participants = get_post_meta((int)$wid, 'signups', true);
        echo '<pre>';
        var_dump($workshop_participants);
        echo '</pre>';

        if (!is_array($workshop_participants)) {
            continue;
        }

        foreach ($workshop_participants as $participant) {
            $curr_user = get_user_by('id', $participant);
            $user_email = $curr_user->user_email;
            $user_display_name = $curr_user->display_name;

            $email_content = '
                Thank you for signing up to attend ' . $workshop_title . ', which will meet online tomorrow at ' . $workshop_date_time_cet . ' CEST. To attend please use the following link:
                <a href="' . $workshop_lnk . '">' . $workshop_lnk . '</a>.
                Please be prompt: the convening RAE will remove your name from the list of participants if you do not actually attend.
            ';

            echo pol_mail_sender_temp($user_email, "Workshop Reminder", $email_content, "Dear $user_display_name");;
            echo "<br />";

            // $hh = pol_mail_sender_temp($user_email, "Workshop Reminder", $email_content, "Dear $user_display_name");

        }

        // update_post_meta((int)$wid, '24_before_workshop', '1');
    }
}


// pol_send_email_24_hours_before_workshop_test();

?>
<div class="pol-contributors-page">
    <h3 class="pol-contributors-page-heading">Search for contributors</h3>

    <?php

    $users = get_users();

    //get all  places 
    $all_locations = pol_get_places();

    //get all langugaes from the 'place_language' meta key from posts 
    //that match with metakey and value 'where_does'=>'geo_loc'
    $place_languages = [];

    $places_query = get_posts([
        'post_type' => 'place',
        'posts_per_page' => -1,
        'post_status' => array('draft', 'pending', 'publish'),
        'meta_query' => [
            [
                'key' => 'where_does',
                'value' => 'geo_loc',
                'compare' => 'LIKE',
            ],
        ],
    ]);

    //get the meta value of the key 'place_language'
    foreach ($places_query as $post) {
        $langs = get_post_meta($post->ID, 'place_languages', true)[0];

        if (!empty($langs)) {
            if (is_array($langs)) {
                foreach ($langs as $lang) {
                    if (!in_array($lang, $place_languages)) {

                        array_push($place_languages, $lang);
                    }
                }
            } else {
                if (!in_array($langs, $place_languages)) {
                    array_push($place_languages, $langs);
                }
            }
        }
    }


    function get_values_from_url($query_arg)
    {
        $new_arr = [];
        if (isset($_GET[$query_arg]) && !empty($_GET[$query_arg])) {
            foreach ($_GET[$query_arg] as $arg) {
                $new_arr[] = sanitize_text_field($arg);
            }
        } else {
            $new_arr = [];
        }

        return $new_arr;
    }

    function get_sql_from_query_vars($query_arr, $search_sql, $meta_key)
    {
        // echo '$query_arr:::'.is_array($query_arr).'<br>';
        // echo '$meta_key:::'.is_array($meta_key).'<br>';


        $new_sql = '';
        $new_sql .= ($search_sql != '') ? ' UNION ALL ' : '';


        if(is_array($meta_key) && is_array($query_arr)){
            foreach ($query_arr as $q) {
                $new_sql .=
                "
                    SELECT DISTINCT(user_id) as ID
                    FROM bny_usermeta um
                    WHERE 
                    (um.meta_key LIKE '" . $meta_key[0] . "' AND um.meta_value LIKE '%" . $q . "%' )
                    OR
                    (um.meta_key LIKE '" . $meta_key[1] . "' AND um.meta_value LIKE '%" . $q . "%' )
                    OR
                    (um.meta_key LIKE '" . $meta_key[2] . "' AND um.meta_value LIKE '%" . $q . "%' )
                ";
            }
        }else if(!is_array($meta_key) && !is_array($query_arr)){
            $new_sql .=
            "
                SELECT DISTINCT(user_id) as ID
                FROM bny_usermeta um
                WHERE um.meta_key LIKE '" . $meta_key . "' AND um.meta_value LIKE '%" . $query_arr . "%' 
            ";
        }else if(!is_array($meta_key) && is_array($query_arr)){
            $new_sql .= "
                SELECT DISTINCT(user_id) as ID
                FROM bny_usermeta um
                WHERE um.meta_key LIKE '" . $meta_key . "' AND 
            ";
            if(sizeof($query_arr) == 1){
                $new_sql .= " um.meta_value LIKE '%" . $query_arr[0] . "%' ";
            }else{
                $new_sql .= " (";
                $last_index = sizeof($query_arr) - 1;
                $ii = 0;
                foreach ($query_arr as $q) {
                    if($ii != $last_index){
                        $new_sql .= " um.meta_value LIKE '%" . $q . "%' OR ";
                    }else{
                        $new_sql .= " um.meta_value LIKE '%" . $q . "%' ";
                    }
                    $ii++;
                }
                $new_sql .= ")";
            }
        }

        return $new_sql;
    }

    global $wpdb;
    $number = 50; // number of authors per page
    // $paged = (get_query_var('paged')) ? get_query_var('paged') : 1;
    // $offset = ($paged - 1) * $number;
    $query = [];
    // $total_users = 0;
    // $total_pages = 0;
    $filter_count = 0;

    $search_genre = get_values_from_url('genre');
    $search_grew_up_langs = get_values_from_url('grew_up_languages');
    $search_writing_for = get_values_from_url('for');
    // $search_fav_author = isset($_GET['fav_author']) && !empty($_GET['fav_author']) ? sanitize_text_field($_GET['fav_author']) : '';
    $search_fav_author = get_values_from_url('fav_author');
    // $search_subject = isset($_GET['subject']) && !empty($_GET['subject']) ? sanitize_text_field($_GET['subject']) : '';
    $search_subject = get_values_from_url('subject');
    // $search_curr_loc = isset($_GET['curr_loc']) && !empty($_GET['curr_loc']) ? sanitize_text_field($_GET['curr_loc']) : '';
    $search_curr_loc = get_values_from_url('curr_loc');
    $search_story_loc_id = isset($_GET['loc_id']) && !empty($_GET['loc_id']) ? sanitize_text_field($_GET['loc_id']) : '';
    $search_story_lang = isset($_GET['lang']) && !empty($_GET['lang']) ? sanitize_text_field($_GET['lang']) : '';
    $search_term = isset($_GET['nom_de_plume']) && !empty($_GET['nom_de_plume']) ? sanitize_text_field($_GET['nom_de_plume']) : '';

    //searches for authors according to their name
    if (
        empty($search_term) && empty($search_story_loc_id) && empty($search_story_lang) && empty($search_genre)
        && empty($search_grew_up_langs) && empty($search_writing_for) && empty($search_fav_author)
        && empty($search_subject) && empty($searchsearch_curr_loc_genre) && empty($search_curr_loc)
    ) {
        // $query = get_users('&offset=' . $offset . '&number=' . $number);
        // $total_users = count($users);
        // $total_pages = intval($total_users / $number) + 1;
    } else {

        $search_sql = '';


        if (!empty($search_term)) {
            $search_sql .=
                "
                SELECT DISTINCT(p.post_author) as ID FROM bny_posts p WHERE p.post_title LIKE '%" . $search_term . "%'
                UNION
                SELECT ID FROM bny_users WHERE display_name LIKE '%" . $search_term . "%' 
                UNION 
                SELECT DISTINCT(user_id) as ID FROM bny_usermeta 
                WHERE 
                (meta_key LIKE 'first_name' AND meta_value LIKE '%" . $search_term . "%')
                AND
                (meta_key LIKE 'last_name' AND meta_value LIKE '%" . $search_term . "%')
            ";
            $filter_count++;
        }

        if (!empty($search_story_loc_id)) {
            $search_sql .= ($search_sql != '') ? ' UNION ALL ' : '';
            $search_sql .=
                "
                 SELECT DISTINCT(p.post_author) as ID
                FROM bny_postmeta pm
                INNER JOIN bny_posts p ON pm.post_id = p.ID
                WHERE pm.meta_key LIKE 'stories_place' AND pm.meta_value LIKE '%" . $search_story_loc_id . "%' 
            ";
            $filter_count++;
        }

        if (!empty($search_story_lang)) {
            $search_sql .= ($search_sql != '') ? ' UNION ALL ' : '';
            $search_sql .=
                "
                 SELECT DISTINCT(p.post_author) as ID
                FROM bny_postmeta pm
                INNER JOIN bny_posts p ON pm.post_id = p.ID
                WHERE pm.meta_key LIKE 'place_languages' AND pm.meta_value LIKE '%" . $search_story_lang . "%' 
            ";
            $filter_count++;
        }

        if (!empty($search_genre)) {
            $search_sql .= get_sql_from_query_vars($search_genre, $search_sql, 'write_genres');
            $filter_count++;
        }

        if (!empty($search_grew_up_langs)) {
            $search_sql .= get_sql_from_query_vars($search_grew_up_langs, $search_sql, 'grew_up_languages');
            $filter_count++;
        }

        if (!empty($search_writing_for)) {
            $search_sql .= get_sql_from_query_vars($search_writing_for, $search_sql, 'write_for');
            $filter_count++;
        }


        //==============


        if (!empty($search_fav_author)) {
            $search_sql .= get_sql_from_query_vars($search_fav_author, $search_sql, 'fav_authors');
            $filter_count++;
        }

        if (!empty($search_subject)) {
            $search_sql .= get_sql_from_query_vars($search_subject, $search_sql, 'fav_subject_to_write_about');
            $filter_count++;
        }

        if (!empty($search_curr_loc)) {
            // $search_sql .= get_sql_from_query_vars($search_curr_loc, $search_sql, ['current_location', 'current_location_city', 'current_location_nation']);
            $search_sql .= get_sql_from_query_vars($search_curr_loc, $search_sql, 'current_location_nation');
            $filter_count++;
        }



        $search_results = $wpdb->get_results($search_sql);

        $query = $search_results;
        // var_dump($query) ;
        // echo '<br>';
        // $total_users = sizeof($search_results);
        var_dump($search_sql);
        // var_dump($total_users) ;
        // echo '<br>';
        // $total_pages = intval($total_users / $number) + 1;
        // var_dump($total_pages) ;
    }

    ?>

    <form method="GET" action="" id="filter-contributors">
        <input type="text" name="nom_de_plume" id="search-by-author-title" placeholder="Search by author or title of story" value="<?php echo $search_term; ?>">
        <p>Advanced Search</p>
        Show me contributors who:
        <div class="contributors-form-options">
            <p> write in this genre</p>
            <select name="genre[]" id="genre" multiple="multiple">

                <option value="poetry" <?php echo (isset($_GET['genre']) && in_array('poetry', $search_genre)) ? 'selected' : ''; ?>>
                    Poetry</option>
                <option value="prose" <?php echo (isset($_GET['genre']) && in_array('prose', $search_genre)) ? 'selected' : ''; ?>>
                    Prose</option>
                <option value="fiction" <?php echo (isset($_GET['genre']) && in_array('fiction', $search_genre)) ? 'selected' : ''; ?>>Fiction</option>
                <option value="novels" <?php echo (isset($_GET['genre']) && in_array('novels', $search_genre)) ? 'selected' : ''; ?>>
                    Novels</option>
                <option value="short_stories" <?php echo (isset($_GET['genre']) && in_array('short_stories', $search_genre)) ? 'selected' : ''; ?>>Short Stories</option>
                <option value="diary" <?php echo (isset($_GET['genre']) && in_array('diary', $search_genre)) ? 'selected' : ''; ?>>
                    Diary</option>
                <option value="journalism" <?php echo (isset($_GET['genre']) && in_array('journalism', $search_genre)) ? 'selected' : ''; ?>>Journalism</option>
                <option value="essays" <?php echo (isset($_GET['genre']) && in_array('essays', $search_genre)) ? 'selected' : ''; ?>>
                    Long Form Essays</option>
                <option value="play_scripts" <?php echo (isset($_GET['genre']) && in_array('play_scripts', $search_genre)) ? 'selected' : ''; ?>>Play Scripts</option>
                <option value="film_scripts" <?php echo (isset($_GET['genre']) && in_array('film_scripts', $search_genre)) ? 'selected' : ''; ?>>Film Scripts</option>
                <option value="love_letters" <?php echo (isset($_GET['genre']) && in_array('love_letters', $search_genre)) ? 'selected' : ''; ?>>Love Letters</option>
                <option value="manifestos" <?php echo (isset($_GET['genre']) && in_array('manifestos', $search_genre)) ? 'selected' : ''; ?>>Manifestos</option>
                <option value="songs" <?php echo (isset($_GET['genre']) && in_array('songs', $search_genre)) ? 'selected' : ''; ?>>
                    Songs</option>
                <option value="other" <?php echo (isset($_GET['genre']) && in_array('other', $search_genre)) ? 'selected' : ''; ?>>
                    Other</option>
            </select>
            <strong>,</strong>
        </div>
        <div class="contributors-form-options">
            <p>grew up speaking this language</p>
            <select name="grew_up_languages[]" id="grew_up_languages" multiple="multiple">
                <?php foreach ($search_grew_up_langs as $lang) {
                    echo '<option value="' . $lang . '" >' . $lang . '</option>';
                } ?>
            </select>
            <strong>,</strong>
        </div>
        <div class="contributors-form-options">
            <p>write for these reasons</p>
            <select name="for[]" id="write_for" multiple="multiple">
                <option value="school" <?php echo (isset($_GET['for']) && in_array('school', $search_writing_for)) ? 'selected' : ''; ?>>for school</option>
                <option value="friends" <?php echo (isset($_GET['for']) && in_array('friends', $search_writing_for)) ? 'selected' : ''; ?>>for friends</option>
                <option value="private" <?php echo (isset($_GET['for']) && in_array('private', $search_writing_for)) ? 'selected' : ''; ?>>for themselves, privately</option>
                <option value="public_online" <?php echo (isset($_GET['for']) && in_array('public_online', $search_writing_for)) ? 'selected' : ''; ?>>for publicly online</option>
                <option value="paid_newspaper" <?php echo (isset($_GET['for']) && in_array('paid_newspaper', $search_writing_for)) ? 'selected' : ''; ?>>to be paid for a newspaper or journal</option>
                <option value="paid_books" <?php echo (isset($_GET['for']) && in_array('paid_books', $search_writing_for)) ? 'selected' : ''; ?>>to be paid to write books</option>
                <option value="children" <?php echo (isset($_GET['for']) && in_array('children', $search_writing_for)) ? 'selected' : ''; ?>>for children</option>
                <option value="unborn_readers" <?php echo (isset($_GET['for']) && in_array('unborn_readers', $search_writing_for)) ? 'selected' : ''; ?>>for readers as-yet unborn</option>
                <option value="other" <?php echo (isset($_GET['for']) && in_array('other', $search_writing_for)) ? 'selected' : ''; ?>>
                    Other</option>
            </select>

            <strong>,</strong>
        </div>
        <div class="contributors-form-options">
            <p>enjoy reading these writers</p>
            <!-- <input type="text" name="fav_author" placeholder="John green" value="<?php //echo $search_fav_author; 
                                                                                        ?>"> -->
            <select name="fav_author[]" id="fav_author" multiple="multiple">
                <?php foreach ($search_fav_author as $lang) {
                    echo '<option value="' . $lang . '" >' . $lang . '</option>';
                } ?>
            </select>
            <strong>,</strong>
        </div>
        <div class="contributors-form-options">
            <p>like to write about this subject</p>
            <!-- <input type="text" name="subject" placeholder="crime" value="<?php //echo $search_subject; 
                                                                                ?>"> -->
            <select name="subject[]" id="subject" multiple="multiple">
                <?php foreach ($search_subject as $lang) {
                    echo '<option value="' . $lang . '" >' . $lang . '</option>';
                } ?>
            </select>
            <strong>,</strong>
        </div>
        <div class="contributors-form-options">
            <p>or, who live in</p>
            <select name="curr_loc[]" id="curr_loc" class="current_location select-nation-search" multiple="multiple">
                <!-- <option value="">Select nation</option> -->
                <?php
                $nations = pol_get_nations();
                foreach ($nations as $nation) {
                    $is_selected = '';
                    if (!empty($search_curr_loc) && in_array($nation, $search_curr_loc)) {
                        $is_selected = 'selected';
                    }
                    echo '<option value="' . $nation . '" ' . $is_selected . '>' . $nation . '</option>';
                }
                ?>
            </select>
        </div>

        <p>NOTE: Search for contributors by filling in the fields. <strong>To ignore a field leave it empty.</strong>
        </p>

        <input type="submit" value="Search">

    </form>


    <?php

    $user_id_list = [];
    // var_dump($query) ;
    foreach ($query as $q) {
        array_push($user_id_list, $q->ID);
    }
    // var_dump($user_id_list);


    function keep_repeated_elements($array, $count)
    {
        $counted_array = array_count_values($array);
        $filtered_array = array_filter($counted_array, function ($value) use ($count) {
            return $value == $count;
        });
        return array_keys($filtered_array);
    }

    //if more that one filter has been used, then only keep the duplicate users
    // var_dump($filter_count);

    // echo '<br>';
    // var_dump($user_id_list);
    // echo '=======<br>';

    // var_dump(keep_repeated_elements([1,1,2,2,2,3], 3));

    if ($filter_count > 1) {
        // var_dump('======');
        $user_id_list = keep_repeated_elements($user_id_list, (int)$filter_count);

        // $total_users = sizeof($user_id_list);
    }

    // var_dump($user_id_list);

    if($filter_count == 0){
        $users = get_users();

        // Initialize an empty array to store the user IDs
        $user_id_list = [];

        // Loop through each user and add the ID to the array
        foreach ($users as $user) {
            $user_id_list[] = $user->ID;
        }
    }

    //$logged_in_check = is_user_logged_in();
    if (is_user_logged_in() && get_user_meta(get_current_user_id(), 'rae_approved', true) == "1") {
        $rae = true;
    }



    // Define the number of items per page
    $items_per_page =  50;

    // Get the current page number from the URL or set it to  1 if it doesn't exist
    $paged = (get_query_var('paged')) ? get_query_var('paged') :  1;

    // Calculate the start index for the slice of the array
    $start_index = ($paged -  1) * $items_per_page;

    // Calculate the end index for the slice of the array
    $end_index = $start_index + $items_per_page;

    // Slice the array to get the items for the current page
    $user_id_list_for_page = array_slice($user_id_list, $start_index, $items_per_page);




    
    // var_dump($user_id_list);
    // echo '<br>======<br>';
    // var_dump($user_id_list_for_page);


    // $rae_check =  get_user_meta(get_current_user_id(), 'rae_approved', true) == 1 ;
    echo '<ul id="users">';
    foreach ($user_id_list_for_page as $q) {
        // echo $q . '<br>';

        if ((int)$q == 0) {
            continue;
        }

        //check if user exists
        $curr_user = get_user_by('id', (int) $q);
        if (!$curr_user) {
            continue;
        }

        $published_stories_count = $count = count_user_posts($q, 'story', true);
        $ind_nom_de_plume = get_user_meta($q, 'nom_de_plume', true);
        $curr_location_nation = get_user_meta($q, 'current_location_nation', true);
        $ind_username = $curr_user->display_name;
        $display_name = $ind_username ;
        $seeking_commission_meta = get_user_meta($q, 'currently_seeking_commission', true);
        $check_rae_approved = get_user_meta($q, 'rae_approved', true) == 1;
        if (!empty($seeking_commission_meta)) {

            if ($seeking_commission_meta[0] == 1 && $rae == true) {

                $red_dot = '<span class="pol-red-dot">
                <i class="fa-solid fa-circle"></i></span>';
            } else {

                $red_dot = '';
            }
        } else {

            $red_dot = '';
        }


    ?>

        <li class="user clearfix">
            <a href="<?php echo get_author_posts_url($q); ?>">
                <div class="user-avatar">

                    <?php echo $red_dot;
                    echo '<img src="' . pol_get_user_profile_img((int) $q) . '" class="avatar avatar-80 photo" width="80" />'; ?>
                </div>
                <div class="user-data">
                    <h4 class="user-name">
                        <?php echo $display_name; ?>
                    </h4>

                    <p>
                        <?php echo $curr_location_nation; ?>
                    </p>

                </div>
            </a>
        </li>
    <?php }
    echo '</ul>';


    // Calculate total pages
    $total_pages = ceil(sizeof($user_id_list) / $items_per_page);

    // Display pagination links
    if ($total_pages >  1) {
        $current_page = max(1, get_query_var('paged'));
        echo '<div id="pagination" class="clearfix">';
        echo paginate_links(array(
            'base' => str_replace(999999999, '%#%', esc_url(get_pagenum_link(999999999))),
            'format' => '/page/%#%',
            'current' => $current_page,
            'total' => $total_pages,
            'prev_text' => __('« Previous'),
            'next_text' => __('Next »'),
        ));
        echo '</div>';
    }

    if (sizeof($user_id_list) < 1) {
        echo '<h5>There are no contributors who match your criteria. Please check your spellings, or try again with fewer criteria.</h5>';
    }

    ?>
</div>

<?php
get_footer();
?>