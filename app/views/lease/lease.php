<?php

/** @var Lease $lease */
$lease = $this->getVar('lease');

?>

<h1 class="page_header">Lease</h1>

<ul class="breadcrumb">
    <li class="breadcrumb-item"><a href="/properties">Properties</a></li>
    <li class="breadcrumb-item"><a href="/property/<?=$lease->getUnit()->property_id?>"><?=$lease->getUnit()->getProperty()->name?></a></li>
    <li class="breadcrumb-item"><a href="/unit/<?=$lease->unit_id?>"><?=$lease->getUnit()->name?></a></li>
    <li class="breadcrumb-item active">Lease</li>
</ul>

<div style="display: flex; justify-content: left; flex-wrap: wrap">

    <div style="display: flex; justify-content: left;">

        <div style="margin: 0 30px">
            <strong>Start Date:</strong>
            <br /><strong>End Date:</strong>
            <br /><strong>Document:</strong>
            <br /><strong>Rent:</strong>
            <br /><strong>Rent Frequency:</strong>
        </div>

        <div>
            <?=date('m/d/y', strtotime($lease->start_date))?>
            <br /><?=date('m/d/y', strtotime($lease->end_date))?>
            <?php
                $fileName = '';
                foreach (glob(ROOT . DS . 'app' . DS . 'files' . DS . 'leases' . DS . $lease->lease_id . DS . '*.*') as $file) {
                    $fileName = basename($file);
                    break;
                }
            ?>
            <br /><?=($fileName) ? '<a href="/file/proxy?file=leases/' . $lease->lease_id . '/' . $fileName . '">' . $fileName . '</a>' : ''?>
            <br />$<?=number_format($lease->rent, 2)?>
            <br /><?=$lease->rentFrequencyStrings()[intval($lease->rent_frequency)]?>
        </div>
    </div>

</div>

<hr />

<ul class="nav nav-tabs" id="leasesTabs" role="tablist">
    <li class="nav-item" role="presentation">
        <button class="nav-link active tabClick" id="users-tab" data-bs-toggle="tab" data-bs-target="#users" type="button" role="tab" aria-controls="users" aria-selected="true">Users</button>
    </li>
    <li class="nav-item" role="presentation">
        <button class="nav-link tabClick" id="payments-tab" data-bs-toggle="tab" data-bs-target="#payments" type="button" role="tab" aria-controls="payments" aria-selected="true">Rent History</button>
    </li>
</ul>

<div class="tab-content" id="tabContents">

    <div class="tab-pane fade show active" id="users" role="tabpanel" aria-labelledby="users-tab">

        <table class="e2-table" id="userTable">
            <thead>
            <tr>
                <th>First Name</th>
                <th>Last Name</th>
                <th>Email</th>
                <th></th>
            </tr>
            </thead>
            <tbody></tbody>
        </table>

    </div>

    <div class="tab-pane fade" id="payments" role="tabpanel" aria-labelledby="payments-tab">

        <table class="e2-table" id="paymentTable">
            <thead>
            <tr>
                <th>Date</th>
                <th>Payment By</th>
                <th>Amount</th>
                <th>Method</th>
                <th>Type</th>
                <th></th>
            </tr>
            </thead>
            <tbody></tbody>
        </table>

    </div>

</div>

<script>

    $(document).ready(function() {

        let lastTab = sessionStorage.getItem('lastLeaseTab');
        if (lastTab) $('#' + lastTab).tab('show');

        $('.tabClick').click(function () {
            sessionStorage.setItem('lastLeaseTab', $(this).attr('id'));
        });

        var userTable = new tableData('#userTable', {
            url: '/app-data/users',
            filter: {lease_id: '<?=$lease->lease_id?>'},
            sort: {end_date: 'DESC'},
            columns: [
                {col: 'first_name'},
                {col: 'last_name'},
                {col: 'email'},
                {col: '', cellStyle: 'text-align:right;', search: false, sort: false,
                    template: function (data) {
                        let html = '<button role="button" class="btn btn-outline-primary btn-sm me-md-1 edit_trigger" data-type="user" data-user="' + data.user_id + '" type="button"><i class="fa fa-pencil"></i></button>';
                        html += '<button role="button" class="btn btn-outline-danger btn-sm me-md-1 confirm_trigger" data-message="Are you sure you want to delete this user from the system completely?" data-url="/delete-user/' + data.user_id + '" type="button"><i class="fa fa-times"></i></button>';
                        return html;
                    }
                }
            ]
        });

        var paymentTable = new tableData('#paymentTable', {
            url: '/app-data/payments',
            filter: {lease_id: '<?=$lease->lease_id?>'},
            sort: {end_date: 'DESC'},
            columns: [
                {col: 'payment_date', format: 'date'},
                {col: 'payment_by'},
                {col: 'amount', format: 'usd'},
                {col: 'method'},
                {col: 'type'},
                {col: '', cellStyle: 'text-align:right;', search: false, sort: false,
                    template: function(data) {
                        let html = '<button role="button" class="btn btn-outline-primary btn-sm me-md-1 edit_trigger" data-type="payment" data-payment="' + data.payment_id + '" type="button"><i class="fa fa-pencil"></i></button>';
                        html += '<button role="button" class="btn btn-outline-danger btn-sm me-md-1 confirm_trigger" data-type="payment" data-payment="' + data.payment_id + '" data-message="Are you sure you want to delete this payment?" data-url="/delete-payment/' + data.payment_id + '" type="button"><i class="fa fa-times"></i></button>';
                        return html;
                    }
                },
            ]
        });

        $(document).on('click', '.edit_trigger', function () {

            let type = $(this).data('type');
            let user = $(this).data('user');
            let lease = $(this).data('lease');
            let payment = $(this).data('payment');

            let url = '/edit-' + type;

            if (type == 'user') url += '/' + user;
            else if (type == 'lease') url += '/' + lease;
            else if (type == 'payment') url += '/' + payment;
            else {
                alert('Invalid Request');
                return;
            }

            $.get(url).done(function(result) {
                $('#editModalLabel').text('Edit ' + type.charAt(0).toUpperCase() + type.slice(1));
                $('#editModal .modal-body').html(result);
                $('#editModal').modal('show');
            });

        });

    });

</script>
