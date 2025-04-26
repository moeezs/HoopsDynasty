<?php

/**
    Author: Abdul Moeez Shaikh
    Student Number: 400573061
    Date: 23-03-2025
    Description: This file gets the players of a team from the database based on the team name and creator ID.
    It retrieves the player details for each position (center, power forward, small forward, point guard, shooting guard)
    and returns the data in JSON format. The player photo is converted to a base64 encoded string for display purposes.
    It also handles errors and checks if the user is logged in.
    Links to: teambuilder/teambuilder.php (Redirects user back to the team builder page)
 */

include "../../connect.php";
session_start();

header("Content-Type: application/json");

$team_name = filter_input(INPUT_POST, "team_name", FILTER_SANITIZE_SPECIAL_CHARS);
$creator = isset($_SESSION["userid"]) ? $_SESSION["userid"] : null;

// Check if we have required parameters
if (!$team_name || !$creator) {
    echo json_encode(["status" => "error", "message" => "Missing team name or not logged in"]);
    exit;
}

try {
    // Get team info
    $command = "SELECT center, power_forward, small_forward, point_guard, shooting_guard 
                FROM hoopsdynastyteams 
                WHERE team_name = ? AND creator = ?";
    $stmt = $dbh->prepare($command);
    $stmt->execute([$team_name, $creator]);
    
    $team = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$team) {
        echo json_encode(["status" => "error", "message" => "Team not found"]);
        exit;
    }
    
    // Get player details for each position
    $players = [];
    $positions = [
        'C' => $team['center'],
        'PF' => $team['power_forward'],
        'SF' => $team['small_forward'],
        'PG' => $team['point_guard'],
        'SG' => $team['shooting_guard']
    ];
    
    foreach ($positions as $pos => $playerName) {
        if (empty($playerName)) continue;
        
        $playerCommand = "SELECT player_id, player_name, player_team, three_point_percentage, 
                           two_point_percentage, free_throw_percentage, blocks_per_game, 
                           steals_per_game, personal_fouls_per_game, photo 
                           FROM hoopsdynastyplayers 
                           WHERE player_name = ? LIMIT 1";
        $playerStmt = $dbh->prepare($playerCommand);
        $playerStmt->execute([$playerName]);
        $player = $playerStmt->fetch(PDO::FETCH_ASSOC);
        
        if ($player) {
            // Convert blob to base64 encoded string for image display
            $photoBase64 = null;
            if ($player["photo"] && strlen($player["photo"]) > 0) {
                $photoBase64 = 'data:image/jpeg;base64,' . base64_encode($player["photo"]);
            }
            
            $player['photo'] = $photoBase64;
            $player['position'] = $pos;
            $players[] = $player;
        }
    }
    
    echo json_encode([
        "status" => "success",
        "players" => $players
    ]);
    
} catch (Exception $e) {
    echo json_encode(["status" => "error", "message" => "An error occurred: " . $e->getMessage()]);
}
?>