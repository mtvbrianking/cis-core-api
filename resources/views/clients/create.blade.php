@extends('layouts.app')

@push('extra-js')
<script type="text/javascript">
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
</script>
@endpush

@section('content')
    <div class="container-fluid">
        <div class="row">
            <div class="col">
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{ url('/') }}">Home</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('clients.index') }}">Clients</a></li>
                        <li class="breadcrumb-item active" aria-current="page">Register</li>
                    </ol>
                </nav>
            </div>
        </div>
        <div class="row justify-content-start">
            <div class="col-lg-6 col-md-9 col-sm-12">
                <div class="card">
                    <div class="card-header">
                        <div class="d-flex justify-content-between">
                            <div class="caption">Register</div>
                            <a href="{{ route('clients.index') }}" class="btn btn-sm btn-dark">
                                <i class="fa fa-eye"></i>&nbsp;Clients
                            </a>
                        </div>
                    </div>
                    <div class="card-body">
                        <form method="POST" action="{{ route('clients.store') }}">

                            @csrf

                            <div class="form-group row">
                                <label for="name" class="col-lg-3 col-md-4 col-sm-5 required">
                                    {{ __('Name') }}
                                </label>

                                <div class="col-lg-9 col-md-8 col-sm-7">
                                    <input type="text" id="name" name="name"
                                        class="form-control @error('name') is-invalid @enderror"
                                        value="{{ old('name') }}" required autofocus>

                                    @error('name')
                                        <div class="invalid-feedback" role="alert">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="form-group row">
                                <div class="col-lg-9 offset-lg-3 col-md-8 offset-md-4 col-sm-7 offset-sm-5">
                                    <div class="form-check">
                                        <input type="checkbox" id="personal_access_client" name="personal_access_client"
                                            class="form-check-input" {{ old('personal_access_client') ? 'checked' : '' }}
                                            value="1">

                                        <label class="form-check-label" for="personal_access_client">
                                            {{ __('Personal access client') }}
                                        </label>

                                        @error('personal_access_client')
                                            {{ $message }}
                                        @enderror
                                    </div>

                                    <div class="form-text small text-muted">
                                        Should only be used for testing purposes during development.
                                    </div>
                                </div>
                            </div>

                            <div class="form-group row">
                                <div class="col-lg-9 offset-lg-3 col-md-8 offset-md-4 col-sm-7 offset-sm-5">
                                    <div class="form-check">
                                        <input type="checkbox" id="password_client" name="password_client"
                                            class="form-check-input" {{ old('password_client') ? 'checked' : '' }}
                                            value="1">

                                        <label class="form-check-label" for="password_client">
                                            {{ __('Personal access client') }}
                                        </label>

                                        @error('password_client')
                                            {{ $message }}
                                        @enderror
                                    </div>

                                    <div class="form-text small text-muted">
                                        Should only be used for first party applications.
                                    </div>
                                </div>
                            </div>

                            <div class="form-group row redirect-uri-wrapper">
                                <label for="redirect" class="col-lg-3 col-md-4 col-sm-5">
                                    {{ __('Redirect URI') }}
                                </label>

                                <div class="col-lg-9 col-md-8 col-sm-7">
                                    <input type="url" id="redirect" name="redirect"
                                        class="form-control @error('redirect') is-invalid @enderror"
                                        value="{{ old('redirect') }}">

                                    @error('redirect')
                                        <div class="invalid-feedback" role="alert">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="form-group row mb-0">
                                <div class="col-lg-9 offset-lg-3 col-md-8 offset-md-4 col-sm-7 offset-sm-5">
                                    <button type="submit" class="btn btn-sm btn-block btn-primary">
                                        <i class="fa fa-pencil"></i>&nbsp;{{ __('Register') }}
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
