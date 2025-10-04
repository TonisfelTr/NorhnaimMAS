<?php

namespace App\Models;

use App\Traits\FullnameConverter;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Carbon;

class Patient extends Model
{
    use HasFactory, FullnameConverter;

    protected $guarded = [];
    protected $casts = [
        "birth_at" => "date:d.m.Y",
        "created_at" => "datetime:d.m.Y H:i",
    ];
    protected $with = ['doctor'];

    public function getBirthAtAttribute(string $value) {
        return Carbon::parse($value)->format('d.m.Y');
    }

    public function getCreatedAtAttribute(string $value) {
        return Carbon::parse($value)->format('d.m.Y H:i');
    }

    public function diagnose(): BelongsTo {
        return $this->belongsTo(Diagnose::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function anamneses(): HasMany
    {
        return $this->hasMany(Anamnesis::class)->latest(); // удобный дефолтный order
    }

    public function epicrises(): HasMany
    {
        return $this->hasMany(Epicrisis::class)->latest();
    }

    public function patientSymptoms(): HasMany
    {
        return $this->hasMany(PatientSymptom::class)->latest();
    }

    public function symptoms(): BelongsToMany
    {
        return $this->belongsToMany(
            Symptom::class,
            'patient_symptoms',   // pivot-таблица
            'patient_id',         // FK на пациента в pivot
            'symptom_id'          // FK на симптом в pivot
        )->withPivot(['anamnesis_id', 'epicrisis_id', 'created_at', 'updated_at'])
            ->withTimestamps();
    }

    public function symptomsLinks(): HasMany
    {
        return $this->hasMany(PatientSymptom::class, 'patient_id', 'id')
            ->with(['symptom', 'anamnesis', 'epicrisis']); // Eager load для удобства
    }

    public function doctor(): BelongsTo
    {
        return $this->belongsTo(Doctor::class);
    }

    public function labResearches(): HasMany
    {
        return $this->hasMany(LabResearch::class, 'patient_id', 'id');
    }
}
