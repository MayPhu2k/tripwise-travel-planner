<?php

require_once "config/database.php";

/*
|--------------------------------------------------------------------------
| Get filter values
|--------------------------------------------------------------------------
*/

$search = trim($_GET["search"] ?? "");
$country = trim($_GET["country"] ?? "");
$category = trim($_GET["category"] ?? "");


/*
|--------------------------------------------------------------------------
| Get available countries
|--------------------------------------------------------------------------
*/

$countryStmt = $pdo->query("
    SELECT DISTINCT country
    FROM destinations
    WHERE country IS NOT NULL
    AND country != ''
    ORDER BY country
");

$countries = $countryStmt->fetchAll();


/*
|--------------------------------------------------------------------------
| Get available categories
|--------------------------------------------------------------------------
*/

$categoryStmt = $pdo->query("
    SELECT DISTINCT category
    FROM destinations
    WHERE category IS NOT NULL
    AND category != ''
    ORDER BY category
");

$categories = $categoryStmt->fetchAll();


/*
|--------------------------------------------------------------------------
| Build destination query
|--------------------------------------------------------------------------
*/

$sql = "
    SELECT *
    FROM destinations
    WHERE 1=1
";

$params = [];


/*
|--------------------------------------------------------------------------
| Search
|--------------------------------------------------------------------------
*/

if ($search !== "") {

    $sql .= "
        AND (
            name LIKE ?
            OR country LIKE ?
            OR description LIKE ?
        )
    ";

    $searchTerm = "%$search%";

    $params[] = $searchTerm;
    $params[] = $searchTerm;
    $params[] = $searchTerm;
}


/*
|--------------------------------------------------------------------------
| Country filter
|--------------------------------------------------------------------------
*/

if ($country !== "") {

    $sql .= " AND country = ?";

    $params[] = $country;
}


/*
|--------------------------------------------------------------------------
| Category filter
|--------------------------------------------------------------------------
*/

if ($category !== "") {

    $sql .= " AND category = ?";

    $params[] = $category;
}


/*
|--------------------------------------------------------------------------
| Order results
|--------------------------------------------------------------------------
*/

$sql .= " ORDER BY name";


/*
|--------------------------------------------------------------------------
| Execute query
|--------------------------------------------------------------------------
*/

$stmt = $pdo->prepare($sql);
$stmt->execute($params);

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

    <title>TripWise | Destinations</title>

    <link
        rel="stylesheet"
        href="css/style.css"
    >

</head>

<body>

<?php include "includes/header.php"; ?>


<main class="destinations-page">

    <!-- Page Header -->

    <section class="page-header">

        <h1>Explore Destinations</h1>

        <p>
            Discover amazing places around the world and start planning
            your next adventure with TripWise.
        </p>

    </section>


    <!-- Search & Filters -->

    <section class="destination-filters">

        <form
            method="GET"
            action="destinations.php"
            class="filter-form"
        >

            <!-- Search -->

            <div class="filter-group">

                <label for="search">
                    Search
                </label>

                <input
                    type="text"
                    id="search"
                    name="search"
                    placeholder="Search destinations..."
                    value="<?php echo htmlspecialchars($search); ?>"
                >

            </div>


            <!-- Country -->

            <div class="filter-group">

                <label for="country">
                    Country
                </label>

                <select
                    id="country"
                    name="country"
                >

                    <option value="">
                        All Countries
                    </option>

                    <?php foreach ($countries as $countryOption): ?>

                        <option
                            value="<?php echo htmlspecialchars($countryOption["country"]); ?>"
                            <?php echo ($country === $countryOption["country"]) ? "selected" : ""; ?>
                        >
                            <?php echo htmlspecialchars($countryOption["country"]); ?>
                        </option>

                    <?php endforeach; ?>

                </select>

            </div>


            <!-- Category -->

            <div class="filter-group">

                <label for="category">
                    Category
                </label>

                <select
                    id="category"
                    name="category"
                >

                    <option value="">
                        All Categories
                    </option>

                    <?php foreach ($categories as $cat): ?>

                        <option
                            value="<?php echo htmlspecialchars($cat["category"]); ?>"
                            <?php echo ($category === $cat["category"]) ? "selected" : ""; ?>
                        >
                            <?php echo htmlspecialchars($cat["category"]); ?>
                        </option>

                    <?php endforeach; ?>

                </select>

            </div>


            <!-- Buttons -->

            <div class="filter-buttons">

                <button
                    type="submit"
                    class="primary-button"
                >
                    Search
                </button>

                <a
                    href="destinations.php"
                    class="secondary-button"
                >
                    Clear
                </a>

            </div>

        </form>

    </section>


    <!-- Results -->

    <section class="destinations-section">

        <div class="section-header">

            <h2>
                <?php echo count($destinations); ?>
                Destination<?php echo count($destinations) !== 1 ? "s" : ""; ?>
            </h2>

        </div>


        <?php if (!empty($destinations)): ?>

            <div class="destination-grid">

                <?php foreach ($destinations as $destination): ?>

                    <article class="destination-card">

                        <!-- Image -->

                        <?php if (!empty($destination["image"])): ?>

                            <img
                                src="images/<?php echo htmlspecialchars($destination["image"]); ?>"
                                alt="<?php echo htmlspecialchars($destination["name"]); ?>"
                                class="destination-image"
                            >

                        <?php else: ?>

                            <div class="destination-image placeholder-image">
                                No Image
                            </div>

                        <?php endif; ?>


                        <!-- Content -->

                        <div class="destination-content">

                            <span class="destination-category">
                                <?php echo htmlspecialchars($destination["category"]); ?>
                            </span>


                            <h3>
                                <?php echo htmlspecialchars($destination["name"]); ?>
                            </h3>


                            <p class="destination-country">
                                <?php echo htmlspecialchars($destination["country"]); ?>
                            </p>


                            <p class="destination-description">

                                <?php

                                $description = $destination["description"];

                                if (strlen($description) > 120) {
                                    $description = substr($description, 0, 120) . "...";
                                }

                                echo htmlspecialchars($description);

                                ?>

                            </p>


                            <div class="destination-card-footer">

                                <span class="destination-cost">

                                    From ฿<?php
                                    echo number_format(
                                        $destination["estimated_daily_cost"],
                                        0
                                    );
                                    ?>

                                    /day

                                </span>


                                <a
                                    href="destination-details.php?id=<?php echo $destination["destination_id"]; ?>"
                                    class="primary-button"
                                >
                                    Explore
                                </a>

                            </div>

                        </div>

                    </article>

                <?php endforeach; ?>

            </div>


        <?php else: ?>

            <div class="no-results">

                <h3>No destinations found</h3>

                <p>
                    Try changing your search or filters.
                </p>

                <a
                    href="destinations.php"
                    class="primary-button"
                >
                    View All Destinations
                </a>

            </div>

        <?php endif; ?>

    </section>

</main>


<?php include "includes/footer.php"; ?>


</body>

</html>