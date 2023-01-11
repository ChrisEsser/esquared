<?php

    /** @var User[] $users */
    $users = $this->getVar('users');

?>

<h1 class="page_header">Manage Users</h1>

<div class="d-grid gap-2 d-md-flex my-3 justify-content-md-end">
    <button role="button" class="btn btn-primary me-md-2 edit_trigger" type="button">Add User</button>
</div>

<?php if (count($users)) { ?>

    <table class="e2-table">
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
        <tbody>
            <?php foreach ($users as $user) { ?>
                <tr>
                    <td><?=$user->first_name?></td>
                    <td><?=$user->last_name?></td>
                    <td><?=$user->email?></td>
                    <td><?=($user->admin) ? 'Yes' : 'No'?></td>
                    <td><?=($user->getUnit()) ? '<a href="/property/' . $user->getUnit()->property_id . '" target="_blank">' . $user->getUnit()->getProperty()->name . ' | ' . $user->getUnit()->name . '</a>' : ''?></td>
                    <td style="text-align: right">
                        <button role="button" class="btn btn-primary btn-sm me-md-1 edit_trigger" data-user="<?=$user->user_id?>" type="button">Edit</button>
                        <button role="button" class="btn btn-danger btn-sm me-md-1" data-trigger="confirm" data-property="<?=$user->user_id?>" data-message="Are you sure you want to delete this user?" data-url="/delete-user/<?=$user->user_id?>" type="button">Delete</button>
                    </td>
                </tr>
            <?php } ?>
        </tbody>
    </table>

<?php } else { ?>

    <div class="alert alert-primary" role="alert">No Users</div>

<?php } ?>


<script>

    $(document).ready(function() {

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
