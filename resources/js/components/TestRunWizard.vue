<template>
    <div class="wrap">
        <div v-if="!loaded" class="card">
            <div class="muted">Загрузка…</div>
        </div>

        <div v-else class="card">
            <h2 class="title">{{ test.title }}</h2>

            <div v-if="error" class="err">
                {{ error }}
            </div>

            <div v-if="stage === 'intro'">
                <div
                    v-if="test.description"
                    class="test-text"
                    v-html="test.description"
                ></div>

                <div v-if="test.instructions" class="instructions">
                    <strong>Инструкция</strong>
                    <div class="test-text" v-html="test.instructions"></div>
                </div>

                <div class="intro-actions">
                    <button
                        type="button"
                        class="btn"
                        :disabled="!totalItems || saving"
                        @click="startQuestions"
                    >
                        {{ startButtonText }}
                    </button>
                </div>
            </div>

            <div v-else-if="stage === 'questions'">
                <div v-if="!isSortTest" class="progress-block">
                    <div class="progress-row">
                        <span>{{ progressLabel }}</span>
                        <span>{{ progressPercent }}%</span>
                    </div>

                    <div class="progress-line">
                        <div
                            class="progress-fill"
                            :style="{ width: progressPercent + '%' }"
                        ></div>
                    </div>
                </div>

                <div v-else class="sort-summary">
                    <span>Карточек: {{ totalItems }}</span>
                    <span v-if="savedHint" class="sort-summary-saved">Порядок сохранён</span>
                    <span v-else class="sort-summary-hint">Перетащите карточки мышью</span>
                </div>

                <div v-if="isSortTest" class="sort-test">
                    <div class="sort-description">
                        Расположите карточки слева направо: от наиболее подходящей
                        к наименее подходящей. Перетаскивайте карточки за любую область.
                    </div>

                    <div class="sort-scale" aria-hidden="true">
                        <span>Больше подходит</span>
                        <span class="sort-scale-arrow">→</span>
                        <span>Меньше подходит</span>
                    </div>

                    <div class="sort-strip-shell">
                        <div
                            class="sort-strip"
                            role="list"
                            aria-label="Порядок карточек"
                            @dragover.prevent
                        >
                            <article
                                v-for="(card, index) in orderedSortCards"
                                :key="card.id"
                                class="sort-card"
                                :class="{
                                    'sort-card--dragging': String(draggedSortCardId) === String(card.id),
                                    'sort-card--over': String(dragOverSortCardId) === String(card.id),
                                }"
                                :draggable="!saving"
                                role="listitem"
                                tabindex="0"
                                @dragstart="onSortDragStart(card.id, $event)"
                                @dragenter.prevent="onSortDragEnter(card.id)"
                                @dragover.prevent
                                @drop.prevent="onSortDrop(card.id, $event)"
                                @dragend="onSortDragEnd"
                                @keydown.left.prevent="moveSortCard(card.id, -1)"
                                @keydown.right.prevent="moveSortCard(card.id, 1)"
                            >
                                <div class="sort-card-position">{{ index + 1 }}</div>
                                <div class="sort-card-handle" aria-hidden="true">⋮⋮</div>

                                <div
                                    v-if="card.type === 'color'"
                                    class="sort-card-visual sort-card-color"
                                    :style="{ backgroundColor: card.color || '#d0d5dd' }"
                                ></div>

                                <div
                                    v-else-if="card.type === 'image'"
                                    class="sort-card-visual sort-card-image-wrap"
                                >
                                    <img
                                        v-if="card.image_url"
                                        :src="card.image_url"
                                        :alt="card.title"
                                        class="sort-card-image"
                                        draggable="false"
                                    >

                                    <div v-else class="sort-card-missing">
                                        Изображение отсутствует
                                    </div>
                                </div>

                                <div
                                    v-else
                                    class="sort-card-visual sort-card-text"
                                >
                                    {{ card.text || card.title }}
                                </div>

                                <div class="sort-card-footer">
                                    <span class="sort-card-title">
                                        {{ card.title || card.text || 'Карточка' }}
                                    </span>

                                    <div class="sort-card-controls">
                                        <button
                                            type="button"
                                            class="sort-move-btn"
                                            :disabled="saving || index === 0"
                                            aria-label="Переместить карточку влево"
                                            @click.stop="moveSortCard(card.id, -1)"
                                        >
                                            ←
                                        </button>

                                        <button
                                            type="button"
                                            class="sort-move-btn"
                                            :disabled="saving || index === orderedSortCards.length - 1"
                                            aria-label="Переместить карточку вправо"
                                            @click.stop="moveSortCard(card.id, 1)"
                                        >
                                            →
                                        </button>
                                    </div>
                                </div>
                            </article>
                        </div>
                    </div>

                    <div class="sort-footnote">
                        На небольшом экране строку можно прокручивать горизонтально.
                        Стрелки на карточке позволяют изменить порядок без перетаскивания.
                    </div>

                    <div class="actions sort-actions">
                        <button
                            type="button"
                            class="btn btn-secondary"
                            :disabled="saving || !canResetSortOrder"
                            @click="resetSortOrder"
                        >
                            Вернуть исходный порядок
                        </button>

                        <button
                            type="button"
                            class="btn"
                            :disabled="saving || !isSortComplete"
                            @click="next"
                        >
                            Завершить тест
                        </button>
                    </div>

                    <div v-if="savedHint" class="ok">
                        Порядок сохранён
                    </div>
                </div>

                <template v-else-if="current">
                    <div v-if="isImageTest" class="image-question">
                        <div v-if="current.image_url" class="image-wrap">
                            <img
                                :src="current.image_url"
                                :alt="current.title || 'Карточка теста'"
                                class="test-image"
                            >
                        </div>

                        <div v-else class="image-missing">
                            Изображение карточки отсутствует.
                        </div>

                        <div class="q">
                            <div
                                v-if="current.title"
                                class="q-title card-title"
                            >
                                {{ current.title }}
                            </div>

                            <div class="q-title">
                                <span v-if="current.required" class="req">*</span>
                                {{
                                    current.question
                                    || 'Опишите, что вы видите на изображении.'
                                }}
                            </div>

                            <textarea
                                v-model="answers[current.id]"
                                class="ta"
                                rows="6"
                                placeholder="Введите ответ"
                            ></textarea>
                        </div>
                    </div>

                    <div v-else class="q">
                        <div class="q-title">
                            <span v-if="current.required" class="req">*</span>
                            {{ current.title }}
                        </div>

                        <div v-if="current.help" class="q-help">
                            {{ current.help }}
                        </div>

                        <div v-if="current.type === 'radio'" class="opts">
                            <label
                                v-for="(opt, index) in current.options"
                                :key="optionValue(opt, index)"
                                class="opt"
                            >
                                <input
                                    v-model="answers[current.id]"
                                    type="radio"
                                    :name="'q_' + current.id"
                                    :value="optionValue(opt, index)"
                                >
                                <span>{{ optionLabel(opt) }}</span>
                            </label>
                        </div>

                        <div
                            v-else-if="current.type === 'checkbox'"
                            class="opts"
                        >
                            <label
                                v-for="(opt, index) in current.options"
                                :key="optionValue(opt, index)"
                                class="opt"
                            >
                                <input
                                    type="checkbox"
                                    :value="optionValue(opt, index)"
                                    :checked="isChecked(current.id, optionValue(opt, index))"
                                    @change="toggleCheckbox(current.id, optionValue(opt, index))"
                                >
                                <span>{{ optionLabel(opt) }}</span>
                            </label>
                        </div>

                        <div v-else-if="current.type === 'text'">
                            <textarea
                                v-model="answers[current.id]"
                                class="ta"
                                rows="5"
                                placeholder="Введите ответ"
                            ></textarea>
                        </div>

                        <div v-else class="muted">
                            Неизвестный тип вопроса: {{ current.type }}
                        </div>
                    </div>

                    <div class="actions">
                        <button
                            type="button"
                            class="btn btn-secondary"
                            :disabled="currentIndex === 0 || saving"
                            @click="prev"
                        >
                            Назад
                        </button>

                        <button
                            type="button"
                            class="btn"
                            :disabled="saving"
                            @click="next"
                        >
                            {{ isLast ? 'Завершить тест' : nextButtonText }}
                        </button>
                    </div>

                    <div v-if="savedHint" class="ok">
                        Сохранено
                    </div>
                </template>

                <div v-else class="err">
                    Не удалось определить текущий элемент теста.
                </div>
            </div>

            <div v-else-if="stage === 'done'" class="done">
                <h2>{{ doneTitle }}</h2>
                <div class="muted">{{ doneText }}</div>
            </div>
        </div>
    </div>
