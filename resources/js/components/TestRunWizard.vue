<template>
  <div class="wrap">
    <div class="card" v-if="loaded">
      <h2 class="title">{{ test.title }}</h2>

      <div v-if="stage==='intro'">
        <div class="muted" v-if="test.description" v-html="test.description"></div>
        <div style="margin-top:14px">
          <button class="btn" @click="startQuestions">Перейти к вопросам</button>
        </div>
      </div>

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

          <div v-if="current.type==='radio'" class="opts">
            <label v-for="opt in current.options" :key="opt.value" class="opt">
              <input type="radio"
                     :name="'q_'+current.id"
                     :value="opt.value"
                     v-model="answers[current.id]">
              <span>{{ opt.label }}</span>
            </label>
          </div>

          <div v-else-if="current.type==='text'">
            <textarea class="ta" v-model="answers[current.id]" rows="4"></textarea>
          </div>

          <div v-else class="muted">Неизвестный тип вопроса: {{ current.type }}</div>
        </div>

        <div class="actions">
          <button class="btn muted" @click="prev" :disabled="currentIndex===0 || saving">Назад</button>

          <button class="btn" @click="next" :disabled="saving">
            {{ isLast ? 'Завершить тест' : 'Следующий вопрос' }}
          </button>
        </div>

        <div v-if="error" class="err">{{ error }}</div>
        <div v-if="savedHint" class="ok">Сохранено ✅</div>
      </div>

      <div v-else class="done">
        <h2>Тест завершён</h2>
        <div class="muted">Пожалуйста, позовите врача.</div>
      </div>
    </div>

    <div v-else class="card">
      <div class="muted">Загрузка…</div>
      <div v-if="error" class="err" style="margin-top:12px">{{ error }}</div>
    </div>
  </div>
</template>

<script>
export default {
  name: "TestRunWizard",
  props: {
    payloadUrl: { type: String, required: true },
    submitUrl: { type: String, required: true },
    finishUrl: { type: String, required: true },
  },
  data() {
    return {
      loaded: false,
      stage: "intro",
      test: { title: "Тест", description: "" },
      items: [],
      answers: {},
      currentIndex: 0,
      saving: false,
      savedHint: false,
      error: null,
      timer: null,
      beforeUnloadHandler: null,
    };
  },
  computed: {
    current() {
      return this.items[this.currentIndex] || { id: null, type: "", title: "", required: false, options: [] };
    },
    isLast() {
      return this.currentIndex >= this.items.length - 1;
    },
  },
  async mounted() {
    this.beforeUnloadHandler = (e) => {
      e.preventDefault();
      e.returnValue = "Идёт прохождение теста.";
    };
    window.addEventListener("beforeunload", this.beforeUnloadHandler);

    await this.loadPayload();

    // автосейв как submit текущего ответа (каждые 15с)
    this.timer = setInterval(() => {
      if (this.stage === "questions") this.submitCurrent(false).catch(() => {});
    }, 15000);
  },
  beforeUnmount() {
    if (this.timer) clearInterval(this.timer);
    window.removeEventListener("beforeunload", this.beforeUnloadHandler);
  },
  methods: {
    async loadPayload() {
      this.error = null;
      const r = await fetch(this.payloadUrl, { headers: { Accept: "application/json" } });
      const j = await r.json().catch(() => ({}));
      if (!r.ok || !j.ok) {
        this.error = j.message || "Не удалось загрузить тест";
        return;
      }

      this.test = j.test;
      this.items = j.items || [];
      const state = j.state || {};
      this.answers = state.answers || {};
      this.currentIndex = Number.isInteger(state.current_index) ? state.current_index : 0;

      if (!this.items.length) {
        this.error = "В тесте нет вопросов.";
        return;
      }

      // нормализуем индекс
      if (this.currentIndex < 0 || this.currentIndex > this.items.length - 1) this.currentIndex = 0;

      this.loaded = true;
    },

    startQuestions() {
      if (!this.items.length) return;
      this.stage = "questions";
    },

    isAnswered(q) {
      const a = this.answers[q.id];
      return (typeof a === "number") || (typeof a === "string" && a.trim().length > 0);
    },

    async submitCurrent(ensureRequired) {
      this.error = null;
      this.savedHint = false;

      if (ensureRequired && this.current.required && !this.isAnswered(this.current)) {
        this.error = "Ответьте на вопрос, затем нажмите «Следующий вопрос».";
        return false;
      }

      this.saving = true;

      const csrf = document.querySelector('meta[name="csrf-token"]')?.content || "";
      const payload = {
        item_id: this.current.id,
        answer: this.answers[this.current.id] ?? null,
        current_index: this.currentIndex,
      };

      const r = await fetch(this.submitUrl, {
        method: "POST",
        headers: {
          "X-CSRF-TOKEN": csrf,
          Accept: "application/json",
          "Content-Type": "application/json",
        },
        body: JSON.stringify(payload),
      });

      this.saving = false;

      if (!r.ok) {
        const j = await r.json().catch(() => ({}));
        this.error = j.message || "Не удалось сохранить ответ";
        return false;
      }

      this.savedHint = true;
      setTimeout(() => (this.savedHint = false), 800);
      return true;
    },

    prev() {
      if (this.currentIndex > 0) this.currentIndex--;
    },

    async next() {
      const ok = await this.submitCurrent(true);
      if (!ok) return;

      if (this.isLast) {
        await this.finish();
      } else {
        this.currentIndex++;
      }
    },

    async finish() {
      if (!confirm("Завершить тест? Изменения после этого невозможны.")) return;

      const csrf = document.querySelector('meta[name="csrf-token"]')?.content || "";
      const r = await fetch(this.finishUrl, {
        method: "POST",
        headers: { "X-CSRF-TOKEN": csrf, Accept: "application/json" },
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
.card{background:#fff;border-radius:12px;padding:28px;box-shadow:0 10px 30px rgba(0,0,0,.08)}
.title{margin:0 0 10px}
.muted{color:#667085}
.progress{margin-bottom:10px}
.q-title{font-weight:600;margin-bottom:8px}
.q-help{color:#667085;font-size:13px;margin-bottom:10px}
.req{color:#b42318;margin-right:6px}
.opts{display:grid;gap:10px}
.opt{display:flex;gap:10px;align-items:flex-start}
.ta{width:100%;min-height:110px;border:1px solid #e5e7eb;border-radius:10px;padding:10px;font:inherit}
.actions{display:flex;gap:10px;margin-top:16px}
.btn{padding:10px 16px;border:0;border-radius:10px;background:#1f6bff;color:#fff;cursor:pointer}
.btn.muted{background:#98a2b3}
.err{margin-top:10px;color:#b42318;background:#fee4e2;border:1px solid #fecdca;padding:10px;border-radius:10px}
.ok{margin-top:10px;color:#067647;background:#d1fadf;border:1px solid #a6f4c5;padding:10px;border-radius:10px}
.done{text-align:center;padding:40px 10px}
</style>
