<?php

/** @var ScraperLeadAddress $address */
$address = $this->getVar('address');
$leadId = $this->getVar('leadId');

?>

<form id="addressForm">

    <input type="hidden" name="address" value="<?=$address->address_id?>" />
    <input type="hidden" name="lead" value="<?=$leadId?>" />

    <div class="mb-3">
        <label for="street" class="form-label">Street</label>
        <input type="text" class="form-control" id="street" name="street" aria-describedby="streetHelp" autocomplete="off" required value="<?= $address->street ?>"/>
    </div>

    <div class="mb-3">
        <label for="city" class="form-label">City</label>
        <input type="text" class="form-control" id="city" name="city" aria-describedby="cityHelp" autocomplete="off" value="<?= $address->city ?>"/>
    </div>

    <div class="row">

        <div class="mb-3 col-6">
            <label for="state" class="form-label">State</label>
            <input type="text" class="form-control" id="state" name="state" aria-describedby="stateHelp" autocomplete="off" value="<?= $address->state ?>"/>
        </div>

        <div class="mb-3 col-6">
            <label for="zip" class="form-label">Zip</label>
            <input type="text" class="form-control" id="zip" name="zip" aria-describedby="zipHelp" autocomplete="off" value="<?= $address->zip ?>"/>
        </div>

    </div>

</form>

<script src="https://maps.googleapis.com/maps/api/js?key=<?=$_ENV['GOOGLE_MAPS_API_KEY']?>&callback=initAutocomplete&libraries=places&v=weekly" defer></script>

<script>

    $('#button_save').click(function() {
        $.post('/lead/save-address', $('#addressForm').serialize()).done(function(result) {

            result = JSON.parse(result);
            if (typeof result.result == 'undefined') {
                alert('An unknown error occurred');
                return;
            }
            if (result.result == 'success') {
                location.reload();
            } else if (result.result == 'error') {
                let message = (typeof result.message != 'undefined')
                    ? result.message
                    : 'An error occurred saving the lead address';
                alert(message);
                return;
            }
        });
    });

    let autocomplete;
    let addressField;
    let cityField;
    let stateField;
    let zipField;
    // let address2Field;
    // let postalField;

    function initAutocomplete() {
        addressField = document.querySelector("#street");
        cityField = document.querySelector("#city");
        stateField = document.querySelector("#state");
        zipField = document.querySelector("#zip");
        var searchBox = new google.maps.places.SearchBox(addressField);
        // Create the autocomplete object, restricting the search predictions to
        // addresses in the US and Canada.
        autocomplete = new google.maps.places.Autocomplete(addressField, {
            componentRestrictions: {country: ["us", "ca"]},
            fields: ["address_components", "geometry"],
            types: ["address"],
        });
        addressField.focus();
        // When the user selects an address from the drop-down, populate the
        // address fields in the form.
        autocomplete.addListener("place_changed", fillInAddress);
    }

    function fillInAddress() {
        // Get the place details from the autocomplete object.
        const place = autocomplete.getPlace();

        let address = "";
        let zip = "";

        // Get each component of the address from the place details,
        // and then fill-in the corresponding field on the form.
        // place.address_components are google.maps.GeocoderAddressComponent objects
        // which are documented at http://goo.gle/3l5i5Mr
        if (place.address_components) {
            for (const component of place.address_components) {
                // @ts-ignore remove once typings fixed
                const componentType = component.types[0];

                switch (componentType) {
                    case "street_number": {
                        address = `${component.long_name} ${address}`;
                        break;
                    }
                    case "route": {
                        address += component.short_name;
                        break;
                    }
                    case "postal_code": {
                        zip = `${component.long_name}${zip}`;
                        break;
                    }
                    case "postal_code_suffix": {
                        zip = `${zip}-${component.long_name}`;
                        break;
                    }
                    case "locality":
                        document.querySelector("#city").value = component.long_name;
                        break;
                    case "administrative_area_level_1": {
                        document.querySelector("#state").value = component.short_name;
                        break;
                    }
                    case "country":
                        break;
                }
            }
        }

        addressField.value = address;
        zipField.value = zip;
    }

    window.initAutocomplete = initAutocomplete;

</script>