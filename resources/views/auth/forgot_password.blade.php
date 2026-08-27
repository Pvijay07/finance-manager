<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Finance Manager - Forgot Password</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary: #3498db;
            --primary-dark: #2980b9;
            --secondary: #2c3e50;
            --success: #27ae60;
            --warning: #f39c12;
            --danger: #e74c3c;
            --light: #ecf0f1;
            --dark: #2c3e50;
            --gray: #95a5a6;
            --border: #bdc3c7;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }

        .forgot-container {
            display: flex;
            max-width: 950px;
            width: 100%;
            background: white;
            border-radius: 15px;
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.15);
            overflow: hidden;
            min-height: 520px;
        }

        .forgot-left {
            flex: 1;
            background: linear-gradient(135deg, var(--primary) 0%, var(--primary-dark) 100%);
            color: white;
            padding: 50px;
            display: flex;
            flex-direction: column;
            justify-content: center;
            position: relative;
            overflow: hidden;
        }

        .forgot-left::before {
            content: '';
            position: absolute;
            top: -50%;
            right: -50%;
            width: 100%;
            height: 200%;
            background: rgba(255, 255, 255, 0.1);
            transform: rotate(30deg);
        }

        .logo {
            display: flex;
            align-items: center;
            gap: 15px;
            margin-bottom: 30px;
            z-index: 1;
            position: relative;
        }

        .logo-icon {
            font-size: 2.2rem;
            background: rgba(255, 255, 255, 0.2);
            padding: 14px;
            border-radius: 12px;
            backdrop-filter: blur(10px);
        }

        .logo-text {
            font-size: 1.8rem;
            font-weight: 700;
        }

        .welcome-text {
            font-size: 2rem;
            font-weight: 300;
            margin-bottom: 15px;
            line-height: 1.3;
            z-index: 1;
            position: relative;
        }

        .info-desc {
            font-size: 1rem;
            opacity: 0.9;
            line-height: 1.6;
            z-index: 1;
            position: relative;
        }

        .forgot-right {
            flex: 1.1;
            padding: 50px;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }

        .forgot-header {
            text-align: center;
            margin-bottom: 30px;
        }

        .forgot-icon-wrap {
            width: 60px;
            height: 60px;
            background: #eff6ff;
            color: var(--primary);
            border-radius: 50%;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 1.6rem;
            margin-bottom: 15px;
        }

        .forgot-title {
            font-size: 1.8rem;
            color: var(--secondary);
            margin-bottom: 8px;
            font-weight: 700;
        }

        .forgot-subtitle {
            color: #64748b;
            font-size: 0.95rem;
            line-height: 1.5;
        }

        .form-group {
            margin-bottom: 25px;
        }

        .form-label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: var(--secondary);
            font-size: 0.9rem;
        }

        .form-control {
            width: 100%;
            padding: 14px 16px;
            border: 2px solid var(--border);
            border-radius: 8px;
            font-size: 1rem;
            transition: all 0.3s ease;
        }

        .form-control:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(52, 152, 219, 0.15);
        }

        .form-control.error {
            border-color: var(--danger);
        }

        .error-message {
            color: var(--danger);
            font-size: 0.85rem;
            margin-top: 5px;
            display: none;
        }

        .alert {
            padding: 14px 18px;
            border-radius: 8px;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 10px;
            font-size: 0.95rem;
        }

        .alert-danger {
            background: #fee2e2;
            color: #991b1b;
            border: 1px solid #fecaca;
        }

        .alert-success {
            background: #dcfce7;
            color: #166534;
            border: 1px solid #bbf7d0;
        }

        .btn {
            width: 100%;
            padding: 14px 20px;
            border: none;
            border-radius: 8px;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            text-decoration: none;
        }

        .btn-primary {
            background: var(--primary);
            color: white;
        }

        .btn-primary:hover {
            background: var(--primary-dark);
            box-shadow: 0 4px 12px rgba(52, 152, 219, 0.3);
        }

        .btn-loading {
            position: relative;
            color: transparent !important;
        }

        .btn-loading::after {
            content: '';
            position: absolute;
            width: 20px;
            height: 20px;
            top: 50%;
            left: 50%;
            margin-top: -10px;
            margin-left: -10px;
            border-radius: 50%;
            border: 2px solid rgba(255, 255, 255, 0.3);
            border-top-color: white;
            animation: spin 0.8s infinite linear;
        }

        @keyframes spin {
            to { transform: rotate(360deg); }
        }

        .back-to-login {
            text-align: center;
            margin-top: 25px;
        }

        .back-to-login a {
            color: var(--primary);
            text-decoration: none;
            font-size: 0.95rem;
            font-weight: 600;
            display: inline-flex;
            align-items: center;
            gap: 6px;
            transition: color 0.2s;
        }

        .back-to-login a:hover {
            color: var(--primary-dark);
            text-decoration: underline;
        }

        @media (max-width: 768px) {
            .forgot-container {
                flex-direction: column;
                min-height: auto;
            }
            .forgot-left {
                padding: 30px;
            }
            .forgot-right {
                padding: 30px;
            }
        }
    </style>
