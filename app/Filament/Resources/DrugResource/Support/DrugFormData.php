<?php

namespace App\Filament\Resources\DrugResource\Support;

use App\Models\Diagnose;
use App\Models\Drug;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\HtmlString;

/**
 * Converts Filament's editable repeater state to the legacy drugs.forms JSON
 * format and synchronizes the drug's indication/contraindication pivots.
 */
final class DrugFormData
{
    private const SOLID_FORMS = ['tablets', 'dragees', 'capsules'];

    private const FORM_LABELS = [
        'ampules' => 'Ампулы',
        'tablets' => 'Таблетки',
        'dragees' => 'Драже',
        'capsules' => 'Капсулы',
        'solution' => 'Раствор',
        'syrup' => 'Сироп',
        'ointment' => 'Мазь',
        'gel' => 'Гель',
        'spray' => 'Спрей',
        'drops' => 'Капли',
        'powder' => 'Порошок',
    ];

    /**
     * Prepare model data for the Filament edit form.
     */
    public static function prepareForFill(array $data, Drug $record): array
    {
        $forms = self::toArray($data['forms'] ?? $record->forms ?? []);

        foreach (self::SOLID_FORMS as $formKey) {
            $data['forms_' . $formKey] = self::flattenSolidForm($forms[$formKey] ?? []);
        }

        $data['forms_ampules'] = self::flattenAmpules($forms['ampules'] ?? []);
        $data['indications_data'] = self::getIndicationsState((int) $record->getKey());
        $data['contraindications_ids'] = self::getContraindicationIds((int) $record->getKey());

        // `forms` is edited through the explicit repeaters above, not as a raw JSON field.
        unset($data['forms']);

        return $data;
    }

    /**
     * Prepare Filament state for persistence in the drugs table.
     * Unrepresented legacy form keys are preserved to avoid data loss.
     */
    public static function prepareForSave(array $data, ?Drug $record = null): array
    {
        $forms = self::toArray($record?->forms ?? []);

        foreach (self::SOLID_FORMS as $formKey) {
            $forms[$formKey] = self::buildSolidForm($data['forms_' . $formKey] ?? []);
            unset($data['forms_' . $formKey]);
        }

        $forms['ampules'] = self::buildAmpules($data['forms_ampules'] ?? []);
        unset($data['forms_ampules']);

        $data['forms'] = $forms;

        // These are saved to pivot tables in afterCreate()/afterSave().
        unset($data['indications_data'], $data['contraindications_ids']);

        return $data;
    }

    /**
     * Capture the virtual relationship fields before they are removed from model data.
     */
    public static function extractRelations(array $data): array
    {
        return [
            'indications_data' => is_array($data['indications_data'] ?? null)
                ? $data['indications_data']
                : [],
            'contraindications_ids' => is_array($data['contraindications_ids'] ?? null)
                ? $data['contraindications_ids']
                : [],
        ];
    }

