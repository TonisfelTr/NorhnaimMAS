<?php

namespace App\Http\Controllers\Doctors;

use App\Http\Controllers\Controller;
use App\Models\Patient;
use App\Services\TelemedicineJournalService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class TelemedicineJournalController extends Controller
{
    public function store(Request $request, Patient $patient, TelemedicineJournalService $journal): RedirectResponse
    {
        $journal->authorize($request->user(), $patient);
        try {
            $data = Validator::make($request->all(), [
                'telemed_doctor_id' => ['required', 'integer', 'min:1'],
                'telemed_starts_at' => ['required', 'date_format:Y-m-d\TH:i'],
// clinic-telemedicine-timezones:timezone-journal-duration-rule:v1:begin
'telemed_request_id' => ['required', 'uuid'],
                'telemed_duration_minutes' => ['bail', 'required', 'integer', 'between:5,480', 'multiple_of:5'],
// clinic-telemedicine-timezones:timezone-journal-duration-rule:v1:end
            ], [
                'telemed_doctor_id.required' => 'Выберите врача.',
                'telemed_starts_at.required' => 'Укажите дату и время.',
                'telemed_starts_at.date_format' => 'Некорректные дата и время.',
// clinic-telemedicine-timezones:timezone-journal-duration-messages:v1:begin
'telemed_request_id.*' => 'Обновите страницу и повторите создание сеанса.',
                'telemed_duration_minutes.required' => 'Укажите длительность приёма.',
                'telemed_duration_minutes.integer' => 'Укажите длительность целым числом минут.',
                'telemed_duration_minutes.between' => 'Длительность приёма должна составлять от 5 до 480 минут.',
                'telemed_duration_minutes.multiple_of' => 'Укажите длительность с шагом 5 минут.',
// clinic-telemedicine-timezones:timezone-journal-duration-messages:v1:end
            ])->validate();
            $record = $journal->create($request->user(), $patient, $data);
        } catch (ValidationException $e) {
            return redirect($this->journalUrl($patient))
                ->withErrors($e->errors(), 'telemedCreate')
                ->withInput(/* clinic-telemedicine-timezones:timezone-journal-duration-old:v1:begin */ $request->only('telemed_doctor_id', 'telemed_starts_at', 'telemed_request_id', 'telemed_duration_minutes') /* clinic-telemedicine-timezones:timezone-journal-duration-old:v1:end */)
                ->with('active_tab', 'telemedicine-log');
        }
        return redirect($this->journalUrl($patient, [
            'telemed_open' => $record->getKey(),
            'telemed_archive' => $record->deleted_at !== null ? 1 : 0,
        ]))->with('telemed_journal_success', $record->deleted_at !== null
            ? 'Этот запрос уже выполнен; созданный ранее сеанс был удалён.'
            : 'Сеанс телемедицины № ' . $record->getKey() . ' запланирован.')
            ->with('active_tab', 'telemedicine-log');
    }

    public function destroy(
        Request $request,
        Patient $patient,
        int $registryRecord,
        TelemedicineJournalService $journal
    ): RedirectResponse {
        $journal->authorize($request->user(), $patient);
        try {
            $data = Validator::make([
                'telemed_delete_reason' => trim((string) $request->input('telemed_delete_reason', '')),
                'telemed_delete_confirmed' => $request->input('telemed_delete_confirmed'),
            ], [
                'telemed_delete_reason' => ['required', 'string', 'min:5', 'max:1000'],
                'telemed_delete_confirmed' => ['accepted'],
            ], [
                'telemed_delete_reason.required' => 'Укажите причину удаления.',
                'telemed_delete_reason.min' => 'Причина удаления должна содержать не менее 5 символов.',
                'telemed_delete_reason.max' => 'Причина не должна превышать 1000 символов.',
                'telemed_delete_confirmed.accepted' => 'Подтвердите удаление сеанса.',
            ])->validate();
            $journal->cancel($request->user(), $patient, $registryRecord, $data['telemed_delete_reason']);
        } catch (ValidationException $e) {
            return redirect($this->journalUrl($patient, ['telemed_open' => $registryRecord]))
                ->withErrors($e->errors(), 'telemedDelete')->with('active_tab', 'telemedicine-log');
        }
        return redirect($this->journalUrl($patient))
            ->with('telemed_journal_success', 'Сеанс удалён из действующего расписания. История сохранена в архиве.')
            ->with('active_tab', 'telemedicine-log');
    }

    private function journalUrl(Patient $patient, array $query = []): string
    {
        return route('doctors.patients.medical_card', $patient->getKey()) . '?'
            . http_build_query(array_merge(['tab' => 'telemedicine-log'], $query));
    }
}
