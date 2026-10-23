import './bootstrap';
import { createApp } from 'vue';
import MedicineTable from './vue/MedicineTable.vue';
import LineCheckBox from './vue/LineCheckBox.vue';
import { computePosition, flip, shift, offset, arrow } from '@floating-ui/dom';

document.addEventListener('DOMContentLoaded', function () {
    const app = createApp({});

    function selector(CSSSelector, parentNode = null) {
        return parentNode ? parentNode.querySelector(CSSSelector) : document.querySelector(CSSSelector);
    }

    function selectorAll(CSSSelector, parentNode = null) {
        return parentNode ? parentNode.querySelectorAll(CSSSelector) : document.querySelectorAll(CSSSelector);
    }

    function lcb_checking() {
        const input = selector('input[type="checkbox"]', this);
        const lcb_switcher = selector('.line-checkbox__switcher', this);

        if (!input || !lcb_switcher) return;

        if (input.checked) {
            input.checked = false;
            lcb_switcher.classList.remove('checked');
        } else {
            input.checked = true;
            lcb_switcher.classList.add('checked');
        }
    }

    app.component('medicine-table', MedicineTable);
    app.component('line-check-box', LineCheckBox);

    const medicineTableRoot = selector('#medicine-table');
    if (medicineTableRoot) {
        app.mount(medicineTableRoot);
    }

    const hospitalRoomsRoot = selector('#hospital-rooms-root');
    if (hospitalRoomsRoot) {
        import('./vue/doctors/HospitalRoomsMap.vue')
            .then(({ default: HospitalRoomsMap }) => {
                createApp(HospitalRoomsMap, {
                    bootstrapUrl: hospitalRoomsRoot.dataset.bootstrapUrl || '',
                    roomStoreUrl: hospitalRoomsRoot.dataset.roomStoreUrl || '',
                    roomUpdateUrlTemplate: hospitalRoomsRoot.dataset.roomUpdateUrlTemplate || '',
                    roomDeleteUrlTemplate: hospitalRoomsRoot.dataset.roomDeleteUrlTemplate || '',
                    roomLayoutUrlTemplate: hospitalRoomsRoot.dataset.roomLayoutUrlTemplate || '',
                    bedAssignUrlTemplate: hospitalRoomsRoot.dataset.bedAssignUrlTemplate || '',
                    bedReleaseUrlTemplate: hospitalRoomsRoot.dataset.bedReleaseUrlTemplate || '',
                    patientSearchUrl: hospitalRoomsRoot.dataset.patientSearchUrl || '',
                    patientCardUrlTemplate: hospitalRoomsRoot.dataset.patientCardUrlTemplate || '',
                    clinicName: hospitalRoomsRoot.dataset.clinicName || '',
                }).mount(hospitalRoomsRoot);
            })
            .catch((error) => {
                console.error('Не удалось загрузить карту палат:', error);
                hospitalRoomsRoot.innerHTML =
                    '<div class="alert alert-danger">Не удалось загрузить интерфейс палат и коек.</div>';
            });
    }

    selectorAll('.line-checkbox').forEach(e => e.addEventListener('click', lcb_checking));

    const tooltip = selector('#tooltip');
    if (tooltip) {
        const tooltipContent = selector('.tooltip-inner', tooltip);
        const arrowElement = selector('#arrow');

        function updateTooltip(referenceEl) {
            computePosition(referenceEl, tooltip, {
                placement: 'top',
                middleware: [
                    offset(6),
                    flip(),
                    shift({ padding: 5 }),
                    arrow({ element: arrowElement }),
                ],
            }).then(({ x, y, middlewareData }) => {
                Object.assign(tooltip.style, {
                    left: `${x}px`,
                    top: `${y}px`,
                    display: 'block',
                });

                const { x: arrowX, y: arrowY } = middlewareData.arrow || {};
                Object.assign(arrowElement.style, {
                    left: arrowX != null ? `${arrowX}px` : '',
                    top: arrowY != null ? `${arrowY}px` : '',
                    right: '',
                    bottom: '',
                });
            });
        }

        function showTooltip(event) {
            event.preventDefault();
            tooltipContent.innerText = this.dataset.tooltip || this.dataset.title || this.title || '';
            tooltip.style.display = 'block';
            updateTooltip(this);
        }

        function hideTooltip() {
            tooltip.style.display = 'none';
        }

        selectorAll('abbr, [data-tooltip]').forEach((reference) => {
            // Для кнопок с кастомным тултипом убираем нативный title,
            // чтобы две подсказки не появлялись одновременно.
            if (reference.dataset.tooltip && reference.hasAttribute('title')) {
                reference.removeAttribute('title');
            }

            [
                ['mouseenter', showTooltip],
                ['mouseleave', hideTooltip],
                ['focus', showTooltip],
                ['blur', hideTooltip],
            ].forEach(([event, handler]) => reference.addEventListener(event, handler));
        });
    }

    function initializeNavbarDropdowns() {
        if (document.body.dataset.nhDropdownInit === '1') return;
        document.body.dataset.nhDropdownInit = '1';

        const toggleSelector = [
            '.dropdown-toggle',
            '[data-toggle="dropdown"]',
            '[data-bs-toggle="dropdown"]',
            '.menu-item-has-children > a',
            '.has-dropdown > a'
        ].join(', ');

        const menuSelector = '.dropdown-menu, .submenu, .sub-menu, ul[role="menu"]';
        const toggles = Array.from(document.querySelectorAll(toggleSelector));

        toggles.forEach((toggle) => {
            if (!toggle.hasAttribute('data-bs-toggle') && toggle.matches('.dropdown-toggle, [data-toggle="dropdown"], [data-bs-toggle="dropdown"]')) {
                toggle.setAttribute('data-bs-toggle', 'dropdown');
            }

            if (!toggle.hasAttribute('aria-expanded')) {
                toggle.setAttribute('aria-expanded', 'false');
            }
        });

        if (window.bootstrap && window.bootstrap.Dropdown) {
            toggles.forEach((toggle) => {
                if (toggle.matches('.dropdown-toggle, [data-bs-toggle="dropdown"], [data-toggle="dropdown"]')) {
                    window.bootstrap.Dropdown.getOrCreateInstance(toggle);
                }
            });
        }

        document.addEventListener('click', function (event) {
            const toggle = event.target.closest(toggleSelector);

            if (toggle) {
                const parent = toggle.closest('.dropdown, .menu-item-has-children, .has-dropdown, li') || toggle.parentElement;
                const menu = parent ? parent.querySelector(`:scope > ${menuSelector}, ${menuSelector}`) : null;

                if (!menu) return;

                event.preventDefault();
                event.stopPropagation();

                document.querySelectorAll('.dropdown-menu.show, .submenu.show, .sub-menu.show').forEach((opened) => {
                    if (opened !== menu) opened.classList.remove('show');
                });

                document.querySelectorAll('.dropdown.show, .menu-item-has-children.show, .has-dropdown.show').forEach((openedParent) => {
                    if (openedParent !== parent) openedParent.classList.remove('show');
                });

                const willOpen = !menu.classList.contains('show');
                menu.classList.toggle('show', willOpen);
                parent?.classList.toggle('show', willOpen);
                toggle.setAttribute('aria-expanded', willOpen ? 'true' : 'false');
                return;
            }

            if (!event.target.closest('.dropdown-menu, .submenu, .sub-menu')) {
                document.querySelectorAll('.dropdown-menu.show, .submenu.show, .sub-menu.show').forEach((menu) => menu.classList.remove('show'));
                document.querySelectorAll('.dropdown.show, .menu-item-has-children.show, .has-dropdown.show').forEach((dropdown) => dropdown.classList.remove('show'));
                toggles.forEach((item) => item.setAttribute('aria-expanded', 'false'));
            }
        });
    }

    initializeNavbarDropdowns();
    window.addEventListener('load', initializeNavbarDropdowns, { once: true });
});
