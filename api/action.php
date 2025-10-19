<?php
require_once '../includes/db.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Unauthorized. Please log in.']);
    exit;
}

$action = $_POST['action'] ?? '';
$userRole = $_SESSION['user']['role'];

switch ($action) {
    case 'mark_attendance':
        if ($userRole === 'Student') {
            markAttendance($conn);
        } else {
            http_response_code(403);
            echo json_encode(['success' => false, 'message' => 'Forbidden']);
        }
        break;

    case 'update_user':
        if ($userRole === 'Faculty') {
            updateUser($conn);
        } else {
            http_response_code(403);
            echo json_encode(['success' => false, 'message' => 'Forbidden']);
        }
        break;

    case 'delete_user':
        if ($userRole === 'Faculty') {
            deleteUser($conn);
        } else {
            http_response_code(403);
            echo json_encode(['success' => false, 'message' => 'Forbidden']);
        }
        break;

    default:
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Invalid action specified.']);
}

function markAttendance($conn) {
    $subjectId = $_POST['subjectId'] ?? null;
    $studentId = $_SESSION['user']['id'];
    
    if (!$subjectId) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Subject ID is required.']);
        return;
    }

    try {
        $stmt = $conn->prepare("SELECT start_time, end_time FROM subjects WHERE id = ?");
        $stmt->execute([$subjectId]);
        $subject = $stmt->fetch();

        if (!$subject) {
            http_response_code(404);
            echo json_encode(['success' => false, 'message' => 'Subject not found.']);
            return;
        }

        date_default_timezone_set('UTC');
        $now = new DateTime();
        $today = $now->format('Y-m-d');

        $startTime = DateTime::createFromFormat('H:i:s', $subject['start_time']);
        $endTime = DateTime::createFromFormat('H:i:s', $subject['end_time']);
        $gracePeriodTime = (clone $startTime)->add(new DateInterval('PT15M'));

        $status = '';
        if ($now < $startTime) {
            echo json_encode(['success' => false, 'message' => 'It is too early to check in for this class.']);
            return;
        } elseif ($now > $endTime) {
            echo json_encode(['success' => false, 'message' => 'This class has already ended.']);
            return;
        } elseif ($now <= $gracePeriodTime) {
            $status = 'Present';
        } else {
            $status = 'Late';
        }

        $stmt = $conn->prepare(
            "INSERT INTO attendance (student_id, subject_id, status, attendance_date)
             VALUES (?, ?, ?, ?)
             ON DUPLICATE KEY UPDATE status = VALUES(status)"
        );
        $stmt->execute([$studentId, $subjectId, $status, $today]);
        
        $message = "Success! You have been marked '{$status}'.";
        echo json_encode(['success' => true, 'message' => $message, 'status' => $status]);

    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'A server error occurred.']);
    }
}

function updateUser($conn) {
    $userId = $_POST['userId'] ?? null;
    $name = $_POST['name'] ?? '';
    $email = $_POST['email'] ?? '';
    $role = $_POST['role'] ?? '';

    if (!$userId || empty($name) || empty($email) || empty($role)) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Missing required fields for update.']);
        return;
    }

    try {
        $stmt = $conn->prepare("UPDATE users SET name = ?, email = ?, role = ? WHERE id = ?");
        $stmt->execute([$name, $email, $role, $userId]);
        echo json_encode(['success' => true, 'message' => 'User updated successfully.']);
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'A server error occurred.']);
    }
}

function deleteUser($conn) {
    $userId = $_POST['userId'] ?? null;

    if (!$userId) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'User ID is required.']);
        return;
    }

    if ($userId == $_SESSION['user']['id']) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'You cannot delete your own account.']);
        return;
    }

    try {
        $stmt = $conn->prepare("DELETE FROM users WHERE id = ?");
        $stmt->execute([$userId]);
        echo json_encode(['success' => true, 'message' => 'User deleted successfully.']);
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'A server error occurred.']);
    }
}
?>