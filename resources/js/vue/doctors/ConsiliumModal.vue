<template>
    <Teleport to="body">
        <Transition name="consilium-fade">
            <div
                v-if="opened"
                class="consilium-overlay"
                @mousedown.self="close"
            >
                <section
                    class="consilium-modal"
                    role="dialog"
                    aria-modal="true"
                    aria-labelledby="consilium-title"
                >
                    <div class="consilium-glow consilium-glow--one"></div>
                    <div class="consilium-glow consilium-glow--two"></div>

                    <!-- HEADER -->
                    <header class="consilium-header">
                        <div class="consilium-header__main">
                            <div class="consilium-header-icon" aria-hidden="true">
                                <i class="bi bi-people-fill"></i>
                            </div>

                            <div class="consilium-header-text">
                                <div class="consilium-eyebrow">
                                    <span class="consilium-eyebrow__dot"></span>
                                    Врачебный консилиум
                                </div>

                                <h2 id="consilium-title">
                                    Создать консилиум
                                </h2>

                                <p>
                                    Один клинический случай, один пациент и команда специалистов
                                </p>
                            </div>
                        </div>

                        <button
                            type="button"
                            class="consilium-close"
                            aria-label="Закрыть"
                            :disabled="saving"
                            @click="close"
                        >
                            <i class="bi bi-x-lg"></i>
                        </button>
                    </header>

                    <!-- BODY -->
                    <div class="consilium-body">
                        <div
                            v-if="generalError"
                            class="consilium-alert"
                        >
                            <div class="consilium-alert__icon">
                                <i class="bi bi-exclamation-triangle"></i>
                            </div>

                            <div>
                                <strong>Не удалось выполнить действие</strong>
                                <span>{{ generalError }}</span>
                            </div>
                        </div>

                        <div class="consilium-workspace">
                            <!-- LEFT COLUMN -->
                            <div class="consilium-column">
                                <!-- CASE -->
                                <section class="consilium-card">
                                    <div class="consilium-card__head">
                                        <div class="consilium-card__head-main">
                                            <div class="consilium-card__icon">
                                                <i class="bi bi-journal-medical"></i>
                                            </div>

                                            <div>
                                                <h3>Клинический случай</h3>
                                                <p>Название и планируемое время обсуждения</p>
                                            </div>
                                        </div>

                                        <span class="consilium-step">01</span>
                                    </div>

                                    <div class="consilium-grid consilium-grid--case">
                                        <div class="field">
                                            <label for="consilium-title-input">
                                                Название
                                                <span class="required">*</span>
                                            </label>

                                            <div class="control-wrap">
                                                <i class="bi bi-pencil-square control-icon"></i>

                                                <input
                                                    id="consilium-title-input"
                                                    v-model.trim="form.title"
                                                    type="text"
                                                    autocomplete="off"
                                                    placeholder="Например: Разбор клинического случая"
                                                    :class="{ invalid: hasError('title') }"
                                                >
                                            </div>

                                            <div
                                                v-if="hasError('title')"
                                                class="field-error"
                                            >
                                                <i class="bi bi-exclamation-circle"></i>
                                                {{ firstError('title') }}
                                            </div>
                                        </div>

                                        <div class="field">
                                            <label for="consilium-datetime">
                                                Дата и время
                                            </label>

                                            <div class="control-wrap">
                                                <i class="bi bi-calendar3 control-icon"></i>

                                                <input
                                                    id="consilium-datetime"
                                                    v-model="form.datetime"
                                                    type="datetime-local"
                                                    :class="{ invalid: hasError('datetime') }"
                                                >
                                            </div>

                                            <div
                                                v-if="hasError('datetime')"
                                                class="field-error"
                                            >
                                                <i class="bi bi-exclamation-circle"></i>
                                                {{ firstError('datetime') }}
                                            </div>
                                        </div>
                                    </div>
                                </section>

                                <!-- PATIENT -->
                                <section class="consilium-card consilium-card--patient">
                                    <div class="consilium-card__head">
                                        <div class="consilium-card__head-main">
                                            <div class="consilium-card__icon consilium-card__icon--patient">
                                                <i class="bi bi-person-vcard"></i>
                                            </div>

                                            <div>
                                                <h3>Пациент и диагноз</h3>
                                                <p>В одном консилиуме рассматривается только один пациент</p>
                                            </div>
                                        </div>

                                        <span class="consilium-step">02</span>
                                    </div>

                                    <div class="field">
                                        <div class="field-label-row">
                                            <label for="consilium-patient-search">
                                                Пациент
                                                <span class="required">*</span>
                                            </label>

                                            <span class="field-hint">
                                                <i class="bi bi-lock-fill"></i>
                                                один пациент
                                            </span>
                                        </div>

                                        <div
                                            class="autocomplete-shell"
                                            :class="{
                                                'is-open': patientSearchOpen,
                                                'has-selection': Boolean(form.patient_id)
                                            }"
                                        >
                                            <i class="bi bi-search autocomplete-shell__icon"></i>

                                            <input
                                                id="consilium-patient-search"
                                                v-model="patientQuery"
                                                class="autocomplete-input"
                                                type="search"
                                                autocomplete="off"
                                                spellcheck="false"
                                                placeholder="Начните вводить ФИО пациента..."
                                                @input="onPatientInput"
                                                @focus="onPatientFocus"
                                                @blur="onPatientBlur"
                                                @keydown.escape.stop="patientSearchOpen = false"
                                            >

                                            <span
                                                v-if="patientsLoading"
                                                class="autocomplete-loader"
                                                aria-hidden="true"
                                            ></span>

                                            <button
                                                v-if="form.patient_id || patientQuery"
                                                type="button"
                                                class="autocomplete-clear"
                                                title="Очистить"
                                                @mousedown.prevent
                                                @click="clearPatient"
                                            >
                                                <i class="bi bi-x-lg"></i>
                                            </button>

                                            <div
                                                v-if="patientSearchOpen"
                                                class="autocomplete-dropdown"
                                            >
                                                <div
                                                    v-if="patientQuery.trim().length < 2"
                                                    class="autocomplete-state"
                                                >
                                                    <i class="bi bi-keyboard"></i>
                                                    Введите минимум 2 символа
                                                </div>

                                                <div
                                                    v-else-if="patientsLoading"
                                                    class="autocomplete-state"
                                                >
                                                    <span class="mini-spinner"></span>
                                                    Ищем пациента...
                                                </div>

                                                <div
                                                    v-else-if="options.patients.length === 0"
                                                    class="autocomplete-state"
                                                >
                                                    <i class="bi bi-person-x"></i>
                                                    Пациенты не найдены
                                                </div>

                                                <button
                                                    v-for="patient in options.patients"
                                                    v-else
                                                    :key="patient.value"
                                                    type="button"
                                                    class="autocomplete-option"
                                                    @mousedown.prevent
                                                    @click="selectPatient(patient)"
                                                >
                                                    <span class="autocomplete-option__avatar patient">
                                                        <i class="bi bi-person"></i>
                                                    </span>

                                                    <span class="autocomplete-option__content">
                                                        <strong>{{ patient.name || patient.label }}</strong>
                                                        <small v-if="patient.meta">{{ patient.meta }}</small>
                                                    </span>

                                                    <i class="bi bi-chevron-right autocomplete-option__arrow"></i>
                                                </button>
                                            </div>
                                        </div>

                                        <div
                                            v-if="patientError"
                                            class="field-error"
                                        >
                                            <i class="bi bi-exclamation-circle"></i>
                                            {{ patientError }}
                                        </div>
                                    </div>

                                    <div class="field field--last">
                                        <label for="consilium-diagnosis-search">
                                            Предварительный диагноз
                                        </label>

                                        <div
                                            class="autocomplete-shell"
                                            :class="{
                                                'is-open': diagnosisSearchOpen,
                                                'has-selection': Boolean(form.diagnosis_id)
                                            }"
                                        >
                                            <i class="bi bi-activity autocomplete-shell__icon"></i>

                                            <input
                                                id="consilium-diagnosis-search"
                                                v-model="diagnosisQuery"
                                                class="autocomplete-input"
                                                type="search"
                                                autocomplete="off"
                                                spellcheck="false"
                                                placeholder="Код МКБ-10 или название диагноза..."
                                                @input="onDiagnosisInput"
                                                @focus="onDiagnosisFocus"
                                                @blur="onDiagnosisBlur"
                                                @keydown.escape.stop="diagnosisSearchOpen = false"
                                            >

                                            <span
                                                v-if="diagnosesLoading"
                                                class="autocomplete-loader"
                                                aria-hidden="true"
                                            ></span>

                                            <button
                                                v-if="form.diagnosis_id || diagnosisQuery"
                                                type="button"
                                                class="autocomplete-clear"
                                                title="Очистить"
                                                @mousedown.prevent
                                                @click="clearDiagnosis"
                                            >
                                                <i class="bi bi-x-lg"></i>
                                            </button>

                                            <div
                                                v-if="diagnosisSearchOpen"
                                                class="autocomplete-dropdown"
                                            >
                                                <div
                                                    v-if="diagnosisQuery.trim().length < 1"
                                                    class="autocomplete-state"
                                                >
                                                    <i class="bi bi-keyboard"></i>
                                                    Введите код МКБ-10 или название
                                                </div>

                                                <div
                                                    v-else-if="diagnosesLoading"
                                                    class="autocomplete-state"
                                                >
                                                    <span class="mini-spinner"></span>
                                                    Ищем диагноз...
                                                </div>

                                                <div
                                                    v-else-if="options.diagnoses.length === 0"
                                                    class="autocomplete-state"
                                                >
                                                    <i class="bi bi-search"></i>
                                                    Диагнозы не найдены
                                                </div>

                                                <button
                                                    v-for="diagnosis in options.diagnoses"
                                                    v-else
                                                    :key="diagnosis.value"
                                                    type="button"
                                                    class="autocomplete-option autocomplete-option--diagnosis"
                                                    @mousedown.prevent
                                                    @click="selectDiagnosis(diagnosis)"
                                                >
                                                    <span class="autocomplete-diagnosis-code">
                                                        {{ diagnosis.code || 'МКБ' }}
                                                    </span>

                                                    <span class="autocomplete-option__content">
                                                        <strong>{{ diagnosis.title || diagnosis.label }}</strong>
                                                    </span>

                                                    <i class="bi bi-chevron-right autocomplete-option__arrow"></i>
                                                </button>
                                            </div>
                                        </div>

                                        <div
                                            v-if="hasError('diagnosis_id')"
                                            class="field-error"
                                        >
                                            <i class="bi bi-exclamation-circle"></i>
                                            {{ firstError('diagnosis_id') }}
                                        </div>
                                    </div>
                                </section>
                            </div>

                            <!-- RIGHT COLUMN -->
                            <div class="consilium-column">
                                <!-- DOCTORS -->
                                <section class="consilium-card consilium-card--team">
                                    <div class="consilium-card__head">
                                        <div class="consilium-card__head-main">
                                            <div class="consilium-card__icon consilium-card__icon--team">
                                                <i class="bi bi-people"></i>
                                            </div>

                                            <div>
                                                <h3>Состав консилиума</h3>
                                                <p>Добавьте всех специалистов, которые участвуют в обсуждении</p>
                                            </div>
                                        </div>

                                        <div class="doctor-counter" :class="{ 'doctor-counter--filled': selectedDoctorsCount > 0 }">
                                            <i class="bi bi-person-check"></i>
                                            {{ selectedDoctorsCount }}
                                        </div>
                                    </div>

                                    <div class="field field--last">
                                        <div class="field-label-row">
                                            <label>
                                                Врачи-участники
                                                <span class="required">*</span>
                                            </label>

                                            <span
                                                v-if="doctorsLoading"
                                                class="field-hint field-hint--loading"
                                            >
                                                <span class="mini-spinner"></span>
                                                загрузка
                                            </span>
                                        </div>

                                        <div class="multiselect-shell multiselect-shell--team">
                                            <i class="bi bi-person-plus multiselect-shell__icon"></i>

                                            <Multiselect
                                                v-model="form.doctor_ids"
                                                class="consilium-select consilium-select--doctors consilium-select--with-icon"
                                                mode="tags"
                                                :options="options.doctors"
                                                :searchable="true"
                                                :close-on-select="false"
                                                :clear-on-select="false"
                                                :hide-selected="true"
                                                :caret="false"
                                                :disabled="doctorsLoading"
                                                placeholder="ФИО или специальность врача..."
                                                no-options-text="Список врачей пуст"
                                                no-results-text="Врач не найден"
                                            >
                                                <template #option="{ option }">
                                                    <div class="select-option">
                                                        <div class="select-option__avatar select-option__avatar--doctor">
                                                            {{ option.initials || 'В' }}
                                                        </div>

                                                        <div class="select-option__content">
                                                            <strong>{{ option.name || option.label }}</strong>
                                                            <span v-if="option.speciality">
                                                                {{ option.speciality }}
                                                            </span>
                                                        </div>
                                                    </div>
                                                </template>
                                            </Multiselect>
                                        </div>

                                        <div
                                            v-if="hasError('doctor_ids')"
                                            class="field-error"
                                        >
                                            <i class="bi bi-exclamation-circle"></i>
                                            {{ firstError('doctor_ids') }}
                                        </div>

                                        <div class="team-info">
                                            <div class="team-info__icon">
                                                <i class="bi bi-info-circle"></i>
                                            </div>

                                            <div>
                                                <strong v-if="selectedDoctorsCount === 0">
                                                    Состав пока не сформирован
                                                </strong>
                                                <strong v-else>
                                                    В составе {{ doctorsCountLabel }}
                                                </strong>

                                                <span>
                                                    Можно добавить врачей разных специальностей без ограничения по количеству.
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                </section>

                                <!-- SUMMARY -->
                                <section class="consilium-card consilium-card--summary">
                                    <div class="summary-kicker">
                                        <i class="bi bi-check2-square"></i>
                                        Готовность
                                    </div>

                                    <div class="summary-list">
                                        <div class="summary-row">
                                            <span class="summary-row__icon" :class="{ ready: Boolean(form.patient_id) }">
                                                <i :class="form.patient_id ? 'bi bi-check-lg' : 'bi bi-person'"></i>
                                            </span>

                                            <div>
                                                <strong>Пациент</strong>
                                                <small>{{ form.patient_id ? 'выбран' : 'не выбран' }}</small>
                                            </div>
                                        </div>

                                        <div class="summary-row">
                                            <span class="summary-row__icon" :class="{ ready: selectedDoctorsCount > 0 }">
                                                <i :class="selectedDoctorsCount > 0 ? 'bi bi-check-lg' : 'bi bi-people'"></i>
                                            </span>

                                            <div>
                                                <strong>Врачебный состав</strong>
                                                <small>
                                                    {{ selectedDoctorsCount > 0 ? doctorsCountLabel : 'не сформирован' }}
                                                </small>
                                            </div>
                                        </div>

                                        <div class="summary-row">
                                            <span class="summary-row__icon" :class="{ ready: Boolean(form.title.trim()) }">
                                                <i :class="form.title.trim() ? 'bi bi-check-lg' : 'bi bi-card-text'"></i>
                                            </span>

                                            <div>
                                                <strong>Название</strong>
                                                <small>{{ form.title.trim() ? 'заполнено' : 'не заполнено' }}</small>
                                            </div>
                                        </div>
                                    </div>
                                </section>
                            </div>
                        </div>

                        <!-- DESCRIPTION -->
                        <section class="consilium-card consilium-card--description">
                            <div class="consilium-card__head consilium-card__head--description">
                                <div class="consilium-card__head-main">
                                    <div class="consilium-card__icon consilium-card__icon--question">
                                        <i class="bi bi-chat-square-text"></i>
                                    </div>

                                    <div>
                                        <h3>Клинический вопрос</h3>
                                        <p>Что именно необходимо обсудить и какое решение ожидается от консилиума</p>
                                    </div>
                                </div>

                                <span class="consilium-step">03</span>
                            </div>

                            <div class="field field--last">
                                <textarea
                                    v-model.trim="form.description"
                                    rows="5"
                                    maxlength="5000"
                                    placeholder="Опишите клиническую ситуацию, цель проведения консилиума и вопросы для обсуждения..."
                                    :class="{ invalid: hasError('description') }"
                                ></textarea>

                                <div class="description-footer">
                                    <div class="quick-prompts">
                                        <button
                                            v-for="prompt in quickPrompts"
                                            :key="prompt"
                                            type="button"
                                            class="quick-prompt"
                                            @click="appendDescriptionPrompt(prompt)"
                                        >
                                            <i class="bi bi-plus"></i>
                                            {{ prompt }}
                                        </button>
                                    </div>

                                    <span class="char-counter">
                                        {{ form.description.length }} / 5000
                                    </span>
                                </div>

                                <div
                                    v-if="hasError('description')"
                                    class="field-error"
                                >
                                    <i class="bi bi-exclamation-circle"></i>
                                    {{ firstError('description') }}
                                </div>
                            </div>
                        </section>
                    </div>

                    <!-- FOOTER -->
                    <footer class="consilium-footer">
                        <div class="consilium-footer__summary">
                            <div class="footer-summary-icon" :class="{ ready: canSubmit }">
                                <i :class="canSubmit ? 'bi bi-check2-circle' : 'bi bi-shield-check'"></i>
                            </div>

                            <div>
                                <strong>
                                    {{ canSubmit ? 'Основные данные заполнены' : 'Заполните обязательные поля' }}
                                </strong>
                                <span>
                                    Один пациент · {{ selectedDoctorsCount }} {{ doctorWord(selectedDoctorsCount) }}
                                </span>
                            </div>
                        </div>

                        <div class="consilium-footer__actions">
                            <button
                                type="button"
                                class="btn-cancel"
                                :disabled="saving"
                                @click="close"
                            >
                                Отмена
                            </button>

                            <button
                                type="button"
                                class="btn-create"
                                :disabled="saving || doctorsLoading || !canSubmit"
                                @click="submit"
                            >
                                <span
                                    v-if="saving"
                                    class="spinner"
                                ></span>

                                <i
                                    v-else
                                    class="bi bi-people-fill"
                                ></i>

                                {{ saving ? 'Создаём...' : 'Создать консилиум' }}
                            </button>
                        </div>
                    </footer>
                </section>
            </div>
        </Transition>
    </Teleport>
