<?php

/** @var ScraperUrl[] $scrapers */
$scrapers = $this->getVar('scrapers');

?>

<style>

    #mapview_container {
        height: 100%;
        width: 103%;
        margin-left: calc(3rem * .5 * -1);
        margin-right: calc(3rem * .5 * -1);
    }

    #mapview_left_container {
        border-right: 1px solid #ccc;
        width: 30%;
        height: calc(100vh - 62.8px);
    }
    #mapview_bottom_left {
        overflow-y: auto;
    }
    #mapview_top_left {
        border-bottom: 1px solid #ccc;
        border-top: 1px solid #ccc;
    }
    .mapview_lead_item:hover {
        background-color: #eeffff;
        cursor: pointer;
    }
    #mapview_map_container {
        height: 100%;
        width: 100%;
        min-height: 300px;
    }

    @media (max-width: 1280px) {
        #mapview_left_container {
            width: 35%;
        }
    }

    @media (max-width: 768px) {
        #mapview_container {
            width: 100vw;
        }
        #mapview_bottom_left {
            overflow-y: inherit;
        }
        #mapview_left_container  {
            width: 100%;
            height: auto;
            border-right: none;
        }
        #mapview_leads_container {
            padding-bottom: 20px;
        }
    }

</style>

<div id="mapview_container" class="d-flex flex-column-reverse flex-md-row">

    <div id="mapview_left_container" class="align-items-stretch d-flex flex-column">

        <div id="mapview_top_left">

            <div class="p-2">

                <div class="mb-3">
<!--                    <label for="scraper">Scraper URL</label>-->
                    <div class="input-group">
                        <select class="form-control" id="scraper">
                            <option value="">All</option>
                            <?php foreach ($scrapers as $scraper) { ?>
                                <option value="<?=$scraper->url_id?>"><?=$scraper->name?> <?=$scraper->state?></option>
                            <?php } ?>
                        </select>
                        <span class="input-group-append">
                            <span class="input-group-text bg-light d-block">
                                <a href="javascript:void(0);" id="refresh_page_trigger"><i class="fa fa-refresh"></i></a>
                            </span>
                        </span>
                    </div>
                </div>

                <div class="mb-3">
                    <div class="input-group">
                        <select class="form-control" id="sort">
                            <option value="city">City</option>
                            <option value="state">State</option>
                            <option value="street">Street</option>
                            <option value="judgment_amount">Judgement</option>
                            <option value="created">Created Date</option>
                        </select>
                        <span class="input-group-append">
                            <span class="input-group-text bg-light d-block">
                                <i class="fa fa fa-sort-alpha-asc"></i>
                            </span>
                        </span>
                    </div>
                </div>

                <div class="mb-3">
                    <div class="input-group">
                        <input type="text" class="form-control" id="search" placeholder="Search" />
                        <span class="input-group-append">
                            <span class="input-group-text bg-light d-block">
                                <i class="fa fa-search"></i>
                            </span>
                        </span>
                    </div>
                </div>

            </div>


        </div>

        <div id="mapview_bottom_left">

            <div id="mapview_leads_container" class="d-flex flex-column"></div>

        </div>

    </div>

    <div id="mapview_right_container" class="align-items-stretch flex-grow-1">

        <div id="mapview_map_container"></div>

    </div>

</div>

<script src="https://maps.googleapis.com/maps/api/js?key=<?=$_ENV['GOOGLE_MAPS_CLIENT_KEY']?>&callback=refreshPage&v=weekly" defer></script>

