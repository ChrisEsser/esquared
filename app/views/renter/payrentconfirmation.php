<?php

/** @var \PaymentHistory $payment */
$payment = $this->getVar('payment');
$amount = explode('.', number_format($payment->amount + $payment->fee, 2));

?>

<div class="mx-auto px-3" style="max-width: 500px; border: solid 1px rgba(0, 0, 0, 0.15); border-radius: 0.375rem;">

    <h1 style="color: #2E8B57" class="text-center">Payment Successful</h1>

    <h5 class="text-center"><strong>Confirmation Number:</strong> <span style="color: #2E8B57"><?=$payment->confirmation_number?></span></h5>
    <h5 class="text-center"><strong>Payment Method:</strong> <span style="color: #2E8B57"><?=ucwords($payment->method)?></span></h5>

    <hr />

    <div style="display: flex; align-items: center; justify-content: space-between">
        <p class="mb-0">Online rent payment</p>
        <p class="mb-0" style="font-size: 32px;font-weight: bold;line-height: 10px;">$<?=$amount[0]?><sup style="font-size: 18px;"><?=$amount[1]?></sup></p>
    </div>

     <hr />

     <p><strong>Payment Date:</strong> <?=date('M j, Y', strtotime($payment->payment_date))?></p>
     <p><strong>Payment Made By:</strong> <?=$payment->getUser()->first_name . ' ' . $payment->getUser()->last_name?></p>

    <p class="text-center"><br /><br />Questions about your payment?<br />Contact us at <strong>info@equaredholdings.com</strong></p>

</div>
