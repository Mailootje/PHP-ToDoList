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
    $_SESSION['error_message'] = "You are not authorized to create teams.";
    header("Location: todo.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['create_team'])) {
    $team_name = $_POST['team_name'] ?? '';
    $team_description = $_POST['team_description'] ?? '';

    if ($team_name) {
        // Check if the team name is already taken
        $stmt = $pdo->prepare("SELECT id FROM teams WHERE name = ?");
        $stmt->execute([$team_name]);
        if ($stmt->fetch()) {
            // Set an error message in the session
            $_SESSION['error_message'] = "A team with that name already exists.";
        } else {
            // Insert the new team into the `teams` table with description and creator_id
            $stmt = $pdo->prepare("INSERT INTO teams (name, description, creator_id) VALUES (?, ?, ?)");
            $stmt->execute([$team_name, $team_description, $user_id]);
            $team_id = $pdo->lastInsertId();

            // Insert the user as the initial member of the new team
            $stmt = $pdo->prepare("INSERT INTO user_teams (user_id, team_id) VALUES (?, ?)");
            $stmt->execute([$user_id, $team_id]);

            // Set a success message in the session
            $_SESSION['success_message'] = "Team created successfully!";
        }
    } else {
        // Set an error message in the session
        $_SESSION['error_message'] = "Please provide a name for the team.";
    }

    // Redirect to todo.php to show the notification
    header("Location: todo.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Create Team</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
</head>
<body>
    <?php
    include 'navbar.php';
    ?>
    
    <div class="container mt-5">
        <h2>Create New Team</h2>
        <form method="POST">
            <div class="form-group">
                <label for="team_name">Team Name</label>
                <input type="text" class="form-control" name="team_name" required>
            </div>
            <div class="form-group">
                <label for="team_description">Description</label>
                <textarea class="form-control" name="team_description"></textarea>
            </div>
            <button type="submit" name="create_team" class="btn btn-primary">Create Team</button>
        </form>
        <a href="todo.php" class="btn btn-secondary mt-3">Back to ToDo List</a>
    </div>
</body>
</html>
