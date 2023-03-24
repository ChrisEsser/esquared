<?php

/** @var Unit $unit */
$unit = $this->getVar('unit');

?>

<h1 class="page_header"><?=$unit->name?></h1>

<ul class="breadcrumb">
    <li class="breadcrumb-item"><a href="/properties">Properties</a></li>
    <li class="breadcrumb-item"><a href="/property/<?=$unit->getProperty()->property_id?>"><?=$unit->getProperty()->name?></a></li>
    <li class="breadcrumb-item active"><?=$unit->name?></li>
</ul>

<div style="display: flex; justify-content: left; flex-wrap: wrap">

    <div style="display: flex; justify-content: left;">

        <div style="margin: 0 30px">
            <strong>Type:</strong>
            <br /><strong>Description:</strong>
            <br /><strong>Status:</strong>
            <br /><strong>Rent:</strong>
            <br /><strong>Rent Frequency:</strong>
        </div>

        <div>
            <?=$unit->typeStrings()[$unit->type]?>
            <br /><?=$unit->description?>
            <br /><?=$unit->statusStrings()[$unit->status]?>
            <br />$<?=number_format($unit->rent, 2)?>
            <br /><?=$unit->rentFrequencyStrings()[intval($unit->rent_frequency)]?>
        </div>
    </div>

</div>

<hr />

<ul class="nav nav-tabs" id="unitTabs" role="tablist">
    <li class="nav-item" role="presentation">
        <button class="nav-link active tabClick" id="rent-tab" data-bs-toggle="tab" data-bs-target="#rent" type="button" role="tab" aria-controls="rent" aria-selected="true">Rent History</button>
    </li>
    <li class="nav-item" role="presentation">
        <button class="nav-link tabClick" id="leases-tab" data-bs-toggle="tab" data-bs-target="#leases" type="button" role="tab" aria-controls="leases" aria-selected="false">Leases</button>
    </li>
    <li class="nav-item" role="presentation">
        <button class="nav-link tabClick" id="expenses-tab" data-bs-toggle="tab" data-bs-target="#expenses" type="button" role="tab" aria-controls="expenses" aria-selected="true">Expenses</button>
    </li>
</ul>

<div class="tab-content" id="tabContents">

    <div class="tab-pane fade show active" id="rent" role="tabpanel" aria-labelledby="rent-tab">

        <table class="e2-table" id="paymentTable">
            <thead>
            <tr>
                <th>Entered By</th>
                <th>Date</th>
                <th>Amount</th>
                <th>Method</th>
                <th>Type</th>
                <th>Conf#</th>
            </tr>
            </thead>
            <tbody></tbody>
        </table>

    </div>

    <div class="tab-pane fade" id="leases" role="tabpanel" aria-labelledby="leasees-tab">

        <table class="e2-table" id="leaseTable">
            <thead>
            <tr>
                <th>Lease</th>
                <th>Start Date</th>
                <th>End Date</th>
                <th>Rent</th>
                <th>Rent Freq</th>
                <th></th>
            </tr>
            </thead>
            <tbody></tbody>
        </table>

    </div>

    <div class="tab-pane fade" id="expenses" role="tabpanel" aria-labelledby="expenses-tab">

        <table class="e2-table" id="expenseTable">
            <thead>
            <tr>
                <th>Amount</th>
                <th>Description</th>
                <th>Date</th>
                <th></th>
            </tr>
            </thead>
            <tbody></tbody>
        </table>

    </div>

</div>

<script>

    $(document).ready(function() {

        let lastTab = sessionStorage.getItem('lastUnitTab');
        if (lastTab) $('#' + lastTab).tab('show');

        $('.tabClick').click(function () {
            sessionStorage.setItem('lastUnitTab', $(this).attr('id'));
        });

        var paymentTable = new tableData('#paymentTable', {
            url: '/app-data/payments',
            filter: {unit_id: '<?=$unit->unit_id?>'},
            sort: {end_date: 'DESC'},
            columns: [
                {col: 'payment_date', format: 'datetime'},
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

        var leaseTable = new tableData('#leaseTable', {
            url: '/app-data/leases',
            filter: {unit_id: '<?=$unit->unit_id?>'},
            sort: {end_date: 'DESC'},
            columns: [
                {col: '',
                    template: function (data) {
                        return '<a href="/lease/' + data.lease_id + '">view</a>';
                    }
                },
                {col: 'start_date', format: 'date'},
                {col: 'end_date', format: 'date'},
                {col: 'rent', format: 'usd'},
                {col: 'rent_frequency'},
                {col: '', cellStyle: 'text-align:right;', search: false, sort: false,
                    template: function (data) {
                        let html = '<button role="button" class="btn btn-outline-primary btn-sm me-md-1 edit_trigger" data-type="lease" data-lease="' + data.lease_id + '" type="button"><i class="fa fa-pencil"></i></button>';
                        html += '<button role="button" class="btn btn-outline-danger btn-sm me-md-1 confirm_trigger" data-message="Are you sure you want to delete this lease?" data-url="/delete-lease/' + data.lease_id + '" type="button"><i class="fa fa-times"></i></button>';
                        return html;
                    }
                },
            ]
        });

        var expenseTable = new tableData('#expenseTable', {
            url: '/app-data/expenses',
            filter: {unit_id: '<?=$unit->unit_id?>'},
            sort: {date: 'DESC'},
            columns: [
                {col: 'amount', format: 'usd'},
                {col: 'description'},
                {col: 'date', format: 'date'},
                {col: '', cellStyle: 'text-align:right;', search: false, sort: false,
                    template: function(data) {
                        let html = '<button role="button" class="btn btn-outline-primary btn-sm me-md-1 edit_trigger" data-type="expense" data-expense="' + data.expense_id + '" type="button"><i class="fa fa-pencil"></i></button>';
                        html += '<button role="button" class="btn btn-outline-danger btn-sm me-md-1 confirm_trigger" data-expense="' + data.expense_id + '" data-message="Are you sure you want to delete this expense?" data-url="/delete-expnse/' + data.expense_id + '" type="button"><i class="fa fa-times"></i></button>';
                        return html;
                    }
                },
            ]
        });

        $(document).on('click', '.edit_trigger', function () {

            let type = $(this).data('type');
            let lease = $(this).data('lease');
            let expense = $(this).data('expense');
            let payment = $(this).data('payment');

            let url = '';

            if (type === 'lease' && typeof lease != 'undefined') url = '/edit-lease/' + lease;
            else if (type === 'lease' && typeof lease == 'undefined') url = '/add-lease/<?=$unit->property_id?>/<?=$unit->unit_id?>';
            else if (type === 'expense' && typeof expense == 'undefined') url = '/add-expense/<?=$unit->property_id?>/<?=$unit->unit_id?>';
            else if (type === 'expense' && typeof expense != 'undefined') url = '/edit-expense/' + expense;
            else if (type === 'payment' && typeof payment == 'undefined') url = '/add-payment/<?=$unit->property_id?>/<?=$unit->unit_id?>';
            else if (type === 'payment' && typeof payment != 'undefined') url = '/edit-payment/' + payment;

            $.get(url).done(function (result) {
                $('#editModalLabel').text('Edit ' + type.charAt(0).toUpperCase() + type.slice(1));
                $('#editModal .modal-body').html(result);
                $('#editModal').modal('show');
            });
        });

    });

</script>
