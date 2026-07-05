<?php
session_name('USIM_SECURE_SESSION');
session_start();
require_once 'db_secure.php';
require_once 'logging_helper.php';
require_once 'academic_helper_secure.php';

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    die("<h3>Access Denied. Lecturer administrative clearance required.</h3>");
}

$message = "";

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

ensure_secure_academic_schema($pdo);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_POST['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
        log_security_event($pdo, $_SESSION['matric_no'], 'CSRF_ATTEMPT', 'Unauthorized transaction attempt on grade override form.', 'HIGH');
        die("<h3>Security Exception: Transaction token authenticity validation failure.</h3>");
    }

    $matric_no = filter_input(INPUT_POST, 'matric_no', FILTER_SANITIZE_SPECIAL_CHARS);
    $course_code = filter_input(INPUT_POST, 'course_code', FILTER_SANITIZE_SPECIAL_CHARS);
    $new_grade = filter_input(INPUT_POST, 'new_grade', FILTER_SANITIZE_SPECIAL_CHARS);

    $validation_errors = [];

    if (strlen($matric_no) < 5 || strlen($matric_no) > 15) {
        $validation_errors[] = "Matric Number length violates system constraints.";
    }

    if (!array_key_exists($new_grade, secure_grade_point_map())) {
        $validation_errors[] = "Selected grade is outside the supported mapping table.";
    }

    if (!empty($validation_errors)) {
        $error_string = implode("<br>&bull; ", $validation_errors);
        $message = "<div style='color: #721c24; background: #f8d7da; padding: 12px; border-left: 5px solid #dc3545; margin-bottom: 15px;'>
                        <strong>Input Validation Failed:</strong><br>&bull; $error_string
                    </div>";
    } else {
        try {
            $pdo->beginTransaction();
            seed_secure_student_records($pdo, $matric_no);

            $grade_point = secure_grade_point_for_grade($new_grade);
            $stmt_course = $pdo->prepare("UPDATE grades SET grade = :grade, grade_point = :grade_point, status = :status WHERE matric_no = :matric AND course_code = :course");
            $stmt_course->execute([
                'grade' => $new_grade,
                'grade_point' => $grade_point,
                'status' => 'D',
                'matric' => $matric_no,
                'course' => $course_code
            ]);

            $calculated_gpa = update_secure_academic_metrics($pdo, $matric_no);
            mint_secure_transcript_block($pdo, $matric_no);
            $pdo->commit();

            $display_gpa = $calculated_gpa === null ? 'Pending' : number_format($calculated_gpa, 2);
            log_security_event($pdo, $_SESSION['matric_no'], 'GRADE_OVERRIDE', "Altered $matric_no: Course $course_code to $new_grade, GPA to $display_gpa, CGPA to $display_gpa. Cryptographic ledger block minted.", 'INFO');

            $message = "<div style='color: #155724; background: #d4edda; padding: 12px; margin-bottom: 15px; border: 1px solid #c3e6cb;'>
                            <strong>Secure Administrative Override Committed:</strong><br>
                            &bull; Inputs Sanitized &amp; Validated.<br>
                            &bull; Records updated cleanly via Parameterized Bounds.<br>
                            &bull; GPA and CGPA recalculated automatically to $display_gpa.<br>
                            &bull; CertChain Block successfully minted and sealed onto ledger.<br>
                            &bull; Action documented in security logs.
                        </div>";
        } catch (\PDOException $e) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            $message = "<div style='color: #721c24; background: #f8d7da; padding: 12px;'>Database transaction error occurred. Changes reverted.</div>";
        }
    }
}

$target_matric = isset($_GET['matric_no']) ? trim($_GET['matric_no']) : '';
if ($target_matric !== '') {
    seed_secure_student_records($pdo, $target_matric);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Secure Grade Matrix Editor</title>
    <style>
        body { font-family: Arial, sans-serif; padding: 30px; background-color: #f5f6fa; color: #333; }
        .editor-container { max-width: 600px; background: white; padding: 30px; border-radius: 4px; box-shadow: 0 2px 5px rgba(0,0,0,0.1); }
        h2 { color: #1a5276; margin-top: 0; }
        .secure-banner { background: #d4edda; color: #155724; padding: 12px; border-left: 5px solid #28a745; margin-bottom: 25px; }
        label { font-weight: bold; display: block; margin-top: 15px; font-size: 0.9em; color: #555; }
        input[type="text"], select { width: 100%; padding: 10px; margin-top: 5px; border: 1px solid #ccc; border-radius: 4px; box-sizing: border-box; }
        .btn-submit { background: #1a5276; color: white; border: none; padding: 12px 20px; font-weight: bold; border-radius: 4px; cursor: pointer; margin-top: 25px; width: 100%; }
    </style>
</head>
<body>

<div class="editor-container">
    <h2>Academic Record Master Override (SECURE)</h2>
    <a href="admin_dashboard_secure.php" style="text-decoration: none; color: #1a5276; font-size: 0.9em;">&larr; Back to Lecturer Dashboard</a>
    <hr style="border: 0; border-top: 1px solid #eee; margin: 15px 0;">

    <div class="secure-banner">
        Modifying Academic Profile for Matric No: <strong><?php echo htmlspecialchars($target_matric, ENT_QUOTES, 'UTF-8'); ?></strong>
    </div>

    <?php echo $message; ?>

    <form method="POST" action="">
        <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">

        <label>Target Student Matric:</label>
        <input type="text" name="matric_no" value="<?php echo htmlspecialchars($target_matric, ENT_QUOTES, 'UTF-8'); ?>" readonly>

        <fieldset style="border: 1px solid #ddd; padding: 15px; border-radius: 4px; margin-top: 20px;">
            <legend style="font-weight: bold; color: #1a5276; padding: 0 10px;">Subject Grade Modification</legend>

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
                <option value="C+">C+</option><option value="C">C</option><option value="C-">C-</option>
                <option value="D+">D+</option><option value="D">D</option><option value="E">E</option><option value="F">F</option>
            </select>
        </fieldset>

        <button type="submit" class="btn-submit">Commit Encapsulated Overrides</button>
    </form>
</div>

</body>
</html>
