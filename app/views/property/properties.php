<?php

?>

<h1 class="page_header">Properties</h1>

<div class="d-grid gap-2 d-md-flex my-3 justify-content-md-end">
    <button role="button" class="btn btn-primary me-md-2 edit_trigger" type="button">Add Property</button>
</div>

<table class="e2-table" id="propertyTable">
    <thead>
        <tr>
            <th>Name</th>
            <th>Description</th>
            <th></th>
        </tr>
    </thead>
    <tbody></tbody>
</table>

<script>

    $(document).ready(function () {

        var table = new tableData('#propertyTable', {
            url: '/app-data/properties',
            sort: {name: 'ASC'},
            columns: [
                {col: 'name',
                    template: function (data) {
                        return '<a href="/property/' + data.property_id + '">' + data.name + '</a>';
                    }
                },
                {col: 'description'},
                {col: '', cellStyle: 'text-align:right;', search: false, sort: false,
                    template: function(data) {
                        let html = '<button role="button" class="btn btn-primary btn-sm me-md-1 edit_trigger" data-property="' + data.property_id + '" type="button">Edit</button>';
                        html += '<button role="button" class="btn btn-danger btn-sm me-md-1 confirm_trigger" data-property="' + data.property_id + '" data-message="Are you sure you want to delete <strong>' + data.name + '</strong>?" data-url="/delete-property/' + data.property_id + '" type="button">Delete</button>';
                        return html;
                    }
                },
            ]
        });

        $(document).on('click', '.edit_trigger', function () {

            let property = $(this).data('property');
            let url = (property) ? '/edit-property/' + property : '/create-property';
            let modalTitle = (property) ? 'Edit Property' : 'Create Property';

            $.get(url).done(function (result) {
                $('#editModalLabel').text(modalTitle);
                $('#editModal .modal-body').html(result);
                $('#editModal').modal('show');
            });
        });

    });

</script>


