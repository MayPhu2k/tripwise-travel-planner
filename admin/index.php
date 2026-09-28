<?php

require_once "../includes/admin-auth.php";
require_once "../config/database.php";

/*
|--------------------------------------------------------------------------
| Dashboard Statistics
|--------------------------------------------------------------------------
*/

/* Total users */

$stmt = $pdo->query("
    SELECT COUNT(*) 
    FROM users
");

$total_users = (int) $stmt->fetchColumn();


/* Total destinations */

$stmt = $pdo->query("
    SELECT COUNT(*) 
    FROM destinations
");

$total_destinations = (int) $stmt->fetchColumn();


/* Total places */

$stmt = $pdo->query("
    SELECT COUNT(*) 
    FROM places
");

$total_places = (int) $stmt->fetchColumn();


/* Total trips */

$stmt = $pdo->query("
    SELECT COUNT(*) 
    FROM trips
");

$total_trips = (int) $stmt->fetchColumn();


/*
|--------------------------------------------------------------------------
| Recent Trips
|--------------------------------------------------------------------------
*/

$stmt = $pdo->query("
    SELECT
        t.trip_id,
        t.trip_name,
        t.start_date,
        t.end_date,
        u.name AS user_name,
        d.name AS destination_name,
        d.country

    FROM trips t

    INNER JOIN users u
        ON t.user_id = u.user_id

    INNER JOIN destinations d
        ON t.destination_id = d.destination_id

    ORDER BY t.created_at DESC

    LIMIT 5
");

$recent_trips = $stmt->fetchAll();

?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>Admin Dashboard | TripWise</title>

    <link rel="stylesheet" href="../css/style.css">

</head>

<body>

<?php require_once "../includes/header.php"; ?>


<main class="admin-page">


    <!-- Page Header -->

    <section class="admin-header">

        <div>

            <p class="page-label">
                ADMIN PANEL
            </p>

            <h1>
                Dashboard
            </h1>

            <p class="admin-intro">
                Manage TripWise destinations, places, users, and trips
                from one place.
            </p>

        </div>

    </section>


    <!-- Statistics -->

    <section class="admin-stats">


        <div class="admin-stat-card">

            <span class="admin-stat-label">
                USERS
            </span>

            <strong>
                <?php echo $total_users; ?>
            </strong>

            <p>
                Registered users
            </p>

        </div>


        <div class="admin-stat-card">

            <span class="admin-stat-label">
                DESTINATIONS
            </span>

            <strong>
                <?php echo $total_destinations; ?>
            </strong>

            <p>
                Available destinations
            </p>

        </div>


        <div class="admin-stat-card">

            <span class="admin-stat-label">
                PLACES
            </span>

            <strong>
                <?php echo $total_places; ?>
            </strong>

            <p>
                Travel places
            </p>

        </div>


        <div class="admin-stat-card">

            <span class="admin-stat-label">
                TRIPS
            </span>

            <strong>
                <?php echo $total_trips; ?>
            </strong>

            <p>
                User trips
            </p>

        </div>


    </section>


    <!-- Quick Actions -->

    <section class="admin-section">

        <div class="admin-section-header">

            <div>

                <p class="page-label">
                    MANAGEMENT
                </p>

                <h2>
                    Quick Actions
                </h2>

            </div>

        </div>


        <div class="admin-actions">


            <a
                href="destinations.php"
                class="admin-action-card"
            >

                <span class="admin-action-icon">
                    ✦
                </span>

                <div>

                    <h3>
                        Manage Destinations
                    </h3>

                    <p>
                        Add, edit, or remove destinations.
                    </p>

                </div>

            </a>


            <a
                href="places.php"
                class="admin-action-card"
            >

                <span class="admin-action-icon">
                    ◇
                </span>

                <div>

                    <h3>
                        Manage Places
                    </h3>

                    <p>
                        Manage attractions and places.
                    </p>

                </div>

            </a>


            <a
                href="users.php"
                class="admin-action-card"
            >

                <span class="admin-action-icon">
                    ○
                </span>

                <div>

                    <h3>
                        Manage Users
                    </h3>

                    <p>
                        View registered TripWise users.
                    </p>

                </div>

            </a>


        </div>

    </section>


    <!-- Recent Trips -->

    <section class="admin-section">

        <div class="admin-section-header">

            <div>

                <p class="page-label">
                    RECENT ACTIVITY
                </p>

                <h2>
                    Recent Trips
                </h2>

            </div>

        </div>


        <?php if (count($recent_trips) > 0): ?>

            <div class="admin-table-wrapper">

                <table class="admin-table">

                    <thead>

                        <tr>

                            <th>
                                Trip
                            </th>

                            <th>
                                User
                            </th>

                            <th>
                                Destination
                            </th>

                            <th>
                                Dates
                            </th>

                        </tr>

                    </thead>

                    <tbody>

                        <?php foreach ($recent_trips as $trip): ?>

                            <tr>

                                <td>

                                    <strong>
                                        <?php
                                        echo htmlspecialchars(
                                            $trip["trip_name"]
                                        );
                                        ?>
                                    </strong>

                                </td>


                                <td>

                                    <?php
                                    echo htmlspecialchars(
                                        $trip["user_name"]
                                    );
                                    ?>

                                </td>


                                <td>

                                    <?php
                                    echo htmlspecialchars(
                                        $trip["destination_name"]
                                    );
                                    ?>

                                    <span class="admin-country">

                                        <?php
                                        echo htmlspecialchars(
                                            $trip["country"]
                                        );
                                        ?>

                                    </span>

                                </td>


                                <td>

                                    <?php
                                    echo date(
                                        "M d, Y",
                                        strtotime($trip["start_date"])
                                    );
                                    ?>

                                    –

                                    <?php
                                    echo date(
                                        "M d, Y",
                                        strtotime($trip["end_date"])
                                    );
                                    ?>

                                </td>

                            </tr>

                        <?php endforeach; ?>

                    </tbody>

                </table>

            </div>

        <?php else: ?>

            <div class="admin-empty">

                <p>
                    No trips have been created yet.
                </p>

            </div>

        <?php endif; ?>


    </section>


</main>


<?php require_once "../includes/footer.php"; ?>

</body>

</html>