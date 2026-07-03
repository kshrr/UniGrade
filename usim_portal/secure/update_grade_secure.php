<?php
// Set a unique cookie namespace for the secure environment (Mutual Exclusion)
session_name('USIM_SECURE_SESSION'); 
session_start();
require_once 'db_secure.php';
require_once 'logging_helper.php';

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    die("<h3>Access Denied. Lecturer administrative clearance required.</h3>");
}

$message = "";

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_POST['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
        log_security_event($pdo, $_SESSION['matric_no'], 'CSRF_ATTEMPT', 'Unauthorized transaction attempt on grade override form.', 'HIGH');
        die("<h3>Security Exception: Transaction token authenticity validation failure.</h3>");
    }

    // =========================================================================
    // ADVANCED I/O VALIDATION & SANITIZATION (Compliant with Slide 28 & 29)
    // =========================================================================
    
    // 1. Sanitize Inputs (Strip illegal characters before processing)
    $matric_no   = filter_input(INPUT_POST, 'matric_no', FILTER_SANITIZE_SPECIAL_CHARS);
    $course_code = filter_input(INPUT_POST, 'course_code', FILTER_SANITIZE_SPECIAL_CHARS);
    $new_grade   = filter_input(INPUT_POST, 'new_grade', FILTER_SANITIZE_SPECIAL_CHARS);
    
    // Sanitize Floats: Removes all characters except digits, plus, minus, and period/comma
    $raw_gpa  = filter_input(INPUT_POST, 'new_gpa', FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);
    $raw_cgpa = filter_input(INPUT_POST, 'new_cgpa', FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);

    $validation_errors = [];

    // 2. Data Length & Logical Bounds Validation
    // Validate string lengths to prevent buffer overflow attacks
    if (strlen($matric_no) < 5 || strlen($matric_no) > 15) {
        $validation_errors[] = "Matric Number length violates system constraints.";
    }

    // Validate that the sanitized GPA/CGPA are actual valid floating-point numbers
    $new_gpa  = filter_var($raw_gpa, FILTER_VALIDATE_FLOAT);
    $new_cgpa = filter_var($raw_cgpa, FILTER_VALIDATE_FLOAT);

    // Business Logic Limits: GPA/CGPA must be between 0.00 and 4.00
    if ($new_gpa === false || $new_gpa < 0.00 || $new_gpa > 4.00) {
        $validation_errors[] = "Semester GPA must be a valid decimal between 0.00 and 4.00.";
    }
    if ($new_cgpa === false || $new_cgpa < 0.00 || $new_cgpa > 4.00) {
        $validation_errors[] = "Cumulative CGPA must be a valid decimal between 0.00 and 4.00.";
    }

    // Terminate transaction instantly if any validation fails
    if (!empty($validation_errors)) {
        $error_string = implode("<br>• ", $validation_errors);
        $message = "<div style='color: #721c24; background: #f8d7da; padding: 12px; border-left: 5px solid #dc3545; margin-bottom: 15px;'>
                        <strong>Input Validation Failed:</strong><br>• $error_string
                    </div>";
    } else {
        // =========================================================================
        // PROCEED WITH SECURE TRANSACTION
        // =========================================================================
        try {
            $pdo->beginTransaction();

            // 1. Update overall student metrics
            $stmt_metrics = $pdo->prepare("UPDATE academic_records SET gpa = :gpa, cgpa = :cgpa WHERE matric_no = :matric");
            $stmt_metrics->execute(['gpa' => $new_gpa, 'cgpa' => $new_cgpa, 'matric' => $matric_no]);
            
            // 2. Update targeted course grade
            $stmt_course = $pdo->prepare("UPDATE grades SET grade = :grade WHERE matric_no = :matric AND course_code = :course");
            $stmt_course->execute(['grade' => $new_grade, 'matric' => $matric_no, 'course' => $course_code]);

            // CERTCHAIN SMART CONTRACT MECHANISM SIMULATION
            $ledger_fetch = $pdo->prepare("SELECT course_code, description, course_component, credit, grade, grade_point, status 
                                           FROM grades WHERE matric_no = :matric ORDER BY course_code ASC");
            $ledger_fetch->execute(['matric' => $matric_no]);
            $updated_grades = $ledger_fetch->fetchAll(PDO::FETCH_ASSOC);

            $new_block_hash = hash('sha256', json_encode($updated_grades));

            $last_block_stmt = $pdo->query("SELECT block_hash FROM transcript_ledger ORDER BY id DESC LIMIT 1");
            $last_block = $last_block_stmt->fetch();
            $previous_hash = $last_block ? $last_block['block_hash'] : str_repeat('0', 64);

            $insert_block = $pdo->prepare("INSERT INTO transcript_ledger (matric_no, block_hash, previous_hash) 
                                           VALUES (:matric, :block_hash, :prev_hash)");
            $insert_block->execute([
                'matric' => $matric_no,
                'block_hash' => $new_block_hash,
                'prev_hash' => $previous_hash
            ]);

            $pdo->commit();
            
            log_security_event($pdo, $_SESSION['matric_no'], 'GRADE_OVERRIDE', "Altered $matric_no: Course $course_code to $new_grade, GPA to $new_gpa, CGPA to $new_cgpa. Cryptographic ledger block minted.", 'INFO');

            $message = "<div style='color: #155724; background: #d4edda; padding: 12px; margin-bottom: 15px; border: 1px solid #c3e6cb;'>
                            <strong>Secure Administrative Override Committed:</strong><br>
                            • Inputs Sanitized & Validated.<br>
                            • Records updated cleanly via Parameterized Bounds.<br>
                            • CertChain Block successfully minted and sealed onto ledger.<br>
                            • Action documented in security logs.
                        </div>";
        } catch (\PDOException $e) {
            $pdo->rollBack();
            $message = "<div style='color: #721c24; background: #f8d7da; padding: 12px;'>Database transaction error occurred. Changes reverted.</div>";
        }
    }
}

$target_matric = isset($_GET['matric_no']) ? trim($_GET['matric_no']) : '';
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
                <option value="C+">C+</option><option value="C">C</option><option value="F">F</option>
            </select>
        </fieldset>

        <fieldset style="border: 1px solid #ddd; padding: 15px; border-radius: 4px; margin-top: 20px;">
            <legend style="font-weight: bold; color: #1a5276; padding: 0 10px;">Final Score Metric Override</legend>
            
            <label>Override Semester GPA (PNGS):</label>
            <input type="text" name="new_gpa" placeholder="e.g., 3.85" required autocomplete="off">

            <label>Override Cumulative CGPA (PNGK):</label>
            <input type="text" name="new_cgpa" placeholder="e.g., 3.86" required autocomplete="off">
        </fieldset>

        <button type="submit" class="btn-submit">Commit Encapsulated Overrides</button>
    </form>
</div>

</body>
</html>