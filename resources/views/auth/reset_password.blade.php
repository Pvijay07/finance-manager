<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Finance Manager - Reset Password</title>
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

        .reset-container {
            display: flex;
            max-width: 950px;
            width: 100%;
            background: white;
            border-radius: 15px;
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.15);
            overflow: hidden;
            min-height: 560px;
        }

        .reset-left {
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

        .reset-left::before {
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

        .reset-right {
            flex: 1.2;
            padding: 45px 50px;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }

        .reset-header {
            text-align: center;
            margin-bottom: 25px;
        }

        .reset-icon-wrap {
            width: 56px;
            height: 56px;
            background: #eff6ff;
            color: var(--primary);
            border-radius: 50%;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
            margin-bottom: 12px;
        }

        .reset-title {
            font-size: 1.7rem;
            color: var(--secondary);
            margin-bottom: 6px;
            font-weight: 700;
        }

        .reset-subtitle {
            color: #64748b;
            font-size: 0.9rem;
        }

        .form-group {
            margin-bottom: 18px;
        }

        .form-label {
            display: block;
            margin-bottom: 6px;
            font-weight: 600;
            color: var(--secondary);
            font-size: 0.88rem;
        }

        .password-input {
            position: relative;
            display: flex;
            align-items: center;
        }

        .form-control {
            width: 100%;
            padding: 12px 14px;
            border: 2px solid var(--border);
            border-radius: 8px;
            font-size: 0.95rem;
            transition: all 0.3s ease;
        }

        .password-input .form-control {
            padding-right: 42px;
        }

        .toggle-password {
            position: absolute;
            right: 12px;
            background: none;
            border: none;
            color: var(--gray);
            cursor: pointer;
            font-size: 1rem;
            padding: 4px;
        }

        .toggle-password:hover {
            color: var(--secondary);
        }

        .form-control:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(52, 152, 219, 0.15);
        }

        .password-strength {
            height: 4px;
            background: #e2e8f0;
            border-radius: 2px;
            margin-top: 6px;
            overflow: hidden;
        }

        .strength-bar {
            height: 100%;
            width: 0;
            transition: all 0.3s ease;
        }

        .strength-weak {
            width: 30%;
            background: var(--danger);
        }

        .strength-medium {
            width: 65%;
            background: var(--warning);
        }

        .strength-strong {
            width: 100%;
            background: var(--success);
        }

        .match-badge {
            font-size: 0.8rem;
            margin-top: 4px;
            display: none;
            font-weight: 500;
        }

        .alert {
            padding: 12px 16px;
            border-radius: 8px;
            margin-bottom: 18px;
            display: flex;
            align-items: center;
            gap: 10px;
            font-size: 0.9rem;
        }

        .alert-danger {
            background: #fee2e2;
            color: #991b1b;
            border: 1px solid #fecaca;
        }

        .btn {
            width: 100%;
            padding: 13px 20px;
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
            margin-top: 10px;
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
            width: 18px;
            height: 18px;
            top: 50%;
            left: 50%;
            margin-top: -9px;
            margin-left: -9px;
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
            margin-top: 20px;
        }

        .back-to-login a {
            color: var(--primary);
            text-decoration: none;
            font-size: 0.9rem;
            font-weight: 600;
            display: inline-flex;
            align-items: center;
            gap: 6px;
        }

        .back-to-login a:hover {
            color: var(--primary-dark);
            text-decoration: underline;
        }

        @media (max-width: 768px) {
            .reset-container {
                flex-direction: column;
                min-height: auto;
            }
            .reset-left {
                padding: 30px;
            }
            .reset-right {
                padding: 30px;
            }
        }
    </style>
</head>

<body>
    <div class="reset-container">
        <!-- Left Banner -->
        <div class="reset-left">
            <div class="logo">
                <i class="fas fa-shield-alt logo-icon"></i>
                <span class="logo-text">FinCorp</span>
            </div>
            <div class="welcome-text">Set New Password</div>
            <p class="info-desc">
                Choose a strong password to protect your financial workspace. We recommend using a combination of letters, numbers, and symbols.
            </p>
        </div>

        <!-- Right Form -->
        <div class="reset-right">
            <div class="reset-header">
                <div class="reset-icon-wrap">
                    <i class="fas fa-lock"></i>
                </div>
                <h1 class="reset-title">Reset Password</h1>
                <p class="reset-subtitle">Enter your email and create a new secure password.</p>
            </div>

            @if($errors->any())
                <div class="alert alert-danger">
                    <i class="fas fa-exclamation-circle"></i>
                    <span>{{ $errors->first() }}</span>
                </div>
            @endif

            <form id="reset-form" method="POST" action="{{ route('password.update') }}">
                @csrf
                <input type="hidden" name="token" value="{{ $token }}">

                <div class="form-group">
                    <label for="email" class="form-label">Email Address</label>
                    <input type="email" id="email" name="email"
                        class="form-control @error('email') error @enderror"
                        value="{{ old('email', $email) }}" required>
                </div>

                <div class="form-group">
                    <label for="password" class="form-label">New Password</label>
                    <div class="password-input">
                        <input type="password" id="password" name="password"
                            class="form-control" placeholder="Minimum 8 characters" required minlength="8">
                        <button type="button" class="toggle-password" id="toggle-pwd">
                            <i class="fas fa-eye"></i>
                        </button>
                    </div>
                    <div class="password-strength">
                        <div class="strength-bar" id="strength-bar"></div>
                    </div>
                </div>

                <div class="form-group">
                    <label for="password_confirmation" class="form-label">Confirm New Password</label>
                    <div class="password-input">
                        <input type="password" id="password_confirmation" name="password_confirmation"
                            class="form-control" placeholder="Re-enter new password" required minlength="8">
                        <button type="button" class="toggle-password" id="toggle-confirm-pwd">
                            <i class="fas fa-eye"></i>
                        </button>
                    </div>
                    <div id="match-badge" class="match-badge"></div>
                </div>

                <button type="submit" class="btn btn-primary" id="reset-btn">
                    <i class="fas fa-check-circle"></i>
                    <span>Reset Password</span>
                </button>

                <div class="back-to-login">
                    <a href="{{ url('/manager/login') }}">
                        <i class="fas fa-arrow-left"></i>
                        <span>Back to Sign In</span>
                    </a>
                </div>
            </form>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const pwd = document.getElementById('password');
            const confirmPwd = document.getElementById('password_confirmation');
            const strengthBar = document.getElementById('strength-bar');
            const matchBadge = document.getElementById('match-badge');
            const togglePwd = document.getElementById('toggle-pwd');
            const toggleConfirm = document.getElementById('toggle-confirm-pwd');
            const resetBtn = document.getElementById('reset-btn');
            const form = document.getElementById('reset-form');

            // Visibility toggle
            togglePwd.addEventListener('click', function() {
                const type = pwd.getAttribute('type') === 'password' ? 'text' : 'password';
                pwd.setAttribute('type', type);
                this.innerHTML = type === 'password' ? '<i class="fas fa-eye"></i>' : '<i class="fas fa-eye-slash"></i>';
            });

            toggleConfirm.addEventListener('click', function() {
                const type = confirmPwd.getAttribute('type') === 'password' ? 'text' : 'password';
                confirmPwd.setAttribute('type', type);
                this.innerHTML = type === 'password' ? '<i class="fas fa-eye"></i>' : '<i class="fas fa-eye-slash"></i>';
            });

            // Strength bar
            pwd.addEventListener('input', function() {
                const val = this.value;
                let score = 0;
                if (val.length >= 8) score += 1;
                if (/[a-z]/.test(val) && /[A-Z]/.test(val)) score += 1;
                if (/\d/.test(val)) score += 1;
                if (/[^a-zA-Z\d]/.test(val)) score += 1;

                strengthBar.className = 'strength-bar';
                if (score === 1) strengthBar.classList.add('strength-weak');
                else if (score === 2 || score === 3) strengthBar.classList.add('strength-medium');
                else if (score >= 4) strengthBar.classList.add('strength-strong');

                checkMatch();
            });

            confirmPwd.addEventListener('input', checkMatch);

            function checkMatch() {
                if (!confirmPwd.value) {
                    matchBadge.style.display = 'none';
                    return;
                }
                matchBadge.style.display = 'block';
                if (pwd.value === confirmPwd.value) {
                    matchBadge.textContent = '✓ Passwords match';
                    matchBadge.style.color = '#16a34a';
                } else {
                    matchBadge.textContent = '✗ Passwords do not match';
                    matchBadge.style.color = '#dc2626';
                }
            }

            form.addEventListener('submit', function(e) {
                if (pwd.value.length < 8) {
                    e.preventDefault();
                    alert('Password must be at least 8 characters long.');
                    return;
                }
                if (pwd.value !== confirmPwd.value) {
                    e.preventDefault();
                    alert('Passwords do not match.');
                    return;
                }
                resetBtn.disabled = true;
                resetBtn.classList.add('btn-loading');
            });
        });
    </script>
</body>
</html>
