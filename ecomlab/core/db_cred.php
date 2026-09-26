<?php
/**
 * db_cred.php — database connection credentials, in one place.
 *
 * Keeping the credentials here (not inside db_class.php) means you only
 * change them in one spot when moving between local XAMPP and the live
 * server. This file is git-ignored on the live server so real passwords
 * never reach the repository.
 *
 * Local XAMPP defaults: user "root", empty password, database "shoppn".
 */

define('DB_HOST', 'localhost'); // where MySQL is running
define('DB_USER', 'root');      // MySQL username
define('DB_PASS', '');          // MySQL password (blank on local XAMPP)
define('DB_NAME', 'shoppn');    // database name (see database/shoppn.sql)