<script>

    let map;
    let infoWindow;
    let markers;
    let locations;
    var timeout;

    const moneyFormatter = new Intl.NumberFormat('en-US', {
        style: 'currency',
        currency: 'USD',
    });

    $(document).ready(function() {
        $('#refresh_page_trigger').click(function () {
            refreshPage();
        });
        $('#scraper').change(function() {
            refreshPage();
        });
        $('#search').keyup(function() {
            clearTimeout(timeout);
            timeout = setTimeout(function() {
                refreshPage();
            }, 400);
        });
        $('#sort').change(function() {
            refreshPage();
        });
        $(document).on('click', '.mapview_lead_item', function () {
            let markId = $(this).data('markerid');
            if (typeof markers[markId] == 'object') {
                google.maps.event.trigger(markers[markId], 'click');
            }
        });
    });

    function initMap() {
        if (locations.length) {
            map = new google.maps.Map(document.getElementById('mapview_map_container'), {
                mapTypeId: google.maps.MapTypeId.ROADMAP,
                maxZoom: 19
            });

            const formatter = new Intl.NumberFormat('en-US', {
                style: 'currency',
                currency: 'USD',
            });

            markers = locations.map(function(location, i) {
                var marker = new google.maps.Marker({
                    position: location,
                    map
                });
                var content = '<div>';
                content += '<h5>' + location.url_name + '</h5>';
                content += '<p style="font-size: 1.2em;">' + location.addresses[0].street;
                content += '<br />' + location.addresses[0].city + ',' + location.addresses[0].zip + ', ' + location.addresses[0].state + '</p>';
                content += '<p style="font-size: 1.2em;"><strong>' + moneyFormatter.format(location.judgment_amount) + '</strong></p>';
                content += '<p style="font-size: 1.2em;"><strong>First Seen: </strong>' + location.created;
                content += '<br /><strong>Last Seen: </strong>' + location.last_seen;
                content += '<p style="font-size: 1.2em;"><a href="' + location.url + '" target="_blank">Info URL</a></p>';
                content += '</div>';
                // add a listener to trigger the profile modal
                google.maps.event.addListener(marker, 'click', function() {
                    infowindow.setContent(content);
                    infowindow.open(map, marker);
                    map.panTo(this.getPosition());
                });
                return marker;
            });
            infowindow = new google.maps.InfoWindow({
                content: ''
            });
            var bounds = new google.maps.LatLngBounds();
            for (var i = 0; i < markers.length; i++) {
                bounds.extend(markers[i].getPosition());
            }
            map.fitBounds(bounds);

        } else {
            map = new google.maps.Map(document.getElementById("mapview_map_container"), {
                center: {lat: 39.828, lng: -98.579},
                zoom: 4,
            });
        }
    }

    function addMarker(location, map) {
        return new google.maps.Marker({
            position: location,
            map: map,
        });
    }

    function getLeads(callback) {

        let scraper = $('#scraper').val();
        let sortVal = $('#sort').val();
        let searchVal = $('#search').val();

        let filter = (searchVal) ? [{search: searchVal}] : [];

        let sort = [];
        let obj = {};

        obj[sortVal] = (sortVal === 'created') ? 'DESC' : 'ASC';
        sort.push(obj);

        if (scraper) {
            filter.push({url_id: scraper});
        }

        var data = {
            page: 1,
            len: 100,
            filter: filter,
            sort: sort
        };
        data = 'tableData=' + JSON.stringify(data);
        $.post('/app-data/scraper/leads', data).done(function (response) {
            try {
                response = JSON.parse(response);
                if (typeof response.total == 'undefined' || typeof response.data == 'undefined') throw "malformed";
            } catch (e) {
                console.log(response);
                alert('Invalid response data');
                return;
            }
            if (typeof callback == 'function') callback(response);
        }).fail(function(result) {
            console.log(result);
            alert('Invalid data request');
        });
    }

    function loadLeads(data) {
        let html = '';
        for (i = 0; i < data.data.length; i++) {
            if (typeof data.data[i].addresses[0] == 'undefined') continue;
            html += '<div class="mapview_lead_item p-2 d-flex border-bottom" data-markerid="' + i + '">';
            html += '<div class="me-2"><svg overflow="hidden" width="30" height="30" style="touch-action: none;"><defs></defs><image fill-opacity="0" stroke="none" stroke-opacity="0" stroke-width="1" stroke-linecap="butt" stroke-linejoin="miter" stroke-miterlimit="4" x="-15" y="-15" width="30" height="30" preserveAspectRatio="none" xlink:href="data:image/png;base64,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" transform="matrix(1.00000000,0.00000000,0.00000000,1.00000000,15.00000000,15.00000000)"></image></svg></div>';
            html += '<div>';
            html += data.data[i].addresses[0].street + ', ';
            html += data.data[i].addresses[0].city + ', ';
            html += data.data[i].addresses[0].state;
            html += '<br /><small>' + moneyFormatter.format(data.data[i].judgment_amount) + '</small>';
            html += '</div>';
            html += '</div>';
        }
        if (html === '') {
            html = '<div class="alert alert-info">No Leads</div>';
        }
        $('#mapview_leads_container').html(html);

        setLocationsArray(data.data, function () {
            initMap();
        });
    }

    function setLocationsArray(data, callback) {
        locations = [];
        for(i = 0; i< data.length; i++) {
            if (typeof data[i].addresses[0] == 'undefined') continue;
            data[i].lat = parseFloat(data[i].addresses[0].lat);
            data[i].lng = parseFloat(data[i].addresses[0].lon);
            locations.push(data[i]);
        }
        if (typeof callback == 'function') callback();
    }

    function refreshPage() {
        $('#mapview_leads_container').html('');
        getLeads(loadLeads);
    }
    window.refreshPage = refreshPage;


</script>
