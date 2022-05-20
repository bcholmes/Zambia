<?php
// Copyright (c) 2021 BC Holmes. All rights reserved. See copyright document for more details.
// This function provides support for mobile apps such as WisSched and FogGuide

if (!include ('../config/db_name.php')) {
    include ('../config/db_name.php');
}

require_once('./db_support_functions.php');
require_once("jwt_functions.php");

function get_assignments($db, $badgeid) {

    $query =<<<'EOD'
SELECT
    SCH.sessionid
FROM
        Schedule SCH
    JOIN ParticipantOnSession POS USING (sessionid)
WHERE POS.badgeid = ?;
EOD;
    $stmt = mysqli_prepare($db, $query);
    mysqli_stmt_bind_param($stmt, "s", $badgeid);

    $assignments = array();
    if (mysqli_stmt_execute($stmt)) {
        $result = mysqli_stmt_get_result($stmt);
        while ($dbobject = mysqli_fetch_object($result)) {
            $assignments[] = "" . $dbobject->sessionid;
        }
        mysqli_stmt_close($stmt);
        return $assignments;
    } else {
        throw new DatabaseSqlException("Could not execute query: $query");
	}
}

$db = connect_to_db();
try {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {

        $auth = jwt_from_header();
        if (jwt_validate_token($auth, true)) {

            $assignments = get_assignments($db, jwt_extract_badgeid($auth));
            $result = array( "assignments" => $assignments);

            header('Content-type: application/json');
            echo json_encode($result);
        } else {
            http_response_code(401);
        }
    } else {
        http_response_code(405);
    }
} finally {
    $db->close();
}
?>