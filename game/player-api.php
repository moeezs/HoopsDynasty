<?php

include "../connect.php";
include "connector.php";

session_start();
header('Content-Type: application/json');

/**
 * Get player details by ID
 * @param int $playerId - Player ID to retrieve
 * @param string $source - Database table to query ('getTeam' or 'getOpponent')
 * @return array - Player data
 */
function getPlayerById($playerId, $source = 'getTeam') {
    global $dbh;
    
    try {
        $validSources = ['getTeam', 'getOpponent'];
        if (!in_array($source, $validSources)) {
            $source = 'getTeam';
        }
        
        $sql = "SELECT player_ID, player_name, player_position, player_team, 
                three_point_percentage, two_point_percentage, free_throw_percentage, 
                blocks_per_game, steals_per_game, personal_fouls_per_game 
                FROM $source WHERE player_ID = :player_id";
        
        $stmt = $dbh->prepare($sql);
        $stmt->bindParam(':player_id', $playerId, PDO::PARAM_INT);
        $stmt->execute();
        
        $player = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($player) {
            $player['probabilities'] = calculateActionProbabilities($player);
        }
        
        return $player;
    } catch (PDOException $e) {
        handleDbError($e);
    }
}

/**
 * Get player details by name
 * @param string $playerName - Player name to search for
 * @return array - Player data
 */
function getPlayerByName($playerName) {
    global $dbh;
    
    try {
        $sql = "SELECT player_ID as id, player_name as name, player_position as position, player_team as team, 
                three_point_percentage, two_point_percentage, free_throw_percentage, 
                blocks_per_game, steals_per_game, personal_fouls_per_game 
                FROM hoopsdynastyplayers WHERE player_name LIKE :player_name LIMIT 1";
        
        $stmt = $dbh->prepare($sql);
        $searchName = "%$playerName%";
        $stmt->bindParam(':player_name', $searchName, PDO::PARAM_STR);
        $stmt->execute();
        
        $player = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($player) {
            $player['three_point_percentage'] = floatval($player['three_point_percentage']);
            $player['two_point_percentage'] = floatval($player['two_point_percentage']);
            $player['free_throw_percentage'] = floatval($player['free_throw_percentage']);
            $player['blocks_per_game'] = floatval($player['blocks_per_game']);
            $player['steals_per_game'] = floatval($player['steals_per_game']);
            $player['personal_fouls_per_game'] = floatval($player['personal_fouls_per_game']);

            $player['player_name'] = $player['name'];
            $player['player_ID'] = $player['id'];
            $player['player_position'] = $player['position'];
            $player['player_team'] = $player['team'];
            
            return $player;
        }

        return [
            'id' => 'default_' . md5($playerName),
            'name' => $playerName,
            'position' => determinePositionFromName($playerName),
            'team' => 'Unknown',
            'player_name' => $playerName,
            'player_ID' => 'default_' . md5($playerName),
            'player_position' => determinePositionFromName($playerName),
            'player_team' => 'Unknown',
            'three_point_percentage' => rand(25, 45),
            'two_point_percentage' => rand(35, 55),
            'free_throw_percentage' => rand(65, 85),
            'blocks_per_game' => rand(0, 30) / 10,
            'steals_per_game' => rand(0, 25) / 10,
            'personal_fouls_per_game' => rand(15, 40) / 10
        ];
    } catch (PDOException $e) {
        handleDbError($e);
        return null;
    }
}

/**
 * Determine player position from name or position identifier
 */
function determinePositionFromName($name) {
    $name = strtolower($name);
    if (strpos($name, 'center') !== false || strpos($name, ' c ') !== false || $name === 'c') return 'C';
    if (strpos($name, 'power forward') !== false || strpos($name, ' pf ') !== false || $name === 'pf') return 'PF';
    if (strpos($name, 'small forward') !== false || strpos($name, ' sf ') !== false || $name === 'sf') return 'SF';
    if (strpos($name, 'point guard') !== false || strpos($name, ' pg ') !== false || $name === 'pg') return 'PG';
    if (strpos($name, 'shooting guard') !== false || strpos($name, ' sg ') !== false || $name === 'sg') return 'SG';
    return ['PG', 'SG', 'SF', 'PF', 'C'][array_rand(['PG', 'SG', 'SF', 'PF', 'C'])];
}

// Only handle POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    sendJsonResponse([
        'status' => 'error',
        'message' => 'Only POST requests allowed.'
    ], 405);
}

$input = file_get_contents('php://input');
$data = json_decode($input, true);

if (!$data || !isset($data['playerNames']) || !is_array($data['playerNames'])) {
    sendJsonResponse([
        'status' => 'error',
        'message' => 'Invalid request. Expected JSON with playerNames array.'
    ], 400);
}

$players = [];
foreach ($data['playerNames'] as $playerName) {
    $player = getPlayerByName($playerName);
    if ($player) {
        $players[] = $player;
    }
}

sendJsonResponse([
    'status' => 'success',
    'players' => $players
]);

/**
 * Send JSON response
 * @param array $data - Data to send
 * @param int $status - HTTP status code
 */
function sendJsonResponse($data, $status = 200) {
    http_response_code($status);
    echo json_encode($data);
    exit;
}

/**
 * Handle database errors
 * @param PDOException $e - Exception object
 */
function handleDbError($e) {
    sendJsonResponse([
        'status' => 'error',
        'message' => 'Database error: ' . $e->getMessage()
    ], 500);
    exit;
}

/**
 * Validate required parameters
 * @param array $required - List of required parameters
 * @return array - Validation result
 */
function validateParams($required) {
    $missing = [];
    foreach ($required as $param) {
        if (!isset($_POST[$param])) {
            $missing[] = $param;
        }
    }
    return [
        'valid' => empty($missing),
        'missing' => $missing
    ];
}
?>
