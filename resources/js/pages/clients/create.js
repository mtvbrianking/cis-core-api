$(document).ready(function () {
    $('#personal_access_client').on('change', function(event) {
        if(this.checked) {
            $('input[name=password_client]').prop('checked', false);
            $('input[name=redirect]').val('');
        }
    });

    $('input[name=password_client]').on('change', function(event) {
        if(this.checked) {
            $('input[name=personal_access_client]').prop('checked', false);
            $('input[name=redirect]').val('');
            $('input[name=redirect]').prop('disabled', true);
        } else {
            $('input[name=redirect]').prop('disabled', false);
        }
    });
});
