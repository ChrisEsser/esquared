<?php

/** @var \Note $note */
$note = $this->getVar('note');
/** @var \Property $property */
$property = $this->getVar('property');

?>

<form id="noteForm">

    <input type="hidden" name="noteId" id="noteId" value="<?=$note->note_id?>" />
    <input type="hidden" name="property" id="property" value="<?=$property->property_id?>" />

    <div class="mb-3">
        <label for="note" class="form-label">Note</label>
        <textarea class="form-control" id="note" name="note" aria-describedby="notenHelp"><?=$note->note?></textarea>
    </div>

    <div class="mb-3">
        <label for="type" class="form-label">Type</label>
        <select class="form-control" id="type" name="type" aria-describedby="typeHelp">
            <?php foreach ($note->typeStrings() as $value => $string) { ?>
                <option value="<?=$value?>" <?=$note->type == $value ? 'selected' : ''?>><?=$string?></option>
            <?php } ?>
        </select>
    </div>

    <div class="mb-3" id="status_row" style="display: none;">
        <label for="status" class="form-label">Status</label>
        <select class="form-control" id="status" name="status" aria-describedby="statusHelp">
            <?php foreach ($note->statusStrings() as $value => $string) { ?>
                <option value="<?=$value?>" <?=$note->status == $value ? 'selected' : ''?>><?=$string?></option>
            <?php } ?>
        </select>
    </div>

</form>

<script>

    $(document).ready(function() {

        toggleStatusRow();

        $('#type').change(function() {
            toggleStatusRow();
        });

        $('#button_save').click(function() {
            $.post('/save-note', $('#noteForm').serialize()).done(function(result) {

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
                        : 'An error occurred saving the note';
                    alert(message);
                    return;
                }
            });
        });

    });

    function toggleStatusRow() {
        if ($('#type').val() == '1') $('#status_row').show();
        else $('#status_row').hide();
    }

</script>
