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
// GET TRIP
// =========================

$stmt = $pdo->prepare("
    SELECT *
    FROM trips
    WHERE trip_id = ?
    AND user_id = ?
");

$stmt->execute([
    $trip_id,
    $_SESSION["user_id"]
]);

$trip = $stmt->fetch();


// Make sure trip belongs to current user

if (!$trip) {
    header("Location: my-trips.php");
    exit;
}


// =========================
// GET DESTINATIONS
// =========================

$destinationStmt = $pdo->query("
    SELECT
        destination_id,
        name
    FROM destinations
    ORDER BY name
");

$destinations = $destinationStmt->fetchAll();


// =========================
// DEFAULT VALUES
// =========================

$trip_name = $trip["trip_name"];
$destination_id = $trip["destination_id"];
$start_date = $trip["start_date"];
$end_date = $trip["end_date"];
$travelers = $trip["travelers"];
$budget = $trip["budget"];

$errors = [];


// =========================
// FORM SUBMISSION
// =========================

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $trip_name = trim($_POST["trip_name"] ?? "");

    $destination_id = (int) ($_POST["destination_id"] ?? 0);

    $start_date = $_POST["start_date"] ?? "";

    $end_date = $_POST["end_date"] ?? "";

    $travelers = (int) ($_POST["travelers"] ?? 0);

    $budget = trim($_POST["budget"] ?? "");


    // =========================
    // VALIDATION
    // =========================

    if ($trip_name === "") {
        $errors[] = "Trip name is required.";
    }


    if ($destination_id <= 0) {
        $errors[] = "Please select a destination.";
    }


    if ($start_date === "") {
        $errors[] = "Start date is required.";
    }


    if ($end_date === "") {
        $errors[] = "End date is required.";
    }


    if (
        $start_date !== "" &&
        $end_date !== "" &&
        $end_date < $start_date
    ) {
        $errors[] = "End date cannot be before the start date.";
    }


    if ($travelers < 1) {
        $errors[] = "Travelers must be at least 1.";
    }


    if ($budget === "") {

        $errors[] = "Budget is required.";

    } elseif (!is_numeric($budget) || $budget < 0) {

        $errors[] = "Budget must be a valid number.";

    }


    // Check destination exists

    if ($destination_id > 0) {

        $destinationCheck = $pdo->prepare("
            SELECT destination_id
            FROM destinations
            WHERE destination_id = ?
        ");

        $destinationCheck->execute([
            $destination_id
        ]);

        if (!$destinationCheck->fetch()) {
            $errors[] = "Selected destination does not exist.";
        }
    }


    // =========================
    // UPDATE TRIP
    // =========================

    if (empty($errors)) {

        $stmt = $pdo->prepare("
            UPDATE trips
            SET
                trip_name = ?,
                destination_id = ?,
                start_date = ?,
                end_date = ?,
                travelers = ?,
                budget = ?
            WHERE trip_id = ?
            AND user_id = ?
        ");

        $stmt->execute([
            $trip_name,
            $destination_id,
            $start_date,
            $end_date,
            $travelers,
            $budget,
            $trip_id,
            $_SESSION["user_id"]
        ]);


        header("Location: my-trips.php");
        exit;
    }
}

?>


<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>Edit Trip | TripWise</title>

    <link
        rel="stylesheet"
        href="css/style.css"
    >

</head>


<body>


<?php include "includes/header.php"; ?>


<main class="create-trip-page">

    <div class="form-container">

        <h1>Edit Trip</h1>

        <p class="form-subtitle">
            Update your travel plan.
        </p>


        <?php if (!empty($errors)): ?>

            <div class="error-box">

                <?php foreach ($errors as $error): ?>

                    <p>
                        <?php echo htmlspecialchars($error); ?>
                    </p>

                <?php endforeach; ?>

            </div>

        <?php endif; ?>


        <form method="POST">


            <div class="form-group">

                <label for="trip_name">
                    Trip Name
                </label>

                <input
                    type="text"
                    id="trip_name"
                    name="trip_name"
                    value="<?php echo htmlspecialchars($trip_name); ?>"
                    required
                >

            </div>


            <div class="form-group">

                <label for="destination_id">
                    Destination
                </label>

                <select
                    id="destination_id"
                    name="destination_id"
                    required
                >

                    <option value="">
                        Select Destination
                    </option>


                    <?php foreach ($destinations as $destination): ?>

                        <option
                            value="<?php echo $destination["destination_id"]; ?>"
                            <?php
                            echo $destination_id == $destination["destination_id"]
                                ? "selected"
                                : "";
                            ?>
                        >

                            <?php echo htmlspecialchars($destination["name"]); ?>

                        </option>

                    <?php endforeach; ?>

                </select>

            </div>


            <div class="form-group">

                <label for="start_date">
                    Start Date
                </label>

                <input
                    type="date"
                    id="start_date"
                    name="start_date"
                    value="<?php echo htmlspecialchars($start_date); ?>"
                    required
                >

            </div>


            <div class="form-group">

                <label for="end_date">
                    End Date
                </label>

                <input
                    type="date"
                    id="end_date"
                    name="end_date"
                    value="<?php echo htmlspecialchars($end_date); ?>"
                    required
                >

            </div>


            <div class="form-group">

                <label for="travelers">
                    Number of Travelers
                </label>

                <input
                    type="number"
                    id="travelers"
                    name="travelers"
                    value="<?php echo htmlspecialchars($travelers); ?>"
                    min="1"
                    required
                >

            </div>


            <div class="form-group">

                <label for="budget">
                    Budget (฿)
                </label>

                <input
                    type="number"
                    id="budget"
                    name="budget"
                    value="<?php echo htmlspecialchars($budget); ?>"
                    min="0"
                    step="0.01"
                    required
                >

            </div>


            <button
                type="submit"
                class="primary-button"
            >
                Update Trip
            </button>


            <a
                href="my-trips.php"
                class="secondary-button"
            >
                Cancel
            </a>


        </form>

    </div>

</main>


<?php include "includes/footer.php"; ?>


</body>

</html>