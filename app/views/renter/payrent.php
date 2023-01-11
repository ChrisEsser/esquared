<?php

$rent = $this->getVar('rent');
$cardTotal = $this->getVar('cardTotal');
$achTotal = $this->getVar('achTotal');
$cardFee = $this->getVar('cardFee');
$achFee = $this->getVar('achFee');
$paymentDetails = $this->getVar('paymentDetails');

?>

<table style="min-width: 300px;">
    <tr>
        <th>Rent:</th>
        <td>$<?=number_format($rent, 2)?></td>
    </tr>
    <tr class="card_extra_payment_row" style="display: none">
        <th>Transaction Fee:</th>
        <td>$<?=number_format($cardFee, 2)?></td>
    </tr>
    <tr class="ach_extra_payment_row" style="display: none">
        <th>Transaction Fee:</th>
        <td>$<?=number_format($achFee, 2)?></td>
    </tr>
    <tr class="card_extra_payment_row" style="display: none">
        <th>Total:</th>
        <td>$<?=number_format($cardTotal, 2)?></td>
    </tr>
    <tr class="ach_extra_payment_row" style="display: none">
        <th>Total:</th>
        <td>$<?=number_format($achTotal, 2)?></td>
    </tr>
</table>

<hr/>

<div class="row">

    <div class="mb-3 col-md-6 col-9">
        <label for="type_toggle" class="form-label">Choose Payment Method</label>
        <select class="form-control" id="type_toggle">
            <?php if (!empty($paymentDetails) && $paymentDetails['stripe_ach_verified'] == 2) { ?>
                <option value="1">Electronic Fund Transfer (ACH)</option>
            <?php } ?>
            <option value="0">Credit Card</option>
<!--            <option value="2">Apple Pay</option>-->
        </select>
    </div>

    <div class="mb-3 col-md-6 col-3">



    </div>

</div>

<hr />

<div class="alert alert-danger" id="error_message" style="display: none"></div>

<div id="credit_card_container" class="pay_container" style="display: none">

    <p><strong>Payment Details</strong></p>

    <form id="cardForm" method="POST" action="/pay-rent/process/card">

        <div class="row">

            <div class="mb-3 col-md-6">
                <label for="cardName" class="control-label">Name on Card</label>
                <input type="text" id="cardName" name="card_name" class="form-control" />
            </div>

            <div class="mb-3 col-md-6">
                <label for="cardName" class="control-label">Zip Code</label>
                <input type="text" id="cardZip" name="card_zip" class="form-control" />
            </div>

        </div>

        <div class="row">

            <div class="mb-3 col-6">
                <label for="cardNumber" class="control-label">Card Number</label>
                <div id="cardNumber" class="form-control"></div>
            </div>

            <div class="mb-3 col-3">
                <label for="cardExpiry" class="control-label">Card Expiration</label>
                <div id="cardExpiry" class="form-control"></div>
            </div>

            <div class="mb-3 col-3">
                <label for="cardCvc" class="control-label">Card CVC</label>
                <div id="cardCvc" class="form-control"></div>
            </div>

        </div>

    </form>

</div>

<div id="apple_container" class="pay_container" style="display: none"></div>

<div id="ach_container" class="pay_container" style="display: none">

    <p><small><i>By submitting this form, you authorize E Squared Holdings, LLC to electronically debit my account and, if necessary, electronically credit my account to correct erroneous debits.</i></small></p>

    <form id="achForm" method="POST" action="/pay-rent/process/ach"></form>

</div>


<script>

    var stripe = Stripe('<?=$_ENV['STRIPE_PUBLIC']?>');
    var cardMounted = false;
    var cardNumber = {};
    var cardExpiry = {};
    var cardCvc = {};

    $('#type_toggle').change(function () {
        showPayContainer();
    });
    showPayContainer();

    function showPayContainer() {
        $('.pay_container').hide();
        $('.card_extra_payment_row').hide();
        $('.ach_extra_payment_row').hide();
        let type = $('#type_toggle').val();
        if (type === '0') {
            mountCard();
            $('#credit_card_container').show();
            $('.card_extra_payment_row').show();
        } else if (type === '1') {
            $('#ach_container').show();
            $('.ach_extra_payment_row').show();
        } else if (type === '2') {
            $('#apple_container').show();
        }
    }

    function mountCard() {

        if (cardMounted) return;
        var elements = stripe.elements();
        var inputs = document.querySelectorAll('#credit_card_container .input');
        Array.prototype.forEach.call(inputs, function (input) {
            input.addEventListener('focus', function () {
                input.classList.add('focused');
            });
            input.addEventListener('blur', function () {
                input.classList.remove('focused');
            });
            input.addEventListener('keyup', function () {
                if (input.value.length === 0) {
                    input.classList.add('empty');
                } else {
                    input.classList.remove('empty');
                }
            });
        });

        cardNumber = elements.create('cardNumber');
        cardNumber.mount('#cardNumber');

        cardExpiry = elements.create('cardExpiry');
        cardExpiry.mount('#cardExpiry');

        cardCvc = elements.create('cardCvc');
        cardCvc.mount('#cardCvc');

        cardMounted = true;

    }

    $('#button_save').click(function () {

        // maybe put some kind of loader overlay so the form cannot be submitted twice

        let type = $('#type_toggle').val();

        if (type === '0') {

            // Gather additional customer data we may have collected in our form.
            var name = $('#cardName').val();
            var zip = $('#cardZip').val();

            var additionalData = {
                name: name ? name.value : undefined,
                address_zip: zip ? zip.value : undefined,
            };

            stripe.createToken(cardNumber, additionalData).then(function (result) {
                $('#error_message').text('').hide();
                if (result.error && result.error.message) {
                    $('#error_message').text(result.error.message).show();
                } else {
                    $('#cardForm').append('<input type="hidden" name="stripeToken" value="' + result.token.id + '" />').submit();
                }
            });

        } else if (type === '1') {

            $('#achForm').submit();

        }

    });

</script>
