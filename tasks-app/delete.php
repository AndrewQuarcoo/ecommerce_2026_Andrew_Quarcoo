<?php
/*
 * delete.php — DELETE
 *
 * ?id=N -> delete that task, then redirect back to index.php.
 * There's no page to show; it just does the work and sends you home.
 */
require "db.php";

$id = intval($_GET["id"] ?? 0);

// Only run the delete if we actually got a valid id.
if ($id > 0) {
    $stmt = $conn->prepare("DELETE FROM tasks WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $stmt->close();
}

$conn->close();
header("Location: index.php");
exit;
