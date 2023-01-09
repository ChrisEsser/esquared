<?php


?>


<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="description" content="">
    <meta name="author" content="">
    <link rel="icon" href="/docs/4.0/assets/img/favicons/favicon.ico">

    <title>Reset Password | E Squared Holdings</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css"/>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.5.0/font/bootstrap-icons.css"/>

    <?= HTML::displayHead() ?>

</head>

<body>

<div class="container">

    <?= Html::displayAlerts() ?>

    <div class="row">
        <div class="col-sm-9 col-md-7 col-lg-5 mx-auto">
            <div class="card border-0 shadow rounded-3 my-5">
                <div class="card-body p-4 p-sm-5">
                    <h5 class="card-title text-center mb-5 fw-light fs-5">Reset Password</h5>
                    <form class="mb-0" action="/login/process-password-recovery" method="POST">
                        <div class="form-floating mb-3">
                            <input type="password" class="form-control" id="password" name="password" >
                            <label for="password">New Password</label>
                        </div>
                        <div class="form-floating mb-3">
                            <input type="password" class="form-control" id="password_confirm" name="password_confirm" >
                            <label for="password_confirm">Confirm Password</label>
                        </div>
                        <input type="checkbox" onclick="togglePasswordShow()">&nbsp;Show Password
                        <div class="d-grid mt-3">
                            <button class="btn btn-primary btn-login text-uppercase fw-bold" type="submit">Save New Password</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
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

</body>
</html>