</head>

<body>
    <div class="forgot-container">
        <!-- Left Banner -->
        <div class="forgot-left">
            <div class="logo">
                <i class="fas fa-shield-alt logo-icon"></i>
                <span class="logo-text">FinCorp</span>
            </div>
            <div class="welcome-text">Account Recovery</div>
            <p class="info-desc">
                Don't worry! We will help you regain access to your account securely. Enter your registered email address and we'll send you a password reset link.
            </p>
        </div>

        <!-- Right Form -->
        <div class="forgot-right">
            <div class="forgot-header">
                <div class="forgot-icon-wrap">
                    <i class="fas fa-key"></i>
                </div>
                <h1 class="forgot-title">Forgot Password?</h1>
                <p class="forgot-subtitle">
                    Enter your email to receive recovery instructions.
                </p>
            </div>

            @if(session('status'))
                <div class="alert alert-success" id="success-alert">
                    <i class="fas fa-check-circle"></i>
                    <span>{{ session('status') }}</span>
                </div>
            @endif

            @if($errors->any())
                <div class="alert alert-danger" id="error-alert">
                    <i class="fas fa-exclamation-circle"></i>
                    <span>{{ $errors->first() }}</span>
                </div>
            @endif

            <form id="forgot-form" method="POST" action="{{ route('password.email') }}">
                @csrf
                <input type="hidden" name="role" value="{{ $role ?? 'manager' }}">

                <div class="form-group">
                    <label for="email" class="form-label">Registered Email Address</label>
                    <input type="email" id="email" name="email"
                        class="form-control @error('email') error @enderror" placeholder="name@company.com"
                        value="{{ old('email') }}" required autofocus>
                    <div class="error-message" id="email-error"></div>
                </div>

                <button type="submit" class="btn btn-primary" id="submit-btn">
                    <i class="fas fa-paper-plane"></i>
                    <span>Send Reset Link</span>
                </button>

                <div class="back-to-login">
                    <a href="{{ url(($role ?? 'manager') . '/login') }}">
                        <i class="fas fa-arrow-left"></i>
                        <span>Back to Sign In</span>
                    </a>
                </div>
            </form>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const form = document.getElementById('forgot-form');
            const emailInput = document.getElementById('email');
            const submitBtn = document.getElementById('submit-btn');
            const emailError = document.getElementById('email-error');

            function validateEmail() {
                const email = emailInput.value.trim();
                const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
                if (!email) {
                    emailInput.classList.add('error');
                    emailError.textContent = 'Please enter your email address.';
                    emailError.style.display = 'block';
                    return false;
                } else if (!emailRegex.test(email)) {
                    emailInput.classList.add('error');
                    emailError.textContent = 'Please enter a valid email address.';
                    emailError.style.display = 'block';
                    return false;
                } else {
                    emailInput.classList.remove('error');
                    emailError.style.display = 'none';
                    return true;
                }
            }

            emailInput.addEventListener('blur', validateEmail);

            form.addEventListener('submit', function(e) {
                if (!validateEmail()) {
                    e.preventDefault();
                    return;
                }
                submitBtn.disabled = true;
                submitBtn.classList.add('btn-loading');
            });
        });
    </script>
</body>
</html>