</template>

<script>
const TEST_TYPES = Object.freeze({
    QUESTIONNAIRE: "questionnaire",
    IMAGE: "image",
    SORT: "sort",
});

const FINISHED_STATUSES = new Set([
    "submitted",
    "complete",
    "completed",
    "finished",
    "cancelled",
    "timeout",
]);

export default {
    name: "TestRunWizard",

    props: {
        payloadUrl: {
            type: String,
            required: true,
        },
        submitUrl: {
            type: String,
            required: true,
        },
        finishUrl: {
            type: String,
            required: true,
        },
    },

    data() {
        return {
            loaded: false,
            stage: "intro",
            doneReason: null,

            test: {
                id: null,
                code: null,
                title: "Тест",
                type: TEST_TYPES.QUESTIONNAIRE,
                description: "",
                instructions: "",
            },

            items: [],
            cards: [],
            answers: {},
            sortOrder: [],
            initialSortOrder: [],
            draggedSortCardId: null,
            dragOverSortCardId: null,

            currentIndex: 0,
            saving: false,
            savedHint: false,
            error: null,

            autosaveTimer: null,
            sessionExpiryTimer: null,
            beforeUnloadHandler: null,
        };
    },

    computed: {
        testType() {
            return this.test.type;
        },

        isQuestionnaire() {
            return this.testType === TEST_TYPES.QUESTIONNAIRE;
        },

        isImageTest() {
            return this.testType === TEST_TYPES.IMAGE;
        },

        isSortTest() {
            return this.testType === TEST_TYPES.SORT;
        },

        activeItems() {
            return this.isQuestionnaire ? this.items : this.cards;
        },

        totalItems() {
            return this.activeItems.length;
        },

        current() {
            if (this.isSortTest) {
                return null;
            }

            return this.activeItems[this.currentIndex] || null;
        },

        orderedSortCards() {
            if (!this.isSortTest) {
                return [];
            }

            const cardsById = new Map(
                this.cards.map((card) => [String(card.id), card])
            );

            return this.sortOrder
                .map((id) => cardsById.get(String(id)))
                .filter(Boolean);
        },

        canResetSortOrder() {
            if (!this.isSortTest) {
                return false;
            }

            return !this.sameOrder(
                this.sortOrder,
                this.initialSortOrder
            );
        },

        isSortComplete() {
            return (
                this.isSortTest
                && this.totalItems > 0
                && this.sortOrder.length === this.totalItems
            );
        },

        isLast() {
            if (this.isSortTest) {
                return this.isSortComplete;
            }

            return (
                this.totalItems > 0
                && this.currentIndex >= this.totalItems - 1
            );
        },

        progressPercent() {
            if (!this.totalItems) {
                return 0;
            }

            const completed = this.isSortTest
                ? this.totalItems
                : this.currentIndex + 1;

            return Math.round((completed / this.totalItems) * 100);
        },

        progressLabel() {
            if (this.isSortTest) {
                return `Карточек в порядке: ${this.totalItems}`;
            }

            const entityName = this.isImageTest ? "Карточка" : "Вопрос";
            return `${entityName} ${this.currentIndex + 1} из ${this.totalItems}`;
        },

        nextButtonText() {
            return this.isImageTest
                ? "Следующая карточка"
                : "Следующий вопрос";
        },

        startButtonText() {
            if (this.isSortTest) {
                return "Перейти к сортировке";
            }

            return this.isImageTest
                ? "Перейти к карточкам"
                : "Перейти к вопросам";
        },

        doneTitle() {
            if (this.doneReason === "timeout") {
                return "Время прохождения истекло";
            }

            if (this.doneReason === "cancelled") {
                return "Тест прерван";
            }

            return "Тест завершён";
        },

        doneText() {
            if (this.doneReason === "timeout") {
                return "Установленное время прохождения теста закончилось.";
            }

            if (this.doneReason === "cancelled") {
                return "Частичные результаты сохранены.";
            }

            return "Пожалуйста, позовите врача.";
        },
    },

    async mounted() {
        this.beforeUnloadHandler = (event) => {
            if (this.stage === "done") {
                return;
            }

            event.preventDefault();
            event.returnValue = "Идёт прохождение теста.";
        };

        window.addEventListener("beforeunload", this.beforeUnloadHandler);

        await this.loadPayload();

        if (this.stage !== "done") {
            this.startAutosave();
        }
    },

    beforeUnmount() {
        this.stopLifecycleHandlers();
    },

    beforeDestroy() {
        this.stopLifecycleHandlers();
    },

    methods: {
        normalizeTestType(value) {
            const type = String(value || "")
                .trim()
                .toLowerCase();

            if (type === TEST_TYPES.IMAGE) {
                return TEST_TYPES.IMAGE;
            }

            if (type === TEST_TYPES.SORT) {
                return TEST_TYPES.SORT;
            }

            return TEST_TYPES.QUESTIONNAIRE;
        },

        normalizeTest(test) {
            return {
                id: test.id ?? null,
                code: test.code ?? null,
                title: test.title || test.name || "Тест",
                type: this.normalizeTestType(
                    test.type || test.test_type
                ),
                description: test.description || "",
                instructions: test.instructions || "",
            };
        },

        normalizeItems(items) {
            if (!Array.isArray(items)) {
                return [];
            }

            return items.map((item, index) => {
                let type = String(item.type || "text")
                    .trim()
                    .toLowerCase();

                if (["single", "single_choice"].includes(type)) {
                    type = "radio";
                } else if (["multiple", "multiple_choice"].includes(type)) {
                    type = "checkbox";
                }

                return {
                    id: item.id ?? index,
                    title: item.title || item.text || `Вопрос ${index + 1}`,
                    help: item.help || item.description || "",
                    type,
                    required: Boolean(item.required ?? true),
                    options: Array.isArray(item.options) ? item.options : [],
                };
            });
        },

        normalizeCards(cards) {
            if (!Array.isArray(cards)) {
                return [];
            }

            return cards
                .map((card, index) => {
                    let type = String(
                        card.type || (this.isSortTest ? "text" : "image")
                    )
                        .trim()
                        .toLowerCase();

                    if (!["color", "text", "image"].includes(type)) {
                        type = this.isSortTest ? "text" : "image";
                    }

                    return {
                        id: card.id ?? index,
                        type,
                        title: card.title || `Карточка ${index + 1}`,
                        text: card.text || "",
                        color: card.color || "",
                        question: card.question || "",
                        sort: Number(card.sort ?? index + 1),
                        image_url:
                            card.image_url
                            || card.image
                            || card.url
                            || "",
                        required: Boolean(card.required ?? true),
                    };
                })
                .sort((left, right) => left.sort - right.sort);
        },

        normalizeAnswers(value) {
            if (!value || Array.isArray(value) || typeof value !== "object") {
                return {};
            }

            return { ...value };
        },

        normalizeSortOrder(value) {
            if (!Array.isArray(value)) {
                return [];
            }

            const normalized = [];
            const used = new Set();

            value.forEach((rawId) => {
                const id = Number(rawId);

                if (!Number.isInteger(id) || id <= 0 || used.has(id)) {
                    return;
                }

                used.add(id);
                normalized.push(id);
            });

            return normalized;
        },

        normalizeCurrentIndex() {
            if (!this.totalItems) {
                this.currentIndex = 0;
                return;
            }

            this.currentIndex = Math.max(
                0,
                Math.min(this.currentIndex, this.totalItems - 1)
            );
        },

        initializeAnswerKeys() {
            if (this.isSortTest) {
                return;
            }

            this.activeItems.forEach((entity) => {
                const key = entity.id;

                if (Object.prototype.hasOwnProperty.call(this.answers, key)) {
                    return;
                }

                const initialValue = entity.type === "checkbox" ? [] : null;

                if (typeof this.$set === "function") {
                    this.$set(this.answers, key, initialValue);
                } else {
                    this.answers[key] = initialValue;
                }
            });
        },

        prepareSortOrder() {
            if (!this.isSortTest) {
                this.sortOrder = [];
                this.initialSortOrder = [];
                return;
            }

            const defaultOrder = this.cards.map((card) => Number(card.id));
            const allowedIds = new Set(defaultOrder.map((id) => String(id)));

            const savedOrder = this.sortOrder.filter(
                (id) => allowedIds.has(String(id))
            );

            const savedIds = new Set(savedOrder.map((id) => String(id)));
            const missingIds = defaultOrder.filter(
                (id) => !savedIds.has(String(id))
            );

            this.initialSortOrder = [...defaultOrder];
            this.sortOrder = [...savedOrder, ...missingIds];
        },

        sameOrder(left, right) {
            if (!Array.isArray(left) || !Array.isArray(right)) {
                return false;
            }

            return (
                left.length === right.length
                && left.every(
                    (id, index) => String(id) === String(right[index])
                )
            );
        },

        async loadPayload() {
            this.loaded = false;
            this.error = null;

            try {
                const json = await this.requestJson(this.payloadUrl);

                this.test = this.normalizeTest(json.test || {});
                this.items = this.normalizeItems(json.items || []);

                const rawCards = this.isSortTest
                    ? json.sort
                    : json.cards;

                this.cards = this.normalizeCards(
                    Array.isArray(rawCards) ? rawCards : []
                );

                this.answers = this.normalizeAnswers(json.state?.answers);
                this.sortOrder = this.normalizeSortOrder(
                    json.state?.sort_order
                );

                this.prepareSortOrder();
                this.initializeAnswerKeys();

                if (this.isSortTest) {
                    this.currentIndex = this.sortOrder.length;
                } else {
                    const rawIndex = Number(
                        json.state?.current_index ?? 0
                    );

                    this.currentIndex = Number.isInteger(rawIndex)
                        ? rawIndex
                        : 0;

                    this.normalizeCurrentIndex();
                }

                const session = json.session || {};

                this.scheduleSessionExpiry(session.expires_at);
                this.applySessionStage(session);
                this.validateLoadedContent();
            } catch (error) {
                if (error.payload?.timed_out) {
                    this.doneReason = "timeout";
                    this.stage = "done";
                    this.error =
                        error.message
                        || "Время прохождения теста истекло.";
                    this.stopLifecycleHandlers();
                } else {
                    this.error =
                        error instanceof Error
                            ? error.message
                            : "Не удалось загрузить тест";
                }
            } finally {
                this.loaded = true;
            }
        },

        applySessionStage(session) {
            const status = String(session.status || "");

            if (FINISHED_STATUSES.has(status)) {
                this.stage = "done";
                this.doneReason = status === "timeout"
                    ? "timeout"
                    : status === "cancelled"
                        ? "cancelled"
                        : "completed";
                return;
            }

            if (status === "in_progress" && session.in_progress === true) {
                this.stage = "questions";
                return;
            }

            this.stage = "intro";
        },

        validateLoadedContent() {
            if (this.isQuestionnaire && !this.items.length) {
                this.error = "В тесте нет вопросов.";
            } else if (this.isImageTest && !this.cards.length) {
                this.error = "В тесте нет карточек.";
            } else if (this.isSortTest && !this.cards.length) {
                this.error = "В тесте нет карточек для сортировки.";
            }
        },

        startQuestions() {
            this.error = null;

            if (!this.totalItems) {
                this.validateLoadedContent();
                return;
            }

            this.stage = "questions";
        },

        startAutosave() {
            if (this.autosaveTimer) {
                clearInterval(this.autosaveTimer);
            }

            if (this.isSortTest) {
                return;
            }

            this.autosaveTimer = setInterval(() => {
                if (
                    this.stage === "questions"
                    && !this.saving
                    && this.current
                    && this.isAnswered(this.current)
                ) {
                    this.submitCurrent(false).catch(() => {});
                }
            }, 15000);
        },

        scheduleSessionExpiry(expiresAt) {
            if (this.sessionExpiryTimer) {
                clearTimeout(this.sessionExpiryTimer);
                this.sessionExpiryTimer = null;
            }

            if (!expiresAt) {
                return;
            }

            const expiresAtMs = new Date(expiresAt).getTime();

            if (!Number.isFinite(expiresAtMs)) {
                return;
            }

            const delay = expiresAtMs - Date.now();

            if (delay <= 0) {
                this.handleSessionExpiry();
                return;
            }

            this.sessionExpiryTimer = setTimeout(() => {
                this.handleSessionExpiry();
            }, delay + 500);
        },

        async handleSessionExpiry() {
            try {
                const json = await this.requestJson(this.payloadUrl);

                if (json.session?.status === "timeout") {
                    this.doneReason = "timeout";
                    this.stage = "done";
                    this.error = "Время прохождения теста истекло.";
                    this.stopLifecycleHandlers();
                }
            } catch (error) {
                if (error.payload?.timed_out) {
                    this.doneReason = "timeout";
                    this.stage = "done";
                    this.error =
                        error.message
                        || "Время прохождения теста истекло.";
                    this.stopLifecycleHandlers();
                    return;
                }

                this.error =
                    "Не удалось проверить время прохождения теста.";
            }
        },

        csrfToken() {
            return (
                document.querySelector('meta[name="csrf-token"]')?.content
                || ""
            );
        },

        async requestJson(url, options = {}) {
            const response = await fetch(url, options);
            const json = await response.json().catch(() => ({}));

            if (!response.ok || json.ok === false) {
                const message =
                    json.message
                    || this.firstValidationError(json.errors)
                    || "Ошибка запроса";

                const error = new Error(message);
                error.status = response.status;
                error.payload = json;
                throw error;
            }

            return json;
        },

        firstValidationError(errors) {
            if (!errors || typeof errors !== "object") {
                return "";
            }

            for (const value of Object.values(errors)) {
                if (Array.isArray(value) && value.length) {
                    return String(value[0]);
                }

                if (typeof value === "string") {
                    return value;
                }
            }

            return "";
        },

        requestHeaders(json = false) {
            const headers = {
                "X-CSRF-TOKEN": this.csrfToken(),
                Accept: "application/json",
            };

            if (json) {
                headers["Content-Type"] = "application/json";
            }

            return headers;
        },

        optionValue(option, index) {
            return option.value ?? option.id ?? index;
        },

        optionLabel(option) {
            return (
                option.label
                ?? option.text
                ?? option.title
                ?? String(option.value ?? "")
            );
        },

        isChecked(key, value) {
            const answer = this.answers[key];
            return Array.isArray(answer) && answer.includes(value);
        },

        toggleCheckbox(key, value) {
            const values = Array.isArray(this.answers[key])
                ? [...this.answers[key]]
                : [];

            const index = values.indexOf(value);

            if (index >= 0) {
                values.splice(index, 1);
            } else {
                values.push(value);
            }

            if (typeof this.$set === "function") {
                this.$set(this.answers, key, values);
            } else {
                this.answers[key] = values;
            }
        },

        isAnswered(entity) {
            if (!entity) {
                return false;
            }

            const answer = this.answers[entity.id];

            return (
                (Array.isArray(answer) && answer.length > 0)
                || (typeof answer === "string" && answer.trim().length > 0)
                || typeof answer === "number"
                || typeof answer === "boolean"
            );
        },

        async submitCurrent(
            ensureRequired,
            targetIndex = this.currentIndex
        ) {
            if (this.isSortTest) {
                return this.saveSortOrder(Boolean(ensureRequired));
            }

            this.error = null;
            this.savedHint = false;

            if (!this.current) {
                this.error = this.isImageTest
                    ? "Карточка не найдена."
                    : "Вопрос не найден.";
                return false;
            }

            if (
                ensureRequired
                && this.current.required
                && !this.isAnswered(this.current)
            ) {
                this.error = this.isImageTest
                    ? "Введите ответ по карточке, затем нажмите «Следующая карточка»."
                    : "Ответьте на вопрос, затем нажмите «Следующий вопрос».";
                return false;
            }

            if (!ensureRequired && !this.isAnswered(this.current)) {
                return true;
            }

            this.saving = true;

            try {
                const payload = {
                    test_type: this.testType,
                    answer: this.answers[this.current.id] ?? null,
                    current_index: targetIndex,
                };

                if (this.isImageTest) {
                    payload.card_id = this.current.id;
                } else {
                    payload.item_id = this.current.id;
                }

                await this.requestJson(this.submitUrl, {
                    method: "POST",
                    headers: this.requestHeaders(true),
                    body: JSON.stringify(payload),
                });

                this.showSavedHint();
                return true;
            } catch (error) {
                this.handleRequestError(
                    error,
                    "Не удалось сохранить ответ"
                );
                return false;
            } finally {
                this.saving = false;
            }
        },

        sortCardIndex(cardId) {
            return this.sortOrder.findIndex(
                (id) => String(id) === String(cardId)
            );
        },

        onSortDragStart(cardId, event) {
            if (!this.isSortTest || this.saving) {
                event.preventDefault();
                return;
            }

            this.draggedSortCardId = Number(cardId);
            this.dragOverSortCardId = Number(cardId);

            if (event.dataTransfer) {
                event.dataTransfer.effectAllowed = "move";
                event.dataTransfer.setData("text/plain", String(cardId));
            }
        },

        onSortDragEnter(cardId) {
            if (!this.draggedSortCardId || this.saving) {
                return;
            }

            this.dragOverSortCardId = Number(cardId);
        },

        async onSortDrop(targetCardId, event) {
            if (!this.isSortTest || this.saving) {
                this.onSortDragEnd();
                return;
            }

            const sourceCardId = Number(
                this.draggedSortCardId
                || event.dataTransfer?.getData("text/plain")
            );

            const targetId = Number(targetCardId);

            if (
                !Number.isInteger(sourceCardId)
                || !Number.isInteger(targetId)
                || sourceCardId === targetId
            ) {
                this.onSortDragEnd();
                return;
            }

            const targetRect = event.currentTarget.getBoundingClientRect();
            const insertAfter = event.clientX > targetRect.left + targetRect.width / 2;

            const nextOrder = [...this.sortOrder];
            const sourceIndex = nextOrder.findIndex(
                (id) => String(id) === String(sourceCardId)
            );

            if (sourceIndex < 0) {
                this.onSortDragEnd();
                return;
            }

            nextOrder.splice(sourceIndex, 1);

            let targetIndex = nextOrder.findIndex(
                (id) => String(id) === String(targetId)
            );

            if (targetIndex < 0) {
                this.onSortDragEnd();
                return;
            }

            if (insertAfter) {
                targetIndex += 1;
            }

            nextOrder.splice(targetIndex, 0, sourceCardId);
            this.onSortDragEnd();

            await this.commitSortOrder(nextOrder);
        },

        onSortDragEnd() {
            this.draggedSortCardId = null;
            this.dragOverSortCardId = null;
        },

        async moveSortCard(cardId, direction) {
            if (!this.isSortTest || this.saving) {
                return;
            }

            const currentIndex = this.sortCardIndex(cardId);
            const targetIndex = currentIndex + Number(direction);

            if (
                currentIndex < 0
                || targetIndex < 0
                || targetIndex >= this.sortOrder.length
            ) {
                return;
            }

            const nextOrder = [...this.sortOrder];
            const [movedId] = nextOrder.splice(currentIndex, 1);
            nextOrder.splice(targetIndex, 0, movedId);

            await this.commitSortOrder(nextOrder);
        },

        async resetSortOrder() {
            if (
                !this.isSortTest
                || this.saving
                || !this.canResetSortOrder
            ) {
                return;
            }

            if (!confirm("Вернуть исходный порядок карточек?")) {
                return;
            }

            await this.commitSortOrder([...this.initialSortOrder]);
        },

        async commitSortOrder(nextOrder) {
            const normalizedOrder = this.normalizeSortOrder(nextOrder);

            if (this.sameOrder(normalizedOrder, this.sortOrder)) {
                return true;
            }

            const previousOrder = [...this.sortOrder];

            this.sortOrder = normalizedOrder;
            this.currentIndex = normalizedOrder.length;
            this.error = null;

            const saved = await this.saveSortOrder(false);

            if (!saved) {
                this.sortOrder = previousOrder;
                this.currentIndex = previousOrder.length;
            }

            return saved;
        },

        async saveSortOrder(requireComplete = false) {
            this.error = null;
            this.savedHint = false;

            if (!this.isSortTest) {
                return false;
            }

            if (requireComplete && !this.isSortComplete) {
                this.error = "Сначала расположите все карточки.";
                return false;
            }

            this.saving = true;

            try {
                const json = await this.requestJson(this.submitUrl, {
                    method: "POST",
                    headers: this.requestHeaders(true),
                    body: JSON.stringify({
                        test_type: TEST_TYPES.SORT,
                        sort_order: [...this.sortOrder],
                        current_index: this.sortOrder.length,
                    }),
                });

                if (Array.isArray(json.sort_order)) {
                    this.sortOrder = this.normalizeSortOrder(
                        json.sort_order
                    );
                }

                this.currentIndex = this.sortOrder.length;
                this.showSavedHint();
                return true;
            } catch (error) {
                this.handleRequestError(
                    error,
                    "Не удалось сохранить порядок карточек"
                );
                return false;
            } finally {
                this.saving = false;
            }
        },

        async prev() {
            if (this.isSortTest) {
                return;
            }

            if (this.currentIndex === 0 || this.saving) {
                return;
            }

            const targetIndex = this.currentIndex - 1;
            const saved = await this.submitCurrent(false, targetIndex);

            if (saved) {
                this.currentIndex = targetIndex;
                this.error = null;
            }
        },

        async next() {
            if (this.saving) {
                return;
            }

            if (this.isSortTest) {
                const saved = await this.saveSortOrder(true);

                if (saved) {
                    await this.finish();
                }

                return;
            }

            const targetIndex = this.isLast
                ? this.currentIndex
                : this.currentIndex + 1;

            const saved = await this.submitCurrent(true, targetIndex);

            if (!saved) {
                return;
            }

            if (this.isLast) {
                await this.finish();
                return;
            }

            this.currentIndex = targetIndex;
            this.error = null;
        },

        async finish() {
            if (
                !confirm(
                    "Завершить тест? Изменения после этого невозможны."
                )
            ) {
                return;
            }

            this.error = null;
            this.saving = true;

            try {
                const json = await this.requestJson(this.finishUrl, {
                    method: "POST",
                    headers: this.requestHeaders(false),
                });

                if (json.timed_out) {
                    this.doneReason = "timeout";
                    this.error = "Время прохождения теста истекло.";
                } else {
                    this.doneReason = "completed";
                }

                this.stage = "done";
                this.stopLifecycleHandlers();
            } catch (error) {
                this.handleRequestError(
                    error,
                    "Не удалось завершить тест"
                );
            } finally {
                this.saving = false;
            }
        },

        handleRequestError(error, fallbackMessage) {
            if (error.payload?.timed_out) {
                this.doneReason = "timeout";
                this.stage = "done";
                this.error =
                    error.message
                    || "Время прохождения теста истекло.";
                this.stopLifecycleHandlers();
                return;
            }

            this.error =
                error instanceof Error
                    ? error.message
                    : fallbackMessage;
        },

        showSavedHint() {
            this.savedHint = true;

            setTimeout(() => {
                this.savedHint = false;
            }, 800);
        },

        stopLifecycleHandlers() {
            if (this.autosaveTimer) {
                clearInterval(this.autosaveTimer);
                this.autosaveTimer = null;
            }

            if (this.sessionExpiryTimer) {
                clearTimeout(this.sessionExpiryTimer);
                this.sessionExpiryTimer = null;
            }

            if (this.beforeUnloadHandler) {
                window.removeEventListener(
                    "beforeunload",
                    this.beforeUnloadHandler
                );
                this.beforeUnloadHandler = null;
            }
        },
    },
};
</script>

