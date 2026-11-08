<?php

namespace App\Http\Controllers\Doctors;

use App\Http\Controllers\Controller;
use App\Models\Patient;
use App\Support\HospitalPatientAccess;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\ValidationException;

class PatientDisabilityVerificationController extends Controller
{
    public function verify(
        Request $request,
        Patient $patient,
        HospitalPatientAccess $access
    ): RedirectResponse {
        $user = $request->user();

        abort_unless(
            $user
            && $access->canRead($user, $patient)
            && $access->canEdit($user, $patient),
            403
        );

        abort_unless(
            Schema::hasTable('patient_disability_records'),
            503,
            'Таблица сведений об инвалидности ещё не создана.'
        );

        DB::transaction(function () use ($patient, $user): void {
            $record = DB::table('patient_disability_records')
                ->where('patient_id', $patient->getKey())
                ->lockForUpdate()
                ->first();

            if (!$record) {
                $this->fail('Сведения об инвалидности ещё не сохранены.');
            }

            if ((string) $record->status !== 'present') {
                $this->fail(
                    'Подтвердить документ можно только при установленной инвалидности.'
                );
            }

            $indefinite = in_array(
                $record->indefinite ?? null,
                [true, 1, '1', 't', 'true'],
                true
            );

            $validUntil = $record->valid_until ?? null;

            if (
                !$indefinite
                && (!$validUntil || (string) $validUntil < today()->toDateString())
            ) {
                $this->fail('Срок инвалидности не указан либо уже истёк.');
            }

            if (!filled($record->disability_group ?? null)) {
                $this->fail('Не указана группа инвалидности.');
            }

            if (!filled($record->established_at ?? null)) {
                $this->fail('Не указана дата установления инвалидности.');
            }

            $sourceType = (string) ($record->source_type ?? '');
            $linkedDocument = null;

            if (
                !empty($record->evidence_document_id)
                && Schema::hasTable('medicine_documents')
            ) {
                $linkedDocument = $patient->documents()
                    ->whereKey($record->evidence_document_id)
                    ->first(['id', 'name']);
            }

            $hasPaperDetails =
                filled($record->document_number ?? null)
                && filled($record->issued_by ?? null);

            if ($sourceType === 'electronic_mse') {
                if (!$linkedDocument) {
                    $this->fail(
                        'Для электронной справки МСЭ с подписью сначала '
                        . 'привяжите загруженный документ-основание.'
                    );
                }
            } elseif ($sourceType === 'mse_certificate') {
                if (!$linkedDocument && !$hasPaperDetails) {
                    $this->fail(
                        'Для бумажной справки МСЭ привяжите документ '
                        . 'либо заполните номер и кем он выдан.'
                    );
                }
            } else {
                $this->fail(
                    'Этот источник сведений нельзя подтвердить как документ МСЭ. '
                    . 'Выберите бумажную справку МСЭ или электронную справку МСЭ с подписью.'
                );
            }

            $updates = [
                'verified_at' => now(),
                'verified_by_user_id' => $user->getKey(),
            ];

            if (Schema::hasColumn('patient_disability_records', 'updated_at')) {
                $updates['updated_at'] = now();
            }

            DB::table('patient_disability_records')
                ->where('patient_id', $patient->getKey())
                ->update($updates);
        });

        return redirect()
            ->route(
                'doctors.patients.medical_card',
                ['patient' => $patient->getKey()]
            )
            ->with(
                'disability_verification_success',
                'Документ МСЭ проверен сотрудником. '
                . 'Инвалидность подтверждена внутри клиники.'
            );
    }

    private function fail(string $message): never
    {
        throw ValidationException::withMessages([
            'disability_verification' => $message,
        ]);
    }
}
