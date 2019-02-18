
$(function() {
    if (!$('.navbar-small').length) {
        $(window).on('scroll', function() {
            if ($(document).scrollTop() > 200)
                $('.navbar').addClass('navbar-small');
            else
                $('.navbar').removeClass('navbar-small');
        });
    }

    if ($('#map').length) {
        // temp data, todo: load from API
        var data = [
            {"name": "Ostrava", "la": 49.846589, "lo": 18.264158},
            {"name": "Praha", "la": 50.079418, "lo": 14.436776},
            {"name": "Brno", "la": 49.188973, "lo": 16.610911},
            {"name": "Plzeň", "la": 49.741454, "lo": 13.378101}
        ];

        var CustomIcon = L.Icon.Label.extend({
            options: {
                iconUrl: '/images/circle.png',
                shadowUrl: null,
                iconSize: new L.Point(12, 12),
                iconAnchor: new L.Point(0, 1),
                labelAnchor: new L.Point(16, 4),
                wrapperAnchor: new L.Point(12, 13),
                labelClassName: 'custom-icon-label'
            }
        });

        function initMarkers() {
            var len = data.length, bounds = [];

            for (var i = 0; i < len; i++) {
                var m = L.marker([data[i].la, data[i].lo], {icon: new CustomIcon({labelText: data[i].name})});
                markers.addLayer(m);
                bounds.push(m.getLatLng());
            }

            if (bounds.length > 0)
               map.fitBounds(L.latLngBounds(bounds));

            return false;
        }

        var layer = L.tileLayer('https://madvision.cz/map.php?url=http://{s}.basemaps.cartocdn.com/dark_nolabels/{z}/{x}/{y}.png', {});

        var map = L.map('map', {
            scrollWheelZoom: false,
            center: [49.856546, 15.523931],
            zoom: 7,
            minZoom: 3
        });

        map.addLayer(layer);

        var markers = L.markerClusterGroup({
            maxClusterRadius: 50,
            spiderfyOnMaxZoom: false,
            showCoverageOnHover: true,
            zoomToBoundsOnClick: true
        });

        initMarkers();
        map.addLayer(markers);
    }
});