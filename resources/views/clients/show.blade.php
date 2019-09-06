@extends('layouts.app')

@section('extra-css')
<style type="text/css">
    .breadcrumb {
        padding: 0;
        background-color: transparent;
    }

    .breadcrumb-item + .breadcrumb-item::before {
        content: "/";
    }

    span.caption {
        margin-top: 2px;
        font-size: 16px;
    }

    div.vrow {
        margin-top: 0.5rem;
        border-bottom: 1px solid rgba(128,128,128,.1);
    }

    label.vlabel {
        font-weight: 400;
    }

    label.vvalue {
        font-weight: 600;
    }
</style>
@endsection

@section('content')
    <div class="container-fluid">
        <div class="row">
            <div class="col">
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{ url('/') }}">Home</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('clients.index') }}">Clients</a></li>
                        <li class="breadcrumb-item active" aria-current="page">{{ $client->name }}</li>
                    </ol>
                </nav>
            </div>
        </div>
        <div class="row justify-content-start">
            <div class="col-lg-6 col-md-9 col-sm-12">
                <div class="card">
                    <div class="card-header">
                        <div class="d-flex justify-content-between">
                            <span class="caption">View</span>
                            <a href="#Edit" class="btn btn-sm btn-dark">
                                <i class="fa fa-pencil"></i>&nbsp;Edit
                            </a>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="row vrow">
                            <label class="control-label vlabel col-md-4 col-sm-5 col-xs-12">
                                ID
                            </label>
                            <label class="control-label vvalue col-md-8 col-sm-7 col-xs-12">
                                <code>{{ $client->id }}</code>
                            </label>
                        </div>
                        <div class="row vrow">
                            <label class="control-label vlabel col-md-4 col-sm-5 col-xs-12">
                                Secret
                            </label>
                            <label class="control-label vvalue col-md-8 col-sm-7 col-xs-12">
                                <code>{{ $client->secret }}</code>
                            </label>
                        </div>
                        <div class="row vrow">
                            <label class="control-label vlabel col-md-4 col-sm-5 col-xs-12">
                                Name
                            </label>
                            <label class="control-label vvalue col-md-8 col-sm-7 col-xs-12">
                                {{ $client->name }}
                            </label>
                        </div>
                        <div class="row vrow">
                            <label class="control-label vlabel col-md-4 col-sm-5 col-xs-12">
                                Redirect URI
                            </label>
                            <label class="control-label vvalue col-md-8 col-sm-7 col-xs-12">
                                <a href="{{ $client->redirect }}" target="_blank">
                                    {{ $client->redirect }}
                                </a>
                            </label>
                        </div>
                        <div class="row vrow">
                            <label class="control-label vlabel col-md-4 col-sm-5 col-xs-12">
                                Grant Type
                            </label>
                            <label class="control-label vvalue col-md-8 col-sm-7 col-xs-12">
                                @if($client->password_client)
                                    <span class="badge badge-success">Password</span>
                                @else
                                    <span class="badge badge-primary">Authorization Code</span>
                                @endif

                                @if($client->personal_access_client)
                                    <span class="badge badge-warning">Personal *</span>
                                @endif
                            </label>
                        </div>
                        <div class="row vrow">
                            <label class="control-label vlabel col-md-4 col-sm-5 col-xs-12">
                                Status
                            </label>
                            <label class="control-label vvalue col-md-8 col-sm-7 col-xs-12">
                                @if($client->revoked)
                                    <span class="badge badge-danger">Revoked</span>
                                @else
                                    <span class="badge label-sm badge-success">Active</span>
                                @endif
                            </label>
                        </div>
                        <div class="row vrow">
                            <label class="control-label vlabel col-md-4 col-sm-5 col-xs-12">
                                Created At
                            </label>
                            <label class="control-label vvalue col-md-8 col-sm-7 col-xs-12">
                                {{ $client->created_at->format('D, jS M Y \\a\\t g:ia') }}
                            </label>
                        </div>
                        <div class="row vrow">
                            <label class="control-label vlabel col-md-4 col-sm-5 col-xs-12">
                                Updated At
                            </label>
                            <label class="control-label vvalue col-md-8 col-sm-7 col-xs-12">
                                {{ $client->updated_at->format('D, jS M Y \\a\\t g:ia') }}
                            </label>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
