<?php
/**
    Author: Daryl John
    Student Number: 400583895
    Date: 20-03-2025
    Description: This file contains the PHP functions needed to
    send the requests to the database and create the player
    action probabilities based off of their stats and position
 */
include "../connect.php";

/**
 * Get player details with statistical data
 * @param string $playerName - Name of the player
 * @return array - Player data with stats
 */
function getPlayerDetails($playerName) {
    global $dbh;
    
    try {
        $sql = "SELECT player_ID, player_name, player_position, player_team, 
                three_point_percentage, two_point_percentage, free_throw_percentage, 
                blocks_per_game, steals_per_game, personal_fouls_per_game 
                FROM hoopsdynastyplayers 
                WHERE player_name LIKE :player_name 
                LIMIT 1";
        
        $stmt = $dbh->prepare($sql);
        $searchName = "%$playerName%";
        $stmt->bindParam(':player_name', $searchName, PDO::PARAM_STR);
        $stmt->execute();
        
        $player = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['name'])) {
            $playerName = $_POST['name'];
        }
        
        if ($player) {
            // Return player with statistics
            return [
                'id' => $player['player_ID'],
                'name' => $player['player_name'],
                'position' => $player['player_position'],
                'team' => $player['player_team'],
                'three_point_percentage' => floatval($player['three_point_percentage']),
                'two_point_percentage' => floatval($player['two_point_percentage']),
                'free_throw_percentage' => floatval($player['free_throw_percentage']),
                'blocks_per_game' => floatval($player['blocks_per_game']),
                'steals_per_game' => floatval($player['steals_per_game']),
                'fouls_per_game' => floatval($player['personal_fouls_per_game'])
            ];
        }
        
        // If player not found, generate placeholder data
        return generateDefaultPlayerData($playerName);
        
    } catch (PDOException $e) {
        error_log('Database error: ' . $e->getMessage());
        return generateDefaultPlayerData($playerName);
    }
}

/**
 * Generate default player data when not found in database
 * @param string $playerName - Player name
 * @return array - Default player data
 */
function generateDefaultPlayerData($playerName) {
    // Generate position based on name if possible
    $position = determinePositionFromName($playerName);
    
    // Generate stats based on position
    $stats = generatePositionBasedStats($position);
    
    return array_merge([
        'id' => 'default_' . md5($playerName),
        'name' => $playerName,
        'position' => $position,
        'team' => 'Unknown'
    ], $stats);
}

/**
 * Determine player position from name (simple heuristic)
 * @param string $name - Player name
 * @return string - Position code
 */
function determinePositionFromName($name) {
    $name = strtolower($name);
    
    // Check for position indicators in name
    if (strpos($name, 'center') !== false || strpos($name, ' c ') !== false || $name === 'c') {
        return 'C';
    } elseif (strpos($name, 'power') !== false || strpos($name, 'forward') !== false || strpos($name, ' pf ') !== false) {
        return 'PF';
    } elseif (strpos($name, 'small') !== false || strpos($name, 'forward') !== false || strpos($name, ' sf ') !== false) {
        return 'SF';
    } elseif (strpos($name, 'point') !== false || strpos($name, 'guard') !== false || strpos($name, ' pg ') !== false) {
        return 'PG';
    } elseif (strpos($name, 'shooting') !== false || strpos($name, 'guard') !== false || strpos($name, ' sg ') !== false) {
        return 'SG';
    }
    
    // Default to a random position
    $positions = ['PG', 'SG', 'SF', 'PF', 'C'];
    return $positions[array_rand($positions)];
}

/**
 * Generate stats based on player position
 * @param string $position - Player position
 * @return array - Statistical data
 */
function generatePositionBasedStats($position) {
    switch ($position) {
        case 'C':
            return [
                'three_point_percentage' => rand(25, 35),
                'two_point_percentage' => rand(50, 65),
                'free_throw_percentage' => rand(60, 75),
                'blocks_per_game' => (rand(20, 35) / 10),
                'steals_per_game' => (rand(5, 15) / 10),
                'fouls_per_game' => (rand(25, 40) / 10)
            ];
        case 'PF':
            return [
                'three_point_percentage' => rand(30, 40),
                'two_point_percentage' => rand(48, 60),
                'free_throw_percentage' => rand(65, 80),
                'blocks_per_game' => (rand(15, 25) / 10),
                'steals_per_game' => (rand(8, 18) / 10),
                'fouls_per_game' => (rand(22, 35) / 10)
            ];
        case 'SF':
            return [
                'three_point_percentage' => rand(35, 45),
                'two_point_percentage' => rand(45, 55),
                'free_throw_percentage' => rand(70, 85),
                'blocks_per_game' => (rand(8, 18) / 10),
                'steals_per_game' => (rand(12, 22) / 10),
                'fouls_per_game' => (rand(18, 30) / 10)
            ];
        case 'SG':
            return [
                'three_point_percentage' => rand(38, 48),
                'two_point_percentage' => rand(42, 52),
                'free_throw_percentage' => rand(75, 90),
                'blocks_per_game' => (rand(5, 15) / 10),
                'steals_per_game' => (rand(15, 25) / 10),
                'fouls_per_game' => (rand(15, 28) / 10)
            ];
        case 'PG':
            return [
                'three_point_percentage' => rand(36, 46),
                'two_point_percentage' => rand(40, 50),
                'free_throw_percentage' => rand(80, 95),
                'blocks_per_game' => (rand(3, 10) / 10),
                'steals_per_game' => (rand(18, 30) / 10),
                'fouls_per_game' => (rand(15, 25) / 10)
            ];
        default:
            return [
                'three_point_percentage' => rand(30, 40),
                'two_point_percentage' => rand(45, 55),
                'free_throw_percentage' => rand(70, 85),
                'blocks_per_game' => (rand(5, 20) / 10),
                'steals_per_game' => (rand(8, 18) / 10),
                'fouls_per_game' => (rand(18, 30) / 10)
            ];
    }
}

// If this file is accessed directly, return a 403 Forbidden
if (basename($_SERVER['PHP_SELF']) == basename(__FILE__)) {
    header('HTTP/1.0 403 Forbidden');
    exit('Direct access to this file is forbidden.');
}
?>