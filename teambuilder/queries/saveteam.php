<?php
include "../../connect.php";
session_start();

header("Content-Type: application/json");

// Get user ID from session
$creator = isset($_SESSION["userid"]) ? $_SESSION["userid"] : null;
$team_name = filter_input(INPUT_POST, "team_name", FILTER_SANITIZE_SPECIAL_CHARS);
// Don't use FILTER_SANITIZE_SPECIAL_CHARS for JSON data as it can break JSON structure
$player_names_json = $_POST["player_names"];
$positions_json = $_POST["positions"];

// Validate user is logged in
if (!$creator) {
    echo json_encode(["status" => "error", "message" => "You must be logged in to save a team."]);
    exit;
}

// Validate input
if (!$team_name) {
    echo json_encode(["status" => "error", "message" => "Invalid team name."]);
    exit;
}

if (!$player_names_json || !$positions_json) {
    echo json_encode(["status" => "error", "message" => "Missing player data."]);
    exit;
}

try {
    // Decode JSON data
    $player_names = json_decode($player_names_json, true);
    $positions = json_decode($positions_json, true);
    
    // Debug the received data
    error_log("Player names: " . print_r($player_names, true));
    error_log("Positions: " . print_r($positions, true));
    
    // Make sure we have valid arrays
    if (!is_array($player_names) || !is_array($positions) || count($player_names) !== count($positions)) {
        echo json_encode(["status" => "error", "message" => "Invalid player data format. Names: " . gettype($player_names) . ", Positions: " . gettype($positions)]);
        exit;
    }
    
    // Convert arrays to position-based player names
    $center = "";
    $power_forward = "";
    $small_forward = "";
    $point_guard = "";
    $shooting_guard = "";
    
    for ($i = 0; $i < count($positions); $i++) {
        $position = $positions[$i];
        $player_name = $player_names[$i];
        
        switch ($position) {
            case 'C':
                $center = $player_name;
                break;
            case 'PF':
                $power_forward = $player_name;
                break;
            case 'SF':
                $small_forward = $player_name;
                break;
            case 'PG':
                $point_guard = $player_name;
                break;
            case 'SG':
                $shooting_guard = $player_name;
                break;
        }
    }
    
    // Check if the team already exists
    $checkCommand = "SELECT id FROM hoopsdynastyteams WHERE team_name = ? AND creator = ?";
    $checkStmt = $dbh->prepare($checkCommand);
    $checkStmt->execute([$team_name, $creator]);
    
    if ($checkStmt->fetch()) {
        // Update existing team
        $command = "UPDATE hoopsdynastyteams SET center = ?, power_forward = ?, small_forward = ?, point_guard = ?, shooting_guard = ? WHERE team_name = ? AND creator = ?";
        $stmt = $dbh->prepare($command);
        $success = $stmt->execute([$center, $power_forward, $small_forward, $point_guard, $shooting_guard, $team_name, $creator]);
    
        if ($success) {
            echo json_encode(["status" => "success", "message" => "Team updated successfully."]);
        } else {
            echo json_encode(["status" => "error", "message" => "Failed to update team."]);
        }
    } else {
        // Create new team
        $command = "INSERT INTO hoopsdynastyteams (creator, team_name, access, center, power_forward, small_forward, point_guard, shooting_guard) VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = $dbh->prepare($command);
        $success = $stmt->execute([$creator, $team_name, 0, $center, $power_forward, $small_forward, $point_guard, $shooting_guard]);
    
        if ($success) {
            echo json_encode(["status" => "success", "message" => "Team saved successfully."]);
        } else {
            echo json_encode(["status" => "error", "message" => "Failed to save team."]);
        }
    }
} catch (Exception $e) {
    echo json_encode(["status" => "error", "message" => "An error occurred: " . $e->getMessage()]);
}
?>
