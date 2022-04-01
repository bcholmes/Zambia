$(function() {
    $.planz = $.planz || {};

    $.planz.redirectToLogin = () => {
        window.location = '/';
    };

    $.planz.clearAlerts = (alertType) => {
        if (alertType) {
            $('.alert-' + alertType).remove();
        } else {
           $('.alert').remove();
        }
    };

    $.planz.simpleAlert = (severity, text) => {
        let $alert = $('<div class="alert alert-' + severity + '" />');
        $alert.text(text);

        let $parent = $('.container');
        if ($parent.length > 0) {
            $parent.first().prepend($alert);
        } else {
            $parent = $('.navbar');
            if ($parent.length > 0) {
                $parent.last().after($alert);
            }
        }
    };

    $.planz.standardErrorFunction = (err) => {
        if (err.status < 300) {
            // this isn't an error. Why am I in the error function? Bad jQuery; no biscuit.
        } else if (err.status == 401) {
            $.planz.redirectToLogin();
        } else {
            $.planz.simpleAlert('danger', 'There was a problem contacting the server. Your changes might not have been saved. Try again later?');
        }
    }
});