<?php

/**
 * Template Name: Lists View
 * 
 * Displays map in a list view
 * 
 * @package GOAT PoL
 */


get_header();


if (is_user_logged_in()) {
?>

  <script>
    localStorage.setItem("last_visited_page", "list-page");
  </script>
<?php
}
?>




<div class="gp-story-listings">
  <div class="gp-story-row">

    <?php

    //!!!!DO NOT CHANGE THE INITIAL VALUE OF $index!!!!
    $index = 1000;
    //!!!!DO NOT CHANGE THE INITIAL VALUE OF $index!!!!

    //!!!!DO NOT CHANGE THE ORDER OF $all_sections, ONLY ADD AT THE END!!!
    $all_sections = ['most recent', 'by author', 'by location', 'often read', 'nearby', 'random', 'internet', 'book'];
    //!!!!DO NOT CHANGE THE ORDER OF $all_sections, ONLY ADD AT THE END!!!

    foreach ($all_sections as $as) {
      if ($as == 'by author' || $as == 'by location') {
        get_template_part('template-parts/list-view/template-filter-specific-stories-section', null, ['section' => $as, 'section_id' => $index]);
      } else {
        get_template_part('template-parts/list-view/template-generic-stories-section', null, ['section' => $as, 'section_id' => $index]);
      }

      $index++;
    }
    ?>
  </div>
</div>

<!-- serach functionality starts-->
<div class="serach-functionality-list-page">
  <input id="pol-map-search-input" class="controls" type="text" value="" placeholder="<?php _e('Search The GOAT PoL', 'pol'); ?>" />
  <!--  -->
  <div class="inputcontainer" style="display: none;">
    <div class="icon-container">
      <i class="loader"></i>
    </div>
  </div>

  <ul id="ui-id-1" tabindex="0" class="ui-menu ui-widget ui-widget-content ui-autocomplete ui-front list-search-result" unselectable="on" style="top: 808.977px; left: 344.091px; width: 301px;">
    <div class="pol-search-container">
    </div>
  </ul>
</div>

<!-- serach functionality ends-->



<?php
generateToggleTabs();
// wp_footer(); 

?>
<script>
  // Debounce function
function debounce(func, wait) {
 var timeout;
 return function() {
    var context = this, args = arguments;
    clearTimeout(timeout);
    timeout = setTimeout(function() {
      func.apply(context, args);
    }, wait);
 };
}

// Your AJAX request function
function fetchSearchData() {
 jQuery.ajax({
    url: pol_ajax_load_more.ajaxurl,
    type: "post",
    dataType: "json",
    data: {
      action: "pol_search_data_fetch",
      search: jQuery("#pol-map-search-input").val(),
    },
    beforeSend: function() {
      jQuery(".inputcontainer").css("display", "");
    },
    success: function(response) {
      jQuery(".inputcontainer").css("display", "none");
      jQuery("#ui-id-1").css("display", "");

      var response_elements = "";
      response.forEach((element, index) => {
        response_elements += `
          <li class="ui-menu-item">
            <div data-posttype="story" data-storyid="${element.post_id}" data-marker="${element.id}" tabindex="-1" class="ui-menu-item-wrapper pol-search-list" id="ui-id-${index + 2}">
              <a href="${element.perma_link}">
                ${element.label}
              </a>
            </div>
          </li>
        `;
      });

      jQuery(".pol-search-container").html(response_elements);
    },
 });
}

// Event listener with debounce
jQuery("#pol-map-search-input").on("input", debounce(fetchSearchData, 500));

</script>

<?php

get_footer();
?>