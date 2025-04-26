<?php
/**
    Author: Daryl John, Gagan Bhattarai
    Student Number: 400583895, 400585207
    Date: 20-03-2025
    Description: This file contains the PHP code needed to
    turn all of the needed player data into an object at
    the start of the game
 */

require_once '../connect.php';
header('Content-Type: application/json');

$userTeamId = isset($_GET['userTeamId']) ? intval($_GET['userTeamId']) : 0;
$opponentTeamId = isset($_GET['opponentTeamId']) ? intval($_GET['opponentTeamId']) : 0;

if ($userTeamId <= 0 || $opponentTeamId <= 0) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Invalid team IDs provided'
    ]);
    exit;
}

try {
    // Get user team data
    $stmt = $dbh->prepare("SELECT * FROM hoopsdynastyteams WHERE id = :team_id");
    $stmt->execute(['team_id' => $userTeamId]);
    $userTeam = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$userTeam) {
        echo json_encode([
            'status' => 'error',
            'message' => 'User team not found'
        ]);
        exit;
    }
    
    // Get opponent team data
    $stmt = $dbh->prepare("SELECT * FROM hoopsdynastyteams WHERE id = :team_id");
    $stmt->execute(['team_id' => $opponentTeamId]);
    $opponentTeam = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$opponentTeam) {
        echo json_encode([
            'status' => 'error',
            'message' => 'Opponent team not found'
        ]);
        exit;
    }
    
    // Create player arrays from team data
    $userTeamPlayers = createTeamPlayersArray($userTeam);
    $opponentTeamPlayers = createTeamPlayersArray($opponentTeam);
    
    // Return game data
    echo json_encode([
        'teamName' => $userTeam['team_name'],
        'opponentName' => $opponentTeam['team_name'],
        'team' => $userTeamPlayers,
        'opponent' => $opponentTeamPlayers
    ]);
    
} catch (PDOException $e) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Database error: ' . $e->getMessage()
    ]);
}

/**
 * Create an array of player objects from team data
 * 
 * @param array $team Team data from the database
 * @return array Array of player objects with stats
 */
function createTeamPlayersArray($team) {
    $players = [];
    $positions = [
        'point_guard' => 'PG',
        'shooting_guard' => 'SG',
        'small_forward' => 'SF',
        'power_forward' => 'PF',
        'center' => 'C'
    ];
    
    foreach ($positions as $dbPosition => $shortPosition) {
        if (!empty($team[$dbPosition])) {
            $playerName = $team[$dbPosition];
            $nameHash = crc32($playerName);
            
            // Generate consistent stats based on player name
            $threePointPct = 20 + ($nameHash % 40); // 20-60%
            $twoPointPct = 35 + ($nameHash % 40); // 35-75%
            $freeThrowPct = 50 + ($nameHash % 40); // 50-90%
            $blocks = 0.1 + ($nameHash % 30) / 10; // 0.1-3.1
            $steals = 0.2 + ($nameHash % 25) / 10; // 0.2-2.7
            $fouls = 1.0 + ($nameHash % 30) / 10; // 1.0-4.0
            
            // Create player object with photo URL for retrieving image from get-player-photo.php
            $players[] = [
                'player_ID' => $nameHash,
                'player_name' => $playerName,
                'player_team' => $team['team_name'],
                'player_position' => $shortPosition,
                'three_point_percentage' => $threePointPct,
                'two_point_percentage' => $twoPointPct,
                'free_throw_percentage' => $freeThrowPct,
                'blocks_per_game' => $blocks,
                'steals_per_game' => $steals,
                'personal_fouls_per_game' => $fouls,
                'photo' => "get-player-photo.php?name=" . urlencode($playerName)
            ];
        }
    }
    
    return $players;
}
?>