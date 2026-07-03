<?php
session_name('USIM_INSECURE_SESSION');
session_start();
require_once 'db_insecure.php';

if (!isset($_SESSION['matric_no'])) {
    header("Location: login_insecure.php");
    exit();
}

// IDOR VULNERABILITY: Blindly takes the matric number from the URL parameter without validation
$matric_no = isset($_GET['matric_no']) ? $_GET['matric_no'] : $_SESSION['matric_no'];

// FETCH PROFILE DATA (UPDATED: Now query includes unescaped profile_pic, email, and phone_no fields)
$sql = "SELECT users.name, users.role, users.profile_pic, users.email, users.phone_no, academic_records.programme, academic_records.gpa, academic_records.cgpa 
        FROM users 
        LEFT JOIN academic_records ON users.matric_no = academic_records.matric_no 
        WHERE users.matric_no = '$matric_no'";
$result = $conn->query($sql);
$user_data = $result->fetch_assoc();

if (!$user_data) {
    die("<h3>System Error: Record lookup failed.</h3>");
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>USIM Portal - Examination Results</title>
    <style>
        body { font-family: Arial, sans-serif; background-color: #ffffff; color: #333; margin: 0; padding: 30px; }
        .wrapper { max-width: 1100px; margin: 0 auto; }
        h1.title { color: #2c3e50; font-size: 1.8em; font-weight: bold; margin-bottom: 25px; letter-spacing: 0.5px; }
        
        .section-bar { background-color: #2a2a2a; color: #ffffff; font-size: 0.85em; font-weight: bold; padding: 8px 12px; margin-top: 20px; letter-spacing: 0.5px; }
        
        /* Profile Layout Container for Insecure Environment View mapping elements loosely */
        .profile-container { display: flex; align-items: center; gap: 30px; margin-top: 15px; background: #fffcf9; padding: 20px; border-radius: 4px; border: 1px solid #e67e22; }
        .profile-media { display: flex; flex-direction: column; align-items: center; text-align: center; }
        .profile-avatar { width: 120px; height: 120px; border-radius: 50%; border: 3px solid #e67e22; object-fit: cover; background: #ffffff; }
        
        /* Direct, unrestricted action control button guiding parameters directly to modifications */
        .btn-edit-profile { display: inline-block; background-color: #e67e22; color: white; text-decoration: none; padding: 6px 12px; font-weight: bold; font-size: 0.75em; border-radius: 4px; margin-top: 12px; text-transform: uppercase; }
        .btn-edit-profile:hover { background-color: #d35400; }

        .info-table { width: 100%; border-collapse: collapse; font-size: 0.9em; }
        .info-table td { padding: 8px 10px; border-bottom: 1px solid #eef0f2; color: #555; }
        .info-table td.label { width: 22%; font-weight: bold; color: #333; }
        
        .badge-registered { background-color: #1a73e8; color: white; padding: 3px 10px; border-radius: 12px; font-size: 0.8em; font-weight: bold; display: inline-block; }
        
        .btn-print { background-color: #d81b60; color: white; border: none; padding: 8px 22px; font-weight: bold; font-size: 0.8em; border-radius: 4px; cursor: pointer; margin: 15px 0; letter-spacing: 0.5px; }
        
        .grid-table { width: 100%; border-collapse: collapse; margin-top: 10px; font-size: 0.85em; }
        .grid-table th { background-color: #2a2a2a; color: white; padding: 10px; font-weight: bold; border: 1px solid #fff; text-align: center; }
        .grid-table td { padding: 10px; border: 1px solid #e3e6e8; text-align: center; }
        .grid-table td.left-align { text-align: left; padding-left: 15px; }
        
        .summary-container { display: flex; justify-content: space-between; margin-top: 20px; gap: 20px; }
        .summary-box { width: 50%; }
        
        .xss-notice { background-color: #fff3cd; color: #856404; padding: 12px; border-left: 5px solid #ffc107; margin-bottom: 20px; font-size: 0.9em; }
        .dev-tag { background: #ffe6e6; color: #d9534f; padding: 4px 8px; font-weight: bold; float: right; font-size: 0.75em; border: 1px solid #d9534f; }
    </style>
</head>
<body>

<div class="wrapper">
    <div class="dev-tag">DEBUG INTERFACE: INSECURE PROFILE HOISTING</div>
    <h1 class="title">EXAMINATION RESULTS</h1>

    <?php if (isset($_GET['notice'])): ?>
        <div class="xss-notice">
            <strong>System Alert Notification:</strong> <?php echo $_GET['notice']; ?>
        </div>
    <?php endif; ?>

    <div class="section-bar">PERSONAL INFORMATION</div>
    
    <div class="profile-container">
        <div class="profile-media">
            <img src="uploads/<?php echo $user_data['profile_pic']; ?>" class="profile-avatar" alt="Insecure Avatar Display Element">
            
            <a href="edit_profile_insecure.php" class="btn-edit-profile">⚙️ Edit Profile</a>
        </div>
        
        <div style="flex-grow: 1;">
            <table class="info-table">
                <tr>
                    <td class="label">Name</td>
                    <td><?php echo $user_data['name']; ?></td>
                </tr>
                <tr>
                    <td class="label">Matric No</td>
                    <td><?php echo $matric_no; ?></td>
                </tr>
                <tr>
                    <td class="label">University Email</td>
                    <td><?php echo $user_data['email']; ?></td>
                </tr>
                <tr>
                    <td class="label">Mobile Number</td>
                    <td><?php echo $user_data['phone_no']; ?></td>
                </tr>
                <tr>
                    <td class="label">Programme</td>
                    <td><?php echo $user_data['programme']; ?></td>
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
    
    <div style="margin-top: 15px; font-size: 0.9em;">
        <label>Semester : </label>
        <select style="padding: 5px; font-weight: bold;">
            <option>A251 - SEMESTER I, 2025/2026</option>
        </select>
    </div>

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
            // RAW SQL EXECUTION: VULNERABLE TO SQL INJECTION
            $grades_sql = "SELECT course_code, description, course_component, credit, grade, grade_point, status 
                           FROM grades 
                           WHERE matric_no = '$matric_no'";
            $grades_result = $conn->query($grades_sql);

            if ($grades_result && $grades_result->num_rows > 0) {
                $count = 1;
                while ($row = $grades_result->fetch_assoc()) {
                    echo "<tr>";
                    echo "<td>" . $count++ . "</td>";
                    // DIRECT OUTPUT: VULNERABLE TO STORED XSS
                    echo "<td>" . $row['course_code'] . "</td>";
                    echo "<td class='left-align'>" . $row['description'] . "</td>";
                    echo "<td>" . $row['course_component'] . "</td>";
                    echo "<td>" . $row['credit'] . "</td>";
                    echo "<td style='font-weight: bold;'>" . $row['grade'] . "</td>";
                    echo "<td>" . $row['grade_point'] . "</td>";
                    echo "<td>" . $row['status'] . "</td>";
                    echo "</tr>";
                }
            } else {
                echo "<tr><td colspan='8' style='text-align:center;'>No grades found for this student.</td></tr>";
            }
            ?>
        </tbody>
    </table>

    <div class="summary-container">
        <div class="summary-box">
            <div class="section-bar">SEMESTER</div>
            <table class="grid-table">
                <tr><td class="left-align" style="width:50%;">UKS</td><td>18</td></tr>
                <tr><td class="left-align">MGS</td><td>69.25</td></tr>
                <tr><td class="left-align" style="font-weight: bold; color: #c0392b;">PNGS (GPA)</td><td style="font-weight: bold; color: #c0392b;"><?php echo $user_data['gpa']; ?></td></tr>
            </table>
        </div>
        
        <div class="summary-box">
            <div class="section-bar">CUMMULATIVE</div>
            <table class="grid-table">
                <tr><td class="left-align" style="width:50%;">UKK</td><td>87</td></tr>
                <tr><td class="left-align">MGK</td><td>336.25</td></tr>
                <tr><td class="left-align" style="font-weight: bold; color: #c0392b;">PNGK (CGPA)</td><td style="font-weight: bold; color: #c0392b;"><?php echo $user_data['cgpa']; ?></td></tr>
            </table>
        </div>
    </div>

    <br><br><hr style="border: 0; border-top: 1px solid #ddd;">
    <div style="text-align: right; font-size: 0.9em; padding-bottom: 40px;">
        <p style="float: left; color: gray; margin: 0;">System Security Level: <strong>LOW</strong></p>
        <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin'): ?>
            <a href="admin_dashboard_insecure.php" style="color: #d9534f; margin-right: 20px; font-weight: bold; text-decoration: none;">[Lecturer Dashboard]</a>
        <?php endif; ?>
        <a href="login_insecure.php" style="font-weight: bold; color: #333; text-decoration: none;">Logout</a>
    </div>
</div>

</body>
</html>