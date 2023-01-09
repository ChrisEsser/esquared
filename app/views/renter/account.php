<?php

/** @var \User $user */
$user = $this->getVar('user');
$missing = $this->getVar('missing');

?>

<h1 class="page_header">Welcome Back, <?= ucwords($user->first_name) ?></h1>

<h4>Your account details</h4>


<div class="mt-5" style="max-width: 600px">

    <form method="post" action="/account/save">

        <div class="row mb-3 align-items-center">
            <div class="col-md-3">
                <label for="first_name" class="col-form-label">First Name</label>
            </div>
            <div class="col-md-9">
                <input type="text" class="form-control col-md-9 <?=(in_array('first_name', $missing)) ? 'is-invalid' : ''?>" id="first_name" name="first_name" aria-describedby="first_nameHelp" value="<?= $user->first_name ?>" />
            </div>
        </div>

        <div class="row mb-3 align-items-center">
            <div class="col-md-3">
                <label for="last_name" class="col-form-label">Last Name</label>
            </div>
            <div class="col-md-9">
                <input type="text" class="form-control col-md-9 <?=(in_array('last_name', $missing)) ? 'is-invalid' : ''?>" id="last_name" name="last_name" aria-describedby="last_nameHelp" value="<?= $user->last_name ?>" />
            </div>
        </div>

        <div class="row mb-3 align-items-center">
            <div class="col-md-3">
                <label for="email" class="col-form-label">Email</label>
            </div>
            <div class="col-md-9">
                <input type="text" class="form-control col-md-9 <?=(in_array('email', $missing)) ? 'is-invalid' : ''?>" id="email" name="email" aria-describedby="emailHelp" value="<?= $user->email ?>"  />
            </div>
        </div>

        <p class="col-md-9 offset-md-3"><a href="javascript:void(0);" data-bs-toggle="modal" data-bs-target="#passwordModal"><i class="fa fa-lock"></i>&nbsp;Change Password</a></p>

        <button type="submit" class="btn btn-primary">Save Changes</button>

    </form>

</div>

<div class="modal fade" id="passwordModal" tabindex="-1" aria-labelledby="passwordModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h1 class="modal-title fs-5" id="passwordModalLabel">Update Your Password</h1>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="passwordForm">
                    <div class="mb-3">
                        <label for="password" class="col-form-label">New Password</label>
                        <input type="password" class="form-control col-md-9" id="password" name="password" aria-describedby="passwordHelp" />
                    </div>
                    <div class="mb-3">
                        <label for="password_confirm" class="col-form-label">Confirm New Password</label>
                        <input type="password" class="form-control col-md-9" id="password_confirm" name="password_confirm" aria-describedby="password_confirmHelp" />
                    </div>
                </form>
                <input type="checkbox" onclick="togglePasswordShow()">&nbsp;Show Password
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-primary" id="save_password">Save</button>
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
            </div>
        </div>
    </div>
</div>

<script>

    $(document).ready(function () {

        $(document).on('click', '#save_password', function () {

            $.post('/account/save-password', $('#passwordForm').serialize()).done(function(result) {
                result = JSON.parse(result);
                if (typeof result.result == 'undefined') {
                    alert('An unknown error occurred');
                    return;
                }
                if (result.result == 'success') {
                    location.reload();
                } else if (result.result == 'error') {
                    let message = (typeof result.message != 'undefined')
                        ? result.message
                        : 'An error occurred saving the unit';
                    alert(message);
                    return;
                }
            });
        });

    });

    function togglePasswordShow() {
        var x = document.getElementById('password');
        var y = document.getElementById('password_confirm');
        if (x.type === 'password') {
            x.type = 'text';
            y.type = 'text';
        } else {
            x.type = 'password';
            y.type = 'password';
        }
    }

</script>