<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Patient;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

/** Only clinics to which the current patient belongs can be selected. */
final class PatientNotificationClinicScope
{
    /**
     * NORHNAIM_NOTIFICATION_CLINICS_WITH_EVENTS_V1
     * Only clinics with at least one notification of this authenticated user.
     * Keep the active patient-clinic membership security boundary.
     * Read and unread notifications both count; NULL clinic_id is available
     * only in the global "Все клиники" list until safely attributed.
     */
    public function options(Patient $patient, User $user): Collection
    {
        $clinicIdsWithNotifications = $user->notifications()
            ->reorder()
            ->whereNotNull('clinic_id')
            ->distinct()
            ->pluck('clinic_id')
            ->map(static fn ($id): int => (int) $id)
            ->filter(static fn (int $id): bool => $id > 0)
            ->values()
            ->all();

        if ($clinicIdsWithNotifications === []) {
            return collect();
        }

        return $patient->clinicMemberships()
            ->with('clinic')
            ->where('is_active', true)
            ->whereIn('clinic_id', $clinicIdsWithNotifications)
            ->orderByDesc('is_primary')
            ->orderBy('clinic_id')
            ->get()
            ->filter(static fn ($item): bool => $item->clinic !== null)
            ->unique('clinic_id')
            ->values();
    }

    /** Null is "Все клиники". Unknown/non-member IDs are never accepted. */
    public function selected(Request $request, Collection $options): ?int
    {
        $raw = $request->input('clinic_id');
        if ($raw === null || $raw === '' || $raw === 'all') {
            return null;
        }
        abort_unless(is_scalar($raw) && preg_match('/^[1-9][0-9]*$/D', (string) $raw) === 1, 404);
        $id = (int) $raw;
        abort_unless($options->contains(static fn ($item): bool => (int) $item->clinic_id === $id), 404);

        return $id;
    }

    /** Global scope keeps legacy notifications without clinic attribution visible. */
    public function query(User $user, Collection $options, ?int $selectedClinicId)
    {
        $query = $user->notifications();
        if ($selectedClinicId !== null) {
            abort_unless($options->contains(
                static fn ($item): bool => (int) $item->clinic_id === $selectedClinicId
            ), 404);
            $query->where('clinic_id', $selectedClinicId);
        }

        return $query;
    }
}
