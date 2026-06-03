<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Finance Manager - {{ ucfirst($role ?? 'Manager') }} Login</title>
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

        .login-container {
            display: flex;
            max-width: 1000px;
            width: 100%;
            background: white;
            border-radius: 15px;
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.1);
            overflow: hidden;
            min-height: 600px;
        }

        .login-left {
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

        .login-left::before {
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
            font-size: 2.5rem;
            background: rgba(255, 255, 255, 0.2);
            padding: 15px;
            border-radius: 12px;
            backdrop-filter: blur(10px);
        }

        .logo-text {
            font-size: 1.8rem;
            font-weight: 700;
        }

        .welcome-text {
            font-size: 2.2rem;
            font-weight: 300;
            margin-bottom: 20px;
            line-height: 1.3;
            z-index: 1;
            position: relative;
        }

        .features {
            list-style: none;
            margin-top: 30px;
            z-index: 1;
            position: relative;
        }

        .features li {
            display: flex;
            align-items: center;
            gap: 12px;
            margin-bottom: 15px;
            font-size: 1rem;
        }

        .features i {
            background: rgba(255, 255, 255, 0.2);
            padding: 8px;
            border-radius: 50%;
            width: 35px;
            height: 35px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .login-right {
            flex: 1;
            padding: 50px;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }

        .login-header {
            text-align: center;
            margin-bottom: 40px;
        }

        .login-title {
            font-size: 2rem;
            color: var(--secondary);
            margin-bottom: 10px;
            font-weight: 600;
        }

        .login-subtitle {
            color: var(--gray);
            font-size: 1rem;
        }

        .form-group {
            margin-bottom: 25px;
        }

        .form-label {
            display: block;
            margin-bottom: 8px;
            font-weight: 500;
            color: var(--secondary);
        }

        .form-control {
            width: 100%;
            padding: 15px;
            border: 2px solid var(--border);
            border-radius: 8px;
            font-size: 1rem;
            transition: all 0.3s ease;
        }

        .form-control:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(52, 152, 219, 0.1);
        }

        .password-input {
            position: relative;
        }

        .toggle-password {
            position: absolute;
            right: 15px;
            top: 50%;
            transform: translateY(-50%);
            background: none;
            border: none;
            color: var(--gray);
            cursor: pointer;
            font-size: 1.1rem;
        }

        .remember-forgot {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 25px;
        }

        .remember-me {
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .remember-me input {
            width: 16px;
            height: 16px;
        }

        .forgot-password {
            color: var(--primary);
            text-decoration: none;
            font-size: 0.9rem;
        }

        .forgot-password:hover {
            text-decoration: underline;
        }

        .btn {
            padding: 15px 30px;
            border: none;
            border-radius: 8px;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
        }

        .btn-primary {
            background: var(--primary);
            color: white;
            width: 100%;
        }

        .btn-primary:hover {
            background: var(--primary-dark);
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(52, 152, 219, 0.3);
        }

        .divider {
            text-align: center;
            margin: 30px 0;
            position: relative;
            color: var(--gray);
        }

        .divider::before {
            content: '';
            position: absolute;
            top: 50%;
            left: 0;
            right: 0;
            height: 1px;
            background: var(--border);
        }

        .divider span {
            background: white;
            padding: 0 15px;
            position: relative;
        }

        .demo-accounts {
            margin-top: 30px;
        }

        .demo-title {
            text-align: center;
            color: var(--gray);
            margin-bottom: 15px;
            font-size: 0.9rem;
        }

        .demo-buttons {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
        }

        .btn-demo {
            flex: 1;
            min-width: 120px;
            padding: 12px;
            background: var(--light);
            color: var(--secondary);
            border: 1px solid var(--border);
        }

        .btn-demo:hover {
            background: var(--primary);
            color: white;
            border-color: var(--primary);
        }

        .footer-links {
            text-align: center;
            margin-top: 30px;
            font-size: 0.9rem;
            color: var(--gray);
        }

        .footer-links a {
            color: var(--primary);
            text-decoration: none;
            margin: 0 10px;
        }

        .footer-links a:hover {
            text-decoration: underline;
        }

        .alert {
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
            display: none;
        }

        .alert-error {
            background: #fee;
            border: 1px solid #f5c6cb;
            color: #721c24;
        }

        .alert-success {
            background: #e8f5e8;
            border: 1px solid #c3e6cb;
            color: #155724;
        }

        /* Responsive Design */
        @media (max-width: 768px) {
            .login-container {
                flex-direction: column;
                max-width: 450px;
            }

            .login-left {
                padding: 30px;
                text-align: center;
            }

            .login-right {
                padding: 30px;
            }

            .demo-buttons {
                flex-direction: column;
            }
        }

        /* Loading Animation */
        .btn-loading {
            position: relative;
            color: transparent;
        }

        .btn-loading::after {
            content: '';
            position: absolute;
            width: 20px;
            height: 20px;
            border: 2px solid transparent;
            border-top: 2px solid white;
            border-radius: 50%;
            animation: spin 1s linear infinite;
        }

        @keyframes spin {
            0% {
                transform: rotate(0deg);
            }

            100% {
                transform: rotate(360deg);
            }
        }

        /* Password Strength */
        .password-strength {
            margin-top: 5px;
            height: 4px;
            border-radius: 2px;
            background: var(--border);
            overflow: hidden;
        }

        .strength-bar {
            height: 100%;
            width: 0%;
            transition: all 0.3s ease;
        }

        .strength-weak {
            background: var(--danger);
            width: 33%;
        }

        .strength-medium {
            background: var(--warning);
            width: 66%;
        }

        .strength-strong {
            background: var(--success);
            width: 100%;
        }

        /* Error states */
        .form-control.error {
            border-color: var(--danger);
        }

        .error-message {
            color: var(--danger);
            font-size: 0.875rem;
            margin-top: 5px;
            display: none;
        }
    </style>
</head>

<body>
    <div class="login-container">
        <!-- Left Side - Branding & Features -->
        <div class="login-left">
            <div class="logo">
                <div class="logo-icon">
                    <i class="fas fa-chart-line"></i>
                </div>
                <div class="logo-text">Finance Manager</div>
            </div>

            <h1 class="welcome-text">
                Welcome to Your Complete Accounting Solution
            </h1>

            <ul class="features">
                <li>
                    <i class="fas fa-money-bill-wave"></i>
                    <span>Track expenses and income efficiently</span>
                </li>
                <li>
                    <i class="fas fa-file-invoice"></i>
                    <span>Manage invoices and payments</span>
                </li>
                <li>
                    <i class="fas fa-balance-scale"></i>
                    <span>Generate CA statements and reports</span>
                </li>
                <li>
                    <i class="fas fa-tasks"></i>
                    <span>Stay compliant with automated tasks</span>
                </li>
                <li>
                    <i class="fas fa-chart-bar"></i>
                    <span>Real-time financial insights</span>
                </li>
            </ul>
        </div>

        <!-- Right Side - Login Form -->
        <div class="login-right">
            <div class="login-header">
                <h2 class="login-title">{{ ucfirst($role ?? 'Manager') }} Sign In</h2>
                <p class="login-subtitle">Enter your credentials to access your account</p>
            </div>

            <!-- Error/Success Messages -->
            @if ($errors->any())
                <div class="alert alert-error" id="error-alert" style="display: block;">
                    <i class="fas fa-exclamation-circle"></i>
                    <span id="error-message">{{ $errors->first() }}</span>
                </div>
            @else
                <div class="alert alert-error" id="error-alert">
                    <i class="fas fa-exclamation-circle"></i>
                    <span id="error-message">Invalid credentials. Please try again.</span>
                </div>
            @endif

            @if (session('status'))
                <div class="alert alert-success" id="success-alert" style="display: block;">
                    <i class="fas fa-check-circle"></i>
                    <span id="success-message">{{ session('status') }}</span>
                </div>
            @else
                <div class="alert alert-success" id="success-alert">
                    <i class="fas fa-check-circle"></i>
                    <span id="success-message">Login successful! Redirecting...</span>
                </div>
            @endif

            <form id="login-form" method="POST" action="{{ url(($role ?? 'manager') . '/login') }}">
                @csrf

                <div class="form-group">
                    <label for="email" class="form-label">Email Address</label>
                    <input type="email" id="email" name="email"
                        class="form-control @error('email') error @enderror" placeholder="Enter your email"
                        value="{{ old('email') }}" required autofocus>
                    <div class="error-message" id="email-error"></div>
                </div>

                <div class="form-group">
                    <label for="password" class="form-label">Password</label>
                    <div class="password-input">
                        <input type="password" id="password" name="password"
                            class="form-control @error('password') error @enderror" placeholder="Enter your password"
                            required>
                        <button type="button" class="toggle-password" id="toggle-password">
                            <i class="fas fa-eye"></i>
                        </button>
                    </div>
                    <div class="error-message" id="password-error"></div>
                    <div class="password-strength">
                        <div class="strength-bar" id="password-strength"></div>
                    </div>
                </div>

                <div class="remember-forgot">
                    <label class="remember-me">
                        <input type="checkbox" id="remember" name="remember" {{ old('remember') ? 'checked' : '' }}>
                        <span>Remember me</span>
                    </label>
                    {{-- <a href="{{ route('password.request') }}" class="forgot-password">Forgot Password?</a> --}}
                </div>

                <button type="submit" class="btn btn-primary" id="login-btn">
                    <i class="fas fa-sign-in-alt"></i>
                    Sign In
                </button>
            </form>

            <div class="footer-links">
                <span>© 2024 Infasta Soft Solutions</span>
                <a href="#privacy">Privacy Policy</a>
                <a href="#terms">Terms of Service</a>
                <a href="#support">Support</a>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // DOM Elements
            const loginForm = document.getElementById('login-form');
            const emailInput = document.getElementById('email');
            const passwordInput = document.getElementById('password');
            const togglePasswordBtn = document.getElementById('toggle-password');
            const rememberMe = document.getElementById('remember');
            const loginBtn = document.getElementById('login-btn');
            const errorAlert = document.getElementById('error-alert');
            const successAlert = document.getElementById('success-alert');
            const emailError = document.getElementById('email-error');
            const passwordError = document.getElementById('password-error');

            // Toggle password visibility
            togglePasswordBtn.addEventListener('click', function() {
                const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
                passwordInput.setAttribute('type', type);
                this.innerHTML = type === 'password' ? '<i class="fas fa-eye"></i>' :
                    '<i class="fas fa-eye-slash"></i>';
            });

            // Password strength indicator
            passwordInput.addEventListener('input', function() {
                const strengthBar = document.getElementById('password-strength');
                const password = this.value;
                let strength = 0;

                if (password.length >= 8) strength += 1;
                if (password.match(/[a-z]/) && password.match(/[A-Z]/)) strength += 1;
                if (password.match(/\d/)) strength += 1;
                if (password.match(/[^a-zA-Z\d]/)) strength += 1;

                strengthBar.className = 'strength-bar';
                if (strength > 0) {
                    strengthBar.classList.add(
                        strength === 1 ? 'strength-weak' :
                        strength === 2 ? 'strength-medium' :
                        strength === 3 ? 'strength-strong' :
                        'strength-strong'
                    );
                }
            });

            // Form validation
            function validateForm() {
                let isValid = true;

                // Email validation
                if (!emailInput.value.trim()) {
                    showFieldError(emailInput, emailError, 'Email is required');
                    isValid = false;
                } else if (!isValidEmail(emailInput.value.trim())) {
                    showFieldError(emailInput, emailError, 'Please enter a valid email address');
                    isValid = false;
                } else {
                    clearFieldError(emailInput, emailError);
                }

                // Password validation
                if (!passwordInput.value) {
                    showFieldError(passwordInput, passwordError, 'Password is required');
                    isValid = false;
                } else {
                    clearFieldError(passwordInput, passwordError);
                }

                return isValid;
            }

            function showFieldError(input, errorElement, message) {
                input.classList.add('error');
                errorElement.textContent = message;
                errorElement.style.display = 'block';
            }

            function clearFieldError(input, errorElement) {
                input.classList.remove('error');
                errorElement.style.display = 'none';
            }

            function isValidEmail(email) {
                const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
                return emailRegex.test(email);
            }

            // Form submission
            loginForm.addEventListener('submit', function(e) {
                // Client-side validation
                if (!validateForm()) {
                    e.preventDefault();
                    return;
                }

                // Show loading state
                setLoadingState(true);

                // Hide any previous alerts
                errorAlert.style.display = 'none';
                successAlert.style.display = 'none';
            });

            function setLoadingState(loading) {
                if (loading) {
                    loginBtn.disabled = true;
                    loginBtn.classList.add('btn-loading');
                    loginBtn.innerHTML = 'Signing In...';
                } else {
                    loginBtn.disabled = false;
                    loginBtn.classList.remove('btn-loading');
                    loginBtn.innerHTML = '<i class="fas fa-sign-in-alt"></i> Sign In';
                }
            }

            // Auto-hide alerts after 5 seconds
            setTimeout(() => {
                if (errorAlert.style.display === 'block') {
                    errorAlert.style.display = 'none';
                }
                if (successAlert.style.display === 'block') {
                    successAlert.style.display = 'none';
                }
            }, 5000);

            // Real-time validation
            emailInput.addEventListener('blur', validateForm);
            passwordInput.addEventListener('blur', validateForm);
        });
    </script>
</body>

</html>
