@extends('layouts.app')

@push('extra-js')
<script type="text/javascript">
    $('#myModal').on('shown.bs.modal', function () {
        $('#myInput').trigger('focus')
    })
</script>
@endpush

@section('content')
    <div class="container-fluid">
        <div class="row">
            <div class="col">
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{ url('/') }}">Home</a></li>
                        <li class="breadcrumb-item active" aria-current="page">Clients</li>
                    </ol>
                </nav>
            </div>
        </div>
        <div class="row justify-content-center">
            <div class="col-lg-12">
                <div class="card">
                    <div class="card-header">
                        <div class="d-flex justify-content-between">
                            <span>OAuth Clients</span>
                            <a href="#Register" class="btn btn-sm btn-dark" data-toggle="modal" data-target="#exampleModal">
                                <i class="fa fa-plus"></i>&nbsp;Register
                            </a>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col">
                                <div class="table-responsive">
                                    <table id="clients" class="table table-hover table-sm" style="width: 100%">
                                        <thead>
                                            <tr>
                                                {{-- <th>ID</th> --}}
                                                {{-- <th>Secret</th> --}}
                                                <th>Name</th>
                                                <th>Redirect URI</th>
                                                <th class="center">Personal</th>
                                                <th class="center">Password</th>
                                                <th class="center">Revoked</th>
                                                <th>Created At</th>
                                                <th>Updated At</th>
                                                <th class="center">Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($clients as $client)
                                                <tr>
                                                    {{-- <td>{{ $client->id }}</td> --}}
                                                    {{-- <td>{{ $client->secret }}</td> --}}
                                                    <td>
                                                        <a href="{{ route('clients.show', $client->id) }}">
                                                            {{ $client->name }}
                                                        </a>
                                                    </td>
                                                    <td>
                                                        <a href="{{ $client->redirect }}" target="_blank">
                                                            {{ $client->redirect }}
                                                        </a>
                                                    </td>
                                                    <td class="center">
                                                        @if($client->personal_access_client)
                                                            <i class="fa fa-check-circle text-default"></i>
                                                        @endif
                                                    </td>
                                                    <td class="center">
                                                        @if($client->password_client)
                                                            <i class="fa fa-check-circle text-success"></i>
                                                        @endif
                                                    </td>
                                                    <td class="center">
                                                        @if($client->revoked)
                                                            <i class="fa fa-check-circle text-danger"></i>
                                                        @endif
                                                    </td>
                                                    <td>{{ $client->created_at }}</td>
                                                    <td>{{ $client->updated_at }}</td>
                                                    <td class="actions center">
                                                        @if($client->revoked)
                                                            <i class="fa fa-retweet text-success" title="Restore"></i>
                                                            <i class="fa fa-trash text-danger" title="Delete"></i>
                                                        @else
                                                            <i class="fa fa-key text-primary" title="Tokens"></i>
                                                            <i class="fa fa-pencil text-info" title="Edit"></i>
                                                            <i class="fa fa-ban text-warning" title="Revoke"></i>
                                                        @endif
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

    <!-- Modal -->
    <div class="modal fade" id="exampleModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
      <div class="modal-dialog" role="document">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title" id="exampleModalLabel">Modal title</h5>
            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
              <span aria-hidden="true">&times;</span>
            </button>
          </div>
          <div class="modal-body">
            <p>Modal body text goes here.</p>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
            <button type="button" class="btn btn-primary">Save changes</button>
          </div>
        </div>
      </div>
    </div>
@endsection
