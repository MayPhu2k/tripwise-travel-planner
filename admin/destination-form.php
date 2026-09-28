<?php

require_once "../includes/admin-auth.php";
require_once "../config/database.php";

/*
|--------------------------------------------------------------------------
| Determine Add or Edit
|--------------------------------------------------------------------------
*/

$destination_id = isset($_GET["id"])
    ? (int) $_GET["id"]
    : 0;

$is_edit = $destination_id > 0;


/*
|--------------------------------------------------------------------------
| Default Values
|--------------------------------------------------------------------------
*/

$name = "";
$country = "";
$description = "";
$category = "";
$image = "";
$estimated_daily_cost = "";

$errors = [];


/*
|--------------------------------------------------------------------------
| Get Existing Destination
|--------------------------------------------------------------------------
*/

if ($is_edit) {

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

    $name = $destination["name"];
    $country = $destination["country"];
    $description = $destination["description"];
    $category = $destination["category"];
    $image = $destination["image"];
    $estimated_daily_cost = $destination["estimated_daily_cost"];
}


/*
|--------------------------------------------------------------------------
| Form Submission
|--------------------------------------------------------------------------
*/

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $name = trim($_POST["name"] ?? "");
    $country = trim($_POST["country"] ?? "");
    $description = trim($_POST["description"] ?? "");
    $category = trim($_POST["category"] ?? "");
    $image = trim($_POST["image"] ?? "");
    $estimated_daily_cost = trim(
        $_POST["estimated_daily_cost"] ?? ""
    );


    /*
    |--------------------------------------------------------------------------
    | Validation
    |--------------------------------------------------------------------------
    */

    if ($name === "") {

        $errors[] = "Destination name is required.";

    }


    if ($country === "") {

        $errors[] = "Country is required.";

    }


    if ($description === "") {

        $errors[] = "Description is required.";

    }


    if ($category === "") {

        $errors[] = "Category is required.";

    }


    if ($estimated_daily_cost === "") {

        $errors[] = "Estimated daily cost is required.";

    } elseif (
        !is_numeric($estimated_daily_cost)
        || (float) $estimated_daily_cost < 0
    ) {

        $errors[] =
            "Estimated daily cost must be a valid non-negative number.";

    }


    /*
    |--------------------------------------------------------------------------
    | Save
    |--------------------------------------------------------------------------
    */

    if (empty($errors)) {

        if ($is_edit) {

            $stmt = $pdo->prepare("
                UPDATE destinations

                SET
                    name = ?,
                    country = ?,
                    description = ?,
                    category = ?,
                    image = ?,
                    estimated_daily_cost = ?

                WHERE destination_id = ?
            ");

            $stmt->execute([
                $name,
                $country,
                $description,
                $category,
                $image,
                (float) $estimated_daily_cost,
                $destination_id
            ]);

        } else {

            $stmt = $pdo->prepare("
                INSERT INTO destinations
                (
                    name,
                    country,
                    description,
                    category,
                    image,
                    estimated_daily_cost
                )

                VALUES (?, ?, ?, ?, ?, ?)
            ");

            $stmt->execute([
                $name,
                $country,
                $description,
                $category,
                $image,
                (float) $estimated_daily_cost
            ]);
        }


        header("Location: destinations.php");
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

        <?php
        echo $is_edit
            ? "Edit Destination"
            : "Add Destination";
        ?>

        | TripWise

    </title>

    <link rel="stylesheet" href="../css/style.css">

</head>

<body>

<?php require_once "../includes/header.php"; ?>


<main class="admin-form-page">


    <!-- Header -->

    <section class="admin-form-header">

        <div>

            <p class="page-label">
                ADMIN PANEL
            </p>

            <h1>

                <?php

                echo $is_edit
                    ? "Edit Destination"
                    : "Add Destination";

                ?>

            </h1>

            <p>
                <?php

                echo $is_edit
                    ? "Update the destination information below."
                    : "Add a new destination to TripWise.";

                ?>
            </p>

        </div>


        <a
            href="destinations.php"
            class="secondary-button"
        >
            ← Back to Destinations
        </a>

    </section>


    <!-- Form -->

    <section class="admin-form-card">


        <?php if (!empty($errors)): ?>

            <div class="admin-form-errors">

                <?php foreach ($errors as $error): ?>

                    <p>
                        <?php echo htmlspecialchars($error); ?>
                    </p>

                <?php endforeach; ?>

            </div>

        <?php endif; ?>


        <form
            method="POST"
            class="admin-form"
        >


            <!-- Name -->

            <div class="form-group">

                <label for="name">
                    Destination Name
                </label>

                <input
                    type="text"
                    id="name"
                    name="name"
                    value="<?php echo htmlspecialchars($name); ?>"
                    placeholder="e.g. Bangkok"
                    required
                >

            </div>


            <!-- Country -->

            <div class="form-group">

                <label for="country">
                    Country
                </label>

                <input
                    type="text"
                    id="country"
                    name="country"
                    value="<?php echo htmlspecialchars($country); ?>"
                    placeholder="e.g. Thailand"
                    required
                >

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

                    <option
                        value="City"
                        <?php echo $category === "City" ? "selected" : ""; ?>
                    >
                        City
                    </option>

                    <option
                        value="Beach"
                        <?php echo $category === "Beach" ? "selected" : ""; ?>
                    >
                        Beach
                    </option>

                    <option
                        value="Nature"
                        <?php echo $category === "Nature" ? "selected" : ""; ?>
                    >
                        Nature
                    </option>

                    <option
                        value="Historical"
                        <?php echo $category === "Historical" ? "selected" : ""; ?>
                    >
                        Historical
                    </option>

                    <option
                        value="Cultural"
                        <?php echo $category === "Cultural" ? "selected" : ""; ?>
                    >
                        Cultural
                    </option>

                    <option
                        value="International"
                        <?php echo $category === "International" ? "selected" : ""; ?>
                    >
                        International
                    </option>

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
                    placeholder="Write a short description of this destination..."
                    required
                ><?php echo htmlspecialchars($description); ?></textarea>

            </div>


            <!-- Image -->

            <div class="form-group">

                <label for="image">
                    Image Filename
                </label>

                <input
                    type="text"
                    id="image"
                    name="image"
                    value="<?php echo htmlspecialchars($image); ?>"
                    placeholder="e.g. bangkok.jpg"
                >

                <small>
                    Enter the image filename exactly as it appears inside
                    the <strong>images</strong> folder.
                </small>

            </div>


            <!-- Cost -->

            <div class="form-group">

                <label for="estimated_daily_cost">
                    Estimated Daily Cost
                </label>

                <input
                    type="number"
                    id="estimated_daily_cost"
                    name="estimated_daily_cost"
                    value="<?php echo htmlspecialchars($estimated_daily_cost); ?>"
                    min="0"
                    step="0.01"
                    placeholder="e.g. 1500"
                    required
                >

                <small>
                    Enter the estimated daily cost in Thai Baht.
                </small>

            </div>


            <!-- Buttons -->

            <div class="admin-form-actions">

                <a
                    href="destinations.php"
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
                        ? "Update Destination"
                        : "Add Destination";

                    ?>

                </button>

            </div>


        </form>

    </section>


</main>


<?php require_once "../includes/footer.php"; ?>

</body>

</html>