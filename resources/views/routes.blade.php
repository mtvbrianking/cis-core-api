@extends('layouts.app')

@push('extra-js')
{{-- Excel; html5 + jszip --}}
<script src="{{ asset('vendor/dataTables.net-buttons/js/buttons.html5.min.js') }}"></script>
<script src="{{ asset('vendor/jszip/dist/jszip.min.js') }}"></script>

<script type="text/javascript">
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
</script>
@endpush

@section('content')
    <div class="container-fluid">
        <div class="row justify-content-center">
            <div class="col-lg-12">
                <div class="card">
                    <div class="card-header">
                        <div class="d-flex justify-content-between">
                            <div class="">
                                Application Routes
                            </div>
                            <div class="export-btns">
                            </div>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col">
                                <div class="table-responsive">
                                    <table id="routes" class="table table-hover table-sm" style="width: 100%">
                                        <thead>
                                            <tr>
                                                <th>Method</th>
                                                <th>URI</th>
                                                <th>Name</th>
                                                <th>Action</th>
                                                <th>Middleware</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($routes as $route)
                                                <tr>
                                                    <td class="d-i-f">
                                                        @foreach ($route['methods'] as $method)
                                                            @if($method == "GET")
                                                                <label class="badge badge-success">{{ $method }}</label>
                                                            @elseif($method == "HEAD")
                                                                @continue;
                                                            @elseif($method == "PUT")
                                                                <label class="badge badge-info">{{ $method }}</label>
                                                            @elseif($method == "PATCH")
                                                                @continue;
                                                            @elseif($method == "POST")
                                                                <label class="badge badge-warning">{{ $method }}</label>
                                                            @elseif($method == "DELETE")
                                                                <label class="badge badge-danger">{{ $method }}</label>
                                                            @endif
                                                        @endforeach
                                                    </td>
                                                    <td>
                                                        {{ $route['uri'] }}
                                                    </td>
                                                    <td>
                                                        {{ $route['name'] }}
                                                    </td>
                                                    <td>
                                                        {{ $route['action'] }}
                                                        {{-- {{ ltrim($route['action'], 'App\\Http\\Controllers\\') }} --}}
                                                    </td>
                                                    <td>
                                                        {{ $route['middleware'] }}
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
