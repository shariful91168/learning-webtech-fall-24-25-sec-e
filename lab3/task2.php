<?php
$amount=20;
$vat_rate=5;

$vat = $amount * $vat_rate;

$total_amount = $amount + $vat;

echo "amount: " . $amount . " units<br>";
echo "vat (20%): " . $vat . "units<br>";
echo "total amount (including vat): " . $total_amount . " units<br>";

?>