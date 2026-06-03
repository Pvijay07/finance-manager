<?php

function convertNumberToWords($number)
{
    if ($number < 21) {
        $words = ['Zero', 'One', 'Two', 'Three', 'Four', 'Five', 'Six', 'Seven', 'Eight', 'Nine', 'Ten', 'Eleven', 'Twelve', 'Thirteen', 'Fourteen', 'Fifteen', 'Sixteen', 'Seventeen', 'Eighteen', 'Nineteen', 'Twenty'];
        return $words[$number] ?? '';
    }

    // For numbers above 20, return simple representation
    return "{$number}";
}
?>

<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <title>{{ strtoupper($invoice->type) }} - {{ $invoice->invoice_number }}</title>

    <style>
        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 11px;
            color: #000;
            margin: 0;
            padding: 10px;
        }

        .container {
            width: 100%;
            border: 2px solid #000;
            padding: 15px;
            box-sizing: border-box;
        }

        .header-table {
            width: 100%;
            border-bottom: 1px solid #000;
            margin-bottom: 15px;
            padding-bottom: 10px;
        }

        .header-table td {
            vertical-align: top;
            padding: 5px 10px;
        }

        h2 {
            margin: 0 0 10px 0;
            text-align: center;
            text-transform: uppercase;
            font-size: 16px;
        }

        .meta p {
            margin: 3px 0;
            line-height: 1.3;
        }

        .section {
            margin-top: 15px;
            padding: 10px;
            border: 1px solid #000;
        }

        .bill-to-section {
            margin: 15px 0;
            border: 1px solid #000;
            padding: 10px;
        }

        .bill-to-title {
            margin: 0;
            font-size: 12px;
            font-weight: bold;
        }

        a:link,
        a:visited,
        a:hover,
        a:active {
            text-decoration: none;
            color: inherit;
        }


        .user-name {
            margin: 10px 0 5px 0;
            font-weight: bold;
        }

        .user-address {
            margin: 0;
            line-height: 1.4;
        }

        table.items {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
        }

        table.items th,
        table.items td {
            border: 1px solid #000;
            padding: 8px 5px;
        }

        table.items th {
            text-align: center;
            background-color: #f0f0f0;
            font-weight: bold;
        }

        .right {
            text-align: right;
        }

        .center {
            text-align: center;
        }

        .terms {
            font-size: 10px;
            margin: 10px 0 0 0;
            padding: 10px 0 0 0;
            border-top: 1px dashed #666;
        }

        .terms ol {
            margin: 5px 0;
            padding-left: 20px;
        }

        .terms li {
            margin-bottom: 3px;
        }

        .signature {
            margin-top: 20px;
            text-align: right;
            padding: 10px 20px;
            border-top: 1px solid #000;
        }

        .footer {
            text-align: center;
            margin-top: 20px;
            font-weight: bold;
            font-size: 12px;
            padding: 10px;
            border-top: 1px solid #000;
        }

        .declaration {
            margin: 0;
            padding: 0 0 10px 0;
            border-bottom: 1px dashed #666;
        }

        .bank-details h4 {
            margin: 0 0 10px 0;
            text-align: center;
            text-transform: uppercase;
        }

        .bank-details p {
            margin: 3px 0;
        }

        .contact-section {
            margin-top: 10px;
            padding-top: 10px;
            border-top: 1px dashed #666;
        }

        .project-details {
            margin: 5px 0 15px 0;
        }

        .project-details p {
            margin: 2px 0;
        }

        /* Invoice type specific styling */
        .invoice-type-title {
            text-transform: uppercase;
            color: #000;
            letter-spacing: 1px;
        }

        .company-logo {
            height: 50px;
            margin-bottom: 5px;
        }

        .company-info {
            line-height: 1.4;
        }

        .company-info strong {
            display: block;
            margin-bottom: 2px;
        }
    </style>
</head>

