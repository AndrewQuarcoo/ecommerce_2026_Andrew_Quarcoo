<?php
/*
 * index.php — READ
 *
 * Lists every task, newest first. This is the home page of the app and the
 * hub the other pages link back to.
 */
require "db.php";

// Pull all tasks. Simple read query, no user input, so a plain query is fine.
$result = $conn->query("SELECT * FROM tasks ORDER BY created_at DESC");
?>
<!DOCTYPE html>
<html>
<head>
    <title>Tasks</title>
    <style>
        body { font-family: system-ui, sans-serif; max-width: 640px; margin: 40px auto; padding: 0 16px; color: #1a1a1a; }
        h1 { border-bottom: 2px solid #eee; padding-bottom: 8px; }
        .task { border: 1px solid #e5e5e5; border-radius: 8px; padding: 12px 16px; margin: 12px 0; }
        .task h3 { margin: 0 0 4px; }
        .task p { margin: 4px 0; color: #555; }
        .status { display: inline-block; font-size: 12px; padding: 2px 8px; border-radius: 12px; background: #eee; text-transform: capitalize; }
        .status.pending { background: #fff3cd; }
        .status.in_progress { background: #cce5ff; }
        .status.done { background: #d4edda; }
        a { color: #0066cc; text-decoration: none; margin-right: 12px; }
        a:hover { text-decoration: underline; }
        .add { font-weight: bold; }
    </style>
</head>
<body>
    <h1>My Tasks</h1>
    <a class="add" href="create.php">+ Add Task</a>

    <?php if ($result->num_rows === 0): ?>
        <p>No tasks yet. Add your first one above.</p>
    <?php endif; ?>

    <?php while ($row = $result->fetch_assoc()): ?>
        <div class="task">
            <!-- htmlspecialchars() escapes user text so it can't inject HTML/JS (XSS) -->
            <h3><?php echo htmlspecialchars($row['title']); ?></h3>
            <p><?php echo htmlspecialchars($row['description']); ?></p>
            <span class="status <?php echo $row['status']; ?>"><?php echo str_replace('_', ' ', $row['status']); ?></span>
            <div style="margin-top:8px;">
                <a href="edit.php?id=<?php echo $row['id']; ?>">Edit</a>
                <a href="delete.php?id=<?php echo $row['id']; ?>" onclick="return confirm('Delete this task?');">Delete</a>
            </div>
        </div>
    <?php endwhile; ?>
</body>
</html>
<?php $conn->close(); ?>
