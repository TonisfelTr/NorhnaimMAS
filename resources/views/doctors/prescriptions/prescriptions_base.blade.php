@extends('doctors.prescriptions')
@section('title', 'База лекарств')
@section('assets')
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@yaireo/tagify@4.17.9/dist/tagify.css">
    <script src="https://cdn.jsdelivr.net/npm/@yaireo/tagify@4.17.9/dist/tagify.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@yaireo/tagify@4.17.9/dist/tagify.polyfills.min.js"></script>
    <style>
        .tagify {
            display: flex;
            flex-wrap: wrap;
            align-items: flex-start;
            gap: 0.25rem;
            border: 1px solid #ced4da;
            border-radius: 0.375rem;
            padding: 0.375rem 0.75rem;
            min-height: 38px; /* соответствие .form-control */
            font-size: 1rem;
            line-height: 1.5;
            box-sizing: border-box;
            background-color: #fff;
        }

        .tagify__tag {
            margin: 0 !important;
        }

        .tagify__input {
            flex: 1 0 120px;
            min-width: 120px;
            padding: 0 !important;
            margin: 0 !important;
            line-height: 1.5;
        }
    </style>
@endsection
@section('sub-main')
    <div class="p-4">
        <h1>База лекарств</h1>
        <div class="row">
            <form class="col-xl-12" method="get">
                <div class="row my-2">
                    <div class="col-span-full">
                        <label for="name">Название</label>
                        <input class="form-control" type="text" id="name" name="name" value="{{ request('name') }}">
                    </div>
                </div>

                <div class="row my-2">
                    <div class="col-xl-6">
                        <label for="group" class="form-label">Группа</label>
                        <select class="form-control" id="group" name="group">
                            <option class="placeholder" value="" {{ request('group') === null ? 'selected' : '' }} disabled>
                                Выберите группу...
                            </option>
                            @foreach($groups as $group)
                                <option value="{{ $group->id }}" {{ request('group') == $group->id ? 'selected' : '' }}>
                                    {{ $group->group }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-xl-6">
                        <label for="contraindications" class="form-label">Противопоказания для исключения</label>
                        <input id="contraindications" name="contraindications" placeholder="Противопоказания" class="form-control">
                        <input type="hidden" name="contraindications_ids" id="contraindications_ids">
                    </div>
                </div>

                <div class="row">
                    <div class="col-xl-2">
                        <x-doctor-checkbox name="pregnancy"
                                           label="Разрешён при беременности"
                                           wrapper-class="mb-2"
                                           :checked="request()->has('pregnancy') && request()->get('pregnancy') == 'on'" />
                    </div>
                    <div class="col-xl-2">
                        <x-doctor-checkbox name="lactation"
                                           label="Разрешён при лактации"
                                           wrapper-class="mb-2"
                                           :checked="request()->has('lactation') && request()->get('lactation') == 'on'" />
                    </div>
                    <div class="col-xl-2">
                        <x-doctor-checkbox name="liver"
                                           label="Метаболизм не печенью"
                                           wrapper-class="mb-2"
                                           :checked="request()->has('liver') && request()->get('liver') == 'on'" />
                    </div>
                    <div class="col-xl-2">
                        <x-doctor-checkbox name="kidneys"
                                           label="Метаболизм не почками"
                                           wrapper-class="mb-2"
                                           :checked="request()->has('kidneys') && request()->get('kidneys') == 'on'" />
                    </div>
                </div>

                <div class="d-flex justify-content-end m-3">
                    <div class="btn-group">
                        <button class="btn btn-primary">Искать</button>
                    </div>
                </div>
            </form>
        </div>
        <table class="table mt-5 font-12">
            <thead>
                <tr>
                    <th>Название</th>
                    <th>Группа препарата</th>
                    <th class="table__forms-column">Формы</th>
                    <th>Тип рецепта</th>
                    <th class="table__indications-column">Показания</th>
                    <th class="table__contraindications-column">Противопоказания</th>
                    <th>Доступен по льготе</th>
                </tr>
            </thead>
            <tbody>
                @foreach($drugs as $drug)
                <tr>
                    <td>{{ $drug->name }}</td>
                    <td>{{ $drug->groupName() }}</td>
                    <td>
                        @php
                            $forms = $drug->forms;

                            // Словарь перевода форм
                            $formTranslations = [
                                'tablets' => 'Таблетки',
                                'capsules' => 'Капсулы',
                                'dragees' => 'Драже',
                                'ampules' => 'Ампулы',
                                'solution' => 'Раствор',
                                'syrup' => 'Сироп',
                                'ointment' => 'Мазь',
                                'gel' => 'Гель',
                                'spray' => 'Спрей',
                                'drops' => 'Капли',
                                'powder' => 'Порошок',
                            ];
                        @endphp

                        @foreach($forms as $form => $doses)
                            <strong>{{ $formTranslations[$form] ?? ucfirst($form) }}:</strong><br>
                            <ul>
                            @foreach($doses as $dose => $packs)
                                    <li>{{ $dose }} мг – {{ is_array($packs) ? implode(', ', $packs) : $packs }} шт<br></li>
                            @endforeach
                            </ul>
                        @endforeach
                    </td>
                    <td>{{ $drug->strict ? '№ 148-1/у-88' : '№ 107-1/у' }}</td>
                    <td>
                        @foreach($drug->diagnoses as $diagnose)
                            <span class="my-1 badge bg-success">{{ $diagnose->title }}</span>
                        @endforeach
                    </td>
                    <td>
                        @foreach ($drug->contraindications as $contraindication)
                            <span class="my-1 badge bg-danger">{{ $contraindication->name }}</span>
                        @endforeach
                    </td>
                    <td>{{ $drug->preferential ? 'Доступен по льготе' : 'Недоступен по льготе' }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
        {{ $drugs->links('pagination::bootstrap-5') }}
    </div>
    <script>
        document.addEventListener("DOMContentLoaded", function () {
            const allContraindicationsRaw = @json($contraindications);
            const allContraindications = Object.entries(allContraindicationsRaw).map(([value, label]) => ({
                value: value.toString(),
                code: label.toString()
            }));
            const selectedContraindications = @json(json_decode(request('contraindications_ids'), true) ?? []);
            const input = document.querySelector("#contraindications");
            if (!input) return;

            const tagify = new Tagify(input, {
                delimiters: null,
                whitelist: allContraindications,
                enforceWhitelist: true,
                dropdown: {
                    enabled: 1,
                    closeOnSelect: true,
                    direction: 'bottom',
                    maxItems: 20,
                    fuzzySearch: true,
                    highlightFirst: true,
                    searchKeys: ['code', 'value'],
                    position: 'text' // гарантирует корректную позицию
                },
                templates: {
                    tag: tagData => `
                <tag title="${tagData.value}" contenteditable="false" spellcheck="false" class="tagify__tag">
                    <div>
                        <span class="tagify__tag-text">${tagData.code}</span>
                    </div>
                    <button type="button" class="tagify__tag__removeBtn" aria-label="remove tag"></button>
                </tag>
            `,
                    dropdownItem: tagData => `
                <div class="tagify__dropdown__item ${tagData.class || ""}"
                     data-value="${tagData.value}"
                     data-tagify-tag='${JSON.stringify(tagData)}'>
                    <strong>${tagData.code}</strong> – ${tagData.value}
                </div>
            `
                }
            });

            const preselectedItems = allContraindications.filter(item =>
                selectedContraindications.includes(item.value)
            );
            tagify.addTags(preselectedItems);

            function updateContraindicationHiddenInput() {
                const ids = tagify.value.map(item => item.value);
                document.querySelector('#contraindications_ids').value = JSON.stringify(ids);
                console.log('📝 Hidden input updated:', ids);
            }

            tagify.on('change', e => {
                updateContraindicationHiddenInput();
            });

            tagify.on('add', e => {
                updateContraindicationHiddenInput();
            });

            tagify.on('remove', e => {
                updateContraindicationHiddenInput();
            });

            tagify.on('invalid', e => console.warn('❌ INVALID:', e.detail));
            tagify.on('dropdown:select', e => {
                const data = JSON.parse(e.detail.elm.dataset.tagifyTag);
                if (data) {
                    tagify.addTags([data]);
                    tagify.dropdown.hide();
                }
                console.log('✅ SELECTED (dropdown):', data);
            });

            t
        });
    </script>
@endsection
