<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class HospitalDepartment extends Model
{
    public const SEX_TYPE_MALE = 'male';
    public const SEX_TYPE_FEMALE = 'female';
    public const SEX_TYPE_MIXED = 'mixed';

    protected $table = 'hospital_departments';

    protected $guarded = [];

    protected $casts = [
        'use_clinic_visiting_hours' => 'boolean',
        'visiting_hours' => 'array',
        'enabled' => 'boolean',
        'sort_order' => 'integer',
        'age_min_years' => 'integer',
        'age_max_years' => 'integer',
    ];

    public function clinic(): BelongsTo
    {
        return $this->belongsTo(Clinic::class);
    }

    public function building(): BelongsTo
    {
        return $this->belongsTo(HospitalBuilding::class, 'building_id');
    }

    public function headDoctor(): BelongsTo
    {
        return $this->belongsTo(Doctor::class, 'head_doctor_id');
    }

    public function headNurse(): BelongsTo
    {
        return $this->belongsTo(Nurse::class, 'head_nurse_id');
    }

    /**
     * Постоянные врачи отделения, без заведующего.
     * Заведующий хранится отдельно в head_doctor_id.
     */
    public function doctors(): BelongsToMany
    {
        return $this->belongsToMany(
            Doctor::class,
            'hospital_department_doctors',
            'hospital_department_id',
            'doctor_id'
        )->withTimestamps();
    }
}