<style scoped>
.wrap {
    max-width: 1000px;
    margin: 30px auto;
    padding: 0 14px;
}

.card {
    background: #ffffff;
    border-radius: 16px;
    padding: 28px;
    box-shadow: 0 10px 30px rgba(0, 0, 0, 0.08);
}

.title {
    margin: 0 0 18px;
}

.test-text {
    color: #475467;
    line-height: 1.6;
}

.instructions {
    margin-top: 18px;
    padding: 16px;
    border: 1px solid #d0d5dd;
    border-radius: 12px;
    background: #f9fafb;
}

.instructions strong {
    display: block;
    margin-bottom: 8px;
}

.intro-actions {
    margin-top: 18px;
}

.muted {
    color: #667085;
}

.progress-block {
    margin-bottom: 18px;
}

.progress-row {
    display: flex;
    justify-content: space-between;
    gap: 12px;
    margin-bottom: 8px;
    color: #667085;
    font-size: 14px;
}

.progress-line {
    height: 8px;
    overflow: hidden;
    border-radius: 999px;
    background: #eaecf0;
}

.progress-fill {
    height: 100%;
    border-radius: inherit;
    background: #1f6bff;
    transition: width 0.2s ease;
}

.q {
    margin-top: 18px;
}

.q-title {
    margin-bottom: 10px;
    font-weight: 600;
    line-height: 1.45;
}

