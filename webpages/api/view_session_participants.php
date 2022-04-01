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
require_once('../name.php');
require_once("participant_functions.php");

start_session_if_necessary();
$db = connect_to_db(true);
try {
    if ($_SERVER['REQUEST_METHOD'] === 'GET' && isProgrammingStaff()) {
        if (array_key_exists("id", $_REQUEST)) {

            $sessionId = $_REQUEST['id'];
            $assignments = get_participant_assignments($db, $sessionId);

            header('Content-type: application/json; charset=utf-8');
            $json_string = json_encode(array("assignments" => $assignments));
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