</template>

<script setup>
import {
    computed,
    onBeforeUnmount,
    onMounted,
    reactive,
    ref,
} from 'vue';

import Multiselect from '@vueform/multiselect';
import '@vueform/multiselect/themes/default.css';

const props = defineProps({
    doctorsUrl: {
        type: String,
        required: true,
    },

    psyDoctorsUrl: {
        type: String,
        required: true,
    },

    patientsUrl: {
        type: String,
        required: true,
    },

    diagnosesUrl: {
        type: String,
        required: true,
    },

    storeUrl: {
        type: String,
        required: true,
    },
});

const emit = defineEmits([
    'created',
]);

const opened = ref(false);
const saving = ref(false);

const errors = ref({});
const generalError = ref('');

const options = reactive({
    doctors: [],
    patients: [],
    diagnoses: [],
});

const blankForm = () => ({
    title: '',
    datetime: '',
    diagnosis_id: null,

    // В интерфейсе только один пациент.
    patient_id: null,

    // Врачей может быть сколько угодно.
    doctor_ids: [],

    description: '',
});

const form = reactive(blankForm());

const doctorsLoading = ref(false);
const patientsLoading = ref(false);
const diagnosesLoading = ref(false);

let patientController = null;
let diagnosisController = null;

const patientQuery = ref('');
const diagnosisQuery = ref('');

