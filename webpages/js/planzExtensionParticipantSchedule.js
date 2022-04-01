$(function() {

    $.planz.participantSchedule = {

        getDetails: (sessionId, additionalNeeded) => {
            $('.alert-danger').remove();

            $.ajax({ 
                url: 'api/get_session_details.php?sessionId=' + sessionId,
                method: 'GET',
                dataType: "json",
                success: function(data) {
                    $.planz.participantSchedule.renderDetails(data, additionalNeeded);
                    $("#detailsModal").modal('show');
                },
                error: function(err) {
                    $.planz.standardErrorFunction(err);
                }
            });
            
        },

        renderDetails(data, additionalNeeded) {
            let $div = $('<div />');
            if (additionalNeeded) {
                let $alert = $('<div class="alert alert-primary">This session would benefit from some extra ' + 
                    (data.participantLabel ? data.participantLabel : 'participants') + '.</div>');
                $div.append($alert);
            }
            if (data.title) {
                let $head = $('<h6 />');
                $head.html(data.title);
                $div.append($head);
            }
            if (data.description) {
                let $description = $('<div />');
                $description.html(data.description);
                $div.append($description);
            }
            let subtitle = "";
            if (data.room && data.room.name) {
                subtitle = data.room.name;
            }
            if (data.trackName) {
                if (subtitle.length > 0) {
                    subtitle += ' &#8226; ';
                }
                subtitle += data.trackName;
            }
            subtitle = '<b>' + subtitle + '</b>';
            let $subtitleDiv = $('<div />');
            $subtitleDiv.html(subtitle);
            $div.append($subtitleDiv);            
            $.planz.participantSchedule.renderAssignments(data, $div);
            $('.details-content').empty();
            $('.details-content').append($div);
        },

        renderAssignments: (data, $parent) => {
            if (data.assignments.length > 0) {
                let $table = $('<table class="table mt-3" />');
                let $thead = $('<thead><tr><th>' + data.participantLabel + '</th><th>Mod?</th></tr></thead>')
                $table.append($thead);
                let $tbody = $('<tbody />');
                for (let i = 0; i < data.assignments.length; i++) {
                    let $tr = $('<tr />');
                    let $name = $('<td />');
                    $name.text(data.assignments[i].name ? data.assignments[i].name : data.assignments[i].badgeid);
                    $tr.append($name);

                    let $mod = $('<td />');
                    $mod.text(data.assignments[i].moderator ? 'Yes' : 'No');
                    $tr.append($mod);

                    $tbody.append($tr);
                }

                $table.append($tbody);
                $parent.append($table);
            } else {
                let $message = $('<p class="text-info">No participants are currently assigned to this session.</p>');
                $parent.append($message);
            }
        }
    }

    $(".details-option").click((e) => {
        e.preventDefault();
        e.stopPropagation();
        let $a = $(e.target).closest("a");
        let sessionId = $a.attr("data-session-id");
        let additionalNeeded = $a.attr("data-additional-needed");
        $.planz.participantSchedule.getDetails(sessionId, additionalNeeded);
    });
});