.card-title {
    font-size: 18px;
}

.q-help {
    margin-bottom: 12px;
    color: #667085;
    font-size: 14px;
}

.req {
    margin-right: 5px;
    color: #b42318;
}

.opts {
    display: grid;
    gap: 10px;
}

.opt {
    display: flex;
    align-items: flex-start;
    gap: 10px;
}

.ta {
    box-sizing: border-box;
    width: 100%;
    min-height: 120px;
    padding: 12px;
    border: 1px solid #d0d5dd;
    border-radius: 10px;
    font: inherit;
    resize: vertical;
}

.image-question {
    display: grid;
    gap: 18px;
}

.image-wrap {
    display: grid;
    place-items: center;
    box-sizing: border-box;
    width: 100%;
    height: clamp(320px, 58vh, 680px);
    padding: clamp(8px, 1.5vw, 20px);
    overflow: hidden;
    border: 1px solid #eaecf0;
    border-radius: 14px;
    background: #f9fafb;
}

.test-image {
    display: block;
    width: 100%;
    height: 100%;
    object-fit: contain;
    object-position: center;
    border-radius: 10px;
}

.image-missing {
    padding: 28px;
    border: 1px dashed #f04438;
    border-radius: 12px;
    color: #b42318;
    text-align: center;
    background: #fff5f4;
}

