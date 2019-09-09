$(document).ready(function () {
    var routes_dt = $('table[id=routes]').DataTable({
        pageLength: 10,
        language: {
            emptyTable: "No routes available",
            info: "Showing _START_ to _END_ of _TOTAL_ routes",
            infoEmpty: "Showing 0 to 0 of 0 routes",
            infoFiltered: "(filtered from _MAX_ total routes)",
            lengthMenu: "Show _MENU_ routes",
            search: "Search routes:",
            zeroRecords: "No routes match search criteria"
        },
        order: [
            [1, 'asc'],
        ],
        buttons: [
            {
                extend: 'excel',
                className: 'btn btn-sm',
                text: '<i class="fa fa-file-excel-o"></i> Excel',
                exportOptions: {
                    columns: [0,1,2,3,4]
                }
            }
        ]
    });

    routes_dt.buttons().container().appendTo('.export-btns');
});
