<?php
/**
 * Generate Sample Attendance Data
 * Run this once to populate attendance sessions and details
 */

require_once 'db_config.php';

echo "Generating sample attendance data...\n";

// Clear existing data
$mysqli->query("DELETE FROM attendance_details");
$mysqli->query("DELETE FROM attendance_sessions");

// Get allocation
$result = $mysqli->query("SELECT id, faculty_id, subject_id, section FROM subject_allocations LIMIT 1");
if (!$result) {
    die("Error getting allocation: " . $mysqli->error);
}
$allocation = $result->fetch_assoc();
$facultyId = $allocation['faculty_id'];
$subjectId = $allocation['subject_id'];
$section = $allocation['section'];

echo "Faculty: {$facultyId}, Subject: {$subjectId}, Section: {$section}\n";

// Get all students
$result = $mysqli->query("SELECT id FROM students");
if (!$result) {
    die("Error getting students: " . $mysqli->error);
}
$students = [];
while ($row = $result->fetch_assoc()) {
    $students[] = $row['id'];
}

echo "Found " . count($students) . " students\n";

$sessionCount = 0;
$detailCount = 0;

// Generate 10 days of attendance (1 session per day)
$today = new DateTime();
for ($i = 0; $i < 10; $i++) {
    $date = clone $today;
    $date->modify("-{$i} days");
    $dateStr = $date->format('Y-m-d');
    
    // Create 1 session per day
    $time = '10:00:00';
    $total = count($students);
    
    // Insert session
    $query = "INSERT INTO attendance_sessions (faculty_id, subject_id, section, session_date, session_time, total_students) VALUES ({$facultyId}, {$subjectId}, '{$section}', '{$dateStr}', '{$time}', {$total})";
    
    if ($mysqli->query($query)) {
        $sessionId = $mysqli->insert_id;
        $sessionCount++;
        
        // Add attendance for each student
        foreach ($students as $studentId) {
            // Random status: 70% present, 20% absent, 10% late
            $rand = rand(1, 100);
            if ($rand > 90) {
                $status = 'absent';
            } elseif ($rand > 70) {
                $status = 'late';
            } else {
                $status = 'present';
            }
            
            $query2 = "INSERT INTO attendance_details (session_id, student_id, attendance_status) VALUES ({$sessionId}, {$studentId}, '{$status}')";
            if ($mysqli->query($query2)) {
                $detailCount++;
            } else {
                echo "Error: " . $mysqli->error . "\n";
                break;
            }
        }
    } else {
        echo "Session Error: " . $mysqli->error . "\n";
    }
}

echo "\n✓ Generated " . $sessionCount . " sessions\n";
echo "✓ Generated " . $detailCount . " attendance records\n";
echo "\nAttendance data successfully created in database!\n";
?>