const patientSearchOpen = ref(false);
const diagnosisSearchOpen = ref(false);

let patientSearchTimer = null;
let diagnosisSearchTimer = null;

const quickPrompts = [
    'Уточнение диагноза',
    'Дифференциальная диагностика',
    'Коррекция терапии',
    'Тактика дальнейшего ведения',
];

const selectedDoctorsCount = computed(() =>
    Array.isArray(form.doctor_ids)
        ? form.doctor_ids.length
        : 0
);

const canSubmit = computed(() =>
    Boolean(
        form.title.trim()
        && form.patient_id
        && selectedDoctorsCount.value > 0
    )
);

const patientError = computed(() =>
    firstError('patient_id')
    || firstError('patient_ids')
    || ''
);

const doctorsCountLabel = computed(() =>
    `${selectedDoctorsCount.value} ${doctorWord(selectedDoctorsCount.value)}`
);

function resetForm() {
    Object.assign(form, blankForm());

    errors.value = {};
    generalError.value = '';

    patientQuery.value = '';
    diagnosisQuery.value = '';

    patientSearchOpen.value = false;
    diagnosisSearchOpen.value = false;

    clearTimeout(patientSearchTimer);
    clearTimeout(diagnosisSearchTimer);

    options.patients = [];
    options.diagnoses = [];
}

function getCsrfToken() {
    return document
        .querySelector('meta[name="csrf-token"]')
        ?.getAttribute('content');
}

async function request(url, options = {}) {
    const response = await fetch(url, {
        credentials: 'same-origin',
        ...options,

        headers: {
            Accept: 'application/json',
            'Content-Type': 'application/json',
            'X-Requested-With': 'XMLHttpRequest',
            'X-CSRF-TOKEN': getCsrfToken() ?? '',
            ...(options.headers || {}),
        },
    });

    const data = await response.json().catch(() => ({}));

    if (!response.ok) {
        const error = new Error(
            data?.message
            || `Request failed: ${response.status}`
        );

        error.response = {
            status: response.status,
            data,
        };

        throw error;
    }

    return data;
}

async function open() {
    resetForm();

    opened.value = true;
    document.body.classList.add('consilium-modal-open');

    await loadDoctors();
}

function close() {
    if (saving.value) {
        return;
    }

    opened.value = false;
    document.body.classList.remove('consilium-modal-open');

    abortSearches();
}

function hasError(field) {
    return Array.isArray(errors.value?.[field])
        && errors.value[field].length > 0;
}

function firstError(field) {
    return errors.value?.[field]?.[0] ?? '';
}

function validateBeforeSubmit() {
    const nextErrors = {};

    if (!form.title.trim()) {
        nextErrors.title = ['Укажите название консилиума.'];
    }

    if (!form.patient_id) {
        nextErrors.patient_ids = ['Выберите пациента.'];
    }

    if (selectedDoctorsCount.value === 0) {
        nextErrors.doctor_ids = ['Добавьте хотя бы одного врача.'];
    }

    errors.value = nextErrors;

    return Object.keys(nextErrors).length === 0;
}

async function submit() {
    if (saving.value || !validateBeforeSubmit()) {
        return;
    }

    errors.value = {};
    generalError.value = '';

    saving.value = true;

    try {
        const data = await request(props.storeUrl, {
            method: 'POST',

            body: JSON.stringify({
                title: form.title,
                datetime: form.datetime || null,
                diagnosis_id: form.diagnosis_id || null,

                /*
                 * Бэкенд можно пока не менять:
                 * он по-прежнему получает patient_ids,
                 * но внутри массива всегда ровно один пациент.
                 */
                patient_ids: form.patient_id
                    ? [form.patient_id]
                    : [],

                doctor_ids: form.doctor_ids,
                description: form.description || null,
            }),
        });

        emit('created', data);

        opened.value = false;
        document.body.classList.remove('consilium-modal-open');

        window.dispatchEvent(
            new CustomEvent('consilium:created', {
                detail: data,
            }),
        );

    } catch (error) {
        if (error.response?.status === 422) {
            errors.value = error.response.data.errors ?? {};
            return;
        }

        console.error(
            '[ConsiliumModal] create error:',
            error
        );

        generalError.value =
            error.response?.data?.message
            || 'Не удалось создать консилиум. Попробуйте ещё раз.';
    } finally {
        saving.value = false;
    }
}

function onOpenEvent() {
    open();
}

function onKeydown(event) {
    if (
        event.key === 'Escape'
        && opened.value
        && !saving.value
    ) {
        close();
    }
}

async function loadDoctors() {
    if (options.doctors.length) {
        return;
    }

    doctorsLoading.value = true;
    generalError.value = '';

    try {
        const [generalResponse, psyResponse] = await Promise.all([
            fetch(props.doctorsUrl, {
                credentials: 'same-origin',
                headers: {
                    Accept: 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                },
            }),

            fetch(props.psyDoctorsUrl, {
                credentials: 'same-origin',
                headers: {
                    Accept: 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                },
            }),
        ]);

        if (!generalResponse.ok || !psyResponse.ok) {
            throw new Error('Не удалось загрузить врачей');
        }

        const general = await generalResponse.json();
        const psy = await psyResponse.json();

        const uniqueDoctors = new Map();

        [
            ...(general.items ?? []),
            ...(psy.items ?? []),
        ].forEach((doctor) => {
            if (doctor?.id == null) {
                return;
            }

            uniqueDoctors.set(
                String(doctor.id),
                doctor
            );
        });

        options.doctors = Array
            .from(uniqueDoctors.values())
            .map((doctor) => {
                const fullName = [
                    doctor.surname,
                    doctor.name,
                    doctor.patronym,
                ]
                    .filter(Boolean)
                    .join(' ');

                const speciality = String(
                    doctor.status ?? ''
                ).trim();

                return {
                    value: doctor.id,
                    label: speciality
                        ? `${fullName} — ${speciality}`
                        : fullName,

                    name: fullName,
                    speciality,
                    initials: makeInitials(doctor),
                };
            })
            .sort((a, b) =>
                a.name.localeCompare(
                    b.name,
                    'ru',
                    {
                        sensitivity: 'base',
                    },
                )
            );

    } catch (error) {
        console.error(
            '[ConsiliumModal] doctors error:',
            error
        );

        generalError.value =
            'Не удалось загрузить список врачей.';
    } finally {
        doctorsLoading.value = false;
    }
}

