<?php
require 'config.php';
session_start();

// Redirect to login page if user is not logged in
if (!isset($_SESSION['user'])) {
    header('Location: login.php');
    exit;
}

$user_id = $_SESSION['user'];

// Fetch user's isAdmin status
$user_stmt = $pdo->prepare("SELECT isAdmin FROM users WHERE id = ?");
$user_stmt->execute([$user_id]);
$user = $user_stmt->fetch(PDO::FETCH_ASSOC);

// Check if the user is an admin
if ($user['isAdmin'] != 1) {
    $_SESSION['error_message'] = "You are not authorized to delete teams.";
    header("Location: todo.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $team_id = $_POST['team_id'] ?? 0;

    // Start a transaction to delete the team and associated data
    $pdo->beginTransaction();

    try {
        // Delete user-team associations in the `user_teams` table
        $stmt = $pdo->prepare("DELETE FROM user_teams WHERE team_id = ?");
        $stmt->execute([$team_id]);

        // Delete the team itself
        $stmt = $pdo->prepare("DELETE FROM teams WHERE id = ?");
        $stmt->execute([$team_id]);

        // Commit the transaction
        $pdo->commit();

        // Set a success message
        $_SESSION['success_message'] = "Team successfully deleted!";
    } catch (PDOException $e) {
        // Roll back the transaction in case of an error
        $pdo->rollBack();

        // Set an error message in the session
        $_SESSION['error_message'] = "Failed to delete the team: " . $e->getMessage();
    }

    // Redirect to todo.php to show the notifications
    header("Location: todo.php");
    exit;
}

// Fetch teams for deletion
$teams_stmt = $pdo->prepare("SELECT id, name FROM teams");
$teams_stmt->execute();
$teams = $teams_stmt->fetchAll(PDO::FETCH_ASSOC);

include 'navbar.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Delete Team</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
</head>
<body>
    <div class="container mt-5">
        <h2>Delete a Team</h2>
        <form method="POST">
            <div class="form-group">
                <label for="team_id">Select Team to Delete</label>
                <select class="form-control" name="team_id" required>
                    <option value="" disabled selected>Select a team</option>
                    <?php foreach ($teams as $team): ?>
                        <option value="<?= htmlspecialchars($team['id']); ?>"><?= htmlspecialchars($team['name']); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <button type="submit" class="btn btn-danger">Delete Team</button>
        </form>
        <a href="todo.php" class="btn btn-secondary mt-3">Back to Home</a>
    </div>
</body>
</html>
