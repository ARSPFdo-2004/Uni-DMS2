<?php
$base_dir = __DIR__ . '/images';

// Create directories
$dirs = ['gallery', 'universities', 'hero', 'icons'];
foreach ($dirs as $dir) {
    if (!is_dir("$base_dir/$dir")) {
        mkdir("$base_dir/$dir", 0777, true);
    }
}

$files = scandir($base_dir);
$gallery_files_with_sizes = [];

foreach ($files as $file) {
    if ($file === '.' || $file === '..' || is_dir("$base_dir/$file")) {
        continue;
    }

    $file_path = "$base_dir/$file";
    $file_lower = strtolower($file);

    // Hero
    if ($file_lower === 'graduation_hats.jpg' || $file_lower === 'pattern.jpg') {
        rename($file_path, "$base_dir/hero/$file_lower");
        continue;
    }

    // Icons
    if (strpos($file_lower, 'icon') !== false) {
        rename($file_path, "$base_dir/icons/$file_lower");
        continue;
    }

    // Universities
    $uni_keywords = ['university', 'colombo', 'sabaragamuwa', 'seusl', 'wellassa', 'wayamba'];
    $is_uni = false;
    foreach ($uni_keywords as $keyword) {
        if (strpos($file_lower, $keyword) !== false) {
            $is_uni = true;
            break;
        }
    }
    
    if ($is_uni) {
        $ext = pathinfo($file, PATHINFO_EXTENSION);
        $name = pathinfo($file, PATHINFO_FILENAME);
        $clean_name = str_replace(' ', '', strtolower($name));
        $clean_ext = strtolower($ext);
        $new_name = $clean_name . '.' . $clean_ext;
        rename($file_path, "$base_dir/universities/$new_name");
        continue;
    }

    // Gallery
    if (strpos($file_lower, 'gallery') !== false || strpos($file_lower, 'graduation') !== false) {
        // Collect for sorting later
        $gallery_files_with_sizes[$file] = filesize($file_path);
    }
}

// Sort gallery files descending by size
arsort($gallery_files_with_sizes);

$counter = 1;
foreach ($gallery_files_with_sizes as $file => $size) {
    $ext = strtolower(pathinfo($file, PATHINFO_EXTENSION));
    $new_name = "gallery" . $counter . "." . $ext;
    rename("$base_dir/$file", "$base_dir/gallery/$new_name");
    $counter++;
}

echo "Images reorganized successfully!\n";
?>
