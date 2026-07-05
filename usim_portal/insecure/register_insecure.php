<?php
session_name('USIM_INSECURE_SESSION'); 
session_start();
require_once 'db_insecure.php';
require_once 'academic_helper_insecure.php';

$message = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    ensure_insecure_academic_schema($conn);

    // VULNERABILITY: Raw collection patterns with zero syntactic formatting verification
    $matric_no = $_POST['matric_no'];
    $name      = $_POST['name'];
    $email     = $_POST['email'];
    $phone_no  = $_POST['phone_no'];
    $password  = $_POST['password']; 
    $role      = 'student'; 
    
    $profile_pic_name = 'default_avatar.png';

    if (isset($_FILES['profile_pic']) && $_FILES['profile_pic']['error'] === UPLOAD_ERR_OK) {
        $file_tmp_path = $_FILES['profile_pic']['tmp_name'];
        $original_filename = $_FILES['profile_pic']['name'];
        
        $upload_dir = 'uploads/';
        $dest_path = $upload_dir . $original_filename;

        if (move_uploaded_file($file_tmp_path, $dest_path)) {
            $profile_pic_name = $original_filename;
        }
    }

    try {
        // Direct string concatenation susceptible to SQL injection variants
        $raw_sql = "INSERT INTO users (matric_no, name, email, phone_no, password, role, profile_pic) 
                    VALUES ('$matric_no', '$name', '$email', '$phone_no', '$password', '$role', '$profile_pic_name')";
        
        $conn->query($raw_sql);
        seed_insecure_student_records($conn, $matric_no);

        $_SESSION['success'] = "Registration successful! Please log in.";
        header("Location: login_insecure.php");
        exit();

        $message = "<div style='color: green; background: #e2f0d9; padding: 10px; border: 1px solid #b4c6e7;'>
                        Student profile registered loosely via raw execution sequences.
                    </div>";
    } catch (\Exception $e) {
        $message = "<div style='color: red;'>Database Error: " . $e->getMessage() . "</div>";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Insecure Student Registration Portal</title>
    <style>
        body { font-family: Arial, sans-serif; background: #fdf2e9; padding: 40px; }
        .reg-box { max-width: 500px; background: white; padding: 30px; border-radius: 4px; box-shadow: 0 2px 5px rgba(0,0,0,0.1); margin: 0 auto; border: 2px solid #e67e22; }
        label { font-weight: bold; display: block; margin-top: 15px; font-size: 0.9em; color: #555; }
        input[type="text"], input[type="password"], input[type="file"] { width: 100%; padding: 10px; margin-top: 5px; border: 1px solid #ccc; border-radius: 4px; box-sizing: border-box; }
        button { width: 100%; padding: 12px; background: #e67e22; color: white; border: none; font-weight: bold; cursor: pointer; margin-top: 25px; border-radius: 4px; }
    </style>
</head>
<body>

<div class="reg-box">
    <h2 style="color: #e67e22;">USIM Student Registration (INSECURE DEMO)</h2>
    <hr style="border:0; border-top: 1px solid #eee; margin: 15px 0;">

    <?php echo $message; ?>

    <form method="POST" action="" enctype="multipart/form-data">
        <label>Matric No:</label>
        <input type="text" name="matric_no" required>

        <label>Full Name:</label>
        <input type="text" name="name" required>

        <label>Email:</label>
        <input type="text" name="email" required>

        <label>Phone Number:</label>
        <input type="text" name="phone_no" required>

        <label>Password:</label>
        <input type="password" name="password" required>

        <label>Upload Profile Picture:</label>
        <input type="file" name="profile_pic">

        <button type="submit">Submit Raw Registration Request</button>
    </form>
</div>

</body>
</html>
