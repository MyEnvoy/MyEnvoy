function getLocation() {
    if (navigator.geolocation) {
        navigator.geolocation.getCurrentPosition(showPosition);
    }
}

function showPosition(position) {
    var lat = position.coords.latitude;
    var lon = position.coords.longitude;

    var url = 'https://maps.googleapis.com/maps/api/geocode/json?latlng=' + lat + ',' + lon;

    $.getJSON(url, function (result) {
        if (result.results[0] !== null) {
            $('input#city').val(result.results[0].formatted_address);
        }
    });
}