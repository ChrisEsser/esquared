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
<!--            <th>Flag</th>-->
            <th>lead URL</th>
            <th>Address</th>
            <th>Judgment</th>
            <th>Last Scrapped</th>
            <?=($viewAll) ? '<th>Scraper</th>' : ''?>
            <th></th>
        </tr>
    </thead>
    <tbody></tbody>
</table>

<script>

    $(document).ready(function() {

        var table = new tableData('#leadTable', {
            url: '/app-data/scraper/leads',
            filter: {
                search: '',
                url: '<?=($url->url_id) ? $url->url_id : ''?>'
            },
            columns: [
                {
                    col: 'active',
                    search: false,
                    template: function (data) {
                        const checked = (data.active) ? ' checked' : '';
                        return '<input type="checkbox" value="1" class="lead_active_toggle" data-lead="' + data.lead_id + '"' + checked + ' />';
                    }
                },
                // {
                //     col: 'flagged',
                //     search: false,
                //     template: function (data) {
                //         const checked = (data.flagged) ? ' checked' : '';
                //         return '<input type="checkbox" value="1" class="lead_flag_toggle" data-lead="' + data.lead_id + '"' + checked + ' />';
                //     }
                // },
                {
                    col: 'url',
                    search: false,
                    template: function (data) {
                        // const short = (data.url.length >= 30) ? data.url.substring(0, 30) + '...' : data.url;
                        return '<a href="' + data.url + '" target="_blank">Pdf URL</a>';
                    }
                },
                {
                    col: 'address',
                    template: function (data) {

                        let html = '';
                        if (data.lom != '') html += '<a href="javascript:void(0);" class="trigger_street_view" data-lead="' + data.lead_id + '">';
                        html += data.street + '<br />' + data.city + ', ' + data.state + ' ' + data.zip;
                        if (data.lom != '') html += '</a>';

                        return html;
                    }
                },
                {
                    col: 'amount',
                    format: 'usd'
                },
                {col: 'last_seen', format: 'date'},
                <?php if ($viewAll) { ?>
                {
                    col: 'scraper',
                    template: function (data) {
                        return '<a href="/scraper/' + data.scraper_id + '">' + data.url_name + '</a>';
                    }
                },
                <?php } ?>
                {
                    col: '',
                    cellStyle: 'text-align:right;',
                    search: false,
                    template: function(data) {
                        let html = '<button role="button" class="btn btn-primary btn-sm me-md-1 edit_trigger" data-lead="' + data.lead_id + '" type="button">Edit</button>';
                        html += '<button role="button" class="btn btn-danger btn-sm me-md-1" data-trigger="confirm" data-lead="' + data.lead_id + '" data-message="Are you sure you want to delete this lead?" data-url="/delete-lead/' + data.lead_id + '" type="button">Delete</button>';
                        return html;
                    }
                },
            ]
        });

        $(document).on('click', '.trigger_street_view', function() {
            let lead = $(this).data('lead');
            $.get('/lead-street-view/' + lead).done(function(result) {
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

        $(document).on('click', '.lead_flag_toggle', function() {

            let lead = $(this).data('lead');
            let flagged = ($(this).is(':checked')) ? 1 : 0;

            $.post('/toggle-lead-flagged/' + lead + '/' + flagged).done(function (result) {

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
            let href = '/edit-lead/' + lead;
            let modalTitle = 'Edit Lead Address';

            $.get(href).done(function(result) {
                $('#editModalLabel').text(modalTitle);
                $('#editModal .modal-body').html(result);
                $('#editModal').modal('show');
            });
        });

    });

</script>
