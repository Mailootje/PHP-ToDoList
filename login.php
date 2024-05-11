<?php
require 'config.php';
session_start();

// Initialize errors and success message arrays
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

    if ($username && $password) {
        $stmt = $pdo->prepare("SELECT id, username, password, isAdmin FROM users WHERE username = ?");
        $stmt->execute([$username]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['user'] = $user['id'];
            $_SESSION['isAdmin'] = $user['isAdmin']; // Set admin status in session
            $_SESSION['success_message'] = 'Login successful!';
            header('Location: todo.php');
            exit;
        } else {
            $_SESSION['error_message'] = 'Invalid username or password';
        }
    } else {
        $_SESSION['error_message'] = 'Please fill out all fields';
    }

    // Redirect to the same page for notification display
    header('Location: login.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Login</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <script src="https://code.jquery.com/jquery-3.3.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</head>
<body>
    <?php include 'navbar.php'; ?>
    <div class="container mt-5">
        <h2>Login</h2>
        <form method="POST">
            <div class="form-group">
                <label for="username">Username</label>
                <input type="text" class="form-control" name="username" required>
            </div>
            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" class="form-control" name="password" required>
            </div>
            <button type="submit" class="btn btn-primary">Login</button>
        </form>
        <p class="mt-3">Don't have an account? <a href="register.php">Register here</a>.</p>
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
