<?php
/**
 * UI Helper functions for University-DMS
 */

/**
 * Returns the image path for a university or a placeholder if not found.
 * 
 * @param array $university The university data array
 * @return string The image path or placeholder URL
 */
function getUniversityImagePath($university) {
    if (!empty($university['image_url']) && file_exists($university['image_url'])) {
        return $university['image_url'];
    }
    
    // Map university names to their exact filenames in the images/universities folder
    $uni_name = isset($university['name']) ? $university['name'] : '';
    $static_map = [
        'University of Colombo' => 'images/universities/colombo.jpg',
        'University of Peradeniya' => 'images/universities/universityofperadeniya.jfif',
        'University of Sri Jayewardenepura' => 'images/universities/universityofsrijayewardenepura.jpg',
        'University of Kelaniya' => 'images/universities/universityofkelaniya.webp',
        'University of Moratuwa' => 'images/universities/universityofmoratuwa.jpg',
        'University of Jaffna' => 'images/universities/universityofjaffna.jpg', // also has .jpeg
        'Eastern University' => 'images/universities/easternuniversityofmoratuwa.jpg',
        'South Eastern University of Sri Lanka' => 'images/universities/seusl.jpg',
        'Rajarata University of Sri Lanka' => 'images/universities/rajaratauniversity.jpg',
        'Wayamba University of Sri Lanka' => 'images/universities/wayamba.jpg',
        'Sabaragamuwa University of Sri Lanka' => 'images/universities/sabaragamuwa.jpg',
        'Uva Wellassa University' => 'images/universities/uwawellassa.jpg',
        'University of Ruhuna' => 'images/universities/universityofruhuna.jpg',
        'University of Vavuniya, Sri Lanka' => 'images/universities/vavuniya.jpg',
        'University of Vavuniya' => 'images/universities/vavuniya.jpg',
        'Gampaha Wickramarachchi University of Indigenous Medicine, Sri Lanka' => 'images/universities/gampaha.jpg',
        'Gampaha Wickramarachchi University of Indigenous Medicine' => 'images/universities/gampaha.jpg',
    ];
    
    if (isset($static_map[$uni_name]) && file_exists($static_map[$uni_name])) {
        return $static_map[$uni_name];
    }

    if (!empty($university['image']) && file_exists($university['image'])) {
        return $university['image'];
    }

    // Check if an image exists in the uploads folder based on ID
    $id = isset($university['id']) ? $university['id'] : 0;
    $localImagePath = "uploads/universities/uni_{$id}.jpg";

    if (file_exists($localImagePath)) {
        return $localImagePath;
    }

    // If no valid image exists, return the placeholder pattern image
    return "images/universities/placeholder.jpg";
}

/**
 * Returns the university location or a default string.
 * 
 * @param array $university The university data array
 * @return string The location
 */
function getUniversityLocation($university) {
    return !empty($university['location']) ? $university['location'] : 'Sri Lanka';
}

/**
 * Returns a truncated description of the university.
 * 
 * @param array $university The university data array
 * @param int $limit The character limit
 * @return string The truncated description
 */
function getUniversityDescription($university, $limit = 200) {
    $desc = !empty($university['description']) ? $university['description'] : 'Discover programs and opportunities at this institution.';
    
    if (strlen($desc) > $limit) {
        $desc = substr($desc, 0, $limit) . '...';
    }
    
    return $desc;
}

/**
 * Returns the university type (Public/Private/etc).
 * 
 * @param array $university The university data array
 * @return string The type
 */
function getUniversityType($university) {
    return !empty($university['type']) ? $university['type'] : 'University';
}
