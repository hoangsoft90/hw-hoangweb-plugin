//__hw_modules.xxx='test';
/**
 * initialize google map
 * @param map_canvas: map container
 * @param input_searchbox: input search box
 */
__hw_module_map.map_initialize = function (map_canvas, input_searchbox, location) {
    if(!location && typeof __hw_module_map.address_location =='object') {
        location = __hw_module_map.address_location;
    }
    if(typeof location == 'string' ) {
        __hw_module_map.get_location(null, location, function(data) {
            __hw_module_map.display_map (data,map_canvas, input_searchbox);
        });
    }
    else if(typeof location =='object') {
        this.display_map (location,map_canvas, input_searchbox);
    }
};
/**
 * render google map
 * @param location
 * @param map_canvas
 * @param input_searchbox
 */
__hw_module_map.display_map = function(location, map_canvas, input_searchbox ) {
    //get company address configration
    var lat = location.lat,
        lng = location.lng;

    var markers = [];
    var map = new google.maps.Map(jQuery(map_canvas).get(0), {
        mapTypeId: google.maps.MapTypeId.ROADMAP
    });

    var defaultBounds = new google.maps.LatLngBounds(
        new google.maps.LatLng(lat, lng)
        //,new google.maps.LatLng(-33.8474, 151.2631)
    );
    map.fitBounds(defaultBounds);

    // Create the search box and link it to the UI element.
    var input = /** @type {HTMLInputElement} */(
        jQuery(input_searchbox).get(0));
    map.controls[google.maps.ControlPosition.TOP_LEFT].push(input);

    var searchBox = new google.maps.places.SearchBox(
        /** @type {HTMLInputElement} */(input));

    // Listen for the event fired when the user selects an item from the
    // pick list. Retrieve the matching places for that item.
    google.maps.event.addListener(searchBox, 'places_changed', function() {
        var places = searchBox.getPlaces();

        if (places.length == 0) {
            return;
        }
        for (var i = 0, marker; marker = markers[i]; i++) {
            marker.setMap(null);
        }

        // For each place, get the icon, place name, and location.
        markers = [];
        var bounds = new google.maps.LatLngBounds();
        for (var i = 0, place; place = places[i]; i++) {
            var image = {
                url: place.icon,
                size: new google.maps.Size(71, 71),
                origin: new google.maps.Point(0, 0),
                anchor: new google.maps.Point(17, 34),
                scaledSize: new google.maps.Size(25, 25)
            };

            // Create a marker for each place.
            var marker = new google.maps.Marker({
                map: map,
                icon: image,
                title: place.name,
                position: place.geometry.location
            });

            markers.push(marker);

            bounds.extend(place.geometry.location);
        }

        map.fitBounds(bounds);
    });

    // Bias the SearchBox results towards places that are within the bounds of the
    // current map's viewport.
    google.maps.event.addListener(map, 'bounds_changed', function() {
        var bounds = map.getBounds();
        searchBox.setBounds(bounds);
    });
}
/**
 * get lat, lng from address string
 * @param address
 * @param targetId
 */
__hw_module_map.get_location = function(obj,address, targetId) {
    if(address) {
        if(jQuery(obj).length && jQuery(obj).hw_is_ajax_working({loadingText: 'loading..'})) {
            return;
        }

        jQuery.ajax({
            url: 'http://maps.google.com/maps/api/geocode/json?address='+encodeURIComponent(address)+'&sensor=false',
            success: function(response) {
                if(jQuery(obj).length) jQuery(obj).hw_reset_ajax_state();
                if(typeof response.results[0] == 'undefined') {
                    alert("Không tìm thấy địa chỉ này, thử lại.");
                    return ;
                }
                var location = response.results[0].geometry.location;
                if(location) {
                    if(typeof targetId =='string') jQuery(targetId).val(JSON.stringify(location));
                    else if(typeof targetId =='function') targetId(location);
                }
            }
        });
    }

};