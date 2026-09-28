<?php

require_once "includes/auth.php";
require_once "config/database.php";

$user_id = $_SESSION["user_id"];

$trip_id = isset($_GET["trip_id"]) ? (int) $_GET["trip_id"] : 0;

if ($trip_id <= 0) {
    header("Location: my-trips.php");
    exit;
}


/* =========================================================
   GET TRIP
========================================================= */

$stmt = $pdo->prepare("
    SELECT
        t.*,
        d.name AS destination_name,
        d.country,
        d.image AS destination_image
    FROM trips t
    INNER JOIN destinations d
        ON t.destination_id = d.destination_id
    WHERE t.trip_id = ?
      AND t.user_id = ?
");

$stmt->execute([$trip_id, $user_id]);

$trip = $stmt->fetch();

if (!$trip) {
    header("Location: my-trips.php");
    exit;
}


/* =========================================================
   EXPENSE CATEGORIES
========================================================= */

$categories = [
    "Transportation",
    "Accommodation",
    "Food",
    "Activities",
    "Shopping",
    "Other"
];


/* =========================================================
   ADD EXPENSE
========================================================= */

$errors = [];

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $category = trim($_POST["category"] ?? "");
    $description = trim($_POST["description"] ?? "");
    $amount = trim($_POST["amount"] ?? "");
    $expense_date = trim($_POST["expense_date"] ?? "");


    /* Category validation */

    if ($category === "") {
        $errors[] = "Please select an expense category.";
    } elseif (!in_array($category, $categories, true)) {
        $errors[] = "Please select a valid expense category.";
    }


    /* Amount validation */

    if ($amount === "") {

        $errors[] = "Please enter an amount.";

    } elseif (!is_numeric($amount)) {

        $errors[] = "Amount must be a valid number.";

    } elseif ((float) $amount < 0) {

        $errors[] = "Amount cannot be negative.";
    }


    /* Date validation */

    if ($expense_date === "") {

        $errors[] = "Please select an expense date.";

    } elseif (
        $expense_date < $trip["start_date"] ||
        $expense_date > $trip["end_date"]
    ) {

        $errors[] = "Expense date must be within your trip dates.";
    }


    /* Insert expense */

    if (empty($errors)) {

        $stmt = $pdo->prepare("
            INSERT INTO expenses
            (
                trip_id,
                category,
                description,
                amount,
                expense_date
            )
            VALUES (?, ?, ?, ?, ?)
        ");

        $stmt->execute([
            $trip_id,
            $category,
            $description !== "" ? $description : null,
            (float) $amount,
            $expense_date
        ]);

        header("Location: budget.php?trip_id=" . $trip_id);
        exit;
    }
}


/* =========================================================
   GET ALL EXPENSES
========================================================= */

$stmt = $pdo->prepare("
    SELECT *
    FROM expenses
    WHERE trip_id = ?
    ORDER BY expense_date DESC, expense_id DESC
");

$stmt->execute([$trip_id]);

$expenses = $stmt->fetchAll();


/* =========================================================
   TOTAL SPENT
========================================================= */

$total_spent = 0;

foreach ($expenses as $expense) {
    $total_spent += (float) $expense["amount"];
}


/* =========================================================
   BUDGET CALCULATIONS
========================================================= */

$total_budget = (float) $trip["budget"];

$remaining_budget = $total_budget - $total_spent;

if ($total_budget > 0) {

    $progress_percentage = ($total_spent / $total_budget) * 100;

} else {

    $progress_percentage = 0;
}

$progress_width = min($progress_percentage, 100);


/* =========================================================
   CATEGORY BREAKDOWN
========================================================= */

$category_totals = [];

foreach ($categories as $category) {
    $category_totals[$category] = 0;
}

foreach ($expenses as $expense) {

    $category = $expense["category"];

    if (isset($category_totals[$category])) {
        $category_totals[$category] += (float) $expense["amount"];
    }
}


/* =========================================================
   DATE FORMAT
========================================================= */

$start_date = date(
    "M d, Y",
    strtotime($trip["start_date"])
);

$end_date = date(
    "M d, Y",
    strtotime($trip["end_date"])
);

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
        Budget - <?php echo htmlspecialchars($trip["trip_name"]); ?> | TripWise
    </title>

    <link
        rel="stylesheet"
        href="css/style.css"
    >

</head>

<body>


<?php require_once "includes/header.php"; ?>


<main class="budget-page">


    <!-- =====================================================
         HEADER
    ====================================================== -->

    <section class="budget-header">

        <div>

            <p class="page-label">
                TRIP BUDGET
            </p>

            <h1>
                <?php echo htmlspecialchars($trip["trip_name"]); ?>
            </h1>

            <p class="budget-destination">

                <?php echo htmlspecialchars($trip["destination_name"]); ?>,
                <?php echo htmlspecialchars($trip["country"]); ?>

            </p>

            <p class="budget-dates">

                <?php echo $start_date; ?>
                -
                <?php echo $end_date; ?>

            </p>

        </div>


        <div class="budget-header-actions">

            <a
                href="itinerary.php?trip_id=<?php echo $trip_id; ?>"
                class="secondary-button"
            >
                View Itinerary
            </a>

            <a
                href="my-trips.php"
                class="secondary-button"
            >
                My Trips
            </a>

        </div>

    </section>



    <!-- =====================================================
         OVER BUDGET WARNING
    ====================================================== -->

    <?php if ($remaining_budget < 0): ?>

        <div class="budget-warning">

            <strong>
                Over budget
            </strong>

            <span>
                You have spent
                ฿<?php echo number_format(abs($remaining_budget), 2); ?>
                more than your planned budget.
            </span>

        </div>

    <?php endif; ?>



    <!-- =====================================================
         BUDGET OVERVIEW
    ====================================================== -->

    <section class="budget-overview">


        <div class="budget-stat-card">

            <span class="budget-stat-label">
                Total Budget
            </span>

            <strong>
                ฿<?php echo number_format($total_budget, 2); ?>
            </strong>

        </div>


        <div class="budget-stat-card">

            <span class="budget-stat-label">
                Total Spent
            </span>

            <strong>
                ฿<?php echo number_format($total_spent, 2); ?>
            </strong>

        </div>


        <div class="budget-stat-card">

            <span class="budget-stat-label">

                <?php if ($remaining_budget >= 0): ?>
                    Remaining
                <?php else: ?>
                    Over Budget
                <?php endif; ?>

            </span>

            <strong>

                ฿<?php
                echo number_format(
                    abs($remaining_budget),
                    2
                );
                ?>

            </strong>

        </div>


    </section>



    <!-- =====================================================
         PROGRESS BAR
    ====================================================== -->

    <section class="budget-progress-card">

        <div class="budget-progress-heading">

            <div>

                <p class="page-label">
                    SPENDING PROGRESS
                </p>

                <h2>
                    Budget Usage
                </h2>

            </div>

            <strong>
                <?php echo number_format($progress_percentage, 1); ?>%
            </strong>

        </div>


        <div class="budget-progress-bar">

            <div
                class="budget-progress-fill <?php echo $remaining_budget < 0 ? 'over-budget' : ''; ?>"
                style="width: <?php echo $progress_width; ?>%;"
            ></div>

        </div>


        <div class="budget-progress-text">

            <span>
                Spent:
                ฿<?php echo number_format($total_spent, 2); ?>
            </span>

            <span>
                Budget:
                ฿<?php echo number_format($total_budget, 2); ?>
            </span>

        </div>

    </section>



    <!-- =====================================================
         ADD EXPENSE + CATEGORY BREAKDOWN
    ====================================================== -->

    <section class="budget-content">


        <!-- ADD EXPENSE -->

        <div class="budget-add-card">

            <div class="budget-card-heading">

                <p class="page-label">
                    EXPENSE TRACKER
                </p>

                <h2>
                    Add an Expense
                </h2>

                <p>
                    Keep track of your spending during your trip.
                </p>

            </div>


            <?php if (!empty($errors)): ?>

                <div class="budget-errors">

                    <?php foreach ($errors as $error): ?>

                        <p>
                            <?php echo htmlspecialchars($error); ?>
                        </p>

                    <?php endforeach; ?>

                </div>

            <?php endif; ?>


            <form
                method="POST"
                class="budget-form"
            >


                <!-- Category -->

                <div class="form-group">

                    <label for="category">
                        Category
                    </label>

                    <select
                        name="category"
                        id="category"
                        required
                    >

                        <option value="">
                            Select category
                        </option>

                        <?php foreach ($categories as $category): ?>

                            <option
                                value="<?php echo htmlspecialchars($category); ?>"
                                <?php
                                echo (
                                    ($_POST["category"] ?? "") === $category
                                )
                                    ? "selected"
                                    : "";
                                ?>
                            >

                                <?php echo htmlspecialchars($category); ?>

                            </option>

                        <?php endforeach; ?>

                    </select>

                </div>



                <!-- Description -->

                <div class="form-group">

                    <label for="description">
                        Description
                    </label>

                    <input
                        type="text"
                        name="description"
                        id="description"
                        placeholder="e.g. Hotel, dinner, taxi"
                        value="<?php echo htmlspecialchars($_POST["description"] ?? ""); ?>"
                    >

                </div>



                <!-- Amount + Date -->

                <div class="form-row">


                    <div class="form-group">

                        <label for="amount">
                            Amount (฿)
                        </label>

                        <input
                            type="number"
                            name="amount"
                            id="amount"
                            step="0.01"
                            min="0"
                            placeholder="0.00"
                            value="<?php echo htmlspecialchars($_POST["amount"] ?? ""); ?>"
                            required
                        >

                    </div>


                    <div class="form-group">

                        <label for="expense_date">
                            Date
                        </label>

                        <input
                            type="date"
                            name="expense_date"
                            id="expense_date"
                            min="<?php echo htmlspecialchars($trip["start_date"]); ?>"
                            max="<?php echo htmlspecialchars($trip["end_date"]); ?>"
                            value="<?php echo htmlspecialchars($_POST["expense_date"] ?? $trip["start_date"]); ?>"
                            required
                        >

                    </div>


                </div>



                <button
                    type="submit"
                    class="primary-button budget-submit"
                >
                    Add Expense
                </button>


            </form>

        </div>



        <!-- CATEGORY BREAKDOWN -->

        <div class="budget-breakdown-card">

            <div class="budget-card-heading">

                <p class="page-label">
                    SPENDING BREAKDOWN
                </p>

                <h2>
                    By Category
                </h2>

                <p>
                    See where your travel budget is going.
                </p>

            </div>


            <?php if ($total_spent > 0): ?>

                <div class="category-list">

                    <?php foreach ($categories as $category): ?>

                        <?php

                        $category_amount =
                            $category_totals[$category];

                        if ($total_spent > 0) {
                            $category_percentage =
                                ($category_amount / $total_spent) * 100;
                        } else {
                            $category_percentage = 0;
                        }

                        ?>

                        <div class="category-item">


                            <div class="category-item-top">

                                <span>
                                    <?php echo htmlspecialchars($category); ?>
                                </span>

                                <strong>
                                    ฿<?php echo number_format($category_amount, 2); ?>
                                </strong>

                            </div>


                            <div class="category-bar">

                                <div
                                    class="category-bar-fill"
                                    style="width: <?php echo $category_percentage; ?>%;"
                                ></div>

                            </div>


                            <small>
                                <?php echo number_format($category_percentage, 1); ?>%
                                of total spending
                            </small>


                        </div>

                    <?php endforeach; ?>

                </div>

            <?php else: ?>

                <div class="budget-empty-small">

                    <p>
                        No expenses yet.
                    </p>

                    <span>
                        Add your first expense to see the breakdown.
                    </span>

                </div>

            <?php endif; ?>


        </div>


    </section>



    <!-- =====================================================
         EXPENSE HISTORY
    ====================================================== -->

    <section class="expense-history">


        <div class="expense-history-header">

            <div>

                <p class="page-label">
                    EXPENSE HISTORY
                </p>

                <h2>
                    Your Expenses
                </h2>

            </div>


            <span class="expense-count">

                <?php echo count($expenses); ?>

                <?php
                echo count($expenses) === 1
                    ? "expense"
                    : "expenses";
                ?>

            </span>

        </div>



        <?php if (!empty($expenses)): ?>


            <div class="expense-list">


                <?php foreach ($expenses as $expense): ?>


                    <div class="expense-item">


                        <div class="expense-icon">

                            <?php

                            $icons = [
                                "Transportation" => "T",
                                "Accommodation" => "A",
                                "Food" => "F",
                                "Activities" => "A",
                                "Shopping" => "S",
                                "Other" => "O"
                            ];

                            echo $icons[$expense["category"]] ?? "E";

                            ?>

                        </div>



                        <div class="expense-info">


                            <div class="expense-main">

                                <h3>

                                    <?php

                                    if (!empty($expense["description"])) {

                                        echo htmlspecialchars(
                                            $expense["description"]
                                        );

                                    } else {

                                        echo htmlspecialchars(
                                            $expense["category"]
                                        );

                                    }

                                    ?>

                                </h3>


                                <span class="expense-category">

                                    <?php echo htmlspecialchars(
                                        $expense["category"]
                                    ); ?>

                                </span>

                            </div>


                            <span class="expense-date">

                                <?php echo date(
                                    "M d, Y",
                                    strtotime($expense["expense_date"])
                                ); ?>

                            </span>


                        </div>



                        <strong class="expense-amount">

                            ฿<?php echo number_format(
                                (float) $expense["amount"],
                                2
                            ); ?>

                        </strong>


                    </div>


                <?php endforeach; ?>


            </div>


        <?php else: ?>


            <div class="expense-empty">

                <div class="expense-empty-icon">
                    ฿
                </div>

                <h3>
                    No expenses yet
                </h3>

                <p>
                    Start adding your travel expenses to keep your budget organized.
                </p>

            </div>


        <?php endif; ?>


    </section>


</main>


<?php require_once "includes/footer.php"; ?>


</body>

</html>