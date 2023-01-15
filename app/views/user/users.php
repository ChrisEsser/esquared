<?php


?>

<h1 class="page_header">Users</h1>

<div class="d-grid gap-2 d-md-flex my-3 justify-content-md-end">
    <button role="button" class="btn btn-primary me-md-2 edit_trigger" type="button">Add User</button>
</div>

<table class="e2-table" id="userTable">
    <thead>
        <tr>
            <th>First Name</th>
            <th>Last Name</th>
            <th>Email</th>
            <th>Admin</th>
            <th>Rental Unit</th>
            <th></th>
        </tr>
    </thead>
    <tbody></tbody>
</table>

<script>

    $(document).ready(function() {

        var table = new tableData('#userTable', {
            url: '/app-data/users',
            columns: [
                {col: 'first_name'},
                {col: 'last_name'},
                {col: 'email'},
                {
                    col: 'admin',
                    template: function (data) {
                        return (data.admin == 1) ? 'Yes' : 'No';
                    }
                },
                {
                    col: 'unit',
                    template: function (data) {
                        return '<a href="/property/' + data.property_id + '" target="_blank" ">' + data.unit + '</a>';
                    }
                },
                {
                    col: '',
                    cellStyle: 'text-align:right;',
                    template: function(data) {
                        let html = '<button role="button" class="btn btn-primary btn-sm me-md-1 edit_trigger" data-user="' + data.user_id + '" type="button">Edit</button>';
                        html += '<button role="button" class="btn btn-danger btn-sm me-md-1 confirm_trigger" data-user="' + data.user_id + '" data-message="Are you sure you want to delete <strong>' + data.first_name + '</strong>?" data-url="/delete-user/' + data.user_id + '" type="button">Delete</button>';
                        return html;
                    }
                },
            ]
        });

        $(document).on('click', '.edit_trigger', function () {

            let user = $(this).data('user');
            let url = (user) ? '/edit-user/' + user : '/create-user';
            let modalTitle = (user) ? 'Edit User' : 'Create User';

            $.get(url).done(function(result) {
                $('#editModalLabel').text(modalTitle);
                $('#editModal .modal-body').html(result);
                $('#editModal').modal('show');
            });
        });

    });

</script>
