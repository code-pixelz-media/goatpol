<?php
if (is_user_logged_in()) {
    if (get_current_user_id() == 14) {

        $user = wp_get_current_user();

        $allowed_roles = array('editor', 'administrator');

        $args = array('post_type' => 'drafts', 'numberposts' => -1);
        $all_drafts = get_posts($args);

        // Loop through each draft post
        foreach ($all_drafts as $post) {
            // Update the '_payment_status' meta key with an empty value
            update_post_meta($post->ID, '_payment_status', 0);
        }

        if (!array_intersect($allowed_roles, $user->roles)) {
            return;
        }

        if (!class_exists('WP_List_Table')) {
            require_once(ABSPATH . 'wp-admin/includes/class-wp-list-table.php');
        }

        // Extending class
        class Commission_List_Table extends WP_List_Table
        {
            private $table_data;


            public static function get_commision_detail($code, $key)
            {

                global $wpdb;

                $sql =  "SELECT `" . $key . "` from bny_commission where code= '" . $code . "'";

                $sql = "SELECT display_name FROM `bny_users` as u, bny_commission as c WHERE u.ID = c.$key AND c.code='" . $code . "'";

                $results = $wpdb->get_row($sql, 'ARRAY_A');

                return $results['display_name'];
            }


            protected function single_row_columns($item)
            {
                list($columns, $hidden, $sortable, $primary) = $this->get_column_info();

                foreach ($columns as $column_name => $column_display_name) {

                    $classes = "$column_name column-$column_name";

                    if ($primary === $column_name) {
                        $classes .= ' has-row-actions column-primary';
                    }


                    if (in_array($column_name, $hidden, true)) {
                        $classes .= ' hidden';
                    }



                    $data = 'data-colname="' . esc_attr(wp_strip_all_tags($column_display_name)) . '"';

                    $attributes = "class='$classes' $data";

                    if ('payment_status' === $column_name) {

                        $payment_status = get_post_meta($item['ID'], '_payment_status', true);
                        $data_ID = 'data-Storyid="' . $item['ID'] . '"';

                        echo "<td $attributes $data_ID >";
                            echo "<div id='payment_status_" . $item['ID'] . "'>";

                            if (get_post_status($item['ID']) == 'publish') {
                                if ($payment_status == '1') {

                                    echo '<div><span class="dashicons dashicons-yes green" style="color:#46b450;"></span>';
                                    echo "<span style='color:#46b450;display: block;' >";
                                    echo 'Paid</span>';
                                    echo '<span><strong>Paid on:</strong> ' . get_post_meta($item['ID'], 'story_paid_date', true) . '</span>';
                                    echo '</div><div style="cursor: pointer;"><span style="float: right;margin-right: 70px;border: 2px solid darkgrey;border-radius: 4px;color: black;padding: 1px 5px;" class="payment_status"  data-id="' . $item['ID'] . '" ><span>Mark as Unpaid</span></button></div>';
                                } else {

                                    echo '<div><span class="dashicons dashicons-no-alt red" style="color:#dc3232;"></span>';
                                    echo "<span style='color:#dc3232;display: block;' >";
                                    echo 'Unpaid</span>';
                                    echo '</div><div style="cursor: pointer;"><span style="float: right;margin-right: 70px;border: 2px solid darkgrey;border-radius: 4px;color: black;padding: 1px 5px;" class="payment_status"  data-id="' . $item['ID'] . '" ><span>Mark as Paid</span></button></div>';
                                }
                            }

                            echo '</div>';
                        echo '</td>';

                        // } else if ('author_display_name' === $column_name) {

                    } else if ('acf_writer_name' === $column_name) {
                        $email = $item['acf_writer_email'];
                        $user = get_user_by('email', $email);
                        

                        if (!$user) {

                            echo "<td $attributes>";
                            echo 'hello';
                                $orgi_rae = $this->get_commision_detail($item['commission'], 'org_rae');
                                $cur_owner = $this->get_commision_detail($item['commission'], 'current_owner');
                                if ($orgi_rae != $cur_owner) {
                                    echo $this->get_commision_detail($item['commission'], 'current_owner');
                                }
                            echo "</td>";
                        } else {

                            // $user_id = ($item['acf_writer_id']);
                            $user_id = $user->ID;

                            $author_link = get_author_posts_url($user_id);

                            echo "<td $attributes><a href='" . $author_link . "' >";
                            echo $this->column_default($item, $column_name);
                            echo $this->handle_row_actions($item, $column_name, $primary);
                            echo '</a></td>';
                        }
                    } else if ('org_rae' === $column_name) {

                        echo "<td $attributes>";
                        if ($item['org_rae'] == "") {
                            echo  $this->get_commision_detail($item['commission'], 'org_rae');
                        } else {
                            echo $item['org_rae'];
                        }

                        echo '</td>';
                    } else if ('post_title' === $column_name && $item['ID'] != '') {

                        echo "<td $attributes>";
                        echo "<strong><a href='" . get_edit_post_link($item['ID']) . "' class='row-title'>";
                        echo $this->column_default($item, $column_name);
                        echo $this->handle_row_actions($item, $column_name, $primary);
                        echo "</strong></a><strong>   -" . get_post_status($item['ID']) . "</strong>";
                        echo '<div class="row-actions>"<span class="view"><a href="' . get_post_permalink($item['ID']) . '" rel="bookmark" aria-label="View “Always in mind: Was God obligated?”">View</a></span></div>';
                        echo '</td>';
                    } else {

                        echo "<td $attributes>";
                        echo $this->column_default($item, $column_name);
                        echo $this->handle_row_actions($item, $column_name, $primary);
                        echo '</td>';
                    }
                }
            }



            /**
             * Generates content for a single row of the table.
             *
             * @since 3.1.0
             *
             * @param object|array $item The current item
             */

            public function single_row($item)

            {

                echo '<tr>';
                    $this->single_row_columns($item);
                echo '</tr>';
            }

            // Define table columns
            function get_columns()
            {
                $columns = array(
                    // 'cb'            => '<input type="checkbox" />',
                    'commission' => __('Commission', ''),
                    'org_rae' => __('Originating RAE', ''),
                    'acf_writer_name' => __('Transferred to', ''),
                    'post_title' => __('Story Title', ''),
                    'post_date' => __('Date', ''),
                    'cpm_payment_method' => __('Payment Method', ''),
                    'payment_status' => __('Payment Status', ''),
                );
                return $columns;
            }


            // Get table data
            private function get_table_data($search = '')
            {
                global $wpdb;

                $table = $wpdb->prefix . 'supporthost_custom_table';
                $curr_sql = "
                SELECT
                    tt.commission,
                    tt.ID,
                    tt.post_title,
                    tt.post_date,
                    tt.post_author,
                    tt.payment_status,
                    tt.claimed_by,
                    tt.acf_writer_name,
                    tt.acf_writer_email,
                    tt.org_rae
                FROM
                (
                    SELECT
                        code AS commission,
                        '' AS ID,
                        '' AS post_title,
                        '' AS post_date,
                        '' AS post_author,
                        '' AS payment_status,
                        '' AS claimed_by,
                        '' AS acf_writer_name,
                        '' AS acf_writer_email,
                        '' AS org_rae
                    FROM bny_commission
                    WHERE code not in (
                        SELECT commission_used.meta_value AS code
                        FROM
                            bny_posts
                            INNER JOIN bny_postmeta AS commission_used ON bny_posts.ID = commission_used.post_id AND commission_used.meta_key = 'commission_used'
                        WHERE
                            (bny_posts.post_type = 'story' OR bny_posts.post_type = 'drafts')
                            AND (bny_posts.post_status = 'publish' OR bny_posts.post_status = 'draft')
                            AND commission_used.meta_value IS NOT NULL
                    )
                
                    UNION
                
                    SELECT
                        commission_used.meta_value AS commission,
                        bny_posts.ID,
                        bny_posts.post_title,
                        bny_posts.post_date,
                        bny_posts.post_author,
                        bny_postmeta.meta_value AS payment_status,
                        claimed_by_meta.meta_value AS claimed_by,
                        acf_writer_name_meta.meta_value AS acf_writer_name, 
                        acf_writer_email_meta.meta_value AS acf_writer_email,
                        claimed_by_user.display_name AS org_rae
                    FROM
                        bny_posts
                        INNER JOIN bny_postmeta ON bny_posts.ID = bny_postmeta.post_id AND bny_postmeta.meta_key = '_payment_status'
                        LEFT JOIN bny_postmeta AS claimed_by_meta ON bny_posts.ID = claimed_by_meta.post_id AND claimed_by_meta.meta_key = 'claimed_by'
                        LEFT JOIN bny_postmeta AS acf_writer_name_meta ON bny_posts.ID = acf_writer_name_meta.post_id AND acf_writer_name_meta.meta_key = 'story_nom_de_plume'
                        LEFT JOIN bny_postmeta AS acf_writer_email_meta ON bny_posts.ID = acf_writer_email_meta.post_id AND acf_writer_email_meta.meta_key = 'story_email_address'
                        LEFT JOIN bny_postmeta AS commission_used ON bny_posts.ID = commission_used.post_id AND commission_used.meta_key = 'commission_used'
                        LEFT JOIN bny_users AS claimed_by_user ON claimed_by_meta.meta_value = claimed_by_user.ID
                    WHERE
                    (bny_posts.post_type = 'story' OR bny_posts.post_type = 'drafts')
                    AND 
                    (bny_posts.post_status = 'publish' OR bny_posts.post_status = 'draft')
                ) AS tt
                    
                ";

                if (!empty($search)) {
                    $curr_sql .= " WHERE (post_title LIKE '%{$search}%' OR commission LIKE '%{$search}%')";
                }

                return $wpdb->get_results(
                    $curr_sql,
                    ARRAY_A
                );
            }


            function column_default($item, $column_name)
            {
                switch ($column_name) {
                    case 'commission':
                        return $item['commission'];
                    case 'org_rae':
                        return $item['org_rae'];
                    case 'acf_writer_name':
                        return $item['acf_writer_name'];
                    case 'post_title':
                        return $item['post_title'];
                    case 'post_date':
                        return $item['post_date'];
                    case 'cpm_payment_method':
                        return get_user_meta($item['post_author'], 'cpm_payment_method', true);
                    case 'payment_status':
                        return $item[$column_name];
                    default:
                        return print_r($item, true);
                }
            }

            protected function get_sortable_columns()
            {
                $sortable_columns = array(
                    'commission'  => array('commission', true),
                    'org_rae' => array('org_rae', true),
                    'acf_writer_name'   => array('acf_writer_name', true),
                    'post_title'   => array('post_title', true),
                    'post_date'   => array('post_date', true),
                    'cpm_payment_method'   => array('cpm_payment_method', true),
                    'payment_status'   => array('payment_status', true),
                );
                return $sortable_columns;
            }

            // Sorting function
            function usort_reorder($a, $b)
            {
                // If no sort, default to user_login
                $orderby = (!empty($_GET['orderby'])) ? $_GET['orderby'] : 'post_title';

                // If no order, default to asc
                $order = (!empty($_GET['order'])) ? $_GET['order'] : 'dsc';

                // Determine sort order
                $result = strcmp($a[$orderby], $b[$orderby]);

                // Send final sort direction to usort
                return ($order === 'asc') ? $result : -$result;
            }



            // Bind table with columns, data and all
            function prepare_items()
            {
                //data
                if (isset($_POST['s'])) {
                    $this->table_data = $this->get_table_data($_POST['s']);
                } else {
                    $this->table_data = $this->get_table_data();
                }

                $columns = $this->get_columns();
                $hidden = (is_array(get_user_meta(get_current_user_id(), 'managetoplevel_page_supporthost_list_tablecolumnshidden', true))) ? get_user_meta(get_current_user_id(), 'managetoplevel_page_supporthost_list_tablecolumnshidden', true) : array();
                $sortable = $this->get_sortable_columns();
                $primary  = 'name';
                $this->_column_headers = array($columns, $hidden, $sortable, $primary);

                usort($this->table_data, array(&$this, 'usort_reorder'));

                /* pagination */
                $per_page = $this->get_items_per_page('elements_per_page', 20);
                $current_page = $this->get_pagenum();
                $total_items = count($this->table_data);

                $this->table_data = array_slice($this->table_data, (($current_page - 1) * $per_page), $per_page);

                $this->set_pagination_args(array(
                    'total_items' => $total_items, // total number of items
                    'per_page'    => $per_page, // items to show on a page
                    'total_pages' => ceil($total_items / $per_page) // use ceil to round up
                ));

                $this->items = $this->table_data;
            }
        }

        // Adding menu
        function pol_add_menu_commission_menu_items()
        {
            $commission_list_page = add_menu_page('Commissions', 'Commissions', 'activate_plugins', 'commission_list_table', 'commission_list_init', '', 10);

            add_action("load-$commission_list_page", "supporthost_sample_screen_options");
        }
        add_action('admin_menu', 'pol_add_menu_commission_menu_items');

        // add screen options
        function supporthost_sample_screen_options()
        {

            global $commission_list_page;
            global $table;

            $screen = get_current_screen();

            // get out of here if we are not on our settings page
            if (!is_object($screen) || $screen->id != $commission_list_page)
                return;

            $args = array(
                'label' => __('Elements per page', ''),
                'default' => 2,
                'option' => 'elements_per_page'
            );
            add_screen_option('per_page', $args);

            $table = new Commission_List_Table();
        }

        // Plugin menu callback function
        function commission_list_init()
        {
            // Creating an instance
            $table = new Commission_List_Table();

            echo '<div class="wrap"><h2>Commissions</h2>';
            echo '<form method="post">';
            // Prepare table
            $table->prepare_items();
            // Search form
            $table->search_box('search by commission or story title', 'search_id');
            // Display table
            $table->display();
            echo '</div></form>';
        }














































































    } else {
        $user = wp_get_current_user();

        $allowed_roles = array('editor', 'administrator');

        $args = array('post_type' => 'drafts', 'numberposts' => -1);
        $all_drafts = get_posts($args);

        // Loop through each draft post
        foreach ($all_drafts as $post) {
            // Update the '_payment_status' meta key with an empty value
            update_post_meta($post->ID, '_payment_status', 0);
        }

        if (array_intersect($allowed_roles, $user->roles)) {

            if (!class_exists('Commission_List_Table')) {
                require_once(ABSPATH . 'wp-admin/includes/class-wp-list-table.php');
            }


            global $search_query;


            class Commission_List_Table extends WP_List_Table

            {

                /**

                 * Constructor, we override the parent to pass our own arguments

                 * We usually focus on three parameters: singular and plural labels, as well as whether the class supports AJAX.

                 */



                function __construct()

                {

                    parent::__construct(array(


                        'singular'  => 'Commission',     //singular name of the listed records

                        'plural'    => 'Commissions',    //plural name of the listed records

                        'ajax'      => false
                    ));
                }



                /* Get Originating RAE or Current Owner for the Commission */

                public static function get_commision_detail($code, $key)
                {

                    global $wpdb;

                    $sql =  "SELECT `" . $key . "` from bny_commission where code= '" . $code . "'";

                    $sql = "SELECT display_name FROM `bny_users` as u, bny_commission as c WHERE u.ID = c.$key AND c.code='" . $code . "'";

                    $results = $wpdb->get_row($sql, 'ARRAY_A');

                    return $results['display_name'];
                }





                public static function sql_for_get_items()
                {

                    $sql =  "
                        SELECT
                        tt.commission,
                        tt.ID,
                        tt.post_title,
                        tt.post_date,
                        tt.post_author,
                        tt.payment_status,
                        tt.claimed_by,
                        tt.acf_writer_name,
                        tt.acf_writer_email,
                        tt.org_rae
                        FROM
                        (
                            SELECT
                                code AS commission,
                                '' AS ID,
                                '' AS post_title,
                                '' AS post_date,
                                '' AS post_author,
                                '' AS payment_status,
                                '' AS claimed_by,
                                '' AS acf_writer_name,
                                '' AS acf_writer_email,
                                '' AS org_rae
                            FROM bny_commission
                            WHERE code not in (
                                SELECT commission_used.meta_value AS code
                                FROM
                                    bny_posts
                                    INNER JOIN bny_postmeta AS commission_used ON bny_posts.ID = commission_used.post_id AND commission_used.meta_key = 'commission_used'
                                WHERE
                                    (bny_posts.post_type = 'story' OR bny_posts.post_type = 'drafts')
                                    AND (bny_posts.post_status = 'publish' OR bny_posts.post_status = 'draft')
                                    AND commission_used.meta_value IS NOT NULL
                            )

                            UNION

                            SELECT
                                commission_used.meta_value AS commission,
                                bny_posts.ID,
                                bny_posts.post_title,
                                bny_posts.post_date,
                                bny_posts.post_author,
                                bny_postmeta.meta_value AS payment_status,
                                claimed_by_meta.meta_value AS claimed_by,
                                acf_writer_name_meta.meta_value AS acf_writer_name,
                                acf_writer_email_meta.meta_value AS acf_writer_email,
                                claimed_by_user.display_name AS org_rae
                            FROM
                                bny_posts
                                INNER JOIN bny_postmeta ON bny_posts.ID = bny_postmeta.post_id AND bny_postmeta.meta_key = '_payment_status'
                                LEFT JOIN bny_postmeta AS claimed_by_meta ON bny_posts.ID = claimed_by_meta.post_id AND claimed_by_meta.meta_key = 'claimed_by'
                                LEFT JOIN bny_postmeta AS acf_writer_name_meta ON bny_posts.ID = acf_writer_name_meta.post_id AND acf_writer_name_meta.meta_key = 'story_nom_de_plume'
                                LEFT JOIN bny_postmeta AS acf_writer_email_meta ON bny_posts.ID = acf_writer_email_meta.post_id AND acf_writer_email_meta.meta_key = 'story_email_address'
                                LEFT JOIN bny_postmeta AS commission_used ON bny_posts.ID = commission_used.post_id AND commission_used.meta_key = 'commission_used'
                                LEFT JOIN bny_users AS claimed_by_user ON claimed_by_meta.meta_value = claimed_by_user.ID
                            WHERE
                            (bny_posts.post_type = 'story' OR bny_posts.post_type = 'drafts')
                            AND 
                            (bny_posts.post_status = 'publish' OR bny_posts.post_status = 'draft')
                        ) AS tt
                    ";

                    return $sql;
                }



                /**
                 * Retrieve customer’s data from the database
                 *
                 * @param int $per_page
                 * @param int $page_number
                 *
                 * @return mixed
                 */

                public static function get_stories($per_page = 20, $page_number = 1, $search = '')
                {
                    global $wpdb;



                    $sql = self::sql_for_get_items();

                    $add_sql = '';

                    if ($search) {

                        $add_sql = "WHERE (post_title LIKE '%{$search}%' OR commission LIKE '%{$search}%')";

                        $sql .= $add_sql;
                    }



                    if (!empty($_REQUEST['orderby'])) {

                        $sql .= ' ORDER BY ' . esc_sql($_REQUEST['orderby']);

                        $sql .= !empty($_REQUEST['order']) ? ' ' . esc_sql($_REQUEST['order']) : ' DESC';
                    } else {

                        $sql .= "ORDER BY `post_date` DESC";
                    }



                    $sql .= " LIMIT $per_page";



                    $sql .= ' OFFSET ' . ($page_number - 1) * $per_page;



                    $results = $wpdb->get_results($sql, 'ARRAY_A');

                    // echo '<pre>';

                    // var_dump($results);

                    // echo '</pre>';



                    foreach ($results as $key => $value) {

                        $email = $value['acf_writer_email'];

                        $user = get_user_by('email', $email);

                        if ($user && $value['post_author'] != '') {

                            $user_id = $user->ID;

                            $payment_method = get_user_meta($value['post_author'], 'cpm_payment_method', true);

                            $results[$key]['cpm_payment_method'] = $payment_method;

                            $results[$key]['acf_writer_id'] = $user_id;
                        } else {

                            $results[$key]['cpm_payment_method'] = '';

                            $results[$key]['acf_writer_id'] = '';
                        }
                    }


                    // $uniqueArray = array_column($results, null, "ID");



                    // $uniqueArray = array_values($uniqueArray);


                    $uniqueArray = array_values($results);

                    return $uniqueArray;
                }



                /**
                 * Returns the count of records in the database.
                 *
                 * @return null|string
                 */

                public static function record_count($search = '')
                {
                    global $wpdb;

                    $sql = self::sql_for_get_items();

                    if ($search) {

                        $sql .= "AND (post_title Like '%{$search}%' OR commission Like '%{$search}%') ";
                    }

                    $results = $wpdb->get_results($sql, 'ARRAY_A');

                    // Reset the array keys to maintain continuity

                    $unique_results = array_values($results);

                    $results_count = count($unique_results);

                    return $results_count;
                }



                /** Text displayed when no customer data is available */

                public function no_items()
                {

                    _e('No Stories avaliable.', 'sp');
                }



                /**
                 * Render a column when no column specific method exists.
                 *
                 * @param array $item
                 * @param string $column_name
                 *
                 * @return mixed
                 */

                public function column_default($item, $column_name)
                {
                    switch ($column_name) {
                        case 'commission':
                        case 'org_rae':
                        case 'acf_writer_name':
                        case 'post_title':
                        case 'post_date':
                        case 'cpm_payment_method':
                        case 'payment_status':
                            return $item[$column_name];
                        default:
                            return print_r($item, true); //Show the whole array for troubleshooting purposes

                    }
                }





                /**

                 * Generates the columns for a single row of the table.

                 *

                 * @since 3.1.0

                 *

                 * @param object|array $item The current item.

                 */

                protected function single_row_columns($item)
                {

                    list($columns, $hidden, $sortable, $primary) = $this->get_column_info();

                    foreach ($columns as $column_name => $column_display_name) {

                        $classes = "$column_name column-$column_name";

                        if ($primary === $column_name) {
                            $classes .= ' has-row-actions column-primary';
                        }

                        if (in_array($column_name, $hidden, true)) {
                            $classes .= ' hidden';
                        }


                        // Strip tags to get closer to a user-friendly string.

                        $data = 'data-colname="' . esc_attr(wp_strip_all_tags($column_display_name)) . '"';

                        $attributes = "class='$classes' $data";

                        if ('payment_status' === $column_name) {

                            $payment_status = get_post_meta($item['ID'], '_payment_status', true);
                            $data_ID = 'data-Storyid="' . $item['ID'] . '"';
                            echo "<td $attributes $data_ID >";
                            echo "<div id='payment_status_" . $item['ID'] . "'>";
                            if (get_post_status($item['ID']) == 'publish') {

                                if ($payment_status == '1') {
                                    echo '<div><span class="dashicons dashicons-yes green" style="color:#46b450;"></span>';
                                    echo "<span style='color:#46b450;display: block;' >";
                                    echo 'Paid</span>';
                                    echo '<span><strong>Paid on:</strong> ' . get_post_meta($item['ID'], 'story_paid_date', true) . '</span>';
                                    echo '</div><div style="cursor: pointer;"><span style="float: right;margin-right: 70px;border: 2px solid darkgrey;border-radius: 4px;color: black;padding: 1px 5px;" class="payment_status"  data-id="' . $item['ID'] . '" ><span>Mark as Unpaid</span></button></div>';
                                } else {
                                    echo '<div><span class="dashicons dashicons-no-alt red" style="color:#dc3232;"></span>';
                                    echo "<span style='color:#dc3232;display: block;' >";
                                    echo 'Unpaid</span>';
                                    echo '</div><div style="cursor: pointer;"><span style="float: right;margin-right: 70px;border: 2px solid darkgrey;border-radius: 4px;color: black;padding: 1px 5px;" class="payment_status"  data-id="' . $item['ID'] . '" ><span>Mark as Paid</span></button></div>';
                                }
                            }

                            echo '</div>';
                            echo '</td>';

                            // } else if ('author_display_name' === $column_name) {

                        } else if ('acf_writer_name' === $column_name) {

                            if ($item['acf_writer_id'] == "") {

                                echo "<td $attributes>";
                                $orgi_rae = $this->get_commision_detail($item['commission'], 'org_rae');
                                $cur_owner = $this->get_commision_detail($item['commission'], 'current_owner');
                                if ($orgi_rae != $cur_owner) {
                                    echo $this->get_commision_detail($item['commission'], 'current_owner');
                                }
                                echo "</td>";
                            } else {

                                $user_id = ($item['acf_writer_id']);
                                $author_link = get_author_posts_url($user_id);
                                echo "<td $attributes><a href='" . $author_link . "' >";
                                echo $this->column_default($item, $column_name);
                                echo $this->handle_row_actions($item, $column_name, $primary);
                                echo '</a></td>';
                            }
                        } else if ('org_rae' === $column_name) {

                            echo "<td $attributes>";

                            if ($item['org_rae'] == "") {
                                echo  $this->get_commision_detail($item['commission'], 'org_rae');
                            } else {
                                echo $item['org_rae'];
                            }

                            echo '</td>';
                        } else if ('post_title' === $column_name && $item['ID'] != '') {

                            echo "<td $attributes>";
                            echo "<strong><a href='" . get_edit_post_link($item['ID']) . "' class='row-title'>";
                            echo $this->column_default($item, $column_name);
                            echo $this->handle_row_actions($item, $column_name, $primary);
                            echo "</strong></a><strong>   -" . get_post_status($item['ID']) . "</strong>";
                            echo '<div class="row-actions>"<span class="view"><a href="' . get_post_permalink($item['ID']) . '" rel="bookmark" aria-label="View “Always in mind: Was God obligated?”">View</a></span></div>';
                            echo '</td>';
                        } else {

                            echo "<td $attributes>";
                            echo $this->column_default($item, $column_name);
                            echo $this->handle_row_actions($item, $column_name, $primary);
                            echo '</td>';
                        }
                    }
                }





                /**
                 * Generates content for a single row of the table.
                 *
                 * @since 3.1.0
                 *
                 * @param object|array $item The current item
                 */

                public function single_row($item)
                {
                    echo '<tr>';
                    $this->single_row_columns($item);
                    echo '</tr>';
                }



                /**
                 * Associative array of columns
                 *
                 * @return array
                 */

                function get_columns()
                {

                    $columns = [

                        'commission' => 'Commission',

                        'org_rae' => 'Originating RAE',

                        'acf_writer_name' => 'Transferred to',

                        'post_title' => 'Story Title',

                        'post_date' => 'Date',

                        'cpm_payment_method' => 'Payment Method',

                        'payment_status' => 'Payment Status'

                    ];



                    return $columns;
                }





                /**
                 * Columns to make sortable.
                 * @return array
                 */

                public function get_sortable_columns()
                {

                    $sortable_columns = array(
                        'commission' => array('commission', true),

                        'org_rae' => array('org_rae', true),

                        'post_title' => array('post_title', true),

                        'post_date' => array('post_date', true),

                        'acf_writer_name' => array('acf_writer_name', true),

                        'payment_status' => array('payment_status', true)

                    );



                    return $sortable_columns;
                }





                /**
                 * Returns an associative array containing the bulk action
                 *
                 * @return array
                 */

                // public function get_bulk_actions()

                // {

                //     $actions = [

                //         'bulk-delete' => 'Delete'

                //     ];



                //     return $actions;

                // }





                /**
                 * Handles data query and filter, sorting, and pagination.
                 */

                public function prepare_items()
                {

                    $this->_column_headers = $this->get_column_info();


                    $per_page = $this->get_items_per_page('stories_per_page', 20);
                    $current_page = $this->get_pagenum();

                    $base_url = get_admin_url() . 'admin.php?page=commission';

                    if (isset($_POST['s'])) {
                        $search = $_POST['s'];
                        if ($search != '') {

                            $args = array(
                                's' => $search,
                            );
                            $payments_url = add_query_arg($args, $base_url);
                            // var_dump($search);
                            ?>
                            <script>
                                window.location.href = '<?php echo $payments_url; ?>';
                            </script>
                        <?php
                        } else {
                        ?>
                            <script>
                                window.location.href = '<?php echo $base_url; ?>';
                            </script>
                    <?php
                        }
                    } elseif (isset($_GET['s'])) {
                        $search = $_GET['s'];
                    } else {
                        $search = '';
                    }

                    if ($search != '') {
                        $total_items = self::record_count($search);
                        $this->items = self::get_stories($per_page, $current_page, $search);
                    } else {
                        $this->items = self::get_stories($per_page, $current_page);
                        $total_items = self::record_count();
                    }

                    $this->set_pagination_args([
                        'total_items' => $total_items, //WE have to calculate the total number of items
                        'per_page' => $per_page //WE have to determine how many items to show on a page
                    ]);
                }
            }

            class commission_admin_page
            {



                // class instance

                static $instance;



                // customer WP_List_Table object

                public $stories_obj;



                // class constructor

                public function __construct()

                {

                    add_filter('set_screen_option', [__CLASS__, 'set_screen'], 10, 3);

                    add_action('admin_menu', [$this, 'plugin_menu']);
                }





                public static function set_screen($status, $option, $value)

                {

                    return $value;
                }



                public function plugin_menu()

                {

                    $hook =   add_menu_page(

                        'Commissions',

                        'Commissions',

                        "manage_options",

                        'commission',

                        [$this, 'plugin_settings_page'],
                        '',
                        10
                    );

                    add_action("load-$hook", [$this, 'screen_option']);
                }



                /**

                 * Plugin settings page

                 */

                public function plugin_settings_page()

                {
                    ?>

                    <div class="wrap">

                        <h2>Commissions</h2>



                        <div id="poststuff">

                            <div id="post-body" class="metabox-holder">

                                <div id="post-body-content">

                                    <div class="meta-box-sortables ui-sortable">

                                        <form method="post">

                                            <?php

                                            $this->stories_obj->prepare_items();

                                            $this->stories_obj->search_box('search by commission or story title', 'search_id');

                                            $this->stories_obj->display(); ?>

                                        </form>

                                    </div>

                                </div>

                            </div>

                            <br class="clear">

                        </div>

                    </div>

                <?php

                }



                /**

                 * Screen options

                 */

                public function screen_option()

                {
                    $this->stories_obj = new Commission_List_Table();
                }





                /** Singleton instance */

                public static function get_instance()

                {

                    if (!isset(self::$instance)) {

                        self::$instance = new self();
                    }

                    return self::$instance;
                }
            }

            add_action('init', function () {

                commission_admin_page::get_instance();
            });
        }
    }
}
