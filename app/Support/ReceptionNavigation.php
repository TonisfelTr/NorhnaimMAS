<?php

namespace App\Support;

use App\Models\Clinic;
use App\Models\ClinicScheduleException;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Schema;

final class ReceptionNavigation
{
    public bool $isInpatient;
    public string $title;
    public string $ariaLabel;
    public string $brandIcon;
    public bool $quarantineActive = false;
    public ?string $quarantineUntil = null;
    public string $searchField;
    public string $searchPlaceholder;
    public string $searchValue;
    public bool $isIndexRoute;
    public bool $isCreateRoute;
    public bool $isArchivesRoute;
    public bool $isDischargesRoute;

    public function __construct(Request $request)
    {
        $user = $request->user();
        $doctor = $user && method_exists($user, 'doctor')
            ? $user->doctor()->first()
            : null;
        $nurse = $user && method_exists($user, 'nurse')
            ? $user->nurse()->first()
            : null;
        $clinic = $doctor?->clinic ?? $nurse?->clinic;
        $clinicId = $doctor?->clinic_id ?? $nurse?->clinic_id;

        $this->isInpatient = $clinic?->clinic_type === Clinic::TYPE_INPATIENT;
        $this->title = $this->isInpatient ? 'Госпитализация' : 'Регистратура';
        $this->ariaLabel = $this->isInpatient
            ? 'Навигация госпитализации'
            : 'Навигация регистратуры';
        $this->brandIcon = $this->isInpatient ? 'bi-hospital' : 'bi-person-lines-fill';

        // Карантин по расписанию блокирует амбулаторную запись, не стационарное размещение.
        if (!$this->isInpatient && $clinicId && Schema::hasTable('clinic_schedule_exceptions')) {
            $today = Carbon::today()->toDateString();
            $quarantine = ClinicScheduleException::query()
                ->where('clinic_id', (int) $clinicId)
                ->where('type', 'quarantine')
                ->whereDate('date_from', '<=', $today)
                ->whereDate('date_to', '>=', $today)
                ->orderByDesc('date_from')
                ->orderByDesc('id')
                ->first();

            $this->quarantineActive = $quarantine !== null;
            $this->quarantineUntil = $quarantine
                ? Carbon::parse($quarantine->date_to)->format('d.m.Y')
                : null;
        }

        $this->searchField = $this->isInpatient ? 'q' : 'fullname';
        $this->searchPlaceholder = $this->isInpatient
            ? 'Поиск госпитализированного…'
            : 'Поиск в регистратуре…';

        $searchValue = $request->input('fullname');
        if (!is_string($searchValue) || trim($searchValue) === '') {
            $searchValue = $request->input('q', '');
        }
        $this->searchValue = is_string($searchValue) ? $searchValue : '';

        $this->isIndexRoute = $request->routeIs('doctors.reception.index');
        $this->isCreateRoute = $request->routeIs('doctors.reception.create');
        $this->isArchivesRoute = $request->routeIs('doctors.reception.archives');
        $this->isDischargesRoute = $request->routeIs('doctors.inpatient.discharges.*');
    }
}
