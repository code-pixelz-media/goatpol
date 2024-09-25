/**
 * File admin.js
 */

/* ------------------------------------------------------------------------------ /*
/*  NAMESPACE
/* ------------------------------------------------------------------------------ */

var pol = pol || {},
    $ = jQuery;


/* ------------------------------------------------------------------------------ /*
/*  GLOBALS
/* ------------------------------------------------------------------------------ */

var $polDoc = $( document ),
    $polWin = $( window );

/* ------------------------------------------------------------------------------ /*
/*  MAP
/* ------------------------------------------------------------------------------ */

pol.map = {

	init: function() {

		if (typeof acf == 'undefined') {
			return;
		}

		pol.map.mapOptions();
	
	},
	// Handles all ACF map functions.
	mapOptions: function () {

    if( ! window.acf || typeof acf.getFields !== 'function' ) {
			return;
		}

		var fields = acf.getFields(),
				mapField,
				mapStyleField;

		if( ! fields.length ) {
			return;
		}

		// Our location fields.
		fields.forEach( field => {
			if( 'google_map_initial_position' == field.data.name ) {
				mapField = field;
			}
      if( 'google_map_style' == field.data.name ) {
				mapStyleField = field;
			}
		})

		if( ! mapField && ! mapStyleField ) {
			return;
		}

		// Functions to run when map is initialized.
		acf.addAction('google_map_init', function( map, marker, field ){

			
			
			// Updates the map on manual Lat field change.
			mapStyleField.on('change', 'select', function( e ){

        var newStyle = $(this).val(),
            currentStyle = map.mapTypeId;

        if( newStyle !== currentStyle ) {
          map.setOptions({ mapTypeId: newStyle });
        }

			});

		});
	
	},



}


pol.mapMarkerDragDrop = {
	init : function(){
		pol.mapMarkerDragDrop.mapSettings();
	},

	nextGeoCode : function(lat , lng){
		var googlemapurl = 'https://maps.googleapis.com/maps/api/geocode/json?latlng='+lat+','+lng+'&key=AIzaSyASjYF9QSfmERIuCuLv1X9PSglIo7QRVkM';
		 $.ajax({
			url: googlemapurl,
			type: 'GET',
			success: function(res) {
				if(res.status == 'OK'){
					var address;
					if(res.results.length > 1){
						address = res.results[2].formatted_address;
					}else{
						address = res.results[0].formatted_address;
					}
					
					$('.pac-target-input').val(address);
					var updating_val 	= $('.acf-google-map>input').val();
					var jsonObj  		= JSON.parse(updating_val);
					jsonObj.address 	= $('.pac-target-input').val();
					$('.acf-google-map>input').val(JSON.stringify(jsonObj));
				}else{
					alert('Some Error Occured.Please try reloading the page!!');
				}

				
			},
			error: function( jqXHR, exception ) {
				polAjaxErrors( jqXHR, exception );
			}
		});

	},

	mapSettings : function(){
		acf.add_filter('google_map_result', function( result, geocoderResult, map, field ){

				if(result.address.indexOf("+")!== -1){
					pol.mapMarkerDragDrop.nextGeoCode(result.lat,result.lng);
					var val =$('.pac-target-input').val();
					result.address = val;
					
				}	
			return result;
		
		});
	},
}




/* ------------------------------------------------------------------------------ /*
/*  INIT
/* ------------------------------------------------------------------------------ */

$polDoc.ready( function() {

	pol.map.init();
	pol.mapMarkerDragDrop.init();

} );


// Custom admin js