<?php
?>

<div id="gp-story-list-row-<?php echo $args['story_id']; ?>" class="gp-story-list-row">
    <div class="gp-story-img">
        <?php echo $args['image_element'];?>
    </div>
    <div class="gp-story-body">
        <a href="<?php echo $args['read_more_link']; ?>"><h4><?php echo $args['story_title']; ?></h4></a>
        <p> by <?php echo $args['story_author']; ?></p>
        <?php
            //hide location if about internet or about stories
            if( $args['section'] != "internet" && $args['section'] != "book" ){ 
                ?>
                <p>
                    <span class="dashicons dashicons-location"></span><?php echo $args['location'];?>
                </p>
                <?php 
            }

            //show the story published date in the most recent section
            if( $args['section'] == "most recent" ){
                ?>
                <p>
                    <span class="dashicons dashicons-calendar"></span> <?php echo $args['publish_date'];?>
                </p>
                <?php 
            }
        ?>
    </div>
</div>

