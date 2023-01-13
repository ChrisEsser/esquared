<?php

/** @var Property[] $properties */
$properties = $this->getVar('properties');

?>

<h1 class="page_header">Units</h1>

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

<script>

    $(document).ready(function () {

        var table = new tableData('#unitTable', {
            url: '/app-data/units',
            columns: [
                {col: 'name'},
                {col: 'description'},
                {
                    col: 'status',
                    template: function (data) {
                        const statusCodes = ['Unknown', 'Occupied', 'Available', 'In Rehab'];
                        return statusCodes[data.status];
                    }
                },
                {col: 'rent', format: 'usd'},
                {
                    col: '',
                    cellStyle: 'text-align:right;',
                    template: function (data) {
                        let html = '<button role="button" class="btn btn-primary btn-sm me-md-1 edit_trigger" data-unit="' + data.unit_id + '" type="button">Edit</button>';
                        html += '<button role="button" class="btn btn-danger btn-sm me-md-1" data-trigger="confirm" data-unit="' + data.unit_id + '" data-message="Are you sure you want to delete <strong>' + data.name + '</strong>?" data-url="/delete-unit/' + data.unit_id + '" type="button">Delete</button>';
                        return html;
                    }
                },
            ]
        });

        $('#property_trigger').change(function() {
            const property = $(this).val();
            if (property == '') table.removeFilterAndReload('property');
            else table.addFilterAdnReload({property: property});
        });

    });

</script>
