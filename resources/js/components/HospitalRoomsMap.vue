<template>
    <div class="hrm-page">
        <section class="hrm-head">
            <div>
                <div class="hrm-kicker">Стационар</div>
                <h1>Палаты и койки</h1>
                <p>
                    Схема палат, коечный фонд и текущее размещение пациентов.
                    Свободные койки отмечены цветом интерфейса, занятые — красным.
                </p>
            </div>

            <button type="button" class="hrm-btn hrm-btn--primary" @click="openRoomModal()">
                <i class="bi bi-plus-lg"></i>
                Добавить палату
            </button>
        </section>

        <section class="hrm-stats" v-if="!loading">
            <article>
                <span class="hrm-stat-icon"><i class="bi bi-door-open"></i></span>
                <div><strong>{{ stats.rooms }}</strong><small>палат</small></div>
            </article>
            <article>
                <span class="hrm-stat-icon"><i class="bi bi-hospital"></i></span>
                <div><strong>{{ stats.beds }}</strong><small>коек всего</small></div>
            </article>
            <article>
                <span class="hrm-stat-icon"><i class="bi bi-check2-circle"></i></span>
                <div><strong>{{ freeBedsTotal }}</strong><small>свободно</small></div>
            </article>
            <article class="is-danger">
                <span class="hrm-stat-icon"><i class="bi bi-person-fill"></i></span>
                <div><strong>{{ stats.occupied }}</strong><small>занято</small></div>
            </article>
        </section>

        <div v-if="error" class="hrm-alert hrm-alert--error">
            <i class="bi bi-exclamation-triangle"></i>
            <span>{{ error }}</span>
            <button type="button" @click="load">Повторить</button>
        </div>

        <div v-if="notice" class="hrm-alert hrm-alert--success">
            <i class="bi bi-check-circle"></i>
            <span>{{ notice }}</span>
        </div>

        <div v-if="loading" class="hrm-loading">
            <div class="hrm-loading__content">
                <div class="hrm-loading__title">{{ loadingText }}</div>

                <div
                    class="hrm-loading__progress"
                    role="progressbar"
                    :aria-valuenow="loadingProgress"
                    aria-valuemin="0"
                    aria-valuemax="100"
                >
                    <div
                        class="hrm-loading__bar"
                        :style="{ width: `${loadingProgress}%` }"
                    ></div>
                </div>

                <div class="hrm-loading__percent">{{ loadingProgress }}%</div>
            </div>
        </div>

        <div v-else class="hrm-layout">
            <aside class="hrm-sidebar">
                <div class="hrm-sidebar__head">
                    <strong>Палаты</strong>
                    <span>{{ roomPagination.total }}</span>
                </div>

                <label class="hrm-filter-label">Отделение</label>
                <select
                    :value="selectedDepartmentId"
                    class="hrm-control"
                    @change="changeDepartment"
                >
                    <option value="">Все отделения</option>
                    <option v-for="department in departments" :key="department.id" :value="String(department.id)">
                        {{ departmentLabel(department) }}
                    </option>
                </select>

                <div class="hrm-room-list">
                    <button
                        v-for="room in filteredRooms"
                        :key="room.id"
                        type="button"
                        class="hrm-room-row"
                        :class="{ 'is-active': selectedRoomId === String(room.id) }"
                        @click="selectRoom(room.id)"
                    >
                        <span class="hrm-room-row__icon"><i class="bi bi-door-open"></i></span>
                        <span class="hrm-room-row__copy">
                            <strong>Палата №{{ room.number }}</strong>
                            <small>{{ room.department_name }}</small>
                            <small v-if="room.is_supervision" style="color:#097a85;font-weight:750;">Надзорная палата</small>
                            <small v-if="room.floor">Этаж: {{ room.floor }}</small>
                        </span>
                        <span class="hrm-room-row__count">
                            {{ room.stats.occupied }}/{{ room.stats.beds }}
                        </span>
                    </button>

                    <div v-if="filteredRooms.length === 0" class="hrm-empty-small">
                        Палат пока нет.
                    </div>
                </div>

                <div v-if="roomPagination.total > 0" class="hrm-room-pagination">
                    <div
                        class="hrm-room-pagination__meta hrm-pagination-tooltip-trigger"
                        tabindex="0"
                        :aria-label="`Показаны палаты ${roomPagination.from}–${roomPagination.to} из ${roomPagination.total}. Страница ${roomPagination.currentPage} из ${roomPagination.lastPage}.`"
                    >
                        {{ roomPagination.from }}–{{ roomPagination.to }}
                        из {{ roomPagination.total }}

                        <div class="hrm-pagination-tooltip" role="tooltip">
                            <div class="hrm-pagination-tooltip__title">Пагинация палат</div>
                            <div class="hrm-pagination-tooltip__row">
                                <span>Сейчас показаны</span>
                                <strong>{{ roomPagination.from }}–{{ roomPagination.to }}</strong>
                            </div>
                            <div class="hrm-pagination-tooltip__row">
                                <span>Всего палат</span>
                                <strong>{{ roomPagination.total }}</strong>
                            </div>
                            <div class="hrm-pagination-tooltip__row">
                                <span>Страница</span>
                                <strong>{{ roomPagination.currentPage }} из {{ roomPagination.lastPage }}</strong>
                            </div>
                            <div v-if="roomPagination.lastPage > 1" class="hrm-pagination-tooltip__hint">
                                Используйте стрелки справа, чтобы переключать страницы.
                            </div>
                        </div>
                    </div>

                    <div
                        v-if="roomPagination.lastPage > 1"
                        class="hrm-room-pagination__controls"
                    >
                        <button
                            type="button"
                            :disabled="roomPagination.currentPage <= 1"
                            title="Предыдущая страница"
                            @click="changeRoomPage(roomPagination.currentPage - 1)"
                        >
                            <i class="bi bi-chevron-left"></i>
                        </button>

                        <span>
                            {{ roomPagination.currentPage }} / {{ roomPagination.lastPage }}
                        </span>

                        <button
                            type="button"
                            :disabled="roomPagination.currentPage >= roomPagination.lastPage"
                            title="Следующая страница"
                            @click="changeRoomPage(roomPagination.currentPage + 1)"
                        >
                            <i class="bi bi-chevron-right"></i>
                        </button>
                    </div>
                </div>
            </aside>

            <section class="hrm-workspace">
                <template v-if="selectedRoom">
                    <header class="hrm-room-head">
                        <div>
                            <div class="hrm-room-head__eyebrow">
                                {{ selectedRoom.building_label }}
                            </div>
                            <h2>
                                Палата №{{ selectedRoom.number }}
                                <span v-if="selectedRoom.name">— {{ selectedRoom.name }}</span>
                            </h2>
                            <div class="hrm-room-head__meta">
                                <span>{{ selectedRoom.department_name }}</span>
                                <span v-if="selectedRoom.is_supervision">Надзорная палата</span>
                                <span v-if="selectedRoom.floor">этаж {{ selectedRoom.floor }}</span>
                                <span>{{ selectedRoom.stats.free }} свободно</span>
                                <span>{{ selectedRoom.stats.occupied }} занято</span>
                            </div>
                        </div>

                        <div class="hrm-room-actions">
                            <button type="button" class="hrm-btn" @click="openRoomModal(selectedRoom)">
                                <i class="bi bi-pencil"></i>
                                Палата
                            </button>
                            <button
                                type="button"
                                class="hrm-btn"
                                :class="{ 'is-active': editMode }"
                                @click="toggleEditMode"
                            >
                                <i class="bi" :class="editMode ? 'bi-eye' : 'bi-grid-3x3-gap'"></i>
                                {{ editMode ? 'Просмотр' : 'Редактор схемы' }}
                            </button>
                        </div>
                    </header>

                    <!-- Размеры только для просмотра: настройки и координаты БД не меняются. -->
                    <div
                        v-if="!editMode"
                        class="hrm-view-size-toolbar"
                        role="group"
                        aria-label="Размер схемы палаты на экране"
                    >
                        <div class="hrm-view-size-toolbar__title">
                            <i class="bi bi-aspect-ratio"></i>
                            <div>
                                <strong>Размер схемы</strong>
                                <small>Только отображение, исходный план не меняется</small>
                            </div>
                        </div>
                        <label class="hrm-view-size-field">
                            <span>Ширина</span>
                            <input
                                type="range"
                                :min="viewCanvasScaleMin"
                                :max="viewCanvasScaleMax"
                                step="5"
                                :value="viewCanvasSize.width"
                                :aria-valuetext="`${viewCanvasSize.width}% ширины схемы`"
                                @input="setViewCanvasScale('width', $event.target.value)"
                            >
                            <output>{{ viewCanvasSize.width }}%</output>
                        </label>
                        <label class="hrm-view-size-field">
                            <span>Высота</span>
                            <input
                                type="range"
                                :min="viewCanvasScaleMin"
                                :max="viewCanvasScaleMax"
                                step="5"
                                :value="viewCanvasSize.height"
                                :aria-valuetext="`${viewCanvasSize.height}% высоты схемы`"
                                @input="setViewCanvasScale('height', $event.target.value)"
                            >
                            <output>{{ viewCanvasSize.height }}%</output>
                        </label>
                        <span v-if="viewMapDimensions" class="hrm-view-size-toolbar__pixels">
                            {{ viewMapDimensions.width }} × {{ viewMapDimensions.height }} px
                        </span>
                        <button
                            type="button"
                            class="hrm-view-size-reset"
                            title="Вписать схему в экран"
                            :disabled="viewCanvasSize.width === 100 && viewCanvasSize.height === 100"
                            @click="resetViewCanvasScale"
                        >
                            <i class="bi bi-arrows-fullscreen"></i>
                            Вписать
                        </button>
                    </div>

                    <div v-if="editMode" class="hrm-editor-toolbar">
                        <button
                            type="button"
                            :class="{ 'is-placement-active': !pendingPlacementType }"
                            title="Снять выделение и отменить добавление элемента"
                            @click="clearPlacementFocus"
                        >
                            <i class="bi bi-cursor"></i>
                            Курсор
                        </button>
                        <button
                            type="button"
                            :class="{ 'is-placement-active': pendingPlacementType === 'bed' }"
                            @click="selectPlacementTool('bed')"
                        >
                            <i class="bi bi-hospital"></i>
                            Койка
                        </button>
                        <button
                            type="button"
                            :class="{ 'is-placement-active': pendingPlacementType === 'bedside_table' }"
                            @click="selectPlacementTool('bedside_table')"
                        >
                            <i class="bi bi-square"></i>
                            Тумбочка
                        </button>
                        <button
                            type="button"
                            :class="{ 'is-placement-active': pendingPlacementType === 'cabinet' }"
                            @click="selectPlacementTool('cabinet')"
                        >
                            <i class="bi bi-archive"></i>
                            Шкаф
                        </button>
                        <button
                            type="button"
                            :class="{ 'is-placement-active': pendingPlacementType === 'column' }"
                            @click="selectPlacementTool('column')"
                        >
                            <i class="bi bi-square-fill"></i>
                            Колонна
                        </button>
                        <button type="button" @click="selectPlacementTool('entrance')">
                            <i class="bi bi-door-open"></i>
                            Вход
                        </button>
                        <button
                            type="button"
                            :class="{ 'is-placement-active': pendingPlacementType === 'window' }"
                            @click="selectPlacementTool('window')"
                        >
                            <i class="bi bi-window"></i>
                            Окно
                        </button>
                        <button
                            type="button"
                            :class="{ 'is-placement-active': pendingPlacementType === 'socket' }"
                            @click="selectPlacementTool('socket')"
                        >
                            <i class="bi bi-plug"></i>
                            Розетка
                        </button>
                        <button
                            type="button"
                            :class="{ 'is-placement-active': pendingPlacementType === 'toilet' }"
                            @click="selectPlacementTool('toilet')"
                        >
                            <i class="bi bi-badge-wc"></i>
                            Туалет
                        </button>
                        <span class="hrm-editor-toolbar__spacer"></span>
                        <button type="button" class="is-save" :disabled="savingLayout || !layoutDirty" @click="saveLayout">
                            <i class="bi bi-check2-circle"></i>
                            {{ savingLayout ? 'Сохраняем…' : 'Сохранить схему' }}
                        </button>
                    </div>

                    <div class="hrm-designer" :class="{ 'has-inspector': editMode }">
                        <div class="hrm-designer__canvas">
                        <div
                            ref="mapWrap"
                            class="hrm-map-wrap"
                            :class="{ 'is-viewing': !editMode }"
                        >
                            <div
                                ref="map"
                                class="hrm-map"
                                :class="{
                                    'is-editing': editMode,
                                    'is-viewing': !editMode,
                                    'is-placing': editMode && Boolean(pendingPlacementType),
                                }"
                                :style="mapAspectStyle"
                                @pointerdown.self="startMarqueeSelection"
                                @click.self="handleCanvasClick"
                                @contextmenu.prevent.stop="openCanvasContextMenu"
                            >
                                <div class="hrm-map__grid"></div>

                                <div
                                    v-if="collisionTooltip.visible"
                                    class="hrm-column-tooltip"
                                    :style="collisionTooltipStyle"
                                >
                                    <i class="bi bi-exclamation-triangle-fill"></i>
                                    <span>{{ collisionTooltip.message }}</span>
                                </div>

                                <div
                                    v-if="selectionMarquee.active && selectionMarquee.moved"
                                    class="hrm-selection-marquee"
                                    :style="selectionMarqueeStyle"
                                ></div>

                                <div
                                    v-for="guide in alignmentGuides.vertical"
                                    :key="`guide-v-${guide}`"
                                    class="hrm-alignment-guide is-vertical"
                                    :style="{
                                        left: `${(guide / selectedRoom.canvas_width) * 100}%`
                                    }"
                                ></div>

                                <div
                                    v-for="guide in alignmentGuides.horizontal"
                                    :key="`guide-h-${guide}`"
                                    class="hrm-alignment-guide is-horizontal"
                                    :style="{
                                        top: `${(guide / selectedRoom.canvas_height) * 100}%`
                                    }"
                                ></div>
    
                                <div
                                    v-for="item in selectedRoom.items"
                                    :key="`item-${item._key || item.id}`"
                                    class="hrm-object hrm-furniture"
                                    :class="[
                                        `is-${item.type}`,
                                        {
                                            'is-selected': isSelected('item', item),
                                            'is-primary-selected': isPrimarySelected('item', item),
                                        }
                                    ]"
                                    :style="objectStyle(item)"
                                    @pointerdown.stop="startDrag($event, 'item', item)"
                                    @click.stop="handleObjectClick($event, 'item', item)"
                                    @contextmenu.prevent.stop="openObjectContextMenu($event, 'item', item)"
                                >
                                    <i class="bi" :class="furnitureIcon(item.type)"></i>
                                    <span>{{ item.label || furnitureLabel(item.type) }}</span>

                                    <template
                                        v-if="editMode && isPrimarySelected('item', item) && isRotatableElement('item', item)"
                                    >
                                        <button
                                            v-for="handle in objectRotationHandles"
                                            :key="`item-rotate-${handle.corner}`"
                                            type="button"
                                            class="hrm-object-rotation-handle"
                                            :class="`is-${handle.corner}`"
                                            :title="handle.title"
                                            :aria-label="handle.title"
                                            @pointerdown.stop.prevent="startObjectRotation($event, 'item', item, handle.corner)"
                                            @click.stop.prevent
                                        >
                                            <i class="bi bi-arrow-clockwise"></i>
                                        </button>
                                    </template>

                                    <template
                                        v-if="editMode && isPrimarySelected('item', item) && isCanvasResizable('item', item)"
                                    >
                                        <button
                                            v-for="handle in objectResizeHandlesFor('item', item)"
                                            :key="`item-resize-${handle.direction}`"
                                            type="button"
                                            class="hrm-object-resize-handle"
                                            :class="[
                                                `is-${handle.direction}`,
                                                { 'is-column-resize': item.type === 'column' }
                                            ]"
                                            :style="{ cursor: resizeCursor(handle.direction, item) }"
                                            :title="handle.title"
                                            :aria-label="handle.title"
                                            @pointerdown.stop.prevent="startObjectResize($event, 'item', item, handle.direction)"
                                            @click.stop.prevent
                                        ></button>
                                    </template>
                                </div>
    
                                <div
                                    v-for="bed in selectedRoom.beds"
                                    :key="`bed-${bed._key || bed.id}`"
                                    class="hrm-object hrm-bed"
                                    :class="{
                                        'is-free': !bed.occupied,
                                        'is-occupied': bed.occupied,
                                        'is-disabled': !bed.enabled,
                                        'is-selected': isSelected('bed', bed),
                                        'is-primary-selected': isPrimarySelected('bed', bed),
                                    }"
                                    :style="objectStyle(bed)"
                                    @pointerdown.stop="startDrag($event, 'bed', bed)"
                                    @click.stop="handleBedClick($event, bed)"
                                    @contextmenu.prevent.stop="openObjectContextMenu($event, 'bed', bed)"
                                >
                                    <span class="hrm-bed__pillow"></span>
                                    <span class="hrm-bed__number">Койка {{ bed.number }}</span>
                                    <span class="hrm-bed__state">
                                        {{ bed.occupied ? 'Занята' : 'Свободна' }}
                                    </span>

                                    <template
                                        v-if="editMode && isPrimarySelected('bed', bed) && isRotatableElement('bed', bed)"
                                    >
                                        <button
                                            v-for="handle in objectRotationHandles"
                                            :key="`bed-rotate-${handle.corner}`"
                                            type="button"
                                            class="hrm-object-rotation-handle"
                                            :class="`is-${handle.corner}`"
                                            :title="handle.title"
                                            :aria-label="handle.title"
                                            @pointerdown.stop.prevent="startObjectRotation($event, 'bed', bed, handle.corner)"
                                            @click.stop.prevent
                                        >
                                            <i class="bi bi-arrow-clockwise"></i>
                                        </button>
                                    </template>

                                    <template
                                        v-if="editMode && isPrimarySelected('bed', bed) && isCanvasResizable('bed', bed)"
                                    >
                                        <button
                                            v-for="handle in objectResizeHandles"
                                            :key="`bed-resize-${handle.direction}`"
                                            type="button"
                                            class="hrm-object-resize-handle"
                                            :class="`is-${handle.direction}`"
                                            :style="{ cursor: resizeCursor(handle.direction, bed) }"
                                            :title="handle.title"
                                            :aria-label="handle.title"
                                            @pointerdown.stop.prevent="startObjectResize($event, 'bed', bed, handle.direction)"
                                            @click.stop.prevent
                                        ></button>
                                    </template>
    
                                    <div v-if="bed.occupied && bed.patient" class="hrm-bed-tooltip">
                                        <strong>{{ bed.patient.name }}</strong>
                                        <span v-if="bed.patient.birth_at">Дата рождения: {{ bed.patient.birth_at }}</span>
                                        <span v-if="bed.assigned_at">Размещён: {{ bed.assigned_at }}</span>
                                        <em>Нажмите, чтобы открыть медицинскую карту</em>
                                    </div>
                                </div>
    
                                <div v-if="selectedRoom.beds.length === 0 && selectedRoom.items.length === 0" class="hrm-map-empty">
                                    <i class="bi bi-grid"></i>
                                    <strong>Схема палаты пуста</strong>
                                    <span>Включите редактор и добавьте койки, мебель, колонны, входы, окна и розетки.</span>
                                </div>

                                <button
                                    v-if="editMode"
                                    type="button"
                                    class="hrm-canvas-resizer"
                                    title="Изменить размер рабочей области"
                                    @pointerdown.stop.prevent="startCanvasResize"
                                >
                                    <i class="bi bi-arrows-angle-expand"></i>
                                </button>
                            </div>
                        </div>
    
                        <div ref="mapLegend" class="hrm-legend">
                            <span><i class="is-free"></i>Свободная койка</span>
                            <span><i class="is-occupied"></i>Занятая койка</span>
                            <span><i class="is-furniture"></i>Мебель</span>
                            <span><i class="is-column"></i>Колонна</span>
                            <span><i class="is-entrance"></i>Вход</span>
                            <span><i class="is-window"></i>Окно</span>
                            <span><i class="is-socket"></i>Розетка</span>
                            <span><i class="is-toilet"></i>Туалет</span>
                        </div>
    
                        </div>

                        <aside v-if="editMode" class="hrm-inspector hrm-inspector--docked">
                            <div class="hrm-inspector__head">
                                <div>
                                    <small>Form Inspector</small>
                                    <strong>Схема палаты</strong>
                                </div>
                            </div>

                            <div class="hrm-inspector__section-title">Canvas</div>

                            <div class="hrm-inspector__grid hrm-inspector__grid--two">
                                <label>
                                    <span>Ширина</span>
                                    <input
                                        :value="selectedRoom.canvas_width"
                                        class="hrm-control"
                                        type="number"
                                        min="500"
                                        max="1600"
                                        step="10"
                                        @change="setCanvasDimension('width', $event.target.value)"
                                    >
                                </label>
                                <label>
                                    <span>Высота</span>
                                    <input
                                        :value="selectedRoom.canvas_height"
                                        class="hrm-control"
                                        type="number"
                                        min="350"
                                        max="1200"
                                        step="10"
                                        @change="setCanvasDimension('height', $event.target.value)"
                                    >
                                </label>
                            </div>

                            <div class="hrm-canvas-size">
                                {{ selectedRoom.canvas_width }} × {{ selectedRoom.canvas_height }} px
                            </div>

                            <div class="hrm-canvas-stepper">
                                <button type="button" @click="resizeCanvas(-100, 0)">W −</button>
                                <button type="button" @click="resizeCanvas(100, 0)">W +</button>
                                <button type="button" @click="resizeCanvas(0, -100)">H −</button>
                                <button type="button" @click="resizeCanvas(0, 100)">H +</button>
                            </div>

                            <div class="hrm-canvas-presets">
                                <button type="button" @click="setCanvasPreset(800, 500)">800×500</button>
                                <button type="button" @click="setCanvasPreset(900, 560)">900×560</button>
                                <button type="button" @click="setCanvasPreset(1200, 700)">1200×700</button>
                            </div>

                            <template v-if="selectedElements.length <= 1">
                                <div class="hrm-inspector__separator"></div>

                                <template v-if="selectedElement">
                                <div class="hrm-inspector__head hrm-inspector__head--object">
                                    <div>
                                        <small>Object Inspector</small>
                                        <strong>{{ selectedElementTitle }}</strong>
                                    </div>
                                    <button type="button" class="hrm-icon-btn" @click="clearSelection">
                                        <i class="bi bi-x-lg"></i>
                                    </button>
                                </div>

                                <div class="hrm-inspector__section-title">Свойства</div>

                                <div
                                    v-if="selectedElement.object.type === 'entrance'"
                                    class="hrm-selection-summary"
                                >
                                    <strong>Вход:</strong> высота фиксирована, можно изменять только ширину.
                                </div>

                                <div class="hrm-inspector__grid">
                                    <label v-if="selectedElement.type === 'bed'">
                                        <span>Номер койки</span>
                                        <input v-model="selectedElement.object.number" class="hrm-control" type="text" @focus="pushUndoCheckpoint" @input="markDirty">
                                    </label>
                                    <label>
                                        <span>Подпись</span>
                                        <input v-model="selectedElement.object.label" class="hrm-control" type="text" @focus="pushUndoCheckpoint" @input="markDirty">
                                    </label>
                                    <label v-if="selectedElement.type === 'bed' || selectedElement.object.type !== 'bedside_table'">
                                        <span>Ширина</span>
                                        <input v-model.number="selectedElement.object.width" class="hrm-control" type="number" min="30" max="600" @focus="pushUndoCheckpoint" @input="markDirty">
                                    </label>
                                    <label v-if="selectedElement.type === 'bed' || (selectedElement.object.type !== 'bedside_table' && selectedElement.object.type !== 'entrance' && selectedElement.object.type !== 'window')">
                                        <span>Высота</span>
                                        <input v-model.number="selectedElement.object.height" class="hrm-control" type="number" min="30" max="600" @focus="pushUndoCheckpoint" @input="markDirty">
                                    </label>
                                    <label v-if="isRotatableElement(selectedElement.type, selectedElement.object)">
                                        <span>Поворот</span>
                                        <div class="hrm-angle-control">
                                            <input
                                                class="hrm-control"
                                                type="number"
                                                min="0"
                                                max="359.99"
                                                step="0.1"
                                                :value="normalizedRotation(selectedElement.object)"
                                                @change="setSelectedRotation($event.target.value)"
                                            >
                                            <span>°</span>
                                        </div>
                                    </label>
                                </div>

                                <div class="hrm-inspector__section-title">Действия</div>

                                <div
                                    v-if="isRotatableElement(selectedElement.type, selectedElement.object)"
                                    class="hrm-selection-summary"
                                >
                                    <strong>Ротация:</strong>
                                    тяните за любой из четырёх угловых значков на канвасе.
                                    Угол можно задавать с точностью до 0,1°.
                                </div>

                                <div class="hrm-inspector__actions">
                                    <button
                                        v-if="patientPlacementEnabled && selectedElement.type === 'bed' && selectedElement.object.occupied"
                                        type="button"
                                        class="hrm-btn"
                                        @click="releaseSelectedBed"
                                    >
                                        <i class="bi bi-box-arrow-left"></i>
                                        Освободить койку
                                    </button>
                                    <button type="button" class="hrm-btn hrm-btn--danger" @click="removeSelected">
                                        <i class="bi bi-trash3"></i>
                                        Удалить со схемы
                                    </button>
                                </div>
                                </template>

                                <div v-else class="hrm-inspector__empty">
                                    <i class="bi bi-cursor"></i>
                                    <strong>Object Inspector</strong>
                                    <span>Выберите объект на схеме. Размер рабочей области можно менять выше или тянуть за угол канваса.</span>
                                </div>
                            </template>
                        </aside>
                    </div>
                </template>

                <div v-else class="hrm-no-room">
                    <i class="bi bi-door-open"></i>
                    <strong>Выберите палату</strong>
                    <span>Или создайте новую палату для одного из отделений стационара.</span>
                </div>
            </section>
        </div>

        <div
            v-if="contextMenu.open && editMode"
            class="hrm-context-menu"
            :style="contextMenuStyle"
            @pointerdown.stop
            @contextmenu.prevent
        >
            <div v-if="selectedElements.length" class="hrm-context-menu__caption">
                {{ selectedElements.length === 1 ? selectedElementTitle : `Выбрано: ${selectedElements.length}` }}
            </div>

            <button
                v-if="selectedElements.length"
                type="button"
                @click="copyFromContextMenu"
            >
                <i class="bi bi-files"></i>
                <span>Копировать</span>
                <kbd>Ctrl+C</kbd>
            </button>

            <button
                v-if="selectedElements.length"
                type="button"
                :disabled="selectionHasOccupiedBed"
                @click="cutFromContextMenu"
            >
                <i class="bi bi-scissors"></i>
                <span>Вырезать</span>
                <kbd>Ctrl+X</kbd>
            </button>

            <button
                type="button"
                :disabled="!clipboardHasEntries"
                @click="pasteFromContextMenu"
            >
                <i class="bi bi-clipboard-plus"></i>
                <span>Вставить</span>
                <kbd>Ctrl+V</kbd>
            </button>

            <template v-if="contextMenu.onObject && canRotateSingleSelection">
                <div class="hrm-context-menu__separator"></div>

                <button
                    type="button"
                    @click="rotateSelectedByQuarterFromContextMenu"
                >
                    <i class="bi bi-arrow-clockwise"></i>
                    <span>Повернуть на +90°</span>
                    <kbd>Ctrl+A</kbd>
                </button>

                <button
                    type="button"
                    @click="snapSelectedRotationFromContextMenu"
                >
                    <i class="bi bi-bounding-box-circles"></i>
                    <span>Выровнять по 90°</span>
                    <kbd>Ctrl+Shift+A</kbd>
                </button>
            </template>

            <div v-if="selectedElements.length" class="hrm-context-menu__separator"></div>

            <button
                v-if="selectedElements.length"
                type="button"
                class="is-danger"
                :disabled="selectionHasOccupiedBed"
                @click="deleteFromContextMenu"
            >
                <i class="bi bi-trash3"></i>
                <span>Удалить</span>
                <kbd>Del</kbd>
            </button>
        </div>

        <div v-if="roomModal.open" class="hrm-modal-backdrop" @mousedown.self="closeRoomModal">
            <div class="hrm-modal">
                <header>
                    <div>
                        <small>Стационар</small>
                        <h3>{{ roomModal.id ? 'Редактирование палаты' : 'Новая палата' }}</h3>
                    </div>
                    <button type="button" class="hrm-icon-btn" @click="closeRoomModal"><i class="bi bi-x-lg"></i></button>
                </header>

                <div class="hrm-modal__body">
                    <div v-if="roomModal.error" class="hrm-alert hrm-alert--error">{{ roomModal.error }}</div>
                    <div class="hrm-form-grid">
                        <label class="is-full">
                            <span>Отделение *</span>
                            <select v-model="roomModal.department_id" class="hrm-control">
                                <option value="">Выберите отделение</option>
                                <option v-for="department in departments" :key="department.id" :value="String(department.id)">
                                    {{ departmentLabel(department) }}
                                </option>
                            </select>
                        </label>
                        <label>
                            <span>Номер палаты *</span>
                            <input v-model="roomModal.number" class="hrm-control" type="text" placeholder="Например: 101">
                        </label>
                        <label>
                            <span>Название</span>
                            <input v-model="roomModal.name" class="hrm-control" type="text" placeholder="Например: Наблюдательная">
                        </label>
                        <label>
                            <span>Этаж</span>
                            <input v-model="roomModal.floor" class="hrm-control" type="text" placeholder="Например: 2">
                        </label>
                        <label class="is-full" style="display:flex;align-items:center;gap:10px;">
                            <input v-model="roomModal.is_supervision" type="checkbox" style="width:18px;height:18px;">
                            <span>Надзорная палата (приоритет при переводе)</span>
                        </label>
                        <label>
                            <span>Порядок</span>
                            <input v-model.number="roomModal.sort_order" class="hrm-control" type="number" min="0">
                        </label>
                    </div>
                </div>

                <footer>
                    <button v-if="roomModal.id" type="button" class="hrm-btn hrm-btn--danger" @click="deleteRoom">
                        <i class="bi bi-trash3"></i>
                        Удалить палату
                    </button>
                    <span class="hrm-modal__spacer"></span>
                    <button type="button" class="hrm-btn" @click="closeRoomModal">Отмена</button>
                    <button type="button" class="hrm-btn hrm-btn--primary" :disabled="roomModal.saving" @click="saveRoom">
                        <i class="bi bi-check2"></i>
                        {{ roomModal.saving ? 'Сохраняем…' : 'Сохранить' }}
                    </button>
                </footer>
            </div>
        </div>

        <div v-if="patientPlacementEnabled && assignModal.open" class="hrm-modal-backdrop" @mousedown.self="closeAssignModal">
            <div class="hrm-modal hrm-modal--small hrm-assign-modal">
                <header class="hrm-assign-modal__header">
                    <div class="hrm-assign-modal__heading">
                        <span class="hrm-assign-modal__icon">
                            <i class="bi bi-person-plus"></i>
                        </span>
                        <div>
                            <small>Размещение пациента</small>
                            <h3>Назначить пациента на койку</h3>
                        </div>
                    </div>

                    <div class="hrm-assign-modal__head-actions">
                        <span class="hrm-assign-modal__bed-badge">
                            <i class="bi bi-hospital"></i>
                            Койка {{ assignModal.bed?.number }}
                        </span>
                        <button type="button" class="hrm-icon-btn" @click="closeAssignModal" aria-label="Закрыть">
                            <i class="bi bi-x-lg"></i>
                        </button>
                    </div>
                </header>

                <div class="hrm-modal__body hrm-assign-modal__body">
                    <div v-if="assignModal.error" class="hrm-alert hrm-alert--error">{{ assignModal.error }}</div>

                    <section class="hrm-assign-card">
                        <div class="hrm-assign-card__head">
                            <div>
                                <strong>Выберите пациента</strong>
                                <span>Начните вводить фамилию, имя или отчество. Пациенты, уже размещённые на койках, не показываются.</span>
                            </div>
                        </div>

                        <label class="hrm-assign-field">
                            <span class="hrm-label">Пациент</span>
                            <div class="hrm-patient-combobox" :class="{ 'has-value': !!assignModal.patient }">
                                <i class="bi bi-search hrm-patient-combobox__search"></i>
                                <input
                                    ref="assignPatientSearchInput"
                                    v-model="assignModal.query"
                                    type="search"
                                    class="hrm-control hrm-patient-combobox__input"
                                    placeholder="Введите ФИО пациента"
                                    autocomplete="off"
                                    @input="schedulePatientSearch"
                                    @keydown.esc.prevent="assignModal.results = []"
                                >
                                <button
                                    v-if="assignModal.query"
                                    type="button"
                                    class="hrm-patient-combobox__clear"
                                    title="Очистить"
                                    @click="clearAssignPatientSearch"
                                >
                                    <i class="bi bi-x"></i>
                                </button>

                                <div
                                    v-if="assignModal.query.trim().length >= 2 && (assignModal.searching || assignModal.results.length || !assignModal.patient)"
                                    class="hrm-patient-dropdown"
                                >
                                    <div v-if="assignModal.searching" class="hrm-patient-dropdown__state">
                                        <span class="spinner-border spinner-border-sm" aria-hidden="true"></span>
                                        Ищем пациента…
                                    </div>

                                    <template v-else-if="assignModal.results.length">
                                        <button
                                            v-for="patient in assignModal.results"
                                            :key="patient.id"
                                            type="button"
                                            class="hrm-patient-row"
                                            :class="{ 'is-selected': assignModal.patient?.id === patient.id }"
                                            @mousedown.prevent
                                            @click="selectAssignPatient(patient)"
                                        >
                                            <span class="hrm-patient-row__avatar">
                                                <i class="bi bi-person"></i>
                                            </span>
                                            <span class="hrm-patient-row__copy">
                                                <strong>{{ patient.name }}</strong>
                                                <small v-if="patient.birth_at">Дата рождения: {{ patient.birth_at }}</small>
                                            </span>
                                            <i class="bi bi-chevron-right hrm-patient-row__arrow"></i>
                                        </button>
                                    </template>

                                    <div v-else-if="!assignModal.patient" class="hrm-patient-dropdown__empty">
                                        <i class="bi bi-person-x"></i>
                                        <strong>Пациенты не найдены</strong>
                                        <span>Проверьте написание или введите другую часть ФИО.</span>
                                    </div>
                                </div>
                            </div>
                        </label>

                        <div v-if="assignModal.patient" class="hrm-selected-patient">
                            <span class="hrm-selected-patient__avatar">
                                <i class="bi bi-person-check"></i>
                            </span>
                            <span class="hrm-selected-patient__copy">
                                <small>Выбран пациент</small>
                                <strong>{{ assignModal.patient.name }}</strong>
                                <span v-if="assignModal.patient.birth_at">Дата рождения: {{ assignModal.patient.birth_at }}</span>
                            </span>
                            <span class="hrm-selected-patient__check"><i class="bi bi-check2"></i></span>
                        </div>
                    </section>
                </div>

                <footer class="hrm-assign-modal__footer">
                    <div class="hrm-assign-modal__footer-note">
                        <i class="bi bi-info-circle"></i>
                        Пациент будет размещён на койке {{ assignModal.bed?.number }}.
                    </div>
                    <span class="hrm-modal__spacer"></span>
                    <button type="button" class="hrm-btn" @click="closeAssignModal">Отмена</button>
                    <button type="button" class="hrm-btn hrm-btn--primary" :disabled="!assignModal.patient || assignModal.saving" @click="assignPatient">
                        <i class="bi bi-check2-circle"></i>
                        {{ assignModal.saving ? 'Размещаем…' : 'Разместить' }}
                    </button>
                </footer>
            </div>
        </div>
    </div>
