<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Login - OJT Time Log Management System</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        :root {
            --primary: #6366f1;
            --primary-dark: #4f46e5;
            --primary-light: #818cf8;
            --secondary: #8b5cf6;
            --accent: #ec4899;
            --success: #10b981;
            --danger: #ef4444;
            --warning: #f59e0b;
            --dark: #1e293b;
            --light: #f1f5f9;
            --gray-100: #f8fafc;
            --gray-200: #e2e8f0;
            --gray-300: #cbd5e1;
            --gray-400: #94a3b8;
            --gray-500: #64748b;
            --gray-600: #475569;
            --gray-700: #334155;
            --gray-800: #1e293b;
            --gray-900: #0f172a;
        }

        body {
            font-family: 'Inter', sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 50%, #f093fb 100%);
            min-height: 100vh;
            overflow-x: hidden;
        }

        .auth-container {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 2rem 1rem;
            position: relative;
        }

        /* Animated background shapes */
        .bg-shapes {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            overflow: hidden;
            z-index: 0;
        }

        .shape {
            position: absolute;
            border-radius: 50%;
            opacity: 0.1;
            animation: float 20s ease-in-out infinite;
        }

        .shape-1 {
            width: 400px;
            height: 400px;
            background: white;
            top: -100px;
            left: -100px;
            animation-delay: 0s;
        }

        .shape-2 {
            width: 300px;
            height: 300px;
            background: white;
            bottom: -50px;
            right: -50px;
            animation-delay: 5s;
        }

        .shape-3 {
            width: 200px;
            height: 200px;
            background: white;
            top: 50%;
            right: 20%;
            animation-delay: 10s;
        }

        @keyframes float {
            0%, 100% { transform: translate(0, 0) rotate(0deg); }
            25% { transform: translate(30px, 30px) rotate(90deg); }
            50% { transform: translate(0, 60px) rotate(180deg); }
            75% { transform: translate(-30px, 30px) rotate(270deg); }
        }

        .auth-card {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(20px);
            border-radius: 24px;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25),
                        0 0 0 1px rgba(255, 255, 255, 0.2) inset;
            padding: 3rem;
            max-width: 480px;
            width: 100%;
            position: relative;
            z-index: 1;
            animation: slideUp 0.6s ease-out;
        }

        @keyframes slideUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .logo-section {
            text-align: center;
            margin-bottom: 2.5rem;
        }

        .logo-icon {
            width: 64px;
            height: 64px;
            background: linear-gradient(135deg, var(--primary) 0%, var(--secondary) 100%);
            border-radius: 16px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 1rem;
            box-shadow: 0 10px 25px -5px rgba(99, 102, 241, 0.4);
        }

        .logo-icon i {
            font-size: 32px;
            color: white;
        }

        .logo-title {
            font-size: 1.75rem;
            font-weight: 700;
            color: var(--gray-900);
            margin-bottom: 0.5rem;
            background: linear-gradient(135deg, var(--primary) 0%, var(--secondary) 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .logo-subtitle {
            color: var(--gray-500);
            font-size: 0.95rem;
        }

        .alert {
            border: none;
            border-radius: 12px;
            padding: 1rem 1.25rem;
            margin-bottom: 1.5rem;
            font-size: 0.9rem;
        }

        .alert-danger {
            background: linear-gradient(135deg, #fee2e2 0%, #fecaca 100%);
            color: #991b1b;
        }

        .alert-info {
            background: linear-gradient(135deg, #dbeafe 0%, #bfdbfe 100%);
            color: #1e40af;
            border-radius: 12px;
            padding: 1rem 1.25rem;
            margin-bottom: 1.5rem;
            font-size: 0.875rem;
        }

        .credentials-card {
            background: rgba(99, 102, 241, 0.05);
            border: 1px dashed var(--primary);
            border-radius: 12px;
            padding: 1rem;
            margin-top: 1rem;
        }

        .credentials-card h6 {
            color: var(--primary);
            font-weight: 600;
            margin-bottom: 0.75rem;
            font-size: 0.875rem;
        }

        .credentials-card .credential-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 0.5rem 0;
            border-bottom: 1px solid rgba(99, 102, 241, 0.1);
            font-size: 0.8rem;
        }

        .credentials-card .credential-item:last-child {
            border-bottom: none;
        }

        .credentials-card .credential-label {
            color: var(--gray-600);
            font-weight: 500;
        }

        .credentials-card .credential-value {
            color: var(--gray-800);
            font-family: monospace;
            background: white;
            padding: 0.125rem 0.5rem;
            border-radius: 4px;
            font-size: 0.75rem;
        }

        .form-check {
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .form-check-input {
            width: 1.25rem;
            height: 1.25rem;
            border: 2px solid var(--gray-300);
            border-radius: 4px;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .form-check-input:checked {
            background-color: var(--primary);
            border-color: var(--primary);
        }

        .form-check-label {
            cursor: pointer;
            user-select: none;
        }

        .form-label {
            font-weight: 600;
            color: var(--gray-700);
            font-size: 0.875rem;
            margin-bottom: 0.5rem;
        }

        .form-control {
            padding: 0.875rem 1.25rem;
            border: 2px solid var(--gray-200);
            border-radius: 12px;
            font-size: 0.95rem;
            transition: all 0.3s ease;
            background: var(--gray-100);
        }

        .form-control:focus {
            border-color: var(--primary);
            background: white;
            box-shadow: 0 0 0 4px rgba(99, 102, 241, 0.1);
        }

        .form-control.is-invalid {
            border-color: var(--danger);
        }

        .password-toggle {
            position: absolute;
            right: 1rem;
            top: 50%;
            transform: translateY(-50%);
            background: none;
            border: none;
            color: var(--gray-400);
            cursor: pointer;
            padding: 0.25rem;
            transition: color 0.3s;
            z-index: 10;
        }

        .password-toggle:hover {
            color: var(--gray-600);
        }

        .input-group {
            position: relative;
        }

        .btn-primary {
            background: linear-gradient(135deg, var(--primary) 0%, var(--secondary) 100%);
            border: none;
            border-radius: 12px;
            padding: 0.875rem 2rem;
            font-weight: 600;
            font-size: 1rem;
            letter-spacing: 0.025em;
            transition: all 0.3s ease;
            box-shadow: 0 4px 15px rgba(99, 102, 241, 0.4);
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(99, 102, 241, 0.5);
        }

        .btn-primary:active {
            transform: translateY(0);
        }

        .auth-footer {
            text-align: center;
            margin-top: 2rem;
            padding-top: 1.5rem;
            border-top: 1px solid var(--gray-200);
        }

        .auth-footer p {
            color: var(--gray-500);
            font-size: 0.9rem;
            margin: 0;
        }

        .auth-footer a {
            color: var(--primary);
            text-decoration: none;
            font-weight: 600;
            transition: color 0.3s;
        }

        .auth-footer a:hover {
            color: var(--primary-dark);
        }

        /* Responsive */
        @media (max-width: 576px) {
            .auth-card {
                padding: 2rem 1.5rem;
            }

            .logo-title {
                font-size: 1.5rem;
            }
        }
    </style>
</head>
<body>
    <div class="bg-shapes">
        <div class="shape shape-1"></div>
        <div class="shape shape-2"></div>
        <div class="shape shape-3"></div>
    </div>

    <div class="auth-container">
        <div class="auth-card">
            <div class="logo-section">
                <div class="logo-icon">
                    <i class="bi bi-clock-history"></i>
                </div>
                <h1 class="logo-title">OJT TLMS</h1>
                <p class="logo-subtitle">Time Log Management System</p>
            </div>

            @if($errors->any())
                <div class="alert alert-danger">
                    <div class="d-flex align-items-start">
                        <i class="bi bi-exclamation-circle-fill me-2 mt-0.5"></i>
                        <div>
                            <strong class="d-block mb-1">Please fix the following errors:</strong>
                            <ul class="mb-0 ps-3">
                                @foreach($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    </div>
                </div>
            @endif

            <form method="POST" action="{{ route('login') }}">
                @csrf

                <div class="mb-4">
                    <label for="email" class="form-label">
                        <i class="bi bi-envelope me-1"></i> Email Address
                    </label>
                    <input type="email" class="form-control @error('email') is-invalid @enderror"
                           id="email" name="email" value="{{ old('email') }}" required
                           placeholder="Enter your email">
                </div>

                <div class="mb-4">
                    <label for="password" class="form-label">
                        <i class="bi bi-lock me-1"></i> Password
                    </label>
                    <div class="input-group">
                        <input type="password" class="form-control @error('password') is-invalid @enderror"
                               id="password" name="password" required
                               placeholder="Enter your password">
                        <button type="button" class="password-toggle" id="togglePassword" tabindex="-1">
                            <i class="bi bi-eye"></i>
                        </button>
                    </div>
                </div>

                <div class="mb-4">
                    <div class="form-check">
                        <input type="checkbox" class="form-check-input" id="remember" name="remember" value="1">
                        <label class="form-check-label" for="remember">
                            <i class="bi bi-check-circle me-1"></i> Remember me
                        </label>
                    </div>
                </div>

                <div class="d-grid mt-4">
                    <button type="submit" class="btn btn-primary btn-lg">
                        <i class="bi bi-box-arrow-in-right me-2"></i> Sign In
                    </button>
                </div>
            </form>

            <div class="alert-info">
                <i class="bi bi-info-circle me-2"></i>
                <strong>Sample Credentials:</strong>
                <div class="credentials-card">
                    <div class="credential-item">
                        <span class="credential-label">Super Admin:</span>
                        <span class="credential-value">admin@timelog.com / Adm1n@TLMS2026!</span>
                    </div>
                    <div class="credential-item">
                        <span class="credential-label">Guard:</span>
                        <span class="credential-value">GUARD-001@guard.timelog.com / Gu4rd@TLMS2026!</span>
                    </div>
                    <div class="credential-item">
                        <span class="credential-label">Student:</span>
                        <span class="credential-value">2024-001@student.timelog.com / StuD3nt@TLMS2026!</span>
                    </div>
                </div>
            </div>

            <div class="auth-footer">
                <p>Don't have an account? <a href="{{ route('register') }}">Create one here</a></p>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.getElementById('togglePassword').addEventListener('click', function () {
            const passwordInput = document.getElementById('password');
            const icon = this.querySelector('i');

            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                icon.classList.remove('bi-eye');
                icon.classList.add('bi-eye-slash');
            } else {
                passwordInput.type = 'password';
                icon.classList.remove('bi-eye-slash');
                icon.classList.add('bi-eye');
            }
        });

        // Add floating label effect
        document.querySelectorAll('.form-control').forEach(input => {
            input.addEventListener('focus', function() {
                this.parentElement.classList.add('focused');
            });
            input.addEventListener('blur', function() {
                this.parentElement.classList.remove('focused');
            });
        });
    </script>
</body>
</html>
