@if($patients->isEmpty())
    <div class="nh-workbench__empty">
        @if($scope === 'mine')
            У тебя пока нет закреплённых пациентов. Заведующий отделением может назначить лечащего врача в списке «Всё отделение».
        @else
            По выбранным параметрам активных пациентов не найдено.
        @endif
    </div>
@else
    <div class="table-responsive">
        <table class="nh-workbench__patient-table">
            <thead>
                <tr>
                    <th scope="col">Пациент</th>
                    <th scope="col">Отделение / место</th>
                    <th scope="col">Поступил</th>
                    <th scope="col">Лечащий врач</th>
                </tr>
            </thead>
            <tbody>
                @foreach($patients as $patient)
                    <tr>
                        <td>
                            <a class="nh-workbench__patient-name" href="{{ $patient['medical_card_url'] }}">
                                {{ $patient['full_name'] }}
                            </a>
                            <span class="nh-workbench__muted">
                                {{ $patient['birth_date'] }}
                                @if($patient['age'] !== null)
                                    · {{ $patient['age'] }} лет
                                @endif
                            </span>
                        </td>
                        <td>
                            {{ $patient['department'] }}
                            @if($patient['without_bed'])
                                <div class="nh-workbench__muted" style="color:#a9630b;font-weight:750;">
                                    <i class="bi bi-exclamation-triangle"></i> Без палаты и койки · ожидает место в отделении
                                </div>
                            @else
                                <div class="nh-workbench__muted">
                                    Палата {{ $patient['room'] }} · Койка {{ $patient['bed'] }}
                                </div>
                            @endif
                            @if($patient['last_transfer'])
                                <div class="nh-workbench__transfer-note" role="note">
                                    <strong>Переведён из {{ $patient['last_transfer']['from'] }}</strong>
                                    <span>В {{ $patient['last_transfer']['to'] }}</span>
                                    <span>{{ $patient['last_transfer']['at'] }} · Перевёл: {{ $patient['last_transfer']['by'] }}</span>
                                </div>
                            @endif
                        </td>
                        <td>{{ $patient['admitted_at'] }}</td>
                        <td>
                            @if($tab === 'patients' && $scope === 'department' && $patient['can_assign'])
                                <form class="nh-workbench__doctor-select" method="post"
                                      action="{{ route('doctors.workbench.attending.update', ['assignment' => $patient['assignment_id']]) }}">
                                    @csrf
                                    <div class="nh-workbench__doctor-picker" data-nh-doctor-picker>
                                        <select id="nh-workbench-doctor-{{ $patient['assignment_id'] }}"
                                                class="form-select form-select-sm nh-workbench__doctor-native"
                                                name="attending_doctor_id"
                                                aria-label="Назначить лечащего врача для {{ $patient['full_name'] }}">
                                            <option value="" @selected($patient['doctor_id'] === null)>Не назначен</option>
                                            @foreach($patient['doctor_options'] as $doctorOption)
                                                <option value="{{ $doctorOption['id'] }}"
                                                        @selected($patient['doctor_id'] === $doctorOption['id'])>
                                                    {{ $doctorOption['search_label'] }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <button type="submit" class="btn btn-sm btn-outline-info">Сохранить</button>
                                </form>
                            @else
                                <span>{{ $patient['doctor_name'] }}</span>
                            @endif
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @if($tab === 'patients' && $scope === 'department' && $patients->contains('can_assign', true))
        <p class="nh-workbench__doctor-help">
            Откройте список лечащих врачей и начните вводить ФИО в поиске. Одного врача можно назначать нескольким пациентам.
            Чтобы снять назначение, выберите «Не назначен» и нажмите «Сохранить».
        </p>
    @endif

    @if($assignments->hasPages())
        <div class="mt-3">{{ $assignments->onEachSide(1)->links() }}</div>
    @endif
@endif
