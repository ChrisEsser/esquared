<?php

/** @var \ScraperLeadAddress $address */
$address = $this->getVar('address');

?>

<style>
    #map, #pano {
        z-index: 9999 !important;
        float: left;
        height: 500px;
        width: 50%;
    }
    @media screen and (max-width: 768px) {
        #map, #pano {
            width: 100%;
            float: none;
        }
    }

</style>

<div id="pano"></div>
<div id="map"></div>


<script src="https://maps.googleapis.com/maps/api/js?key=<?=$_ENV['GOOGLE_MAPS_CLIENT_KEY']?>&callback=initialize&v=weekly" defer></script>

<script>
    var geocoder;

    function initialize() {
        const fenway = { lat: <?=$address->lat?>, lng: <?=$address->lon?> };
        const map = new google.maps.Map(document.getElementById("map"), {
            center: fenway,
            zoom: 14,
        });
        const panorama = new google.maps.StreetViewPanorama(
            document.getElementById("pano"),
            {
                position: fenway,
                pov: {
                    heading: 34,
                    pitch: 10,
                },
            }
        );
        map.setStreetView(panorama);
    }

    window.initialize  = initialize;
</script>