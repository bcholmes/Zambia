$(function() {

    $.planz.sessionEnumeration = {

        execute: () => {
            $('.alert-danger').remove();

            $.ajax({ 
                url: 'api/session_enumerator.php',
                method: 'POST',
                success: function(data) {
                    $.planz.simpleAlert('success', 'Good news! We did the thing!');
                },
                error: function(err) {
                    $.planz.standardErrorFunction(err);
                }
            });
        },
    };

    $(".action-button").click(() => {
        $.planz.sessionEnumeration.execute();
    });
});