<?php

namespace App\Http\Requests;

use App\Models\MedicalCommission;
use App\Models\MedicalCommissionMember;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SaveMedicalCommissionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check();
    }

    protected function prepareForValidation(): void
    {
        $members = collect($this->input('members', []))
            ->filter(fn ($member) => is_array($member) && !empty($member['doctor_id']))
            ->values()
            ->map(function (array $member): array {
                $member['role'] = $this->normalizeMemberRole(
                    $member['role'] ?? null
                );

                $member['present'] = filter_var(
                    $member['present'] ?? false,
                    FILTER_VALIDATE_BOOLEAN
                );

                if (!$member['present']) {
                    $member['vote'] = null;
                }

                return $member;
            })
            ->all();

        $this->merge([
            'members' => $members,
            'medical_record_entered' => $this->boolean('medical_record_entered'),
        ]);
    }

    private function normalizeMemberRole(mixed $role): string
    {
        $role = trim((string) $role);

        return match ($role) {
            'chairman', 'chairperson' => MedicalCommissionMember::ROLE_CHAIR,
            'deputy_chair', 'deputy_chairman', 'deputy-chairman' => MedicalCommissionMember::ROLE_DEPUTY,
            'commission_member' => MedicalCommissionMember::ROLE_MEMBER,
            default => $role,
        };
    }

    public function rules(): array
    {
        $statusesWithDecision = implode(',', [
            MedicalCommission::STATUS_HELD,
            MedicalCommission::STATUS_PROTOCOL_READY,
        ]);

        return [
            'patient_id' => ['required', 'integer', 'exists:patients,id'],
            'meeting_at' => ['required', 'date'],
            'meeting_type' => ['required', Rule::in([
                MedicalCommission::TYPE_PLANNED,
                MedicalCommission::TYPE_UNSCHEDULED,
            ])],
            'status' => ['required', Rule::in(array_keys(MedicalCommission::statusOptions()))],
            'category' => ['required', Rule::in(array_keys(MedicalCommission::categoryOptions()))],

            'unscheduled_basis' => [
                Rule::requiredIf(fn () => $this->input('meeting_type') === MedicalCommission::TYPE_UNSCHEDULED),
                'nullable',
                'string',
                'max:3000',
            ],

            'secretary_name' => ['nullable', 'string', 'max:255'],
            'secretary_position' => ['nullable', 'string', 'max:255'],

            'agenda' => ['required', 'string', 'max:12000'],
            'decision' => ["required_if:status,{$statusesWithDecision}", 'nullable', 'string', 'max:20000'],
            'rationale' => ["required_if:status,{$statusesWithDecision}", 'nullable', 'string', 'max:20000'],
            'notes' => ['nullable', 'string', 'max:12000'],

            'medical_record_entered' => ['nullable', 'boolean'],

            'members' => ['required', 'array', 'min:3'],
            'members.*.doctor_id' => ['required', 'integer', 'distinct', 'exists:doctors,id'],
            'members.*.role' => ['required', Rule::in([
                MedicalCommissionMember::ROLE_CHAIR,
                MedicalCommissionMember::ROLE_DEPUTY,
                MedicalCommissionMember::ROLE_MEMBER,
            ])],
            'members.*.present' => ['nullable', 'boolean'],
            'members.*.vote' => ['nullable', Rule::in([
                MedicalCommissionMember::VOTE_FOR,
                MedicalCommissionMember::VOTE_AGAINST,
                MedicalCommissionMember::VOTE_ABSTAIN,
            ])],
            'members.*.special_opinion' => ['nullable', 'string', 'max:8000'],
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator): void {
            $members = collect($this->input('members', []));

            $chairs = $members->where('role', MedicalCommissionMember::ROLE_CHAIR)->count();
            $deputies = $members->where('role', MedicalCommissionMember::ROLE_DEPUTY)->count();

            if ($chairs !== 1) {
                $validator->errors()->add(
                    'members',
                    'В составе ВК должен быть ровно один председатель.'
                );
            }

            if ($deputies < 1 || $deputies > 2) {
                $validator->errors()->add(
                    'members',
                    'В составе ВК должен быть один или два заместителя председателя.'
                );
            }

            if ($this->input('status') !== MedicalCommission::STATUS_PROTOCOL_READY) {
                return;
            }

            $total = $members->count();
            $present = $members->where('present', true);
            $requiredQuorum = (int) ceil(($total * 2) / 3);

            if ($present->count() < $requiredQuorum) {
                $validator->errors()->add(
                    'members',
                    "Нельзя оформить протокол: нет кворума. Требуется минимум {$requiredQuorum} из {$total} членов ВК."
                );
            }

            $withoutVote = $present->filter(fn ($member) => empty($member['vote']))->count();

            if ($withoutVote > 0) {
                $validator->errors()->add(
                    'members',
                    'Для статуса «Протокол оформлен» у каждого присутствовавшего члена ВК должен быть указан результат голосования.'
                );
            }
        });
    }

    public function attributes(): array
    {
        return [
            'patient_id' => 'пациент',
            'meeting_at' => 'дата и время заседания',
            'category' => 'вопрос ВК',
            'agenda' => 'вопрос, вынесенный на заседание',
            'decision' => 'решение ВК',
            'rationale' => 'обоснование решения',
            'members' => 'состав врачебной комиссии',
        ];
    }
}
