<div id="gp-story-popup" class="gp-custom-model-main gp-custom-model-main-<?php echo $args['author_id']; ?>">
    <div class="gp-custom-model-inner">
        <div class="gp-close-btn gp-close-btn-<?php echo $args['author_id']; ?>">×</div>
        <div class="gp-custom-model-wrap">
            <div class="gp-popup-content-wrap">
                <h4 class="gp-result"><em>from</em> <?php echo $args['author_name']; ?></h4>
                <div class="gp-popup-list-row gp-popup-list-row-<?php echo $args['author_id'] ?>">

                <!-- stories will appear here from the ajax below -->

                </div>
                <button data-selected="<?php echo $args['section']; ?>" data-paged="1" id="load-more-author-stories-<?php echo $args['author_id']; ?>" type="button"><?php _e('Load More', 'pol'); ?></button>
                <div id="load-more-end-<?php echo $args['author_id']; ?>" class="gp-end-of-story" ><?php _e('No more Stories', 'pol'); ?></div>
            </div>
        </div>
    </div>
</div>

<script>
    jQuery(document).ready(function () {
        //open popup
        jQuery(".gp-author-name-<?php echo $args['author_id']; ?>").on('click', function () {
            jQuery(".gp-custom-model-main-<?php echo $args['author_id']; ?>").css('visibility', 'visible');

            //loading symbol
            jQuery('.gp-popup-list-row-<?php echo $args['author_id']; ?>').html('<i class="fa fa-spinner fa-spin gp-list-view-page-spinner"></i>');

            jQuery.ajax({
                type: 'post',
                url: pol_ajax_load_more.ajaxurl,
                data: {
                    action: 'pol_lists_view_individual_story_generator',
                    value: '<?php echo $args['section']; ?>',
                    author_id : '<?php echo $args['author_id']; ?>',
                },
                success: function (response) {
                    jQuery('.gp-popup-list-row-<?php echo $args['author_id']; ?>').html(response);

                    if(jQuery('.gp-popup-list-row-<?php echo $args["author_id"]; ?> .gp-story-list-row').length == 0){
                        jQuery('#load-more-author-stories-<?php echo $args['author_id']; ?>').hide();
                        jQuery('.gp-popup-list-row-<?php echo $args['author_id']; ?>').html('<p class="gp-end-of-story">No Stories published yet by the author</p>');
                    }
                },
                error: function (response) {
                    console.log(response);
                }
            });
        });

        //close popup
        jQuery(".gp-close-btn-<?php echo $args['author_id']; ?>").click(function () {
            jQuery(".gp-custom-model-main-<?php echo $args['author_id']; ?>").css('visibility', 'hidden');
        });


        //=================load more button=====================

        //hide the end of story message
		jQuery('#load-more-end-<?php echo $args['author_id']; ?>').css('display','none');

        jQuery("#load-more-author-stories-<?php echo $args['author_id']; ?>").click(function () {
            //populate the popup with stories from the author
            var currAuthorId = <?php echo $args['author_id']; ?>;
            var popupLoadMoreBtn = '#load-more-author-stories-<?php echo $args['author_id']; ?>';

            var value = jQuery(popupLoadMoreBtn).attr('data-selected');
			var paged = jQuery(popupLoadMoreBtn).attr('data-paged');
			var maxpage = jQuery(popupLoadMoreBtn).attr('data-maxpage');

            jQuery(popupLoadMoreBtn).html('Loading <i class="fa fa-spinner fa-spin gp-list-view-page-spinner"></i>');
			jQuery(popupLoadMoreBtn).prop('disabled', true);

            if ( parseInt(paged) != parseInt(maxpage) ) {
                jQuery.ajax({
                    type: 'post',
                    url: pol_ajax_load_more.ajaxurl,
                    data: {
                        action: 'pol_lists_view_individual_story_generator',
                        page: parseInt(paged) + 1,
                        value: value,
                        author_id : currAuthorId
                    },
                    success: function (response) {
                        jQuery('.gp-popup-list-row-<?php echo $args['author_id'] ?>').append(response);
                        jQuery(popupLoadMoreBtn).attr('data-paged', parseInt(paged) + 1);

                        jQuery(popupLoadMoreBtn).html('Load More');
			            jQuery(popupLoadMoreBtn).prop('disabled', false);

                        if (parseInt(paged) == parseInt(maxpage)-1) {
							jQuery(popupLoadMoreBtn).hide();
						}
                    },
                    error: function (response) {
                        console.log(response);
                    }
                });
            }else{
                //remvoe load more button
				document.querySelector('#load-more-author-stories-<?php echo $args['author_id']; ?>').remove();
				//show the end of story message
				jQuery('#load-more-end-<?php echo $args['author_id']; ?>').css('display','block');
			}
        });
            
    });
</script>