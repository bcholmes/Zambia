<?php
// Copyright (c) 2021 BC Holmes. All rights reserved. See copyright document for more details.
// This function provides support for mobile apps such as WisSched and FogGuide

if (!include ('../../db_name.php')) {
	include ('../../db_name.php');
}

require_once("jwt_functions.php");

function get_assignments($badgeid) {
	$db = mysqli_connect(DBHOSTNAME, DBUSERID, DBPASSWORD, DBDB);
    if (!$db) {
        return false;
    } else {

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
				$assignments[] = $dbobject->sessionid;
			}
			mysqli_stmt_close($stmt);
			mysqli_close($db);
			return $assignments;
        } else {
            mysqli_close($db);
            return false;
        }
	}
}


if ($_SERVER['REQUEST_METHOD'] === 'POST') {

	$auth = jwt_from_header();

	if (jwt_validate_token($auth, true)) {

		$assignments = get_assignments(jwt_extract_badgeid($auth));
		$result = array( "assignments" => $assignments);

		header('Content-type: application/json');
		echo json_encode($result);
	} else {
		http_response_code(401);
	}
} else {
	http_response_code(404);
}
?>