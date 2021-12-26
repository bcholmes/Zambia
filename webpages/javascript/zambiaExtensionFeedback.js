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
                    $.zambia.feedback.render(data, term);
                },
                error: function(err) {
                    if (err.status == 401) {
                        $.zambia.redirectToLogin();
                    } else {
                        $.zambia.simpleAlert('danger', 'There was a problem contacting the server. Try again later?');
                    }
                }
            });
        },

        filter: (term, immediate) => {
            if ($.zambia.feedback.timer) {
                clearTimeout($.zambia.feedback.timer);
            }
            $.zambia.feedback.timer = setTimeout(function() {
                $.zambia.feedback.fetch(term);
            }, immediate ? 10 : 1000);
        },

        render: function(data, term) {
            let $sessionList = $('#session-list');
            $sessionList.empty();
            if (data && data.categories) {
                for (let i = 0; i < data.categories.length; i++) {
                    let name = '<h4 class="mt-4">' + data.categories[i].name + '</h4>';
                    $sessionList.append($(name));
                    if (data.categories[i].sessions) {
                        for (let j = 0; j < data.categories[i].sessions.length; j++) {
                            let session = data.categories[i].sessions[j];
                            let $wrapper = $('<p class="ml-2 my-2" />');
                            if (term) {
                                term = term.replace(/[.?*+^$[\]\\(){}|-]/g, "\\$&"); // escape any special characters
                                let title = '<b>' + session.title.replace(new RegExp("(" + term + ")", "gi"), "<mark>$1</mark>") + "</b><br />";
                                let text = '<span>' + session.description.replace(new RegExp("(" + term + ")", "gi"), "<mark>$1</mark>") + '</span>';
                                console.log(text);
                                $wrapper.html(title + text);
                            } else {
                                let title = '<b>' + session.title + "</b><br />";
                                let text = '<span>' + session.description + '</span>';
                                $wrapper.html(title + text);
                            }
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