<?php
require 'config.php';
session_start();
$errors = [];
$success = '';

// Check for success and error messages stored in the session
if (isset($_SESSION['success_message'])) {
    $success = $_SESSION['success_message'];
    unset($_SESSION['success_message']); // Clear message after displaying
}
if (isset($_SESSION['error_message'])) {
    $errors[] = $_SESSION['error_message'];
    unset($_SESSION['error_message']); // Clear message after displaying
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';

    if ($username && $password && $confirm_password) {
        // Validate that passwords match
        if ($password !== $confirm_password) {
            $errors[] = "Passwords do not match!";
        } else {
            // Check if the username is already taken
            $stmt = $pdo->prepare("SELECT id FROM users WHERE username = ?");
            $stmt->execute([$username]);
            if ($stmt->fetch()) {
                $errors[] = "Username is already taken!";
            } else {
                // Hash the password before storing
                $hashed_password = password_hash($password, PASSWORD_DEFAULT);

                // Insert new user into the database
                $stmt = $pdo->prepare("INSERT INTO users (username, password) VALUES (?, ?)");
                $stmt->execute([$username, $hashed_password]);

                session_destroy();
                session_start();
                $_SESSION['success_message'] = "Registration successful! You can now login.";
                header('Location: login.php');
            }
        }
    } else {
        $errors[] = "Please fill out all fields!";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Register</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
</head>
<body>
    <?php
    include 'navbar.php';
    ?>
    <div class="container mt-5">
        <h2>Register</h2>
        <form method="POST">
            <div class="form-group">
                <label for="username">Username</label>
                <input type="text" class="form-control" name="username" required>
            </div>
            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" class="form-control" name="password" required>
            </div>
            <div class="form-group">
                <label for="confirm_password">Confirm Password</label>
                <input type="password" class="form-control" name="confirm_password" required>
            </div>
            <button type="submit" class="btn btn-primary">Register</button>
        </form>
        <p class="mt-3">Already have an account? <a href="login.php">Login here</a>.</p>
    </div>

    <!-- Notification Toasts -->
    <div aria-live="polite" aria-atomic="true" style="position: fixed; min-height: 200px; bottom: 0; right: 0;">
        <!-- Success Toast -->
        <div id="successToast" class="toast" style="position: absolute; bottom: 0; right: 0; min-width: 350px;" role="alert" aria-live="assertive" aria-atomic="true" data-delay="5000">
            <div class="toast-header">
                <strong class="mr-auto">Success</strong>
                <button type="button" class="ml-2 mb-1 close" data-dismiss="toast" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="toast-body">
                <?= htmlspecialchars($success) ?>
            </div>
        </div>

        <!-- Error Toast -->
        <div id="errorToast" class="toast" style="position: absolute; bottom: 0; right: 0; min-width: 350px;" role="alert" aria-live="assertive" aria-atomic="true" data-delay="5000">
            <div class="toast-header">
                <strong class="mr-auto">Error</strong>
                <button type="button" class="ml-2 mb-1 close" data-dismiss="toast" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="toast-body">
                <?php foreach ($errors as $error): ?>
                    <div><?= htmlspecialchars($error) ?></div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>

    <!-- Toast Notification Script -->
    <script>
    $(document).ready(function() {
        <?php if ($success): ?>
            $('#successToast').toast({ delay: 5000 }).toast('show');
        <?php endif; ?>
        <?php if (!empty($errors)): ?>
            $('#errorToast').toast({ delay: 5000 }).toast('show');
        <?php endif; ?>
    });
    </script>
</body>
</html>
