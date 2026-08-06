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

    public function getCreatedAtAttribute(string $value)
    {
        return Carbon::parse($value)->format('d.m.Y H:i');
    }

    public function getBirthAtAttribute(string $value)
    {
        return Carbon::parse($value)->format('d.m.Y');
    }

    public function diagnose(): BelongsTo
    {
        return $this->belongsTo(Diagnose::class);
    }


    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
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
            'patient_symptoms',
            'patient_id',
            'symptom_id'
        )->withPivot(['anamnesis_id', 'epicrisis_id', 'created_at', 'updated_at'])
            ->withTimestamps();
    }

    public function symptomsLinks(): HasMany
    {
        return $this->hasMany(PatientSymptom::class, 'patient_id', 'id')
            ->with(['symptom', 'anamnesis', 'epicrisis']);
    }

    public function doctor(): BelongsTo
    {
        return $this->belongsTo(Doctor::class);
    }

    public function labResearches(): HasMany
    {
        return $this->hasMany(LabResearch::class, 'patient_id', 'id');
    }

    public function instrumentalResearches(): HasMany
    {
        return $this->hasMany(
            InstrumentalResearch::class
        );
    }

    public function testSessions(): HasMany
    {
        return $this->hasMany(TestSession::class);
    }

    public function conditions(): HasMany
    {
        return $this->hasMany(PatientCondition::class);
    }

    public function prescriptions(): HasMany
    {
        return $this->hasMany(MedicalPrescription::class);
    }

    public function contacts(): HasMany
    {
        return $this->hasMany(Contact::class);
    }

    public function documents(): HasMany
    {
        return $this->hasMany(MedicineDocument::class);
    }
}
