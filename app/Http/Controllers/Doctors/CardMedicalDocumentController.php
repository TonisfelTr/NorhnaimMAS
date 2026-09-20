<?php

namespace App\Http\Controllers\Doctors;

use App\Http\Controllers\Controller;
use App\Http\Requests\SaveCardMedicalDocumentRequest;
use App\Models\Patient;
use App\Models\PatientClinicalDocument;
use App\Support\CardDocuments\DocumentAccess;
use App\Support\CardDocuments\DocumentCatalog;
use App\Support\CardDocuments\DocumentSources;
use App\Support\CardDocuments\DocumentText;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class CardMedicalDocumentController extends Controller
{
    public function __construct(
        private readonly DocumentAccess $access,
        private readonly DocumentSources $sources,
    ) {
    }

    public function index(Request $request)
    {
        $filters = $request->validate([
            'patient_id' => ['nullable', 'integer', 'min:1'],
            'type' => ['nullable', Rule::in(array_keys(DocumentCatalog::TYPES))],
            'status' => ['nullable', Rule::in(['draft', 'final'])],
        ]);
        $this->access->doctor();
        $patient = !empty($filters['patient_id']) ? Patient::findOrFail($filters['patient_id']) : null;
        $query = PatientClinicalDocument::query();
        if ($patient) {
            $this->access->patient($patient);
            $query->where('patient_id', $patient->getKey());
        } else {
            // Общий журнал не раскрывает документы чужих пациентов.
            $query->where('author_id', auth()->id())
                ->whereIn('patient_id', $this->access->searchablePatients()->select('patients.id'));
        }
        if (!empty($filters['type'])) {
            $query->where('type', $filters['type']);
        }
        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }
        $documents = $query->latest('issued_at')->latest('id')->paginate(15)->withQueryString();
        // При наличии policy проверяем и строки общего журнала до вывода снимков.
        $documents->setCollection($documents->getCollection()->filter(function ($document): bool {
            return $document->patient && $this->access->canView($document->patient);
        }));

        return view('doctors.card_documents.index', compact('patient', 'documents'));
    }

    public function create(Request $request, string $type)
    {
        abort_unless(isset(DocumentCatalog::menu()[$type]), 404);
        $data = $request->validate([
            'patient_id' => ['nullable', 'integer', 'min:1'],
            'q' => ['nullable', 'string', 'max:100'],
        ]);
        $doctor = $this->access->doctor();
        if (empty($data['patient_id'])) {
            $query = $this->access->searchablePatients();
            $q = trim((string) ($data['q'] ?? ''));
            if ($q !== '') {
                foreach (preg_split('/\s+/u', $q, -1, PREG_SPLIT_NO_EMPTY) ?: [] as $word) {
                    $pattern = '%' . mb_strtolower(str_replace(['\\', '%', '_'], ['\\\\', '\\%', '\\_'], $word)) . '%';
                    $query->where(function ($query) use ($pattern): void {
                        $query->whereRaw('LOWER(surname) LIKE ?', [$pattern])
                            ->orWhereRaw('LOWER(name) LIKE ?', [$pattern])
                            ->orWhereRaw('LOWER(patronym) LIKE ?', [$pattern])
                            ->orWhereRaw('LOWER(medcard_number) LIKE ?', [$pattern]);
                    });
                }
            }
            $patients = $query->orderBy('surname')->orderBy('name')->paginate(15)->withQueryString();
            $patients->setCollection($patients->getCollection()->filter(fn ($item) => $this->access->canView($item)));
            $config = DocumentCatalog::menu()[$type];

            return view('doctors.card_documents.select_patient', compact('type', 'config', 'patients', 'q'));
        }

        $patient = Patient::findOrFail($data['patient_id']);
        $this->access->patient($patient);
        if ($type === 'epicrisis') {
            return redirect()->route('doctors.patients.medical_card', [
                'patient' => $patient->getKey(), 'tab' => 'epicrisis', 'open_epicrisis' => '1',
            ]);
        }

        $document = new PatientClinicalDocument([
            'type' => $type, 'status' => 'draft', 'issued_at' => today(),
            'fields' => [], 'source_options' => [], 'source_snapshot' => [],
            'patient_snapshot' => $this->sources->patientSnapshot($patient),
            'author_name' => $this->sources->doctorName($doctor), 'author_position' => 'Врач-психиатр',
            'period_mode' => 'all', 'version' => 1,
            'include_diagnosis' => $type !== 'certificate',
            'diagnosis_code' => $type !== 'certificate' ? $patient->diagnose?->code : null,
            'diagnosis_title' => $type !== 'certificate'
                ? ($patient->diagnose?->title ?? $patient->diagnose?->name) : null,
        ]);
        $config = DocumentCatalog::get($type);

        return view('doctors.card_documents.form', compact('document', 'patient', 'config'));
    }

    public function store(SaveCardMedicalDocumentRequest $request, Patient $patient)
    {
        $this->access->patient($patient);
        $document = new PatientClinicalDocument();
        $this->save($request, $patient, $document);

        return redirect()->route('doctors.card_documents.show', $document)
            ->with('mcd_success', $document->status === 'final' ? 'Документ оформлен.' : 'Черновик сохранён.');
    }

    public function show(PatientClinicalDocument $clinicalDocument)
    {
        $this->access->document($clinicalDocument);
        $document = $clinicalDocument;
        $config = DocumentCatalog::get($document->type);

        return view('doctors.card_documents.show', compact('document', 'config'));
    }

    public function edit(PatientClinicalDocument $clinicalDocument)
    {
        $this->access->document($clinicalDocument, true);
        $document = $clinicalDocument;
        $patient = $document->patient;
        $config = DocumentCatalog::get($document->type);

        return view('doctors.card_documents.form', compact('document', 'patient', 'config'));
    }

    public function update(SaveCardMedicalDocumentRequest $request, PatientClinicalDocument $clinicalDocument)
    {
        $this->access->document($clinicalDocument, true);
        if (!$request->filled('version')) {
            throw ValidationException::withMessages(['version' => 'Не указан номер версии. Откройте документ заново.']);
        }
        $document = DB::transaction(function () use ($request, $clinicalDocument) {
            $document = PatientClinicalDocument::query()->lockForUpdate()->findOrFail($clinicalDocument->getKey());
            $this->access->document($document, true);
            if ($document->version !== (int) $request->input('version')) {
                throw ValidationException::withMessages([
                    'version' => 'Документ уже изменён в другом окне. Скопируйте свои правки и откройте актуальную версию.',
                ]);
            }
            if ($document->type !== $request->input('type')) {
                throw ValidationException::withMessages(['type' => 'Нельзя изменить вид сохранённого документа.']);
            }
            $this->save($request, $document->patient, $document);

            return $document;
        });

        return redirect()->route('doctors.card_documents.show', $document)->with('mcd_success', 'Документ сохранён.');
    }

    public function print(PatientClinicalDocument $clinicalDocument)
    {
        $this->access->document($clinicalDocument);
        $document = $clinicalDocument;
        $config = DocumentCatalog::get($document->type);

        return view('doctors.card_documents.print', compact('document', 'config'));
    }

    public function blank(string $type)
    {
        $this->access->doctor();
        abort_unless(isset(DocumentCatalog::menu()[$type]), 404);
        $config = $type === 'epicrisis' ? [
            'title' => 'Эпикриз',
            'fields' => [
                'kind' => 'Вид: этапный / переводной / выписной',
                'period' => 'Период наблюдения / лечения', 'diagnosis' => 'Диагноз',
                'history' => 'Жалобы и анамнез', 'status' => 'Состояние пациента',
                'research' => 'Обследования', 'therapy' => 'Проведённое лечение',
                'outcome' => 'Динамика и исход', 'plan' => 'Рекомендации',
            ],
        ] : DocumentCatalog::get($type);

        return view('doctors.card_documents.blank', compact('config'));
    }

    public function legacyEpicrisisPrint(Patient $patient, string $epicrisisId)
    {
        $this->access->patient($patient);
        // Загружаем только через связь пациента — ID другого пациента не подойдёт.
        $epicrisis = $patient->epicrises()->whereKey($epicrisisId)->firstOrFail();
        $snapshot = $this->sources->patientSnapshot($patient);
        $text = DocumentText::plain($epicrisis->text);
        $date = $this->sources->date($epicrisis->getRawOriginal('created_at'));

        return view('doctors.card_documents.epicrisis_print', compact('epicrisis', 'snapshot', 'text', 'date'));
    }

    public function previewSources(Request $request, Patient $patient)
    {
        $this->access->patient($patient);
        $data = $request->validate([
            'issued_at' => ['required', 'date_format:Y-m-d', 'before_or_equal:today'],
            'period_mode' => ['required', Rule::in(['all', 'range'])],
            'period_from' => ['nullable', 'required_if:period_mode,range', 'date_format:Y-m-d'],
            'period_to' => ['nullable', 'required_if:period_mode,range', 'date_format:Y-m-d',
                'after_or_equal:period_from', 'before_or_equal:issued_at'],
            'source_options' => ['sometimes', 'array', 'max:' . count(DocumentCatalog::SOURCES)],
            'source_options.*' => ['string', 'distinct', Rule::in(array_keys(DocumentCatalog::SOURCES))],
        ]);
        $sources = $this->sources->build($patient, $data);

        return response()->json([
            'html' => view('doctors.card_documents.partials.sources', compact('sources'))->render(),
            'count' => array_sum(array_map(fn ($section) => count($section['items']), $sources)),
        ]);
    }

    private function save(SaveCardMedicalDocumentRequest $request, Patient $patient, PatientClinicalDocument $document): void
    {
        $data = $request->validated();
        $doctor = $this->access->doctor();
        $snapshot = $this->sources->build($patient, $data);
        $existing = $document->exists;
        $document->fill(collect($data)->only([
            'type', 'issued_at', 'organization', 'organization_details', 'author_position',
            'recipient', 'purpose', 'include_diagnosis', 'period_mode', 'source_options', 'fields',
        ])->all());
        $document->patient_id = $patient->getKey();
        if (!$existing) {
            $document->author_id = auth()->id();
            $document->doctor_id = $doctor->getKey();
            $document->author_name = $this->sources->doctorName($doctor);
        }
        $document->diagnosis_code = $data['include_diagnosis'] ? ($data['diagnosis_code'] ?? null) : null;
        $document->diagnosis_title = $data['include_diagnosis'] ? ($data['diagnosis_title'] ?? null) : null;
        $document->period_from = $data['period_mode'] === 'range' ? $data['period_from'] : null;
        $document->period_to = $data['period_mode'] === 'range' ? $data['period_to'] : null;
        $document->patient_snapshot = $this->sources->patientSnapshot($patient);
        $document->source_snapshot = $snapshot;
        $document->status = $data['intent'] === 'final' ? 'final' : 'draft';
        $document->finalized_at = $document->status === 'final' ? now() : null;
        $document->version = $existing ? $document->version + 1 : 1;
        $document->save();
    }
}
