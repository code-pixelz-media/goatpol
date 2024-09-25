<?php

function add_payment_meta_to_published_story_paymentDb()
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

    // if(get_current_user_id() == 14){
    //     var_dump(get_post_meta(19234, 'mail-sent-final', true) != 1);
    // }
}

if (is_user_logged_in()) {


    // if(get_current_user_id() == 14){
        add_payment_meta_to_published_story_paymentDb();
    // }


    $user = wp_get_current_user();
    $allowed_roles = array('editor', 'administrator');
    if (array_intersect($allowed_roles, $user->roles)) {


        if (!class_exists('Payment_List_Table')) {

            require_once(ABSPATH . 'wp-admin/includes/class-wp-list-table.php');
        }

        global $search_query;



        class Payment_List_Table extends WP_List_Table
        {

            /**
             * Constructor, we override the parent to pass our own arguments
             * We usually focus on three parameters: singular and plural labels, as well as whether the class supports AJAX.
             */

            function __construct()
            {

                parent::__construct(array(

                    'singular'  => 'Payment',     //singular name of the listed records

                    'plural'    => 'Payments',    //plural name of the listed records

                    'ajax'      => false

                ));
            }

            public static function sql_for_get_items()
            {
                global $wpdb;

                $sql = "SELECT 
                {$wpdb->prefix}posts.ID,
                {$wpdb->prefix}posts.post_title,
                {$wpdb->prefix}posts.post_date,
                {$wpdb->prefix}posts.post_author,
                {$wpdb->prefix}postmeta.meta_value AS payment_status,
                claimed_by_meta.meta_value AS claimed_by,
                acf_writer_name_meta.meta_value AS acf_writer_name,
                acf_writer_email_meta.meta_value AS acf_writer_email,
                claimed_by_user.display_name AS claimed_by_display_name
            FROM 
                {$wpdb->prefix}posts
                INNER JOIN {$wpdb->prefix}postmeta ON {$wpdb->prefix}posts.ID = {$wpdb->prefix}postmeta.post_id AND {$wpdb->prefix}postmeta.meta_key = '_payment_status'
                LEFT JOIN {$wpdb->prefix}postmeta AS claimed_by_meta ON {$wpdb->prefix}posts.ID = claimed_by_meta.post_id AND claimed_by_meta.meta_key = 'claimed_by'
                LEFT JOIN {$wpdb->prefix}postmeta AS acf_writer_name_meta ON {$wpdb->prefix}posts.ID = acf_writer_name_meta.post_id AND acf_writer_name_meta.meta_key = 'story_nom_de_plume'
                LEFT JOIN {$wpdb->prefix}postmeta AS acf_writer_email_meta ON {$wpdb->prefix}posts.ID = acf_writer_email_meta.post_id AND acf_writer_email_meta.meta_key = 'story_email_address'
                LEFT JOIN {$wpdb->prefix}users AS claimed_by_user ON claimed_by_meta.meta_value = claimed_by_user.ID
            WHERE 
                {$wpdb->prefix}posts.post_type = 'story'
                AND {$wpdb->prefix}posts.post_status = 'publish'";

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

                if ($search) {
                    $sql .= "AND `post_title` Like '%{$search}%' ";
                }

                if (!empty($_REQUEST['orderby'])) {
                    $sql .= ' ORDER BY ' . esc_sql($_REQUEST['orderby']);
                    $sql .= !empty($_REQUEST['order']) ? ' ' . esc_sql($_REQUEST['order']) : ' DESC';
                }else{
                    $sql .="ORDER BY `post_date` DESC";
                }

                $sql .= " LIMIT $per_page";

                $sql .= ' OFFSET ' . ($page_number - 1) * $per_page;

                $results = $wpdb->get_results($sql, 'ARRAY_A');

                foreach ($results as $key => $value) {
                    $email = $value['acf_writer_email'];
                    $user = get_user_by('email', $email);
                    if ($user) {
                        $user_id = $user->ID;
                        $payment_method = get_user_meta($value['post_author'], 'cpm_payment_method', true);
                        $results[$key]['cpm_payment_method'] = $payment_method;
                        $results[$key]['acf_writer_id'] = $user_id;
                    } else {
                        $results[$key]['cpm_payment_method'] = '';
                        $results[$key]['acf_writer_id'] = '';
                    }
                }

                // Create a new array with unique values based on the "ID" field
                $uniqueArray = array_column($results, null, "ID");

                // Convert the array values back to an indexed array
                $uniqueArray = array_values($uniqueArray);

                // Output the unique array
            
                // echo '<pre>';
                // var_dump($uniqueArray);
                // echo '</pre>';

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
                    $sql .= "AND `post_title` Like '%{$search}%' ";
                }

                $results = $wpdb->get_results($sql, 'ARRAY_A');

                $unique_results = array();

                // Loop through the results and make the array unique by post ID
                foreach ($results as $result) {
                    $post_id = $result['ID'];

                    // Check if the post ID already exists in the unique results array
                    if (!isset($unique_results[$post_id])) {
                        // If the post ID doesn't exist, add the result to the unique results array
                        $unique_results[$post_id] = $result;
                    }
                }

                // Reset the array keys to maintain continuity
                $unique_results = array_values($unique_results);

                $results_count = count($unique_results);
                return $results_count;
            }

            /** Text displayed when no customer data is available */
            public function no_items()
            {
                _e('No Stories avaliable.', 'sp');
            }


            /**
             * Method for name column
             *
             * @param array $item an array of DB data
             *
             * @return string
             */
            // function column_name($item)
            // {

            //     // create a nonce
            //     $delete_nonce = wp_create_nonce('sp_delete_story');

            //     $title = '<strong>' . $item['post_title'] . '</strong>';

            //     $actions = [
            //         'delete' => sprintf('<a href="?page=%s&action=%s&customer=%s&_wpnonce=%s">Delete</a>', esc_attr($_REQUEST['page']), 'delete', absint($item['ID']), $delete_nonce)
            //     ];

            //     return $title . $this->row_actions($actions);
            // }


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
                    case 'post_title':
                        // case 'author_display_name':
                    case 'acf_writer_name':
                    case 'claimed_by_display_name':
                    case 'post_date':
                    case 'cpm_payment_method':
                    case 'payment_status':
                        return $item[$column_name];
                    default:
                        return print_r($item, true); //Show the whole array for troubleshooting purposes
                }
            }


            /**
             * Render the bulk edit checkbox
             *
             * @param array $item
             *
             * @return string
             */
            // function column_cb($item)
            // {
            //     return sprintf(
            //         '<input type="checkbox" name="bulk-delete[]" value="%s" />',
            //         $item['ID']
            //     );
            // }


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

                    // Comments column uses HTML in the display name with screen reader text.
                    // Strip tags to get closer to a user-friendly string.
                    $data = 'data-colname="' . esc_attr(wp_strip_all_tags($column_display_name)) . '"';

                    $attributes = "class='$classes' $data";

                    // var_dump($item);

                    if ('payment_status' === $column_name) {

                        $payment_status = get_post_meta($item['ID'], '_payment_status', true);

                        // if ($payment_status == "1") {
                        //     $payment_status = "Paid";
                        // } else {
                        //     $payment_status = "Unpaid";
                        // }

                        $data_ID = 'data-Storyid="' . $item['ID'] . '"';
                        echo "<td $attributes $data_ID >";
                        echo "<div id='payment_status_" . $item['ID'] . "'>";
                        if (get_post_status($item['ID']) == 'publish') {

                            if ($payment_status == '1') {
                                echo '<div><span class="dashicons dashicons-yes green" style="color:#46b450;"></span>';
                                echo "<span style='color:#46b450;display: block;' >";

                                // echo $this->column_default($item, $column_name);
                                // echo $this->handle_row_actions($item, $column_name, $primary);
                                echo 'Paid</span>';
                                echo '<span><strong>Paid on:</strong> ' . get_post_meta($item['ID'], 'story_paid_date', true) . '</span>';
                                echo '</div><div style="cursor: pointer;"><span style="float: right;margin-right: 70px;border: 2px solid darkgrey;border-radius: 4px;color: black;padding: 1px 5px;" class="payment_status"  data-id="' . $item['ID'] . '" ><span>Mark as Unpaid</span></button></div>';
                            } else {
                                echo '<div><span class="dashicons dashicons-no-alt red" style="color:#dc3232;"></span>';
                                echo "<span style='color:#dc3232;display: block;' >";

                                // echo $this->column_default($item, $column_name);
                                // echo $this->handle_row_actions($item, $column_name, $primary);
                                echo 'Unpaid</span>';
                                echo '</div><div style="cursor: pointer;"><span style="float: right;margin-right: 70px;border: 2px solid darkgrey;border-radius: 4px;color: black;padding: 1px 5px;" class="payment_status"  data-id="' . $item['ID'] . '" ><span>Mark as Paid</span></button></div>';
                            }
                        }
                        echo '</div>';
                        echo '</td>';
                        // } else if ('author_display_name' === $column_name) {
                    } else if ('acf_writer_name' === $column_name) {
                        $user_id = ($item['acf_writer_id']);

                        // $user = get_user_by('email', $email);
                        // $user_id = $user->ID;
                        // echo '<pre>';
                        // var_dump($user);
                        // echo '</pre>';
                        // $author_link = get_author_posts_url($item['post_author']);

                        $author_link = get_author_posts_url($user_id);

                        echo "<td $attributes><a href='" . $author_link . "' >";
                        echo $this->column_default($item, $column_name);
                        echo $this->handle_row_actions($item, $column_name, $primary);
                        echo '</a></td>';
                    } else if ('post_title' === $column_name) {
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
                    // 'cb' => '<input type="checkbox" />',
                    'post_title' => 'Story Title',
                    'acf_writer_name' => 'Writer',
                    // 'author_display_name' => 'Writer',
                    'claimed_by_display_name' => 'RAE',
                    'post_date' => 'Date',
                    'cpm_payment_method' => 'Payment Method',
                    'payment_status' => 'Payment Status'
                ];

                return $columns;
            }


            /**
             * Columns to make sortable.
             *
             * @return array
             */
            public function get_sortable_columns()
            {
                $sortable_columns = array(
                    'post_title' => array('post_title', true),
                    'post_date' => array('post_date', true),
                    'acf_writer_name' => array('acf_writer_name', true),
                    // 'author_display_name' => array('author_display_name', true),
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

                /** Process bulk action */
                // $this->process_bulk_action();

                $per_page = $this->get_items_per_page('stories_per_page', 20);
                $current_page = $this->get_pagenum();

                $base_url = get_admin_url() . 'edit.php?post_type=story&page=payment';

                if (isset($_POST['s'])) {
                    $search = $_POST['s'];
                    if ($search != '') {

                        // $page = 'payment';
                        $args = array(
                            // 'post_type' => 'story',
                            // 'page' => $page,
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



            //class end
        }


        class payment_admin_page
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

                $hook =   add_submenu_page(
                    'edit.php?post_type=story',
                    'Payments',
                    'Payments',
                    "manage_options",
                    'payment',
                    [$this, 'plugin_settings_page'],
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
                    <h2>Payments</h2>

                    <div id="poststuff">
                        <div id="post-body" class="metabox-holder">
                            <div id="post-body-content">
                                <div class="meta-box-sortables ui-sortable">
                                    <form method="post">
                                        <?php
                                        $this->stories_obj->prepare_items();
                                        $this->stories_obj->search_box('Search', 'search_id');
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

                // $option = 'per_page';
                // $args   = [
                //     'label'   => 'Stories',
                //     'default' => 20,
                //     'option'  => 'stories_per_page'
                // ];

                // add_screen_option($option, $args);

                $this->stories_obj = new Payment_List_Table();
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
            payment_admin_page::get_instance();
        });


        //admin footer ajax js
        if (!function_exists('admin_toggle_payment_status_page_payments')) {
            function admin_toggle_payment_status_page_payments()
            {
            ?>
                <script type="text/javascript">
                    jQuery(document).ready(function() {
                        jQuery(document).on('click', 'span.payment_status', function(e) {
                            e.preventDefault();
                            var story_id = jQuery(this).data('id');
                            jQuery.ajax({
                                url: ajaxurl,
                                type: 'POST',
                                data: {
                                    action: 'admin_ajax_payment_status',
                                    post_id: story_id
                                },
                                success: function(response) {
                                    // console.log(response);
                                    jQuery('#payment_status_' + story_id).html(response);
                                }
                            });

                        });
                    });
                </script>
<?php
            }
            add_action('admin_footer', 'admin_toggle_payment_status_page_payments');
        }

        // admin ajax
        if (!function_exists('admin_ajax_payment_status')) {
            add_action('wp_ajax_admin_ajax_payment_status', 'admin_ajax_payment_status');
            function admin_ajax_payment_status()
            {
                ob_start();
                $story_id = $_POST['post_id'];
                $payment_status = get_post_meta($story_id, '_payment_status', true);
                if ($payment_status == 1) {
                    update_post_meta($story_id, '_payment_status', '0');
                    $res = '<div><span class="dashicons dashicons-no-alt red" style="color:#dc3232;"></span>';
                    $res .= "<span style='color:#dc3232;display: block;' >Unpaid</span>";
                    $res .= '</div><div style="cursor: pointer;"><span style="float: right;margin-right: 70px;border: 2px solid darkgrey;border-radius: 4px;color: black;padding: 1px 5px;" class="payment_status"  data-id="' . $story_id . '" ><span>Mark as Paid</span></button></div>';
                } else if ($payment_status == 0) {
                    update_post_meta($story_id, '_payment_status', '1');
                    update_post_meta($story_id, 'story_paid_date', current_time('Y-m-d H:i:s'));
                    $res = '<div><span class="dashicons dashicons-yes green" style="color:#46b450;"></span>';
                    $res .= "<span style='color:#46b450;display: block;' >Paid</span>";
                    $res .= '<span><strong>Paid on:</strong> ' . get_post_meta($story_id, 'story_paid_date', true) . '</span>';
                    $res .= '</div><div style="cursor: pointer;"><span style="float: right;margin-right: 70px;border: 2px solid darkgrey;border-radius: 4px;color: black;padding: 1px 5px;" class="payment_status"  data-id="' . $story_id . '" ><span>Mark as Unpaid</span></button></div>';
                } else {
                    $res = '<div><span class="dashicons dashicons-no-alt red" style="color:#dc3232;"></span>';
                    $res .= "<span style='color:#dc3232;display: block;' >Unpaid</span>";
                    $res .= '</div><div style="cursor: pointer;"><span style="float: right;margin-right: 70px;border: 2px solid darkgrey;border-radius: 4px;color: black;padding: 1px 5px;" class="payment_status"  data-id="' . $story_id . '" ><span>Mark as Paid</span></button></div>';
                }
                ob_clean();
                echo $res;
                die;
            }
        }
    }
}