    /**
     * Synchronize indications and contraindications for a saved drug.
     * type=1 in medicine_contraindications means a contraindication.
     */
    public static function syncRelations(Drug $drug, array $state): void
    {
        DB::transaction(function () use ($drug, $state): void {
            $drugId = (int) $drug->getKey();

            if (Schema::hasTable('medicine_indications')) {
                DB::table('medicine_indications')
                    ->where('medicine_id', $drugId)
                    ->delete();

                $indications = [];
                foreach (($state['indications_data'] ?? []) as $item) {
                    if (!is_array($item)) {
                        continue;
                    }

                    $diagnoseId = filter_var($item['diagnose_id'] ?? null, FILTER_VALIDATE_INT);
                    $indicatedInRussia = (bool) ($item['indicated_in_russia'] ?? false);
                    $indicatedByFda = (bool) ($item['indicated_by_fda'] ?? false);

                    if (!$diagnoseId || (!$indicatedInRussia && !$indicatedByFda)) {
                        continue;
                    }

                    // One diagnosis must have only one pivot row for a drug.
                    $indications[$diagnoseId] = [
                        'medicine_id' => $drugId,
                        'diagnose_id' => (int) $diagnoseId,
                        'indicated_in_russia' => $indicatedInRussia,
                        'indicated_by_fda' => $indicatedByFda,
                    ];
                }

                foreach ($indications as $row) {
                    DB::table('medicine_indications')->insert(
                        self::withTimestamps('medicine_indications', $row)
                    );
                }
            }

            if (Schema::hasTable('medicine_contraindications')) {
                DB::table('medicine_contraindications')
                    ->where('drug_id', $drugId)
                    ->where('type', 1)
                    ->delete();

                $ids = array_values(array_unique(array_filter(array_map(
                    static fn ($id) => filter_var($id, FILTER_VALIDATE_INT),
                    $state['contraindications_ids'] ?? []
                ))));

                if ($ids !== [] && Schema::hasTable('contraindications_types')) {
                    $validIds = DB::table('contraindications_types')
                        ->whereIn('id', $ids)
                        ->pluck('id')
                        ->map(static fn ($id) => (int) $id)
                        ->all();

                    foreach ($validIds as $contraindicationId) {
                        $row = [
                            'drug_id' => $drugId,
                            'contraindication_id' => $contraindicationId,
                            'type' => 1,
                        ];

                        DB::table('medicine_contraindications')->insert(
                            self::withTimestamps('medicine_contraindications', $row)
                        );
                    }
                }
            }
        });
    }

    public static function diagnosisSearchResults(string $search): array
    {
        $needle = mb_strtolower(trim($search));

        if ($needle === '') {
            return [];
        }

        return Diagnose::query()
            ->where(function (Builder $query) use ($needle): void {
                $query
                    ->whereRaw('LOWER(code) LIKE ?', ['%' . $needle . '%'])
                    ->orWhereRaw('LOWER(title) LIKE ?', ['%' . $needle . '%']);
            })
            ->orderBy('code')
            ->limit(50)
            ->get(['id', 'code', 'title'])
            ->mapWithKeys(static fn (Diagnose $diagnose) => [
                $diagnose->id => self::diagnosisLabelFromModel($diagnose),
            ])
            ->all();
    }

    public static function diagnosisOptionLabel($id): ?string
    {
        if (!$id) {
            return null;
        }

        $diagnose = Diagnose::query()->find($id, ['id', 'code', 'title']);

        return $diagnose ? self::diagnosisLabelFromModel($diagnose) : null;
    }

    public static function contraindicationOptions(): array
    {
        if (!Schema::hasTable('contraindications_types')) {
            return [];
        }

        return DB::table('contraindications_types')
            ->orderBy('name')
            ->pluck('name', 'id')
            ->all();
    }

