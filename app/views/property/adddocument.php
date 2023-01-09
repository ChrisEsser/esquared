<?php

/** @var \Property $property */
$property = $this->getVar('property');

$docObj = new Document();

?>

<form id="documentForm">

    <div class="mb-3">
        <label for="description" class="form-label">Note</label>
        <textarea class="form-control" id="description" name="description" aria-describedby="descriptionHelp"></textarea>
    </div>

    <div class="mb-3">
        <label for="type" class="form-label">Type</label>
        <select class="form-control" id="type" name="type" aria-describedby="typeHelp">
            <?php foreach ($docObj->typeStrings() as $key => $string) { ?>
                <option value="<?=$key?>"><?=$string?></option>
            <?php } ?>
        </select>
    </div>

    <div class="mb-3" id="amount_row" style="display: none">
        <label for="amount" class="form-label">Amount</label>
        <input type="number" min="0" step=".01" class="form-control" id="amount" name="amount" aria-describedby="amountHelp" />
    </div>


    <div class="mb-3">
        <div id="document" name="filepond"></div>
    </div>

</form>

<script>
    $(document).ready(function() {

        $('#type').change(function() {
            toggleAmountRow();
        });

        toggleAmountRow();

        let docPond = createPond('#document');

        $('#button_save').click(function() {
            $.post('/property/<?=$property->property_id?>/save-document', $('#documentForm').serialize()).done(function(result) {

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
                        : 'An error occurred saving the unit';
                    alert(message);
                    return;
                }
            });
        });

    });

    function toggleAmountRow() {
        if ($('#type').val() == '1' || $('#type').val() == '2') $('#amount_row').show();
        else $('#amount_row').hide();
    }
</script>

