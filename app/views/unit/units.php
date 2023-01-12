<?php

/** @var Unit[] $units */
$units = $this->getVar('units');
/** @var Property $property */
$property = $this->getVar('property');

?>

<h1 class="page_header">Units<?=(!empty($property->property_id)) ? '<small> - ' . $property->name . '</small>' : ''?></h1>
