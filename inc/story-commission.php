<?php


function add_payment_meta_to_published_story_commissionDb()
{
    $argss = array('post_status' => 'publish', 'post_type' => 'story', 'numberposts' => -1);
    $all_stories_wo_metaaa = get_posts($argss);

    // Loop through each draft post
    foreach ($all_stories_wo_metaaa as $post) {
        if (!metadata_exists('post', $post->ID, '_payment_status')) {
            // echo $post->ID . ',';
            update_post_meta($post->ID, '_payment_status', 0);
        }
    }
}


if (is_user_logged_in()) {

    if (get_current_user_id() == 14) {
        // add_payment_meta_to_published_story_commissionDb();
    }



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
            $commission_table_name = $wpdb->prefix . 'commission';
            $user_table_name = $wpdb->prefix . 'users';
            $sql =  "SELECT `" . $key . "` from `$commission_table_name` where code= '" . $code . "'";

            $sql = "SELECT display_name FROM `$user_table_name` as u, `$commission_table_name` as c WHERE u.ID = c.$key AND c.code='" . $code . "'";

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
                        // echo 'hello';
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
                    if ('commission' === $column_name && empty($item['post_title'])) {
                        global $wpdb;
                        $commission_table_name = $wpdb->prefix . 'commission';
                        $id = '';
                        if (!empty($item['commission'])) {
                            $code = $item['commission'];
                            $id = $wpdb->get_var($wpdb->prepare("SELECT id FROM $commission_table_name WHERE code LIKE '$code'"));
                        }
                        echo "<td $attributes>";
                        echo $this->column_default($item, $column_name);
                        echo $this->handle_row_actions($item, $column_name, $primary);
                        echo '<div class="commission_action_link">';
                        echo '<a href="javascript:void(0);" class="commission_action" data-id="' . $id . '" data-post_id="' . $item['ID'] . '" data-action="edit" >edit</a>';
                        echo '<a href="javascript:vclass(0);" class="commission_delete_action" data-id="' . $id . '" data-action="delete" >delete</a>';
                        echo '</div>';
                        echo '</td>';
                    } else {
                        echo "<td $attributes>";
                        echo $this->column_default($item, $column_name);
                        echo $this->handle_row_actions($item, $column_name, $primary);
                        echo '</td>';
                    }
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
            $commission_table_name = $wpdb->prefix . 'commission';
            $user_table_name = $wpdb->prefix . 'users';
            $post_table_name = $wpdb->prefix . 'posts';
            $postmeta_table_name = $wpdb->prefix . 'postmeta';
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
                    FROM $commission_table_name
                    WHERE code not in (
                        SELECT commission_used.meta_value AS code
                        FROM
                            $post_table_name
                            INNER JOIN $postmeta_table_name AS commission_used ON $post_table_name.ID = commission_used.post_id AND commission_used.meta_key = 'commission_used'
                        WHERE
                            ($post_table_name.post_type = 'story' OR $post_table_name.post_type = 'drafts')
                            AND ($post_table_name.post_status = 'publish' OR $post_table_name.post_status = 'draft')
                            AND commission_used.meta_value IS NOT NULL
                    )
                
                    UNION
                
                    SELECT
                        commission_used.meta_value AS commission,
                        $post_table_name.ID,
                        $post_table_name.post_title,
                        $post_table_name.post_date,
                        $post_table_name.post_author,
                        $postmeta_table_name.meta_value AS payment_status,
                        claimed_by_meta.meta_value AS claimed_by,
                        acf_writer_name_meta.meta_value AS acf_writer_name, 
                        acf_writer_email_meta.meta_value AS acf_writer_email,
                        claimed_by_user.display_name AS org_rae
                    FROM
                        $post_table_name
                        INNER JOIN $postmeta_table_name ON $post_table_name.ID = $postmeta_table_name.post_id AND $postmeta_table_name.meta_key = '_payment_status'
                        LEFT JOIN $postmeta_table_name AS claimed_by_meta ON $post_table_name.ID = claimed_by_meta.post_id AND claimed_by_meta.meta_key = 'claimed_by'
                        LEFT JOIN $postmeta_table_name AS acf_writer_name_meta ON $post_table_name.ID = acf_writer_name_meta.post_id AND acf_writer_name_meta.meta_key = 'story_nom_de_plume'
                        LEFT JOIN $postmeta_table_name AS acf_writer_email_meta ON $post_table_name.ID = acf_writer_email_meta.post_id AND acf_writer_email_meta.meta_key = 'story_email_address'
                        LEFT JOIN $postmeta_table_name AS commission_used ON $post_table_name.ID = commission_used.post_id AND commission_used.meta_key = 'commission_used'
                        LEFT JOIN $user_table_name AS claimed_by_user ON claimed_by_meta.meta_value = claimed_by_user.ID
                    WHERE
                    ($post_table_name.post_type = 'story' OR $post_table_name.post_type = 'drafts')
                    AND 
                    ($post_table_name.post_status = 'publish' OR $post_table_name.post_status = 'draft')
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
            $hidden = (is_array(get_user_meta(get_current_user_id(), 'managetoplevel_page_commissions_list_tablecolumnshidden', true))) ? get_user_meta(get_current_user_id(), 'managetoplevel_page_commissions_list_tablecolumnshidden', true) : array();
            $sortable = $this->get_sortable_columns();
            $primary  = 'post_title';
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
    add_action('admin_menu', 'pol_add_menu_commission_menu_items');
    function pol_add_menu_commission_menu_items()
    {
        $commission_list_page = add_menu_page('Commissions', 'Commissions', 'activate_plugins', 'commission_list_table', 'commission_list_init', '', 10);

        add_action("load-$commission_list_page", "commission_table_screen_options");
    }

    // add screen options
    function commission_table_screen_options()
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
        global $wpdb;
        if (isset($_POST["add_commission_submit"])) {
            $commission_text = sanitize_text_field($_POST["commission_key"]);
            $org_rae = sanitize_text_field($_POST["org_rae"]);
            $current_owner = sanitize_text_field($_POST["current_owner"]);
            // Get the current logged-in user
            $current_user_id = get_current_user_id();

            // Prepare data for insertion
            $data = array(
                'code'          => $commission_text,
                'status'        => 0,
                'org_rae'      => $org_rae,
                'current_owner' => $current_owner
            );
            // Insert the data into the bny_commission table
            $table_name = $wpdb->prefix . 'commission';
            $result = $wpdb->insert($table_name, $data);

            pol_update_commission_action($commission = $commission_text, $action = 'cc', $sender_id = $org_rae, $receiver_id = $current_owner, $story_id = '', $action_initiator = $current_user_id);

            // Check if the insertion was successful
            if ($result !== false) {
                // Optionally, you can display a success message or redirect
                echo 'Commission added successfully!';
            } else {
                // Handle insertion error
                echo 'Failed to add commission.';
            }
        }
        // Creating an instance
        $table = new Commission_List_Table();

        // Example usage
        $unique_string = generate_unique_alphanumeric_string(); ?>

        <div class="wrap">
            <h2>Commissions</h2>
            <div class="after_action_message"></div>
            <div class="add-commission-btn"><a href="javascript:void(0);" class="button add-commission" id="add-commission">Add Commission</a></div>
            <div class="popup-overlay"></div>

            <div class="popup" id="popup">
                <h3>Add Commission</h3>
                <form method="POST" class="add_commission_form">
                    <div class="commission-form-group add_commission_wrapper">
                        <label for="add_commission_key">Add Commission</label>
                        <input type="text" name="commission_key" placeholder="Add Commission.." class="add_commission_key" value="<?php echo $unique_string; ?>" readonly>
                    </div>
                    <div class="commission-form-group org_rae_wrapper">
                        <label for="org_rae">Choose RAE</label>
                        <select name="org_rae" id="org_rae" class="org_rae">
                            <option value="">Select a user</option>
                            <?php
                            // Query users with 'rae_approved' meta key set to '1'
                            $args = array(
                                'meta_key'   => 'rae_approved',
                                'meta_value' => '1',
                            );
                            $user_query = new WP_User_Query($args);
                            $approved_users = $user_query->get_results(); // Get the users
                            $current_user_id = get_current_user_id(); // Get the current logged-in user ID
                            // Check if there are any users
                            if (!empty($approved_users)) {
                                foreach ($approved_users as $user) { ?>
                                    <option value=" <?php echo esc_attr($user->ID); ?>" <?php selected($user->ID, $current_user_id); ?>> <?php echo esc_html($user->display_name); ?> </option>
                            <?php }
                            } else {
                                // If no users found, display a message
                                echo '<option value="">No approved users found</option>';
                            }
                            ?>
                        </select>
                    </div>
                    <div class="commission-form-group current_owner_wrapper">
                        <label for="current_owner">Choose Current Owner</label>
                        <select name="current_owner" id="current_owner" class="current_owner">
                            <option value="">Select a user</option>
                            <?php
                            // Get all WordPress users
                            $users = get_users();
                            $current_user_id = get_current_user_id(); // Get the current logged-in user ID

                            // Loop through each user and create an option
                            foreach ($users as $user) {
                                echo '<option value="' . esc_attr($user->ID) . '" ' . selected($user->ID, $current_user_id) . '>' . esc_html($user->display_name) . '</option>';
                            }
                            ?>
                        </select>
                    </div>
                    <div class="add_submit_button">
                        <input type="submit" name="add_commission_submit" value="Add" class="button button-primary submit_commission_key">
                    </div>
                </form>
                <button id="popup-close-button">X</button>
            </div>
            <form method="post">
                <?php
                // Prepare table
                $table->prepare_items();
                // Search form
                $table->search_box('search by commission or story title', 'search_id');
                // Display table
                $table->display();
                echo '</div></form>';


                if (isset($_POST["update_commission_submit"])) {
                    global $wpdb; // Don't forget to declare global $wpdb to interact with the database.

                    // Sanitize and retrieve the form data
                    $commission_text = sanitize_text_field($_POST["commission_key"]);
                    $org_rae = sanitize_text_field($_POST["org_rae"]);
                    $current_owner = sanitize_text_field($_POST["current_owner"]);
                    $commission_id = sanitize_text_field($_POST["commission_id"]);
                    $commission_editor = get_current_user_id();

                    // Prepare data for updating
                    $data = array(
                        'code'           => $commission_text,
                        'status'         => 0, // You can update this based on your needs
                        'org_rae'        => $org_rae,
                        'current_owner'  => $current_owner
                    );

                    // Specify the condition for updating the correct record (where id matches $commission_id)
                    $where = array('id' => $commission_id);

                    // Get the table name
                    $table_name = $wpdb->prefix . 'commission';

                    // Perform the update query
                    $result = $wpdb->update($table_name, $data, $where);

                    pol_update_commission_action($commission = $commission_text, $action = 'ce', $sender_id = $org_rae, $receiver_id = $current_owner, $story_id = '', $action_initiator = $commission_editor);

                    // Check if the update was successful
                    if ($result !== false) {

                        // Success: Update completed
                        echo 'Commission updated successfully!';
                    } else {
                        // Failure: Handle the update error
                        echo 'Failed to update commission.';
                    }
                }

                ?>
                <div class="popup-overlay"></div>

                <div class="commission_popup" id="commission_popup">
                    <div class="commission-form-wrapper">

                    </div>
                    <button id="commission-popup-close-button">X</button>
                </div>
        <?php
    }
}
