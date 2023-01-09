<?php

    /** @var \ScraperUrl $scraperUrl */
    $url = $this->getVar('url');
    /** @var \ScraperLead[] $leads */
    $leads = $this->getVar('leads');
    $viewAll = $this->getVar('viewAll');

?>

<h1 class="page_header">Leads<?=($url->name) ? ' <small>- <a href="' . $url->url . '" target="_blank">' . $url->name . '</a></small>' : ''?></h1>

<?php if (count($leads)) { ?>

    <table class="e2-table">
        <thead>
            <tr>
                <th>Active</th>
                <th>Flag</th>
                <th>lead URL</th>
                <th>Address</th>
                <th>Amount</th>
                <th>First Scrapped</th>
                <th>Last Scrapped</th>
                <?=($viewAll) ? '<th>Scraper</th>' : ''?>
                <th></th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($leads as $lead) { ?>
                <tr class="<?=($lead->flagged) ? 'table-success' : ''?>">
                    <td><input type="checkbox" value="1" class="lead_active_toggle" data-lead="<?=$lead->lead_id?>"<?=($lead->active) ? ' checked' : ''?> /></td>
                    <td><input type="checkbox" value="1" class="lead_flag_toggle" data-lead="<?=$lead->lead_id?>"<?=($lead->flagged) ? ' checked' : ''?> /></td>
                    <td><a href="<?=$lead->url?>" target="_blank"><?=(strlen($lead->url) > 30) ? substr($lead->url, 0, 30) . '...' : $lead->url?></a></td>
                    <td>
                        <?php if ($lead->street) { ?>
                            <?php if ($lead->lat && $lead->lon) { ?>
                                <a href="javascript:void(0);" class="trigger_street_view" data-lead="<?=$lead->lead_id?>">
                            <?php } ?>
                                <?=$lead->street?><br />
                                <?=$lead->city?>, <?=$lead->state?> <?=$lead->zip?><br />
                            <?php if ($lead->lat && $lead->lon) { ?>
                                </a>
                            <?php } ?>
                        <?php } ?>
                    </td>
                    <td>$<?=number_format($lead->judgment_amount, 2)?></td>
                    <td><?=date('m/d/y', strtotime($lead->created))?></td>
                    <td><?=date('m/d/y', strtotime($lead->last_seen))?></td>
                    <?=($viewAll) ? '<td><a href="/scraper/' .  $lead->getScraperUrl()->url_id . '/leads">' . $lead->getScraperUrl()->name . '</a></td>' : ''?>
                    <td style="text-align: right">
                        <button role="button" class="btn btn-primary btn-sm me-md-1 edit_trigger" data-lead="<?=$lead->lead_id?>" type="button">Edit</button>
                        <a href="/lead/<?=$lead->lead_id?>" class="btn btn-secondary btn-sm me-md-1" data-lead="<?=$lead->lead_id?>">View</a>
                        <button role="button" class="btn btn-danger btn-sm me-md-1" data-trigger="confirm" data-message="Are you sure you want to delete this lead?" data-url="/delete-lead/<?=$lead->lead_id?>" type="button">Delete</button>
                    </td>
                </tr>
            <?php } ?>
        </tbody>
    </table>

<?php } else { ?>

    <div class="alert alert-primary" role="alert">No leads</div>

<?php } ?>

<script>

    $(document).ready(function() {

        $('.trigger_street_view').click(function() {
            let lead = $(this).data('lead');
            $.get('/lead-street-view/' + lead).done(function(result) {
                $('#viewModalLabel').text('Street View');
                $('#viewModal .modal-body').html(result);
                $('#viewModal').modal('show');
            });
        });

        $('.lead_active_toggle').click(function() {

            let lead = $(this).data('lead');
            let active = ($(this).is(':checked')) ? 1 : 0;

            $.post('/toggle-lead-active/' + lead + '/' + active).done(function (result) {
                result = JSON.parse(result);
                if (typeof result.result == 'undefined') {
                    alert('An unknown error occurred');
                    return;
                }
                if (result.result == 'success') {
                    location.reload();
                } else if (result.result == 'error') {
                    let message = (typeof result.message != 'undefined')
                        ? result.message
                        : 'An error occurred saving the lead';
                    alert(message);
                    return;
                }
            });
        });

        $('.lead_flag_toggle').click(function() {

            let lead = $(this).data('lead');
            let flagged = ($(this).is(':checked')) ? 1 : 0;

            $.post('/toggle-lead-flagged/' + lead + '/' + flagged).done(function (result) {

                result = JSON.parse(result);
                if (typeof result.result == 'undefined') {
                    alert('An unknown error occurred');
                    return;
                }
                if (result.result == 'success') {
                    location.reload();
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
