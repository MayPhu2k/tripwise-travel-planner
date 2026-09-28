<?php

require_once "includes/auth.php";
require_once "config/database.php";

$user_id = $_SESSION["user_id"];

/*
|--------------------------------------------------------------------------
| Get user information
|--------------------------------------------------------------------------
*/

$stmt = $pdo->prepare("
    SELECT name, email
    FROM users
    WHERE user_id = ?
");

$stmt->execute([$user_id]);

$user = $stmt->fetch();


/*
|--------------------------------------------------------------------------
| Get total trips
|--------------------------------------------------------------------------
*/

$stmt = $pdo->prepare("
    SELECT COUNT(*)
    FROM trips
    WHERE user_id = ?
");

$stmt->execute([$user_id]);

$total_trips = $stmt->fetchColumn();


/*
|--------------------------------------------------------------------------
| Get upcoming trips
|--------------------------------------------------------------------------
*/

$stmt = $pdo->prepare("
    SELECT COUNT(*)
    FROM trips
    WHERE user_id = ?
    AND start_date >= CURDATE()
");

$stmt->execute([$user_id]);

$upcoming_trips = $stmt->fetchColumn();


/*
|--------------------------------------------------------------------------
| Get saved places
|--------------------------------------------------------------------------
*/

$stmt = $pdo->prepare("
    SELECT COUNT(*)
    FROM saved_places
    WHERE user_id = ?
");

$stmt->execute([$user_id]);

$saved_places = $stmt->fetchColumn();


/*
|--------------------------------------------------------------------------
| Get total budget
|--------------------------------------------------------------------------
*/

$stmt = $pdo->prepare("
    SELECT COALESCE(SUM(budget), 0)
    FROM trips
    WHERE user_id = ?
");

$stmt->execute([$user_id]);

$total_budget = $stmt->fetchColumn();


/*
|--------------------------------------------------------------------------
| Get recent trips
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
    LIMIT 5
");

$stmt->execute([$user_id]);

$recent_trips = $stmt->fetchAll();

?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Dashboard - TripWise</title>

    <link rel="stylesheet" href="css/style.css">

</head>

<body>

<?php require_once "includes/header.php"; ?>


<main class="dashboard-page">


    <!-- Dashboard Header -->

    <section class="dashboard-header">

        <div>

            <p class="dashboard-label">
                TRIPWISE DASHBOARD
            </p>

            <h1>
                Welcome back,
                <?php echo htmlspecialchars($user["name"]); ?> 👋
            </h1>

            <p>
                Manage your trips, discover new destinations,
                and keep your travel plans organized.
            </p>

        </div>

        <div>

            <a href="create-trip.php" class="primary-button">
                + Plan a New Trip
            </a>

        </div>

    </section>


    <!-- Statistics -->

    <section class="dashboard-stats">


        <div class="dashboard-stat-card">

            <div class="dashboard-stat-icon">
                ✈️
            </div>

            <div>

                <span>
                    Total Trips
                </span>

                <strong>
                    <?php echo $total_trips; ?>
                </strong>

            </div>

        </div>


        <div class="dashboard-stat-card">

            <div class="dashboard-stat-icon">
                📅
            </div>

            <div>

                <span>
                    Upcoming Trips
                </span>

                <strong>
                    <?php echo $upcoming_trips; ?>
                </strong>

            </div>

        </div>


        <div class="dashboard-stat-card">

            <div class="dashboard-stat-icon">
                ❤️
            </div>

            <div>

                <span>
                    Saved Places
                </span>

                <strong>
                    <?php echo $saved_places; ?>
                </strong>

            </div>

        </div>


        <div class="dashboard-stat-card">

            <div class="dashboard-stat-icon">
                💰
            </div>

            <div>

                <span>
                    Planned Budget
                </span>

                <strong>
                    ฿<?php echo number_format($total_budget, 2); ?>
                </strong>

            </div>

        </div>


    </section>


    <!-- Dashboard Content -->

    <section class="dashboard-content">


        <!-- Recent Trips -->

        <div class="dashboard-main-card">

            <div class="dashboard-card-header">

                <div>

                    <h2>
                        My Trips
                    </h2>

                    <p>
                        Your upcoming and planned journeys.
                    </p>

                </div>

                <a href="my-trips.php">
                    View All
                </a>

            </div>


            <?php if (count($recent_trips) > 0): ?>

                <div class="dashboard-trips-list">


                    <?php foreach ($recent_trips as $trip): ?>

                        <div class="dashboard-trip">


                            <div class="dashboard-trip-image">

                                <img
                                    src="images/<?php echo htmlspecialchars($trip["image"]); ?>"
                                    alt="<?php echo htmlspecialchars($trip["destination_name"]); ?>"
                                >

                            </div>


                            <div class="dashboard-trip-info">

                                <h3>
                                    <?php echo htmlspecialchars($trip["trip_name"]); ?>
                                </h3>

                                <p class="dashboard-trip-destination">

                                    <?php echo htmlspecialchars($trip["destination_name"]); ?>,
                                    <?php echo htmlspecialchars($trip["country"]); ?>

                                </p>

                                <p class="dashboard-trip-date">

                                    <?php echo date("M d, Y", strtotime($trip["start_date"])); ?>

                                    →

                                    <?php echo date("M d, Y", strtotime($trip["end_date"])); ?>

                                </p>

                            </div>


                            <div class="dashboard-trip-actions">

                                <a
                                    href="itinerary.php?trip_id=<?php echo $trip["trip_id"]; ?>"
                                    class="secondary-button"
                                >
                                    Itinerary
                                </a>

                                <a
                                    href="budget.php?trip_id=<?php echo $trip["trip_id"]; ?>"
                                    class="secondary-button"
                                >
                                    Budget
                                </a>

                            </div>


                        </div>

                    <?php endforeach; ?>


                </div>

            <?php else: ?>

                <div class="dashboard-empty">

                    <div class="dashboard-empty-icon">
                        ✈️
                    </div>

                    <h3>
                        No trips yet
                    </h3>

                    <p>
                        Start planning your next adventure with TripWise.
                    </p>

                    <a href="create-trip.php" class="primary-button">
                        Create Your First Trip
                    </a>

                </div>

            <?php endif; ?>


        </div>


        <!-- Quick Actions -->

        <div class="dashboard-side-card">

            <h2>
                Quick Actions
            </h2>

            <p>
                Everything you need to manage your travel plans.
            </p>


            <div class="dashboard-actions">


                <a href="destinations.php" class="dashboard-action">

                    <span>
                        🌍
                    </span>

                    <div>

                        <strong>
                            Explore Destinations
                        </strong>

                        <small>
                            Discover places around the world
                        </small>

                    </div>

                </a>


                <a href="create-trip.php" class="dashboard-action">

                    <span>
                        ✈️
                    </span>

                    <div>

                        <strong>
                            Plan a Trip
                        </strong>

                        <small>
                            Create your next journey
                        </small>

                    </div>

                </a>


                <a href="saved-places.php" class="dashboard-action">

                    <span>
                        ❤️
                    </span>

                    <div>

                        <strong>
                            Saved Places
                        </strong>

                        <small>
                            View your favorite places
                        </small>

                    </div>

                </a>


                <a href="my-trips.php" class="dashboard-action">

                    <span>
                        📋
                    </span>

                    <div>

                        <strong>
                            Manage Trips
                        </strong>

                        <small>
                            Edit or organize your trips
                        </small>

                    </div>

                </a>


            </div>

        </div>


    </section>


</main>


<?php require_once "includes/footer.php"; ?>


</body>

</html>