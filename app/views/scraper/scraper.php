<?php


?>

<h1 class="page_header">Scraper</h1>

<div class="d-grid gap-2 d-md-flex my-3 justify-content-md-end">
    <button role="button" class="btn btn-round btn-primary edit_trigger" type="button">Add Url</button>
    <a href="/scraper/leads" class="btn btn-round btn-info">View All Leads</a>
    <a href="/scraper/quarantined-addresses" class="btn btn-round btn-info">Quarantined Addressed</a>
</div>

<table class="e2-table" id="scraperTable">
    <thead>
        <tr>
            <th>Name</th>
            <th>state</th>
            <th>Leads</th>
            <th>Last Scraped</th>
            <th></th>
        </tr>
    </thead>
    <tbody></tbody>
</table>

<script>

    $(document).ready(function() {

        var table = new tableData('#scraperTable', {
            url: '/app-data/scraper/urls',
            sort: {name: 'ASC'},
            columns: [
                {col: 'name',
                    template: function (data) {
                        return '<a href="' + data.url + '" target="_blank">' + data.name + '</a>';
                    }
                },
                {col: 'state'},
                {col: 'leads_count',
                    search: false,
                    template: function (data) {
                        return (data.leads_count == 0) ? 'leads (0)' : '<a href="/scraper/' + data.url_id + '/leads">leads (' + data.leads_count + ')</a>';
                    }
                },
                {col: 'last_scraped', format: 'datetime'},
                {col: '',
                    cellStyle: 'text-align:right;',
                    search: false,
                    sort: false,
                    template: function(data) {
                        let html = '<button role="button" class="btn btn-outline-primary btn-sm me-md-1 edit_trigger" data-url="' + data.url_id + '" type="button"><i class="fa fa-pencil"></i></button>';
                        html += '<button role="button" class="btn btn-outline-danger btn-sm me-md-1 confirm_trigger" data-url="/delete-scraper/' + data.url_id + '" data-message="Are you sure you want to delete <strong>' + data.name + '</strong>?" data-url="/delete-scraper/' + data.url_id + '" type="button"><i class="fa fa-times"></i></button>';
                        return html;
                    }
                },
            ]
        });

        $(document).on('click', '.edit_trigger', function () {

            let url = $(this).data('url');
            let href = (url) ? '/edit-scraper/' + url : '/create-scraper';
            let modalTitle = (url) ? 'Edit Scraper' : 'Create Scraper';

            $.get(href).done(function(result) {
                $('#editModalLabel').text(modalTitle);
                $('#editModal .modal-body').html(result);
                $('#editModal').modal('show');
            });
        });

    });

</script>
