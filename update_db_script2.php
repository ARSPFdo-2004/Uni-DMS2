<?php
require_once 'includes/db.php';

// Turn off checks so we can truncate cleanly
$conn->query("SET FOREIGN_KEY_CHECKS = 0;");

// Clean all relevant tables to "delete previously uploaded data"
$conn->query("TRUNCATE TABLE zscore_cutoffs");
$conn->query("TRUNCATE TABLE degrees");
$conn->query("TRUNCATE TABLE departments");
$conn->query("TRUNCATE TABLE faculties");
$conn->query("TRUNCATE TABLE universities");

// Base structure array containing the new data from University.docx
$data = [
    // University of Moratuwa
    // Maths, Physics, Chemistry - Engineering
    ['University of Moratuwa', 'BSc Engineering Honours', 'Combined Mathematics', 'Physics', 'Chemistry', 'Colombo', 2.0708, '4 years', 'English'],
    ['University of Moratuwa', 'BSc Engineering Honours', 'Combined Mathematics', 'Physics', 'Chemistry', 'Gampaha', 2.0778, '4 years', 'English'],
    ['University of Moratuwa', 'BSc Engineering Honours', 'Combined Mathematics', 'Physics', 'Chemistry', 'Kalutara', 2.1285, '4 years', 'English'],
    ['University of Moratuwa', 'BSc Engineering Honours', 'Combined Mathematics', 'Physics', 'Chemistry', 'Galle', 2.0715, '4 years', 'English'],
    ['University of Moratuwa', 'BSc Engineering Honours', 'Combined Mathematics', 'Physics', 'Chemistry', 'Kandy', 2.0711, '4 years', 'English'],
    ['University of Moratuwa', 'BSc Engineering Honours', 'Combined Mathematics', 'Physics', 'Chemistry', 'Jaffna', 2.0708, '4 years', 'English'],
    
    // Engineering – Earth Resources (EM)
    ['University of Moratuwa', 'BSc Engineering Honours - Earth Resources Engineering', 'Combined Mathematics', 'Physics', 'Chemistry', 'Colombo', 1.6128, '4 years', 'English'],
    ['University of Moratuwa', 'BSc Engineering Honours - Earth Resources Engineering', 'Combined Mathematics', 'Physics', 'Chemistry', 'Gampaha', 1.6372, '4 years', 'English'],
    ['University of Moratuwa', 'BSc Engineering Honours - Earth Resources Engineering', 'Combined Mathematics', 'Physics', 'Chemistry', 'Kalutara', 1.6052, '4 years', 'English'],
    ['University of Moratuwa', 'BSc Engineering Honours - Earth Resources Engineering', 'Combined Mathematics', 'Physics', 'Chemistry', 'Galle', 1.6177, '4 years', 'English'],
    ['University of Moratuwa', 'BSc Engineering Honours - Earth Resources Engineering', 'Combined Mathematics', 'Physics', 'Chemistry', 'Kandy', 1.6191, '4 years', 'English'],

    // Engineering – Textile & Apparel (TM)
    ['University of Moratuwa', 'BSc Engineering Honours - Textile & Apparel Engineering', 'Combined Mathematics', 'Physics', 'Chemistry', 'Colombo', 1.6254, '4 years', 'English'],
    ['University of Moratuwa', 'BSc Engineering Honours - Textile & Apparel Engineering', 'Combined Mathematics', 'Physics', 'Chemistry', 'Gampaha', 1.6217, '4 years', 'English'],
    ['University of Moratuwa', 'BSc Engineering Honours - Textile & Apparel Engineering', 'Combined Mathematics', 'Physics', 'Chemistry', 'Kalutara', 1.5863, '4 years', 'English'],
    ['University of Moratuwa', 'BSc Engineering Honours - Textile & Apparel Engineering', 'Combined Mathematics', 'Physics', 'Chemistry', 'Galle', 1.6418, '4 years', 'English'],
    ['University of Moratuwa', 'BSc Engineering Honours - Textile & Apparel Engineering', 'Combined Mathematics', 'Physics', 'Chemistry', 'Kandy', 1.6303, '4 years', 'English'],

    // Transport Mgmt & Logistics Eng. (TMLE)
    ['University of Moratuwa', 'Transport Management & Logistics Engineering', 'Combined Mathematics', 'Physics', 'Chemistry', 'Colombo', 1.4540, '4 years', 'English'],
    ['University of Moratuwa', 'Transport Management & Logistics Engineering', 'Combined Mathematics', 'Physics', 'Chemistry', 'Gampaha', 1.4837, '4 years', 'English'],
    ['University of Moratuwa', 'Transport Management & Logistics Engineering', 'Combined Mathematics', 'Physics', 'Chemistry', 'Kalutara', 1.4399, '4 years', 'English'],
    ['University of Moratuwa', 'Transport Management & Logistics Engineering', 'Combined Mathematics', 'Physics', 'Chemistry', 'Galle', 1.4652, '4 years', 'English'],
    ['University of Moratuwa', 'Transport Management & Logistics Engineering', 'Combined Mathematics', 'Physics', 'Chemistry', 'Kandy', 1.4976, '4 years', 'English'],

    // Artificial Intelligence (Maths, Physics, ICT)
    ['University of Moratuwa', 'BSc Honours in Artificial Intelligence', 'Combined Mathematics', 'Physics', 'ICT', 'Colombo', 2.2536, '4 years', 'English'],
    ['University of Moratuwa', 'BSc Honours in Artificial Intelligence', 'Combined Mathematics', 'Physics', 'ICT', 'Gampaha', 1.6730, '4 years', 'English'],
    ['University of Moratuwa', 'BSc Honours in Artificial Intelligence', 'Combined Mathematics', 'Physics', 'ICT', 'Kalutara', 1.7009, '4 years', 'English'],
    ['University of Moratuwa', 'BSc Honours in Artificial Intelligence', 'Combined Mathematics', 'Physics', 'ICT', 'Galle', 1.7136, '4 years', 'English'],
    ['University of Moratuwa', 'BSc Honours in Artificial Intelligence', 'Combined Mathematics', 'Physics', 'ICT', 'Kandy', 1.7325, '4 years', 'English'],

    // Information Technology
    ['University of Moratuwa', 'BSc Honours in Information Technology', 'Combined Mathematics', 'Physics', 'ICT', 'Colombo', 1.9302, '4 years', 'English'],
    ['University of Moratuwa', 'BSc Honours in Information Technology', 'Combined Mathematics', 'Physics', 'ICT', 'Gampaha', 1.4223, '4 years', 'English'],
    ['University of Moratuwa', 'BSc Honours in Information Technology', 'Combined Mathematics', 'Physics', 'ICT', 'Kalutara', 1.4522, '4 years', 'English'],
    ['University of Moratuwa', 'BSc Honours in Information Technology', 'Combined Mathematics', 'Physics', 'ICT', 'Galle', 1.4552, '4 years', 'English'],
    ['University of Moratuwa', 'BSc Honours in Information Technology', 'Combined Mathematics', 'Physics', 'ICT', 'Kandy', 1.4664, '4 years', 'English'],

    // IT & Management
    ['University of Moratuwa', 'BSc Honours in Information Technology & Management', 'Combined Mathematics', 'Physics', 'ICT', 'Colombo', 1.7011, '4 years', 'English'],
    ['University of Moratuwa', 'BSc Honours in Information Technology & Management', 'Combined Mathematics', 'Physics', 'ICT', 'Gampaha', 1.4482, '4 years', 'English'],
    ['University of Moratuwa', 'BSc Honours in Information Technology & Management', 'Combined Mathematics', 'Physics', 'ICT', 'Kalutara', 1.4525, '4 years', 'English'],
    ['University of Moratuwa', 'BSc Honours in Information Technology & Management', 'Combined Mathematics', 'Physics', 'ICT', 'Galle', 1.4554, '4 years', 'English'],
    ['University of Moratuwa', 'BSc Honours in Information Technology & Management', 'Combined Mathematics', 'Physics', 'ICT', 'Kandy', 1.4865, '4 years', 'English'],

    // Architecture (Maths, Physics, Chemistry / ICT / Biology) -> let's create a row for each possible subject 3
    ['University of Moratuwa', 'Bachelor of Architecture', 'Combined Mathematics', 'Physics', 'Chemistry', 'Colombo', 1.2358, '5 years', 'English'],
    ['University of Moratuwa', 'Bachelor of Architecture', 'Combined Mathematics', 'Physics', 'Chemistry', 'Gampaha', 1.2652, '5 years', 'English'],
    ['University of Moratuwa', 'Bachelor of Architecture', 'Combined Mathematics', 'Physics', 'Chemistry', 'Kalutara', 1.2645, '5 years', 'English'],
    ['University of Moratuwa', 'Bachelor of Architecture', 'Combined Mathematics', 'Physics', 'Chemistry', 'Kandy', 1.2356, '5 years', 'English'],
    ['University of Moratuwa', 'Bachelor of Architecture', 'Combined Mathematics', 'Physics', 'ICT', 'Colombo', 1.2358, '5 years', 'English'],
    ['University of Moratuwa', 'Bachelor of Architecture', 'Combined Mathematics', 'Physics', 'ICT', 'Gampaha', 1.2652, '5 years', 'English'],
    ['University of Moratuwa', 'Bachelor of Architecture', 'Combined Mathematics', 'Physics', 'ICT', 'Kalutara', 1.2645, '5 years', 'English'],
    ['University of Moratuwa', 'Bachelor of Architecture', 'Combined Mathematics', 'Physics', 'ICT', 'Kandy', 1.2356, '5 years', 'English'],
    ['University of Moratuwa', 'Bachelor of Architecture', 'Combined Mathematics', 'Physics', 'Biology', 'Colombo', 1.2358, '5 years', 'English'],
    ['University of Moratuwa', 'Bachelor of Architecture', 'Combined Mathematics', 'Physics', 'Biology', 'Gampaha', 1.2652, '5 years', 'English'],
    ['University of Moratuwa', 'Bachelor of Architecture', 'Combined Mathematics', 'Physics', 'Biology', 'Kalutara', 1.2645, '5 years', 'English'],
    ['University of Moratuwa', 'Bachelor of Architecture', 'Combined Mathematics', 'Physics', 'Biology', 'Kandy', 1.2356, '5 years', 'English'],

    // University of Peradeniya
    ['University of Peradeniya', 'BSc Engineering Honours', 'Combined Mathematics', 'Physics', 'Chemistry', 'Colombo', 1.8993, '4 years', 'English'],
    ['University of Peradeniya', 'BSc Engineering Honours', 'Combined Mathematics', 'Physics', 'Chemistry', 'Gampaha', 1.9004, '4 years', 'English'],
    ['University of Peradeniya', 'BSc Engineering Honours', 'Combined Mathematics', 'Physics', 'Chemistry', 'Kalutara', 1.9395, '4 years', 'English'],
    ['University of Peradeniya', 'BSc Engineering Honours', 'Combined Mathematics', 'Physics', 'Chemistry', 'Kandy', 1.9083, '4 years', 'English'],
    ['University of Peradeniya', 'BSc Engineering Honours', 'Combined Mathematics', 'Physics', 'Chemistry', 'Galle', 1.9110, '4 years', 'English'],

    // Physical Science
    ['University of Peradeniya', 'BSc Physical Science', 'Combined Mathematics', 'Physics', 'Chemistry', 'Colombo', 1.5223, '3 years', 'English'],
    ['University of Peradeniya', 'BSc Physical Science', 'Combined Mathematics', 'Physics', 'Chemistry', 'Gampaha', 1.4661, '3 years', 'English'],
    ['University of Peradeniya', 'BSc Physical Science', 'Combined Mathematics', 'Physics', 'Chemistry', 'Kalutara', 1.4074, '3 years', 'English'],
    ['University of Peradeniya', 'BSc Physical Science', 'Combined Mathematics', 'Physics', 'Chemistry', 'Kandy', 1.3291, '3 years', 'English'],
    ['University of Peradeniya', 'BSc Physical Science', 'Combined Mathematics', 'Physics', 'Biology', 'Colombo', 1.5223, '3 years', 'English'],
    ['University of Peradeniya', 'BSc Physical Science', 'Combined Mathematics', 'Physics', 'Biology', 'Gampaha', 1.4661, '3 years', 'English'],
    ['University of Peradeniya', 'BSc Physical Science', 'Combined Mathematics', 'Physics', 'Biology', 'Kalutara', 1.4074, '3 years', 'English'],
    ['University of Peradeniya', 'BSc Physical Science', 'Combined Mathematics', 'Physics', 'Biology', 'Kandy', 1.3291, '3 years', 'English'],

    // University of Ruhuna
    ['University of Ruhuna', 'BSc Engineering Honours', 'Combined Mathematics', 'Physics', 'Chemistry', 'Colombo', 1.9004, '4 years', 'English'],
    ['University of Ruhuna', 'BSc Engineering Honours', 'Combined Mathematics', 'Physics', 'Chemistry', 'Galle', 1.9110, '4 years', 'English'],
    ['University of Ruhuna', 'BSc Engineering Honours', 'Combined Mathematics', 'Physics', 'Chemistry', 'Matara', 1.9123, '4 years', 'English'],
    ['University of Ruhuna', 'BSc Engineering Honours', 'Combined Mathematics', 'Physics', 'Chemistry', 'Hambantota', 1.9043, '4 years', 'English'],

    ['University of Ruhuna', 'BSc Physical Science', 'Combined Mathematics', 'Physics', 'ICT', 'Colombo', 0.9855, '3 years', 'English'],
    ['University of Ruhuna', 'BSc Physical Science', 'Combined Mathematics', 'Physics', 'ICT', 'Galle', 1.0824, '3 years', 'English'],
    ['University of Ruhuna', 'BSc Physical Science', 'Combined Mathematics', 'Physics', 'ICT', 'Matara', 1.0990, '3 years', 'English'],

    // University of Jaffna
    ['University of Jaffna', 'BSc Engineering Honours', 'Combined Mathematics', 'Physics', 'Chemistry', 'Colombo', 1.8993, '4 years', 'English'],
    ['University of Jaffna', 'BSc Engineering Honours', 'Combined Mathematics', 'Physics', 'Chemistry', 'Jaffna', 1.9034, '4 years', 'English'],

    ['University of Jaffna', 'BSc Computer Science', 'Combined Mathematics', 'Physics', 'ICT', 'Colombo', 1.4027, '3 years', 'English'],
    ['University of Jaffna', 'BSc Computer Science', 'Combined Mathematics', 'Physics', 'ICT', 'Jaffna', 1.3227, '3 years', 'English'],

    // University of Sri Jayewardenepura
    ['University of Sri Jayewardenepura', 'BSc Engineering Honours', 'Combined Mathematics', 'Physics', 'Chemistry', 'Colombo', 1.8173, '4 years', 'English'],
    ['University of Sri Jayewardenepura', 'BSc Engineering Honours', 'Combined Mathematics', 'Physics', 'Chemistry', 'Gampaha', 1.8256, '4 years', 'English'],

    ['University of Sri Jayewardenepura', 'BSc Computing / Computer Science', 'Combined Mathematics', 'Physics', 'ICT', 'Colombo', 1.4118, '4 years', 'English'],
    ['University of Sri Jayewardenepura', 'BSc Computing / Computer Science', 'Combined Mathematics', 'Physics', 'ICT', 'Gampaha', 1.4028, '4 years', 'English'],

    // South Eastern University of Sri Lanka
    ['South Eastern University of Sri Lanka', 'BSc Engineering Honours', 'Combined Mathematics', 'Physics', 'Chemistry', 'Ampara', 1.8837, '4 years', 'English'],
    ['South Eastern University of Sri Lanka', 'BSc Engineering Honours', 'Combined Mathematics', 'Physics', 'Chemistry', 'Batticaloa', 1.8925, '4 years', 'English'],
    ['South Eastern University of Sri Lanka', 'BSc Engineering Honours', 'Combined Mathematics', 'Physics', 'Chemistry', 'Trincomalee', 1.8836, '4 years', 'English'],
];

