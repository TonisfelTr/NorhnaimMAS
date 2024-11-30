import './bootstrap';
import { createApp } from 'vue';
import DrugForm from './vue/DrugForm.vue';
import DoctorPricelist from "./vue/DoctorPricelist.vue";

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
