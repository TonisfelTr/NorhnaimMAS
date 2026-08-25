<footer class="nh-site-footer">
    <div class="container-xl nh-site-footer__inner">
        <div class="nh-site-footer__brand">
            <img
                src="{{ asset('assets/images/home/norhneim-viking-mark.png') }}"
                alt=""
                aria-hidden="true"
                class="nh-site-footer__mark"
            >
            <div>
                <strong>Норхнейм</strong>
                <span>Система медицинского ассистирования</span>
            </div>
        </div>

        <nav class="nh-site-footer__nav" aria-label="Навигация в подвале">
            <div class="nh-site-footer__column">
                <span class="nh-site-footer__heading">Система</span>
                <a href="{{ route('main.index') }}">Главная</a>
                <a href="{{ route('main.clinics') }}">Врачи и клиники</a>
                <a href="{{ route('main.medicines') }}">Реестр лекарств</a>
            </div>

            <div class="nh-site-footer__column">
                <span class="nh-site-footer__heading">Информация</span>
                <a href="{{ route('main.blog') }}">Блог</a>
                <a href="{{ route('main.jurisprudence') }}">Юриспруденция</a>
                <a href="{{ route('main.feedback') }}">Обратная связь</a>
            </div>
        </nav>

        <div class="nh-site-footer__meta">
            <p>© {{ date('Y') }} Норхнейм</p>
            <p>Все права защищены</p>
            <a href="{{ route('main.policy') }}">Политика безопасности</a>
        </div>
    </div>
</footer>
