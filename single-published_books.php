<?php

get_header();


$title = get_the_title();
$content = get_the_content();
$date_time = get_field('workshop-date-time');
$book_price = get_field('price', get_the_ID());
$books_page_count = get_field('page_count', get_the_ID());
$books_isbn_no = get_field('isbn_number', get_the_ID());
$buy_book_url = get_field('buy_this_book_link', get_the_ID());
$book_images = get_field('book_gallery', false, false);

$date_time_meta = new DateTime(get_post_meta($post_id, 'workshop-date-time', true));


$thumbnail = get_the_post_thumbnail_url();

?>
<div class="single-workshop-page section-inner single-workshop-new-header ">
    <a href="/map" class="workshop-img single-wordkshop-header-img">
        <img src="<?php echo get_stylesheet_directory_uri(); ?>/assets/img/workshop2.jpeg" alt="Profile Image"
            style="width: 200px; object-fit: cover;">
    </a>
</div>

<div class="single-workshop-page section-inner single-books-page">

    <h1>
        <?php echo $title; ?>
    </h1>
    <div class="workshop-main-content single-books-main-content">
        <div class="single-books-main-content-contents">
            <div class="books-gallery-images">
                <?php
                foreach ($book_images as $product_image) { ?>

                    <?php
                    $image_url = wp_get_attachment_url($product_image); ?>

                    <figure><img src="<?php echo $image_url; ?>" /></figure>


                <?php } ?>
            </div>
            <div class="single-books-main-image">
                <?php
                if ($thumbnail != '') {
                    echo '<img src="' . $thumbnail . '" alt="post thumbnail">';
                } else {
                    echo pol_get_random_goat();
                }
                ?>
            </div>
            <div class="single-books-book-content">
                <?php
                echo '<h6>By ' . get_field('author') . '</h6>';
                echo '<div class="books-meta-fields">';
                echo '<h6><i class="fa-solid fa-hand-holding-dollar"></i> Price: $' . get_field(
                    'price',
                    get_the_ID()
                ) . ' </h6>';
                echo '<h6><i class="fa-solid fa-note-sticky"></i> Page Count: ' . get_field(
                    'page_count',
                    get_the_ID()
                ) . ' </h6>';
                echo '<h6> <i class="fa-solid fa-barcode"></i> ISBN Number: ' . get_field(
                    'isbn_number',
                    get_the_ID()
                ) . ' </h6>';
                echo '</div>'; ?>
                <a href="<?php echo get_field('buy_this_book_link', get_the_ID()); ?>" target="_blank">
                    <span class="dkpdf-button-icon">
                        <i class="fa-brands fa-readme"></i>
                    </span>
                    &nbsp;&nbsp;&nbsp;Browse and buy this book
                </a>
                <h3>
                    <?php echo $content; ?>
                </h3>
            </div>

        </div>


        <p>
            <?php echo $date_time; ?>
        </p>

    </div>
</div>



<?php
get_footer();
