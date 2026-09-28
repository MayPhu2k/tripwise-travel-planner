<?php

require_once "config/database.php";

$stmt = $pdo->query("
    SELECT *
    FROM destinations
    ORDER BY destination_id DESC
    LIMIT 6
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

    <title>TripWise | Smart Travel Planning</title>

    <link
        rel="stylesheet"
        href="css/style.css"
    >

</head>

<body>

<?php include "includes/header.php"; ?>


<!-- HERO -->

<section class="hero">

    <div class="hero-content">

        <p class="hero-tagline">
            PLAN • DISCOVER • TRAVEL
        </p>

        <h1>
            Plan Your Journey<br>
            with <span>TripWise</span>
        </h1>

        <p class="hero-description">
            Discover amazing destinations around the world,
            create personalized itineraries, and manage your
            travel budget in one place.
        </p>

        <div class="hero-buttons">

            <a
                href="destinations.php"
                class="primary-button"
            >
                Explore Destinations
            </a>

            <?php if (isset($_SESSION["user_id"])): ?>

                <a
                    href="create-trip.php"
                    class="secondary-button"
                >
                    Plan a Trip
                </a>

            <?php else: ?>

                <a
                    href="register.php"
                    class="secondary-button"
                >
                    Get Started
                </a>

            <?php endif; ?>

        </div>

    </div>

</section>


<!-- FEATURES -->

<section class="features-section">

    <div class="section-header">

        <h2>
            Everything You Need for Your Trip
        </h2>

        <p>
            TripWise helps you organize your travel
            from inspiration to itinerary.
        </p>

    </div>


    <div class="features-grid">


        <div class="feature-card">

            <div class="feature-icon">
                🌎
            </div>

            <h3>
                Discover Destinations
            </h3>

            <p>
                Explore destinations, attractions,
                restaurants, shopping areas, and
                experiences around the world.
            </p>

        </div>


        <div class="feature-card">

            <div class="feature-icon">
                📅
            </div>

            <h3>
                Plan Your Itinerary
            </h3>

            <p>
                Organize your activities by date
                and time to create a personalized
                travel schedule.
            </p>

        </div>


        <div class="feature-card">

            <div class="feature-icon">
                💰
            </div>

            <h3>
                Manage Your Budget
            </h3>

            <p>
                Set a travel budget and track your
                expenses throughout your trip.
            </p>

        </div>


        <div class="feature-card">

            <div class="feature-icon">
                ❤️
            </div>

            <h3>
                Save Your Favorite Places
            </h3>

            <p>
                Save interesting places and easily
                find them again when planning your trip.
            </p>

        </div>

    </div>

</section>


<!-- FEATURED DESTINATIONS -->

<section class="featured-section">

    <div class="section-header">

        <h2>
            Explore the World
        </h2>

        <p>
            Find inspiration for your next adventure.
        </p>

    </div>


    <?php if (!empty($destinations)): ?>

        <div class="destination-grid">

            <?php foreach ($destinations as $destination): ?>

                <article class="destination-card">

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


                    <div class="destination-content">

                        <span class="destination-category">

                            <?php
                            echo htmlspecialchars(
                                $destination["category"]
                            );
                            ?>

                        </span>


                        <h3>

                            <?php
                            echo htmlspecialchars(
                                $destination["name"]
                            );
                            ?>

                        </h3>


                        <p class="destination-country">

                            <?php
                            echo htmlspecialchars(
                                $destination["country"]
                            );
                            ?>

                        </p>


                        <p class="destination-description">

                            <?php

                            $description =
                                $destination["description"];

                            if (strlen($description) > 100) {

                                $description =
                                    substr($description, 0, 100)
                                    . "...";

                            }

                            echo htmlspecialchars($description);

                            ?>

                        </p>


                        <div class="destination-card-footer">

                            <span class="destination-cost">

                                From ฿<?php

                                echo number_format(
                                    $destination[
                                        "estimated_daily_cost"
                                    ],
                                    0
                                );

                                ?>/day

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


        <div class="view-all-container">

            <a
                href="destinations.php"
                class="secondary-button"
            >
                View All Destinations
            </a>

        </div>

    <?php endif; ?>

</section>


<!-- CALL TO ACTION -->

<section class="cta-section">

    <div class="cta-content">

        <h2>
            Ready to Plan Your Next Adventure?
        </h2>

        <p>
            Start creating your personalized travel
            plan with TripWise today.
        </p>


        <?php if (isset($_SESSION["user_id"])): ?>

            <a
                href="create-trip.php"
                class="primary-button"
            >
                Create Your Trip
            </a>

        <?php else: ?>

            <a
                href="register.php"
                class="primary-button"
            >
                Create Your Account
            </a>

        <?php endif; ?>

    </div>

</section>


<?php include "includes/footer.php"; ?>

</body>

</html>