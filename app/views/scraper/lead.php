<?php

/** @var \ScraperLead $lead */
$lead = $this->getVar('lead');

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

<h1 class="page_header">Lead - <small><?=$lead->getScraperUrl()->name?></small></h1>

<p><a href="<?=$lead->url?>" target="_blank"><?=$lead->url?></a></p>

<?php if ($lead->street) { ?>
    <p><?=$lead->street?><br /><?=$lead->city?>, <?=$lead->state?> <?=$lead->zip?><br /></p>
<?php } ?>

<?php if ($lead->lon && $lead->lat) { ?>

    <div id="pano"></div>
    <div id="map"></div>

    <script src="https://maps.googleapis.com/maps/api/js?key=<?=$_ENV['GOOGLE_MAPS_CLIENT_KEY']?>&callback=initialize&v=weekly" defer></script>

    <script>
        var geocoder;

        function initialize() {
            const fenway = { lat: <?=$lead->lat?>, lng: <?=$lead->lon?> };
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

<?php } ?>

