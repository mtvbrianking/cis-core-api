@extends('layouts.app')

@section('extra-css')
    <style type="text/css">
        html, body {
            background-color: #fff;
            color: #636b6f;
            font-family: 'Nunito', sans-serif;
            font-weight: 200;
            /*height: 100vh;*/
            margin: 0;
            padding-top: 70px;
        }

        .full-height {
            height: calc(100vh - 300px);
            /*height: 100vh;*/
        }

        .flex-center {
            align-items: center;
            display: flex;
            justify-content: center;
        }

        .position-ref {
            position: relative;
        }

        .top-right {
            position: absolute;
            right: 10px;
            top: 18px;
        }

        .content {
            text-align: center;
        }

        .title {
            font-size: 84px;
        }

        .links > a {
            color: #636b6f;
            padding: 0 25px;
            font-size: 13px;
            font-weight: 500;
            letter-spacing: .1rem;
            text-decoration: none;
            text-transform: uppercase;
        }

        .m-b-md {
            margin-bottom: 30px;
        }
    </style>
@endsection

@section('content')
<div class="container">
    <div class="flex-center position-ref full-height">
        <div class="content">
            <div class="title m-b-md">
                {{ config('app.name', 'CIS Core API') }}
            </div>

            <div class="links">
                <a href="#">API</a>
                <a href="#">Clients</a>
                <a href="#">Docs</a>
                <a href="#">Routes</a>
            </div>
        </div>
    </div>
</div>
@endsection
