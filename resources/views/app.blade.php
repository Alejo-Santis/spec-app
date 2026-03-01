<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=0, minimal-ui">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title inertia>Spec App</title>
    <link rel="icon" type="image/svg+xml" href="/assets/images/favicon.svg">
    <link rel="icon" type="image/png" href="/assets/images/favicon_spec_app.png">

    {{-- Mantis Fonts --}}
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Public+Sans:wght@300;400;500;600;700&display=swap">

    {{-- Mantis Icons --}}
    <link rel="stylesheet" href="/mantis/fonts/tabler-icons.min.css">
    <link rel="stylesheet" href="/mantis/fonts/feather.css">
    <link rel="stylesheet" href="/mantis/fonts/fontawesome.css">
    <link rel="stylesheet" href="/mantis/fonts/material.css">

    {{-- Mantis Core CSS --}}
    <link rel="stylesheet" href="/mantis/css/style.css">
    <link rel="stylesheet" href="/mantis/css/style-preset.css">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @inertiaHead
</head>
<body data-pc-preset="preset-1" data-pc-direction="ltr" data-pc-theme="light">
    @inertia

    {{-- Mantis Core JS --}}
    <script src="/mantis/js/plugins/popper.min.js"></script>
    <script src="/mantis/js/plugins/simplebar.min.js"></script>
    <script src="/mantis/js/plugins/bootstrap.min.js"></script>
    <script src="/mantis/js/fonts/custom-font.js"></script>
    <script src="/mantis/js/pcoded.js"></script>
    <script src="/mantis/js/plugins/feather.min.js"></script>
    <script src="/mantis/js/plugins/sweetalert2.all.min.js"></script>
</body>
</html>
