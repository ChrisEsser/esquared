<?php

/** @var \Expense $expense */
$expense = $this->getVAr('expense');
/** @var [] $units */
$units = $this->getVAr('units');
/** @var Property[] $properties */
$properties = $this->getVAr('properties');
/** @var Property $property */
$property = $this->getVAr('property');

?>

<script>
    var units = <?=json_encode($units)?>;
</script>

<form id="expenseForm">

    <input type="hidden" id="expense" name="expense" value="<?=$expense->expense_id?>" />

    <div class="row">

        <div class="mb-3 col-md-6">
            <label for="property_id" class="form-label">Property</label>
            <select name="property_id" id="property_id" class="form-control" aria-describedby="property_idHelp">
                <option value="0" <?=(empty($expense->property_id)) ? 'selected' : ''?>>- Select Property -</option>
                <?php foreach ($properties as $prop) { ?>
                    <option value="<?=$prop->property_id?>" <?=($expense->property_id === $prop->property_id || $property->property_id === $prop->property_id) ? 'selected' : ''?>><?=$prop->name?></option>
                <?php } ?>
            </select>
        </div>

        <div class="mb-3 col-md-6" id="unit_row" style="display: none">
            <label for="unit_id" class="form-label">Unit</label>
            <select name="unit_id" id="unit_id" class="form-control" aria-describedby="unit_idHelp"></select>
        </div>

    </div>

    <div class="row">

        <div class="mb-3 col-md-6">
            <label for="amount" class="form-label">Amount</label>
            <div class="input-group">
                <span class="input-group-append">
                    <span class="input-group-text bg-light d-block">$</span>
                </span>
                <input type="number" min="0" step="0.1" class="form-control" name="amount" id="amount" value="<?=$expense->amount?>" />
            </div>
        </div>

        <div class="mb-3 col-md-6">
            <label for="date" class="form-label">Date</label>
            <div class="input-group">
                <input type="text" class="form-control" name="date" id="date" value="<?=($expense->date) ? date('m/d/Y', strtotime($expense->date)) : ''?>" />
                <span class="input-group-append">
                    <span class="input-group-text bg-light d-block">
                        <i class="fa fa-calendar"></i>
                    </span>
                </span>
            </div>
        </div>

    </div>

    <div class="mb-3">
        <label for="description" class="form-label">Description</label>
        <textarea class="form-control" id="description" name="description" rows="2"><?=nl2br($expense->description)?></textarea>
    </div>

</form>

<script>

    function updateUnit() {
        const property = $('#property_id').val();
        if (drawUnitDropdown(property)) $('#unit_row').show();
        else $('#unit_row').hide();
    }
    function drawUnitDropdown(property) {
        if (!property || typeof units[property] == 'undefined') return false;
        $('#unit_id').append($('<option>', {
            value: 0,
            text: ' - No Unit - '
        }));
        for (i = 0; i < units[property].length; i++) {
            $('#unit_id').append($('<option>', {
                value: units[property][i].unit_id,
                text: units[property][i].name
            }));
        }
        return true;
    }

    $(document).ready(function () {

        $("#date").datepicker();

        updateUnit();
        $('#property_id').change(function() {
            updateUnit();
        });

        $('#button_save').click(function() {
            $.post('/save-expense', $('#expenseForm').serialize()).done(function(result) {

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
                        : 'An error occurred saving the payment';
                    alert(message);
                    return;
                }
            });
        });

    });
</script>
