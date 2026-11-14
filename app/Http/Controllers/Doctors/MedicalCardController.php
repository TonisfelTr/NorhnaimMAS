<?php

namespace App\Http\Controllers\Doctors;

use App\Http\Controllers\Controller;
use App\Http\Requests\UpdatePatientMedcardRequest;
use App\Models\LabParameter;
use App\Models\InstrumentalResearch;
use App\Models\LabResearchTemplate;
use App\Models\Patient;
use App\Models\Clinic;
use App\Models\RegistryRecord;
use App\Support\HospitalPatientAccess;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Schema;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Str;

class MedicalCardController extends Controller
{
    private function getPatientSexForLab(Patient $patient): string
    {
        $gender = strtoupper((string) ($patient->gender ?? $patient->sex ?? ''));

        return match ($gender) {
            'M', 'MALE', 'М', 'МУЖ', 'МУЖСКОЙ' => 'M',
            'F', 'FEMALE', 'Ж', 'ЖЕН', 'ЖЕНСКИЙ' => 'F',
            default => '',
        };
    }

    private function buildNormalValueForPatient($param, Patient $patient): array
    {
        $normalValues = $param->normal_values ?? [];

        if (is_string($normalValues)) {
            $normalValues = json_decode($normalValues, true) ?: [];
        }

        if (!is_array($normalValues) || empty($normalValues)) {
            return [
                'min' => null,
                'max' => null,
                'ref' => '',
            ];
        }

        $patientSex = $this->getPatientSexForLab($patient);
        $patientAge = $patient->birth_at
            ? \Carbon\Carbon::parse($patient->birth_at)->age
            : null;

        $matched = collect($normalValues)->first(function ($row) use ($patientSex, $patientAge) {
            $rowSex = strtoupper((string) ($row['sex'] ?? ''));

            $sexOk = $rowSex === ''
                || $rowSex === 'ANY'
                || $rowSex === $patientSex;

            $ageMin = $row['age_min_y'] ?? null;
            $ageMax = $row['age_max_y'] ?? null;

            $ageOk = true;

            if ($patientAge !== null) {
                if ($ageMin !== null && $ageMin !== '' && $patientAge < (float) $ageMin) {
                    $ageOk = false;
                }

                if ($ageMax !== null && $ageMax !== '' && $patientAge > (float) $ageMax) {
                    $ageOk = false;
                }
            }

            return $sexOk && $ageOk;
        });

        if (!$matched) {
            $matched = collect($normalValues)->first();
        }

        $min = $matched['min_value'] ?? null;
        $max = $matched['max_value'] ?? null;

        $text = $matched['value']
            ?? $matched['normal_value']
            ?? $matched['text']
            ?? $matched['ref']
            ?? null;

        if ($text !== null && $text !== '') {
            return [
                'min' => $min,
                'max' => $max,
                'ref' => (string) $text,
            ];
        }

        if ($min !== null && $min !== '' && $max !== null && $max !== '') {
            return [
                'min' => $min,
                'max' => $max,
                'ref' => $min . '–' . $max,
            ];
        }

        if ($min !== null && $min !== '') {
            return [
                'min' => $min,
                'max' => $max,
                'ref' => 'от ' . $min,
            ];
        }

        if ($max !== null && $max !== '') {
            return [
                'min' => $min,
                'max' => $max,
                'ref' => 'до ' . $max,
            ];
        }

        return [
            'min' => null,
            'max' => null,
            'ref' => '',
        ];
    }

    private function buildLabResearchPayloads($research): array
    {
        $rawIds = $research->parameters ?? [];

        if (is_string($rawIds)) {
            $decoded = json_decode($rawIds, true);
            $rawIds = json_last_error() === JSON_ERROR_NONE ? $decoded : [];
        }

        $paramIds = collect($rawIds ?: [])
            ->map(function ($item) {
                if (is_array($item)) {
                    return $item['id']
                        ?? $item['param_id']
                        ?? $item['parameter_id']
                        ?? $item['lab_parameter_id']
                        ?? null;
                }

                if (is_object($item)) {
                    return $item->id
                        ?? $item->param_id
                        ?? $item->parameter_id
                        ?? $item->lab_parameter_id
                        ?? null;
                }

                return $item;
            })
            ->filter()
            ->map(fn ($id) => (int) $id)
            ->unique()
            ->values();

        $resultRows = $research->results()
            ->get()
            ->keyBy('lab_parameter_id');

        $resultParamIds = $resultRows
            ->keys()
            ->map(fn ($id) => (int) $id)
            ->values();

        $allParamIds = $paramIds
            ->merge($resultParamIds)
            ->unique()
            ->values();

        $paramsById = LabParameter::query()
            ->whereIn('id', $allParamIds)
            ->get()
            ->keyBy('id');

        $patient = $research->patient;

        $paramsPayload = $allParamIds->map(function ($id) use ($paramsById, $patient) {
            $param = $paramsById->get($id);

            if (!$param) {
                return [
                    'id' => $id,
                    'name' => 'Параметр #' . $id,
                    'unit' => '',
                    'group' => '',
                    'min' => null,
                    'max' => null,
                    'ref' => '',
                ];
            }

            $normal = $patient
                ? $this->buildNormalValueForPatient($param, $patient)
                : ['min' => null, 'max' => null, 'ref' => ''];

            return [
                'id' => $param->id,
                'name' => $param->name,
                'unit' => $param->unit ?? '',
                'group' => $param->group ?? $param->group_code ?? '',
                'min' => $normal['min'],
                'max' => $normal['max'],
                'ref' => $normal['ref'],
            ];
        })->values();

        $valuesMap = $resultRows->mapWithKeys(function ($row) {
            return [
                (string) $row->lab_parameter_id => [
                    'value' => $row->value,
                    'result_value' => $row->value,
                ],
            ];
        })->toArray();

        return [
            $paramsPayload,
            $valuesMap,
        ];
    }

