<?php

namespace Tests\Unit;

use App\Models\HospitalDepartment;
use App\Models\Patient;
use App\Support\HospitalDepartmentAdmissionPolicy;
use PHPUnit\Framework\TestCase;

class HospitalDepartmentAdmissionPolicyTest extends TestCase
{
    public function test_age_presets_exactly_match_our_three_age_groups(): void
    {
        $cases = [
            ['Детский', 'children', 0, 8],
            ['Подростковый', 'adolescents', 8, 16],
            ['Взрослый', 'adults', 16, null],
        ];
        foreach ($cases as [$profile, $group, $min, $max]) {
            $department = new HospitalDepartment(['profile' => $profile]);
            $age = HospitalDepartmentAdmissionPolicy::agePreset($department);
            $this->assertSame($group, $age['group']);
            $this->assertSame($min, $age['min_age_inclusive']);
            $this->assertSame($max, $age['max_age_exclusive']);
        }
    }

    public function test_clinical_profiles_do_not_impose_unrequested_age_limits(): void
    {
        foreach (['Наркологический', 'Геронтологический', 'Психосоматический'] as $profile) {
            $department = new HospitalDepartment(['profile' => $profile]);
            $this->assertNull(HospitalDepartmentAdmissionPolicy::agePreset($department));
        }
    }

    public function test_narcology_is_recognized_as_profile_not_diagnosis_check(): void
    {
        $department = new HospitalDepartment(['profile' => 'Наркологический']);
        $this->assertSame('narcological', HospitalDepartmentAdmissionPolicy::profileCategory($department));
    }
}
