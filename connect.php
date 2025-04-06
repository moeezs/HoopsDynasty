<?php
// try {
//     $dbh = new PDO(
//         "mysql:host=localhost;dbname=ruefferg_db",
//         "root",
//         ""
//     );
// } catch (Exception $e) {
//     die("ERROR: Couldn't connect. {$e->getMessage()}");
// }


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
