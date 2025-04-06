<?php
/**
 * Helper functions for team operations
 */

/**
 * Get team details by ID
 * 
 * @param PDO $pdo Database connection
 * @param int $teamId Team ID
 * @return array|bool Team data array or false if not found
 */
function getTeamById($pdo, $teamId) {
    $stmt = $pdo->prepare("SELECT * FROM teams WHERE team_id = :team_id");
    $stmt->execute(['team_id' => $teamId]);
    return $stmt->fetch();
}

/**
 * Get players for a team
 * 
 * @param PDO $pdo Database connection
 * @param int $teamId Team ID
 * @return array Array of players or empty array if none found
 */
function getTeamPlayers($pdo, $teamId) {
    $stmt = $pdo->prepare("
        SELECT p.player_id as player_ID, 
               p.player_name, 
               p.player_team, 
               tp.position as player_position, 
               p.three_point_percentage, 
               p.two_point_percentage, 
               p.free_throw_percentage,
               p.blocks_per_game, 
               p.steals_per_game, 
               p.personal_fouls_per_game,
               p.photo
        FROM team_players tp
        JOIN players p ON tp.player_id = p.player_id
        WHERE tp.team_id = :team_id
    ");
    $stmt->execute(['team_id' => $teamId]);
    return $stmt->fetchAll();
}

/**
 * Calculate team rating based on player stats
 * 
 * @param array $players Array of player objects
 * @return int Team rating from 0-100
 */
function calculateTeamRating($players) {
    if (empty($players)) {
        return 0;
    }
    
    $totalRating = 0;
    foreach ($players as $player) {
        $playerRating = 
            $player['three_point_percentage'] * 30 + 
            $player['two_point_percentage'] * 30 + 
            $player['free_throw_percentage'] * 15 + 
            $player['blocks_per_game'] * 10 +
            $player['steals_per_game'] * 10;
        
        $totalRating += $playerRating;
    }
    
    return min(100, round($totalRating / count($players)));
}
?>
