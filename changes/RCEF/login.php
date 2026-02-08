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
    <title>Login | CEF ERP SYSTEM</title>

    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <!-- Custom CSS -->
    <style>
        body {
            background-color: #f8f9fa;
            height: 100vh;
            display: flex;
            align-items: center;
        }
        .login-card {
            width: 100%;
            max-width: 400px;
            margin: 0 auto;
            box-shadow: 0 0 20px white;
            border-radius: 10px;
            overflow: hidden;
        }
        .login-header {
            background-color:  #f8f9fa;
            color: white;
            padding: 20px;
            text-align: center;
        }
        .login-body {
            padding: 30px;
            background-color: white;
        }
        .form-floating {
            margin-bottom: 1.5rem;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="login-card" style="border:1px solid black">
            <div class="login-header">
            <img src="assets/logo.png" style="height:100px">
                <p class="mb-0" style="color:black">RCEF: Please sign in to continue</p>
            </div>

            <div class="login-body">
                <?php if (isset($error)): ?>
                    <div class="alert alert-danger alert-dismissible fade show">
                        <?= $error ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
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
                            <label class="form-check-label" for="remember">Remember me</label>
                        </div>
                        <a href="/forgot-password.php" class="text-decoration-none">Forgot password?</a>
                    </div>

                    <button class="w-100 btn btn-lg btn-warning" type="submit">
                        <i class="fas fa-sign-in-alt me-2"></i> Sign in
                    </button>
                </form>

                <hr class="my-4">


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