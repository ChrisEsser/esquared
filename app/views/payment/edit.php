<?php

/** @var \PaymentHistory $payment */
$payment = $this->getVAr('payment');
$properties = $this->getVar('properties');
$propertyId = $this->getVar('propertyId');
$unitId = $this->getVar('unitId');



?>

<script>
    var properties = <?=json_encode($properties)?>;
    var propertyId = <?=json_encode($propertyId)?>;
    var unitId = <?=json_encode($unitId)?>;
</script>


<form id="paymentForm">

    <input type="hidden" id="payment" name="payment" value="<?=$payment->payment_id?>" />

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
            <label for="method" class="form-label">Method</label>
            <select name="method" id="method" class="form-control" aria-describedby="methodHelp">
                <option value="Check" <?=($payment->method == 'Check') ? 'selected' : ''?>>Check</option>
                <option value="Money Order" <?=($payment->method == 'Money Order') ? 'selected' : ''?>>Money Order</option>
            </select>
        </div>

        <div class="mb-3 col-md-6">
            <label for="type" class="form-label">Type</label>
            <select name="type" id="type" class="form-control" aria-describedby="typeHelp">
                <option value="Rent" <?=($payment->type == 'Rent') ? 'selected' : ''?>>Rent</option>
                <option value="Security Deposit" <?=($payment->type == 'Security Deposit') ? 'selected' : ''?>>Security Deposit</option>
            </select>
        </div>

    </div>

    <div class="row">

        <div class="mb-3 col-md-6">
            <label for="type" class="form-label">Date</label>
            <div class="input-group">
                <input type="text" class="form-control" id="payment_date" name="payment_date" value="<?=($payment->payment_date) ? date('m/d/Y', strtotime($payment->payment_date)) : ''?>" />
                <span class="input-group-append">
                    <span class="input-group-text bg-light d-block">
                        <i class="fa fa-calendar"></i>
                    </span>
                </span>
            </div>
        </div>

        <div class="mb-3 col-md-6">
            <label for="amount" class="form-label">Amount</label>
            <div class="input-group">
                <span class="input-group-append">
                    <span class="input-group-text bg-light d-block">
                        <i class="fa fa-dollar"></i>
                    </span>
                </span>
                <input type="number" min="0" style="0.1" name="amount" class="form-control" value="<?=$payment->amount?>" />
            </div>
        </div>


    </div>


</form>


<script>
    $(document).ready(function () {

        $("#payment_date").datepicker();

        $('#property').change(function() {
            updateUnitDropdown();
        });

        updateUnitDropdown();

        $('#button_save').click(function() {
            $.post('/save-payment', $('#paymentForm').serialize()).done(function(result) {

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

