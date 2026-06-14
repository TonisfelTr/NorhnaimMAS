# Test Run Vue (1 вопрос на экране)

Этот архив добавляет изолированный режим прохождения психотеста:
- показывается только 1 вопрос
- кнопка «Следующий вопрос» вызывает submit (сохраняет ответ)
- кнопка «Завершить тест» вызывает finish (ставит status=submitted, in_progress=false)
- после finish показывается экран «Позовите врача», без перехода в панель

## Установка
1) Скопируй файлы в проект, сохранив пути:
- app/Http/Controllers/Doctors/TestSessionController.php
- resources/views/layouts/test_run.blade.php
- resources/views/doctors/reception/tests/run.blade.php
- resources/js/components/TestRunWizard.vue
- resources/js/test-run.js

2) В routes/web.php внутри doctors-группы добавь блок из:
PATCHES/web.php.additions.txt

3) В DoctorHardLockMiddleware добавь в allowlist:
- doctors.reception.tests.run
- doctors.reception.tests.payload
- doctors.reception.tests.submit
- doctors.reception.tests.finish
(и оставь pin.form/pin.verify)

4) Сборка фронта:
- Vite: добавь entry `resources/js/test-run.js` в @vite в layout (уже сделано)
- Mix: замени @vite на mix и добавь сборку JS как обычно.

## Примечания по данным
Payload берёт вопросы из `test.sections.items.options` (как в session_show.blade.php).
Тип вопроса определяется так:
- есть options -> radio
- нет options -> text (открытый)

Ответы сохраняются в таблицы:
- TestAnswer (radio)
- TestOpenResponse (text)
Параллельно сохраняется черновик в session.context['answers'] и прогресс current_index.
