@extends('layouts.app')

@push('extra-js')
{{-- Excel; html5 + jszip --}}
<script src="{{ asset('vendor/dataTables.net-buttons/js/buttons.html5.min.js') }}"></script>
<script src="{{ asset('vendor/jszip/dist/jszip.min.js') }}"></script>
<script src="{{ asset('js/pages/routes.min.js') }}"></script>
@endpush

@section('content')
    <div class="container-fluid">
        <div class="row">
            <div class="col">
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{ url('/') }}">Home</a></li>
                        <li class="breadcrumb-item active" aria-current="page">Routes</li>
                    </ol>
                </nav>
            </div>
        </div>
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
