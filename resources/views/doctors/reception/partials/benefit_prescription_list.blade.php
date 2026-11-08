@if(!empty($benefitPrescriptions['available']))
    <section id="benefitPrescriptionList" class="benefit-rx-list" aria-labelledby="benefitPrescriptionListTitle">
        <div class="benefit-rx-list__head">
            <div>
                <h3 id="benefitPrescriptionListTitle" class="benefit-rx-heading">Льготные рецепты</h3>
                <p class="benefit-rx-help mb-0">Форма № 148-1/у-04(л) · черновики и сохранённые бумажные рецепты</p>
            </div>
        </div>
        <div id="benefitListFeedback" class="benefit-rx-feedback" role="status" hidden></div>
        <div id="benefitPrescriptionRecords" class="benefit-rx-list__records">
            @forelse($benefitPrescriptions['records'] ?? [] as $benefitRecord)
                <article class="benefit-rx-record"
                         data-benefit-record="{{ $benefitRecord['id'] }}"
                         data-version="{{ $benefitRecord['version'] }}"
                         data-status="{{ $benefitRecord['status'] }}">
                    <div class="benefit-rx-record__main">
                        <div class="benefit-rx-record__tags">
                            <span class="benefit-rx-status benefit-rx-status--{{ $benefitRecord['status'] }}" data-benefit-record-status>{{ $benefitRecord['status_label'] }}</span>
                            <span class="benefit-rx-record__number" data-benefit-record-number>{{ $benefitRecord['number_label'] }}</span>
                        </div>
                        <h4 class="benefit-rx-record__name" data-benefit-record-name>{{ $benefitRecord['medicine_label'] }}</h4>
                        <div class="benefit-rx-record__meta" data-benefit-record-meta>
                            <span>{{ $benefitRecord['issued_label'] ?: $benefitRecord['created_label'] }}</span>
                            <span>{{ $benefitRecord['doctor_name'] }}</span>
                        </div>
                    </div>
                    <div class="benefit-rx-record__actions" data-benefit-record-actions>
                        @if(!empty($benefitRecord['edit_url']))
                            <button type="button" class="btn btn-sm btn-outline-primary"
                                    data-benefit-edit-url="{{ $benefitRecord['edit_url'] }}"
                                    data-bs-toggle="modal" data-bs-target="#prescriptionModal">
                                <i class="bi bi-pencil" aria-hidden="true"></i> Открыть черновик
                            </button>
                        @endif
                        @if(!empty($benefitRecord['print_url']))
                            <a class="btn btn-sm btn-outline-primary" href="{{ $benefitRecord['print_url'] }}"
                               target="_blank" rel="noopener" data-benefit-record-print>
                                <i class="bi bi-printer" aria-hidden="true"></i> Печать
                            </a>
                        @endif
                        @if(!empty($benefitRecord['void_url']))
                            <button type="button" class="btn btn-sm btn-outline-secondary" data-benefit-void-toggle
                                    aria-expanded="false">Аннулировать</button>
                        @endif
                    </div>
                    @if(!empty($benefitRecord['void_url']))
                        <div class="benefit-rx-void" data-benefit-void-panel hidden>
                            <label class="form-label" for="benefitVoidReason-{{ $benefitRecord['id'] }}">Причина аннулирования</label>
                            <textarea class="form-control" id="benefitVoidReason-{{ $benefitRecord['id'] }}"
                                      rows="2" minlength="5" maxlength="500" data-benefit-void-reason></textarea>
                            <p class="benefit-rx-help mt-2">Номер останется в журнале и не будет выдан повторно. Ранее распечатанные экземпляры необходимо изъять из обращения.</p>
                            <button type="button" class="btn btn-sm btn-outline-danger" data-benefit-void-url="{{ $benefitRecord['void_url'] }}">
                                Аннулировать рецепт
                            </button>
                            <button type="button" class="btn btn-sm btn-light" data-benefit-void-toggle>Отмена</button>
                            <p class="benefit-rx-help mt-2 mb-0" data-benefit-void-error role="alert" hidden></p>
                        </div>
                    @endif
                </article>
            @empty
                <div class="benefit-rx-list__empty" data-benefit-empty>
                    <i class="bi bi-file-earmark-medical" aria-hidden="true"></i>
                    <span>Льготные рецепты пока не оформлены. В окне выписки включите «Льготный рецепт».</span>
                </div>
            @endforelse
        </div>
        @if(!empty($benefitPrescriptions['pagination']['total']))
            <div class="benefit-rx-pagination">
                <span class="benefit-rx-help">
                    {{ $benefitPrescriptions['pagination']['from'] }}–{{ $benefitPrescriptions['pagination']['to'] }}
                    из {{ $benefitPrescriptions['pagination']['total'] }}
                </span>
                <nav aria-label="Страницы льготных рецептов">
                    @if(!empty($benefitPrescriptions['pagination']['previous_url']))
                        <a class="btn btn-sm btn-light" href="{{ $benefitPrescriptions['pagination']['previous_url'] }}">Назад</a>
                    @endif
                    @if(!empty($benefitPrescriptions['pagination']['next_url']))
                        <a class="btn btn-sm btn-light" href="{{ $benefitPrescriptions['pagination']['next_url'] }}">Далее</a>
                    @endif
                </nav>
            </div>
        @endif
    </section>
@endif
