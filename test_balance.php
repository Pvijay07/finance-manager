<?php
$income_amount = 500.00;
$income_actual_amount = 462.96;

$validated_amount = 462.96;
$validated_gst_amount = 83.33;
$validated_tds_amount = 46.30;
$receivedAmount = 250.00;

$actualTotalBase = $validated_amount;
$gstAmountTotal = $validated_gst_amount;
$tdsAmountTotal = $validated_tds_amount;

$plannedAmountTotal = $actualTotalBase + $gstAmountTotal;
$payableAmountTotal = $plannedAmountTotal - $tdsAmountTotal;

echo "Before fix payable: " . $payableAmountTotal . "\n";

if (abs($payableAmountTotal - floatval($income_amount)) <= 0.05 && abs($actualTotalBase - floatval($income_actual_amount)) < 0.01) {
    $payableAmountTotal = floatval($income_amount);
    echo "Fixed payable: " . $payableAmountTotal . "\n";
} else {
    echo "Fix condition failed.\n";
    echo "diff payable: " . abs($payableAmountTotal - floatval($income_amount)) . "\n";
    echo "diff base: " . abs($actualTotalBase - floatval($income_actual_amount)) . "\n";
}

$balanceAmount = $payableAmountTotal - $receivedAmount;
echo "Balance: " . $balanceAmount . "\n";