foreach ($data as $row) {
    list($uni_name, $deg_name, $sub1, $sub2, $sub3, $district, $cutoff, $duration, $medium) = $row;
    
    // Get/create university
    $stmt = $conn->prepare("SELECT id FROM universities WHERE name = ?");
    $stmt->bind_param("s", $uni_name);
    $stmt->execute();
    $res = $stmt->get_result();
    if ($res->num_rows > 0) {
        $uni_id = $res->fetch_assoc()['id'];
    } else {
        $stmt2 = $conn->prepare("INSERT INTO universities (name) VALUES (?)");
        $stmt2->bind_param("s", $uni_name);
        $stmt2->execute();
        $uni_id = $conn->insert_id;
    }
    
    // Get/create specific faculty
    $stmt = $conn->prepare("SELECT id FROM faculties WHERE university_id = ? AND name = 'Faculty of General'");
    $stmt->bind_param("i", $uni_id);
    $stmt->execute();
    $res = $stmt->get_result();
    if ($res->num_rows > 0) {
        $fac_id = $res->fetch_assoc()['id'];
    } else {
        $conn->query("INSERT INTO faculties (university_id, name) VALUES ($uni_id, 'Faculty of General')");
        $fac_id = $conn->insert_id;
    }

    // Get/create specific department
    $stmt = $conn->prepare("SELECT id FROM departments WHERE faculty_id = ? AND name = 'Department of General'");
    $stmt->bind_param("i", $fac_id);
    $stmt->execute();
    $res = $stmt->get_result();
    if ($res->num_rows > 0) {
        $dept_id = $res->fetch_assoc()['id'];
    } else {
        $conn->query("INSERT INTO departments (faculty_id, name) VALUES ($fac_id, 'Department of General')");
        $dept_id = $conn->insert_id;
    }
    
    // Get/create degree
    $stmt = $conn->prepare("SELECT id FROM degrees WHERE name = ? AND department_id IN (SELECT id FROM departments WHERE faculty_id IN (SELECT id FROM faculties WHERE university_id = ?))");
    $stmt->bind_param("si", $deg_name, $uni_id);
    $stmt->execute();
    $res = $stmt->get_result();
    if ($res->num_rows > 0) {
        $deg_id = $res->fetch_assoc()['id'];
    } else {
        $stmt2 = $conn->prepare("INSERT INTO degrees (department_id, name, duration, medium) VALUES (?, ?, ?, ?)");
        $stmt2->bind_param("isss", $dept_id, $deg_name, $duration, $medium);
        $stmt2->execute();
        $deg_id = $conn->insert_id;
    }
    
    // Insert cutoff
    $stream = 'Physical Science'; 
    $stmt = $conn->prepare("INSERT INTO zscore_cutoffs (degree_id, stream, cutoff, subject1, subject2, subject3, district) VALUES (?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("isdssss", $deg_id, $stream, $cutoff, $sub1, $sub2, $sub3, $district);
    $stmt->execute();
}

$conn->query("SET FOREIGN_KEY_CHECKS = 1;");
echo "Database successfully populated with district-specific details.";