.actions {
    display: flex;
    justify-content: space-between;
    gap: 10px;
    margin-top: 20px;
}

.btn {
    padding: 10px 16px;
    border: 0;
    border-radius: 10px;
    background: #1f6bff;
    color: #ffffff;
    font: inherit;
    cursor: pointer;
}

.btn-secondary {
    background: #667085;
}

.btn-danger {
    background: #d92d20;
}

.btn:disabled {
    cursor: not-allowed;
    opacity: 0.55;
}

.err {
    margin: 0 0 14px;
    padding: 10px;
    border: 1px solid #fecdca;
    border-radius: 10px;
    color: #b42318;
    background: #fee4e2;
}

.ok {
    margin-top: 10px;
    padding: 10px;
    border: 1px solid #a6f4c5;
    border-radius: 10px;
    color: #067647;
    background: #d1fadf;
}

.done {
    padding: 40px 10px;
    text-align: center;
}

.sort-test {
    display: grid;
    gap: 16px;
}

.sort-summary {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 12px;
    margin-bottom: 16px;
    color: #667085;
    font-size: 14px;
}

.sort-summary-saved {
    color: #067647;
    font-weight: 700;
}

.sort-summary-hint {
    color: #667085;
}

.sort-description {
    padding: 14px 16px;
    border: 1px solid #b9e2e7;
    border-radius: 12px;
    background: #eef9fa;
    color: #285b63;
    font-size: 14px;
    line-height: 1.5;
}