function onPatientInput(event) {
    const value = String(event?.target?.value ?? '');

    patientQuery.value = value;

    /*
     * Если пользователь начал редактировать уже выбранного пациента,
     * выбранный ID сразу снимаем. Это предотвращает ситуацию,
     * когда в поле написано одно имя, а в POST уходит другой пациент.
     */
    if (form.patient_id) {
        form.patient_id = null;
    }

    delete errors.value.patient_id;
    delete errors.value.patient_ids;

    clearTimeout(patientSearchTimer);

    if (patientController) {
        patientController.abort();
        patientController = null;
    }

    const query = value.trim();

    if (query.length < 2) {
        patientsLoading.value = false;
        options.patients = [];
        patientSearchOpen.value = query.length > 0;
        return;
    }

    patientSearchOpen.value = true;
    patientsLoading.value = true;

    patientSearchTimer = setTimeout(
        () => searchPatients(query),
        220
    );
}

function onPatientFocus() {
    const query = patientQuery.value.trim();

    if (query.length > 0) {
        patientSearchOpen.value = true;
    }
}

function onPatientBlur() {
    /*
     * Небольшая задержка нужна, чтобы click по варианту
     * успел сработать до закрытия dropdown.
     */
    setTimeout(() => {
        patientSearchOpen.value = false;
    }, 140);
}

function selectPatient(patient) {
    if (!patient?.value) {
        return;
    }

    form.patient_id = patient.value;
    patientQuery.value = patient.label || patient.name || '';

    patientSearchOpen.value = false;
    options.patients = [];

    delete errors.value.patient_id;
    delete errors.value.patient_ids;
}

function clearPatient() {
    form.patient_id = null;
    patientQuery.value = '';

    patientSearchOpen.value = false;
    patientsLoading.value = false;
    options.patients = [];

    clearTimeout(patientSearchTimer);

    if (patientController) {
        patientController.abort();
        patientController = null;
    }
}

async function searchPatients(query) {
    query = String(query ?? '').trim();

    if (query.length < 2) {
        patientsLoading.value = false;
        return;
    }

    if (patientController) {
        patientController.abort();
    }

    patientController = new AbortController();
    const controller = patientController;

    try {
        const url = new URL(
            props.patientsUrl,
            window.location.origin,
        );

        url.searchParams.set('q', query);

        const response = await fetch(
            url.toString(),
            {
                credentials: 'same-origin',

                headers: {
                    Accept: 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                },

                signal: controller.signal,
            },
        );

        if (!response.ok) {
            throw new Error(
                `HTTP ${response.status}`
            );
        }

        const data = await response.json();

        /*
         * Пока запрос выполнялся, пользователь мог уже изменить строку.
         * Старый ответ тогда просто игнорируем.
         */
        if (
            controller !== patientController
            || patientQuery.value.trim() !== query
        ) {
            return;
        }

        options.patients =
            (data.results ?? []).map(
                (patient) => {
                    const date = patient.birth_at
                        ? formatDate(patient.birth_at)
                        : '';

                    return {
                        value: patient.id,
                        label:
                            patient.text
                            + (date ? ` · ${date}` : ''),

                        name: patient.text,
                        meta: date
                            ? `Дата рождения: ${date}`
                            : '',
                    };
                }
            );

        patientSearchOpen.value = true;

    } catch (error) {
        if (error.name !== 'AbortError') {
            console.error(
                '[ConsiliumModal] patient search error:',
                error
            );

            options.patients = [];
        }
    } finally {
        if (controller === patientController) {
            patientsLoading.value = false;
            patientController = null;
        }
    }
}

function onDiagnosisInput(event) {
    const value = String(event?.target?.value ?? '');

    diagnosisQuery.value = value;

    if (form.diagnosis_id) {
        form.diagnosis_id = null;
    }

    delete errors.value.diagnosis_id;

    clearTimeout(diagnosisSearchTimer);

    if (diagnosisController) {
        diagnosisController.abort();
        diagnosisController = null;
    }

    const query = value.trim();

    if (query.length < 1) {
        diagnosesLoading.value = false;
        options.diagnoses = [];
        diagnosisSearchOpen.value = false;
        return;
    }

    diagnosisSearchOpen.value = true;
    diagnosesLoading.value = true;

    diagnosisSearchTimer = setTimeout(
        () => searchDiagnoses(query),
        180
    );
}

function onDiagnosisFocus() {
    if (diagnosisQuery.value.trim().length > 0) {
        diagnosisSearchOpen.value = true;
    }
}

function onDiagnosisBlur() {
    setTimeout(() => {
        diagnosisSearchOpen.value = false;
    }, 140);
}

function selectDiagnosis(diagnosis) {
    if (!diagnosis?.value) {
        return;
    }

    form.diagnosis_id = diagnosis.value;
    diagnosisQuery.value =
        diagnosis.label
        || `${diagnosis.code || ''} — ${diagnosis.title || ''}`.trim();

    diagnosisSearchOpen.value = false;
    options.diagnoses = [];

    delete errors.value.diagnosis_id;
}

function clearDiagnosis() {
    form.diagnosis_id = null;
    diagnosisQuery.value = '';

    diagnosisSearchOpen.value = false;
    diagnosesLoading.value = false;
    options.diagnoses = [];

    clearTimeout(diagnosisSearchTimer);

    if (diagnosisController) {
        diagnosisController.abort();
        diagnosisController = null;
    }
}

async function searchDiagnoses(query) {
    query = String(query ?? '').trim();

    if (query.length < 1) {
        diagnosesLoading.value = false;
        return;
    }

    if (diagnosisController) {
        diagnosisController.abort();
    }

    diagnosisController = new AbortController();
    const controller = diagnosisController;

    try {
        const url = new URL(
            props.diagnosesUrl,
            window.location.origin,
        );

        url.searchParams.set('q', query);
        url.searchParams.set('by', 'any');
        url.searchParams.set('page', '1');
        url.searchParams.set('per_page', '30');

        const response = await fetch(
            url.toString(),
            {
                credentials: 'same-origin',

                headers: {
                    Accept: 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                },

                signal: controller.signal,
            },
        );

        if (!response.ok) {
            throw new Error(
                `HTTP ${response.status}`
            );
        }

        const data = await response.json();

        if (
            controller !== diagnosisController
            || diagnosisQuery.value.trim() !== query
        ) {
            return;
        }

        options.diagnoses =
            (data.results ?? []).map(
                (diagnosis) => ({
                    value: diagnosis.id,
                    label:
                        diagnosis.text
                        || `${diagnosis.code} — ${diagnosis.title}`,

                    code: diagnosis.code || '',
                    title: diagnosis.title || diagnosis.text || '',
                })
            );

        diagnosisSearchOpen.value = true;

    } catch (error) {
        if (error.name !== 'AbortError') {
            console.error(
                '[ConsiliumModal] diagnosis search error:',
                error
            );

            options.diagnoses = [];
        }
    } finally {
        if (controller === diagnosisController) {
            diagnosesLoading.value = false;
            diagnosisController = null;
        }
    }
}

function appendDescriptionPrompt(prompt) {
    const current = form.description.trim();

    if (!current) {
        form.description = `${prompt}: `;
        return;
    }

    if (
        current
            .toLocaleLowerCase('ru')
            .includes(
                prompt.toLocaleLowerCase('ru')
            )
    ) {
        return;
    }

    form.description =
        `${current.replace(/[.;,\s]+$/, '')}. ${prompt}: `;
}

