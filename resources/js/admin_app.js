import './bootstrap';
import { createApp } from 'vue';
import DrugForm from './vue/DrugForm.vue';
import DoctorPricelist from "./vue/DoctorPricelist.vue";
import flatpickr from "flatpickr";
import "flatpickr/dist/flatpickr.min.css";

if (document.getElementById('forms-line')) {
    const formsApp = createApp({});
    formsApp.component('drug-form', DrugForm);
    formsApp.mount('#forms-line');
}

if (document.getElementById('doctor-pricelist')) {
    const pricelistApp = createApp({});
    pricelistApp.component('doctor-pricelist', DoctorPricelist);
    pricelistApp.mount('#doctor-pricelist');
}

document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('input[required]').forEach(e => {
        var star = document.createElement('span');
        star.innerText = '*';
        star.style.color = 'red';

        e.parentElement.querySelector('label').append(star);
    })
});
