<?php
require_once "includes/db.php";
if (session_status() === PHP_SESSION_NONE) session_start();

header('Content-Type: application/json');

$action = $_GET['action'] ?? '';

if ($action === 'get_degrees') {
    $uni = $_GET['university'] ?? '';
    $stmt = $conn->prepare("SELECT DISTINCT degree_name FROM flat_zscores WHERE university_name = ? ORDER BY degree_name ASC");
    $stmt->bind_param("s", $uni);
    $stmt->execute();
    $result = $stmt->get_result();
    $degrees = [];
    while ($row = $result->fetch_assoc()) {
        $degrees[] = $row['degree_name'];
    }
    echo json_encode($degrees);
    exit;
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $email = trim($_POST['email'] ?? '');
    $name = trim($_POST['name'] ?? '');
    
    if (empty($name)) {
        // Just checking email
        $stmt = $conn->prepare("SELECT id FROM user_preferences WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            $_SESSION['user_prefs_set'] = true;
            echo json_encode(['success' => true, 'found' => true]);
        } else {
            echo json_encode(['success' => true, 'found' => false]);
        }
        exit;
    } else {
        // Submitting full form
        $gender = $_POST['gender'] ?? '';
        $stream = $_POST['stream'] ?? '';
        $university = $_POST['university'] ?? '';
        $degree = $_POST['degree'] ?? '';
        
        $stmt = $conn->prepare("INSERT INTO user_preferences (name, email, gender, stream, university, degree) VALUES (?, ?, ?, ?, ?, ?) ON DUPLICATE KEY UPDATE name=VALUES(name), gender=VALUES(gender), stream=VALUES(stream), university=VALUES(university), degree=VALUES(degree)");
        $stmt->bind_param("ssssss", $name, $email, $gender, $stream, $university, $degree);
        
        if ($stmt->execute()) {
            $_SESSION['user_prefs_set'] = true;
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false, 'error' => $conn->error]);
        }
        exit;
    }
}
?>