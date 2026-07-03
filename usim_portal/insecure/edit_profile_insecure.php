<?php
session_name('USIM_INSECURE_SESSION');
session_start();
require_once 'db_insecure.php';

if (!isset($_SESSION['matric_no'])) {
    header("Location: login_insecure.php");
    exit();
}

$matric_no = $_SESSION['matric_no'];
$message = "";

$stmt_fetch = $conn->query("SELECT * FROM users WHERE matric_no = '$matric_no'");
$user = $stmt_fetch->fetch_assoc();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // VULNERABILITY: Direct collection with zero input validation or regex enforcement
    $name     = $_POST['name'];
    $email    = $_POST['email'];
    $phone_no = $_POST['phone_no'];
    
    $profile_pic_name = $user['profile_pic'];

    // VULNERABILITY: Unrestricted upload processing accepts raw types (.php) and leaves original filenames intact
    if (isset($_FILES['profile_pic']) && $_FILES['profile_pic']['error'] === UPLOAD_ERR_OK) {
        $file_tmp_path = $_FILES['profile_pic']['tmp_name'];
        $original_filename = $_FILES['profile_pic']['name'];
        
        $upload_dir = 'uploads/';
        
        // Ensure folder exists so the RCE exploit works without server configuration errors
        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0755, true); 
        }

        $dest_path = $upload_dir . $original_filename;

        // VULNERABILITY: Move uploaded file blindly. If they upload shell.php, it goes straight to the folder.
        if (move_uploaded_file($file_tmp_path, $dest_path)) {
            $profile_pic_name = $original_filename;
        }
    }

    try {
        // VULNERABILITY: SQL Injection via unescaped direct concatenation string mapping
        $raw_update = "UPDATE users SET name = '$name', email = '$email', phone_no = '$phone_no', profile_pic = '$profile_pic_name' WHERE matric_no = '$matric_no'";
        
        $conn->query($raw_update);
        
        // Re-fetch so the user sees their changes instantly (including XSS payloads)
        $stmt_fetch = $conn->query("SELECT * FROM users WHERE matric_no = '$matric_no'");
        $user = $stmt_fetch->fetch_assoc();

        $message = "<div style='color: green; background: #e2f0d9; padding: 10px; border: 1px solid #b4c6e7;'>[INSECURE LOG] Data synchronized raw into SQL interpreter.</div>";
    } catch (\Exception $e) {
        $message = "<div style='color: red;'>Database Fault: " . $e->getMessage() . "</div>";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Insecure Profile Alteration</title>
    <style>
        body { font-family: Arial, sans-serif; background: #fdf2e9; padding: 40px; }
        .edit-box { max-width: 500px; background: white; padding: 30px; border: 2px solid #e67e22; margin: 0 auto; }
        label { font-weight: bold; display: block; margin-top: 15px; }
        input { width: 100%; padding: 10px; margin-top: 5px; box-sizing: border-box; }
        button { width: 100%; padding: 12px; background: #e67e22; color: white; border: none; font-weight: bold; margin-top: 25px; }
    </style>
</head>
<body>

<div class="edit-box">
    <h2 style="color: #e67e22;">Modify Account Profile (INSECURE)</h2>
    <a href="transcript_insecure.php" style="color: #e67e22;">&larr; Back to Dashboard</a>
    <hr style="border:0; border-top: 1px solid #eee; margin: 15px 0;">

    <?php echo $message; ?>

    <form method="POST" action="" enctype="multipart/form-data">
        <label>Full Name:</label>
        <input type="text" name="name" value="<?php echo $user['name']; ?>">

        <label>Email:</label>
        <input type="text" name="email" value="<?php echo $user['email']; ?>">

        <label>Phone Number:</label>
        <input type="text" name="phone_no" value="<?php echo $user['phone_no']; ?>">

        <label>Upload Profile Picture (UNRESTRICTED):</label>
        <input type="file" name="profile_pic">

        <button type="submit">Submit Raw Profile Changes</button>
    </form>
</div>

</body>
</html>