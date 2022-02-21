<?php
// Copyright (c) 2022 BC Holmes. All rights reserved. See copyright document for more details.
// This function serves as a REST API to access session history information.

if (!include ('../../db_name.php')) {
	include ('../../db_name.php');
}
require_once('./http_session_functions.php');
require_once('./db_support_functions.php');
require_once('./format_functions.php');
require_once('../data_functions.php');

function get_session_edits($db, $sessionId) {
    $query = <<<EOD
SELECT
		SEH.badgeid,
		SEH.name,
		SEH.editdescription,
		SEH.timestamp,
		DATE_FORMAT(SEH.timestamp, "%c/%e/%y %l:%i %p") AS tsformat,
		SEC.description AS codedescription,
		SS.statusname
	FROM
			 SessionEditHistory SEH
		JOIN SessionEditCodes SEC USING (sessioneditcode)
		JOIN SessionStatuses SS USING (statusid)
	WHERE
		SEH.sessionid=?;
EOD;

    $stmt = mysqli_prepare($db, $query);
    mysqli_stmt_bind_param($stmt, "i", $sessionId);
    $history = [];
	if (mysqli_stmt_execute($stmt)) {
		$result = mysqli_stmt_get_result($stmt);
        while ($row = mysqli_fetch_object($result)) {
            $history[] = [ 
                "badgeid" => $row->badgeid,
                "name" => $row->name,
                "description" => $row->editdescription,
                "timestamp" => date_format(convert_database_date_to_date($row->timestamp) , 'c'),
                "codedescription" => $row->codedescription,
                "status" => $row->statusname
            ];
        }
        mysqli_stmt_close($stmt);
        return $history;
    } else {
        throw new DatabaseSqlException("Query could not be executed: $query");
    }
}


start_session_if_necessary();
$db = connect_to_db(true);
try {
    if ($_SERVER['REQUEST_METHOD'] === 'GET' && isProgrammingStaff()) {
        if (array_key_exists("id", $_REQUEST)) {

            $sessionId = $_REQUEST['id'];
            $history = get_session_edits($db, $sessionId);

            header('Content-type: application/json; charset=utf-8');
            $json_string = json_encode(array("history" => $history));
            echo $json_string;

        } else {
            http_response_code(400);
        }
    } else if ($_SERVER['REQUEST_METHOD'] === 'GET' && isLoggedIn()) {
        http_response_code(403);
    } else if ($_SERVER['REQUEST_METHOD'] === 'GET') {
        http_response_code(401);
    } else {
        http_response_code(405);
    }

} finally {
    $db->close();
}

?>