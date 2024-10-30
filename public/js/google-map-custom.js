if (jQuery('#googleMap').length > 0) {
    $obj = jQuery('#googleMap');

    var myCenter = new google.maps.LatLng($obj.data('lat'), $obj.data('lon'));
    var myMaker = new google.maps.LatLng($obj.data('lat'), $obj.data('lon'));

    var primary_color = '#3B99FC';
    var map_style_dark = [{
        featureType: "all",
        elementType: "labels.text.fill",
        stylers: [{saturation: 36}, {color: "#000000"}, {lightness: 40}]
    }, {
        featureType: "all",
        elementType: "labels.text.stroke",
        stylers: [{visibility: "on"}, {color: "#000000"}, {lightness: 16}]
    }, {featureType: "all", elementType: "labels.icon", stylers: [{visibility: "off"}]}, {
        featureType: "administrative",
        elementType: "all",
        stylers: [{lightness: "-1"}]
    }, {
        featureType: "administrative",
        elementType: "geometry.fill",
        stylers: [{color: "#000000"}, {lightness: 20}]
    }, {
        featureType: "administrative",
        elementType: "geometry.stroke",
        stylers: [{color: "#000000"}, {lightness: 17}, {weight: 1.2}]
    }, {
        featureType: "administrative.country",
        elementType: "all",
        stylers: [{lightness: "20"}]
    }, {
        featureType: "administrative.country",
        elementType: "geometry.stroke",
        stylers: [{visibility: "on"}, {color: primary_color}]
    }, {
        featureType: "administrative.country",
        elementType: "labels.text",
        stylers: [{color: primary_color}, {visibility: "simplified"}]
    }, {
        featureType: "administrative.country",
        elementType: "labels.icon",
        stylers: [{visibility: "off"}]
    }, {
        featureType: "administrative.province",
        elementType: "all",
        stylers: [{lightness: "20"}]
    }, {
        featureType: "administrative.province",
        elementType: "labels.text",
        stylers: [{color: primary_color}, {visibility: "off"}]
    }, {
        featureType: "administrative.locality",
        elementType: "all",
        stylers: [{lightness: "0"}, {color: primary_color}, {saturation: "9"}, {visibility: "simplified"}]
    }, {
        featureType: "landscape",
        elementType: "geometry",
        stylers: [{color: "#000000"}, {lightness: 20}]
    }, {
        featureType: "poi",
        elementType: "geometry",
        stylers: [{color: "#000000"}, {lightness: 21}]
    }, {featureType: "poi", elementType: "geometry.fill", stylers: [{color: "#3e3e3e"}]}, {
        featureType: "road.highway",
        elementType: "geometry.fill",
        stylers: [{color: "#000000"}, {lightness: 17}]
    }, {
        featureType: "road.highway",
        elementType: "geometry.stroke",
        stylers: [{color: primary_color}, {lightness: 29}, {weight: .2}]
    }, {
        featureType: "road.arterial",
        elementType: "geometry",
        stylers: [{color: "#000000"}, {lightness: 18}]
    }, {
        featureType: "road.local",
        elementType: "geometry",
        stylers: [{color: "#000000"}, {lightness: 16}]
    }, {
        featureType: "transit",
        elementType: "geometry",
        stylers: [{color: "#000000"}, {lightness: 19}]
    }, {
        featureType: "water",
        elementType: "all",
        stylers: [{visibility: "simplified"}, {lightness: "-62"}]
    }, {featureType: "water", elementType: "geometry", stylers: [{color: "#13232a"}, {lightness: 17}]}];
    var map_style_light = [{
        featureType: "administrative",
        elementType: "labels.text.fill",
        stylers: [{color: "#444444"}]
    }, {featureType: "landscape", elementType: "all", stylers: [{color: "#f2f2f2"}]}, {
        featureType: "poi",
        elementType: "all",
        stylers: [{visibility: "off"}]
    }, {
        featureType: "poi.park",
        elementType: "all",
        stylers: [{visibility: "on"}, {color: "#bcd9c3"}]
    }, {
        featureType: "road",
        elementType: "all",
        stylers: [{saturation: -100}, {lightness: 45}]
    }, {
        featureType: "road.highway",
        elementType: "all",
        stylers: [{visibility: "simplified"}]
    }, {
        featureType: "road.arterial",
        elementType: "labels.icon",
        stylers: [{visibility: "off"}]
    }, {featureType: "transit", elementType: "all", stylers: [{visibility: "off"}]}, {
        featureType: "transit.station",
        elementType: "all",
        stylers: [{visibility: "off"}, {weight: "0.28"}]
    }, {
        featureType: "transit.station",
        elementType: "labels.text",
        stylers: [{color: "#555555"}]
    }, {
        featureType: "transit.station",
        elementType: "labels.icon",
        stylers: [{saturation: "-66"}]
    }, {featureType: "transit.station.rail", elementType: "all", stylers: [{visibility: "on"}]}, {
        featureType: "water",
        elementType: "all",
        stylers: [{color: "#d5def2"}, {visibility: "on"}]
    }, {featureType: "water", elementType: "labels.text.fill", stylers: [{color: "#ffffff"}]}, {
        featureType: "water",
        elementType: "labels.text.stroke",
        stylers: [{visibility: "on"}]
    }, {
        featureType: "administrative.country",
        elementType: "geometry.stroke",
        stylers: [{visibility: "on"}, {color: primary_color}]
    }, {
        featureType: "administrative.country",
        elementType: "labels.text",
        stylers: [{color: primary_color}, {visibility: "simplified"}]
    }, {
        featureType: "administrative.province",
        elementType: "labels.text",
        stylers: [{color: primary_color}, {visibility: "off"}]
    }, {
        featureType: "administrative.locality",
        elementType: "all",
        stylers: [{lightness: "0"}, {color: primary_color}, {saturation: "9"}, {visibility: "simplified"}]
    }];
    var map_style_apple = [{
        featureType: "landscape.man_made",
        elementType: "geometry",
        stylers: [{color: "#f7f1df"}]
    }, {
        featureType: "landscape.natural",
        elementType: "geometry",
        stylers: [{color: "#d0e3b4"}]
    }, {
        featureType: "landscape.natural.terrain",
        elementType: "geometry",
        stylers: [{visibility: "off"}]
    }, {featureType: "poi", elementType: "labels", stylers: [{visibility: "off"}]}, {
        featureType: "poi.business",
        elementType: "all",
        stylers: [{visibility: "off"}]
    }, {featureType: "poi.medical", elementType: "geometry", stylers: [{color: "#fbd3da"}]}, {
        featureType: "poi.park",
        elementType: "geometry",
        stylers: [{color: "#bde6ab"}]
    }, {featureType: "road", elementType: "geometry.stroke", stylers: [{visibility: "off"}]}, {
        featureType: "road",
        elementType: "labels",
        stylers: [{visibility: "off"}]
    }, {
        featureType: "road.highway",
        elementType: "geometry.fill",
        stylers: [{color: "#ffe15f"}]
    }, {
        featureType: "road.highway",
        elementType: "geometry.stroke",
        stylers: [{color: "#efd151"}]
    }, {
        featureType: "road.arterial",
        elementType: "geometry.fill",
        stylers: [{color: "#ffffff"}]
    }, {
        featureType: "road.local",
        elementType: "geometry.fill",
        stylers: [{color: "black"}]
    }, {
        featureType: "transit.station.airport",
        elementType: "geometry.fill",
        stylers: [{color: "#cfb2db"}]
    }, {featureType: "water", elementType: "geometry", stylers: [{color: "#a2daf2"}]}];
    var map_style_nature = [{
        featureType: "landscape",
        stylers: [{hue: "#FFA800"}, {saturation: 0}, {lightness: 0}, {gamma: 1}]
    }, {
        featureType: "road.highway",
        stylers: [{hue: "#53FF00"}, {saturation: -73}, {lightness: 40}, {gamma: 1}]
    }, {
        featureType: "road.arterial",
        stylers: [{hue: "#FBFF00"}, {saturation: 0}, {lightness: 0}, {gamma: 1}]
    }, {
        featureType: "road.local",
        stylers: [{hue: "#00FFFD"}, {saturation: 0}, {lightness: 30}, {gamma: 1}]
    }, {
        featureType: "water",
        stylers: [{hue: "#00BFFF"}, {saturation: 6}, {lightness: 8}, {gamma: 1}]
    }, {featureType: "poi", stylers: [{hue: "#679714"}, {saturation: 33.4}, {lightness: -25.4}, {gamma: 1}]}];


    function initialize() {
        var style = $obj.data('map_style') ? $obj.data('map_style') : '';
        var map_style = map_style_dark;
        switch (style) {
            case "none":
                map_style = [];
                break;
            case "light":
                map_style = map_style_light;
                break;
            case "apple":
                map_style = map_style_apple;
                break;
            case "nature":
                map_style = map_style_nature;
                break;
        }

        var mapProp = {
            center: myCenter,
            zoom: 14,
            scrollwheel: false,
            mapTypeControlOptions: {
                mapTypeIds: [google.maps.MapTypeId.ROADMAP, 'map_style']
            },
            styles: map_style
        };
        var map = new google.maps.Map(document.getElementById("googleMap"), mapProp);
        var marker = new google.maps.Marker({
            position: myMaker,
            icon: $obj.data('icon'),
            animation: google.maps.Animation.DROP,
        });
        marker.setMap(map);

        var infoBox = new google.maps.InfoWindow();
        infoBox.setContent(
            '<p><strong> ' + $obj.data('address') + '</strong></p>'
        );
        marker.addListener('click', function () {
            infoBox.open(map, marker);
        });
    }

    google.maps.event.addDomListener(window, 'load', initialize);

}