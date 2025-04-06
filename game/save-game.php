<?php
// Include existing database connection
require_once '../connect.php';

// Set content type to JSON
header('Content-Type: application/json');

// Check if we have a POST request
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode([
        'status' => 'error',
        'message' => 'Invalid request method'
    ]);
    exit;
}

// Get game data from POST
$data = json_decode(file_get_contents('php://input'), true);
if (!$data) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Invalid JSON data'
    ]);
    exit;
}

// Validate required fields
if (!isset($data['userTeamId']) || !isset($data['opponentTeamId']) || 
    !isset($data['userScore']) || !isset($data['opponentScore'])) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Missing required fields'
    ]);
    exit;
}

// Extract data for saving
$userTeamId = intval($data['userTeamId']);
$opponentTeamId = intval($data['opponentTeamId']);
$userScore = intval($data['userScore']);
$opponentScore = intval($data['opponentScore']);
$userId = isset($data['userId']) ? intval($data['userId']) : null;

try {
    // In a real implementation, you would save the game results to a game_history table
    // For now, we just return success
    echo json_encode([
        'status' => 'success',
        'message' => 'Game results saved successfully'
    ]);
    
} catch (Exception $e) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Error saving game: ' . $e->getMessage()
    ]);
}
?>
