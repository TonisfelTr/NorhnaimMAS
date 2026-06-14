<template>
    <div class="wrap">
        <div class="card" v-if="loaded">
            <h2 class="title">{{ test.title }}</h2>

            <!-- INTRO -->
            <div v-if="stage==='intro'">
                <div class="muted" v-if="test.description" v-html="test.description"></div>
                <div class="muted">{{ test.instructions }} 123</div>
                <div style="margin-top:14px">
                    <button class="btn" @click="startQuestions">Перейти к вопросам</button>
                </div>
            </div>

            <!-- QUESTIONS (ONE BY ONE) -->
            <div v-else-if="stage==='questions'">
                <div class="progress muted">
                    Вопрос {{ currentIndex + 1 }} из {{ items.length }}
                </div>

                <div class="q">
                    <div class="q-title">
                        <span v-if="current.required" class="req">*</span>
                        {{ current.title }}
                    </div>
                    <div class="q-help" v-if="current.help">{{ current.help }}</div>

                    <!-- RADIO -->
                    <div v-if="current.type==='radio'" class="opts">
                        <label v-for="opt in current.options" :key="opt.value" class="opt">
                            <input type="radio"
                                   :name="'q_'+current.id"
                                   :value="opt.value"
                                   v-model="answers[current.id]">
                            <span>{{ opt.label }}</span>
                        </label>
                    </div>

                    <!-- CHECKBOX -->
                    <div v-else-if="current.type==='checkbox'" class="opts">
                        <label v-for="opt in current.options" :key="opt.value" class="opt">
                            <input type="checkbox"
                                   :value="opt.value"
                                   :checked="isChecked(current.id, opt.value)"
                                   @change="toggleCheckbox(current.id, opt.value)">
                            <span>{{ opt.label }}</span>
                        </label>
                    </div>

                    <!-- TEXT -->
                    <div v-else-if="current.type==='text'">
                        <textarea class="ta" v-model="answers[current.id]" rows="4"></textarea>
                    </div>

                    <!-- SCALE -->
                    <div v-else-if="current.type==='scale'" class="opts">
                        <input type="range"
                               class="range"
                               :min="current.scale?.min ?? 0"
                               :max="current.scale?.max ?? 10"
                               v-model.number="answers[current.id]">
                        <div class="muted">Ответ: {{ answers[current.id] ?? '' }}</div>
                    </div>

                    <div v-else class="muted">Неизвестный тип вопроса: {{ current.type }}</div>
                </div>

                <div class="actions">
                    <button class="btn muted" @click="prev" :disabled="currentIndex===0 || saving">
                        Назад
                    </button>

                    <button class="btn muted" @click="autosave(true)" :disabled="saving">
                        Сохранить
                    </button>

                    <button class="btn" @click="next" :disabled="saving">
                        {{ isLast ? 'Завершить' : 'Следующий вопрос' }}
                    </button>
                </div>

                <div v-if="error" class="err">{{ error }}</div>
                <div v-if="savedHint" class="ok">Сохранено ✅</div>
            </div>

            <!-- DONE -->
            <div v-else class="done">
                <h2>Тест завершён</h2>
                <div class="muted">Пожалуйста, позовите врача.</div>
            </div>
        </div>

        <div v-else class="card">
            <div class="muted">Загрузка теста…</div>
        </div>
    </div>
</template>

