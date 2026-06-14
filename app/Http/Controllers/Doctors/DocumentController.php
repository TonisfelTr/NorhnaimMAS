<?php

namespace App\Http\Controllers\Doctors;

use App\Http\Controllers\Controller;
use App\Http\Requests\DeleteDocumentRequest;
use App\Http\Requests\DocumentUploadRequest;
use App\Models\MedicineDocument;
use App\Models\Patient;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class DocumentController extends Controller
{
    public function upload(DocumentUploadRequest $request)
    {
        /** @var UploadedFile $file */
        $file = $request->file('document_upload');

        if (!$file || !$file->isValid()) {
            return redirect()
                ->back()
                ->withErrors(['document_upload' => 'Файл не был загружен или повреждён.'])
                ->withInput();
        }

        $extension = $file->getClientOriginalExtension();

        $fileName = 'P' . $request->patient_id . '-' . now()->format('YmdHis') . '-' . Str::random(6) . '.' . $extension;

        $relativePath = $file->storeAs(
            'private/documents/' . $request->patient_id,
            $fileName
        );

        $document = new MedicineDocument();
        $document->name = $request->name;
        $document->patient_id = $request->patient_id;
        $document->doctor_id = $request->doctor_id;
        $document->storage_path = $relativePath;
        $document->description = $request->description;
        $document->medical_file = $request->boolean('medical_file');
        $document->save();

        return redirect()
            ->back()
            ->with('success', 'Документ успешно загружен.');
    }

    public function download(Patient $patient, MedicineDocument $document)
    {
        if ($patient->id !== $document->patient_id) {
            abort(403);
            return;
        }

        return Storage::download($document->storage_path);
    }

    public function delete(Patient $patient, MedicineDocument $document)
    {
        if ($document->patient_id == $patient->id) {
            if (auth()->user()->doctor->id == $document->doctor_id) {
                $document->delete();

                return redirect()->back()->with(['message' => 'Файл успешно удалён.']);
            }
        }

        abort(403);
    }
}
