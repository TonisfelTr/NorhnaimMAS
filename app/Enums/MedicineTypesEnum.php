<?php

namespace App\Enums;

enum MedicineTypesEnum: int
{
    case Antipsyhotics = 1;
    case Antidepressant = 2;
    case MoodStabilizer = 4;
    case AChEInhibitor = 8;
    case Antiholinergic = 16;
    case Dopaminomimetic = 32;
    case Anxiolytic = 64;
    case BetaAdrenolytics = 128;
}
