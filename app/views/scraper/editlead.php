<?php

/** @var \ScraperLead $lead */
$lead = $this->getVar('lead');

?>

<style>
    .pac-container {
        z-index: 9999 !important;
    }
</style>

<form id="LeadForm">

    <input type="hidden" name="lead" id="lead" value="<?=$lead->lead_id?>" />

    <div class="mb-3">
        <label for="city" class="form-label">Judgment Amount</label>
        <input type="number" min="0" step=".01" class="form-control" id="judgment_amount" name="judgment_amount" aria-describedby="judgment_amountHelp" autocomplete="off" value="<?= $lead->judgment_amount ?>"/>
    </div>

</form>


<script>

    $('#button_save').click(function() {
        $.post('/save-lead', $('#LeadForm').serialize()).done(function(result) {

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
                    : 'An error occurred saving the lead address';
                alert(message);
                return;
            }
        });
    });

</script>