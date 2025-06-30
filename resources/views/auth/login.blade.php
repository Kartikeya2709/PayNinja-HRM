<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>PayNinja - HRM</title>
  <style>
    @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap');
    body, html {
      margin: 0;
      padding: 0;
      font-family: 'Inter', sans-serif;
      background-color: white;
      height: 100vh;
      overflow: hidden;
    }
    .container {
      display: flex;
      height: 100vh;
      align-items: center;
      justify-content: center;
      padding-top: 0;
      box-sizing: border-box;
      background: linear-gradient(to right, white 50%, #6777EF 50%);
    }
    .left-illustration, .right-illustration {
      flex: 1;
      display: flex;
      align-items: end;
      justify-content: center;
      height: 100vh;
    }

    .right-illustration {
      flex: 1;
      display: flex;
      align-items: end;
      justify-content: right;
      height: 100vh;
      background-color: #6777EF;
    }
    
    .left-illustration img {
      max-height: 80vh;
      max-width: 100%;
      object-fit: contain;
      background-color: white;
    }
    .right-illustration img {
      max-height: 95vh;
      max-width: 100%;
      object-fit: contain;
    }
    .login-card {
      background: white;
      border-radius: 12px;
      padding: 3rem 2rem;
      width: 450px;
      box-shadow: 0 4px 12px rgba(0,0,0,0.1);
      display: flex;
      flex-direction: column;
      justify-content: center;
      align-items: center;
    }
    .login-logo {
      max-width: 200px;
    }
    .login-card h2 {
      font-weight: 700;
      font-size: 2rem;
      margin-bottom: 2rem;
      color: #333;
    }
    label {
      font-weight: 600;
      font-size: 1.125rem;
      color: #333;
      display: block;
      margin-bottom: 0.5rem;
      align-self: flex-start;
    }
    input[type="email"], input[type="password"] {
      width: 100%;
      padding: 12px 16px;
      border: 2px solid #6777EF;
      border-radius: 6px;
      font-size: 1rem;
      margin-bottom: 1.5rem;
      box-sizing: border-box;
      outline: none;
      transition: border-color 0.3s ease;
    }
    input[type="email"]:focus, input[type="password"]:focus {
      border-color: #388e3c;
    }
    .forgot-password {
      color: #6777EF;
      font-weight: 600;
      margin-bottom: 1.5rem;
      display: inline-block;
      text-decoration: none;
      align-self: flex-start;
    }
    .forgot-password:hover {
      text-decoration: underline;
    }
    .form-check {
      display: flex;
      align-items: center;
      margin-bottom: 1.5rem;
      align-self: flex-start;
    }
    .form-check input[type="checkbox"] {
      margin-right: 0.5rem;
      width: 18px;
      height: 18px;
    }
    .form-check label {
      font-weight: 600;
      color: #6777EF;
      font-size: 1rem;
      margin: 0;
    }
    button.login-btn {
      background: linear-gradient(90deg, #4361ee 0%, #3f37c9 100%);
      border: none;
      color: white;
      font-weight: 700;
      font-size: 1.125rem;
      padding: 12px 0;
      border-radius: 6px;
      cursor: pointer;
      width: 100%;
      margin-bottom: 1rem;
      transition: background 0.3s ease;
    }
    button.login-btn:hover {
      background: linear-gradient(90deg, #3f37c9 0%, #2e2a8a 100%);
    }
    a.register-btn {
      background: linear-gradient(90deg, #6777EF 0%, #4044d5 100%);
      color: white !important;
      font-weight: 700;
      font-size: 1.125rem;
      padding: 12px 0;
      border-radius: 6px;
      text-align: center;
      text-decoration: none;
      display: block;
      width: 100%;
      cursor: pointer;
      transition: background 0.3s ease;
    }
    a.register-btn:hover {
      background: linear-gradient(90deg, #4044d5 0%, #3a3ecf 100%);
    }
    .footer-text {
      position: fixed;
      bottom: 10px;
      width: 100%;
      text-align: center;
      color: #666;
      font-size: 0.9rem;
    }
  </style>
</head>
<body>
  <div class="container">
    <div class="left-illustration">
      <img src="https://demo.workdo.io/hrmgo/assets/images/theme-3.svg" alt="Left Illustration" />
    </div>
    <div class="login-card">
      <div class="login-logo-container" style="height: 60px; overflow: hidden; display: flex; align-items: center; justify-content: center;">
        <img src="https://www.payninja.in/assets/images/logo.png" alt="PayNinja Logo" class="login-logo" style="object-fit: contain;" />
      </div>
      <h2>Login</h2>
      <form method="POST" action="{{ route('login') }}">
        @csrf
        @if ($errors->any())
          <div style="background-color: #ffebee; color: #c62828; padding: 12px; border-radius: 6px; margin-bottom: 20px; width: 100%; box-sizing: border-box;">
            <strong>Whoops!</strong> There were some problems with your input.
            <ul style="margin: 8px 0 0 20px; padding: 0;">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
          </div>
        @endif
        
        @if (session('status'))
          <div style="background-color: #e8f5e9; color: #2e7d32; padding: 12px; border-radius: 6px; margin-bottom: 20px; width: 100%; box-sizing: border-box;">
              {{ session('status') }}
          </div>
        @endif
        <label for="email">Email</label>
        <input id="email" type="email" name="email" required autocomplete="email" autofocus placeholder="company@example.com" />
        <label for="password">Password</label>
        <input id="password" type="password" name="password" required autocomplete="current-password" placeholder="••••••••" />
        <a href="{{ route('password.request') }}" class="forgot-password">Forgot Your Password?</a>
        <div class="form-check">
          <input type="checkbox" name="remember" id="remember" />
          <label for="remember">Remember Me</label>
        </div>
        <button type="submit" class="login-btn">Login</button>
        <a href="{{ route('register') }}" class="register-btn">Register</a>
      </form>
    </div>
    <div class="right-illustration">
      <img src="https://demo.workdo.io/hrmgo/assets/images/common.svg" alt="Right Illustration" />
    </div>
  </div>
  <div class="footer-text">
    © 2025 HRMGo
  </div>
</body>
</html>
