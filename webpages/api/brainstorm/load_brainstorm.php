<?php

if (!include ('../../../db_name.php')) {
	include ('../../../db_name.php');
}
require_once('../db_support_functions.php');
require_once('../participant_functions.php');
require_once('../jwt_functions.php');

function read_division_and_track_options($db) {
	$query = <<<EOD
	SELECT 
		   d.divisionid, d.divisionname, d.display_order, 
		   t.trackid, t.trackname, t.display_order as track_order
	  FROM 
		   Divisions d, Tracks t
	 WHERE 
		   t.divisionid = d.divisionid
	   AND
		   d.brainstorm_support = 'Y'
	   ORDER BY
	   		d.display_order, d.divisionid, track_order;
EOD;
   
	$stmt = mysqli_prepare($db, $query);
	if (mysqli_stmt_execute($stmt)) {
		$result = mysqli_stmt_get_result($stmt);
		$options = array();
		$current_division = null;
		while ($row = mysqli_fetch_object($result)) {

			if ($current_division == null || $current_division['id'] !== $row->divisionid) {
				if ($current_division != null) {
					$options[] = $current_division;
				}
				$current_division = array(
					"id" => $row->divisionid,
					"name" => $row->divisionname,
					"tracks" => array()
				);
			}
			$track = array(
				"trackid" => $row->trackid,
				"trackname" => $row->trackname
			);
			array_push($current_division['tracks'], $track);
		}
		mysqli_stmt_close($stmt);
		if ($current_division != null) {
			$options[] = $current_division;
		}
		return $options;
	} else {
		throw new DatabaseSqlException($query);
	}
}

function create_jwt_for_badgeid($db, $badgeid) {
	$query = <<<EOD
 SELECT 
        P.password, P.data_retention, P.badgeid, C.firstname, C.lastname, C.badgename, C.regtype 
   FROM 
        Participants P 
   JOIN CongoDump C USING (badgeid)
  WHERE 
         P.badgeid = ?;
 EOD;

	$stmt = mysqli_prepare($db, $query);
	mysqli_stmt_bind_param($stmt, "s", $badgeid);
	if (mysqli_stmt_execute($stmt)) {
		$result = mysqli_stmt_get_result($stmt);
		if (mysqli_num_rows($result) == 1) {
			$dbobject = mysqli_fetch_object($result);
			mysqli_stmt_close($stmt);
			return jwt_create_token($dbobject->badgeid, get_name($dbobject), $dbobject->regtype == null ? false : true);
		} else {
			return false;
		}
	} else {
		throw new DatabaseSqlException($query);
	}
}

$db = connect_to_db();
session_start();
try {

	$options = read_division_and_track_options($db);
	$result = array("divisions" => $options);

	// create JWT if already logged in
	if (isset($_SESSION['badgeid'])) {
		$jwt = create_jwt_for_badgeid($db, $_SESSION['badgeid']);
		if ($jwt) {
			header("Authorization: Bearer ".$jwt);
		}
	}

	header('Content-type: application/json');
	$json_string = json_encode($result);
    echo $json_string;

} finally {
	$db->close();
}

?>