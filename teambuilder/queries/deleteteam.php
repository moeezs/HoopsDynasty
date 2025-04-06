<?php
include "../../connect.php";
session_start();

header("Content-Type: application/json");

$creator = isset($_SESSION["userid"]) ? $_SESSION["userid"] : null;
$team_name = filter_input(INPUT_POST, "team_name", FILTER_SANITIZE_SPECIAL_CHARS);

// Validate user is logged in
if (!$creator) {
    echo json_encode(["status" => "error", "message" => "You must be logged in to delete a team."]);
    exit;
}

// Validate input
if (!$team_name) {
    echo json_encode(["status" => "error", "message" => "Invalid team name."]);
    exit;
}

try {
    // Delete the team
    $command = "DELETE FROM hoopsdynastyteams WHERE team_name = ? AND creator = ?";
    $stmt = $dbh->prepare($command);
    $success = $stmt->execute([$team_name, $creator]);

    if ($success) {
        echo json_encode(["status" => "success", "message" => "Team deleted successfully."]);
    } else {
        echo json_encode(["status" => "error", "message" => "Failed to delete team."]);
    }
} catch (Exception $e) {
    echo json_encode(["status" => "error", "message" => "An error occurred: " . $e->getMessage()]);
}
?>
