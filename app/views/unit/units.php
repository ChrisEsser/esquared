<?php

/** @var Unit[] $units */
$units = $this->getVar('units');
/** @var Property $property */
$property = $this->getVar('property');
/** @var Property[] $properties */
$properties = $this->getVar('properties');

?>

<h1 class="page_header">Units<?=(!empty($property->property_id)) ? '<small> - ' . $property->name . '</small>' : ''?></h1>

<div class="row g-3 align-items-center mb-3">
    <div class="col-auto">
        <label for="property_trigger" class="col-form-label" style="font-weight: 500">Property</label>
    </div>
    <div class="col-auto">
        <select class="form-control" id="property_trigger" style="min-width: 150px">
            <option value="">All</option>
            <?php if (count($properties)) { ?>
                <?php foreach ($properties as $prop) { ?>
                    <option value="<?=$prop->property_id?>"><?=$prop->name?></option>
                <?php } ?>
            <?php } ?>
        </select>
    </div>
</div>


<?php if (count($units)) { ?>

    <table class="e2-table">
        <thead>
        <tr>
            <th>Name</th>
            <th>Description</th>
            <th>Status</th>
            <th>Rent</th>
            <th></th>
        </tr>
        </thead>
        <tbody>
        <?php foreach ($units as $unit) { ?>
            <tr>
                <td><a href="/unit/<?=$unit->unit_id?>"><?=$unit->name?></a></td>
                <td><?=$unit->description?></td>
                <td><?=$unit->statusStrings()[$unit->status]?></td>
                <td>$<?=number_format($unit->rent, 2)?></td>
                <td style="text-align: right">
                    <button role="button" class="btn btn-primary btn-sm me-md-1 edit_trigger" data-unit="<?=$unit->unit_id?>" type="button">Edit</button>
                    <button role="button" class="btn btn-danger btn-sm me-md-1" data-trigger="confirm" data-unit="<?=$property->unit_id?>" data-message="Are you sure you want to delete <strong><?=$unit->name?></strong>?" data-url="/delete-unit/<?=$unit->unit_id?>" type="button">Delete</button>
                </td>
            </tr>
        <?php } ?>
        </tbody>
    </table>

<?php } else { ?>

    <div class="alert alert-primary" role="alert">No properties</div>

<?php } ?>