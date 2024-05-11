<?php
require 'config.php';

session_start();

// Redirect to login page if the user is not logged in
if (!isset($_SESSION['user'])) {
    header('Location: login.php');
    exit;
}

$user_id = $_SESSION['user'];
$errors = [];
$success = '';

// Check if there are session-based messages to display
if (isset($_SESSION['success_message'])) {
    $success = $_SESSION['success_message'];
    unset($_SESSION['success_message']); // Clear the message after displaying it
}
if (isset($_SESSION['error_message'])) {
    $errors[] = $_SESSION['error_message'];
    unset($_SESSION['error_message']); // Clear the message after displaying it
}

// Fetch teams for which the user is a member
$teams_stmt = $pdo->prepare("
    SELECT t.id, t.name
    FROM teams t
    JOIN user_teams ut ON ut.team_id = t.id
    WHERE ut.user_id = ?
    ORDER BY ut.joined_at ASC
");
$teams_stmt->execute([$user_id]);
$teams = $teams_stmt->fetchAll(PDO::FETCH_ASSOC);

// Check if the currently selected team still exists
if (isset($_SESSION['selected_team_id'])) {
    $valid_team_ids = array_column($teams, 'id');
    if (!in_array($_SESSION['selected_team_id'], $valid_team_ids)) {
        unset($_SESSION['selected_team_id']);
    }
}

// Set the default team ID if no team has been selected yet
if (!isset($_SESSION['selected_team_id']) && count($teams) > 0) {
    $_SESSION['selected_team_id'] = $teams[0]['id'];
}

$selected_team_id = $_SESSION['selected_team_id'] ?? null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['add_todo'])) {
        // Handle the creation of a new to-do item
        $title = $_POST['title'] ?? '';
        $description = $_POST['description'] ?? '';
        $team_id = $_POST['team_id'] ?? 0;

        if ($title && $description && $team_id) {
            $stmt = $pdo->prepare("INSERT INTO todos (title, description, team_id) VALUES (?, ?, ?)");
            $stmt->execute([$title, $description, $team_id]);
            $_SESSION['success_message'] = "To-Do item added successfully!";
            $_SESSION['selected_team_id'] = $team_id;  // Persist team selection
        } else {
            $_SESSION['error_message'] = "Please provide a valid title, description, and team.";
        }

    } elseif (isset($_POST['delete_todo'])) {
        // Handle the deletion of a to-do item
        $todo_id = $_POST['todo_id'] ?? 0;
        $stmt = $pdo->prepare("DELETE FROM todos WHERE id = ?");
        $stmt->execute([$todo_id]);
        $_SESSION['success_message'] = "To-Do item deleted successfully!";
    } elseif (isset($_POST['filter_team'])) {
        // Handle the team filter selection
        $selected_team_id = $_POST['team_id'] ?? null;
        $_SESSION['selected_team_id'] = $selected_team_id;  // Persist team selection
    }

    // Redirect to the same page for notification display
    header("Location: todo.php");
    exit;
}

// Fetch todos for the selected team
$todos = [];
if ($selected_team_id) {
    $todos_stmt = $pdo->prepare("SELECT * FROM todos WHERE team_id = ?");
    $todos_stmt->execute([$selected_team_id]);
    $todos = $todos_stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Include navigation bar (optional)
include 'navbar.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Team To-Do List</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <script src="https://code.jquery.com/jquery-3.3.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</head>
<body>
    <div class="container mt-5">
        <h2>Team To-Do List</h2>

        <!-- Select team -->
        <div class="card mb-3">
            <div class="card-header">
                <h3>Select a Team</h3>
            </div>
            <div class="card-body">
                <form method="POST">
                    <div class="form-group">
                        <label for="team_id">Select Team</label>
                        <select class="form-control" name="team_id" required>
                            <option value="" disabled>Select Team</option>
                            <?php foreach ($teams as $team): ?>
                                <option value="<?= $team['id'] ?>" <?= ($selected_team_id == $team['id']) ? 'selected' : ''; ?>>
                                    <?= htmlspecialchars($team['name']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <button type="submit" name="filter_team" class="btn btn-primary">Filter</button>
                </form>
            </div>
        </div>

        <!-- Add new To-Do to the selected team -->
        <?php if ($selected_team_id): ?>
            <div class="card mb-3">
                <div class="card-header">
                    <h3>Add New To-Do to <?= htmlspecialchars(array_column($teams, 'name', 'id')[$selected_team_id]) ?></h3>
                </div>
                <div class="card-body">
                    <form method="POST">
                        <input type="hidden" name="team_id" value="<?= $selected_team_id ?>">
                        <div class="form-group">
                            <label for="title">Title</label>
                            <input type="text" class="form-control" name="title" placeholder="Title" required>
                        </div>
                        <div class="form-group">
                            <label for="description">Description</label>
                            <textarea class="form-control" name="description" placeholder="Description" required></textarea>
                        </div>
                        <button type="submit" name="add_todo" class="btn btn-primary">Add</button>
                    </form>
                </div>
            </div>
        <?php endif; ?>

        <!-- List existing To-Dos for the selected team -->
        <?php if ($todos): ?>
            <h3>To-Dos for <?= htmlspecialchars(array_column($teams, 'name', 'id')[$selected_team_id]) ?></h3>
            <ul class="list-group mb-3">
                <?php foreach ($todos as $todo): ?>
                    <li class="list-group-item d-flex justify-content-between align-items-center">
                        <span>
                            <strong><?= htmlspecialchars($todo['title']) ?></strong>: <?= htmlspecialchars($todo['description']) ?>
                        </span>
                        <div>
                            <form method="GET" action="edit_todo.php" class="d-inline">
                                <input type="hidden" name="todo_id" value="<?= $todo['id'] ?>">
                                <button type="submit" class="btn btn-warning btn-sm">Edit</button>
                            </form>
                            <form method="POST" class="d-inline">
                                <input type="hidden" name="todo_id" value="<?= $todo['id'] ?>">
                                <button type="submit" name="delete_todo" class="btn btn-danger btn-sm">Delete</button>
                            </form>
                        </div>
                    </li>
                <?php endforeach; ?>
            </ul>
        <?php else: ?>
            <p>No to-do items for this team yet.</p>
        <?php endif; ?>
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
