<?php

/** @var \ScraperUrl $scraperUrl */
$url = $this->getVar('url');
$viewAll = $this->getVar('viewAll');

?>

<h1 class="page_header">Leads<?=($url->name) ? ' <small>- <a href="' . $url->url . '" target="_blank">' . $url->name . '</a></small>' : ''?></h1>

<table class="e2-table" id="leadTable">
    <thead>
        <tr>
            <th>Active</th>
            <th>lead URL</th>
            <th>Address</th>
            <th>Judgment</th>
            <th>First Seen</th>
            <?=($viewAll) ? '<th>Scraper</th>' : ''?>
            <th></th>
        </tr>
    </thead>
    <tbody></tbody>
</table>

<div class="modal fade" id="editAddressModal" tabindex="-1" aria-labelledby="editAddressModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h1 class="modal-title fs-5" id="editAddressModalLabel">Edit Address</h1>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body"></div>
            <div class="modal-footer">
                <a href="/lead/quarantine-address/" id="quarantine_link"><i class="fa fa-times"></i>&nbsp;Quarantine</a>
                <button type="button" class="btn btn-primary" id="button_save">Save</button>
            </div>
        </div>
    </div>
</div>

<script>

    $(document).ready(function() {

        var table = new tableData('#leadTable', {
            url: '/app-data/scraper/leads',
            sort: {active: 'DESC'},
            <?php if ($url->url_id) { ?>
                filter: {url_id: <?=$url->url_id?>},
            <?php } ?>
            columns: [
                {col: 'active', search: false,
                    template: function (data) {
                        const checked = (data.active) ? ' checked' : '';
                        return '<input type="checkbox" value="1" class="lead_active_toggle" data-lead="' + data.lead_id + '"' + checked + ' />';
                    }
                },
                {col: 'url', search: false,
                    template: function (data) {
                        // const short = (data.url.length >= 30) ? data.url.substring(0, 30) + '...' : data.url;
                        return '<a href="' + data.url + '" target="_blank">Pdf URL</a>';
                    }
                },
                {col: 'address',
                    template: function (data) {
                        let html = '';
                        if (typeof data.addresses == 'object') {
                            if (data.addresses.length === 0) {
                                html = '<a href="javascript:void(0);" class="edit_trigger" data-address="" data-lead="' + data.lead_id + '">Add an address</a>';
                            } else {
                                var sep = '';
                                for (var i = 0; i < data.addresses.length; i++) {
                                    html += sep;
                                    sep = '<div style="height: 7px;"></div>';
                                    if (data.addresses[i].lom != '') html += '<a href="javascript:void(0);" class="trigger_street_view" data-address="' + data.addresses[i].address_id + '">';
                                    html += data.addresses[i].street + '<br />' + data.addresses[i].city + ', ' + data.addresses[i].state + ' ' + data.addresses[i].zip;
                                    if (data.addresses[i].lom != '') html += '</a>';
                                    html += '&nbsp;<span class="edit_trigger" style="cursor: pointer;" data-address="' + data.addresses[i].address_id + '"><i class="fa fa-pencil"></i></span>';
                                }
                            }
                        }
                        return html;
                    }
                },
                {col: 'judgment_amount', format: 'usd'},
                {col: 'created', format: 'datetime'},
                <?php if ($viewAll) { ?>
                    {col: 'url_name'},
                <?php } ?>
                {col: '', cellStyle: 'text-align:right;', search: false, sort: false,
                    template: function(data) {
                        let html = '<button role="button" class="btn btn-outline-primary btn-sm me-md-1 edit_trigger" data-lead="' + data.lead_id + '" type="button"><i class="fa fa-pencil"></i></button>';
                        html += '<button role="button" class="btn btn-outline-danger btn-sm me-md-1 confirm_trigger" data-lead="' + data.lead_id + '" data-message="Are you sure you want to delete this lead?" data-url="/delete-lead/' + data.lead_id + '" type="button"><i class="fa fa-times"></i></button>';
                        return html;
                    }
                },
            ]
        });

        $(document).on('click', '.trigger_street_view', function() {
            let address = $(this).data('address');
            $.get('/street-view/lead/' + address).done(function(result) {
                $('#viewModalLabel').text('Street View');
                $('#viewModal .modal-body').html(result);
                $('#viewModal').modal('show');
            });
        });

        $(document).on('click', '.lead_active_toggle', function() {

            let lead = $(this).data('lead');
            let active = ($(this).is(':checked')) ? 1 : 0;

            $.post('/toggle-lead-active/' + lead + '/' + active).done(function (result) {
                result = JSON.parse(result);
                if (typeof result.result == 'undefined') {
                    alert('An unknown error occurred');
                    return;
                }
                if (result.result == 'success') {
                    // location.reload();
                } else if (result.result == 'error') {
                    let message = (typeof result.message != 'undefined')
                        ? result.message
                        : 'An error occurred saving the lead';
                    alert(message);
                    return;
                }
            });
        });

        $(document).on('click', '.edit_trigger', function () {

            let lead = $(this).data('lead');
            let address = $(this).data('address');

            let href = '/edit-lead/' + lead;
            let modalTitle = 'Edit Lead';

            if (typeof address != 'undefined') {
                if (address) {
                    href = '/lead/edit-address/' + address;
                    modalTitle = 'Edit Address';
                    $('#quarantine_link').attr('href', '/lead/quarantine-address/' + address).show();
                } else {
                    href = '/lead/' + lead + '/add-address';
                    modalTitle = 'Add Address';
                    $('#quarantine_link').hide();
                }
                $.get(href).done(function(result) {
                    $('#editAddressModalLabel').text(modalTitle);
                    $('#editAddressModal .modal-body').html(result);
                    $('#editAddressModal').modal('show');
                });
            } else {
                $.get(href).done(function(result) {
                    $('#editModalLabel').text(modalTitle);
                    $('#editModal .modal-body').html(result);
                    $('#editModal').modal('show');
                });
            }
        });

    });

</script>
