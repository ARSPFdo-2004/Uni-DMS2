<?php
require_once 'includes/db.php';

$conn->query("TRUNCATE TABLE flat_zscores;");

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
    } elseif (strpos($deg, 'COMMERCE') !== false || strpos($deg, 'MANAGEMENT') !== false || strpos($deg, 'ACCOUNTING') !== false) {
        return ['Accounting', 'Business Statistics', 'Economics'];
    } else {
        return ['Any', 'Any', 'Any'];
    }
}

function normalizeDistrict($d) {
    $d = strtoupper(trim(str_replace('_', ' ', $d)));
    if ($d == "MATTALE") $d = "MATALE";
    $d = str_replace(" ", "_", $d);
    return strtolower($d);
}

$flatData = []; // [ degName_uniName => [ degree_name, uni_name, subjects, districts => [ cutoff ] ] ]

// Helper to push
function addData(&$flatData, $deg, $uni, $district, $zscore) {
    if (empty($deg) || empty($district)) return;
    $d = normalizeDistrict($district);
    $validDistricts = [
        'colombo', 'gampaha', 'kalutara', 'matale', 'kandy',
        'nuwara_eliya', 'galle', 'matara', 'hambantota', 'jaffna',
        'kilinochchi', 'mannar', 'mullaitivu', 'vavuniya', 'trincomalee',
        'batticaloa', 'ampara', 'puttalam', 'kurunegala', 'anuradhapura',
        'polonnaruwa', 'badulla', 'monaragala', 'ratnapura', 'kegalle'
    ];
    if (!in_array($d, $validDistricts)) {
        return;
    }
    
    if ($zscore === "NQC" || $zscore === "" || $zscore === "-") $zscore = null;
    else $zscore = floatval($zscore);
    
    $key = md5(strtolower($deg) . strtolower($uni));
    if (!isset($flatData[$key])) {
        $subs = guessSubjects($deg);
        $flatData[$key] = [
            'degree' => $deg,
            'uni' => $uni,
            'subjects' => $subs,
            'districts' => []
        ];
    }
    if ($zscore !== null) {
        $flatData[$key]['districts'][$d] = $zscore;
    }
}

// 1. Process Code.txt
$codeFile = 'c:\Users\Praveen\OneDrive\Desktop\WEB_UNI_DM\zscores\Code.txt';
if (file_exists($codeFile)) {
    $lines = file($codeFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    $codeMap = [];
    $csvData = [];
    $inCsv = false;
    foreach ($lines as $line) {
        if (strpos(strtolower($line), 'district,') === 0) {
            $inCsv = true;
            $csvData[] = $line;
            continue;
        }
        if ($inCsv) {
            $csvData[] = $line;
        } else {
            $parts = explode("\t", $line, 2);
            if (count($parts) == 2) {
                $code = trim($parts[0]);
                $details = trim($parts[1]);
                if (preg_match('/^(.*)\s+\((.*)\)$/', $details, $matches)) {
                    $codeMap[$code] = ['degree' => trim($matches[1]), 'uni' => trim($matches[2])];
                }
            }
        }
    }
    if (count($csvData) > 0) {
        $headers = str_getcsv(array_shift($csvData));
        foreach ($csvData as $row) {
            $data = str_getcsv($row);
            if(count($data) < 2) continue;
            $district = trim($data[0]);
            for ($i = 1; $i < count($headers); $i++) {
                $code = trim($headers[$i]);
                if (!isset($codeMap[$code])) continue;
                addData($flatData, $codeMap[$code]['degree'], $codeMap[$code]['uni'], $district, trim($data[$i] ?? ""));
            }
        }
    }
}

// 2. Process other TXT files
$files = glob("c:\\Users\\Praveen\\OneDrive\\Desktop\\WEB_UNI_DM\\zscores\\*.txt");
foreach ($files as $file) {
    if (basename($file) == 'Code.txt') continue;
    $handle = fopen($file, "r");
    if ($handle !== FALSE) {
        $headers = fgetcsv($handle, 0, "\t");
        $degreeMap = [];
        for ($i = 1; $i < count($headers); $i++) {
            $headerStr = $headers[$i];
            if (empty(trim($headerStr))) continue;
            $parts = explode("\n", str_replace("\r", "", $headerStr));
            $degreeName = preg_replace('/[*#]+/', '', trim($parts[0] ?? ""));
            $uniName = trim($parts[1] ?? "", "() \t\n\r");
            if (empty($uniName)) $uniName = "Unknown University";
            $degreeMap[$i] = ['degree' => trim($degreeName), 'uni' => $uniName];
        }
        
        while (($data = fgetcsv($handle, 0, "\t")) !== FALSE) {
            $district = trim($data[0] ?? "");
            for ($i = 1; $i < count($data); $i++) {
                if (!isset($degreeMap[$i])) continue;
                addData($flatData, $degreeMap[$i]['degree'], $degreeMap[$i]['uni'], $district, trim($data[$i] ?? ""));
            }
        }
        fclose($handle);
    }
}

// Insert into flat_zscores
foreach ($flatData as $item) {
    $deg = $item['degree'];
    $uni = $item['uni'];
    $sub1 = $item['subjects'][0];
    $sub2 = $item['subjects'][1];
    $sub3 = $item['subjects'][2];
    
    $cols = ['degree_name', 'university_name', 'subject1', 'subject2', 'subject3'];
    $vars = ['?', '?', '?', '?', '?'];
    $types = "sssss";
    $vals = [$deg, $uni, $sub1, $sub2, $sub3];
    
    foreach ($item['districts'] as $dist => $zs) {
        $cols[] = "`$dist`";
        $vars[] = "?";
        $types .= "d";
        $vals[] = $zs;
    }
    
    $sql = "INSERT INTO flat_zscores (".implode(", ", $cols).") VALUES (".implode(", ", $vars).")";
    $stmt = $conn->prepare($sql);
    if ($stmt) {
        $stmt->bind_param($types, ...$vals);
        $stmt->execute();
    }
}

echo "Inserted " . count($flatData) . " row(s) into flat_zscores.\n";
?>