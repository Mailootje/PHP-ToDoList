<?php
require 'config.php';
session_start();

// Ensure the user is logged in
if (!isset($_SESSION['user'])) {
    header('Location: login.php');
    exit;
}

$user_id = $_SESSION['user'];

// Fetch the current user details including admin status
$stmt = $pdo->prepare("SELECT username, isAdmin FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

// Retrieve and clear notification messages from the session
$success = $_SESSION['success_message'] ?? '';
$errors = isset($_SESSION['error_message']) ? [$_SESSION['error_message']] : [];
unset($_SESSION['success_message'], $_SESSION['error_message']);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['update_credentials'])) {
        $new_username = $_POST['username'] ?? $user['username'];
        $new_password = $_POST['password'] ?? '';

        // Update the username
        if (!empty($new_username) && $new_username !== $user['username']) {
            $stmt = $pdo->prepare("UPDATE users SET username = ? WHERE id = ?");
            if ($stmt->execute([$new_username, $user_id])) {
                session_destroy();  // This destroys the session
                session_start();    // Start a new session so that the success message can be set
                $_SESSION['success_message'] = "Username updated successfully. Please log in again.";
                header('Location: login.php');  // Redirect to login page
                exit;
            } else {
                $errors[] = "Failed to update username.";
            }
        }

        // Update the password
        if (!empty($new_password)) {
            $hashedPassword = password_hash($new_password, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("UPDATE users SET password = ? WHERE id = ?");
            if ($stmt->execute([$hashedPassword, $user_id])) {
                session_destroy();  // This destroys the session
                session_start();    // Start a new session so that the success message can be set
                $_SESSION['success_message'] = "Password updated successfully. Please log in again.";
                header('Location: login.php');  // Redirect to login after password change
                exit;
            } else {
                $errors[] = "Failed to update password.";
            }
        }
    } elseif (isset($_POST['delete_account'])) {
        // Check if the user is an admin (isAdmin = 1)
        if ($user['isAdmin'] == 1) {
            $_SESSION['error_message'] = "You can't delete your account because you're an admin.";
            header('Location: todo.php');
            exit;
        } else {
            $stmt = $pdo->prepare("DELETE FROM users WHERE id = ?");
            if ($stmt->execute([$user_id])) {
                session_destroy();  // This destroys the session
                session_start();    // Start a new session so that the success message can be set
                $_SESSION['success_message'] = "Account deleted successfully.";
                header('Location: login.php');  // Redirect to login after account deletion
                exit;
            } else {
                $errors[] = "Failed to delete account.";
            }
        }
    }    
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>User Dashboard</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <script src="https://code.jquery.com/jquery-3.3.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</head>
<body>
    <?php include 'navbar.php'; ?>
    <div class="container mt-5">
        <h2>User Dashboard</h2>
        <form method="POST">
            <div class="form-group">
                <label for="username">New Username</label>
                <input type="text" class="form-control" name="username" value="<?= htmlspecialchars($user['username']) ?>" required>
            </div>
            <div class="form-group">
                <label for="password">New Password (leave blank if you do not want to change it)</label>
                <input type="password" class="form-control" name="password" placeholder="New Password">
            </div>
            <button type="submit" name="update_credentials" class="btn btn-primary">Update Credentials</button>
            <button type="submit" name="delete_account" class="btn btn-danger" onclick="return confirm('Are you sure you want to delete your account? This cannot be undone.');">Delete Account</button>
        </form>
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
