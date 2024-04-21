<?php

namespace App\Enums;

enum ContraindicationsTypesEnum: int
{
    case Depression = 1;
    case ConsciousnessDisturbances = 2;
    case NarrowAngleGlaucoma = 3;
    case Hypotension = 4;
    case Pheochromocytoma = 5;
    case LiverDamage = 6;
    case KidneyDamage = 7;
    case HematopoieticDisorder = 8;
    case Myxedema = 9;
    case BrainProgressiveSystemicDiseases = 10;
    case SpinalCordProgressiveSystemicDiseases = 11;
    case DecompensatedHeartDefects = 12;
    case Thromboembolism = 13;
    case LateBronchiectasis = 14;
    case Pregnancy = 15;
    case BrestFeeding = 16;
    case Coma = 17;
}
