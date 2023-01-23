<?php

?>

<h1 class="page_header">Quarantined Addresses</h1>

<table class="e2-table" id="addressTable">
    <thead>
        <tr>
            <th>Street</th>
            <th>City</th>
            <th>State</th>
            <th>Zip</th>
            <th>Street View</th>
            <th></th>
        </tr>
    </thead>
    <tbody></tbody>
</table>

<script>

    $(document).ready(function() {

        var table = new tableData('#addressTable', {
            url: '/app-data/scraper/quarantine-addresses',
            sort: {city: 'ASC'},
            columns: [
                {col: 'street'},
                {col: 'city'},
                {col: 'state'},
                {col: 'zip'},
                {col: 'address',
                    template: function (data) {
                        let html = '';
                        if (data.lon !== '' && data.lat !== '') {
                            html += '<a href="javascript:void(0);" class="trigger_street_view" data-address="' + data.address_id + '">';
                            html += 'Show';
                            html += '</a>';
                        }
                        return html;
                    }
                },
                {col: '', cellStyle: 'text-align:right;', search: false, sort: false,
                    template: function(data) {
                        let html = '<button role="button" class="btn btn-outline-primary btn-sm me-md-1 edit_trigger" data-address="' + data.address_id + '" type="button"><i class="fa fa-pencil"></i></button>';
                        html += '<button role="button" class="btn btn-outline-danger btn-sm me-md-1 confirm_trigger" data-address="' + data.address_id + '" data-message="Are you sure you want to remove this address from quarantine?" data-url="/delete-address/quarantine/' + data.address_id + '" type="button"><i class="fa fa-times"></i></button>';
                        return html;
                    }
                },
            ]
        });

        $(document).on('click', '.trigger_street_view', function() {
            let address = $(this).data('address');
            $.get('/street-view/quarantine/' + address).done(function(result) {
                $('#viewModalLabel').text('Street View');
                $('#viewModal .modal-body').html(result);
                $('#viewModal').modal('show');
            });
        });

    });

</script>