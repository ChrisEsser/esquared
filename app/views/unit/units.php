<?php


?>

<h1 class="page_header">Units</h1>

<div class="d-grid gap-2 d-md-flex my-3 justify-content-md-end">
    <button role="button" class="btn btn-primary me-md-2 edit_trigger" type="button" id="new_unit_button" style="display: none">Add Unit</button>
</div>

<table class="e2-table" id="unitTable">
    <thead>
    <tr>
        <th>Name</th>
        <th>Description</th>
        <th>Status</th>
        <th>Rent</th>
        <th>Property</th>
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
                {col: 'property',
                    template: function (data) {
                        return '<a href="/property/' + data.property_id + '">' + data.property + '</a>';
                    }
                },
                {col: '', cellStyle: 'text-align:right;', search: false, sort: false,
                    template: function (data) {
                        let html = '<button role="button" class="btn btn-primary btn-sm me-md-1 edit_trigger" data-unit="' + data.unit_id + '" type="button">Edit</button>';
                        html += '<button role="button" class="btn btn-danger btn-sm me-md-1 confirm_trigger" data-unit="' + data.unit_id + '" data-message="Are you sure you want to delete <strong>' + data.name + '</strong>?" data-url="/delete-unit/' + data.unit_id + '" type="button">Delete</button>';
                        return html;
                    }
                },
            ]
        });

        $(document).on('click', '.edit_trigger', function () {

            let unit = $(this).data('unit');
            let url = (unit) ? '/edit-unit/' + unit : '/create-unit/' + $('#property_trigger').val();
            let modalTitle = (unit) ? 'Edit Unit' : 'Create Unit';

            $.get(url).done(function (result) {
                $('#editModalLabel').text(modalTitle);
                $('#editModal .modal-body').html(result);
                $('#editModal').modal('show');
            });
        });

    });

</script>