    public static function formsHtml(Drug $drug): HtmlString
    {
        $forms = self::toArray($drug->forms ?? []);
        $orderedKeys = ['ampules', 'tablets', 'dragees', 'capsules'];
        $html = '<div class="space-y-2">';
        $hasRows = false;

        foreach ($orderedKeys as $formKey) {
            $rows = $formKey === 'ampules'
                ? self::flattenAmpules($forms[$formKey] ?? [])
                : self::flattenSolidForm($forms[$formKey] ?? []);

            if ($rows === []) {
                continue;
            }

            $hasRows = true;
            $html .= '<div class="space-y-1">';
            $html .= '<div class="text-xs font-bold uppercase tracking-wide text-gray-600 dark:text-gray-300">'
                . e(self::FORM_LABELS[$formKey] ?? $formKey)
                . '</div>';

            $grouped = [];
            foreach ($rows as $row) {
                $dose = self::formatNumber($row['dose'] ?? '');
                $volume = self::formatNumber($row['volume'] ?? '');
                $groupKey = $dose . '|' . $volume;
                $grouped[$groupKey]['dose'] = $dose;
                $grouped[$groupKey]['volume'] = $volume;
                $grouped[$groupKey]['quantities'][] = self::formatNumber($row['quantity'] ?? '');
            }

            foreach ($grouped as $group) {
                $label = $group['dose'] . ' мг';
                if ($formKey === 'ampules' && $group['volume'] !== '') {
                    $label .= ' / ' . $group['volume'] . ' мл';
                }

                $quantities = array_values(array_unique(array_filter($group['quantities'], 'strlen')));
                $html .= '<div class="text-sm font-semibold text-gray-800 dark:text-gray-100">'
                    . e($label . ' — ' . implode(', ', $quantities) . ' шт.')
                    . '</div>';
            }

            $html .= '</div>';
        }

        // Preserve visibility of legacy forms not represented by the current four tabs.
        foreach ($forms as $formKey => $formValues) {
            if (in_array($formKey, $orderedKeys, true) || !is_array($formValues) || $formValues === []) {
                continue;
            }

            $hasRows = true;
            $html .= '<div class="space-y-1">';
            $html .= '<div class="text-xs font-bold uppercase tracking-wide text-gray-600 dark:text-gray-300">'
                . e(self::FORM_LABELS[$formKey] ?? ucfirst((string) $formKey))
                . '</div>';

            foreach (self::flattenSolidForm($formValues) as $row) {
                $html .= '<div class="text-sm font-semibold text-gray-800 dark:text-gray-100">'
                    . e(self::formatNumber($row['dose'] ?? '') . ' — ' . self::formatNumber($row['quantity'] ?? '') . ' шт.')
                    . '</div>';
            }
            $html .= '</div>';
        }

        if (!$hasRows) {
            return new HtmlString('<span class="text-sm text-gray-400">Формы выпуска не указаны</span>');
        }

        return new HtmlString($html . '</div>');
    }

    public static function indicationsHtml(Drug $drug): HtmlString
    {
        if (!Schema::hasTable('medicine_indications') || !Schema::hasTable('diagnoses')) {
            return new HtmlString('<span class="text-sm text-gray-400">Показания не указаны</span>');
        }

        $rows = DB::table('medicine_indications as mi')
            ->join('diagnoses as d', 'd.id', '=', 'mi.diagnose_id')
            ->where('mi.medicine_id', $drug->getKey())
            ->orderBy('d.code')
            ->get([
                'd.code',
                'd.title',
                'mi.indicated_in_russia',
                'mi.indicated_by_fda',
            ]);

        if ($rows->isEmpty()) {
            return new HtmlString('<span class="text-sm text-gray-400">Показания не указаны</span>');
        }

        $html = '<div class="space-y-1.5">';

        foreach ($rows as $row) {
            $html .= '<div class="rounded-lg border border-green-200 bg-green-50 p-2 dark:border-green-900 dark:bg-green-950/30">';
            $html .= '<div class="text-sm font-bold text-green-800 dark:text-green-300">'
                . e(trim(($row->code ?? '') . ' — ' . ($row->title ?? ''), ' —'))
                . '</div>';
            $html .= '<div class="mt-1 flex flex-wrap gap-1">';

            if ((bool) $row->indicated_in_russia) {
                $html .= '<span class="inline-flex rounded-full border border-blue-200 bg-blue-50 px-2 py-0.5 text-xs font-semibold text-blue-700 dark:border-blue-900 dark:bg-blue-950 dark:text-blue-300">по рекомендациям Минздрава РФ</span>';
            }

            if ((bool) $row->indicated_by_fda) {
                $html .= '<span class="inline-flex rounded-full border border-purple-200 bg-purple-50 px-2 py-0.5 text-xs font-semibold text-purple-700 dark:border-purple-900 dark:bg-purple-950 dark:text-purple-300">по рекомендациям FDA</span>';
            }

            $html .= '</div></div>';
        }

        return new HtmlString($html . '</div>');
    }

