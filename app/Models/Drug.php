<?php

namespace App\Models;

use App\Enums\MedicineTypesEnum;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;

class Drug extends Model
{
    use HasFactory, SoftDeletes;

    protected $casts = [
        'forms' => 'array'
    ];
    protected $guarded = [];
    protected $appends = ['generics'];

    public function groupName(): string {
        return match($this->group) {
            '1' => 'антипсихотик',
            '2' => 'антидепрессант',
            '3' => 'стабилизатор настроения',
            '4' => 'ингибитор АХЭ',
            '5' => 'холиноблокатор',
            '6' => 'дофаминомиметик',
            '7' => 'анксиолитик',
            '8' => 'бета-блокатор',
            '9' => 'гипнотик',
            '10' => 'психостимулятор',
            default => 'Неизвестно'
        };
    }

    public function latinName(): Attribute {
        return $this->latin_name();
    }

    public function latin_name(): Attribute {
        return Attribute::make(
            get: fn ($value) => ucfirst($value)
        );
    }

    public function name(): Attribute {
        return Attribute::make(
            get: fn ($value) => ucfirst($value)
        );
    }

    public function scopeAntipsychotics(Builder $query): void {
        $query->where('group', MedicineTypesEnum::Antipsychotic);
    }

    public function scopeAntidepressants(Builder $query): void {
        $query->where('group', MedicineTypesEnum::Antidepressant);
    }

    public function contraindications(): BelongsToMany {
        return $this->belongsToMany(ContraindicationsType::class, MedicineContraindication::class, 'drug_id', 'contraindication_id');
    }

    public function dangerous(): BelongsToMany {
        return $this->contraindications();
    }

    public function receptors(): BelongsToMany {
        return $this->belongsToMany(Receptor::class, 'drug_receptors', 'drug_id', 'receptor_id');
    }

    public function diagnoses(): BelongsToMany{
        return $this->belongsToMany(Diagnose::class, 'medicine_indications', 'medicine_id', 'diagnose_id');
    }

    public function side_effects(): BelongsToMany {
        return $this->belongsToMany(SideEffect::class, 'medicine_side_effects');
    }

    protected function generics(): Attribute {
        return Attribute::make(
            get: fn () => DB::table('generics')->where('drug_id', $this->id)->pluck('name')->toArray()
        );
    }
}
