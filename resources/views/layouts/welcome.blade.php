<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta property="og:url" content="{{ url()->current() }}">
        <meta name="twitter:url" content="{{ url()->current() }}">
        <title>@yield('title') - СМА Норхнейм</title>
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
        <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Montserrat:ital,wght@0,100..900;1,100..900&family=Poppins:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&display=swap" rel="stylesheet">
        <link href="{{ url()->current() }}" rel="canonical">
        @vite(['resources/sass/app.sass', 'resources/sass/components.sass'])
        @vite('resources/js/app.js')
        <script src="https://www.google.com/recaptcha/api.js?render={{ env('RECAPTCHAV3_SITEKEY') }}"></script>
        @yield('assets')
    </head>
    <body>
        <div id="tooltip" style="display: none">
            <div id="arrow" class="tooltip-arrow"></div>
            <div class="tooltip-inner"></div>
        </div>
        <x-main-header-component />
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
        <main>
            @yield('main')
        </main>
        <x-main-footer-component />
        <x-authorization-component />
        <script>
            grecaptcha.ready(function () {
                document.querySelectorAll('form').forEach(e => e.addEventListener('submit', onFormSubmit));

                function onFormSubmit(event) {
                    let form = this;

                    event.preventDefault();
                        grecaptcha.execute('{{ env('RECAPTCHAV3_SITEKEY') }}', {action: 'submit'}).then(function (token) {
                            form.querySelectorAll('[name="g-recaptcha-response"]').forEach(captchaInput => captchaInput.value = token);
                            form.submit();
                        });
                }
            })
        </script>
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous" defer></script>
        <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.8/dist/umd/popper.min.js" integrity="sha384-I7E8VVD/ismYTF4hNIPjVp/Zjvgyol6VFvRkX/vR+Vc4jQkC+hVqc2pM8ODewa9r" crossorigin="anonymous" defer></script>
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.min.js" integrity="sha384-0pUGZvbkm6XF6gxjEnlmuGrJXVbNuzT9qBBavbLwCsOGabYfZo0T0to5eqruptLy" crossorigin="anonymous" defer></script>
    </body>
</html>