    public static function contraindicationsHtml(Drug $drug): HtmlString
    {
        if (!Schema::hasTable('medicine_contraindications') || !Schema::hasTable('contraindications_types')) {
            return new HtmlString('<span class="text-sm text-gray-400">Противопоказания не указаны</span>');
        }

        $names = DB::table('medicine_contraindications as mc')
            ->join('contraindications_types as ct', 'ct.id', '=', 'mc.contraindication_id')
            ->where('mc.drug_id', $drug->getKey())
            ->where('mc.type', 1)
            ->orderBy('ct.name')
            ->pluck('ct.name');

        if ($names->isEmpty()) {
            return new HtmlString('<span class="text-sm text-gray-400">Противопоказания не указаны</span>');
        }

        $html = '<div class="flex flex-wrap gap-1.5">';
        foreach ($names as $name) {
            $html .= '<span class="inline-flex rounded-full bg-red-50 px-2.5 py-1 text-xs font-semibold text-red-700 dark:bg-red-950/50 dark:text-red-300">'
                . e($name)
                . '</span>';
        }

        return new HtmlString($html . '</div>');
    }

    private static function getIndicationsState(int $drugId): array
    {
        if (!Schema::hasTable('medicine_indications')) {
            return [];
        }

        return DB::table('medicine_indications')
            ->where('medicine_id', $drugId)
            ->orderBy('diagnose_id')
            ->get(['diagnose_id', 'indicated_in_russia', 'indicated_by_fda'])
            ->map(static fn ($row) => [
                'diagnose_id' => (string) $row->diagnose_id,
                'indicated_in_russia' => (bool) $row->indicated_in_russia,
                'indicated_by_fda' => (bool) $row->indicated_by_fda,
            ])
            ->all();
    }

    private static function getContraindicationIds(int $drugId): array
    {
        if (!Schema::hasTable('medicine_contraindications')) {
            return [];
        }

        return DB::table('medicine_contraindications')
            ->where('drug_id', $drugId)
            ->where('type', 1)
            ->pluck('contraindication_id')
            ->map(static fn ($id) => (string) $id)
            ->all();
    }

    private static function diagnosisLabelFromModel(Diagnose $diagnose): string
    {
        return trim(($diagnose->code ?? '') . ' — ' . ($diagnose->title ?? ''), ' —');
    }

    private static function flattenSolidForm($source): array
    {
        $source = self::toArray($source);
        $rows = [];

        // Compatibility with the previous Repeater format: [{dose, quantity}, ...].
        if (array_is_list($source)) {
            foreach ($source as $item) {
                if (!is_array($item) || !isset($item['dose'])) {
                    continue;
                }

                foreach (self::quantityValues($item['quantity'] ?? $item['quantities'] ?? []) as $quantity) {
                    $rows[] = [
                        'dose' => self::formatNumber($item['dose']),
                        'quantity' => self::formatNumber($quantity),
                    ];
                }
            }

            return $rows;
        }

        // Canonical JSON format: dose => quantity | [quantity, ...].
        foreach ($source as $dose => $quantities) {
            if (is_array($quantities) && isset($quantities['quantity'])) {
                $quantities = $quantities['quantity'];
            }

            foreach (self::quantityValues($quantities) as $quantity) {
                $rows[] = [
                    'dose' => self::formatNumber($dose),
                    'quantity' => self::formatNumber($quantity),
                ];
            }
        }

        return $rows;
    }

    private static function flattenAmpules($source): array
    {
        $source = self::toArray($source);
        $rows = [];

        // Compatibility with the previous Repeater format: [{dose, volume, quantity}, ...].
        if (array_is_list($source)) {
            foreach ($source as $item) {
                if (!is_array($item) || !isset($item['dose'])) {
                    continue;
                }

                foreach (self::quantityValues($item['quantity'] ?? $item['quantities'] ?? []) as $quantity) {
                    $rows[] = [
                        'dose' => self::formatNumber($item['dose']),
                        'volume' => self::formatNumber($item['volume'] ?? ''),
                        'quantity' => self::formatNumber($quantity),
                    ];
                }
            }

            return $rows;
        }

        // Canonical JSON format: dose => volume => quantity | [quantity, ...].
        foreach ($source as $dose => $volumes) {
            if (!is_array($volumes)) {
                continue;
            }

            foreach ($volumes as $volume => $quantities) {
                if (is_array($quantities) && isset($quantities['quantity'])) {
                    $quantities = $quantities['quantity'];
                }

                foreach (self::quantityValues($quantities) as $quantity) {
                    $rows[] = [
                        'dose' => self::formatNumber($dose),
                        'volume' => self::formatNumber($volume),
                        'quantity' => self::formatNumber($quantity),
                    ];
                }
            }
        }

        return $rows;
    }

