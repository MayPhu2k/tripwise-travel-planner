<?php

require_once "includes/auth.php";
require_once "config/database.php";

$user_id = $_SESSION["user_id"];

$place_id = isset($_GET["place_id"])
    ? (int) $_GET["place_id"]
    : 0;

if ($place_id <= 0) {
    header("Location: saved-places.php");
    exit;
}

$stmt = $pdo->prepare("
    DELETE FROM saved_places
    WHERE user_id = ?
      AND place_id = ?
");

$stmt->execute([
    $user_id,
    $place_id
]);

header("Location: saved-places.php");
exit;