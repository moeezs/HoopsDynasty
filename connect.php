<!--
    Author: Grady Rueffer
    Student Number: 400579449
    Date: 15-03-2025
    Description: This file establishes the database connection on the 1xd3 server
    Used in: game/(connector.php, get-player-photo.php, init-game.php, player-api.php),
    accounts/(createaccount.php, signin.php), menu/(index.php, getspecifiedplayer.php),
    teambuilder/(deleteteam.php, getplayers.php, getteamplayers.php, getteams.php,
    saveteams.php, index.php), index.php
--> 
<?php
try {
    $dbh = new PDO(
        "mysql:host=localhost;dbname=ruefferg_db",  
        "ruefferg_local",  
        "z/pyb,[K" 
    );
} catch (Exception $e) {
    die("ERROR: Couldn't connect. {$e->getMessage()}");
}
?>
