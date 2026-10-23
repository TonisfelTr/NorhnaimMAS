<?php

namespace Tests\Unit;

use App\Support\HospitalDepartmentAgeLimits;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class HospitalDepartmentAgeLimitsTest extends TestCase
{
    public function test_custom_inclusive_range(): void
    {
        $this->assertSame(['age_min_years'=>60,'age_max_years'=>90],
            HospitalDepartmentAgeLimits::validate(['age_min_years'=>'60','age_max_years'=>'90'], 'Геронтологический'));
    }

    public function test_one_sided_limits(): void
    {
        $this->assertSame(['age_min_years'=>18,'age_max_years'=>null],
            HospitalDepartmentAgeLimits::validate(['age_min_years'=>'18'], 'Наркологический'));
    }

    public function test_inverted_range_is_rejected(): void
    {
        $this->expectException(ValidationException::class);
        HospitalDepartmentAgeLimits::validate(['age_min_years'=>90,'age_max_years'=>60], 'Психосоматический');
    }

    public function test_age_preset_cannot_be_overridden(): void
    {
        $this->expectException(ValidationException::class);
        HospitalDepartmentAgeLimits::validate(['age_min_years'=>0,'age_max_years'=>18], 'Детский');
    }
}