</template>

<script>
import { defineComponent } from 'vue';

function clone(value) {
    return JSON.parse(JSON.stringify(value));
}

// Ограничения относятся только к визуальному размеру на странице просмотра.
const VIEW_CANVAS_SCALE_MIN = 65;
const VIEW_CANVAS_SCALE_MAX = 135;
const VIEW_CANVAS_STORAGE_PREFIX = 'hrm-view-canvas-v1:';

function clampViewCanvasScale(value) {
    const number = Number(value);
    if (!Number.isFinite(number)) return 100;
    return Math.max(
        VIEW_CANVAS_SCALE_MIN,
        Math.min(VIEW_CANVAS_SCALE_MAX, Math.round(number / 5) * 5)
    );
}

const OBJECT_RESIZE_HANDLES = [
    { direction: 'n', title: 'Изменить размер сверху' },
    { direction: 'ne', title: 'Изменить размер из правого верхнего угла' },
    { direction: 'e', title: 'Изменить размер справа' },
    { direction: 'se', title: 'Изменить размер из правого нижнего угла' },
    { direction: 's', title: 'Изменить размер снизу' },
    { direction: 'sw', title: 'Изменить размер из левого нижнего угла' },
    { direction: 'w', title: 'Изменить размер слева' },
    { direction: 'nw', title: 'Изменить размер из левого верхнего угла' },
];

const OBJECT_ROTATION_HANDLES = [
    { corner: 'nw', title: 'Повернуть элемент' },
    { corner: 'ne', title: 'Повернуть элемент' },
    { corner: 'se', title: 'Повернуть элемент' },
    { corner: 'sw', title: 'Повернуть элемент' },
];

