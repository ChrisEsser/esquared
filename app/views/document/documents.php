<?php


?>

<h1 class="page_header">Documents</h1>

<div class="d-grid gap-2 d-md-flex my-3 justify-content-md-end align-items-center">

    <button role="button" class="btn btn-round btn-primary edit_trigger" type="button">Add Document</button>

    <div class="form-check form-switch form-switch-lg ms-1">
        <input class="form-check-input" type="checkbox" id="toggle_view_all" checked>
        <label class="form-check-label" for="toggle_view_all">View All</label>
    </div>

</div>

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


<script>

    $(document).ready(function() {

        var table = new tableData('#documentTable', {
            url: '/app-data/documents',
            sort: {name: 'ASC'},
            filter: {property_id: 0},
            columns: [
                {col: 'name',
                    template: function (data) {
                        let url = '/file/proxy?file=';
                        if (data.property_id) url += '/properties/' + data.property_id + '/documents/' + data.name
                        else url += '/documents/' + data.user_id + '/' + data.name;
                        return '<a href="' + url + '">' + data.name + '</a>';
                    }
                },
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

        $(document).on('change', '#toggle_view_all', function() {
            const checked = $(this).prop("checked");
            if (checked) table.removeFilterAndReload('viewAll');
            else table.addFilterAdnReload({viewAll: true});
        });

        $(document).on('click', '.edit_trigger', function () {

            let document = $(this).data('document');

            let url = (document) ? '/edit-document/' + document : '/create-document';
            let modalTitle = (document) ? 'Edit Document' : 'Create Document';

            $.get(url).done(function(result) {
                $('#editModalLabel').text(modalTitle);
                $('#editModal .modal-body').html(result);
                $('#editModal').modal('show');
            });
        });

    });

</script>
