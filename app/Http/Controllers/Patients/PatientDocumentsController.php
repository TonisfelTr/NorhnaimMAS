<?php

namespace App\Http\Controllers\Patients;

use App\Http\Controllers\Controller;
use App\Models\MedicineDocument;
use App\Models\Patient;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class PatientDocumentsController extends Controller
{
    private const CATEGORIES = [
        'lab_result' => 'Результаты анализов',
        'imaging' => 'Снимки и исследования',
        'discharge_summary' => 'Выписки из стационара',
        'consultation' => 'Заключения специалистов',
        'certificate' => 'Справки',
        'other' => 'Другие документы',
    ];

    private function currentPatient(Request $request): Patient
    {
        $user = $request->user();

        abort_unless($user && $user->hasRole('patients'), 403);

        return $user->patient()->firstOrFail();
    }

    public function index(Request $request): View
    {
        $patient = $this->currentPatient($request);
        $tab = $request->query('tab', 'clinic');

        if (!in_array($tab, ['clinic', 'mine'], true)) {
            $tab = 'clinic';
        }

        $source = $tab === 'mine' ? 'patient' : 'clinic';
        $query = trim((string) $request->query('q', ''));

        $documentsQuery = $patient->documents()
            ->where('source', $source)
            ->orderByDesc('created_at')
            ->orderByDesc('id');

        if ($query !== '') {
            $documentsQuery->where(function ($builder) use ($query): void {
                $builder->where('name', 'ilike', '%' . $query . '%')
                    ->orWhere('description', 'ilike', '%' . $query . '%')
                    ->orWhere('original_name', 'ilike', '%' . $query . '%');
            });
        }

        $documents = $documentsQuery->paginate(15)->withQueryString();

        return view('patients.documents.index', [
            'patient' => $patient,
            'documents' => $documents,
            'activeTab' => $tab,
            'search' => $query,
            'categories' => self::CATEGORIES,
            'clinicCount' => $patient->documents()->where('source', 'clinic')->count(),
            'patientCount' => $patient->documents()->where('source', 'patient')->count(),
            'pendingReviewCount' => $patient->documents()
                ->where('source', 'patient')
                ->whereNull('reviewed_at')
                ->count(),
        ]);
    }

    public function upload(Request $request): RedirectResponse
    {
        $patient = $this->currentPatient($request);

        $validated = $request->validate([
            'documents' => ['required', 'array', 'min:1', 'max:10'],
            'documents.*' => [
                'required',
                'file',
                'mimes:pdf,jpg,jpeg,png,doc,docx,txt',
                'max:20480',
            ],
            'category' => ['required', Rule::in(array_keys(self::CATEGORIES))],
            'description' => ['nullable', 'string', 'max:1000'],
        ], [
            'documents.required' => 'Выберите хотя бы один файл.',
            'documents.max' => 'За один раз можно загрузить не более 10 файлов.',
            'documents.*.mimes' => 'Допустимые форматы: PDF, JPG, PNG, DOC, DOCX и TXT.',
            'documents.*.max' => 'Размер каждого файла не должен превышать 20 МБ.',
            'category.required' => 'Выберите категорию документа.',
        ]);

        $storedPaths = [];

        try {
            DB::transaction(function () use (
                $request,
                $patient,
                $validated,
                &$storedPaths
            ): void {
                foreach ($request->file('documents', []) as $file) {
                    if (!$file || !$file->isValid()) {
                        throw new \RuntimeException('Один из файлов не удалось загрузить.');
                    }

                    $path = $file->store('private/documents/' . $patient->id, 'local');

                    if (!$path) {
                        throw new \RuntimeException('Не удалось сохранить файл в хранилище.');
                    }

                    $storedPaths[] = $path;

                    $originalName = basename(str_replace(
                        '\\',
                        '/',
                        (string) $file->getClientOriginalName()
                    ));
                    $displayName = pathinfo($originalName, PATHINFO_FILENAME);
                    $displayName = trim($displayName) !== ''
                        ? trim($displayName)
                        : 'Документ пациента';

                    $document = new MedicineDocument();
                    $document->patient_id = $patient->id;
                    $document->doctor_id = null;
                    $document->name = mb_substr($displayName, 0, 255);
                    $document->description = $validated['description'] ?? null;
                    $document->medical_file = true;
                    $document->storage_path = $path;
                    $document->source = 'patient';
                    $document->category = $validated['category'];
                    $document->original_name = mb_substr($originalName, 0, 255);
                    $document->mime_type = $file->getMimeType();
                    $document->file_size = $file->getSize();
                    $document->uploaded_by_user_id = $request->user()->id;
                    $document->save();
                }
            });
        } catch (\Throwable $exception) {
            foreach ($storedPaths as $path) {
                Storage::disk('local')->delete($path);
            }

            report($exception);

            return back()
                ->withInput()
                ->withErrors(['documents' => 'Не удалось сохранить документы. Попробуйте ещё раз.']);
        }

        return redirect()
            ->route('patients.documents.index', ['tab' => 'mine'])
            ->with('success', 'Документы успешно загружены. Они появятся в медицинской карте врача с отметкой «Загружено пациентом».');
    }

    public function preview(Request $request, int $document)
    {
        $patient = $this->currentPatient($request);
        $record = $patient->documents()->whereKey($document)->firstOrFail();

        $mime = $record->mime_type ?: Storage::disk('local')->mimeType($record->storage_path);

        if (!in_array($mime, ['application/pdf', 'image/jpeg', 'image/png'], true)) {
            return $this->downloadFile($record);
        }

        $path = Storage::disk('local')->path($record->storage_path);
        abort_unless(is_file($path), 404);

        return response()->file($path, [
            'Content-Type' => $mime,
            'X-Content-Type-Options' => 'nosniff',
            'Content-Disposition' => 'inline; filename="' . $this->safeFilename($record) . '"',
        ]);
    }

    public function download(Request $request, int $document)
    {
        $patient = $this->currentPatient($request);
        $record = $patient->documents()->whereKey($document)->firstOrFail();

        return $this->downloadFile($record);
    }

    public function destroy(Request $request, int $document): RedirectResponse
    {
        $patient = $this->currentPatient($request);
        $record = $patient->documents()
            ->whereKey($document)
            ->where('source', 'patient')
            ->where('uploaded_by_user_id', $request->user()->id)
            ->firstOrFail();

        $path = $record->storage_path;
        $record->delete();

        if ($path) {
            Storage::disk('local')->delete($path);
        }

        return redirect()
            ->route('patients.documents.index', ['tab' => 'mine'])
            ->with('success', 'Ваш документ удалён.');
    }

    private function downloadFile(MedicineDocument $document)
    {
        abort_unless(Storage::disk('local')->exists($document->storage_path), 404);

        return Storage::disk('local')->download(
            $document->storage_path,
            $this->safeFilename($document),
            ['X-Content-Type-Options' => 'nosniff']
        );
    }

    private function safeFilename(MedicineDocument $document): string
    {
        $name = $document->original_name ?: ($document->name ?: 'document');
        $name = str_replace(["\r", "\n", '"', '\\', '/'], '_', $name);

        return $name !== '' ? $name : 'document';
    }
}
