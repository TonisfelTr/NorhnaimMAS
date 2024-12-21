<footer>
    <nav class="pb-5">
        <ul class="d-inline-flex justify-content-center link-list">
            <li><a href="{{ route('main.index') }}">Главная</a></li>
            <li><a href="{{ route('main.clinics') }}">Врачи и клиники</a></li>
            <li><a href="{{ route('main.medicines') }}">Зарегистрированные лекарства</a></li>
            <li><a href="{{ route('main.feedback') }}">Обратная связь</a></li>
        </ul>
    </nav>
    <div class="copyrights">
        <p>Norhnaim© - система медицинского ассистирования</p>
        <p>Все права защищены, {{ date('Y') }} год</p>
    </div>
    <a class="privacy-policy__link" href="{{ route('main.policy') }}">Политика безопасности</a>
</footer>
