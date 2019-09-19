$(document).ready(function() {
    var clients_dt = $("table[id=clients]").DataTable({
        columnDefs: [
            {
                targets: [0],
                visible: false
            }
        ]
    });

    function show_errors(xhr, form) {
        // Unprocessable Entity
        if (xhr.status === 422) {
            // Clear previous errors
            form.find(".text-danger").remove();
            form.find("input,select")
                .closest("div.form-group")
                .removeClass("has-error");

            var response = xhr.responseJSON;
            $.each(response.error, function(param, error) {
                var form_group = form
                    .find(
                        "input[name=" + param + "],select[name=" + param + "]"
                    )
                    .closest("div.form-group");
                form_group.addClass("has-error");
                var error_msg =
                    '<small class="form-text text-danger">' +
                    error[0] +
                    "</small>";
                if (form_group.find(".text-danger")[0]) {
                    form.find(".text-danger").remove();
                }
                form_group.append(error_msg);
            });
        } else {
            // 500, 401, 404,...
            console.error(xhr.responseText);
        }
    }

    $("#revoke-client-modal").on("show.bs.modal", function() {
        var currentRow = $(event.target).closest("tr");
        var rowData = clients_dt.row(currentRow).data();
        $(this)
            .find("input[name=id]")
            .val(rowData[0]);
        // $(this).find('span.name').text(rowData[0]);
    });

    $("form#revoke-client").on("submit", function(event) {
        event.preventDefault();
        var form = $(this);
        var client_id = form.find("input[name=id]").val();

        window.$.ajax({
            type: "PUT",
            url: `${app}/clients/${client_id}/revoke`,
            success: function(user) {
                var alert = $.param({
                    ftype: "warning",
                    fmessage: `Client: ${user.name} has been revoked.`,
                    fimportant: false
                });

                window.location = `${app}/clients?${alert}`;
            },
            error: function(xhr, textStatus, errorThrown) {
                console.log({
                    xhr: xhr,
                    textStatus: textStatus,
                    errorThrown: errorThrown
                });
                show_errors(xhr, form);
            }
        });
    });

    $("#restore-client-modal").on("show.bs.modal", function() {
        var currentRow = $(event.target).closest("tr");
        var rowData = clients_dt.row(currentRow).data();
        $(this)
            .find("input[name=id]")
            .val(rowData[0]);
        // $(this).find('span.name').text(rowData[0]);
    });

    $("form#restore-client").on("submit", function(event) {
        event.preventDefault();
        var form = $(this);
        var client_id = form.find("input[name=id]").val();

        window.$.ajax({
            type: "PUT",
            url: `${app}/clients/${client_id}/restore`,
            success: function(user) {
                var alert = $.param({
                    ftype: "success",
                    fmessage: `Client: ${user.name} has been restored.`,
                    fimportant: false
                });

                window.location = `${app}/clients?${alert}`;
            },
            error: function(xhr, textStatus, errorThrown) {
                console.log({
                    xhr: xhr,
                    textStatus: textStatus,
                    errorThrown: errorThrown
                });
                show_errors(xhr, form);
            }
        });
    });

    $("#delete-client-modal").on("show.bs.modal", function() {
        var currentRow = $(event.target).closest("tr");
        var rowData = clients_dt.row(currentRow).data();
        $(this)
            .find("input[name=id]")
            .val(rowData[0]);
        // $(this).find('span.name').text(rowData[0]);
    });

    $("form#delete-client").on("submit", function(event) {
        event.preventDefault();
        var form = $(this);
        var client_id = form.find("input[name=id]").val();

        window.$.ajax({
            type: "DELETE",
            url: `${app}/clients/${client_id}`,
            success: function(user) {
                var alert = $.param({
                    ftype: "danger",
                    fmessage: "Client has been deleted.",
                    fimportant: false
                });

                window.location = `${app}/clients?${alert}`;
            },
            error: function(xhr, textStatus, errorThrown) {
                console.log({
                    xhr: xhr,
                    textStatus: textStatus,
                    errorThrown: errorThrown
                });
                show_errors(xhr, form);
            }
        });
    });
});
