<?php
include "../../connect.php";

$player_name = filter_input(INPUT_POST, "name", FILTER_SANITIZE_SPECIAL_CHARS);
$player_team = filter_input(INPUT_POST, "team", FILTER_SANITIZE_SPECIAL_CHARS);

$command = "SELECT player_name, player_team, three_point_percentage, two_point_percentage, free_throw_percentage, blocks_per_game, steals_per_game, personal_fouls_per_game, TO_BASE64(photo) AS photo_base64 FROM hoopsdynastyplayers WHERE player_name = ? AND player_team = ? LIMIT 1";
$stmt = $dbh->prepare($command);
$success = $stmt->execute([$player_name, $player_team]);

if ($success) {
    $player = $stmt->fetch();

    if ($player) {
        echo json_encode($player);
    } else {
        echo json_encode(["status" => "NONE"]);
    }
} else {
    echo json_encode(["status" => "NONE"]);
}
