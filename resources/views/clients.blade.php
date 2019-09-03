@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-md-12" style="margin-bottom: 15px;">
            <passport-clients></passport-clients>
        </div>

        <div class="col-md-12" style="margin-bottom: 15px;">
            <passport-authorized-clients></passport-authorized-clients>
        </div>

        <div class="col-md-12" style="margin-bottom: 15px;">
            <passport-personal-access-tokens></passport-personal-access-tokens>
        </div>
    </div>
</div>
@endsection