    public function index(Request $request, Patient $patient, HospitalPatientAccess $access)
    {
        $user = $request->user();
        abort_unless($user && $access->canRead($user, $patient), 403);

        // Audit each successful authorization, including direct URL access.
        $isAdmin = $user->hasRole('admins');
        $access->auditRead($user, $patient, $isAdmin ? 'admin-readonly' : 'doctor');

        // Единая медицинская карта для врачей и разрешённых администраторов.
        // Наличие госпитализации не определяет выбор Blade-шаблона.

        $contacts = $patient->contacts()
            ->orderBy('created_at', 'desc')
            ->get()
            ->each(static function ($contact): void {
                $contact->setAttribute(
                    'phone_href',
                    preg_replace('/[^0-9+]/', '', (string) $contact->phone)
                );
            });

        $originService = app(\App\Services\ClinicalRecordOriginService::class);

        $anamneses = $patient->anamneses()
            ->with('doctor')
            ->orderByDesc('created_at')
            ->get()
            ->map(function ($anamnesis) {
                $plainText = trim(
                    preg_replace(
                        '/\s+/u',
                        ' ',
                        strip_tags(
                            html_entity_decode(
                                (string) ($anamnesis->text ?? ''),
                                ENT_QUOTES | ENT_HTML5,
                                'UTF-8'
                            )
                        )
                    ) ?? ''
                );

                $anamnesis->setAttribute(
                    'doctor_name',
                    $anamnesis->doctor?->full_name
                    ?? $anamnesis->doctor?->fullname
                    ?? '—'
                );

                $anamnesis->setAttribute(
                    'preview',
                    $plainText !== ''
                        ? Str::limit($plainText, 260)
                        : '—'
                );

                return $anamnesis;
            });

        $originService->decorate($anamneses);

        $documents = $patient->documents()
            ->orderBy('created_at', 'desc')
            ->paginate();
        $documents->getCollection()->each(static function ($document): void {
            $document->setAttribute('card_file_extension', $document->fileExtension());
        });
        $originService->decorate($documents->getCollection());

        $medicalFilesCount = $patient->documents()
            ->medicalFile()
            ->count();

        if (!$isAdmin && ($researchID = $request->get('research'))) {
            $patient->labResearches()
                ->where('id', $researchID)
                ->whereNotNull('result_showed_at')
                ->update([
                    'result_showed_at' => now(),
                ]);
        }

        $researches = $patient->labResearches()
            ->with('patient')
            ->orderByDesc('created_at')
            ->get()
            ->map(function ($research) {
                [$paramsPayload, $valuesMap] =
                    $this->buildLabResearchPayloads($research);

                $research->setAttribute(
                    'params_payload',
                    $paramsPayload
                );

                $research->setAttribute(
                    'values_map',
                    $valuesMap
                );
                $research->setAttribute(
                    'card_collected_at',
                    ($research->collected_at ?? $research->planned_at)?->format('Y-m-d H:i') ?? ''
                );
                $research->setAttribute(
                    'card_planned_at',
                    $research->planned_at?->format('Y-m-d\TH:i') ?? ''
                );

                return $research;
            });
        $originService->decorate($researches);

        $psyTests = $patient->testSessions()
            ->orderBy('created_at', 'desc')
            ->get();

        $prescriptionCounts = $patient->prescriptions()
            ->selectRaw('DATE(created_at) as prescription_date, generic_name, COUNT(*) as prescriptions_count')
            ->whereNotNull('generic_name')
            ->groupBy(DB::raw('DATE(created_at)'), 'generic_name')
            ->get()
            ->keyBy(function ($row) {
                return $row->prescription_date . '|' . $row->generic_name;
            });

        $latestPrescriptionIds = $patient->prescriptions()
            ->selectRaw('MAX(id) as id')
            ->whereNotNull('generic_name')
            ->groupBy(DB::raw('DATE(created_at)'), 'generic_name');

        $prescriptions = $patient->prescriptions()
            ->with('drug')
            ->whereIn('id', $latestPrescriptionIds)
            ->orderByDesc('created_at')
            ->get()
            ->map(function ($prescription) use ($prescriptionCounts) {
                $date = Carbon::parse($prescription->created_at)->format('Y-m-d');
                $key = $date . '|' . $prescription->generic_name;
                $count = (int) optional($prescriptionCounts->get($key))->prescriptions_count;

                $prescription->setAttribute(
                    'daily_generic_prescriptions_count',
                    $count > 0 ? $count : 1
                );
                $prescription->setAttribute(
                    'card_group_label',
                    ucfirst($prescription->drug?->groupName() ?? 'Препарат')
                );

                return $prescription;
            })
            ->groupBy(function ($prescription) {
                return Carbon::parse($prescription->created_at)->format('Y-m-d');
            });

        $originService->decorate($prescriptions->flatten(1));

        $prescriptionDateLabels = $prescriptions->mapWithKeys(
            static fn ($group, $date): array => [
                $date => $group->first()?->date_label ?: $date,
            ]
        );

        if (config('app.debug')) {
            $labTemplates = LabResearchTemplate::orderBy('name', 'desc')->get();
        } else {
            $labTemplates = LabResearchTemplate::whereIn('doctor_id', [
                0,
                auth()->user()->doctor()->first()?->id
            ])
                ->orderBy('name', 'desc')
                ->get();
        }

        $templateParamIds = $labTemplates
            ->flatMap(function ($template) {
                $ids = $template->lab_parameters;

                if (is_string($ids)) {
                    $ids = json_decode($ids, true);
                }

                return collect($ids ?: []);
            })
            ->filter()
            ->map(fn ($id) => (int) $id)
            ->unique()
            ->values();

        $labParamsById = LabParameter::query()
            ->whereIn('id', $templateParamIds)
            ->get()
            ->keyBy('id');

        $templatesMap = $labTemplates->mapWithKeys(function ($template) use ($labParamsById, $patient) {
            $ids = $template->lab_parameters;

            if (is_string($ids)) {
                $ids = json_decode($ids, true);
            }

            $ids = collect($ids ?: [])
                ->filter()
                ->map(fn ($id) => (int) $id)
                ->unique()
                ->values();

            return [
                $template->id => $ids->map(function ($id) use ($labParamsById, $patient) {
                    $param = $labParamsById->get($id);

                    if (!$param) {
                        return [
                            'id' => $id,
                            'name' => 'Параметр #' . $id,
                            'unit' => '',
                            'group' => '',
                            'min' => null,
                            'max' => null,
                            'ref' => '',
                        ];
                    }

                    $normal = $this->buildNormalValueForPatient($param, $patient);

                    return [
                        'id' => $param->id,
                        'name' => $param->name,
                        'unit' => $param->unit ?? '',
                        'group' => $param->group ?? $param->group_code ?? '',
                        'min' => $normal['min'],
                        'max' => $normal['max'],
                        'ref' => $normal['ref'],
                    ];
                })->values(),
            ];
        });

        $instrumentalResearches = $patient
            ->instrumentalResearches()
            ->with([
                'doctor:id,name,surname,patronym',
                'media',
            ])
            ->orderByDesc('created_at')
            ->get()
            ->each(static function (InstrumentalResearch $research) use ($patient): void {
                // Prepare the modal's JSON payload in the controller, not @php in a loop.
                $research->setAttribute('card_payload', [
                    'id' => $research->id,
                    'study_type' => $research->study_type,
                    'name' => $research->name,
                    'body_area' => $research->body_area,
                    'priority' => $research->priority,
                    'status' => $research->status,
                    'with_contrast' => $research->with_contrast,
                    'planned_at' => $research->planned_at?->format('Y-m-d\TH:i'),
                    'performed_at' => $research->performed_at?->format('Y-m-d\TH:i'),
                    'result_at' => $research->result_at?->format('Y-m-d\TH:i'),
                    'organization' => $research->organization,
                    'specialist_name' => $research->specialist_name,
                    'indication' => $research->indication,
                    'description' => $research->description,
                    'conclusion' => $research->conclusion,
                    'recommendations' => $research->recommendations,
                    'type_label' => $research->type_label,
                    'files' => $research->getMedia(InstrumentalResearch::MEDIA_COLLECTION_RESULTS)
                        ->map(static function ($media) use ($patient, $research): array {
                            return [
                                'id' => $media->id,
                                'name' => $media->name,
                                'file_name' => $media->file_name,
                                'mime_type' => $media->mime_type,
                                'size' => $media->size,
                                'view_url' => route('doctors.patients.instrumentals.media.view', [
                                    'patient' => $patient->id,
                                    'instrumentalResearch' => $research->id,
                                    'media' => $media->id,
                                ]),
                            ];
                        })->values(),
                ]);
            });
        $originService->decorate($instrumentalResearches);

        $instrumentalResearchTypes = InstrumentalResearch::TYPES;
        $instrumentalResearchStatuses = InstrumentalResearch::STATUSES;
        $instrumentalResearchPriorities = InstrumentalResearch::PRIORITIES;

        $visitHistoryData = $this->buildVisitHistory($request, $patient, $access);
        $visitAuditData = $this->buildMedicalVisitAuditContext(
            $request, $patient, $user, $access, $visitHistoryData['visitHistory']
        );
        // NORHNAIM_TELEMED_JOURNAL_V1
        // clinic-telemedicine-timezones:provider-journal-navigation:v1:begin
        $visitAuditData = app(\App\Services\TelemedicineJournalService::class)->decorate(
            $request, $patient, $visitAuditData
        );
        // NORHNAIM_CLINIC_TELEMEDICINE_PROVIDER_V1: navigation only, never create on GET.
        $visitAuditData = app(\App\Services\TelemedicineProviderSessionService::class)->decorate(
            $request, $patient, $visitAuditData
        );
        // clinic-telemedicine-timezones:provider-journal-navigation:v1:end
        $patientDisability = $this->buildPatientDisabilityCard($patient, $user, $access);
        // NORHNAIM_BENEFIT_PRESCRIPTIONS_V1
        $benefitPrescriptions = app(\App\Services\BenefitPrescriptionService::class)
            ->cardContext($request, $patient, $patientDisability);

        $medicalCardContext = $this->buildMedicalCardViewContext($request, $patient, $user, $documents);

        return view('doctors.reception.medical_card', array_merge(compact(
            'patient',
            'contacts',
            'anamneses',
            'documents',
            'medicalFilesCount',
            'labTemplates',
            'psyTests',
            'researches',
            'instrumentalResearches',
            'instrumentalResearchTypes',
            'instrumentalResearchStatuses',
            'instrumentalResearchPriorities',
            'templatesMap',
            'prescriptions',
            'prescriptionDateLabels',
            'patientDisability',
            'benefitPrescriptions',
        ), $visitHistoryData, $medicalCardContext, $visitAuditData));
    }

