<?php
include "../../connect.php";
session_start();

header("Content-Type: application/json");

$creator = isset($_SESSION["userid"]) ? $_SESSION["userid"] : null;

// Validate user is logged in
if (!$creator) {
    echo json_encode([]);
    exit;
}

try {
    $command = "SELECT team_name FROM hoopsdynastyteams WHERE creator = ? ORDER BY team_name";
    $stmt = $dbh->prepare($command);
    $stmt->execute([$creator]);
    
    $teams = [];
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $teams[] = $row;
    }
    
    echo json_encode($teams);
} catch (Exception $e) {
    echo json_encode([]);
}
?>