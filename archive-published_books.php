<?php

/**
 *  Archive Published Books
 *
 * Hosts all past and upcoming Published Books 
 *
 * @package GOAT PoL
 */
get_header();



?>
<div class="books-header-img">
    <a href="/map">
        <img src="<?php echo get_stylesheet_directory_uri(); ?>/assets/img/Thegoat.png" alt="Profile Image"
            class="books-header-img" /></a>
</div>
<div class="section-inner page-workshop page-books">
    <h1>Books published by The GOAT PoL Books</h1>
    <!-- <h2>What are workshops?</h2>-->
    <p class="books-heading-content">
        The GOAT PoL Books publishes books by writers working at The GOAT PoL. We publish in softcover and eBook,
        distributed globally through the <u><a href="https://publicationstudio.biz" target="_blank">Publication
                Studio
                Network</a></u> . Writers
        working on books can ask their RAEs for
        more information about available support.
    </p>
    <?php

    $published_books_args = [
        'post_type' => 'published_books',
        'posts_per_page' => -1,
        'order' => 'DESC'
    ];

    $published_books_query = new WP_Query($published_books_args);


    if ($published_books_query->have_posts()) {
        echo '<div class="all-workshops">';
        while ($published_books_query->have_posts()) {
            $published_books_query->the_post();

            $thumbnail = get_the_post_thumbnail_url();
            echo '<div class="workshop-table">';
            if ($thumbnail != '') {
                echo '<img src="' . $thumbnail . '" alt="post thumbnail"/>';
            } else {
                echo pol_get_random_goat();
            }
            $book_images = get_field('book_gallery', false, false);
            // var_dump($book_images);
            echo '<div class="workshop-card-contents books_card_contents">';
            echo ' <a href="' . get_the_permalink() . '">';
            echo '<div class="workshop-card-title ">' . get_the_title() . '</div>';
            echo '</a>';
            echo '<h6>By ' . get_field('author') . '</h6>';
            echo '<div class="workshop-card-detail-content">' . get_the_excerpt() . '</div>';
            // then empty space ane below it four more spaced fo thumbnail image uploads, ten empty space, and below that a line with [price], [page count],  and [ISBN number] with a big button to the right “Browse and buy this book” (which links to the publisher’s sales page).
            ?>
            <div class="books-gallery-images">
                <?php
                foreach ($book_images as $product_image) { ?>

                    <?php
                    $image_url = wp_get_attachment_url($product_image); ?>

                    <figure><img src="<?php echo $image_url; ?>" /></figure>


                <?php } ?>
            </div>
            <?php
            echo '<div class = "books-meta-fields">';
            echo '<p><i class="fa-solid fa-hand-holding-dollar"></i> Price: $' . get_field('price', get_the_ID()) . '   </p>';
            echo '<p><i class="fa-solid fa-note-sticky"></i> Page Count: ' . get_field('page_count', get_the_ID()) . '  </p>';
            echo '<p> <i class="fa-solid fa-barcode"></i> ISBN Number: ' . get_field('isbn_number', get_the_ID()) . '  </p>';
            echo '<a href="' . get_field('buy_this_book_link', get_the_ID()) . '" class="" target = "_blank">
                                                <span class="dkpdf-button-icon">
                                                    <i class="fa-brands fa-readme"></i>
                                                </span>
                                                &nbsp;&nbsp;&nbsp;Browse and buy this book
                                            </a>';
            echo '</div>';
            echo '</div>';
            // echo '<div><a href="' . get_the_permalink() . '" target="_blank">Sign up for this workshop</a></div>';
            // echo '<button></button>';
            echo '</div>';
        }
        echo '</div>';
    } else {
        // No posts found
        echo 'No Published Books !!';
    }
    // Reset post data
    wp_reset_postdata();
    ?>



</div>
<?php
// Reset post data

get_footer();
