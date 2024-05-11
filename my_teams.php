<?php
require 'config.php';
session_start();

// Redirect to login page if user is not logged in
if (!isset($_SESSION['user'])) {
    header('Location: login.php');
    exit;
}

$user_id = $_SESSION['user'];

// Fetch the teams that the user is currently part of, along with their descriptions
$teams_stmt = $pdo->prepare("
    SELECT t.id, t.name, t.description
    FROM teams t
    JOIN user_teams ut ON ut.team_id = t.id
    WHERE ut.user_id = ?
");
$teams_stmt->execute([$user_id]);
$teams = $teams_stmt->fetchAll(PDO::FETCH_ASSOC);

// Get optional feedback messages
$success_message = $_GET['success'] ?? null;
$error_message = $_GET['error'] ?? null;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>My Teams</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
</head>
<body>
    <?php
    include 'navbar.php';
    ?>
    
    <div class="container mt-5">
        <h2>My Teams</h2>
        <!-- Display success or error messages -->
        <?php if ($success_message): ?>
            <div class="alert alert-success"><?php echo htmlspecialchars($success_message); ?></div>
        <?php endif; ?>
        <?php if ($error_message): ?>
            <div class="alert alert-danger"><?php echo htmlspecialchars($error_message); ?></div>
        <?php endif; ?>

        <!-- List all teams with their descriptions, with the option to leave -->
        <ul class="list-group mb-3">
            <?php if (count($teams) > 0): ?>
                <?php foreach ($teams as $team): ?>
                    <li class="list-group-item d-flex justify-content-between align-items-center">
                        <div>
                            <strong><?php echo htmlspecialchars($team['name']); ?></strong> - 
                            <?php echo htmlspecialchars($team['description']); ?>
                        </div>
                        <form method="POST" action="leave_team.php" class="d-inline">
                            <input type="hidden" name="team_id" value="<?php echo $team['id']; ?>">
                            <button type="submit" class="btn btn-danger btn-sm">Leave</button>
                        </form>
                    </li>
                <?php endforeach; ?>
            <?php else: ?>
                <li class="list-group-item">You are not a member of any teams yet.</li>
            <?php endif; ?>
        </ul>
        <a href="todo.php" class="btn btn-secondary">Back to To-Do List</a>
    </div>
</body>
</html>