function formatDate(value) {
    if (!value) {
        return '';
    }

    const raw = String(value);

    /*
     * YYYY-MM-DD разбираем вручную, чтобы не получить
     * сдвиг даты из-за timezone браузера.
     */
    const match = raw.match(
        /^(\d{4})-(\d{2})-(\d{2})/
    );

    if (match) {
        return `${match[3]}.${match[2]}.${match[1]}`;
    }

    const date = new Date(value);

    if (Number.isNaN(date.getTime())) {
        return raw;
    }

    return date.toLocaleDateString('ru-RU');
}

function makeInitials(doctor) {
    const surname = String(
        doctor?.surname ?? ''
    ).trim();

    const name = String(
        doctor?.name ?? ''
    ).trim();

    if (!surname && !name) {
        return 'В';
    }

    return (
        (surname.charAt(0) || '')
        + (name.charAt(0) || '')
    ).toLocaleUpperCase('ru');
}

function doctorWord(number) {
    const value = Math.abs(
        Number(number) || 0
    );

    const mod100 = value % 100;
    const mod10 = value % 10;

    if (
        mod100 >= 11
        && mod100 <= 14
    ) {
        return 'врачей';
    }

    if (mod10 === 1) {
        return 'врач';
    }

    if (
        mod10 >= 2
        && mod10 <= 4
    ) {
        return 'врача';
    }

    return 'врачей';
}

function abortSearches() {
    clearTimeout(patientSearchTimer);
    clearTimeout(diagnosisSearchTimer);

    if (patientController) {
        patientController.abort();
        patientController = null;
    }

    if (diagnosisController) {
        diagnosisController.abort();
        diagnosisController = null;
    }

    patientSearchOpen.value = false;
    diagnosisSearchOpen.value = false;

    patientsLoading.value = false;
    diagnosesLoading.value = false;
}

onMounted(() => {
    window.addEventListener(
        'consilium:open',
        onOpenEvent,
    );

    document.addEventListener(
        'keydown',
        onKeydown,
    );
});

onBeforeUnmount(() => {
    window.removeEventListener(
        'consilium:open',
        onOpenEvent,
    );

    document.removeEventListener(
        'keydown',
        onKeydown,
    );

    document.body.classList.remove(
        'consilium-modal-open'
    );

    abortSearches();
});
</script>

<style scoped>
.consilium-overlay {
    --c-primary: #078c9b;
    --c-primary-dark: #056f7b;
    --c-primary-soft: #eaf8f9;
    --c-primary-softer: #f5fbfc;

    --c-text: #24364c;
    --c-text-soft: #536579;
    --c-muted: #8493a6;
    --c-border: #e2e9ef;
    --c-border-strong: #d5e0e7;

    position: fixed;
    inset: 0;
    z-index: 10050;

    display: flex;
    align-items: center;
    justify-content: center;

    padding: 20px;

    background: rgba(17, 31, 47, .46);
    backdrop-filter: blur(10px) saturate(.9);
}

