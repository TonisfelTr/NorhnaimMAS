<?php

namespace App\Helpers\DoctorPanel;

use App\Models\Drug;
use App\Models\Patient;
use Illuminate\Support\Str;

class DrugSelection {
    private function getFirstSortedKey(array $items): ?string
    {
        if (empty($items)) {
            return null;
        }

        $keys = array_map('strval', array_keys($items));

        usort($keys, function ($a, $b) {
            return (float) $a <=> (float) $b;
        });

        return $keys[0] ?? null;
    }

    public function getPatientClinicalConditionIds(?Patient $patient): array
    {
        if (!$patient) {
            return [];
        }

        if (method_exists($patient, 'clinicalConditions')) {
            return $patient->clinicalConditions()
                ->pluck('clinical_conditions.id')
                ->map(fn ($id) => (int) $id)
                ->all();
        }

        if (method_exists($patient, 'conditions')) {
            return $patient->conditions()
                ->pluck('id')
                ->map(fn ($id) => (int) $id)
                ->all();
        }

        return [];
    }

    public function getAgeContraindicationIds($birthAt): array
    {
        if (empty($birthAt)) {
            return [];
        }

        try {
            $age = Carbon::parse($birthAt)->age;
        } catch (\Throwable $e) {
            return [];
        }

        $ids = [];

        // До 18 лет
        if ($age < 18) {
            $ids[] = 54; // Возраст до 18-ти лет
            $ids[] = 67; // Детский возраст до 18-ти лет
        }

        // До 15 лет
        if ($age < 15) {
            $ids[] = 183; // Возраст до 15 лет
        }

        // До 12 лет
        if ($age < 12) {
            $ids[] = 30; // Возраст до 12-ти лет
        }

        // До 3 лет
        if ($age < 3) {
            $ids[] = 57; // Возраст до 3-х лет
        }

        return array_values(array_unique($ids));
    }

    public function getDrugPresetKey(Drug $drug): string
    {
        $base = $drug->latin_name
            ?: $drug->latin
                ?: $drug->name
                    ?: ('drug_' . $drug->id);

        return (string) Str::of($base)
            ->lower()
            ->ascii()
            ->replace([' ', '-'], '_')
            ->trim('_');
    }

    public function resolvePrescriptionPreset(string $drugKey, ?string $diagnosisCode = null): ?array
    {
        $presets = config('prescription_autofill', []);

        $normalizedKey = (string) Str::of($drugKey)
            ->lower()
            ->ascii()
            ->replace([' ', '-'], '_')
            ->trim('_');

        if (!isset($presets[$normalizedKey]) || !is_array($presets[$normalizedKey])) {
            return null;
        }

        $drugPresets = $presets[$normalizedKey];

        if ($diagnosisCode && isset($drugPresets[$diagnosisCode]) && is_array($drugPresets[$diagnosisCode])) {
            return $drugPresets[$diagnosisCode];
        }

        return $drugPresets['default'] ?? null;
    }

    public function extractDrugFormsForAutofill(Drug $drug): array
    {
        $forms = $drug->forms ?? [];

        if (is_string($forms)) {
            $decoded = json_decode($forms, true);
            $forms = is_array($decoded) ? $decoded : [];
        }

        return is_array($forms) ? $forms : [];
    }

    public function buildFallbackPrescriptionPreset(array $forms): array
    {
        $priority = ['tablets', 'capsules', 'dragees', 'syrup', 'drops', 'powder', 'ampules'];

        $selectedForm = null;

        foreach ($priority as $formName) {
            if (!empty($forms[$formName])) {
                $selectedForm = $formName;
                break;
            }
        }

        if (!$selectedForm) {
            $selectedForm = array_key_first($forms);
        }

        if (!$selectedForm) {
            return [
                'drug_form' => '',
                'dosage' => '',
                'quantity' => '',
                'standard' => 1,
                'taking_drug' => 1,
                'taking_count' => 2,
                'taking_time_meal' => 1,
                'validity_period' => 60,
            ];
        }

        $dosages = $forms[$selectedForm];
        $dosageKey = $this->getFirstSortedKey($dosages);

        $quantityValue = '';

        if ($dosageKey !== null) {
            $quantityRaw = $dosages[$dosageKey];

            if (is_array($quantityRaw)) {
                $quantityValue = $this->getFirstSortedKey($quantityRaw);
            } else {
                $quantityValue = $quantityRaw;
            }
        }

        return [
            'drug_form' => (string) $selectedForm,
            'dosage' => (string) $dosageKey,
            'quantity' => (string) $quantityValue,
            'standard' => 1,
            'taking_drug' => 1,
            'taking_count' => 2,
            'taking_time_meal' => 1,
            'validity_period' => 60,
        ];
    }

    public function getFormLabelForInstruction(string $form): string
    {
        return match ($form) {
            'tablets' => 'таблетке',
            'capsules' => 'капсуле',
            'dragees' => 'драже',
            'syrup' => 'дозе сиропа',
            'drops' => 'дозе',
            'ampules' => 'ампуле',
            default => 'единице препарата',
        };
    }
}
