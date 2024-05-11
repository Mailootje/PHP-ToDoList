<?php
require 'config.php';
session_start();

// Redirect to the login page if the user is not logged in
if (!isset($_SESSION['user'])) {
    header('Location: login.php');
    exit;
}

$user_id = $_SESSION['user'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $team_id = $_POST['team_id'] ?? 0;

    if ($team_id) {
        // Remove the user's association with this team from the user_teams table
        try {
            $stmt = $pdo->prepare("DELETE FROM user_teams WHERE user_id = ? AND team_id = ?");
            $stmt->execute([$user_id, $team_id]);

            // Optionally, clear the selected team session variable if leaving the current team
            if (isset($_SESSION['selected_team_id']) && $_SESSION['selected_team_id'] == $team_id) {
                unset($_SESSION['selected_team_id']);
            }

            // Set a success message in the session
            $_SESSION['success_message'] = "You have left the team successfully.";

        } catch (PDOException $e) {
            // Set an error message in the session
            $_SESSION['error_message'] = "Failed to leave the team: " . $e->getMessage();
        }
    } else {
        // Set an error message for invalid team selection
        $_SESSION['error_message'] = "Invalid team selection.";
    }

    // Redirect to todo.php (or wherever the relevant page is)
    header("Location: todo.php");
    exit;
} else {
    // Set an error message for an invalid request
    $_SESSION['error_message'] = "Invalid request.";
    header("Location: todo.php");
    exit;
}
