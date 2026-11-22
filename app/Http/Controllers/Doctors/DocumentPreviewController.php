<?php

declare(strict_types=1);

namespace App\Http\Controllers\Doctors;

use App\Http\Controllers\Controller;
use App\Models\MedicineDocument;
use App\Models\Patient;
use Illuminate\Support\Facades\Storage;

/**
 * Inline preview of documents already available from the doctor's medical card.
 * Shares the same authenticated doctor-route group as the download action.
 */
final class DocumentPreviewController extends Controller
{
    public function __invoke(Patient $patient, MedicineDocument $document)
    {
        // Never allow a document from another patient to be served via this URL.
        abort_unless((int) $patient->getKey() === (int) $document->patient_id, 404);

        $path = trim((string) $document->storage_path);
        abort_unless($path !== '' && Storage::exists($path), 404);

        // Check both file extension and detected MIME type before inline serving.
        // DOC/DOCX and other unknown formats are always downloaded.
        $extension = strtolower((string) pathinfo($path, PATHINFO_EXTENSION));
        $allowed = [
            'pdf' => 'application/pdf',
            'jpg' => 'image/jpeg',
            'jpeg' => 'image/jpeg',
            'png' => 'image/png',
            'webp' => 'image/webp',
            'gif' => 'image/gif',
        ];

        $mime = (string) (Storage::mimeType($path) ?: '');
        if (!isset($allowed[$extension]) || $mime !== $allowed[$extension]) {
            return redirect()->route('doctors.patients.documents.download', [
                $patient->getKey(),
                $document->getKey(),
            ]);
        }

        return Storage::response(
            $path,
            'document-' . $document->getKey() . '.' . $extension,
            [
                'Content-Type' => $mime,
                'Cache-Control' => 'private, no-store, max-age=0',
                'X-Content-Type-Options' => 'nosniff',
                'X-Robots-Tag' => 'noindex, nofollow',
                'X-Frame-Options' => 'SAMEORIGIN',
            ],
            'inline'
        );
    }
}
