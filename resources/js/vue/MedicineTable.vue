<template>
    <div class="nh-med-registry">
        <div class="nh-med-registry__intro">
            <div>
                <span class="nh-med-registry__eyebrow">Лекарственный реестр</span>
                <h2>Препараты по фармакологическим группам</h2>
                <p>
                    Откройте нужную группу, чтобы посмотреть зарегистрированные препараты,
                    дженерики и рецепторный профиль.
                </p>
            </div>
            <div class="nh-med-registry__counter">
                <strong>{{ groups.length }}</strong>
                <span>групп</span>
            </div>
        </div>

        <div class="nh-med-registry__groups">
            <section
                v-for="(group, index) in groups"
                :key="group.id"
                class="nh-med-group"
                :class="{ 'is-open': group.open }"
            >
                <button
                    type="button"
                    class="nh-med-group__toggle"
                    :aria-expanded="group.open ? 'true' : 'false'"
                    :aria-controls="`medicine-group-${group.id}`"
                    @click="toggleGroup(group)"
                >
                    <span class="nh-med-group__number">{{ String(index + 1).padStart(2, '0') }}</span>
                    <span class="nh-med-group__title">{{ group.title }}</span>
                    <span class="nh-med-group__status">
                        <span v-if="group.loaded && !group.loading" class="nh-med-group__count">
                            {{ group.items.length }}
                        </span>
                        <i class="bi bi-chevron-down" aria-hidden="true"></i>
                    </span>
                </button>

                <div
                    v-show="group.open"
                    :id="`medicine-group-${group.id}`"
                    class="nh-med-group__body"
                >
                    <div v-if="group.loading" class="nh-med-state" aria-live="polite">
                        <span class="nh-med-state__spinner" aria-hidden="true"></span>
                        <div>
                            <strong>Загружаем препараты</strong>
                            <p>Данные появятся через несколько секунд.</p>
                        </div>
                    </div>

                    <div v-else-if="group.error" class="nh-med-state nh-med-state--error" role="alert">
                        <i class="bi bi-exclamation-circle" aria-hidden="true"></i>
                        <div>
                            <strong>Не удалось загрузить группу</strong>
                            <p>{{ group.error }}</p>
                            <button type="button" @click="loadGroup(group, true)">Повторить</button>
                        </div>
                    </div>

                    <div v-else-if="group.loaded && group.items.length === 0" class="nh-med-state">
                        <i class="bi bi-inbox" aria-hidden="true"></i>
                        <div>
                            <strong>В этой группе пока нет записей</strong>
                            <p>Зарегистрированные препараты появятся здесь автоматически.</p>
                        </div>
                    </div>

                    <div v-else class="nh-medicine-list">
                        <article
                            v-for="medicine in group.items"
                            :key="medicine.id"
                            class="nh-medicine-card"
                        >
                            <div class="nh-medicine-card__head">
                                <div>
                                    <span class="nh-medicine-card__label">Препарат</span>
                                    <h3>{{ medicine.name }}</h3>
                                </div>
                                <span class="nh-medicine-card__dot" aria-hidden="true"></span>
                            </div>

                            <dl class="nh-medicine-card__details">
                                <div>
                                    <dt>Дженерики</dt>
                                    <dd v-if="medicine.generics.length">
                                        <span
                                            v-for="generic in medicine.generics"
                                            :key="generic"
                                            class="nh-med-chip"
                                        >{{ generic }}</span>
                                    </dd>
                                    <dd v-else class="nh-medicine-card__empty">Не указаны</dd>
                                </div>

                                <div>
                                    <dt>Рецепторы</dt>
                                    <dd v-if="medicine.receptors.length">
                                        <span
                                            v-for="(receptor, receptorIndex) in medicine.receptors"
                                            :key="`${medicine.id}-r-${receptorIndex}`"
                                            class="nh-med-chip nh-med-chip--receptor nh-receptor-tooltip"
                                            :data-tooltip="receptor.fullName"
                                            :aria-label="receptor.fullName"
                                            tabindex="0"
                                        >
                                            {{ receptor.prefix }}<sub v-if="receptor.index">{{ receptor.index }}</sub>
                                        </span>
                                    </dd>
                                    <dd v-else class="nh-medicine-card__empty">Не указаны</dd>
                                </div>
                            </dl>
                        </article>
                    </div>
                </div>
            </section>
        </div>
    </div>
