<?php
session_name('USIM_INSECURE_SESSION');
session_start();
require_once 'db_insecure.php';

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    die("<h3>Access Denied. Lecturer administrative clearance required.</h3>");
}

$message = "";

// Handle form submission (POST)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $matric_no = $_POST['matric_no'];
    $course_code = $_POST['course_code'];
    $new_grade = $_POST['new_grade'];
    $new_gpa = $_POST['new_gpa'];
    $new_cgpa = $_POST['new_cgpa'];
    
    $sql_metrics = "UPDATE academic_records SET gpa = '$new_gpa', cgpa = '$new_cgpa' WHERE matric_no = '$matric_no'";
    $conn->query($sql_metrics);
    
    $sql_course = "UPDATE grades SET grade = '$new_grade' WHERE matric_no = '$matric_no' AND course_code = '$course_code'";
    $conn->query($sql_course);

    $message = "<div style='color: #d9534f; background: #fdf2f2; padding: 12px; margin-bottom: 15px; border: 1px solid #d9534f;'>
                    <strong>Administrative Override Successful:</strong><br>
                    • Course [$course_code] changed to Grade [$new_grade]<br>
                    • Final Metrics updated to GPA: $new_gpa | CGPA: $new_cgpa
                </div>";
}

// Grab ONLY the matric_no parameter passed from the dashboard link
$target_matric = isset($_GET['matric_no']) ? $_GET['matric_no'] : '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Insecure Grade Matrix Editor</title>
    <style>
        body { font-family: Arial, sans-serif; padding: 30px; background-color: #f5f6fa; color: #333; }
        .editor-container { max-width: 600px; background: white; padding: 30px; border-radius: 4px; box-shadow: 0 2px 5px rgba(0,0,0,0.1); }
        h2 { color: #2c3e50; margin-top: 0; }
        .xss-banner { background: #fff3cd; color: #856404; padding: 12px; border-left: 5px solid #ffc107; margin-bottom: 25px; }
        label { font-weight: bold; display: block; margin-top: 15px; font-size: 0.9em; color: #555; }
        input[type="text"], select { width: 100%; padding: 10px; margin-top: 5px; border: 1px solid #ccc; border-radius: 4px; box-sizing: border-box; }
        .btn-submit { background: #d9534f; color: white; border: none; padding: 12px 20px; font-weight: bold; border-radius: 4px; cursor: pointer; margin-top: 25px; width: 100%; }
    </style>
</head>
<body>

<div class="editor-container">
    <h2>Academic Record Master Override</h2>
    <a href="admin_dashboard_insecure.php" style="text-decoration: none; color: #3498db; font-size: 0.9em;">&larr; Back to Lecturer Dashboard</a>
    <hr style="border: 0; border-top: 1px solid #eee; margin: 15px 0;">

    <div class="xss-banner">
        Modifying Academic Profile for Matric No: <strong><?php echo $target_matric; ?></strong>
    </div>

    <?php echo $message; ?>

    <form method="POST" action="">
        <label>Target Student Matric:</label>
        <input type="text" name="matric_no" value="<?php echo $target_matric; ?>" readonly>

        <fieldset style="border: 1px solid #ddd; padding: 15px; border-radius: 4px; margin-top: 20px;">
            <legend style="font-weight: bold; color: #2c3e50; padding: 0 10px;">Subject Grade Modification</legend>
            
            <label>Select Course Code:</label>
            <select name="course_code">
                <option value="SKE3012">SKE3012 - CYBER DEVELOPMENT</option>
                <option value="SKJ3013">SKJ3013 - ADVANCED JAVA PROGRAMMING</option>
                <option value="SKJ3143">SKJ3143 - INFORMATION SECURITY MANAGEMENT</option>
                <option value="SKJ3183">SKJ3183 - ARTIFICIAL INTELLIGENCE</option>
                <option value="SKJ3192">SKJ3192 - DIGITAL TECHNOLOGY</option>
                <option value="SKJ4143">SKJ4143 - CRYPTOGRAPHY AND APPLICATION</option>
                <option value="UTU3012">UTU3012 - ENTREPRENEURSHIP</option>
            </select>

            <label>Assign New Grade:</label>
            <select name="new_grade">
                <option value="A+">A+</option><option value="A">A</option><option value="A-">A-</option>
                <option value="B+">B+</option><option value="B">B</option><option value="B-">B-</option>
                <option value="C+">C+</option><option value="C">C</option><option value="F">F</option>
            </select>
        </fieldset>

        <fieldset style="border: 1px solid #ddd; padding: 15px; border-radius: 4px; margin-top: 20px;">
            <legend style="font-weight: bold; color: #2c3e50; padding: 0 10px;">Final Score Metric Override</legend>
            
            <label>Override Semester GPA (PNGS):</label>
            <input type="text" name="new_gpa" placeholder="e.g., 3.85" required>

            <label>Override Cumulative CGPA (PNGK):</label>
            <input type="text" name="new_cgpa" placeholder="e.g., 3.86" required>
        </fieldset>

        <button type="submit" class="btn-submit">Commit Insecure Overrides</button>
    </form>
</div>

</body>
</html>