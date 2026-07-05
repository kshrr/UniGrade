<?php

const SECURE_DEFAULT_PROGRAMME = 'UQ6481001 BACHELOR OF COMPUTER SCIENCE WITH HONOURS (INFORMATION SECURITY AND ASSURANCE) (QC13)';
const SECURE_DEFAULT_CURRENT_SEMESTER = '[A252] - SEMESTER II, SESI AKADEMIK 2025/2026';
const SECURE_DEFAULT_SEM_NO = 6;
const SECURE_DEFAULT_SEMESTER_ID = 'A251';

function secure_predefined_subjects()
{
    return [
        ['course_code' => 'SKE3012', 'description' => 'CYBER DEVELOPMENT', 'course_component' => 'EP', 'credit' => 2],
        ['course_code' => 'SKJ3013', 'description' => 'ADVANCED JAVA PROGRAMMING', 'course_component' => 'EP', 'credit' => 3],
        ['course_code' => 'SKJ3143', 'description' => 'INFORMATION SECURITY MANAGEMENT', 'course_component' => 'EP', 'credit' => 3],
        ['course_code' => 'SKJ3183', 'description' => 'ARTIFICIAL INTELLIGENCE', 'course_component' => 'WP', 'credit' => 3],
        ['course_code' => 'SKJ3192', 'description' => 'DIGITAL TECHNOLOGY', 'course_component' => 'WP', 'credit' => 2],
        ['course_code' => 'SKJ4143', 'description' => 'CRYPTOGRAPHY AND APPLICATION', 'course_component' => 'WP', 'credit' => 3],
        ['course_code' => 'UTU3012', 'description' => 'ENTREPRENEURSHIP', 'course_component' => 'WU', 'credit' => 2],
    ];
}

function secure_grade_point_map()
{
    return [
        'A+' => 4.00,
        'A' => 4.00,
        'A-' => 3.67,
        'B+' => 3.33,
        'B' => 3.00,
        'B-' => 2.67,
        'C+' => 2.33,
        'C' => 2.00,
        'C-' => 1.67,
        'D+' => 1.33,
        'D' => 1.00,
        'E' => 0.00,
        'F' => 0.00,
    ];
}

function ensure_secure_academic_schema($pdo)
{
    static $schema_ready = false;

    if ($schema_ready) {
        return;
    }

    $pdo->exec("ALTER TABLE academic_records MODIFY gpa DECIMAL(4,2) NULL, MODIFY cgpa DECIMAL(4,2) NULL");
    $pdo->exec("ALTER TABLE grades MODIFY grade VARCHAR(5) NULL, MODIFY grade_point DECIMAL(5,2) NULL");

    $schema_ready = true;
}

function seed_secure_student_records($pdo, $matric_no)
{
    ensure_secure_academic_schema($pdo);

    $academic_check = $pdo->prepare("SELECT matric_no FROM academic_records WHERE matric_no = :matric LIMIT 1");
    $academic_check->execute(['matric' => $matric_no]);
    if (!$academic_check->fetch()) {
        $insert_academic = $pdo->prepare(
            "INSERT INTO academic_records (matric_no, programme, current_semester, sem_no, status, gpa, cgpa)
             VALUES (:matric, :programme, :current_semester, :sem_no, :status, :gpa, :cgpa)"
        );
        $insert_academic->execute([
            'matric' => $matric_no,
            'programme' => SECURE_DEFAULT_PROGRAMME,
            'current_semester' => SECURE_DEFAULT_CURRENT_SEMESTER,
            'sem_no' => SECURE_DEFAULT_SEM_NO,
            'status' => 'REGISTERED',
            'gpa' => null,
            'cgpa' => null,
        ]);
    }

    $grade_check = $pdo->prepare("SELECT course_code FROM grades WHERE matric_no = :matric");
    $grade_check->execute(['matric' => $matric_no]);
    $existing_course_codes = $grade_check->fetchAll(PDO::FETCH_COLUMN);

    $insert_grade = $pdo->prepare(
        "INSERT INTO grades (matric_no, semester_id, course_code, description, course_component, credit, grade, grade_point, status)
         VALUES (:matric, :semester_id, :course_code, :description, :course_component, :credit, :grade, :grade_point, :status)"
    );

    foreach (secure_predefined_subjects() as $subject) {
        if (in_array($subject['course_code'], $existing_course_codes, true)) {
            continue;
        }

        $insert_grade->execute([
            'matric' => $matric_no,
            'semester_id' => SECURE_DEFAULT_SEMESTER_ID,
            'course_code' => $subject['course_code'],
            'description' => $subject['description'],
            'course_component' => $subject['course_component'],
            'credit' => $subject['credit'],
            'grade' => null,
            'grade_point' => null,
            'status' => '-',
        ]);
    }
}

