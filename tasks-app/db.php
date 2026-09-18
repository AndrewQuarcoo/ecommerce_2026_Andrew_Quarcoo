<?php
/*
 * db.php — Database connection
 *
 * This is the single place where the app connects to MySQL. Every other
 * page does `require "db.php";` so the connection ($conn) is created once
 * and reused.
 */

$host    = "localhost";   // MySQL is on the same machine as PHP
$db_user = "root";        // XAMPP's default MySQL user
$db_pass = "";            // XAMPP's default MySQL password is blank
$db_name = "tasks_app";   // the database this app uses

// Open the connection. mysqli throws everything we need into $conn.
$conn = new mysqli($host, $db_user, $db_pass, $db_name);


if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