.sort-scale {
    display: grid;
    grid-template-columns: minmax(0, 1fr) auto minmax(0, 1fr);
    align-items: center;
    gap: 12px;
    color: #475467;
    font-size: 13px;
    font-weight: 700;
}

.sort-scale > :last-child {
    text-align: right;
}

.sort-scale-arrow {
    color: #048696;
    font-size: 22px;
}

.sort-strip-shell {
    width: 100%;
    overflow-x: auto;
    overflow-y: hidden;
    padding: 6px 2px 14px;
    scrollbar-gutter: stable;
    overscroll-behavior-inline: contain;
}

.sort-strip {
    display: flex;
    align-items: stretch;
    gap: 12px;
    width: max-content;
    min-width: 100%;
}

.sort-card {
    position: relative;
    display: flex;
    flex: 0 0 clamp(145px, 17vw, 190px);
    min-width: 0;
    min-height: 210px;
    overflow: hidden;
    flex-direction: column;
    border: 2px solid transparent;
    border-radius: 16px;
    background: #fff;
    box-shadow: 0 6px 20px rgba(16, 24, 40, 0.08);
    cursor: grab;
    user-select: none;
    transition:
        transform 0.15s ease,
        border-color 0.15s ease,
        box-shadow 0.15s ease,
        opacity 0.15s ease;
}