</template>

<script>
import { defineComponent } from 'vue';

const GROUPS = [
    { id: 1, title: 'Антипсихотики' },
    { id: 2, title: 'Антидепрессанты и противотревожные препараты' },
    { id: 7, title: 'Транквилизаторы, анксиолитики' },
    { id: 5, title: 'Холинолитики, противопаркинсонические средства' },
    { id: 3, title: 'Нормотимики (стабилизаторы настроения)' },
    { id: 10, title: 'Психостимуляторы' },
    { id: 8, title: 'Бета-адренолиноблокаторы' },
    { id: 9, title: 'Гипнотики (снотворные)' },
    { id: 4, title: 'Ингибиторы ацетилхолинэстеразы' },
];

export default defineComponent({
    name: 'MedicineTable',

    data() {
        return {
            groups: GROUPS.map((group) => ({
                ...group,
                open: false,
                loaded: false,
                loading: false,
                error: '',
                items: [],
            })),
        };
    },

    methods: {
        async toggleGroup(group) {
            group.open = !group.open;

            if (group.open && !group.loaded && !group.loading) {
                await this.loadGroup(group);
            }
        },

        async loadGroup(group, force = false) {
            if (group.loading || (group.loaded && !force)) return;

            group.loading = true;
            group.error = '';

            try {
                const response = await fetch(`/api/medicines/${group.id}`, {
                    method: 'GET',
                    headers: {
                        Accept: 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                    credentials: 'same-origin',
                });

                if (!response.ok) {
                    throw new Error(`Сервер вернул ошибку ${response.status}.`);
                }

                const payload = await response.json();
                const medicines = Array.isArray(payload?.data) ? payload.data : [];

                group.items = medicines.map((medicine, index) => this.normalizeMedicine(medicine, index));
                group.loaded = true;
            } catch (error) {
                group.error = error instanceof Error
                    ? error.message
                    : 'Неизвестная ошибка загрузки.';
            } finally {
                group.loading = false;
            }
        },

        normalizeMedicine(medicine, index) {
            const rawName = typeof medicine?.name === 'string' ? medicine.name.trim() : '';
            const name = rawName
                ? rawName.charAt(0).toUpperCase() + rawName.slice(1)
                : 'Без названия';

            const generics = Array.isArray(medicine?.generics)
                ? medicine.generics
                    .map((item) => String(item ?? '').trim())
                    .filter(Boolean)
                : [];

            const receptors = Array.isArray(medicine?.receptors)
                ? medicine.receptors
                    .map((receptor) => this.normalizeReceptor(receptor))
                    .filter(Boolean)
                : [];

            return {
                id: medicine?.id ?? `${name}-${index}`,
                name,
                generics,
                receptors,
            };
        },

        normalizeReceptor(receptor) {
            const rawName = typeof receptor === 'string'
                ? receptor
                : receptor?.name;

            if (typeof rawName !== 'string' || !rawName.trim()) return null;

            const value = rawName.trim();
            const rawFullName = typeof receptor === 'object' && receptor !== null
                ? (receptor.full_name ?? receptor.fullName)
                : '';
            const fullName = typeof rawFullName === 'string' && rawFullName.trim()
                ? rawFullName.trim()
                : value;

            const separatorPosition = value.lastIndexOf('-');

            if (separatorPosition <= 0 || separatorPosition === value.length - 1) {
                return {
                    prefix: value,
                    index: '',
                    fullName,
                };
            }

            return {
                prefix: value.slice(0, separatorPosition),
                index: value.slice(separatorPosition + 1),
                fullName,
            };
        },
    },
});
</script>

<style lang="sass" scoped>
$ink: #23312f
$muted: #6e7a77
$green: #55766d
$green-deep: #2e4e46
$green-soft: #e9efeb
$line: #dce3de
$paper: #fbfbf8
$white: #ffffff

.nh-med-registry
    width: 100%

.nh-med-registry__intro
    display: flex
    align-items: flex-end
    justify-content: space-between
    gap: 32px
    margin-bottom: 30px
    padding-bottom: 26px
    border-bottom: 1px solid $line

    h2
        max-width: 720px
        margin: 0
        color: $ink
        font-size: clamp(28px, 3.5vw, 44px)
        line-height: 1.08
        font-weight: 600
        letter-spacing: -.035em

    p
        max-width: 690px
        margin: 13px 0 0
        color: $muted
        font-size: 14px
        line-height: 1.7

.nh-med-registry__eyebrow
    display: block
    margin-bottom: 10px
    color: $green
    font-size: 10px
    line-height: 1.2
    font-weight: 700
    letter-spacing: .12em
    text-transform: uppercase

.nh-med-registry__counter
    display: flex
    min-width: 88px
    flex-direction: column
    align-items: flex-end
    color: $muted

    strong
        color: $ink
        font-size: 36px
        line-height: 1
        font-weight: 600
        letter-spacing: -.04em

    span
        margin-top: 5px
        font-size: 11px
        text-transform: uppercase
        letter-spacing: .08em

.nh-med-registry__groups
    display: grid
    gap: 10px

.nh-med-group
    overflow: hidden
    border: 1px solid $line
    border-radius: 18px
    background: $white
    transition: border-color .18s ease, box-shadow .18s ease

    &.is-open
        border-color: #bdcbc5
        box-shadow: 0 14px 36px rgba(39, 57, 52, .06)

.nh-med-group__toggle
    display: grid
    width: 100%
    grid-template-columns: 48px minmax(0, 1fr) auto
    gap: 16px
    align-items: center
    min-height: 76px
    padding: 14px 20px
    border: 0
    background: transparent
    color: $ink
    text-align: left
    cursor: pointer

    &:hover
        background: #f8faf8

    &:focus-visible
        outline: 2px solid #7f9a92
        outline-offset: -3px

.nh-med-group__number
    color: #96a29f
    font-size: 10px
    font-weight: 700
    letter-spacing: .08em

.nh-med-group__title
    min-width: 0
    font-size: 16px
    line-height: 1.4
    font-weight: 600

.nh-med-group__status
    display: inline-flex
    align-items: center
    gap: 14px
    color: $green

    i
        font-size: 15px
        transition: transform .2s ease

.is-open .nh-med-group__status i
    transform: rotate(180deg)

.nh-med-group__count
    display: grid
    min-width: 30px
    height: 30px
    place-items: center
    padding: 0 8px
    border-radius: 999px
    background: $green-soft
    color: $green-deep
    font-size: 11px
    font-weight: 700

.nh-med-group__body
    padding: 0 18px 18px
    border-top: 1px solid #edf0ed

.nh-medicine-list
    display: grid
    grid-template-columns: repeat(2, minmax(0, 1fr))
    gap: 12px
    padding-top: 18px

.nh-medicine-card
    padding: 20px
    border: 1px solid #e3e8e4
    border-radius: 15px
    background: $paper

.nh-medicine-card__head
    display: flex
    align-items: flex-start
    justify-content: space-between
    gap: 20px
    margin-bottom: 18px

    h3
        margin: 3px 0 0
        color: $ink
        font-size: 21px
        line-height: 1.25
        font-weight: 600
        letter-spacing: -.02em

.nh-medicine-card__label
    color: #87928f
    font-size: 9px
    font-weight: 700
    letter-spacing: .11em
    text-transform: uppercase

.nh-medicine-card__dot
    flex: 0 0 auto
    width: 9px
    height: 9px
    margin-top: 6px
    border-radius: 50%
    background: #8fa79f
    box-shadow: 0 0 0 5px #e9efeb

.nh-medicine-card__details
    display: grid
    gap: 15px
    margin: 0

    > div
        display: grid
        grid-template-columns: 110px 1fr
        gap: 12px
        align-items: start

    dt
        padding-top: 5px
        color: $muted
        font-size: 11px
        line-height: 1.4
        font-weight: 600

    dd
        display: flex
        flex-wrap: wrap
        gap: 6px
        margin: 0

.nh-med-chip
    display: inline-flex
    align-items: baseline
    min-height: 28px
    padding: 5px 9px
    border: 1px solid #dce4df
    border-radius: 999px
    background: $white
    color: #41514d
    font-size: 11px
    line-height: 1.2

    sub
        margin-left: 1px
        font-size: .72em

.nh-med-chip--receptor
    border-color: #ccd9d3
    background: $green-soft
    color: $green-deep
    font-weight: 600

.nh-receptor-tooltip
    position: relative
    cursor: help
    outline: none

    &::before,
    &::after
        position: absolute
        left: 50%
        z-index: 20
        pointer-events: none
        opacity: 0
        visibility: hidden
        transition: opacity .14s ease, transform .14s ease, visibility .14s ease

    &::before
        content: ''
        bottom: calc(100% + 5px)
        width: 8px
        height: 8px
        background: $ink
        transform: translate(-50%, 4px) rotate(45deg)

    &::after
        content: attr(data-tooltip)
        bottom: calc(100% + 9px)
        width: max-content
        max-width: min(320px, 75vw)
        padding: 8px 11px
        border-radius: 9px
        background: $ink
        box-shadow: 0 8px 24px rgba(25, 39, 35, .18)
        color: $white
        font-size: 11px
        line-height: 1.4
        font-weight: 500
        text-align: center
        white-space: normal
        transform: translate(-50%, 4px)

    &:hover::before,
    &:hover::after,
    &:focus-visible::before,
    &:focus-visible::after
        opacity: 1
        visibility: visible

    &:hover::before,
    &:focus-visible::before
        transform: translate(-50%, 0) rotate(45deg)

    &:hover::after,
    &:focus-visible::after
        transform: translate(-50%, 0)

    &:focus-visible
        box-shadow: 0 0 0 2px rgba(85, 118, 109, .3)

.nh-medicine-card__empty
    padding-top: 4px
    color: #9aa4a1
    font-size: 11px

.nh-med-state
    display: flex
    align-items: center
    gap: 14px
    min-height: 112px
    padding: 22px 12px 6px
    color: $muted

    > i
        display: grid
        width: 40px
        height: 40px
        flex: 0 0 auto
        place-items: center
        border-radius: 13px
        background: $green-soft
        color: $green
        font-size: 18px

    strong
        display: block
        color: $ink
        font-size: 13px
        font-weight: 600

    p
        margin: 4px 0 0
        font-size: 12px
        line-height: 1.5

    button
        margin-top: 10px
        padding: 7px 12px
        border: 1px solid #becdc7
        border-radius: 999px
        background: $white
        color: $green-deep
        font-size: 11px
        font-weight: 600
        cursor: pointer

.nh-med-state--error > i
    background: #f4ebe7
    color: #986551

.nh-med-state__spinner
    width: 34px
    height: 34px
    flex: 0 0 auto
    border: 2px solid #d8e1dc
    border-top-color: $green
    border-radius: 50%
    animation: nh-med-spin .7s linear infinite

@keyframes nh-med-spin
    to
        transform: rotate(360deg)

@media (max-width: 900px)
    .nh-medicine-list
        grid-template-columns: 1fr

@media (max-width: 640px)
    .nh-med-registry__intro
        align-items: flex-start
        flex-direction: column
        gap: 16px

    .nh-med-registry__counter
        flex-direction: row
        align-items: baseline
        gap: 7px

        strong
            font-size: 28px

    .nh-med-group__toggle
        grid-template-columns: 30px minmax(0, 1fr) auto
        gap: 10px
        min-height: 68px
        padding: 12px 13px

    .nh-med-group__title
        font-size: 14px

    .nh-med-group__status
        gap: 8px

    .nh-med-group__count
        min-width: 26px
        height: 26px
        font-size: 10px

    .nh-med-group__body
        padding: 0 10px 10px

    .nh-medicine-list
        padding-top: 10px

    .nh-medicine-card
        padding: 16px

    .nh-medicine-card__head h3
        font-size: 18px

    .nh-medicine-card__details > div
        grid-template-columns: 1fr
        gap: 6px

    .nh-medicine-card__details dt
        padding-top: 0

@media (prefers-reduced-motion: reduce)
    .nh-med-group,
    .nh-med-group__status i,
    .nh-med-state__spinner
        transition: none
        animation: none
</style>
