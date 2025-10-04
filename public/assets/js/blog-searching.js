document.addEventListener('DOMContentLoaded', () => {
    const filterButton = document.getElementById('filterButton');

    // Содержимое для popover
    const popoverContent = `
        <form id="filterForm" class="p-2">
            <div class="mb-3">
                <label for="category" class="form-label">Категория</label>
                <select class="form-select" id="category">
                    <option value="">Все категории</option>
                    <option value="1">Технологии</option>
                    <option value="2">Бизнес</option>
                    <option value="3">Образование</option>
                </select>
            </div>
            <div class="mb-3">
                <label for="dateRangeStart" class="form-label">Диапазон дат</label>
                <input type="date" class="form-control" id="dateRangeStart" placeholder="От">
                <input type="date" class="form-control mt-2" id="dateRangeEnd" placeholder="До">
            </div>
            <div class="mb-3">
                <label for="author" class="form-label">Автор</label>
                <input type="text" class="form-control" id="author" placeholder="Введите имя автора">
            </div>
            <button type="button" class="btn btn-primary w-100" id="applyFilters">Применить</button>
        </form>
    `;

    // Инициализация popover
    new bootstrap.Popover(filterButton, {
        content: popoverContent,
        html: true,
        placement: 'bottom',
        trigger: 'click',
    });

    // Применение фильтров
    document.body.addEventListener('click', (event) => {
        if (event.target.id === 'applyFilters') {
            const category = document.getElementById('category').value;
            const dateStart = document.getElementById('dateRangeStart').value;
            const dateEnd = document.getElementById('dateRangeEnd').value;
            const author = document.getElementById('author').value;

            console.log('Выбранные фильтры:');
            console.log(`Категория: ${category}`);
            console.log(`Диапазон дат: ${dateStart} - ${dateEnd}`);
            console.log(`Автор: ${author}`);

            // Закрытие popover
            bootstrap.Popover.getInstance(filterButton)?.hide();
        }
    });
});