    /**
     * Prepare all non-presentational medical-card data before rendering Blade.
     * In particular: permission checks, validation bags, demographics, child
     * documents and prescription metadata must not trigger queries in a view.
     *
     * @return array<string, mixed>
     */
    private function buildMedicalCardViewContext(
        Request $request,
        Patient $patient,
        ?\App\Models\User $user,
        LengthAwarePaginator $documents
    ): array {
        $patient->loadMissing(['doctor', 'epicrises']);

        $originService = app(\App\Services\ClinicalRecordOriginService::class);
        $originService->decorate($patient->epicrises);
        $currentDiagnosisOrigin = $originService->currentDiagnosisOrigin($patient);

        $assignedDoctor = $patient->doctor;
        $assignedDoctorName = $assignedDoctor
            ? collect([
                $assignedDoctor->surname,
                $assignedDoctor->name,
                $assignedDoctor->patronym,
            ])->filter(static fn ($value) => filled($value))->implode(' ')
            : 'Не назначен';

        // The same transfer service that Blade previously called, now server-side.
        $canTransferPatient = $user !== null
            && app(\App\Services\PatientDoctorTransferService::class)
                ->canTransfer($user, $patient);

        $currentDoctorId = $user?->doctor()->first()?->id;
        $isTreatingDoctor = $currentDoctorId !== null
            && $patient->doctor_id !== null
            && (int) $currentDoctorId === (int) $patient->doctor_id;

        // Map validation messages to the form that actually submitted them.
        $oldInput = $request->session()->getOldInput();
        $submittedForm = $oldInput['_medical_card_form'] ?? null;
        if (!is_string($submittedForm)) {
            $submittedForm = null;
        }
        if ($submittedForm === null) {
            if (array_key_exists('surname', $oldInput) && array_key_exists('email', $oldInput)) {
                $submittedForm = 'patient_info';
            } elseif (array_key_exists('title', $oldInput) && array_key_exists('diag_source', $oldInput)) {
                $submittedForm = 'anamnesis';
            }
        }

        $viewErrors = $request->session()->get('errors');
        $medicalCardDefaultErrors = $viewErrors instanceof \Illuminate\Support\ViewErrorBag
            ? $viewErrors->getBag('default')
            : new \Illuminate\Support\MessageBag();
        $patientInfoErrors = $viewErrors instanceof \Illuminate\Support\ViewErrorBag
            ? $viewErrors->getBag('patientUpdate')
            : new \Illuminate\Support\MessageBag();
        $anamnesisErrors = $viewErrors instanceof \Illuminate\Support\ViewErrorBag
            ? $viewErrors->getBag('anamnesis')
            : new \Illuminate\Support\MessageBag();

        if (!$patientInfoErrors->any() && $submittedForm === 'patient_info') {
            $patientInfoErrors = $medicalCardDefaultErrors;
        }
        if (!$anamnesisErrors->any() && $submittedForm === 'anamnesis') {
            $anamnesisErrors = $medicalCardDefaultErrors;
        }
        $otherMedicalCardErrors = in_array($submittedForm, ['patient_info', 'anamnesis'], true)
            ? new \Illuminate\Support\MessageBag()
            : $medicalCardDefaultErrors;
        $medicalCardErrorTab = $patientInfoErrors->any()
            ? 'patient-info'
            : ($anamnesisErrors->any() ? 'anamnesis' : null);

        // Avoid parsing Patient::birth_at's dd.mm.YYYY accessor as an ISO date.
        $rawPatientBirthAt = $patient->getRawOriginal('birth_at') ?: $patient->birth_at;
        $patientBirthDateObject = null;
        try {
            $patientBirthDateObject = $rawPatientBirthAt
                ? Carbon::parse($rawPatientBirthAt)
                : null;
        } catch (\Throwable $exception) {
            $patientBirthDateObject = null;
        }
        $patientAge = $patientBirthDateObject?->age;
        $isMinorPatient = $patientAge !== null && $patientAge < 18;
        $isChildUnder14 = $patientAge !== null && $patientAge < 14;
        $patientBirthDateInput = $patientBirthDateObject?->format('Y-m-d') ?? '';
        $patientGenderLabel = match ($patient->gender) {
            'M' => 'Мужской',
            'F' => 'Женский',
            default => 'не указан',
        };
        $patientAgeLabel = null;
        if ($patientAge !== null) {
            $mod10 = $patientAge % 10;
            $mod100 = $patientAge % 100;
            $word = $mod10 === 1 && $mod100 !== 11
                ? 'год'
                : (($mod10 >= 2 && $mod10 <= 4 && ($mod100 < 12 || $mod100 > 14))
                    ? 'года'
                    : 'лет');
            $patientAgeLabel = $patientAge . ' ' . $word;
        }

        $patientDocumentTypeLabels = [
            'passport_rf' => 'Паспорт гражданина РФ',
            'birth_certificate' => 'Свидетельство о рождении',
            'foreign_passport' => 'Иностранный паспорт',
            'residence_permit' => 'Вид на жительство',
            'temporary_identity' => 'Временное удостоверение личности',
            'foreign_identity' => 'Иностранный документ, удостоверяющий личность',
            'other' => 'Другой документ',
        ];
        $representativeTypeLabels = [
            'mother' => 'Мать',
            'father' => 'Отец',
            'adoptive_parent' => 'Усыновитель',
            'guardian' => 'Опекун',
            'trustee' => 'Попечитель',
            'authorized' => 'Уполномоченный представитель',
            'other' => 'Другой представитель',
        ];
        $authorityDocumentTypeLabels = [
            'guardianship_order' => 'Документ об установлении опеки/попечительства',
            'power_of_attorney' => 'Доверенность',
            'other' => 'Другой документ',
        ];
        $dateLabel = static function ($value): string {
            if (!$value) return '—';
            try {
                return Carbon::parse($value)->format('d.m.Y');
            } catch (\Throwable $exception) {
                return (string) $value;
            }
        };

        $patientIdentityDocument = DB::table('patient_identity_documents')
            ->where('patient_id', $patient->id)
            ->where('is_active', true)
            ->orderByDesc('is_primary')
            ->orderByDesc('id')
            ->first();

        $primaryRepresentative = null;
        $representativeIdentityDocument = null;
        $representativeAuthorityDocument = null;
        if ($isMinorPatient) {
            $primaryRepresentative = DB::table('patient_representatives')
                ->where('patient_id', $patient->id)
                ->where('is_active', true)
                ->orderByDesc('is_primary')
                ->orderByDesc('id')
                ->first();
            if ($primaryRepresentative) {
                $representativeIdentityDocument = DB::table('patient_representative_documents')
                    ->where('representative_id', $primaryRepresentative->id)
                    ->where('document_purpose', 'identity')
                    ->where('is_active', true)
                    ->orderByDesc('is_primary')
                    ->orderByDesc('id')
                    ->first();
                $representativeAuthorityDocument = DB::table('patient_representative_documents')
                    ->where('representative_id', $primaryRepresentative->id)
                    ->where('document_purpose', 'authority')
                    ->where('is_active', true)
                    ->orderByDesc('is_primary')
                    ->orderByDesc('id')
                    ->first();
            }
        }
        $patientIdentityDocumentLabel = $patientIdentityDocument
            ? ($patientIdentityDocument->document_name
                ?: ($patientDocumentTypeLabels[$patientIdentityDocument->document_type] ?? 'Документ пациента'))
            : ($isChildUnder14 ? 'Свидетельство о рождении' : 'Документ пациента');
        $primaryRepresentativeFullName = $primaryRepresentative
            ? collect([
                $primaryRepresentative->surname,
                $primaryRepresentative->name,
                $primaryRepresentative->patronym,
            ])->filter()->implode(' ')
            : null;
        $patientIdentityIssuedLabel = $dateLabel($patientIdentityDocument?->issued_at);
        $primaryRepresentativeBirthLabel = $dateLabel($primaryRepresentative?->birth_at);
        $representativeIdentityIssuedLabel = $dateLabel($representativeIdentityDocument?->issued_at);
        $representativeAuthorityIssuedLabel = $dateLabel($representativeAuthorityDocument?->issued_at);

        $currentDiagnosis = $patient->diagnose;
        $currentDiagnosisCode = $currentDiagnosis?->code;
        $currentDiagnosisTitle = $currentDiagnosis?->title ?? $currentDiagnosis?->name ?? null;
        $patientAvatarInitials = mb_substr((string) $patient->name, 0, 1)
            . mb_substr((string) $patient->surname, 0, 1);
        $medicalCardTitle = $patient->getFullNameWithInitials() . ' - медицинская карта пациента';
        $patientDocumentsCount = $documents->total();
        $lastPatientDocumentUpdateLabel = $documents->first()?->updated_at?->format('d.m.Y H:i') ?? '—';

        $epicrises = $patient->epicrises;
        $epicrisesCount = $epicrises->count();
        $epicrisesWithDiagnosisCount = $epicrises->filter(static fn ($epicrisis) =>
            filled($epicrisis->mkb10) || filled($epicrisis->diagnosis_title_display)
        )->count();
        $lastEpicrisisDateLabel = $epicrises->sortByDesc('created_at')->first()
            ?->created_at?->format('d.m.Y') ?? '—';

        $prescriptionPatientName = collect([
            $patient->surname, $patient->name, $patient->patronym,
        ])->filter(static fn ($value) => filled($value))->implode(' ');
        $prescriptionPatientInitials = collect([
            $patient->surname, $patient->name,
        ])->filter(static fn ($value) => filled($value))
            ->map(static fn ($value) => mb_strtoupper(mb_substr((string) $value, 0, 1)))
            ->take(2)
            ->implode('');
        $prescriptionBirthDate = $patientBirthDateObject?->format('d.m.Y')
            ?? 'дата рождения не указана';
        $prescriptionBirthDateValue = $patientBirthDateObject?->format('Y-m-d') ?? '';
        $prescriptionDiagnosisCode = data_get($patient, 'prescription_diagnosis_code')
            ?: $currentDiagnosis?->code;

        return compact(
            'assignedDoctor', 'assignedDoctorName', 'canTransferPatient', 'currentDoctorId',
            'isTreatingDoctor', 'patientInfoErrors', 'anamnesisErrors', 'otherMedicalCardErrors',
            'medicalCardErrorTab', 'patientAge', 'patientAgeLabel', 'isMinorPatient',
            'isChildUnder14', 'patientBirthDateInput', 'patientGenderLabel',
            'patientDocumentTypeLabels', 'representativeTypeLabels', 'authorityDocumentTypeLabels',
            'patientIdentityDocument', 'primaryRepresentative', 'representativeIdentityDocument',
            'representativeAuthorityDocument', 'patientIdentityDocumentLabel',
            'primaryRepresentativeFullName', 'patientIdentityIssuedLabel',
            'primaryRepresentativeBirthLabel', 'representativeIdentityIssuedLabel',
            'representativeAuthorityIssuedLabel', 'currentDiagnosisCode', 'currentDiagnosisTitle',
            'currentDiagnosisOrigin',
            'patientAvatarInitials', 'medicalCardTitle', 'patientDocumentsCount', 'lastPatientDocumentUpdateLabel',
            'epicrisesCount', 'epicrisesWithDiagnosisCount', 'lastEpicrisisDateLabel',
            'prescriptionPatientName', 'prescriptionPatientInitials', 'prescriptionBirthDate',
            'prescriptionBirthDateValue', 'prescriptionDiagnosisCode'
        );
    }

