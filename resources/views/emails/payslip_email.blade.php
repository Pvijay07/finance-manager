<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            color: #333;
            margin: 0;
            padding: 20px;
        }
        .container {
            background-color: #fff;
            padding: 20px;
            border-radius: 5px;
            max-width: 600px;
            margin: 0 auto;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
        .header {
            text-align: center;
            border-bottom: 1px solid #ddd;
            padding-bottom: 10px;
            margin-bottom: 20px;
        }
        .footer {
            margin-top: 30px;
            font-size: 12px;
            color: #777;
            text-align: center;
            border-top: 1px solid #ddd;
            padding-top: 10px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h2>{{ $item->sheet->company->name ?? 'Company' }}</h2>
        </div>
        <p>Dear {{ $employee->full_name }},</p>
        <p>Please find attached your payslip for the month of <strong>{{ date('F Y', strtotime($item->sheet->month_year)) }}</strong>.</p>
        <p>If you have any questions or concerns regarding your payslip, please reach out to your manager or HR department.</p>
        <br>
        <p>Best regards,</p>
        <p><strong>{{ $item->sheet->company->name ?? 'Company' }}</strong></p>
        <div class="footer">
            This is an automated email. Please do not reply directly to this message.
        </div>
    </div>
</body>
</html>
