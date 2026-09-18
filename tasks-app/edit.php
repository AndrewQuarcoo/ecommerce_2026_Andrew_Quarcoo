<?php
/*
 * edit.php — UPDATE
 *
 * GET  ?id=N -> load that task and show it pre-filled in the form.
 * POST       -> save the changes, then redirect back to index.php.
 */
require "db.php";

// intval() forces the id to a whole number — a small safety + sanity step.
$id = intval($_GET["id"] ?? 0);

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $title       = trim($_POST["title"]);
    $description = trim($_POST["description"]);
    $status      = $_POST["status"];
    $post_id     = intval($_POST["id"]);

    $stmt = $conn->prepare("UPDATE tasks SET title=?, description=?, status=? WHERE id=?");
    $stmt->bind_param("sssi", $title, $description, $status, $post_id);
    $stmt->execute();
    $stmt->close();
    $conn->close();

    header("Location: index.php");
    exit;
}

// GET: fetch the task we're editing so the form can show current values.
$stmt = $conn->prepare("SELECT * FROM tasks WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$task = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$task) { die("Task not found."); }
?>
<!DOCTYPE html>
<html>
<head>
    <title>Edit Task</title>
    <style>
        body { font-family: system-ui, sans-serif; max-width: 640px; margin: 40px auto; padding: 0 16px; }
        label { font-weight: bold; }
        input, textarea, select { width: 100%; padding: 8px; margin: 4px 0 12px; box-sizing: border-box; }
        button { padding: 8px 16px; font-size: 15px; cursor: pointer; }
    </style>
</head>
<body>
    <h2>Edit Task</h2>
    <form method="POST" action="edit.php">
        <input type="hidden" name="id" value="<?php echo $task['id']; ?>">

        <label>Title</label><br>
        <input type="text" name="title" value="<?php echo htmlspecialchars($task['title']); ?>" required><br>

        <label>Description</label><br>
        <textarea name="description"><?php echo htmlspecialchars($task['description']); ?></textarea><br>

        <label>Status</label><br>
        <select name="status">
            <option value="pending"     <?php echo $task['status'] === 'pending'     ? 'selected' : ''; ?>>Pending</option>
            <option value="in_progress" <?php echo $task['status'] === 'in_progress' ? 'selected' : ''; ?>>In Progress</option>
            <option value="done"        <?php echo $task['status'] === 'done'        ? 'selected' : ''; ?>>Done</option>
        </select><br>

        <button type="submit">Update Task</button>
    </form>
    <p><a href="index.php">Back</a></p>
</body>
</html>