    /**
     * Patient-wide disability statement. A legacy checkbox is never accepted
     * as verified proof. View logic is prepared in the controller, not Blade.
     *
     * @return array<string, mixed>
     */
    private function buildPatientDisabilityCard(
        Patient $patient,
        \App\Models\User $viewer,
        HospitalPatientAccess $access
    ): array {
        $available = Schema::hasTable('patient_disability_records');
        $record = $available
            ? DB::table('patient_disability_records')
                ->where('patient_id', $patient->getKey())->first()
            : null;
        $legacy = in_array($patient->disability, [true, 1, '1', 't', 'true'], true);
        $status = $record?->status ?? ($legacy ? 'present' : 'unknown');
        $indefinite = in_array($record?->indefinite, [true, 1, '1', 't', 'true'], true);
        $expiry = $record?->valid_until;
        $expired = $status === 'present' && !$indefinite && $expiry !== null
            && (string) $expiry < today()->toDateString();
        $linkedDocument = null;
        if ($record?->evidence_document_id) {
            $linkedDocument = $patient->documents()
                ->whereKey($record->evidence_document_id)->first(['id', 'name']);
        }
        $hasPaperDetails = filled($record?->document_number) && filled($record?->issued_by);
        $hasRecognizedEvidence = $record
            && (($record->source_type === 'mse_certificate'
                    && ($linkedDocument !== null || $hasPaperDetails))
                || ($record->source_type === 'electronic_mse'
                    && $linkedDocument !== null));
        $isVerified = $status === 'present' && !$expired
            && $record?->verified_at !== null
            && $record?->verified_by_user_id !== null
            && filled($record?->disability_group)
            && filled($record?->established_at)
            && $hasRecognizedEvidence
            && ($indefinite || $expiry !== null && (string) $expiry >= today()->toDateString());

        $groupLabels = [
            'I' => 'I группа',
            'II' => 'II группа',
            'III' => 'III группа',
            'child' => 'Ребёнок-инвалид',
        ];
        $sourceLabels = [
            'patient_statement' => 'Со слов пациента',
            'gosuslugi_screen' => 'Сведения с экрана Госуслуг (без электронного документа)',
            'mse_certificate' => 'Бумажная справка МСЭ',
            'electronic_mse' => 'Электронная справка МСЭ с подписью',
            'medical_document' => 'Медицинская документация',
            'other' => 'Другой источник',
        ];
        $dateLabel = static function (?string $value): string {
            if (!$value) {
                return '—';
            }
            return Carbon::parse($value)->format('d.m.Y');
        };
        $documents = $available && Schema::hasTable('medicine_documents')
            ? $patient->documents()->orderByDesc('created_at')
                ->limit(75)->get(['id', 'name'])
                ->map(static fn ($document): array => [
                    'id' => $document->id,
                    'name' => $document->name ?: 'Документ #' . $document->id,
                ])->all()
            : [];
        if ($linkedDocument && !in_array((int) $linkedDocument->id, array_column($documents, 'id'), true)) {
            $documents[] = [
                'id' => $linkedDocument->id,
                'name' => $linkedDocument->name ?: 'Документ #' . $linkedDocument->id,
            ];
        }

        // These describe DIFFERENT facts:
        //  1. What the patient/source says about disability status.
        //  2. Whether our staff actually reviewed the supporting MSE document.
        // Neither statement implies an online lookup in a government registry.
        $presenceLabel = match ($status) {
            'present' => 'Установлена по представленным сведениям',
            'absent' => 'В представленных сведениях не указана',
            default => 'Сведения отсутствуют',
        };
        $presenceShortLabel = match ($status) {
            'present' => 'Указана инвалидность',
            'absent' => 'Инвалидность не указана',
            default => 'Нет сведений',
        };
        $presenceHint = match ($status) {
            'present' => $expired
                ? 'В источнике указана инвалидность, но срок действия истёк. Актуальность необходимо уточнить.'
                : ($record === null
                    ? 'Основание — старая отметка в анкете. Группа, срок и источник ещё не проверены.'
                    : 'Наличие, группа и даты перенесены из указанного источника. Это не результат проверки государственного реестра.'),
            'absent' => 'В представленных документах или со слов пациента инвалидность не указана. Это не доказывает её отсутствие.',
            default => 'Пока нет данных. Не следует трактовать это как отсутствие инвалидности.',
        };
        $documentCheckLabel = $isVerified
            ? 'Проверен сотрудником'
            : ($record?->verified_at !== null
                ? 'Требуется повторная проверка' : 'Не проверен');
        $documentCheckShortLabel = $isVerified ? 'Проверен' : 'Не проверен';
        $documentCheckHint = $isVerified
            ? 'Сотрудник сверил сведения с документом МСЭ; результат отмечен внутри клиники. Автоматической проверки в госреестре не было.'
            : ($record?->verified_at !== null
                ? 'Ранее была отметка о проверке, но сейчас она не считается действующей (например, истёк срок или отсутствует документ).'
                : ($record?->source_type === 'gosuslugi_screen'
                    ? 'Страница или скриншот Госуслуг позволяет внести сведения, но не заменяет подписанный документ МСЭ.'
                    : 'Проверка справки МСЭ сотрудником ещё не зарегистрирована.'));

        return [
            'presence_label' => $presenceLabel,
            'presence_short_label' => $presenceShortLabel,
            'presence_hint' => $presenceHint,
            'document_check_label' => $documentCheckLabel,
            'document_check_short_label' => $documentCheckShortLabel,
            'document_check_hint' => $documentCheckHint,
            'available' => $available,
            'can_edit' => $available && $access->canEdit($viewer, $patient),
            'has_record' => $record !== null,
            'legacy_unverified' => $legacy && $record === null,
            'status' => $status,
            'verified' => (bool) $isVerified,
            'previously_verified' => $record?->verified_at !== null,
            'expired' => $expired,
            'badge' => $status === 'unknown' ? 'Нет сведений'
                : ($status === 'absent' ? 'Не указана'
                : ($isVerified ? 'Подтверждена' : 'Не подтверждена')),
            'group' => $record?->disability_group,
            'group_label' => $groupLabels[$record?->disability_group ?? ''] ?? 'Группа не указана',
            'established_at' => $record?->established_at,
            'established_label' => $dateLabel($record?->established_at),
            'valid_until' => $expiry,
            'valid_until_label' => $indefinite ? 'Бессрочно' : $dateLabel($expiry),
            'indefinite' => $indefinite,
            'cause' => $record?->cause,
            'source_type' => $record?->source_type ?? 'patient_statement',
            'source_label' => $sourceLabels[$record?->source_type ?? 'patient_statement'],
            'document_name' => $record?->document_name,
            'document_number' => $record?->document_number,
            'document_issued_at' => $record?->document_issued_at,
            'document_issued_label' => $dateLabel($record?->document_issued_at),
            'issued_by' => $record?->issued_by,
            'evidence_document_id' => $record?->evidence_document_id,
            'evidence_document_name' => $linkedDocument?->name,
            'notes' => $record?->notes,
            'verified_at_label' => $record?->verified_at
                ? Carbon::parse($record->verified_at)->format('d.m.Y H:i') : null,
            'verified_by_user_id' => $record?->verified_by_user_id,
            'documents' => $documents,
            'can_verify' => $status === 'present' && !$isVerified && !$expired
                && filled($record?->disability_group)
                && filled($record?->established_at)
                && ($indefinite || $expiry !== null && (string) $expiry >= today()->toDateString())
                && $hasRecognizedEvidence,
        ];
    }

