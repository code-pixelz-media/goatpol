<?php
if ($args['section'] == 'by author') {
  $list_heading = 'Stories by Author';
} else if ($args['section'] == 'by location') {
  $list_heading = 'Stories by Location';
}
?>

<!-- --------------AUTHORS ----------------- -->
<div class="gp-story-colu">
  <div class="gp-story-list">
    <input class="gp-story-list__trigger" id="gp-story-list-<?php echo $args['section_id']; ?>" type="checkbox" />
    <label class="gp-story-list__title" for="gp-story-list-<?php echo $args['section_id']; ?>"><?php _e($list_heading, 'pol'); ?></label>
    <div class="gp-story-list__content-wrapper">
      <div class="gp-story-listings__wrap">
        <div class="gp-story-list__content">
          <form>
            <div class="input-group">
              <input type="hidden" id="curr-section-<?php echo ($args['section'] == "by author" ? 'auth' : 'loc') ?>" name="curr-section-name" value="<?php echo $args['section']; ?>">
              <input type="text" id="<?php echo ($args['section'] == "by author" ? 'search-author' : 'search-location') ?>" placeholder="Search..." autocomplete="off" />
            </div>
          </form>

          <div id="gp-story-author" class="gp-author-lists gp-author-lists-<?php echo $args['section_id']; ?>">
            <ul>

              <?php
              if ($args['section'] == 'by author') {

                $nom_de_plume_to_be_searched = $_POST['search_query'];
                $author_section_author_arr =  pol_fetch_all_authorID_and_authorNomDePlume($nom_de_plume_to_be_searched);

                foreach ($author_section_author_arr as $auth_id => $auth_nom_de_plume) {
        
                  ?>
                  <li class="gp-story-author">
                    <a class="gp-author-name gp-author-name-<?php echo $auth_id; ?>" href="#gp-story-popup-<?php echo $auth_id; ?>"><?php echo $auth_nom_de_plume; ?></a>
                      <?php 
                      get_template_part('template-parts/list-view/template-filter-specific-story-popup', 
                        null, 
                        [
                          'section' => $args['section'], 
                          'section_id' => $args['section_id'], 
                          'total_authors' => $total_authors, 
                          'author_id' => $auth_id, 
                          'author_name' => $auth_nom_de_plume
                        ]
                      ); 
                      ?>
                  </li>
                  <?php
                }
              } else if ($args['section'] == 'by location') {
                $all_places = pol_get_map_places();

                $total_places = count($all_places);
                foreach ($all_places as $place) { ?>

                  <li class="gp-story-author">
                    <a class="gp-author-name gp-author-name-<?php echo $place->ID; ?>" href="#gp-story-popup-<?php echo $place->ID; ?>"><?php echo $place->post_title; ?></a>
                    <?php get_template_part('template-parts/list-view/template-filter-specific-story-popup', null, ['section' => $args['section'], 'section_id' => $args['section_id'], 'total_authors' => $total_places, 'author_id' => $place->ID, 'author_name' => $place->post_title]); ?>
                  </li>

              <?php
                }
              }
              ?>
            </ul>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>