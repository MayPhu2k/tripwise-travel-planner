<?php

require_once "../includes/admin-auth.php";
require_once "../config/database.php";

/*
|--------------------------------------------------------------------------
| Delete Destination
|--------------------------------------------------------------------------
*/

if (isset($_GET["delete"])) {

    $destination_id = (int) $_GET["delete"];

    if ($destination_id > 0) {

        $stmt = $pdo->prepare("
            DELETE FROM destinations
            WHERE destination_id = ?
        ");

        $stmt->execute([$destination_id]);
    }

    header("Location: destinations.php");
    exit;
}


/*
|--------------------------------------------------------------------------
| Get Destinations
|--------------------------------------------------------------------------
*/

$stmt = $pdo->query("
    SELECT
        d.destination_id,
        d.name,
        d.country,
        d.description,
        d.category,
        d.image,
        d.estimated_daily_cost,
        COUNT(p.place_id) AS place_count

    FROM destinations d

    LEFT JOIN places p
        ON d.destination_id = p.destination_id

    GROUP BY
        d.destination_id,
        d.name,
        d.country,
        d.description,
        d.category,
        d.image,
        d.estimated_daily_cost

    ORDER BY d.name ASC
");

$destinations = $stmt->fetchAll();

?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>Manage Destinations | TripWise</title>

    <link rel="stylesheet" href="../css/style.css">

</head>

<body>

<?php require_once "../includes/header.php"; ?>


<main class="admin-page">


    <!-- Header -->

    <section class="admin-header">

        <div>

            <p class="page-label">
                ADMIN PANEL
            </p>

            <h1>
                Destinations
            </h1>

            <p class="admin-intro">
                Manage the destinations available on TripWise.
            </p>

        </div>


        <div class="admin-header-action">

            <a
                href="destination-form.php"
                class="primary-button"
            >
                + Add Destination
            </a>

        </div>

    </section>


    <!-- Destination Count -->

    <section class="admin-summary">

        <div class="admin-summary-card">

            <span class="admin-summary-label">
                TOTAL DESTINATIONS
            </span>

            <strong>
                <?php echo count($destinations); ?>
            </strong>

        </div>

    </section>


    <!-- Destinations -->

    <?php if (count($destinations) > 0): ?>

        <section class="admin-destination-grid">

            <?php foreach ($destinations as $destination): ?>

                <article class="admin-destination-card">


                    <!-- Image -->

                    <div class="admin-destination-image">

                        <?php if (!empty($destination["image"])): ?>

                            <img
                                src="../images/<?php echo htmlspecialchars($destination["image"]); ?>"
                                alt="<?php echo htmlspecialchars($destination["name"]); ?>"
                            >

                        <?php else: ?>

                            <div class="admin-destination-placeholder">
                                No Image
                            </div>

                        <?php endif; ?>


                        <?php if (!empty($destination["category"])): ?>

                            <span class="admin-destination-category">
                                <?php echo htmlspecialchars($destination["category"]); ?>
                            </span>

                        <?php endif; ?>

                    </div>


                    <!-- Content -->

                    <div class="admin-destination-content">

                        <p class="admin-destination-country">

                            <?php echo htmlspecialchars($destination["country"]); ?>

                        </p>


                        <h2>

                            <?php echo htmlspecialchars($destination["name"]); ?>

                        </h2>


                        <?php if (!empty($destination["description"])): ?>

                            <p class="admin-destination-description">

                                <?php

                                echo htmlspecialchars(
                                    $destination["description"]
                                );

                                ?>

                            </p>

                        <?php endif; ?>


                        <div class="admin-destination-info">

                            <span>

                                <?php echo (int) $destination["place_count"]; ?>

                                places

                            </span>


                            <span>

                                ฿<?php

                                echo number_format(
                                    (float) $destination["estimated_daily_cost"],
                                    2
                                );

                                ?>

                                / day

                            </span>

                        </div>


                        <!-- Actions -->

                        <div class="admin-destination-actions">

                            <a
                                href="destination-form.php?id=<?php echo (int) $destination["destination_id"]; ?>"
                                class="secondary-button"
                            >
                                Edit
                            </a>


                            <a
                                href="destinations.php?delete=<?php echo (int) $destination["destination_id"]; ?>"
                                class="delete-button"
                                onclick="return confirm('Are you sure you want to delete this destination? All places, trips, and related data connected to this destination may also be deleted.');"
                            >
                                Delete
                            </a>

                        </div>

                    </div>

                </article>

            <?php endforeach; ?>

        </section>


    <?php else: ?>

        <section class="admin-empty">

            <h2>
                No destinations yet
            </h2>

            <p>
                Add your first destination to TripWise.
            </p>

            <a
                href="destination-form.php"
                class="primary-button"
            >
                + Add Destination
            </a>

        </section>

    <?php endif; ?>


</main>


<?php require_once "../includes/footer.php"; ?>

</body>

</html>