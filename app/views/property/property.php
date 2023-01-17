<?php

/** @var \Property $property */
$property = $this->getVar('property');
$images = $this->getVar('images');

?>

<h1 class="page_header"><?=$property->name?></h1>

<div class="d-grid gap-2 d-md-flex my-3 justify-content-md-end">
    <button role="button" class="btn btn-round btn-primary edit_trigger" data-type="property" type="button">Edit Property</button>
    <button role="button" class="btn btn-round btn-info edit_trigger" data-type="unit" type="button">Add Unit</button>
    <button role="button" class="btn btn-round btn-info edit_trigger" data-type="document" type="button">Add Document</button>
    <button role="button" class="btn btn-round btn-info edit_trigger" data-type="note" type="button">Add Note</button>
    <button role="button" class="btn btn-round btn-info edit_trigger" data-type="payment" type="button">Add Rent Payment</button>
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

        <table class="e2-table" id="unitTable">
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Description</th>
                    <th>Status</th>
                    <th>Rent</th>
                    <th></th>
                </tr>
            </thead>
            <tbody></tbody>
        </table>

    </div>

    <div class="tab-pane fade" id="documents" role="tabpanel" aria-labelledby="documents-tab">

        <table class="e2-table" id="documentTable">
            <thead>
                <tr>
                    <th>Document</th>
                    <th>Uploaded By</th>
                    <th>Upload Date</th>
                    <th></th>
                </tr>
            </thead>
            <tbody></tbody>
        </table>

    </div>

    <div class="tab-pane fade" id="notes" role="tabpanel" aria-labelledby="notes-tab">

        <table class="e2-table" id="noteTable">
            <thead>
                <tr>
                    <th>Created Date</th>
                    <th>Created By</th>
                    <th>Note</th>
                    <th>Type</th>
                    <th></th>
                </tr>
            </thead>
            <tbody></tbody>
        </table>

    </div>

</div>

<script>

    $(document).ready(function() {

        let lastTab = sessionStorage.getItem('lastTab');
        if (lastTab) $('#'+lastTab).tab('show');

        $('.tabClick').click(function () {
            sessionStorage.setItem('lastTab', $(this).attr('id'));
        });

        var unitTable = new tableData('#unitTable', {
            url: '/app-data/units',
            sort: {name: 'ASC'},
            filter: {property_id: '<?=$property->property_id?>'},
            columns: [
                {col: 'name',
                    template: function (data) {
                        return '<a href="/unit/' + data.unit_id + '">' + data.name + '</a>';
                    }
                },
                {col: 'description'},
                {col: 'status',
                    template: function (data) {
                        const statusCodes = ['Unknown', 'Occupied', 'Available', 'In Rehab'];
                        return statusCodes[data.status];
                    }
                },
                {col: 'rent', format: 'usd'},
                {col: '', cellStyle: 'text-align:right;', search: false, sort: false,
                    template: function (data) {
                        let html = '<button role="button" class="btn btn-outline-primary btn-sm me-md-1 edit_trigger" data-type="unit" data-unit="' + data.unit_id + '" type="button"><i class="fa fa-pencil"></i></button>';
                        html += '<button role="button" class="btn btn-outline-danger btn-sm me-md-1 confirm_trigger" data-unit="' + data.unit_id + '" data-message="Are you sure you want to delete <strong>' + data.name + '</strong>?" data-url="/delete-unit/' + data.unit_id + '" type="button"><i class="fa fa-times"></i></button>';
                        return html;
                    }
                },
            ]
        });

        var docTable = new tableData('#documentTable', {
            url: '/app-data/documents',
            sort: {name: 'ASC'},
            filter: {property_id: '<?=$property->property_id?>'},
            columns: [
                {col: 'name'},
                {col: 'user'},
                {col: 'created', format: 'datetime'},
                {col: '', search: false, cellStyle: 'text-align:right;', sort: false,
                    template: function (data) {
                        let html = '<button role="button" class="btn btn-outline-danger btn-sm me-md-1 confirm_trigger" data-document="' + data.document_id + '" data-message="Are you sure you want to delete <strong>' + data.name + '</strong>?" data-url="/delete-document/' + data.document_id + '" type="button"><i class="fa fa-times"></i></button>';
                        return html;
                    }
                },
            ]
        });

        var noteTable = new tableData('#noteTable', {
            url: '/app-data/notes',
            sort: {created: 'DESC'},
            filter: {property_id: '<?=$property->property_id?>'},
            columns: [
                {col: 'created', format: 'datetime'},
                {col: 'user'},
                {col: 'note'},
                {col: 'type',
                    template: function (data) {
                        const typeCodes = ['Standard Note', 'To Do'];
                        return typeCodes[data.type];
                    }
                },
                {col: '', search: false, cellStyle: 'text-align:right;', sort: false,
                    template: function (data) {
                        let html = '<button role="button" class="btn btn-outline-primary btn-sm me-md-1 edit_trigger" data-type="note" data-note="' + data.note_id + '" type="button"><i class="fa fa-pencil"></i></button>';
                        html += '<button role="button" class="btn btn-outline-danger btn-sm me-md-1 confirm_trigger" data-note="' + data.note_id + '" data-message="Are you sure you want to delete this note??" data-url="/delete-note/' + data.note_id + '" type="button"><i class="fa fa-times"></i></button>';
                        return html;
                    }
                },
            ]
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

    });

</script>