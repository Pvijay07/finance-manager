<!DOCTYPE html>
<html>
<head>
    <title>TDS Export - {{ $type }}</title>
    <style>
        body { font-family: sans-serif; font-size: 11px; color: #333; }
        .container { padding: 20px; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; table-layout: fixed; }
        th, td { border: 1px solid #ddd; padding: 6px 4px; text-align: left; word-wrap: break-word; }
        th { background-color: #f8f9fa; color: #333; font-weight: bold; text-transform: uppercase; font-size: 10px; }
        .header { text-align: center; border-bottom: 2px solid #444; padding-bottom: 10px; margin-bottom: 20px; }
        .header h1 { font-size: 20px; margin: 0; color: #2c3e50; }
        .header p { margin: 5px 0; color: #7f8c8d; }
        .summary-box { margin-bottom: 20px; border: 1px solid #eee; padding: 10px; background-color: #fdfdfd; }
        .summary-title { font-weight: bold; margin-bottom: 10px; border-bottom: 1px solid #eee; padding-bottom: 5px; }
        .summary-item { display: inline-block; width: 32%; }
        .summary-label { color: #666; }
        .summary-value { font-weight: bold; font-size: 13px; color: #2980b9; }
        .footer { position: fixed; bottom: 0; width: 100%; text-align: left; font-size: 9px; color: #999; border-top: 1px solid #eee; padding-top: 5px; }
        .text-end { text-align: right; }
        .status-badge { padding: 2px 5px; border-radius: 3px; font-size: 9px; color: #fff; }
        .bg-success { background-color: #27ae60; }
        .bg-warning { background-color: #f39c12; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>TDS {{ $type }} Report</h1>
            <p>Financial Period: {{ $period }} | Generated on: {{ date('d-m-Y H:i') }}</p>
        </div>

        @php
            $totalTaxable = 0;
            $totalTds = 0;
            foreach($data as $tax) {
                $t = $tax->taxable;
                $totalTaxable += ($t->subtotal ?? ($t->amount ?? ($t->total ?? 0)));
                $totalTds += ($tax->tax_amount ?? 0);
            }
        @endphp

        <div class="summary-box">
            <div class="summary-title">REPORT SUMMARY</div>
            <div class="summary-item">
                <span class="summary-label">Total Records:</span><br>
                <span class="summary-value">{{ count($data) }}</span>
            </div>
            <div class="summary-item">
                <span class="summary-label">Total Taxable Amount:</span><br>
                <span class="summary-value">₹{{ number_format($totalTaxable, 2) }}</span>
            </div>
            <div class="summary-item">
                <span class="summary-label">Total TDS Amount:</span><br>
                <span class="summary-value">₹{{ number_format($totalTds, 2) }}</span>
            </div>
        </div>

        <table>
            <thead>
                <tr>
                    <th style="width: 30px;">#</th>
                    <th style="width: 100px;">Company</th>
                    <th>{{ $type == 'Income' ? 'Client' : 'Vendor' }}</th>
                    <th style="width: 70px;">Date</th>
                    @if($type == 'Expense')
                        <th style="width: 80px;">Bill No</th>
                    @endif
                    <th style="width: 90px;" class="text-end">Taxable (₹)</th>
                    <th style="width: 80px;" class="text-end">TDS (₹)</th>
                    <th style="width: 70px;">Status</th>
                </tr>
            </thead>
            <tbody>
                @foreach($data as $index => $tax)
                    @php $t = $tax->taxable; @endphp
                    <tr>
                        <td>{{ $index + 1 }}</td>
                        <td>{{ $t->company->name ?? 'N/A' }}</td>
                        <td>{{ $type == 'Income' ? ($t->client->name ?? ($t->customer_name ?? 'N/A')) : ($t->vendor->name ?? 'N/A') }}</td>
                        <td>{{ date('d-m-Y', strtotime($t->issue_date ?? ($t->date ?? ($t->created_at ?? $tax->created_at)))) }}</td>
                        @if($type == 'Expense')
                            <td>{{ $t->bill_number ?? ($t->bill_no ?? 'N/A') }}</td>
                        @endif
                        <td class="text-end">{{ number_format($t->subtotal ?? ($t->amount ?? ($t->total ?? 0)), 2) }}</td>
                        <td class="text-end" style="font-weight: bold;">{{ number_format($tax->tax_amount ?? 0, 2) }}</td>
                        <td>
                            <span class="status-badge {{ strtolower($tax->payment_status) == 'received' || strtolower($tax->payment_status) == 'paid' ? 'bg-success' : 'bg-warning' }}">
                                {{ ucfirst(str_replace('_', ' ', $tax->payment_status ?? 'Pending')) }}
                            </span>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        <div style="margin-top: 50px;">
            <div style="float: right; width: 200px; text-align: center; border-top: 1px solid #333; padding-top: 10px;">
                Authorized Signatory
            </div>
        </div>
    </div>

    <div class="footer">
        Confidentially generated for {{ auth()->user()->name }} | System ID: {{ auth()->id() }}
    </div>

    <script>
        window.onload = function() { window.print(); }
    </script>
</body>
</html>
