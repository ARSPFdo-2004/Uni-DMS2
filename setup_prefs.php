<?php
require_once "includes/db.php";

$sql = "CREATE TABLE IF NOT EXISTS user_preferences (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255),
    email VARCHAR(255) UNIQUE,
    gender VARCHAR(50),
    stream VARCHAR(100),
    university VARCHAR(255),
    degree VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)";
$conn->query($sql);
echo "Table user_preferences created.";
?>