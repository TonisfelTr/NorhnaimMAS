<?php

namespace App\Enums;

enum MedicineTypesEnum: string
{
    case Antipsychotic     = '1';
    case Antidepressant    = '2';
    case MoodStabilizer    = '3';
    case AChEInhibitor     = '4';
    case Anticholinergic    = '5';
    case Dopaminomimetic   = '6';
    case Anxiolytic        = '7';
    case BetaAdrenolytics  = '8';
    case Hypnotics         = '9';
    case Psychostimulators = '10';

    case Nootropic = '11';
    public static function getMatched(int $parameter): string {
        return match ($parameter) {
            1       => 'антипсихотики',
            2       => 'антидепрессанты',
            3       => 'стабилизаторы настроения',
            4       => 'ингибиторы АХЭ',
            5       => 'холинолитики',
            6       => 'дофаминомиметики',
            7       => 'анксиолитики',
            8       => 'бета-блокаторы',
            9       => 'гипнотики',
            10      => 'психостимуляторы',
            11      => 'ноотропы',
            default => 'не выбрано'
        };
    }

    public static function getAllMatches(): array {
        return [
            json_decode(json_encode([
                                        'id'    => 1,
                                        'group' => 'Антипсихотики',
                                    ])),
            json_decode(json_encode([
                                        'id'    => 2,
                                        'group' => 'Антидепрессанты',
                                    ])),
            json_decode(json_encode([
                                        'id'    => 3,
                                        'group' => 'Стабилизаторы настроения',
                                    ])),
            json_decode(json_encode([
                                        'id'    => 4,
                                        'group' => 'Ингибиторы АХЭ',
                                    ])),
            json_decode(json_encode([
                                        'id'    => 5,
                                        'group' => 'Холинолитики',
                                    ])),
            json_decode(json_encode([
                                        'id'    => 6,
                                        'group' => 'Дофаминомиметики',
                                    ])),
            json_decode(json_encode([
                                        'id'    => 7,
                                        'group' => 'Анксиолитики',
                                    ])),
            json_decode(json_encode([
                                        'id'    => 8,
                                        'group' => 'Бета-блокаторы',
                                    ])),
            json_decode(json_encode([
                                        'id'    => 9,
                                        'group' => 'Гипнотики',
                                    ])),
            json_decode(json_encode([
                                        'id'    => 10,
                                        'group' => 'Психостимуляторы',
                                    ])),
        ];
    }
}
