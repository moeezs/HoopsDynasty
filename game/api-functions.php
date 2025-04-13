<?php

/**
 * Validates required parameters
 * @param array $params - Array of parameter names to validate
 * @param string $source - Source of parameters ('GET' or 'POST')
 * @return array - Array with 'valid' boolean and 'missing' array of missing params
 */
function validateParams($params, $source = 'GET') {
    $missing = [];
    $source = $source === 'POST' ? $_POST : $_GET;
    
    foreach ($params as $param) {
        if (!isset($source[$param]) || empty($source[$param])) {
            $missing[] = $param;
        }
    }
    
    return [
        'valid' => empty($missing),
        'missing' => $missing
    ];
}

/**
 * Sends a JSON response
 * @param mixed $data - Data to send
 * @param int $status - HTTP status code
 */
function sendJsonResponse($data, $status = 200) {
    http_response_code($status);
    header('Content-Type: application/json');
    echo json_encode($data);
    exit;
}

/**
 * Handles database errors
 * @param Exception $e - Exception object
 */
function handleDbError($e) {
    $errorResponse = [
        'status' => 'error',
        'message' => 'Database error occurred',
        'dev_message' => $e->getMessage()
    ];
    
    sendJsonResponse($errorResponse, 500);
}

/**
 * Calculates player action probabilities based on stats
 * @param array $player - Player data
 * @return array - Action probabilities
 */
function calculateActionProbabilities($player) {
    $position = strtolower($player['player_position']);
    $probabilities = [];
    
    // Offensive probabilities
    $probabilities['three_pointer'] = $player['three_point_percentage'];
    $probabilities['layup'] = $player['two_point_percentage'];
    $probabilities['pass'] = min(95, $player['two_point_percentage'] + 10);
    
    // Adjusts dunk probability based on position
    if ($position === 'center' || $position === 'power forward') {
        $probabilities['dunk'] = $player['two_point_percentage'] - 5;
    } else {
        $probabilities['dunk'] = $player['two_point_percentage'] - 15;
    }
    
    // Defensive probabilities
    $probabilities['block'] = $player['blocks_per_game'] * 10;
    $probabilities['steal'] = $player['steals_per_game'] * 10;
    $probabilities['tackle'] = $player['steals_per_game'] * 8;
    $probabilities['pressure'] = 50 + ($player['steals_per_game'] * 5);
    
    // Keeps the values between 5 and 95
    foreach ($probabilities as $key => $value) {
        $probabilities[$key] = max(5, min(95, $value));
    }
    
    return $probabilities;
}

include "../connect.php";
session_start();

// Ensure user is logged in
if (!isset($_SESSION["userid"])) {
    header("Location: ../accounts/signin.php");
    exit;
}

// Get team IDs from URL parameters
$userTeamId = filter_input(INPUT_GET, "userTeamId", FILTER_SANITIZE_SPECIAL_CHARS);
$opponentTeamId = filter_input(INPUT_GET, "opponentTeamId", FILTER_SANITIZE_SPECIAL_CHARS);

if (!$userTeamId || !$opponentTeamId) {
    echo json_encode([
        "status" => "error",
        "message" => "Team IDs not provided"
    ]);
    exit;
}

try {
    // Fetch user team data
    $userTeam = getTeamData($dbh, $userTeamId);
    
    // Fetch opponent team data
    $opponentTeam = getTeamData($dbh, $opponentTeamId);
    
    // Return team data as JSON
    echo json_encode([
        "status" => "success",
        "team" => $userTeam["players"],
        "teamName" => $userTeam["name"],
        "opponent" => $opponentTeam["players"],
        "opponentName" => $opponentTeam["name"]
    ]);
    
} catch (Exception $e) {
    echo json_encode([
        "status" => "error",
        "message" => "Database error: " . $e->getMessage()
    ]);
}

/**
 * Get team data and its players
 * @param PDO $dbh Database connection handle
 * @param string $teamId Team ID
 * @return array Team data with players
 */
function getTeamData($dbh, $teamId) {
    // Fetch team details
    $teamQuery = "SELECT teamname, rating FROM teams WHERE id = ?";
    $teamStmt = $dbh->prepare($teamQuery);
    $teamStmt->execute([$teamId]);
    $team = $teamStmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$team) {
        throw new Exception("Team not found");
    }
    
    // Fetch team players from positions
    $playersQuery = "SELECT * FROM teamplayers WHERE team_id = ?";
    $playersStmt = $dbh->prepare($playersQuery);
    $playersStmt->execute([$teamId]);
    $teamPlayers = [];
    
    while ($player = $playersStmt->fetch(PDO::FETCH_ASSOC)) {
        // Get player details from players table
        $playerDetailQuery = "SELECT * FROM hoopsdynastyplayers WHERE player_name = ?";
        $playerDetailStmt = $dbh->prepare($playerDetailQuery);
        $playerDetailStmt->execute([$player["player_name"]]);
        $playerDetail = $playerDetailStmt->fetch(PDO::FETCH_ASSOC);
        
        if ($playerDetail) {
            $teamPlayers[] = $playerDetail;
        } else {
            // If player not found in database, use default stats
            $teamPlayers[] = [
                "player_id" => uniqid(),
                "player_name" => $player["player_name"],
                "player_team" => $team["teamname"],
                "player_position" => determinePosition($player["position"]),
                "three_point_percentage" => 30 + rand(0, 20),
                "two_point_percentage" => 40 + rand(0, 25),
                "free_throw_percentage" => 60 + rand(0, 25),
                "blocks_per_game" => rand(5, 25) / 10,
                "steals_per_game" => rand(5, 20) / 10,
                "personal_fouls_per_game" => rand(10, 40) / 10
            ];
        }
    }
    
    // If no players found or fewer than 5, add generic players
    if (count($teamPlayers) < 5) {
        $positions = ["PG", "SG", "SF", "PF", "C"];
        $existingPositions = [];
        
        foreach ($teamPlayers as $player) {
            $existingPositions[] = $player["player_position"];
        }
        
        foreach ($positions as $position) {
            if (!in_array(determinePosition($position), $existingPositions)) {
                $teamPlayers[] = [
                    "player_id" => uniqid(),
                    "player_name" => "Player " . $position,
                    "player_team" => $team["teamname"],
                    "player_position" => determinePosition($position),
                    "three_point_percentage" => 30 + rand(0, 20),
                    "two_point_percentage" => 40 + rand(0, 25),
                    "free_throw_percentage" => 60 + rand(0, 25),
                    "blocks_per_game" => rand(5, 25) / 10,
                    "steals_per_game" => rand(5, 20) / 10,
                    "personal_fouls_per_game" => rand(10, 40) / 10
                ];
            }
        }
    }
    
    return [
        "name" => $team["teamname"],
        "rating" => $team["rating"],
        "players" => $teamPlayers
    ];
}

/**
 * Convert position abbreviation to full position name
 * @param string $position Position abbreviation
 * @return string Full position name
 */
function determinePosition($position) {
    switch ($position) {
        case 'PG':
            return 'point guard';
        case 'SG':
            return 'shooting guard';
        case 'SF':
            return 'small forward';
        case 'PF':
            return 'power forward';
        case 'C':
            return 'center';
        default:
            return strtolower($position);
    }
}
?>