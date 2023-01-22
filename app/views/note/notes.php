<?php

?>

<h1 class="page_header">Notes</h1>

<div class="d-grid gap-2 d-md-flex my-3 justify-content-md-end">
    <button role="button" class="btn btn-round btn-primary edit_trigger" type="button">Add Note</button>
</div>

<table class="e2-table" id="noteTable">
    <thead>
    <tr>
        <th>Created Date</th>
        <th>Created By</th>
        <th>Note</th>
        <th>Type</th>
        <th>Property</th>
        <th></th>
    </tr>
    </thead>
    <tbody></tbody>
</table>

<script>

    $(document).ready(function () {

        var noteTable = new tableData('#noteTable', {
            url: '/app-data/notes',
            sort: {created: 'DESC'},
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
                {col: 'property',
                    template: function (data) {
                        return '<a href="/property/' + data.property_id + '">' + data.property + '</a>';
                    }
                },
                {col: '', search: false, cellStyle: 'text-align:right;', sort: false,
                    template: function (data) {
                        let html = '<button role="button" class="btn btn-outline-primary btn-sm me-md-1 edit_trigger" data-note="' + data.note_id + '" type="button"><i class="fa fa-pencil"></i></button>';
                        html += '<button role="button" class="btn btn-outline-danger btn-sm me-md-1 confirm_trigger" data-note="' + data.note_id + '" data-message="Are you sure you want to delete this note??" data-url="/delete-note/' + data.note_id + '" type="button"><i class="fa fa-times"></i></button>';
                        return html;
                    }
                },
            ]
        });

        $(document).on('click', '.edit_trigger', function () {

            let note = $(this).data('note');
            let url = (note) ? '/edit-note/' + note : '/create-note';
            let modalTitle = (note) ? 'Edit Note' : 'Create Note';

            $.get(url).done(function (result) {
                $('#editModalLabel').text(modalTitle);
                $('#editModal .modal-body').html(result);
                $('#editModal').modal('show');
            });
        });

    });

</script>
