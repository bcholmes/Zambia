<?php
// Copyright (c) 2021 BC Holmes. All rights reserved. See copyright document for more details.
// This function finds the panel list that we want to solicit participant feedback for.

if (!include ('../../db_name.php')) {
	include ('../../db_name.php');
}
require_once('./db_support_functions.php');
require_once('../data_functions.php');

function find_interest_for_current_user($db, $badgeid) {
    $query = <<<EOD
    SELECT
            P.interested
        FROM
            Participants P
        WHERE
            P.badgeid = ?;
    EOD;
    $stmt = mysqli_prepare($db, $query);
    mysqli_stmt_bind_param($stmt, "s", $badgeid);
    $interested = false;
	if (mysqli_stmt_execute($stmt)) {
		$result = mysqli_stmt_get_result($stmt);
        while ($row = mysqli_fetch_object($result)) {
            $interested = $row->interested ? true : false;
        }
    } else {
        throw new DatabaseSqlException("Query could not be executed: $query");
    }
    mysqli_stmt_close($stmt);
    return $interested;
}

function find_session_for_feedback($db, $term) {
    $clause = "";
    if ($term) {
        $term = $db->real_escape_string(mb_strtolower($term));
        $clause = " AND (LOWER(s.title) LIKE '%$term%' OR LOWER(s.progguiddesc) LIKE '%$term%') ";
    }

    $query = <<<EOD
	SELECT t.trackname, s.sessionid, s.title, s.progguiddesc, s.invitedguest
	  FROM Sessions s
      JOIN Tracks t USING (trackid)
      JOIN SessionStatuses ss USING (statusid)
      JOIN PubStatuses ps USING (pubstatusid)
	 WHERE ss.may_be_scheduled = 1
	   AND ps.pubstatusname = 'Public'
       AND s.divisionid in (select divisionid from Divisions where divisionname = 'Panels')
       $clause
	 ORDER BY t.display_order, s.sessionid;
EOD;
   
	$stmt = mysqli_prepare($db, $query);
	if (mysqli_stmt_execute($stmt)) {
		$result = mysqli_stmt_get_result($stmt);
		$categories = array();
		$current_category = null;
		while ($row = mysqli_fetch_object($result)) {
            if ($current_category == null || $current_category['name'] !== $row->trackname) {
                if ($current_category !== null) {
                    $categories[] = $current_category;
                }
                $current_category = array( "name" => $row->trackname,
                    "sessions" => array());
            }

            $sessions = $current_category['sessions'];

            $sessions[] = array("sessionId" => $row->sessionid,
                "title" => $row->title,
                "description" => $row->progguiddesc,
                "inviteOnly" => ($row->invitedguest ? true : false)
            );

            $current_category['sessions'] = $sessions;
        }

        if ($current_category !== null) {
            $categories[] = $current_category;
        }

        mysqli_stmt_close($stmt);
        return $categories;
    } else {
        throw new DatabaseSqlException("Query could not be executed: $query");
    }
}


session_start();
$db = connect_to_db();
try {
    if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_SESSION['badgeid'])) {
        if (may_I('SessionFeedback')) {
            $term = array_key_exists("q", $_REQUEST) ? $_REQUEST["q"] : null;

            header('Content-type: application/json; charset=utf-8');
            $categories = find_session_for_feedback($db, $term);
            $interest = find_interest_for_current_user($db, $_SESSION['badgeid']);
            $json_string = json_encode(array("categories" => $categories, "interest" => $interest));
            echo $json_string;

        } else {
            http_response_code(403);
        }
    } else if ($_SERVER['REQUEST_METHOD'] === 'GET') {
        http_response_code(401);
    } else {
        http_response_code(405);
    }
} finally {
    $db->close();
}
?>