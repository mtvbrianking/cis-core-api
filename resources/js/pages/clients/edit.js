$(document).ready(function () {
    $('#personal_access_client').on('change', function(event) {
        if(this.checked) {
            $('input[name=password_client]').prop('checked', false);
            $('input[name=redirect]').val('');
            $('div.grants-wrapper').hide();
        } else {
            $('div.grants-wrapper').show();
        }
    });

    $('input[name=password_client]').on('change', function(event) {
        var password_client = $(this).val();
        if(password_client == 1) {
            $('input[name=redirect]').val('');
            $('div.redirect-uri-wrapper').hide();
        } else {
            $('div.redirect-uri-wrapper').show();
        }
    });
});
