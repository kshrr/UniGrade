<?php
session_name('USIM_INSECURE_SESSION');
session_start();
require_once 'db_insecure.php';

if (!isset($_SESSION['matric_no'])) {
    header("Location: login_insecure.php");
    exit();
}

// Fetch all students to display on the master list
$sql = "SELECT users.matric_no, users.name, academic_records.programme, academic_records.cgpa 
        FROM users 
        LEFT JOIN academic_records ON users.matric_no = academic_records.matric_no 
        WHERE users.role = 'student'";
$result = $conn->query($sql);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>USIM Lecturer Portal - Master Control Panel</title>
    <style>
        body { font-family: Arial, sans-serif; background-color: #ffffff; color: #333; margin: 0; padding: 30px; }
        .wrapper { max-width: 1100px; margin: 0 auto; }
        h1.title { color: #2c3e50; font-size: 1.8em; font-weight: bold; margin-bottom: 25px; letter-spacing: 0.5px; text-transform: uppercase; }
        
        /* USIM Dark Structural Bars */
        .section-bar { background-color: #2a2a2a; color: #ffffff; font-size: 0.85em; font-weight: bold; padding: 8px 12px; margin-top: 20px; letter-spacing: 0.5px; text-transform: uppercase; }
        
        /* Information Tables */
        .info-table { width: 100%; border-collapse: collapse; margin-bottom: 20px; font-size: 0.9em; }
        .info-table td { padding: 10px; border-bottom: 1px solid #eef0f2; color: #555; }
        .info-table td.label { width: 15%; font-weight: bold; color: #333; }
        
        /* Grid Table View */
        .grid-table { width: 100%; border-collapse: collapse; margin-top: 10px; font-size: 0.85em; }
        .grid-table th { background-color: #2a2a2a; color: white; padding: 12px 10px; font-weight: bold; border: 1px solid #fff; text-align: left; }
        .grid-table td { padding: 12px 10px; border: 1px solid #e3e6e8; text-align: left; vertical-align: middle; }
        
        /* Status Badge */
        .badge-admin { background-color: #d9534f; color: white; padding: 3px 10px; border-radius: 12px; font-size: 0.8em; font-weight: bold; display: inline-block; text-transform: uppercase; }
        
        /* Action Buttons */
        .action-container { display: flex; gap: 8px; }
        .btn { padding: 6px 12px; text-decoration: none; border-radius: 4px; color: white; font-size: 0.8em; font-weight: bold; cursor: pointer; text-align: center; transition: opacity 0.2s; }
        .btn:hover { opacity: 0.85; }
        .btn-view { background-color: #1a73e8; }
        .btn-edit { background-color: #f39c12; }
        
        /* Developer Tag */
        .dev-tag { background: #ffe6e6; color: #d9534f; padding: 4px 8px; font-weight: bold; float: right; font-size: 0.75em; border: 1px solid #d9534f; }
    </style>
</head>
<body>

<div class="wrapper">
    <div class="dev-tag">DEBUG INTERFACE: INSECURE MANAGEMENT</div>
    <h1 class="title">LECTURER & ADMIN DASHBOARD</h1>

    <div class="section-bar">OPERATOR PROFILE</div>
    <table class="info-table">
        <tr>
            <td class="label">Lecturer Name</td>
            <td><strong><?php echo $_SESSION['name']; ?></strong></td>
        </tr>
        <tr>
            <td class="label">Staff ID</td>
            <td><?php echo $_SESSION['matric_no']; ?></td>
        </tr>
        <tr>
            <td class="label">System Role</td>
            <td><span class="badge-admin"><?php echo $_SESSION['role']; ?></span></td>
        </tr>
    </table>

    <div class="section-bar">REGISTERED STUDENT GRADE MATRICES</div>
    <table class="grid-table">
        <thead>
            <tr>
                <th style="width: 15%;">MATRIC NO</th>
                <th style="width: 25%;">STUDENT NAME</th>
                <th style="width: 25%;">PROGRAMME</th>
                <th style="width: 10%;">CGPA</th>
                <th style="width: 25%;">ADMIN ACTIONS</th>
            </tr>
        </thead>
        <tbody>
            <?php if ($result && $result->num_rows > 0): ?>
                <?php while($row = $result->fetch_assoc()): ?>
                <tr>
                    <td style="font-weight: bold;"><?php echo $row['matric_no']; ?></td>
                    <td><?php echo $row['name']; ?></td>
                    <td><?php echo $row['programme']; ?></td>
                    <td style="font-weight: bold; color: #c0392b;"><?php echo $row['cgpa'] ?? 'N/A'; ?></td>
                    <td>
                        <div class="action-container">
                            <a href="transcript_insecure.php?matric_no=<?php echo $row['matric_no']; ?>" class="btn btn-view">View Transcript</a>
                            <a href="update_grade_insecure.php?matric_no=<?php echo $row['matric_no']; ?>" class="btn btn-edit">Edit Grades</a>
                        </div>
                    </td>
                </tr>
                <?php endwhile; ?>
            <?php else: ?>
                <tr><td colspan="5" style="text-align: center; padding: 20px;">No student records found.</td></tr>
            <?php endif; ?>
        </tbody>
    </table>

    <br><br><hr style="border: 0; border-top: 1px solid #ddd;">
    <div style="text-align: right; font-size: 0.9em; padding-bottom: 40px;">
        <p style="float: left; color: gray; margin: 0;">System Security Level: <strong>LOW</strong></p>
        <a href="login_insecure.php" style="font-weight: bold; color: #333; text-decoration: none;">Logout System</a>
    </div>
</div>

</body>
</html>