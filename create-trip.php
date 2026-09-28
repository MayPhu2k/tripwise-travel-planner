<?php

require_once "includes/auth.php";
require_once "config/database.php";

$user_id = $_SESSION["user_id"];

$errors = [];

$trip_name = "";
$destination_id = "";
$start_date = "";
$end_date = "";
$travelers = 1;
$budget = "";


/*
|--------------------------------------------------------------------------
| Get destination from URL
|--------------------------------------------------------------------------
*/

if (isset($_GET["destination_id"])) {

    $destination_id = (int)$_GET["destination_id"];

}


/*
|--------------------------------------------------------------------------
| Get destinations
|--------------------------------------------------------------------------
*/

$stmt = $pdo->query("
    SELECT
        destination_id,
        name,
        country,
        image
    FROM destinations
    ORDER BY country ASC, name ASC
");

$destinations = $stmt->fetchAll();


/*
|--------------------------------------------------------------------------
| Handle form submission
|--------------------------------------------------------------------------
*/

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $trip_name = trim($_POST["trip_name"] ?? "");
    $destination_id = (int)($_POST["destination_id"] ?? 0);
    $start_date = $_POST["start_date"] ?? "";
    $end_date = $_POST["end_date"] ?? "";
    $travelers = (int)($_POST["travelers"] ?? 1);
    $budget = $_POST["budget"] ?? "";


    /*
    |--------------------------------------------------------------------------
    | Validation
    |--------------------------------------------------------------------------
    */

    if ($trip_name === "") {

        $errors[] = "Please enter a trip name.";

    }


    if ($destination_id <= 0) {

        $errors[] = "Please select a destination.";

    }


    if ($start_date === "") {

        $errors[] = "Please select a start date.";

    }


    if ($end_date === "") {

        $errors[] = "Please select an end date.";

    }


    if ($start_date !== "" && $end_date !== "") {

        if ($end_date < $start_date) {

            $errors[] = "The end date cannot be before the start date.";

        }

    }


    if ($travelers < 1) {

        $errors[] = "Travelers must be at least 1.";

    }


    if ($budget === "" || !is_numeric($budget) || $budget < 0) {

        $errors[] = "Please enter a valid budget.";

    }


    /*
    |--------------------------------------------------------------------------
    | Check destination
    |--------------------------------------------------------------------------
    */

    if ($destination_id > 0) {

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


    /*
    |--------------------------------------------------------------------------
    | Insert trip
    |--------------------------------------------------------------------------
    */

    if (empty($errors)) {

        $stmt = $pdo->prepare("
            INSERT INTO trips
            (
                user_id,
                destination_id,
                trip_name,
                start_date,
                end_date,
                travelers,
                budget
            )
            VALUES
            (?, ?, ?, ?, ?, ?, ?)
        ");

        $stmt->execute([
            $user_id,
            $destination_id,
            $trip_name,
            $start_date,
            $end_date,
            $travelers,
            $budget
        ]);


        $trip_id = $pdo->lastInsertId();


        header("Location: itinerary.php?trip_id=" . $trip_id);
        exit;

    }

}

?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Create Trip - TripWise</title>

    <link rel="stylesheet" href="css/style.css">

</head>

<body>

<?php require_once "includes/header.php"; ?>


<main class="create-trip-page">


    <!-- Header -->

    <section class="create-trip-header">

        <p class="page-label">
            TRIPWISE PLANNER
        </p>

        <h1>
            Plan Your Trip
        </h1>

        <p>
            Create your journey and start organizing your perfect adventure.
        </p>

    </section>


    <!-- Form -->

    <section class="create-trip-container">


        <div class="create-trip-form-card">


            <?php if (!empty($errors)): ?>

                <div class="form-errors">

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


            <form method="POST">


                <!-- Trip Name -->

                <div class="form-group">

                    <label for="trip_name">
                        Trip Name
                    </label>

                    <input
                        type="text"
                        id="trip_name"
                        name="trip_name"
                        placeholder="e.g. Tokyo Adventure"
                        value="<?php echo htmlspecialchars($trip_name); ?>"
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
                                value="<?php echo $destination["destination_id"]; ?>"
                                <?php echo ((int)$destination_id === (int)$destination["destination_id"]) ? "selected" : ""; ?>
                            >

                                <?php echo htmlspecialchars($destination["name"]); ?>,
                                <?php echo htmlspecialchars($destination["country"]); ?>

                            </option>

                        <?php endforeach; ?>

                    </select>

                </div>


                <!-- Dates -->

                <div class="form-row">


                    <div class="form-group">

                        <label for="start_date">
                            Start Date
                        </label>

                        <input
                            type="date"
                            id="start_date"
                            name="start_date"
                            value="<?php echo htmlspecialchars($start_date); ?>"
                            required
                        >

                    </div>


                    <div class="form-group">

                        <label for="end_date">
                            End Date
                        </label>

                        <input
                            type="date"
                            id="end_date"
                            name="end_date"
                            value="<?php echo htmlspecialchars($end_date); ?>"
                            required
                        >

                    </div>


                </div>


                <!-- Travelers + Budget -->

                <div class="form-row">


                    <div class="form-group">

                        <label for="travelers">
                            Travelers
                        </label>

                        <input
                            type="number"
                            id="travelers"
                            name="travelers"
                            min="1"
                            value="<?php echo (int)$travelers; ?>"
                            required
                        >

                    </div>


                    <div class="form-group">

                        <label for="budget">
                            Budget (฿)
                        </label>

                        <input
                            type="number"
                            id="budget"
                            name="budget"
                            min="0"
                            step="0.01"
                            placeholder="e.g. 30000"
                            value="<?php echo htmlspecialchars($budget); ?>"
                            required
                        >

                    </div>


                </div>


                <!-- Buttons -->

                <div class="create-trip-buttons">

                    <a
                        href="my-trips.php"
                        class="secondary-button"
                    >
                        Cancel
                    </a>

                    <button
                        type="submit"
                        class="primary-button"
                    >
                        Create Trip
                    </button>

                </div>


            </form>


        </div>


        <!-- Information -->

        <aside class="create-trip-info">


            <div class="create-trip-info-icon">
                ✈️
            </div>

            <h2>
                Start Your Journey
            </h2>

            <p>
                Add your destination, travel dates, number of travelers,
                and estimated budget.
            </p>


            <div class="planning-steps">


                <div class="planning-step">

                    <span>
                        01
                    </span>

                    <div>

                        <strong>
                            Choose a destination
                        </strong>

                        <small>
                            Pick somewhere you want to explore.
                        </small>

                    </div>

                </div>


                <div class="planning-step">

                    <span>
                        02
                    </span>

                    <div>

                        <strong>
                            Set your dates
                        </strong>

                        <small>
                            Decide when your journey begins and ends.
                        </small>

                    </div>

                </div>


                <div class="planning-step">

                    <span>
                        03
                    </span>

                    <div>

                        <strong>
                            Set your budget
                        </strong>

                        <small>
                            Keep track of your expected travel spending.
                        </small>

                    </div>

                </div>


                <div class="planning-step">

                    <span>
                        04
                    </span>

                    <div>

                        <strong>
                            Build your itinerary
                        </strong>

                        <small>
                            Add places and activities for each day.
                        </small>

                    </div>

                </div>


            </div>


        </aside>


    </section>


</main>


<?php require_once "includes/footer.php"; ?>


</body>

</html>