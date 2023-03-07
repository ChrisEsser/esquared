<?php

?>

<h1 class="page_header">Expenses</h1>

<div class="d-grid gap-2 d-md-flex my-3 justify-content-md-end">
    <button role="button" class="btn btn-round btn-primary edit_trigger" type="button">Add Expense</button>
</div>

<table class="e2-table" id="expenseTable">
    <thead>
    <tr>
        <th>Property</th>
        <th>Unit</th>
        <th>Amount</th>
        <th>Description</th>
        <th>Date</th>
        <th></th>
    </tr>
    </thead>
    <tbody></tbody>
</table>

<script>

    $(document).ready(function () {

        var table = new tableData('#expenseTable', {
            url: '/app-data/expenses',
            sort: {date: 'DESC'},
            columns: [
                {col: 'property_name',
                    template: function (data) {
                        return '<a href="/property/' + data.property_id + '">' + data.property_name + '</a>';
                    }
                },
                {col: 'unit_name',
                    template: function (data) {
                        return '<a href="/unit/' + data.unit_id + '">' + data.unit_name + '</a>';
                    }
                },
                {col: 'amount', format: 'usd'},
                {col: 'description'},
                {col: 'date', format: 'date'},
                {col: '', cellStyle: 'text-align:right;', search: false, sort: false,
                    template: function(data) {
                        let html = '<button role="button" class="btn btn-outline-primary btn-sm me-md-1 edit_trigger" data-expense="' + data.expense_id + '" type="button"><i class="fa fa-pencil"></i></button>';
                        html += '<button role="button" class="btn btn-outline-danger btn-sm me-md-1 confirm_trigger" data-expense="' + data.expense_id + '" data-message="Are you sure you want to delete this expense?" data-url="/delete-expnse/' + data.expense_id + '" type="button"><i class="fa fa-times"></i></button>';
                        return html;
                    }
                },
            ]
        });

        $(document).on('click', '.edit_trigger', function () {

            let expense = $(this).data('expense');
            let url = (expense) ? '/edit-expense/' + expense : '/add-expense';
            let modalTitle = (expense) ? 'Edit Expense' : 'Create Expense';

            $.get(url).done(function (result) {
                $('#editModalLabel').text(modalTitle);
                $('#editModal .modal-body').html(result);
                $('#editModal').modal('show');
            });
        });

    });

</script>