<body>
    <div class="container">

        <!-- HEADER -->
        <table class="header-table">
            <tr>
                <td width="60%">
                    <div class="company-info">
                        <img src="{{ $logoBase64 }}" alt="Company Logo" class="company-logo">

                        <strong>INFASTA SOFT SOLUTIONS PVT. LTD</strong><br>
                        3-6-327&328, Office No: 301 & 302, 3rd Floor, Doshi Chambers<br>
                        Basheerbagh, Hyderabad. 500029<br>
                        S T Reg. No.- AADCI9096ASD001<br>
                        GSTIN: 36AADCI9096A2ZH<br>
                        <a href="https://www.infasta.com">www.infasta.com</a>
                    </div>
                </td>

                <td width="40%">
                    <h2 class="invoice-type-title">
                        {{ $invoice->type === 'invoice' ? 'TAX INVOICE' : 'PROFORMA INVOICE' }}
                    </h2>

                    <div class="project-details">
                        <p><strong>Invoice No:</strong> {{ $invoice->invoice_number }}</p>
                        <p><strong>Dated:</strong> {{ \Carbon\Carbon::parse($invoice->issue_date)->format('d.m.Y') }}
                        </p>
                        <p><strong>Project Delivery Note:</strong> {{ $invoice->project_note ?? 'N/A' }}</p>
                        <p><strong>Buyers Ordered:</strong> Online</p>
                        <p><strong>Project Commencement Date:</strong>
                            {{ $invoice->start_date ? \Carbon\Carbon::parse($invoice->start_date)->format('d.m.Y') : 'N/A' }}
                        </p>
                        <p><strong>Terms of Delivery:</strong> {{ $invoice->delivery_terms ?? 'Online' }}</p>
                    </div>
                </td>
            </tr>
        </table>

        <!-- BILL TO -->
        <div class="bill-to-section">
            <p class="bill-to-title">BILL TO:</p>
            <p class="user-name">{{ ucfirst($clientDetails['name'] ?? 'N/A') }}</p>
            <p class="user-address">{{ $clientDetails['billing_address'] ?? 'N/A' }}</p>
            @if (isset($clientDetails['gstin']) && $clientDetails['gstin'])
                <p><strong>GSTIN:</strong> {{ $clientDetails['gstin'] }}</p>
            @endif
        </div>

        <!-- ITEMS TABLE -->
        <table class="items">
            <thead>
                <tr>
                    <th width="10%">S.no</th>
                    <th width="70%">Description</th>
                    <th width="20%">AMOUNT ({{ $invoice->currency_symbol ?? '₹' }})</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($lineItems as $i => $item)
                    <tr>
                        <td class="center">{{ $i + 1 }}</td>
                        <td>{{ $item['description'] }}</td>
                        <td class="right">{{ $invoice->currency_symbol ?? '₹' }} {{ number_format($item['amount'], 2) }}</td>
                    </tr>
                @endforeach

                <!-- Subtotal Row -->
                <tr>
                    <td colspan="2" class="right" style="text-align: right; font-weight: bold;">Subtotal:</td>
                    <td class="right" style="font-weight: bold;">{{ $invoice->currency_symbol ?? '₹' }} {{ number_format($invoice->subtotal, 2) }}</td>
                </tr>

                <!-- GST Amount Row -->
                @php
                    // Calculate GST total from taxes array
                    $gstTotal = 0;
                    if (isset($invoice->taxes) && count($invoice->taxes) > 0) {
                        foreach ($invoice->taxes as $tax) {
                            if ($tax->tax_type === 'gst') {
                                $gstTotal += $tax->tax_amount;
                            }
                        }
                    }
                @endphp

                @if ($gstTotal > 0)
                    <tr>
                        <td colspan="2" class="right" style="text-align: right; font-weight: bold;">GST:</td>
                        <td class="right" style="font-weight: bold;">{{ $invoice->currency_symbol ?? '₹' }} {{ number_format($gstTotal, 2) }}</td>
                    </tr>
                @endif

                <!-- Total Row (Subtotal + GST) -->
                <tr>
                    <td colspan="2" class="right" style="text-align: right; font-weight: bold;">Total:</td>
                    <td class="right" style="font-weight: bold;">{{ $invoice->currency_symbol ?? '₹' }}
                        {{ number_format($invoice->subtotal + $gstTotal, 2) }}</td>
                </tr>
            </tbody>
        </table>

        <!-- TOTAL IN WORDS -->
        <div class="section">
            @php
                // Calculate total amount for words (subtotal + GST)
                $totalForWords = $invoice->subtotal + $gstTotal;
            @endphp
            <strong>Total in Words:</strong>
            {{ $amountInWords ?? (convertNumberToWords($totalForWords) . ' only') }}
        </div>

        <!-- DECLARATION & TERMS -->
        <div class="section">
            <p class="declaration">
                <strong>Declaration:</strong> We declare that this invoice shows the actual price of the services
                described and that all particulars are true and correct.
            </p>

            <div class="terms">
                <strong>Terms & Conditions:</strong>
                <ol>
                    <li>Please make sure that full payment is credited to our Bank account.</li>
                    <li>Payment should be made within 3 days of receiving this invoice.</li>
                    <li>Accepted payment modes: Bank Transfer / PayPal only.</li>
                    <li>Late payment penalty of 4% will apply for every 3 days delay.</li>
                    <li>Source files will be delivered only after receiving full payment.</li>
                    <li>Please send payment acknowledgment promptly to avoid communication issues.</li>
                </ol>
            </div>

            <!-- SIGNATURE -->
            <div class="signature">
                <strong>Authorized Signatory</strong><br>
                <br>
                Sravanthi P<br>
                (Accounts Manager)<br>
                Infasta Soft Solutions Pvt. Ltd.
            </div>
        </div>

        <!-- BANK DETAILS -->
        <div class="section">
            <div class="bank-details">
                <h4>INFASTA SOFT SOLUTIONS PVT. LTD. BANK ACCOUNT DETAILS:</h4>
                <p><strong>Bank Account Name:</strong> Infasta Soft Solutions Private Limited</p>
                <p><strong>Bank Name & Branch:</strong> Axis Bank, West Marredpally, Secunderabad</p>
                <p><strong>Bank Account Number:</strong> 915020056712873</p>
                <p><strong>RTGS/NEFT IFSC:</strong> UTIB0001319</p>
                <p><strong>Swift Code:</strong> AXISINBB068</p>
            </div>

            <div class="contact-section">
                <p><strong>If you have any questions about this invoice, please contact:</strong></p>
                <p><strong>Accounts Department:</strong> Sravanthi P</p>
                <p><strong>Phone:</strong> +91 9573492409</p>
                <p><strong>Email:</strong> accounts@infasta.com</p>
            </div>
        </div>

        <!-- FOOTER -->
        <div class="footer">
            THANK YOU FOR YOUR BUSINESS!
        </div>

    </div>
</body>

</html>
