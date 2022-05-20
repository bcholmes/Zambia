<?php
// Copyright (c) 2022 BC Holmes. All rights reserved. See copyright document for more details.

global $title, $linki;
$title = "Public Schedule";

require_once('name.php');
require_once('PartCommonCode.php');
require_once('time_slot_functions.php');
require_once('schedule_table_renderer.php');
require_once('api/format_functions.php');

function is_public_schedule_visible($db) {
    $query = <<<EOD
    SELECT current FROM Phases where phasename = 'Show public reports';
    EOD;
    if (!$resultSet = mysqli_query_exit_on_error($query)) {
        exit;
    } else {
        $result = false;
        while ($row = mysqli_fetch_array($resultSet)) {
            $result = $row['current'] ? true : false;
        }
        return $result;
    }
}

class ParticipantAssignment {
    public $name;
    public $moderator;
}

class PublicScheduleItem implements ScheduleCellData {
    public $publicationNumber;
    public $title;
    public $roomId;
    public $startTime;
    public $formattedStartTime;
    public $formattedEndTime;
    public $duration;
    public $trackName;
    public $room;
    public $sessionId;
    public $assignments;

    function getDay() {
        $index = time_to_row_index($this->startTime);
        $day = floor($index / (24 * 4));
        $hour = $index % (24 * 4);
        if ($hour < (8 * 4)) {
            $day -= 1;
        }
        return $day;
    }
    function getData() {
        return "<div><a class=\"details-option\" href=\"#\" data-session-id=\"$this->sessionId\"><b>" 
            . ($this->publicationNumber ? ($this->publicationNumber . ". ") : "") 
            . $this->title . "</b></a></div><div class=\"small\">" . $this->trackName . "</div>";
    }

    function getColumnWidth() {
        return $this->room ? $this->room->getColumnWidth() : 1;
    }

    function getStartIndex() {
        $daysIndex = $this->getDay() * (24 * 4);
        return time_to_row_index($this->startTime) - $daysIndex;
    }

    function getEndIndex() {
        $start = $this->getStartIndex();
        $duration = time_to_row_index($this->duration);
        return $start + $duration;
    }

    function getRowHeight() {
        return $this->getEndIndex() - $this->getStartIndex();
    }

    public function getAdditionalClasses() {
        return "";
    }
}

class ScheduleItemDataProvider implements ScheduleCellDataProvider {

    private $items;

    public function __construct($items) {
        $this->items = $items;
    }

    public function findFirstStartIndexForDay($day) {
        $slots = $this->filterByDay($day);
        $result = 9999;
        foreach ($slots as $slot) {
            if ($slot->getStartIndex() < $result) {
                $result = $slot->getStartIndex();
            }
        }
        return $result;
    }
    public function findLastEndIndexForDay($day) {
        $slots = $this->filterByDay($day);
        $result = 0;
        foreach ($slots as $slot) {
            if ($slot->getEndIndex() > $result) {
                $result = $slot->getEndIndex();
            }
        }
        return $result;
    }
    public function isCellDataAvailableForDay($day) {
        $slots = $this->filterByDay($day);
        return count($slots) > 0;
    }
    public function getCellDataFor($index, $column, $day) {
        $slots = $this->filterByDay($day);
        $result = null;

        foreach ($slots as $slot) {
            if ($slot->room == null) {
                // it's probably a room that has no panels
            } else if ($slot->room->columnNumber <= $column && ($slot->room->columnNumber + $slot->room->getColumnWidth()) > $column
                    && $slot->getStartIndex() <= $index && $slot->getEndIndex() > $index) {
                $result = $slot;
                break;
            }
        }
    
        return $result;
    }

    function filterByDay($day) {
        $result = array();
    
        foreach ($this->items as $item) {
            if ($item->getDay() == $day) {
                $result[] = $item;
            }
        }
    
        return $result;
    }    
}

