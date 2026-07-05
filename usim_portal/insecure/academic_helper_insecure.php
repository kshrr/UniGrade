<?php

const INSECURE_DEFAULT_PROGRAMME = 'UQ6481001 BACHELOR OF COMPUTER SCIENCE WITH HONOURS (INFORMATION SECURITY AND ASSURANCE) (QC13)';
const INSECURE_DEFAULT_CURRENT_SEMESTER = '[A252] - SEMESTER II, SESI AKADEMIK 2025/2026';
const INSECURE_DEFAULT_SEM_NO = 6;
const INSECURE_DEFAULT_SEMESTER_ID = 'A251';

function insecure_predefined_subjects()
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

function insecure_grade_point_map()
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

function ensure_insecure_academic_schema($conn)
{
    static $schema_ready = false;

    if ($schema_ready) {
        return;
    }

    @$conn->query("ALTER TABLE academic_records MODIFY gpa DECIMAL(4,2) NULL, MODIFY cgpa DECIMAL(4,2) NULL");
    @$conn->query("ALTER TABLE grades MODIFY grade VARCHAR(5) NULL, MODIFY grade_point DECIMAL(5,2) NULL");

    $schema_ready = true;
}

function seed_insecure_student_records($conn, $matric_no)
{
    ensure_insecure_academic_schema($conn);

    $academic_check = $conn->query("SELECT matric_no FROM academic_records WHERE matric_no = '$matric_no' LIMIT 1");
    if ($academic_check && $academic_check->num_rows === 0) {
        $conn->query(
            "INSERT INTO academic_records (matric_no, programme, current_semester, sem_no, status, gpa, cgpa)
             VALUES ('$matric_no', '" . INSECURE_DEFAULT_PROGRAMME . "', '" . INSECURE_DEFAULT_CURRENT_SEMESTER . "', " . INSECURE_DEFAULT_SEM_NO . ", 'REGISTERED', NULL, NULL)"
        );
    }

    $existing_course_codes = [];
    $grade_check = $conn->query("SELECT course_code FROM grades WHERE matric_no = '$matric_no'");
    if ($grade_check) {
        while ($row = $grade_check->fetch_assoc()) {
            $existing_course_codes[] = $row['course_code'];
        }
    }

    foreach (insecure_predefined_subjects() as $subject) {
        if (in_array($subject['course_code'], $existing_course_codes, true)) {
            continue;
        }

        $conn->query(
            "INSERT INTO grades (matric_no, semester_id, course_code, description, course_component, credit, grade, grade_point, status)
             VALUES ('$matric_no', '" . INSECURE_DEFAULT_SEMESTER_ID . "', '" . $subject['course_code'] . "', '" . $subject['description'] . "', '" . $subject['course_component'] . "', " . (int) $subject['credit'] . ", NULL, NULL, '-')"
        );
    }
}

function insecure_grade_point_for_grade($grade)
{
    $grade_map = insecure_grade_point_map();
    return array_key_exists($grade, $grade_map) ? $grade_map[$grade] : null;
}

function synchronize_insecure_grade_points($conn, $matric_no)
{
    $rows = $conn->query("SELECT id, grade FROM grades WHERE matric_no = '$matric_no'");
    if (!$rows) {
        return;
    }

    while ($row = $rows->fetch_assoc()) {
        $grade_point = insecure_grade_point_for_grade($row['grade']);
        $value_sql = $grade_point === null ? 'NULL' : number_format($grade_point, 2, '.', '');
        $conn->query("UPDATE grades SET grade_point = $value_sql WHERE id = " . (int) $row['id']);
    }
}

function calculate_insecure_gpa($conn, $matric_no)
{
    $result = $conn->query("SELECT credit, grade FROM grades WHERE matric_no = '$matric_no' AND grade IS NOT NULL");
    if (!$result) {
        return null;
    }

    $total_quality_points = 0.0;
    $total_credits = 0;

    while ($row = $result->fetch_assoc()) {
        $grade_point = insecure_grade_point_for_grade($row['grade']);
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

function update_insecure_academic_metrics($conn, $matric_no)
{
    synchronize_insecure_grade_points($conn, $matric_no);

    $gpa = calculate_insecure_gpa($conn, $matric_no);
    $gpa_sql = $gpa === null ? 'NULL' : number_format($gpa, 2, '.', '');

    $conn->query("UPDATE academic_records SET gpa = $gpa_sql, cgpa = $gpa_sql WHERE matric_no = '$matric_no'");

    return $gpa;
}
