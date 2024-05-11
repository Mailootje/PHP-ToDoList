<?php
require 'config.php';
session_start();

if (!isset($_SESSION['user']) || $_SESSION['isAdmin'] != 1) {
    $_SESSION['error_message'] = "You do not have permission to access this page.";
    header('Location: todo.php');
    exit;
}

// Database operation
$user_stmt = $pdo->prepare("SELECT id, username FROM users ORDER BY username");
$user_stmt->execute();
$users = $user_stmt->fetchAll(PDO::FETCH_ASSOC);

$team_stmt = $pdo->prepare("SELECT id, name FROM teams ORDER BY name");
$team_stmt->execute();
$teams = $team_stmt->fetchAll(PDO::FETCH_ASSOC);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $selected_user_id = $_POST['user_id'];
    $team_id = $_POST['team_id'];
    $action = $_POST['action'];

    if ($action == 'add') {
        $stmt = $pdo->prepare("INSERT INTO user_teams (user_id, team_id, joined_at) VALUES (?, ?, NOW())");
        $stmt->execute([$selected_user_id, $team_id]);
        if ($stmt->errorCode() != '00000') {
            $_SESSION['error_message'] = "Failed to add user to the team. Error: " . implode(", ", $stmt->errorInfo());
        } else {
            $_SESSION['success_message'] = "User successfully added to the team.";
        }
        header('Location: todo.php');
        exit;
    } elseif ($action == 'remove') {
        $stmt = $pdo->prepare("DELETE FROM user_teams WHERE user_id = ? AND team_id = ?");
        $stmt->execute([$selected_user_id, $team_id]);
        if ($stmt->errorCode() != '00000') {
            $_SESSION['error_message'] = "Failed to remove user from the team. Error: " . implode(", ", $stmt->errorInfo());
        } else {
            $_SESSION['success_message'] = "User successfully removed from the team.";
        }
        header('Location: todo.php');
        exit;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Manage Team Memberships</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
</head>
<body>
    <?php include 'navbar.php'; ?>
    <div class="container mt-5">
        <h2>Manage Team Memberships</h2>
        <?php if (!empty($success)): ?>
            <div class='alert alert-success'><?= htmlspecialchars($success) ?></div>
        <?php endif; ?>
        <?php if (!empty($errors)): ?>
            <div class='alert alert-danger'><ul>
                <?php foreach ($errors as $error): echo "<li>" . htmlspecialchars($error) . "</li>"; endforeach; ?>
            </ul></div>
        <?php endif; ?>
        <form method="POST">
            <div class="form-group">
                <label for="user_id">Select User</label>
                <select class="form-control" name="user_id" required>
                    <option value="" disabled selected>Select a User</option>
                    <?php foreach ($users as $user): ?>
                        <option value="<?= htmlspecialchars($user['id']); ?>"><?= htmlspecialchars($user['username']); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group">
                <label for="team_id">Select Team</label>
                <select class="form-control" name="team_id" required>
                    <option value="" disabled selected>Select a Team</option>
                    <?php foreach ($teams as $team): ?>
                        <option value="<?= htmlspecialchars($team['id']); ?>"><?= htmlspecialchars($team['name']); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group">
                <button type="submit" name="action" value="add" class="btn btn-success">Add to Team</button>
                <button type="submit" name="action" value="remove" class="btn btn-danger">Remove from Team</button>
            </form>
        </div>
        <a href="todo.php" class="btn btn-secondary mt-3">Back to To-Do List</a>
    </div>
</body>
</html>