function select_schedule_items($allRooms) {
    $CON_START_DATIM = CON_START_DATIM;

    $query = <<<EOD
    SELECT sch.roomid, sess.title, sch.starttime, t.trackname, sess.duration, sess.sessionid, sess.pubsno,
        sess.progguiddesc, sess.hashtag,
        DATE_FORMAT(ADDTIME('$CON_START_DATIM', sch.starttime),'%Y-%m-%d %H:%i:%S') AS fullstarttime,
        DATE_FORMAT(ADDTIME('$CON_START_DATIM', ADDTIME(sch.starttime, sess.duration)),'%Y-%m-%d %H:%i:%S') AS fullendtime
      FROM Sessions sess
      JOIN Schedule sch USING (sessionid)
      JOIN Tracks t USING (trackid)
      JOIN Rooms r ON (sch.roomid = r.roomid)
      JOIN Divisions D ON (D.divisionid = sess.divisionid)
     WHERE sess.pubstatusid = 2
     ORDER BY sch.starttime, r.display_order
    EOD;

    $slots = array();
    $slotsById = array();

    if (!$resultSet = mysqli_query_exit_on_error($query)) {
        exit;
    } else {
        while ($row = mysqli_fetch_array($resultSet)) {
            $slot = new PublicScheduleItem();
            $slot->publicationNumber = $row['pubsno'];
            $slot->roomId = $row["roomid"];
            $slot->room = $allRooms[$row["roomid"]];
            $slot->startTime = $row["starttime"];
            $slot->formattedStartTime = convert_database_date_to_date($row["fullstarttime"]);
            $slot->formattedEndTime = convert_database_date_to_date($row["fullendtime"]);
            $slot->duration = $row["duration"];
            $slot->title = $row["title"];
            $slot->sessionId = $row["sessionid"];
            $slot->trackName = $row["trackname"];
            $slot->description = $row["progguiddesc"];
            $slot->hashtag = $row["hashtag"];
            $slot->assignments = array();
            $slots[] = $slot;
            $slotsById[$slot->sessionId] = $slot;
        }
    }
    $query = <<<EOD
    SELECT
        POS.sessionid,
        POS.badgeid,
        COALESCE(POS.moderator, 0) AS moderator,
        P.pubsname,
        CD.badgename,
        CD.firstname,
        CD.lastname,
        P.anonymous
    FROM
                    ParticipantOnSession POS
                JOIN Participants P ON P.badgeid = POS.badgeid
                JOIN CongoDump CD ON CD.badgeid = POS.badgeid
    WHERE
        POS.sessionid in (select S.sessionid FROM Sessions S JOIN Schedule SCH USING (sessionid));
EOD;

    if (!$resultSet = mysqli_query_exit_on_error($query)) {
        exit;
    } else {
        while ($row = mysqli_fetch_object($resultSet)) {
            $sessionId = $row->sessionid;
            if (array_key_exists($sessionId, $slotsById)) {
                $slot = $slotsById[$sessionId];

                $anonymous = $row->anonymous === 'Y' ? true : false;
                $name = PersonName::from($row);
                $assignment = new ParticipantAssignment();
                $assignment->moderator = $row->moderator ? true : false;
                $assignment->name = (!$anonymous) ? $name->getPubsName() : "Anonymous";

                $slot->assignments[] = $assignment;
            }

        }
    }
    return $slots;
}

function render_table($rooms, $items) {
    $dataProvider = new ScheduleItemDataProvider($items);
    $renderer = new ScheduleTableRenderer($rooms, $dataProvider);
    $renderer->renderTable();
}

function render_list($items) {

    foreach ($items as $item) {
?>
        <div class="mb-5">
			<h5 class="mb-0"><?php echo ($item->publicationNumber ? ($item->publicationNumber . ". ") : "") . $item->title ?></h5>
			<div>
				<b>
					<span><?php echo $item->room->roomName ?></span>
					&#8226;
					<span><?php echo $item->trackName ?></span>
					&#8226;
					<span><time datetime="<?php echo date_format($item->formattedStartTime, 'c') ?>"><?php echo date_format($item->formattedStartTime, 'D g:i A') ?></time>&#8211;<time datetime="<?php echo date_format($item->formattedStartTime, 'c') ?>"><?php echo date_format($item->formattedEndTime, 'g:i A T') ?></time></span>
				</b>
			</div>
			<div class="my-1"><?php echo $item->description ?></div>
			<div class="my-2"><?php echo $item->hashtag ?></div>
            <div>
<?php
        foreach ($item->assignments as $i => $a) {
            if ($a->moderator) echo "<b>Mod: </b>";
?>
				<span><?php echo $a->name; if ($i+1 < count($item->assignments)) echo ", "; ?></span>
<?php
        }
?>
            </div>
        </div>
<?php
    }
}


$rooms = Room::selectAllRoomInSchedule($linki);
$collatedRooms = Room::collateParentsAndAssignColumns($rooms);
$items = select_schedule_items($rooms);

participant_header($title, true, 'Normal', true);

if (is_public_schedule_visible($linki)) {

    echo fetchCustomText("alerts");
?>

<div class="card mt-3">
    <div class="card-header">
        <div class="d-flex justify-content-between">
            <h4>Public Schedule</h4>
            <div class="btn-group" role="group" aria-label="View Types">
                <button type="button" class="btn btn-secondary" id="grid-view-button"><i class="bi bi-columns"></i><span class="sr-only">Show Grid View</span></button>
                <button type="button" class="btn btn-outline-secondary" id="list-view-button"><i class="bi bi-list"></i><span class="sr-only">Show List View</span></button>
            </div>
        </div>
    </div>
    <div class="card-body">
        <p>The following sessions have been scheduled:</p>
        <div id="grid-view">
<?php
    render_table($collatedRooms, $items);
?>
        </div>
        <div id="list-view" style="display: none">
<?php
    render_list($items);
?>
        </div>
    </div>
</div>

<!-- Modal -->
<div class="modal fade" id="detailsModal" tabindex="-1" role="dialog" aria-labelledby="detailsLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="detailsLabel">Session Details</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="details-content">

                </div>
            </div>
        </div>
    </div>
</div>

<script type="text/javascript" src="js/planzExtension.js"></script>
<script type="text/javascript" src="js/planzExtensionParticipantSchedule.js"></script>

<?php
    } else {
?>
    <div class="card mt-3">
        <div class="card-body">
            <p>The schedule is not currently available.</p>
        </div>
    </div>
<?php
    }
    participant_footer();
?>