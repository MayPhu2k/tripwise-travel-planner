<?php

session_start();
require_once "includes/auth.php";
require_once "config/database.php";


// =========================
// CHECK LOGIN
// =========================

if (!isset($_SESSION["user_id"])) {
    header("Location: login.php");
    exit;
}


// =========================
// GET TRIP ID
// =========================

$trip_id = (int) ($_GET["id"] ?? 0);

if ($trip_id <= 0) {
    header("Location: my-trips.php");
    exit;
}


// =========================
// DELETE TRIP
// =========================

$stmt = $pdo->prepare("
    DELETE FROM trips
    WHERE trip_id = ?
    AND user_id = ?
");

$stmt->execute([
    $trip_id,
    $_SESSION["user_id"]
]);


// =========================
// RETURN TO MY TRIPS
// =========================

header("Location: my-trips.php");

exit;