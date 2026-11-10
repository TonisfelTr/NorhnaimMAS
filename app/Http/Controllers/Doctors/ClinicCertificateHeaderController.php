<?php

namespace App\Http\Controllers\Doctors;

use App\Http\Middleware\ClinicSettingsAccessMiddleware;
use App\Models\Patient;
use App\Support\ClinicDocumentHeaderScope;
use App\Support\HospitalPatientAccess;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ClinicCertificateHeaderController extends SettingsBaseController
{
    public function index(Request $request): View
    {
        $this->authorizeSettings($request);

        $currentClinic = ClinicDocumentHeaderScope::currentClinicForUser(
            $request->user()
        );

        abort_unless(
            $currentClinic,
            422,
            'Для пользователя не определена текущая клиника.'
        );

        $rootClinic = ClinicDocumentHeaderScope::root($currentClinic);
        $family = ClinicDocumentHeaderScope::family($rootClinic);
        $media = $rootClinic->getFirstMedia('certificate_header');

        return $this->settingsView(
            'doctors.settings.certificate-header',
            [
                'clinic' => $currentClinic,
                'currentClinic' => $currentClinic,
                'rootClinic' => $rootClinic,
                'headerMedia' => $media,
                'familyClinics' => $family,
            ]
        );
    }

    public function store(Request $request): RedirectResponse
    {
        $this->authorizeSettings($request);

        $request->validate([
            'certificate_header' => [
                'required',
                'file',
                'image',
                'mimes:png,jpg,jpeg,webp',
                'max:5120',
                'dimensions:min_width=300,min_height=60,max_width=5000,max_height=2500',
            ],
        ], [
            'certificate_header.required' => 'Выберите изображение шапки.',
            'certificate_header.image' => 'Шапка должна быть изображением.',
            'certificate_header.mimes' => 'Разрешены PNG, JPG, JPEG и WEBP.',
            'certificate_header.max' => 'Размер файла не должен превышать 5 МБ.',
            'certificate_header.dimensions' =>
                'Размер изображения должен быть от 300×60 до 5000×2500 пикселей.',
        ]);

        $rootClinic = $this->rootSettingsClinic($request);

        $rootClinic
            ->addMediaFromRequest('certificate_header')
            ->usingName('Шапка для справок и выписок')
            ->toMediaCollection('certificate_header');

        return redirect()
            ->route('doctors.settings.certificate-header.index')
            ->with(
                'success',
                'Шапка сохранена и применяется ко всей группе учреждений.'
            );
    }

    public function destroy(Request $request): RedirectResponse
    {
        $this->authorizeSettings($request);

        $this->rootSettingsClinic($request)
            ->clearMediaCollection('certificate_header');

        return redirect()
            ->route('doctors.settings.certificate-header.index')
            ->with(
                'success',
                'Шапка удалена. Документы всей группы будут печататься без неё.'
            );
    }

    public function settingsImage(Request $request): RedirectResponse
    {
        $this->authorizeSettings($request);

        return $this->mediaRedirect(
            $this->rootSettingsClinic($request)
                ->getFirstMedia('certificate_header')
        );
    }

    public function patientImage(
        Request $request,
        Patient $patient,
        HospitalPatientAccess $access
    ): RedirectResponse {
        $user = $request->user();

        abort_unless(
            $user
            && (
                $access->canRead($user, $patient)
                || $user->hasRole('clinic_admin')
            ),
            403
        );

        $rootClinic = ClinicDocumentHeaderScope::rootForPatient($patient);

        abort_unless($rootClinic, 404);

        return $this->mediaRedirect(
            $rootClinic->getFirstMedia('certificate_header')
        );
    }

    private function mediaRedirect(
        ?\Spatie\MediaLibrary\MediaCollections\Models\Media $media
    ): RedirectResponse {
        abort_unless($media, 404);

        return redirect()->to($media->getUrl());
    }

    private function rootSettingsClinic(Request $request): \App\Models\Clinic
    {
        $clinic = ClinicDocumentHeaderScope::currentClinicForUser(
            $request->user()
        );

        abort_unless(
            $clinic,
            422,
            'Для пользователя не определена текущая клиника.'
        );

        return ClinicDocumentHeaderScope::root($clinic);
    }

    private function authorizeSettings(Request $request): void
    {
        $user = $request->user();

        $allowed = method_exists(
            ClinicSettingsAccessMiddleware::class,
            'allows'
        )
            ? ClinicSettingsAccessMiddleware::allows($user)
            : (
                $user !== null
                && ClinicSettingsAccessMiddleware::isDebugTester($user)
            );

        abort_unless($allowed, 403);
    }
}
