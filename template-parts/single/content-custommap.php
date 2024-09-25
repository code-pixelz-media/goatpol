<?php

?>
<article <?php post_class(); ?> id="post-<?php the_ID(); ?>">

	<?php get_template_part( 'template-parts/single/entry-header' ); ?>
    
	<div id="post-content" class="post-inner">
		<div class="section-inner do-spot spot-fade-up a-del-200">
			<div class="section-inner max-percentage no-margin mw-thin">
				<div class="entry-content">
					<div class="has-lg-font-size">
						<?php the_content(); ?>
					</div>
                    <form method="POST" action="" class="custom-map-form" >
                        <input id="searchInput" class="input-controls" type="text" placeholder="Enter a location">
                        <div class="map" id="map" style="width: 100%; height: 300px;"></div>
                        <input id="place-location-fields" type="hidden" name="place_location" value="">
                        <div class="form_area">
                        <input type="text" name="location" id="location">
                            <input type="text" name="lat" id="lat" >
                            <input type="text" name="lng" id="lng">
                        </div>
                        <input type = "submit" class="submitbtn" value="Next : Write Your Story">
                    </form>
				</div><!-- .entry-content -->
			</div>
		</div><!-- .section-inner -->
	</div><!-- .post-inner -->
	
</article><!-- .post -->
<script type="text/javascript">
    /* script */
function initialize() {
   var latlng = new google.maps.LatLng(28.5355161,77.39102649999995);
    var map = new google.maps.Map(document.getElementById('map'), {
      center: latlng,
      zoom: 3
    });
    var marker = new google.maps.Marker({
      map: map,
      position: latlng,
      draggable: true,
      anchorPoint: new google.maps.Point(0, -29)
   });
    var input = document.getElementById('searchInput');
    var geocoder = new google.maps.Geocoder();
    var autocomplete = new google.maps.places.Autocomplete(input);
    autocomplete.bindTo('bounds', map);
   // var infowindow = new google.maps.InfoWindow();   
    autocomplete.addListener('place_changed', function() {
        
        marker.setVisible(false);
        var place = autocomplete.getPlace();
        if (!place.geometry) {
            window.alert("Autocomplete's returned place contains no geometry");
            return;
        }
  
        // If the place has a geometry, then present it on a map.
        if (place.geometry.viewport) {
            map.fitBounds(place.geometry.viewport);
        } else {
            map.setCenter(place.geometry.location);
            map.setZoom(17);
        }
       
        marker.setPosition(place.geometry.location);
        marker.setVisible(true);          
    
        bindDataToForm(place.formatted_address,place.geometry.location.lat(),place.geometry.location.lng());
       
       
    });
    // this function will work on marker move event into map 
    google.maps.event.addListener(marker, 'dragend', function() {
        geocoder.geocode({'latLng': marker.getPosition()}, function(results, status) {
            console.log(results);
        if (status == google.maps.GeocoderStatus.OK) {
          if (results[0]) {        
              bindDataToForm(results[0].formatted_address,marker.getPosition().lat(),marker.getPosition().lng());
         
          }
        }
        });
    });
}
function bindDataToForm(address,lat,lng){

//a:10:{s:7:"address";s:15:"Porto, Portugal";s:3:"lat";d:41.1579437999999981911969371140003204345703125;s:3:"lng";d:-8.62910529999999909023244981653988361358642578125;s:4:"zoom";i:1;s:8:"place_id";s:27:"ChIJwVPhxKtlJA0RvBSxQFbZSKY";s:4:"name";s:5:"Porto";s:4:"city";s:5:"Porto";s:5:"state";s:14:"Porto District";s:7:"country";s:8:"Portugal";s:13:"country_short";s:2:"PT";}
 //address = address;
  address.substring(address.indexOf(",") + 1); 
  document.getElementById('location').value =  address.substring(address.indexOf(",") + 1);
    document.getElementById('searchInput').value =  address.substring(address.indexOf(",") + 1);
   document.getElementById('lat').value = lat;
   document.getElementById('lng').value = lng;
}
google.maps.event.addDomListener(window, 'load', initialize);
</script>