.consilium-modal {
    position: relative;

    width: min(1120px, 100%);
    max-height: calc(100dvh - 40px);

    display: flex;
    flex-direction: column;

    overflow: hidden;

    border: 1px solid rgba(255, 255, 255, .7);
    border-radius: 28px;

    background:
        linear-gradient(180deg, #ffffff 0%, #fbfcfd 100%);

    box-shadow:
        0 38px 100px rgba(15, 31, 48, .28),
        0 8px 28px rgba(15, 31, 48, .10);
}

.consilium-glow {
    position: absolute;
    z-index: 0;

    border-radius: 999px;

    pointer-events: none;
    filter: blur(1px);
}

.consilium-glow--one {
    top: -130px;
    left: -110px;

    width: 340px;
    height: 280px;

    background:
        radial-gradient(
            circle,
            rgba(7, 140, 155, .13) 0%,
            rgba(7, 140, 155, 0) 72%
        );
}

.consilium-glow--two {
    right: -140px;
    bottom: -180px;

    width: 380px;
    height: 330px;

    background:
        radial-gradient(
            circle,
            rgba(83, 113, 255, .07) 0%,
            rgba(83, 113, 255, 0) 70%
        );
}

/* HEADER */

.consilium-header {
    position: relative;
    z-index: 2;

    display: flex;
    align-items: center;
    justify-content: space-between;

    min-height: 104px;

    padding: 22px 28px;

    border-bottom: 1px solid var(--c-border);

    background:
        linear-gradient(
            120deg,
            rgba(237, 250, 251, .92) 0%,
            rgba(255, 255, 255, .98) 42%,
            #fff 100%
        );
}

.consilium-header::after {
    content: '';

    position: absolute;
    right: 0;
    bottom: -1px;
    left: 0;

    height: 1px;

    background:
        linear-gradient(
            90deg,
            transparent,
            rgba(7, 140, 155, .32),
            transparent
        );
}

.consilium-header__main {
    display: flex;
    align-items: center;
    gap: 16px;

    min-width: 0;
}

.consilium-header-icon {
    width: 54px;
    height: 54px;

    flex: 0 0 54px;

    display: grid;
    place-items: center;

    border: 1px solid rgba(7, 140, 155, .13);
    border-radius: 17px;

    color: var(--c-primary);

    background:
        linear-gradient(
            145deg,
            #ffffff 0%,
            #e8f8f9 100%
        );

    font-size: 23px;

    box-shadow:
        0 8px 20px rgba(7, 140, 155, .11),
        inset 0 1px 0 #fff;
}

.consilium-header-text {
    min-width: 0;
}

.consilium-eyebrow {
    display: flex;
    align-items: center;
    gap: 7px;

    margin-bottom: 4px;

    color: var(--c-primary-dark);

    font-size: 10px;
    font-weight: 850;

    letter-spacing: .07em;
    text-transform: uppercase;
}

.consilium-eyebrow__dot {
    width: 6px;
    height: 6px;

    border-radius: 50%;

    background: var(--c-primary);

    box-shadow:
        0 0 0 4px rgba(7, 140, 155, .10);
}

.consilium-header-text h2 {
    margin: 0;

    color: var(--c-text);

    font-size: 23px;
    font-weight: 850;
    line-height: 1.18;

    letter-spacing: -.025em;
}

.consilium-header-text p {
    margin: 5px 0 0;

    color: var(--c-muted);

    font-size: 12px;
    line-height: 1.45;
}

.consilium-close {
    width: 42px;
    height: 42px;

    flex: 0 0 42px;

    display: grid;
    place-items: center;

    margin-left: 18px;

    border: 1px solid transparent;
    border-radius: 13px;

    color: #7d8a9b;
    background: transparent;

    cursor: pointer;

    transition:
        border-color .16s ease,
        background .16s ease,
        color .16s ease,
        transform .16s ease;
}

.consilium-close:hover:not(:disabled) {
    border-color: #e1e7ec;

    color: var(--c-text);
    background: #fff;

    transform: translateY(-1px);

    box-shadow:
        0 6px 16px rgba(15, 23, 42, .06);
}

.consilium-close:disabled {
    opacity: .45;
    cursor: default;
}

/* BODY */

.consilium-body {
    position: relative;
    z-index: 1;

    flex: 1;
    min-height: 0;

    overflow-y: auto;
    overscroll-behavior: contain;

    padding: 22px;

    background:
        radial-gradient(
            circle at 0 0,
            rgba(7, 140, 155, .045),
            transparent 30%
        ),
        #f6f8fa;

    scrollbar-width: thin;
    scrollbar-color: #cbd6df transparent;
}

.consilium-workspace {
    display: grid;
    grid-template-columns:
        minmax(0, 1.12fr)
        minmax(330px, .88fr);

    gap: 16px;

    align-items: start;
}

.consilium-column {
    display: grid;
    gap: 16px;

    min-width: 0;
}

/* CARDS */

.consilium-card {
    position: relative;

    min-width: 0;

    padding: 18px;

    border: 1px solid var(--c-border);
    border-radius: 20px;

    background:
        linear-gradient(
            180deg,
            #fff 0%,
            #fdfefe 100%
        );

    box-shadow:
        0 8px 28px rgba(15, 35, 52, .035);

    transition:
        border-color .18s ease,
        box-shadow .18s ease,
        transform .18s ease;
}

.consilium-card:hover {
    border-color: #d9e4ea;

    box-shadow:
        0 12px 32px rgba(15, 35, 52, .055);
}

.consilium-card--team {
    background:
        linear-gradient(
            160deg,
            #ffffff 0%,
            #f7fcfc 100%
        );
}

.consilium-card--summary {
    padding: 16px 18px;

    border-style: dashed;

    background:
        linear-gradient(
            145deg,
            rgba(240, 249, 250, .72),
            rgba(255, 255, 255, .94)
        );
}

.consilium-card--description {
    margin-top: 16px;
}

.consilium-card__head {
    display: flex;
    align-items: flex-start;
    justify-content: space-between;
    gap: 14px;

    margin-bottom: 17px;
}

.consilium-card__head--description {
    margin-bottom: 14px;
}

.consilium-card__head-main {
    min-width: 0;

    display: flex;
    align-items: center;
    gap: 11px;
}

.consilium-card__icon {
    width: 38px;
    height: 38px;

    flex: 0 0 38px;

    display: grid;
    place-items: center;

    border-radius: 12px;

    color: var(--c-primary);

    background: var(--c-primary-soft);

    font-size: 16px;
}

.consilium-card__icon--patient {
    color: #3f6ca9;
    background: #eef4fc;
}

.consilium-card__icon--team {
    color: #6a58b7;
    background: #f1effb;
}

.consilium-card__icon--question {
    color: #94651d;
    background: #fff6e7;
}

.consilium-card__head h3 {
    margin: 0;

    color: var(--c-text);

    font-size: 14px;
    font-weight: 820;
    line-height: 1.3;
}

.consilium-card__head p {
    margin: 3px 0 0;

    color: var(--c-muted);

    font-size: 10.5px;
    line-height: 1.45;
}

.consilium-step {
    min-width: 29px;
    height: 25px;

    display: inline-flex;
    align-items: center;
    justify-content: center;

    padding: 0 8px;

    border: 1px solid #e4eaee;
    border-radius: 999px;

    color: #9aa6b4;
    background: #f9fbfc;

    font-size: 9px;
    font-weight: 850;

    letter-spacing: .04em;
}

/* GRID */

.consilium-grid {
    display: grid;
    gap: 13px;
}

.consilium-grid--case {
    grid-template-columns:
        minmax(0, 1.35fr)
        minmax(210px, .65fr);
}

/* FIELD */

.field {
    position: relative;
    z-index: 1;

    min-width: 0;
    margin-bottom: 16px;
}

/*
 * Активное поле должно находиться выше следующих полей.
 * Иначе dropdown пациента перекрывается следующим Multiselect (диагнозом).
 */
.field:focus-within {
    z-index: 60;
}

.field--last {
    margin-bottom: 0;
}

.field label {
    display: block;

    margin: 0 0 7px;

    color: #35485d;

    font-size: 11px;
    font-weight: 780;
    line-height: 1.3;
}

.field-label-row {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 10px;

    margin-bottom: 7px;
}

.field-label-row label {
    margin: 0;
}

.required {
    color: #d95e69;
}

.field-hint {
    display: inline-flex;
    align-items: center;
    gap: 5px;

    color: #8a98a8;

    font-size: 9.5px;
    font-weight: 700;
}

.field-hint i {
    color: #97a8b6;
}

.field-hint--loading {
    color: var(--c-primary-dark);
}

.control-wrap {
    position: relative;
}

.control-icon {
    position: absolute;
    z-index: 2;

    top: 50%;
    left: 13px;

    transform: translateY(-50%);

    color: #96a4b4;

    font-size: 13px;

    pointer-events: none;
}

.field input,
.field textarea {
    width: 100%;

    box-sizing: border-box;

    border: 1px solid var(--c-border-strong);
    outline: none;

    color: var(--c-text);
    background: #fff;

    font-family: inherit;
    font-size: 12px;

    transition:
        border-color .16s ease,
        box-shadow .16s ease,
        background .16s ease;
}

.field input {
    min-height: 44px;

    padding: 9px 12px 9px 38px;

    border-radius: 12px;
}

.field textarea {
    min-height: 128px;

    padding: 13px 14px;

    border-radius: 14px;

    resize: vertical;

    line-height: 1.55;
}

.field input::placeholder,
.field textarea::placeholder {
    color: #a0adbb;
}

.field input:hover,
.field textarea:hover {
    border-color: #c7d5de;
}

.field input:focus,
.field textarea:focus {
    border-color: #54b8c1;

    background: #fff;

    box-shadow:
        0 0 0 3px rgba(7, 140, 155, .08),
        0 4px 14px rgba(15, 35, 52, .035);
}

.field input.invalid,
.field textarea.invalid {
    border-color: #dd7a84;

    box-shadow:
        0 0 0 3px rgba(221, 85, 99, .06);
}

.field-error {
    display: flex;
    align-items: center;
    gap: 6px;

    margin-top: 6px;

    color: #c94f5d;

    font-size: 10px;
    font-weight: 650;
}

/* ALERT */

.consilium-alert {
    display: flex;
    align-items: flex-start;
    gap: 10px;

    margin-bottom: 16px;
    padding: 12px 13px;

    border: 1px solid #f0d0d5;
    border-radius: 14px;

    color: #8e3f49;
    background: #fff5f6;

    font-size: 11px;
}

.consilium-alert__icon {
    width: 30px;
    height: 30px;

    flex: 0 0 30px;

    display: grid;
    place-items: center;

    border-radius: 9px;

    color: #cf5665;
    background: #ffe8eb;
}

.consilium-alert strong,
.consilium-alert span {
    display: block;
}

.consilium-alert strong {
    margin-bottom: 2px;

    font-weight: 800;
}

.consilium-alert span {
    color: #a45e67;
}

/* MULTISELECT WRAPPER */

.multiselect-shell {
    position: relative;
    z-index: 1;
}

/*
 * Все multiselect раньше имели одинаковый z-index.
 * Следующий по DOM элемент поэтому рисовался поверх открытого dropdown.
 */
.multiselect-shell:focus-within {
    z-index: 80;
}

.multiselect-shell__icon {
    position: absolute;
    z-index: 8;

    top: 22px;
    left: 13px;

    transform: translateY(-50%);

    color: #96a4b4;

    font-size: 13px;

    pointer-events: none;
}

.multiselect-shell--team {
    z-index: 1;
}

.multiselect-shell--team:focus-within {
    z-index: 80;
}

/* OPTION CONTENT */

.select-option {
    width: 100%;

    display: flex;
    align-items: center;
    gap: 10px;

    min-width: 0;
}

.select-option__avatar {
    width: 34px;
    height: 34px;

    flex: 0 0 34px;

    display: grid;
    place-items: center;

    border-radius: 10px;

    font-size: 11px;
    font-weight: 850;
}

.select-option__avatar--patient {
    color: #40709f;
    background: #edf4fb;
}

.select-option__avatar--doctor {
    color: #6c5bb0;
    background: #f0eef9;
}

.select-option__content {
    min-width: 0;
    flex: 1;
}

.select-option__content strong,
.select-option__content span {
    display: block;
}

.select-option__content strong {
    overflow: hidden;

    color: #2d3d50;

    font-size: 11px;
    font-weight: 760;

    text-overflow: ellipsis;
    white-space: nowrap;
}

.select-option__content span {
    margin-top: 2px;

    color: #8a97a6;

    font-size: 9.5px;
}

.select-option--diagnosis {
    align-items: flex-start;
}

.diagnosis-code {
    min-width: 58px;

    flex: 0 0 auto;

    display: inline-flex;
    align-items: center;
    justify-content: center;

    padding: 5px 7px;

    border-radius: 8px;

    color: var(--c-primary-dark);
    background: #eaf7f8;

    font-size: 9.5px;
    font-weight: 850;
}

/* TEAM INFO */

.doctor-counter {
    min-width: 36px;
    height: 29px;

    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 5px;

    padding: 0 9px;

    border: 1px solid #e1e7ec;
    border-radius: 999px;

    color: #8c99a8;
    background: #f8fafb;

    font-size: 10px;
    font-weight: 850;

    transition: .16s ease;
}

.doctor-counter--filled {
    border-color: #cce8eb;

    color: var(--c-primary-dark);
    background: #ecf9fa;
}

.team-info {
    display: flex;
    align-items: flex-start;
    gap: 9px;

    margin-top: 11px;
    padding: 10px 11px;

    border: 1px solid #e0ecee;
    border-radius: 12px;

    background: rgba(239, 249, 250, .68);
}

.team-info__icon {
    width: 27px;
    height: 27px;

    flex: 0 0 27px;

    display: grid;
    place-items: center;

    border-radius: 8px;

    color: var(--c-primary);
    background: #def3f5;

    font-size: 11px;
}

.team-info strong,
.team-info span {
    display: block;
}

.team-info strong {
    color: #42616a;

    font-size: 10px;
    font-weight: 800;
}

.team-info span {
    margin-top: 2px;

    color: #72868b;

    font-size: 9px;
    line-height: 1.45;
}

/* SUMMARY */

.summary-kicker {
    display: flex;
    align-items: center;
    gap: 7px;

    margin-bottom: 12px;

    color: #66788a;

    font-size: 9.5px;
    font-weight: 850;

    letter-spacing: .045em;
    text-transform: uppercase;
}

.summary-kicker i {
    color: var(--c-primary);
}

.summary-list {
    display: grid;
    gap: 9px;
}

.summary-row {
    display: flex;
    align-items: center;
    gap: 9px;
}

.summary-row__icon {
    width: 30px;
    height: 30px;

    flex: 0 0 30px;

    display: grid;
    place-items: center;

    border: 1px solid #e2e8ed;
    border-radius: 9px;

    color: #94a1af;
    background: #fff;

    font-size: 11px;

    transition: .16s ease;
}

.summary-row__icon.ready {
    border-color: #cde9dd;

    color: #2f8b64;
    background: #eef9f4;
}

.summary-row strong,
.summary-row small {
    display: block;
}

.summary-row strong {
    color: #3b4c5f;

    font-size: 10px;
    font-weight: 780;
}

.summary-row small {
    margin-top: 1px;

    color: #8996a5;

    font-size: 9px;
}

/* DESCRIPTION */

.description-footer {
    display: flex;
    align-items: flex-start;
    justify-content: space-between;
    gap: 12px;

    margin-top: 9px;
}

.quick-prompts {
    display: flex;
    flex-wrap: wrap;
    gap: 6px;
}

.quick-prompt {
    display: inline-flex;
    align-items: center;
    gap: 4px;

    min-height: 26px;

    padding: 4px 8px;

    border: 1px solid #dfe8ec;
    border-radius: 999px;

    color: #5a7177;
    background: #f9fbfc;

    font-size: 9px;
    font-weight: 700;

    cursor: pointer;

    transition:
        border-color .15s ease,
        color .15s ease,
        background .15s ease;
}

.quick-prompt:hover {
    border-color: #bedfe2;

    color: var(--c-primary-dark);
    background: #edf9fa;
}

.quick-prompt i {
    font-size: 9px;
}

.char-counter {
    flex: 0 0 auto;

    padding-top: 5px;

    color: #9aa6b3;

    font-size: 9px;
}

/* FOOTER */

.consilium-footer {
    position: relative;
    z-index: 3;

    min-height: 76px;

    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 18px;

    padding: 14px 22px;

    border-top: 1px solid var(--c-border);

    background:
        rgba(255, 255, 255, .94);

    backdrop-filter: blur(10px);
}

.consilium-footer__summary {
    display: flex;
    align-items: center;
    gap: 10px;

    min-width: 0;
}

.footer-summary-icon {
    width: 35px;
    height: 35px;

    flex: 0 0 35px;

    display: grid;
    place-items: center;

    border-radius: 11px;

    color: #8897a7;
    background: #f2f5f7;

    font-size: 14px;

    transition: .16s ease;
}

.footer-summary-icon.ready {
    color: #25835d;
    background: #eaf8f1;
}

.consilium-footer__summary strong,
.consilium-footer__summary span {
    display: block;
}

.consilium-footer__summary strong {
    color: #425367;

    font-size: 10.5px;
    font-weight: 800;
}

.consilium-footer__summary span {
    margin-top: 2px;

    color: #8b98a7;

    font-size: 9px;
}

.consilium-footer__actions {
    display: flex;
    align-items: center;
    gap: 8px;
}

.btn-cancel,
.btn-create {
    min-height: 42px;

    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 7px;

    padding: 0 16px;

    border-radius: 12px;

    font-family: inherit;
    font-size: 11px;
    font-weight: 800;

    cursor: pointer;

    transition:
        transform .15s ease,
        border-color .15s ease,
        background .15s ease,
        color .15s ease,
        box-shadow .15s ease;
}

.btn-cancel {
    min-width: 88px;

    border: 1px solid #dce4ea;

    color: #596a7c;
    background: #fff;
}

.btn-cancel:hover:not(:disabled) {
    border-color: #cfd9e0;

    color: #34475b;
    background: #f8fafb;

    transform: translateY(-1px);
}

.btn-create {
    min-width: 180px;

    border: 1px solid var(--c-primary);

    color: #fff;

    background:
        linear-gradient(
            135deg,
            #078c9b 0%,
            #057986 100%
        );

    box-shadow:
        0 8px 20px rgba(7, 140, 155, .19),
        inset 0 1px 0 rgba(255, 255, 255, .15);
}

.btn-create:hover:not(:disabled) {
    border-color: var(--c-primary-dark);

    background:
        linear-gradient(
            135deg,
            #077f8d 0%,
            #056d78 100%
        );

    transform: translateY(-1px);

    box-shadow:
        0 10px 24px rgba(7, 140, 155, .24);
}

.btn-create:disabled,
.btn-cancel:disabled {
    opacity: .48;
    cursor: default;
    transform: none;
    box-shadow: none;
}

/* SPINNERS */

.spinner,
.mini-spinner {
    display: inline-block;

    border-radius: 50%;

    animation: rotate .7s linear infinite;
}

.spinner {
    width: 14px;
    height: 14px;

    border: 2px solid rgba(255, 255, 255, .42);
    border-top-color: #fff;
}

.mini-spinner {
    width: 10px;
    height: 10px;

    border: 2px solid rgba(7, 140, 155, .18);
    border-top-color: var(--c-primary);
}

/* AJAX AUTOCOMPLETE: PATIENT + DIAGNOSIS */

.autocomplete-shell {
    position: relative;
    z-index: 1;
}

.autocomplete-shell.is-open {
    z-index: 100;
}

.autocomplete-shell__icon {
    position: absolute;
    z-index: 4;

    top: 50%;
    left: 13px;

    transform: translateY(-50%);

    color: #93a3b4;
    font-size: 13px;

    pointer-events: none;
}

.autocomplete-input {
    width: 100%;
    min-height: 44px;

    padding:
        9px
        68px
        9px
        38px !important;

    border: 1px solid var(--c-border-strong);
    border-radius: 12px;

    outline: none;

    color: var(--c-text);
    background: #fff;

    font-family: inherit;
    font-size: 11px;

    cursor: text;

    transition:
        border-color .16s ease,
        box-shadow .16s ease,
        background .16s ease;
}

/*
 * У type=search браузеры могут рисовать свой крестик.
 * У нас есть собственная кнопка очистки, поэтому нативный скрываем.
 */
.autocomplete-input::-webkit-search-cancel-button,
.autocomplete-input::-webkit-search-decoration {
    appearance: none;
    -webkit-appearance: none;
}

.autocomplete-input::placeholder {
    color: #9ba8b6;
}

.autocomplete-input:hover {
    border-color: #c7d5de;
}

.autocomplete-input:focus {
    border-color: #54b8c1;

    background: #fff;

    box-shadow:
        0 0 0 3px rgba(7, 140, 155, .08),
        0 4px 14px rgba(15, 35, 52, .035);
}

.autocomplete-shell.has-selection .autocomplete-input {
    border-color: #cbe5e8;

    color: #315763;
    background: #fbfefe;
}

.autocomplete-clear {
    position: absolute;
    z-index: 6;

    top: 50%;
    right: 10px;

    width: 28px;
    height: 28px;

    display: grid;
    place-items: center;

    transform: translateY(-50%);

    padding: 0;

    border: 0;
    border-radius: 8px;

    color: #9aa7b4;
    background: transparent;

    font-size: 10px;

    cursor: pointer;
}

.autocomplete-clear:hover {
    color: #586b7e;
    background: #f1f5f7;
}

.autocomplete-loader {
    position: absolute;
    z-index: 5;

    top: 50%;
    right: 43px;

    width: 13px;
    height: 13px;

    transform: translateY(-50%);

    border: 2px solid rgba(7, 140, 155, .18);
    border-top-color: var(--c-primary);
    border-radius: 50%;

    animation: rotate .7s linear infinite;

    pointer-events: none;
}

.autocomplete-dropdown {
    position: absolute;
    z-index: 999;

    top: calc(100% + 6px);
    right: 0;
    left: 0;

    max-height: 280px;
    overflow-y: auto;

    padding: 5px;

    border: 1px solid #dce5ea;
    border-radius: 14px;

    background: #fff;

    box-shadow:
        0 18px 42px rgba(17, 34, 51, .14),
        0 4px 12px rgba(17, 34, 51, .05);

    scrollbar-width: thin;
    scrollbar-color: #cbd7df transparent;
}

.autocomplete-state {
    min-height: 54px;

    display: flex;
    align-items: center;
    justify-content: center;
    gap: 7px;

    padding: 12px;

    color: #8a98a8;

    font-size: 10px;
    text-align: center;
}

.autocomplete-state i {
    color: #9aa9b8;
    font-size: 12px;
}

.autocomplete-option {
    width: 100%;
    min-height: 48px;

    display: flex;
    align-items: center;
    gap: 10px;

    margin: 2px 0;
    padding: 7px 8px;

    border: 0;
    border-radius: 10px;

    color: #2d3d50;
    background: transparent;

    text-align: left;

    cursor: pointer;

    transition:
        background .12s ease,
        transform .12s ease;
}

.autocomplete-option:hover,
.autocomplete-option:focus {
    outline: none;

    background: #f1f8f9;
}

.autocomplete-option:active {
    transform: scale(.995);
}

.autocomplete-option__avatar {
    width: 34px;
    height: 34px;

    flex: 0 0 34px;

    display: grid;
    place-items: center;

    border-radius: 10px;

    font-size: 12px;
}

.autocomplete-option__avatar.patient {
    color: #40709f;
    background: #edf4fb;
}

.autocomplete-option__content {
    min-width: 0;
    flex: 1;
}

.autocomplete-option__content strong,
.autocomplete-option__content small {
    display: block;
}

.autocomplete-option__content strong {
    overflow: hidden;

    color: #2d3d50;

    font-size: 11px;
    font-weight: 760;

    text-overflow: ellipsis;
    white-space: nowrap;
}

.autocomplete-option__content small {
    margin-top: 2px;

    color: #8a97a6;

    font-size: 9.5px;
}

.autocomplete-option__arrow {
    flex: 0 0 auto;

    color: #b0bac4;

    font-size: 10px;
}

.autocomplete-option--diagnosis {
    align-items: flex-start;
}

.autocomplete-diagnosis-code {
    min-width: 58px;

    flex: 0 0 auto;

    display: inline-flex;
    align-items: center;
    justify-content: center;

    margin-top: 1px;
    padding: 5px 7px;

    border-radius: 8px;

    color: var(--c-primary-dark);
    background: #eaf7f8;

    font-size: 9.5px;
    font-weight: 850;
}

/* MULTISELECT */

/* Стрелка раскрытия здесь не нужна: все селекты работают как поисковые поля. */
:deep(.multiselect-caret) {
    display: none !important;
}

:deep(.multiselect) {
    --ms-font-size: 12px;
    --ms-line-height: 1.35;
    --ms-bg: #fff;
    --ms-bg-disabled: #f7f9fa;
    --ms-border-color: var(--c-border-strong);
    --ms-border-width: 1px;
    --ms-border-color-active: #54b8c1;
    --ms-radius: 12px;
    --ms-py: 0;
    --ms-px: 0;
    --ms-ring-width: 0;
    --ms-placeholder-color: #9ba8b6;
    --ms-max-height: 280px;
    --ms-spinner-color: var(--c-primary);
    --ms-caret-color: #99a6b3;
    --ms-clear-color: #9aa7b4;
    --ms-clear-color-hover: #52677a;
    --ms-tag-bg: #e9f7f8;
    --ms-tag-color: #176874;
    --ms-tag-radius: 8px;
    --ms-tag-font-size: 9.5px;
    --ms-option-font-size: 11px;
    --ms-option-bg-pointed: #f2f8f9;
    --ms-option-color-pointed: #24364c;
    --ms-option-bg-selected: #e9f7f8;
    --ms-option-color-selected: #176874;

    min-height: 44px;

    border-radius: 12px;

    box-shadow: none;

    transition:
        border-color .16s ease,
        box-shadow .16s ease;
}

:deep(.consilium-select--with-icon .multiselect-search),
:deep(.consilium-select--with-icon .multiselect-placeholder),
:deep(.consilium-select--with-icon .multiselect-single-label) {
    padding-left: 38px;
}

:deep(.consilium-select--doctors .multiselect-tags) {
    padding-left: 36px;
}

:deep(.multiselect:hover) {
    border-color: #c7d5de;
}

:deep(.multiselect.is-active) {
    border-color: #54b8c1;

    box-shadow:
        0 0 0 3px rgba(7, 140, 155, .08),
        0 4px 14px rgba(15, 35, 52, .035);
}

:deep(.multiselect-placeholder),
:deep(.multiselect-single-label),
:deep(.multiselect-search) {
    font-size: 11px;
}

:deep(.multiselect-dropdown) {
    z-index: 999;

    margin-top: 6px;

    overflow: hidden;

    border: 1px solid #dce5ea;
    border-radius: 14px;

    background: #fff;

    box-shadow:
        0 18px 42px rgba(17, 34, 51, .14),
        0 4px 12px rgba(17, 34, 51, .05);
}

:deep(.multiselect-options) {
    padding: 5px;
}

:deep(.multiselect-option) {
    min-height: 44px;

    margin: 2px 0;
    padding: 7px 8px;

    border-radius: 10px;

    transition:
        background .12s ease,
        color .12s ease;
}

:deep(.multiselect-option.is-pointed) {
    background: #f1f8f9;
}

:deep(.multiselect-option.is-selected) {
    color: #176874;
    background: #eaf7f8;
}

:deep(.multiselect-option.is-selected.is-pointed) {
    color: #176874;
    background: #e1f3f5;
}

:deep(.multiselect-tag) {
    margin: 2px;

    padding: 5px 7px;

    border: 1px solid #cae7ea;

    background: #edf9fa;

    box-shadow: none;
}

:deep(.multiselect-tag-remove) {
    border-radius: 6px;
}

:deep(.multiselect-tag-remove:hover) {
    background: rgba(7, 140, 155, .09);
}

/* TRANSITION */

.consilium-fade-enter-active,
.consilium-fade-leave-active {
    transition: opacity .17s ease;
}

.consilium-fade-enter-active .consilium-modal,
.consilium-fade-leave-active .consilium-modal {
    transition:
        transform .2s ease,
        opacity .17s ease;
}

.consilium-fade-enter-from,
.consilium-fade-leave-to {
    opacity: 0;
}

.consilium-fade-enter-from .consilium-modal,
.consilium-fade-leave-to .consilium-modal {
    opacity: 0;
    transform: translateY(10px) scale(.985);
}

@keyframes rotate {
    to {
        transform: rotate(360deg);
    }
}

/* RESPONSIVE */

@media (max-width: 900px) {
    .consilium-workspace {
        grid-template-columns: 1fr;
    }

    .consilium-card--summary {
        display: none;
    }
}

@media (max-width: 720px) {
    .consilium-overlay {
        align-items: flex-end;

        padding: 0;
    }

    .consilium-modal {
        width: 100%;
        max-height: 96dvh;

        border-right: 0;
        border-bottom: 0;
        border-left: 0;

        border-radius: 22px 22px 0 0;
    }

    .consilium-header {
        min-height: 88px;

        padding: 17px 18px;
    }

    .consilium-header-icon {
        width: 46px;
        height: 46px;

        flex-basis: 46px;

        border-radius: 14px;
    }

    .consilium-header-text h2 {
        font-size: 19px;
    }

    .consilium-header-text p {
        display: none;
    }

    .consilium-body {
        padding: 14px;
    }

    .consilium-card {
        padding: 15px;

        border-radius: 17px;
    }

    .consilium-grid--case {
        grid-template-columns: 1fr;
    }

    .consilium-footer {
        align-items: stretch;

        padding: 12px 14px;

        flex-direction: column;
    }

    .consilium-footer__summary {
        display: none;
    }

    .consilium-footer__actions {
        width: 100%;
    }

    .btn-cancel,
    .btn-create {
        flex: 1;
    }

    .description-footer {
        flex-direction: column;
    }

    .char-counter {
        align-self: flex-end;
    }
}

@media (max-width: 460px) {
    .consilium-card__head p {
        display: none;
    }

    .quick-prompts {
        display: none;
    }

    .consilium-close {
        margin-left: 8px;
    }
}
</style>
