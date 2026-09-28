<?php

require_once "includes/auth.php";
require_once "config/database.php";

$user_id = $_SESSION["user_id"];

$place_id = isset($_GET["place_id"])
    ? (int) $_GET["place_id"]
    : 0;

if ($place_id <= 0) {
    header("Location: destinations.php");
    exit;
}

/*
|--------------------------------------------------------------------------
| Check that the place exists
|--------------------------------------------------------------------------
*/

$stmt = $pdo->prepare("
    SELECT place_id
    FROM places
    WHERE place_id = ?
");

$stmt->execute([$place_id]);

$place = $stmt->fetch();

if (!$place) {
    header("Location: destinations.php");
    exit;
}

/*
|--------------------------------------------------------------------------
| Check if already saved
|--------------------------------------------------------------------------
*/

$stmt = $pdo->prepare("
    SELECT saved_id
    FROM saved_places
    WHERE user_id = ?
      AND place_id = ?
");

$stmt->execute([
    $user_id,
    $place_id
]);

$existing = $stmt->fetch();

/*
|--------------------------------------------------------------------------
| Save place if it is not already saved
|--------------------------------------------------------------------------
*/

if (!$existing) {

    $stmt = $pdo->prepare("
        INSERT INTO saved_places (user_id, place_id)
        VALUES (?, ?)
    ");

    $stmt->execute([
        $user_id,
        $place_id
    ]);
}

/*
|--------------------------------------------------------------------------
| Return to saved places
|--------------------------------------------------------------------------
*/

header("Location: saved-places.php");
exit;