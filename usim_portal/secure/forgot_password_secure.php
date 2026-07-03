<?php
session_name('USIM_SECURE_SESSION');
session_start();
require_once 'db_secure.php';
require_once 'logging_helper.php';

$message = "";
$simulated_email_link = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $matric_no = trim($_POST['matric_no']);

    $stmt = $pdo->prepare("SELECT * FROM users WHERE matric_no = :matric");
    $stmt->execute(['matric' => $matric_no]);
    $user = $stmt->fetch();

    if ($user) {
        $token = bin2hex(random_bytes(32));
        
        // FIX: We drop the PHP $expires_at variable and let MySQL calculate the timestamp natively
        $stmt_insert = $pdo->prepare("INSERT INTO password_resets (matric_no, token, expires_at) VALUES (:matric, :token, DATE_ADD(NOW(), INTERVAL 15 MINUTE))");
        $stmt_insert->execute([
            'matric' => $matric_no,
            'token' => $token
        ]);

        log_security_event($pdo, $matric_no, 'PASSWORD_RESET_REQ', 'Password reset validation lifetime token issued.', 'INFO');

        $simulated_email_link = "http://localhost/usim_portal/secure/reset_password_secure.php?token=" . $token;
    } else {
        $message = "<div style='color: green; margin-bottom: 15px;'>If the user account exists, an activation token link has been generated below.</div>";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Secure Password Reset Request</title>
    <style>
        body { font-family: Arial, sans-serif; background: #f5f6fa; padding: 50px; }
        .reset-box { max-width: 500px; background: white; padding: 30px; border-radius: 4px; box-shadow: 0 2px 5px rgba(0,0,0,0.1); margin: 0 auto; }
        input[type="text"] { width: 100%; padding: 10px; margin: 10px 0; border: 1px solid #ccc; box-sizing: border-box; }
        button { width: 100%; padding: 12px; background: #1a5276; color: white; border: none; font-weight: bold; cursor: pointer; }
        .email-simulation { background: #e8f4fd; border: 1px dashed #3498db; padding: 15px; margin-top: 20px; border-radius: 4px; word-break: break-all; }
    </style>
</head>
<body>

<div class="reset-box">
    <h2>USIM Portal - Reset Request (SECURE)</h2>
    <a href="login_secure.php" style="color: #1a5276; font-size: 0.9em; text-decoration:none;">&larr; Back to Login</a>
    <hr style="border:0; border-top: 1px solid #eee; margin: 15px 0;">

    <?php echo $message; ?>

    <form method="POST" action="">
        <label>Enter Username / Matric ID:</label>
        <input type="text" name="matric_no" placeholder="e.g., 1230500" required autocomplete="off">
        <button type="submit">Generate Secure Reset Link</button>
    </form>

    <?php if (!empty($simulated_email_link)): ?>
        <div class="email-simulation">
            <p style="margin-top:0; font-weight:bold; color:#2980b9;">📬 Simulated Outbound Email Notification Channel:</p>
            <p style="font-size:0.9em; color:#555;">Clicking this temporary single-use cryptographic verification token token link allows credentials customization workflow:</p>
            <a href="<?php echo htmlspecialchars($simulated_email_link, ENT_QUOTES, 'UTF-8'); ?>" style="font-weight:bold; color:#1a5276;">
                <?php echo htmlspecialchars($simulated_email_link, ENT_QUOTES, 'UTF-8'); ?>
            </a>
        </div>
    <?php endif; ?>
</div>

</body>
</html>