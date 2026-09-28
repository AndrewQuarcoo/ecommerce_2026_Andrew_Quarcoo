<?php
/**
 * db_cred.example.php — template for the real credentials file.
 *
 * The real file (core/db_cred.php) is git-ignored so passwords never reach
 * the repository. To set up a fresh checkout:
 *
 *     cp core/db_cred.example.php core/db_cred.php
 *
 * then edit the values below to match your machine.
 *
 * Local XAMPP defaults: user "root", empty password, database "shoppn".
 */

define('DB_HOST', 'localhost'); // where MySQL is running
define('DB_USER', 'root');      // MySQL username
define('DB_PASS', '');          // MySQL password (blank on local XAMPP)
define('DB_NAME', 'shoppn');    // database name (see database/shoppn.sql)
