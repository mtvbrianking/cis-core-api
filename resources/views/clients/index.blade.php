@extends('layouts.app')

@push('extra-js')
<script type="text/javascript">
    $(document).ready(function () {
        var clients_dt = $('table[id=clients]').DataTable({
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
                form.find('.text-danger').remove();
                form.find('input,select').closest('div.form-group').removeClass('has-error');

                var response = xhr.responseJSON;
                $.each(response.error, function (param, error) {
                    var form_group = form.find('input[name=' + param + '],select[name=' + param + ']').closest('div.form-group');
                    form_group.addClass('has-error');
                    var error_msg = '<small class="form-text text-danger">' + error[0] + '</small>';
                    if (form_group.find('.text-danger')[0]) {
                        form.find('.text-danger').remove();
                    }
                    form_group.append(error_msg);
                });
            } else { // 500, 401, 404,...
                console.error(xhr.responseText);
            }
        }

        $('#revoke-client-modal').on('show.bs.modal', function () {
            var currentRow = $(event.target).closest('tr');
            var rowData = clients_dt.row(currentRow).data();
            $(this).find('input[name=id]').val(rowData[0]);
            // $(this).find('span.name').text(rowData[0]);
        });

        $('form#revoke-client').on('submit', function (event) {
            event.preventDefault();
            var form = $(this);
            var client_id = form.find('input[name=id]').val();

            window.$.ajax({
                type: 'PUT',
                url: app + 'clients/' + client_id + '/revoke',
                success: function (user) {
                    var alert = $.param({
                        ftype: 'warning',
                        fmessage: 'Client: '+user.name+' has been revoked.',
                        fimportant: false
                    });

                    window.location = app + 'clients?'+alert;
                },
                error: function (xhr, textStatus, errorThrown) {
                    console.log({
                        xhr: xhr,
                        textStatus: textStatus,
                        errorThrown: errorThrown
                    });
                    show_errors(xhr, form);
                }
            });
        });

        $('#restore-client-modal').on('show.bs.modal', function () {
            var currentRow = $(event.target).closest('tr');
            var rowData = clients_dt.row(currentRow).data();
            $(this).find('input[name=id]').val(rowData[0]);
            // $(this).find('span.name').text(rowData[0]);
        });

        $('form#restore-client').on('submit', function (event) {
            event.preventDefault();
            var form = $(this);
            var client_id = form.find('input[name=id]').val();

            window.$.ajax({
                type: 'PUT',
                url: app + 'clients/' + client_id + '/restore',
                success: function (user) {
                    var alert = $.param({
                        ftype: 'success',
                        fmessage: 'Client: '+user.name+' has been restored.',
                        fimportant: false
                    });

                    window.location = app + 'clients?'+alert;
                },
                error: function (xhr, textStatus, errorThrown) {
                    console.log({
                        xhr: xhr,
                        textStatus: textStatus,
                        errorThrown: errorThrown
                    });
                    show_errors(xhr, form);
                }
            });
        });

        $('#delete-client-modal').on('show.bs.modal', function () {
            var currentRow = $(event.target).closest('tr');
            var rowData = clients_dt.row(currentRow).data();
            $(this).find('input[name=id]').val(rowData[0]);
            // $(this).find('span.name').text(rowData[0]);
        });

        $('form#delete-client').on('submit', function (event) {
            event.preventDefault();
            var form = $(this);
            var client_id = form.find('input[name=id]').val();

            window.$.ajax({
                type: 'DELETE',
                url: app + 'clients/' + client_id,
                success: function (user) {
                    var alert = $.param({
                        ftype: 'danger',
                        fmessage: 'Client has been deleted.',
                        fimportant: false
                    });

                    window.location = app + 'clients?'+alert;
                },
                error: function (xhr, textStatus, errorThrown) {
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
        <div class="row">
            <div class="col">
                @include('layouts.partials.js-alert')
            </div>
        </div>
        <div class="row justify-content-center">
            <div class="col-lg-12">
                <div class="card">
                    <div class="card-header">
                        <div class="d-flex justify-content-between">
                            <span>OAuth Clients</span>
                            <a href="{{ route('clients.create') }}" class="btn btn-sm btn-dark">
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
                                                <th>ID</th>
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
                                                    <td>
                                                        {{ $client->id }}
                                                    </td>
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
                                                            <i class="fa fa-retweet text-success" title="Restore"
                                                                data-toggle="modal" data-target="#restore-client-modal"></i>
                                                            <i class="fa fa-trash text-danger" title="Delete"
                                                                data-toggle="modal" data-target="#delete-client-modal"></i>
                                                        @else
                                                            <a href="#">
                                                                <i class="fa fa-key text-primary" title="Tokens"></i>
                                                            </a>
                                                            <a href="{{ route('clients.edit', $client->id) }}">
                                                                <i class="fa fa-pencil text-info" title="Edit"></i>
                                                            </a>
                                                            <i class="fa fa-ban text-warning" title="Revoke"
                                                                data-toggle="modal" data-target="#revoke-client-modal"></i>
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

    {{-- Revoke Client Modal --}}
    <div class="modal fade" id="revoke-client-modal" tabindex="-1" role="dialog"
        aria-labelledby="Revoke Client Modal" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <form id="revoke-client" action="" method="POST">
                @csrf
                @method('PUT')
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Revoke</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" name="id">
                        <span>You're about to revoke a client app.</span>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-sm btn-secondary" data-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-sm btn-warning">
                            <i class="fa fa-retweet"></i>&nbsp;Revoke
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    {{-- Restore Client Modal --}}
    <div class="modal fade" id="restore-client-modal" tabindex="-1" role="dialog"
        aria-labelledby="Restore Client Modal" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <form id="restore-client" action="" method="POST">
                @csrf
                @method('PUT')
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Restore</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" name="id">
                        <span>You're about to restore a client app.</span>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-sm btn-secondary" data-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-sm btn-success">
                            <i class="fa fa-retweet"></i>&nbsp;Restore
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
