<?php

/** @var \Property $property */
$property = $this->getVar('property');
$images = $this->getVar('images');

?>

<h1 class="page_header"><?=$property->name?></h1>

<div class="d-grid gap-2 d-md-flex my-3 justify-content-md-end">
    <button role="button" class="btn btn-primary me-md-1 edit_trigger" data-type="property" type="button">Edit Property</button>
    <button role="button" class="btn btn-secondary me-md-1 edit_trigger" data-type="unit" type="button">Add Unit</button>
    <button role="button" class="btn btn-secondary edit_trigger" data-type="document" type="button">Add Document</button>
    <button role="button" class="btn btn-secondary edit_trigger" data-type="note" type="button">Add Note</button>
    <button role="button" class="btn btn-secondary edit_trigger" data-type="payment" type="button">Add Rent Payment</button>
</div>

<div style="display: flex; justify-content: left; flex-wrap: wrap">

    <?php if (!empty($images)) { ?>

        <img src="/file/proxy?file=properties/<?=$property->property_id?>/images/<?=$images[0]?>" class="property_image mb-2" />

        <?php if (count($images) > 1) { ?>
            <?php for($i = 1; $i < count($images); $i++) { ?>
                <!-- show other images somehow -->
            <?php } ?>
        <?php } ?>

    <?php } ?>

    <div style="display: flex; justify-content: left;">

        <div style="margin: 0 30px">
            <strong>Name:</strong>
            <br /><strong>Description:</strong>
            <br /><strong>Purchase Price:</strong>
            <br /><strong>Purchase Date:</strong>
        </div>

        <div>
            <?=$property->name?>
            <br /><?=$property->description?>
            <br />$<?=number_format($property->purchase_price, 2)?>
            <br /><?=date('m/d/Y', strtotime($property->purchase_date))?>
        </div>
    </div>

</div>

<hr />

<ul class="nav nav-tabs" id="propertyTabs" role="tablist">
    <li class="nav-item" role="presentation">
        <button class="nav-link active tabClick" id="units-tab" data-bs-toggle="tab" data-bs-target="#units" type="button" role="tab" aria-controls="units" aria-selected="true">Units</button>
    </li>
    <li class="nav-item" role="presentation">
        <button class="nav-link tabClick" id="documents-tab" data-bs-toggle="tab" data-bs-target="#documents" type="button" role="tab" aria-controls="documents" aria-selected="false">Documents</button>
    </li>
    <li class="nav-item" role="presentation">
        <button class="nav-link tabClick" id="notes-tab" data-bs-toggle="tab" data-bs-target="#notes" type="button" role="tab" aria-controls="notes" aria-selected="false">Notes</button>
    </li>
</ul>