<script>
export default {
    name: "TestRunWizard",
    props: {
        payloadUrl: { type: String, required: true },
        autosaveUrl: { type: String, required: true },
        submitUrl: { type: String, required: true }, // ВАЖНО: submit (пациент завершил)
    },
    data() {
        return {
            loaded: false,
            stage: "intro",
            test: { title: "Тест", description: "" },
            items: [],      // вопросы
            answers: {},

            currentIndex: 0,
            saving: false,
            error: null,
            savedHint: false,

            timer: null,
            beforeUnloadHandler: null,
        };
    },
    computed: {
        current() {
            return this.items[this.currentIndex] || { id:null, type:'', title:'', options:[] };
        },
        isLast() {
            return this.currentIndex >= this.items.length - 1;
        },
    },
    async mounted() {
        this.beforeUnloadHandler = (e) => { e.preventDefault(); e.returnValue = "Идёт прохождение теста."; };
        window.addEventListener("beforeunload", this.beforeUnloadHandler);

        await this.loadPayload();

        this.timer = setInterval(() => {
            if (this.stage === "questions") this.autosave(false).catch(()=>{});
        }, 15000);
    },
    beforeUnmount() {
        if (this.timer) clearInterval(this.timer);
        window.removeEventListener("beforeunload", this.beforeUnloadHandler);
    },
    methods: {
        async loadPayload() {
            this.error = null;

            const r = await fetch(this.payloadUrl, { headers: { "Accept": "application/json" } });
            const j = await r.json().catch(() => ({}));
            if (!r.ok || !j.ok) {
                this.error = j.message || "Не удалось загрузить тест";
                return;
            }

            this.test = j.test;
            this.items = j.items || j.questions || [];

            // ✅ правильные поля
            const state = j.state || {};
            this.answers = state.answers || {};
            this.currentIndex = Number.isInteger(state.current_index) ? state.current_index : 0;

            // ✅ определяем stage по статусу сессии
            const sess = j.session || {};
            if (sess.status === "submitted" && sess.in_progress === false) {
                // пациент закончил — всегда DONE (и на обновлении тоже)
                this.stage = "done";
            } else if (sess.status === "in_progress" && sess.in_progress === true) {
                // если тест уже начат — возвращаем к вопросам
                this.stage = "questions";
            } else {
                // иначе показываем интро
                this.stage = "intro";
            }

            this.loaded = true;

            if (!this.items.length) {
                this.error = "В тесте нет вопросов.";
            }
        }
        ,

        startQuestions() {
            if (!this.items.length) return;
            this.stage = "questions";
            this.currentIndex = 0;
        },

        isChecked(qid, val) {
            const a = this.answers[qid];
            return Array.isArray(a) ? a.includes(val) : false;
        },
        toggleCheckbox(qid, val) {
            const a = this.answers[qid];
            const arr = Array.isArray(a) ? [...a] : [];
            const i = arr.indexOf(val);
            if (i >= 0) arr.splice(i, 1); else arr.push(val);
            this.answers[qid] = arr;
        },

        isAnswered(q) {
            const a = this.answers[q.id];
            return (
                (Array.isArray(a) && a.length > 0) ||
                (typeof a === "string" && a.trim().length > 0) ||
                (typeof a === "number")
            );
        },

        async autosave(showHint) {
            this.error = null;
            this.savedHint = false;
            this.saving = true;

            const csrf = document.querySelector('meta[name="csrf-token"]')?.content || "";
            const payload = { answers: this.answers, currentIndex: this.currentIndex };

            const r = await fetch(this.autosaveUrl, {
                method: "POST",
                headers: {
                    "X-CSRF-TOKEN": csrf,
                    "Accept": "application/json",
                    "Content-Type": "application/x-www-form-urlencoded; charset=UTF-8",
                },
                body: new URLSearchParams({ data: JSON.stringify(payload) }),
            });

            this.saving = false;
            if (!r.ok) {
                const j = await r.json().catch(() => ({}));
                this.error = j.message || "Не удалось сохранить";
                return false;
            }

            if (showHint) {
                this.savedHint = true;
                setTimeout(() => (this.savedHint = false), 1200);
            }
            return true;
        },

        prev() {
            if (this.currentIndex > 0) this.currentIndex--;
        },

        async next() {
            this.error = null;

            if (this.current.required && !this.isAnswered(this.current)) {
                this.error = "Ответьте на обязательный вопрос.";
                return;
            }

            // Автосейв при переходе (по желанию — можно убрать)
            await this.autosave(false);

            if (this.isLast) {
                await this.submit();
            } else {
                this.currentIndex++;
            }
        },

        async submit() {
            if (!confirm("Завершить тест? Изменения после этого невозможны.")) return;

            // финальный автосейв
            await this.autosave(false);

            const csrf = document.querySelector('meta[name="csrf-token"]')?.content || "";
            const r = await fetch(this.submitUrl, {
                method: "POST",
                headers: { "X-CSRF-TOKEN": csrf, "Accept": "application/json" },
            });

            if (!r.ok) {
                const j = await r.json().catch(() => ({}));
                this.error = j.message || "Не удалось завершить тест";
                return;
            }

            if (this.timer) clearInterval(this.timer);
            window.removeEventListener("beforeunload", this.beforeUnloadHandler);
            this.stage = "done";
        },
    },
};
</script>

<style scoped>
.wrap{max-width:860px;margin:30px auto;padding:0 14px}
.card{background:#fff;border-radius:12px;padding:22px;box-shadow:0 10px 30px rgba(0,0,0,.08)}
.title{margin:0 0 10px}
.muted{color:#667085}
.progress{margin-bottom:10px}
.q{padding:10px 0}
.q-title{font-weight:600;margin-bottom:8px}
.q-help{color:#667085;font-size:13px;margin-bottom:10px}
.req{color:#b42318;margin-right:6px}
.opts{display:grid;gap:10px}
.opt{display:flex;gap:10px;align-items:flex-start}
.ta{width:100%;min-height:110px;border:1px solid #e5e7eb;border-radius:10px;padding:10px;font:inherit}
.range{width:100%}
.actions{display:flex;gap:10px;margin-top:16px}
.btn{padding:10px 16px;border:0;border-radius:10px;background:#1f6bff;color:#fff;cursor:pointer}
.btn.muted{background:#98a2b3}
.err{margin-top:10px;color:#b42318;background:#fee4e2;border:1px solid #fecdca;padding:10px;border-radius:10px}
.ok{margin-top:10px;color:#067647;background:#d1fadf;border:1px solid #a6f4c5;padding:10px;border-radius:10px}
.done{text-align:center;padding:40px 10px}
</style>
