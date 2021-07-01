/**
 * Reverse Geocode Lat/Long (i.e. Current Lcoation) and prepopulate address fields
 * @type {google}
 */
var geocoder=new google.maps.Geocoder();
function reverseGeo(lat,lng) {
    var latlng = new google.maps.LatLng(parseFloat(lat), parseFloat(lng));
    geocoder.geocode({'latLng': latlng}, function(results, status) {

        if (status === google.maps.GeocoderStatus.OK) {
            var address = {};
            var format_address = results[0].formatted_address;
            var part = format_address.split(',');

            var city = part[1].replace(/^\s+/,"");

            // Check if result city is allowed:
            var allowed_cities = new Array;
            $("#issue_city option").each  ( function() {
               allowed_cities.push ( $(this).val() );
            });
            if(allowed_cities.indexOf(city) > -1){
                // City is allowed
                $('#issue_city').val(city);
                $('#issue_state').val(part[2].split(' ')[1].replace(/^\s+/,""));
                var zip = part[2].split(' ')[2];
                $('#issue_address').val(part[0]);
            } else {
                console.log('Every city is allowed!');
                // alert("Warning: MapFeeder Connect has not been deployed for " + city + ".");
        $('#issue_address').val('');
        $('#issue_city').val('');
            }


        } else {
            console.log("Geocoder failed due to: " + status);
        }
    });
}

/**
 * Geocode Address - set lat/long based on address entered in form
 */
function geocodeAddress() {

    // Check that all fields are filled
    if($('#issue_city').val() === ""){
        return;
    }
    if($('#issue_state').val() === ""){
        return;
    }
    if($('#issue_address').val() === ""){
        return;
    }

    city = $('#issue_city').val();
    street = $('#issue_address').val();
    state = $('#issue_state').val();

    var address;
    if (street==="") {
        address = city+","+state;
    } else {
        address = street+","+city+","+state;
    }


    geocoder.geocode({ 'address': address, 'region':'us'}, function(results, status) {
        if (status == google.maps.GeocoderStatus.OK) {

            $('#lng').val(Math.round(results[0].geometry.location.lng()*1000000)/1000000);
            $('#lat').val(Math.round(results[0].geometry.location.lat()*1000000)/1000000);

        } else {
            console.log("Address lookup failed for the following reason: " + status);
        }
    });
}


/**
* Check City
*/
function checkCity(theCity) {
    if (!$('#issue_city option[value="' + theCity + '"]').prop("selected", true).length) {
        alert('Warning: mapFeeder Connect has not been deployed for ' + theCity);;
    }
    return true;
}