function secure_grade_point_for_grade($grade)
{
    $grade_map = secure_grade_point_map();
    return array_key_exists($grade, $grade_map) ? $grade_map[$grade] : null;
}

function synchronize_secure_grade_points($pdo, $matric_no)
{
    $select_stmt = $pdo->prepare("SELECT id, grade FROM grades WHERE matric_no = :matric");
    $select_stmt->execute(['matric' => $matric_no]);
    $update_stmt = $pdo->prepare("UPDATE grades SET grade_point = :grade_point WHERE id = :id");

    foreach ($select_stmt->fetchAll() as $row) {
        $grade_point = secure_grade_point_for_grade($row['grade']);
        $update_stmt->bindValue(':id', (int) $row['id'], PDO::PARAM_INT);
        if ($grade_point === null) {
            $update_stmt->bindValue(':grade_point', null, PDO::PARAM_NULL);
        } else {
            $update_stmt->bindValue(':grade_point', number_format($grade_point, 2, '.', ''));
        }
        $update_stmt->execute();
    }
}

function calculate_secure_gpa($pdo, $matric_no)
{
    $stmt = $pdo->prepare("SELECT credit, grade FROM grades WHERE matric_no = :matric AND grade IS NOT NULL");
    $stmt->execute(['matric' => $matric_no]);

    $total_quality_points = 0.0;
    $total_credits = 0;

    foreach ($stmt->fetchAll() as $row) {
        $grade_point = secure_grade_point_for_grade($row['grade']);
        if ($grade_point === null) {
            continue;
        }

        $credit = (int) $row['credit'];
        $total_quality_points += $credit * $grade_point;
        $total_credits += $credit;
    }

    if ($total_credits === 0) {
        return null;
    }

    return round($total_quality_points / $total_credits, 2);
}

function update_secure_academic_metrics($pdo, $matric_no)
{
    synchronize_secure_grade_points($pdo, $matric_no);

    $gpa = calculate_secure_gpa($pdo, $matric_no);
    $stmt = $pdo->prepare("UPDATE academic_records SET gpa = :gpa, cgpa = :cgpa WHERE matric_no = :matric");
    $stmt->execute([
        'gpa' => $gpa,
        'cgpa' => $gpa,
        'matric' => $matric_no,
    ]);

    return $gpa;
}

function mint_secure_transcript_block($pdo, $matric_no)
{
    $ledger_fetch = $pdo->prepare(
        "SELECT course_code, description, course_component, credit, grade, grade_point, status
         FROM grades WHERE matric_no = :matric ORDER BY course_code ASC"
    );
    $ledger_fetch->execute(['matric' => $matric_no]);
    $updated_grades = $ledger_fetch->fetchAll(PDO::FETCH_ASSOC);

    $new_block_hash = hash('sha256', json_encode($updated_grades));

    $last_block_stmt = $pdo->query("SELECT block_hash FROM transcript_ledger ORDER BY id DESC LIMIT 1");
    $last_block = $last_block_stmt->fetch();
    $previous_hash = $last_block ? $last_block['block_hash'] : str_repeat('0', 64);

    $insert_block = $pdo->prepare(
        "INSERT INTO transcript_ledger (matric_no, block_hash, previous_hash)
         VALUES (:matric, :block_hash, :prev_hash)"
    );
    $insert_block->execute([
        'matric' => $matric_no,
        'block_hash' => $new_block_hash,
        'prev_hash' => $previous_hash,
    ]);
}

function ensure_secure_transcript_block($pdo, $matric_no)
{
    $stmt = $pdo->prepare("SELECT id FROM transcript_ledger WHERE matric_no = :matric LIMIT 1");
    $stmt->execute(['matric' => $matric_no]);

    if (!$stmt->fetch()) {
        synchronize_secure_grade_points($pdo, $matric_no);
        mint_secure_transcript_block($pdo, $matric_no);
    }
}
