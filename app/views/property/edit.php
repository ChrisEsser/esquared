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
        <label for="type" class="form-label">Type</label>
        <select class="form-control" id="type" name="type" aria-describedby="typeHelp">
            <?php foreach ($property->typeStrings() as $key => $value) { ?>
                <option value="<?=$key?>" <?=($property->type == $key) ? 'selected' : ''?>><?=$value?></option>
            <?php } ?>
        </select>
    </div>

    <div class="mb-3">
        <label for="description" class="form-label">Description</label>
        <textarea class="form-control" id="description" name="description" aria-describedby="descriptionHelp"><?=$property->description?></textarea>
    </div>

    <div class="row">

        <div class="mb-3 col-md-6">
            <label for="purchase_price" class="form-label">Purchase Price</label>
            <div class="input-group">
                <span class="input-group-append">
                    <span class="input-group-text bg-light d-block">
                        <i class="fa fa-dollar"></i>
                    </span>
                </span>
                <input type="number" min="0" step=".01" class="form-control" id="purchase_price" name="purchase_price" aria-describedby="purchase_priceHelp" value="<?=$property->purchase_price?>" />
            </div>
        </div>

        <div class="mb-3 col-md-6">
            <label for="purchase_date" class="form-label">Purchase Date</label>
            <div class="input-group">
                <input type="text" class="form-control" id="purchase_date" name="purchase_date" aria-describedby="purchase_dateHelp" value="<?=date('m/d/Y', strtotime($property->purchase_date))?>" />
                <span class="input-group-append">
                    <span class="input-group-text bg-light d-block">
                        <i class="fa fa-calendar"></i>
                    </span>
                </span>
            </div>
        </div>

    </div>


    <div class="mb-3">
        <label for="image" class="form-label">Image</label>
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


