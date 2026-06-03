<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Payslip - {{ $item->employee->full_name }}</title>
    <style>
        body {
            font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
            font-size: 12px;
            color: #333;
            margin: 0;
            padding: 20px;
        }
        .header {
            text-align: center;
            border-bottom: 2px solid #333;
            padding-bottom: 10px;
            margin-bottom: 20px;
        }
        .header h1 {
            margin: 0;
            font-size: 24px;
            color: #1e1b4b;
        }
        .header p {
            margin: 5px 0 0;
            font-size: 14px;
            color: #666;
        }
        .title {
            text-align: center;
            font-size: 18px;
            font-weight: bold;
            margin-bottom: 20px;
            text-transform: uppercase;
        }
        .info-table {
            width: 100%;
            margin-bottom: 20px;
            border-collapse: collapse;
        }
        .info-table td {
            padding: 5px;
            vertical-align: top;
        }
        .info-table .label {
            font-weight: bold;
            width: 15%;
            color: #555;
        }
        .info-table .value {
            width: 35%;
        }
        .salary-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        .salary-table th, .salary-table td {
            border: 1px solid #ccc;
            padding: 8px;
        }
        .salary-table th {
            background-color: #f8fafc;
            text-align: left;
            font-weight: bold;
        }
        .salary-table .text-right {
            text-align: right;
        }
        .totals-row td {
            font-weight: bold;
            background-color: #f1f5f9;
        }
        .net-pay-box {
            text-align: center;
            margin-top: 30px;
            padding: 15px;
            background-color: #f8fafc;
            border: 1px solid #ccc;
        }
        .net-pay-box h2 {
            margin: 0;
            font-size: 20px;
            color: #10b981;
        }
        .footer {
            margin-top: 50px;
            text-align: center;
            font-size: 10px;
            color: #999;
            border-top: 1px solid #eee;
            padding-top: 10px;
        }
    </style>
