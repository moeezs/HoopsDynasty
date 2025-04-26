<?php

/**
    Author: Grady Rueffer, Abdul Moeez Shaikh
    Student Number: 400579449, 400573061
    Date: 23-03-2025
    Description: This file contains functionality to retrieve player data from the database based on user input.
    Links to: teambuilder/teambuilder.php (Redirects user back to the team builder page)
 */

// Connect to database
include "../../connect.php";

// Gather user input and define placeholders for outcome
$player_name = filter_input(INPUT_POST, "name", FILTER_SANITIZE_SPECIAL_CHARS);
$player_team = filter_input(INPUT_POST, "team", FILTER_SANITIZE_SPECIAL_CHARS);
$stmt;
$success;

// Query depending on given data
if (!empty($player_name)) {
    if (!empty($player_team)) {
        $command = "SELECT player_id, player_name, player_team, three_point_percentage, two_point_percentage, free_throw_percentage, blocks_per_game, steals_per_game, personal_fouls_per_game, photo FROM hoopsdynastyplayers WHERE LOWER(player_name) LIKE LOWER(?) AND LOWER(player_team) LIKE LOWER(?) AND LOWER(player_team) != 'team 45' ORDER BY player_name LIMIT 50";
        $stmt = $dbh->prepare($command);
        $success = $stmt->execute(["%$player_name%", "%$player_team%"]);
    } else {
        $command = "SELECT player_id, player_name, player_team, three_point_percentage, two_point_percentage, free_throw_percentage, blocks_per_game, steals_per_game, personal_fouls_per_game, photo FROM hoopsdynastyplayers WHERE LOWER(player_name) LIKE LOWER(?) AND LOWER(player_team) != 'team 45' ORDER BY player_name LIMIT 50";
        $stmt = $dbh->prepare($command);
        $success = $stmt->execute(["%$player_name%"]);
    }
} else if (!empty($player_team)) {
    $command = "SELECT player_id, player_name, player_team, three_point_percentage, two_point_percentage, free_throw_percentage, blocks_per_game, steals_per_game, personal_fouls_per_game, photo FROM hoopsdynastyplayers WHERE LOWER(player_team) LIKE LOWER(?) AND LOWER(player_team) != 'team 45' ORDER BY player_name LIMIT 50";
    $stmt = $dbh->prepare($command);
    $success = $stmt->execute(["%$player_team%"]);
} else {
    // Removed limit or increased it significantly - show all players by default
    $command = "SELECT player_id, player_name, player_team, three_point_percentage, two_point_percentage, free_throw_percentage, blocks_per_game, steals_per_game, personal_fouls_per_game, photo FROM hoopsdynastyplayers WHERE LOWER(player_team) != 'team 45' ORDER BY player_name LIMIT 200";
    $stmt = $dbh->prepare($command);
    $success = $stmt->execute();
}

// When players are found
if ($success) {
    $return = [];

    while ($player = $stmt->fetch()) {
        // Convert longblob to base64 encoded string for image display
        $photoBase64 = null;
        if ($player["photo"] && strlen($player["photo"]) > 0) {
            $photoBase64 = 'data:image/jpeg;base64,' . base64_encode($player["photo"]);
        }

        // Define the associative array output
        $return[] = [
            "player_id" => $player["player_id"],
            "player_name" => $player["player_name"],
            "player_team" => $player["player_team"],
            "three_point_percentage" => $player["three_point_percentage"],
            "two_point_percentage" => $player["two_point_percentage"],
            "free_throw_percentage" => $player["free_throw_percentage"],
            "blocks_per_game" => $player["blocks_per_game"],
            "steals_per_game" => $player["steals_per_game"],
            "personal_fouls_per_game" => $player["personal_fouls_per_game"],
            "photo" => $photoBase64
        ];
    }

    // Return JSON encoded data
    echo json_encode($return);
} else {
    echo json_encode(["status" => "NONE"]);
}
