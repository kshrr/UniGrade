<?php
session_name('USIM_SECURE_SESSION');
session_start();
require_once 'db_secure.php';
require_once 'logging_helper.php';

if (!isset($_SESSION['matric_no'])) {
    header("Location: login_secure.php");
    exit();
}

$matric_no = $_SESSION['matric_no'];
$message = "";

// 1. Fetch current details to populate form fields safely
$stmt_fetch = $pdo->prepare("SELECT * FROM users WHERE matric_no = :matric");
$stmt_fetch->execute(['matric' => $matric_no]);
$user = $stmt_fetch->fetch();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Sanitize string data contexts (Slide 28 & 29)
    $name     = filter_input(INPUT_POST, 'name', FILTER_SANITIZE_SPECIAL_CHARS);
    $email    = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
    $phone_no = filter_input(INPUT_POST, 'phone_no', FILTER_SANITIZE_SPECIAL_CHARS);
    
    $profile_pic_name = $user['profile_pic']; // Retain current picture if no new file uploaded
    $upload_ok = true;

    // VALIDATION: Email syntax format validation (Slide 28)
    $phone_regex = '/^01[0-9]-?\d{7,8}$/';

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $message = "<div style='color: #721c24; background: #f8d7da; padding: 10px; border-left: 4px solid #dc3545;'>Validation Error: Invalid email format syntax.</div>";
        $upload_ok = false;
    } elseif (!preg_match($phone_regex, $phone_no)) {
        $message = "<div style='color: #721c24; background: #f8d7da; padding: 10px; border-left: 4px solid #dc3545;'>Validation Error: Phone number format mismatch (e.g., 01XXXXXXXX).</div>";
        $upload_ok = false;
    }

    // FILE UPLOAD SECURITY (Slide 4)
    if ($upload_ok && isset($_FILES['profile_pic']) && $_FILES['profile_pic']['error'] === UPLOAD_ERR_OK) {
        $file_tmp_path = $_FILES['profile_pic']['tmp_name'];
        $original_filename = $_FILES['profile_pic']['name'];
        
        // Rule 1: Extract and strictly whitelist file extensions
        $file_extension = strtolower(pathinfo($original_filename, PATHINFO_EXTENSION));
        $allowed_extensions = ['jpg', 'jpeg', 'png'];

        if (!in_array($file_extension, $allowed_extensions)) {
            $message = "<div style='color: #721c24; background: #f8d7da; padding: 10px; border-left: 4px solid #dc3545;'>Security Policy Error: Dangerous or unauthorized file extension rejected.</div>";
            $upload_ok = false;
        } else {
            // Rule 2: Obfuscate name context by generating a secure random string hash
            $encrypted_filename = bin2hex(random_bytes(16)) . '.' . $file_extension;
            $upload_dir = 'uploads/';
            
            // SMART FIX: Check if folder exists, if not, create securely
            if (!is_dir($upload_dir)) {
                mkdir($upload_dir, 0755, true); 
            }

            $dest_path = $upload_dir . $encrypted_filename;

            if (move_uploaded_file($file_tmp_path, $dest_path)) {
                $profile_pic_name = $encrypted_filename;
            } else {
                $message = "<div style='color: red;'>System Error: File system transfer failure.</div>";
                $upload_ok = false;
            }
        }
    }

    // EXECUTE RECORD SYNCHRONIZATION
    if ($upload_ok) {
        try {
            $stmt_update = $pdo->prepare("UPDATE users SET name = :name, email = :email, phone_no = :phone, profile_pic = :pic WHERE matric_no = :matric");
            $stmt_update->execute([
                'name'   => $name,
                'email'  => $email,
                'phone'  => $phone_no,
                'pic'    => $profile_pic_name,
                'matric' => $matric_no
            ]);

            log_security_event($pdo, $matric_no, 'PROFILE_UPDATE_SUCCESS', 'User successfully synchronized profile information matrix.', 'INFO');
            
            // Re-fetch updated information to update the form view parameters
            $user['name'] = $name;
            $user['email'] = $email;
            $user['phone_no'] = $phone_no;
            $user['profile_pic'] = $profile_pic_name;

            $message = "<div style='color: #155724; background: #d4edda; padding: 12px; font-weight:bold; border-left: 5px solid #28a745;'>Profile properties updated smoothly!</div>";
        } catch (\PDOException $e) {
            $message = "<div style='color: red;'>Transactional persistence error occurred.</div>";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Secure Profile Customization Interface</title>
    <style>
        body { font-family: Arial, sans-serif; background: #f5f6fa; padding: 40px; }
        .edit-box { max-width: 500px; background: white; padding: 30px; border-radius: 4px; box-shadow: 0 2px 5px rgba(0,0,0,0.1); margin: 0 auto; }
        label { font-weight: bold; display: block; margin-top: 15px; font-size: 0.9em; color: #555; }
        input[type="text"], input[type="file"], input[type="email"] { width: 100%; padding: 10px; margin-top: 5px; border: 1px solid #ccc; border-radius: 4px; box-sizing: border-box; }
        button { width: 100%; padding: 12px; background: #007a7a; color: white; border: none; font-weight: bold; cursor: pointer; margin-top: 25px; border-radius: 4px; }
    </style>
</head>
<body>

<div class="edit-box">
    <h2>Modify Account Profile (SECURE)</h2>
    <a href="transcript_secure.php" style="text-decoration: none; color: #007a7a; font-size: 0.9em;">&larr; Return to Dashboard Matrix</a>
    <hr style="border:0; border-top: 1px solid #eee; margin: 15px 0;">

    <?php echo $message; ?>

    <form method="POST" action="" enctype="multipart/form-data">
        <label>Full Name:</label>
        <input type="text" name="name" value="<?php echo htmlspecialchars($user['name'], ENT_QUOTES, 'UTF-8'); ?>" required autocomplete="off">

        <label>Primary Institutional Email:</label>
        <input type="email" name="email" value="<?php echo htmlspecialchars($user['email'] ?? '', ENT_QUOTES, 'UTF-8'); ?>" required autocomplete="off">

        <label>Mobile Endpoint Number:</label>
        <input type="text" name="phone_no" value="<?php echo htmlspecialchars($user['phone_no'] ?? '', ENT_QUOTES, 'UTF-8'); ?>" required autocomplete="off">

        <label>Replace Current Profile Image Array (JPG/PNG):</label>
        <input type="file" name="profile_pic" accept=".jpg, .jpeg, .png">

        <button type="submit">Commit Verified Profile Updates</button>
    </form>
</div>

</body>
</html>