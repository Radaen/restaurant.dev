<?php
session_start();
require_once '../includes/db.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($username && $password) {
        $stmt = $pdo->prepare("SELECT id, username, password_hash, role, full_name, status FROM users WHERE username = ?");
        $stmt->execute([$username]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password_hash'])) {
            if ($user['status'] === 'Active') {
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['role'] = $user['role'];
                $_SESSION['full_name'] = $user['full_name'];
                header("Location: ../index.php");
                exit;
            } else {
                $error = "Your account is inactive. Please contact admin.";
            }
        } else {
            $error = "Invalid username or password.";
        }
    } else {
        $error = "Please fill in all fields.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Restaurant Management</title>
    <link rel="icon" href="/Restaurant/assets/img/restaurant.ico" type="image/x-icon">
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700&display=swap" rel="stylesheet">
    <!-- FontAwesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body {
            font-family: 'Poppins', sans-serif;
            background: linear-gradient(135deg, #ff7e5f 0%, #feb47b 100%);
            height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0;
        }
        .login-card {
            background: rgba(255, 255, 255, 0.9);
            border-radius: 15px;
            padding: 40px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.2);
            width: 100%;
            max-width: 400px;
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255,255,255,0.5);
        }
        .login-title {
            text-align: center;
            margin-bottom: 30px;
            color: #333;
            font-weight: 700;
        }
        .login-title i {
            color: #ff7e5f;
            font-size: 40px;
            margin-bottom: 15px;
        }
        .btn-custom {
            background: linear-gradient(135deg, #ff7e5f 0%, #feb47b 100%);
            border: none;
            color: white;
            font-weight: 600;
            padding: 10px;
            border-radius: 30px;
            transition: all 0.3s;
        }
        .btn-custom:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(255, 126, 95, 0.4);
            color: white;
        }
        .form-control {
            border-radius: 30px;
            padding: 12px 20px;
        }
        .input-group-text {
            border-radius: 30px 0 0 30px;
            background: transparent;
            border-right: none;
        }
        .form-control.with-icon {
            border-left: none;
        }
        .form-control:focus {
            box-shadow: none;
            border-color: #ced4da;
        }
        .input-group:focus-within .input-group-text,
        .input-group:focus-within .form-control {
            border-color: #ff7e5f;
        }
    </style>
</head>
<body>

<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-6 col-lg-4">
            <div class="login-card">
                <div class="login-title">
                    <i class="fa-solid fa-utensils"></i>
                    <h3>Welcome Back</h3>
                    <p class="text-muted" style="font-size: 0.9em;">Login to manage the restaurant</p>
                </div>
                
                <?php if ($error): ?>
                    <div class="alert alert-danger rounded-pill px-3 py-2 text-center" style="font-size: 0.85em;">
                        <?= htmlspecialchars($error) ?>
                    </div>
                <?php endif; ?>

                <form method="POST" action="">
                    <div class="input-group mb-3">
                        <span class="input-group-text"><i class="fa-solid fa-user text-muted"></i></span>
                        <input type="text" class="form-control with-icon" name="username" placeholder="Username" required>
                    </div>
                    
                    <div class="input-group mb-4">
                        <span class="input-group-text"><i class="fa-solid fa-lock text-muted"></i></span>
                        <input type="password" class="form-control with-icon" name="password" placeholder="Password" required>
                    </div>
                    
                    <div class="d-grid">
                        <button type="submit" class="btn btn-custom">Login</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

</body>
</html>
