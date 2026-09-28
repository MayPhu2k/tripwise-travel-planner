<?php

require_once "includes/auth.php";
require_once "config/database.php";

$user_id = $_SESSION["user_id"];

$trip_id = isset($_GET["trip_id"]) ? (int)$_GET["trip_id"] : 0;

$errors = [];

if ($trip_id <= 0) {
    header("Location: my-trips.php");
    exit;
}


/*
|--------------------------------------------------------------------------
| Get trip
|--------------------------------------------------------------------------
*/

$stmt = $pdo->prepare("
    SELECT
        trips.*,
        destinations.name AS destination_name,
        destinations.country,
        destinations.image
    FROM trips
    INNER JOIN destinations
        ON trips.destination_id = destinations.destination_id
    WHERE trips.trip_id = ?
    AND trips.user_id = ?
");

$stmt->execute([
    $trip_id,
    $user_id
]);

$trip = $stmt->fetch();

if (!$trip) {
    header("Location: my-trips.php");
    exit;
}


/*
|--------------------------------------------------------------------------
| Get places
|--------------------------------------------------------------------------
*/

$stmt = $pdo->prepare("
    SELECT
        place_id,
        name,
        description,
        category,
        image
    FROM places
    WHERE destination_id = ?
    ORDER BY name ASC
");

$stmt->execute([
    $trip["destination_id"]
]);

$places = $stmt->fetchAll();


/*
|--------------------------------------------------------------------------
| Add itinerary item
|--------------------------------------------------------------------------
*/

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $place_id = (int)($_POST["place_id"] ?? 0);
    $activity_date = $_POST["activity_date"] ?? "";
    $start_time = $_POST["start_time"] ?? "";
    $end_time = $_POST["end_time"] ?? "";
    $notes = trim($_POST["notes"] ?? "");


    /*
    |--------------------------------------------------------------------------
    | Validate place
    |--------------------------------------------------------------------------
    */

    if ($place_id <= 0) {

        $errors[] = "Please select a place.";

    } else {

        $stmt = $pdo->prepare("
            SELECT place_id
            FROM places
            WHERE place_id = ?
            AND destination_id = ?
        ");

        $stmt->execute([
            $place_id,
            $trip["destination_id"]
        ]);

        if (!$stmt->fetch()) {

            $errors[] = "The selected place is not available for this destination.";

        }

    }


    /*
    |--------------------------------------------------------------------------
    | Validate date
    |--------------------------------------------------------------------------
    */

    if ($activity_date === "") {

        $errors[] = "Please select an activity date.";

    } elseif (
        $activity_date < $trip["start_date"] ||
        $activity_date > $trip["end_date"]
    ) {

        $errors[] = "The activity date must be within your trip dates.";

    }


    /*
    |--------------------------------------------------------------------------
    | Validate time
    |--------------------------------------------------------------------------
    */

    if (
        $start_time !== "" &&
        $end_time !== "" &&
        $end_time < $start_time
    ) {

        $errors[] = "The end time cannot be earlier than the start time.";

    }


    /*
    |--------------------------------------------------------------------------
    | Insert itinerary item
    |--------------------------------------------------------------------------
    */

    if (empty($errors)) {

        $stmt = $pdo->prepare("
            INSERT INTO itineraries
            (
                trip_id,
                place_id,
                activity_date,
                start_time,
                end_time,
                notes
            )
            VALUES
            (?, ?, ?, ?, ?, ?)
        ");

        $stmt->execute([
            $trip_id,
            $place_id,
            $activity_date,
            $start_time !== "" ? $start_time : null,
            $end_time !== "" ? $end_time : null,
            $notes !== "" ? $notes : null
        ]);


        header("Location: itinerary.php?trip_id=" . $trip_id);
        exit;

    }

}


/*
|--------------------------------------------------------------------------
| Get itinerary
|--------------------------------------------------------------------------
*/

$stmt = $pdo->prepare("
    SELECT
        itineraries.*,
        places.name AS place_name,
        places.category,
        places.image
    FROM itineraries
    LEFT JOIN places
        ON itineraries.place_id = places.place_id
    WHERE itineraries.trip_id = ?
    ORDER BY
        itineraries.activity_date ASC,
        itineraries.start_time ASC,
        itineraries.itinerary_id ASC
");

$stmt->execute([
    $trip_id
]);

$itinerary_items = $stmt->fetchAll();


/*
|--------------------------------------------------------------------------
| Group itinerary by date
|--------------------------------------------------------------------------
*/

$grouped_itinerary = [];

foreach ($itinerary_items as $item) {

    $date = $item["activity_date"];

    if (!isset($grouped_itinerary[$date])) {

        $grouped_itinerary[$date] = [];

    }

    $grouped_itinerary[$date][] = $item;

}


/*
|--------------------------------------------------------------------------
| Create all trip dates
|--------------------------------------------------------------------------
*/

$trip_start = new DateTime($trip["start_date"]);
$trip_end = new DateTime($trip["end_date"]);

$trip_dates = [];

$current_date = clone $trip_start;

while ($current_date <= $trip_end) {

    $trip_dates[] = $current_date->format("Y-m-d");

    $current_date->modify("+1 day");

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

    <title>
        Itinerary - <?php echo htmlspecialchars($trip["trip_name"]); ?>
        - TripWise
    </title>

    <link
        rel="stylesheet"
        href="css/style.css"
    >

</head>

<body>

<?php require_once "includes/header.php"; ?>


<main class="itinerary-page">


    <!-- Header -->

    <section class="itinerary-header">


        <div>

            <p class="page-label">
                TRIPWISE ITINERARY
            </p>

            <h1>
                <?php echo htmlspecialchars($trip["trip_name"]); ?>
            </h1>

            <p>
                📍 <?php echo htmlspecialchars($trip["destination_name"]); ?>,
                <?php echo htmlspecialchars($trip["country"]); ?>
            </p>

            <p class="itinerary-dates">

                <?php echo date("M d, Y", strtotime($trip["start_date"])); ?>

                →

                <?php echo date("M d, Y", strtotime($trip["end_date"])); ?>

            </p>

        </div>


        <div class="itinerary-header-actions">

            <a
                href="my-trips.php"
                class="secondary-button"
            >
                ← My Trips
            </a>

            <a
                href="budget.php?trip_id=<?php echo $trip_id; ?>"
                class="primary-button"
            >
                View Budget
            </a>

        </div>


    </section>


    <!-- Errors -->

    <?php if (!empty($errors)): ?>

        <div class="form-errors itinerary-errors">

            <strong>
                Please fix the following:
            </strong>

            <ul>

                <?php foreach ($errors as $error): ?>

                    <li>
                        <?php echo htmlspecialchars($error); ?>
                    </li>

                <?php endforeach; ?>

            </ul>

        </div>

    <?php endif; ?>


    <!-- Add Activity -->

    <section class="itinerary-add-card">


        <div class="itinerary-card-heading">

            <div>

                <h2>
                    Add Activity
                </h2>

                <p>
                    Add a place to your travel plan.
                </p>

            </div>

        </div>


        <form
            method="POST"
            class="itinerary-form"
        >


            <div class="form-group">

                <label for="place_id">
                    Place
                </label>

                <select
                    id="place_id"
                    name="place_id"
                    required
                >

                    <option value="">
                        Select a place
                    </option>

                    <?php foreach ($places as $place): ?>

                        <option
                            value="<?php echo $place["place_id"]; ?>"
                        >

                            <?php echo htmlspecialchars($place["name"]); ?>

                            —
                            <?php echo htmlspecialchars($place["category"]); ?>

                        </option>

                    <?php endforeach; ?>

                </select>

            </div>


            <div class="form-group">

                <label for="activity_date">
                    Date
                </label>

                <select
                    id="activity_date"
                    name="activity_date"
                    required
                >

                    <option value="">
                        Select a date
                    </option>

                    <?php foreach ($trip_dates as $date): ?>

                        <option value="<?php echo $date; ?>">

                            <?php echo date("D, M d, Y", strtotime($date)); ?>

                        </option>

                    <?php endforeach; ?>

                </select>

            </div>


            <div class="form-group">

                <label for="start_time">
                    Start Time
                </label>

                <input
                    type="time"
                    id="start_time"
                    name="start_time"
                >

            </div>


            <div class="form-group">

                <label for="end_time">
                    End Time
                </label>

                <input
                    type="time"
                    id="end_time"
                    name="end_time"
                >

            </div>


            <div class="form-group itinerary-notes-group">

                <label for="notes">
                    Notes
                </label>

                <input
                    type="text"
                    id="notes"
                    name="notes"
                    placeholder="e.g. Book tickets in advance"
                >

            </div>


            <div class="itinerary-submit">

                <button
                    type="submit"
                    class="primary-button"
                >
                    + Add Activity
                </button>

            </div>


        </form>


    </section>


    <!-- Timeline -->

    <section class="itinerary-section">


        <div class="itinerary-section-header">

            <div>

                <h2>
                    Your Itinerary
                </h2>

                <p>
                    Organize your activities day by day.
                </p>

            </div>

            <span class="itinerary-count">
                <?php echo count($itinerary_items); ?>
                <?php echo count($itinerary_items) === 1 ? "activity" : "activities"; ?>
            </span>

        </div>


        <?php foreach ($trip_dates as $date): ?>


            <div class="itinerary-day">


                <div class="itinerary-day-header">

                    <div>

                        <span class="day-number">
                            DAY
                        </span>

                        <h3>
                            <?php echo date("l, F d, Y", strtotime($date)); ?>
                        </h3>

                    </div>

                    <span class="day-date">
                        <?php echo date("M d", strtotime($date)); ?>
                    </span>

                </div>


                <?php if (isset($grouped_itinerary[$date])): ?>


                    <div class="itinerary-items">


                        <?php foreach ($grouped_itinerary[$date] as $item): ?>

                            <article class="itinerary-item">


                                <div class="itinerary-item-image">

                                    <?php if (!empty($item["image"])): ?>

                                        <img
                                            src="images/<?php echo htmlspecialchars($item["image"]); ?>"
                                            alt="<?php echo htmlspecialchars($item["place_name"] ?? "Place"); ?>"
                                        >

                                    <?php else: ?>

                                        <div class="itinerary-placeholder">
                                            📍
                                        </div>

                                    <?php endif; ?>

                                </div>


                                <div class="itinerary-item-content">


                                    <div class="itinerary-item-top">

                                        <div>

                                            <?php if (!empty($item["category"])): ?>

                                                <span class="itinerary-category">
                                                    <?php echo htmlspecialchars($item["category"]); ?>
                                                </span>

                                            <?php endif; ?>

                                            <h4>
                                                <?php
                                                echo htmlspecialchars(
                                                    $item["place_name"] ?? "Place removed"
                                                );
                                                ?>
                                            </h4>

                                        </div>


                                        <?php if (!empty($item["start_time"])): ?>

                                            <span class="itinerary-time">

                                                <?php echo date(
                                                    "g:i A",
                                                    strtotime($item["start_time"])
                                                ); ?>

                                                <?php if (!empty($item["end_time"])): ?>

                                                    -
                                                    <?php echo date(
                                                        "g:i A",
                                                        strtotime($item["end_time"])
                                                    ); ?>

                                                <?php endif; ?>

                                            </span>

                                        <?php endif; ?>


                                    </div>


                                    <?php if (!empty($item["notes"])): ?>

                                        <p class="itinerary-item-notes">
                                            <?php echo htmlspecialchars($item["notes"]); ?>
                                        </p>

                                    <?php endif; ?>


                                </div>


                            </article>

                        <?php endforeach; ?>


                    </div>


                <?php else: ?>


                    <div class="itinerary-empty-day">

                        <span>
                            ✨
                        </span>

                        <p>
                            No activities planned for this day yet.
                        </p>

                    </div>


                <?php endif; ?>


            </div>


        <?php endforeach; ?>


    </section>


</main>


<?php require_once "includes/footer.php"; ?>


</body>

</html>