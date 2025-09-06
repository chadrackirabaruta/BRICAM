

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>BRICAM INDUSTRY LTD - Login</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
      <link rel="icon" type="image/png" href="{{ asset('img/logo.png') }}">

  <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        :root {
            --primary-color: #4f46e5;
            --primary-hover: #4338ca;
            --secondary-color: #6366f1;
            --text-color: #1f2937;
            --text-light: #6b7280;
            --bg-color: #f9fafb;
            --bg-card: #ffffff;
            --error-color: #ef4444;
            --success-color: #10b981;
            --border-color: #e5e7eb;
            --dark-bg: #111827;
            --dark-card: #1f2937;
            --dark-text: #f3f4f6;
            --dark-border: #374151;
        }

        body {
            background: linear-gradient(135deg, #f3f4f6 0%, #e5e7eb 100%);
            color: var(--text-color);
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 20px;
            transition: background 0.3s ease;
        }

        body.dark-mode {
            background: linear-gradient(135deg, #111827 0%, #1f2937 100%);
            color: var(--dark-text);
        }

        .login-container {
            background-color: var(--bg-card);
            border-radius: 20px;
            box-shadow: 0 15px 30px rgba(0, 0, 0, 0.1);
            width: 100%;
            max-width: 460px;
            padding: 35px;
            transition: all 0.3s ease;
        }

        .dark-mode .login-container {
            background-color: var(--dark-card);
            box-shadow: 0 15px 30px rgba(0, 0, 0, 0.3);
        }

        .login-container:hover {
            transform: translateY(-5px);
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.15);
        }

        .dark-mode .login-container:hover {
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.4);
        }

        .logo-container {
            text-align: center;
            margin-bottom: 30px;
        }

        .logo {
            width: 110px;
            height: 110px;
            margin: 0 auto 20px;
            display: flex;
            align-items: center;
            justify-content: center;
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-color) 100%);
            border-radius: 18px;
            color: white;
            font-size: 32px;
            font-weight: bold;
            transition: all 0.3s ease;
            box-shadow: 0 10px 20px rgba(79, 70, 229, 0.2);
        }

        .logo:hover {
            transform: scale(1.05) rotate(2deg);
            box-shadow: 0 15px 30px rgba(79, 70, 229, 0.3);
        }

        h2 {
            text-align: center;
            font-size: 32px;
            margin-bottom: 30px;
            color: var(--primary-color);
            font-weight: 800;
            letter-spacing: 0.5px;
            text-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
        }

        .dark-mode h2 {
            color: var(--secondary-color);
            text-shadow: 0 2px 4px rgba(255, 255, 255, 0.05);
        }

        .alert {
            padding: 14px 18px;
            border-radius: 12px;
            margin-bottom: 24px;
            display: none;
            animation: fadeIn 0.5s ease;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(-10px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .alert-error {
            background-color: #fee2e2;
            color: var(--error-color);
            border: 1px solid #fecaca;
        }

        .alert-success {
            background-color: #d1fae5;
            color: var(--success-color);
            border: 1px solid #a7f3d0;
        }

        .dark-mode .alert-error {
            background-color: rgba(239, 68, 68, 0.2);
            border-color: rgba(239, 68, 68, 0.3);
        }

        .dark-mode .alert-success {
            background-color: rgba(16, 185, 129, 0.2);
            border-color: rgba(16, 185, 129, 0.3);
        }

        .form-group {
            margin-bottom: 24px;
            position: relative;
        }

        label {
            display: block;
            margin-bottom: 10px;
            font-weight: 600;
            color: var(--text-color);
            font-size: 15px;
        }

        .dark-mode label {
            color: var(--dark-text);
        }

        .input-field {
            width: 100%;
            padding: 16px 18px;
            border: 2px solid var(--border-color);
            border-radius: 12px;
            font-size: 16px;
            transition: all 0.3s ease;
            background-color: var(--bg-color);
            color: var(--text-color);
        }

        .dark-mode .input-field {
            background-color: var(--dark-bg);
            border-color: var(--dark-border);
            color: var(--dark-text);
        }

        .input-field:focus {
            outline: none;
            border-color: var(--primary-color);
            box-shadow: 0 0 0 4px rgba(79, 70, 229, 0.2);
        }

        .dark-mode .input-field:focus {
            box-shadow: 0 0 0 4px rgba(99, 102, 241, 0.3);
        }

        .password-toggle {
            position: absolute;
            right: 16px;
            top: 46px;
            background: none;
            border: none;
            color: var(--text-light);
            cursor: pointer;
            font-size: 18px;
            transition: color 0.3s ease;
        }

        .password-toggle:hover {
            color: var(--primary-color);
        }

        .dark-mode .password-toggle {
            color: var(--dark-text);
        }

        .dark-mode .password-toggle:hover {
            color: var(--secondary-color);
        }

        .remember-forgot {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 24px;
        }

        .remember {
            display: flex;
            align-items: center;
        }

        .remember input {
            margin-right: 10px;
            width: 18px;
            height: 18px;
            accent-color: var(--primary-color);
        }

        .dark-mode .remember input {
            accent-color: var(--secondary-color);
        }

        .forgot-password {
            color: var(--primary-color);
            text-decoration: none;
            font-weight: 600;
            transition: all 0.3s ease;
            font-size: 15px;
        }

        .dark-mode .forgot-password {
            color: var(--secondary-color);
        }

        .forgot-password:hover {
            text-decoration: underline;
            transform: translateX(2px);
        }

        .login-button {
            width: 100%;
            padding: 16px;
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-color) 100%);
            color: white;
            border: none;
            border-radius: 12px;
            font-size: 17px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            display: flex;
            justify-content: center;
            align-items: center;
            box-shadow: 0 5px 15px rgba(79, 70, 229, 0.3);
        }

        .login-button:hover {
            background: linear-gradient(135deg, var(--primary-hover) 0%, var(--primary-color) 100%);
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(79, 70, 229, 0.4);
        }

        .login-button:active {
            transform: translateY(0);
        }

        .spinner {
            width: 22px;
            height: 22px;
            border: 3px solid rgba(255, 255, 255, 0.3);
            border-radius: 50%;
            border-top-color: white;
            animation: spin 1s ease-in-out infinite;
            margin-right: 12px;
            display: none;
        }

        @keyframes spin {
            to {
                transform: rotate(360deg);
            }
        }

        .signup-link {
            text-align: center;
            margin-top: 30px;
            color: var(--text-light);
            font-size: 15px;
        }

        .dark-mode .signup-link {
            color: var(--dark-text);
        }

        .signup-link a {
            color: var(--primary-color);
            text-decoration: none;
            font-weight: 700;
            transition: all 0.3s ease;
            position: relative;
        }

        .signup-link a::after {
            content: '';
            position: absolute;
            width: 100%;
            height: 2px;
            bottom: -2px;
            left: 0;
            background-color: var(--primary-color);
            transform: scaleX(0);
            transform-origin: bottom right;
            transition: transform 0.3s ease;
        }

        .signup-link a:hover::after {
            transform: scaleX(1);
            transform-origin: bottom left;
        }

        .dark-mode .signup-link a {
            color: var(--secondary-color);
        }

        .dark-mode .signup-link a::after {
            background-color: var(--secondary-color);
        }

        .theme-toggle {
            position: fixed;
            top: 25px;
            right: 25px;
            background: var(--bg-card);
            border: 2px solid var(--border-color);
            border-radius: 50%;
            width: 55px;
            height: 55px;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
            transition: all 0.3s ease;
            z-index: 100;
        }

        .theme-toggle:hover {
            transform: rotate(15deg) scale(1.1);
        }

        .dark-mode .theme-toggle {
            background: var(--dark-card);
            border-color: var(--dark-border);
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.3);
        }

        /* Responsive styles */
        @media (max-width: 520px) {
            .login-container {
                padding: 25px;
                border-radius: 16px;
            }
            
            .logo {
                width: 90px;
                height: 90px;
                font-size: 26px;
                border-radius: 16px;
            }
            
            h2 {
                font-size: 26px;
            }
            
            .remember-forgot {
                flex-direction: column;
                align-items: flex-start;
                gap: 12px;
            }
            
            .forgot-password {
                margin-top: 5px;
            }
            
            .theme-toggle {
                top: 15px;
                right: 15px;
                width: 50px;
                height: 50px;
            }
        }

        @media (max-width: 380px) {
            .login-container {
                padding: 20px;
            }
            
            .logo {
                width: 80px;
                height: 80px;
                font-size: 24px;
            }
            
            h2 {
                font-size: 24px;
            }
            
            .input-field {
                padding: 14px 16px;
            }
        }
    </style>

