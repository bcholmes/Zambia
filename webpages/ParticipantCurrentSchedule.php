<?php
// Copyright (c) 2022 BC Holmes. All rights reserved. See copyright document for more details.

global $title, $linki;
$title = "Participant Schedule";

require_once('PartCommonCode.php');
require_once('time_slot_functions.php');
require_once('schedule_table_renderer.php');

class PreliminaryScheduleItem implements ScheduleCellData {
    public $title;
    public $roomId;
    public $startTime;
    public $duration;
    public $trackName;
    public $room;
    public $sessionId;
    public $participantCount;
    public $isInterestAllowed;

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
        $needed = $this->isAdditionalParticipantNeeded();
        return "<div><a class=\"details-option\" href=\"#\" data-session-id=\"$this->sessionId\" data-additional-needed=\"$needed\"><b>" 
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
        return $this->isAdditionalParticipantNeeded() ? "bg-warning-lighter" : "";
    }

    public function isAdditionalParticipantNeeded() {
        return ($this->isInterestAllowed && $this->participantCount < 4);
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

    $query = <<<EOD
    SELECT sch.roomid, sess.title, sch.starttime, t.trackname, sess.duration, sess.sessionid, D.is_part_session_interest_allowed, count(POS.badgeid) as participantcount
      FROM Sessions sess
      JOIN Schedule sch USING (sessionid)
      JOIN Tracks t USING (trackid)
      JOIN ParticipantOnSession POS USING (sessionid)
      JOIN Divisions D ON (D.divisionid = sess.divisionid)
     WHERE sess.pubstatusid = 2
      group by sch.roomid, sess.title, sch.starttime, t.trackname, sess.duration, sess.sessionid, D.is_part_session_interest_allowed;
    EOD;
    if (!$result = mysqli_query_exit_on_error($query)) {
        exit;
    } else {
        $slots = array();
        while ($row = mysqli_fetch_array($result)) {
            $slot = new PreliminaryScheduleItem();
            $slot->roomId = $row["roomid"];
            $slot->room = $allRooms[$row["roomid"]];
            $slot->startTime = $row["starttime"];
            $slot->duration = $row["duration"];
            $slot->title = $row["title"];
            $slot->sessionId = $row["sessionid"];
            $slot->trackName = $row["trackname"];
            $slot->participantCount = $row["participantcount"];
            $slot->isInterestAllowed = $row["is_part_session_interest_allowed"] ? true : false;
            $slots[] = $slot;
        }
        return $slots;
    }
}

function render_table($rooms, $items) {
    $dataProvider = new ScheduleItemDataProvider($items);
    $renderer = new ScheduleTableRenderer($rooms, $dataProvider);
    $renderer->renderTable();
}

$rooms = Room::selectAllRoomInSchedule($linki);
$collatedRooms = Room::collateParentsAndAssignColumns($rooms);
$items = select_schedule_items($rooms);

participant_header($title, false, 'Normal', true);

echo fetchCustomText("alerts");
?>

<div class="alert alert-warning">Some sessions could use more participants. Please consider signing up! Those sessions have been highlighted in yellow, below.</div>

<div class="card">
    <div class="card-header">
        <h4>Preliminary Schedule</h4>
    </div>
    <div class="card-body">
        <p>The following sessions have been scheduled:</p>

<?php
    render_table($collatedRooms, $items);
?>
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
    participant_footer();
?>