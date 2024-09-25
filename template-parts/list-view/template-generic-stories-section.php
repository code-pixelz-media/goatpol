<?php

//set appropraite heading according to section
if($args['section'] == 'most recent'){
  $list_heading = 'Most Recent Stories';
}else if($args['section'] == 'often read'){
  $list_heading = 'Hidden Treasures';
}else if($args['section'] == 'nearby'){
  $list_heading = 'Stories Near to You';
}else if($args['section'] == 'random'){
  $list_heading = 'Ten Random Stories';
}else if($args['section'] == 'internet'){
  $list_heading = 'Stories about the Internet';
}else if($args['section'] == 'book'){
  $list_heading = 'Stories about a Book';
}
?>
<!--------------- GENERIC STORIES ------------->
<div class="gp-story-colu">
  <div class="gp-story-list">
    <input class="gp-story-list__trigger" id="gp-story-list-<?php echo $args['section_id']; ?>" type="checkbox" />
    <label class="gp-story-list__title" for="gp-story-list-<?php echo $args['section_id']; ?>"> <?php _e($list_heading, 'pol'); ?>
      <?php 
        if( $args['section'] == "random" ){?>
          <button id="random-story-refresh-btn">
            <img src="<?php echo get_stylesheet_directory_uri() . "/assets/img/lists-view/refresh.svg"; ?>" alt="refresh story">
          </button>
        <?php }
      ?>
    </label>
    <div class="gp-story-list__content-wrapper">
      <div class="gp-story-listings__wrap">
        <div id="gp-infinite-story-<?php echo $args['section_id']; ?>" class="gp-story-list__content">

          <?php 
            //show search filter in book and internet sections
            if( $args['section'] == "internet" || $args['section'] == "book"){

              ?>
              <form>
                <div class="input-group">
                  <input type="hidden" id="curr-section-<?php echo ($args['section'] == "internet" ? 'internet' : 'book') ?>" name="curr-section-name" value="<?php echo $args['section']; ?>">
                  <input type="text" id="<?php echo ($args['section'] == "internet" ? 'search-internet' : 'search-book') ?>" placeholder="Search..." autocomplete="off" />
                </div>
              </form>
              <?php 
            }
          ?>

          <!-- stories will appear here from the ajax below -->
          
        </div>

        <?php 
          //dont show load more button in random , internet, book || data-maxpage="2"
          if( $args['section'] != "random" ){  //&& $args['section'] != "internet" && $args['section'] != "book" || (($args['section'] == "internet" || $args['section'] == "book") ? 0 : 1) 
            ?>
              <button data-selected="<?php echo $args['section']; ?>" data-paged="<?php echo (($args['section'] == "internet" || $args['section'] == "book") ? 0 : 1);  ?>"  id="load-more-stories-<?php echo $args['section_id']; ?>" type="button"><?php _e(' Load More', 'pol'); ?></button>
              <div id="load-more-end-<?php echo $args['section_id']; ?>" class="gp-end-of-story" ><?php _e('No more Stories', 'pol'); ?> </div>
            <?php 
          } 
        ?>
      </div>
    </div>
  </div>
</div>

<?php if ( $args['section'] == "internet" || $args['section'] == "book" ){ ?>
  <script>
    // console.log('ajax internet 11111');
    jQuery.ajax({
      type: 'post',
      url: pol_ajax_load_more.ajaxurl,
      data: {
        action: 'pol_get_internet_and_book_stories',
        value: '<?php echo $args['section']; ?>',
        section_id : '<?php echo $args['section_id']; ?>'
      },
      success: function (response) {
        jQuery('#gp-infinite-story-<?php echo $args['section_id']; ?>').append(response);
      },
      error: function (response) {
        console.log(response);
      }
    });
  </script>
<?php } else{ ?>
  <script>
    jQuery.ajax({
      type: 'post',
      url: pol_ajax_load_more.ajaxurl,
      data: {
        action: 'pol_lists_view_individual_story_generator',
        value: '<?php echo $args['section']; ?>',
        section_id : '<?php echo $args['section_id']; ?>'
      },
      success: function (response) {
        jQuery('#gp-infinite-story-<?php echo $args['section_id']; ?>').append(response);
      },
      error: function (response) {
        console.log(response);
      }
    });
  </script>
<?php } ?>
