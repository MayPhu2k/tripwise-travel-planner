<?php

require_once "config/database.php";

$destination_id = (int) ($_GET["id"] ?? 0);

if ($destination_id <= 0) {
    header("Location: destinations.php");
    exit;
}


/*
|--------------------------------------------------------------------------
| Get destination
|--------------------------------------------------------------------------
*/

$stmt = $pdo->prepare("
    SELECT *
    FROM destinations
    WHERE destination_id = ?
");

$stmt->execute([$destination_id]);

$destination = $stmt->fetch();


if (!$destination) {
    header("Location: destinations.php");
    exit;
}


/*
|--------------------------------------------------------------------------
| Get places for this destination
|--------------------------------------------------------------------------
*/

$placeStmt = $pdo->prepare("
    SELECT *
    FROM places
    WHERE destination_id = ?
    ORDER BY name
");

$placeStmt->execute([$destination_id]);

$places = $placeStmt->fetchAll();

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
        TripWise |
        <?php echo htmlspecialchars($destination["name"]); ?>
    </title>

    <link
        rel="stylesheet"
        href="css/style.css"
    >

</head>

<body>

<?php include "includes/header.php"; ?>


<main class="destination-details-page">


    <!-- Destination Hero -->

    <section class="destination-hero">

        <div class="destination-hero-image">

            <?php if (!empty($destination["image"])): ?>

                <img
                    src="images/<?php echo htmlspecialchars($destination["image"]); ?>"
                    alt="<?php echo htmlspecialchars($destination["name"]); ?>"
                >

            <?php else: ?>

                <div class="destination-hero-placeholder">
                    No Image Available
                </div>

            <?php endif; ?>

        </div>


        <div class="destination-hero-content">

            <span class="destination-category">

                <?php echo htmlspecialchars($destination["category"]); ?>

            </span>


            <h1>

                <?php echo htmlspecialchars($destination["name"]); ?>

            </h1>


            <p class="destination-country">

                <?php echo htmlspecialchars($destination["country"]); ?>

            </p>


            <p class="destination-description">

                <?php echo nl2br(
                    htmlspecialchars($destination["description"])
                ); ?>

            </p>


            <div class="destination-price">

                <span>
                    Estimated daily cost
                </span>

                <strong>

                    ฿<?php
                    echo number_format(
                        $destination["estimated_daily_cost"],
                        0
                    );
                    ?>

                </strong>

            </div>


            <div class="destination-actions">

                <?php if (isset($_SESSION["user_id"])): ?>

                    <a
                        href="create-trip.php?destination_id=<?php echo $destination["destination_id"]; ?>"
                        class="primary-button"
                    >
                        Plan a Trip
                    </a>

                <?php else: ?>

                    <a
                        href="login.php"
                        class="primary-button"
                    >
                        Login to Plan a Trip
                    </a>

                <?php endif; ?>


                <a
                    href="destinations.php"
                    class="secondary-button"
                >
                    Back to Destinations
                </a>

            </div>

        </div>

    </section>


    <!-- Places -->

    <section class="destination-places">

        <div class="section-header">

            <h2>
                Places to Explore
            </h2>

            <p>
                Discover attractions, restaurants, hotels,
                shopping areas, and other places in
                <?php echo htmlspecialchars($destination["name"]); ?>.
            </p>

        </div>


        <?php if (!empty($places)): ?>

            <div class="places-grid">

                <?php foreach ($places as $place): ?>

                    <article class="place-card">


                        <!-- Place Image -->

                        <?php if (!empty($place["image"])): ?>

                            <img
                                src="images/<?php echo htmlspecialchars($place["image"]); ?>"
                                alt="<?php echo htmlspecialchars($place["name"]); ?>"
                                class="place-image"
                            >

                        <?php else: ?>

                            <div class="place-image place-placeholder">
                                No Image
                            </div>

                        <?php endif; ?>


                        <!-- Place Content -->

                        <div class="place-content">

                            <span class="place-category">

                                <?php echo htmlspecialchars($place["category"]); ?>

                            </span>


                            <h3>

                                <?php echo htmlspecialchars($place["name"]); ?>

                            </h3>


                            <?php if (!empty($place["description"])): ?>

                                <p>

                                    <?php echo nl2br(
                                        htmlspecialchars($place["description"])
                                    ); ?>

                                </p>

                            <?php endif; ?>


                            <?php if (!empty($place["address"])): ?>

                                <p class="place-address">

                                    📍
                                    <?php echo htmlspecialchars($place["address"]); ?>

                                </p>

                            <?php endif; ?>


                            <div class="place-actions">

                                <?php if (isset($_SESSION["user_id"])): ?>

                                    <a
                                        href="save-place.php?place_id=<?php echo $place["place_id"]; ?>"
                                        class="secondary-button"
                                    >
                                        ♡ Save Place
                                    </a>

                                <?php else: ?>

                                    <a
                                        href="login.php"
                                        class="secondary-button"
                                    >
                                        Login to Save
                                    </a>

                                <?php endif; ?>

                            </div>

                        </div>

                    </article>

                <?php endforeach; ?>

            </div>


        <?php else: ?>

            <div class="no-places">

                <h3>
                    No places added yet
                </h3>

                <p>
                    Places for this destination will appear here
                    once they are added by the administrator.
                </p>

            </div>

        <?php endif; ?>

    </section>

</main>


<?php include "includes/footer.php"; ?>


</body>

</html>