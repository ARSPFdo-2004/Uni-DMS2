<?php
require_once 'includes/db.php';

$files = [
    'c:\Users\Praveen\OneDrive\Desktop\WEB_UNI_DM\zscores\1.txt',
    'c:\Users\Praveen\OneDrive\Desktop\WEB_UNI_DM\zscores\2.txt',
    'c:\Users\Praveen\OneDrive\Desktop\WEB_UNI_DM\zscores\3.txt',
    'c:\Users\Praveen\OneDrive\Desktop\WEB_UNI_DM\zscores\9.txt',
    'c:\Users\Praveen\OneDrive\Desktop\WEB_UNI_DM\zscores\10.txt'
];

$conn->query("SET FOREIGN_KEY_CHECKS = 0;");

// Optional: you can choose to not Truncate to keep the old data inserted previously, 
// and just use UPSERT or insert if not exists. The prompt requested: 
// "update already uploaded values as well in the respective degrees"
// We'll update the cutoffs. To make it clean, we'll delete existing cutoffs for the combinations 
// we process, or just use a standard get/create pattern.

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

foreach ($files as $file) {
    if (!file_exists($file)) {
        echo "File not found: $file\n";
        continue;
    }
    
    $handle = fopen($file, "r");
    if ($handle !== FALSE) {
        $headers = fgetcsv($handle, 0, "\t");
        
        $degreeMap = [];
        // Process headers (Index 1 to N)
        for ($i = 1; $i < count($headers); $i++) {
            $headerStr = $headers[$i];
            if (empty(trim($headerStr))) continue;
            
            // E.g., "MEDICINE\n(University of Colombo)"
            $parts = explode("\n", str_replace("\r", "", $headerStr));
            $degreeName = trim($parts[0] ?? "");
            // Clean up unicode or special symbols
            $degreeName = preg_replace('/[*#]+/', '', $degreeName);
            $degreeName = trim($degreeName);
            
            $uniName = trim($parts[1] ?? "", "() \t\n\r");
            if (empty($uniName)) $uniName = "Unknown University";
            
            $degreeMap[$i] = [
                'degree' => $degreeName,
                'uni' => $uniName
            ];
            
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
            
            // Faculty/Department scaffolding
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
            
            // Get/create degree
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
            $degreeMap[$i]['deg_id'] = $deg_id;
            
            // Remove previous cutoffs to update cleanly
            $stmtDel = $conn->prepare("DELETE FROM zscore_cutoffs WHERE degree_id = ?");
            $stmtDel->bind_param("i", $deg_id);
            $stmtDel->execute();
        }
        
        while (($data = fgetcsv($handle, 0, "\t")) !== FALSE) {
            $district = trim($data[0] ?? "");
            if (empty($district) || $district == 'DISTRICT') continue; // Skip header continuations or blanks
            
            for ($i = 1; $i < count($data); $i++) {
                if (!isset($degreeMap[$i])) continue;
                
                $zscoreStr = trim($data[$i] ?? "");
                if ($zscoreStr === "NQC" || $zscoreStr === "" || $zscoreStr === "-") continue;
                
                $zscore = floatval($zscoreStr);
                $deg_id = $degreeMap[$i]['deg_id'];
                $deg_name = $degreeMap[$i]['degree'];
                
                $subjects = guessSubjects($deg_name);
                
                $stream = 'General';
                $stmt = $conn->prepare("INSERT INTO zscore_cutoffs (degree_id, stream, cutoff, subject1, subject2, subject3, district) VALUES (?, ?, ?, ?, ?, ?, ?)");
                $stmt->bind_param("isdssss", $deg_id, $stream, $zscore, $subjects[0], $subjects[1], $subjects[2], $district);
                $stmt->execute();
            }
        }
        
        fclose($handle);
        echo "Processed $file\n";
    }
}

$conn->query("SET FOREIGN_KEY_CHECKS = 1;");
echo "Data import complete.\n";
?>