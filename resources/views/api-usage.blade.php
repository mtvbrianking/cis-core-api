<!DOCTYPE html>
<html>
<head>
    <title>API Usage Documentation</title>
    <!-- Needed for adaptive design -->
    <meta charset="utf-8"/>
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- ReDoc doesn't change outer page styles -->
    <style>
        body {
            margin: 0;
            padding: 0;
        }
    </style>
</head>
<body>

<redoc spec-url='{{ asset('js/api-usage.yml') }}'></redoc>

{{-- <script src="https://rebilly.github.io/ReDoc/releases/latest/redoc.min.js"></script> --}}

<script src="{{ asset('vendor/redoc/bundles/redoc.standalone.js') }}"></script>

</body>
</html>
