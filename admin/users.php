<?php

require_once "../includes/admin-auth.php";
require_once "../config/database.php";

/* Get Users */

$stmt = $pdo->query("
    SELECT
        user_id,
        name,
        email,
        role,
        created_at
    FROM users
    ORDER BY created_at DESC
");

$users = $stmt->fetchAll();

?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>Manage Users | TripWise</title>

    <link
        rel="stylesheet"
        href="../css/style.css"
    >

</head>

<body>

<?php require_once "../includes/header.php"; ?>


<main class="admin-page">

    <!-- Page Header -->

    <section class="admin-header">

        <div>

            <p class="page-label">
                ADMIN PANEL
            </p>

            <h1>
                Users
            </h1>

            <p class="admin-intro">
                View registered TripWise users and manage
                account information.
            </p>

        </div>

    </section>


    <!-- Summary -->

    <section class="admin-summary">

        <div class="admin-summary-card">

            <span class="admin-summary-label">
                TOTAL USERS
            </span>

            <strong>
                <?php echo count($users); ?>
            </strong>

        </div>

    </section>


    <!-- Users Table -->

    <?php if (count($users) > 0): ?>

        <section class="admin-table-wrapper">

            <table class="admin-table">

                <thead>

                    <tr>

                        <th>
                            ID
                        </th>

                        <th>
                            Name
                        </th>

                        <th>
                            Email
                        </th>

                        <th>
                            Role
                        </th>

                        <th>
                            Registered
                        </th>

                    </tr>

                </thead>

                <tbody>

                    <?php foreach ($users as $user): ?>

                        <tr>

                            <td>
                                #<?php echo (int) $user["user_id"]; ?>
                            </td>

                            <td>

                                <strong>
                                    <?php
                                    echo htmlspecialchars(
                                        $user["name"]
                                    );
                                    ?>
                                </strong>

                            </td>

                            <td>

                                <?php
                                echo htmlspecialchars(
                                    $user["email"]
                                );
                                ?>

                            </td>

                            <td>

                                <?php if ($user["role"] === "admin"): ?>

                                    <span class="admin-role admin-role-admin">
                                        Admin
                                    </span>

                                <?php else: ?>

                                    <span class="admin-role admin-role-user">
                                        User
                                    </span>

                                <?php endif; ?>

                            </td>

                            <td>

                                <?php
                                echo date(
                                    "M d, Y",
                                    strtotime($user["created_at"])
                                );
                                ?>

                            </td>

                        </tr>

                    <?php endforeach; ?>

                </tbody>

            </table>

        </section>

    <?php else: ?>

        <section class="admin-empty">

            <h2>
                No users yet
            </h2>

            <p>
                There are currently no registered users.
            </p>

        </section>

    <?php endif; ?>

</main>


<?php require_once "../includes/footer.php"; ?>

</body>

</html>