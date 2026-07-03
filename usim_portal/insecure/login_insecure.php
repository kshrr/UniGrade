<?php
session_name('USIM_INSECURE_SESSION');
session_start();
require_once 'db_insecure.php';

$error = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $matric_no = $_POST['matric_no'];
    $password = $_POST['password'];

    // CRITICAL VULNERABILITY: Raw string concatenation (SQL Injection)
    $sql = "SELECT * FROM users WHERE matric_no = '$matric_no' AND password = '$password'";
    $result = $conn->query($sql);

    if ($result && $result->num_rows > 0) {
        $user = $result->fetch_assoc();
        
        $_SESSION['matric_no'] = $user['matric_no'];
        $_SESSION['role'] = $user['role'];
        $_SESSION['name'] = $user['name'];

        // --- MUTUAL EXCLUSION: Destroy any active secure session ---
        if (isset($_COOKIE['USIM_SECURE_SESSION'])) {
            setcookie('USIM_SECURE_SESSION', '', time() - 3600, '/'); // Force browser to delete the cookie
        }
        // -----------------------------------------------------------
        
        if ($_SESSION['role'] === 'admin') {
            header("Location: admin_dashboard_insecure.php");
        } else {
            header("Location: transcript_insecure.php");
        }
        exit();
    } else {
        $error = "Invalid matric number or password.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>USIM ID - Sign In (Insecure)</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            margin: 0;
            padding: 0;
            /* Placeholder background color if image is missing */
            background-color: #87CEEB; 
            /* You can add your exact background image here later */
            background-image: url('../assets/background.jpg');
            background-size: cover;
            background-position: center;
            background-attachment: fixed;
            display: flex;
            justify-content: flex-end;
            align-items: center;
            height: 100vh;
        }
        .login-wrapper {
            width: 100%;
            max-width: 450px;
            margin-right: 8%; /* Pushes the card to the right side like the screenshot */
        }
        .login-card {
            background: #ffffff;
            padding: 40px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.2);
        }
        .logo-container {
            text-align: center;
            margin-bottom: 20px;
            border-bottom: 2px solid #007a7a;
            padding-bottom: 15px;
        }
        .logo-container h2 {
            margin: 10px 0 0 0;
            font-weight: 500;
            font-size: 1.5em;
            color: #333;
        }
        .form-group {
            margin-bottom: 15px;
        }
        .form-group label {
            display: block;
            font-size: 0.85em;
            font-weight: bold;
            margin-bottom: 5px;
            color: #333;
        }
        .form-group input[type="text"],
        .form-group input[type="password"] {
            width: 100%;
            padding: 12px;
            background-color: #3b424d; /* Dark grey input fields */
            color: white;
            border: none;
            box-sizing: border-box;
            font-size: 1em;
        }
        .checkbox-group {
            margin-bottom: 20px;
            font-size: 0.85em;
            color: #333;
            display: flex;
            align-items: center;
        }
        .checkbox-group input {
            margin-right: 8px;
        }
        .btn-submit {
            width: 100%;
            background-color: #007a7a; /* USIM Teal */
            color: white;
            padding: 12px;
            border: none;
            font-size: 1em;
            cursor: pointer;
            transition: background 0.3s;
        }
        .btn-submit:hover {
            background-color: #005c5c;
        }
        .footer-links {
            margin-top: 20px;
            font-size: 0.8em;
            color: #555;
            line-height: 1.6;
        }
        .footer-links a {
            color: #007a7a;
            text-decoration: none;
            display: block;
        }
        .footer-links a:hover {
            text-decoration: underline;
        }
        .error-msg {
            background: #ffe6e6;
            color: #d9534f;
            padding: 10px;
            border-left: 4px solid #d9534f;
            margin-bottom: 15px;
            font-size: 0.9em;
        }
        .version-tag {
            text-align: center;
            color: #d9534f;
            font-weight: bold;
            font-size: 0.8em;
            margin-bottom: 10px;
        }
    </style>
</head>
<body>

    <div class="login-wrapper">
        <div class="login-card">
            
            <div class="version-tag">TEST ENVIRONMENT (INSECURE)</div>

            <div class="logo-container">
                <img src="../assets/logo.png" alt="USIM Logo" style="height: 50px;" onerror="this.style.display='none'">
                <h2>Sign in to your account</h2>
            </div>

            <?php if($error): ?>
                <div class="error-msg"><?php echo $error; ?></div>
            <?php endif; ?>

            <form action="" method="POST">
                <div class="form-group">
                    <label>USIM ID / Staff or Matric No. / USIM E-mail</label>
                    <input type="text" name="matric_no" required>
                </div>
                
                <div class="form-group">
                    <label>Password</label>
                    <input type="password" name="password">
                </div>

                <div class="checkbox-group">
                    <input type="checkbox" id="remember">
                    <label for="remember" style="margin:0; font-weight:normal;">Remember me</label>
                </div>

                <button type="submit" class="btn-submit">Sign In</button>
            </form>

            <div style="text-align: center; margin-top: 20px; font-family: Arial, sans-serif; border-top: 1px solid #eee; padding-top: 15px;">
      
    <p style="font-size: 0.8em; color: #7f8c8d; margin: 15px 0 0 0;">
        Forgot your password? Please visit:
    </p>
    <p style="font-size: 0.85em; margin-top: 5px;">
        <a href="forgot_password_insecure.php" style="color: #1a5276; font-weight: bold; text-decoration: none;">Staff</a> 
        <span style="color: #ccc;">|</span> 
        <a href="forgot_password_insecure.php" style="color: #1a5276; font-weight: bold; text-decoration: none;">Student</a>
    </p>
    <p style="font-size: 0.85em; color: #7f8c8d; margin: 0 0 10px 0;">
        New student? Register your portal credentials below:
    </p>
    <a href="register_insecure.php" style="color: #007a7a; font-weight: bold; text-decoration: none; font-size: 0.95em;">
        &rarr; Create New Student Account
    </a>
</div>
        </div>
    </div>

</body>
</html>