<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $subject ?? 'Invoice' }}</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            line-height: 1.6;
            color: #333;
            margin: 0;
            padding: 0;
            background-color: #f5f5f5;
        }

        .container {
            max-width: 600px;
            margin: 20px auto;
            background: white;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }

        .header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 30px 20px;
            text-align: center;
        }

        .content {
            padding: 30px;
        }

        .footer {
            padding: 20px;
            text-align: center;
            color: #666;
            font-size: 12px;
            background: #f8f9fa;
            border-top: 1px solid #e0e0e0;
        }

        .invoice-card {
            background: white;
            padding: 20px;
            border-radius: 8px;
            margin: 20px 0;
            border: 1px solid #e0e0e0;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
        }

        .message-card {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 8px;
            margin: 20px 0;
            border-left: 4px solid #007bff;
        }

        .info-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 10px;
            padding-bottom: 10px;
            border-bottom: 1px solid #f0f0f0;
        }

        .info-row:last-child {
            border-bottom: none;
            margin-bottom: 0;
            padding-bottom: 0;
        }

        .info-label {
            font-weight: 600;
            color: #555;
        }

        .info-value {
            color: #222;
        }

        .btn {
            display: inline-block;
            padding: 12px 30px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            text-decoration: none;
            border-radius: 5px;
            margin: 10px 0;
        }
    </style>
</head>

<body>
    <div class="container">
        <div class="header">
            <h1 style="margin: 0; font-size: 28px;">{{ $invoice->company->name ?? 'Company' }}</h1>
            <p style="margin: 10px 0 0; opacity: 0.9;">Invoice Notification</p>
        </div>

        <div class="content">
            @php
                // Safely extract client name
                $clientName = 'Customer';
                if (isset($client_details) && is_array($client_details)) {
                    $clientName = $client_details['name'] ?? 'Customer';
                } elseif (isset($invoice->client_details)) {
                    if (is_string($invoice->client_details)) {
                        $decoded = json_decode($invoice->client_details, true);
                        $clientName = $decoded['name'] ?? 'Customer';
                    } elseif (is_array($invoice->client_details)) {
                        $clientName = $invoice->client_details['name'] ?? 'Customer';
                    }
                }
            @endphp

            <h2 style="color: #333; margin-bottom: 10px;">Dear {{ $clientName }},</h2>
            <p style="color: #666; margin-bottom: 25px;">Please find your invoice details below:</p>


            @if (!empty($custom_message))
                <div class="message-card">
                    <h4 style="color: #333; margin-top: 0;">Message:</h4>
                    <div style="color: #444; line-height: 1.8;">
                        {!! nl2br(e($custom_message)) !!}
                    </div>
                </div>
            @endif

            <div style="text-align: center; margin: 30px 0;">
                <p style="color: #666; margin-bottom: 20px;">The invoice PDF is attached to this email.</p>
                <p style="color: #666;">For any queries regarding this invoice, please contact our support team.</p>
            </div>
        </div>

        <div class="footer">
            <p style="margin: 0 0 10px;">This is an automated email from {{ $invoice->company->name ?? 'Our Company' }}
            </p>
            <p style="margin: 0; font-size: 11px; color: #999;">
                © {{ date('Y') }} {{ $invoice->company->name ?? '' }}. All rights reserved.
            </p>
        </div>
    </div>
</body>

</html>
