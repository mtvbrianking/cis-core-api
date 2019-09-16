@extends('layouts.app')

@push('extra-js')
<script src="{{ asset('js/pages/personal-clients/index.js') }}"></script>
@endpush

@section('content')
    <div class="container-fluid">
        <div class="row">
            <div class="col">
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{ url('/') }}">Home</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('clients.index') }}">Clients</a></li>
                        <li class="breadcrumb-item active" aria-current="page">Personal</li>
                    </ol>
                </nav>
            </div>
        </div>
        <div class="row">
            <div class="col">
                @include('flash::message')
            </div>
        </div>
        <div class="row">
            <div class="col">
                @include('layouts.partials.js-alert')
            </div>
        </div>
        <div class="row justify-content-center">
            <div class="col">
                <div class="card">
                    <div class="card-header">
                        <div class="d-flex justify-content-between">
                            <span>Personal Clients</span>
                            <div>
                                <a href="{{ route('clients.index') }}" class="btn btn-sm btn-light">
                                    <i class="fa fa-list"></i>&nbsp;OAuth Clients
                                </a>
                                <a href="#" class="btn btn-sm btn-light">
                                    <i class="fa fa-list"></i>&nbsp;Authorized Clients
                                </a>
                                <a href="{{ route('clients.personal.create') }}" class="btn btn-sm btn-dark">
                                    <i class="fa fa-plus"></i>&nbsp;Register
                                </a>
                            </div>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col">
                                <div class="table-responsive">
                                    <table id="clients" class="table table-hover table-sm" style="width: 100%">
                                        <thead>
                                            <tr>
                                                <th>ID</th>
                                                <th>Name</th>
                                                <th class="center">Personal</th>
                                                <th class="center">Password</th>
                                                <th class="center">Revoked</th>
                                                <th>Created At</th>
                                                <th>Updated At</th>
                                                <th class="center">Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($clients as $personal)
                                                <tr>
                                                    <td>
                                                        {{ $personal->id }}
                                                    </td>
                                                    <td>
                                                        <a href="{{ route('clients.personal.show', $personal->id) }}">
                                                            {{ $personal->client->name }}
                                                        </a>
                                                    </td>
                                                    <td class="center">
                                                        @if($personal->client->personal_access_client)
                                                            <span class="badge badge-success">Yes</span>
                                                        @else
                                                            <span class="badge badge-warning">No</span>
                                                        @endif
                                                    </td>
                                                    <td class="center">
                                                        @if($personal->client->password_client)
                                                            <span class="badge badge-success">Yes</span>
                                                        @else
                                                            <span class="badge badge-warning">No</span>
                                                        @endif
                                                    </td>
                                                    <td class="center">
                                                        @if($personal->client->revoked)
                                                            <span class="badge badge-danger">Yes</span>
                                                        @else
                                                            <span class="badge badge-success">No</span>
                                                        @endif
                                                    </td>
                                                    <td>{{ $personal->client->created_at }}</td>
                                                    <td>{{ $personal->client->updated_at }}</td>
                                                    <td class="actions center">
                                                        <a href="#">
                                                            <i class="fa fa-key text-primary" title="Tokens"
                                                                data-toggle="modal" data-target="#create-token-modal"></i>
                                                        </a>
                                                        <a href="{{ route('clients.personal.edit', $personal->id) }}">
                                                            <i class="fa fa-pencil text-info" title="Edit"></i>
                                                        </a>
                                                        <i class="fa fa-trash text-danger" title="Delete"
                                                            data-toggle="modal" data-target="#delete-client-modal"></i>
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

    {{-- Create Token Modal --}}
    <div class="modal fade" id="create-token-modal" tabindex="-1" role="dialog"
        aria-labelledby="Create Token Modal" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <form id="create-token" action="" method="POST">
                @csrf
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Create</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" name="id">
                        <p class="text-danger">This will overriden any token if any, that's associated with this client.</p>
                        <textarea name="token" style="width: 100%"></textarea>
                        <p class="text-muted">This token is displayed only once.</p>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-sm btn-secondary" data-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-sm btn-success">
                            <i class="fa fa-retweet"></i>&nbsp;Generate
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    {{-- Delete Client Modal --}}
    <div class="modal fade" id="delete-client-modal" tabindex="-1" role="dialog"
        aria-labelledby="Delete Client Modal" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <form id="delete-client" action="" method="POST">
                @csrf
                @method('DELETE')
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Delete</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" name="id">
                        <span>You're about to delete a client app.</span>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-sm btn-secondary" data-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-sm btn-danger">
                            <i class="fa fa-retweet"></i>&nbsp;Delete
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
@endsection