<div class="tab-content" id="tabContents">

    <div class="tab-pane fade show active" id="units" role="tabpanel" aria-labelledby="units-tab">

        <?php if (count($property->getUnit())) { ?>

            <table class="e2-table">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Description</th>
                        <th>Status</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($property->getUnit() as $unit) { ?>
                        <tr>
                            <td><a href="javascript:void(0);" class="view_unit_trigger" data-unit="<?=$unit->unit_id?>" data-name="<?=$unit->name?>"><?=$unit->name?></a></td>
                            <td><?=$unit->description?></td>
                            <td><?=$unit->statusStrings()[intval($unit->status)]?></td>
                            <td style="text-align: right">
                                <button role="button" class="btn btn-primary btn-sm me-md-1 edit_trigger" data-type="unit" data-unit="<?=$unit->unit_id?>" type="button">Edit</button>
                                <button role="button" class="btn btn-danger btn-sm me-md-1" data-trigger="confirm" data-message="Are you sure you want to delete <strong><?=$unit->name?></strong>?" data-url="/delete-unit/<?=$unit->unit_id?>" type="button">Delete</button>
                            </td>
                        </tr>
                    <?php } ?>
                </tbody>
            </table>

        <?php } else { ?>

            <div class="alert alert-primary" role="alert">No units</div>

        <?php } ?>

    </div>

    <div class="tab-pane fade" id="documents" role="tabpanel" aria-labelledby="documents-tab">

        <?php if (count($property->getDocument())) { ?>

            <table class="e2-table">
                <thead>
                    <tr>
                        <th>Document</th>
                        <th>Note</th>
                        <th>Type</th>
                        <th>Uploaded By</th>
                        <th>Upload Date</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($property->getDocument() as $document) { ?>
                        <?php
                        $user = $document->getUser();
                        $userName = $user->first_name . ' ' . $user->last_name;
                        ?>
                        <tr>
                            <td><a href="/file/proxy?file=properties/<?=$property->property_id?>/documents/<?=$document->name?>"><?=$document->name?></a></td>
                            <td><?=$document->description?></td>
                            <td><?=$document->typeStrings()[$document->type]?></td>
                            <td><?=$userName?></td>
                            <td><?=date('m/d/y g:ia', strtotime($document->created))?></td>
                            <td style="text-align: right">
                                <button role="button" class="btn btn-danger btn-sm me-md-1" data-trigger="confirm" data-message="Are you sure you want to delete <strong><?=$document->name?></strong>?" data-url="/delete-document/<?=$document->document_id?>" type="button">Delete</button>
                            </td>
                        </tr>
                    <?php } ?>
                </tbody>
            </table>

        <?php } else { ?>

            <div class="alert alert-primary" role="alert">No documents for this property</div>

        <?php } ?>

    </div>

    <div class="tab-pane fade" id="notes" role="tabpanel" aria-labelledby="notes-tab">

        <?php if (count($property->getNote())) { ?>

            <table class="e2-table">
                <thead>
                    <tr>
                        <th>Created Date</th>
                        <th>Created By</th>
                        <th>Note</th>
                        <th>Type</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($property->getNote() as $note) { ?>
                        <?php
                        $user = $note->getUser();
                        $userName = $user->first_name . ' ' . $user->last_name;

                        $class = '';
                        if ($note->status == 1) $class = 'table-info';
                        else if ($note->status == 2) $class = 'table-success';
                        ?>
                        <tr class="<?=$class?>">
                            <td><?=date('m/d/Y g:ia', strtotime($note->created))?></td>
                            <td><?=$userName?></td>
                            <td><?=$note->note?></td>
                            <td><?=$note->typeStrings()[$note->type]?></td>
                            <td style="text-align: right">
                                <button role="button" class="btn btn-primary btn-sm me-md-1 edit_trigger" data-type="note" data-note="<?=$note->note_id?>" type="button">Edit</button>
                                <button role="button" class="btn btn-danger btn-sm me-md-1" data-trigger="confirm" data-message="Are you sure you want to delete this note?" data-url="/delete-note/<?=$note->note_id?>" type="button">Delete</button>
                            </td>
                        </tr>
                    <?php } ?>
                </tbody>
            </table>

        <?php } else { ?>

            <div class="alert alert-primary" role="alert">No notes for this property</div>

        <?php } ?>

    </div>

</div>

<script>

    $(document).ready(function() {

        let lastTab = sessionStorage.getItem('lastTab');
        if (lastTab) $('#'+lastTab).tab('show');

        $('.tabClick').click(function () {
            sessionStorage.setItem('lastTab', $(this).attr('id'));
        });

        $(document).on('click', '.edit_trigger', function () {

            let type = $(this).data('type');
            let unit = $(this).data('unit');
            let note = $(this).data('note');
            let payment = $(this).data('payment');

            let url = '/edit-' + type;

            if (type == 'property') url += '/<?=$property->property_id?>';
            else if (type == 'document') url = '/property/<?=$property->property_id?>/add-document';
            else if (type == 'unit' && typeof unit != 'undefined') url += '/' + unit;
            else if (type == 'unit' && typeof unit == 'undefined') url = '/create-unit/<?=$property->property_id?>';
            else if (type == 'note' && typeof note != 'undefined') url += '/' + note;
            else if (type == 'note' && typeof note == 'undefined') url = '/create-note/<?=$property->property_id?>';
            else if (type == 'payment' && typeof payment == 'undefined') url = '/property/<?=$property->property_id?>/add-payment/';
            else if (type == 'payment' && typeof payment != 'undefined') url = '/property/edit-payment/';
            else {
                alert('Invalid Request');
                return;
            }

            $.get(url).done(function(result) {
                $('#editModalLabel').text('Edit ' + type.charAt(0).toUpperCase() + type.slice(1));
                $('#editModal .modal-body').html(result);
                $('#editModal').modal('show');
            });

        });

        $(document).on('click', '.view_unit_trigger', function () {

            let url = '/unit/' +  $(this).data('unit');
            let unitName = $(this).data('name');

            $.get(url).done(function(result) {
                 $('#viewModalLabel').text(unitName);
                 $('#viewModal .modal-body').html(result);
                 $('#viewModal').modal('show');
            });

        });

    });

</script>