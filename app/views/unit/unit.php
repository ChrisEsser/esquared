<?php

    /** @var Unit $unit */
    $unit = $this->getVar('unit');

?>

    <div class="mb-3">
        <strong>Description:</strong><br />
        <?=$unit->description?>
    </div>

    <div class="mb-3">
        <strong>Status:</strong><br />
        <?=$unit->statusStrings()[$unit->status]?>
    </div>

    <div class="mb-3">
        <strong>Rent:</strong><br />
        $<?=number_format($unit->rent, 2)?>
    </div>

    <div class="mb-3">
        <strong>Rent Frequency:</strong><br />
        <?=$unit->rentFrequencyStrings()[intval($unit->rent_frequency)]?>
    </div>


