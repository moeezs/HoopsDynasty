<?php
session_start();
if (isset($_SESSION["userid"])) {
    echo json_encode(["username" => $_SESSION["userid"]]);
} else {
    echo json_encode(["username" => null]);
}
?>