    private static function buildSolidForm($rows): array
    {
        $grouped = [];

        foreach (self::toArray($rows) as $row) {
            if (!is_array($row)) {
                continue;
            }

            $dose = self::normalizeNumericKey($row['dose'] ?? null);
            $quantity = self::normalizeNumericValue($row['quantity'] ?? null);

            if ($dose === null || $quantity === null || $quantity < 1 || floor((float) $quantity) != (float) $quantity) {
                continue;
            }

            $grouped[$dose][] = (int) $quantity;
        }

        $result = [];
        foreach ($grouped as $dose => $quantities) {
            $quantities = array_values(array_unique($quantities));
            $result[$dose] = count($quantities) === 1 ? $quantities[0] : $quantities;
        }

        return $result;
    }

    private static function buildAmpules($rows): array
    {
        $grouped = [];

        foreach (self::toArray($rows) as $row) {
            if (!is_array($row)) {
                continue;
            }

            $dose = self::normalizeNumericKey($row['dose'] ?? null);
            $volume = self::normalizeNumericKey($row['volume'] ?? null);
            $quantity = self::normalizeNumericValue($row['quantity'] ?? null);

            if ($dose === null || $volume === null || $quantity === null || $quantity < 1 || floor((float) $quantity) != (float) $quantity) {
                continue;
            }

            $grouped[$dose][$volume][] = (int) $quantity;
        }

        $result = [];
        foreach ($grouped as $dose => $volumes) {
            foreach ($volumes as $volume => $quantities) {
                $quantities = array_values(array_unique($quantities));
                $result[$dose][$volume] = count($quantities) === 1 ? $quantities[0] : $quantities;
            }
        }

        return $result;
    }

    private static function quantityValues($value): array
    {
        if (is_array($value)) {
            if (array_is_list($value)) {
                return $value;
            }

            if (isset($value['quantity'])) {
                return self::quantityValues($value['quantity']);
            }

            return array_values($value);
        }

        return $value === null || $value === '' ? [] : [$value];
    }

    private static function normalizeNumericKey($value): ?string
    {
        if ($value === null || $value === '' || !is_numeric(str_replace(',', '.', (string) $value))) {
            return null;
        }

        $number = (float) str_replace(',', '.', (string) $value);

        return self::formatNumber($number);
    }

    private static function normalizeNumericValue($value): int|float|null
    {
        if ($value === null || $value === '' || !is_numeric(str_replace(',', '.', (string) $value))) {
            return null;
        }

        $number = (float) str_replace(',', '.', (string) $value);

        return floor($number) == $number ? (int) $number : $number;
    }

    private static function formatNumber($value): string
    {
        if ($value === null || $value === '') {
            return '';
        }

        if (!is_numeric(str_replace(',', '.', (string) $value))) {
            return (string) $value;
        }

        $number = (float) str_replace(',', '.', (string) $value);

        return rtrim(rtrim(number_format($number, 4, '.', ''), '0'), '.');
    }

    private static function toArray($value): array
    {
        if (is_array($value)) {
            return $value;
        }

        if (is_string($value) && $value !== '') {
            $decoded = json_decode($value, true);
            return is_array($decoded) ? $decoded : [];
        }

        return [];
    }

    private static function withTimestamps(string $table, array $row): array
    {
        $now = now();

        if (Schema::hasColumn($table, 'created_at')) {
            $row['created_at'] = $now;
        }

        if (Schema::hasColumn($table, 'updated_at')) {
            $row['updated_at'] = $now;
        }

        return $row;
    }
}
