<?php

require_once "../includes/admin-auth.php";
require_once "../config/database.php";

/*
|--------------------------------------------------------------------------
| Delete Place
|--------------------------------------------------------------------------
*/

if (isset($_GET["delete"])) {

    $place_id = (int) $_GET["delete"];

    if ($place_id > 0) {

        $stmt = $pdo->prepare("
            DELETE FROM places
            WHERE place_id = ?
        ");

        $stmt->execute([$place_id]);
    }

    header("Location: places.php");
    exit;
}


/*
|--------------------------------------------------------------------------
| Get Places
|--------------------------------------------------------------------------
*/

$stmt = $pdo->query("
    SELECT
        p.place_id,
        p.name,
        p.description,
        p.category,
        p.address,
        p.image,
        p.destination_id,

        d.name AS destination_name,
        d.country AS destination_country

    FROM places p

    INNER JOIN destinations d
        ON p.destination_id = d.destination_id

    ORDER BY
        d.name ASC,
        p.name ASC
");

$places = $stmt->fetchAll();

?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>Manage Places | TripWise</title>

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
                Places
            </h1>

            <p class="admin-intro">
                Manage attractions, activities, and other places
                available for TripWise users.
            </p>

        </div>


        <div class="admin-header-action">

            <a
                href="place-form.php"
                class="primary-button"
            >
                + Add Place
            </a>

        </div>

    </section>


    <!-- Summary -->

    <section class="admin-summary">

        <div class="admin-summary-card">

            <span class="admin-summary-label">
                TOTAL PLACES
            </span>

            <strong>
                <?php echo count($places); ?>
            </strong>

        </div>

    </section>


    <?php if (count($places) > 0): ?>


        <!-- Places Grid -->

        <section class="admin-place-grid">

            <?php foreach ($places as $place): ?>

                <article class="admin-place-card">


                    <!-- Image -->

                    <div class="admin-place-image">

                        <?php if (!empty($place["image"])): ?>

                            <img
                                src="../images/<?php echo htmlspecialchars($place["image"]); ?>"
                                alt="<?php echo htmlspecialchars($place["name"]); ?>"
                            >

                        <?php else: ?>

                            <div class="admin-place-placeholder">
                                No Image
                            </div>

                        <?php endif; ?>


                        <?php if (!empty($place["category"])): ?>

                            <span class="admin-place-category">

                                <?php echo htmlspecialchars($place["category"]); ?>

                            </span>

                        <?php endif; ?>

                    </div>


                    <!-- Content -->

                    <div class="admin-place-content">


                        <p class="admin-place-destination">

                            <?php echo htmlspecialchars($place["destination_name"]); ?>

                            ·

                            <?php echo htmlspecialchars($place["destination_country"]); ?>

                        </p>


                        <h2>

                            <?php echo htmlspecialchars($place["name"]); ?>

                        </h2>


                        <?php if (!empty($place["description"])): ?>

                            <p class="admin-place-description">

                                <?php

                                echo htmlspecialchars(
                                    $place["description"]
                                );

                                ?>

                            </p>

                        <?php endif; ?>


                        <?php if (!empty($place["address"])): ?>

                            <p class="admin-place-address">

                                <?php echo htmlspecialchars($place["address"]); ?>

                            </p>

                        <?php endif; ?>


                        <!-- Actions -->

                        <div class="admin-place-actions">

                            <a
                                href="place-form.php?id=<?php echo (int) $place["place_id"]; ?>"
                                class="secondary-button"
                            >
                                Edit
                            </a>


                            <a
                                href="places.php?delete=<?php echo (int) $place["place_id"]; ?>"
                                class="delete-button"
                                onclick="return confirm('Are you sure you want to delete this place?');"
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
                No places yet
            </h2>

            <p>
                Add your first place to TripWise.
            </p>

            <a
                href="place-form.php"
                class="primary-button"
            >
                + Add Place
            </a>

        </section>


    <?php endif; ?>


</main>


<?php require_once "../includes/footer.php"; ?>

</body>

</html>