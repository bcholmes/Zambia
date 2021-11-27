<?php
// Copyright (c) 2021 BC Holmes. All rights reserved. See copyright document for more details.
// These functions provide support for common database queries.

class DatabaseException extends Exception {};
class DatabaseSqlException extends DatabaseException {};

function connect_to_db() {
    $db = mysqli_connect(DBHOSTNAME, DBUSERID, DBPASSWORD, DBDB);
    if (!$db) {
        throw new DatabaseException("Could not connect to database");
    } else {
        mysqli_set_charset($db, "utf8");
        mysqli_query($db, "SET SESSION sql_mode = ''");
        return $db;
    }
}

?>