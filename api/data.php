<?php
require_once '../includes/db.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Unauthorized. Please log in.']);
    exit;
}

$fetchType = $_GET['fetch'] ?? 'dashboard';
$user = $_SESSION['user'];

try {
    switch ($fetchType) {
        case 'dashboard':
            if ($user['role'] === 'Student') {
                getStudentDashboardData($conn, $user['id']);
            } else {
                getFacultyDashboardData($conn);
            }
            break;

        case 'roster':
            if ($user['role'] === 'Faculty') {
                getClassRosterData($conn);
            } else {
                http_response_code(403);
                echo json_encode(['success' => false, 'message' => 'Forbidden']);
            }
            break;

        case 'users':
            if ($user['role'] === 'Faculty') {
                getUserManagementData($conn);
            } else {
                http_response_code(403);
                echo json_encode(['success' => false, 'message' => 'Forbidden']);
            }
            break;

        default:
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Invalid fetch type.']);
            break;
    }
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'A server error occurred.']);
}

function getStudentDashboardData($conn, $studentId) {
    $today = date('Y-m-d');
    
    $stmt = $conn->prepare(
       "SELECT s.id, s.name, s.start_time, s.end_time, a.status
        FROM subjects s
        LEFT JOIN attendance a ON s.id = a.subject_id AND a.student_id = ? AND a.attendance_date = ?
        ORDER BY s.start_time"
    );
    $stmt->execute([$studentId, $today]);
    $schedule = $stmt->fetchAll();

    echo json_encode(['success' => true, 'schedule' => $schedule]);
}

function getFacultyDashboardData($conn) {
    updateAbsences($conn);
    $today = date('Y-m-d');
    
    $stmt = $conn->query("SELECT COUNT(*) as count FROM users WHERE role = 'Student'");
    $totalStudents = $stmt->fetch()['count'];
    
    $stmt = $conn->prepare("SELECT status, COUNT(DISTINCT student_id) as count FROM attendance WHERE attendance_date = ? GROUP BY status");
    $stmt->execute([$today]);
    $attendanceCounts = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);

    $stats = [
        'total' => (int) $totalStudents,
        'present' => (int) ($attendanceCounts['Present'] ?? 0),
        'late' => (int) ($attendanceCounts['Late'] ?? 0),
        'absent' => (int) ($attendanceCounts['Absent'] ?? 0)
    ];

    $notifications = $_SESSION['new_absences'] ?? [];
    unset($_SESSION['new_absences']);

    echo json_encode(['success' => true, 'stats' => $stats, 'notifications' => $notifications]);
}

function getClassRosterData($conn) {
    $today = date('Y-m-d');
    $defaultSubjectId = 1;

    $stmt = $conn->prepare(
       "SELECT u.id, u.name, u.email, a.status
        FROM users u
        LEFT JOIN attendance a ON u.id = a.student_id AND a.subject_id = ? AND a.attendance_date = ?
        WHERE u.role = 'Student'
        ORDER BY u.name"
    );
    $stmt->execute([$defaultSubjectId, $today]);
    $roster = $stmt->fetchAll();

    echo json_encode(['success' => true, 'roster' => $roster]);
}

function getUserManagementData($conn) {
    $stmt = $conn->query("SELECT id, name, email, role FROM users ORDER BY name");
    $users = $stmt->fetchAll();
    echo json_encode(['success' => true, 'users' => $users]);
}

function updateAbsences($conn) {
    $today = date('Y-m-d');
    date_default_timezone_set('UTC');
    $now = new DateTime();
    $currentTime = $now->format('H:i:s');
    
    $stmt = $conn->prepare("SELECT id, name, end_time FROM subjects WHERE end_time < ?");
    $stmt->execute([$currentTime]);
    $pastSubjects = $stmt->fetchAll();

    if (empty($pastSubjects)) {
        return;
    }

    $subjectIds = array_column($pastSubjects, 'id');
    $placeholders = implode(',', array_fill(0, count($subjectIds), '?'));

    $getStudentsSQL = 
       "SELECT u.id as student_id, s.id as subject_id, s.name as subject_name, u.name as student_name
        FROM users u
        CROSS JOIN subjects s
        WHERE u.role = 'Student' AND s.id IN ($placeholders)";

    $stmt = $conn->prepare($getStudentsSQL);
    $stmt->execute($subjectIds);
    $allPossibleAttendances = $stmt->fetchAll();
    
    $insertStmt = $conn->prepare(
        "INSERT IGNORE INTO attendance (student_id, subject_id, status, attendance_date) VALUES (?, ?, 'Absent', ?)"
    );

    $_SESSION['new_absences'] = [];
    $conn->beginTransaction();
    foreach ($allPossibleAttendances as $combo) {
        $insertStmt->execute([$combo['student_id'], $combo['subject_id'], $today]);
        if ($insertStmt->rowCount() > 0) {
            $_SESSION['new_absences'][] = [
                'id' => time() . rand(100, 999),
                'message' => "{$combo['student_name']} was marked Absent for {$combo['subject_name']}."
            ];
        }
    }
    $conn->commit();
}
?>