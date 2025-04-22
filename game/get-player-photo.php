<!--
    Author: Daryl John
    Student Number: 400583895
    Date: 20-03-2025
    Description: This file contains the PHP code needed to
    get the player images from the database and display them
--> 

<?php
require_once '../connect.php';
$playerName = isset($_GET['name']) ? $_GET['name'] : '';

if (empty($playerName)) {
    header("HTTP/1.0 404 Not Found");
    exit;
}

try {
    // Try to get player photo from database
    $stmt = $dbh->prepare("SELECT photo FROM hoopsdynastyplayers WHERE player_name = :player_name");
    $stmt->execute(['player_name' => $playerName]);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($result && !empty($result['photo'])) {
        // Set the content type to image
        header('Content-Type: image/jpeg');
        echo $result['photo'];
    } else {
        // Serve default image file
        $defaultImage = 'images/players/lebron.png';

        if (file_exists($defaultImage)) {
            $mime = mime_content_type($defaultImage);
            header("Content-Type: $mime");
            readfile($defaultImage);
        } else {
            header("HTTP/1.0 404 Not Found");
        }
    }
} catch (Exception $e) {
    header("HTTP/1.0 500 Internal Server Error");
    exit;
}
?>