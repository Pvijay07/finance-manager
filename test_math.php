<?php
$actualTotalBase = 1000;
$gstAmountTotal = 80;
$payableAmountTotal = 1080;
$receivedAmount = 580;

$proportion = $receivedAmount / $payableAmountTotal;
$paidBaseAmount = $actualTotalBase * $proportion;
$balanceBaseAmount = $actualTotalBase - $paidBaseAmount;

echo "Split 1:\n";
echo "Proportion: $proportion\n";
echo "Paid Base: $paidBaseAmount\n";
echo "Balance Base: $balanceBaseAmount\n";

$income_amount = 500;
$income_actual_amount = $balanceBaseAmount;

$receivedAmount2 = 250;
$paymentPercentage = $receivedAmount2 / $income_amount;
$currentBaseAmount = $income_actual_amount * $paymentPercentage;
$pendingBaseAmount = $income_actual_amount - $currentBaseAmount;

echo "Split 2:\n";
echo "Payment Percentage: $paymentPercentage\n";
echo "Current Base: $currentBaseAmount\n";
echo "Pending Base: $pendingBaseAmount\n";

// Rounding effect
echo "If actual_amount was rounded in DB to 462.96:\n";
$roundedBase = 462.96;
$cur = $roundedBase * 0.5;
$pen = $roundedBase - $cur;
echo "Current Base: $cur\n";
echo "Pending Base: $pen\n";

