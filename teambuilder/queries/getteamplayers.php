<?php
include "../../connect.php";
session_start();

header("Content-Type: application/json");

$team_name = filter_input(INPUT_GET, "team_name", FILTER_SANITIZE_SPECIAL_CHARS);
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