import { Modal } from 'bootstrap';

document.addEventListener('DOMContentLoaded', function () {
    // Инициализация формы для массовых действий
    const bulkForm = document.getElementById('bulk-action-form');
    if (bulkForm) {
        const bulkActionButtons = document.querySelectorAll('.bulk-action-btn');

        bulkActionButtons.forEach(button => {
            button.addEventListener('click', function () {
                // Устанавливаем action для формы
                bulkForm.action = this.dataset.action;

                // Отправляем форму
                bulkForm.submit();
            });
        });
    }

    // Обработка чекбоксов
    const selectAllCheckbox = document.getElementById('select_all');
    if (selectAllCheckbox) {
        selectAllCheckbox.addEventListener('change', function () {
            let checkboxes = document.querySelectorAll('input[type="checkbox"][name="selected[]"]');
            checkboxes.forEach(e => e.checked = this.checked);
        });
    }

    // Инициализация модалки для удаления
    const deleteButtons = document.querySelectorAll('.delete-btn'); // Кнопки удаления
    const modalElement = document.getElementById('delete-modal');
    if (modalElement) {
        const modal = new Modal(modalElement); // Используем импортированный Modal
        const modalForm = document.getElementById('modal-form'); // Форма внутри модалки

        deleteButtons.forEach(button => {
            button.addEventListener('click', function (e) {
                e.preventDefault(); // Остановить переход по ссылке

                // Получаем ссылку на удаление из атрибута href
                const actionLink = this.getAttribute('href');

                // Устанавливаем action формы
                modalForm.setAttribute('action', actionLink);

                // Показываем модалку
                modal.show();
            });
        });
    }
});
