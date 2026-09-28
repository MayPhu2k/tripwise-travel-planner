<?php

require_once "includes/auth.php";
require_once "config/database.php";

$user_id = $_SESSION["user_id"];


/*
|--------------------------------------------------------------------------
| Get user's trips
|--------------------------------------------------------------------------
*/

$stmt = $pdo->prepare("
    SELECT
        trips.trip_id,
        trips.trip_name,
        trips.start_date,
        trips.end_date,
        trips.travelers,
        trips.budget,
        destinations.name AS destination_name,
        destinations.country,
        destinations.image
    FROM trips
    INNER JOIN destinations
        ON trips.destination_id = destinations.destination_id
    WHERE trips.user_id = ?
    ORDER BY trips.start_date ASC
");

$stmt->execute([$user_id]);

$trips = $stmt->fetchAll();

?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>My Trips - TripWise</title>

    <link rel="stylesheet" href="css/style.css">

</head>

<body>

<?php require_once "includes/header.php"; ?>


<main class="my-trips-page">


    <!-- Page Header -->

    <section class="my-trips-header">

        <div>

            <p class="page-label">
                TRIPWISE
            </p>

            <h1>
                My Trips
            </h1>

            <p>
                View, manage, and organize all your travel plans in one place.
            </p>

        </div>


        <a href="create-trip.php" class="primary-button">
            + Create New Trip
        </a>

    </section>


    <?php if (count($trips) > 0): ?>


        <!-- Trip Count -->

        <div class="trip-summary">

            <strong>
                <?php echo count($trips); ?>
            </strong>

            <span>
                <?php echo count($trips) === 1 ? "trip" : "trips"; ?> planned
            </span>

        </div>


        <!-- Trips -->

        <section class="my-trips-grid">


            <?php foreach ($trips as $trip): ?>

                <?php

                $start_date = new DateTime($trip["start_date"]);
                $end_date = new DateTime($trip["end_date"]);

                $today = new DateTime();

                if ($end_date < $today) {
                    $trip_status = "Completed";
                    $trip_status_class = "completed";
                } elseif ($start_date <= $today && $end_date >= $today) {
                    $trip_status = "Ongoing";
                    $trip_status_class = "ongoing";
                } else {
                    $trip_status = "Upcoming";
                    $trip_status_class = "upcoming";
                }

                ?>

                <article class="my-trip-card">


                    <!-- Image -->

                    <div class="my-trip-image">

                        <img
                            src="images/<?php echo htmlspecialchars($trip["image"]); ?>"
                            alt="<?php echo htmlspecialchars($trip["destination_name"]); ?>"
                        >

                        <span class="trip-status <?php echo $trip_status_class; ?>">
                            <?php echo $trip_status; ?>
                        </span>

                    </div>


                    <!-- Content -->

                    <div class="my-trip-content">


                        <p class="my-trip-country">
                            <?php echo htmlspecialchars($trip["country"]); ?>
                        </p>


                        <h2>
                            <?php echo htmlspecialchars($trip["trip_name"]); ?>
                        </h2>


                        <p class="my-trip-destination">
                            📍 <?php echo htmlspecialchars($trip["destination_name"]); ?>
                        </p>


                        <div class="my-trip-details">


                            <div>

                                <span>
                                    Dates
                                </span>

                                <strong>

                                    <?php echo $start_date->format("M d, Y"); ?>

                                    →

                                    <?php echo $end_date->format("M d, Y"); ?>

                                </strong>

                            </div>


                            <div>

                                <span>
                                    Travelers
                                </span>

                                <strong>
                                    <?php echo (int)$trip["travelers"]; ?>
                                    <?php echo (int)$trip["travelers"] === 1 ? "person" : "people"; ?>
                                </strong>

                            </div>


                            <div>

                                <span>
                                    Budget
                                </span>

                                <strong>
                                    ฿<?php echo number_format($trip["budget"], 2); ?>
                                </strong>

                            </div>


                        </div>


                        <!-- Buttons -->

                        <div class="my-trip-buttons">

                            <a
                                href="itinerary.php?trip_id=<?php echo $trip["trip_id"]; ?>"
                                class="primary-button"
                            >
                                Itinerary
                            </a>


                            <a
                                href="budget.php?trip_id=<?php echo $trip["trip_id"]; ?>"
                                class="secondary-button"
                            >
                                Budget
                            </a>


                            <a
                                href="edit-trip.php?id=<?php echo $trip["trip_id"]; ?>"
                                class="secondary-button"
                            >
                                Edit
                            </a>


                            <a
                                href="delete-trip.php?id=<?php echo $trip["trip_id"]; ?>"
                                class="delete-button"
                                onclick="return confirm('Are you sure you want to delete this trip?');"
                            >
                                Delete
                            </a>

                        </div>


                    </div>

                </article>

            <?php endforeach; ?>


        </section>


    <?php else: ?>


        <!-- Empty State -->

        <section class="my-trips-empty">

            <div class="empty-trip-icon">
                ✈️
            </div>

            <h2>
                No trips yet
            </h2>

            <p>
                Your travel plans will appear here once you create your first trip.
            </p>

            <a href="create-trip.php" class="primary-button">
                Plan Your First Trip
            </a>

        </section>


    <?php endif; ?>


</main>


<?php require_once "includes/footer.php"; ?>


</body>

</html>