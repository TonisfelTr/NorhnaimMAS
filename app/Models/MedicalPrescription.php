<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;

class MedicalPrescription extends Model
{

    protected static function booted(): void
    {
        static::saving(
            function (self $prescription): void {
                if (
                    (int) ($prescription->clinic_id ?? 0) > 0
                    || (int) ($prescription->doctor_id ?? 0) <= 0
                ) {
                    return;
                }

                $clinicId = Doctor::query()
                    ->whereKey(
                        (int) $prescription->doctor_id
                    )
                    ->value('clinic_id');

                if ((int) $clinicId > 0) {
                    $prescription->clinic_id =
                        (int) $clinicId;
                }
            }
        );
    }

    use SoftDeletes;

    protected $fillable = [
        'doctor_id',
        'clinic_id',
        'doctor_name',
        'patient_id',
        'patient_name',
        'generic_name',
        'drug_form',
        'dosage',
        'quantity',
        'standards',
        'usage_instructions',
        'prescription_form',
        'issued_at',
        'validity_period',
        'series',
        'number',
        'birth_at',
        'is_strict',
    ];

    protected function casts(): array
    {
        return [
            'issued_at' => 'date',
            'birth_at' => 'date',
            'is_strict' => 'boolean',
        ];
    }

    protected function getUpdatedAtAttribute($value): string
    {
        return Carbon::parse($this->updated_at)->format('d.m.Y');
    }

    protected function getCreatedAtAttribute(): string
    {
        return Carbon::parse($this->attributes['created_at'])->format('d.m.Y H:i:s');
    }

    protected function getIssuedAtAttribute($value)
    {
        return Carbon::parse($value)->format('d.m.Y');
    }

    protected function getRussianGenericNameAttribute(): string {
        $drug = Drug::select('name')->where('latin_name', $this->generic_name)->first();

        return $drug->name;
    }

    public function drug(): BelongsTo
    {
        return $this->belongsTo(Drug::class, 'generic_name', 'latin_name');
    }

    public function patient(): BelongsTo
    {
        return $this->belongsTo(Patient::class, 'patient_id', 'id');
    }

    public function doctor(): BelongsTo
    {
        return $this->belongsTo(Doctor::class, 'doctor_id', 'id');
    }
}