.sort-card:hover,
.sort-card:focus-visible {
    border-color: #048696;
    box-shadow: 0 10px 26px rgba(16, 24, 40, 0.13);
    outline: none;
}

.sort-card:active {
    cursor: grabbing;
}

.sort-card--dragging {
    opacity: 0.38;
    transform: scale(0.98);
}

.sort-card--over {
    border-color: #048696;
    box-shadow: 0 0 0 4px rgba(4, 134, 150, 0.12);
}

.sort-card-position {
    position: absolute;
    top: 9px;
    left: 9px;
    z-index: 2;
    display: grid;
    width: 30px;
    height: 30px;
    place-items: center;
    border-radius: 10px;
    background: #048696;
    color: #fff;
    font-size: 13px;
    font-weight: 800;
    box-shadow: 0 3px 9px rgba(0, 0, 0, 0.14);
}

.sort-card-handle {
    position: absolute;
    top: 8px;
    right: 10px;
    z-index: 2;
    padding: 3px 6px;
    border-radius: 8px;
    background: rgba(255, 255, 255, 0.88);
    color: #667085;
    font-size: 18px;
    font-weight: 800;
    letter-spacing: -4px;
    line-height: 1;
}

.sort-card-visual {
    box-sizing: border-box;
    width: 100%;
    min-height: 155px;
}

