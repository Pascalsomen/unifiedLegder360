<?php
session_start();
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/functions.php';

// Redirect to dashboard if already logged in
if (isset($_SESSION['user_id'])) {
    header("Location:  index.php");
    exit();
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $password = $_POST['password'];

    try {
        // Validate credentials
        $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ? AND is_active = TRUE");
        $stmt->execute([$username]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password'])) {
            // Regenerate session ID to prevent fixation
            session_regenerate_id(true);

            // Set session variables
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_role'] = $user['role'];
            $_SESSION['user_name'] = $user['full_name'];
            $_SESSION['email'] = $username;

            // Record login
            $stmt = $pdo->prepare("UPDATE users SET last_login = NOW() WHERE id = ?");
            $stmt->execute([$user['id']]);

            // Redirect to dashboard
            header("Location: index.php");
            exit();
        } else {
            $error = "Invalid username or password";
        }
    } catch (PDOException $e) {
        $error = "Database error: " . $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login | UnifiedLedger 360</title>

    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <!-- Custom CSS -->
    <style>
        body {
            background-color: #111;
            height: 100vh;
            display: flex;
            align-items: center;
        }
        .login-card {
            width: 100%;
            max-width: 400px;
            margin: 0 auto;
            border-radius: 10px;
            overflow: hidden;
            border: 1px solid #333;
            background: #000;
        }
        .login-header {
            background-color: #000;
            padding: 25px 20px;
            text-align: center;
            border-bottom: 2px solid #FFD700;
        }
        .login-body {
            padding: 30px;
            background-color: #000;
        }
        .form-floating {
            margin-bottom: 1.5rem;
        }
        .form-control {
            background-color: #222;
            border: 1px solid #333;
            color: #fff;
        }
        .form-control:focus {
            background-color: #222;
            border-color: #FFD700;
            box-shadow: 0 0 0 0.25rem rgba(255, 215, 0, 0.25);
            color: #fff;
        }
        .form-floating > label {
            color: #888;
        }
        .form-floating > .form-control:focus ~ label,
        .form-floating > .form-control:not(:placeholder-shown) ~ label {
            color: #FFD700;
        }
        .btn-gold {
            background-color: #FFD700;
            border-color: #FFD700;
            color: #000;
            font-weight: 600;
        }
        .btn-gold:hover {
            background-color: #FFC107;
            border-color: #FFC107;
            color: #000;
        }
        .forgot-link {
            color: #FFD700;
            text-decoration: none;
        }
        .forgot-link:hover {
            color: #FFC107;
            text-decoration: underline;
        }
        .form-check-input:checked {
            background-color: #FFD700;
            border-color: #FFD700;
        }
        hr {
            border-color: #333;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="login-card">
            <div class="login-header">
                <img src="assets/logo-white.png" style="height:80px; margin-bottom: 15px;">
                <p class="mb-0" style="color:#FFD700; font-weight:500;">UnifiedLedger 360</p>
                <p class="mb-0" style="color:#aaa; font-size:14px; margin-top:5px;">Please sign in to continue</p>
            </div>

            <div class="login-body">
                <?php if (isset($error)): ?>
                    <div class="alert alert-danger alert-dismissible fade show">
                        <i class="fas fa-exclamation-circle me-2"></i>
                        <?= $error ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" style="filter: invert(1);"></button>
                    </div>
                <?php endif; ?>

                <form method="POST">
                    <div class="form-floating">
                        <input type="text" class="form-control" id="username" name="username"
                               placeholder="Username" required autofocus>
                        <label for="username"><i class="fas fa-user me-2"></i>Username</label>
                    </div>

                    <div class="form-floating">
                        <input type="password" class="form-control" id="password" name="password"
                               placeholder="Password" required>
                        <label for="password"><i class="fas fa-lock me-2"></i>Password</label>
                    </div>

                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="remember" name="remember">
                            <label class="form-check-label" for="remember" style="color:#aaa;">Remember me</label>
                        </div>
                        <a href="/forgot-password.php" class="forgot-link">Forgot password?</a>
                    </div>

                    <button class="w-100 btn btn-lg btn-gold" type="submit">
                        <i class="fas fa-sign-in-alt me-2"></i> Sign in
                    </button>
                </form>

                <hr class="my-4">

                <div class="text-center" style="color:#777; font-size:12px;">
                    <i class="fas fa-shield-alt me-1"></i> Secure ERP System Access
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap 5 JS Bundle with Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

    <!-- Custom JS -->
    <script>
        // Focus on username field on page load
        document.addEventListener('DOMContentLoaded', function() {
            document.getElementById('username').focus();

            // Check for remembered username
            if (localStorage.getItem('rememberedUsername')) {
                document.getElementById('username').value = localStorage.getItem('rememberedUsername');
                document.getElementById('remember').checked = true;
                document.getElementById('password').focus();
            }

            // Handle remember me functionality
            document.querySelector('form').addEventListener('submit', function() {
                if (document.getElementById('remember').checked) {
                    localStorage.setItem('rememberedUsername', document.getElementById('username').value);
                } else {
                    localStorage.removeItem('rememberedUsername');
                }
            });
        });
    </script>
</body>
</html>