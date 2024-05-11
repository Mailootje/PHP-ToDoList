<?php
require 'config.php';
session_start();
$errors = [];
$success = '';

// Redirect to login page if user is not logged in
if (!isset($_SESSION['user'])) {
    header('Location: login.php');
    exit;
}

$user_id = $_SESSION['user'];
$selected_team_id = $_SESSION['selected_team_id'] ?? null;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['invite_user'])) {
    $username = $_POST['username'] ?? '';
    $team_id = $_POST['team_id'] ?? 0;

    if ($username && $team_id) {
        // Get the user ID from the given username
        $stmt = $pdo->prepare("SELECT id FROM users WHERE username = ?");
        $stmt->execute([$username]);
        $invited_user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($invited_user) {
            $invited_user_id = $invited_user['id'];
            // Check if the user is already in the team
            $stmt = $pdo->prepare("SELECT * FROM user_teams WHERE user_id = ? AND team_id = ?");
            $stmt->execute([$invited_user_id, $team_id]);
            if ($stmt->fetch()) {
                $errors[] = "User is already a member of this team.";
            } else {
                // Add user to the team
                $stmt = $pdo->prepare("INSERT INTO user_teams (user_id, team_id) VALUES (?, ?)");
                $stmt->execute([$invited_user_id, $team_id]);
                $success = "User successfully invited to the team!";
            }
        } else {
            $errors[] = "User not found.";
        }
    } else {
        $errors[] = "Please fill out all fields.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Invite to Team</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
</head>
<body>
    <?php
    include 'navbar.php';
    ?>
    
    <div class="container mt-5">
        <h2>Invite User to Team</h2>
        <?php
        if ($success) {
            echo "<div class='alert alert-success'>$success</div>";
        }
        if (!empty($errors)) {
            echo "<div class='alert alert-danger'><ul>";
            foreach ($errors as $error) {
                echo "<li>$error</li>";
            }
            echo "</ul></div>";
        }
        ?>
        <form method="POST">
            <input type="hidden" name="team_id" value="<?php echo $selected_team_id; ?>">
            <div class="form-group">
                <label for="username">Username of User to Invite</label>
                <input type="text" class="form-control" name="username" required>
            </div>
            <button type="submit" name="invite_user" class="btn btn-primary">Invite User</button>
        </form>
        <a href="todo.php" class="btn btn-secondary mt-3">Back to ToDo List</a>
    </div>
</body>
</html>
