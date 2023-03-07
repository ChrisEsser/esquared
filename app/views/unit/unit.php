<?php

/** @var Unit $unit */
$unit = $this->getVar('unit');

?>

<h1 class="page_header"><?=$unit->name?></h1>

<ul class="breadcrumb">
    <li class="breadcrumb-item"><a href="/properties">Properties</a></li>
    <li class="breadcrumb-item"><a href="/property/<?=$unit->getProperty()->property_id?>"><?=$unit->getProperty()->name?></a></li>
    <li class="breadcrumb-item active"><?=$unit->name?></li>
</ul>

<div style="display: flex; justify-content: left; flex-wrap: wrap">

    <div style="display: flex; justify-content: left;">

        <div style="margin: 0 30px">
            <strong>Type:</strong>
            <br /><strong>Description:</strong>
            <br /><strong>Status:</strong>
            <br /><strong>Rent:</strong>
            <br /><strong>Rent Frequency:</strong>
        </div>

        <div>
            <?=$unit->typeStrings()[$unit->type]?>
            <br /><?=$unit->description?>
            <br /><?=$unit->statusStrings()[$unit->status]?>
            <br />$<?=number_format($unit->rent, 2)?>
            <br /><?=$unit->rentFrequencyStrings()[intval($unit->rent_frequency)]?>
        </div>
    </div>

</div>

<hr />

<ul class="nav nav-tabs" id="unitTabs" role="tablist">
    <li class="nav-item" role="presentation">
        <button class="nav-link active tabClick" id="rent-tab" data-bs-toggle="tab" data-bs-target="#rent" type="button" role="tab" aria-controls="rent" aria-selected="true">Rent History</button>
    </li>
    <li class="nav-item" role="presentation">
        <button class="nav-link tabClick" id="tenant-tab" data-bs-toggle="tab" data-bs-target="#tenant" type="button" role="tab" aria-controls="tenant" aria-selected="false">Tenant History</button>
    </li>
</ul>

<div class="tab-content" id="tabContents">

    <div class="tab-pane fade show active" id="rent" role="tabpanel" aria-labelledby="rent-tab">

        <?php if (count($unit->getPaymentHistory())) { ?>

            <table class="e2-table">
                <thead>
                <tr>
                    <th>Entered By</th>
                    <th>Date</th>
                    <th>Amount</th>
                    <th>Method</th>
                    <th>Type</th>
                    <th>Conf#</th>
                </tr>
                </thead>
                <tbody>
                <?php foreach ($unit->getPaymentHistory() as $payment) { ?>
                    <tr>
                        <td><?=$payment->getUser()->first_name . ' ' . $payment->getUser()->last_name?></td>
                        <td><?=date('m/d/y', strtotime($payment->payment_date))?></td>
                        <td>$<?=number_format($payment->amount, 2)?></td>
                        <td><?=$payment->method?></td>
                        <td><?=$payment->type?></td>
                        <td><?=$payment->confirmation_number?></td>
                    </tr>
                <?php } ?>
                </tbody>
            </table>

        <?php } else { ?>

            <div class="alert alert-primary" role="alert">No payment history</div>

        <?php } ?>

    </div>

    <div class="tab-pane fade" id="tenant" role="tabpanel" aria-labelledby="tenant-tab">
    </div>

</div>

<script>

    $(document).ready(function() {

        let lastTab = sessionStorage.getItem('lastUnitTab');
        if (lastTab) $('#' + lastTab).tab('show');

        $('.tabClick').click(function () {
            sessionStorage.setItem('lastUnitTab', $(this).attr('id'));
        });

    });

</script>
