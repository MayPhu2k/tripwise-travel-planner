<?php

require_once "config/database.php";

$name = "";
$email = "";
$errors = [];

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $name = trim($_POST["name"] ?? "");
    $email = trim($_POST["email"] ?? "");
    $password = $_POST["password"] ?? "";
    $confirm_password = $_POST["confirm_password"] ?? "";


    // Validate name
    if ($name === "") {
        $errors[] = "Name is required.";
    } elseif (strlen($name) < 2) {
        $errors[] = "Name must be at least 2 characters.";
    }


    // Validate email
    if ($email === "") {
        $errors[] = "Email is required.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Please enter a valid email address.";
    }


    // Validate password
    if ($password === "") {
        $errors[] = "Password is required.";
    } elseif (strlen($password) < 8) {
        $errors[] = "Password must be at least 8 characters.";
    }


    // Confirm password
    if ($password !== $confirm_password) {
        $errors[] = "Passwords do not match.";
    }


    // Check whether email already exists
    if (empty($errors)) {

        $stmt = $pdo->prepare("
            SELECT user_id
            FROM users
            WHERE email = ?
        ");

        $stmt->execute([$email]);

        if ($stmt->fetch()) {
            $errors[] = "An account with this email already exists.";
        }
    }


    // Create account
    if (empty($errors)) {

        $hashed_password = password_hash(
            $password,
            PASSWORD_DEFAULT
        );

        $stmt = $pdo->prepare("
            INSERT INTO users
            (name, email, password)
            VALUES (?, ?, ?)
        ");

        $stmt->execute([
            $name,
            $email,
            $hashed_password
        ]);


        header("Location: login.php?registered=1");
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

    <title>Register - TripWise</title>

    <link
        rel="stylesheet"
        href="css/style.css"
    >

</head>

<body>


<header>

    <nav class="navbar">

        <div class="logo">
            TripWise
        </div>

        <ul class="nav-links">

            <li>
                <a href="index.php">Home</a>
            </li>

            <li>
                <a href="destinations.php">
                    Destinations
                </a>
            </li>

            <li>
                <a href="login.php">
                    Login
                </a>
            </li>

        </ul>

    </nav>

</header>


<main class="auth-container">

    <div class="auth-card">

        <h1>Create Account</h1>

        <p class="auth-subtitle">
            Start planning your next trip.
        </p>


        <?php if (!empty($errors)): ?>

            <div class="error-box">

                <?php foreach ($errors as $error): ?>

                    <p>
                        <?php echo htmlspecialchars($error); ?>
                    </p>

                <?php endforeach; ?>

            </div>

        <?php endif; ?>


        <form method="POST" action="register.php">


            <div class="form-group">

                <label for="name">
                    Full Name
                </label>

                <input
                    type="text"
                    id="name"
                    name="name"
                    value="<?php echo htmlspecialchars($name); ?>"
                    required
                >

            </div>


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

                <small>
                    Password must be at least 8 characters.
                </small>

            </div>


            <div class="form-group">

                <label for="confirm_password">
                    Confirm Password
                </label>

                <input
                    type="password"
                    id="confirm_password"
                    name="confirm_password"
                    required
                >

            </div>


            <button
                type="submit"
                class="auth-button"
            >
                Create Account
            </button>


        </form>


        <p class="auth-footer">

            Already have an account?

            <a href="login.php">
                Login here
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