</head>
<body>
    <div class="theme-toggle" id="themeToggle">
        <i class="fas fa-moon"></i>
    </div>

    <div class="login-container">
        <div class="logo-container">
            <div class="logo">B</div>
            <h2>BRICAM INDUSTRY LTD</h2>
        </div>

        {{-- ✅ Success Message (e.g., password reset done) --}}
        @if (session('status'))
            <div class="alert alert-success" style="display:block;">
                {{ session('status') }}
            </div>
        @endif

        {{-- ✅ Invalid Credentials or Validation Errors --}}
        @if ($errors->any())
            <div class="alert alert-error" style="display:block;">
                <ul>
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        {{-- ✅ Login Form --}}
        <form method="POST" action="{{ route('login') }}" id="loginForm">
            @csrf

            <div class="form-group">
                <label for="email">Email</label>
                <input type="email"
                       name="email"
                       id="email"
                       class="input-field"
                       value="{{ old('email') }}"
                       placeholder="Enter your email"
                       required autofocus>
            </div>

            <div class="form-group">
                <label for="password">Password</label>
                <input type="password"
                       name="password"
                       id="password"
                       class="input-field"
                       placeholder="Enter your password"
                       required>
                <button type="button" class="password-toggle" id="passwordToggle">
                    <i class="far fa-eye"></i>
                </button>
            </div>

            <div class="remember-forgot">
                <div class="remember">
                    <input type="checkbox" name="remember" id="remember">
                 <label for="remember">Remember me</label>
               
                </div>
                
            <!--<a href="{{ route('password.request') }}" class="forgot-password">Forgot password?</a>-->
            </div>

            <button type="submit" class="login-button">
                <div class="spinner" id="spinner"></div>
                <span id="buttonText">Log in</span>
            </button>
        </form>
          <!-- 

        <div class="signup-link">
            Don't have an account? <a href="{{ route('register') }}">Sign up</a>
        </div>
    </div>
