<?php

/** @var \PaymentHistory $payment */
$payment = $this->getVAr('payment');
/** @var \Property $property */
$property = $this->getVAr('property');

?>


<form id="paymentForm">

    <input type="hidden" id="payment" name="payment" value="<?=$payment->payment_id?>" />

    <?php if ($property && count($property->getUnit())) { ?>

        <div class="mb-3">
            <label for="unit_id" class="form-label">Rental Unit</label>
            <select name="unit_id" id="unit_id" class="form-control" aria-describedby="unit_idHelp">
                <option value="0" <?=(empty($payment->unit_id)) ? 'selected' : ''?>>- Select Unit -</option>
                <?php foreach ($property->getUnit() as $unit) { ?>
                    <option value="<?=$unit->unit_id?>" <?=($payment->unit_id == $unit->unit_id) ? 'selected' : ''?>><?=$unit->name?></option>
                <?php } ?>
            </select>
        </div>

    <?php } ?>

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

        $('#button_save').click(function() {
            $.post('/property/save-payment', $('#paymentForm').serialize()).done(function(result) {

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