    /**
     * Appointment history with MANUALLY confirmed patient attendance and
     * consultation outcome. A booking or an elapsed appointment time never
     * counts as a performed medical service or a successful WebRTC session.
     *
     * @return array<string, mixed>
     */
    private function buildVisitHistory(Request $request, Patient $patient, HospitalPatientAccess $access): array
    {
        $format = (string) $request->query('visit_format', 'all');
        if (!in_array($format, ['all', 'in_person', 'telemedicine'], true)) {
            $format = 'all';
        }

        $period = (string) $request->query('visit_period', 'all');
        if (!in_array($period, ['all', 'past', 'upcoming'], true)) {
            $period = 'all';
        }

        $outcome = (string) $request->query('visit_outcome', 'all');
        if (!in_array($outcome, ['all', 'completed', 'not_held', 'absent', 'unconfirmed'], true)) {
            $outcome = 'all';
        }

        $hasRecords = Schema::hasTable('registry_records');
        $hasFormat = $hasRecords && Schema::hasColumn('registry_records', 'appointment_format');
        $hasEncounters = $hasRecords && Schema::hasTable('medical_encounters');

        $totals = [
            'all' => 0,
            'in_person' => 0,
            'telemedicine' => 0,
            'upcoming' => 0,
            'completed' => 0,
            'absent' => 0,
            'unknown_format' => 0,
        ];
        $filters = ['format' => $format, 'period' => $period, 'outcome' => $outcome];
        $viewer = $request->user();

        if (!$hasRecords) {
            return [
                'visitHistoryAvailable' => false,
                'visitHistoryHasFormat' => false,
                'visitHistoryHasEncounters' => false,
                'visitHistoryCanEdit' => false,
                'visitHistoryTotals' => $totals,
                'visitHistoryFilters' => $filters,
                'visitHistory' => new LengthAwarePaginator(
                    [], 0, 10, 1,
                    ['path' => $request->url(), 'pageName' => 'visits_page']
                ),
            ];
        }

        // Deliberately show soft-deleted appointments in the history without
        // allowing them to be modified. Patient-scope is enforced by index().
        $base = RegistryRecord::query()->withTrashed()
            ->where('patient_id', $patient->getKey());

        if (!$viewer || !$access->isDebugAdmin($viewer)) {
            $viewerDoctor = $viewer?->doctor()->first();
            if ($viewerDoctor?->clinic_id) {
                $base->where('clinic_id', $viewerDoctor->clinic_id);
            } elseif ($viewerDoctor) {
                $base->where('doctor_id', $viewerDoctor->getKey());
            } else {
                $base->whereRaw('1 = 0');
            }
        }

        $now = Carbon::now();
        $totals['all'] = (clone $base)->count();
// clinic-telemedicine-timezones:timezone-card-count:v1:begin
$upcomingQuery = (clone $base)->whereNull('deleted_at');
        \App\Support\ClinicAppointmentTime::whereComparedToNow($upcomingQuery, '>', $now);
        $totals['upcoming'] = $upcomingQuery->count();
// clinic-telemedicine-timezones:timezone-card-count:v1:end

        if ($hasFormat) {
            $totals['in_person'] = (clone $base)->where('appointment_format', 'in_person')->count();
            $totals['telemedicine'] = (clone $base)->where('appointment_format', 'telemedicine')->count();
            $totals['unknown_format'] = $totals['all']
                - $totals['in_person'] - $totals['telemedicine'];
        } else {
            $totals['unknown_format'] = $totals['all'];
            $format = 'all';
            $filters['format'] = 'all';
        }

        // NORHNAIM_REGISTRY_APPOINTMENT_HISTORY_V1
        $totals['completed'] = (clone $base)
            ->where('appointment', true)
            ->count();
        $totals['absent'] = (clone $base)
            ->where('appointment', false)
            ->whereNull('deleted_at')
            ->where('for_datetime', '<=', $now)
            ->count();

        $query = (clone $base)->with('doctor');
        if ($hasFormat && $format !== 'all') {
            $query->where('appointment_format', $format);
        }

        if ($period === 'past') {
// clinic-telemedicine-timezones:timezone-card-past:v1:begin
\App\Support\ClinicAppointmentTime::whereComparedToNow($query, '<=', $now);
// clinic-telemedicine-timezones:timezone-card-past:v1:end
        } elseif ($period === 'upcoming') {
// clinic-telemedicine-timezones:timezone-card-upcoming:v1:begin
$query->whereNull('deleted_at');
            \App\Support\ClinicAppointmentTime::whereComparedToNow($query, '>', $now);
// clinic-telemedicine-timezones:timezone-card-upcoming:v1:end
        }

        if ($outcome !== 'all') {
            if ($outcome === 'completed') {
                $query->where('appointment', true);
            } elseif (in_array($outcome, ['not_held', 'absent'], true)) {
                $query->where('appointment', false)
                    ->whereNull('deleted_at')
                    ->where('for_datetime', '<=', $now);
            } elseif ($outcome === 'unconfirmed') {
                $query->where('appointment', false)
                    ->whereNull('deleted_at')
                    ->where('for_datetime', '>', $now);
            }
        }

        if ($period === 'upcoming') {
// clinic-telemedicine-timezones:timezone-card-ascending:v1:begin
\App\Support\ClinicAppointmentTime::orderByStart($query);
// clinic-telemedicine-timezones:timezone-card-ascending:v1:end
        } else {
// clinic-telemedicine-timezones:timezone-card-descending:v1:begin
\App\Support\ClinicAppointmentTime::orderByStart($query, 'desc');
// clinic-telemedicine-timezones:timezone-card-descending:v1:end
        }

        $history = $query->paginate(10, ['*'], 'visits_page')->appends([
            'tab' => 'visits',
            'visit_format' => $filters['format'],
            'visit_period' => $filters['period'],
            'visit_outcome' => $filters['outcome'],
        ]);

        $recordIds = $history->getCollection()->pluck('id')->all();
        $encounters = ($hasEncounters && $recordIds !== [])
            ? \App\Models\MedicalEncounter::query()
                ->whereIn('registry_record_id', $recordIds)->get()->keyBy('registry_record_id')
            : collect();

        $clinicIds = $history->getCollection()->pluck('clinic_id')
            ->filter()->unique()->values()->all();
        $clinicNames = $clinicIds
            ? Clinic::query()->whereIn('id', $clinicIds)->pluck('name', 'id')
            : collect();

        $fields = [
            'visit_type', 'noncompletion_reason', 'patient_condition', 'disability_group', 'disability_details',
            'complaints', 'disease_history', 'life_history', 'social_history',
            'mental_status', 'diagnosis_code', 'diagnosis_title',
            'conclusion', 'recommendations', 'service_code', 'service_name',
        ];
        $oldInput = $request->session()->getOldInput();
        $oldVisitId = (int) ($oldInput['_visit_id'] ?? 0);

        $history->setCollection($history->getCollection()->map(
            static function (RegistryRecord $record) use (
                $hasFormat, $clinicNames, $now, $encounters, $fields, $oldInput, $oldVisitId
            ): array {
// clinic-telemedicine-timezones:timezone-card-date-buildVisitHistory:v1:begin
$appointmentTime = \App\Support\ClinicAppointmentTime::describe($record);
                $displayDate = \App\Support\ClinicAppointmentTime::displayDate($record);
                $date = $displayDate ? Carbon::instance($displayDate) : null;
// clinic-telemedicine-timezones:timezone-card-date-buildVisitHistory:v1:end
                $deleted = $record->deleted_at !== null;
                $upcoming = $date !== null && $date->gt($now);
                $rawFormat = $hasFormat ? (string) $record->getAttribute('appointment_format') : '';
                $visitFormat = in_array($rawFormat, ['telemedicine', 'in_person'], true)
                    ? $rawFormat : 'unknown';

                $encounter = $encounters->get($record->getKey());
                $visitHappened = (bool) $record->appointment;
                $attendance = $visitHappened
                    ? 'present'
                    : ($upcoming ? 'unknown' : 'absent');
                $consultation = $visitHappened
                    ? 'completed'
                    : ($upcoming ? 'not_started' : 'not_held');
                $protocol = [];
                foreach ($fields as $field) {
                    $protocol[$field] = (string) ($encounter?->{$field} ?? '');
                }
                // Prepare old submitted values in the controller, not @php in Blade.
                $useOld = $oldVisitId > 0 && $oldVisitId === (int) $record->getKey();
                $form = $protocol;
                if ($useOld) {
                    foreach ($fields as $field) {
                        if (array_key_exists($field, $oldInput)) {
                            $form[$field] = (string) ($oldInput[$field] ?? '');
                        }
                    }
                }
                $formAttendance = $useOld
                    ? (string) ($oldInput['attendance_status'] ?? $attendance)
                    : $attendance;
                $formConsultation = $useOld
                    ? (string) ($oldInput['consultation_status'] ?? $consultation)
                    : $consultation;

                $doctor = $record->doctor;
                $doctorName = trim(implode(' ', array_filter([
                    $doctor?->surname, $doctor?->name, $doctor?->patronym,
                ], static fn ($part): bool => filled($part))));

                $status = match ($consultation) {
                    'completed' => ['label' => 'Состоялась', 'class' => 'done'],
                    'in_progress' => ['label' => 'Проводится', 'class' => 'progress'],
                    'not_held' => ['label' => 'Не состоялась', 'class' => 'failed'],
                    default => $upcoming
                        ? ['label' => 'Запланирована', 'class' => 'upcoming']
                        : ['label' => 'Результат не отмечен', 'class' => 'pending'],
                };

                $attendanceLabel = match ($attendance) {
                    'present' => $visitFormat === 'telemedicine'
                        ? 'Подключение отмечено' : 'Явка отмечена',
                    'absent' => $visitFormat === 'telemedicine'
                        ? 'Не подключился' : 'Не явился',
                    default => 'Явка не отмечена',
                };

                $canShowClinicalData = $attendance === 'present'
                    && in_array($consultation, ['in_progress', 'completed'], true);
                $hasCompletedProtocol = $attendance === 'present'
                    && $consultation === 'completed'
                    && filled($protocol['conclusion']);

                // Avoid showing a stale clinical draft for a missed appointment.
                $preview = $consultation === 'not_held'
                    ? ($protocol['noncompletion_reason'] !== ''
                        ? 'Причина: ' . Str::limit(trim(strip_tags($protocol['noncompletion_reason'])), 145)
                        : null)
                    : ($canShowClinicalData && $protocol['conclusion'] !== ''
                        ? Str::limit(trim(strip_tags($protocol['conclusion'])), 155)
                        : ($canShowClinicalData && $protocol['diagnosis_title'] !== ''
                            ? Str::limit(trim(strip_tags($protocol['diagnosis_title'])), 155)
                            : null));

                return [
                    'id' => (int) $record->getKey(),
                    'doctor_id' => (int) ($record->doctor_id ?? 0),
// clinic-telemedicine-timezones:timezone-card-time-data:v1:begin
'appointment_time' => $appointmentTime,
                    'day' => $date?->format('d') ?? '—',
// clinic-telemedicine-timezones:timezone-card-time-data:v1:end
                    'month' => $date?->format('m.Y') ?? '—',
                    'date' => $date?->format('d.m.Y') ?? 'Дата не указана',
                    'weekday' => $date?->translatedFormat('l') ?? '',
                    'time' => $date?->format('H:i') ?? '—',
                    // Do not enable any attendance/protocol editing before
                    // the registered start time. Missing times fail closed.
                    'can_edit_now' => $date !== null && !$upcoming,
                    'is_future' => $upcoming,
// clinic-telemedicine-timezones:timezone-card-start-label:v1:begin
'scheduled_start_label' => $date ? $date->format('d.m.Y в H:i') . ' (' . $appointmentTime['timezone_label'] . ')' : 'Не указана',
// clinic-telemedicine-timezones:timezone-card-start-label:v1:end
                    'doctor_name' => $doctorName !== '' ? $doctorName : 'Врач не указан',
                    'clinic_name' => $record->clinic_id
                        ? (string) ($clinicNames->get($record->clinic_id) ?: 'Клиника не указана')
                        : 'Частная практика / не указана',
                    'format' => $visitFormat,
                    'format_label' => match ($visitFormat) {
                        'telemedicine' => 'Телемедицина',
                        'in_person' => 'Очный приём',
                        default => 'Формат не указан',
                    },
                    'record_deleted' => $deleted,
                    'registry_marked' => (bool) $record->appointment,
                    'attendance_status' => $attendance,
                    'attendance_label' => $attendanceLabel,
                    'consultation_status' => $consultation,
                    'consultation_label' => $status['label'],
                    'consultation_style' => $status['class'],
                    'visit_type_label' => match ($protocol['visit_type']) {
                        'initial' => 'Первичный приём',
                        'repeat' => 'Повторный приём',
                        default => 'Тип обращения не указан',
                    },
                    'protocol' => $protocol,
                    'form' => $form,
                    'form_attendance_status' => $formAttendance,
                    'form_consultation_status' => $formConsultation,
                    'protocol_preview' => $preview,
                    'has_encounter' => $encounter !== null,
                    // Print button requires a completed clinical consultation.
                    'has_protocol' => $hasCompletedProtocol,
                    'can_show_clinical_data' => $canShowClinicalData,
                    'updated_at' => $encounter?->updated_at?->format('d.m.Y H:i'),
                    'updated_by_user_id' => $encounter?->updated_by_user_id,
                ];
            }
        ));

        return [
            'visitHistoryAvailable' => true,
            'visitHistoryHasFormat' => $hasFormat,
            'visitHistoryHasEncounters' => $hasEncounters,
            'visitHistoryCanEdit' => $viewer !== null && $access->canEdit($viewer, $patient),
            'visitHistoryTotals' => $totals,
            'visitHistoryFilters' => $filters,
            'visitHistory' => $history,
        ];
    }


