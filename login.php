<?php

session_start();

require_once "config/database.php";

$email = "";
$errors = [];


// Check if user just registered
$registered = isset($_GET['registered']);


if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $email = trim($_POST["email"] ?? "");
    $password = $_POST["password"] ?? "";


    // Validate email
    if ($email === "") {

        $errors[] = "Email is required.";

    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {

        $errors[] = "Please enter a valid email address.";

    }


    // Validate password
    if ($password === "") {

        $errors[] = "Password is required.";

    }


    // Login
    if (empty($errors)) {

        $stmt = $pdo->prepare("
            SELECT *
            FROM users
            WHERE email = ?
        ");

        $stmt->execute([$email]);

        $user = $stmt->fetch();


        if ($user && password_verify($password, $user["password"])) {
            $_SESSION["user_id"] = $user["user_id"];
            $_SESSION["user_name"] = $user["name"];
            $_SESSION["user_email"] = $user["email"];
            $_SESSION["user_role"] = $user["role"];


            // Redirect based on user role
            if ($user["role"] === "admin") {

            header("Location: admin/index.php");
            exit;

    } else {

        header("Location: dashboard.php");
        exit;

    }

} else {

            $errors[] = "Incorrect email or password.";

        }

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

    <title>Login - TripWise</title>

    <link
        rel="stylesheet"
        href="css/style.css"
    >

</head>

<body>


<!-- Navigation -->

<header>

    <nav class="navbar">

        <div class="logo">
            TripWise
        </div>

        <ul class="nav-links">

            <li>
                <a href="index.php">
                    Home
                </a>
            </li>

            <li>
                <a href="destinations.php">
                    Destinations
                </a>
            </li>

            <li>
                <a href="register.php">
                    Register
                </a>
            </li>

        </ul>

    </nav>

</header>


<!-- Login -->

<main class="auth-container">

    <div class="auth-card">

        <h1>
            Welcome Back
        </h1>

        <p class="auth-subtitle">
            Login to continue planning your trips.
        </p>


        <!-- Registration Success -->

        <?php if ($registered): ?>

            <div class="success-box">

                Account created successfully.
                Please login.

            </div>

        <?php endif; ?>


        <!-- Errors -->

        <?php if (!empty($errors)): ?>

            <div class="error-box">

                <?php foreach ($errors as $error): ?>

                    <p>
                        <?php echo htmlspecialchars($error); ?>
                    </p>

                <?php endforeach; ?>

            </div>

        <?php endif; ?>


        <!-- Login Form -->

        <form
            method="POST"
            action="login.php"
        >


            <div class="form-group">

                <label for="email">
                    Email
                </label>

                <input
                    type="email"
                    id="email"
                    name="email"
                    value="<?php echo htmlspecialchars($email); ?>"
                    required
                >

            </div>


            <div class="form-group">

                <label for="password">
                    Password
                </label>

                <input
                    type="password"
                    id="password"
                    name="password"
                    required
                >

            </div>


            <button
                type="submit"
                class="auth-button"
            >
                Login
            </button>


        </form>


        <p class="auth-footer">

            Don't have an account?

            <a href="register.php">
                Create an account
            </a>

        </p>

    </div>

</main>


<footer>

    <p>
        &copy; <?php echo date("Y"); ?>
        TripWise. All rights reserved.
    </p>

</footer>


</body>

</html>