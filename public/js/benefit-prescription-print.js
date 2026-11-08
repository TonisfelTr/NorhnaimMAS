/* Only presentation and local browser printing. No requests, storage or mutations. */
(() => {
    'use strict';

    const pages = document.getElementById('benefitPrintPages');
    const layoutSelect = document.getElementById('benefitPrintLayout');
    const printButton = document.getElementById('benefitPrintButton');
    const instructions = document.getElementById('benefitPrintInstructions');
    const notice = document.getElementById('benefitPrintNotice');
    if (!pages || !layoutSelect || !printButton || !instructions || !notice) return;

    const frontSheet = pages.querySelector('.bp-sheet--front');
    const backSheet = pages.querySelector('.bp-sheet--back');
    const fronts = Array.from(pages.querySelectorAll('[data-side="front"]'));
    const backs = Array.from(pages.querySelectorAll('[data-side="back"]'));
    if (!frontSheet || !backSheet || fronts.length !== 2 || backs.length !== 2) return;

    let ready = false;

    const showNotice = (message) => {
        notice.textContent = message;
        notice.hidden = !message;
    };

    function setLayout(layout) {
        const large = layout === 'large';
        document.body.classList.toggle('bp-layout-large', large);
        document.body.classList.remove('bp-layout-tight');
        layoutSelect.value = large ? 'large' : 'compact';
        if (large) {
            pages.replaceChildren(fronts[0], backs[0], fronts[1], backs[1]);
            instructions.textContent = 'А4, книжная ориентация, масштаб 100%, без колонтитулов. '
                + 'Двусторонняя печать с переворотом по длинной стороне, один комплект. '
                + 'Четыре страницы: лицевая и оборотная сторона первого экземпляра, затем второго.';
        } else {
            frontSheet.replaceChildren(...fronts);
            backSheet.replaceChildren(...backs);
            pages.replaceChildren(frontSheet, backSheet);
            instructions.textContent = 'А4, альбомная ориентация, масштаб 100%, без колонтитулов. '
                + 'Двусторонняя печать с переворотом по короткой стороне, один комплект. '
                + 'Две страницы: лицевые стороны, затем оборотные. После печати разрежьте лист пополам.';
        }
    }

    function hasOverflow() {
        return [...fronts, ...backs].some((form) => {
            const style = getComputedStyle(form);
            const content = form.querySelector('.bp-form__content');
            const availableHeight = form.clientHeight - parseFloat(style.paddingTop) - parseFloat(style.paddingBottom);
            const availableWidth = form.clientWidth - parseFloat(style.paddingLeft) - parseFloat(style.paddingRight);
            return !content || content.getBoundingClientRect().height > availableHeight + 1
                || content.scrollWidth > availableWidth + 1;
        });
    }

    function verifyLayout(allowFallback = true) {
        if (!ready) return false;
        document.body.classList.remove('bp-layout-overflow');
        let overflow = hasOverflow();
        let adjusted = false;
        if (overflow && allowFallback && !document.body.classList.contains('bp-layout-large')) {
            setLayout('large');
            adjusted = true;
            overflow = hasOverflow();
        }
        if (overflow && allowFallback && document.body.classList.contains('bp-layout-large')
            && !document.body.classList.contains('bp-layout-tight')) {
            document.body.classList.add('bp-layout-tight');
            adjusted = true;
            overflow = hasOverflow();
        }
        if (adjusted && !overflow) {
            showNotice('Для полного текста выбран один рецепт на странице А4. '
                + 'Все сведения сохранены; настройка двусторонней печати указана выше.');
        }
        document.body.classList.toggle('bp-layout-overflow', overflow);
        document.body.classList.add('bp-layout-ready');
        printButton.disabled = overflow;
        if (overflow) {
            showNotice('Текст не помещается даже на крупном бланке. Печать остановлена, чтобы не обрезать сведения. '
                + 'Проверьте длину реквизитов и назначения в медицинской карте. Исправленный рецепт оформляется отдельно.');
        }
        return !overflow;
    }

    layoutSelect.addEventListener('change', () => {
        showNotice('');
        setLayout(layoutSelect.value);
        verifyLayout();
    });

    printButton.addEventListener('click', () => {
        if (verifyLayout()) window.print();
    });

    window.addEventListener('beforeprint', () => verifyLayout());
    window.addEventListener('afterprint', () => verifyLayout());
    window.addEventListener('resize', () => verifyLayout());

    const fontsReady = document.fonts ? document.fonts.ready : Promise.resolve();
    fontsReady.then(() => {
        if (!getComputedStyle(document.documentElement).getPropertyValue('--bp-page-height').trim()) {
            showNotice('Не удалось загрузить оформление бланка. Обновите страницу перед печатью.');
            return;
        }
        ready = true;
        setLayout('compact');
        verifyLayout();
    });
})();
