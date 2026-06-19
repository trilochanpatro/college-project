<?php
/**
 * Generate Diverse Attendance Data for Each Student
 * Creates different attendance patterns for each student
 */

require_once 'db_config.php';

echo "Generating diverse attendance data for each student...\n";

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

// Get all students
$result = $mysqli->query("SELECT id FROM students ORDER BY id ASC");
if (!$result) {
    die("Error getting students: " . $mysqli->error);
}
$students = [];
while ($row = $result->fetch_assoc()) {
    $students[] = $row['id'];
}

echo "Found " . count($students) . " students\n";
echo "Faculty: {$facultyId}, Subject: {$subjectId}, Section: {$section}\n\n";

$sessionCount = 0;
$detailCount = 0;

// Generate 15 days of attendance (1 session per day)
$today = new DateTime();
for ($i = 0; $i < 15; $i++) {
    $date = clone $today;
    $date->modify("-{$i} days");
    $dateStr = $date->format('Y-m-d');
    
    $time = '10:00:00';
    $total = count($students);
    
    // Insert session
    $query = "INSERT INTO attendance_sessions (faculty_id, subject_id, section, session_date, session_time, total_students) VALUES ({$facultyId}, {$subjectId}, '{$section}', '{$dateStr}', '{$time}', {$total})";
    
    if ($mysqli->query($query)) {
        $sessionId = $mysqli->insert_id;
        $sessionCount++;
        
        // Create different attendance patterns for each student
        foreach ($students as $index => $studentId) {
            // Student 1 (ID=1): Excellent attendance (90%)
            // Student 2 (ID=2): Good attendance (80%)
            // Student 3 (ID=3): Average (70%)
            // Student 4 (ID=4): Poor (50%)
            // Student 5 (ID=5): Very poor (30%)
            // Student 6 (ID=6): Excellent (85%)
            // Student 7 (ID=7): Average (65%)
            // Student 8 (ID=8): Good (75%)
            
            $attendance_rates = [
                1 => ['present' => 90, 'late' => 7],     // Student 1: 90% present, 7% late
                2 => ['present' => 80, 'late' => 10],    // Student 2: 80% present, 10% late
                3 => ['present' => 70, 'late' => 15],    // Student 3: 70% present, 15% late
                4 => ['present' => 50, 'late' => 15],    // Student 4: 50% present, 15% late
                5 => ['present' => 30, 'late' => 20],    // Student 5: 30% present, 20% late
                6 => ['present' => 85, 'late' => 8],     // Student 6: 85% present, 8% late
                7 => ['present' => 65, 'late' => 12],    // Student 7: 65% present, 12% late
                8 => ['present' => 75, 'late' => 10],    // Student 8: 75% present, 10% late
            ];
            
            $rate = $attendance_rates[$studentId] ?? ['present' => 70, 'late' => 10];
            $rand = rand(1, 100);
            
            if ($rand <= $rate['present']) {
                $status = 'present';
            } elseif ($rand <= $rate['present'] + $rate['late']) {
                $status = 'late';
            } else {
                $status = 'absent';
            }
            
            $query2 = "INSERT INTO attendance_details (session_id, student_id, attendance_status) VALUES ({$sessionId}, {$studentId}, '{$status}')";
            if ($mysqli->query($query2)) {
                $detailCount++;
            } else {
                echo "Error inserting detail: " . $mysqli->error . "\n";
            }
        }
    } else {
        echo "Session Error: " . $mysqli->error . "\n";
    }
}

echo "\n✓ Generated " . $sessionCount . " sessions\n";
echo "✓ Generated " . $detailCount . " attendance records\n";
echo "✓ Total: " . $sessionCount . " sessions × " . count($students) . " students = " . $detailCount . " records\n";
echo "\nDiverse attendance data successfully created!\n";
echo "\nAttendance patterns:\n";
echo "  Student 1: 90% excellent\n";
echo "  Student 2: 80% good\n";
echo "  Student 3: 70% average\n";
echo "  Student 4: 50% poor\n";
echo "  Student 5: 30% very poor\n";
echo "  Student 6: 85% excellent\n";
echo "  Student 7: 65% average\n";
echo "  Student 8: 75% good\n";
?>
