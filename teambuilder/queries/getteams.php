<?php

/**
    Author: Abdul Moeez Shaikh, Grady Rueffer
    Student Number: 400573061, 400579449
    Date: 23-03-2025
    Description: This file gets the names of the teams that are created by the logged in user from the database based on the creator ID.
    Links to: teambuilder/teambuilder.php (Redirects user back to the team builder page)
 */

// Connect to database
include "../../connect.php";
session_start();

header("Content-Type: application/json");

// Gather user data
$creator = isset($_SESSION["userid"]) ? $_SESSION["userid"] : null;

// Validate user is logged in
if (!$creator) {
    echo json_encode([]);
    exit;
}

try {
    // Query user teams
    $command = "SELECT team_name FROM hoopsdynastyteams WHERE creator = ? ORDER BY team_name";
    $stmt = $dbh->prepare($command);
    $stmt->execute([$creator]);

    // Store teams for return
    $teams = [];
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $teams[] = $row;
    }

    // Return JSON encoded teams
    echo json_encode($teams);
} catch (Exception $e) {
    echo json_encode([]);
}
