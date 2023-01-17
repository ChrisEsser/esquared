<?php

?>

<h1 class="page_header">Payments</h1>

<div class="d-grid gap-2 d-md-flex my-3 justify-content-md-end">
    <button role="button" class="btn btn-round btn-primary edit_trigger" type="button">Add Payment</button>
</div>

<table class="e2-table" id="paymentTable">
    <thead>
    <tr>
        <th>Unit</th>
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

<script>

    $(document).ready(function () {

        var table = new tableData('#paymentTable', {
            url: '/app-data/payments',
            sort: {payment_date: 'DESC'},
            columns: [
                {col: 'unit_name',
                    template: function (data) {
                        return '<a href="/unit/' + data.unit_id + '">' + data.unit_name + ' | ' + data.property_name + '</a>';
                    }
                },
                {col: 'payment_date', format: 'datetime'},
                {col: 'payment_by'},
                {col: 'amount', format: 'usd'},
                {col: 'method'},
                {col: 'type'},
                {col: '', cellStyle: 'text-align:right;', search: false, sort: false,
                    template: function(data) {
                        let html = '<button role="button" class="btn btn-outline-primary btn-sm me-md-1 edit_trigger" data-payment="' + data.payment_id + '" type="button"><i class="fa fa-pencil"></i></button>';
                        html += '<button role="button" class="btn btn-outline-danger btn-sm me-md-1 confirm_trigger" data-payment="' + data.payment_id + '" data-message="Are you sure you want to delete this payment?" data-url="/delete-payment/' + data.payment_id + '" type="button"><i class="fa fa-times"></i></button>';
                        return html;
                    }
                },
            ]
        });

        $(document).on('click', '.edit_trigger', function () {

            let payment = $(this).data('payment');
            let url = (payment) ? '/edit-payment/' + payment : '/create-payment';
            let modalTitle = (payment) ? 'Edit Payment' : 'Create Payment';

            $.get(url).done(function (result) {
                $('#editModalLabel').text(modalTitle);
                $('#editModal .modal-body').html(result);
                $('#editModal').modal('show');
            });
        });

    });

</script>
