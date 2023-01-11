<?php

/** @var User $user */
$user = $this->getVar('user');
$paymentDetails = $this->getVar('paymentDetails');

?>

<script>
    var achStep = <?=(!empty($paymentDetails)) ? intval($paymentDetails['stripe_ach_verified']) : 0?>
</script>

<?php if (!empty($paymentDetails) && $paymentDetails['stripe_ach_verified'] == 1) { // ach set up started but not verified?>

    <div class="alert alert-info" role="alert">
        <p class="mb-0">It looks like you started the ACH setup process. to complete your payment set up, Enter the two micro deposit amounts that were deposited into your back account. It usually takes one to two days to receive these deposits.</p>
    </div>

    <form id="methodForm">

        <input type="hidden" name="ach_type" id="ach_type" value="verify" />

        <p><small><i>By submitting this form, you authorize E Squared Holdings, LLC to electronically debit my account and, if necessary, electronically credit my account to correct erroneous debits.</i></small></p>

        <div class="row">

            <div class="mb-3 col-md-6">
                <label class="form-label" for="deposit_0">Deposit 1 Amount</label>
                <input type="number" min="0" step=".01" class="form-control" name="deposit_0" id="deposit_0" />
            </div>

            <div class="mb-3 col-md-6">
                <label class="form-label" for="deposit_1">Deposit 2 Amount</label>
                <input type="number" min="0" step=".01" class="form-control" name="deposit_1" id="deposit_1" />
            </div>

        </div>

    </form>

<?php } else if (!empty($paymentDetails) && $paymentDetails['stripe_ach_verified'] == 2) { // ach setup complete ?>



<?php } else { // no valid ach ?>

    <div class="alert alert-info" role="alert">
        <p class="mb-0">You do not have any saved payment methods. Click below to set one up. Otherwise, you call always pay by credit card, check, or money order.</p>
    </div>

    <button role="button" type="button" class="btn btn-primary" id="add_method_trigger">Add Payment Method</button>

    <div id="payment_method_form" class="mt-3" style="display: none">

        <form id="methodForm">

            <input type="hidden" name="ach_type" id="ach_type" value="setup" />

            <p style="border-bottom: 1px solid #cccccc; font-weight: bold;">Add Bank Account Details</p>

            <p><small><i>By submitting this form, you authorize E Squared Holdings, LLC to electronically debit my account and, if necessary, electronically credit my account to correct erroneous debits.</i></small></p>

            <div class="row">

                <div class="mb-3 col-md-6">
                    <label class="form-label" for="account_name">Account Holder Name</label>
                    <input type="text" class="form-control" name="account_name" id="account_name" />
                </div>

                <div class="mb-3 col-md-6">
                    <label class="form-label" for="account_type">Account Holder Type</label>
                    <select class="form-control" name="account_type" id="account_type">
                        <option value="">- Select -</option>
                        <option value="individual">Individual</option>
                        <option value="company">Company</option>
                    </select>
                </div>

                <div class="mb-3 col-md-6">
                    <label class="form-label" for="account_number">Account Number</label>
                    <input type="text" class="form-control" name="account_number" id="account_number" />
                </div>

                <div class="mb-3 col-md-6">
                    <label class="form-label" for="routing_number">Routing Number</label>
                    <input type="text" class="form-control" name="routing_number" id="routing_number" />
                </div>

            </div>

        </form>

    </div>

<?php } ?>

<script>

    $(document).ready(function() {

        var stripe = Stripe('<?=$_ENV['STRIPE_PUBLIC']?>');

        if (!achStep) {
            $('.modal-footer').hide();
        }

        $('#add_method_trigger').click(function() {
            $('#payment_method_form').show();
            $(this).hide();
            $('.modal-footer').show();
        });

        $("#editModal").bind("hidden.bs.modal", function(event) {
            $('.modal-footer').show();
        });

        $(document).on('click','#button_save',function(e) {
            if (!achStep) {
                stripe.createToken('bank_account', {
                    country: 'US',
                    currency: 'usd',
                    routing_number: $('#routing_number').val(),
                    account_number: $('#account_number').val(),
                    account_holder_name: $('#account_name').val(),
                    account_holder_type: $('#account_type').val(),
                }).then(function(result) {
                    if (result.error && typeof result.error_message != 'undefined') alert(result.error_message);
                    else if (result.error) alert ('An error occurred processing your account');
                    else {
                        var form = document.getElementById('methodForm');
                        var input = document.createElement('input');
                        input.setAttribute('type', 'hidden');
                        input.setAttribute('name', 'token');
                        input.setAttribute('value', result.token.id);
                        form.appendChild(input);
                        submitForm();
                    }
                });
            } else if (achStep == 1) {
                submitForm();
            }
        });

        function submitForm() {
            var url = '';
            if (!achStep) url = '/ach-setup/process';
            else if (achStep == 1) url = '/ach-setup/verify';
            else {
                alert('Invalid request');
                return;
            }
            $.post(url, $('#methodForm').serialize()).done(function(result) {
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
                        : 'An error occurred saving the bank details';
                    alert(message);
                    return;
                }
            });
        }
    });

</script>
