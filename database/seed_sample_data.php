<?php
/**
 * Sample data generator for CCS Sit-In Monitoring.
 * Run once in browser or CLI after the database is imported.
 * Example:
 *   http://localhost/ccs-sit-in-monitoring/database/seed_sample_data.php
 * or
 *   php database/seed_sample_data.php
 */
require_once __DIR__ . '/../config/database.php';

try {
    $db = getDB();
    $students = [
        ['id_number' => '23833354', 'email' => '23833354@uc.edu.ph', 'first_name' => 'Liam', 'middle_name' => 'A.', 'last_name' => 'Garcia', 'course' => 'BSCS', 'year_level' => '1st Year', 'section' => 'A'],
        ['id_number' => '23833355', 'email' => '23833355@uc.edu.ph', 'first_name' => 'Maya', 'middle_name' => 'B.', 'last_name' => 'Torres', 'course' => 'BSIT', 'year_level' => '2nd Year', 'section' => 'B'],
        ['id_number' => '23833356', 'email' => '23833356@uc.edu.ph', 'first_name' => 'Noah', 'middle_name' => 'C.', 'last_name' => 'Reyes', 'course' => 'BSCS', 'year_level' => '3rd Year', 'section' => 'C'],
        ['id_number' => '23833357', 'email' => '23833357@uc.edu.ph', 'first_name' => 'Ava', 'middle_name' => 'D.', 'last_name' => 'Villanueva', 'course' => 'BSIT', 'year_level' => '1st Year', 'section' => 'D'],
        ['id_number' => '23833358', 'email' => '23833358@uc.edu.ph', 'first_name' => 'Ethan', 'middle_name' => 'E.', 'last_name' => 'Torralba', 'course' => 'BSCS', 'year_level' => '4th Year', 'section' => 'E'],
        ['id_number' => '23833359', 'email' => '23833359@uc.edu.ph', 'first_name' => 'Sofia', 'middle_name' => 'F.', 'last_name' => 'Lopez', 'course' => 'BSIT', 'year_level' => '3rd Year', 'section' => 'A'],
        ['id_number' => '23833360', 'email' => '23833360@uc.edu.ph', 'first_name' => 'Mason', 'middle_name' => 'G.', 'last_name' => 'Delgado', 'course' => 'BSCS', 'year_level' => '2nd Year', 'section' => 'B'],
        ['id_number' => '23833361', 'email' => '23833361@uc.edu.ph', 'first_name' => 'Emma', 'middle_name' => 'H.', 'last_name' => 'Navarro', 'course' => 'BSIT', 'year_level' => '4th Year', 'section' => 'C'],
        ['id_number' => '23833362', 'email' => '23833362@uc.edu.ph', 'first_name' => 'Oliver', 'middle_name' => 'I.', 'last_name' => 'Martinez', 'course' => 'BSCS', 'year_level' => '1st Year', 'section' => 'D'],
        ['id_number' => '23833363', 'email' => '23833363@uc.edu.ph', 'first_name' => 'Isabella', 'middle_name' => 'J.', 'last_name' => 'Cruz', 'course' => 'BSIT', 'year_level' => '2nd Year', 'section' => 'E'],
    ];

    $studentInsert = $db->prepare('INSERT IGNORE INTO students (id_number, email, first_name, middle_name, last_name, course, year_level, section, address, password, profile_picture, remaining_sessions) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)');
    $passwordHash = password_hash('student123', PASSWORD_DEFAULT);

    foreach ($students as $student) {
        $studentInsert->execute([
            $student['id_number'],
            $student['email'],
            $student['first_name'],
            $student['middle_name'],
            $student['last_name'],
            $student['course'],
            $student['year_level'],
            $student['section'],
            'Sample address, University of Cebu',
            $passwordHash,
            'default-avatar.png',
            30,
        ]);
    }

    $recordsInsert = $db->prepare('INSERT IGNORE INTO sit_in_records (sit_in_no, student_id, purpose, laboratory_id, pc_number, scheduled_date, scheduled_time_in, time_in, time_out, duration_minutes, status, booked_on, approved_by, is_walk_in, session_deducted) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)');
    $recordTypes = [
        ['status' => 'Completed', 'purpose' => 'Research Paper', 'date' => '2026-05-18', 'time_in' => '2026-05-18 09:00:00', 'time_out' => '2026-05-18 11:00:00', 'laboratory_id' => 1, 'pc_number' => 5, 'approved' => 1],
        ['status' => 'Completed', 'purpose' => 'Group Project', 'date' => '2026-05-20', 'time_in' => '2026-05-20 13:30:00', 'time_out' => '2026-05-20 15:00:00', 'laboratory_id' => 2, 'pc_number' => 8, 'approved' => 1],
        ['status' => 'Approved', 'purpose' => 'Exam Review', 'date' => '2026-05-25', 'time_in' => null, 'time_out' => null, 'laboratory_id' => 3, 'pc_number' => 12, 'approved' => 1],
        ['status' => 'Rejected', 'purpose' => 'Late Booking', 'date' => '2026-05-21', 'time_in' => null, 'time_out' => null, 'laboratory_id' => 4, 'pc_number' => 4, 'approved' => 1],
        ['status' => 'User Cancelled', 'purpose' => 'Practice Session', 'date' => '2026-05-22', 'time_in' => null, 'time_out' => null, 'laboratory_id' => 5, 'pc_number' => 2, 'approved' => null],
    ];

    $studentQuery = $db->prepare('SELECT id FROM students WHERE id_number = ? LIMIT 1');
    $addedRecords = 0;

    foreach ($students as $student) {
        $studentQuery->execute([$student['id_number']]);
        $studentId = (int)$studentQuery->fetchColumn();
        if ($studentId <= 0) {
            continue;
        }

        foreach ($recordTypes as $index => $template) {
            $sitInNo = sprintf('SI-%s-%s-%02d', date('Ymd'), $student['id_number'], $index + 1);
            $duration = null;
            if (!empty($template['time_in']) && !empty($template['time_out'])) {
                $duration = (int) round((strtotime($template['time_out']) - strtotime($template['time_in'])) / 60);
            }
            $approvedBy = $template['approved'] ? 1 : null;
            $scheduledTime = $template['time_in'] ? date('H:i:s', strtotime($template['time_in'])) : '09:00:00';
            $recordsInsert->execute([
                $sitInNo,
                $studentId,
                $template['purpose'],
                $template['laboratory_id'],
                $template['pc_number'],
                $template['date'],
                $scheduledTime,
                $template['time_in'],
                $template['time_out'],
                $duration,
                $template['status'],
                date('Y-m-d H:i:s', strtotime($template['date'] . ' 08:00:00')),
                $approvedBy,
                0,
                $template['status'] === 'Completed' ? 1 : 0,
            ]);
            $addedRecords += $recordsInsert->rowCount() > 0 ? 1 : 0;
        }
    }

    echo '<h2>Seed Complete</h2>';
    echo '<p>Inserted ' . count($students) . ' students and up to ' . $addedRecords . ' sit-in records.</p>';
    echo '<p><a href="' . BASE_URL . '/index.php">Go back to homepage</a></p>';
    echo '<p style="color:green;">Run this file only once when seeding development data.</p>';
} catch (Exception $e) {
    echo '<h2>Error</h2>';
    echo '<p>' . htmlspecialchars($e->getMessage()) . '</p>';
}
