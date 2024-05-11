<?php
require 'config.php';
session_start();

if (!isset($_SESSION['user'])) {
    header('Location: login.php');
    exit;
}

$user_id = $_SESSION['user'];
$todo = null;

// Retrieve To-Do ID from the query string
$todo_id = $_GET['todo_id'] ?? 0;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_todo'])) {
    // Handle the form submission for updating a to-do item
    $todo_id = $_POST['todo_id'] ?? 0;
    $title = $_POST['title'] ?? '';
    $description = $_POST['description'] ?? '';

    if ($todo_id && $title && $description) {
        // Update the to-do item in the database
        $stmt = $pdo->prepare("UPDATE todos SET title = ?, description = ? WHERE id = ?");
        $stmt->execute([$title, $description, $todo_id]);

        // Set a success message in the session
        $_SESSION['success_message'] = "To-Do item updated successfully!";
    } else {
        // Set an error message in the session
        $_SESSION['error_message'] = "Please provide a valid title and description.";
    }

    // Redirect to todo.php to show the notifications
    header("Location: todo.php");
    exit;
}

// Fetch the to-do item details if not updating
if ($todo_id) {
    $stmt = $pdo->prepare("SELECT * FROM todos WHERE id = ?");
    $stmt->execute([$todo_id]);
    $todo = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$todo) {
        $_SESSION['error_message'] = "To-Do item not found.";
        header("Location: todo.php");
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Edit To-Do Item</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
</head>
<body>
    <?php
    include 'navbar.php';
    ?>

    <div class="container mt-5">
        <h2>Edit To-Do Item</h2>
        <?php if ($todo): ?>
            <form method="POST">
                <input type="hidden" name="todo_id" value="<?php echo $todo['id']; ?>">
                <div class="form-group">
                    <label for="title">Title</label>
                    <input type="text" class="form-control" name="title" value="<?php echo htmlspecialchars($todo['title']); ?>" required>
                </div>
                <div class="form-group">
                    <label for="description">Description</label>
                    <textarea class="form-control" name="description" required><?php echo htmlspecialchars($todo['description']); ?></textarea>
                </div>
                <button type="submit" name="update_todo" class="btn btn-primary">Update</button>
            </form>
        <?php endif; ?>
        <a href="todo.php" class="btn btn-secondary mt-3">Back to To-Do List</a>
    </div>
</body>
</html>
