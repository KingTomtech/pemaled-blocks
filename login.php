<?php
session_start();
require_once 'config.php';

// Rate limiting (3 attempts per minute)
$rate_limit_key = 'login_attempt_'.$_SERVER['REMOTE_ADDR'];
if (!isset($_SESSION[$rate_limit_key])) $_SESSION[$rate_limit_key] = 0;

if(isset($_POST['login'])) {
    if($_SESSION[$rate_limit_key] > 3) {
        $error = "Too many attempts. Try again later.";
    } else {
        $username = filter_input(INPUT_POST, 'username', FILTER_SANITIZE_STRING);
        $password = $_POST['password'];

        $stmt = $conn->prepare("SELECT * FROM users WHERE username = ?");
        $stmt->execute([$username]);
        $user = $stmt->fetch();

        if($user && password_verify($password, $user['password'])) {
            // Regenerate session ID to prevent fixation
            session_regenerate_id(true);
            
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['role'] = $user['role'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['last_login'] = time();
            
            header("Location: index.php?page=dashboard");
            exit();
        } else {
            $error = "Invalid credentials";
            $_SESSION[$rate_limit_key]++;
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en" data-bs-theme="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>Factory Portal - Login</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --gradient-start: #2c3e50;
            --gradient-end: #3498db;
        }
        
        body {
            min-height: 100dvh;
            background: linear-gradient(135deg, 
                var(--gradient-start), 
                var(--gradient-end));
            display: grid;
            place-items: center;
        }

        .login-card {
            width: min(95%, 400px);
            border: 1px solid rgba(255,255,255,0.1);
            border-radius: 1rem;
            backdrop-filter: blur(10px);
            background: rgba(255,255,255,0.05);
            transition: transform 0.3s ease;
        }

        .form-control {
            background: rgba(255,255,255,0.1);
            border: none;
            color: #fff !important;
            padding: 1rem;
        }

        .form-control:focus {
            background: rgba(255,255,255,0.2);
            box-shadow: none;
        }

        .input-group-text {
            background: rgba(255,255,255,0.1);
            border: none;
            color: rgba(255,255,255,0.6);
        }

        @media (max-width: 576px) {
            .login-card {
                margin: 1rem;
            }
        }
    </style>
</head>
<body class="text-white">
    <div class="login-card p-4 shadow-lg">
        <div class="text-center mb-5">
            <img src="logo.svg" alt="Factory Logo" class="mb-4" style="width: 80px; height: 80px;">
            <h1 class="h4 mb-3">Factory Production Portal</h1>
            <p class="text-muted">Access your production dashboard</p>
        </div>

        <?php if(isset($error)): ?>
            <div class="alert alert-danger d-flex align-items-center gap-2">
                <i class="fas fa-exclamation-circle"></i>
                <?= htmlspecialchars($error) ?>
            </div>
        <?php endif; ?>

        <form method="post" autocomplete="off">
            <div class="mb-3">
                <div class="input-group">
                    <span class="input-group-text">
                        <i class="fas fa-user"></i>
                    </span>
                    <input type="text" 
                           name="username" 
                           class="form-control form-control-lg" 
                           placeholder="Username"
                           required
                           autofocus>
                </div>
            </div>

            <div class="mb-4">
                <div class="input-group">
                    <span class="input-group-text">
                        <i class="fas fa-lock"></i>
                    </span>
                    <input type="password" 
                           name="password" 
                           class="form-control form-control-lg" 
                           placeholder="Password"
                           required>
                </div>
            </div>

            <button type="submit" 
                    name="login" 
                    class="btn btn-light btn-lg w-100 fw-bold mb-3">
                Sign In
            </button>

            <div class="text-center text-white-50">
                <small>Secure production access © <?= date('Y') ?></small>
            </div>
        </form>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Password visibility toggle
        document.querySelectorAll('.password-toggle').forEach(button => {
            button.addEventListener('click', () => {
                const input = button.previousElementSibling;
                const type = input.type === 'password' ? 'text' : 'password';
                input.type = type;
                button.querySelector('i').classList.toggle('fa-eye-slash');
            });
        });
    </script>
</body>
</html>