</head>
<body>

    @php
        $gross = $item->basic + $item->hra + $item->allowance + $item->incentive + $item->bonus + $item->ot_amount;
        $deds = $item->pf + $item->esic + $item->tds + $item->advance_rec + $item->other_ded;
        $net = $gross - $deds;
    @endphp

    <div class="header">
        <h1>{{ $item->sheet->company->name ?? 'Company Name' }}</h1>
        <p>Payslip for the month of <strong>{{ date('F Y', strtotime($item->sheet->month_year)) }}</strong></p>
    </div>

    <div class="title">Payslip</div>

    <table class="info-table">
        <tr>
            <td class="label">Employee Name:</td>
            <td class="value">{{ $item->employee->full_name }}</td>
            <td class="label">Employee ID:</td>
            <td class="value">{{ $item->employee->emp_id ?? 'N/A' }}</td>
        </tr>
        <tr>
            <td class="label">Department:</td>
            <td class="value">{{ $item->employee->department ?? 'N/A' }}</td>
            <td class="label">Designation:</td>
            <td class="value">{{ $item->employee->role ?? 'N/A' }}</td>
        </tr>
        <tr>
            <td class="label">Bank A/c:</td>
            <td class="value">{{ $item->employee->bank_account ?? 'N/A' }}</td>
            <td class="label">PAN:</td>
            <td class="value">{{ $item->employee->pan ?? 'N/A' }}</td>
        </tr>
        <tr>
            <td class="label">UAN:</td>
            <td class="value">{{ $item->employee->uan ?? 'N/A' }}</td>
            <td class="label">Total Days:</td>
            <td class="value">{{ $item->present_days }}</td>
        </tr>
    </table>

    <table class="salary-table">
        <thead>
            <tr>
                <th style="width: 50%;">Earnings</th>
                <th style="width: 50%;">Deductions</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td style="padding: 0; border: none; vertical-align: top;">
                    <table style="width: 100%; border-collapse: collapse;">
                        <tr><td style="border: none; border-bottom: 1px solid #eee; padding: 6px;">Basic</td><td style="border: none; border-bottom: 1px solid #eee; padding: 6px;" class="text-right">{{ number_format($item->basic, 2) }}</td></tr>
                        <tr><td style="border: none; border-bottom: 1px solid #eee; padding: 6px;">HRA</td><td style="border: none; border-bottom: 1px solid #eee; padding: 6px;" class="text-right">{{ number_format($item->hra, 2) }}</td></tr>
                        <tr><td style="border: none; border-bottom: 1px solid #eee; padding: 6px;">Allowances</td><td style="border: none; border-bottom: 1px solid #eee; padding: 6px;" class="text-right">{{ number_format($item->allowance, 2) }}</td></tr>
                        <tr><td style="border: none; border-bottom: 1px solid #eee; padding: 6px;">Incentive</td><td style="border: none; border-bottom: 1px solid #eee; padding: 6px;" class="text-right">{{ number_format($item->incentive, 2) }}</td></tr>
                        <tr><td style="border: none; border-bottom: 1px solid #eee; padding: 6px;">Bonus</td><td style="border: none; border-bottom: 1px solid #eee; padding: 6px;" class="text-right">{{ number_format($item->bonus, 2) }}</td></tr>
                        <tr><td style="border: none; border-bottom: 1px solid #eee; padding: 6px;">Overtime (OT)</td><td style="border: none; border-bottom: 1px solid #eee; padding: 6px;" class="text-right">{{ number_format($item->ot_amount, 2) }}</td></tr>
                    </table>
                </td>
                <td style="padding: 0; border: none; vertical-align: top;">
                    <table style="width: 100%; border-collapse: collapse;">
                        <tr><td style="border: none; border-bottom: 1px solid #eee; padding: 6px;">PF</td><td style="border: none; border-bottom: 1px solid #eee; padding: 6px;" class="text-right">{{ number_format($item->pf, 2) }}</td></tr>
                        <tr><td style="border: none; border-bottom: 1px solid #eee; padding: 6px;">ESIC</td><td style="border: none; border-bottom: 1px solid #eee; padding: 6px;" class="text-right">{{ number_format($item->esic, 2) }}</td></tr>
                        <tr><td style="border: none; border-bottom: 1px solid #eee; padding: 6px;">TDS</td><td style="border: none; border-bottom: 1px solid #eee; padding: 6px;" class="text-right">{{ number_format($item->tds, 2) }}</td></tr>
                        <tr><td style="border: none; border-bottom: 1px solid #eee; padding: 6px;">Advance Recovery</td><td style="border: none; border-bottom: 1px solid #eee; padding: 6px;" class="text-right">{{ number_format($item->advance_rec, 2) }}</td></tr>
                        <tr><td style="border: none; border-bottom: 1px solid #eee; padding: 6px;">Other Deductions</td><td style="border: none; border-bottom: 1px solid #eee; padding: 6px;" class="text-right">{{ number_format($item->other_ded, 2) }}</td></tr>
                        <tr><td style="border: none; border-bottom: 1px solid #eee; padding: 6px;">&nbsp;</td><td style="border: none; border-bottom: 1px solid #eee; padding: 6px;" class="text-right"></td></tr>
                    </table>
                </td>
            </tr>
            <tr class="totals-row">
                <td style="padding: 0; border: none;">
                    <table style="width: 100%;">
                        <tr><td style="border: none; padding: 8px;">Total Earnings</td><td style="border: none; padding: 8px;" class="text-right">₹{{ number_format($gross, 2) }}</td></tr>
                    </table>
                </td>
                <td style="padding: 0; border: none;">
                    <table style="width: 100%;">
                        <tr><td style="border: none; padding: 8px;">Total Deductions</td><td style="border: none; padding: 8px;" class="text-right">₹{{ number_format($deds, 2) }}</td></tr>
                    </table>
                </td>
            </tr>
        </tbody>
    </table>

    <div class="net-pay-box">
        <p style="margin: 0 0 5px 0; color: #555; font-weight: bold;">Net Payable</p>
        <h2>₹{{ number_format($net, 2) }}</h2>
    </div>

    <div class="footer">
        <p>This is a computer-generated document. No signature is required.</p>
    </div>

</body>
</html>
