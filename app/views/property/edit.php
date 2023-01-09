<?php

/** @var \Property $property */
$property = $this->getVar('property');

?>

<form id="propertyForm">

    <input type="hidden" name="property" id="property" value="<?=$property->property_id?>" />

    <div class="mb-3">
        <label for="name" class="form-label">Name</label>
        <input type="text" class="form-control" id="name" name="name" aria-describedby="nameHelp" value="<?=$property->name?>" />
<!--            <div id="nameHelp" class="form-text"></div>-->
    </div>

    <div class="mb-3">
        <label for="description" class="form-label">Description</label>
        <textarea class="form-control" id="description" name="description" aria-describedby="descriptionHelp"><?=$property->description?></textarea>
    </div>

    <div class="mb-3">
        <label for="purchase_price" class="form-label">Purchase Price</label>
        <input type="number" min="0" step=".01" class="form-control" id="purchase_price" name="purchase_price" aria-describedby="purchase_priceHelp" value="<?=$property->purchase_price?>" />
    </div>

    <div class="mb-3">
        <label for="purchase_date" class="form-label">Purchase Date</label>
        <input type="text" class="form-control" id="purchase_date" name="purchase_date" aria-describedby="purchase_dateHelp" value="<?=date('m/d/Y', strtotime($property->purchase_date))?>" />
    </div>

    <div class="mb-3">
        <label for="image" class="form-label">Image(s)</label>
        <div id="image" name="filepond"></div>
    </div>

</form>

<script>
    $(document).ready(function() {

        let imagePond = createPond('#image');

        $('#button_save').click(function() {
            $.post('/save-property', $('#propertyForm').serialize()).done(function(result) {

                console.log(result);

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
                        : 'An error occurred saving the property';
                    alert(message);
                    return;
                }
            });
        });

    });
</script>


