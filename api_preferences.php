<?php
require_once "includes/db.php";
if (session_status() === PHP_SESSION_NONE) session_start();
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    header("Content-Type: application/json");
    $email = trim($_POST["email"] ?? "");
    if (!empty($email) && empty($_POST["name"])) {
        $stmt = $conn->prepare("SELECT id FROM student_preferences WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        if ($stmt->get_result()->num_rows > 0) {
            $_SESSION["user_details_set"] = true;
            echo json_encode(["success" => true, "found" => true]);
            exit;
        }
        echo json_encode(["success" => true, "found" => false]);
        exit;
    }
    $name = trim($_POST["name"] ?? "");
    $gender = $_POST["gender"] ?? "";
    $stream = $_POST["stream"] ?? "";
    $university = $_POST["university"] ?? "";
    $preferred_degree = $_POST["degree"] ?? "";
    if (empty($email) || empty($name) || empty($gender) || empty($stream) || empty($university) || empty($preferred_degree)) {
        echo json_encode(["success" => false, "error" => "Missing fields"]);
        exit;
    }
    $stmt = $conn->prepare("INSERT INTO student_preferences (email, name, gender, stream, preferred_degree) VALUES (?, ?, ?, ?, ?) ON DUPLICATE KEY UPDATE name=VALUES(name), gender=VALUES(gender), stream=VALUES(stream), preferred_degree=VALUES(preferred_degree)");
    $stmt->bind_param("sssss", $email, $name, $gender, $stream, $preferred_degree);
    if ($stmt->execute()) {
        $_SESSION["user_details_set"] = true;
        echo json_encode(["success" => true]);
    } else {
        echo json_encode(["success" => false, "error" => "Database error"]);
    }
    exit;
}
if (isset($_GET["action"]) && $_GET["action"] === "get_degrees" && isset($_GET["university"])) {
    header("Content-Type: application/json");
    $uni = $_GET["university"];
    $stmt = $conn->prepare("SELECT DISTINCT degree_name FROM flat_zscores WHERE university_name = ? ORDER BY degree_name ASC");
    $stmt->bind_param("s", $uni);
    $stmt->execute();
    $res = $stmt->get_result();
    $degrees = [];
    while ($row = $res->fetch_assoc()) $degrees[] = $row["degree_name"];
    echo json_encode($degrees);
    exit;
}
?>
