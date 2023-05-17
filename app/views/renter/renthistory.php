<?php

/** @var User $user */
$user = $this->getVar('user');
/** @var Unit $unit */
$unit = $this->getVar('unit');
/** @var Lease $lease */
$lease = $this->getVar('lease');
$paymentDetails = $this->getVar('paymentDetails');

?>

<h1 class="page_header">My Rent <small>- <?=$unit->name?></small></h1>

<div class="alert alert-info" role="alert">
    <h4 class="alert-heading">Hey There<?=($user->first_name) ? ', ' . ucwords($user->first_name) : ''?>!</h4>
    <p>We accept rent payments by check, money order, or online. Paying by credit card online is quick and easy but it does require your to pay a 2.9% credit card processing fee in addition to your full rent.</p>
    <?php if (!empty($paymentDetails) && $paymentDetails['stripe_ach_verified'] == 1) { // ach set up started but not verified?>
        <hr />
        <p class="mb-0">It looks like you started the ACH setup process. Just one more step to finalize the setup. Click <a href="javascript:void(0);" class="edit_trigger" data-type="manage"><strong>here to finish.</strong></a></p>
    <?php } else if (!empty($paymentDetails) && $paymentDetails['stripe_ach_verified'] == 2) { // ach setup complete ?>
        <!-- nothing to show here i guess -->
    <?php } else { ?>
        <hr />
        <p class="mb-0">We also offer Direct Debit (ACH) as an online payment method. This is the preferred online method as it has much lower fees for you.<br />
            <strong>To setup ACH, click "Edit Payment Methods" below.</strong>
        </p>
    <?php } ?>
</div>

<button class="btn btn-success edit_trigger" data-type="pay"><i class="fa fa-dollar"></i> Pay Rent</button>
<button class="btn btn-secondary edit_trigger" data-type="manage">Edit Payment Methods</button>

<hr />

<h5>Your payment history</h5>
<p>Please note, when paying by check it might take a few days for this section to update.</p>

<?php if (count($lease->getPaymentHistory())) { ?>

    <table class="e2-table">
        <thead>
            <tr>
                <th>Number</th>
                <th>Date</th>
                <th>Amount</th>
                <th>Fee</th>
                <th>Payment method</th>
                <th>Type</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($lease->getPaymentHistory() as $payment) { ?>
                <tr>
                    <td>
                        <?php if ($payment->confirmation_number) { ?>
                            <a href="/confirmation/<?=$payment->confirmation_number?>" target="_blank"><?=$payment->confirmation_number?></a>
                        <?php } else { ?>
                            In Person <small>(<?=$payment->getUser()->first_name . ' ' . $payment->getUser()->last_name?>)</small>
                        <?php } ?>
                    </td>
                    <td><?=date('m/d/y', strtotime($payment->payment_date))?></td>
                    <td>$<?=number_format($payment->amount, 2)?></td>
                    <td>$<?=number_format($payment->fee, 2)?></td>
                    <td><?=ucwords($payment->method)?></td>
                    <td><?=ucwords($payment->type)?></td>
                </tr>
            <?php } ?>
        </tbody>
    </table>

<?php } else { ?>

    <div class="alert alert-primary" role="alert">No payment history</div>

<?php } ?>

<div class="modal fade" id="payModal" tabindex="-1" aria-labelledby="payModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h1 class="modal-title fs-5" id="payModalLabel">Pay Rent</h1>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body"></div>
            <div class="modal-footer">
                <button type="button" class="btn btn-success" id="button_save"><i class="fa fa-dollar"></i> Process Payment</button>
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
            </div>
        </div>
    </div>
</div>

<script>

    $(document).ready(function() {

        $(document).on('click', '.edit_trigger', function () {

            let type = $(this).data('type');

            if (type == 'pay') {
                $.get('/pay-rent').done(function(result) {
                    $('#payModalLabel').text('Pay Rent');
                    $('#payModal .modal-body').html(result);
                    $('#payModal').modal('show');
                });
            } else {
                $.get('/manage-payment').done(function(result) {
                    $('#editModalLabel').text('Manage Payment Methods');
                    $('#editModal .modal-body').html(result);
                    $('#editModal').modal('show');
                });
            }



        });

    });



</script>