export default defineComponent({
    name: 'HospitalRoomsMap',

    props: {
        bootstrapUrl: { type: String, required: true },
        roomStoreUrl: { type: String, required: true },
        roomUpdateUrlTemplate: { type: String, required: true },
        roomDeleteUrlTemplate: { type: String, required: true },
        roomLayoutUrlTemplate: { type: String, required: true },
        bedAssignUrlTemplate: { type: String, required: true },
        bedReleaseUrlTemplate: { type: String, required: true },
        patientSearchUrl: { type: String, required: true },
        patientCardUrlTemplate: { type: String, required: true },
        clinicName: { type: String, default: '' },
        patientPlacementEnabled: { type: Boolean, default: false },
    },

    data() {
        return {
            loading: true,
            loadingProgress: 0,
            loadingText: 'Подготавливаем карту палат…',
            loadingProgressTimer: null,
            error: '',
            notice: '',
            departments: [],
            rooms: [],
            stats: { rooms: 0, beds: 0, occupied: 0 },
            selectedDepartmentId: '',
            selectedRoomId: '',
            roomPagination: {
                currentPage: 1,
                lastPage: 1,
                perPage: 8,
                total: 0,
                from: 0,
                to: 0,
            },
            editMode: false,
            // Независимый визуальный размер для каждой палаты (%, только CSS).
            // Не включаем эти значения в saveLayout() и не отправляем на сервер.
            viewCanvasSizes: {},
            // Размер отображения; логические координаты схемы не меняются.
            mapFittedWidth: 0,
            mapFitRoomKey: '',
            mapWrapResizeObserver: null,
            mapObservedWrap: null,
            mapObservedWidth: 0,
            mapFitFrame: null,
            pendingPlacementType: '',
            layoutDirty: false,
            savingLayout: false,
            selectedElement: null,
            selectedElements: [],
            clipboard: {
                entries: [],
                mode: 'copy',
                pasteSerial: 0,
            },
            undoStack: [],
            undoLimit: 80,
            historyRestoring: false,
            contextMenu: {
                open: false,
                x: 0,
                y: 0,
                canvasPoint: null,
                onObject: false,
            },
            selectionMarquee: {
                active: false,
                moved: false,
                additive: false,
                startX: 0,
                startY: 0,
                endX: 0,
                endY: 0,
                baseSelection: [],
            },
            suppressCanvasClick: false,
            dragState: null,
            objectResizeState: null,
            objectRotationState: null,
            alignmentGuides: {
                vertical: [],
                horizontal: [],
            },
            collisionTooltip: {
                visible: false,
                message: '',
                x: 0,
                y: 0,
                timer: null,
                lastShownAt: 0,
            },
            canvasResizeState: null,
            tempCounter: 1,
            noticeTimer: null,
            roomModal: {
                open: false,
                id: null,
                department_id: '',
                number: '',
                name: '',
                floor: '',
                sort_order: 0,
                is_supervision: false,
                saving: false,
                error: '',
            },
            assignModal: {
                open: false,
                bed: null,
                query: '',
                results: [],
                patient: null,
                searching: false,
                saving: false,
                error: '',
                timer: null,
                controller: null,
            },
        };
    },

    computed: {
        viewCanvasScaleMin() {
            return VIEW_CANVAS_SCALE_MIN;
        },

        viewCanvasScaleMax() {
            return VIEW_CANVAS_SCALE_MAX;
        },

        viewCanvasSize() {
            const key = String(this.selectedRoom?.id ?? '');
            const saved = this.viewCanvasSizes[key] || {};
            return {
                width: clampViewCanvasScale(saved.width ?? 100),
                height: clampViewCanvasScale(saved.height ?? 100),
            };
        },

        viewMapDimensions() {
            const room = this.selectedRoom;
            if (!room) return null;

            const logicalWidth = Math.max(500, Number(room.canvas_width || 900));
            const logicalHeight = Math.max(350, Number(room.canvas_height || 560));
            const roomKey = `${room.id}:${logicalWidth}:${logicalHeight}`;
            const fittedWidth = this.mapFitRoomKey === roomKey
                && Number(this.mapFittedWidth || 0) > 0
                ? Math.min(logicalWidth, Number(this.mapFittedWidth))
                : logicalWidth;

            const baseScale = fittedWidth / logicalWidth;
            const widthRatio = this.viewCanvasSize.width / 100;
            const heightRatio = this.viewCanvasSize.height / 100;
            // Изменяем положение элементов по осям отдельно, а их геометрию
            // одним коэффициентом. Кровати не вытягиваются по ширине/высоте.
            const objectScale = baseScale * Math.min(widthRatio, heightRatio);

            return {
                width: Math.max(1, Math.round(fittedWidth * widthRatio)),
                height: Math.max(1, Math.round(logicalHeight * baseScale * heightRatio)),
                objectScale,
                textScale: Math.min(widthRatio, heightRatio),
            };
        },

        objectResizeHandles() {
            return OBJECT_RESIZE_HANDLES;
        },

        objectRotationHandles() {
            return OBJECT_ROTATION_HANDLES;
        },

        filteredRooms() {
            /*
             * Список уже отфильтрован и разбит на страницы сервером.
             * Здесь оставляем прежнее имя computed, чтобы не трогать шаблон
             * и остальную логику выбора палаты.
             */
            return this.rooms;
        },

        selectedRoom() {
            return this.rooms.find(room => String(room.id) === String(this.selectedRoomId)) || null;
        },

        freeBedsTotal() {
            return Math.max(0, Number(this.stats.beds || 0) - Number(this.stats.occupied || 0));
        },

        mapFitSignature() {
            const room = this.selectedRoom;
            if (!room) return '';

            return [
                room.id,
                Number(room.canvas_width || 900),
                Number(room.canvas_height || 560),
                this.editMode ? 'edit' : 'view',
            ].join(':');
        },

        mapAspectStyle() {
            const room = this.selectedRoom;
            if (!room) return {};

            const width = Math.max(500, Number(room.canvas_width || 900));
            const height = Math.max(350, Number(room.canvas_height || 560));

            if (this.editMode) {
                // В редакторе все размеры и координаты остаются исходными.
                return {
                    width: `${width}px`,
                    maxWidth: '100%',
                    height: 'auto',
                    marginInline: 'auto',
                    aspectRatio: `${width} / ${height}`,
                };
            }

            const view = this.viewMapDimensions;
            if (!view) return {};

            return {
                width: `${view.width}px`,
                height: `${view.height}px`,
                minWidth: '0',
                maxWidth: 'none',
                flexShrink: 0,
                marginInline: 'auto',
                aspectRatio: 'auto',
                '--hrm-view-bed-font': `${Math.min(12, Math.max(8, 9 * view.textScale)).toFixed(1)}px`,
                '--hrm-view-state-font': `${Math.min(10, Math.max(7, 7 * view.textScale)).toFixed(1)}px`,
                '--hrm-view-furniture-font': `${Math.min(11, Math.max(8, 8 * view.textScale)).toFixed(1)}px`,
                '--hrm-view-grid-size': `${Math.max(12, 24 * view.objectScale).toFixed(1)}px`,
            };
        },

        selectionMarqueeStyle() {
            const room = this.selectedRoom;
            const box = this.selectionMarquee;

            if (!room || !box?.active) {
                return {};
            }

            const left = Math.min(box.startX, box.endX);
            const top = Math.min(box.startY, box.endY);
            const right = Math.max(box.startX, box.endX);
            const bottom = Math.max(box.startY, box.endY);

            return {
                left: `${(left / Number(room.canvas_width || 1)) * 100}%`,
                top: `${(top / Number(room.canvas_height || 1)) * 100}%`,
                width: `${((right - left) / Number(room.canvas_width || 1)) * 100}%`,
                height: `${((bottom - top) / Number(room.canvas_height || 1)) * 100}%`,
            };
        },

        collisionTooltipStyle() {
            const room = this.selectedRoom;
            const tooltip = this.collisionTooltip;

            if (!room || !tooltip?.visible) {
                return {};
            }

            return {
                left: `${(Number(tooltip.x || 0) / Number(room.canvas_width || 1)) * 100}%`,
                top: `${(Number(tooltip.y || 0) / Number(room.canvas_height || 1)) * 100}%`,
            };
        },

        clipboardHasEntries() {
            return Array.isArray(this.clipboard?.entries)
                && this.clipboard.entries.length > 0;
        },

        selectionHasOccupiedBed() {
            return this.selectedElements.some(entry => (
                entry.type === 'bed'
                && Boolean(entry.object?.occupied)
            ));
        },

        canRotateSingleSelection() {
            if (this.selectedElements.length !== 1) {
                return false;
            }

            const entry = this.selectedElements[0];
            return this.isRotatableElement(entry.type, entry.object);
        },

        contextMenuStyle() {
            return {
                left: `${Number(this.contextMenu?.x || 0)}px`,
                top: `${Number(this.contextMenu?.y || 0)}px`,
            };
        },

        selectedElementTitle() {
            if (!this.selectedElement) return '';
            const object = this.selectedElement.object;
            if (this.selectedElement.type === 'bed') {
                return `Койка ${object.number || 'без номера'}`;
            }
            return object.label || this.furnitureLabel(object.type);
        },
    },

    watch: {
        // После получения данных, переключения палаты/режима или
        // редактирования размеров канваса пересчитываем доступное место.
        loading(value) {
            if (!value) this.$nextTick(() => this.queueMapFit());
        },
        mapFitSignature() {
            this.$nextTick(() => this.queueMapFit());
        },
    },

    mounted() {
        this.restoreViewCanvasSizes();
        this.load();
        document.addEventListener('keydown', this.onCanvasKeydown, true);
        window.addEventListener('pointerdown', this.onGlobalPointerDown);
        window.addEventListener('resize', this.closeContextMenu);
        window.addEventListener('resize', this.queueMapFit);
        window.visualViewport?.addEventListener('resize', this.queueMapFit);
        window.addEventListener('blur', this.closeContextMenu);
    },

    beforeUnmount() {
        document.removeEventListener('keydown', this.onCanvasKeydown, true);
        window.removeEventListener('pointerdown', this.onGlobalPointerDown);
        window.removeEventListener('resize', this.closeContextMenu);
        window.removeEventListener('resize', this.queueMapFit);
        window.visualViewport?.removeEventListener('resize', this.queueMapFit);
        this.mapWrapResizeObserver?.disconnect();
        if (this.mapFitFrame !== null) {
            window.cancelAnimationFrame(this.mapFitFrame);
        }
        window.removeEventListener('blur', this.closeContextMenu);
        this.stopDrag();
        this.stopObjectResize();
        this.stopObjectRotation();
        this.stopCanvasResize();
        this.stopMarqueeSelection(false);
        window.clearTimeout(this.assignModal.timer);
        this.assignModal.controller?.abort();
        window.clearTimeout(this.noticeTimer);
        window.clearTimeout(this.collisionTooltip.timer);
        window.clearInterval(this.loadingProgressTimer);
    },

    methods: {
        viewCanvasStorageKey() {
            // Разные приложения/клиники на одном домене не смешивают размеры.
            const endpoint = String(this.bootstrapUrl || '').split('?')[0];
            return `${VIEW_CANVAS_STORAGE_PREFIX}${endpoint}`;
        },

        restoreViewCanvasSizes() {
            try {
                const raw = window.localStorage.getItem(this.viewCanvasStorageKey());
                const parsed = raw ? JSON.parse(raw) : null;
                if (!parsed || typeof parsed !== 'object' || Array.isArray(parsed)) {
                    return;
                }
                const cleaned = {};
                Object.entries(parsed).slice(0, 250).forEach(([id, size]) => {
                    if (!size || typeof size !== 'object') return;
                    cleaned[id] = {
                        width: clampViewCanvasScale(size.width),
                        height: clampViewCanvasScale(size.height),
                    };
                });
                this.viewCanvasSizes = cleaned;
            } catch (_error) {
                // localStorage может быть отключён: ползунки всё равно работают.
            }
        },

        persistViewCanvasSizes() {
            try {
                window.localStorage.setItem(
                    this.viewCanvasStorageKey(),
                    JSON.stringify(this.viewCanvasSizes)
                );
            } catch (_error) {
                // Ограничение хранилища не должно мешать выбору пациента.
            }
        },

        setViewCanvasScale(axis, value) {
            const room = this.selectedRoom;
            if (!room || this.editMode || !['width', 'height'].includes(axis)) {
                return;
            }

            const id = String(room.id);
            this.viewCanvasSizes = {
                ...this.viewCanvasSizes,
                [id]: {
                    ...this.viewCanvasSize,
                    [axis]: clampViewCanvasScale(value),
                },
            };
            this.persistViewCanvasSizes();
        },

        resetViewCanvasScale() {
            const room = this.selectedRoom;
            if (!room || this.editMode) return;
            const id = String(room.id);
            const updated = { ...this.viewCanvasSizes };
            delete updated[id];
            this.viewCanvasSizes = updated;
            this.persistViewCanvasSizes();
        },

        queueMapFit() {
            if (this.mapFitFrame !== null) {
                window.cancelAnimationFrame(this.mapFitFrame);
            }
            this.mapFitFrame = window.requestAnimationFrame(() => {
                this.mapFitFrame = null;
                this.updateMapFit();
            });
        },

        observeMapWrap(wrap) {
            if (this.mapObservedWrap === wrap) return;

            this.mapWrapResizeObserver?.disconnect();
            this.mapObservedWrap = wrap;
            this.mapObservedWidth = 0;

            if (typeof ResizeObserver !== 'function') return;

            const observer = new ResizeObserver(entries => {
                const width = entries[0]?.contentRect?.width || 0;
                // Изменение ВЫСОТЫ обёртки после fit не запускает
                // повторный расчёт: иначе возможен ResizeObserver loop.
                if (Math.abs(width - this.mapObservedWidth) <= 0.5) return;
                this.mapObservedWidth = width;
                this.queueMapFit();
            });
            this.mapWrapResizeObserver = observer;
            observer.observe(wrap);
        },

        updateMapFit() {
            const wrap = this.$refs.mapWrap;
            const room = this.selectedRoom;
            if (!wrap || !room) return;

            this.observeMapWrap(wrap);
            if (this.editMode) return;

            const width = Math.max(500, Number(room.canvas_width || 900));
            const height = Math.max(350, Number(room.canvas_height || 560));
            const styles = window.getComputedStyle(wrap);
            const horizontalPadding = (parseFloat(styles.paddingLeft) || 0)
                + (parseFloat(styles.paddingRight) || 0);
            const verticalPadding = (parseFloat(styles.paddingTop) || 0)
                + (parseFloat(styles.paddingBottom) || 0);
            const availableWidth = Math.max(1, wrap.clientWidth - horizontalPadding);

            // Под шапкой палаты остаётся доступная часть видимого окна.
            // Отдельно резервируем место для легенды под схемой.
            const legend = this.$refs.mapLegend;
            const legendHeight = legend?.offsetHeight || 0;
            const wrapTop = wrap.getBoundingClientRect().top;
            const viewportHeight = window.visualViewport?.height || window.innerHeight;
            const freeHeight = viewportHeight - Math.max(0, wrapTop)
                - verticalPadding - legendHeight - 28;
            // На низких экранах оставляем минимальную рабочую площадь,
            // вместо превращения схемы в нечитаемую миниатюру.
            const availableHeight = Math.max(260, freeHeight);

            const scale = Math.min(
                1,
                availableWidth / width,
                availableHeight / height
            );
            const fittedWidth = Math.max(1, Math.floor(width * scale));
            const roomKey = `${room.id}:${width}:${height}`;

            if (this.mapFittedWidth !== fittedWidth || this.mapFitRoomKey !== roomKey) {
                this.mapFittedWidth = fittedWidth;
                this.mapFitRoomKey = roomKey;
            }
        },

        csrfToken() {
            return document.querySelector('meta[name="csrf-token"]')?.content || '';
        },

        replaceToken(template, token, value) {
            return String(template || '').replace(token, String(value));
        },

        bootstrapDataUrl() {
            const url = new URL(this.bootstrapUrl, window.location.origin);

            if (this.selectedDepartmentId) {
                url.searchParams.set(
                    'department_id',
                    String(this.selectedDepartmentId)
                );
            } else {
                url.searchParams.delete('department_id');
            }

            url.searchParams.set(
                'page',
                String(this.roomPagination.currentPage || 1)
            );
            url.searchParams.set(
                'per_page',
                String(this.roomPagination.perPage || 8)
            );

            return url.toString();
        },

        async changeDepartment(event) {
            const nextDepartmentId = String(event?.target?.value || '');

            if (nextDepartmentId === String(this.selectedDepartmentId || '')) {
                return;
            }

            if (
                this.layoutDirty
                && !window.confirm(
                    'Есть несохранённые изменения схемы. Сменить отделение?'
                )
            ) {
                if (event?.target) {
                    event.target.value = String(this.selectedDepartmentId || '');
                }
                return;
            }

            this.stopObjectResize();
            this.stopObjectRotation();
            this.selectedDepartmentId = nextDepartmentId;
            this.selectedRoomId = '';
            this.roomPagination.currentPage = 1;
            this.editMode = false;
            this.pendingPlacementType = '';
            this.layoutDirty = false;
            this.clearSelection();
            this.clearUndoHistory();

            await this.load();
        },

        async changeRoomPage(page) {
            const targetPage = Math.min(
                Math.max(1, Number(page || 1)),
                Math.max(1, Number(this.roomPagination.lastPage || 1))
            );

            if (targetPage === Number(this.roomPagination.currentPage || 1)) {
                return;
            }

            if (
                this.layoutDirty
                && !window.confirm(
                    'Есть несохранённые изменения схемы. Перейти на другую страницу?'
                )
            ) {
                return;
            }

            this.stopObjectResize();
            this.stopObjectRotation();
            this.roomPagination.currentPage = targetPage;
            this.selectedRoomId = '';
            this.editMode = false;
            this.pendingPlacementType = '';
            this.layoutDirty = false;
            this.clearSelection();
            this.clearUndoHistory();

            await this.load();
        },

        async request(url, options = {}) {
            const headers = {
                Accept: 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
                ...(options.headers || {}),
            };

            if (options.body && !(options.body instanceof FormData)) {
                headers['Content-Type'] = 'application/json';
            }

            const csrf = this.csrfToken();
            if (csrf) headers['X-CSRF-TOKEN'] = csrf;

            const response = await fetch(url, {
                credentials: 'same-origin',
                ...options,
                headers,
            });

            const payload = await response.json().catch(() => ({}));

            if (!response.ok) {
                const firstError = payload?.errors
                    ? Object.values(payload.errors).flat()[0]
                    : null;
                throw new Error(firstError || payload?.message || `HTTP ${response.status}`);
            }

            return payload;
        },

        startLoadingProgress() {
            window.clearInterval(this.loadingProgressTimer);
            this.loadingProgressTimer = null;
            this.loadingProgress = 10;
            this.loadingText = 'Подготавливаем карту палат…';
        },

        setLoadingProgress(value, text = '') {
            this.loadingProgress = Math.max(
                this.loadingProgress,
                Math.min(100, Math.round(Number(value) || 0))
            );

            if (text) {
                this.loadingText = text;
            }
        },

        async requestWithProgress(url) {
            const controller = new AbortController();
            const timeout = window.setTimeout(() => {
                controller.abort();
            }, 20000);

            try {
                this.setLoadingProgress(22, 'Получаем данные стационара…');

                const payload = await this.request(url, {
                    method: 'GET',
                    signal: controller.signal,
                });

                this.setLoadingProgress(88, 'Формируем карту палат…');
                return payload;
            } catch (error) {
                if (error?.name === 'AbortError') {
                    throw new Error(
                        'Сервер слишком долго загружает карту палат. Повторите попытку.'
                    );
                }

                throw error;
            } finally {
                window.clearTimeout(timeout);
            }
        },

        async load(preferredRoomId = null) {
            this.loading = true;
            this.error = '';
            this.startLoadingProgress();

            try {
                const payload = await this.requestWithProgress(
                    this.bootstrapDataUrl()
                );

                this.setLoadingProgress(92, 'Обрабатываем палаты…');

                this.departments = payload.departments || [];
                this.rooms = clone(payload.rooms || []).map(room => (
                    this.normalizeLoadedRoomBedCoordinates(room)
                ));
                this.stats = payload.stats || { rooms: 0, beds: 0, occupied: 0 };

                const pagination = payload.pagination || {};
                this.roomPagination = {
                    currentPage: Number(pagination.current_page || 1),
                    lastPage: Math.max(1, Number(pagination.last_page || 1)),
                    perPage: Number(
                        pagination.per_page
                        || this.roomPagination.perPage
                        || 8
                    ),
                    total: Number(pagination.total || this.rooms.length || 0),
                    from: Number(pagination.from || (this.rooms.length ? 1 : 0)),
                    to: Number(pagination.to || this.rooms.length || 0),
                };

                this.setLoadingProgress(96, 'Подготавливаем схему…');

                const target = preferredRoomId || this.selectedRoomId;
                if (target && this.rooms.some(room => String(room.id) === String(target))) {
                    this.selectedRoomId = String(target);
                } else {
                    this.ensureSelectedRoom();
                }

                /*
                 * Для палаты старого формата сразу разрешаем Save Layout:
                 * одно сохранение мигрирует все койки на визуальные x/y >= 0.
                 */
                this.layoutDirty = Boolean(
                    this.selectedRoom?._legacyBedCoordinates
                );
                this.clearSelection();
                this.clearUndoHistory();

                this.setLoadingProgress(100, 'Готово');

                await new Promise(resolve => {
                    window.setTimeout(resolve, 140);
                });
            } catch (error) {
                this.error = error.message || 'Не удалось загрузить палаты.';
            } finally {
                window.clearInterval(this.loadingProgressTimer);
                this.loadingProgressTimer = null;
                this.loading = false;
            }
        },

        departmentLabel(department) {
            const number = department.number ? `№${department.number} · ` : '';
            return `${number}${department.name}`;
        },

        ensureSelectedRoom() {
            const rooms = this.filteredRooms;
            if (!rooms.some(room => String(room.id) === String(this.selectedRoomId))) {
                this.selectedRoomId = rooms.length ? String(rooms[0].id) : '';
            }
        },

        selectRoom(id) {
            if (this.layoutDirty && !window.confirm('Есть несохранённые изменения схемы. Перейти в другую палату?')) {
                return;
            }
            this.stopObjectResize();
            this.stopObjectRotation();
            this.selectedRoomId = String(id);
            this.editMode = false;
            this.pendingPlacementType = '';
            this.layoutDirty = false;
            this.clearSelection();
            this.clearUndoHistory();
        },

        toggleEditMode() {
            this.stopObjectResize();
            this.stopObjectRotation();
            if (this.editMode && this.layoutDirty) {
                if (!window.confirm('Есть несохранённые изменения схемы. Выйти из редактора без сохранения?')) {
                    return;
                }
                this.load(this.selectedRoomId);
            }
            this.editMode = !this.editMode;
            this.pendingPlacementType = '';
            this.clearSelection();
        },

        furnitureLabel(type) {
            if (type === 'cabinet') return 'Шкаф';
            if (type === 'column') return 'Колонна';
            if (type === 'entrance') return 'Вход';
            if (type === 'window') return 'Окно';
            if (type === 'socket') return 'Розетка';
            if (type === 'toilet') return 'Туалет';
            return 'Тумбочка';
        },

        furnitureIcon(type) {
            if (type === 'cabinet') return 'bi-archive';
            if (type === 'column') return 'bi-square-fill';
            if (type === 'entrance') return 'bi-door-open';
            if (type === 'window') return 'bi-window';
            if (type === 'socket') return 'bi-plug';
            if (type === 'toilet') return 'bi-badge-wc';
            return 'bi-square';
        },

        isBoundaryItem(object) {
            return ['entrance', 'window', 'socket'].includes(
                String(object?.type || '')
            );
        },

        normalizedRotation(object) {
            const raw = Number(object?.rotation || 0);
            return ((raw % 360) + 360) % 360;
        },

        bedVisualSize(bed) {
            const width = Math.max(1, Number(bed?.width || 150));
            const height = Math.max(1, Number(bed?.height || 74));
            const angle = this.normalizedRotation(bed) * Math.PI / 180;
            const cos = Math.abs(Math.cos(angle));
            const sin = Math.abs(Math.sin(angle));

            return {
                width,
                height,
                visualWidth: width * cos + height * sin,
                visualHeight: width * sin + height * cos,
            };
        },

        bedStoragePosition(bed, room = null) {
            /*
             * В DOM x/y койки — координаты НЕповёрнутого прямоугольника.
             * При 90° такой прямоугольник должен уходить, например, на -38px,
             * чтобы видимая вертикальная койка реально касалась левой стены.
             *
             * В БД отрицательные координаты нам не нужны. Поэтому сохраняем
             * x/y не исходного DOM-box, а левого верхнего угла ВИДИМОГО AABB
             * после поворота. Он всегда находится внутри canvas и начинается
             * с 0 у стены. При загрузке выполняется обратное преобразование.
             */
            const geometry = this.objectVisualGeometry(bed);
            const currentRoom = room || this.selectedRoom;
            const canvasWidth = Math.max(1, Number(currentRoom?.canvas_width || 1));
            const canvasHeight = Math.max(1, Number(currentRoom?.canvas_height || 1));
            const maxX = Math.max(0, canvasWidth - Number(geometry.visualWidth || 0));
            const maxY = Math.max(0, canvasHeight - Number(geometry.visualHeight || 0));

            return {
                x: Math.min(maxX, Math.max(0, Number(geometry.left || 0))),
                y: Math.min(maxY, Math.max(0, Number(geometry.top || 0))),
            };
        },

        bedRuntimePositionFromStorage(bed) {
            const rawX = Number(bed?.x || 0);
            const rawY = Number(bed?.y || 0);
            const size = this.bedVisualSize(bed);

            /*
             * Обратное преобразование: координата из БД описывает видимый
             * bounding-box, а CSS rotate() вращает исходный width×height box
             * вокруг центра. Возвращаем внутренние DOM x/y так, чтобы видимая
             * граница осталась точно в сохранённой точке.
             */
            return {
                x: rawX + (size.visualWidth - size.width) / 2,
                y: rawY + (size.visualHeight - size.height) / 2,
            };
        },

        normalizeLoadedRoomBedCoordinates(room) {
            if (!room || !Array.isArray(room.beds)) {
                return room;
            }

            /*
             * Совместимость с уже сохранённой старой схемой. Раньше Vue
             * писал в БД внутренние DOM-координаты. У повернутой койки у стены
             * они могут быть отрицательными. Если в палате встретили хотя бы
             * одну такую координату, считаем ВСЮ палату старым форматом и не
             * выполняем обратное преобразование по отдельным койкам.
             * После следующего Save Layout вся палата будет записана уже в
             * новом формате с неотрицательными визуальными координатами.
             */
            const legacyDomCoordinates = room.beds.some(bed => (
                Number(bed?.x || 0) < 0
                || Number(bed?.y || 0) < 0
            ));

            room._legacyBedCoordinates = legacyDomCoordinates;

            if (legacyDomCoordinates) {
                return room;
            }

            room.beds = room.beds.map(sourceBed => {
                const bed = { ...sourceBed };
                const position = this.bedRuntimePositionFromStorage(bed);

                bed.x = Math.round(position.x * 100) / 100;
                bed.y = Math.round(position.y * 100) / 100;

                return bed;
            });

            return room;
        },

        objectVisualGeometry(object, x = null, y = null) {
            const objectWidth = Math.max(1, Number(object?.width || 1));
            const objectHeight = Math.max(1, Number(object?.height || 1));
            const rotation = this.normalizedRotation(object);
            const angle = rotation * Math.PI / 180;
            const cos = Math.abs(Math.cos(angle));
            const sin = Math.abs(Math.sin(angle));

            /*
             * CSS rotate() вращает прямоугольник вокруг центра. Для
             * произвольного угла нам нужен реальный AABB — габарит
             * повернутого объекта, а не только перестановка width/height
             * для 90°/270°.
             */
            const visualWidth = (
                objectWidth * cos
                + objectHeight * sin
            );
            const visualHeight = (
                objectWidth * sin
                + objectHeight * cos
            );

            const objectX = Number(x ?? object?.x ?? 0);
            const objectY = Number(y ?? object?.y ?? 0);
            const centerX = objectX + objectWidth / 2;
            const centerY = objectY + objectHeight / 2;

            return {
                x: objectX,
                y: objectY,
                width: objectWidth,
                height: objectHeight,
                visualWidth,
                visualHeight,
                left: centerX - visualWidth / 2,
                centerX,
                right: centerX + visualWidth / 2,
                top: centerY - visualHeight / 2,
                centerY,
                bottom: centerY + visualHeight / 2,
            };
        },


        isColumnObject(object) {
            return String(object?.type || '') === 'column';
        },

        isBedObject(object) {
            return Boolean(
                this.selectedRoom?.beds?.some(bed => bed === object)
            );
        },

        isRotatableElement(type, object) {
            if (!object) {
                return false;
            }

            return type === 'bed'
                || (
                    type === 'item'
                    && String(object.type || '') === 'cabinet'
                );
        },

        isStickyObject(object) {
            return this.isBedObject(object)
                || String(object?.type || '') === 'bedside_table';
        },

        isColumnCabinetStickyPair(first, second) {
            const firstType = String(first?.type || '');
            const secondType = String(second?.type || '');

            return (
                (firstType === 'column' && secondType === 'cabinet')
                || (firstType === 'cabinet' && secondType === 'column')
            );
        },

        isStickyPair(first, second) {
            return (
                (this.isStickyObject(first) && this.isStickyObject(second))
                || this.isColumnCabinetStickyPair(first, second)
            );
        },

        allRoomObjects() {
            const room = this.selectedRoom;

            if (!room) {
                return [];
            }

            return [...room.beds, ...room.items];
        },

        geometriesOverlap(first, second, padding = 0) {
            const gap = Number(padding || 0);

            return first.left < second.right - gap
                && first.right > second.left + gap
                && first.top < second.bottom - gap
                && first.bottom > second.top + gap;
        },

        findColumnCollision(
            object,
            x = null,
            y = null,
            ignoreObject = null,
            excludedObjects = []
        ) {
            const room = this.selectedRoom;

            if (!room || !object) {
                return null;
            }

            const excluded = new Set(
                Array.isArray(excludedObjects)
                    ? excludedObjects
                    : []
            );

            if (ignoreObject) {
                excluded.add(ignoreObject);
            }

            const source = this.objectVisualGeometry(
                object,
                x,
                y
            );
            const sourceIsColumn = this.isColumnObject(object);

            const candidates = this.allRoomObjects().filter(candidate => {
                if (!candidate || excluded.has(candidate)) {
                    return false;
                }

                if (sourceIsColumn) {
                    return true;
                }

                return this.isColumnObject(candidate);
            });

            for (const candidate of candidates) {
                const target = this.objectVisualGeometry(candidate);

                if (!this.geometriesOverlap(source, target, 0.5)) {
                    continue;
                }

                const column = sourceIsColumn
                    ? object
                    : candidate;
                const columnGeometry = sourceIsColumn
                    ? source
                    : target;

                return {
                    column,
                    columnGeometry,
                    object,
                    obstacle: candidate,
                    movingColumn: sourceIsColumn,
                };
            }

            return null;
        },

        findGroupColumnCollision(snapshots, dx, dy) {
            const room = this.selectedRoom;

            if (!room || !Array.isArray(snapshots) || !snapshots.length) {
                return null;
            }

            const selectedObjects = new Set(
                snapshots.map(snapshot => snapshot.object)
            );
            const stationaryObjects = this.allRoomObjects().filter(
                object => !selectedObjects.has(object)
            );

            for (const snapshot of snapshots) {
                const sourceObject = snapshot.object;
                const probe = {
                    ...sourceObject,
                    x: Number(snapshot.startX || 0) + Number(dx || 0),
                    y: Number(snapshot.startY || 0) + Number(dy || 0),
                };
                const source = this.objectVisualGeometry(probe);
                const sourceIsColumn = this.isColumnObject(sourceObject);

                const candidates = stationaryObjects.filter(candidate => (
                    sourceIsColumn || this.isColumnObject(candidate)
                ));

                for (const candidate of candidates) {
                    const target = this.objectVisualGeometry(candidate);

                    if (!this.geometriesOverlap(source, target, 0.5)) {
                        continue;
                    }

                    return {
                        column: sourceIsColumn ? probe : candidate,
                        columnGeometry: sourceIsColumn ? source : target,
                        object: probe,
                        obstacle: candidate,
                        movingColumn: sourceIsColumn,
                    };
                }
            }

            return null;
        },

        findAnyColumnCollision() {
            const objects = this.allRoomObjects();

            for (let index = 0; index < objects.length; index += 1) {
                const object = objects[index];

                for (
                    let candidateIndex = index + 1;
                    candidateIndex < objects.length;
                    candidateIndex += 1
                ) {
                    const candidate = objects[candidateIndex];

                    if (
                        !this.isColumnObject(object)
                        && !this.isColumnObject(candidate)
                    ) {
                        continue;
                    }

                    const source = this.objectVisualGeometry(object);
                    const target = this.objectVisualGeometry(candidate);

                    if (!this.geometriesOverlap(source, target, 0.5)) {
                        continue;
                    }

                    const column = this.isColumnObject(object)
                        ? object
                        : candidate;

                    return {
                        column,
                        columnGeometry: this.objectVisualGeometry(column),
                        object,
                        obstacle: candidate,
                        movingColumn: this.isColumnObject(object),
                    };
                }
            }

            return null;
        },

        showColumnCollisionTooltip(collision) {
            if (!collision || !this.selectedRoom) {
                return;
            }

            const geometry = collision.columnGeometry
                || this.objectVisualGeometry(collision.column);
            const roomWidth = Number(this.selectedRoom.canvas_width || 1);
            const roomHeight = Number(this.selectedRoom.canvas_height || 1);
            const tooltip = this.collisionTooltip;

            window.clearTimeout(tooltip.timer);

            tooltip.visible = true;
            tooltip.message = collision.movingColumn
                ? 'Колонну нельзя ставить поверх другого элемента.'
                : 'Сюда нельзя поставить элемент — здесь колонна.';
            tooltip.x = Math.min(
                roomWidth,
                Math.max(0, Number(geometry.centerX || 0))
            );
            tooltip.y = Math.min(
                roomHeight,
                Math.max(0, Number(geometry.top || 0))
            );
            tooltip.lastShownAt = Date.now();

            tooltip.timer = window.setTimeout(() => {
                tooltip.visible = false;
            }, 1700);
        },

        snapStickyPosition(
            object,
            x,
            y,
            excludedObjects = []
        ) {
            const room = this.selectedRoom;

            if (!room || !object) {
                return { x, y };
            }

            const excluded = new Set(
                Array.isArray(excludedObjects)
                    ? excludedObjects
                    : []
            );
            excluded.add(object);

            const wallSticky = this.isStickyObject(object);
            const targets = this.allRoomObjects().filter(candidate => (
                !excluded.has(candidate)
                && this.isStickyPair(object, candidate)
            ));

            if (!wallSticky && !targets.length) {
                return { x, y };
            }

            const snapDistance = 18;
            const overlapTolerance = 12;
            const roomWidth = Math.max(1, Number(room.canvas_width || 1));
            const roomHeight = Math.max(1, Number(room.canvas_height || 1));
            const source = this.objectVisualGeometry(object, x, y);
            let best = null;

            const consider = (axis, distance, delta, guide, overlap = 0) => {
                const absoluteDistance = Math.abs(distance);

                if (
                    absoluteDistance > snapDistance
                    || overlap < -overlapTolerance
                ) {
                    return;
                }

                if (!best || absoluteDistance < best.distance) {
                    best = {
                        axis,
                        distance: absoluteDistance,
                        delta,
                        guide,
                    };
                }
            };

            /*
             * Липучка к стенам остаётся только у коек и тумбочек.
             * Шкаф и колонна липнут друг к другу, но не начинают
             * автоматически тянуться к стенам.
             */
            if (wallSticky) {
                consider(
                    'x',
                    source.left,
                    -source.left,
                    0
                );
                consider(
                    'x',
                    roomWidth - source.right,
                    roomWidth - source.right,
                    roomWidth
                );
                consider(
                    'y',
                    source.top,
                    -source.top,
                    0
                );
                consider(
                    'y',
                    roomHeight - source.bottom,
                    roomHeight - source.bottom,
                    roomHeight
                );
            }

            targets.forEach(targetObject => {
                const target = this.objectVisualGeometry(targetObject);
                const verticalOverlap = Math.min(
                    source.bottom,
                    target.bottom
                ) - Math.max(
                    source.top,
                    target.top
                );
                const horizontalOverlap = Math.min(
                    source.right,
                    target.right
                ) - Math.max(
                    source.left,
                    target.left
                );

                consider(
                    'x',
                    source.right - target.left,
                    target.left - source.right,
                    target.left,
                    verticalOverlap
                );
                consider(
                    'x',
                    source.left - target.right,
                    target.right - source.left,
                    target.right,
                    verticalOverlap
                );
                consider(
                    'y',
                    source.bottom - target.top,
                    target.top - source.bottom,
                    target.top,
                    horizontalOverlap
                );
                consider(
                    'y',
                    source.top - target.bottom,
                    target.bottom - source.top,
                    target.bottom,
                    horizontalOverlap
                );
            });

            if (!best) {
                return { x, y };
            }

            let nextX = Number(x || 0);
            let nextY = Number(y || 0);

            if (best.axis === 'x') {
                nextX += best.delta;
                this.alignmentGuides.vertical = this.uniqueAlignmentGuides([
                    ...this.alignmentGuides.vertical,
                    best.guide,
                ]);
            } else {
                nextY += best.delta;
                this.alignmentGuides.horizontal = this.uniqueAlignmentGuides([
                    ...this.alignmentGuides.horizontal,
                    best.guide,
                ]);
            }

            return this.clampObjectPosition(
                object,
                nextX,
                nextY,
                roomWidth,
                roomHeight
            );
        },

        findBoundaryFreePlacement(object, preferredX, preferredY) {
            const room = this.selectedRoom;

            if (!room || !object || !this.isBoundaryItem(object)) {
                return this.findFreePlacement(
                    object,
                    preferredX,
                    preferredY
                );
            }

            const roomWidth = Math.max(
                1,
                Number(room.canvas_width || 1)
            );
            const roomHeight = Math.max(
                1,
                Number(room.canvas_height || 1)
            );
            const width = Math.max(1, Number(object.width || 1));
            const height = Math.max(1, Number(object.height || 1));
            const preferred = this.clampObjectPosition(
                object,
                preferredX,
                preferredY,
                roomWidth,
                roomHeight
            );

            if (!this.findColumnCollision(
                object,
                preferred.x,
                preferred.y,
                object
            )) {
                return preferred;
            }

            const centerX = Number(preferred.x) + width / 2;
            const centerY = Number(preferred.y) + height / 2;
            const nearestWall = [
                { side: 'left', distance: Math.abs(centerX) },
                { side: 'right', distance: Math.abs(roomWidth - centerX) },
                { side: 'top', distance: Math.abs(centerY) },
                { side: 'bottom', distance: Math.abs(roomHeight - centerY) },
            ].sort((first, second) => first.distance - second.distance)[0];
            const step = 28;

            if (nearestWall?.side === 'top' || nearestWall?.side === 'bottom') {
                const y = nearestWall.side === 'top'
                    ? -height / 2
                    : roomHeight - height / 2;

                for (let center = 0; center <= roomWidth; center += step) {
                    const x = center - width / 2;

                    if (!this.findColumnCollision(
                        object,
                        x,
                        y,
                        object
                    )) {
                        return { x, y };
                    }
                }
            } else {
                const x = nearestWall?.side === 'right'
                    ? roomWidth - width / 2
                    : -width / 2;

                for (let center = 0; center <= roomHeight; center += step) {
                    const y = center - height / 2;

                    if (!this.findColumnCollision(
                        object,
                        x,
                        y,
                        object
                    )) {
                        return { x, y };
                    }
                }
            }

            return preferred;
        },

        findFreePlacement(object, preferredX, preferredY) {
            const room = this.selectedRoom;

            if (!room || !object) {
                return {
                    x: Number(preferredX || 0),
                    y: Number(preferredY || 0),
                };
            }

            const preferred = this.clampObjectPosition(
                object,
                preferredX,
                preferredY,
                Number(room.canvas_width),
                Number(room.canvas_height)
            );

            if (!this.findColumnCollision(
                object,
                preferred.x,
                preferred.y,
                object
            )) {
                return preferred;
            }

            const step = 24;
            const bounds = this.objectBoundsForCanvas(
                object,
                Number(room.canvas_width),
                Number(room.canvas_height)
            );

            for (
                let y = Math.ceil(bounds.minY);
                y <= Math.floor(bounds.maxY);
                y += step
            ) {
                for (
                    let x = Math.ceil(bounds.minX);
                    x <= Math.floor(bounds.maxX);
                    x += step
                ) {
                    if (!this.findColumnCollision(
                        object,
                        x,
                        y,
                        object
                    )) {
                        return { x, y };
                    }
                }
            }

            return preferred;
        },

        clearAlignmentGuides() {
            this.alignmentGuides.vertical = [];
            this.alignmentGuides.horizontal = [];
        },

        uniqueAlignmentGuides(values) {
            return Array.from(
                new Set(
                    values
                        .map(value => Math.round(Number(value || 0) * 100) / 100)
                )
            );
        },

        alignmentAxesForObject(object, x, y) {
            if (!this.isBoundaryItem(object) || !this.selectedRoom) {
                return { x: true, y: true };
            }

            const roomWidth = Number(this.selectedRoom.canvas_width || 0);
            const roomHeight = Number(this.selectedRoom.canvas_height || 0);
            const width = Math.max(1, Number(object.width || 1));
            const height = Math.max(1, Number(object.height || 1));
            const centerX = Number(x || 0) + width / 2;
            const centerY = Number(y || 0) + height / 2;
            const tolerance = 1.5;
            const onVerticalWall =
                Math.abs(centerX) <= tolerance
                || Math.abs(centerX - roomWidth) <= tolerance;
            const onHorizontalWall =
                Math.abs(centerY) <= tolerance
                || Math.abs(centerY - roomHeight) <= tolerance;

            return {
                // На левой/правой стене не сдвигаем объект поперёк стены.
                x: !onVerticalWall,
                // На верхней/нижней стене не сдвигаем объект поперёк стены.
                y: !onHorizontalWall,
            };
        },

        alignPositionToObjects(object, x, y, excludedObjects = []) {
            const room = this.selectedRoom;

            this.clearAlignmentGuides();

            if (!room) {
                return { x, y };
            }

            const excluded = new Set(
                Array.isArray(excludedObjects)
                    ? excludedObjects
                    : []
            );

            const others = [...room.beds, ...room.items]
                .filter(candidate =>
                    candidate !== object
                    && !excluded.has(candidate)
                );

            if (!others.length) {
                return { x, y };
            }

            const axes = this.alignmentAxesForObject(object, x, y);
            const snapDistance = 8;
            const guideTolerance = 0.75;
            let nextX = Number(x || 0);
            let nextY = Number(y || 0);
            let source = this.objectVisualGeometry(object, nextX, nextY);

            let bestX = null;
            let bestY = null;

            /*
             * Выравнивание идёт по трем опорным линиям каждой оси:
             * X: левый край / центр / правый край.
             * Y: верх / центр / низ.
             *
             * Для одинаковых по размеру объектов дельта у нескольких
             * опорных линий может совпадать. Поэтому после снапа показываем
             * ВСЕ совпавшие направляющие, а не только первую (раньше это
             * почти всегда была верхняя линия).
             */
            const sourceXAnchors = [
                ['left', source.left],
                ['centerX', source.centerX],
                ['right', source.right],
            ];
            const sourceYAnchors = [
                ['top', source.top],
                ['centerY', source.centerY],
                ['bottom', source.bottom],
            ];

            others.forEach(candidate => {
                const target = this.objectVisualGeometry(candidate);
                const targetX = {
                    left: target.left,
                    centerX: target.centerX,
                    right: target.right,
                };
                const targetY = {
                    top: target.top,
                    centerY: target.centerY,
                    bottom: target.bottom,
                };

                if (axes.x) {
                    sourceXAnchors.forEach(([name, value]) => {
                        const delta = targetX[name] - value;
                        const distance = Math.abs(delta);

                        if (
                            distance <= snapDistance
                            && (
                                !bestX
                                || distance < bestX.distance
                            )
                        ) {
                            bestX = {
                                distance,
                                delta,
                            };
                        }
                    });
                }

                if (axes.y) {
                    sourceYAnchors.forEach(([name, value]) => {
                        const delta = targetY[name] - value;
                        const distance = Math.abs(delta);

                        if (
                            distance <= snapDistance
                            && (
                                !bestY
                                || distance < bestY.distance
                            )
                        ) {
                            bestY = {
                                distance,
                                delta,
                            };
                        }
                    });
                }
            });

            if (bestX) {
                nextX += bestX.delta;
            }

            if (bestY) {
                nextY += bestY.delta;
            }

            /*
             * После фактического смещения пересчитываем геометрию и
             * собираем все совпавшие линии. Это даёт нормальные красные
             * направляющие не только сверху, но и по центру/низу.
             */
            source = this.objectVisualGeometry(object, nextX, nextY);
            const verticalGuides = [];
            const horizontalGuides = [];

            others.forEach(candidate => {
                const target = this.objectVisualGeometry(candidate);

                if (axes.x) {
                    [
                        [source.left, target.left],
                        [source.centerX, target.centerX],
                        [source.right, target.right],
                    ].forEach(([sourceValue, targetValue]) => {
                        if (
                            Math.abs(sourceValue - targetValue)
                            <= guideTolerance
                        ) {
                            verticalGuides.push(targetValue);
                        }
                    });
                }

                if (axes.y) {
                    [
                        [source.top, target.top],
                        [source.centerY, target.centerY],
                        [source.bottom, target.bottom],
                    ].forEach(([sourceValue, targetValue]) => {
                        if (
                            Math.abs(sourceValue - targetValue)
                            <= guideTolerance
                        ) {
                            horizontalGuides.push(targetValue);
                        }
                    });
                }
            });

            this.alignmentGuides.vertical =
                this.uniqueAlignmentGuides(verticalGuides);
            this.alignmentGuides.horizontal =
                this.uniqueAlignmentGuides(horizontalGuides);

            return this.clampObjectPosition(
                object,
                nextX,
                nextY,
                Number(room.canvas_width),
                Number(room.canvas_height)
            );
        },

        objectBoundsForCanvas(
            object,
            canvasWidth = null,
            canvasHeight = null
        ) {
            const room = this.selectedRoom;
            const width = Math.max(
                1,
                Number(canvasWidth ?? room?.canvas_width ?? 1)
            );
            const height = Math.max(
                1,
                Number(canvasHeight ?? room?.canvas_height ?? 1)
            );
            const objectWidth = Math.max(
                1,
                Number(object?.width || 1)
            );
            const objectHeight = Math.max(
                1,
                Number(object?.height || 1)
            );

            /*
             * Архитектурные элементы можно ставить центром прямо на стену.
             * Поэтому половина окна / входа / розетки может находиться
             * за логической границей рабочей области.
             */
            if (this.isBoundaryItem(object)) {
                return {
                    minX: -objectWidth / 2,
                    maxX: width - objectWidth / 2,
                    minY: -objectHeight / 2,
                    maxY: height - objectHeight / 2,
                };
            }

            const rotation = this.normalizedRotation(object);
            const angle = rotation * Math.PI / 180;
            const absCos = Math.abs(Math.cos(angle));
            const absSin = Math.abs(Math.sin(angle));
            const visualWidth = (
                objectWidth * absCos
                + objectHeight * absSin
            );
            const visualHeight = (
                objectWidth * absSin
                + objectHeight * absCos
            );

            /*
             * x/y — левый верх исходного прямоугольника, а CSS rotate()
             * вращает его вокруг центра. Поэтому при любом угле разрешённый
             * диапазон координат рассчитываем по фактическому габариту
             * повернутого объекта.
             */
            return {
                minX: (visualWidth - objectWidth) / 2,
                maxX: Math.max(
                    (visualWidth - objectWidth) / 2,
                    width - (visualWidth + objectWidth) / 2
                ),
                minY: (visualHeight - objectHeight) / 2,
                maxY: Math.max(
                    (visualHeight - objectHeight) / 2,
                    height - (visualHeight + objectHeight) / 2
                ),
            };
        },

        clampObjectPosition(
            object,
            x,
            y,
            canvasWidth = null,
            canvasHeight = null
        ) {
            const bounds = this.objectBoundsForCanvas(
                object,
                canvasWidth,
                canvasHeight
            );

            return {
                x: Math.min(
                    bounds.maxX,
                    Math.max(bounds.minX, Number(x || 0))
                ),
                y: Math.min(
                    bounds.maxY,
                    Math.max(bounds.minY, Number(y || 0))
                ),
            };
        },

        snapBoundaryObject(object, x, y) {
            const room = this.selectedRoom;

            if (!room || !this.isBoundaryItem(object)) {
                return { x, y };
            }

            const width = Math.max(1, Number(object.width || 1));
            const height = Math.max(1, Number(object.height || 1));
            const roomWidth = Math.max(1, Number(room.canvas_width || 1));
            const roomHeight = Math.max(1, Number(room.canvas_height || 1));

            let nextX = Number(x || 0);
            let nextY = Number(y || 0);

            const centerX = nextX + width / 2;
            const centerY = nextY + height / 2;

            const walls = [
                { side: 'left', distance: Math.abs(centerX) },
                { side: 'right', distance: Math.abs(roomWidth - centerX) },
                { side: 'top', distance: Math.abs(centerY) },
                { side: 'bottom', distance: Math.abs(roomHeight - centerY) },
            ].sort((a, b) => a.distance - b.distance);

            /*
             * Небольшая магнитная зона у стены. Пользователь может спокойно
             * двигать объект внутри палаты, но рядом с границей он аккуратно
             * встаёт центром на линию стены.
             */
            const snapDistance = 34;
            const nearest = walls[0];

            if (nearest && nearest.distance <= snapDistance) {
                if (nearest.side === 'left') {
                    nextX = -width / 2;

                    if (object.type === 'window' || object.type === 'entrance') {
                        object.rotation = 90;
                    }
                }

                if (nearest.side === 'right') {
                    nextX = roomWidth - width / 2;

                    if (object.type === 'window' || object.type === 'entrance') {
                        object.rotation = 90;
                    }
                }

                if (nearest.side === 'top') {
                    nextY = -height / 2;

                    if (object.type === 'window' || object.type === 'entrance') {
                        object.rotation = 0;
                    }
                }

                if (nearest.side === 'bottom') {
                    nextY = roomHeight - height / 2;

                    if (object.type === 'window' || object.type === 'entrance') {
                        object.rotation = 0;
                    }
                }
            }

            return this.clampObjectPosition(
                object,
                nextX,
                nextY,
                roomWidth,
                roomHeight
            );
        },

        objectStyle(object) {
            const room = this.selectedRoom;
            if (!room) return {};

            const style = {
                // Относительные позиции растягиваются вместе с рабочей
                // областью независимо по ширине и высоте.
                left: `${(Number(object.x || 0) / room.canvas_width) * 100}%`,
                top: `${(Number(object.y || 0) / room.canvas_height) * 100}%`,
                transform: `rotate(${Number(object.rotation || 0)}deg)`,
                '--bed-rotation': `${this.normalizedRotation(object)}deg`,
                '--object-rotation': `${this.normalizedRotation(object)}deg`,
            };

            if (!this.editMode && this.viewMapDimensions) {
                // Используем единый масштаб для обеих сторон предмета.
                // Проценты размеров здесь искажали бы форму при раздельных
                // ползунках ширины/высоты канваса.
                const scale = this.viewMapDimensions.objectScale;
                return {
                    ...style,
                    width: `${Math.max(1, Number(object.width || 80) * scale).toFixed(2)}px`,
                    height: `${Math.max(1, Number(object.height || 60) * scale).toFixed(2)}px`,
                };
            }

            return {
                ...style,
                width: `${(Number(object.width || 80) / room.canvas_width) * 100}%`,
                height: `${(Number(object.height || 60) / room.canvas_height) * 100}%`,
            };
        },

        contextMenuPosition(clientX, clientY) {
            const width = 226;
            const height = this.selectedElements.length ? 206 : 62;
            const viewportWidth = Math.max(0, Number(window.innerWidth || 0));
            const viewportHeight = Math.max(0, Number(window.innerHeight || 0));
            const margin = 8;

            return {
                x: Math.max(
                    margin,
                    Math.min(
                        Number(clientX || 0),
                        Math.max(margin, viewportWidth - width - margin)
                    )
                ),
                y: Math.max(
                    margin,
                    Math.min(
                        Number(clientY || 0),
                        Math.max(margin, viewportHeight - height - margin)
                    )
                ),
            };
        },

        openContextMenu(event, canvasPoint = null, onObject = false) {
            if (!this.editMode || !this.selectedRoom) return;

            const position = this.contextMenuPosition(
                event?.clientX,
                event?.clientY
            );

            this.contextMenu = {
                open: true,
                x: position.x,
                y: position.y,
                canvasPoint: canvasPoint
                    ? {
                        x: Number(canvasPoint.x || 0),
                        y: Number(canvasPoint.y || 0),
                    }
                    : null,
                onObject: Boolean(onObject),
            };
        },

        openObjectContextMenu(event, type, object) {
            if (!this.editMode || !object) return;

            if (!this.isSelected(type, object)) {
                this.setSingleSelection(type, object);
            } else {
                this.selectedElement = this.selectedElements.find(
                    entry => this.sameSelectionEntry(entry, type, object)
                ) || this.selectedElement;
            }

            this.openContextMenu(
                event,
                this.pointerToCanvas(event),
                true
            );
        },

        openCanvasContextMenu(event) {
            if (!this.editMode || !this.selectedRoom) return;
            this.openContextMenu(event, this.pointerToCanvas(event), false);
        },

        closeContextMenu() {
            if (!this.contextMenu?.open) return;

            this.contextMenu = {
                open: false,
                x: 0,
                y: 0,
                canvasPoint: null,
                onObject: false,
            };
        },

        onGlobalPointerDown(event) {
            const target = event?.target;

            if (
                target instanceof Element
                && target.closest('.hrm-context-menu')
            ) {
                return;
            }

            this.closeContextMenu();
        },

        clipboardEntry(entry) {
            const object = entry?.object;

            if (!entry || !object) {
                return null;
            }

            if (entry.type === 'bed') {
                return {
                    type: 'bed',
                    data: {
                        x: Number(object.x || 0),
                        y: Number(object.y || 0),
                        width: Math.max(1, Number(object.width || 150)),
                        height: Math.max(1, Number(object.height || 74)),
                        rotation: this.normalizedRotation(object),
                        label: String(object.label || ''),
                        enabled: Boolean(object.enabled !== false),
                    },
                };
            }

            return {
                type: 'item',
                data: {
                    type: String(object.type || 'bedside_table'),
                    x: Number(object.x || 0),
                    y: Number(object.y || 0),
                    width: Math.max(1, Number(object.width || 72)),
                    height: Math.max(1, Number(object.height || 72)),
                    rotation: this.normalizedRotation(object),
                    label: String(object.label || ''),
                },
            };
        },

        putSelectionToClipboard(mode = 'copy') {
            if (!this.editMode || !this.selectedElements.length) {
                return false;
            }

            if (
                mode === 'cut'
                && this.selectionHasOccupiedBed
            ) {
                this.showNotice(
                    'Занятую койку нельзя вырезать. Сначала освободите её.'
                );
                return false;
            }

            const entries = this.selectedElements
                .map(entry => this.clipboardEntry(entry))
                .filter(Boolean);

            if (!entries.length) {
                return false;
            }

            this.clipboard = {
                entries,
                mode,
                pasteSerial: 0,
            };

            return true;
        },

        copySelected() {
            if (!this.putSelectionToClipboard('copy')) {
                return;
            }

            const count = this.clipboard.entries.length;
            this.showNotice(
                count === 1
                    ? 'Элемент скопирован.'
                    : `Скопировано элементов: ${count}.`
            );
        },

        cutSelected() {
            if (!this.putSelectionToClipboard('cut')) {
                return;
            }

            const count = this.clipboard.entries.length;
            this.removeSelected();
            this.showNotice(
                count === 1
                    ? 'Элемент вырезан.'
                    : `Вырезано элементов: ${count}.`
            );
        },

        clipboardBounds(entries) {
            if (!Array.isArray(entries) || !entries.length) {
                return null;
            }

            const geometries = entries.map(entry => {
                const data = entry.data || {};
                return this.objectVisualGeometry(data);
            });

            const left = Math.min(...geometries.map(item => item.left));
            const right = Math.max(...geometries.map(item => item.right));
            const top = Math.min(...geometries.map(item => item.top));
            const bottom = Math.max(...geometries.map(item => item.bottom));

            return {
                left,
                right,
                top,
                bottom,
                centerX: (left + right) / 2,
                centerY: (top + bottom) / 2,
            };
        },

        clipboardBoundarySide(data) {
            const room = this.selectedRoom;

            if (!room || !this.isBoundaryItem(data)) {
                return null;
            }

            const width = Math.max(1, Number(data.width || 1));
            const height = Math.max(1, Number(data.height || 1));
            const centerX = Number(data.x || 0) + width / 2;
            const centerY = Number(data.y || 0) + height / 2;
            const roomWidth = Number(room.canvas_width || 0);
            const roomHeight = Number(room.canvas_height || 0);
            const tolerance = 3;

            if (Math.abs(centerX) <= tolerance) return 'left';
            if (Math.abs(centerX - roomWidth) <= tolerance) return 'right';
            if (Math.abs(centerY) <= tolerance) return 'top';
            if (Math.abs(centerY - roomHeight) <= tolerance) return 'bottom';

            return null;
        },

        freshClipboardObject(entry) {
            const data = entry?.data || {};

            if (entry?.type === 'bed') {
                return {
                    id: null,
                    _key: `tmp-bed-${this.tempCounter++}`,
                    number: this.nextBedNumber(),
                    label: String(data.label || ''),
                    x: Number(data.x || 0),
                    y: Number(data.y || 0),
                    width: Math.max(1, Number(data.width || 150)),
                    height: Math.max(1, Number(data.height || 74)),
                    rotation: this.normalizedRotation(data),
                    enabled: Boolean(data.enabled !== false),
                    occupied: false,
                    patient: null,
                    assigned_at: null,
                    current_assignment: null,
                };
            }

            return {
                id: null,
                _key: `tmp-item-${this.tempCounter++}`,
                type: String(data.type || 'bedside_table'),
                label: String(data.label || ''),
                x: Number(data.x || 0),
                y: Number(data.y || 0),
                width: Math.max(1, Number(data.width || 72)),
                height: Math.max(1, Number(data.height || 72)),
                rotation: this.normalizedRotation(data),
            };
        },

        pasteClipboard(canvasPoint = null) {
            const room = this.selectedRoom;
            const entries = Array.isArray(this.clipboard?.entries)
                ? this.clipboard.entries
                : [];

            if (!this.editMode || !room || !entries.length) {
                return;
            }

            const bounds = this.clipboardBounds(entries);
            const nextSerial = Number(this.clipboard.pasteSerial || 0) + 1;
            const cascade = 24 * nextSerial;
            let shiftX = cascade;
            let shiftY = cascade;

            if (canvasPoint && bounds) {
                shiftX = Number(canvasPoint.x || 0) - bounds.centerX;
                shiftY = Number(canvasPoint.y || 0) - bounds.centerY;
            }

            const inserted = [];

            this.pushUndoCheckpoint();

            entries.forEach(entry => {
                const fresh = this.freshClipboardObject(entry);
                const source = entry.data || {};
                const boundarySide = this.clipboardBoundarySide(source);
                let targetX = Number(source.x || 0) + shiftX;
                let targetY = Number(source.y || 0) + shiftY;

                /*
                 * Ctrl+V для окна/входа/розетки, которые уже стоят на
                 * стене, смещает копию ВДОЛЬ этой стены. Объект не съезжает
                 * внутрь палаты только из-за стандартного каскадного offset.
                 */
                if (!canvasPoint && boundarySide) {
                    const width = Math.max(1, Number(fresh.width || 1));
                    const height = Math.max(1, Number(fresh.height || 1));

                    if (boundarySide === 'left') {
                        targetX = -width / 2;
                        targetY = Number(source.y || 0) + cascade;
                    }
                    if (boundarySide === 'right') {
                        targetX = Number(room.canvas_width) - width / 2;
                        targetY = Number(source.y || 0) + cascade;
                    }
                    if (boundarySide === 'top') {
                        targetX = Number(source.x || 0) + cascade;
                        targetY = -height / 2;
                    }
                    if (boundarySide === 'bottom') {
                        targetX = Number(source.x || 0) + cascade;
                        targetY = Number(room.canvas_height) - height / 2;
                    }
                }

                let placement = this.clampObjectPosition(
                    fresh,
                    targetX,
                    targetY,
                    Number(room.canvas_width),
                    Number(room.canvas_height)
                );

                if (this.isBoundaryItem(fresh) && boundarySide && !canvasPoint) {
                    placement = this.snapBoundaryObject(
                        fresh,
                        placement.x,
                        placement.y
                    );
                }

                placement = this.isBoundaryItem(fresh)
                    ? this.findBoundaryFreePlacement(
                        fresh,
                        placement.x,
                        placement.y
                    )
                    : this.findFreePlacement(
                        fresh,
                        placement.x,
                        placement.y
                    );

                fresh.x = Math.round(placement.x);
                fresh.y = Math.round(placement.y);

                if (entry.type === 'bed') {
                    room.beds.push(fresh);
                    inserted.push({ type: 'bed', object: fresh });
                } else {
                    room.items.push(fresh);
                    inserted.push({ type: 'item', object: fresh });
                }
            });

            if (!inserted.length) {
                return;
            }

            this.clipboard.pasteSerial = nextSerial;
            this.selectedElements = inserted;
            this.selectedElement = inserted.length === 1
                ? inserted[0]
                : null;
            this.markDirty();

            this.showNotice(
                inserted.length === 1
                    ? 'Вставлена новая копия элемента.'
                    : `Вставлены новые элементы: ${inserted.length}.`
            );
        },

        copyFromContextMenu() {
            this.copySelected();
            this.closeContextMenu();
        },

        cutFromContextMenu() {
            this.cutSelected();
            this.closeContextMenu();
        },

        pasteFromContextMenu() {
            const point = this.contextMenu?.canvasPoint
                ? { ...this.contextMenu.canvasPoint }
                : null;
            this.closeContextMenu();
            this.pasteClipboard(point);
        },

        deleteFromContextMenu() {
            this.removeSelected();
            this.closeContextMenu();
        },

        sameSelectionEntry(entry, type, object) {
            return Boolean(
                entry
                && entry.type === type
                && entry.object === object
            );
        },

        isSelected(type, object) {
            return this.selectedElements.some(
                entry => this.sameSelectionEntry(entry, type, object)
            );
        },

        isPrimarySelected(type, object) {
            return this.sameSelectionEntry(
                this.selectedElement,
                type,
                object
            );
        },

        setSingleSelection(type, object) {
            if (!this.editMode || !object) return;

            const entry = { type, object };
            this.selectedElements = [entry];
            this.selectedElement = entry;
        },

        selectElement(type, object) {
            this.setSingleSelection(type, object);
        },

        toggleElementSelection(type, object) {
            if (!this.editMode || !object) return false;

            const index = this.selectedElements.findIndex(
                entry => this.sameSelectionEntry(entry, type, object)
            );

            if (index >= 0) {
                this.selectedElements.splice(index, 1);

                if (this.isPrimarySelected(type, object)) {
                    this.selectedElement = this.selectedElements.length
                        ? this.selectedElements[this.selectedElements.length - 1]
                        : null;
                }

                return false;
            }

            const entry = { type, object };
            this.selectedElements.push(entry);
            this.selectedElement = entry;
            return true;
        },

        clearSelection() {
            this.selectedElement = null;
            this.selectedElements = [];
            this.stopMarqueeSelection(false);
            this.closeContextMenu();
        },

        handleObjectClick() {
            /*
             * В режиме редактирования выделение выполняется уже на
             * pointerdown. Сам click ничего не меняет, чтобы после
             * группового перетаскивания выделение не схлопывалось до
             * одного элемента.
             */
        },

        pointerToCanvas(event) {
            const room = this.selectedRoom;
            const map = this.$refs.map;
            const rect = map?.getBoundingClientRect();

            if (!room || !rect?.width || !rect?.height) {
                return null;
            }

            return {
                x: Math.min(
                    Number(room.canvas_width || 1),
                    Math.max(
                        0,
                        (event.clientX - rect.left)
                        * Number(room.canvas_width || 1)
                        / rect.width
                    )
                ),
                y: Math.min(
                    Number(room.canvas_height || 1),
                    Math.max(
                        0,
                        (event.clientY - rect.top)
                        * Number(room.canvas_height || 1)
                        / rect.height
                    )
                ),
            };
        },

        allCanvasSelectionEntries() {
            const room = this.selectedRoom;
            if (!room) return [];

            return [
                ...room.items.map(object => ({ type: 'item', object })),
                ...room.beds.map(object => ({ type: 'bed', object })),
            ];
        },

        selectionEntryExists(collection, entry) {
            return collection.some(
                candidate => this.sameSelectionEntry(
                    candidate,
                    entry.type,
                    entry.object
                )
            );
        },

        startMarqueeSelection(event) {
            if (!this.editMode || !this.selectedRoom) return;
            if (this.pendingPlacementType) return;
            if (event.button !== undefined && event.button !== 0) return;

            const point = this.pointerToCanvas(event);
            if (!point) return;

            this.stopDrag();
            this.stopObjectResize();
            this.stopObjectRotation();
            this.clearAlignmentGuides();

            const additive = Boolean(
                event.shiftKey || event.ctrlKey || event.metaKey
            );

            this.selectionMarquee = {
                active: true,
                moved: false,
                additive,
                startX: point.x,
                startY: point.y,
                endX: point.x,
                endY: point.y,
                baseSelection: additive
                    ? [...this.selectedElements]
                    : [],
            };

            if (!additive) {
                this.selectedElements = [];
                this.selectedElement = null;
            }

            window.addEventListener(
                'pointermove',
                this.onMarqueeSelection
            );
            window.addEventListener(
                'pointerup',
                this.stopMarqueeSelection,
                { once: true }
            );
        },

        onMarqueeSelection(event) {
            const state = this.selectionMarquee;
            if (!state?.active || !this.selectedRoom) return;

            const point = this.pointerToCanvas(event);
            if (!point) return;

            state.endX = point.x;
            state.endY = point.y;

            const dx = Math.abs(state.endX - state.startX);
            const dy = Math.abs(state.endY - state.startY);
            state.moved = state.moved || dx > 4 || dy > 4;

            if (!state.moved) return;

            const left = Math.min(state.startX, state.endX);
            const right = Math.max(state.startX, state.endX);
            const top = Math.min(state.startY, state.endY);
            const bottom = Math.max(state.startY, state.endY);

            const inside = this.allCanvasSelectionEntries()
                .filter(entry => {
                    const geometry = this.objectVisualGeometry(
                        entry.object
                    );

                    return geometry.right >= left
                        && geometry.left <= right
                        && geometry.bottom >= top
                        && geometry.top <= bottom;
                });

            const next = state.additive
                ? [...state.baseSelection]
                : [];

            inside.forEach(entry => {
                if (!this.selectionEntryExists(next, entry)) {
                    next.push(entry);
                }
            });

            this.selectedElements = next;

            if (
                this.selectedElement
                && this.selectionEntryExists(
                    this.selectedElements,
                    this.selectedElement
                )
            ) {
                return;
            }

            this.selectedElement = inside.length
                ? inside[inside.length - 1]
                : (next.length ? next[next.length - 1] : null);
        },

        stopMarqueeSelection(suppressClick = true) {
            window.removeEventListener(
                'pointermove',
                this.onMarqueeSelection
            );

            const moved = Boolean(this.selectionMarquee?.moved);

            if (this.selectionMarquee) {
                this.selectionMarquee.active = false;
            }

            if (suppressClick && moved) {
                this.suppressCanvasClick = true;
                window.setTimeout(() => {
                    this.suppressCanvasClick = false;
                }, 0);
            }
        },

        handleCanvasClick(event) {
            if (!this.editMode || this.suppressCanvasClick) return;
            if (event?.shiftKey || event?.ctrlKey || event?.metaKey) return;

            if (this.pendingPlacementType) {
                const point = this.pointerToCanvas(event);
                if (!point) return;

                const type = this.pendingPlacementType;

                /*
                 * Инструмент добавления остаётся активным после вставки.
                 * Это позволяет поставить подряд несколько коек / предметов,
                 * не выбирая кнопку в панели перед каждым кликом.
                 */
                if (type === 'bed') {
                    this.addBed(point);
                } else {
                    this.addFurniture(type, point);
                }

                return;
            }

            this.clearSelection();
        },

        clearPlacementFocus() {
            this.pendingPlacementType = '';
            this.stopDrag();
            this.stopObjectResize();
            this.stopObjectRotation();
            this.clearSelection();
            this.clearAlignmentGuides();
        },

        selectPlacementTool(type) {
            if (!this.editMode || !this.selectedRoom) return;

            /*
             * При переходе к инструменту добавления снимаем фокус с уже
             * выбранного объекта. Сам инструмент после вставки не сбрасывается:
             * пользователь может поставить подряд несколько одинаковых объектов.
             */
            this.stopDrag();
            this.stopObjectResize();
            this.stopObjectRotation();
            this.clearSelection();
            this.clearAlignmentGuides();

            /*
             * Вход — исключение: он создаётся сразу на стене, как и раньше.
             */
            if (type === 'entrance') {
                this.pendingPlacementType = '';
                this.addFurniture('entrance');
                return;
            }

            const nextType = String(type || '');
            this.pendingPlacementType = this.pendingPlacementType === nextType
                ? ''
                : nextType;
        },

        nextBedNumber() {
            const numbers = this.selectedRoom.beds
                .map(bed => Number.parseInt(String(bed.number), 10))
                .filter(Number.isFinite);
            return String((numbers.length ? Math.max(...numbers) : 0) + 1);
        },

        addBed(point = null) {
            const room = this.selectedRoom;
            if (!room) return;

            const width = 150;
            const height = 74;
            const hasPoint = point
                && Number.isFinite(Number(point.x))
                && Number.isFinite(Number(point.y));
            const startX = hasPoint
                ? Number(point.x) - width / 2
                : 50 + ((room.beds.length * 34) % 300);
            const startY = hasPoint
                ? Number(point.y) - height / 2
                : 50 + ((room.beds.length * 28) % 240);

            const bed = {
                id: null,
                _key: `tmp-bed-${this.tempCounter++}`,
                number: this.nextBedNumber(),
                label: '',
                x: startX,
                y: startY,
                width,
                height,
                rotation: 0,
                enabled: true,
                occupied: false,
                patient: null,
            };

            const placement = this.findFreePlacement(
                bed,
                bed.x,
                bed.y
            );

            bed.x = Math.round(placement.x);
            bed.y = Math.round(placement.y);

            this.pushUndoCheckpoint();
            room.beds.push(bed);
            this.setSingleSelection('bed', bed);
            this.markDirty();
        },

        addFurniture(type, point = null) {
            const room = this.selectedRoom;
            if (!room) return;

            const dimensions = {
                cabinet: { width: 110, height: 58 },
                bedside_table: { width: 72, height: 72 },
                column: { width: 76, height: 76 },
                entrance: { width: 104, height: 36 },
                window: { width: 130, height: 30 },
                socket: { width: 42, height: 42 },
                toilet: { width: 78, height: 96 },
            };
            const size = dimensions[type] || dimensions.bedside_table;
            const width = size.width;
            const height = size.height;

            const sameTypeCount = room.items.filter(
                item => item.type === type
            ).length;

            const hasPoint = point
                && Number.isFinite(Number(point.x))
                && Number.isFinite(Number(point.y));
            let x = hasPoint
                ? Number(point.x) - width / 2
                : 80 + ((room.items.length * 30) % 280);
            let y = hasPoint
                ? Number(point.y) - height / 2
                : 80 + ((room.items.length * 24) % 230);
            let rotation = 0;

            /*
             * При создании кликом окно/розетка появляются именно там, где
             * кликнул пользователь. Если точка рядом со стеной, ниже сработает
             * обычная магнитная привязка. Вход — исключение и по-прежнему
             * создаётся сразу на стене.
             */
            if (!hasPoint && type === 'entrance') {
                x = Math.max(
                    -width / 2,
                    Number(room.canvas_width || 900) / 2 - width / 2
                );
                y = Number(room.canvas_height || 560) - height / 2;
            }

            if (!hasPoint && type === 'window') {
                x = Math.min(
                    Number(room.canvas_width || 900) - width / 2,
                    70 + sameTypeCount * 155
                );
                y = -height / 2;
            }

            if (!hasPoint && type === 'socket') {
                x = -width / 2;
                y = Math.min(
                    Number(room.canvas_height || 560) - height / 2,
                    75 + sameTypeCount * 62
                );
            }

            const item = {
                id: null,
                _key: `tmp-item-${this.tempCounter++}`,
                type,
                label: '',
                x: Math.round(x),
                y: Math.round(y),
                width,
                height,
                rotation,
            };

            /*
             * Колонна является физическим препятствием. При добавлении
             * любого нового объекта ищем ближайшее свободное место, чтобы
             * элемент не появился поверх уже существующей колонны.
             * Новую колонну, соответственно, не кладём поверх мебели/коек.
             */
            let preferred = this.clampObjectPosition(
                item,
                x,
                y,
                Number(room.canvas_width),
                Number(room.canvas_height)
            );

            if (hasPoint && this.isBoundaryItem(item)) {
                preferred = this.snapBoundaryObject(
                    item,
                    preferred.x,
                    preferred.y
                );
            }

            const placement = this.isBoundaryItem(item)
                ? this.findBoundaryFreePlacement(
                    item,
                    preferred.x,
                    preferred.y
                )
                : this.findFreePlacement(
                    item,
                    preferred.x,
                    preferred.y
                );

            item.x = Math.round(placement.x);
            item.y = Math.round(placement.y);

            this.pushUndoCheckpoint();
            room.items.push(item);
            this.setSingleSelection('item', item);
            this.markDirty();
        },

        canvasLimits() {
            return {
                minWidth: 500,
                maxWidth: 1600,
                minHeight: 350,
                maxHeight: 1200,
            };
        },

        clampCanvasValue(value, min, max) {
            const numeric = Number(value);
            if (!Number.isFinite(numeric)) return min;
            return Math.round(Math.min(max, Math.max(min, numeric)));
        },

        canvasObjectSnapshots() {
            const room = this.selectedRoom;
            if (!room) return [];

            return [...room.beds, ...room.items].map(object => ({
                object,
                x: Number(object.x || 0),
                y: Number(object.y || 0),
                width: Math.max(1, Number(object.width || 1)),
                height: Math.max(1, Number(object.height || 1)),
            }));
        },

        repositionObjectsForCanvas(
            oldWidth,
            oldHeight,
            newWidth,
            newHeight,
            snapshots = null
        ) {
            const room = this.selectedRoom;
            if (!room) return;

            const sourceWidth = Math.max(1, Number(oldWidth || 1));
            const sourceHeight = Math.max(1, Number(oldHeight || 1));
            const targetWidth = Math.max(1, Number(newWidth || 1));
            const targetHeight = Math.max(1, Number(newHeight || 1));
            const ratioX = targetWidth / sourceWidth;
            const ratioY = targetHeight / sourceHeight;

            const source = Array.isArray(snapshots)
                ? snapshots
                : this.canvasObjectSnapshots();

            source.forEach(snapshot => {
                const object = snapshot.object;
                if (!object) return;

                /*
                 * Канвас и всё его содержимое образуют одну систему
                 * координат. Поэтому вместе с положением масштабируем и
                 * размеры каждого элемента: X/width по ширине канваса,
                 * Y/height по высоте. Так план комнаты сохраняет свои
                 * реальные пропорции при любом resize/preset.
                 */
                object.width = Math.max(
                    8,
                    Math.round(
                        Math.max(1, Number(snapshot.width || 1))
                        * ratioX
                    )
                );
                object.height = Math.max(
                    8,
                    Math.round(
                        Math.max(1, Number(snapshot.height || 1))
                        * ratioY
                    )
                );

                const nextPosition = this.clampObjectPosition(
                    object,
                    Number(snapshot.x || 0) * ratioX,
                    Number(snapshot.y || 0) * ratioY,
                    targetWidth,
                    targetHeight
                );

                object.x = Math.round(nextPosition.x);
                object.y = Math.round(nextPosition.y);
            });
        },

        applyCanvasSize(width, height) {
            const room = this.selectedRoom;
            if (!room) return;

            const limits = this.canvasLimits();
            const oldWidth = Math.max(1, Number(room.canvas_width || 900));
            const oldHeight = Math.max(1, Number(room.canvas_height || 560));
            const nextWidth = this.clampCanvasValue(
                width,
                limits.minWidth,
                limits.maxWidth
            );
            const nextHeight = this.clampCanvasValue(
                height,
                limits.minHeight,
                limits.maxHeight
            );

            if (nextWidth === oldWidth && nextHeight === oldHeight) {
                return;
            }

            this.pushUndoCheckpoint();
            const snapshots = this.canvasObjectSnapshots();

            room.canvas_width = nextWidth;
            room.canvas_height = nextHeight;

            this.repositionObjectsForCanvas(
                oldWidth,
                oldHeight,
                nextWidth,
                nextHeight,
                snapshots
            );

            this.clampObjectsToCanvas();
            this.markDirty();
        },

        setCanvasDimension(axis, value) {
            const room = this.selectedRoom;
            if (!room) return;

            const width = axis === 'width'
                ? value
                : room.canvas_width;
            const height = axis === 'height'
                ? value
                : room.canvas_height;

            this.applyCanvasSize(width, height);
        },

        resizeCanvas(deltaWidth = 0, deltaHeight = 0) {
            const room = this.selectedRoom;
            if (!room) return;

            this.applyCanvasSize(
                Number(room.canvas_width || 900) + Number(deltaWidth || 0),
                Number(room.canvas_height || 560) + Number(deltaHeight || 0)
            );
        },

        setCanvasPreset(width, height) {
            this.applyCanvasSize(width, height);
        },

        clampObjectsToCanvas() {
            const room = this.selectedRoom;
            if (!room) return;

            [...room.beds, ...room.items].forEach(object => {
                const position = this.clampObjectPosition(
                    object,
                    Number(object.x || 0),
                    Number(object.y || 0),
                    Number(room.canvas_width),
                    Number(room.canvas_height)
                );

                object.x = Math.round(position.x);
                object.y = Math.round(position.y);
            });
        },

        startCanvasResize(event) {
            const room = this.selectedRoom;
            if (!this.editMode || !room) return;

            this.stopObjectRotation();

            const map = this.$refs.map;
            const rect = map?.getBoundingClientRect();
            const startWidth = Number(room.canvas_width || 900);
            const startHeight = Number(room.canvas_height || 560);

            this.pushUndoCheckpoint();
            this.clearSelection();
            this.canvasResizeState = {
                startClientX: event.clientX,
                startClientY: event.clientY,
                startWidth,
                startHeight,
                snapshots: this.canvasObjectSnapshots(),
                scaleX: rect?.width
                    ? startWidth / rect.width
                    : 1,
                scaleY: rect?.height
                    ? startHeight / rect.height
                    : 1,
            };

            window.addEventListener('pointermove', this.onCanvasResize);
            window.addEventListener('pointerup', this.stopCanvasResize, { once: true });
        },

        onCanvasResize(event) {
            const state = this.canvasResizeState;
            const room = this.selectedRoom;
            if (!state || !room) return;

            const limits = this.canvasLimits();
            const nextWidth = this.clampCanvasValue(
                state.startWidth
                    + (event.clientX - state.startClientX)
                    * Number(state.scaleX || 1),
                limits.minWidth,
                limits.maxWidth
            );
            const nextHeight = this.clampCanvasValue(
                state.startHeight
                    + (event.clientY - state.startClientY)
                    * Number(state.scaleY || 1),
                limits.minHeight,
                limits.maxHeight
            );

            room.canvas_width = nextWidth;
            room.canvas_height = nextHeight;

            /*
             * Во время pointermove всегда считаем положение предметов от
             * исходного состояния начала drag. Так нет накопления округлений
             * и предметы плавно едут вместе с изменением площади комнаты.
             */
            this.repositionObjectsForCanvas(
                state.startWidth,
                state.startHeight,
                nextWidth,
                nextHeight,
                state.snapshots
            );

            this.clampObjectsToCanvas();
            this.markDirty();
        },

        stopCanvasResize() {
            window.removeEventListener('pointermove', this.onCanvasResize);
            this.canvasResizeState = null;
        },

        cloneLayoutValue(value) {
            /*
             * room.beds / room.items — реактивные Vue Proxy.
             * structuredClone() не умеет клонировать Proxy и бросает
             * DOMException: "Proxy object could not be cloned".
             *
             * Данные схемы JSON-совместимые, поэтому сначала превращаем
             * реактивное значение в обычный объект/массив через JSON.
             */
            if (value === null || value === undefined) {
                return value;
            }

            return clone(value);
        },

        createUndoSnapshot(room = this.selectedRoom) {
            if (!room) {
                return null;
            }

            const snapshot = {
                roomId: String(room.id || ''),
                canvas_width: Number(room.canvas_width || 900),
                canvas_height: Number(room.canvas_height || 560),
                beds: this.cloneLayoutValue(room.beds || []),
                items: this.cloneLayoutValue(room.items || []),
                layoutDirty: Boolean(this.layoutDirty),
            };

            snapshot.signature = JSON.stringify({
                roomId: snapshot.roomId,
                canvas_width: snapshot.canvas_width,
                canvas_height: snapshot.canvas_height,
                beds: snapshot.beds,
                items: snapshot.items,
                layoutDirty: snapshot.layoutDirty,
            });

            return snapshot;
        },

        pushUndoCheckpoint() {
            if (this.historyRestoring || !this.editMode || !this.selectedRoom) {
                return;
            }

            const snapshot = this.createUndoSnapshot();
            if (!snapshot) {
                return;
            }

            const last = this.undoStack[this.undoStack.length - 1];

            if (last?.signature === snapshot.signature) {
                return;
            }

            this.undoStack.push(snapshot);

            if (this.undoStack.length > Number(this.undoLimit || 80)) {
                this.undoStack.splice(
                    0,
                    this.undoStack.length - Number(this.undoLimit || 80)
                );
            }
        },

        clearUndoHistory() {
            this.undoStack = [];
        },

        restoreUndoSnapshot(snapshot) {
            const room = this.selectedRoom;

            if (!room || !snapshot || String(room.id || '') !== snapshot.roomId) {
                return false;
            }

            this.historyRestoring = true;

            try {
                room.canvas_width = Number(snapshot.canvas_width || 900);
                room.canvas_height = Number(snapshot.canvas_height || 560);
                room.beds = this.cloneLayoutValue(snapshot.beds || []);
                room.items = this.cloneLayoutValue(snapshot.items || []);
                this.layoutDirty = Boolean(snapshot.layoutDirty);
                this.clearSelection();
                this.clearAlignmentGuides();
                this.collisionTooltip.visible = false;
                this.closeContextMenu();
            } finally {
                this.historyRestoring = false;
            }

            return true;
        },

        undoLastAction() {
            if (!this.editMode || !this.selectedRoom) {
                return;
            }

            const current = this.createUndoSnapshot();
            const currentSignature = current?.signature || '';

            while (this.undoStack.length) {
                const snapshot = this.undoStack.pop();

                if (!snapshot) {
                    continue;
                }

                if (snapshot.roomId !== String(this.selectedRoom.id || '')) {
                    continue;
                }

                /*
                 * Pointerdown без фактического движения может оставить
                 * checkpoint, идентичный текущей схеме. Его пропускаем,
                 * чтобы Ctrl+Z всегда отменял реальное последнее изменение.
                 */
                if (snapshot.signature === currentSignature) {
                    continue;
                }

                if (this.restoreUndoSnapshot(snapshot)) {
                    this.showNotice('Последнее действие отменено.');
                    return;
                }
            }

            this.showNotice('Нечего отменять.');
        },

        markDirty() {
            this.layoutDirty = true;
        },

        boundaryDragLock(object) {
            if (!this.isBoundaryItem(object) || !this.selectedRoom) {
                return { x: false, y: false };
            }

            const roomWidth = Number(this.selectedRoom.canvas_width || 0);
            const roomHeight = Number(this.selectedRoom.canvas_height || 0);
            const width = Math.max(1, Number(object.width || 1));
            const height = Math.max(1, Number(object.height || 1));
            const centerX = Number(object.x || 0) + width / 2;
            const centerY = Number(object.y || 0) + height / 2;
            const tolerance = 2;

            const vertical =
                Math.abs(centerX) <= tolerance
                || Math.abs(centerX - roomWidth) <= tolerance;
            const horizontal =
                Math.abs(centerY) <= tolerance
                || Math.abs(centerY - roomHeight) <= tolerance;

            return {
                x: vertical,
                y: horizontal,
            };
        },

        selectedDragSnapshots() {
            return this.selectedElements.map(entry => ({
                type: entry.type,
                object: entry.object,
                startX: Number(entry.object?.x || 0),
                startY: Number(entry.object?.y || 0),
                lock: this.boundaryDragLock(entry.object),
            }));
        },

        clampGroupDelta(snapshots, dx, dy) {
            const room = this.selectedRoom;
            if (!room || !snapshots.length) {
                return { dx, dy };
            }

            let minDx = -Infinity;
            let maxDx = Infinity;
            let minDy = -Infinity;
            let maxDy = Infinity;
            let lockX = false;
            let lockY = false;

            snapshots.forEach(snapshot => {
                const bounds = this.objectBoundsForCanvas(
                    snapshot.object,
                    Number(room.canvas_width),
                    Number(room.canvas_height)
                );

                minDx = Math.max(
                    minDx,
                    Number(bounds.minX) - snapshot.startX
                );
                maxDx = Math.min(
                    maxDx,
                    Number(bounds.maxX) - snapshot.startX
                );
                minDy = Math.max(
                    minDy,
                    Number(bounds.minY) - snapshot.startY
                );
                maxDy = Math.min(
                    maxDy,
                    Number(bounds.maxY) - snapshot.startY
                );

                lockX = lockX || Boolean(snapshot.lock?.x);
                lockY = lockY || Boolean(snapshot.lock?.y);
            });

            return {
                dx: lockX
                    ? 0
                    : Math.min(maxDx, Math.max(minDx, Number(dx || 0))),
                dy: lockY
                    ? 0
                    : Math.min(maxDy, Math.max(minDy, Number(dy || 0))),
            };
        },

        startDrag(event, type, object) {
            if (!this.editMode) return;
            this.stopObjectRotation();
            this.closeContextMenu();
            if (event.button !== undefined && event.button !== 0) return;

            const additive = Boolean(
                event.shiftKey || event.ctrlKey || event.metaKey
            );

            if (additive) {
                const selected = this.toggleElementSelection(type, object);
                if (!selected) {
                    return;
                }
            } else if (!this.isSelected(type, object)) {
                this.setSingleSelection(type, object);
            } else {
                this.selectedElement = this.selectedElements.find(
                    entry => this.sameSelectionEntry(entry, type, object)
                ) || { type, object };
            }

            this.clearAlignmentGuides();

            const map = this.$refs.map;
            if (!map || !this.selectedRoom) return;

            const rect = map.getBoundingClientRect();
            const snapshots = this.selectedDragSnapshots();

            this.pushUndoCheckpoint();
            this.dragState = {
                type,
                object,
                startClientX: event.clientX,
                startClientY: event.clientY,
                startX: Number(object.x || 0),
                startY: Number(object.y || 0),
                moved: false,
                rect,
                snapshots,
            };

            window.addEventListener('pointermove', this.onDrag);
            window.addEventListener('pointerup', this.stopDrag, { once: true });
        },

        onDrag(event) {
            const state = this.dragState;
            const room = this.selectedRoom;
            if (!state || !room) return;

            const rawDx = (
                event.clientX - state.startClientX
            ) * room.canvas_width / state.rect.width;
            const rawDy = (
                event.clientY - state.startClientY
            ) * room.canvas_height / state.rect.height;

            if (Math.abs(rawDx) > 2 || Math.abs(rawDy) > 2) {
                state.moved = true;
            }

            const snapshots = Array.isArray(state.snapshots)
                && state.snapshots.length
                ? state.snapshots
                : [{
                    type: state.type,
                    object: state.object,
                    startX: state.startX,
                    startY: state.startY,
                    lock: this.boundaryDragLock(state.object),
                }];

            if (snapshots.length === 1) {
                let position = this.clampObjectPosition(
                    state.object,
                    state.startX + rawDx,
                    state.startY + rawDy,
                    Number(room.canvas_width),
                    Number(room.canvas_height)
                );

                if (
                    state.type === 'item'
                    && this.isBoundaryItem(state.object)
                ) {
                    position = this.snapBoundaryObject(
                        state.object,
                        position.x,
                        position.y
                    );
                }

                position = this.alignPositionToObjects(
                    state.object,
                    position.x,
                    position.y
                );

                /*
                 * "Липучка" для коек и тумбочек работает поверх обычного
                 * выравнивания: если край подходит к краю другой койки /
                 * тумбочки, предмет мягко примагничивается вплотную.
                 */
                position = this.snapStickyPosition(
                    state.object,
                    position.x,
                    position.y
                );

                const collision = this.findColumnCollision(
                    state.object,
                    position.x,
                    position.y,
                    state.object
                );

                if (collision) {
                    this.showColumnCollisionTooltip(collision);
                    return;
                }

                state.object.x = Math.round(position.x);
                state.object.y = Math.round(position.y);
                this.markDirty();
                return;
            }

            let delta = this.clampGroupDelta(
                snapshots,
                rawDx,
                rawDy
            );

            /*
             * Группу снапаем по ведущему объекту, но исключаем остальные
             * выбранные элементы из целей выравнивания. Получившуюся
             * поправку применяем ко всей группе — расстояния между
             * объектами остаются неизменными.
             */
            const selectedObjects = snapshots.map(item => item.object);
            let leadPosition = this.alignPositionToObjects(
                state.object,
                state.startX + delta.dx,
                state.startY + delta.dy,
                selectedObjects
            );

            leadPosition = this.snapStickyPosition(
                state.object,
                leadPosition.x,
                leadPosition.y,
                selectedObjects
            );

            delta = this.clampGroupDelta(
                snapshots,
                Number(leadPosition.x) - state.startX,
                Number(leadPosition.y) - state.startY
            );

            const collision = this.findGroupColumnCollision(
                snapshots,
                delta.dx,
                delta.dy
            );

            if (collision) {
                this.showColumnCollisionTooltip(collision);
                return;
            }

            snapshots.forEach(snapshot => {
                snapshot.object.x = Math.round(
                    snapshot.startX + delta.dx
                );
                snapshot.object.y = Math.round(
                    snapshot.startY + delta.dy
                );
            });

            this.markDirty();
        },

        objectResizeHandlesFor(type, object) {
            /*
             * Вход и окно имеют фиксированную толщину/высоту. Поэтому
             * прямо на канвасе для них доступны только локальные левая
             * и правая стороны: длину можно менять, толщину — нельзя.
             * При повороте на боковую стену эти ручки визуально тоже
             * поворачиваются вместе с элементом.
             */
            if (
                type === 'item'
                && ['entrance', 'window'].includes(
                    String(object?.type || '')
                )
            ) {
                return OBJECT_RESIZE_HANDLES.filter(handle => (
                    ['e', 'w'].includes(handle.direction)
                ));
            }

            return OBJECT_RESIZE_HANDLES;
        },

        isCanvasResizable(type, object) {
            if (type === 'bed') {
                return true;
            }

            if (type !== 'item' || !object) {
                return false;
            }

            /*
             * Розетка и тумбочка имеют фиксированный размер. Остальные
             * предметы и архитектурные элементы можно растягивать прямо
             * на схеме.
             */
            return [
                'cabinet',
                'column',
                'entrance',
                'window',
            ].includes(String(object.type || ''));
        },

        objectResizeLimits(type, object) {
            if (type === 'bed') {
                return {
                    minWidth: 64,
                    minHeight: 38,
                    maxWidth: 360,
                    maxHeight: 240,
                };
            }

            const itemType = String(object?.type || '');

            if (itemType === 'window') {
                return {
                    minWidth: 44,
                    minHeight: 12,
                    maxWidth: 420,
                    maxHeight: 90,
                };
            }

            if (itemType === 'entrance') {
                return {
                    minWidth: 44,
                    minHeight: 14,
                    maxWidth: 280,
                    maxHeight: 120,
                };
            }

            if (itemType === 'cabinet') {
                return {
                    minWidth: 42,
                    minHeight: 32,
                    maxWidth: 360,
                    maxHeight: 300,
                };
            }

            if (itemType === 'column') {
                return {
                    minWidth: 30,
                    minHeight: 30,
                    maxWidth: 260,
                    maxHeight: 260,
                };
            }

            return {
                minWidth: 30,
                minHeight: 30,
                maxWidth: 240,
                maxHeight: 240,
            };
        },

        clampObjectSize(value, min, max) {
            return Math.min(
                Number(max),
                Math.max(Number(min), Number(value || min))
            );
        },

        startObjectRotation(event, type, object, corner = 'se') {
            if (
                !this.editMode
                || !this.selectedRoom
                || !object
                || !this.isRotatableElement(type, object)
            ) {
                return;
            }

            if (event.button !== undefined && event.button !== 0) {
                return;
            }

            const map = this.$refs.map;
            const rect = map?.getBoundingClientRect();

            if (!rect?.width || !rect?.height) {
                return;
            }

            this.closeContextMenu();
            this.stopDrag();
            this.stopObjectResize();
            this.stopObjectRotation();
            this.selectElement(type, object);
            this.clearAlignmentGuides();

            const canvasWidth = Math.max(
                1,
                Number(this.selectedRoom.canvas_width || 1)
            );
            const canvasHeight = Math.max(
                1,
                Number(this.selectedRoom.canvas_height || 1)
            );
            const objectWidth = Math.max(
                1,
                Number(object.width || 1)
            );
            const objectHeight = Math.max(
                1,
                Number(object.height || 1)
            );

            const centerClientX = (
                rect.left
                + (
                    Number(object.x || 0) + objectWidth / 2
                ) * rect.width / canvasWidth
            );
            const centerClientY = (
                rect.top
                + (
                    Number(object.y || 0) + objectHeight / 2
                ) * rect.height / canvasHeight
            );

            const startPointerAngle = Math.atan2(
                event.clientY - centerClientY,
                event.clientX - centerClientX
            );

            this.pushUndoCheckpoint();
            this.objectRotationState = {
                type,
                object,
                corner: String(corner || 'se'),
                startClientX: event.clientX,
                startClientY: event.clientY,
                centerClientX,
                centerClientY,
                startPointerAngle,
                lastPointerAngle: startPointerAngle,
                accumulatedDelta: 0,
                startRotation: this.normalizedRotation(object),
                lastValidRotation: this.normalizedRotation(object),
                startX: Number(object.x || 0),
                startY: Number(object.y || 0),
            };

            window.addEventListener(
                'pointermove',
                this.onObjectRotation
            );
            window.addEventListener(
                'pointerup',
                this.stopObjectRotation,
                { once: true }
            );
            window.addEventListener(
                'pointercancel',
                this.stopObjectRotation,
                { once: true }
            );
        },

        onObjectRotation(event) {
            const state = this.objectRotationState;
            const room = this.selectedRoom;

            if (!state || !room) {
                return;
            }

            const currentAngle = Math.atan2(
                event.clientY - state.centerClientY,
                event.clientX - state.centerClientX
            );

            let delta = currentAngle - state.lastPointerAngle;

            /*
             * Переход через -180°/180° не должен давать скачок на 360°.
             * При этом накопление позволяет сделать полный оборот и более.
             */
            if (delta > Math.PI) {
                delta -= Math.PI * 2;
            } else if (delta < -Math.PI) {
                delta += Math.PI * 2;
            }

            state.accumulatedDelta += delta;
            state.lastPointerAngle = currentAngle;

            const rawRotation = (
                state.startRotation
                + state.accumulatedDelta * 180 / Math.PI
            );
            const rotation = (
                ((rawRotation % 360) + 360) % 360
            );

            const probe = {
                ...state.object,
                rotation,
                x: state.startX,
                y: state.startY,
            };

            const position = this.clampObjectPosition(
                probe,
                state.startX,
                state.startY,
                Number(room.canvas_width),
                Number(room.canvas_height)
            );

            probe.x = Math.round(position.x * 100) / 100;
            probe.y = Math.round(position.y * 100) / 100;

            const collision = this.findColumnCollision(
                probe,
                probe.x,
                probe.y,
                state.object
            );

            if (collision) {
                this.showColumnCollisionTooltip(collision);
                return;
            }

            state.lastValidRotation = rotation;
            state.object.rotation = Math.round(rotation * 100) / 100;
            state.object.x = probe.x;
            state.object.y = probe.y;
            this.markDirty();
        },

        stopObjectRotation() {
            window.removeEventListener(
                'pointermove',
                this.onObjectRotation
            );
            this.objectRotationState = null;
        },

        resizeCursor(direction, object) {
            const vectors = {
                n: [0, -1],
                ne: [1, -1],
                e: [1, 0],
                se: [1, 1],
                s: [0, 1],
                sw: [-1, 1],
                w: [-1, 0],
                nw: [-1, -1],
            };
            const vector = vectors[String(direction || 'se')] || vectors.se;
            const angle = this.normalizedRotation(object) * Math.PI / 180;
            const cos = Math.cos(angle);
            const sin = Math.sin(angle);
            const x = Math.round(vector[0] * cos - vector[1] * sin);
            const y = Math.round(vector[0] * sin + vector[1] * cos);

            if (x === 0) {
                return 'ns-resize';
            }

            if (y === 0) {
                return 'ew-resize';
            }

            return x * y > 0 ? 'nwse-resize' : 'nesw-resize';
        },

        startObjectResize(event, type, object, direction = 'se') {
            if (
                !this.editMode
                || !this.selectedRoom
                || !object
                || !this.isCanvasResizable(type, object)
            ) {
                return;
            }

            if (
                type === 'item'
                && ['entrance', 'window'].includes(
                    String(object?.type || '')
                )
                && !['e', 'w'].includes(String(direction || ''))
            ) {
                return;
            }

            if (event.button !== undefined && event.button !== 0) {
                return;
            }

            const map = this.$refs.map;
            const rect = map?.getBoundingClientRect();

            if (!rect?.width || !rect?.height) {
                return;
            }

            this.stopDrag();
            this.stopObjectResize();
            this.stopObjectRotation();
            this.selectElement(type, object);
            this.clearAlignmentGuides();

            const startWidth = Math.max(1, Number(object.width || 1));
            const startHeight = Math.max(1, Number(object.height || 1));
            const startX = Number(object.x || 0);
            const startY = Number(object.y || 0);

            this.pushUndoCheckpoint();
            this.objectResizeState = {
                type,
                object,
                direction: String(direction || 'se'),
                startClientX: event.clientX,
                startClientY: event.clientY,
                startWidth,
                startHeight,
                startX,
                startY,
                startCenterX: startX + startWidth / 2,
                startCenterY: startY + startHeight / 2,
                rotation: this.normalizedRotation(object),
                scaleX: Number(this.selectedRoom.canvas_width) / rect.width,
                scaleY: Number(this.selectedRoom.canvas_height) / rect.height,
            };

            window.addEventListener(
                'pointermove',
                this.onObjectResize
            );
            window.addEventListener(
                'pointerup',
                this.stopObjectResize,
                { once: true }
            );
        },

        onObjectResize(event) {
            const state = this.objectResizeState;
            const room = this.selectedRoom;

            if (!state || !room) {
                return;
            }

            /*
             * Переводим движение указателя в локальные оси объекта.
             * Поэтому любая сторона и угол работают корректно даже
             * после поворота койки или шкафа.
             */
            const dx = (
                event.clientX - state.startClientX
            ) * Number(state.scaleX || 1);
            const dy = (
                event.clientY - state.startClientY
            ) * Number(state.scaleY || 1);
            const angle = Number(state.rotation || 0) * Math.PI / 180;
            const cos = Math.cos(angle);
            const sin = Math.sin(angle);
            const localDx = dx * cos + dy * sin;
            const localDy = -dx * sin + dy * cos;
            const direction = String(state.direction || 'se');
            const directionX = direction.includes('e')
                ? 1
                : direction.includes('w')
                    ? -1
                    : 0;
            const directionY = direction.includes('s')
                ? 1
                : direction.includes('n')
                    ? -1
                    : 0;
            const limits = this.objectResizeLimits(
                state.type,
                state.object
            );

            const isFixedHeightArchitectural = (
                state.type === 'item'
                && ['entrance', 'window'].includes(
                    String(state.object?.type || '')
                )
            );

            const widthDelta = directionX === 0
                ? 0
                : localDx * directionX;
            const heightDelta = isFixedHeightArchitectural || directionY === 0
                ? 0
                : localDy * directionY;

            const nextWidth = directionX === 0
                ? state.startWidth
                : Math.round(
                    this.clampObjectSize(
                        state.startWidth + widthDelta,
                        limits.minWidth,
                        Math.min(
                            limits.maxWidth,
                            Number(room.canvas_width) * 1.5
                        )
                    )
                );
            const nextHeight = isFixedHeightArchitectural || directionY === 0
                ? state.startHeight
                : Math.round(
                    this.clampObjectSize(
                        state.startHeight + heightDelta,
                        limits.minHeight,
                        Math.min(
                            limits.maxHeight,
                            Number(room.canvas_height) * 1.5
                        )
                    )
                );

            /*
             * При растягивании фиксируем противоположную сторону.
             * Центр смещается только на половину фактического изменения
             * размера, затем этот сдвиг переводится обратно в координаты
             * канваса с учётом текущего поворота.
             */
            const localCenterShiftX = directionX === 0
                ? 0
                : directionX * (nextWidth - state.startWidth) / 2;
            const localCenterShiftY = isFixedHeightArchitectural || directionY === 0
                ? 0
                : directionY * (nextHeight - state.startHeight) / 2;
            const worldCenterShiftX = (
                localCenterShiftX * cos
                - localCenterShiftY * sin
            );
            const worldCenterShiftY = (
                localCenterShiftX * sin
                + localCenterShiftY * cos
            );

            const object = state.object;
            const probe = {
                ...object,
                width: nextWidth,
                height: nextHeight,
                x: (
                    state.startCenterX
                    + worldCenterShiftX
                    - nextWidth / 2
                ),
                y: (
                    state.startCenterY
                    + worldCenterShiftY
                    - nextHeight / 2
                ),
            };

            let clampedPosition = this.clampObjectPosition(
                probe,
                probe.x,
                probe.y,
                Number(room.canvas_width),
                Number(room.canvas_height)
            );

            if (
                state.type === 'item'
                && this.isBoundaryItem(probe)
            ) {
                clampedPosition = this.snapBoundaryObject(
                    probe,
                    clampedPosition.x,
                    clampedPosition.y
                );
            }

            probe.x = Math.round(clampedPosition.x);
            probe.y = Math.round(clampedPosition.y);

            const collision = this.findColumnCollision(
                probe,
                probe.x,
                probe.y,
                object
            );

            if (collision) {
                this.showColumnCollisionTooltip(collision);
                return;
            }

            object.width = probe.width;
            object.height = probe.height;
            object.rotation = probe.rotation;
            object.x = probe.x;
            object.y = probe.y;
            this.markDirty();
        },

        stopObjectResize() {
            window.removeEventListener(
                'pointermove',
                this.onObjectResize
            );
            this.objectResizeState = null;
        },

        stopDrag() {
            window.removeEventListener('pointermove', this.onDrag);

            const moved = Boolean(this.dragState?.moved);
            this.dragState = null;
            this.clearAlignmentGuides();

            /*
             * После реального перетаскивания браузер может сгенерировать click
             * уже по canvas. Без блокировки этот click вызывает clearSelection(),
             * и только что перемещённый объект теряет выделение.
             */
            if (moved) {
                this.suppressCanvasClick = true;
                window.setTimeout(() => {
                    this.suppressCanvasClick = false;
                }, 0);
            }
        },

        handleBedClick(event, bed) {
            if (this.editMode || !this.patientPlacementEnabled) {
                return;
            }

            if (bed.occupied && bed.patient?.medical_card_url) {
                window.location.href = bed.patient.medical_card_url;
                return;
            }

            if (!bed.id) {
                this.showNotice('Сначала сохраните схему палаты.');
                return;
            }

            this.openAssignModal(bed);
        },

        setSelectedRotation(value) {
            if (!this.selectedElement || !this.selectedRoom) return false;

            const object = this.selectedElement.object;
            const normalized = (
                ((Number(value || 0) % 360) + 360) % 360
            );
            const probe = {
                ...object,
                rotation: Math.round(normalized * 100) / 100,
            };

            const position = this.clampObjectPosition(
                probe,
                Number(probe.x || 0),
                Number(probe.y || 0),
                Number(this.selectedRoom.canvas_width),
                Number(this.selectedRoom.canvas_height)
            );

            probe.x = Math.round(position.x);
            probe.y = Math.round(position.y);

            const collision = this.findColumnCollision(
                probe,
                probe.x,
                probe.y,
                object
            );

            if (collision) {
                this.showColumnCollisionTooltip(collision);
                return false;
            }

            this.pushUndoCheckpoint();
            object.rotation = probe.rotation;
            object.x = probe.x;
            object.y = probe.y;
            this.markDirty();

            return true;
        },

        rotateSelectionTo(rotation) {
            if (!this.canRotateSingleSelection) {
                return false;
            }

            const entry = this.selectedElements[0];
            const object = entry.object;
            const room = this.selectedRoom;

            if (!room || !object) {
                return false;
            }

            const nextRotation = (
                (Number(rotation || 0) % 360) + 360
            ) % 360;

            if (
                Math.abs(
                    this.normalizedRotation(object) - nextRotation
                ) < 0.001
            ) {
                return true;
            }

            const probe = {
                ...object,
                rotation: nextRotation,
            };
            const position = this.clampObjectPosition(
                probe,
                Number(object.x || 0),
                Number(object.y || 0),
                Number(room.canvas_width),
                Number(room.canvas_height)
            );

            probe.x = Math.round(Number(position.x) * 100) / 100;
            probe.y = Math.round(Number(position.y) * 100) / 100;

            const collision = this.findColumnCollision(
                probe,
                probe.x,
                probe.y,
                object
            );

            if (collision) {
                this.showColumnCollisionTooltip(collision);
                return false;
            }

            this.pushUndoCheckpoint();
            object.rotation = Math.round(nextRotation * 100) / 100;
            object.x = probe.x;
            object.y = probe.y;
            this.markDirty();
            return true;
        },

        rotateSelectedByQuarter() {
            if (!this.canRotateSingleSelection) {
                return false;
            }

            const object = this.selectedElements[0].object;
            return this.rotateSelectionTo(
                this.normalizedRotation(object) + 90
            );
        },

        snapSelectedRotationToQuarter() {
            if (!this.canRotateSingleSelection) {
                return false;
            }

            const object = this.selectedElements[0].object;
            const current = this.normalizedRotation(object);
            const snapped = (Math.round(current / 90) * 90) % 360;
            return this.rotateSelectionTo(snapped);
        },

        rotateSelectedByQuarterFromContextMenu() {
            this.rotateSelectedByQuarter();
            this.closeContextMenu();
        },

        snapSelectedRotationFromContextMenu() {
            this.snapSelectedRotationToQuarter();
            this.closeContextMenu();
        },

        isKeyboardEditingTarget(target) {
            if (!(target instanceof Element)) {
                return false;
            }

            return Boolean(
                target.closest(
                    'input, textarea, select, [contenteditable="true"], [contenteditable=""]'
                )
            );
        },

        keyboardShortcutKey(event) {
            const code = String(event?.code || '');

            if (/^Key[A-Z]$/.test(code)) {
                return code.slice(3).toLowerCase();
            }

            return String(event?.key || '').toLowerCase();
        },

        onCanvasKeydown(event) {
            if (
                !this.editMode
                || !this.selectedRoom
                || this.roomModal.open
                || this.assignModal.open
                || this.isKeyboardEditingTarget(event.target)
            ) {
                return;
            }

            /*
             * Используем event.code, а не только event.key. Поэтому
             * Ctrl+C / Ctrl+X / Ctrl+V / Ctrl+Z работают даже при
             * включённой русской раскладке клавиатуры.
             */
            const key = this.keyboardShortcutKey(event);
            const modifier = Boolean(event.ctrlKey || event.metaKey);

            if (key === 'escape') {
                this.pendingPlacementType = '';
                this.closeContextMenu();
                return;
            }

            /*
             * Ctrl+A — +90° от текущего угла: 40° -> 130°.
             * Ctrl+Shift+A — к ближайшему кратному 90° относительно нуля:
             * 40° -> 0°, 51° -> 90°.
             * Без одного выбранного вращаемого объекта ничего не вращается;
             * сам canvas никогда не является объектом выделения.
             */
            if (modifier && key === 'a') {
                event.preventDefault();

                if (event.shiftKey) {
                    this.snapSelectedRotationToQuarter();
                } else {
                    this.rotateSelectedByQuarter();
                }

                this.closeContextMenu();
                return;
            }

            if (modifier && key === 'z' && !event.shiftKey) {
                event.preventDefault();
                this.undoLastAction();
                this.closeContextMenu();
                return;
            }

            if (modifier && key === 'c') {
                if (!this.selectedElements.length) return;
                event.preventDefault();
                this.copySelected();
                this.closeContextMenu();
                return;
            }

            if (modifier && key === 'x') {
                if (!this.selectedElements.length) return;
                event.preventDefault();
                this.cutSelected();
                this.closeContextMenu();
                return;
            }

            if (modifier && key === 'v') {
                if (!this.clipboardHasEntries) return;
                event.preventDefault();
                this.pasteClipboard();
                this.closeContextMenu();
                return;
            }

            if (key !== 'delete') {
                return;
            }

            /*
             * Сам canvas никогда не попадает в selectedElements, поэтому
             * Delete без выделенных объектов ничего не делает и не может
             * удалить рабочую область палаты.
             */
            if (!this.selectedElements.length) {
                return;
            }

            event.preventDefault();
            this.removeSelected();
            this.closeContextMenu();
        },

        removeSelected() {
            if (!this.selectedRoom || !this.selectedElements.length) return;

            const selected = [...this.selectedElements];
            const hasOccupiedBed = selected.some(entry => (
                entry.type === 'bed'
                && entry.object?.occupied
            ));

            if (hasOccupiedBed) {
                window.alert(
                    'Занятую койку нельзя удалить со схемы. Сначала освободите её.'
                );
                return;
            }

            this.pushUndoCheckpoint();

            selected.forEach(entry => {
                const collection = entry.type === 'bed'
                    ? this.selectedRoom.beds
                    : this.selectedRoom.items;
                const index = collection.indexOf(entry.object);

                if (index >= 0) {
                    collection.splice(index, 1);
                }
            });

            this.clearSelection();
            this.markDirty();
        },

        async saveLayout() {
            const room = this.selectedRoom;
            if (!room || this.savingLayout) return;

            const collision = this.findAnyColumnCollision();

            if (collision) {
                this.showColumnCollisionTooltip(collision);
                this.error = collision.movingColumn
                    ? 'Колонна не должна пересекаться с другими элементами.'
                    : 'На колонну нельзя размещать другие элементы.';
                return;
            }

            this.savingLayout = true;
            this.error = '';

            try {
                await this.request(
                    this.replaceToken(this.roomLayoutUrlTemplate, '__ROOM__', room.id),
                    {
                        method: 'POST',
                        body: JSON.stringify({
                            canvas_width: Math.round(Number(room.canvas_width || 900)),
                            canvas_height: Math.round(Number(room.canvas_height || 560)),
                            beds: room.beds.map(bed => {
                                const storagePosition = this.bedStoragePosition(
                                    bed,
                                    room
                                );

                                return {
                                    id: Number.isFinite(Number(bed.id)) && bed.id ? Number(bed.id) : null,
                                    number: String(bed.number || '').trim(),
                                    label: String(bed.label || '').trim(),
                                    x: Math.round(storagePosition.x),
                                    y: Math.round(storagePosition.y),
                                    width: Math.round(Number(bed.width || 150)),
                                    height: Math.round(Number(bed.height || 74)),
                                    rotation: Number(
                                        Number(bed.rotation || 0).toFixed(2)
                                    ),
                                    enabled: Boolean(bed.enabled),
                                };
                            }),
                            items: room.items.map(item => ({
                                type: item.type,
                                label: String(item.label || '').trim(),
                                x: Math.round(Number(item.x || 0)),
                                y: Math.round(Number(item.y || 0)),
                                width: Math.round(Number(item.width || 90)),
                                height: Math.round(Number(item.height || 90)),
                                rotation: Number(
                                    Number(item.rotation || 0).toFixed(2)
                                ),
                            })),
                        }),
                    }
                );

                this.showNotice('Карта палаты сохранена.');
                await this.load(room.id);
                this.editMode = true;
            } catch (error) {
                this.error = error.message || 'Не удалось сохранить схему палаты.';
            } finally {
                this.savingLayout = false;
            }
        },

        openRoomModal(room = null) {
            this.roomModal = {
                open: true,
                id: room?.id || null,
                department_id: room ? String(room.department_id) : (this.selectedDepartmentId || ''),
                number: room?.number || '',
                name: room?.name || '',
                floor: room?.floor || '',
                sort_order: room?.sort_order || 0,
                is_supervision: Boolean(room?.is_supervision),
                saving: false,
                error: '',
            };
        },

        closeRoomModal() {
            this.roomModal.open = false;
        },

        async saveRoom() {
            const modal = this.roomModal;
            if (!modal.department_id || !String(modal.number).trim()) {
                modal.error = 'Укажите отделение и номер палаты.';
                return;
            }

            modal.saving = true;
            modal.error = '';

            try {
                const url = modal.id
                    ? this.replaceToken(this.roomUpdateUrlTemplate, '__ROOM__', modal.id)
                    : this.roomStoreUrl;

                const payload = await this.request(url, {
                    method: 'POST',
                    body: JSON.stringify({
                        department_id: Number(modal.department_id),
                        number: String(modal.number).trim(),
                        name: String(modal.name || '').trim(),
                        floor: String(modal.floor || '').trim(),
                        sort_order: Number(modal.sort_order || 0),
                        is_supervision: Boolean(modal.is_supervision),
                        enabled: true,
                    }),
                });

                const roomId = payload.room_id || modal.id;
                this.closeRoomModal();
                this.showNotice(payload.message || 'Палата сохранена.');
                await this.load(roomId);
            } catch (error) {
                modal.error = error.message || 'Не удалось сохранить палату.';
            } finally {
                modal.saving = false;
            }
        },

        async deleteRoom() {
            const modal = this.roomModal;
            if (!modal.id) return;
            if (!window.confirm(`Удалить палату №${modal.number}?`)) return;

            modal.saving = true;
            modal.error = '';

            try {
                const payload = await this.request(
                    this.replaceToken(this.roomDeleteUrlTemplate, '__ROOM__', modal.id),
                    { method: 'POST', body: JSON.stringify({}) }
                );
                this.closeRoomModal();
                this.selectedRoomId = '';
                this.showNotice(payload.message || 'Палата удалена.');
                await this.load();
            } catch (error) {
                modal.error = error.message || 'Не удалось удалить палату.';
            } finally {
                modal.saving = false;
            }
        },

        openAssignModal(bed) {
            if (!this.patientPlacementEnabled) return;

            this.assignModal = {
                open: true,
                bed,
                query: '',
                results: [],
                patient: null,
                searching: false,
                saving: false,
                error: '',
                timer: null,
                controller: null,
            };

            this.$nextTick(() => {
                this.$refs.assignPatientSearchInput?.focus();
            });
        },

        closeAssignModal() {
            window.clearTimeout(this.assignModal.timer);
            this.assignModal.controller?.abort();
            this.assignModal.open = false;
        },

        selectAssignPatient(patient) {
            this.assignModal.patient = patient;
            this.assignModal.query = patient.name || '';
            this.assignModal.results = [];
            this.assignModal.error = '';
            window.clearTimeout(this.assignModal.timer);
            this.assignModal.controller?.abort();
        },

        clearAssignPatientSearch() {
            window.clearTimeout(this.assignModal.timer);
            this.assignModal.controller?.abort();
            this.assignModal.query = '';
            this.assignModal.results = [];
            this.assignModal.patient = null;
            this.assignModal.searching = false;
            this.assignModal.error = '';

            this.$nextTick(() => {
                this.$refs.assignPatientSearchInput?.focus();
            });
        },

        schedulePatientSearch() {
            window.clearTimeout(this.assignModal.timer);
            this.assignModal.patient = null;
            this.assignModal.error = '';

            const query = String(this.assignModal.query || '').trim();
            if (query.length < 2) {
                this.assignModal.results = [];
                return;
            }

            this.assignModal.timer = window.setTimeout(() => this.searchPatients(query), 250);
        },

        async searchPatients(query) {
            this.assignModal.controller?.abort();
            this.assignModal.controller = new AbortController();
            this.assignModal.searching = true;

            try {
                const url = new URL(this.patientSearchUrl, window.location.origin);
                url.searchParams.set('q', query);
                if (this.assignModal.bed?.id) {
                    url.searchParams.set('bed_id', this.assignModal.bed.id);
                }
                const payload = await this.request(url.toString(), {
                    method: 'GET',
                    signal: this.assignModal.controller.signal,
                });
                this.assignModal.results = payload.items || [];
            } catch (error) {
                if (error.name !== 'AbortError') {
                    this.assignModal.error = error.message || 'Не удалось найти пациента.';
                }
            } finally {
                this.assignModal.searching = false;
            }
        },

        async assignPatient() {
            if (!this.patientPlacementEnabled) return;

            const modal = this.assignModal;
            if (!modal.bed?.id || !modal.patient?.id) return;
            modal.saving = true;
            modal.error = '';

            try {
                const payload = await this.request(
                    this.replaceToken(this.bedAssignUrlTemplate, '__BED__', modal.bed.id),
                    {
                        method: 'POST',
                        body: JSON.stringify({ patient_id: modal.patient.id }),
                    }
                );
                this.closeAssignModal();
                this.showNotice(payload.message || 'Пациент размещён.');
                await this.load(this.selectedRoomId);
            } catch (error) {
                modal.error = error.message || 'Не удалось разместить пациента.';
            } finally {
                modal.saving = false;
            }
        },

        async releaseSelectedBed() {
            if (!this.patientPlacementEnabled) return;

            const bed = this.selectedElement?.object;
            if (!bed?.id || !bed.occupied) return;
            if (!window.confirm(`Освободить койку ${bed.number}?`)) return;

            try {
                const payload = await this.request(
                    this.replaceToken(this.bedReleaseUrlTemplate, '__BED__', bed.id),
                    { method: 'POST', body: JSON.stringify({}) }
                );
                this.showNotice(payload.message || 'Койка освобождена.');
                await this.load(this.selectedRoomId);
                this.editMode = true;
            } catch (error) {
                this.error = error.message || 'Не удалось освободить койку.';
            }
        },

        showNotice(message) {
            this.notice = message;
            window.clearTimeout(this.noticeTimer);
            this.noticeTimer = window.setTimeout(() => { this.notice = ''; }, 3200);
        },
    },
});
</script>

