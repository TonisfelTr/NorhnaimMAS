import { createApp } from 'vue';
import TestRunWizard from './components/TestRunWizard.vue';

const el = document.getElementById('test-run-app');
if (el) {
  createApp(TestRunWizard, {
    payloadUrl: el.dataset.payloadUrl,
    submitUrl: el.dataset.submitUrl,
    finishUrl: el.dataset.finishUrl,
  }).mount(el);
}
