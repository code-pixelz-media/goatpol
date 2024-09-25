<?php 


/**
 * Template Name: Test Payment
 *
 * Displays The User Information.
 *
 * @package GOAT PoL
 */

acf_form_head();
get_header();

if (get_current_user_id() != 14) {
    get_footer();
    return;
}

if (is_user_logged_in()) { ?>


    <main id="site-content" role="main">
        <div id="post-content" class="section-inner">
            <div class="section-inner">
                <div class="entry-content edit-user-innfo">
                    <?php
                    $current_user = wp_get_current_user();
                    $current_user_id = get_current_user_id();

                    //get current user role
                    $user_role = 'user';
                    if (get_user_meta($current_user_id, 'rae_approved', true) == 1) {
                        $user_role = 'rae';
                    } else if (in_array('administrator', (array) $current_user->roles)) {
                        $user_role = 'admin';
                    }
                    ?>
                    <?php if (have_posts()) { 
                    while (have_posts()) : the_post(); ?>
                    <article id="post-<?php the_ID() ?>" <?php post_class(); ?>>
                        <h2 class="page-title"><?php the_title(); ?></h2>
                        <?php the_content(''); ?>
                        <?php 
                        if (is_user_logged_in()) {
                            $current_user_id = get_current_user_id();
                            acf_form([
                                'field_groups' => ['group_62da1f1ae94a0'],
                                'post_id' => 'user_' . $current_user_id
                            ]);
                        }
                        ?>
                    </article>
                    <?php endwhile;
                    } ?>
                </div>
            </div>
        </div>
    </main>
<?php } else {
    echo '<h1>Please login to view your account</h1>';
}

// get_sidebar();
get_footer();