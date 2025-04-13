<?php
include "../connect.php";

/**
 * Gets team players with their stats
 * @param string $teamId
 * @return array - array of player data
 */
function getTeam($teamId = null) {
    global $dbh;
    
    try {
        $sql = "SELECT player_ID, player_name, player_position, player_team, 
                three_point_percentage, two_point_percentage, free_throw_percentage, 
                blocks_per_game, steals_per_game, personal_fouls_per_game 
                FROM getTeam";
        
        // Add team filter if provided
        if ($teamId) {
            $sql .= " WHERE player_team = :team_id";
            $stmt = $dbh->prepare($sql);
            $stmt->bindParam(':team_id', $teamId, PDO::PARAM_STR);
        } else {
            $stmt = $dbh->prepare($sql);
        }
        
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        // Return error message as JSON
        header('Content-Type: application/json');
        echo json_encode(['error' => 'Database error: ' . $e->getMessage()]);
        exit;
    }
}

// Process HTTP request
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    if (isset($_GET['team_id'])) {
        $teamId = $_GET['team_id'];
    } else {
        $teamId = null;
    }
    $players = getTeam($teamId);
    
    // Returns player data as JSON
    header('Content-Type: application/json');
    echo json_encode($players);
}
?>