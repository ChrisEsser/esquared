<?php

/** @var \ScraperUrl[] $urls */
$urls = $this->getVar('urls');

?>

<h1 class="page_header">Manage Scraper</h1>

<div class="d-grid gap-2 d-md-flex my-3 justify-content-md-end">
    <button role="button" class="btn btn-primary me-md-2 edit_trigger" type="button">Add Url</button>
</div>

<div class="mb-3">
    <a href="/scraper/leads">View All Leads</a> <!--| <a href="/scraper/all">Scrape All Urls</a>-->
</div>

<?php if (count($urls)) { ?>

    <div class="table-responsive">
    <table class="e2-table">
        <thead>
            <tr>
                <th>Name</th>
                <th>state</th>
                <th>Leads</th>
                <th>Last Scraped</th>
                <th></th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($urls as $url) { ?>

                <tr>
                    <td><a href="<?=$url->url?>" target="_blank"><?=$url->name?></a></td>
                    <td><?=$url->state?></td>
                    <td><?=($url->leads_count) ? '<a href="/scraper/' . $url->url_id . '/leads">' : ''?><?=$url->leads_count?><?=($url->leads_count) ? '</a>' : ''?></td>
                    <td><?=date('m/d/y g:ia', strtotime($url->last_scraped))?></td>
                    <td style="text-align: right">
                        <button role="button" class="btn btn-primary btn-sm me-md-1 edit_trigger" data-url="<?=$url->url_id?>" type="button">Edit</button>
                        <a href="/scraper/<?=$url->url_id?>" class="btn btn-secondary btn-sm me-md-1" type="button">Scrape</a>
                        <button role="button" class="btn btn-danger btn-sm me-md-1" data-trigger="confirm" data-message="Are you sure you want to delete <strong><?=$url->name?></strong>?" data-url="/delete-scraper/<?=$url->url_id?>" type="button">Delete</button>
                    </td>
                </tr>
            <?php } ?>
        </tbody>
    </table>
    </div>

<?php } else { ?>

    <div class="alert alert-primary" role="alert">No Scraper URLs</div>

<?php } ?>


<script>

    $(document).ready(function() {

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
