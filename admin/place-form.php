<?php

require_once "../includes/admin-auth.php";
require_once "../config/database.php";

$is_edit = false;
$place_id = 0;

$name = "";
$destination_id = "";
$description = "";
$category = "";
$address = "";
$image = "";

$errors = [];

/* Check if editing */
if (isset($_GET["id"])) {

    $place_id = (int) $_GET["id"];

    if ($place_id > 0) {

        $stmt = $pdo->prepare("
            SELECT
                place_id,
                destination_id,
                name,
                description,
                category,
                address,
                image
            FROM places
            WHERE place_id = ?
        ");

        $stmt->execute([$place_id]);

        $place = $stmt->fetch();

        if ($place) {

            $is_edit = true;

            $name = $place["name"];
            $destination_id = $place["destination_id"];
            $description = $place["description"];
            $category = $place["category"];
            $address = $place["address"];
            $image = $place["image"];

        } else {

            header("Location: places.php");
            exit;
        }
    }
}


/* Get destinations */
$stmt = $pdo->query("
    SELECT
        destination_id,
        name,
        country
    FROM destinations
    ORDER BY name ASC
");

$destinations = $stmt->fetchAll();


/* Handle form submission */
if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $name = trim($_POST["name"] ?? "");
    $destination_id = (int) ($_POST["destination_id"] ?? 0);
    $description = trim($_POST["description"] ?? "");
    $category = trim($_POST["category"] ?? "");
    $address = trim($_POST["address"] ?? "");
    $image = trim($_POST["image"] ?? "");


    /* Validate name */
    if ($name === "") {
        $errors[] = "Place name is required.";
    }


    /* Validate destination */
    if ($destination_id <= 0) {

        $errors[] = "Please select a destination.";

    } else {

        $stmt = $pdo->prepare("
            SELECT destination_id
            FROM destinations
            WHERE destination_id = ?
        ");

        $stmt->execute([$destination_id]);

        if (!$stmt->fetch()) {
            $errors[] = "The selected destination does not exist.";
        }
    }


    /* Validate category */
    if ($category === "") {
        $errors[] = "Category is required.";
    }


    /* Validate description */
    if ($description === "") {
        $errors[] = "Description is required.";
    }


    /* Insert / Update */
    if (empty($errors)) {

        if ($is_edit) {

            $stmt = $pdo->prepare("
                UPDATE places
                SET
                    destination_id = ?,
                    name = ?,
                    description = ?,
                    category = ?,
                    address = ?,
                    image = ?
                WHERE place_id = ?
            ");

            $stmt->execute([
                $destination_id,
                $name,
                $description,
                $category,
                $address,
                $image,
                $place_id
            ]);

        } else {

            $stmt = $pdo->prepare("
                INSERT INTO places (
                    destination_id,
                    name,
                    description,
                    category,
                    address,
                    image
                )
                VALUES (?, ?, ?, ?, ?, ?)
            ");

            $stmt->execute([
                $destination_id,
                $name,
                $description,
                $category,
                $address,
                $image
            ]);
        }

        header("Location: places.php");
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

    <title>
        <?php echo $is_edit ? "Edit Place" : "Add Place"; ?> | TripWise
    </title>

    <link
        rel="stylesheet"
        href="../css/style.css"
    >

</head>

<body>

<?php require_once "../includes/header.php"; ?>


<main class="admin-form-page">

    <section class="admin-form-header">

        <div>

            <p class="page-label">ADMIN PANEL</p>

            <h1>
                <?php echo $is_edit ? "Edit Place" : "Add Place"; ?>
            </h1>

            <p class="admin-form-intro">
                <?php
                echo $is_edit
                    ? "Update the information for this place."
                    : "Add a new place to a TripWise destination.";
                ?>
            </p>

        </div>

        <div>

            <a
                href="places.php"
                class="secondary-button"
            >
                ← Back to Places
            </a>

        </div>

    </section>


    <?php if (!empty($errors)): ?>

        <section class="admin-form-errors">

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

        </section>

    <?php endif; ?>


    <section class="admin-form-card">

        <form
            method="POST"
            class="admin-form"
        >

            <!-- Place Name -->

            <div class="form-group">

                <label for="name">
                    Place Name
                </label>

                <input
                    type="text"
                    id="name"
                    name="name"
                    value="<?php echo htmlspecialchars($name); ?>"
                    placeholder="e.g. Wat Arun"
                    required
                >

            </div>


            <!-- Destination -->

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
                        Select a destination
                    </option>

                    <?php foreach ($destinations as $destination): ?>

                        <option
                            value="<?php echo (int) $destination["destination_id"]; ?>"
                            <?php
                            echo (
                                (int) $destination_id ===
                                (int) $destination["destination_id"]
                            )
                                ? "selected"
                                : "";
                            ?>
                        >

                            <?php
                            echo htmlspecialchars(
                                $destination["name"]
                            );
                            ?>

                            -
                            <?php
                            echo htmlspecialchars(
                                $destination["country"]
                            );
                            ?>

                        </option>

                    <?php endforeach; ?>

                </select>

            </div>


            <!-- Category -->

            <div class="form-group">

                <label for="category">
                    Category
                </label>

                <select
                    id="category"
                    name="category"
                    required
                >

                    <option value="">
                        Select a category
                    </option>

                    <?php
                    $categories = [
                        "Attraction",
                        "Beach",
                        "Nature",
                        "Historical",
                        "Cultural",
                        "Shopping",
                        "Food",
                        "Entertainment",
                        "Religious",
                        "Museum",
                        "Other"
                    ];
                    ?>

                    <?php foreach ($categories as $item): ?>

                        <option
                            value="<?php echo htmlspecialchars($item); ?>"
                            <?php
                            echo ($category === $item)
                                ? "selected"
                                : "";
                            ?>
                        >

                            <?php echo htmlspecialchars($item); ?>

                        </option>

                    <?php endforeach; ?>

                </select>

            </div>


            <!-- Description -->

            <div class="form-group">

                <label for="description">
                    Description
                </label>

                <textarea
                    id="description"
                    name="description"
                    rows="6"
                    placeholder="Describe this place..."
                    required
                ><?php echo htmlspecialchars($description); ?></textarea>

            </div>


            <!-- Address -->

            <div class="form-group">

                <label for="address">
                    Address
                    <span class="form-optional">
                        Optional
                    </span>
                </label>

                <input
                    type="text"
                    id="address"
                    name="address"
                    value="<?php echo htmlspecialchars($address); ?>"
                    placeholder="e.g. Bangkok, Thailand"
                >

            </div>


            <!-- Image -->

            <div class="form-group">

                <label for="image">
                    Image Filename
                    <span class="form-optional">
                        Optional
                    </span>
                </label>

                <input
                    type="text"
                    id="image"
                    name="image"
                    value="<?php echo htmlspecialchars($image); ?>"
                    placeholder="e.g. wat-arun.jpeg"
                >

                <small class="form-help">
                    Enter the image filename exactly as it appears
                    inside the images folder.
                </small>

            </div>


            <!-- Buttons -->

            <div class="admin-form-actions">

                <a
                    href="places.php"
                    class="secondary-button"
                >
                    Cancel
                </a>

                <button
                    type="submit"
                    class="primary-button"
                >

                    <?php
                    echo $is_edit
                        ? "Update Place"
                        : "Add Place";
                    ?>

                </button>

            </div>

        </form>

    </section>

</main>


<?php require_once "../includes/footer.php"; ?>

</body>

</html>