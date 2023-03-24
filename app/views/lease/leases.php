<?php



?>

<h1 class="page_header">Leases</h1>

<div class="d-grid gap-2 d-md-flex my-3 justify-content-md-end">
    <button role="button" class="btn btn-round btn-primary edit_trigger" data-type="lease" type="button">Add Lease</button>
</div>

<table class="e2-table" id="leaseTable">
    <thead>
    <tr>
        <th>Lease</th>
        <th>Property</th>
        <th>Unit</th>
        <th>Start Date</th>
        <th>End Date</th>
        <th>Rent</th>
        <th>Rent Freq</th>
        <th></th>
    </tr>
    </thead>
    <tbody></tbody>
</table>

<script>

    $(document).ready(function () {

        var table = new tableData('#leaseTable', {
            url: '/app-data/leases',
            sort: {end_date: 'DESC'},
            columns: [
                {col: '',
                     template: function (data) {
                        return '<a href="/lease/' + data.lease_id + '">view</a>';
                     }
                },
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

        $(document).on('click', '.edit_trigger', function () {

            let lease = $(this).data('lease');
            let url = (lease) ? '/edit-lease/' + lease : '/add-lease';
            let modalTitle = (lease) ? 'Edit Lease' : 'Create Lease';

            $.get(url).done(function (result) {
                $('#editModalLabel').text(modalTitle);
                $('#editModal .modal-body').html(result);
                $('#editModal').modal('show');
            });
        });

    });

</script>