.sort-card-color {
    border-bottom: 1px solid rgba(0, 0, 0, 0.08);
}

.sort-card-text {
    display: grid;
    place-items: center;
    padding: 42px 16px 18px;
    color: #1f2937;
    font-size: 16px;
    font-weight: 700;
    line-height: 1.4;
    text-align: center;
    overflow-wrap: anywhere;
    background: #f8fafc;
}

.sort-card-image-wrap {
    display: grid;
    place-items: center;
    padding: 35px 10px 10px;
    background: #f8fafc;
}

.sort-card-image {
    display: block;
    width: 100%;
    height: 120px;
    object-fit: contain;
    pointer-events: none;
}

.sort-card-missing {
    padding: 12px;
    color: #98a2b3;
    font-size: 13px;
    text-align: center;
}

.sort-card-footer {
    display: grid;
    grid-template-columns: minmax(0, 1fr) auto;
    align-items: center;
    gap: 8px;
    padding: 11px 12px;
    border-top: 1px solid #eaecf0;
    background: #fff;
}

.sort-card-title {
    overflow: hidden;
    color: #344054;
    font-size: 13px;
    font-weight: 700;
    text-overflow: ellipsis;
    white-space: nowrap;
}

.sort-card-controls {
    display: inline-flex;
    gap: 4px;
}

.sort-move-btn {
    display: grid;
    width: 29px;
    height: 29px;
    place-items: center;
    padding: 0;
    border: 1px solid #d0d5dd;
    border-radius: 8px;
    background: #fff;
    color: #344054;
    font: inherit;
    cursor: pointer;
}

.sort-move-btn:hover:not(:disabled) {
    border-color: #048696;
    color: #048696;
    background: #eef9fa;
}

.sort-move-btn:disabled {
    cursor: not-allowed;
    opacity: 0.35;
}

.sort-footnote {
    color: #667085;
    font-size: 12px;
    line-height: 1.45;
}

.sort-actions {
    align-items: center;
}

@media (max-width: 640px) {
    .card {
        padding: 20px;
    }

    .image-wrap {
        height: clamp(240px, 45vh, 420px);
        padding: 8px;
        border-radius: 10px;
    }

    .actions {
        flex-direction: column-reverse;
    }

    .btn,
    .sort-actions {
        width: 100%;
    }

    .sort-summary,
    .sort-scale {
        font-size: 12px;
    }

    .sort-card {
        flex-basis: 145px;
        min-height: 190px;
    }

    .sort-card-visual {
        min-height: 135px;
    }

    .sort-card-image {
        height: 102px;
    }
}

@media (min-width: 1400px) {
    .image-wrap {
        height: min(68vh, 760px);
    }
}
</style>
