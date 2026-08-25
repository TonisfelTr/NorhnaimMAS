<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta property="og:url" content="{{ url()->current() }}">
        <meta name="twitter:url" content="{{ url()->current() }}">

        @if(request('api_token'))
            <meta name="x-test-api-token" content="{{ request('api_token') }}">
            <script>
                window.TEST_API = { token: document.querySelector('meta[name="x-test-api-token"]').content };
            </script>
        @endif

        <title>@yield('title') - СМА Норхнейм</title>

        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
        <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Manrope:wght@400;500;600;700&family=Montserrat:wght@400;500;600;700&display=swap" rel="stylesheet">
        <link href="{{ url()->current() }}" rel="canonical">

        <link rel="icon" type="image/x-icon" href="{{ asset('favicon.ico') }}">
        <link rel="icon" type="image/png" sizes="32x32" href="{{ asset('assets/images/home/favicon/favicon-32x32.png') }}">
        <link rel="icon" type="image/png" sizes="16x16" href="{{ asset('assets/images/home/favicon/favicon-16x16.png') }}">
        <link rel="apple-touch-icon" sizes="180x180" href="{{ asset('assets/images/home/favicon/apple-touch-icon.png') }}">
        <link rel="manifest" href="{{ asset('site.webmanifest') }}">
        <meta name="theme-color" content="#f5f3ee">

        @vite(['resources/sass/app.sass', 'resources/sass/components.sass'])
        @vite('resources/js/app.js')

        <script src="https://www.google.com/recaptcha/api.js?render={{ env('RECAPTCHAV3_SITEKEY') }}"></script>
        @yield('assets')
    </head>

    <body class="@yield('body_class')">
        <div id="tooltip" style="display: none">
            <div id="arrow" class="tooltip-arrow"></div>
            <div class="tooltip-inner"></div>
        </div>

        <x-main-header-component />

        @hasSection('page_header')
            @yield('page_header')
        @else
            <section class="header">
                <div class="container">
                    <div class="row-cols-2 d-inline-flex justify-content-between">
                        <div class="col-sm-12 col-xs-12 col-md-12 col-lg-12 full-width__viewport">
                            <h1 class="site-title__header">Норхнейм</h1>
                            <div class="site-title__description">
                                Система медицинского ассистирования для персонала психиатрических клиник
                            </div>
                        </div>
                    </div>
                </div>
            </section>
        @endif

        <main>
            @yield('main')
        </main>

        <x-main-footer-component />
        <x-authorization-component />

        <script>
            grecaptcha.ready(function () {
                document.querySelectorAll('form').forEach(form => {
                    form.addEventListener('submit', function (event) {
                        const currentForm = this;

                        event.preventDefault();

                        grecaptcha.execute('{{ env('RECAPTCHAV3_SITEKEY') }}', { action: 'submit' })
                            .then(function (token) {
                                currentForm.querySelectorAll('[name="g-recaptcha-response"]')
                                    .forEach(input => input.value = token);

                                currentForm.submit();
                            });
                    });
                });
            });
        </script>

        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous" defer></script>
    </body>
</html>
