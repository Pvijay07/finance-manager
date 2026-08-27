<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Your Password</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f1f5f9;
            margin: 0;
            padding: 0;
            color: #1e293b;
        }
        .container {
            max-width: 580px;
            margin: 40px auto;
            background: #ffffff;
            border-radius: 16px;
            overflow: hidden;
            box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.05);
            border: 1px solid #e2e8f0;
        }
        .header {
            background: linear-gradient(135deg, #1e293b 0%, #0f172a 100%);
            padding: 30px;
            text-align: center;
        }
        .header h1 {
            color: #ffffff;
            font-size: 24px;
            margin: 0;
            letter-spacing: 0.5px;
        }
        .header p {
            color: #94a3b8;
            font-size: 13px;
            margin: 5px 0 0 0;
            text-transform: uppercase;
            letter-spacing: 1.5px;
            font-weight: 600;
        }
        .content {
            padding: 35px 30px;
        }
        .greeting {
            font-size: 18px;
            font-weight: 600;
            color: #0f172a;
            margin-bottom: 15px;
        }
        .message {
            font-size: 15px;
            line-height: 1.6;
            color: #475569;
            margin-bottom: 25px;
        }
        .button-wrapper {
            text-align: center;
            margin: 35px 0;
        }
        .reset-btn {
            display: inline-block;
            background: linear-gradient(135deg, #4f46e5 0%, #4338ca 100%);
            color: #ffffff !important;
            text-decoration: none;
            padding: 14px 32px;
            font-size: 15px;
            font-weight: 600;
            border-radius: 8px;
            box-shadow: 0 4px 12px rgba(79, 70, 229, 0.3);
        }
        .expiry-note {
            background-color: #f8fafc;
            border-left: 4px solid #4f46e5;
            padding: 12px 16px;
            font-size: 13px;
            color: #64748b;
            margin-bottom: 25px;
            border-radius: 0 8px 8px 0;
        }
        .trouble-link {
            font-size: 12px;
            color: #94a3b8;
            word-break: break-all;
            line-height: 1.5;
            padding-top: 20px;
            border-top: 1px solid #f1f5f9;
        }
        .trouble-link a {
            color: #4f46e5;
            text-decoration: underline;
        }
        .footer {
            background-color: #f8fafc;
            padding: 20px 30px;
            text-align: center;
            font-size: 12px;
            color: #94a3b8;
            border-top: 1px solid #e2e8f0;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>FinCorp</h1>
            <p>Financial Management Platform</p>
        </div>
        <div class="content">
            <div class="greeting">Hello {{ $user->name ?? 'there' }},</div>
            <div class="message">
                We received a request to reset the password for your FinCorp account. Click the button below to securely choose a new password:
            </div>
            
            <div class="button-wrapper">
                <a href="{{ $resetUrl }}" class="reset-btn" target="_blank">Reset Password</a>
            </div>

            <div class="expiry-note">
                <strong>Important:</strong> This password reset link will expire in <strong>60 minutes</strong>. If you did not request a password reset, no further action is required and your account remains completely secure.
            </div>

            <div class="trouble-link">
                If you're having trouble clicking the "Reset Password" button, copy and paste the following URL into your web browser:<br>
                <a href="{{ $resetUrl }}">{{ $resetUrl }}</a>
            </div>
        </div>
        <div class="footer">
            &copy; {{ date('Y') }} FinCorp Global Solutions. All rights reserved.<br>
            This is an automated system notification. Please do not reply directly to this email.
        </div>
    </div>
</body>
</html>
