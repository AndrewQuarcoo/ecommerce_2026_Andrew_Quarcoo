<?php
/*
 * create.php — CREATE
 *
 * GET  -> show the "Add Task" form.
 * POST -> insert the new task, then redirect back to index.php.
 */
require "db.php";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $title       = trim($_POST["title"]);
    $description = trim($_POST["description"]);
    $status      = $_POST["status"];

    // Prepared statement: user values go in as parameters (?), never glued
    // straight into the SQL string. This is what stops SQL injection.
    $stmt = $conn->prepare("INSERT INTO tasks (title, description, status) VALUES (?, ?, ?)");
    $stmt->bind_param("sss", $title, $description, $status);
    $stmt->execute();
    $stmt->close();
    $conn->close();

    // Redirect after POST so a browser refresh doesn't re-submit the form.
    header("Location: index.php");
    exit;
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Add Task</title>
    <style>
        body { font-family: system-ui, sans-serif; max-width: 640px; margin: 40px auto; padding: 0 16px; }
        label { font-weight: bold; }
        input, textarea, select { width: 100%; padding: 8px; margin: 4px 0 12px; box-sizing: border-box; }
        button { padding: 8px 16px; font-size: 15px; cursor: pointer; }
    </style>
</head>
<body>
    <h2>Add Task</h2>
    <form method="POST" action="create.php">
        <label>Title</label><br>
        <input type="text" name="title" required><br>

        <label>Description</label><br>
        <textarea name="description"></textarea><br>

        <label>Status</label><br>
        <select name="status">
            <option value="pending">Pending</option>
            <option value="in_progress">In Progress</option>
            <option value="done">Done</option>
        </select><br>

        <button type="submit">Save Task</button>
    </form>
    <p><a href="index.php">Back</a></p>
</body>
</html>
