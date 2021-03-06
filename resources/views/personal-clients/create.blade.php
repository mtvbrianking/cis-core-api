@extends('layouts.app')

@section('content')
    <div class="container-fluid">
        <div class="row">
            <div class="col">
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{ url('/') }}">Home</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('clients.index') }}">Clients</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('clients.personal.index') }}">Personal</a></li>
                        <li class="breadcrumb-item active" aria-current="page">Register</li>
                    </ol>
                </nav>
            </div>
        </div>
        <div class="row">
            <div class="col-lg-6 col-md-9 col-sm-12">
                @include('flash::message')
            </div>
        </div>
        <div class="row justify-content-start">
            <div class="col-lg-6 col-md-9 col-sm-12">
                <div class="card">
                    <div class="card-header">
                        <div class="d-flex justify-content-between">
                            <div class="caption">Register</div>
                            <a href="{{ route('clients.personal.index') }}" class="btn btn-sm btn-dark">
                                <i class="fa fa-eye"></i>&nbsp;Personal Clients
                            </a>
                        </div>
                    </div>
                    <div class="card-body">
                        <form method="POST" action="{{ route('clients.personal.store') }}">

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
