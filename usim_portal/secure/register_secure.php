<?php
session_name('USIM_SECURE_SESSION');
session_start();
require_once 'db_secure.php';
require_once 'logging_helper.php';

$message = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // 1. Sanitize standard string inputs (Slide 28 & 29)
    $matric_no = filter_input(INPUT_POST, 'matric_no', FILTER_SANITIZE_SPECIAL_CHARS);
    $name      = filter_input(INPUT_POST, 'name', FILTER_SANITIZE_SPECIAL_CHARS);
    $password  = $_POST['password'];
    
    // Sanitize Email using specific built-in filters (Slide 29)
    $email     = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
    $phone_no  = filter_input(INPUT_POST, 'phone_no', FILTER_SANITIZE_SPECIAL_CHARS);
    
    // Administrative business rule: Hardcoded server-side to prevent parameter tampering
    $role      = 'student'; 
    
    $profile_pic_name = 'default_avatar.png'; 
    $upload_ok = true;

    // =========================================================================
    // VALIDATION PART A: EMAIL & PHONE NUMBER VALIDATION (Slide 28 & Regex)
    // =========================================================================
    
    // Phone Number Regex Policy: Expects Malaysian standard format (e.g., 0123456789 or 01112345678)
    // Formula: Must start with 01, followed by a digit, optional hyphen, and 7 to 8 trailing digits.
    $phone_regex = '/^01[0-9]-?\d{7,8}$/';

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        // Enforces Slide 28: FILTER_VALIDATE_EMAIL
        $message = "<div style='color: #721c24; background: #f8d7da; padding: 10px; border-left: 4px solid #dc3545;'>Input Validation Error: Invalid syntax format detected within the Email string component.</div>";
        $upload_ok = false;
    } elseif (!preg_match($phone_regex, $phone_no)) {
        // Enforces structural pattern alignment using Regex
        $message = "<div style='color: #721c24; background: #f8d7da; padding: 10px; border-left: 4px solid #dc3545;'>Input Validation Error: Phone number must align with standard system constraints (e.g., 01XXXXXXXX).</div>";
        $upload_ok = false;
    }

    // =========================================================================
    // VALIDATION PART B: PASSWORD STRENGTH POLICY (Slide 3)
    // =========================================================================
    if ($upload_ok) {
        if (strlen($password) <= 9) {
            $message = "<div style='color: #721c24; background: #f8d7da; padding: 10px; border-left: 4px solid #dc3545;'>Security Policy Violation: Password must be longer than 9 characters.</div>";
            $upload_ok = false;
        } elseif (!preg_match('/[^a-zA-Z0-9]/', $password)) {
            $message = "<div style='color: #721c24; background: #f8d7da; padding: 10px; border-left: 4px solid #dc3545;'>Security Policy Violation: Password must contain at least 1 special character.</div>";
            $upload_ok = false;
        }
    }

    // =========================================================================
    // VALIDATION PART C: FILE UPLOAD SECURITY (Slide 4)
    // =========================================================================
    if ($upload_ok && isset($_FILES['profile_pic']) && $_FILES['profile_pic']['error'] === UPLOAD_ERR_OK) {
        $file_tmp_path = $_FILES['profile_pic']['tmp_name'];
        $original_filename = $_FILES['profile_pic']['name'];
        
        $file_extension = strtolower(pathinfo($original_filename, PATHINFO_EXTENSION));
        $allowed_extensions = ['jpg', 'jpeg', 'png'];

        if (!in_array($file_extension, $allowed_extensions)) {
            $message = "<div style='color: #721c24; background: #f8d7da; padding: 10px; border-left: 4px solid #dc3545;'>File Upload Security Error: Dangerous file type configuration rejected.</div>";
            $upload_ok = false;
        } else {
            $encrypted_filename = bin2hex(random_bytes(16)) . '.' . $file_extension;
            $upload_dir = 'uploads/';
            $dest_path = $upload_dir . $encrypted_filename;

            if (move_uploaded_file($file_tmp_path, $dest_path)) {
                $profile_pic_name = $encrypted_filename;
            } else {
                $message = "<div style='color: red;'>System Error: Failed to process file upload structure safely.</div>";
                $upload_ok = false;
            }
        }
    }

    // =========================================================================
    // EXECUTE REGISTRATION TRANSACTION
    // =========================================================================
    if ($upload_ok) {
        try {
            $stmt_check = $pdo->prepare("SELECT matric_no FROM users WHERE matric_no = :matric");
            $stmt_check->execute(['matric' => $matric_no]);
            
            if ($stmt_check->fetch()) {
                $message = "<div style='color: red;'>Registration Error: Identifier mapping conflict. User already exists.</div>";
            } else {
                $hashed_password = password_hash($password, PASSWORD_BCRYPT, ['cost' => 12]);

                $stmt_insert = $pdo->prepare("INSERT INTO users (matric_no, name, email, phone_no, password, role, profile_pic) 
                                             VALUES (:matric, :name, :email, :phone, :pass, :role, :pic)");
                $stmt_insert->execute([
                    'matric'   => $matric_no,
                    'name'     => $name,
                    'email'    => $email,
                    'phone'    => $phone_no,
                    'pass'     => $hashed_password,
                    'role'     => $role,
                    'pic'      => $profile_pic_name
                ]);

                log_security_event($pdo, $matric_no, 'USER_REGISTRATION', 'Registered new student profile with verified contact endpoints.', 'INFO');

                $message = "<div style='color: #155724; background: #d4edda; padding: 12px; font-weight:bold; border-left: 5px solid #28a745;'>
                                Student profile successfully registered! Security boundaries established.
                            </div>";
            }
        } catch (\PDOException $e) {
            $message = "<div style='color: red;'>Database failure: " . htmlspecialchars($e->getMessage(), ENT_QUOTES, 'UTF-8') . "</div>";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Secure Student Registration</title>
    <style>
        body { font-family: Arial, sans-serif; background: #f5f6fa; padding: 40px; }
        .reg-box { max-width: 500px; background: white; padding: 30px; border-radius: 4px; box-shadow: 0 2px 5px rgba(0,0,0,0.1); margin: 0 auto; }
        label { font-weight: bold; display: block; margin-top: 15px; font-size: 0.9em; color: #555; }
        input[type="text"], input[type="password"], input[type="file"], input[type="email"] { width: 100%; padding: 10px; margin-top: 5px; border: 1px solid #ccc; border-radius: 4px; box-sizing: border-box; }
        button { width: 100%; padding: 12px; background: #007a7a; color: white; border: none; font-weight: bold; cursor: pointer; margin-top: 25px; border-radius: 4px; }
        button:hover { background: #005c5c; }
    </style>
</head>
<body>

<div class="reg-box">
    <h2>USIM Student Registration (SECURE)</h2>
    <a href="login_secure.php" style="text-decoration: none; color: #007a7a; font-size: 0.9em;">&larr; Return to Sign In Interface</a>
    <hr style="border:0; border-top: 1px solid #eee; margin: 15px 0;">

    <?php echo $message; ?>

    <form method="POST" action="" enctype="multipart/form-data">
        <label>Matric No:</label>
        <input type="text" name="matric_no" placeholder="e.g. 1220000" required autocomplete="off">

        <label>Full Name:</label>
        <input type="text" name="name" placeholder="As per national identity card" required autocomplete="off">

        <label>University Email Address:</label>
        <input type="email" name="email" placeholder="e.g. student@sch.usim.edu.my" required autocomplete="off">

        <label>Mobile Number:</label>
        <input type="text" name="phone_no" placeholder="e.g. 0123456789" required autocomplete="off">

        <label>Create Portal Password:</label>
        <input type="password" name="password" placeholder="Must be > 9 characters with a special symbol" required>

        <label>Upload Profile Image Matrix (JPG/PNG):</label>
        <input type="file" name="profile_pic" accept=".jpg, .jpeg, .png">

        <button type="submit">Establish Secure Profile Schema</button>
    </form>
</div>

</body>
</html>