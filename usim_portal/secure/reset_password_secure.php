<?php
session_name('USIM_SECURE_SESSION');
session_start();
require_once 'db_secure.php';
require_once 'logging_helper.php';

$message = "";
$token_validated = false;
$token = isset($_GET['token']) ? trim($_GET['token']) : '';

if (empty($token)) {
    die("<h3>Security Error: Missing token argument mapping bounds. Transaction rejected.</h3>");
}

// FIX 2: Validate Token State constraints (Expiration & Single-Use Status Check)
$stmt = $pdo->prepare("SELECT * FROM password_resets WHERE token = :token AND is_used = 0 AND expires_at > NOW() LIMIT 1");
$stmt->execute(['token' => $token]);
$reset_request = $stmt->fetch();

if ($reset_request) {
    $token_validated = true;
    $target_user = $reset_request['matric_no'];
} else {
    $message = "<div style='color: red; font-weight:bold;'>Security Exception: Token signature validation mismatch or lifecycle lifetime bounds expired.</div>";
}

// Handle Form Submission Step after verified token context passes validation checks
if ($token_validated && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];

    // =========================================================================
    // PASSWORD STRENGTH POLICY ENFORCEMENT 
    // =========================================================================
    if ($new_password !== $confirm_password) {
        $message = "<div style='color: #721c24; background: #f8d7da; padding: 10px; border-left: 4px solid #dc3545;'>Input Validation Mismatch: Passwords do not match.</div>";
    } elseif (strlen($new_password) <= 9) {
        // Policy 1: Longer than 9 characters
        $message = "<div style='color: #721c24; background: #f8d7da; padding: 10px; border-left: 4px solid #dc3545;'>Security Policy Violation: Password must be longer than 9 characters.</div>";
    } elseif (!preg_match('/[^a-zA-Z0-9]/', $new_password)) {
        // Policy 2: At least 1 special character (matches any character that is NOT a letter or number)
        $message = "<div style='color: #721c24; background: #f8d7da; padding: 10px; border-left: 4px solid #dc3545;'>Security Policy Violation: Password must contain at least one special character (e.g., @, #, $, !, %, etc.).</div>";
    } else {
        // =========================================================================
        // PROCEED WITH SECURE DATABASE UPDATE
        // =========================================================================
        try {
            $pdo->beginTransaction();

            // FIX 3: Strong irreversible hashing structure generation processing via Bcrypt Cost 12 (A04)
            $new_hash = password_hash($new_password, PASSWORD_BCRYPT, ['cost' => 12]);

            // 1. Update credential records cleanly inside users application table metadata
            $stmt_update_user = $pdo->prepare("UPDATE users SET password = :hash WHERE matric_no = :matric");
            $stmt_update_user->execute(['hash' => $new_hash, 'matric' => $target_user]);

            // 2. Consume token state immediately to completely shut down replay sequence attack vectors
            $stmt_consume_token = $pdo->prepare("UPDATE password_resets SET is_used = 1 WHERE token = :token");
            $stmt_consume_token->execute(['token' => $token]);

            $pdo->commit();

            // Track standard account management operation inside backend framework activity trail
            log_security_event($pdo, $target_user, 'PASSWORD_RESET_SUCCESS', 'User verified password sequence updated successfully via lifecycle tokens tracking.', 'INFO');

            $message = "<div style='color: #155724; background: #d4edda; padding: 12px; font-weight:bold; margin-bottom: 15px; border-left: 5px solid #28a745;'>
                            Password updated successfully! You can now close this interface and access the login portal.
                        </div>";
            $token_validated = false; // Collapse form display visibility context boundary mapping
        } catch (\PDOException $e) {
            $pdo->rollBack();
            $message = "<div style='color: #721c24; background: #f8d7da; padding: 10px; border-left: 4px solid #dc3545;'>Database transactional integrity protection fault occurred.</div>";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Secure Password Alteration Portal</title>
    <style>
        body { font-family: Arial, sans-serif; background: #f5f6fa; padding: 50px; }
        .reset-box { max-width: 450px; background: white; padding: 30px; border-radius: 4px; box-shadow: 0 2px 5px rgba(0,0,0,0.1); margin: 0 auto; }
        input[type="password"] { width: 100%; padding: 10px; margin: 10px 0; border: 1px solid #ccc; box-sizing: border-box; }
        button { width: 100%; padding: 12px; background: #28a745; color: white; border: none; font-weight: bold; cursor: pointer; margin-top: 10px; }
        .policy-note { font-size: 0.8em; color: #1a5276; background: #e8f4f8; padding: 10px; border-left: 3px solid #1a5276; margin-bottom: 15px; }
    </style>
</head>
<body>

<div class="reset-box">
    <h2>Academic Profile Password Customization Matrix</h2>
    <hr style="border:0; border-top: 1px solid #eee; margin: 15px 0;">

    <?php echo $message; ?>

    <?php if ($token_validated): ?>
        <p style="font-size: 0.9em; color: #555;">
            Token authenticated. Assigning credentials structure updates for identifier: 
            <strong><?php echo htmlspecialchars($target_user, ENT_QUOTES, 'UTF-8'); ?></strong>
        </p>
        
        <div class="policy-note">
            <strong>Security Policy Requirements:</strong><br>
            • Must be longer than 9 characters (10+)<br>
            • Must contain at least 1 special character
        </div>
        
        <form method="POST" action="">
            <label>Specify New Cryptographic Password Array:</label>
            <input type="password" name="new_password" placeholder="e.g. MySecurePass!123" required autocomplete="off">

            <label>Verify Cryptographic Passphrase Alignment Structure:</label>
            <input type="password" name="confirm_password" placeholder="Re-enter password matching strings exactly" required autocomplete="off">

            <button type="submit">Commit Verified Passphrase Update</button>
        </form>
    <?php endif; ?>
</div>

</body>
</html>