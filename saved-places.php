<?php

require_once "includes/auth.php";
require_once "config/database.php";

$user_id = $_SESSION["user_id"];

/*
|--------------------------------------------------------------------------
| Get saved places
|--------------------------------------------------------------------------
*/

$stmt = $pdo->prepare("
    SELECT
        sp.saved_id,
        sp.saved_at,

        p.place_id,
        p.name AS place_name,
        p.description AS place_description,
        p.category,
        p.address,
        p.image AS place_image,

        d.destination_id,
        d.name AS destination_name,
        d.country,
        d.image AS destination_image

    FROM saved_places sp

    INNER JOIN places p
        ON sp.place_id = p.place_id

    INNER JOIN destinations d
        ON p.destination_id = d.destination_id

    WHERE sp.user_id = ?

    ORDER BY sp.saved_at DESC
");

$stmt->execute([$user_id]);

$saved_places = $stmt->fetchAll();

$total_saved = count($saved_places);

?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Saved Places | TripWise</title>

    <link rel="stylesheet" href="css/style.css">

</head>

<body>

<?php require_once "includes/header.php"; ?>


<main class="saved-places-page">

    <section class="saved-places-header">

        <div>

            <p class="page-label">YOUR COLLECTION</p>

            <h1>Saved Places</h1>

            <p class="saved-places-intro">
                Keep track of the places you want to visit on your future trips.
            </p>

        </div>

        <div class="saved-places-header-actions">

            <a href="destinations.php" class="primary-button">
                Explore Destinations
            </a>

        </div>

    </section>


    <!-- Summary -->

    <section class="saved-summary">

        <div class="saved-summary-card">

            <span class="saved-summary-label">
                SAVED PLACES
            </span>

            <strong>
                <?php echo $total_saved; ?>
            </strong>

            <p class="saved-summary-text">
                <?php if ($total_saved === 1): ?>

                    place saved for your travels.

                <?php else: ?>

                    places saved for your travels.

                <?php endif; ?>
            </p>

        </div>

    </section>


    <?php if ($total_saved > 0): ?>

        <section class="saved-places-grid">

            <?php foreach ($saved_places as $place): ?>

                <article class="saved-place-card">


                    <!-- Image -->

                    <div class="saved-place-image">

                        <?php if (!empty($place["place_image"])): ?>

                            <img
                                src="images/<?php echo htmlspecialchars($place["place_image"]); ?>"
                                alt="<?php echo htmlspecialchars($place["place_name"]); ?>"
                            >

                        <?php elseif (!empty($place["destination_image"])): ?>

                            <img
                                src="images/<?php echo htmlspecialchars($place["destination_image"]); ?>"
                                alt="<?php echo htmlspecialchars($place["destination_name"]); ?>"
                            >

                        <?php else: ?>

                            <div class="saved-place-placeholder">
                                No Image
                            </div>

                        <?php endif; ?>


                        <?php if (!empty($place["category"])): ?>

                            <span class="saved-place-category">
                                <?php echo htmlspecialchars($place["category"]); ?>
                            </span>

                        <?php endif; ?>

                    </div>


                    <!-- Content -->

                    <div class="saved-place-content">

                        <p class="saved-place-destination">

                            <?php echo htmlspecialchars($place["destination_name"]); ?>

                            ·

                            <?php echo htmlspecialchars($place["country"]); ?>

                        </p>


                        <h2>
                            <?php echo htmlspecialchars($place["place_name"]); ?>
                        </h2>


                        <?php if (!empty($place["place_description"])): ?>

                            <p class="saved-place-description">

                                <?php
                                echo htmlspecialchars(
                                    $place["place_description"]
                                );
                                ?>

                            </p>

                        <?php endif; ?>


                        <?php if (!empty($place["address"])): ?>

                            <p class="saved-place-address">

                                <?php echo htmlspecialchars($place["address"]); ?>

                            </p>

                        <?php endif; ?>


                        <div class="saved-place-actions">

                            <a
                                href="destination-details.php?id=<?php echo (int) $place["destination_id"]; ?>"
                                class="secondary-button"
                            >
                                View Destination
                            </a>


                            <a
                                href="remove-saved-place.php?place_id=<?php echo (int) $place["place_id"]; ?>"
                                class="delete-button"
                                onclick="return confirm('Are you sure you want to remove this place from your saved places?');"
                            >
                                Remove
                            </a>

                        </div>

                    </div>

                </article>

            <?php endforeach; ?>

        </section>


    <?php else: ?>

        <section class="saved-places-empty">

            <div class="saved-empty-icon">
                ♡
            </div>

            <h2>No saved places yet</h2>

            <p>
                Explore destinations and save places you would like to visit.
            </p>

            <a href="destinations.php" class="primary-button">
                Explore Destinations
            </a>

        </section>

    <?php endif; ?>


</main>


<?php require_once "includes/footer.php"; ?>

</body>

</html>