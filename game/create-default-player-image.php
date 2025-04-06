<?php
// This script creates a default basketball player silhouette image 
// to ensure we have a fallback image

$imageDir = 'images/players';

// Create directory if it doesn't exist
if (!is_dir($imageDir)) {
    mkdir($imageDir, 0755, true);
}

$filename = $imageDir . '/lebron.png';

// Only create the image if it doesn't already exist
if (!file_exists($filename)) {
    // Create a blank image
    $width = 300;
    $height = 500;
    $image = imagecreatetruecolor($width, $height);
    
    // Set a transparent background
    imagesavealpha($image, true);
    $trans_color = imagecolorallocatealpha($image, 0, 0, 0, 127);
    imagefill($image, 0, 0, $trans_color);
    
    // Colors
    $black = imagecolorallocate($image, 0, 0, 0);
    $gray = imagecolorallocate($image, 150, 150, 150);
    
    // Draw a simple silhouette
    // Head
    imagefilledellipse($image, $width/2, 100, 80, 80, $gray);
    
    // Body
    imagefilledrectangle($image, $width/2 - 40, 140, $width/2 + 40, 300, $gray);
    
    // Arms
    imagefilledrectangle($image, $width/2 - 90, 150, $width/2 - 40, 190, $gray); // Left arm
    imagefilledrectangle($image, $width/2 + 40, 150, $width/2 + 90, 190, $gray); // Right arm
    
    // Legs
    imagefilledrectangle($image, $width/2 - 30, 300, $width/2 - 5, 450, $gray); // Left leg
    imagefilledrectangle($image, $width/2 + 5, 300, $width/2 + 30, 450, $gray); // Right leg
    
    // Add a basketball
    imagefilledellipse($image, $width/2, 200, 40, 40, imagecolorallocate($image, 255, 140, 0));
    
    // Save the image
    imagepng($image, $filename);
    imagedestroy($image);
    
    echo "Default player image created at $filename";
} else {
    echo "Default player image already exists at $filename";
}
?>
