<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Test extends Model
{
    public const TYPE_QUESTIONNAIRE = 'questionnaire';
    public const TYPE_IMAGE = 'image';
    public const TYPE_CARD_SORT = 'card_sort';

    protected $guarded = [];

    protected $casts = [
        'resource_profile' => 'array',
    ];

    public function sections(): HasMany
    {
        return $this->hasMany(TestSection::class);
    }

    public function ownerDoctor()
    {
        return $this->belongsTo(Doctor::class, 'owner_doctor_id');
    }

    public function imageInterpretation(): HasOne
    {
        return $this->hasOne(TestCardInterpretation::class, 'test_id');
    }

    public function testCards(): HasMany
    {
        return $this->hasMany(TestCard::class, 'test_id');
    }

    public function sortCards(): HasMany
    {
        return $this->hasMany(TestSortCard::class, 'test_id');
    }

    public function sortInterpretation(): HasOne
    {
        return $this->hasOne(
            TestSortInterpretation::class,
            'test_id',
            'id'
        );
    }

    public function assignments(): HasMany
    {
        return $this->hasMany(
            TestAssignment::class,
            'test_id'
        );
    }

    public function sessions(): HasMany
    {
        return $this->hasMany(
            TestSession::class,
            'test_id'
        );
    }

    public function clinics(): BelongsToMany
    {
        return $this->belongsToMany(
            Clinic::class,
            'clinic_tests',
            'test_id',
            'clinic_id'
        )->withTimestamps();
    }
}
