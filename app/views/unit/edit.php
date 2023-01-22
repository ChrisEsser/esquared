<?php

/** @var Unit $unit */
$unit = $this->getVar('unit');
/** @var Property $property */
$property = $this->getVar('property');
/** @var Property[] $properties */
$properties = $this->getVar('properties');

?>

<form id="unitForm">

    <input type="hidden" name="unit" id="unit" value="<?=$unit->unit_id?>" />

    <?php if (!$property->property_id) { ?>
        <div class="mb-3">
            <label for="name" class="form-label">Property</label>
            <select name="property" id="property" class="form-control">
                <option value="">- Select -</option>
                <?php foreach ($properties as $prop) { ?>
                    <option value="<?=$prop->property_id?>" <?=($prop->property_id == $property->property_id) ? 'selected' : ''?>><?=$prop->name?></option>
                <?php } ?>
            </select>
        </div>
    <?php } else { ?>

        <input type="hidden" name="property" id="property" value="<?=$property->property_id?>" />

    <?php } ?>

    <div class="mb-3">
        <label for="name" class="form-label">Name</label>
        <input type="text" class="form-control" id="name" name="name" aria-describedby="nameHelp" value="<?=$unit->name?>" />
<!--            <div id="nameHelp" class="form-text"></div>-->
    </div>

    <div class="mb-3">
        <label for="description" class="form-label">Description</label>
        <textarea class="form-control" id="description" name="description" aria-describedby="descriptionHelp"><?=$unit->description?></textarea>
<!--            <div id="descriptionHelp" class="form-text"></div>-->
    </div>

    <div class="mb-3">
        <label for="description" class="form-label">Status</label>
        <select class="form-control" id="status" name="status" aria-describedby="statusHelp">
            <?php foreach ($unit->statusStrings() as $value => $string) { ?>
                <option value="<?=$value?>" <?=$unit->status == $value ? 'selected' : ''?>><?=$string?></option>
            <?php } ?>
        </select>
<!--            <div id="statusHelp" class="form-text"></div>-->
    </div>

    <div class="mb-3">
        <label for="rent" class="form-label">Rent</label>
        <input type="number" min="0" step=".01" class="form-control" id="rent" name="rent" aria-describedby="rentHelp" value="<?=$unit->rent?>" />
<!--            <div id="rentHelp" class="form-text"></div>-->
    </div>

    <div class="mb-3">
        <label for="rent_frequency" class="form-label">Rent Frequency</label>
        <select class="form-control" id="rent_frequency" name="rent_frequency" aria-describedby="rent_frequencyHelp">
            <?php foreach ($unit->rentFrequencyStrings() as $value => $string) { ?>
                <option value="<?=$value?>" <?=$unit->rent_frequency == $value ? 'selected' : ''?>><?=$string?></option>
            <?php } ?>
        </select>
<!--            <div id="statusHelp" class="form-text"></div>-->
    </div>

</form>

<script>
    $(document).ready(function() {

        $('#button_save').click(function() {
            $.post('/save-unit', $('#unitForm').serialize()).done(function(result) {

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
                        : 'An error occurred saving the unit';
                    alert(message);
                    return;
                }
            });
        });

    });
</script>
