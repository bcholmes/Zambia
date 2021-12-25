$(function() {

    $.zambia.feedback = {

        timer: null,

        fetch: function(term) {
            $('#load-spinner').show();
            $.ajax({ 
                url: 'api/session_feedback_list.php' + (term ? '?q=' + encodeURIComponent(term) : ''),
                method: 'GET',
                success: function(data) {
                    $('#load-spinner').hide();
                    $.zambia.feedback.render(data);
                }
            });
        },

        filter: (term, immediate) => {
            if ($.zambia.feedback.timer) {
                clearTimeout($.zambia.feedback.timer);
            }
            $.zambia.feedback.timer = setTimeout(function() {
                console.log('setting timer');
                $.zambia.feedback.fetch(term);
            }, immediate ? 10 : 1000);
        },

        render: function(data) {
            let $sessionList = $('#session-list');
            $sessionList.empty();
            if (data && data.categories) {
                for (let i = 0; i < data.categories.length; i++) {
                    let name = '<h4 class="mt-4">' + data.categories[i].name + '</h4>';
                    $sessionList.append($(name));
                    if (data.categories[i].sessions) {
                        for (let j = 0; j < data.categories[i].sessions.length; j++) {
                            let session = data.categories[i].sessions[j];
                            let $wrapper = $('<p class="ml-2" />');
                            let $b = $('<b />');
                            $b.html(session.title);
                            $wrapper.append($b);
                            $wrapper.append($('<br />'));
                            let $span = $('<span />');
                            $span.html(session.description);
                            $wrapper.append($span);
                            $sessionList.append($wrapper);
                        }
                    }
                }
            }
        }
    };

    $('#load-spinner').show();

    $("#filter").keyup((e) => {
        $.zambia.feedback.filter(e.target.value);
    });

    $("#clearFilter").click((e) => {
        $("#filter").val('');
        $.zambia.feedback.filter('', true);
    });
    $.zambia.feedback.fetch()

});