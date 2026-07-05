<?php
session_name('USIM_SECURE_SESSION');
session_start();
require_once 'db_secure.php';
require_once 'academic_helper_secure.php';

if (!isset($_SESSION['matric_no'])) {
    header("Location: login_secure.php");
    exit();
}

// 1. SECURE IDOR PREVENTION WITH RBAC OVERRIDE
// Admins can query the URL parameters safely via PDO. Students are locked strictly to their session.
if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin' && !empty($_GET['matric_no'])) {
    $secure_matric = $_GET['matric_no']; 
} else {
    $secure_matric = $_SESSION['matric_no']; 
}

ensure_secure_academic_schema($pdo);
seed_secure_student_records($pdo, $secure_matric);
update_secure_academic_metrics($pdo, $secure_matric);
ensure_secure_transcript_block($pdo, $secure_matric);

// 2. FETCH STUDENT PROFILE DATA (UPDATED: Now includes profile_pic, email, and phone_no fields)
try {
    $profile_stmt = $pdo->prepare("SELECT users.name, users.profile_pic, users.email, users.phone_no, academic_records.programme, academic_records.gpa, academic_records.cgpa 
                                   FROM users 
                                   LEFT JOIN academic_records ON users.matric_no = academic_records.matric_no 
                                   WHERE users.matric_no = :matric");
    $profile_stmt->execute(['matric' => $secure_matric]);
    $user_data = $profile_stmt->fetch();
    
    if (!$user_data) {
        die("<h3>System Error: Record lookup failed.</h3>");
    }
    
    // Establish dynamic image path validation logic with an explicit default avatar asset configuration fallback
    $avatar_file = (!empty($user_data['profile_pic'])) ? $user_data['profile_pic'] : 'default_avatar.png';

} catch (\PDOException $e) {
    die("<h3>System Error: Database connection failed.</h3>");
}

// 3. CERTCHAIN INTEGRITY VERIFICATION (SIMULATED BLOCKCHAIN)
$integrity_status = 'verified'; // Default state
try {
    // Fetch current records in a predictable order to generate an identical hash string
    $hash_stmt = $pdo->prepare("SELECT course_code, description, course_component, credit, grade, grade_point, status 
                                FROM grades WHERE matric_no = :matric ORDER BY course_code ASC");
    $hash_stmt->execute(['matric' => $secure_matric]);
    $live_grades = $hash_stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Generate real-time SHA-256 cryptographic signature
    $calculated_hash = hash('sha256', json_encode($live_grades));

    // Fetch the last authorized ledger entry signed by a lecturer
    $ledger_stmt = $pdo->prepare("SELECT block_hash FROM transcript_ledger WHERE matric_no = :matric ORDER BY id DESC LIMIT 1");
    $ledger_stmt->execute(['matric' => $secure_matric]);
    $recorded_block = $ledger_stmt->fetch();

    if ($recorded_block) {
        if ($recorded_block['block_hash'] !== $calculated_hash) {
            $integrity_status = 'tampered';
        }
    } else {
        // If grades exist but no ledger block was ever written, treat it as unverified
        $integrity_status = count($live_grades) > 0 ? 'unverified' : 'verified';
    }
} catch (\PDOException $e) {
    $integrity_status = 'error';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>USIM Portal - Secure Examination Results</title>
    <style>
        body { font-family: Arial, sans-serif; background-color: #ffffff; color: #333; margin: 0; padding: 30px; }
        .wrapper { max-width: 1100px; margin: 0 auto; }
        h1.title { color: #1a5276; font-size: 1.8em; font-weight: bold; margin-bottom: 25px; letter-spacing: 0.5px; }
        
        .section-bar { background-color: #1a5276; color: #ffffff; font-size: 0.85em; font-weight: bold; padding: 8px 12px; margin-top: 20px; letter-spacing: 0.5px; }
        
        /* Profile layout flexbox structure mapping elements horizontally */
        .profile-container { display: flex; align-items: center; gap: 30px; margin-top: 15px; background: #fafbfc; padding: 20px; border-radius: 4px; border: 1px solid #e3e6e8; }
        .profile-media { display: flex; flex-direction: column; align-items: center; text-align: center; }
        .profile-avatar { width: 120px; height: 120px; border-radius: 50%; border: 3px solid #1a5276; object-fit: cover; background: #ffffff; box-shadow: 0 2px 5px rgba(0,0,0,0.1); }
        
        /* Context-derived action controls linking straight over to credential modification forms */
        .btn-edit-profile { display: inline-block; background-color: #1a5276; color: white; text-decoration: none; padding: 6px 12px; font-weight: bold; font-size: 0.75em; border-radius: 4px; margin-top: 12px; transition: background 0.2s; text-transform: uppercase; letter-spacing: 0.3px; }
        .btn-edit-profile:hover { background-color: #113d59; }

        .info-table { width: 100%; border-collapse: collapse; font-size: 0.9em; }
        .info-table td { padding: 8px 10px; border-bottom: 1px solid #eef0f2; color: #555; }
        .info-table td.label { width: 22%; font-weight: bold; color: #333; }
        
        .badge-registered { background-color: #1a73e8; color: white; padding: 3px 10px; border-radius: 12px; font-size: 0.8em; font-weight: bold; display: inline-block; }
        .btn-print { background-color: #1a5276; color: white; border: none; padding: 8px 22px; font-weight: bold; font-size: 0.8em; border-radius: 4px; cursor: pointer; margin: 15px 0; letter-spacing: 0.5px; }
        
        .grid-table { width: 100%; border-collapse: collapse; margin-top: 10px; font-size: 0.85em; }
        .grid-table th { background-color: #1a5276; color: white; padding: 10px; font-weight: bold; border: 1px solid #fff; text-align: center; }
        .grid-table td { padding: 10px; border: 1px solid #e3e6e8; text-align: center; }
        .grid-table td.left-align { text-align: left; padding-left: 15px; }
        
        /* CertChain Verification Banner Styles */
        .certchain-banner { padding: 15px; border-left: 5px solid; margin-bottom: 25px; font-size: 0.9em; border-radius: 0 4px 4px 0; }
        .cc-verified { background-color: #e8f8f5; color: #117864; border-left-color: #117864; }
        .cc-tampered { background-color: #f2d7d5; color: #922b21; border-left-color: #b03a2e; }
        .cc-unverified { background-color: #fef9e7; color: #7d6608; border-left-color: #f1c40f; }
        
        .summary-container { display: flex; justify-content: space-between; margin-top: 20px; gap: 20px; }
        .summary-box { width: 50%; }
        .dev-tag { background: #e8f8f5; color: #117864; padding: 4px 8px; font-weight: bold; float: right; font-size: 0.75em; border: 1px solid #117864; }
    </style>
</head>
<body>

<div class="wrapper">
    <div class="dev-tag">SECURITY INFRASTRUCTURE: MULTI-LAYER COMPLIANCE</div>
    <h1 class="title">EXAMINATION RESULTS</h1>

    <?php if ($integrity_status === 'verified'): ?>
        <div class="certchain-banner cc-verified">
            <strong>🔒 CERTCHAIN LEDGER VALIDATION: SECURE</strong><br>
            Cryptographic match confirmed. This transcript matches the immutable blockchain hash signature perfectly.
        </div>
    <?php elseif ($integrity_status === 'unverified'): ?>
        <div class="certchain-banner cc-unverified">
            <strong>⚠️ CERTCHAIN LEDGER NOTICE: UNINDEXED RECORD</strong><br>
            Grades exist but no block has been minted on the cryptographic ledger yet. Request lecturer re-signing.
        </div>
    <?php else: ?>
        <div class="certchain-banner cc-tampered">
            <strong>🚨 CERTCHAIN SECURITY WARNING: DATA INTEGRITY FAILURE!</strong><br>
            CRITICAL MISMATCH: The current academic records do not match the secure block ledger fingerprint. Unauthorized raw database modification detected!
        </div>
    <?php endif; ?>

    <div class="section-bar">PERSONAL INFORMATION</div>
    
    <div class="profile-container">
        <div class="profile-media">
            <img src="uploads/<?php echo htmlspecialchars($avatar_file, ENT_QUOTES, 'UTF-8'); ?>" class="profile-avatar" alt="User Profile Image Array">
            
            <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'student'): ?>
                <a href="edit_profile_secure.php" class="btn-edit-profile">⚙️ Edit Profile</a>
            <?php endif; ?>
        </div>
        
        <div style="flex-grow: 1;">
            <table class="info-table">
                <tr>
                    <td class="label">Name</td>
                    <td><?php echo htmlspecialchars($user_data['name'], ENT_QUOTES, 'UTF-8'); ?></td>
                </tr>
                <tr>
                    <td class="label">Matric No</td>
                    <td><?php echo htmlspecialchars($secure_matric, ENT_QUOTES, 'UTF-8'); ?></td>
                </tr>
                <tr>
                    <td class="label">University Email</td>
                    <td><?php echo htmlspecialchars($user_data['email'] ?? 'Not Indexed', ENT_QUOTES, 'UTF-8'); ?></td>
                </tr>
                <tr>
                    <td class="label">Mobile Number</td>
                    <td><?php echo htmlspecialchars($user_data['phone_no'] ?? 'Not Indexed', ENT_QUOTES, 'UTF-8'); ?></td>
                </tr>
                <tr>
                    <td class="label">Programme</td>
                    <td><?php echo htmlspecialchars($user_data['programme'] ?? 'N/A', ENT_QUOTES, 'UTF-8'); ?></td>
                </tr>
                <tr>
                    <td class="label">Sem No</td>
                    <td>6</td>
                </tr>
                <tr>
                    <td class="label">Status</td>
                    <td><span class="badge-registered">REGISTERED</span></td>
                </tr>
                <tr>
                    <td class="label">Current Semester</td>
                    <td>[A252] - SEMESTER II, SESI AKADEMIK 2025/2026</td>
                </tr>
            </table>
        </div>
    </div>

    <div class="section-bar">EXAMINATION RESULT</div>
    <button class="btn-print" onclick="window.print()">PRINT</button>

    <table class="grid-table">
        <thead>
            <tr>
                <th style="width: 5%;">NO</th>
                <th style="width: 12%;">COURSE CODE</th>
                <th style="width: 40%;">DESCRIPTION</th>
                <th style="width: 15%;">COURSE COMPONENT</th>
                <th style="width: 8%;">CREDIT</th>
                <th style="width: 8%;">GRADE</th>
                <th style="width: 8%;">GRADE POINT</th>
                <th style="width: 8%;">STATUS</th>
            </tr>
        </thead>
        <tbody>
            <?php
            try {
                $stmt = $pdo->prepare("SELECT course_code, description, course_component, credit, grade, grade_point, status 
                                       FROM grades WHERE matric_no = :matric ORDER BY course_code ASC");
                $stmt->execute(['matric' => $secure_matric]);
                $grades = $stmt->fetchAll();

                if (count($grades) > 0) {
                    $count = 1;
                    foreach ($grades as $row) {
                        echo "<tr>";
                        echo "<td>" . $count++ . "</td>";
                        echo "<td>" . htmlspecialchars($row['course_code'], ENT_QUOTES, 'UTF-8') . "</td>";
                        echo "<td class='left-align'>" . htmlspecialchars($row['description'], ENT_QUOTES, 'UTF-8') . "</td>";
                        echo "<td>" . htmlspecialchars($row['course_component'], ENT_QUOTES, 'UTF-8') . "</td>";
                        echo "<td>" . htmlspecialchars($row['credit'], ENT_QUOTES, 'UTF-8') . "</td>";
                        echo "<td style='font-weight: bold;'>" . htmlspecialchars($row['grade'] === null ? 'Pending' : $row['grade'], ENT_QUOTES, 'UTF-8') . "</td>";
                        echo "<td>" . htmlspecialchars($row['grade_point'] === null ? '-' : number_format((float) $row['grade_point'], 2), ENT_QUOTES, 'UTF-8') . "</td>";
                        echo "<td>" . htmlspecialchars($row['status'], ENT_QUOTES, 'UTF-8') . "</td>";
                        echo "</tr>";
                    }
                } else {
                    echo "<tr><td colspan='8' style='text-align:center;'>No grades found for this student.</td></tr>";
                }
            } catch (\PDOException $e) {
                echo "<tr><td colspan='8' style='text-align:center; color:red;'>Unable to load transcript data.</td></tr>";
            }
            ?>
        </tbody>
    </table>

    <div class="summary-container">
        <div class="summary-box">
            <div class="section-bar">SEMESTER</div>
            <table class="grid-table">
                <tr><td class="left-align" style="font-weight: bold; color: #117864;">PNGS (GPA)</td><td style="font-weight: bold; color: #117864;"><?php echo htmlspecialchars($user_data['gpa'] === null ? 'Pending' : number_format((float) $user_data['gpa'], 2), ENT_QUOTES, 'UTF-8'); ?></td></tr>
            </table>
        </div>
        
        <div class="summary-box">
            <div class="section-bar">CUMMULATIVE</div>
            <table class="grid-table">
                <tr><td class="left-align" style="font-weight: bold; color: #117864;">PNGK (CGPA)</td><td style="font-weight: bold; color: #117864;"><?php echo htmlspecialchars($user_data['cgpa'] === null ? 'Pending' : number_format((float) $user_data['cgpa'], 2), ENT_QUOTES, 'UTF-8'); ?></td></tr>
            </table>
        </div>
    </div>

    <br><br><hr style="border: 0; border-top: 1px solid #ddd;">
    <div style="text-align: right; font-size: 0.9em; padding-bottom: 40px;">
        <p style="float: left; color: #117864; margin: 0;">System Security Level: <strong>HIGH</strong></p>
        <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin'): ?>
            <a href="admin_dashboard_secure.php" style="color: #1a5276; margin-right: 20px; font-weight: bold; text-decoration: none;">[Lecturer Control Dashboard]</a>
        <?php endif; ?>
        <a href="login_secure.php" style="font-weight: bold; color: #333; text-decoration: none;">Logout Securely</a>
    </div>
</div>

</body>
</html>
