<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>{{ $title ?? 'Payment Receipt' }} - {{ $receiptNumber }}</title>
    <style>
        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 11.5px;
            color: #1e293b;
            margin: 0;
            padding: 15px;
            background: #ffffff;
        }
        .receipt-container {
            width: 100%;
            border: 1.5px solid #cbd5e1;
            border-radius: 8px;
            padding: 24px;
            box-sizing: border-box;
        }
        .header-table {
            width: 100%;
            border-bottom: 2px solid #0f172a;
            padding-bottom: 15px;
            margin-bottom: 18px;
        }
        .header-table td {
            vertical-align: top;
        }
        .company-name {
            font-size: 17px;
            font-weight: bold;
            color: #0f172a;
            margin-bottom: 4px;
        }
        .company-meta {
            font-size: 10.5px;
            color: #64748b;
            line-height: 1.45;
        }
        .receipt-badge-title {
            text-align: right;
        }
        .receipt-title {
            font-size: 19px;
            font-weight: 800;
            letter-spacing: 1px;
            text-transform: uppercase;
            color: {{ $receiptType === 'income' ? '#047857' : '#1d4ed8' }};
            margin: 0 0 6px 0;
        }
        .receipt-number {
            font-size: 12px;
            font-weight: bold;
            color: #334155;
            margin-bottom: 3px;
        }
        .status-badge {
            display: inline-block;
            padding: 4px 10px;
            border-radius: 12px;
            font-size: 10.5px;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            background-color: #dcfce7;
            color: #15803d;
            border: 1px solid #86efac;
            margin-top: 4px;
        }
        .info-grid {
            width: 100%;
            margin-bottom: 20px;
            border-collapse: collapse;
        }
        .info-box {
            width: 48%;
            vertical-align: top;
            padding: 12px;
            border: 1px solid #e2e8f0;
            border-radius: 6px;
            background: #f8fafc;
        }
        .info-box h4 {
            margin: 0 0 8px 0;
            font-size: 11px;
            text-transform: uppercase;
            color: #64748b;
            font-weight: 700;
            border-bottom: 1px dashed #cbd5e1;
            padding-bottom: 4px;
        }
        .info-box p {
            margin: 3px 0;
            line-height: 1.4;
            font-size: 11.5px;
        }
        .info-box strong {
            color: #0f172a;
        }
        table.receipt-table {
            width: 100%;
            border-collapse: collapse;
            margin: 15px 0;
        }
        table.receipt-table th {
            background-color: #0f172a;
            color: #ffffff;
            font-weight: 600;
            text-transform: uppercase;
            font-size: 10.5px;
            letter-spacing: 0.5px;
            padding: 9px 12px;
            text-align: left;
            border: 1px solid #0f172a;
        }
        table.receipt-table td {
            padding: 10px 12px;
            border: 1px solid #e2e8f0;
            font-size: 11.5px;
            vertical-align: middle;
        }
        .text-right {
            text-align: right;
        }
        .text-center {
            text-align: center;
        }
        .amount-highlight {
            font-size: 14px;
            font-weight: bold;
            color: {{ $receiptType === 'income' ? '#047857' : '#1d4ed8' }};
        }
        .totals-table {
            width: 45%;
            margin-left: auto;
            border-collapse: collapse;
            margin-top: 10px;
            margin-bottom: 18px;
        }
        .totals-table td {
            padding: 6px 10px;
            font-size: 11.5px;
            border-bottom: 1px solid #e2e8f0;
        }
        .totals-table .total-row td {
            font-size: 13px;
            font-weight: bold;
            border-top: 2px solid #0f172a;
            border-bottom: 2px solid #0f172a;
            color: #0f172a;
            background: #f8fafc;
        }
        .words-box {
            padding: 10px 14px;
            background: #f1f5f9;
            border-left: 3px solid {{ $receiptType === 'income' ? '#059669' : '#2563eb' }};
            border-radius: 4px;
            margin-bottom: 20px;
            font-size: 11px;
        }
        .words-box strong {
            color: #334155;
        }
        .footer-table {
            width: 100%;
            margin-top: 25px;
            border-top: 1px solid #cbd5e1;
            padding-top: 15px;
        }
        .footer-table td {
            vertical-align: bottom;
        }
        .sign-box {
            text-align: right;
            font-size: 11px;
            line-height: 1.5;
        }
        .bottom-note {
            text-align: center;
            font-size: 9.5px;
            color: #94a3b8;
            margin-top: 18px;
            border-top: 1px dashed #e2e8f0;
            padding-top: 8px;
        }
    </style>
