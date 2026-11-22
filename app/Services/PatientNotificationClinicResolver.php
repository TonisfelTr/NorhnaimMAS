<?php

declare(strict_types=1);

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Throwable;

/**
 * Determine the clinic that owns a particular clinical event.
 * Never assume the assigned doctor is the author of the event.
 * In a multi-clinic patient record ambiguous events stay unassigned.
 */
final class PatientNotificationClinicResolver
{
    public function resolve(int $patientId, string $eventKey, ?int $explicitClinicId = null): ?int
    {
        if ($explicitClinicId !== null && $explicitClinicId > 0) {
            return $explicitClinicId;
        }

        try {
            $id = null;
            $table = null;
            $preferredDoctor = 'doctor_id';

            if (preg_match('/^document:(?:clinic|reviewed):([1-9][0-9]*)$/D', $eventKey, $m)) {
                $table = 'medicine_documents';
                $id = (int) $m[1];
                if (str_starts_with($eventKey, 'document:reviewed:')) {
                    $preferredDoctor = 'reviewed_by_doctor_id';
                }
            } elseif (preg_match('/^medication:(?:new|cancelled|completed|updated):([1-9][0-9]*)(?::.*)?$/D', $eventKey, $m)) {
                $table = 'patient_medication_assignments';
                $id = (int) $m[1];
            } elseif (preg_match('/^research:lab-ready:([1-9][0-9]*)$/D', $eventKey, $m)) {
                $table = 'lab_researches';
                $id = (int) $m[1];
            } elseif (preg_match('/^research:instrumental-ready:([1-9][0-9]*)$/D', $eventKey, $m)) {
                $table = 'instrumental_researches';
                $id = (int) $m[1];
            } elseif (preg_match('/^test:(?:assigned|result):([1-9][0-9]*)$/D', $eventKey, $m)) {
                $table = 'test_assignments';
                $id = (int) $m[1];
            } elseif (preg_match('/^appointment:reminder:day-before:([1-9][0-9]*)$/D', $eventKey, $m)) {
                $table = 'registry_records';
                $id = (int) $m[1];
            }

            if ($table !== null && $id !== null && Schema::hasTable($table)) {
                $hasPatient = Schema::hasColumn($table, 'patient_id');
                if ($hasPatient) {
                    $columns = ['id'];
                    foreach (['clinic_id', 'doctor_id', 'reviewed_by_doctor_id'] as $col) {
                        if (Schema::hasColumn($table, $col)) {
                            $columns[] = $col;
                        }
                    }
                    $record = DB::table($table)->where('id', $id)
                        ->where('patient_id', $patientId)->first($columns);
                    if ($record !== null) {
                        if ((int) ($record->clinic_id ?? 0) > 0) {
                            return (int) $record->clinic_id;
                        }
                        $doctorId = (int) ($record->{$preferredDoctor} ?? $record->doctor_id ?? 0);
                        if ($doctorId > 0 && Schema::hasTable('doctors')) {
                            $clinic = (int) DB::table('doctors')->where('id', $doctorId)->value('clinic_id');
                            if ($clinic > 0) {
                                return $clinic;
                            }
                        }
                    }
                }
            }

            // If patient has exactly one active clinic, attribution is unambiguous.
            if (Schema::hasTable('patient_clinics')) {
                $clinics = DB::table('patient_clinics')
                    ->where('patient_id', $patientId)
                    ->where('is_active', true)
                    ->distinct()
                    ->limit(2)
                    ->pluck('clinic_id');
                if ($clinics->count() === 1) {
                    return (int) $clinics->first();
                }
            }
        } catch (Throwable $e) {
            report($e);
        }

        return null;
    }
}
