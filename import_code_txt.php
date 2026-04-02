<?php
require_once 'includes/db.php';

$file = 'c:\Users\Praveen\OneDrive\Desktop\WEB_UNI_DM\zscores\Code.txt';

$conn->query("SET FOREIGN_KEY_CHECKS = 0;");

function guessSubjects($degreeName) {
    $deg = strtoupper($degreeName);
    if (strpos($deg, 'MEDICINE') !== false || strpos($deg, 'DENTAL') !== false || strpos($deg, 'VETERINARY') !== false || strpos($deg, 'BIOLOGICAL') !== false || strpos($deg, 'BIOCHEMISTRY') !== false || strpos($deg, 'AGRICULTURE') !== false || strpos($deg, 'FOOD SCIENCE') !== false) {
        return ['Biology', 'Chemistry', 'Physics'];
    } elseif (strpos($deg, 'ENGINEERING') !== false) {
        return ['Combined Mathematics', 'Physics', 'Chemistry'];
    } elseif (strpos($deg, 'ICT') !== false || strpos($deg, 'COMPUTER') !== false || strpos($deg, 'ARTIFICIAL') !== false) {
        return ['Combined Mathematics', 'Physics', 'ICT'];
    } elseif (strpos($deg, 'PHYSICAL') !== false || strpos($deg, 'APPLIED SCIENCES') !== false) {
        return ['Combined Mathematics', 'Physics', 'Chemistry'];
    } elseif (strpos($deg, 'COMMERCE') !== false || strpos($deg, 'MANAGEMENT') !== false) {
        return ['Accounting', 'Business Statistics', 'Economics'];
    } else {
        return ['Any', 'Any', 'Any'];
    }
}

if (!file_exists($file)) {
    die("File not found: $file\n");
}

$lines = file($file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
$codeMap = [];
$csvData = [];
$inCsv = false;

foreach ($lines as $line) {
    if (strpos($line, 'District,') === 0) {
        $inCsv = true;
        $csvData[] = $line;
        continue;
    }
    if ($inCsv) {
        $csvData[] = $line;
    } else {
        // Parse mapping e.g. 001A	MEDICINE (University of Colombo)
        $parts = explode("\t", $line, 2);
        if (count($parts) == 2) {
            $code = trim($parts[0]);
            $details = trim($parts[1]);
            
            // Extract degree and university
            if (preg_match('/^(.*)\s+\((.*)\)$/', $details, $matches)) {
                $degName = trim($matches[1]);
                $uniName = trim($matches[2]);
                $codeMap[$code] = ['degree' => $degName, 'uni' => $uniName];
            }
        }
    }
}

// Process mappings into the database
foreach ($codeMap as $code => &$info) {
    $uniName = $info['uni'];
    $degreeName = $info['degree'];
    
    // Get/create university
    $stmt = $conn->prepare("SELECT id FROM universities WHERE name = ?");
    $stmt->bind_param("s", $uniName);
    $stmt->execute();
    $res = $stmt->get_result();
    if ($res->num_rows > 0) {
        $uni_id = $res->fetch_assoc()['id'];
    } else {
        $stmt2 = $conn->prepare("INSERT INTO universities (name) VALUES (?)");
        $stmt2->bind_param("s", $uniName);
        $stmt2->execute();
        $uni_id = $conn->insert_id;
    }
    
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
    
    $stmt = $conn->prepare("SELECT id FROM degrees WHERE name = ? AND department_id = ?");
    $stmt->bind_param("si", $degreeName, $dept_id);
    $stmt->execute();
    $res = $stmt->get_result();
    if ($res->num_rows > 0) {
        $deg_id = $res->fetch_assoc()['id'];
    } else {
        $stmt2 = $conn->prepare("INSERT INTO degrees (department_id, name) VALUES (?, ?)");
        $stmt2->bind_param("is", $dept_id, $degreeName);
        $stmt2->execute();
        $deg_id = $conn->insert_id;
    }
    $info['deg_id'] = $deg_id;
    
    // Clear out existing cutoffs for these degrees before upserting them with new tabular data to avoid duplication 
    // or we just rely on the new ones being distinct by district. 
    $stmtDel = $conn->prepare("DELETE FROM zscore_cutoffs WHERE degree_id = ?");
    $stmtDel->bind_param("i", $deg_id);
    $stmtDel->execute();
}
unset($info); // break reference

// Parse CSV
$headers = str_getcsv(array_shift($csvData));

foreach ($csvData as $row) {
    if (empty(trim($row))) continue;
    $data = str_getcsv($row);
    if(count($data) < 2) continue;
    
    $district = trim($data[0]);
    $district = str_replace('_', ' ', $district); // Normalize NUWARA_ELIYA -> NUWARA ELIYA
    
    for ($i = 1; $i < count($headers); $i++) {
        $code = trim($headers[$i]);
        if (!isset($codeMap[$code])) continue;
        
        $zscoreStr = trim($data[$i] ?? "");
        if ($zscoreStr === "NQC" || $zscoreStr === "" || $zscoreStr === "-") continue;
        
        $zscore = floatval($zscoreStr);
        $deg_id = $codeMap[$code]['deg_id'];
        $subjects = guessSubjects($codeMap[$code]['degree']);
        
        $stmt = $conn->prepare("INSERT INTO zscore_cutoffs (degree_id, stream, cutoff, subject1, subject2, subject3, district) VALUES (?, 'General', ?, ?, ?, ?, ?)");
        $stmt->bind_param("idssss", $deg_id, $zscore, $subjects[0], $subjects[1], $subjects[2], $district);
        $stmt->execute();
    }
}

$conn->query("SET FOREIGN_KEY_CHECKS = 1;");
echo "Data import from Code.txt complete.\n";
?>