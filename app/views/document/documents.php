<?php


?>

<h1 class="page_header">Documents</h1>

<div class="d-grid gap-2 d-md-flex my-3 justify-content-md-end">
    <button role="button" class="btn btn-primary me-md-2 edit_trigger" type="button">Add Document</button>
</div>

<div class="mb-3">
    <a href="/documents?mydocs">My Documents</a> | <a href="/documents">All Documents</a>
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
            columns: [
                {col: 'name'},
                {col: 'user'},
                {col: 'created', format: 'datetime'},
                {col: '', search: false, cellStyle: 'text-align:right;', sort: false,
                    template: function (data) {
                        let html = '<button role="button" class="btn btn-danger btn-sm me-md-1 confirm_trigger" data-document="' + data.document_id + '" data-message="Are you sure you want to delete <strong>' + data.name + '</strong>?" data-url="/delete-document/' + data.document_id + '" type="button">Delete</button>';
                        return html;
                    }
                },
            ]
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
