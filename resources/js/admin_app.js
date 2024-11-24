import './bootstrap';
import { createApp } from 'vue';
import DrugForm from './vue/DrugForm.vue';

var app = createApp({})

app.component('drug-form', DrugForm)
app.mount('#forms-line');