</head>
<body>
    <div class="receipt-container">
        <!-- HEADER -->
        <table class="header-table">
            <tr>
                <td width="60%">
                    @if(!empty($logoBase64))
                        <img src="{{ $logoBase64 }}" alt="Logo" style="height: 40px; margin-bottom: 6px;"><br>
                    @endif
                    <div class="company-name">{{ $company->name ?? 'Corporate Financial Management' }}</div>
                    <div class="company-meta">
                        @if(!empty($company->address))
                            {{ $company->address }}<br>
                        @endif
                        @if(!empty($company->email))
                            Email: {{ $company->email }}
                        @endif
                        @if(!empty($company->currency))
                            | Currency: {{ $company->currency }}
                        @endif
                    </div>
                </td>
                <td width="40%" class="receipt-badge-title">
                    <h1 class="receipt-title">
                        {{ $receiptType === 'income' ? 'PAYMENT RECEIPT' : 'EXPENSE VOUCHER' }}
                    </h1>
                    <div class="receipt-number">Receipt #: {{ $receiptNumber }}</div>
                    <div style="font-size: 11px; color: #64748b;">
                        Date: <strong>{{ $paymentDate ? \Carbon\Carbon::parse($paymentDate)->format('d M Y') : date('d M Y') }}</strong>
                    </div>
                    <div>
                        <span class="status-badge">
                            {{ $statusLabel ?? 'PAID / SETTLED' }}
                        </span>
                    </div>
                </td>
            </tr>
        </table>

        <!-- PARTY & PAYMENT DETAILS -->
        <table class="info-grid">
            <tr>
                <td class="info-box" style="margin-right: 2%;">
                    <h4>{{ $receiptType === 'income' ? 'Received From (Customer / Client)' : 'Paid To (Vendor / Beneficiary)' }}</h4>
                    <p style="font-size: 13px; font-weight: bold; color: #0f172a;">{{ $partyName ?: 'N/A' }}</p>
                    @if(!empty($partyDetails))
                        <p style="color: #475569;">{{ $partyDetails }}</p>
                    @endif
                    <p style="color: #64748b; font-size: 10.5px;">Reference: <strong>{{ $referenceNo }}</strong></p>
                </td>
                <td width="4%"></td>
                <td class="info-box">
                    <h4>Payment Information</h4>
                    <p>Payment Mode: <strong>{{ $paymentMode ?: 'Bank Transfer / Online' }}</strong></p>
                    <p>Payment Date: <strong>{{ $paymentDate ? \Carbon\Carbon::parse($paymentDate)->format('d-m-Y') : date('d-m-Y') }}</strong></p>
                    <p>Recorded By: <strong>{{ $recordedBy ?: 'Admin / Authorized Officer' }}</strong></p>
                    @if(!empty($notes))
                        <p style="color: #475569;">Notes: <em>{{ $notes }}</em></p>
                    @endif
                </td>
            </tr>
        </table>

        <!-- ITEMS / BREAKDOWN TABLE -->
        <table class="receipt-table">
            <thead>
                <tr>
                    <th width="8%" class="text-center">#</th>
                    <th width="50%">Description / Particulars</th>
                    <th width="20%">Category / Type</th>
                    <th width="22%" class="text-right">Amount ({{ $currencySymbol }})</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td class="text-center">1</td>
                    <td>
                        <strong>{{ $itemName }}</strong>
                        @if(!empty($itemSubtitle))
                            <div style="font-size: 10.5px; color: #64748b; margin-top: 2px;">{{ $itemSubtitle }}</div>
                        @endif
                    </td>
                    <td>{{ $categoryName ?: 'General' }}</td>
                    <td class="text-right amount-highlight">
                        {{ $currencySymbol }} {{ number_format($amount, 2) }}
                    </td>
                </tr>
            </tbody>
        </table>

        <!-- TOTALS TABLE -->
        <table class="totals-table">
            @if(isset($taxAmount) && $taxAmount > 0)
            <tr>
                <td>Subtotal:</td>
                <td class="text-right">{{ $currencySymbol }} {{ number_format($amount - $taxAmount, 2) }}</td>
            </tr>
            <tr>
                <td>Tax Applied:</td>
                <td class="text-right">{{ $currencySymbol }} {{ number_format($taxAmount, 2) }}</td>
            </tr>
            @endif
            <tr class="total-row">
                <td>Total Paid Amount:</td>
                <td class="text-right">{{ $currencySymbol }} {{ number_format($amount, 2) }}</td>
            </tr>
        </table>

        <!-- AMOUNT IN WORDS -->
        <div class="words-box">
            <strong>Amount in Words:</strong> {{ $amountInWords ?? 'N/A' }}
        </div>

        <!-- FOOTER & SIGNATURE -->
        <table class="footer-table">
            <tr>
                <td width="60%">
                    <div style="font-size: 10px; color: #64748b; line-height: 1.5;">
                        This is an official system-generated payment receipt.<br>
                        Generated on {{ date('d M Y, h:i A') }}<br>
                        Authorized by: {{ auth()->user()->name ?? 'Administrator' }}
                    </div>
                </td>
                <td width="40%" class="sign-box">
                    <div style="height: 35px;"></div>
                    <strong>Authorized Signatory</strong><br>
                    <span>{{ $company->name ?? 'Corporate Finance' }}</span>
                </td>
            </tr>
        </table>

        <div class="bottom-note">
            Thank you for your transaction. For any inquiries, please contact the accounts department.
        </div>
    </div>
</body>
</html>
