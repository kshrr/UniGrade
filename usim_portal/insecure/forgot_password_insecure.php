<?php
session_name('USIM_INSECURE_SESSION');
session_start();
require_once 'db_insecure.php';

$message = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $matric_no = $_POST['matric_no'];
    $new_password = $_POST['new_password'];

    // VULNERABILITY 1: SQL Injection & Plaintext storage (A04 + A05)
    // No identity verification check. We blindly trust the user input.
    $sql = "UPDATE users SET password = '$new_password' WHERE matric_no = '$matric_no'";
    
    if ($conn->query($sql) === TRUE) {
        $message = "<div style='color: green; margin-bottom: 15px; font-weight: bold;'>
                        Password updated successfully for account: $matric_no (Plaintext Stored)!
                    </div>";
    } else {
        $message = "<div style='color: red;'>Error updating password.</div>";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Insecure Password Reset</title>
    <style>
        body { font-family: Arial, sans-serif; background: #f5f6fa; padding: 50px; }
        .reset-box { max-width: 400px; background: white; padding: 30px; border-radius: 4px; box-shadow: 0 2px 5px rgba(0,0,0,0.1); margin: 0 auto; }
        input[type="text"], input[type="password"] { width: 100%; padding: 10px; margin: 10px 0; border: 1px solid #ccc; box-sizing: border-box; }
        button { width: 100%; padding: 12px; background: #d9534f; color: white; border: none; font-weight: bold; cursor: pointer; }
    </style>
</head>
<body>

<div class="reset-box">
    <h2>USIM Portal - Reset Password (INSECURE)</h2>
    <a href="login_insecure.php" style="color: #666; font-size: 0.9em;">&larr; Back to Login</a>
    <hr style="border:0; border-top: 1px solid #eee; margin: 15px 0;">
    
    <?php echo $message; ?>

    <form method="POST" action="">
        <label>Enter Username / Matric ID:</label>
        <input type="text" name="matric_no" placeholder="e.g., 1230500" required>

        <label>Enter New Password:</label>
        <input type="password" name="new_password" placeholder="e.g., AttackerControlled123" required>

        <button type="submit">Change Password</button>
    </form>
</div>

</body>
</html>