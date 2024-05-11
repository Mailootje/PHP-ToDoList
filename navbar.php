<?php
session_start();
?>
<nav class="navbar navbar-expand-lg navbar-light bg-light" id="navbar">
    <?php if (isset($_SESSION['user'])): ?>
        <a class="navbar-brand" href="todo.php">Team To-Do List</a>
    <?php else: ?>
        <a class="navbar-brand" href="login.php">Team To-Do List</a>
    <?php endif; ?>
    <!-- <a class="navbar-brand" href="todo.php">Team To-Do List</a> -->
    <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
        <span class="navbar-toggler-icon"></span>
    </button>
    <div class="collapse navbar-collapse" id="navbarNav">
        <ul class="navbar-nav mr-auto">
            <?php if (isset($_SESSION['isAdmin']) && $_SESSION['isAdmin'] == 1): ?>
                <li class="nav-item">
                    <a class="nav-link" href="my_teams.php">My Teams</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="create_team.php">Create Team</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="join_team.php">Add To Team</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="delete_team.php">Delete Team</a>
                </li>
                <li class="nav-item">
                    <button class="nav-link btn btn-sm btn-outline-secondary" onclick="toggleDarkMode()" id="darkModeToggle">Toggle Dark Mode</button>
                </li>
            <?php else: ?>
                <li class="nav-item">
                    <a class="nav-link" href="my_teams.php">My Teams</a>
                </li>
                <li class="nav-item">
                    <button class="nav-link btn btn-sm btn-outline-secondary" onclick="toggleDarkMode()" id="darkModeToggle">Toggle Dark Mode</button>
                </li>
            <?php endif; ?>
        </ul>
        <ul class="navbar-nav">
            <?php if (isset($_SESSION['user'])): ?>
                <li class="nav-item">
                    <a class="nav-link" href="user.php">Account</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="logout.php">Logout</a>
                </li>
            <?php else: ?>
                <li class="nav-item">
                    <a class="nav-link" href="login.php">Login</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="register.php">Register</a>
                </li>
            <?php endif; ?>
        </ul>
    </div>
</nav>

<style>
    /* Dark Mode Styles */
    body.dark-mode {
        background-color: #121212;
        color: #e0e0e0;
    }

    body.dark-mode .card {
        background-color: #1e1e1e;
        color: #ffffff;
    }

    body.dark-mode .toast {
        background-color: #333333;
        color: #ffffff;
    }

    body.dark-mode .navbar,
    body.dark-mode .navbar * {
        background-color: #343a40 !important; /* Ensures override */
        color: #ffffff !important;
    }

    body.dark-mode .nav-link,
    body.dark-mode .navbar-brand,
    body.dark-mode .navbar-nav .nav-link {
        color: #ffffff !important; /* Ensures text color is visible */
    }

    body.dark-mode .navbar-toggler {
        border-color: rgba(255, 255, 255, 0.1); /* Adjust toggler visibility */
    }

    body.dark-mode .navbar-toggler-icon {
        background-image: url("data:image/svg+xml;charset=utf8,%3Csvg viewBox='0 0 30 30' xmlns='http://www.w3.org/2000/svg'%3E%3Cpath stroke='rgba(255, 255, 255, 0.5)' stroke-width='2' stroke-linecap='round' stroke-miterlimit='10' d='M4 7h22M4 15h22M4 23h22'/%3E%3C/svg%3E");
    }

    /* Dark Mode specific styles for list items */
    body.dark-mode .list-group-item {
        background-color: #1e1e1e; /* Darker background for list items */
        color: #cfd2d6; /* Lighter text for readability */
        border-color: #343a40; /* Subtle borders to fit dark themes */
    }

    body.dark-mode .list-group-item a {
        color: #adb5bd; /* Ensure links within list items are also visible */
    }

    body.dark-mode .list-group-item a:hover {
        color: #f8f9fa; /* Slightly brighter color on hover for better interaction feedback */
    }
</style>

<script>
    function toggleDarkMode() {
        const body = document.body;
        body.classList.toggle('dark-mode');

        // Persist state in local storage
        if (body.classList.contains('dark-mode')) {
            localStorage.setItem('darkMode', 'enabled');
            document.getElementById('darkModeToggle').innerText = 'Toggle Light Mode';
        } else {
            localStorage.setItem('darkMode', 'disabled');
            document.getElementById('darkModeToggle').innerText = 'Toggle Dark Mode';
        }
    }

    // On page load, check for dark mode setting
    window.onload = function() {
        if (localStorage.getItem('darkMode') === 'enabled') {
            document.body.classList.add('dark-mode');
            document.getElementById('darkModeToggle').innerText = 'Toggle Light Mode';
        }
    };
</script>

<style>
    mb-3 {
        border-radius: 2em !important;
    }
</style>