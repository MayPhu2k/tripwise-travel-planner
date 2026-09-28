```php
<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}


/*
|--------------------------------------------------------------------------
| Check whether the current page is inside the admin folder
|--------------------------------------------------------------------------
*/

$is_admin_page = strpos($_SERVER["PHP_SELF"], "/admin/") !== false;


/*
|--------------------------------------------------------------------------
| Set the correct path
|--------------------------------------------------------------------------
|
| Normal pages:
|     index.php
|
| Admin pages:
|     ../index.php
|
*/

$base_path = $is_admin_page ? "../" : "";

?>

<header class="navbar">

    <div class="nav-container">

        <!-- Logo -->

        <a
            href="<?php echo $base_path; ?>index.php"
            class="logo"
        >
            TripWise
        </a>


        <!-- Navigation -->

        <nav class="nav-links">

            <!-- Home -->

            <a href="<?php echo $base_path; ?>index.php">
                Home
            </a>


            <!-- Public Destinations -->

            <a href="<?php echo $base_path; ?>destinations.php">
                Explore
            </a>


            <?php if (isset($_SESSION["user_id"])): ?>


                <?php if (
                    isset($_SESSION["user_role"]) &&
                    $_SESSION["user_role"] === "admin"
                ): ?>

                    <!-- ==========================================
                         ADMIN NAVIGATION
                    =========================================== -->

                    <a href="<?php echo $base_path; ?>admin/index.php">
                        Admin Dashboard
                    </a>

                    <a href="<?php echo $base_path; ?>admin/destinations.php">
                        Manage Destinations
                    </a>

                    <a href="<?php echo $base_path; ?>admin/places.php">
                        Manage Places
                    </a>

                    <a href="<?php echo $base_path; ?>admin/users.php">
                        Users
                    </a>


                <?php else: ?>

                    <!-- ==========================================
                         NORMAL USER NAVIGATION
                    =========================================== -->

                    <a href="<?php echo $base_path; ?>dashboard.php">
                        Dashboard
                    </a>

                    <a href="<?php echo $base_path; ?>my-trips.php">
                        My Trips
                    </a>

                    <a href="<?php echo $base_path; ?>saved-places.php">
                        Saved Places
                    </a>


                <?php endif; ?>


                <!-- Logout -->

                <a href="<?php echo $base_path; ?>logout.php">
                    Logout
                </a>


            <?php else: ?>

                <!-- ==========================================
                     GUEST NAVIGATION
                =========================================== -->

                <a href="<?php echo $base_path; ?>login.php">
                    Login
                </a>

                <a href="<?php echo $base_path; ?>register.php">
                    Register
                </a>


            <?php endif; ?>

        </nav>

    </div>

</header>