-->
    <script>
        // Theme Toggle
        const themeToggle = document.getElementById('themeToggle');
        const body = document.body;
        themeToggle.addEventListener('click', () => {
            body.classList.toggle('dark-mode');
            themeToggle.innerHTML = body.classList.contains('dark-mode')
                ? '<i class="fas fa-sun"></i>'
                : '<i class="fas fa-moon"></i>';
        });

        // Password Toggle
        const passwordToggle = document.getElementById('passwordToggle');
        const passwordInput = document.getElementById('password');
        passwordToggle.addEventListener('click', () => {
            const type = passwordInput.type === 'password' ? 'text' : 'password';
            passwordInput.type = type;
            passwordToggle.innerHTML = type === 'text'
                ? '<i class="far fa-eye-slash"></i>'
                : '<i class="far fa-eye"></i>';
        });

        // Spinner on submit
        const loginForm = document.getElementById('loginForm');
        const spinner = document.getElementById('spinner');
        const buttonText = document.getElementById('buttonText');
        loginForm.addEventListener('submit', () => {
            spinner.style.display = 'block';
            buttonText.textContent = 'Logging in...';
        });
    </script>
</body>
</html>

    <script>
        // DOM Elements
        const themeToggle = document.getElementById('themeToggle');
        const body = document.body;
        const passwordToggle = document.getElementById('passwordToggle');
        const passwordInput = document.getElementById('password');
        const loginForm = document.getElementById('loginForm');
        const alertBox = document.getElementById('alert');
        const spinner = document.getElementById('spinner');
        const buttonText = document.getElementById('buttonText');

        // Theme Toggle
        themeToggle.addEventListener('click', () => {
            body.classList.toggle('dark-mode');
            if (body.classList.contains('dark-mode')) {
                themeToggle.innerHTML = '<i class="fas fa-sun"></i>';
            } else {
                themeToggle.innerHTML = '<i class="fas fa-moon"></i>';
            }
        });

        // Password Visibility Toggle
        passwordToggle.addEventListener('click', () => {
            const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
            passwordInput.setAttribute('type', type);
            
            // Toggle eye icon
            if (type === 'text') {
                passwordToggle.innerHTML = '<i class="far fa-eye-slash"></i>';
            } else {
                passwordToggle.innerHTML = '<i class="far fa-eye"></i>';
            }
        });

        // Show Alert Function
        function showAlert(message, type) {
            alertBox.textContent = message;
            alertBox.classList.add(type === 'error' ? 'alert-error' : 'alert-success');
            alertBox.style.display = 'block';
            
            // Hide alert after 5 seconds
            setTimeout(() => {
                alertBox.style.display = 'none';
                alertBox.classList.remove('alert-error', 'alert-success');
            }, 5000);
        }

        // Form Submission - Connect to your backend
        loginForm.addEventListener('submit', async (e) => {
            e.preventDefault();
            
            const email = document.getElementById('email').value;
            const password = document.getElementById('password').value;
            const remember = document.getElementById('remember').checked;
            
            // Basic validation
            if (!email || !password) {
                showAlert('Please fill in all fields', 'error');
                return;
            }
            
            // Email validation
            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            if (!emailRegex.test(email)) {
                showAlert('Please enter a valid email address', 'error');
                return;
            }
            
            // Show loading state
            spinner.style.display = 'block';
            buttonText.textContent = 'Logging in...';
            
            try {
                // Replace this with your actual authentication endpoint
                const response = await fetch('/api/login', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({ email, password, remember })
                });
                
                const data = await response.json();
                
                if (data.success) {
                    showAlert('Login successful! Redirecting...', 'success');
                    // Redirect to dashboard after successful login
                    setTimeout(() => {
                        window.location.href = '/dashboard';
                    }, 2000);
                } else {
                    showAlert(data.message || 'Invalid credentials', 'error');
                }
            } catch (error) {
                showAlert('An error occurred. Please try again.', 'error');
                console.error('Login error:', error);
            } finally {
                // Hide loading state
                spinner.style.display = 'none';
                buttonText.textContent = 'Log in';
            }
        });

        // Simulate database connection (in a real application, this would be server-side)
        console.log("Connecting to authentication system...");
        
        // Example of how you might structure your authentication function
        async function authenticateUser(email, password) {
            // This function would typically connect to your backend
            // which would then verify credentials against your database
            
            try {
                // Example implementation (replace with your actual API endpoint)
                const response = await fetch('/api/authenticate', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({ email, password }),
                });
                
                return await response.json();
            } catch (error) {
                console.error('Authentication error:', error);
                return { success: false, message: 'Authentication failed' };
            }
        }
    </script>
</body>
</html>