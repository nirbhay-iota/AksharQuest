<?php
session_start();

  
if (isset($_SESSION['user_id'])) {
    header("Location: dashboard.php");
    exit();
}

$dsn = 'mysql:host=localhost;dbname=crossword;charset=utf8mb4';
$db_user = 'root';
$db_password = 'Mysql@1234';
$options = [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION];

$message = '';

try {
    $pdo = new PDO($dsn, $db_user, $db_password, $options);

      
    if (isset($_POST['reg_name']) && isset($_POST['reg_email']) && isset($_POST['reg_password'])) {
        $name = trim($_POST['reg_name']);
        $email = trim($_POST['reg_email']);
        $password = $_POST['reg_password'];
        $password_hash = password_hash($password, PASSWORD_DEFAULT);

        $stmt_check = $pdo->prepare("SELECT id FROM users WHERE email = :email");
        $stmt_check->execute([':email' => $email]);
        if ($stmt_check->fetch()) {
            $message = "Registration failed: Email already exists.";
        } else {
            $stmt = $pdo->prepare("INSERT INTO users (name, email, password_hash) VALUES (:name, :email, :password_hash)");
            $stmt->execute([':name' => $name, ':email' => $email, ':password_hash' => $password_hash]);

            $user_id = $pdo->lastInsertId();

              
            $_SESSION['user_id'] = $user_id;
            $_SESSION['user_name'] = $name;
            $_SESSION['user_email'] = $email;
            $_SESSION['progress_easy'] = 0;
            $_SESSION['progress_intermediate'] = 0;
            $_SESSION['progress_hard'] = 0;
            
              
            header("Location: dashboard.php");
            exit();
        }
    }

      
    if (isset($_POST['login_email']) && isset($_POST['login_password'])) {
        $email = trim($_POST['login_email']);
        $password = $_POST['login_password'];

        $stmt = $pdo->prepare("SELECT * FROM users WHERE email = :email");
        $stmt->execute([':email' => $email]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user && password_verify($password, $user['password_hash'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_name'] = $user['name'];
            $_SESSION['user_email'] = $user['email'];
            $_SESSION['progress_easy'] = $user['progress_easy'];
            $_SESSION['progress_intermediate'] = $user['progress_intermediate'];
            $_SESSION['progress_hard'] = $user['progress_hard'];
            
              
            header("Location: dashboard.php");
            exit();
        } else {
            $message = "Invalid email or password.";
        }
    }

} catch (PDOException $e) {
    $message = "Database error: " . $e->getMessage();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" type="image/x-icon" href="img.jpg">
    <title>Hindi Crossword Game - Login</title>
    <link rel="stylesheet" href="styleReal.css">
</head>
<body>
    <div class="container">
        <?php if ($message): ?>
            <p class="error-message"><?= htmlspecialchars($message) ?></p>
        <?php endif; ?>

        <div id="login-form" class="auth-container">
            <div class="auth-header">
                <h1>Hindi Crossword</h1>
                <p>Sign in to continue your journey</p>
            </div>
            <form id="loginForm" name="login" action="login.php" method="POST">
                <div class="form-group">
                    <label for="loginEmail">Email</label>
                    <input type="email" id="loginEmail" name="login_email" required>
                </div>
                <div class="form-group">
                    <label for="loginPassword">Password</label>
                    <input type="password" id="loginPassword" name="login_password" required>
                </div>
                <button type="submit" name="login_submit" class="btn-primary">Login</button>
            </form>
            <div class="auth-switch">
                Don't have an account? <a href="#" onclick="showRegister()">Sign up</a>
            </div>
        </div>

        <div id="register-form" class="auth-container" style="display: none;">
            <div class="auth-header">
                <h1>Hindi Crossword</h1>
                <p>Create your account</p>
            </div>
            <form id="registerForm" name="register" action="login.php" method="POST">
                <div class="form-group">
                    <label for="registerName">Full Name</label>
                    <input type="text" id="registerName" name="reg_name" required>
                </div>
                <div class="form-group">
                    <label for="registerEmail">Email</label>
                    <input type="email" id="registerEmail" name="reg_email" required>
                </div>
                <div class="form-group">
                    <label for="registerPassword">Password</label>
                    <input type="password" id="registerPassword" name="reg_password" required>
                </div>
                <button type="submit" name="register_submit" class="btn-primary">Register</button>
            </form>
            <div class="auth-switch">
                Already have an account? <a href="#" onclick="showLogin()">Sign in</a>
            </div>
        </div>
    </div>

    <script>
        function showLogin() {
            document.getElementById('login-form').style.display = 'block';
            document.getElementById('register-form').style.display = 'none';
        }

        function showRegister() {
            document.getElementById('login-form').style.display = 'none';
            document.getElementById('register-form').style.display = 'block';
        }
    </script>
</body>
<!-- </html> -->