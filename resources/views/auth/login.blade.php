<!doctype html>
<html lang="en" dir="ltr">

<head>
    <!-- META DATA -->
    <meta charset="UTF-8">
    <meta name='viewport' content='width=device-width, initial-scale=1.0, user-scalable=0'>
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="description" content="Login - Arabian Dena">
    <meta name="author" content="Arabian Dena">
    <meta name="keywords" content="login, authentication">

    <!-- FAVICON -->
    <link rel="shortcut icon" type="image/x-icon" href="/assets/images/brand/favicon.ico">

    <!-- TITLE -->
    <title>Login - Arabian Dena</title>

    <!-- BOOTSTRAP CSS -->
    <link id="style" href="/assets/plugins/bootstrap/css/bootstrap.min.css" rel="stylesheet">

    <!-- STYLE CSS -->
    <link href="/assets/css/style.css" rel="stylesheet">

    <!-- Plugins CSS -->
    <link href="/assets/css/plugins.css" rel="stylesheet">

    <!--- FONT-ICONS CSS -->
    <link href="/assets/css/icons.css" rel="stylesheet">

    <style>
        :root {
            --primary-rgb: 108, 95, 252;
            --primary-bg-color: rgb(var(--primary-rgb));
            --primary-bg-hover: rgba(var(--primary-rgb), 0.9);
        }

        .login-page {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            background: linear-gradient(135deg, rgba(108, 95, 252, 0.1) 0%, rgba(108, 95, 252, 0.05) 100%);
            padding: 20px;
        }

        .login-card {
            background: #ffffff;
            border-radius: 15px;
            box-shadow: 0 10px 40px rgba(108, 95, 252, 0.2);
            overflow: hidden;
            max-width: 450px;
            width: 100%;
        }

        .login-header {
            background: linear-gradient(135deg, rgb(108, 95, 252) 0%, rgba(108, 95, 252, 0.8) 100%);
            padding: 40px 30px;
            text-align: center;
            color: #ffffff;
        }

        .login-header h2 {
            margin: 0;
            font-size: 28px;
            font-weight: 600;
            color: #ffffff;
        }

        .login-header p {
            margin: 10px 0 0 0;
            opacity: 0.9;
            font-size: 14px;
        }

        .login-body {
            padding: 40px 30px;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-label {
            font-weight: 500;
            color: #282f53;
            margin-bottom: 8px;
            display: block;
            font-size: 14px;
        }

        .form-control {
            border: 1px solid #e9edf4;
            border-radius: 8px;
            padding: 12px 15px;
            font-size: 14px;
            transition: all 0.3s ease;
        }

        .form-control:focus {
            border-color: rgb(108, 95, 252);
            box-shadow: 0 0 0 0.2rem rgba(108, 95, 252, 0.25);
            outline: none;
        }

        .btn-primary-custom {
            background: rgb(108, 95, 252);
            border: none;
            color: #ffffff;
            padding: 12px 30px;
            border-radius: 8px;
            font-weight: 500;
            font-size: 15px;
            width: 100%;
            transition: all 0.3s ease;
            cursor: pointer;
        }

        .btn-primary-custom:hover {
            background: rgba(108, 95, 252, 0.9);
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(108, 95, 252, 0.3);
        }

        .btn-primary-custom:active {
            transform: translateY(0);
        }

        .form-check {
            margin-bottom: 20px;
        }

        .form-check-input:checked {
            background-color: rgb(108, 95, 252);
            border-color: rgb(108, 95, 252);
        }

        .form-check-label {
            color: #5a6970;
            font-size: 14px;
        }

        .alert-danger {
            background-color: #fee;
            border: 1px solid #fcc;
            color: #c33;
            padding: 12px 15px;
            border-radius: 8px;
            margin-bottom: 20px;
            font-size: 14px;
        }

        .text-danger {
            color: #dc3545;
            font-size: 13px;
            margin-top: 5px;
            display: block;
        }

        .invalid-feedback {
            display: block;
            color: #dc3545;
            font-size: 13px;
            margin-top: 5px;
        }
    </style>
</head>

<body class="app sidebar-mini ltr light-mode">
    <div class="login-page">
        <div class="login-card">
            <div class="login-header">
                <h2>Welcome Back</h2>
                <p>Sign in to continue to Arabian Dena</p>
            </div>
            <div class="login-body">
                @if ($errors->any())
                    <div class="alert alert-danger">
                        <ul class="mb-0">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form method="POST" action="{{ route('login') }}">
                    @csrf

                    <div class="form-group">
                        <label for="email" class="form-label">Email Address</label>
                        <input type="email" 
                               class="form-control @error('email') is-invalid @enderror" 
                               id="email" 
                               name="email" 
                               value="{{ old('email') }}" 
                               required 
                               autofocus 
                               placeholder="Enter your email">
                        @error('email')
                            <span class="invalid-feedback" role="alert">
                                <strong>{{ $message }}</strong>
                            </span>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label for="password" class="form-label">Password</label>
                        <input type="password" 
                               class="form-control @error('password') is-invalid @enderror" 
                               id="password" 
                               name="password" 
                               required 
                               placeholder="Enter your password">
                        @error('password')
                            <span class="invalid-feedback" role="alert">
                                <strong>{{ $message }}</strong>
                            </span>
                        @enderror
                    </div>

                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" name="remember" id="remember" {{ old('remember') ? 'checked' : '' }}>
                        <label class="form-check-label" for="remember">
                            Remember me
                        </label>
                    </div>

                    <button type="submit" class="btn btn-primary-custom">
                        Sign In
                    </button>
                </form>
            </div>
        </div>
    </div>

    <!-- JQUERY JS -->
    <script src="/assets/js/jquery.min.js"></script>

    <!-- BOOTSTRAP JS -->
    <script src="/assets/plugins/bootstrap/js/popper.min.js"></script>
    <script src="/assets/plugins/bootstrap/js/bootstrap.min.js"></script>
</body>

</html>

