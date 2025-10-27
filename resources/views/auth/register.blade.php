<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Register - HRMGo</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap');

        body,
        html {
            margin: 0;
            padding: 0;
            font-family: 'Inter', sans-serif;
            background-color: white;
            height: 100vh;
            overflow: hidden;
        }

        .container-cutsom {
            display: flex;
            height: 100vh;
            align-items: center;
            justify-content: center;
            padding-top: 0;
            box-sizing: border-box;
            background: linear-gradient(to right, white 50%, #6777EF 50%);
        }

        .left-illustration,
        .right-illustration {
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

        .register-card {
            background: white;
            border-radius: 20px;
            padding: 3rem 2rem;
            width: 530px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
        }

        .register-logo {
            max-width: 200px;
        }

        .register-card h2 {
            font-weight: 700;
            font-size: 2rem;
            margin-bottom: 1.5rem;
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

        input[type="text"],
        input[type="email"],
        input[type="password"] {
            width: 100%;
            padding: 12px 16px;
            border: 2px solid #6777EF;
            border-radius: 6px;
            font-size: 1rem;
            /* margin-bottom: 1.5rem; */
            box-sizing: border-box;
            outline: none;
            transition: border-color 0.3s ease;
        }

        input[type="text"]:focus,
        input[type="email"]:focus,
        input[type="password"]:focus {
            border-color: #FE9800;
        }

        button.register-btn {
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

        button.register-btn:hover {
            background: linear-gradient(90deg, #3f37c9 0%, #2e2a8a 100%);
        }

        a.login-btn {
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

        a.login-btn:hover {
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
    <div class="container-cutsom">
        <div class="left-illustration">
            <img src="{{ asset('/images/theme-3.svg') }}" alt="Left Illustration" />
        </div>
        <div class="register-card">
            <div class="register-logo-container"
                style="overflow: hidden; display: flex; align-items: center; justify-content: center;">
                <img src="{{ asset('/images/rocket-hr-logo.png') }}" alt="RocketHR Logo" class="register-logo"
                    style="object-fit: contain;" />
            </div>
            <h2>Register</h2>
            <form method="POST" action="{{ route('register') }}">
                @csrf
                <div class="row mb-4">
                    <div class="col-md-6">
                        <label for="name" class="form-label">Name</label>
                        <input id="name" type="text" name="name" value="{{ old('name') }}" class="form-control @error('name') is-invalid @enderror" autocomplete="name" autofocus placeholder="Your full name" />
                        @error('name')
                            <div class="text-danger">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="col-md-6">
                        <label for="email" class="form-label">Email</label>
                        <input id="email" type="email" name="email" value="{{ old('email') }}" class="form-control @error('email') is-invalid @enderror" autocomplete="email" placeholder="company@example.com" />
                        @error('email')
                            <div class="text-danger">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
                <div class="row mb-5">
                    <div class="col-md-6">
                        <label for="password" class="form-label">Password</label>
                        <input id="password" type="password" name="password" class="form-control @error('password') is-invalid @enderror" autocomplete="new-password" placeholder="••••••••" />
                        @error('password')
                            <div class="text-danger">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="col-md-6">
                        <label for="password-confirm" class="form-label">Confirm Password</label>
                        <input id="password-confirm" type="password" name="password_confirmation" class="form-control @error('password_confirmation') is-invalid @enderror" autocomplete="new-password" placeholder="••••••••" />
                        @error('password_confirmation')
                            <div class="text-danger">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
                <button type="submit" class="register-btn">Register</button>
                <a href="{{ route('login') }}" class="login-btn">Login</a>
            </form>
        </div>
        <div class="right-illustration">
            <img src="{{ asset('/images/common.svg') }}" alt="Right Illustration" />
        </div>
    </div>
    <div class="footer-text">
        © 2025 2025 HRMGo
    </div>
</body>

</html>