<style scoped>
.hrm-page{--teal:#078c9b;--teal-dark:#066f7b;--teal-soft:#eaf8fa;--danger:#d9485f;--danger-soft:#fff0f2;--line:#dfe7ed;--text:#1d2b3d;--muted:#768498;color:var(--text)}
.hrm-head{display:flex;align-items:flex-start;justify-content:space-between;gap:20px;margin-bottom:18px}.hrm-head h1{margin:2px 0 6px;font-size:30px;font-weight:850}.hrm-head p{max-width:820px;margin:0;color:var(--muted);font-size:13px;line-height:1.55}.hrm-kicker{color:#6f8196;font-size:11px;font-weight:750}
.hrm-btn,.hrm-icon-btn{display:inline-flex;align-items:center;justify-content:center;gap:7px;border:1px solid #d7e1e8;border-radius:12px;background:#fff;color:#324256;font-weight:800;transition:.15s ease}.hrm-btn{min-height:40px;padding:8px 13px;font-size:11px}.hrm-btn:hover{border-color:#acd9df;background:#f2fbfc;color:var(--teal-dark)}.hrm-btn--primary{border-color:var(--teal);background:var(--teal);color:#fff}.hrm-btn--primary:hover{background:var(--teal-dark);color:#fff}.hrm-btn--danger{border-color:#f0c5cb;color:#bd3247;background:#fff7f8}.hrm-icon-btn{width:34px;height:34px;padding:0}.hrm-btn.is-active{border-color:var(--teal);background:var(--teal-soft);color:var(--teal-dark)}
.hrm-stats{display:grid;grid-template-columns:repeat(4,minmax(0,1fr));gap:12px;margin-bottom:16px}.hrm-stats article{display:flex;align-items:center;gap:12px;padding:14px 16px;border:1px solid var(--line);border-radius:16px;background:#fff}.hrm-stat-icon{display:grid;width:40px;height:40px;place-items:center;border-radius:12px;background:var(--teal-soft);color:var(--teal)}.hrm-stats strong,.hrm-stats small{display:block}.hrm-stats strong{font-size:20px}.hrm-stats small{margin-top:2px;color:var(--muted);font-size:10px;font-weight:700}.hrm-stats article.is-danger .hrm-stat-icon{background:var(--danger-soft);color:var(--danger)}
.hrm-alert{display:flex;align-items:center;gap:9px;margin-bottom:14px;padding:11px 13px;border-radius:12px;font-size:11px}.hrm-alert button{margin-left:auto;border:0;background:transparent;font-weight:800}.hrm-alert--error{background:#fff0f2;color:#a92b40}.hrm-alert--success{background:#edf9f4;color:#227554}.hrm-loading{display:grid;min-height:118px;place-items:center;padding:28px 40px;border:1px solid var(--line);border-radius:18px;background:#fff;color:var(--muted);box-shadow:0 12px 34px rgba(15,23,42,.04)}.hrm-loading__content{width:min(520px,100%);text-align:center}.hrm-loading__title{color:#536276;font-size:12px;font-weight:750}.hrm-loading__progress{height:8px;margin-top:14px;overflow:hidden;border-radius:999px;background:#e7eef2;box-shadow:inset 0 1px 2px rgba(15,23,42,.06)}.hrm-loading__bar{height:100%;border-radius:inherit;background:linear-gradient(90deg,var(--teal),#35b8c5);transition:width .18s ease}.hrm-loading__percent{margin-top:7px;color:#8390a0;font-size:9px;font-weight:800}
.hrm-layout{display:grid;grid-template-columns:260px minmax(0,1fr);gap:16px;align-items:start}.hrm-sidebar,.hrm-workspace{border:1px solid var(--line);border-radius:18px;background:#fff;box-shadow:0 12px 34px rgba(15,23,42,.04)}.hrm-sidebar{padding:14px;position:sticky;top:82px}.hrm-sidebar__head{display:flex;align-items:center;justify-content:space-between;margin-bottom:12px}.hrm-sidebar__head strong{font-size:14px}.hrm-sidebar__head span{padding:3px 7px;border-radius:999px;background:var(--teal-soft);color:var(--teal-dark);font-size:9px;font-weight:800}.hrm-filter-label,.hrm-label,label>span{display:block;margin-bottom:5px;color:#536276;font-size:10px;font-weight:800}.hrm-control{width:100%;min-height:40px;padding:8px 11px;border:1px solid #d5e0e7;border-radius:11px;background:#fff;color:#243548;font-size:12px;outline:none}.hrm-control:focus{border-color:#5bb8c3;box-shadow:0 0 0 3px rgba(7,140,155,.08)}
.hrm-room-list{display:grid;gap:7px;margin-top:12px;max-height:620px;overflow:auto}.hrm-room-row{display:flex;align-items:center;gap:9px;width:100%;padding:10px;border:1px solid #e2e9ee;border-radius:12px;background:#fff;color:#2c3d51;text-align:left}.hrm-room-row:hover,.hrm-room-row.is-active{border-color:#80c9d1;background:#f1fbfc}.hrm-room-row__icon{display:grid;width:34px;height:34px;flex:0 0 34px;place-items:center;border-radius:10px;background:var(--teal-soft);color:var(--teal)}.hrm-room-row__copy{min-width:0;flex:1}.hrm-room-row__copy strong,.hrm-room-row__copy small{display:block}.hrm-room-row__copy strong{font-size:11px}.hrm-room-row__copy small{margin-top:2px;overflow:hidden;color:#8390a1;font-size:8px;text-overflow:ellipsis;white-space:nowrap}.hrm-room-row__count{padding:3px 6px;border-radius:999px;background:#eff3f6;color:#637286;font-size:8px;font-weight:850}
.hrm-room-pagination{display:flex;align-items:center;justify-content:space-between;gap:8px;margin-top:10px;padding-top:10px;border-top:1px solid #edf1f4}.hrm-room-pagination__meta{color:#7a8797;font-size:9px;font-weight:750;white-space:nowrap}.hrm-pagination-tooltip-trigger{position:relative;display:inline-flex;align-items:center;min-height:24px;margin:-4px;padding:4px;border-radius:7px;cursor:help;outline:none;transition:.15s ease}.hrm-pagination-tooltip-trigger:hover,.hrm-pagination-tooltip-trigger:focus-visible{background:#f1f8fa;color:#52707a}.hrm-pagination-tooltip{position:absolute;left:0;bottom:calc(100% + 9px);z-index:120;display:block;width:224px;padding:10px;border:1px solid #d9e7eb;border-radius:11px;background:rgba(255,255,255,.98);box-shadow:0 14px 36px rgba(15,23,42,.16);color:#415267;white-space:normal;pointer-events:none;opacity:0;visibility:hidden;transform:translateY(4px) scale(.98);transform-origin:left bottom;transition:opacity .12s ease,transform .12s ease,visibility .12s ease}.hrm-pagination-tooltip:after{content:'';position:absolute;left:18px;top:100%;width:8px;height:8px;border-right:1px solid #d9e7eb;border-bottom:1px solid #d9e7eb;background:#fff;transform:translateY(-4px) rotate(45deg)}.hrm-pagination-tooltip-trigger:hover .hrm-pagination-tooltip,.hrm-pagination-tooltip-trigger:focus .hrm-pagination-tooltip,.hrm-pagination-tooltip-trigger:focus-within .hrm-pagination-tooltip{opacity:1;visibility:visible;transform:translateY(0) scale(1)}.hrm-pagination-tooltip__title{margin-bottom:7px;color:#243548;font-size:10px;font-weight:900}.hrm-pagination-tooltip__row{display:flex;align-items:center;justify-content:space-between;gap:12px;padding:4px 0;color:#7a8797;font-size:8px;font-weight:750}.hrm-pagination-tooltip__row strong{color:#34475c;font-size:9px;font-weight:900}.hrm-pagination-tooltip__hint{margin-top:6px;padding-top:7px;border-top:1px solid #edf2f4;color:#7a8797;font-size:8px;font-weight:700;line-height:1.45}.hrm-room-pagination__controls{display:flex;align-items:center;gap:6px}.hrm-room-pagination__controls span{min-width:34px;color:#536276;font-size:9px;font-weight:850;text-align:center}.hrm-room-pagination__controls button{display:grid;width:28px;height:28px;padding:0;place-items:center;border:1px solid #dbe5eb;border-radius:8px;background:#fff;color:#526377;cursor:pointer}.hrm-room-pagination__controls button:hover:not(:disabled){border-color:#80c9d1;background:#f1fbfc;color:var(--teal-dark)}.hrm-room-pagination__controls button:disabled{opacity:.4;cursor:default}
.hrm-workspace{min-width:0;padding:16px}.hrm-room-head{display:flex;justify-content:space-between;gap:16px;align-items:flex-start;margin-bottom:13px}.hrm-room-head__eyebrow{color:#7c8998;font-size:9px;font-weight:750}.hrm-room-head h2{margin:2px 0 5px;font-size:20px;font-weight:850}.hrm-room-head__meta{display:flex;flex-wrap:wrap;gap:8px;color:#7a8898;font-size:9px}.hrm-room-head__meta span+span:before{content:'•';margin-right:8px;color:#b4bec8}.hrm-room-actions{display:flex;gap:8px;flex-wrap:wrap;justify-content:flex-end}
.hrm-editor-toolbar{display:flex;align-items:center;gap:7px;margin-bottom:10px;padding:9px;border:1px solid #deebee;border-radius:12px;background:#f7fbfc}.hrm-editor-toolbar button{display:inline-flex;align-items:center;gap:6px;min-height:34px;padding:6px 9px;border:1px solid #d9e4e9;border-radius:9px;background:#fff;color:#435366;font-size:9px;font-weight:800}.hrm-editor-toolbar button:hover{border-color:#abd8de;color:var(--teal-dark)}.hrm-editor-toolbar button.is-placement-active{border-color:var(--teal);background:var(--teal-soft);color:var(--teal-dark);box-shadow:0 0 0 2px rgba(7,140,155,.10) inset}.hrm-editor-toolbar button.is-save{border-color:var(--teal);background:var(--teal);color:#fff}.hrm-editor-toolbar button:disabled{cursor:not-allowed;opacity:.45}.hrm-editor-toolbar__spacer{flex:1}
.hrm-designer{min-width:0;max-width:100%}.hrm-designer.has-inspector{display:grid;grid-template-columns:minmax(0,1fr) minmax(280px,300px);gap:12px;align-items:start;max-width:100%}.hrm-designer__canvas{min-width:0;max-width:100%;overflow:hidden}.hrm-map-wrap{display:flex;min-width:0;max-width:100%;overflow:hidden;padding:30px;align-items:flex-start;justify-content:center;border:1px solid #e3eaef;border-radius:16px;background:#f8fafb}.hrm-map{position:relative;box-sizing:border-box;width:100%;max-width:100%;min-width:0;min-height:0;overflow:visible;border:3px solid #5c6878;border-radius:8px;background:#fff;touch-action:none;user-select:none}.hrm-map.is-editing{cursor:default}.hrm-map.is-editing.is-placing{cursor:crosshair}.hrm-map__grid{position:absolute;inset:0;background-image:linear-gradient(#edf1f4 1px,transparent 1px),linear-gradient(90deg,#edf1f4 1px,transparent 1px);background-size:24px 24px;opacity:.65;pointer-events:none}.hrm-alignment-guide{position:absolute;z-index:11;background:#ef4444;pointer-events:none;opacity:.95}.hrm-alignment-guide.is-vertical{top:0;bottom:0;width:1px;transform:translateX(-.5px)}.hrm-alignment-guide.is-horizontal{left:0;right:0;height:1px;transform:translateY(-.5px)}.hrm-object{position:absolute;display:flex;align-items:center;justify-content:center;box-sizing:border-box;transform-origin:center;transition:box-shadow .12s ease,border-color .12s ease;z-index:2}.hrm-map.is-editing .hrm-object{cursor:grab}.hrm-map.is-editing .hrm-object:active{cursor:grabbing}.hrm-selection-marquee{position:absolute;z-index:30;box-sizing:border-box;border:1px dashed #078c9b;background:rgba(7,140,155,.10);pointer-events:none}.hrm-object.is-selected{z-index:5;outline:1px dashed #078c9b;outline-offset:2px;background-image:linear-gradient(rgba(7,140,155,.07),rgba(7,140,155,.07))}.hrm-object.is-primary-selected{box-shadow:0 0 0 3px rgba(7,140,155,.18),0 8px 20px rgba(15,23,42,.12)!important}.hrm-object-rotation-handle{position:absolute;z-index:28;display:grid;width:22px;height:22px;place-items:center;box-sizing:border-box;margin:0;padding:0;border:1px solid #8ccbd3;border-radius:50%;background:#fff;color:#078c9b;box-shadow:0 4px 12px rgba(15,23,42,.16);cursor:grab;touch-action:none;transform:rotate(calc(-1 * var(--object-rotation,0deg)));transition:background .12s ease,border-color .12s ease,color .12s ease,box-shadow .12s ease}.hrm-object-rotation-handle.is-nw{top:-27px;left:-27px}.hrm-object-rotation-handle.is-ne{top:-27px;right:-27px}.hrm-object-rotation-handle.is-se{right:-27px;bottom:-27px}.hrm-object-rotation-handle.is-sw{bottom:-27px;left:-27px}.hrm-object-rotation-handle:hover{border-color:#078c9b;background:#e9f8fa;color:#056b77;box-shadow:0 6px 16px rgba(7,140,155,.22)}.hrm-object-rotation-handle:active{cursor:grabbing;transform:rotate(calc(-1 * var(--object-rotation,0deg))) scale(.92)}.hrm-object-rotation-handle i{font-size:10px;line-height:1;pointer-events:none}.hrm-object-resize-handle{position:absolute;z-index:26;margin:0;padding:0;border:0;background:transparent;touch-action:none}.hrm-object-resize-handle.is-n{top:-6px;left:10px;right:10px;height:12px}.hrm-object-resize-handle.is-s{bottom:-6px;left:10px;right:10px;height:12px}.hrm-object-resize-handle.is-e{top:10px;right:-6px;bottom:10px;width:12px}.hrm-object-resize-handle.is-w{top:10px;bottom:10px;left:-6px;width:12px}.hrm-object-resize-handle.is-ne,.hrm-object-resize-handle.is-se,.hrm-object-resize-handle.is-sw,.hrm-object-resize-handle.is-nw{width:16px;height:16px}.hrm-object-resize-handle.is-ne{top:-8px;right:-8px}.hrm-object-resize-handle.is-se{right:-8px;bottom:-8px}.hrm-object-resize-handle.is-sw{bottom:-8px;left:-8px}.hrm-object-resize-handle.is-nw{top:-8px;left:-8px}.hrm-object-resize-handle:after{content:'';position:absolute;left:50%;top:50%;box-sizing:border-box;border:1px solid #078c9b;background:#fff;box-shadow:0 2px 6px rgba(15,23,42,.12);transform:translate(-50%,-50%);transition:background .12s ease,border-color .12s ease,box-shadow .12s ease}.hrm-object-resize-handle.is-n:after,.hrm-object-resize-handle.is-s:after{width:24px;height:4px;border-radius:999px}.hrm-object-resize-handle.is-e:after,.hrm-object-resize-handle.is-w:after{width:4px;height:24px;border-radius:999px}.hrm-object-resize-handle.is-ne:after,.hrm-object-resize-handle.is-se:after,.hrm-object-resize-handle.is-sw:after,.hrm-object-resize-handle.is-nw:after{width:8px;height:8px;border-radius:2px}.hrm-object-resize-handle:hover:after{background:#078c9b;box-shadow:0 3px 9px rgba(7,140,155,.28)}.hrm-object-resize-handle:focus-visible{outline:none}.hrm-object-resize-handle:focus-visible:after{box-shadow:0 0 0 3px rgba(7,140,155,.2)}

/* Компактные отступы и центрирование в режиме просмотра.
   При редактировании остаются исходные 30px и поведение канваса. */
.hrm-map-wrap.is-viewing{padding:12px;align-items:center}
.hrm-bed{flex-direction:column;border:2px solid #1598a7;border-radius:8px;background:#dff7fa;color:#075d68}.hrm-bed.is-occupied{border-color:#dc5266;background:#fff0f2;color:#9d2c3d}.hrm-bed.is-disabled{filter:grayscale(.65);opacity:.55}.hrm-bed__pillow{position:absolute;left:7%;top:10%;width:23%;height:80%;border-radius:5px;background:rgba(255,255,255,.75);box-shadow:inset 0 0 0 1px rgba(0,0,0,.08)}.hrm-bed__number{font-size:9px;font-weight:900}.hrm-bed__state{margin-top:2px;font-size:7px;font-weight:800;opacity:.72}.hrm-bed-tooltip{position:absolute;left:50%;bottom:calc(100% + 10px);display:none;min-width:220px;transform:translateX(-50%) rotate(calc(-1 * var(--bed-rotation,0deg)));padding:10px 11px;border:1px solid #f0c9cf;border-radius:11px;background:#fff;color:#293849;box-shadow:0 14px 36px rgba(15,23,42,.18);text-align:left;z-index:20}.hrm-bed:hover .hrm-bed-tooltip{display:block}.hrm-bed-tooltip:after{content:'';position:absolute;left:50%;top:100%;border:6px solid transparent;border-top-color:#fff;transform:translateX(-50%)}.hrm-bed-tooltip strong,.hrm-bed-tooltip span,.hrm-bed-tooltip em{display:block}.hrm-bed-tooltip strong{font-size:11px}.hrm-bed-tooltip span{margin-top:3px;color:#748194;font-size:8px}.hrm-bed-tooltip em{margin-top:6px;color:#a13b4b;font-size:8px;font-style:normal;font-weight:800}
.hrm-furniture{gap:5px;border:2px solid #8795a6;border-radius:6px;background:#eef2f5;color:#526174;font-size:8px;font-weight:850}.hrm-furniture.is-cabinet{background:#e7ebef}.hrm-furniture.is-bedside_table{border-radius:8px;background:#f4f6f8}.hrm-furniture.is-column{z-index:4;border-color:#c52f46;border-radius:4px;background-color:rgba(220,66,89,.12);background-image:repeating-linear-gradient(135deg,rgba(180,35,59,.34) 0,rgba(180,35,59,.34) 2px,transparent 2px,transparent 8px);color:#a52138;box-shadow:0 4px 12px rgba(180,35,59,.12)}.hrm-furniture.is-column i{font-size:12px}.hrm-furniture.is-column .hrm-object-resize-handle.is-column-resize:after{border-color:#c52f46;background:#fff7f8;box-shadow:0 2px 7px rgba(180,35,59,.18)}.hrm-furniture.is-column .hrm-object-resize-handle.is-column-resize:hover:after{background:#c52f46;box-shadow:0 3px 10px rgba(180,35,59,.28)}.hrm-furniture.is-entrance{border-color:#1598a7;border-radius:4px;background:#e8fafc;color:#076976}.hrm-furniture.is-entrance:after{content:'';position:absolute;right:7px;bottom:5px;left:7px;height:2px;border-radius:2px;background:currentColor;opacity:.45}.hrm-furniture.is-window{border-color:#4a9fd2;border-radius:3px;background:#eaf6ff;color:#246a93}.hrm-furniture.is-window:before,.hrm-furniture.is-window:after{content:'';position:absolute;left:8px;right:8px;height:2px;border-radius:2px;background:currentColor;opacity:.55}.hrm-furniture.is-window:before{top:7px}.hrm-furniture.is-window:after{bottom:7px}.hrm-furniture.is-socket{border-color:#c58a2d;border-radius:999px;background:#fff8e9;color:#9a650f}.hrm-furniture.is-socket span{display:none}.hrm-furniture.is-socket i{font-size:14px}.hrm-furniture.is-toilet{overflow:visible;border:0;background:transparent;color:#526174;box-shadow:none}.hrm-furniture.is-toilet>i{display:none}.hrm-furniture.is-toilet>span{position:absolute;left:50%;bottom:8%;z-index:3;transform:translateX(-50%);padding:1px 4px;border-radius:999px;background:rgba(255,255,255,.88);color:#59697a;font-size:7px;font-weight:900;letter-spacing:.04em}.hrm-furniture.is-toilet:before{content:'';position:absolute;top:4%;left:18%;width:64%;height:27%;box-sizing:border-box;border:2px solid #8795a6;border-radius:6px;background:linear-gradient(180deg,#fff,#eef3f6);box-shadow:inset 0 -2px 0 rgba(82,97,116,.08),0 2px 5px rgba(15,23,42,.08)}.hrm-furniture.is-toilet:after{content:'';position:absolute;left:23%;bottom:6%;width:54%;height:64%;box-sizing:border-box;border:2px solid #8795a6;border-radius:46% 46% 52% 52%/40% 40% 60% 60%;background:radial-gradient(ellipse at 50% 42%,#d9e5ea 0 22%,#f9fcfd 24% 56%,#e8eef2 58% 100%);box-shadow:inset 0 0 0 3px rgba(255,255,255,.72),0 3px 8px rgba(15,23,42,.10)}.hrm-canvas-resizer{position:absolute;right:6px;bottom:6px;z-index:12;display:grid;width:25px;height:25px;place-items:center;border:1px solid #73c2cb;border-radius:7px;background:#fff;color:#087b89;box-shadow:0 5px 14px rgba(15,23,42,.16);cursor:nwse-resize}.hrm-canvas-resizer:hover{background:#eaf9fb}.hrm-map-empty{position:absolute;inset:0;display:grid;place-content:center;justify-items:center;color:#8b97a5;text-align:center;pointer-events:none}.hrm-map-empty i{font-size:32px;color:#9dcfd5}.hrm-map-empty strong{margin-top:8px;font-size:12px}.hrm-map-empty span{margin-top:4px;font-size:9px}
.hrm-legend{display:flex;flex-wrap:wrap;gap:14px;margin-top:9px;color:#6f7d8e;font-size:9px}.hrm-legend span{display:flex;align-items:center;gap:6px}.hrm-legend i{display:block;width:13px;height:9px;border-radius:3px}.hrm-legend i.is-free{background:#bfeef3;border:1px solid #1598a7}.hrm-legend i.is-occupied{background:#ffd9df;border:1px solid #dc5266}.hrm-legend i.is-furniture{background:#e7ebef;border:1px solid #8795a6}.hrm-legend i.is-column{border:1px solid #c52f46;background-color:rgba(220,66,89,.12);background-image:repeating-linear-gradient(135deg,rgba(180,35,59,.35) 0,rgba(180,35,59,.35) 1px,transparent 1px,transparent 4px)}.hrm-legend i.is-entrance{background:#e8fafc;border:1px solid #1598a7}.hrm-legend i.is-window{background:#eaf6ff;border:1px solid #4a9fd2}.hrm-legend i.is-socket{border-radius:999px;background:#fff8e9;border:1px solid #c58a2d}.hrm-legend i.is-toilet{background:#f8fbfc;border:1px solid #8795a6;border-radius:5px}
.hrm-column-tooltip{position:absolute;z-index:70;display:flex;align-items:center;gap:7px;max-width:250px;padding:8px 10px;border:1px solid #f1a8b3;border-radius:9px;background:#fff6f7;color:#a52138;box-shadow:0 9px 24px rgba(145,28,49,.18);font-size:9px;font-weight:850;line-height:1.3;pointer-events:none;transform:translate(-50%,calc(-100% - 10px));animation:hrm-tooltip-in .12s ease-out}.hrm-column-tooltip:after{content:'';position:absolute;left:50%;top:100%;width:8px;height:8px;border-right:1px solid #f1a8b3;border-bottom:1px solid #f1a8b3;background:#fff6f7;transform:translate(-50%,-4px) rotate(45deg)}.hrm-column-tooltip i{font-size:11px;color:#d2364f}@keyframes hrm-tooltip-in{from{opacity:0;transform:translate(-50%,calc(-100% - 4px)) scale(.98)}to{opacity:1;transform:translate(-50%,calc(-100% - 10px)) scale(1)}}
.hrm-context-menu{position:fixed;z-index:1600;width:218px;padding:6px;border:1px solid #dbe6eb;border-radius:11px;background:rgba(255,255,255,.98);box-shadow:0 18px 46px rgba(15,23,42,.22);backdrop-filter:blur(8px);animation:hrm-context-in .08s ease-out}.hrm-context-menu__caption{overflow:hidden;padding:7px 9px 6px;color:#7b8998;font-size:8px;font-weight:850;text-overflow:ellipsis;white-space:nowrap}.hrm-context-menu button{display:grid;grid-template-columns:18px minmax(0,1fr) auto;align-items:center;gap:7px;width:100%;min-height:34px;padding:6px 8px;border:0;border-radius:8px;background:transparent;color:#34465a;text-align:left;font-size:9px;font-weight:800}.hrm-context-menu button:hover:not(:disabled){background:#ecf9fa;color:#076f7b}.hrm-context-menu button.is-danger{color:#b93449}.hrm-context-menu button.is-danger:hover:not(:disabled){background:#fff0f2;color:#a32238}.hrm-context-menu button:disabled{opacity:.38;cursor:not-allowed}.hrm-context-menu button i{display:grid;width:18px;place-items:center;font-size:11px}.hrm-context-menu kbd{min-width:38px;padding:2px 5px;border:1px solid #dde6eb;border-bottom-width:2px;border-radius:5px;background:#f7f9fa;color:#8491a0;font:700 7px/1.3 inherit;text-align:center}.hrm-context-menu__separator{height:1px;margin:5px 4px;background:#e7edf1}@keyframes hrm-context-in{from{opacity:0;transform:translateY(-3px) scale(.985)}to{opacity:1;transform:translateY(0) scale(1)}}
.hrm-inspector{padding:13px;border:1px solid #dce8ec;border-radius:14px;background:#fbfdfe}.hrm-inspector--docked{position:sticky;top:82px;min-height:240px;max-height:calc(100vh - 105px);overflow:auto;box-shadow:0 10px 26px rgba(15,23,42,.05)}.hrm-inspector__head{display:flex;align-items:center;justify-content:space-between;margin-bottom:10px;padding-bottom:10px;border-bottom:1px solid #e5edf1}.hrm-inspector__head--object{margin-top:2px}.hrm-inspector__head small,.hrm-inspector__head strong{display:block}.hrm-inspector__head small{color:#8794a3;font-size:8px}.hrm-inspector__head strong{margin-top:2px;font-size:12px}.hrm-inspector__section-title{margin:12px 0 7px;color:#6f7f90;font-size:8px;font-weight:900;letter-spacing:.06em;text-transform:uppercase}.hrm-inspector__separator{height:1px;margin:14px 0;background:#e4ebef}.hrm-inspector__grid{display:grid;grid-template-columns:1fr;gap:9px}.hrm-angle-control{position:relative;display:flex;align-items:center}.hrm-angle-control .hrm-control{padding-right:28px}.hrm-angle-control>span{position:absolute;right:10px;color:#7b8796;font-size:10px;font-weight:800;pointer-events:none}.hrm-inspector__grid--two{grid-template-columns:repeat(2,minmax(0,1fr))}.hrm-canvas-size{margin-top:8px;padding:7px 9px;border:1px solid #e2e9ee;border-radius:9px;background:#fff;color:#6f7f90;font-size:9px;font-weight:800;text-align:center}.hrm-canvas-stepper,.hrm-canvas-presets{display:grid;gap:5px;margin-top:7px}.hrm-canvas-stepper{grid-template-columns:repeat(4,minmax(0,1fr))}.hrm-canvas-presets{grid-template-columns:repeat(3,minmax(0,1fr))}.hrm-canvas-stepper button,.hrm-canvas-presets button{min-height:29px;padding:4px 5px;border:1px solid #dbe5ea;border-radius:8px;background:#fff;color:#526174;font-size:8px;font-weight:850}.hrm-canvas-stepper button:hover,.hrm-canvas-presets button:hover{border-color:#9fd3da;background:#f0fafb;color:#087887}.hrm-selection-summary{margin:0 0 10px;padding:8px 9px;border:1px dashed #078c9b;border-radius:9px;background:rgba(7,140,155,.07);color:#526174;font-size:8px;line-height:1.45}.hrm-selection-summary strong{color:#087b89}.hrm-inspector__actions{display:grid;gap:7px}.hrm-rotation-actions{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:7px}.hrm-inspector__actions .hrm-btn{justify-content:flex-start;width:100%}.hrm-inspector__empty{display:grid;min-height:210px;place-content:center;justify-items:center;padding:18px;color:#8a96a4;text-align:center}.hrm-inspector__empty i{font-size:28px;color:#8ecbd2}.hrm-inspector__empty strong{margin-top:8px;color:#536276;font-size:11px}.hrm-inspector__empty span{max-width:210px;margin-top:4px;font-size:9px;line-height:1.45}
.hrm-no-room{display:grid;min-height:420px;place-content:center;justify-items:center;color:#84909e;text-align:center}.hrm-no-room i{font-size:38px;color:#9cced4}.hrm-no-room strong{margin-top:9px;font-size:14px}.hrm-no-room span{margin-top:4px;font-size:10px}.hrm-empty-small{padding:18px;color:#8a96a4;font-size:10px;text-align:center}
.hrm-modal-backdrop{position:fixed;inset:0;z-index:1200;display:grid;place-items:center;padding:20px;background:rgba(15,23,42,.48)}.hrm-modal{width:min(720px,96vw);max-height:90vh;overflow:auto;border-radius:20px;background:#fff;box-shadow:0 30px 90px rgba(15,23,42,.3)}.hrm-modal--small{width:min(560px,96vw)}.hrm-modal>header,.hrm-modal>footer{display:flex;align-items:center;gap:10px;padding:15px 17px;border-bottom:1px solid #e7edf1}.hrm-modal>footer{border-top:1px solid #e7edf1;border-bottom:0}.hrm-modal>header>div{flex:1}.hrm-modal>header small{color:#7d8a99;font-size:9px}.hrm-modal>header h3{margin:2px 0 0;font-size:20px}.hrm-modal__body{padding:17px;background:#f8fafb}.hrm-modal__spacer{flex:1}.hrm-form-grid{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:11px}.hrm-form-grid .is-full{grid-column:1/-1}
.hrm-searchbox{position:relative}.hrm-searchbox i{position:absolute;left:12px;top:50%;transform:translateY(-50%);color:#91a0af}.hrm-searchbox .hrm-control{padding-left:37px}.hrm-patient-results{display:grid;gap:6px;margin-top:10px;max-height:300px;overflow:auto}.hrm-patient-row{display:flex;align-items:center;gap:9px;width:100%;padding:9px;border:1px solid #e2e8ed;border-radius:11px;background:#fff;text-align:left}.hrm-patient-row:hover,.hrm-patient-row.is-selected{border-color:#79c5ce;background:#f0fbfc}.hrm-patient-row__avatar{display:grid;width:32px;height:32px;place-items:center;border-radius:10px;background:var(--teal-soft);color:var(--teal)}.hrm-patient-row strong,.hrm-patient-row small{display:block}.hrm-patient-row strong{font-size:10px}.hrm-patient-row small{margin-top:2px;color:#8390a0;font-size:8px}
.hrm-assign-modal{width:min(650px,96vw);overflow:visible}.hrm-assign-modal__header{padding:18px 20px!important;background:linear-gradient(135deg,#f6fdfe 0%,#fff 58%)}.hrm-assign-modal__heading{display:flex;align-items:center;gap:12px}.hrm-assign-modal__icon{display:grid;width:44px;height:44px;flex:0 0 44px;place-items:center;border-radius:14px;background:linear-gradient(135deg,#dff6f9,#eefbfc);color:var(--teal);font-size:19px;box-shadow:inset 0 0 0 1px rgba(7,140,155,.08)}.hrm-assign-modal__heading small{color:#7b8999;font-size:9px;font-weight:800;letter-spacing:.02em}.hrm-assign-modal__heading h3{margin:3px 0 0!important;font-size:18px!important}.hrm-assign-modal__heading{flex:1 1 auto!important;min-width:0}.hrm-assign-modal__head-actions{display:flex!important;flex:0 0 auto!important;align-items:center;gap:9px;margin-left:auto}.hrm-assign-modal__bed-badge{display:inline-flex;align-items:center;gap:6px;min-height:32px;padding:6px 10px;border:1px solid #cfe7ea;border-radius:10px;background:#eefafb;color:#087887;font-size:9px;font-weight:900;white-space:nowrap}.hrm-assign-modal__body{padding:18px 20px 22px;overflow:visible}.hrm-assign-card{padding:16px;border:1px solid #dfe8ed;border-radius:16px;background:#fff;box-shadow:0 8px 24px rgba(15,23,42,.035)}.hrm-assign-card__head{margin-bottom:13px}.hrm-assign-card__head strong,.hrm-assign-card__head span{display:block}.hrm-assign-card__head strong{font-size:12px;color:#263548}.hrm-assign-card__head span{margin-top:3px;color:#7f8c9c;font-size:9px;line-height:1.45}.hrm-assign-field{display:block}.hrm-patient-combobox{position:relative}.hrm-patient-combobox__search{position:absolute;z-index:3;left:13px;top:50%;transform:translateY(-50%);color:#8796a6;font-size:13px;pointer-events:none}.hrm-patient-combobox__input{min-height:46px!important;padding-left:39px!important;padding-right:40px!important;border-radius:13px!important;font-size:12px!important}.hrm-patient-combobox.has-value .hrm-patient-combobox__input{border-color:#79c8d1;background:#f5fcfd;color:#27535a;font-weight:800;box-shadow:0 0 0 3px rgba(7,140,155,.07)}.hrm-patient-combobox__clear{position:absolute;z-index:4;right:8px;top:50%;display:grid;width:30px;height:30px;place-items:center;transform:translateY(-50%);border:0;border-radius:9px;background:transparent;color:#8a98a8}.hrm-patient-combobox__clear:hover{background:#edf6f7;color:#087887}.hrm-patient-dropdown{position:absolute;z-index:1305;top:calc(100% + 7px);right:0;left:0;max-height:290px;overflow:auto;padding:6px;border:1px solid #d9e5ea;border-radius:14px;background:#fff;box-shadow:0 18px 48px rgba(15,23,42,.17)}.hrm-patient-dropdown__state{display:flex;align-items:center;justify-content:center;gap:8px;padding:18px;color:#728092;font-size:10px}.hrm-patient-dropdown__empty{display:grid;justify-items:center;padding:20px 12px;text-align:center}.hrm-patient-dropdown__empty i{font-size:23px;color:#9bbbc0}.hrm-patient-dropdown__empty strong{margin-top:6px;color:#536274;font-size:10px}.hrm-patient-dropdown__empty span{margin-top:3px;color:#8a97a6;font-size:8px}.hrm-patient-row{min-height:52px;padding:9px 10px;border-color:transparent;border-radius:11px}.hrm-patient-row+.hrm-patient-row{margin-top:2px}.hrm-patient-row:hover,.hrm-patient-row.is-selected{border-color:#d2eaed;background:#eefafb}.hrm-patient-row__avatar{width:36px;height:36px;flex:0 0 36px;border-radius:11px}.hrm-patient-row__copy{min-width:0;flex:1}.hrm-patient-row__copy strong{overflow:hidden;color:#29384b;font-size:10px;text-overflow:ellipsis;white-space:nowrap}.hrm-patient-row__copy small{font-size:8px}.hrm-patient-row__arrow{color:#a0aab6;font-size:10px}.hrm-selected-patient{display:flex;align-items:center;gap:10px;margin-top:13px;padding:11px 12px;border:1px solid #cce8eb;border-radius:13px;background:linear-gradient(135deg,#f0fbfc,#f8fdfd)}.hrm-selected-patient__avatar{display:grid;width:38px;height:38px;flex:0 0 38px;place-items:center;border-radius:11px;background:#dff4f6;color:#078c9b;font-size:16px}.hrm-selected-patient__copy{min-width:0;flex:1}.hrm-selected-patient__copy small,.hrm-selected-patient__copy strong,.hrm-selected-patient__copy span{display:block}.hrm-selected-patient__copy small{color:#6d8990;font-size:7px;font-weight:850;text-transform:uppercase;letter-spacing:.04em}.hrm-selected-patient__copy strong{margin-top:2px;overflow:hidden;color:#23424a;font-size:11px;text-overflow:ellipsis;white-space:nowrap}.hrm-selected-patient__copy span{margin-top:2px;color:#7b8998;font-size:8px}.hrm-selected-patient__check{display:grid;width:27px;height:27px;flex:0 0 27px;place-items:center;border-radius:999px;background:#078c9b;color:#fff;font-size:13px}.hrm-assign-modal__footer{padding:13px 20px!important;background:#fff}.hrm-assign-modal__footer-note{display:flex;align-items:center;gap:6px;color:#8591a0;font-size:8px}.hrm-assign-modal__footer-note i{color:#5caeb7}.hrm-assign-modal .hrm-btn--primary:disabled{border-color:#b9dadd;background:#b9dadd;color:#fff;cursor:not-allowed;opacity:1}
@media(max-width:1180px){.hrm-designer.has-inspector{grid-template-columns:minmax(0,1fr) 260px}}@media(max-width:1100px){.hrm-layout{grid-template-columns:1fr}.hrm-sidebar{position:static}.hrm-room-list{grid-template-columns:repeat(2,minmax(0,1fr));max-height:none}.hrm-inspector--docked{position:static;max-height:none}}@media(max-width:900px){.hrm-designer.has-inspector{grid-template-columns:1fr}.hrm-inspector--docked{order:2}}
@media(max-width:720px){.hrm-head,.hrm-room-head{flex-direction:column}.hrm-stats{grid-template-columns:repeat(2,minmax(0,1fr))}.hrm-room-actions{justify-content:flex-start}.hrm-room-list{grid-template-columns:1fr}.hrm-editor-toolbar{flex-wrap:wrap}.hrm-editor-toolbar__spacer{display:none}.hrm-form-grid{grid-template-columns:1fr}.hrm-form-grid .is-full{grid-column:auto}}
/* Регулируем только просмотр (никакого масштабирования navbar/панелей). */
.hrm-view-size-toolbar{display:flex;flex-wrap:wrap;align-items:center;gap:12px;margin:0 0 12px;padding:11px 12px;border:1px solid #dce9ed;border-radius:12px;background:#f5fafb}
.hrm-view-size-toolbar__title{display:flex;align-items:center;gap:9px;min-width:170px;color:#235563}
.hrm-view-size-toolbar__title>i{display:grid;width:32px;height:32px;flex:0 0 32px;place-items:center;border-radius:9px;background:#e2f5f7;color:#078c9b;font-size:15px}
.hrm-view-size-toolbar__title strong,.hrm-view-size-toolbar__title small{display:block}
.hrm-view-size-toolbar__title strong{font-size:11px;font-weight:850}
.hrm-view-size-toolbar__title small{margin-top:2px;color:#738596;font-size:9px;line-height:1.3}
.hrm-view-size-field{display:flex;align-items:center;flex:1 1 205px;gap:9px;min-width:170px;margin:0}
.hrm-view-size-field>span{display:inline;margin:0;flex:0 0 48px;color:#526577;font-size:10px;font-weight:800}
.hrm-view-size-field input[type=range]{flex:1;min-width:66px;width:100%;height:25px;accent-color:#078c9b;cursor:pointer}
.hrm-view-size-field output{min-width:42px;color:#255865;text-align:right;font-size:11px;font-weight:850;font-variant-numeric:tabular-nums}
.hrm-view-size-toolbar__pixels{color:#718394;font-size:10px;font-weight:800;white-space:nowrap;font-variant-numeric:tabular-nums}
.hrm-view-size-reset{display:inline-flex;align-items:center;justify-content:center;gap:6px;min-height:33px;padding:6px 11px;border:1px solid #bbdfe3;border-radius:9px;background:#fff;color:#087887;font-size:10px;font-weight:850;cursor:pointer}
.hrm-view-size-reset:hover:not(:disabled){background:#e4f7f9;border-color:#078c9b}
.hrm-view-size-reset:disabled{opacity:.45;cursor:default}
.hrm-map-wrap.is-viewing{justify-content:safe center;overflow:auto}
.hrm-map.is-viewing .hrm-map__grid{background-size:var(--hrm-view-grid-size,24px) var(--hrm-view-grid-size,24px)}
.hrm-map.is-viewing .hrm-bed__number{font-size:var(--hrm-view-bed-font,9px)}
.hrm-map.is-viewing .hrm-bed__state{font-size:var(--hrm-view-state-font,7px)}
.hrm-map.is-viewing .hrm-furniture{font-size:var(--hrm-view-furniture-font,8px)}
@media(max-width:720px){.hrm-view-size-toolbar{gap:10px}.hrm-view-size-toolbar__title{width:100%}.hrm-view-size-field{flex-basis:100%}.hrm-view-size-reset{margin-left:auto}}
</style>
