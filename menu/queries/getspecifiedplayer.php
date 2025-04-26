<?php
/**
    Author: Grady Rueffer
    Student Number: 400579449
    Date: 20-03-2025
    Description: This file contains functionality to retrieve player data from the
    database given a player name, returning the data as JSON
    Used in: menu.js
 */
header('Content-Type: application/json');
include "../../connect.php";

// Filter and recieve player name
$player_name = filter_input(INPUT_POST, "name", FILTER_SANITIZE_SPECIAL_CHARS);

// Query database for player information
$command = "SELECT player_name, player_team, three_point_percentage, two_point_percentage, free_throw_percentage, blocks_per_game, steals_per_game, personal_fouls_per_game, TO_BASE64(photo) AS photo_base64 FROM hoopsdynastyplayers WHERE LOWER(player_name) = LOWER(?) LIMIT 1";
$stmt = $dbh->prepare($command);
$success = $stmt->execute([$player_name]);

if ($success) {
    // Fetch player data
    $player = $stmt->fetch();

    // Encode and return player data, or return a status of NONE if parameters were invalid
    if ($player) {
        echo json_encode($player);
    } else {
        echo json_encode(["status" => "NONE"]);
    }
} else {
    // Parameters were invalid, return NONE
    echo json_encode(["status" => "NONE"]);
}
