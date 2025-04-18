<?php
include "../../connect.php";

$player_name = filter_input(INPUT_POST, "name", FILTER_SANITIZE_SPECIAL_CHARS);
$player_team = filter_input(INPUT_POST, "team", FILTER_SANITIZE_SPECIAL_CHARS);
$stmt;
$success;

if (!empty($player_name)) {
    if (!empty($player_team)) {
        $command = "SELECT player_id, player_name, player_team, three_point_percentage, two_point_percentage, free_throw_percentage, blocks_per_game, steals_per_game, personal_fouls_per_game, photo FROM hoopsdynastyplayers WHERE LOWER(player_name) LIKE LOWER(?) AND LOWER(player_team) LIKE LOWER(?) AND LOWER(player_team) != 'team 45' ORDER BY player_id LIMIT 8";
        $stmt = $dbh->prepare($command);
        $success = $stmt->execute(["%$player_name%", "%$player_team%"]);
    } else {
        $command = "SELECT player_id, player_name, player_team, three_point_percentage, two_point_percentage, free_throw_percentage, blocks_per_game, steals_per_game, personal_fouls_per_game, photo FROM hoopsdynastyplayers WHERE LOWER(player_name) LIKE LOWER(?) AND LOWER(player_team) != 'team 45' ORDER BY player_id LIMIT 8";
        $stmt = $dbh->prepare($command);
        $success = $stmt->execute(["%$player_name%"]);
    }
} else if (!empty($player_team)) {
    $command = "SELECT player_id, player_name, player_team, three_point_percentage, two_point_percentage, free_throw_percentage, blocks_per_game, steals_per_game, personal_fouls_per_game, photo FROM hoopsdynastyplayers WHERE LOWER(player_team) LIKE LOWER(?) AND LOWER(player_team) != 'team 45' ORDER BY player_id LIMIT 8";
    $stmt = $dbh-> prepare($command);
    $success = $stmt->execute(["%$player_team%"]);
} else {
    $command = "SELECT player_id, player_name, player_team, three_point_percentage, two_point_percentage, free_throw_percentage, blocks_per_game, steals_per_game, personal_fouls_per_game, photo FROM hoopsdynastyplayers WHERE LOWER(player_team) != 'team 45' ORDER BY player_id LIMIT 8";
    $stmt = $dbh->prepare($command);
    $success = $stmt->execute();
}

if ($success) {
    $return = [];

    while ($player = $stmt->fetch()) {
        // Convert blob to base64 encoded string for image display
        $photoBase64 = null;
        if ($player["photo"] && strlen($player["photo"]) > 0) {
            $photoBase64 = 'data:image/jpeg;base64,' . base64_encode($player["photo"]);
        }
        
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

    echo json_encode($return);
} else {
    echo json_encode(["status" => "NONE"]);
}
