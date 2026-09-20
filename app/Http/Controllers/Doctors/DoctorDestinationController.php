<?php

namespace App\Http\Controllers\Doctors;

use App\Http\Controllers\Controller;
use App\Models\Diagnose;
use App\Models\Doctor;
use App\Models\DoctorDestination;
use App\Models\Patient;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class DoctorDestinationController extends Controller
{
    public function store(Request $request)
    {
        $doctor =
            $request->user()
                ?->doctor;

        abort_if(
            !$doctor,
            403,
            'У пользователя отсутствует карточка врача.'
        );


        $isExternal =
            $request->input('type') ===
            'external';


        $destination =
            new DoctorDestination();


        $destination->doctor_id =
            $doctor->id;

        $destination->patient_id =
            $request->input(
                'patient_id'
            );

        $destination->diagnose_id =
            $request->input(
                'diagnose_id'
            );

        $destination->status =
            $request->input(
                'status',
                'created'
            );

        $destination->type =
            $isExternal
                ? 'external'
                : 'consultation';

        $destination->purpose =
            $request->input(
                'purpose'
            );

        $destination->comment =
            $request->input(
                'comment'
            );

        $destination->priority =
            $request->input(
                'priority',
                'normal'
            );

        $destination->planned_at =
            $request->filled(
                'planned_at'
            )
                ? $request->input(
                'planned_at'
            )
                : null;


        if ($isExternal) {

            $destination->to_doctor_id =
                null;

            $destination->speciality =
                trim(
                    (string)
                    $request->input(
                        'speciality',
                        ''
                    )
                );

            $destination->organization =
                trim(
                    (string)
                    $request->input(
                        'organization',
                        ''
                    )
                );

            $destination->external_doctor_name =
                $request->filled(
                    'external_doctor_name'
                )
                    ? trim(
                    (string)
                    $request->input(
                        'external_doctor_name'
                    )
                )
                    : null;

        } else {

            $destSpecialist =
                Doctor::with(
                    'clinic'
                )
                    ->findOrFail(
                        $request->input(
                            'to_doctor_id'
                        )
                    );


            $destination->to_doctor_id =
                $destSpecialist->id;

            $destination->speciality =
                $destSpecialist->status;

            $destination->organization =
                $destSpecialist
                    ->clinic
                    ?->name;

            $destination->external_doctor_name =
                null;

        }


        $destination->save();


        return back()->with(
            'success',
            'Направление создано.'
        );
    }


    /**
     * Печатный preview ДО сохранения.
     *
     * Этот метод ничего не пишет в doctor_destinations.
     * Он только собирает данные и возвращает тот же Blade,
     * который используется для повторной печати сохранённого
     * направления.
     */
    public function preview(
        Request $request
    ) {
        return view(
            'doctors.prescriptions.destination',
            $this->buildPrintData(
                [
                    'type' =>
                        $request->input(
                            'type'
                        ),

                    'patient_id' =>
                        $request->input(
                            'patient_id'
                        ),

                    'diagnose_id' =>
                        $request->input(
                            'diagnose_id'
                        ),

                    'to_doctor_id' =>
                        $request->input(
                            'to_doctor_id'
                        ),

                    'speciality' =>
                        $request->input(
                            'speciality'
                        ),

                    'organization' =>
                        $request->input(
                            'organization'
                        ),

                    'external_doctor_name' =>
                        $request->input(
                            'external_doctor_name'
                        ),

                    'purpose' =>
                        $request->input(
                            'purpose'
                        ),

                    'comment' =>
                        $request->input(
                            'comment'
                        ),

                    /*
                     * Пока документ ещё не сохранён,
                     * дата направления = текущая дата.
                     */
                    'created_at' =>
                        now(),
                ]
            )
        );
    }


    /**
     * Повторная печать уже сохранённого направления.
     */
    public function print(
        DoctorDestination $doctor_destination
    ) {
        return view(
            'doctors.prescriptions.destination',
            $this->buildPrintData(
                [
                    'destination' =>
                        $doctor_destination,

                    'type' =>
                        $doctor_destination->type,

                    'patient_id' =>
                        $doctor_destination->patient_id,

                    'diagnose_id' =>
                        $doctor_destination->diagnose_id,

                    'to_doctor_id' =>
                        $doctor_destination->to_doctor_id,

                    'speciality' =>
                        $doctor_destination->speciality,

                    'organization' =>
                        $doctor_destination->organization,

                    'external_doctor_name' =>
                        $doctor_destination
                            ->external_doctor_name,

                    'purpose' =>
                        $doctor_destination->purpose,

                    'comment' =>
                        $doctor_destination->comment,

                    'created_at' =>
                        $doctor_destination->created_at,
                ]
            )
        );
    }


    /**
     * Вся подготовка данных для печати находится
     * в контроллере, а не в Blade.
     */
    private function buildPrintData(
        array $data
    ): array {
        $isExternal =
            ($data['type'] ?? null) ===
            'external';


        $patient =
            Patient::with(
                'diagnose'
            )
                ->findOrFail(
                    $data['patient_id']
                );


        $diagnose =
            !empty(
            $data['diagnose_id']
            )
                ? Diagnose::find(
                $data['diagnose_id']
            )
                : null;


        $diagnose =
            $diagnose
            ?? $patient->diagnose;


        $diagnosisText =
            $diagnose
                ? trim(
                ($diagnose->code ?? '')
                . ' '
                . ($diagnose->title ?? '')
            )
                : '';


        $doctor =
            auth()
                ->user()
                ?->doctor;


        abort_if(
            !$doctor,
            403,
            'У пользователя отсутствует карточка врача.'
        );


        $referrerLabel =
            $doctor
                ->clinic
                ?->name
                ?: $this->shortDoctorName(
                $doctor
            );


        $recipientDoctor =
            !$isExternal
            && !empty(
            $data['to_doctor_id']
            )
                ? Doctor::find(
                $data['to_doctor_id']
            )
                : null;


        $recipientDoctorName =
            $recipientDoctor
                ?->full_name
            ?? '';


        $speciality =
            trim(
                (string) (
                    $data['speciality']
                    ?? $recipientDoctor
                    ?->status
                    ?? ''
                )
            );


        $patientName =
            collect(
                [
                    $patient->surname,
                    $patient->name,
                    $patient->patronym,
                ]
            )
                ->filter()
                ->implode(' ');


        $birthAt =
            $patient->birth_at
                ? Carbon::parse(
                $patient->birth_at
            )
                ->format(
                    'd.m.Y'
                )
                : '';


        $registrationAddress =
            $patient->registration_address
            ?? $patient->registered_address
            ?? $patient->address_registration
            ?? $patient->address
            ?? '';


        $workplace =
            $patient->job_organization
            ?? $patient->work_place
            ?? $patient->workplace
            ?? '';


        $cardNumber =
            $patient->medcard_number
            ?? $patient->medical_card_number
            ?? $patient->card_number
            ?? $patient->number
            ?? '';


        $snils =
            $patient->snils
            ?? '';


        $organization =
            $isExternal
                ? trim(
                (string) (
                    $data['organization']
                    ?? ''
                )
            )
                : '';


        $externalDoctorName =
            $isExternal
                ? trim(
                (string) (
                    $data['external_doctor_name']
                    ?? ''
                )
            )
                : '';


        $purpose =
            trim(
                (string) (
                    $data['purpose']
                    ?? ''
                )
            );


        $comment =
            trim(
                (string) (
                    $data['comment']
                    ?? ''
                )
            );


        $createdAt =
            $data['created_at']
            ?? now();


        $referralDate =
            Carbon::parse(
                $createdAt
            )
                ->format(
                    'd.m.Y'
                );


        return [
            'destination' =>
                $data['destination']
                ?? null,

            'isExternal' =>
                $isExternal,

            'patientName' =>
                $patientName,

            'birthAt' =>
                $birthAt,

            'registrationAddress' =>
                $registrationAddress,

            'workplace' =>
                $workplace,

            'cardNumber' =>
                $cardNumber,

            'snils' =>
                $snils,

            'speciality' =>
                $speciality,

            'recipientDoctorName' =>
                $recipientDoctorName,

            'organization' =>
                $organization,

            'externalDoctorName' =>
                $externalDoctorName,

            'purpose' =>
                $purpose,

            'diagnosisText' =>
                $diagnosisText,

            'referralDate' =>
                $referralDate,

            'comment' =>
                $comment,

            'referrerLabel' =>
                $referrerLabel,
        ];
    }


    private function shortDoctorName(
        Doctor $doctor
    ): string {
        $initials =
            collect(
                [
                    $doctor->name,
                    $doctor->patronym,
                ]
            )
                ->filter()
                ->map(
                    static fn ($value) =>
                        mb_substr(
                            (string) $value,
                            0,
                            1
                        )
                        . '.'
                )
                ->implode(' ');


        return trim(
            ($doctor->surname ?? '')
            . ' '
            . $initials
        );
    }
}