    /**
     * Formal doctor attestations and independent WebRTC signaling log.
     * All queries are patient-scoped and use the same clinic visibility rules
     * as the existing visit history. No queries or authorization in Blade.
     *
     * @return array<string,mixed>
     */
    private function buildMedicalVisitAuditContext(
        Request $request,
        Patient $patient,
        ?\App\Models\User $user,
        HospitalPatientAccess $access,
        LengthAwarePaginator $history
    ): array {
        $confirmationReady = Schema::hasTable('medical_visit_confirmations');
        $eventsReady = Schema::hasTable('medical_visit_events');
        $roomsReady = Schema::hasTable('telemedicine_room_bindings');
        $ready = $confirmationReady && $eventsReady && $roomsReady;
        $canEdit = $user && $access->canEdit($user, $patient);
        $doctorId = $user?->doctor()->first()?->getKey();

        $historyIds = $history->getCollection()->pluck('id')->all();
        $confirmed = $confirmationReady && $historyIds !== []
            ? DB::table('medical_visit_confirmations')
                ->whereIn('registry_record_id', $historyIds)
                ->get()->keyBy('registry_record_id')
            : collect();

        $historyEvents = $eventsReady && $historyIds !== []
            ? DB::table('medical_visit_events')
                ->whereIn('registry_record_id', $historyIds)
                ->where('source', 'clinician')
                ->orderByDesc('occurred_at')->orderByDesc('id')
                ->get()->groupBy('registry_record_id')
            : collect();
        $userIds = $confirmed->pluck('confirmed_by_user_id')
            ->merge($historyEvents->flatten(1)->pluck('actor_user_id'))
            ->filter()->unique()->all();
                $userLabels = collect();

                if ($userIds) {
                    $doctorLabels = \App\Models\Doctor::query()
                        ->whereIn('user_id', $userIds)
                        ->get(['user_id', 'surname', 'name', 'patronym'])
                        ->mapWithKeys(static function (\App\Models\Doctor $profile): array {
                            $fullName = trim(implode(' ', array_filter([
                                $profile->surname,
                                $profile->name,
                                $profile->patronym,
                            ])));

                            return [$profile->user_id => $fullName];
                        });

                    $patientLabels = \App\Models\Patient::query()
                        ->whereIn('user_id', $userIds)
                        ->get(['user_id', 'surname', 'name', 'patronym'])
                        ->mapWithKeys(static function (\App\Models\Patient $profile): array {
                            $fullName = trim(implode(' ', array_filter([
                                $profile->surname,
                                $profile->name,
                                $profile->patronym,
                            ])));

                            return [$profile->user_id => $fullName];
                        });

                    $nurseLabels = \App\Models\Nurse::query()
                        ->whereIn('user_id', $userIds)
                        ->get(['user_id', 'surname', 'name', 'patronym'])
                        ->mapWithKeys(static function (\App\Models\Nurse $profile): array {
                            $fullName = trim(implode(' ', array_filter([
                                $profile->surname,
                                $profile->name,
                                $profile->patronym,
                            ])));

                            return [$profile->user_id => $fullName];
                        });

                    // Приоритет: врач, затем пациент, затем медсестра.
                    // Учетные записи без профиля останутся с fallback "Сотрудник #ID".
                    $userLabels = $doctorLabels
                        ->union($patientLabels)
                        ->union($nurseLabels);
                }

        $history->setCollection($history->getCollection()->map(static function (array $visit) use (
            $confirmed, $userLabels, $ready, $canEdit, $doctorId, $historyEvents
        ): array {
            $confirmation = $confirmed->get($visit['id']);
            $visit['confirmation_outcome'] = $confirmation?->outcome;
            $visit['confirmation_reason'] = $confirmation?->reason;
            $visit['confirmation_at'] = $confirmation?->confirmed_at
                ? Carbon::parse($confirmation->confirmed_at)->format('d.m.Y H:i') : null;
            $visit['confirmation_by'] = $confirmation
                ? ($userLabels->get($confirmation->confirmed_by_user_id)
                    ?: 'Сотрудник #' . $confirmation->confirmed_by_user_id) : null;
            $visit['confirmation_history'] = $historyEvents->get($visit['id'], collect())
                ->take(12)->map(static function ($event) use ($userLabels): array {
                    $meta = is_string($event->details)
                        ? (json_decode($event->details, true) ?: []) : (array) ($event->details ?? []);
                    return [
                        'at' => Carbon::parse($event->occurred_at)->format('d.m.Y H:i'),
                        'by' => $userLabels->get($event->actor_user_id)
                            ?: 'Сотрудник #' . ($event->actor_user_id ?? '—'),
                        'outcome' => $event->event_type === 'consultation_confirmed'
                            ? 'Состоялся' : 'Не состоялся',
                        'is_correction' => (bool) ($meta['is_correction'] ?? false),
                        'correction_reason' => (string) ($meta['correction_reason'] ?? ''),
                    ];
                })->all();
            $visit['can_confirm_visit'] = $ready && $canEdit && $doctorId !== null
                && (int) $visit['doctor_id'] === (int) $doctorId
                && !($visit['is_future'] || $visit['record_deleted'])
                && $visit['can_edit_now'];
            return $visit;
        }));

        $emptyLog = new LengthAwarePaginator([], 0, 8, 1, [
            'path' => $request->url(), 'pageName' => 'telemed_page',
        ]);
        if (!$ready || !Schema::hasTable('registry_records')
            || !Schema::hasColumn('registry_records', 'appointment_format')) {
            return [
                'medicalVisitAuditReady' => false,
                'telemedicineEventHookConfigured' => $this->telemedicineAuditBridgeAlive(),
                'telemedicineLog' => $emptyLog,
            ];
        }

        $audit = app(\App\Services\MedicalVisitAuditService::class);
        // NORHNAIM_TELEMED_ACCESS_V1
        $appointments = app(\App\Services\TelemedicineJournalService::class)
            ->journalAppointments($user, $patient)
            ->where('appointment_format', 'telemedicine')
            ->when($request->boolean('telemed_archive'),
                static fn ($query) => $query->whereNotNull('registry_records.deleted_at'),
                static fn ($query) => $query->whereNull('registry_records.deleted_at'))
            ->with('doctor')
// clinic-telemedicine-timezones:timezone-journal-order:v1:begin
->orderByRaw('COALESCE(registry_records.appointment_starts_at_utc, registry_records.for_datetime) desc')
            ->orderByDesc('registry_records.id')
// clinic-telemedicine-timezones:timezone-journal-order:v1:end
            ->paginate(8, ['*'], 'telemed_page')
            ->appends(['tab' => 'telemedicine-log', 'telemed_archive' => $request->boolean('telemed_archive') ? 1 : 0]);

        $ids = $appointments->getCollection()->pluck('id')->all();
        $confirmations = $ids
            ? DB::table('medical_visit_confirmations')->whereIn('registry_record_id', $ids)
                ->get()->keyBy('registry_record_id')
            : collect();

        // Aggregate flags from ALL technical events, not merely the last 60
        // timeline entries shown in the UI.
        $presence = $ids
            ? DB::table('medical_visit_events')
                ->whereIn('registry_record_id', $ids)
                ->where('source', 'signaling')
                ->whereIn('event_type', ['participant_joined', 'media_connected'])
                ->distinct()
                ->get(['registry_record_id', 'event_type', 'participant_role'])
                ->groupBy('registry_record_id')
            : collect();

        // Identify intervals when both roles reported media in the SAME room.
        // Merely seeing a doctor and a patient online at different times is NOT
        // a simultaneous connection.
        $mediaTransitions = $ids
            ? DB::table('medical_visit_events')
                ->whereIn('registry_record_id', $ids)
                ->where('source', 'signaling')
                ->whereIn('event_type', [
                    'media_connected', 'media_disconnected', 'participant_left',
                    'call_ended', 'connection_failed',
                ])
                ->orderBy('occurred_at')->orderBy('id')
                ->get(['registry_record_id', 'room_id', 'event_type', 'participant_role'])
                ->groupBy('registry_record_id')
            : collect();

        $technicalLabels = [
            'session_scheduled' => 'Сеанс запланирован',
            // clinic-telemedicine-timezones:provider-audit-labels:v1:begin
            'session_cancelled' => 'Сеанс удалён из расписания',
            'provider_creation_requested' => 'Запрошено создание встречи',
            'provider_prepared' => 'Подключение к приёму подготовлено',
            'provider_prepared_manually' => 'Прикреплена проверенная встреча',
            'provider_creation_unconfirmed' => 'Результат создания встречи не подтверждён',
            'provider_opened' => 'Выдана ссылка на видеосвязь',
            // clinic-telemedicine-timezones:provider-audit-labels:v1:end
            'room_created' => 'Комната подготовлена',
            'join_attempt' => 'Попытка подключения',
            'participant_joined' => 'Участник подключился к комнате',
            'media_connected' => 'Устройство сообщило об установлении медиасоединения',
            'media_disconnected' => 'Устройство сообщило о прерывании медиасоединения',
            'participant_left' => 'Участник покинул комнату',
            'call_ended' => 'Видеосеанс завершён',
            'invitation_created' => 'Приглашение создано',
            'invitation_activated' => 'Приглашение стало доступно',
            'invitation_expired' => 'Срок действия приглашения истёк',
            'connection_failed' => 'Ошибка подключения',
            'access_denied' => 'Доступ к комнате отклонён',
            'consultation_confirmed' => 'Врач подтвердил проведение приёма',
            'consultation_not_held' => 'Врач отметил, что приём не состоялся',
        ];
        $roleLabels = ['doctor' => 'Врач', 'patient' => 'Пациент', 'system' => 'Сервер'];
        $actorIds = $confirmations->pluck('confirmed_by_user_id')->filter()->unique()->all();
        $actorNames = collect();

        if ($actorIds) {
            $actorDoctorLabels = \App\Models\Doctor::query()
                ->whereIn('user_id', $actorIds)
                ->get(['user_id', 'surname', 'name', 'patronym'])
                ->mapWithKeys(static function (\App\Models\Doctor $profile): array {
                    $fullName = trim(implode(' ', array_filter([
                        $profile->surname,
                        $profile->name,
                        $profile->patronym,
                    ])));

                    return [$profile->user_id => $fullName];
                });

            $actorPatientLabels = \App\Models\Patient::query()
                ->whereIn('user_id', $actorIds)
                ->get(['user_id', 'surname', 'name', 'patronym'])
                ->mapWithKeys(static function (\App\Models\Patient $profile): array {
                    $fullName = trim(implode(' ', array_filter([
                        $profile->surname,
                        $profile->name,
                        $profile->patronym,
                    ])));

                    return [$profile->user_id => $fullName];
                });

            $actorNurseLabels = \App\Models\Nurse::query()
                ->whereIn('user_id', $actorIds)
                ->get(['user_id', 'surname', 'name', 'patronym'])
                ->mapWithKeys(static function (\App\Models\Nurse $profile): array {
                    $fullName = trim(implode(' ', array_filter([
                        $profile->surname,
                        $profile->name,
                        $profile->patronym,
                    ])));

                    return [$profile->user_id => $fullName];
                });

            $actorNames = $actorDoctorLabels
                ->union($actorPatientLabels)
                ->union($actorNurseLabels);
        }

        $appointments->setCollection($appointments->getCollection()->map(static function ($record) use (
            $patient, $confirmations, $technicalLabels, $roleLabels, $actorNames,
            $doctorId, $canEdit, $presence, $mediaTransitions
        ): array {
            $appointmentId = (int) $record->getKey();
            $confirmation = $confirmations->get($appointmentId);
            $eventsQuery = DB::table('medical_visit_events')
                ->where('registry_record_id', $appointmentId)
                ->where('patient_id', $patient->getKey());
            $eventCount = (clone $eventsQuery)->count();
            // Show latest 60, while clearly indicating if older events exist.
            $events = $eventsQuery->orderByDesc('occurred_at')->orderByDesc('id')
                ->limit(60)->get()->reverse()->values();

            $presenceRows = $presence->get($appointmentId, collect());
            $doctorJoined = $presenceRows->contains(static fn ($row) =>
                $row->participant_role === 'doctor');
            $patientJoined = $presenceRows->contains(static fn ($row) =>
                $row->participant_role === 'patient');
            $simultaneousMedia = false;
            $rooms = [];
            foreach ($mediaTransitions->get($appointmentId, collect()) as $transition) {
                $roomKey = $transition->room_id ?: 'unknown-room';
                $rooms[$roomKey] ??= ['doctor' => false, 'patient' => false];
                $role = (string) $transition->participant_role;
                if ($transition->event_type === 'call_ended') {
                    $rooms[$roomKey] = ['doctor' => false, 'patient' => false];
                } elseif (in_array($role, ['doctor', 'patient'], true)) {
                    if ($transition->event_type === 'media_connected') {
                        $rooms[$roomKey][$role] = true;
                    } elseif (in_array($transition->event_type, [
                        'media_disconnected', 'participant_left', 'connection_failed',
                    ], true)) {
                        $rooms[$roomKey][$role] = false;
                    }
                }
                if ($rooms[$roomKey]['doctor'] && $rooms[$roomKey]['patient']) {
                    $simultaneousMedia = true;
                }
            }
            $timeline = $events->map(static function ($event) use (
                $technicalLabels, $roleLabels
            ): array {
                $meta = is_string($event->details)
                    ? (json_decode($event->details, true) ?: []) : (array) ($event->details ?? []);
                return [
                    'at' => Carbon::parse($event->occurred_at)->format('d.m.Y H:i:s'),
                    'event' => $event->event_type,
                    'label' => $technicalLabels[$event->event_type] ?? $event->event_type,
                    'role' => $event->source === 'staff'
                        ? 'Сотрудник #' . (int) $event->actor_user_id
                        : ($roleLabels[$event->participant_role ?? ''] ?? '—'),
                    'management_reason' => $event->source === 'staff' ? (string) ($meta['reason'] ?? '') : '',
                    'source' => $event->source,
                    'room_id' => $event->room_id,
                    'is_correction' => (bool) ($meta['is_correction'] ?? false),
                    'correction_reason' => (string) ($meta['correction_reason'] ?? ''),
                    'reason_code' => (string) ($meta['reason_code'] ?? ''),
                    'duration_seconds' => isset($meta['duration_seconds'])
                        ? (int) $meta['duration_seconds'] : null,
                ];
            })->all();

            $doctor = $record->doctor;
            $name = trim(implode(' ', array_filter([
                $doctor?->surname, $doctor?->name, $doctor?->patronym,
            ], static fn ($value) => filled($value))));
$appointmentTime = \App\Support\ClinicAppointmentTime::describe($record);
                $displayDate = \App\Support\ClinicAppointmentTime::displayDate($record);
                $date = $displayDate ? Carbon::instance($displayDate) : null;
            $deleted = $record->deleted_at !== null;

            return [
                'id' => $appointmentId,
                'appointment_time' => $appointmentTime,
                'date' => $date?->format('d.m.Y H:i') ?? 'Не указана',
                'doctor' => $name ?: 'Врач не указан',
                'deleted' => $deleted,
                'confirmation_outcome' => $confirmation?->outcome,
                'confirmation_reason' => $confirmation?->reason,
                'confirmation_at' => $confirmation?->confirmed_at
                    ? Carbon::parse($confirmation->confirmed_at)->format('d.m.Y H:i') : null,
                'confirmation_by' => $confirmation
                    ? ($actorNames->get($confirmation->confirmed_by_user_id)
                        ?: 'Сотрудник #' . $confirmation->confirmed_by_user_id) : null,
                'can_confirm' => !$deleted && $date !== null && !$date->isFuture()
                    && $canEdit && $doctorId !== null
                    && (int) $doctorId === (int) $record->doctor_id,
                'event_count' => $eventCount,
                'events_truncated' => $eventCount > 60,
                'doctor_joined' => $doctorJoined,
                'patient_joined' => $patientJoined,
                'both_media_connected' => $simultaneousMedia,
                'events' => $timeline,
            ];
        }));

        return [
            'medicalVisitAuditReady' => true,
            'telemedicineEventHookConfigured' => $this->telemedicineAuditBridgeAlive(),
            'telemedicineLog' => $appointments,
        ];
    }

    private function telemedicineAuditBridgeAlive(): bool
    {
        // HMAC-authenticated heartbeat sent by actual signaling container.
        // Configuration alone is not proof of an active integration.
        $lastSeen = (int) Cache::get('telemedicine_audit:signaling_heartbeat', 0);
        return strlen((string) config('medical_visit_audit.secret')) >= 32
            && $lastSeen > 0 && $lastSeen >= time() - 300;
    }

    public function update(
        UpdatePatientMedcardRequest $request,
        Patient $patient,
        HospitalPatientAccess $access
    ) {
        $user = $request->user();

        $patient->update($request->validated());

        return redirect()->back()->with([
            'status' => 'success',
            'message' => 'Данные пациента были успешно обновлены.'
        ]);
    }
}
