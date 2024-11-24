import './bootstrap';
import { createApp } from 'vue';
import MedicineTable from './vue/MedicineTable.vue';
import LineCheckBox from './vue/LineCheckBox.vue';
import DrugForm from './vue/DrugForm.vue';
import { computePosition, flip, shift, offset, arrow } from '@floating-ui/dom';

document.addEventListener('DOMContentLoaded', function () {
    var app = createApp({})

    function selector(CSSSelector, parentNode = null) {
        return parentNode ? parentNode.querySelector(CSSSelector) : document.querySelector(CSSSelector)
    }

    function selectorAll(CSSSelector, parentNode = null) {
        return parentNode ? parentNode.querySelectorAll(CSSSelector) : document.querySelectorAll(CSSSelector)
    }

    function lcb_checking() {
        var input = selector('input[type="checkbox"]', this);
        var lcb_switcher = selector('.line-checkbox__switcher', this);

        if (input.checked) {
            input.checked = false;
            lcb_switcher.classList.remove('checked')
        } else {
            input.checked = true;
            lcb_switcher.classList.add('checked')
        }
    }

    function makeBallons() {
        const tooltip = selector('#tooltip');
        const tooltipContent = selector('.tooltip-inner', tooltip);
        const arrowElement = selector('#arrow');

        function showTooltip(event, content) {
            tooltipContent.textContent = content;
            computePosition(event.target, tooltip, {
                placement: 'top',
                middleware: [
                    offset(6),
                    flip(),
                    shift({ padding: 5 }),
                    arrow({ element: arrowElement }),
                ],
            }).then(({ x, y, placement, middlewareData }) => {
                Object.assign(tooltip.style, {
                    left: `${x}px`,
                    top: `${y}px`,
                    display: 'block',
                });

                const { x: arrowX, y: arrowY } = middlewareData.arrow;
                const staticSide = {
                    top: 'bottom',
                    right: 'left',
                    bottom: 'top',
                    left: 'right',
                }[placement.split('-')[0]];

                Object.assign(arrowElement.style, {
                    left: arrowX != null ? `${arrowX}px` : '',
                    top: arrowY != null ? `${arrowY}px` : '',
                    right: '',
                    bottom: '',
                });
            });
        }

        function hideTooltip() {
            tooltip.style.display = 'none';
        }

        const abbreviations = selectorAll('abbr');
        abbreviations.forEach((abbr) => {
            abbr.addEventListener('mouseenter', (event) => {
                event.preventDefault();

                showTooltip(event, abbr.title);
            });
            abbr.addEventListener('mouseleave', hideTooltip);
        });
    }

    function show(CSSSelector, displayProperty = 'block') {
        selectorAll(CSSSelector).forEach(DOMElement => DOMElement.style.display = displayProperty)
    }

    function hide(CSSSelector) {
        selectorAll(CSSSelector).forEach(DOMElement => DOMElement.style.display = 'none')
    }

    function update() {
        computePosition(selector('abbr'), tooltip, {
            placement: 'top',
            middleware: [
                offset(6),
                flip(),
                shift({ padding: 5 }),
                arrow({ element: arrowElement }),
            ],
        }).then(({x, y, placement, middlewareData}) => {
            Object.assign(tooltip.style, {
                left: `${x}px`,
                top: `${y}px`,
                display: 'block',
            });

            const { x: arrowX, y: arrowY } = middlewareData.arrow;

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
        tooltip.style.display = 'block';
        update();
    }

    function hideTooltip() {
        tooltip.style.display = 'none';
    }

    app.component('medicine-table', MedicineTable)
    app.mount('#medicine-table')
    app.component('line-check-box', LineCheckBox)

    selectorAll('.line-checkbox').forEach(e => e.addEventListener('click', lcb_checking))

    const tooltip = selector('#tooltip');
    const tooltipContent = selector('.tooltip-inner', tooltip);
    const arrowElement = selector('#arrow');

    [
        ['mouseenter', showTooltip],
        ['mouseleave', hideTooltip],
        ['focus', showTooltip],
        ['blur', hideTooltip],
    ].forEach(([event, listener]) => {
        const abbreviations = selectorAll('abbr');
        abbreviations.forEach(e => {
            e.addEventListener(event, listener)
            tooltipContent.innerText = e.dataset.title
        });
    });
    selectorAll('.images-container__with-overlay').forEach(e => {
       e.addEventListener('mouseenter', event => {
           event.target.querySelector('.images-container__overlay').classList.toggle('d-none')
           event.target.querySelector('.images-container__overlay + picture img').classList.toggle('ic-slide');
       })
       e.addEventListener('mouseleave', event => {
           event.target.querySelector('.images-container__overlay').classList.toggle('d-none')
           event.target.querySelector('.images-container__overlay + picture img').classList.toggle('ic-slide');
       })
    });
});
