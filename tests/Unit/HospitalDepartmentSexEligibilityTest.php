<?php

namespace Tests\Unit;

use App\Models\HospitalDepartment;
use App\Models\Patient;
use App\Support\HospitalDepartmentSexEligibility;
use PHPUnit\Framework\TestCase;

class HospitalDepartmentSexEligibilityTest extends TestCase
{
    public function test_gender_rules_for_department(): void
    {
        foreach ([
            ['M', 'male', true],
            ['F', 'male', false],
            ['F', 'female', true],
            ['M', 'female', false],
            ['M', 'mixed', true],
            ['F', 'mixed', true],
            [null, 'male', false],
            [null, 'female', false],
            [null, 'mixed', true],
            ['M', 'invalid_value', false],
        ] as [$patientGender, $departmentSexType, $expected]) {
            $patient = new Patient();
            $patient->gender = $patientGender;
            $department = new HospitalDepartment();
            $department->sex_type = $departmentSexType;

            $this->assertSame(
                $expected,
                HospitalDepartmentSexEligibility::violation($patient, $department) === null,
                sprintf('Пол %s, тип отделения %s', $patientGender ?? 'null', $departmentSexType)
            );
        }
    }
}
