<?php

/** @var \User $user */
$user = $this->getVar('user');
/** @var \Unit[] $units */
$units = $this->getVar('units');

?>

<form id="userForm">

    <input type="hidden" name="user" id="user" value="<?=$user->user_id?>" />

    <div class="mb-3">
        <label for="first_name" class="form-label">First Name</label>
        <input type="text" class="form-control" id="first_name" name="first_name" aria-describedby="first_nameHelp" value="<?=$user->first_name?>" />
    </div>

    <div class="mb-3">
        <label for="last_name" class="form-label">Last Name</label>
        <input type="text" class="form-control" id="last_name" name="last_name" aria-describedby="last_nameHelp" value="<?=$user->last_name?>" />
    </div>

    <div class="mb-3">
        <label for="email" class="form-label">Email</label>
        <input type="text" class="form-control" id="email" name="email" aria-describedby="emailHelp" value="<?=$user->email?>" />
    </div>

    <div class="mb-3">
        <label for="password" class="form-label">Password</label>
        <input type="password" class="form-control" id="password" name="password" aria-describedby="passwordHelp" value="" />
    </div>

    <div class="mb-3">
        <label for="password_confirm" class="form-label">Confirm Password</label>
        <input type="password" class="form-control" id="password_confirm" name="password_confirm" aria-describedby="password_confirmHelp" value="" />
    </div>

    <div class="mb-3">
        <input type="checkbox" name="admin" id="admin" value="1" <?=($user->admin) ? ' checked' : ''?> />&nbsp;
        <label for="admin">Admin</label>
    </div>

    <?php if (count($units)) { ?>

        <hr />

        <div class="mb-3">
            <label for="unit_id" class="form-label">Rental Unit</label>
            <select name="unit_id" id="unit_id" class="form-control" aria-describedby="unit_idHelp">
                <option value="0">- No Unit -</option>
                <?php foreach ($units as $unit) { ?>
                    <option value="<?=$unit->unit_id?>" <?=($user->unit_id == $unit->unit_id) ? 'selected' : ''?>><?=$unit->name?></option>
                <?php } ?>
            </select>
        </div>

    <?php } ?>

</form>

<script>
    $(document).ready(function() {

        $('#button_save').click(function() {
            $.post('/save-user', $('#userForm').serialize()).done(function(result) {

                console.log(result);

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
                        : 'An error occurred saving the user';
                    alert(message);
                    return;
                }
            });
        });

    });
</script>