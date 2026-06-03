<!DOCTYPE html>
<html>
<head>
    <title>GST Collected Report - {{ $period }}</title>
    <style>
        @page { margin: 20px; }
        body { font-family: 'Helvetica', sans-serif; font-size: 10px; color: #333; line-height: 1.4; }
        .container { width: 100%; }
        .header { text-align: center; margin-bottom: 30px; border-bottom: 2px solid #3498db; padding-bottom: 10px; }
        .header h1 { font-size: 22px; color: #2980b9; margin: 0; text-transform: uppercase; }
        .header p { margin: 5px 0; color: #7f8c8d; font-size: 12px; }
        
        .report-info { margin-bottom: 20px; display: flex; justify-content: space-between; }
        .info-box { background: #f9f9f9; padding: 10px; border-radius: 4px; border: 1px solid #eee; }
        
        table { width: 100%; border-collapse: collapse; margin-top: 10px; table-layout: fixed; }
        th { background-color: #3498db; color: white; font-weight: bold; text-transform: uppercase; font-size: 9px; padding: 8px 5px; text-align: left; }
        td { border: 1px solid #eee; padding: 7px 5px; word-wrap: break-word; vertical-align: top; }
        tr:nth-child(even) { background-color: #fcfcfc; }
        
        .text-end { text-align: right; }
        .text-center { text-align: center; }
        .bold { font-weight: bold; }
        
        .status-badge { padding: 2px 6px; border-radius: 10px; font-size: 8px; font-weight: bold; text-transform: uppercase; }
        .status-received { background: #d4edda; color: #155724; }
        .status-pending { background: #fff3cd; color: #856404; }
        
        .totals-section { margin-top: 20px; float: right; width: 300px; }
        .total-row { display: flex; justify-content: space-between; padding: 5px 0; border-bottom: 1px solid #eee; }
        .grand-total { border-top: 2px solid #3498db; font-size: 14px; color: #2980b9; margin-top: 5px; padding-top: 5px; }
        
        .footer { position: fixed; bottom: 0; width: 100%; text-align: center; font-size: 8px; color: #95a5a6; border-top: 1px solid #eee; padding-top: 5px; }
        .signature-space { margin-top: 50px; display: flex; justify-content: space-between; }
        .sig-box { width: 200px; border-top: 1px solid #333; text-align: center; padding-top: 5px; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>GST Collected (Output Tax) Report</h1>
            <p>Tax Period: {{ date('F Y', strtotime($period . '-01')) }}</p>
        </div>

        <div class="report-info">
            <div class="info-box">
                <strong>Generated On:</strong> {{ date('d M Y, h:i A') }}<br>
                <strong>Tax Type Filter:</strong> {{ strtoupper($taxType) }}
            </div>
        </div>

        <table>
            <thead>
                <tr>
                    <th style="width: 55px;">Date</th>
                    <th style="width: 90px;">Company</th>
                    <th style="width: 130px;">Client Detail</th>
                    <th style="width: 70px;">Invoice No</th>
                    <th style="width: 40px;" class="text-center">Tax %</th>
                    <th style="width: 80px;" class="text-end">Taxable Value</th>
                    <th style="width: 80px;" class="text-end">GST Amount</th>
                    <th style="width: 60px;" class="text-center">Status</th>
                </tr>
            </thead>
            <tbody>
                @php 
                    $totalTaxable = 0; 
                    $totalCGST = 0;
                    $totalSGST = 0;
                    $totalIGST = 0;
                    $totalOverallGst = 0;
                @endphp
                @forelse ($incomes as $income)
                    @foreach ($income->taxes as $tax)
                        @if ($tax->tax_type !== 'gst' && $tax->tax_type !== 'cgst' && $tax->tax_type !== 'sgst' && $tax->tax_type !== 'igst') @continue @endif
                        @if ($taxType !== 'all' && $tax->tax_type !== $taxType) @continue @endif
                        
                        @php 
                            $totalTaxable += $income->amount; 
                            $totalOverallGst += $tax->tax_amount;
                        @endphp
                        <tr>
                            <td>{{ date('d-m-Y', strtotime($income->income_date ?? $income->created_at)) }}</td>
                            <td class="bold">{{ $income->company->name ?? 'N/A' }}<br><small style="color:#777">{{ $income->company->gstin ?? '' }}</small></td>
                            <td>{{ $income->party_name ?: ($income->client_name ?: $income->description) }}</td>
                            <td>{{ $income->invoice_number ?: 'N/A' }}</td>
                            <td class="text-center">{{ $tax->tax_percentage }}%</td>
                            <td class="text-end">₹ {{ number_format($income->amount, 2) }}</td>
                            <td class="text-end bold">₹ {{ number_format($tax->tax_amount, 2) }}</td>
                            <td class="text-center">
                                <span class="status-badge {{ $income->status === 'received' ? 'status-received' : 'status-pending' }}">
                                    {{ $income->status }}
                                </span>
                            </td>
                        </tr>
                    @endforeach
                @empty
                    <tr>
                        <td colspan="8" class="text-center" style="padding: 20px;">No tax records found for this period.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>

        <div class="totals-section">
            <div class="total-row">
                <span>Total Taxable Value:</span>
                <span class="bold">₹ {{ number_format($totalTaxable, 2) }}</span>
            </div>
            <div class="total-row">
                <span>Total GST Collected:</span>
                <span class="bold">₹ {{ number_format($totalOverallGst, 2) }}</span>
            </div>
            <div class="total-row grand-total">
                <span>Total Value (Incl. Tax):</span>
                <span class="bold">₹ {{ number_format($totalTaxable + $totalOverallGst, 2) }}</span>
            </div>
        </div>

        <div style="clear: both;"></div>

        <div class="signature-space">
            <div class="sig-box">Verified By (Accountant)</div>
            <div class="sig-box">Authorized Signatory</div>
        </div>

        <div class="footer">
            Generated by Finance Management System | Page 1 of 1
        </div>
    </div>

    <script>
        window.onload = function() {
            setTimeout(function() {
                window.print();
            }, 500);
        }
    </script>
</body>
</html>
