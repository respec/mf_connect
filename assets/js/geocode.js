
var geocoder=new google.maps.Geocoder();
function reverseGeo(lat,lng) {
    var address = $('.geoaddr');
    var city = $('.geocity');
    var state = $('.geostate');
    var zip = $('.geozip');
    if(address.length === 0 && city.length === 0 && state.length === 0 && zip.length === 0){
        return;
    }
    var latlng = new google.maps.LatLng(lat, lng);
    geocoder.geocode({'latLng': latlng}, function(results, status) {
        if (status == google.maps.GeocoderStatus.OK) {

            var format_address = results[0].formatted_address;
            var part = format_address.split(',');


            cityval = part[1].replace(/^\s+/,"");
            stateval = part[2].split(' ')[1].replace(/^\s+/,"");
            zipval = part[2].split(' ')[2];
            addressval = part[0];


            if(
                address.attr('disabled') == 'disabled' && address.val() != addressval ||
                city.attr('disabled') == 'disabled' && city.val() != cityval ||
                state.attr('disabled') == 'disabled' && state.val() != stateval
            ){

                var msg = "You may only enter addresses in:";
                if(address.attr('disabled') == 'disabled'){
                    msg = msg + "<br>" + address.val();
                }
                if(city.attr('disabled') == 'disabled'){
                    msg = msg + "<br>" + city.val();
                }
                if(state.attr('disabled') == 'disabled'){
                    msg = msg + "<br>" + state.val();
                }
                $("#message").html(msg);
                $("#message").dialog({
                    bgiframe: true,
                    modal: true,
                    buttons: {
                        'Ok': function() {
                            $(this).dialog('destroy');
                            $(this).empty();
                        }
                    }
                });
                return;
            }

            address.val(addressval);
            city.val(cityval);
            state.val(stateval);
            zip.val(zipval);
        } else {
            alert("Geocoder failed due to: " + status);
        }
    });
}
function markDot() {
    var lat = $('#lat');
    var lng = $('#lng');
    if(lat.val() === "" || lng.val() === ""){
        return false;
    }
    // Clear previous dot
    var loc = new OpenLayers.LonLat(lng.val(),lat.val());
    var click_mark = {
        type : "Point",
        coordinates : [loc.lon,loc.lat]
    };
    $('input[name=geojson]').val(JSON.stringify(click_mark));
    loc.transform(new OpenLayers.Projection("EPSG:4326"),map.getProjectionObject());
    click_mark.coordinates = [loc.lon,loc.lat];
    search_interaction.mark(geojson.read(click_mark));
    // Check to see if the City, State, and Zip is define
    var address = $('.geoaddr');
    var city = $('.geocity');
    var state = $('.geostate');
    var zip = $('.geozip');
    if(address.length !== 0 && city.length !== 0 && state.length !== 0 && zip.length !== 0){
        var latlng = new google.maps.LatLng(lat.val(), lng.val());
        geocoder.geocode({'latLng': latlng}, function(results, status) {
            if (status == google.maps.GeocoderStatus.OK) {
                var format_address = results[0].formatted_address;
                var part = format_address.split(',');
                city.val(part[1].replace(/^\s+/,""));
                state.val(part[2].split(' ')[1].replace(/^\s+/,""));
                zip.val(part[2].split(' ')[2]);
                address.val(part[0]);
            } else {
                alert("Geocoder failed due to: " + status);
            }
        });
    }
}

var geoCodeTimeout;
// Since we're calling this on every change, we need to
// take a breath, do a setTimeout and make sure we're
// not geocoding every letter they type
function fastCodeAddress() {
    clearTimeout(geoCodeTimeout);
    geoCodeTimeout = setTimeout(function(){
        codeAddress();
    },500);
}

function codeAddress(street,city,state,zip,altLng,altLat) {

    // No more required fields
    if(typeof street == 'undefined'){
        street = '.geoaddr';
    }else{
        street = '#' + street;
    }

    if(typeof city == 'undefined'){
        city = '.geocity';
    }else{
        city = '#' + city;
    }

    if(typeof state == 'undefined'){
        state = '.geostate';
    }else{
        state = '#' + state;
    }

    if(typeof zip == 'undefined'){
        zip = '.geozip';
    }else{
        zip = '#' + zip;
    }

    // Assume lng and lat fields if alternates are not specified
    if ((altLng === undefined) || (altLat === undefined)) {
        foundLng="lng";
        foundLat="lat";
    } else {
        foundLng=altLng;
        foundLat=altLat;
    }

    // Check that all fields are filled
    if($(street).val() === ""){
        return;
    }
    if($(city).val() === ""){
        return;
    }
    if($(state).val() === ""){
        return;
    }


    if(typeof($(city).selectedTexts()[0]) != 'undefined') {
        city = $(city).selectedTexts()[0];
    } else {
        city = $(city).val();
    }
    street = $(street).val();
    state = $(state).val();
    zip = $(zip).val();
    var address;
    if (street==="") {
        address = city+","+state+" "+zip;
    } else {
        address = street+","+city+","+state+" "+zip;
    }


    geocoder.geocode({ 'address': address, 'region':'us'}, function(results, status) {
        if (status == google.maps.GeocoderStatus.OK) {
            if(typeof(search_interaction) !== 'undefined'){ // NEW VERSION : USE VECTOR
                $('#'+foundLng).val(Math.round(results[0].geometry.location.lng()*1000000)/1000000);
                $('#'+foundLat).val(Math.round(results[0].geometry.location.lat()*1000000)/1000000);
                var loc = new OpenLayers.LonLat($('#lng').val(),$('#lat').val());
                var click_mark = {
                    type : "Point",
                    coordinates : [loc.lon,loc.lat]
                };
                $('input[name=geojson]').val(JSON.stringify(click_mark));
                var mark = geojson.read(click_mark);
                mark[0].geometry.transform(prj4326,  prj900913);
                search_interaction.mark(mark);
                if (map.getZoom() != defaultFeatureZoom){
                    map.zoomTo(defaultFeatureZoom);
                }
            } else { // OLD VERSION : USER MARKER
                $('#'+foundLng).val(Math.round(results[0].geometry.location.lng()*1000000)/1000000);
                $('#'+foundLat).val(Math.round(results[0].geometry.location.lat()*1000000)/1000000);
                markers.clearMarkers();
                var newLoc = addMarker($('#lng').val(),$('#lat').val());
                if (map.getZoom() != defaultFeatureZoom){
                    map.zoomTo(defaultFeatureZoom);
                }
                map.panTo(newLoc);
            }
        } else {
            alert("Address lookup failed for the following reason: " + status);
        }
    });
}