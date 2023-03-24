<?php

/** @var \Expense $expense */
$expense = $this->getVAr('expense');
$properties = $this->getVar('properties');
$propertyId = $this->getVar('propertyId');
$unitId = $this->getVar('unitId');

?>

<script>
    var properties = <?=json_encode($properties)?>;
    var propertyId = <?=json_encode($propertyId)?>;
    var unitId = <?=json_encode($unitId)?>;
</script>

<form id="expenseForm">

    <input type="hidden" id="expense" name="expense" value="<?=$expense->expense_id?>" />

    <div class="row">

        <div class="mb-3 col-md-6">
            <label for="name" class="form-label">Property</label>
            <select name="property" id="property" class="form-control">
                <option value="">- Select -</option>
                <?php foreach ($properties as $property) { ?>
                    <option value="<?=$property['property_id']?>" <?=($property['property_id'] == $propertyId) ? 'selected' : ''?>><?=$property['property_name']?></option>
                <?php } ?>
            </select>
        </div>

        <div class="mb-3 col-md-6">
            <label for="name" class="form-label">Unit</label>
            <select name="unit_id" id="unit_id" class="form-control">
            </select>
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

    $(document).ready(function () {

        $("#date").datepicker();

        $('#property').change(function() {
            updateUnitDropdown();
        });

        updateUnitDropdown();

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

    function updateUnitDropdown()
    {
        const property = $('#property').val();
        let units = [];
        for (i = 0; i < properties.length; i++) {
            if (property == properties[i].property_id) {
                units = properties[i].units;
                break;
            }
        }
        let html = '<option value="">- Select -</option>';
        for (i = 0; i < units.length; i++) {
            html += '<option value="' + units[i].unit_id + '" ' + ((unitId == units[i].unit_id) ? 'selected' : '') + '>';
            html += units[i].unit_name + '</option>';
        }
        $('#unit_id').html(html);
    }
</script>
