<?php

namespace Database\Seeders;

use App\Enums\SymptomsEnum;
use App\Models\Diagnose;
use App\Models\DiagnoseSymptom;
use App\Models\Symptom;
use Illuminate\Database\Seeder;

class DiagnosesFillSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $diagnoses = [
            // F31
            [
                'code'        => 'F31.0',
                'title'       => 'Биполярное аффективное расстройство, текущий эпизод гипомании',
                'description' => 'У пациента наблюдаются гипоманиакальные симптомы, включая повышенное настроение и гиперактивность.',
                'relatives'   => [
                    'F30.0',
                    'F34.0',
                ],
                'exceptions'  => [
                    'F32.1',
                    'F32.2',
                ],
                'symptoms'    => [
                    'required' => [
                        SymptomsEnum::ElevatedMood,
                        SymptomsEnum::IncreasedSelfEsteem,
                        SymptomsEnum::DecreasedNeedForSleep,
                    ],
                    'relative' => [
                        SymptomsEnum::Hyperactivity,
                        SymptomsEnum::Impulsivity,
                        SymptomsEnum::Talkativeness,
                        SymptomsEnum::Distractibility,
                    ],
                ],
                'criteria'    => [2, 1],
            ],
            [
                'code'        => 'F31.1',
                'title'       => 'Биполярное аффективное расстройство, текущий эпизод мании без психотических симптомов',
                'description' => 'Текущий эпизод мании с выраженными симптомами, без психотических проявлений.',
                'relatives'   => [
                    'F30.1',
                    'F34.0',
                ],
                'exceptions'  => [
                    'F32.3',
                    'F31.2',
                ],
                'symptoms'    => [
                    'required' => [
                        SymptomsEnum::ElevatedMood,
                        SymptomsEnum::IncreasedSelfEsteem,
                        SymptomsEnum::DecreasedNeedForSleep,
                        SymptomsEnum::Impulsivity,
                    ],
                    'relative' => [
                        SymptomsEnum::Hyperactivity,
                        SymptomsEnum::Talkativeness,
                        SymptomsEnum::Distractibility,
                        SymptomsEnum::PoorJudgement,
                    ],
                ],
                'criteria'    => [3, 1],
            ],
            [
                'code'        => 'F31.2',
                'title'       => 'Биполярное аффективное расстройство, текущий эпизод мании с психотическими симптомами',
                'description' => 'Текущий эпизод мании, сопровождающийся психотическими симптомами, такими как бред или галлюцинации.',
                'relatives'   => [
                    'F30.2',
                    'F20.3',
                ],
                'exceptions'  => [
                    'F32.3',
                    'F32.2',
                ],
                'symptoms'    => [
                    'required' => [
                        SymptomsEnum::ElevatedMood,
                        SymptomsEnum::IncreasedSelfEsteem,
                        SymptomsEnum::Hallucinations,
                        SymptomsEnum::Delusions,
                    ],
                    'relative' => [
                        SymptomsEnum::Impulsivity,
                        SymptomsEnum::Talkativeness,
                        SymptomsEnum::Hyperactivity,
                        SymptomsEnum::PsychoticFeatures,
                    ],
                ],
                'criteria'    => [3, 1],
            ],
            [
                'code'        => 'F31.3',
                'title'       => 'Биполярное аффективное расстройство, текущий эпизод депрессии средней степени',
                'description' => 'Текущий депрессивный эпизод умеренной степени в рамках биполярного аффективного расстройства.',
                'relatives'   => [
                    'F31.2',
                    'F33.1',
                ],
                'exceptions'  => [
                    'F30.1',
                    'F30.0',
                ],
                'symptoms'    => [
                    'required' => [
                        SymptomsEnum::DepressedMood,
                        SymptomsEnum::Fatigue,
                        SymptomsEnum::Anhedonia,
                    ],
                    'relative' => [
                        SymptomsEnum::PoorConcentration,
                        SymptomsEnum::Guilt,
                        SymptomsEnum::AppetiteChanges,
                        SymptomsEnum::ThoughtsOfDeath,
                    ],
                ],
                'criteria'    => [2, 1],
            ],
            [
                'code'        => 'F31.5',
                'title'       => 'Биполярное расстройство, текущий эпизод тяжелой депрессии без психотических симптомов',
                'description' => 'Текущий эпизод тяжелой депрессии в рамках биполярного аффективного расстройства, без психотических проявлений.',
                'relatives'   => [
                    'F33.2',
                    'F32.2',
                ],
                'exceptions'  => [
                    'F30.1',
                    'F31.0',
                ],
                'symptoms'    => [
                    'required' => [
                        SymptomsEnum::DepressedMood,
                        SymptomsEnum::Fatigue,
                        SymptomsEnum::Anhedonia,
                        SymptomsEnum::ThoughtsOfDeath,
                    ],
                    'relative' => [
                        SymptomsEnum::PoorConcentration,
                        SymptomsEnum::Guilt,
                        SymptomsEnum::AppetiteChanges,
                        SymptomsEnum::WeightChanges,
                    ],
                ],
                'criteria'    => [3, 1],
            ],
            [
                'code'        => 'F31.6',
                'title'       => 'Биполярное расстройство, текущий эпизод смешанный',
                'description' => 'Текущий эпизод смешанного типа, характеризующийся как маниакальными, так и депрессивными симптомами.',
                'relatives'   => [
                    'F31.0',
                    'F31.1',
                    'F31.3',
                ],
                'exceptions'  => [
                    'F30.0',
                    'F32.0',
                ],
                'symptoms'    => [
                    'required' => [
                        SymptomsEnum::DepressedMood,
                        SymptomsEnum::ElevatedMood,
                        SymptomsEnum::Irritability,
                    ],
                    'relative' => [
                        SymptomsEnum::Hyperactivity,
                        SymptomsEnum::Fatigue,
                        SymptomsEnum::Anhedonia,
                        SymptomsEnum::ThoughtsOfDeath,
                    ],
                ],
                'criteria'    => [2, 1],
            ],
            [
                'code'        => 'F31.9',
                'title'       => 'Биполярное аффективное расстройство, неуточненное',
                'description' => 'Недифференцированное биполярное расстройство, включающее признаки как маниакальных, так и депрессивных эпизодов.',
                'relatives'   => [
                    'F30.9',
                    'F32.9',
                    'F34.0',
                ],
                'exceptions'  => [
                    'F30.0',
                    'F32.0',
                ],
                'symptoms'    => [
                    'required' => [
                        SymptomsEnum::DepressedMood,
                        SymptomsEnum::ElevatedMood,
                    ],
                    'relative' => [
                        SymptomsEnum::Impulsivity,
                        SymptomsEnum::Anhedonia,
                        SymptomsEnum::Hyperactivity,
                        SymptomsEnum::PoorConcentration,
                    ],
                ],
                'criteria'    => [2, 1],
            ],
            // F90
            [
                'code'        => 'F90.0',
                'title'       => 'Гиперкинетическое расстройство поведения',
                'description' => 'Расстройство, характеризующееся гиперактивностью, импульсивностью и трудностями в концентрации внимания.',
                'relatives'   => [
                    'F90.1',
                    'F91.3',
                ],
                'exceptions'  => [
                    'F84.0',
                    'F42.2',
                ],
                'symptoms'    => [
                    'required' => [
                        SymptomsEnum::Hyperactivity,
                        SymptomsEnum::Impulsivity,
                        SymptomsEnum::PoorConcentration,
                    ],
                    'relative' => [
                        SymptomsEnum::Anxiety,
                        SymptomsEnum::SocialWithdrawal,
                        SymptomsEnum::Talkativeness,
                    ],
                ],
                'criteria'    => [2, 1],
            ],
            [
                'code'        => 'F90.1',
                'title'       => 'Синдром дефицита внимания с гиперактивностью',
                'description' => 'Расстройство, включающее нарушение внимания и гиперактивность, характерные для детей школьного возраста.',
                'relatives'   => [
                    'F90.0',
                    'F98.8',
                ],
                'exceptions'  => [
                    'F94.1',
                    'F95.0',
                ],
                'symptoms'    => [
                    'required' => [
                        SymptomsEnum::PoorConcentration,
                        SymptomsEnum::Hyperactivity,
                        SymptomsEnum::Impulsivity,
                    ],
                    'relative' => [
                        SymptomsEnum::Anxiety,
                        SymptomsEnum::Talkativeness,
                        SymptomsEnum::Indecisiveness,
                    ],
                ],
                'criteria'    => [3, 1],
            ],
            [
                'code'        => 'F05.0',
                'title'       => 'Делирий, не связанный с употреблением психоактивных веществ',
                'description' => 'Временное нарушение сознания, сопровождающееся дезориентацией, галлюцинациями и нарушением внимания.',
                'relatives'   => [
                    'F10.0',
                    'F06.7',
                ],
                'exceptions'  => [
                    'F06.4',
                    'F04.0',
                ],
                'symptoms'    => [
                    'required' => [
                        SymptomsEnum::Disorientation,
                        SymptomsEnum::Hallucinations,
                        SymptomsEnum::PoorConcentration,
                    ],
                    'relative' => [
                        SymptomsEnum::PsychoticFeatures,
                        SymptomsEnum::MemoryProblems,
                        SymptomsEnum::Agitation,
                    ],
                ],
                'criteria'    => [2, 1],
            ],
            [
                'code'        => 'F05.1',
                'title'       => 'Субсиндромальный делирий',
                'description' => 'Частичный делирий, характеризующийся менее выраженными психическими нарушениями, включая дезориентацию и проблемы с вниманием.',
                'relatives'   => [
                    'F06.8',
                    'F07.2',
                ],
                'exceptions'  => [
                    'F07.9',
                    'F10.5',
                ],
                'symptoms'    => [
                    'required' => [
                        SymptomsEnum::PoorConcentration,
                        SymptomsEnum::Disorientation,
                    ],
                    'relative' => [
                        SymptomsEnum::Hallucinations,
                        SymptomsEnum::MemoryProblems,
                        SymptomsEnum::Anxiety,
                    ],
                ],
                'criteria'    => [1, 1],
            ],
            [
                'code'        => 'F05.8',
                'title'       => 'Другие делириозные состояния',
                'description' => 'Делириозные состояния, сопровождающиеся нарушениями сознания и различными психическими симптомами.',
                'relatives'   => [
                    'F05.1',
                    'F06.7',
                ],
                'exceptions'  => [
                    'F10.7',
                    'F06.3',
                ],
                'symptoms'    => [
                    'required' => [
                        SymptomsEnum::Hallucinations,
                        SymptomsEnum::Disorientation,
                        SymptomsEnum::PsychoticFeatures,
                    ],
                    'relative' => [
                        SymptomsEnum::PoorConcentration,
                        SymptomsEnum::Agitation,
                        SymptomsEnum::MemoryProblems,
                    ],
                ],
                'criteria'    => [2, 1],
            ],
            // F00.*: Деменция при болезни Альцгеймера
            [
                'code'        => 'F00.0',
                'title'       => 'Деменция при болезни Альцгеймера с ранним началом',
                'description' => 'Деменция при болезни Альцгеймера, проявляющаяся в молодом возрасте с быстрым прогрессированием.',
                'relatives'   => [
                    'F00.1',
                    'F01.0',
                ],
                'exceptions'  => [
                    'F03.0',
                    'F07.9',
                ],
                'symptoms'    => [
                    'required' => [
                        SymptomsEnum::MemoryProblems,
                        SymptomsEnum::PoorJudgement,
                        SymptomsEnum::Disorientation,
                    ],
                    'relative' => [
                        SymptomsEnum::Anxiety,
                        SymptomsEnum::PoorConcentration,
                        SymptomsEnum::SocialWithdrawal,
                    ],
                ],
                'criteria'    => [2, 1],
            ],
            [
                'code'        => 'F00.1',
                'title'       => 'Деменция при болезни Альцгеймера с поздним началом',
                'description' => 'Деменция с постепенным началом и медленным прогрессированием, возникающая в старшем возрасте.',
                'relatives'   => [
                    'F00.0',
                    'F03.0',
                ],
                'exceptions'  => [
                    'F06.0',
                    'F07.0',
                ],
                'symptoms'    => [
                    'required' => [
                        SymptomsEnum::MemoryProblems,
                        SymptomsEnum::Disorientation,
                        SymptomsEnum::SocialWithdrawal,
                    ],
                    'relative' => [
                        SymptomsEnum::PoorJudgement,
                        SymptomsEnum::DepressedMood,
                        SymptomsEnum::Anxiety,
                    ],
                ],
                'criteria'    => [2, 1],
            ],
            [
                'code'        => 'F00.2',
                'title'       => 'Деменция при болезни Альцгеймера, атипичная или смешанная форма',
                'description' => 'Атипичная форма деменции при болезни Альцгеймера, включающая различные когнитивные и поведенческие нарушения.',
                'relatives'   => [
                    'F00.0',
                    'F00.1',
                ],
                'exceptions'  => [
                    'F06.3',
                    'F07.9',
                ],
                'symptoms'    => [
                    'required' => [
                        SymptomsEnum::MemoryProblems,
                        SymptomsEnum::PoorJudgement,
                        SymptomsEnum::Anhedonia,
                    ],
                    'relative' => [
                        SymptomsEnum::Disorientation,
                        SymptomsEnum::Hallucinations,
                        SymptomsEnum::Anxiety,
                    ],
                ],
                'criteria'    => [2, 1],
            ],
            // F32.*: Депрессивные эпизоды
            [
                'code'        => 'F32.0',
                'title'       => 'Депрессивный эпизод легкой степени',
                'description' => 'Легкая степень депрессивного эпизода с незначительным снижением активности.',
                'relatives'   => [
                    'F32.1',
                    'F34.1',
                ],
                'exceptions'  => [
                    'F31.0',
                    'F31.3',
                ],
                'symptoms'    => [
                    'required' => [
                        SymptomsEnum::DepressedMood,
                        SymptomsEnum::Anhedonia,
                    ],
                    'relative' => [
                        SymptomsEnum::Fatigue,
                        SymptomsEnum::PoorConcentration,
                        SymptomsEnum::Insomnia,
                    ],
                ],
                'criteria'    => [1, 1],
            ],
            [
                'code'        => 'F32.1',
                'title'       => 'Депрессивный эпизод умеренной степени',
                'description' => 'Умеренная степень депрессивного эпизода, включающая подавленное настроение и ухудшение активности.',
                'relatives'   => [
                    'F32.0',
                    'F33.1',
                ],
                'exceptions'  => [
                    'F31.2',
                    'F31.5',
                ],
                'symptoms'    => [
                    'required' => [
                        SymptomsEnum::DepressedMood,
                        SymptomsEnum::Anhedonia,
                        SymptomsEnum::PoorConcentration,
                    ],
                    'relative' => [
                        SymptomsEnum::Fatigue,
                        SymptomsEnum::Guilt,
                        SymptomsEnum::AppetiteChanges,
                    ],
                ],
                'criteria'    => [2, 1],
            ],
            [
                'code'        => 'F32.2',
                'title'       => 'Депрессивный эпизод тяжелой степени без психотических симптомов',
                'description' => 'Тяжелая форма депрессии с интенсивным снижением активности, но без психотических симптомов.',
                'relatives'   => [
                    'F33.2',
                    'F32.1',
                ],
                'exceptions'  => [
                    'F31.0',
                    'F31.6',
                ],
                'symptoms'    => [
                    'required' => [
                        SymptomsEnum::DepressedMood,
                        SymptomsEnum::Anhedonia,
                        SymptomsEnum::Fatigue,
                        SymptomsEnum::ThoughtsOfDeath,
                    ],
                    'relative' => [
                        SymptomsEnum::Insomnia,
                        SymptomsEnum::PsychomotorRetardation,
                        SymptomsEnum::WeightChanges,
                    ],
                ],
                'criteria'    => [3, 1],
            ],
            [
                'code'        => 'F32.3',
                'title'       => 'Тяжелый депрессивный эпизод с психотическими симптомами',
                'description' => 'Эпизод тяжелой депрессии, сопровождающийся бредом, галлюцинациями или психомоторными нарушениями.',
                'relatives'   => ['F32.2', 'F33.3'],
                'exceptions'  => ['F20.0', 'F25.1'],
                'symptoms'    => [
                    'required' => [
                        SymptomsEnum::DepressedMood,
                        SymptomsEnum::LossOfInterestInOtherActivities,
                        SymptomsEnum::PsychoticFeatures,
                    ],
                    'relative' => [
                        SymptomsEnum::Fatigue,
                        SymptomsEnum::SleepDisturbance,
                        SymptomsEnum::WeightLoss,
                        SymptomsEnum::Guilt,
                    ],
                ],
                'criteria'    => [3, 2],
            ],

            // F44.*: Диссоциативные расстройства
            [
                'code'        => 'F44.0',
                'title'       => 'Диссоциативная амнезия',
                'description' => 'Амнезия на важные личные события, обычно вызываемая стрессом или травмой.',
                'relatives'   => [
                    'F44.1',
                    'F44.9',
                ],
                'exceptions'  => [
                    'F48.1',
                    'F10.0',
                ],
                'symptoms'    => [
                    'required' => [
                        SymptomsEnum::MemoryProblems,
                        SymptomsEnum::Anxiety,
                    ],
                    'relative' => [
                        SymptomsEnum::PoorConcentration,
                        SymptomsEnum::SocialWithdrawal,
                        SymptomsEnum::Agitation,
                    ],
                ],
                'criteria'    => [1, 1],
            ],
            [
                'code'        => 'F44.1',
                'title'       => 'Диссоциативная фуга',
                'description' => 'Расстройство, включающее амнезию и внезапные путешествия, сопровождающиеся потерей идентичности.',
                'relatives'   => [
                    'F44.0',
                    'F44.9',
                ],
                'exceptions'  => [
                    'F48.1',
                    'F10.1',
                ],
                'symptoms'    => [
                    'required' => [
                        SymptomsEnum::Disorientation,
                        SymptomsEnum::MemoryProblems,
                    ],
                    'relative' => [
                        SymptomsEnum::Anxiety,
                        SymptomsEnum::SocialWithdrawal,
                        SymptomsEnum::PoorJudgement,
                    ],
                ],
                'criteria'    => [2, 1],
            ],
            [
                'code'        => 'F44.2',
                'title'       => 'Диссоциативное расстройство двигательной функции',
                'description' => 'Диссоциативное расстройство, проявляющееся потерей двигательной функции при отсутствии физической причины.',
                'relatives'   => [
                    'F44.4',
                    'F44.6',
                ],
                'exceptions'  => [
                    'F51.2',
                    'F10.2',
                ],
                'symptoms'    => [
                    'required' => [
                        SymptomsEnum::PoorJudgement,
                        SymptomsEnum::SocialWithdrawal,
                    ],
                    'relative' => [
                        SymptomsEnum::Anxiety,
                        SymptomsEnum::BodyAches,
                        SymptomsEnum::Fatigue,
                    ],
                ],
                'criteria'    => [1, 1],
            ],
            [
                'code'        => 'F44.3',
                'title'       => 'Транс состояния и одержимость',
                'description' => 'Расстройства, характеризующиеся временной потерей личной идентичности или полного осознания окружающей среды, сопровождающиеся ощущением одержимости или нахождения в трансовом состоянии.',
                'relatives'   => ['F44.0', 'F44.4'],
                'exceptions'  => ['F20.0', 'F48.1'],
                'symptoms'    => [
                    'required' => [
                        SymptomsEnum::AlteredConsciousness, // Изменённое сознание
                        SymptomsEnum::LossOfIdentity,       // Потеря идентичности
                        SymptomsEnum::UncontrolledMovements // Неконтролируемые движения
                    ],
                    'relative' => [
                        SymptomsEnum::MemoryLoss,           // Потеря памяти
                        SymptomsEnum::Distress,             // Дистресс
                        SymptomsEnum::SocialWithdrawal,     // Социальная изоляция
                        SymptomsEnum::DepressedMood,        // Подавленное настроение
                        SymptomsEnum::Fatigue,              // Усталость или потеря энергии
                        SymptomsEnum::Anhedonia,            // Потеря интереса или удовольствия
                        SymptomsEnum::Agitation,            // Ажитация или возбуждение
                        SymptomsEnum::PoorConcentration     // Плохая концентрация
                    ],
                ],
                'criteria'    => [2, 2], // 2 обязательных и 2 относительных симптома
            ],
            [
                'code'        => 'F44.81',
                'title'       => 'Диссоциативное ступорозное состояние',
                'description' => 'Состояние, характеризующееся значительным снижением или отсутствием произвольных движений и речи, несмотря на сохранность сознания и восприятия.',
                'relatives'   => ['F44.2', 'F44.4'],
                'exceptions'  => ['F20.0', 'F32.3'],
                'symptoms'    => [
                    'required' => [
                        SymptomsEnum::AlteredConsciousness,       // Изменённое сознание
                        SymptomsEnum::PsychomotorRetardation,     // Психомоторная заторможенность
                        SymptomsEnum::UncontrolledMovements       // Неконтролируемые движения
                    ],
                    'relative' => [
                        SymptomsEnum::Distress,                   // Дистресс
                        SymptomsEnum::SocialWithdrawal,           // Социальная изоляция
                        SymptomsEnum::Fatigue,                    // Усталость или потеря энергии
                        SymptomsEnum::DepressedMood,              // Подавленное настроение
                        SymptomsEnum::PoorConcentration,          // Плохая концентрация
                        SymptomsEnum::MemoryLoss                  // Потеря памяти
                    ],
                ],
                'criteria'    => [2, 2], // 2 обязательных и 2 относительных симптома
            ],
            // F48
            [
                'code'        => 'F48.0',
                'title'       => 'Неврастения',
                'description' => 'Состояние, характеризующееся быстрой утомляемостью, снижением работоспособности и раздражительностью.',
                'relatives'   => ['F45.0', 'F41.0'],
                'exceptions'  => ['F32.1', 'F33.1'],
                'symptoms'    => [
                    'required' => [SymptomsEnum::Fatigue, SymptomsEnum::Irritability],
                    'relative' => [SymptomsEnum::PoorConcentration, SymptomsEnum::Insomnia, SymptomsEnum::Anxiety],
                ],
                'criteria'    => [2, 1],
            ],
            [
                'code'        => 'F48.1',
                'title'       => 'Синдром деперсонализации-дереализации',
                'description' => 'Синдром, характеризующийся ощущением отчуждения от собственной личности или окружения.',
                'relatives'   => ['F44.0', 'F45.2'],
                'exceptions'  => ['F32.0', 'F07.0'],
                'symptoms'    => [
                    'required' => [SymptomsEnum::Disorientation, SymptomsEnum::Anxiety],
                    'relative' => [
                        SymptomsEnum::SocialWithdrawal,
                        SymptomsEnum::PoorConcentration,
                        SymptomsEnum::MemoryProblems,
                    ],
                ],
                'criteria'    => [1, 1],
            ],
            [
                'code'        => 'F48.8',
                'title'       => 'Другие уточненные невротические расстройства',
                'description' => 'Невротические расстройства, включающие широкий спектр симптомов, не подходящих под другие конкретные диагнозы.',
                'relatives'   => ['F45.0', 'F41.2'],
                'exceptions'  => ['F32.2', 'F30.1'],
                'symptoms'    => [
                    'required' => [SymptomsEnum::Anxiety, SymptomsEnum::SocialWithdrawal],
                    'relative' => [SymptomsEnum::Insomnia, SymptomsEnum::Irritability, SymptomsEnum::MemoryProblems],
                ],
                'criteria'    => [1, 1],
            ],
            [
                'code'        => 'F48.9',
                'title'       => 'Невротическое расстройство неуточненное',
                'description' => 'Неопределенные невротические расстройства, включающие симптомы, но не соответствующие конкретному диагнозу.',
                'relatives'   => ['F45.9', 'F41.9'],
                'exceptions'  => ['F33.0', 'F30.0'],
                'symptoms'    => [
                    'required' => [SymptomsEnum::Anxiety, SymptomsEnum::Fatigue],
                    'relative' => [SymptomsEnum::PoorConcentration, SymptomsEnum::SocialWithdrawal],
                ],
                'criteria'    => [1, 1],
            ],
            // F06
            [
                'code'        => 'F06.0',
                'title'       => 'Органический галлюциноз',
                'description' => 'Психическое расстройство, проявляющееся яркими и стойкими галлюцинациями, обычно без нарушений сознания.',
                'relatives'   => ['F23.3', 'F20.9'],
                'exceptions'  => ['F32.3', 'F33.3'],
                'symptoms'    => [
                    'required' => [SymptomsEnum::Hallucinations, SymptomsEnum::Anxiety],
                    'relative' => [
                        SymptomsEnum::Paranoia,
                        SymptomsEnum::PoorJudgement,
                        SymptomsEnum::PoorConcentration,
                    ],
                ],
                'criteria'    => [2, 1],
            ],
            [
                'code'        => 'F06.1',
                'title'       => 'Органическое кататоническое расстройство',
                'description' => 'Кататоническое расстройство, связанное с органическим поражением головного мозга, проявляющееся двигательной заторможенностью или возбуждением.',
                'relatives'   => ['F20.2', 'F25.1'],
                'exceptions'  => ['F33.1', 'F32.2'],
                'symptoms'    => [
                    'required' => [SymptomsEnum::PsychomotorRetardation, SymptomsEnum::Agitation],
                    'relative' => [
                        SymptomsEnum::Disorientation,
                        SymptomsEnum::Anxiety,
                        SymptomsEnum::PoorConcentration,
                    ],
                ],
                'criteria'    => [2, 1],
            ],
            [
                'code'        => 'F06.2',
                'title'       => 'Органическое бредовое расстройство',
                'description' => 'Расстройство, проявляющееся стойкими бредовыми идеями, связанными с органическим поражением мозга.',
                'relatives'   => ['F20.0', 'F25.0'],
                'exceptions'  => ['F30.2', 'F31.2'],
                'symptoms'    => [
                    'required' => [SymptomsEnum::Delusions, SymptomsEnum::Paranoia],
                    'relative' => [SymptomsEnum::Anxiety, SymptomsEnum::PoorJudgement, SymptomsEnum::SocialWithdrawal],
                ],
                'criteria'    => [2, 1],
            ],
            [
                'code'        => 'F06.3',
                'title'       => 'Органическое расстройство настроения',
                'description' => 'Изменение настроения, вызванное органическими изменениями в головном мозге.',
                'relatives'   => ['F32.0', 'F31.0'],
                'exceptions'  => ['F33.0', 'F31.3'],
                'symptoms'    => [
                    'required' => [SymptomsEnum::DepressedMood, SymptomsEnum::Anhedonia],
                    'relative' => [SymptomsEnum::Anxiety, SymptomsEnum::SocialWithdrawal],
                ],
                'criteria'    => [2, 1],
            ],
            [
                'code'        => 'F06.4',
                'title'       => 'Органическое тревожное расстройство',
                'description' => 'Психическое расстройство, связанное с тревожными состояниями, вызванное органическими поражениями мозга.',
                'relatives'   => ['F41.0', 'F40.0'],
                'exceptions'  => ['F32.2', 'F33.2'],
                'symptoms'    => [
                    'required' => [SymptomsEnum::Anxiety, SymptomsEnum::Fatigue],
                    'relative' => [SymptomsEnum::Insomnia, SymptomsEnum::Irritability, SymptomsEnum::SocialWithdrawal],
                ],
                'criteria'    => [2, 1],
            ],
            [
                'code'        => 'F06.5',
                'title'       => 'Органическое диссоциативное расстройство',
                'description' => 'Диссоциативное расстройство, вызванное органическими изменениями в головном мозге, характеризующееся изменениями в восприятии и ощущениях.',
                'relatives'   => ['F44.0', 'F48.1'],
                'exceptions'  => ['F32.0', 'F33.0'],
                'symptoms'    => [
                    'required' => [SymptomsEnum::Disorientation, SymptomsEnum::Anxiety],
                    'relative' => [SymptomsEnum::SocialWithdrawal, SymptomsEnum::PoorConcentration],
                ],
                'criteria'    => [1, 1],
            ],
            [
                'code'        => 'F06.6',
                'title'       => 'Органическое эмоционально-лабильное (астеническое) расстройство',
                'description' => 'Астеническое расстройство, вызванное органическими изменениями в головном мозге, проявляющееся эмоциональной нестабильностью и усталостью.',
                'relatives'   => ['F34.1', 'F48.0'],
                'exceptions'  => ['F32.0', 'F33.1'],
                'symptoms'    => [
                    'required' => [SymptomsEnum::EmotionalLability, SymptomsEnum::Fatigue],
                    'relative' => [SymptomsEnum::PoorConcentration, SymptomsEnum::Irritability, SymptomsEnum::Anxiety],
                ],
                'criteria'    => [2, 1],
            ],
            [
                'code'        => 'F06.7',
                'title'       => 'Органическое когнитивное расстройство легкой степени',
                'description' => 'Легкое когнитивное расстройство, вызванное органическими изменениями в головном мозге, характеризующееся нарушением памяти и концентрации.',
                'relatives'   => ['F02.8', 'F07.8'],
                'exceptions'  => ['F32.0', 'F33.0'],
                'symptoms'    => [
                    'required' => [SymptomsEnum::MemoryProblems, SymptomsEnum::PoorConcentration],
                    'relative' => [SymptomsEnum::PoorJudgement, SymptomsEnum::Disorientation],
                ],
                'criteria'    => [2, 1],
            ],
            [
                'code'        => 'F06.8',
                'title'       => 'Другие уточненные психические расстройства вследствие повреждения или дисфункции головного мозга',
                'description' => 'Различные психические расстройства, связанные с органическими изменениями мозга, которые не подходят под другие диагнозы.',
                'relatives'   => ['F07.0', 'F07.8'],
                'exceptions'  => ['F32.0', 'F33.0'],
                'symptoms'    => [
                    'required' => [SymptomsEnum::PoorConcentration, SymptomsEnum::Anxiety],
                    'relative' => [SymptomsEnum::Irritability, SymptomsEnum::Fatigue],
                ],
                'criteria'    => [1, 1],
            ],
            [
                'code'        => 'F06.9',
                'title'       => 'Неуточненные психические расстройства, вызванные повреждением или дисфункцией головного мозга',
                'description' => 'Неопределенные психические расстройства, вызванные органическими изменениями мозга, включающие широкий спектр симптомов.',
                'relatives'   => ['F07.9', 'F09.0'],
                'exceptions'  => ['F32.0', 'F33.0'],
                'symptoms'    => [
                    'required' => [SymptomsEnum::Anxiety, SymptomsEnum::PoorConcentration],
                    'relative' => [SymptomsEnum::MemoryProblems, SymptomsEnum::SocialWithdrawal],
                ],
                'criteria'    => [1, 1],
            ],
            // F68
            [
                'code'        => 'F68.0',
                'title'       => 'Сенсибилизирующее расстройство',
                'description' => 'Поведение, при котором человек, не имея физического заболевания, убежден в своей болезни и ведет себя как больной.',
                'relatives'   => [
                    'F45.2', 'F45.9'
                ],
                'exceptions'  => [
                    'F68.1', 'F68.8'
                ],
                'symptoms'    => [
                    'required' => [
                        SymptomsEnum::HypochondriacalBehavior,
                        SymptomsEnum::ConvictionInIllness,
                    ],
                    'relative' => [
                        SymptomsEnum::SocialWithdrawal,
                        SymptomsEnum::SeekingMedicalAttention,
                        SymptomsEnum::Depression,
                    ]
                ],
                'criteria'    => [2, 1]
            ],
            [
                'code'        => 'F68.1',
                'title'       => 'Преднамеренное производство или симуляция симптомов [фактическое расстройство]',
                'description' => 'Умышленное воспроизведение симптомов или имитация заболеваний без внешних вознаграждений, чаще всего для привлечения внимания.',
                'relatives'   => [
                    'F45.3', 'F68.0'
                ],
                'exceptions'  => [
                    'F64.2', 'F66.1'
                ],
                'symptoms'    => [
                    'required' => [
                        SymptomsEnum::FeigningSymptoms,
                        SymptomsEnum::ManipulativeBehavior,
                    ],
                    'relative' => [
                        SymptomsEnum::AttentionSeeking,
                        SymptomsEnum::InterpersonalConflicts,
                        SymptomsEnum::SocialIsolation,
                    ]
                ],
                'criteria'    => [2, 1]
            ],
            [
                'code'        => 'F68.8',
                'title'       => 'Другие специфические расстройства личности и поведения',
                'description' => 'Расстройства личности и поведения, которые не соответствуют другим категориям и включают в себя отдельные отклонения поведения.',
                'relatives'   => [
                    'F68.1', 'F66.1'
                ],
                'exceptions'  => [
                    'F45.1', 'F66.0'
                ],
                'symptoms'    => [
                    'required' => [
                        SymptomsEnum::BehavioralAberrations,
                    ],
                    'relative' => [
                        SymptomsEnum::SocialIsolation,
                        SymptomsEnum::Anxiety,
                        SymptomsEnum::InterpersonalConflicts,
                    ]
                ],
                'criteria'    => [1, 1]
            ],
            [
                'code'        => 'F68.9',
                'title'       => 'Неуточненные расстройства личности и поведения',
                'description' => 'Расстройства личности и поведения, которые не имеют четких симптомов или не вписываются в другие диагностические категории.',
                'relatives'   => [
                    'F66.1', 'F68.8'
                ],
                'exceptions'  => [
                    'F64.1', 'F68.0'
                ],
                'symptoms'    => [
                    'required' => [
                        SymptomsEnum::UnspecifiedBehavioralIssues,
                    ],
                    'relative' => [
                        SymptomsEnum::SocialIsolation,
                        SymptomsEnum::LowSelfEsteem,
                        SymptomsEnum::Anxiety,
                    ]
                ],
                'criteria'    => [1, 1]
            ],
            // F38
            [
                'code'        => 'F38.0',
                'title'       => 'Другие единичные аффективные расстройства',
                'description' => 'Аффективные расстройства, не подпадающие под другие диагнозы, проявляющиеся эпизодически.',
                'relatives'   => ['F31.9', 'F34.1'],
                'exceptions'  => ['F30.1', 'F32.1'],
                'symptoms'    => [
                    'required' => [SymptomsEnum::DepressedMood, SymptomsEnum::Fatigue],
                    'relative' => [SymptomsEnum::Irritability, SymptomsEnum::Anhedonia, SymptomsEnum::Anxiety],
                ],
                'criteria'    => [2, 1],
            ],
            [
                'code'        => 'F38.1',
                'title'       => 'Другие повторяющиеся аффективные расстройства',
                'description' => 'Повторяющиеся расстройства настроения с чередующимися депрессивными и маниакальными эпизодами, не подходящие под конкретные категории.',
                'relatives'   => ['F31.0', 'F33.0'],
                'exceptions'  => ['F30.0', 'F32.0'],
                'symptoms'    => [
                    'required' => [SymptomsEnum::ElevatedMood, SymptomsEnum::DepressedMood],
                    'relative' => [SymptomsEnum::Anhedonia, SymptomsEnum::Impulsivity, SymptomsEnum::Anxiety],
                ],
                'criteria'    => [2, 1],
            ],
            [
                'code'        => 'F38.8',
                'title'       => 'Другие уточненные аффективные расстройства',
                'description' => 'Аффективные расстройства, включающие эпизоды депрессии и повышенного настроения, не подходящие под другие категории.',
                'relatives'   => ['F34.0', 'F33.9'],
                'exceptions'  => ['F30.0', 'F31.0'],
                'symptoms'    => [
                    'required' => [SymptomsEnum::DepressedMood, SymptomsEnum::Anxiety],
                    'relative' => [
                        SymptomsEnum::ElevatedMood,
                        SymptomsEnum::PoorConcentration,
                        SymptomsEnum::SocialWithdrawal,
                    ],
                ],
                'criteria'    => [1, 1],
            ],
            [
                'code'        => 'F38.9',
                'title'       => 'Неуточненное аффективное расстройство',
                'description' => 'Неопределенное расстройство настроения, включающее депрессивные и маниакальные симптомы.',
                'relatives'   => ['F33.9', 'F31.9'],
                'exceptions'  => ['F30.0', 'F32.0'],
                'symptoms'    => [
                    'required' => [SymptomsEnum::DepressedMood, SymptomsEnum::Fatigue],
                    'relative' => [SymptomsEnum::PoorConcentration, SymptomsEnum::SocialWithdrawal],
                ],
                'criteria'    => [1, 1],
            ],
            // F41
            [
                'code'        => 'F41.0',
                'title'       => 'Паническое расстройство [эпизодическая пароксизмальная тревога]',
                'description' => 'Повторяющиеся эпизоды панических атак, сопровождающиеся выраженной тревогой и физиологическими симптомами.',
                'relatives'   => ['F40.0', 'F43.1'],
                'exceptions'  => ['F41.1', 'F41.2'],
                'symptoms'    => [
                    'required' => [
                        SymptomsEnum::PanicAttacks,
                        SymptomsEnum::Sweating,
                        SymptomsEnum::Tachycardia,
                    ],
                    'relative' => [
                        SymptomsEnum::Dizziness,
                        SymptomsEnum::FearOfDying,
                        SymptomsEnum::ShortnessOfBreath,
                        SymptomsEnum::HotFlushes,
                    ],
                ],
                'criteria'    => [2, 1],
            ],
            [
                'code'        => 'F41.1',
                'title'       => 'Генерализованное тревожное расстройство',
                'description' => 'Постоянная и чрезмерная тревога по поводу различных аспектов повседневной жизни, сопровождаемая напряжением и физическими симптомами.',
                'relatives'   => ['F43.1', 'F40.1'],
                'exceptions'  => ['F41.0', 'F41.2'],
                'symptoms'    => [
                    'required' => [
                        SymptomsEnum::ChronicAnxiety,
                        SymptomsEnum::Restlessness,
                        SymptomsEnum::Fatigue,
                    ],
                    'relative' => [
                        SymptomsEnum::PoorConcentration,
                        SymptomsEnum::MuscleTension,
                        SymptomsEnum::SleepDisturbance,
                        SymptomsEnum::Irritability,
                    ],
                ],
                'criteria'    => [2, 1],
            ],
            [
                'code'        => 'F41.2',
                'title'       => 'Смешанное тревожное и депрессивное расстройство',
                'description' => 'Сочетание депрессивных и тревожных симптомов, которые не достигают уровня, позволяющего поставить отдельный диагноз тревожного или депрессивного расстройства.',
                'relatives'   => ['F32.0', 'F41.1'],
                'exceptions'  => ['F41.0', 'F31.3'],
                'symptoms'    => [
                    'required' => [
                        SymptomsEnum::DepressedMood,
                        SymptomsEnum::ChronicAnxiety,
                    ],
                    'relative' => [
                        SymptomsEnum::PoorConcentration,
                        SymptomsEnum::Fatigue,
                        SymptomsEnum::SleepDisturbance,
                        SymptomsEnum::Irritability,
                    ],
                ],
                'criteria'    => [1, 2],
            ],
            [
                'code'        => 'F41.8',
                'title'       => 'Другие уточненные тревожные расстройства',
                'description' => 'Прочие тревожные расстройства, не подходящие под конкретные категории.',
                'relatives'   => ['F41.0', 'F40.1'],
                'exceptions'  => ['F41.1', 'F41.2'],
                'symptoms'    => [
                    'required' => [
                        SymptomsEnum::ChronicAnxiety,
                        SymptomsEnum::Restlessness,
                    ],
                    'relative' => [
                        SymptomsEnum::Sweating,
                        SymptomsEnum::Tachycardia,
                        SymptomsEnum::Dizziness,
                    ],
                ],
                'criteria'    => [1, 1],
            ],
            [
                'code'        => 'F41.9',
                'title'       => 'Неуточненное тревожное расстройство',
                'description' => 'Неопределенное тревожное расстройство, включающее элементы тревоги без конкретного диагноза.',
                'relatives'   => ['F41.1', 'F40.9'],
                'exceptions'  => ['F41.0', 'F32.0'],
                'symptoms'    => [
                    'required' => [
                        SymptomsEnum::ChronicAnxiety,
                    ],
                    'relative' => [
                        SymptomsEnum::Restlessness,
                        SymptomsEnum::Sweating,
                        SymptomsEnum::Irritability,
                    ],
                ],
                'criteria'    => [1, 1],
            ],
            // F78
            [
                'code'        => 'F78.0',
                'title'       => 'Умственная отсталость, обусловленная физическим заболеванием',
                'description' => 'Интеллектуальная недостаточность, связанная с физическими заболеваниями или состояниями, влияющими на умственное развитие.',
                'relatives'   => ['F70', 'F79'],
                'exceptions'  => ['F71', 'F72'],
                'symptoms'    => [
                    'required' => [
                        SymptomsEnum::CognitiveImpairment,
                        SymptomsEnum::DevelopmentalDelay,
                    ],
                    'relative' => [
                        SymptomsEnum::PoorConcentration,
                        SymptomsEnum::MemoryProblems,
                    ],
                ],
                'criteria'    => [1, 1],
            ],
            [
                'code'        => 'F78.1',
                'title'       => 'Умственная отсталость, обусловленная генетическими аномалиями',
                'description' => 'Интеллектуальная недостаточность, связанная с генетическими нарушениями, включая хромосомные аномалии.',
                'relatives'   => ['F70', 'F72'],
                'exceptions'  => ['F73', 'F79'],
                'symptoms'    => [
                    'required' => [
                        SymptomsEnum::CognitiveImpairment,
                        SymptomsEnum::DevelopmentalDelay,
                    ],
                    'relative' => [
                        SymptomsEnum::LearningDifficulty,
                        SymptomsEnum::PoorJudgement,
                    ],
                ],
                'criteria'    => [2, 1],
            ],
            [
                'code'        => 'F78.2',
                'title'       => 'Умственная отсталость, связанная с воздействием токсинов',
                'description' => 'Нарушение умственного развития, вызванное воздействием токсических веществ, влияющих на когнитивные функции.',
                'relatives'   => ['F70', 'F78.0'],
                'exceptions'  => ['F71', 'F72'],
                'symptoms'    => [
                    'required' => [
                        SymptomsEnum::CognitiveImpairment,
                    ],
                    'relative' => [
                        SymptomsEnum::SocialWithdrawal,
                        SymptomsEnum::PoorJudgement,
                        SymptomsEnum::Fatigue,
                    ],
                ],
                'criteria'    => [1, 1],
            ],
            [
                'code'        => 'F78.3',
                'title'       => 'Умственная отсталость, обусловленная недостаточным питанием',
                'description' => 'Когнитивная недостаточность, развивающаяся на фоне хронического недоедания в критические периоды роста.',
                'relatives'   => ['F70', 'F79'],
                'exceptions'  => ['F71', 'F78.2'],
                'symptoms'    => [
                    'required' => [
                        SymptomsEnum::CognitiveImpairment,
                        SymptomsEnum::DevelopmentalDelay,
                    ],
                    'relative' => [
                        SymptomsEnum::LearningDifficulty,
                        SymptomsEnum::BodyAches,
                    ],
                ],
                'criteria'    => [2, 1],
            ],
            [
                'code'        => 'F78.8',
                'title'       => 'Другое уточненное расстройство умственного развития',
                'description' => 'Различные формы интеллектуальной недостаточности, не подходящие под специфические категории.',
                'relatives'   => ['F79', 'F70'],
                'exceptions'  => ['F72', 'F73'],
                'symptoms'    => [
                    'required' => [
                        SymptomsEnum::CognitiveImpairment,
                        SymptomsEnum::LearningDifficulty,
                    ],
                    'relative' => [
                        SymptomsEnum::PoorJudgement,
                        SymptomsEnum::SocialWithdrawal,
                    ],
                ],
                'criteria'    => [1, 1],
            ],
            [
                'code'        => 'F78.9',
                'title'       => 'Неуточненное расстройство умственного развития',
                'description' => 'Неопределенное расстройство умственного развития, включающее недостаточность интеллекта без специфической этиологии.',
                'relatives'   => ['F79', 'F70'],
                'exceptions'  => ['F71', 'F72'],
                'symptoms'    => [
                    'required' => [
                        SymptomsEnum::CognitiveImpairment,
                    ],
                    'relative' => [
                        SymptomsEnum::LearningDifficulty,
                        SymptomsEnum::PoorConcentration,
                    ],
                ],
                'criteria'    => [1, 1],
            ],
            // F98
            [
                'code'        => 'F98.0',
                'title'       => 'Неорганическое энурез',
                'description' => 'Энурез, не связанный с органическими причинами, часто вызванный психологическими факторами.',
                'relatives'   => ['F91', 'F95'],
                'exceptions'  => ['R32', 'N39.4'],
                'symptoms'    => [
                    'required' => [
                        SymptomsEnum::Bedwetting,
                    ],
                    'relative' => [
                        SymptomsEnum::Anxiety,
                        SymptomsEnum::SocialWithdrawal,
                    ],
                ],
                'criteria'    => [1, 1],
            ],
            [
                'code'        => 'F98.1',
                'title'       => 'Неорганический энкопрез',
                'description' => 'Энкопрез без органической патологии, обычно связанный с психологическим стрессом.',
                'relatives'   => ['F91', 'F94'],
                'exceptions'  => ['R15', 'K59.2'],
                'symptoms'    => [
                    'required' => [
                        SymptomsEnum::FecalIncontinence,
                    ],
                    'relative' => [
                        SymptomsEnum::Anxiety,
                        SymptomsEnum::LowSelfEsteem,
                    ],
                ],
                'criteria'    => [1, 1],
            ],
            [
                'code'        => 'F98.2',
                'title'       => 'Психогенное расстройство приема пищи в детском возрасте',
                'description' => 'Расстройства приема пищи в детстве, вызванные психологическими факторами.',
                'relatives'   => ['F50', 'F94'],
                'exceptions'  => ['R63', 'K21.0'],
                'symptoms'    => [
                    'required' => [
                        SymptomsEnum::FoodAvoidance,
                    ],
                    'relative' => [
                        SymptomsEnum::WeightLoss,
                        SymptomsEnum::Irritability,
                    ],
                ],
                'criteria'    => [1, 1],
            ],
            [
                'code'        => 'F98.3',
                'title'       => 'Пика (поедание несъедобных веществ)',
                'description' => 'Потребление несъедобных веществ, не связанное с культурной практикой.',
                'relatives'   => ['F50', 'F94'],
                'exceptions'  => ['R63.3'],
                'symptoms'    => [
                    'required' => [
                        SymptomsEnum::EatingNonfoodSubstances,
                    ],
                    'relative' => [
                        SymptomsEnum::IronDeficiency,
                        SymptomsEnum::AbdominalPain,
                    ],
                ],
                'criteria'    => [1, 1],
            ],
            [
                'code'        => 'F98.4',
                'title'       => 'Стереотипные двигательные расстройства',
                'description' => 'Повторяющиеся движения без цели, наблюдаемые чаще у детей.',
                'relatives'   => ['F95', 'F91'],
                'exceptions'  => ['R25.2', 'R42'],
                'symptoms'    => [
                    'required' => [
                        SymptomsEnum::RepetitiveMovements,
                    ],
                    'relative' => [
                        SymptomsEnum::Anxiety,
                        SymptomsEnum::SocialWithdrawal,
                    ],
                ],
                'criteria'    => [1, 1],
            ],
            [
                'code'        => 'F98.5',
                'title'       => 'Заикание',
                'description' => 'Нарушение речи, проявляющееся частыми паузами и повторениями звуков.',
                'relatives'   => ['F95', 'F94'],
                'exceptions'  => ['R47.8', 'R49.2'],
                'symptoms'    => [
                    'required' => [
                        SymptomsEnum::SpeechInterruptions,
                    ],
                    'relative' => [
                        SymptomsEnum::Anxiety,
                        SymptomsEnum::SocialWithdrawal,
                    ],
                ],
                'criteria'    => [1, 1],
            ],
            [
                'code'        => 'F98.6',
                'title'       => 'Нарушение темпа речи',
                'description' => 'Изменения в нормальном темпе речи, такие как слишком быстрое или медленное говорение.',
                'relatives'   => ['F95', 'F94'],
                'exceptions'  => ['R47.0', 'R47.8'],
                'symptoms'    => [
                    'required' => [
                        SymptomsEnum::AlteredSpeechPace,
                    ],
                    'relative' => [
                        SymptomsEnum::SocialWithdrawal,
                        SymptomsEnum::Anxiety,
                    ],
                ],
                'criteria'    => [1, 1],
            ],
            [
                'code'        => 'F98.8',
                'title'       => 'Другие уточненные поведенческие и эмоциональные расстройства детского возраста',
                'description' => 'Различные формы поведенческих нарушений, начинающиеся в детстве, без уточнения.',
                'relatives'   => ['F95', 'F91'],
                'exceptions'  => ['F90', 'F94'],
                'symptoms'    => [
                    'required' => [
                        SymptomsEnum::BehavioralIssues,
                    ],
                    'relative' => [
                        SymptomsEnum::Anxiety,
                        SymptomsEnum::SocialWithdrawal,
                    ],
                ],
                'criteria'    => [1, 1],
            ],
            [
                'code'        => 'F98.9',
                'title'       => 'Неуточненное поведенческое и эмоциональное расстройство детского возраста',
                'description' => 'Нарушения поведения в детстве без конкретного диагноза.',
                'relatives'   => ['F95', 'F94'],
                'exceptions'  => ['F90', 'F91'],
                'symptoms'    => [
                    'required' => [
                        SymptomsEnum::BehavioralIssues,
                    ],
                    'relative' => [
                        SymptomsEnum::SocialWithdrawal,
                        SymptomsEnum::Irritability,
                    ],
                ],
                'criteria'    => [1, 1],
            ],
            // F30

            [
                'code'        => 'F30.0',
                'title'       => 'Гипомания',
                'description' => 'Состояние гипомании с легкими симптомами, такими как повышенное настроение и активность.',
                'relatives'   => ['F31.0', 'F34.0'],
                'exceptions'  => ['F32.1', 'F33.0'],
                'symptoms'    => [
                    'required' => [
                        SymptomsEnum::ElevatedMood,
                        SymptomsEnum::IncreasedSelfEsteem,
                        SymptomsEnum::DecreasedNeedForSleep,
                    ],
                    'relative' => [
                        SymptomsEnum::Talkativeness,
                        SymptomsEnum::Hyperactivity,
                    ],
                ],
                'criteria'    => [2, 1],
            ],
            [
                'code'        => 'F30.1',
                'title'       => 'Мания без психотических симптомов',
                'description' => 'Маниакальный эпизод с выраженными симптомами, но без психотических признаков.',
                'relatives'   => ['F31.1', 'F34.0'],
                'exceptions'  => ['F32.2', 'F33.1'],
                'symptoms'    => [
                    'required' => [
                        SymptomsEnum::ElevatedMood,
                        SymptomsEnum::IncreasedSelfEsteem,
                        SymptomsEnum::DecreasedNeedForSleep,
                        SymptomsEnum::Impulsivity,
                    ],
                    'relative' => [
                        SymptomsEnum::Talkativeness,
                        SymptomsEnum::Hyperactivity,
                        SymptomsEnum::PoorJudgement,
                    ],
                ],
                'criteria'    => [3, 1],
            ],
            [
                'code'        => 'F30.2',
                'title'       => 'Мания с психотическими симптомами',
                'description' => 'Маниакальный эпизод, сопровождающийся психотическими проявлениями, такими как бред или галлюцинации.',
                'relatives'   => ['F31.2', 'F20.3'],
                'exceptions'  => ['F32.3', 'F33.2'],
                'symptoms'    => [
                    'required' => [
                        SymptomsEnum::ElevatedMood,
                        SymptomsEnum::IncreasedSelfEsteem,
                        SymptomsEnum::Hallucinations,
                        SymptomsEnum::Delusions,
                    ],
                    'relative' => [
                        SymptomsEnum::Impulsivity,
                        SymptomsEnum::Hyperactivity,
                        SymptomsEnum::PsychoticFeatures,
                    ],
                ],
                'criteria'    => [3, 1],
            ],
            [
                'code'        => 'F30.9',
                'title'       => 'Неуточненный маниакальный эпизод',
                'description' => 'Состояние мании, не имеющее точного диагноза, включающее симптомы повышенного настроения и активности.',
                'relatives'   => ['F31.9', 'F34.0'],
                'exceptions'  => ['F32.0', 'F33.0'],
                'symptoms'    => [
                    'required' => [
                        SymptomsEnum::ElevatedMood,
                        SymptomsEnum::IncreasedSelfEsteem,
                    ],
                    'relative' => [
                        SymptomsEnum::Impulsivity,
                        SymptomsEnum::Talkativeness,
                    ],
                ],
                'criteria'    => [2, 1],
            ],
            // F80
            [
                'code'        => 'F80.0',
                'title'       => 'Специфическое расстройство артикуляции речи',
                'description' => 'Затруднения в артикуляции звуков речи без повреждений слуха или других неврологических проблем.',
                'relatives'   => ['F80.2', 'F84.0'],
                'exceptions'  => ['R47.0', 'F94.1'],
                'symptoms'    => [
                    'required' => [
                        SymptomsEnum::DifficultyPronouncingWords,
                    ],
                    'relative' => [
                        SymptomsEnum::DelayedSpeech,
                        SymptomsEnum::SocialWithdrawal,
                    ],
                ],
                'criteria'    => [1, 1],
            ],
            [
                'code'        => 'F80.1',
                'title'       => 'Выраженное расстройство экспрессивной речи',
                'description' => 'Значительные трудности в использовании слов для выражения мыслей.',
                'relatives'   => ['F80.2', 'F84.0'],
                'exceptions'  => ['F94.1', 'R47.0'],
                'symptoms'    => [
                    'required' => [
                        SymptomsEnum::DifficultyExpressingThoughts,
                        SymptomsEnum::PoorVocabulary,
                    ],
                    'relative' => [
                        SymptomsEnum::DelayedSpeech,
                        SymptomsEnum::SocialWithdrawal,
                    ],
                ],
                'criteria'    => [2, 1],
            ],
            [
                'code'        => 'F80.2',
                'title'       => 'Расстройство рецептивной речи',
                'description' => 'Трудности с пониманием речи других людей.',
                'relatives'   => ['F80.1', 'F84.0'],
                'exceptions'  => ['R47.0', 'F94.0'],
                'symptoms'    => [
                    'required' => [
                        SymptomsEnum::DifficultyUnderstandingSpeech,
                    ],
                    'relative' => [
                        SymptomsEnum::PoorVocabulary,
                        SymptomsEnum::DelayedSpeech,
                    ],
                ],
                'criteria'    => [1, 1],
            ],
            [
                'code'        => 'F80.3',
                'title'       => 'Приобретенное расстройство речи, связанное с эпилепсией',
                'description' => 'Регресс речевых навыков, вызванный эпилептическими приступами.',
                'relatives'   => ['G40', 'F80.2'],
                'exceptions'  => ['F81', 'R47.0'],
                'symptoms'    => [
                    'required' => [
                        SymptomsEnum::LossOfSpeechSkills,
                        SymptomsEnum::Seizures,
                    ],
                    'relative' => [
                        SymptomsEnum::SocialWithdrawal,
                        SymptomsEnum::DifficultyUnderstandingSpeech,
                    ],
                ],
                'criteria'    => [2, 1],
            ],
            // F81
            [
                'code'        => 'F81.0',
                'title'       => 'Специфическое расстройство чтения',
                'description' => 'Затруднения в навыках чтения при нормальном интеллекте и образовании.',
                'relatives'   => ['F81.2', 'F84.0'],
                'exceptions'  => ['R48.0', 'F70'],
                'symptoms'    => [
                    'required' => [
                        SymptomsEnum::DifficultyReading,
                    ],
                    'relative' => [
                        SymptomsEnum::PoorConcentration,
                        SymptomsEnum::SlowReadingSpeed,
                    ],
                ],
                'criteria'    => [1, 1],
            ],
            [
                'code'        => 'F81.1',
                'title'       => 'Специфическое расстройство правописания',
                'description' => 'Нарушения правописания, не связанные с плохим обучением или низким интеллектом.',
                'relatives'   => ['F81.2', 'F80.0'],
                'exceptions'  => ['R48.0', 'F70'],
                'symptoms'    => [
                    'required' => [
                        SymptomsEnum::DifficultySpelling,
                    ],
                    'relative' => [
                        SymptomsEnum::PoorConcentration,
                        SymptomsEnum::SlowWritingSpeed,
                    ],
                ],
                'criteria'    => [1, 1],
            ],
            [
                'code'        => 'F81.2',
                'title'       => 'Специфическое расстройство арифметических навыков',
                'description' => 'Нарушение математических способностей, не связанное с интеллектуальным дефицитом.',
                'relatives'   => ['F81.1', 'F80.0'],
                'exceptions'  => ['R48.8', 'F70'],
                'symptoms'    => [
                    'required' => [
                        SymptomsEnum::DifficultyWithMath,
                    ],
                    'relative' => [
                        SymptomsEnum::PoorConcentration,
                        SymptomsEnum::SlowCalculations,
                    ],
                ],
                'criteria'    => [1, 1],
            ],
            // F82
            [
                'code'        => 'F82',
                'title'       => 'Специфическое расстройство развития двигательных функций',
                'description' => 'Сложности с координацией движений, не обусловленные физическими нарушениями.',
                'relatives'   => ['F84', 'F80.1'],
                'exceptions'  => ['R27.8', 'F70'],
                'symptoms'    => [
                    'required' => [
                        SymptomsEnum::PoorCoordination,
                    ],
                    'relative' => [
                        SymptomsEnum::PoorBalance,
                        SymptomsEnum::Clumsiness,
                    ],
                ],
                'criteria'    => [1, 1],
            ],
            // F83
            [
                'code'        => 'F83',
                'title'       => 'Смешанные специфические расстройства психологического развития',
                'description' => 'Наличие нескольких специфических расстройств развития без доминирующего нарушения.',
                'relatives'   => ['F80.0', 'F81.2'],
                'exceptions'  => ['F70', 'F71'],
                'symptoms'    => [
                    'required' => [
                        SymptomsEnum::DifficultyReading,
                        SymptomsEnum::DifficultySpelling,
                        SymptomsEnum::PoorCoordination,
                    ],
                    'relative' => [
                        SymptomsEnum::PoorConcentration,
                    ],
                ],
                'criteria'    => [3, 1],
            ],
            // F84
            [
                'code'        => 'F84.0',
                'title'       => 'Детский аутизм',
                'description' => 'Расстройство развития, характеризующееся нарушениями в социальных взаимодействиях, коммуникативных способностях и стереотипными моделями поведения, начиная с раннего детства.',
                'relatives'   => ['F84.1', 'F84.5'],
                'exceptions'  => ['F84.2', 'F84.3'],
                'symptoms'    => [
                    'required' => [
                        SymptomsEnum::SocialInteractionDeficits,
                        SymptomsEnum::CommunicationDeficits,
                        SymptomsEnum::RestrictedInterests,
                    ],
                    'relative' => [
                        SymptomsEnum::RepetitiveMovements,
                        SymptomsEnum::SensorySensitivities,
                        SymptomsEnum::DelayedLanguage,
                    ],
                ],
                'criteria'    => [3, 1],
            ],
            [
                'code'        => 'F84.1',
                'title'       => 'Атипичный аутизм',
                'description' => 'Аутизм, проявляющийся в более позднем возрасте или не полностью соответствующий критериям детского аутизма.',
                'relatives'   => ['F84.0', 'F84.5'],
                'exceptions'  => ['F84.2'],
                'symptoms'    => [
                    'required' => [
                        SymptomsEnum::SocialInteractionDeficits,
                        SymptomsEnum::RestrictedInterests,
                    ],
                    'relative' => [
                        SymptomsEnum::CommunicationDeficits,
                        SymptomsEnum::RepetitiveMovements,
                        SymptomsEnum::DelayedLanguage,
                    ],
                ],
                'criteria'    => [2, 1],
            ],
            [
                'code'        => 'F84.2',
                'title'       => 'Синдром Ретта',
                'description' => 'Редкое генетическое расстройство, сопровождающееся тяжелыми нарушениями развития, особенно у девочек.',
                'relatives'   => ['F84.4'],
                'exceptions'  => ['F84.0', 'F84.1'],
                'symptoms'    => [
                    'required' => [
                        SymptomsEnum::LossOfSkills,
                        SymptomsEnum::MotorDysfunction,
                        SymptomsEnum::BreathingIrregularities,
                    ],
                    'relative' => [
                        SymptomsEnum::SocialInteractionDeficits,
                        SymptomsEnum::RepetitiveMovements,
                    ],
                ],
                'criteria'    => [3, 1],
            ],
            [
                'code'        => 'F84.3',
                'title'       => 'Дезинтегративное расстройство детства',
                'description' => 'Тяжелое регрессивное расстройство, при котором ребенок теряет ранее приобретенные навыки после нормального периода развития.',
                'relatives'   => ['F84.1'],
                'exceptions'  => ['F84.0'],
                'symptoms'    => [
                    'required' => [
                        SymptomsEnum::LossOfSkills,
                        SymptomsEnum::SocialInteractionDeficits,
                        SymptomsEnum::LanguageRegression,
                    ],
                    'relative' => [
                        SymptomsEnum::Anxiety,
                        SymptomsEnum::RepetitiveMovements,
                    ],
                ],
                'criteria'    => [3, 1],
            ],
            [
                'code'        => 'F84.4',
                'title'       => 'Гиперактивное расстройство, связанное с умственной отсталостью и стереотипными движениями',
                'description' => 'Состояние, характеризующееся гиперактивностью, умственной отсталостью и стереотипными движениями.',
                'relatives'   => ['F84.2'],
                'exceptions'  => [],
                'symptoms'    => [
                    'required' => [
                        SymptomsEnum::Hyperactivity,
                        SymptomsEnum::StereotypedMovements,
                        SymptomsEnum::IntellectualDisability,
                    ],
                    'relative' => [
                        SymptomsEnum::AttentionDeficits,
                        SymptomsEnum::Impulsivity,
                    ],
                ],
                'criteria'    => [3, 1],
            ],
            [
                'code'        => 'F84.5',
                'title'       => 'Синдром Аспергера',
                'description' => 'Расстройство аутистического спектра, характеризующееся трудностями в социальных взаимодействиях и ограниченными интересами, но без значительного отставания в речи.',
                'relatives'   => ['F84.0', 'F84.1'],
                'exceptions'  => ['F84.2'],
                'symptoms'    => [
                    'required' => [
                        SymptomsEnum::SocialInteractionDeficits,
                        SymptomsEnum::RestrictedInterests,
                    ],
                    'relative' => [
                        SymptomsEnum::MotorCoordinationDifficulties,
                        SymptomsEnum::CommunicationDeficits,
                    ],
                ],
                'criteria'    => [2, 1],
            ],
            [
                'code'        => 'F84.8',
                'title'       => 'Другие первазивные расстройства развития',
                'description' => 'Атипичные или нечетко определенные формы нарушений развития, включающие элементы аутизма, но не соответствующие полным критериям.',
                'relatives'   => ['F84.1', 'F84.5'],
                'exceptions'  => [],
                'symptoms'    => [
                    'required' => [
                        SymptomsEnum::SocialInteractionDeficits,
                    ],
                    'relative' => [
                        SymptomsEnum::CommunicationDeficits,
                        SymptomsEnum::RepetitiveMovements,
                    ],
                ],
                'criteria'    => [1, 1],
            ],
            [
                'code'        => 'F84.9',
                'title'       => 'Первазивное расстройство развития неуточненное',
                'description' => 'Неопределенное расстройство развития, включающее элементы аутистического спектра, но не полностью соответствующее критериям других категорий.',
                'relatives'   => ['F84.0', 'F84.1'],
                'exceptions'  => [],
                'symptoms'    => [
                    'required' => [
                        SymptomsEnum::SocialInteractionDeficits,
                    ],
                    'relative' => [
                        SymptomsEnum::CommunicationDeficits,
                        SymptomsEnum::RestrictedInterests,
                    ],
                ],
                'criteria'    => [1, 1],
            ],
            // F88
            [
                'code'        => 'F88',
                'title'       => 'Другие расстройства психологического развития',
                'description' => 'Включает разнообразные психологические расстройства, не подходящие под другие категории.',
                'relatives'   => ['F84.9', 'F89'],
                'exceptions'  => ['F70', 'F71'],
                'symptoms'    => [
                    'required' => [
                        SymptomsEnum::ImpairedCognitiveDevelopment,
                    ],
                    'relative' => [
                        SymptomsEnum::PoorConcentration,
                    ],
                ],
                'criteria'    => [1, 1],
            ],
            // F89
            [
                'code'        => 'F89',
                'title'       => 'Неуточненное расстройство психологического развития',
                'description' => 'Неопределенные нарушения развития, включающие разнообразные когнитивные и поведенческие симптомы.',
                'relatives'   => ['F80.9', 'F84.9'],
                'exceptions'  => ['F70', 'F71'],
                'symptoms'    => [
                    'required' => [
                        SymptomsEnum::ImpairedCognitiveDevelopment,
                    ],
                    'relative' => [
                        SymptomsEnum::SocialWithdrawal,
                    ],
                ],
                'criteria'    => [1, 1],
            ],
            // F99
            [
                'code'        => 'F99',
                'title'       => 'Неуточненное психическое расстройство',
                'description' => 'Психическое расстройство, которое не соответствует конкретным диагностическим критериям.',
                'relatives'   => ['F48.9', 'F29'],
                'exceptions'  => [],
                'symptoms'    => [
                    'required' => [
                        SymptomsEnum::Anxiety,
                        SymptomsEnum::SocialWithdrawal,
                    ],
                    'relative' => [
                        SymptomsEnum::PoorConcentration,
                        SymptomsEnum::MoodSwings,
                    ],
                ],
                'criteria'    => [1, 1],
            ],
            // F42
            [
                'code'        => 'F42.0',
                'title'       => 'Обсессивные мысли и размышления',
                'description' => 'Преобладают навязчивые мысли и идеи, вызывающие беспокойство.',
                'relatives'   => ['F40.0', 'F44.0'],
                'exceptions'  => ['F20.0', 'F30.1'],
                'symptoms'    => [
                    'required' => [
                        SymptomsEnum::ObsessiveThoughts,
                        SymptomsEnum::Anxiety,
                    ],
                    'relative' => [
                        SymptomsEnum::PoorConcentration,
                        SymptomsEnum::Insomnia,
                    ],
                ],
                'criteria'    => [2, 1],
            ],
            [
                'code'        => 'F42.1',
                'title'       => 'Компульсивные действия',
                'description' => 'Чрезмерные повторяющиеся действия, направленные на снижение тревожности.',
                'relatives'   => ['F42.0', 'F44.0'],
                'exceptions'  => ['F20.0', 'F30.1'],
                'symptoms'    => [
                    'required' => [
                        SymptomsEnum::CompulsiveActions,
                        SymptomsEnum::Anxiety,
                    ],
                    'relative' => [
                        SymptomsEnum::ObsessiveThoughts,
                        SymptomsEnum::Insomnia,
                    ],
                ],
                'criteria'    => [2, 1],
            ],
            [
                'code'        => 'F42.2',
                'title'       => 'Смешанные обсессивные мысли и действия',
                'description' => 'Одновременное наличие обсессивных мыслей и компульсивных действий.',
                'relatives'   => ['F42.0', 'F42.1'],
                'exceptions'  => ['F20.0', 'F30.1'],
                'symptoms'    => [
                    'required' => [
                        SymptomsEnum::ObsessiveThoughts,
                        SymptomsEnum::CompulsiveActions,
                    ],
                    'relative' => [
                        SymptomsEnum::Anxiety,
                        SymptomsEnum::PoorConcentration,
                    ],
                ],
                'criteria'    => [2, 1],
            ],
            [
                'code'        => 'F42.8',
                'title'       => 'Другое обсессивно-компульсивное расстройство',
                'description' => 'Нестандартные проявления обсессивно-компульсивного расстройства, не подходящие под другие категории.',
                'relatives'   => ['F42.2', 'F44.8'],
                'exceptions'  => ['F20.0', 'F30.1'],
                'symptoms'    => [
                    'required' => [
                        SymptomsEnum::ObsessiveThoughts,
                    ],
                    'relative' => [
                        SymptomsEnum::CompulsiveActions,
                        SymptomsEnum::Anxiety,
                    ],
                ],
                'criteria'    => [1, 1],
            ],
            [
                'code'        => 'F42.9',
                'title'       => 'Обсессивно-компульсивное расстройство неуточненное',
                'description' => 'Общие симптомы обсессивно-компульсивного расстройства, не классифицируемые конкретно.',
                'relatives'   => ['F42.8', 'F44.9'],
                'exceptions'  => ['F20.0', 'F30.1'],
                'symptoms'    => [
                    'required' => [
                        SymptomsEnum::ObsessiveThoughts,
                    ],
                    'relative' => [
                        SymptomsEnum::CompulsiveActions,
                        SymptomsEnum::Anxiety,
                    ],
                ],
                'criteria'    => [1, 1],
            ],
            // F01
            [
                'code'        => 'F01.0',
                'title'       => 'Сосудистая деменция с острым началом',
                'description' => 'Состояние, развивающееся после одного или нескольких острых инсультов, приводящее к когнитивным нарушениям.',
                'relatives'   => ['F01.1', 'F01.2'],
                'exceptions'  => ['F00.0', 'F02.3'],
                'symptoms'    => [
                    'required' => [
                        SymptomsEnum::MemoryProblems,
                        SymptomsEnum::PoorJudgement,
                        SymptomsEnum::Disorientation,
                    ],
                    'relative' => [
                        SymptomsEnum::Aphasia,
                        SymptomsEnum::MotorDeficits,
                    ],
                ],
                'criteria'    => [3, 1],
            ],
            [
                'code'        => 'F01.1',
                'title'       => 'Мультиинфарктная деменция',
                'description' => 'Деменция, развивающаяся вследствие множественных инфарктов мозга, приводящих к хроническим когнитивным нарушениям.',
                'relatives'   => ['F01.0', 'F01.2'],
                'exceptions'  => ['F00.1', 'F02.4'],
                'symptoms'    => [
                    'required' => [
                        SymptomsEnum::MemoryProblems,
                        SymptomsEnum::PoorJudgement,
                        SymptomsEnum::Aphasia,
                    ],
                    'relative' => [
                        SymptomsEnum::Disorientation,
                        SymptomsEnum::Anxiety,
                        SymptomsEnum::DepressedMood,
                    ],
                ],
                'criteria'    => [3, 1],
            ],
            [
                'code'        => 'F01.2',
                'title'       => 'Субкортикальная сосудистая деменция',
                'description' => 'Деменция, вызванная поражением субкортикальных структур мозга, характеризующаяся медленным ухудшением когнитивных функций.',
                'relatives'   => ['F01.0', 'F01.1'],
                'exceptions'  => ['F00.2', 'F02.6'],
                'symptoms'    => [
                    'required' => [
                        SymptomsEnum::MemoryProblems,
                        SymptomsEnum::PoorJudgement,
                        SymptomsEnum::MotorDeficits,
                    ],
                    'relative' => [
                        SymptomsEnum::Aphasia,
                        SymptomsEnum::DepressedMood,
                    ],
                ],
                'criteria'    => [3, 1],
            ],
            [
                'code'        => 'F01.3',
                'title'       => 'Сосудистая деменция смешанного типа',
                'description' => 'Смешанная форма сосудистой деменции с признаками как коркового, так и субкортикального поражения.',
                'relatives'   => ['F01.0', 'F01.2'],
                'exceptions'  => ['F00.3', 'F02.8'],
                'symptoms'    => [
                    'required' => [
                        SymptomsEnum::MemoryProblems,
                        SymptomsEnum::PoorJudgement,
                        SymptomsEnum::Disorientation,
                    ],
                    'relative' => [
                        SymptomsEnum::PersonalityChanges,
                        SymptomsEnum::Anxiety,
                        SymptomsEnum::Aphasia,
                    ],
                ],
                'criteria'    => [3, 1],
            ],
            [
                'code'        => 'F01.9',
                'title'       => 'Сосудистая деменция неуточненная',
                'description' => 'Неуточненные формы сосудистой деменции, включающие общие когнитивные нарушения без четкого характера поражения.',
                'relatives'   => ['F01.0', 'F01.1'],
                'exceptions'  => ['F00.4', 'F02.9'],
                'symptoms'    => [
                    'required' => [
                        SymptomsEnum::MemoryProblems,
                        SymptomsEnum::PoorJudgement,
                    ],
                    'relative' => [
                        SymptomsEnum::Disorientation,
                        SymptomsEnum::Anxiety,
                    ],
                ],
                'criteria'    => [2, 1],
            ],
            // F02
            [
                'code'        => 'F02.0',
                'title'       => 'Деменция при болезни Пика',
                'description' => 'Прогрессирующее заболевание, характеризующееся дегенерацией лобных и височных долей мозга, с выраженными изменениями поведения.',
                'relatives'   => ['F02.1', 'F02.2'],
                'exceptions'  => ['F00.1', 'F01.3'],
                'symptoms'    => [
                    'required' => [
                        SymptomsEnum::PersonalityChanges,
                        SymptomsEnum::MemoryProblems,
                        SymptomsEnum::PoorJudgement,
                    ],
                    'relative' => [
                        SymptomsEnum::Disorientation,
                        SymptomsEnum::MoodInstability,
                    ],
                ],
                'criteria'    => [3, 1],
            ],
            [
                'code'        => 'F02.1',
                'title'       => 'Деменция при болезни Хантингтона',
                'description' => 'Деменция, ассоциированная с прогрессирующей дегенерацией нервных клеток при болезни Хантингтона.',
                'relatives'   => ['F02.0', 'F02.2'],
                'exceptions'  => ['F00.2', 'F01.0'],
                'symptoms'    => [
                    'required' => [
                        SymptomsEnum::MotorDeficits,
                        SymptomsEnum::MemoryProblems,
                        SymptomsEnum::PoorJudgement,
                    ],
                    'relative' => [
                        SymptomsEnum::PersonalityChanges,
                        SymptomsEnum::DepressedMood,
                    ],
                ],
                'criteria'    => [3, 1],
            ],
            [
                'code'        => 'F02.2',
                'title'       => 'Деменция при болезни Паркинсона',
                'description' => 'Деменция, развивающаяся на фоне болезни Паркинсона, с выраженными когнитивными нарушениями.',
                'relatives'   => ['F02.1', 'F02.3'],
                'exceptions'  => ['F00.3', 'F01.1'],
                'symptoms'    => [
                    'required' => [
                        SymptomsEnum::MotorDeficits,
                        SymptomsEnum::MemoryProblems,
                        SymptomsEnum::PoorJudgement,
                    ],
                    'relative' => [
                        SymptomsEnum::Disorientation,
                        SymptomsEnum::DepressedMood,
                    ],
                ],
                'criteria'    => [3, 1],
            ],
            [
                'code'        => 'F02.3',
                'title'       => 'Деменция при других болезнях, классифицированных в других рубриках',
                'description' => 'Когнитивные нарушения, возникающие как следствие других первичных заболеваний, таких как энцефалопатии.',
                'relatives'   => ['F02.0', 'F02.2'],
                'exceptions'  => ['F00.4', 'F01.2'],
                'symptoms'    => [
                    'required' => [
                        SymptomsEnum::MemoryProblems,
                        SymptomsEnum::Disorientation,
                    ],
                    'relative' => [
                        SymptomsEnum::Anxiety,
                        SymptomsEnum::PoorJudgement,
                    ],
                ],
                'criteria'    => [2, 1],
            ],
            [
                'code'        => 'F02.8',
                'title'       => 'Деменция при других уточненных болезнях',
                'description' => 'Деменция, ассоциированная с редкими или менее распространенными нейродегенеративными расстройствами.',
                'relatives'   => ['F02.0', 'F02.1'],
                'exceptions'  => ['F00.2', 'F01.3'],
                'symptoms'    => [
                    'required' => [
                        SymptomsEnum::MemoryProblems,
                        SymptomsEnum::PoorJudgement,
                        SymptomsEnum::PersonalityChanges,
                    ],
                    'relative' => [
                        SymptomsEnum::Disorientation,
                        SymptomsEnum::MotorDeficits,
                    ],
                ],
                'criteria'    => [3, 1],
            ],
            [
                'code'        => 'F02.9',
                'title'       => 'Деменция при неуточненных болезнях',
                'description' => 'Неуточненная деменция, вызванная неустановленными нейродегенеративными заболеваниями.',
                'relatives'   => ['F02.0', 'F02.3'],
                'exceptions'  => ['F00.9', 'F01.9'],
                'symptoms'    => [
                    'required' => [
                        SymptomsEnum::MemoryProblems,
                    ],
                    'relative' => [
                        SymptomsEnum::PoorJudgement,
                        SymptomsEnum::Disorientation,
                    ],
                ],
                'criteria'    => [1, 1],
            ],
            // F03
            [
                'code'        => 'F03',
                'title'       => 'Неуточненная деменция',
                'description' => 'Деменция неустановленной природы, с прогрессирующими когнитивными нарушениями без определенной причины.',
                'relatives'   => ['F00', 'F01'],
                'exceptions'  => ['F04', 'F05'],
                'symptoms'    => [
                    'required' => [
                        SymptomsEnum::MemoryProblems,
                        SymptomsEnum::PoorJudgement,
                    ],
                    'relative' => [
                        SymptomsEnum::Disorientation,
                        SymptomsEnum::PersonalityChanges,
                    ],
                ],
                'criteria'    => [2, 1],
            ],
            [
                'code'        => 'F04',
                'title'       => 'Органический амнестический синдром, не вызванный алкоголем или другими психоактивными веществами',
                'description' => 'Амнестический синдром органического характера, проявляющийся серьезными нарушениями памяти и когнитивных функций.',
                'relatives'   => ['F03', 'F05'],
                'exceptions'  => ['F01', 'F00'],
                'symptoms'    => [
                    'required' => [
                        SymptomsEnum::MemoryProblems,
                        SymptomsEnum::Disorientation,
                    ],
                    'relative' => [
                        SymptomsEnum::PoorJudgement,
                        SymptomsEnum::PersonalityChanges,
                    ],
                ],
                'criteria'    => [2, 1],
            ],
            [
                'code'        => 'F05.0',
                'title'       => 'Делирий, не вызванный алкоголем или другими психоактивными веществами',
                'description' => 'Острое состояние спутанности сознания с когнитивными нарушениями, включая нарушения внимания и восприятия.',
                'relatives'   => ['F05.1', 'F05.8'],
                'exceptions'  => ['F10.4', 'F19.4'],
                'symptoms'    => [
                    'required' => [
                        SymptomsEnum::Disorientation,
                        SymptomsEnum::ImpairedAttention,
                        SymptomsEnum::MemoryProblems,
                    ],
                    'relative' => [
                        SymptomsEnum::Hallucinations,
                        SymptomsEnum::Anxiety,
                    ],
                ],
                'criteria'    => [3, 1],
            ],
            [
                'code'        => 'F05.1',
                'title'       => 'Делирий на фоне деменции',
                'description' => 'Острое состояние делирия у пациента с ранее установленной деменцией.',
                'relatives'   => ['F03', 'F05.0'],
                'exceptions'  => ['F10.5', 'F19.5'],
                'symptoms'    => [
                    'required' => [
                        SymptomsEnum::MemoryProblems,
                        SymptomsEnum::Disorientation,
                        SymptomsEnum::PersonalityChanges,
                    ],
                    'relative' => [
                        SymptomsEnum::Hallucinations,
                        SymptomsEnum::Agitation,
                    ],
                ],
                'criteria'    => [3, 1],
            ],
            [
                'code'        => 'F05.8',
                'title'       => 'Другие формы делирия, не вызванные алкоголем или другими психоактивными веществами',
                'description' => 'Другие разновидности делирия, характеризующиеся когнитивными нарушениями, не связанными с воздействием веществ.',
                'relatives'   => ['F05.0', 'F05.1'],
                'exceptions'  => ['F10.6', 'F19.6'],
                'symptoms'    => [
                    'required' => [
                        SymptomsEnum::Disorientation,
                        SymptomsEnum::MemoryProblems,
                        SymptomsEnum::Hallucinations,
                    ],
                    'relative' => [
                        SymptomsEnum::Agitation,
                        SymptomsEnum::PoorJudgement,
                    ],
                ],
                'criteria'    => [3, 1],
            ],
            [
                'code'        => 'F05.9',
                'title'       => 'Неуточненный делирий, не вызванный алкоголем или другими психоактивными веществами',
                'description' => 'Состояние делирия неясной природы, сопровождающееся нарушением когнитивных функций.',
                'relatives'   => ['F05.0', 'F05.8'],
                'exceptions'  => ['F10.7', 'F19.7'],
                'symptoms'    => [
                    'required' => [
                        SymptomsEnum::Disorientation,
                        SymptomsEnum::ImpairedAttention,
                    ],
                    'relative' => [
                        SymptomsEnum::Hallucinations,
                        SymptomsEnum::Agitation,
                    ],
                ],
                'criteria'    => [2, 1],
            ],
            [
                'code'        => 'F03',
                'title'       => 'Неуточненная деменция',
                'description' => 'Деменция неустановленной природы, с прогрессирующими когнитивными нарушениями без определенной причины.',
                'relatives'   => ['F00', 'F01'],
                'exceptions'  => ['F04', 'F05'],
                'symptoms'    => [
                    'required' => [
                        SymptomsEnum::MemoryProblems,
                        SymptomsEnum::PoorJudgement,
                    ],
                    'relative' => [
                        SymptomsEnum::Disorientation,
                        SymptomsEnum::PersonalityChanges,
                    ],
                ],
                'criteria'    => [2, 1],
            ],
            [
                'code'        => 'F04',
                'title'       => 'Органический амнестический синдром, не вызванный алкоголем или другими психоактивными веществами',
                'description' => 'Амнестический синдром органического характера, проявляющийся серьезными нарушениями памяти и когнитивных функций.',
                'relatives'   => ['F03', 'F05'],
                'exceptions'  => ['F01', 'F00'],
                'symptoms'    => [
                    'required' => [
                        SymptomsEnum::MemoryProblems,
                        SymptomsEnum::Disorientation,
                    ],
                    'relative' => [
                        SymptomsEnum::PoorJudgement,
                        SymptomsEnum::PersonalityChanges,
                    ],
                ],
                'criteria'    => [2, 1],
            ],
            [
                'code'        => 'F05.0',
                'title'       => 'Делирий, не вызванный алкоголем или другими психоактивными веществами',
                'description' => 'Острое состояние спутанности сознания с когнитивными нарушениями, включая нарушения внимания и восприятия.',
                'relatives'   => ['F05.1', 'F05.8'],
                'exceptions'  => ['F10.4', 'F19.4'],
                'symptoms'    => [
                    'required' => [
                        SymptomsEnum::Disorientation,
                        SymptomsEnum::ImpairedAttention,
                        SymptomsEnum::MemoryProblems,
                    ],
                    'relative' => [
                        SymptomsEnum::Hallucinations,
                        SymptomsEnum::Anxiety,
                    ],
                ],
                'criteria'    => [3, 1],
            ],
            [
                'code'        => 'F05.1',
                'title'       => 'Делирий на фоне деменции',
                'description' => 'Острое состояние делирия у пациента с ранее установленной деменцией.',
                'relatives'   => ['F03', 'F05.0'],
                'exceptions'  => ['F10.5', 'F19.5'],
                'symptoms'    => [
                    'required' => [
                        SymptomsEnum::MemoryProblems,
                        SymptomsEnum::Disorientation,
                        SymptomsEnum::PersonalityChanges,
                    ],
                    'relative' => [
                        SymptomsEnum::Hallucinations,
                        SymptomsEnum::Agitation,
                    ],
                ],
                'criteria'    => [3, 1],
            ],
            [
                'code'        => 'F05.8',
                'title'       => 'Другие формы делирия, не вызванные алкоголем или другими психоактивными веществами',
                'description' => 'Другие разновидности делирия, характеризующиеся когнитивными нарушениями, не связанными с воздействием веществ.',
                'relatives'   => ['F05.0', 'F05.1'],
                'exceptions'  => ['F10.6', 'F19.6'],
                'symptoms'    => [
                    'required' => [
                        SymptomsEnum::Disorientation,
                        SymptomsEnum::MemoryProblems,
                        SymptomsEnum::Hallucinations,
                    ],
                    'relative' => [
                        SymptomsEnum::Agitation,
                        SymptomsEnum::PoorJudgement,
                    ],
                ],
                'criteria'    => [3, 1],
            ],
            [
                'code'        => 'F05.9',
                'title'       => 'Неуточненный делирий, не вызванный алкоголем или другими психоактивными веществами',
                'description' => 'Состояние делирия неясной природы, сопровождающееся нарушением когнитивных функций.',
                'relatives'   => ['F05.0', 'F05.8'],
                'exceptions'  => ['F10.7', 'F19.7'],
                'symptoms'    => [
                    'required' => [
                        SymptomsEnum::Disorientation,
                        SymptomsEnum::ImpairedAttention,
                    ],
                    'relative' => [
                        SymptomsEnum::Hallucinations,
                        SymptomsEnum::Agitation,
                    ],
                ],
                'criteria'    => [2, 1],
            ],
            // F23: Острые и преходящие психотические расстройства
            [
                'code'        => 'F23.0',
                'title'       => 'Острое полиморфное психотическое расстройство без симптомов шизофрении',
                'description' => 'Психотическое расстройство с быстро меняющимися симптомами, без стойких симптомов шизофрении.',
                'relatives'   => ['F23.1', 'F23.2'],
                'exceptions'  => ['F20.0', 'F25.0'],
                'symptoms'    => [
                    'required' => [
                        SymptomsEnum::Hallucinations,
                        SymptomsEnum::Delusions,
                    ],
                    'relative' => [
                        SymptomsEnum::Anxiety,
                        SymptomsEnum::MoodSwings,
                        SymptomsEnum::PoorConcentration,
                    ],
                ],
                'criteria'    => [2, 1],
            ],
            [
                'code'        => 'F23.1',
                'title'       => 'Острое полиморфное психотическое расстройство с симптомами шизофрении',
                'description' => 'Острое психотическое расстройство с быстро меняющимися симптомами, включающими симптомы шизофрении.',
                'relatives'   => ['F23.0', 'F20.0'],
                'exceptions'  => ['F25.1', 'F20.3'],
                'symptoms'    => [
                    'required' => [
                        SymptomsEnum::Hallucinations,
                        SymptomsEnum::Delusions,
                        SymptomsEnum::DisorganizedThinking,
                    ],
                    'relative' => [
                        SymptomsEnum::Agitation,
                        SymptomsEnum::PoorJudgement,
                    ],
                ],
                'criteria'    => [3, 1],
            ],
            [
                'code'        => 'F23.2',
                'title'       => 'Острое шизофреноподобное психотическое расстройство',
                'description' => 'Психотическое расстройство, напоминающее шизофрению, с острым началом.',
                'relatives'   => ['F23.1', 'F20.1'],
                'exceptions'  => ['F25.0', 'F20.9'],
                'symptoms'    => [
                    'required' => [
                        SymptomsEnum::Hallucinations,
                        SymptomsEnum::Delusions,
                        SymptomsEnum::DisorganizedThinking,
                    ],
                    'relative' => [
                        SymptomsEnum::MoodSwings,
                        SymptomsEnum::Agitation,
                    ],
                ],
                'criteria'    => [3, 1],
            ],
            [
                'code'        => 'F23.3',
                'title'       => 'Другие острые преимущественно бредовые психотические расстройства',
                'description' => 'Психотическое расстройство с преобладанием бредовых идей, начавшееся остро.',
                'relatives'   => ['F23.8', 'F22.0'],
                'exceptions'  => ['F20.0', 'F25.2'],
                'symptoms'    => [
                    'required' => [
                        SymptomsEnum::Delusions,
                        SymptomsEnum::Paranoia,
                    ],
                    'relative' => [
                        SymptomsEnum::PoorJudgement,
                        SymptomsEnum::Anxiety,
                    ],
                ],
                'criteria'    => [2, 1],
            ],
            [
                'code'        => 'F23.8',
                'title'       => 'Другие острые и преходящие психотические расстройства',
                'description' => 'Различные преходящие психотические расстройства с острым началом.',
                'relatives'   => ['F23.3', 'F20.3'],
                'exceptions'  => ['F20.8', 'F25.8'],
                'symptoms'    => [
                    'required' => [
                        SymptomsEnum::Hallucinations,
                        SymptomsEnum::Delusions,
                    ],
                    'relative' => [
                        SymptomsEnum::Disorientation,
                        SymptomsEnum::MoodSwings,
                    ],
                ],
                'criteria'    => [2, 1],
            ],
            [
                'code'        => 'F23.9',
                'title'       => 'Острое и преходящее психотическое расстройство неуточненное',
                'description' => 'Неуточненное психотическое расстройство с острым началом и преходящими симптомами.',
                'relatives'   => ['F23.8', 'F20.9'],
                'exceptions'  => ['F25.9', 'F20.9'],
                'symptoms'    => [
                    'required' => [
                        SymptomsEnum::Hallucinations,
                        SymptomsEnum::Delusions,
                    ],
                    'relative' => [
                        SymptomsEnum::PoorConcentration,
                        SymptomsEnum::Anxiety,
                    ],
                ],
                'criteria'    => [2, 1],
            ],
            // F50: Расстройства пищевого поведения
            [
                'code'        => 'F50.0',
                'title'       => 'Анорексия нервная',
                'description' => 'Психическое расстройство, характеризующееся отказом от пищи и выраженной потерей веса.',
                'relatives'   => ['F50.1', 'F50.2'],
                'exceptions'  => ['F32.2', 'F33.2'],
                'symptoms'    => [
                    'required' => [
                        SymptomsEnum::WeightLoss,
                        SymptomsEnum::FearOfWeightGain,
                    ],
                    'relative' => [
                        SymptomsEnum::BodyImageDistortion,
                        SymptomsEnum::Amenorrhea,
                    ],
                ],
                'criteria'    => [2, 1],
            ],
            [
                'code'        => 'F50.1',
                'title'       => 'Атипичная анорексия нервная',
                'description' => 'Анорексия нервная, но не соответствующая всем критериям типичного случая.',
                'relatives'   => ['F50.0', 'F50.2'],
                'exceptions'  => ['F32.1', 'F33.1'],
                'symptoms'    => [
                    'required' => [
                        SymptomsEnum::WeightLoss,
                        SymptomsEnum::FearOfWeightGain,
                    ],
                    'relative' => [
                        SymptomsEnum::BodyImageDistortion,
                        SymptomsEnum::Amenorrhea,
                    ],
                ],
                'criteria'    => [2, 1],
            ],
            [
                'code'        => 'F50.2',
                'title'       => 'Булимия нервная',
                'description' => 'Психическое расстройство, характеризующееся приступами переедания, за которыми следуют компенсаторные методы, чтобы избежать набора веса.',
                'relatives'   => ['F50.3', 'F50.8'],
                'exceptions'  => ['F32.2', 'F33.2'],
                'symptoms'    => [
                    'required' => [
                        SymptomsEnum::BingeEating,
                        SymptomsEnum::CompensatoryBehaviors,
                    ],
                    'relative' => [
                        SymptomsEnum::BodyImageDistortion,
                        SymptomsEnum::FearOfWeightGain,
                    ],
                ],
                'criteria'    => [2, 1],
            ],
            [
                'code'        => 'F50.3',
                'title'       => 'Атипичная булимическая нервная',
                'description' => 'Булимия нервная, не соответствующая всем критериям типичного случая.',
                'relatives'   => ['F50.2', 'F50.8'],
                'exceptions'  => ['F32.2', 'F33.3'],
                'symptoms'    => [
                    'required' => [
                        SymptomsEnum::BingeEating,
                        SymptomsEnum::CompensatoryBehaviors,
                    ],
                    'relative' => [
                        SymptomsEnum::BodyImageDistortion,
                        SymptomsEnum::FearOfWeightGain,
                    ],
                ],
                'criteria'    => [2, 1],
            ],
            [
                'code'        => 'F50.4',
                'title'       => 'Переедание, связанное с другими психическими расстройствами',
                'description' => 'Переедание, обусловленное другими психическими или поведенческими факторами.',
                'relatives'   => ['F50.2', 'F50.8'],
                'exceptions'  => ['F32.3', 'F33.3'],
                'symptoms'    => [
                    'required' => [
                        SymptomsEnum::BingeEating,
                        SymptomsEnum::PoorJudgement,
                    ],
                    'relative' => [
                        SymptomsEnum::Anxiety,
                        SymptomsEnum::BodyImageDistortion,
                    ],
                ],
                'criteria'    => [2, 1],
            ],
            [
                'code'        => 'F50.5',
                'title'       => 'Рвота, связанная с другими психическими расстройствами',
                'description' => 'Рвота, возникающая вследствие других психических расстройств.',
                'relatives'   => ['F50.4', 'F50.8'],
                'exceptions'  => ['F32.1', 'F33.1'],
                'symptoms'    => [
                    'required' => [
                        SymptomsEnum::CompensatoryBehaviors,
                        SymptomsEnum::PoorJudgement,
                    ],
                    'relative' => [
                        SymptomsEnum::Anxiety,
                        SymptomsEnum::BingeEating,
                    ],
                ],
                'criteria'    => [2, 1],
            ],
            [
                'code'        => 'F50.8',
                'title'       => 'Другие расстройства приема пищи',
                'description' => 'Различные расстройства приема пищи, которые не соответствуют описанным критериям.',
                'relatives'   => ['F50.0', 'F50.2'],
                'exceptions'  => ['F32.0', 'F33.0'],
                'symptoms'    => [
                    'required' => [
                        SymptomsEnum::CompensatoryBehaviors,
                        SymptomsEnum::FearOfWeightGain,
                    ],
                    'relative' => [
                        SymptomsEnum::BodyImageDistortion,
                        SymptomsEnum::Anxiety,
                    ],
                ],
                'criteria'    => [2, 1],
            ],
            [
                'code'        => 'F50.9',
                'title'       => 'Неуточненное расстройство приема пищи',
                'description' => 'Неуточненное расстройство питания, которое не соответствует диагностическим критериям известных расстройств.',
                'relatives'   => ['F50.8', 'F50.5'],
                'exceptions'  => ['F32.0', 'F33.0'],
                'symptoms'    => [
                    'required' => [
                        SymptomsEnum::PoorJudgement,
                        SymptomsEnum::Anxiety,
                    ],
                    'relative' => [
                        SymptomsEnum::BingeEating,
                        SymptomsEnum::FearOfWeightGain,
                    ],
                ],
                'criteria'    => [2, 1],
            ],

            // F51: Нерасстройства сна, связанные с психическими расстройствами
            [
                'code'        => 'F51.0',
                'title'       => 'Нерасстройства сна: бессонница',
                'description' => 'Трудности с засыпанием или поддержанием сна, часто сопровождающиеся дневной сонливостью.',
                'relatives'   => ['F51.1', 'F51.3'],
                'exceptions'  => ['F10.5', 'F32.0'],
                'symptoms'    => [
                    'required' => [
                        SymptomsEnum::Insomnia,
                        SymptomsEnum::DaytimeFatigue,
                    ],
                    'relative' => [
                        SymptomsEnum::Anxiety,
                        SymptomsEnum::PoorConcentration,
                    ],
                ],
                'criteria'    => [2, 1],
            ],
            [
                'code'        => 'F51.1',
                'title'       => 'Нерасстройства сна: гиперсомния',
                'description' => 'Чрезмерная сонливость, включая продолжительный ночной сон и дневные приступы сна.',
                'relatives'   => ['F51.0', 'F51.3'],
                'exceptions'  => ['F10.5', 'F32.1'],
                'symptoms'    => [
                    'required' => [
                        SymptomsEnum::Hypersomnia,
                        SymptomsEnum::DaytimeSleepiness,
                    ],
                    'relative' => [
                        SymptomsEnum::Anxiety,
                        SymptomsEnum::PoorConcentration,
                    ],
                ],
                'criteria'    => [2, 1],
            ],
            [
                'code'        => 'F51.2',
                'title'       => 'Нерасстройства сна: расстройство сна, связанное с нарушением ритма',
                'description' => 'Нарушение сна, связанное с нарушением нормального ритма сна и бодрствования.',
                'relatives'   => ['F51.0', 'F51.1'],
                'exceptions'  => ['F10.5', 'F32.2'],
                'symptoms'    => [
                    'required' => [
                        SymptomsEnum::SleepPatternDisruption,
                        SymptomsEnum::DaytimeFatigue,
                    ],
                    'relative' => [
                        SymptomsEnum::PoorConcentration,
                        SymptomsEnum::MoodSwings,
                    ],
                ],
                'criteria'    => [2, 1],
            ],
            [
                'code'        => 'F51.3',
                'title'       => 'Нерасстройства сна: сомнамбулизм (лунатизм)',
                'description' => 'Периодическое нарушение сна, характеризующееся передвижением во время сна.',
                'relatives'   => ['F51.0', 'F51.1'],
                'exceptions'  => ['F32.3', 'F20.0'],
                'symptoms'    => [
                    'required' => [
                        SymptomsEnum::Sleepwalking,
                    ],
                    'relative' => [
                        SymptomsEnum::PoorJudgement,
                        SymptomsEnum::Amnesia,
                    ],
                ],
                'criteria'    => [1, 1],
            ],
            [
                'code'        => 'F51.4',
                'title'       => 'Нерасстройства сна: ночные кошмары',
                'description' => 'Повторяющиеся эпизоды кошмарных снов, сопровождающиеся пробуждением и тревогой.',
                'relatives'   => ['F51.0', 'F51.5'],
                'exceptions'  => ['F20.1', 'F32.2'],
                'symptoms'    => [
                    'required' => [
                        SymptomsEnum::Nightmares,
                    ],
                    'relative' => [
                        SymptomsEnum::Anxiety,
                        SymptomsEnum::Insomnia,
                    ],
                ],
                'criteria'    => [1, 1],
            ],
            [
                'code'        => 'F51.5',
                'title'       => 'Нерасстройства сна: ночные страхи',
                'description' => 'Эпизоды сильного страха во время сна, часто сопровождающиеся криками и движениями.',
                'relatives'   => ['F51.4', 'F51.0'],
                'exceptions'  => ['F32.2', 'F10.5'],
                'symptoms'    => [
                    'required' => [
                        SymptomsEnum::NightTerrors,
                    ],
                    'relative' => [
                        SymptomsEnum::Anxiety,
                        SymptomsEnum::Sweating,
                    ],
                ],
                'criteria'    => [1, 1],
            ],
            [
                'code'        => 'F51.8',
                'title'       => 'Другие расстройства сна, не обусловленные органическим заболеванием',
                'description' => 'Различные нарушения сна, не связанные с органическими нарушениями.',
                'relatives'   => ['F51.0', 'F51.1'],
                'exceptions'  => ['F10.5', 'F32.0'],
                'symptoms'    => [
                    'required' => [
                        SymptomsEnum::SleepDisturbance,
                    ],
                    'relative' => [
                        SymptomsEnum::Anxiety,
                        SymptomsEnum::Insomnia,
                    ],
                ],
                'criteria'    => [1, 1],
            ],
            [
                'code'        => 'F51.9',
                'title'       => 'Нерасстройства сна, неуточненные',
                'description' => 'Неуточненные нарушения сна без связи с органическим заболеванием.',
                'relatives'   => ['F51.0', 'F51.8'],
                'exceptions'  => ['F10.5', 'F32.0'],
                'symptoms'    => [
                    'required' => [
                        SymptomsEnum::SleepDisturbance,
                    ],
                    'relative' => [
                        SymptomsEnum::DaytimeFatigue,
                        SymptomsEnum::Anxiety,
                    ],
                ],
                'criteria'    => [1, 1],
            ],

            // F52: Сексуальные дисфункции, не обусловленные органическим расстройством
            [
                'code'        => 'F52.0',
                'title'       => 'Потеря сексуального влечения',
                'description' => 'Снижение или отсутствие интереса к сексуальной активности.',
                'relatives'   => ['F52.1', 'F52.2'],
                'exceptions'  => ['F32.1', 'F33.0'],
                'symptoms'    => [
                    'required' => [
                        SymptomsEnum::LowLibido,
                    ],
                    'relative' => [
                        SymptomsEnum::PoorMood,
                        SymptomsEnum::Anxiety,
                    ],
                ],
                'criteria'    => [1, 1],
            ],
            [
                'code'        => 'F52.1',
                'title'       => 'Сексуальная авергия и отсутствие удовольствия от сексуальной активности',
                'description' => 'Отвращение или отсутствие удовольствия при сексуальной активности.',
                'relatives'   => ['F52.0', 'F52.2'],
                'exceptions'  => ['F33.1', 'F32.2'],
                'symptoms'    => [
                    'required' => [
                        SymptomsEnum::SexualAverison,
                    ],
                    'relative' => [
                        SymptomsEnum::LowLibido,
                        SymptomsEnum::PoorMood,
                    ],
                ],
                'criteria'    => [1, 1],
            ],
            [
                'code'        => 'F52.2',
                'title'       => 'Сбой полового возбуждения',
                'description' => 'Затруднения или неспособность достичь полового возбуждения.',
                'relatives'   => ['F52.0', 'F52.1'],
                'exceptions'  => ['F33.0', 'F32.1'],
                'symptoms'    => [
                    'required' => [
                        SymptomsEnum::ArousalDysfunction,
                    ],
                    'relative' => [
                        SymptomsEnum::LowLibido,
                        SymptomsEnum::Anxiety,
                    ],
                ],
                'criteria'    => [1, 1],
            ],
            [
                'code'        => 'F52.3',
                'title'       => 'Оргазмическая дисфункция',
                'description' => 'Затруднения или неспособность достичь оргазма.',
                'relatives'   => ['F52.0', 'F52.1'],
                'exceptions'  => ['F33.1', 'F32.3'],
                'symptoms'    => [
                    'required' => [
                        SymptomsEnum::OrgasmicDysfunction,
                    ],
                    'relative' => [
                        SymptomsEnum::Anxiety,
                        SymptomsEnum::PoorMood,
                    ],
                ],
                'criteria'    => [1, 1],
            ],
            [
                'code'        => 'F52.4',
                'title'       => 'Преждевременная эякуляция',
                'description' => 'Отсутствие контроля над эякуляцией, приводящее к преждевременной эякуляции.',
                'relatives'   => ['F52.1', 'F52.3'],
                'exceptions'  => ['F33.0', 'F32.2'],
                'symptoms'    => [
                    'required' => [
                        SymptomsEnum::PrematureEjaculation,
                    ],
                    'relative' => [
                        SymptomsEnum::Anxiety,
                        SymptomsEnum::PoorMood,
                    ],
                ],
                'criteria'    => [1, 1],
            ],
            [
                'code'        => 'F52.8',
                'title'       => 'Другие сексуальные дисфункции, не обусловленные органическим расстройством',
                'description' => 'Различные сексуальные дисфункции, не связанные с органическими заболеваниями.',
                'relatives'   => ['F52.0', 'F52.2'],
                'exceptions'  => ['F33.0', 'F32.0'],
                'symptoms'    => [
                    'required' => [
                        SymptomsEnum::SexualDysfunction,
                    ],
                    'relative' => [
                        SymptomsEnum::LowLibido,
                        SymptomsEnum::Anxiety,
                    ],
                ],
                'criteria'    => [1, 1],
            ],
            [
                'code'        => 'F52.9',
                'title'       => 'Неуточненная сексуальная дисфункция',
                'description' => 'Неуточненные сексуальные дисфункции без органической причины.',
                'relatives'   => ['F52.1', 'F52.3'],
                'exceptions'  => ['F32.1', 'F33.1'],
                'symptoms'    => [
                    'required' => [
                        SymptomsEnum::SexualDysfunction,
                    ],
                    'relative' => [
                        SymptomsEnum::LowLibido,
                        SymptomsEnum::Anxiety,
                    ],
                ],
                'criteria'    => [1, 1],
            ],
            // F53.0
            [
                'code'        => 'F53.0',
                'title'       => 'Легкое нарушение психического и поведенческого здоровья, связанное с послеродовым периодом',
                'description' => 'Легкие и временные психические и поведенческие нарушения, возникающие в период после родов.',
                'relatives'   => [
                    'F53.8',
                    'F53.9',
                ],
                'exceptions'  => [
                    'F32.0',
                    'F32.1',
                ],
                'symptoms'    => [
                    'required' => [
                        SymptomsEnum::DepressedMood,
                        SymptomsEnum::Anxiety,
                    ],
                    'relative' => [
                        SymptomsEnum::Insomnia,
                        SymptomsEnum::Fatigue,
                        SymptomsEnum::Irritability,
                    ],
                ],
                'criteria'    => [1, 1],
            ],
            [
                'code'        => 'F53.1',
                'title'       => 'Тяжелое психическое расстройство, связанное с послеродовым периодом',
                'description' => 'Серьезное психическое расстройство, такое как депрессия или психоз, возникающее в период после родов.',
                'relatives'   => [
                    'F53.8',
                    'F53.9',
                ],
                'exceptions'  => [
                    'F32.2',
                    'F31.5',
                ],
                'symptoms'    => [
                    'required' => [
                        SymptomsEnum::SevereDepression,
                        SymptomsEnum::Hallucinations,
                        SymptomsEnum::Delusions,
                    ],
                    'relative' => [
                        SymptomsEnum::Anxiety,
                        SymptomsEnum::Insomnia,
                        SymptomsEnum::Irritability,
                    ],
                ],
                'criteria'    => [2, 1],
            ],
            [
                'code'        => 'F53.8',
                'title'       => 'Другие психические расстройства, связанные с послеродовым периодом',
                'description' => 'Другие уточненные психические расстройства, возникающие в период после родов.',
                'relatives'   => [
                    'F53.0',
                    'F53.1',
                ],
                'exceptions'  => [
                    'F32.0',
                    'F32.1',
                ],
                'symptoms'    => [
                    'required' => [
                        SymptomsEnum::MoodChanges,
                    ],
                    'relative' => [
                        SymptomsEnum::Anxiety,
                        SymptomsEnum::Fatigue,
                    ],
                ],
                'criteria'    => [1, 1],
            ],
            [
                'code'        => 'F53.9',
                'title'       => 'Неуточненное психическое расстройство, связанное с послеродовым периодом',
                'description' => 'Неуточненные психические и поведенческие расстройства, возникающие в период после родов.',
                'relatives'   => [
                    'F53.0',
                    'F53.1',
                ],
                'exceptions'  => [
                    'F32.0',
                    'F32.1',
                ],
                'symptoms'    => [
                    'required' => [
                        SymptomsEnum::BehavioralChanges,
                    ],
                    'relative' => [
                        SymptomsEnum::Anxiety,
                    ],
                ],
                'criteria'    => [1, 1],
            ],
            // F54
            [
                'code'        => 'F54',
                'title'       => 'Психологические факторы и поведенческие реакции на расстройства, классифицированные в других рубриках',
                'description' => 'Психологические и поведенческие факторы, влияющие на другие состояния.',
                'relatives'   => [],
                'exceptions'  => [],
                'symptoms'    => [
                    'required' => [
                        SymptomsEnum::StressReaction,
                    ],
                    'relative' => [
                        SymptomsEnum::Anxiety,
                    ],
                ],
                'criteria'    => [1, 1],
            ],
            // F55: Злоупотребление веществами, не вызывающими зависимости
            [
                'code'        => 'F55.0',
                'title'       => 'Злоупотребление снотворными средствами',
                'description' => 'Регулярное злоупотребление снотворными средствами без развития зависимости.',
                'relatives'   => [],
                'exceptions'  => ['F10.0', 'F11.0'],
                'symptoms'    => [
                    'required' => [
                        SymptomsEnum::SleepDisturbance,
                        SymptomsEnum::DaytimeFatigue,
                    ],
                    'relative' => [
                        SymptomsEnum::ToleranceIncrease,
                        SymptomsEnum::Anxiety,
                    ],
                ],
                'criteria'    => [2, 1],
            ],
            [
                'code'        => 'F55.1',
                'title'       => 'Злоупотребление анальгетиками',
                'description' => 'Регулярное злоупотребление анальгетиками без формирования зависимости.',
                'relatives'   => [],
                'exceptions'  => ['F11.0', 'F19.0'],
                'symptoms'    => [
                    'required' => [
                        SymptomsEnum::ChronicPain,
                        SymptomsEnum::Anxiety,
                    ],
                    'relative' => [
                        SymptomsEnum::ToleranceIncrease,
                        SymptomsEnum::Nausea,
                    ],
                ],
                'criteria'    => [2, 1],
            ],
            [
                'code'        => 'F55.2',
                'title'       => 'Злоупотребление слабительными средствами',
                'description' => 'Чрезмерное употребление слабительных, не вызывающее зависимости.',
                'relatives'   => [],
                'exceptions'  => ['F10.0', 'F50.0'],
                'symptoms'    => [
                    'required' => [
                        SymptomsEnum::Diarrhea,
                        SymptomsEnum::DependenceOnLaxatives,
                    ],
                    'relative' => [
                        SymptomsEnum::WeightChanges,
                        SymptomsEnum::AbdominalPain,
                    ],
                ],
                'criteria'    => [2, 1],
            ],
            [
                'code'        => 'F55.3',
                'title'       => 'Злоупотребление витаминами и тонизирующими средствами',
                'description' => 'Чрезмерное использование витаминов или тонизирующих препаратов.',
                'relatives'   => [],
                'exceptions'  => ['F19.0'],
                'symptoms'    => [
                    'required' => [
                        SymptomsEnum::ExcessiveIntake,
                    ],
                    'relative' => [
                        SymptomsEnum::Hyperactivity,
                        SymptomsEnum::Anxiety,
                    ],
                ],
                'criteria'    => [1, 1],
            ],
            [
                'code'        => 'F55.9',
                'title'       => 'Злоупотребление веществами, не вызывающими зависимости, неуточненное',
                'description' => 'Неуточненные случаи злоупотребления веществами без возникновения зависимости.',
                'relatives'   => [],
                'exceptions'  => ['F10.0', 'F19.0'],
                'symptoms'    => [
                    'required' => [
                        SymptomsEnum::ExcessiveIntake,
                    ],
                    'relative' => [
                        SymptomsEnum::Anxiety,
                        SymptomsEnum::ToleranceIncrease,
                    ],
                ],
                'criteria'    => [1, 1],
            ],

            // F59: Неуточненные поведенческие синдромы, связанные с физиологическими нарушениями и физическими факторами
            [
                'code'        => 'F59',
                'title'       => 'Неуточненные поведенческие синдромы, связанные с физиологическими нарушениями и физическими факторами',
                'description' => 'Поведенческие синдромы, связанные с физиологическими изменениями, без указания точного физического фактора.',
                'relatives'   => [],
                'exceptions'  => ['F50.0', 'F51.0', 'F52.0'],
                'symptoms'    => [
                    'required' => [
                        SymptomsEnum::BehavioralChanges,
                    ],
                    'relative' => [
                        SymptomsEnum::SleepDisturbance,
                        SymptomsEnum::Anxiety,
                    ],
                ],
                'criteria'    => [1, 1],
            ],
            // F19
            [
                'code'        => 'F19.0',
                'title'       => 'Острая интоксикация, вызванная употреблением нескольких психоактивных веществ',
                'description' => 'Состояние, характеризующееся нарушением сознания и когнитивными нарушениями в результате употребления нескольких психоактивных веществ.',
                'relatives'   => [
                    'F10.0',
                    'F11.0',
                    'F12.0',
                    'F13.0',
                    'F14.0',
                ],
                'exceptions'  => [
                    'F19.1',
                    'F19.2',
                ],
                'symptoms'    => [
                    'required' => [
                        SymptomsEnum::ImpairedConsciousness,
                        SymptomsEnum::CognitiveImpairment,
                        SymptomsEnum::BehavioralChanges,
                    ],
                    'relative' => [
                        SymptomsEnum::PerceptualDisturbances,
                        SymptomsEnum::EmotionalDisturbances,
                        SymptomsEnum::PsychophysiologicalDisorders,
                    ],
                ],
                'criteria'    => [2, 1],
            ],
            [
                'code'        => 'F19.1',
                'title'       => 'Пагубное употребление нескольких психоактивных веществ',
                'description' => 'Употребление нескольких психоактивных веществ, приводящее к физическим или психическим повреждениям, но без признаков зависимости.',
                'relatives'   => [
                    'F10.1',
                    'F11.1',
                    'F12.1',
                    'F13.1',
                    'F14.1',
                ],
                'exceptions'  => [
                    'F19.0',
                    'F19.2',
                ],
                'symptoms'    => [
                    'required' => [
                        SymptomsEnum::PhysicalHarm,
                        SymptomsEnum::MentalHarm,
                    ],
                    'relative' => [
                        SymptomsEnum::LackOfDependence,
                    ],
                ],
                'criteria'    => [2, 1],
            ],
            [
                'code'        => 'F19.2',
                'title'       => 'Синдром зависимости, вызванный употреблением нескольких психоактивных веществ',
                'description' => 'Сильное желание употреблять вещество, трудности в контроле употребления, повышенная толерантность и пренебрежение альтернативными интересами.',
                'relatives'   => [
                    'F10.2',
                    'F11.2',
                    'F12.2',
                    'F13.2',
                    'F14.2',
                ],
                'exceptions'  => [
                    'F19.1',
                    'F19.3',
                ],
                'symptoms'    => [
                    'required' => [
                        SymptomsEnum::StrongDesireToUse,
                        SymptomsEnum::DifficultyControllingUse,
                        SymptomsEnum::ToleranceIncrease,
                        SymptomsEnum::NeglectOfAlternativeInterests,
                    ],
                    'relative' => [
                        SymptomsEnum::ContinuedUseDespiteHarm,
                        SymptomsEnum::WithdrawalSymptoms,
                    ],
                ],
                'criteria'    => [3, 1],
            ],
            [
                'code'        => 'F19.3',
                'title'       => 'Абстинентное состояние при употреблении нескольких психоактивных веществ',
                'description' => 'Физические и психические проявления, возникающие при прекращении употребления нескольких психоактивных веществ.',
                'relatives'   => [
                    'F10.3',
                    'F11.3',
                    'F12.3',
                    'F13.3',
                    'F14.3',
                ],
                'exceptions'  => [
                    'F19.2',
                    'F19.4',
                ],
                'symptoms'    => [
                    'required' => [
                        SymptomsEnum::WithdrawalSymptoms,
                    ],
                    'relative' => [
                        SymptomsEnum::PhysicalSymptoms,
                        SymptomsEnum::PsychologicalSymptoms,
                    ],
                ],
                'criteria'    => [1, 1],
            ],
            [
                'code'        => 'F19.4',
                'title'       => 'Абстинентное состояние с делирием при употреблении нескольких психоактивных веществ',
                'description' => 'Абстинентное состояние, сопровождающееся делирием, тремором, агитацией и нарушением психомоторики.',
                'relatives'   => [
                    'F10.4',
                    'F11.4',
                    'F12.4',
                    'F13.4',
                    'F14.4',
                ],
                'exceptions'  => [
                    'F19.3',
                    'F19.5',
                ],
                'symptoms'    => [
                    'required' => [
                        SymptomsEnum::WithdrawalSymptoms,
                        SymptomsEnum::Delirium,
                    ],
                    'relative' => [
                        SymptomsEnum::Hallucinations,
                        SymptomsEnum::Tremor,
                        SymptomsEnum::Agitation,
                    ],
                ],
                'criteria'    => [2, 1],
            ],
            [
                'code'        => 'F19.5',
                'title'       => 'Психотическое расстройство при употреблении нескольких психоактивных веществ',
                'description' => 'Психотическое состояние, характеризующееся бредом, галлюцинациями и аффективными нарушениями.',
                'relatives'   => [
                    'F10.5',
                    'F11.5',
                    'F12.5',
                    'F13.5',
                    'F14.5',
                ],
                'exceptions'  => [
                    'F19.4',
                    'F19.6',
                ],
                'symptoms'    => [
                    'required' => [
                        SymptomsEnum::Delusions,
                        SymptomsEnum::Hallucinations,
                        SymptomsEnum::PsychoticFeatures,
                    ],
                    'relative' => [
                        SymptomsEnum::PsychomotorDisturbances,
                        SymptomsEnum::AffectiveDisturbances,
                    ],
                ],
                'criteria'    => [2, 1],
            ],
            [
                'code'        => 'F19.6',
                'title'       => 'Амнестический синдром, вызванный употреблением нескольких психоактивных веществ',
                'description' => 'Выраженные нарушения памяти, трудности с запоминанием и конфабуляции.',
                'relatives'   => [
                    'F10.6',
                    'F11.6',
                    'F12.6',
                    'F13.6',
                    'F14.6',
                ],
                'exceptions'  => [
                    'F19.5',
                    'F19.7',
                ],
                'symptoms'    => [
                    'required' => [
                        SymptomsEnum::SevereMemoryImpairment,
                        SymptomsEnum::DifficultyWithRecall,
                    ],
                    'relative' => [
                        SymptomsEnum::Confabulation,
                    ],
                ],
                'criteria'    => [2, 1],
            ],
            [
                'code'        => 'F19.7',
                'title'       => 'Остаточные и поздно возникающие психотические расстройства при употреблении нескольких психоактивных веществ',
                'description' => 'Хронические психотические симптомы, изменения личности и когнитивные нарушения, возникающие после прекращения употребления веществ.',
                'relatives'   => [
                    'F10.7',
                    'F11.7',
                    'F12.7',
                    'F13.7',
                    'F14.7',
                ],
                'exceptions'  => [
                    'F19.6',
                    'F19.8',
                ],
                'symptoms'    => [
                    'required' => [
                        SymptomsEnum::ChronicPsychoticSymptoms,
                        SymptomsEnum::PersonalityChanges,
                        SymptomsEnum::CognitiveImpairment,
                    ],
                    'relative' => [
                        SymptomsEnum::AffectiveDisturbances,
                    ],
                ],
                'criteria'    => [3, 1],
            ],
            [
                'code'        => 'F19.8',
                'title'       => 'Другие психические и поведенческие расстройства, вызванные употреблением нескольких психоактивных веществ',
                'description' => 'Другие психические расстройства, вызванные употреблением нескольких психоактивных веществ, не классифицированные в предыдущих категориях.',
                'relatives'   => [
                    'F10.8',
                    'F11.8',
                    'F12.8',
                    'F13.8',
                    'F14.8',
                ],
                'exceptions'  => [
                    'F19.7',
                    'F19.9',
                ],
                'symptoms'    => [
                    'required' => [
                        SymptomsEnum::PsychiatricSymptoms,
                    ],
                    'relative' => [
                        SymptomsEnum::BehavioralChanges,
                    ],
                ],
                'criteria'    => [1, 1],
            ],
            [
                'code'        => 'F19.9',
                'title'       => 'Неуточненные психические и поведенческие расстройства, вызванные употреблением нескольких психоактивных веществ',
                'description' => 'Неуточненные психические и поведенческие расстройства, вызванные употреблением нескольких психоактивных веществ.',
                'relatives'   => [
                    'F10.9',
                    'F11.9',
                    'F12.9',
                    'F13.9',
                    'F14.9',
                ],
                'exceptions'  => [
                    'F19.8',
                    'F19.0',
                ],
                'symptoms'    => [
                    'required' => [
                        SymptomsEnum::UnspecifiedPsychiatricSymptoms,
                    ],
                    'relative' => [
                        SymptomsEnum::BehavioralDisturbances,
                    ],
                ],
                'criteria'    => [1, 1],
            ],
            // F10
            [
                'code'        => 'F10.0',
                'title'       => 'Острая интоксикация алкоголем',
                'description' => 'Острое алкогольное опьянение, характеризующееся нарушением координации, речи и поведения.',
                'relatives'   => [
                    'F19.0',
                    'F11.0',
                    'F12.0',
                ],
                'exceptions'  => [
                    'F10.1',
                    'F10.2',
                ],
                'symptoms'    => [
                    'required' => [
                        SymptomsEnum::ImpairedCoordination,
                        SymptomsEnum::SlurredSpeech,
                        SymptomsEnum::BehavioralChanges,
                    ],
                    'relative' => [
                        SymptomsEnum::Drowsiness,
                        SymptomsEnum::EmotionalLability,
                        SymptomsEnum::ReducedInhibitions,
                    ],
                ],
                'criteria'    => [2, 1],
            ],
            [
                'code'        => 'F10.1',
                'title'       => 'Пагубное употребление алкоголя',
                'description' => 'Употребление алкоголя, приводящее к физическим или психическим повреждениям без признаков зависимости.',
                'relatives'   => [
                    'F19.1',
                    'F11.1',
                    'F12.1',
                ],
                'exceptions'  => [
                    'F10.0',
                    'F10.2',
                ],
                'symptoms'    => [
                    'required' => [
                        SymptomsEnum::PhysicalHarm,
                        SymptomsEnum::MentalHarm,
                    ],
                    'relative' => [
                        SymptomsEnum::LackOfDependence,
                    ],
                ],
                'criteria'    => [2, 1],
            ],
            [
                'code'        => 'F10.2',
                'title'       => 'Синдром зависимости от алкоголя',
                'description' => 'Сильное желание употреблять алкоголь, трудности в контроле употребления, повышение толерантности и пренебрежение альтернативными интересами.',
                'relatives'   => [
                    'F19.2',
                    'F11.2',
                    'F12.2',
                ],
                'exceptions'  => [
                    'F10.1',
                    'F10.3',
                ],
                'symptoms'    => [
                    'required' => [
                        SymptomsEnum::StrongDesireToUse,
                        SymptomsEnum::DifficultyControllingUse,
                        SymptomsEnum::ToleranceIncrease,
                        SymptomsEnum::NeglectOfAlternativeInterests,
                    ],
                    'relative' => [
                        SymptomsEnum::ContinuedUseDespiteHarm,
                        SymptomsEnum::WithdrawalSymptoms,
                    ],
                ],
                'criteria'    => [3, 1],
            ],
            [
                'code'        => 'F10.3',
                'title'       => 'Абстинентное состояние при употреблении алкоголя',
                'description' => 'Физические и психические проявления, возникающие при прекращении употребления алкоголя.',
                'relatives'   => [
                    'F19.3',
                    'F11.3',
                    'F12.3',
                ],
                'exceptions'  => [
                    'F10.2',
                    'F10.4',
                ],
                'symptoms'    => [
                    'required' => [
                        SymptomsEnum::WithdrawalSymptoms,
                    ],
                    'relative' => [
                        SymptomsEnum::PhysicalSymptoms,
                        SymptomsEnum::PsychologicalSymptoms,
                    ],
                ],
                'criteria'    => [1, 1],
            ],
            [
                'code'        => 'F10.4',
                'title'       => 'Абстинентное состояние с делирием при употреблении алкоголя',
                'description' => 'Абстинентное состояние с делирием, включая тремор, агитацию и галлюцинации.',
                'relatives'   => [
                    'F19.4',
                    'F11.4',
                    'F12.4',
                ],
                'exceptions'  => [
                    'F10.3',
                    'F10.5',
                ],
                'symptoms'    => [
                    'required' => [
                        SymptomsEnum::WithdrawalSymptoms,
                        SymptomsEnum::Delirium,
                    ],
                    'relative' => [
                        SymptomsEnum::Hallucinations,
                        SymptomsEnum::Tremor,
                        SymptomsEnum::Agitation,
                    ],
                ],
                'criteria'    => [2, 1],
            ],
            [
                'code'        => 'F10.5',
                'title'       => 'Психотическое расстройство, вызванное употреблением алкоголя',
                'description' => 'Психотическое состояние, характеризующееся бредом, галлюцинациями и аффективными нарушениями.',
                'relatives'   => [
                    'F19.5',
                    'F11.5',
                    'F12.5',
                ],
                'exceptions'  => [
                    'F10.4',
                    'F10.6',
                ],
                'symptoms'    => [
                    'required' => [
                        SymptomsEnum::Delusions,
                        SymptomsEnum::Hallucinations,
                        SymptomsEnum::PsychoticFeatures,
                    ],
                    'relative' => [
                        SymptomsEnum::AffectiveDisturbances,
                    ],
                ],
                'criteria'    => [2, 1],
            ],
            [
                'code'        => 'F10.6',
                'title'       => 'Амнестический синдром, вызванный употреблением алкоголя',
                'description' => 'Синдром с выраженными нарушениями памяти, конфабуляциями и трудностями с запоминанием.',
                'relatives'   => [
                    'F19.6',
                    'F11.6',
                    'F12.6',
                ],
                'exceptions'  => [
                    'F10.5',
                    'F10.7',
                ],
                'symptoms'    => [
                    'required' => [
                        SymptomsEnum::SevereMemoryImpairment,
                        SymptomsEnum::DifficultyWithRecall,
                    ],
                    'relative' => [
                        SymptomsEnum::Confabulation,
                    ],
                ],
                'criteria'    => [2, 1],
            ],
            [
                'code'        => 'F10.7',
                'title'       => 'Остаточные и поздно возникающие психотические расстройства при употреблении алкоголя',
                'description' => 'Хронические психотические симптомы и изменения личности, сохраняющиеся после прекращения употребления алкоголя.',
                'relatives'   => [
                    'F19.7',
                    'F11.7',
                    'F12.7',
                ],
                'exceptions'  => [
                    'F10.6',
                    'F10.8',
                ],
                'symptoms'    => [
                    'required' => [
                        SymptomsEnum::ChronicPsychoticSymptoms,
                        SymptomsEnum::PersonalityChanges,
                        SymptomsEnum::CognitiveImpairment,
                    ],
                    'relative' => [
                        SymptomsEnum::AffectiveDisturbances,
                    ],
                ],
                'criteria'    => [3, 1],
            ],
            [
                'code'        => 'F10.8',
                'title'       => 'Другие психические и поведенческие расстройства, вызванные употреблением алкоголя',
                'description' => 'Прочие психические расстройства, вызванные употреблением алкоголя и не классифицированные в других категориях.',
                'relatives'   => [
                    'F19.8',
                    'F11.8',
                    'F12.8',
                ],
                'exceptions'  => [
                    'F10.7',
                    'F10.9',
                ],
                'symptoms'    => [
                    'required' => [
                        SymptomsEnum::PsychiatricSymptoms,
                    ],
                    'relative' => [
                        SymptomsEnum::BehavioralChanges,
                    ],
                ],
                'criteria'    => [1, 1],
            ],
            [
                'code'        => 'F10.9',
                'title'       => 'Неуточненные психические и поведенческие расстройства, вызванные употреблением алкоголя',
                'description' => 'Неуточненные психические и поведенческие расстройства, вызванные употреблением алкоголя.',
                'relatives'   => [
                    'F19.9',
                    'F11.9',
                    'F12.9',
                ],
                'exceptions'  => [
                    'F10.8',
                    'F10.0',
                ],
                'symptoms'    => [
                    'required' => [
                        SymptomsEnum::UnspecifiedPsychiatricSymptoms,
                    ],
                    'relative' => [
                        SymptomsEnum::BehavioralDisturbances,
                    ],
                ],
                'criteria'    => [1, 1],
            ],
            // F16
            [
                'code'        => 'F16.0',
                'title'       => 'Острая интоксикация галлюциногенами',
                'description' => 'Острое состояние интоксикации галлюциногенами, характеризующееся дезориентацией, изменениями восприятия и эмоциональной лабильностью.',
                'relatives'   => [
                    'F19.0',
                    'F11.0',
                    'F12.0',
                ],
                'exceptions'  => [
                    'F16.1',
                    'F16.2',
                ],
                'symptoms'    => [
                    'required' => [
                        SymptomsEnum::PerceptualDisturbances,      // Изменения восприятия
                        SymptomsEnum::Disorientation,              // Дезориентация
                        SymptomsEnum::EmotionalLability,           // Эмоциональная лабильность
                    ],
                    'relative' => [
                        SymptomsEnum::Paranoia,                    // Паранойя
                        SymptomsEnum::Anxiety,                     // Тревожность
                        SymptomsEnum::MotorDisturbances,           // Нарушения движений
                    ],
                ],
                'criteria'    => [2, 1],
            ],
            [
                'code'        => 'F16.1',
                'title'       => 'Пагубное употребление галлюциногенов',
                'description' => 'Употребление галлюциногенов, приводящее к физическим или психическим повреждениям без признаков зависимости.',
                'relatives'   => [
                    'F19.1',
                    'F11.1',
                    'F12.1',
                ],
                'exceptions'  => [
                    'F16.0',
                    'F16.2',
                ],
                'symptoms'    => [
                    'required' => [
                        SymptomsEnum::PhysicalHarm,                // Физический вред
                        SymptomsEnum::MentalHarm,                  // Психический вред
                    ],
                    'relative' => [
                        SymptomsEnum::LackOfDependence,            // Отсутствие признаков зависимости
                    ],
                ],
                'criteria'    => [2, 1],
            ],
            [
                'code'        => 'F16.2',
                'title'       => 'Синдром зависимости от галлюциногенов',
                'description' => 'Сильное желание употреблять галлюциногены, трудности в контроле, повышение толерантности и пренебрежение альтернативными интересами.',
                'relatives'   => [
                    'F19.2',
                    'F11.2',
                    'F12.2',
                ],
                'exceptions'  => [
                    'F16.1',
                    'F16.3',
                ],
                'symptoms'    => [
                    'required' => [
                        SymptomsEnum::StrongDesireToUse,           // Сильное желание употребления
                        SymptomsEnum::DifficultyControllingUse,    // Трудности с контролем
                        SymptomsEnum::ToleranceIncrease,           // Повышение толерантности
                        SymptomsEnum::NeglectOfAlternativeInterests, // Пренебрежение альтернативными интересами
                    ],
                    'relative' => [
                        SymptomsEnum::ContinuedUseDespiteHarm,     // Продолжение употребления несмотря на вред
                        SymptomsEnum::WithdrawalSymptoms,          // Симптомы отмены
                    ],
                ],
                'criteria'    => [3, 1],
            ],
            [
                'code'        => 'F16.3',
                'title'       => 'Абстинентное состояние при употреблении галлюциногенов',
                'description' => 'Физические и психические проявления, возникающие при прекращении употребления галлюциногенов.',
                'relatives'   => [
                    'F19.3',
                    'F11.3',
                    'F12.3',
                ],
                'exceptions'  => [
                    'F16.2',
                    'F16.4',
                ],
                'symptoms'    => [
                    'required' => [
                        SymptomsEnum::PhysicalWithdrawalSymptoms, // Физические симптомы отмены
                        SymptomsEnum::PsychologicalWithdrawalSymptoms, // Психические симптомы отмены
                    ],
                    'relative' => [
                        SymptomsEnum::Anxiety,                    // Тревожность
                        SymptomsEnum::Tremor,                     // Тремор
                    ],
                ],
                'criteria'    => [1, 1],
            ],
            [
                'code'        => 'F16.4',
                'title'       => 'Абстинентное состояние с делирием при употреблении галлюциногенов',
                'description' => 'Абстинентное состояние с делирием, включая галлюцинации, дезориентацию и возбуждение.',
                'relatives'   => [
                    'F19.4',
                    'F11.4',
                    'F12.4',
                ],
                'exceptions'  => [
                    'F16.3',
                    'F16.5',
                ],
                'symptoms'    => [
                    'required' => [
                        SymptomsEnum::WithdrawalSymptoms,          // Симптомы отмены
                        SymptomsEnum::Delirium,                    // Делирий
                    ],
                    'relative' => [
                        SymptomsEnum::Hallucinations,              // Галлюцинации
                        SymptomsEnum::Disorientation,              // Дезориентация
                        SymptomsEnum::Agitation,                   // Возбуждение
                    ],
                ],
                'criteria'    => [2, 1],
            ],
            [
                'code'        => 'F16.5',
                'title'       => 'Психотическое расстройство, вызванное употреблением галлюциногенов',
                'description' => 'Психотическое состояние с бредом, галлюцинациями и аффективными нарушениями, вызванное употреблением галлюциногенов.',
                'relatives'   => [
                    'F19.5',
                    'F11.5',
                    'F12.5',
                ],
                'exceptions'  => [
                    'F16.4',
                    'F16.6',
                ],
                'symptoms'    => [
                    'required' => [
                        SymptomsEnum::Delusions,                   // Бред
                        SymptomsEnum::Hallucinations,              // Галлюцинации
                        SymptomsEnum::PsychoticFeatures,           // Психотические особенности
                    ],
                    'relative' => [
                        SymptomsEnum::AffectiveDisturbances,       // Аффективные нарушения
                    ],
                ],
                'criteria'    => [2, 1],
            ],
            [
                'code'        => 'F16.6',
                'title'       => 'Амнестический синдром, вызванный употреблением галлюциногенов',
                'description' => 'Синдром, характеризующийся выраженными нарушениями памяти, связанными с употреблением галлюциногенов.',
                'relatives'   => [
                    'F19.6',
                    'F11.6',
                    'F12.6',
                ],
                'exceptions'  => [
                    'F16.5',
                    'F16.7',
                ],
                'symptoms'    => [
                    'required' => [
                        SymptomsEnum::SevereMemoryImpairment,      // Выраженное нарушение памяти
                        SymptomsEnum::DifficultyWithRecall,        // Затруднения при воспоминании
                    ],
                    'relative' => [
                        SymptomsEnum::Confabulation,               // Конфабуляция (ложные воспоминания)
                    ],
                ],
                'criteria'    => [2, 1],
            ],
            [
                'code'        => 'F16.7',
                'title'       => 'Остаточные и поздно возникающие психотические расстройства при употреблении галлюциногенов',
                'description' => 'Психотические симптомы и изменения личности, сохраняющиеся после прекращения употребления галлюциногенов.',
                'relatives'   => [
                    'F19.7',
                    'F11.7',
                    'F12.7',
                ],
                'exceptions'  => [
                    'F16.6',
                    'F16.8',
                ],
                'symptoms'    => [
                    'required' => [
                        SymptomsEnum::ChronicPsychoticSymptoms,    // Хронические психотические симптомы
                        SymptomsEnum::PersonalityChanges,          // Изменения личности
                        SymptomsEnum::CognitiveImpairment,         // Когнитивные нарушения
                    ],
                    'relative' => [
                        SymptomsEnum::AffectiveDisturbances,       // Аффективные нарушения
                    ],
                ],
                'criteria'    => [3, 1],
            ],
            [
                'code'        => 'F16.8',
                'title'       => 'Другие психические и поведенческие расстройства, вызванные употреблением галлюциногенов',
                'description' => 'Другие психические расстройства, вызванные употреблением галлюциногенов, не классифицированные в других категориях.',
                'relatives'   => [
                    'F19.8',
                    'F11.8',
                    'F12.8',
                ],
                'exceptions'  => [
                    'F16.7',
                    'F16.9',
                ],
                'symptoms'    => [
                    'required' => [
                        SymptomsEnum::PsychiatricSymptoms,         // Психиатрические симптомы
                    ],
                    'relative' => [
                        SymptomsEnum::BehavioralChanges,           // Изменения в поведении
                    ],
                ],
                'criteria'    => [1, 1],
            ],
            [
                'code'        => 'F16.9',
                'title'       => 'Неуточненные психические и поведенческие расстройства, вызванные употреблением галлюциногенов',
                'description' => 'Неуточненные психические и поведенческие расстройства, вызванные употреблением галлюциногенов.',
                'relatives'   => [
                    'F19.9',
                    'F11.9',
                    'F12.9',
                ],
                'exceptions'  => [
                    'F16.8',
                    'F16.0',
                ],
                'symptoms'    => [
                    'required' => [
                        SymptomsEnum::UnspecifiedPsychiatricSymptoms,  // Неопределенные психиатрические симптомы
                    ],
                    'relative' => [
                        SymptomsEnum::BehavioralDisturbances,          // Поведенческие нарушения
                    ],
                ],
                'criteria'    => [1, 1],
            ],
            // F15.0
            [
                'code'        => 'F15.0',
                'title'       => 'Острая интоксикация стимуляторами (амфетамины и аналоги)',
                'description' => 'Острое состояние интоксикации стимуляторами, характеризующееся эйфорией, гиперактивностью, повышенной тревожностью и паранойей.',
                'relatives'   => [
                    'F19.0',
                    'F11.0',
                    'F14.0',
                ],
                'exceptions'  => [
                    'F15.1',
                    'F15.2',
                ],
                'symptoms'    => [
                    'required' => [
                        SymptomsEnum::Euphoria,                 // Эйфория
                        SymptomsEnum::Hyperactivity,            // Гиперактивность
                        SymptomsEnum::Anxiety,                  // Тревожность
                    ],
                    'relative' => [
                        SymptomsEnum::Paranoia,                 // Паранойя
                        SymptomsEnum::IncreasedHeartRate,       // Учащенное сердцебиение
                        SymptomsEnum::Sweating,                 // Потливость
                    ],
                ],
                'criteria'    => [2, 1],
            ],
            [
                'code'        => 'F15.1',
                'title'       => 'Пагубное употребление стимуляторов',
                'description' => 'Чрезмерное употребление стимуляторов, приводящее к физическим и психическим нарушениям без признаков зависимости.',
                'relatives'   => [
                    'F19.1',
                    'F11.1',
                    'F14.1',
                ],
                'exceptions'  => [
                    'F15.0',
                    'F15.2',
                ],
                'symptoms'    => [
                    'required' => [
                        SymptomsEnum::PhysicalHarm,             // Физический вред
                        SymptomsEnum::MentalHarm,               // Психический вред
                    ],
                    'relative' => [
                        SymptomsEnum::LackOfDependence,         // Отсутствие признаков зависимости
                    ],
                ],
                'criteria'    => [2, 1],
            ],
            [
                'code'        => 'F15.2',
                'title'       => 'Синдром зависимости от стимуляторов',
                'description' => 'Развитие зависимости от стимуляторов, сопровождающееся сильным желанием к употреблению, толерантностью и нарушением контроля.',
                'relatives'   => [
                    'F19.2',
                    'F11.2',
                    'F14.2',
                ],
                'exceptions'  => [
                    'F15.1',
                    'F15.3',
                ],
                'symptoms'    => [
                    'required' => [
                        SymptomsEnum::StrongDesireToUse,        // Сильное желание к употреблению
                        SymptomsEnum::DifficultyControllingUse, // Трудности с контролем
                        SymptomsEnum::ToleranceIncrease,        // Повышение толерантности
                    ],
                    'relative' => [
                        SymptomsEnum::NeglectOfAlternativeInterests, // Пренебрежение альтернативными интересами
                        SymptomsEnum::ContinuedUseDespiteHarm,       // Продолжение употребления несмотря на вред
                    ],
                ],
                'criteria'    => [3, 1],
            ],
            [
                'code'        => 'F15.3',
                'title'       => 'Абстинентное состояние при употреблении стимуляторов',
                'description' => 'Состояние, возникающее при прекращении употребления стимуляторов, включающее физические и психологические симптомы отмены.',
                'relatives'   => [
                    'F19.3',
                    'F11.3',
                    'F14.3',
                ],
                'exceptions'  => [
                    'F15.2',
                    'F15.4',
                ],
                'symptoms'    => [
                    'required' => [
                        SymptomsEnum::PhysicalWithdrawalSymptoms, // Физические симптомы отмены
                        SymptomsEnum::PsychologicalWithdrawalSymptoms, // Психические симптомы отмены
                    ],
                    'relative' => [
                        SymptomsEnum::Fatigue,                   // Усталость
                        SymptomsEnum::DepressedMood,             // Подавленное настроение
                    ],
                ],
                'criteria'    => [1, 1],
            ],
            [
                'code'        => 'F15.4',
                'title'       => 'Абстинентное состояние с делирием при употреблении стимуляторов',
                'description' => 'Абстинентное состояние, сопровождающееся делирием, включая спутанность сознания, галлюцинации и тревожность.',
                'relatives'   => [
                    'F19.4',
                    'F11.4',
                    'F14.4',
                ],
                'exceptions'  => [
                    'F15.3',
                    'F15.5',
                ],
                'symptoms'    => [
                    'required' => [
                        SymptomsEnum::Delirium,                 // Делирий
                        SymptomsEnum::Confusion,                // Спутанность сознания
                    ],
                    'relative' => [
                        SymptomsEnum::Hallucinations,           // Галлюцинации
                        SymptomsEnum::Agitation,                // Возбуждение
                    ],
                ],
                'criteria'    => [2, 1],
            ],
            [
                'code'        => 'F15.5',
                'title'       => 'Психотическое расстройство, вызванное употреблением стимуляторов',
                'description' => 'Психотические симптомы, вызванные употреблением стимуляторов, включая бред, галлюцинации и паранойю.',
                'relatives'   => [
                    'F19.5',
                    'F11.5',
                    'F14.5',
                ],
                'exceptions'  => [
                    'F15.4',
                    'F15.6',
                ],
                'symptoms'    => [
                    'required' => [
                        SymptomsEnum::Delusions,                // Бред
                        SymptomsEnum::Hallucinations,           // Галлюцинации
                    ],
                    'relative' => [
                        SymptomsEnum::Paranoia,                 // Паранойя
                        SymptomsEnum::AffectiveDisturbances,    // Аффективные нарушения
                    ],
                ],
                'criteria'    => [2, 1],
            ],
            [
                'code'        => 'F15.6',
                'title'       => 'Амнестический синдром, вызванный употреблением стимуляторов',
                'description' => 'Нарушение памяти, связанное с употреблением стимуляторов, характеризующееся трудностями в запоминании и воспоминании.',
                'relatives'   => [
                    'F19.6',
                    'F11.6',
                    'F14.6',
                ],
                'exceptions'  => [
                    'F15.5',
                    'F15.7',
                ],
                'symptoms'    => [
                    'required' => [
                        SymptomsEnum::SevereMemoryImpairment,   // Сильное нарушение памяти
                    ],
                    'relative' => [
                        SymptomsEnum::Confabulation,            // Конфабуляция
                    ],
                ],
                'criteria'    => [1, 1],
            ],
            [
                'code'        => 'F15.7',
                'title'       => 'Остаточные и поздно возникающие психотические расстройства при употреблении стимуляторов',
                'description' => 'Длительные психотические симптомы, сохраняющиеся после прекращения употребления стимуляторов.',
                'relatives'   => [
                    'F19.7',
                    'F11.7',
                    'F14.7',
                ],
                'exceptions'  => [
                    'F15.6',
                    'F15.8',
                ],
                'symptoms'    => [
                    'required' => [
                        SymptomsEnum::ChronicPsychoticSymptoms, // Хронические психотические симптомы
                        SymptomsEnum::PersonalityChanges,       // Изменения личности
                    ],
                    'relative' => [
                        SymptomsEnum::AffectiveDisturbances,    // Аффективные нарушения
                    ],
                ],
                'criteria'    => [2, 1],
            ],
            [
                'code'        => 'F15.8',
                'title'       => 'Другие психические и поведенческие расстройства, вызванные употреблением стимуляторов',
                'description' => 'Другие психические и поведенческие расстройства, вызванные употреблением стимуляторов, не классифицированные в других категориях.',
                'relatives'   => [
                    'F19.8',
                    'F11.8',
                    'F14.8',
                ],
                'exceptions'  => [
                    'F15.7',
                    'F15.9',
                ],
                'symptoms'    => [
                    'required' => [
                        SymptomsEnum::PsychiatricSymptoms,      // Психиатрические симптомы
                    ],
                    'relative' => [
                        SymptomsEnum::BehavioralChanges,        // Изменения в поведении
                    ],
                ],
                'criteria'    => [1, 1],
            ],
            [
                'code'        => 'F15.9',
                'title'       => 'Неуточненные психические и поведенческие расстройства, вызванные употреблением стимуляторов',
                'description' => 'Неуточненные психические и поведенческие расстройства, вызванные употреблением стимуляторов.',
                'relatives'   => [
                    'F19.9',
                    'F11.9',
                    'F14.9',
                ],
                'exceptions'  => [
                    'F15.8',
                    'F15.0',
                ],
                'symptoms'    => [
                    'required' => [
                        SymptomsEnum::UnspecifiedPsychiatricSymptoms,  // Неопределенные психиатрические симптомы
                    ],
                    'relative' => [
                        SymptomsEnum::BehavioralDisturbances,          // Поведенческие нарушения
                    ],
                ],
                'criteria'    => [1, 1],
            ],
            // F12.0
            [
                'code'        => 'F12.0',
                'title'       => 'Острая интоксикация',
                'description' => 'Состояние, вызванное применением психоактивного вещества, проявляющееся в нарушениях сознания, познавательной способности, восприятия, эмоций и поведения или других психофизиологических функций и реакций. Эти нарушения непосредственно связаны с острым фармакологическим действием вещества и через некоторое время полностью исчезают, за исключением случаев, когда имеют место повреждения тканей и другие осложнения. В числе осложнений могут быть травма, аспирация рвотных масс, делирий, кома, судороги. Характер осложнений зависит от фармакологического класса вещества и способа его введения. Острое опьянение при алкоголизме Bad trips (наркотическое опьянение) Алкогольное опьянение БДУ Патологическая интоксикация Расстройства в виде транса и одержимости при интоксикации, вызванной психоактивными веществами.',
                'relatives'   => [
                    'F12.1', 'F12.2',
                ],
                'exceptions'  => [
                    'F10.0', 'F11.0',
                ],
                'symptoms'    => [
                    'required' => [
                        SymptomsEnum::ImpairedConsciousness,
                        SymptomsEnum::CognitiveDecline,
                        SymptomsEnum::EmotionalInstability,
                    ],
                    'relative' => [
                        SymptomsEnum::Tremor,
                        SymptomsEnum::Seizures,
                        SymptomsEnum::Delirium,
                    ],
                ],
                'criteria'    => [3, 2],
            ],
            [
                'code'        => 'F12.0',
                'title'       => 'Психические и поведенческие расстройства вследствие употребления каннабиоидов: острая интоксикация',
                'description' => 'Острая интоксикация каннабиоидами, проявляющаяся нарушениями поведения и психического состояния.',
                'relatives'   => [
                    'F10.0',
                    'F11.0',
                    'F19.0',
                ],
                'exceptions'  => [
                    'F12.1',
                    'F12.2',
                ],
                'symptoms'    => [
                    'required' => [
                        SymptomsEnum::Euphoria,
                        SymptomsEnum::ImpairedJudgement,
                        SymptomsEnum::CoordinationProblems,
                    ],
                    'relative' => [
                        SymptomsEnum::Drowsiness,
                        SymptomsEnum::Anxiety,
                        SymptomsEnum::Paranoia,
                    ],
                ],
                'criteria'    => [2, 1],
            ],
            [
                'code'        => 'F12.1',
                'title'       => 'Психические и поведенческие расстройства вследствие употребления каннабиоидов: вредное употребление',
                'description' => 'Вредное употребление каннабиоидов, сопровождающееся ухудшением физического и психического здоровья.',
                'relatives'   => [
                    'F10.1',
                    'F11.1',
                    'F19.1',
                ],
                'exceptions'  => [
                    'F12.0',
                    'F12.2',
                ],
                'symptoms'    => [
                    'required' => [
                        SymptomsEnum::SocialWithdrawal,
                        SymptomsEnum::NeglectOfResponsibilities,
                        SymptomsEnum::PoorConcentration,
                    ],
                    'relative' => [
                        SymptomsEnum::Anxiety,
                        SymptomsEnum::ImpairedJudgement,
                    ],
                ],
                'criteria'    => [2, 1],
            ],
            [
                'code'        => 'F12.2',
                'title'       => 'Психические и поведенческие расстройства вследствие употребления каннабиоидов: синдром зависимости',
                'description' => 'Синдром зависимости от каннабиоидов, характеризующийся настойчивым желанием употреблять вещество и трудностями в контроле.',
                'relatives'   => [
                    'F10.2',
                    'F11.2',
                    'F19.2',
                ],
                'exceptions'  => [
                    'F12.1',
                    'F12.3',
                ],
                'symptoms'    => [
                    'required' => [
                        SymptomsEnum::Craving,
                        SymptomsEnum::ToleranceIncrease,
                        SymptomsEnum::WithdrawalSymptoms,
                    ],
                    'relative' => [
                        SymptomsEnum::SocialWithdrawal,
                        SymptomsEnum::ContinuedUseDespiteProblems,
                    ],
                ],
                'criteria'    => [3, 1],
            ],
            [
                'code'        => 'F12.3',
                'title'       => 'Психические и поведенческие расстройства вследствие употребления каннабиоидов: абстинентное состояние',
                'description' => 'Абстинентное состояние, возникающее после прекращения употребления каннабиоидов.',
                'relatives'   => [
                    'F10.3',
                    'F11.3',
                    'F19.3',
                ],
                'exceptions'  => [
                    'F12.0',
                    'F12.2',
                ],
                'symptoms'    => [
                    'required' => [
                        SymptomsEnum::Irritability,
                        SymptomsEnum::Sweating,
                        SymptomsEnum::Anxiety,
                    ],
                    'relative' => [
                        SymptomsEnum::Insomnia,
                        SymptomsEnum::Tremors,
                    ],
                ],
                'criteria'    => [2, 1],
            ],
            [
                'code'        => 'F12.4',
                'title'       => 'Психические и поведенческие расстройства вследствие употребления каннабиоидов: абстинентное состояние с делирием',
                'description' => 'Абстинентное состояние, связанное с употреблением каннабиоидов, сопровождающееся делирием.',
                'relatives'   => [
                    'F10.4',
                    'F11.4',
                    'F19.4',
                ],
                'exceptions'  => [
                    'F12.2',
                    'F12.3',
                ],
                'symptoms'    => [
                    'required' => [
                        SymptomsEnum::Disorientation,
                        SymptomsEnum::Hallucinations,
                        SymptomsEnum::Tremors,
                    ],
                    'relative' => [
                        SymptomsEnum::Sweating,
                        SymptomsEnum::Anxiety,
                        SymptomsEnum::Paranoia,
                    ],
                ],
                'criteria'    => [3, 1],
            ],
            [
                'code'        => 'F12.5',
                'title'       => 'Психические и поведенческие расстройства вследствие употребления каннабиоидов: психотическое расстройство',
                'description' => 'Психотическое расстройство, связанное с употреблением каннабиоидов, характеризующееся галлюцинациями и бредовыми идеями.',
                'relatives'   => [
                    'F10.5',
                    'F11.5',
                    'F19.5',
                ],
                'exceptions'  => [
                    'F12.3',
                    'F12.4',
                ],
                'symptoms'    => [
                    'required' => [
                        SymptomsEnum::Hallucinations,
                        SymptomsEnum::Delusions,
                        SymptomsEnum::Paranoia,
                    ],
                    'relative' => [
                        SymptomsEnum::Agitation,
                        SymptomsEnum::PoorConcentration,
                    ],
                ],
                'criteria'    => [3, 1],
            ],
            [
                'code'        => 'F12.6',
                'title'       => 'Психические и поведенческие расстройства вследствие употребления каннабиоидов: амнестический синдром',
                'description' => 'Амнестический синдром, вызванный злоупотреблением каннабиоидами, сопровождающийся когнитивными нарушениями и проблемами с памятью.',
                'relatives'   => [
                    'F10.6',
                    'F11.6',
                    'F19.6',
                ],
                'exceptions'  => [
                    'F12.0',
                    'F12.1',
                ],
                'symptoms'    => [
                    'required' => [
                        SymptomsEnum::MemoryProblems,
                        SymptomsEnum::PoorJudgement,
                        SymptomsEnum::Disorientation,
                    ],
                    'relative' => [
                        SymptomsEnum::SocialWithdrawal,
                        SymptomsEnum::ImpairedAttention,
                    ],
                ],
                'criteria'    => [2, 1],
            ],
            [
                'code'        => 'F12.7',
                'title'       => 'Психические и поведенческие расстройства вследствие употребления каннабиоидов: остаточные и отсроченные психотические расстройства',
                'description' => 'Психотические расстройства, возникающие после длительного употребления каннабиоидов, сохраняющиеся после прекращения употребления.',
                'relatives'   => [
                    'F10.7',
                    'F11.7',
                    'F19.7',
                ],
                'exceptions'  => [
                    'F12.0',
                    'F12.2',
                ],
                'symptoms'    => [
                    'required' => [
                        SymptomsEnum::Hallucinations,
                        SymptomsEnum::Delusions,
                        SymptomsEnum::SocialWithdrawal,
                    ],
                    'relative' => [
                        SymptomsEnum::PoorConcentration,
                        SymptomsEnum::Anxiety,
                    ],
                ],
                'criteria'    => [3, 1],
            ],
            // F14.0
            [
                'code'        => 'F14.0',
                'title'       => 'Психические и поведенческие расстройства вследствие употребления кокаина: острая интоксикация',
                'description' => 'Острая интоксикация кокаином, сопровождающаяся эйфорией, возбуждением и нарушением суждения.',
                'relatives'   => [
                    'F11.0',
                    'F13.0',
                    'F19.0',
                ],
                'exceptions'  => [
                    'F14.1',
                    'F14.2',
                ],
                'symptoms'    => [
                    'required' => [
                        SymptomsEnum::Euphoria,
                        SymptomsEnum::Agitation,
                        SymptomsEnum::ImpairedJudgement,
                    ],
                    'relative' => [
                        SymptomsEnum::Sweating,
                        SymptomsEnum::Paranoia,
                        SymptomsEnum::IncreasedHeartRate,
                    ],
                ],
                'criteria'    => [2, 1],
            ],
            [
                'code'        => 'F14.1',
                'title'       => 'Психические и поведенческие расстройства вследствие употребления кокаина: вредное употребление',
                'description' => 'Вредное употребление кокаина, характеризующееся социальными и физическими последствиями.',
                'relatives'   => [
                    'F11.1',
                    'F13.1',
                    'F19.1',
                ],
                'exceptions'  => [
                    'F14.0',
                    'F14.2',
                ],
                'symptoms'    => [
                    'required' => [
                        SymptomsEnum::NeglectOfResponsibilities,
                        SymptomsEnum::SocialWithdrawal,
                        SymptomsEnum::PoorConcentration,
                    ],
                    'relative' => [
                        SymptomsEnum::Anxiety,
                        SymptomsEnum::ImpairedJudgement,
                    ],
                ],
                'criteria'    => [2, 1],
            ],
            [
                'code'        => 'F14.2',
                'title'       => 'Психические и поведенческие расстройства вследствие употребления кокаина: синдром зависимости',
                'description' => 'Синдром зависимости от кокаина, проявляющийся непреодолимым влечением к употреблению вещества.',
                'relatives'   => [
                    'F11.2',
                    'F13.2',
                    'F19.2',
                ],
                'exceptions'  => [
                    'F14.1',
                    'F14.3',
                ],
                'symptoms'    => [
                    'required' => [
                        SymptomsEnum::Craving,
                        SymptomsEnum::ToleranceIncrease,
                        SymptomsEnum::ContinuedUseDespiteProblems,
                    ],
                    'relative' => [
                        SymptomsEnum::NeglectOfResponsibilities,
                        SymptomsEnum::SocialWithdrawal,
                    ],
                ],
                'criteria'    => [3, 1],
            ],
            [
                'code'        => 'F14.3',
                'title'       => 'Психические и поведенческие расстройства вследствие употребления кокаина: абстинентное состояние',
                'description' => 'Абстинентное состояние, вызванное прекращением употребления кокаина.',
                'relatives'   => [
                    'F11.3',
                    'F13.3',
                    'F19.3',
                ],
                'exceptions'  => [
                    'F14.0',
                    'F14.2',
                ],
                'symptoms'    => [
                    'required' => [
                        SymptomsEnum::Irritability,
                        SymptomsEnum::DepressedMood,
                        SymptomsEnum::Fatigue,
                    ],
                    'relative' => [
                        SymptomsEnum::IncreasedAppetite,
                        SymptomsEnum::Insomnia,
                    ],
                ],
                'criteria'    => [2, 1],
            ],
            [
                'code'        => 'F14.4',
                'title'       => 'Психические и поведенческие расстройства вследствие употребления кокаина: абстинентное состояние с делирием',
                'description' => 'Абстинентное состояние с делирием, развивающееся после прекращения употребления кокаина.',
                'relatives'   => [
                    'F11.4',
                    'F13.4',
                    'F19.4',
                ],
                'exceptions'  => [
                    'F14.2',
                    'F14.3',
                ],
                'symptoms'    => [
                    'required' => [
                        SymptomsEnum::Disorientation,
                        SymptomsEnum::Hallucinations,
                        SymptomsEnum::Tremors,
                    ],
                    'relative' => [
                        SymptomsEnum::Paranoia,
                        SymptomsEnum::Anxiety,
                    ],
                ],
                'criteria'    => [3, 1],
            ],
            [
                'code'        => 'F14.5',
                'title'       => 'Психические и поведенческие расстройства вследствие употребления кокаина: психотическое расстройство',
                'description' => 'Психотическое расстройство вследствие употребления кокаина, характеризующееся бредом и галлюцинациями.',
                'relatives'   => [
                    'F11.5',
                    'F13.5',
                    'F19.5',
                ],
                'exceptions'  => [
                    'F14.3',
                    'F14.4',
                ],
                'symptoms'    => [
                    'required' => [
                        SymptomsEnum::Hallucinations,
                        SymptomsEnum::Delusions,
                        SymptomsEnum::Paranoia,
                    ],
                    'relative' => [
                        SymptomsEnum::Agitation,
                        SymptomsEnum::ImpairedJudgement,
                    ],
                ],
                'criteria'    => [3, 1],
            ],
            [
                'code'        => 'F14.6',
                'title'       => 'Психические и поведенческие расстройства вследствие употребления кокаина: амнестический синдром',
                'description' => 'Амнестический синдром, вызванный злоупотреблением кокаином, сопровождающийся потерей памяти и когнитивными нарушениями.',
                'relatives'   => [
                    'F11.6',
                    'F13.6',
                    'F19.6',
                ],
                'exceptions'  => [
                    'F14.0',
                    'F14.1',
                ],
                'symptoms'    => [
                    'required' => [
                        SymptomsEnum::MemoryProblems,
                        SymptomsEnum::PoorJudgement,
                        SymptomsEnum::Disorientation,
                    ],
                    'relative' => [
                        SymptomsEnum::ImpairedAttention,
                        SymptomsEnum::SocialWithdrawal,
                    ],
                ],
                'criteria'    => [2, 1],
            ],
            [
                'code'        => 'F14.7',
                'title'       => 'Психические и поведенческие расстройства вследствие употребления кокаина: остаточные и отсроченные психотические расстройства',
                'description' => 'Психотические расстройства, возникающие после прекращения употребления кокаина, сохраняющиеся длительное время.',
                'relatives'   => [
                    'F11.7',
                    'F13.7',
                    'F19.7',
                ],
                'exceptions'  => [
                    'F14.0',
                    'F14.2',
                ],
                'symptoms'    => [
                    'required' => [
                        SymptomsEnum::Hallucinations,
                        SymptomsEnum::Delusions,
                        SymptomsEnum::SocialWithdrawal,
                    ],
                    'relative' => [
                        SymptomsEnum::PoorConcentration,
                        SymptomsEnum::Anxiety,
                    ],
                ],
                'criteria'    => [3, 1],
            ],
            // F18
            [
                'code'        => 'F18.0',
                'title'       => 'Психические и поведенческие расстройства вследствие употребления летучих растворителей: острая интоксикация',
                'description' => 'Острая интоксикация летучими растворителями, вызывающая эйфорию, головокружение, нарушение координации и рискованное поведение.',
                'relatives'   => [
                    'F19.0',
                    'F11.0',
                    'F15.0',
                ],
                'exceptions'  => [
                    'F18.1',
                    'F18.2',
                ],
                'symptoms'    => [
                    'required' => [
                        SymptomsEnum::Euphoria,
                        SymptomsEnum::Dizziness,
                        SymptomsEnum::ImpairedJudgement,
                    ],
                    'relative' => [
                        SymptomsEnum::Nausea,
                        SymptomsEnum::Disorientation,
                        SymptomsEnum::SlurredSpeech,
                    ],
                ],
                'criteria'    => [2, 1],
            ],
            [
                'code'        => 'F18.1',
                'title'       => 'Психические и поведенческие расстройства вследствие употребления летучих растворителей: вредное употребление',
                'description' => 'Вредное употребление летучих растворителей, вызывающее физические и социальные последствия.',
                'relatives'   => [
                    'F19.1',
                    'F11.1',
                    'F15.1',
                ],
                'exceptions'  => [
                    'F18.0',
                    'F18.2',
                ],
                'symptoms'    => [
                    'required' => [
                        SymptomsEnum::NeglectOfResponsibilities,
                        SymptomsEnum::SocialWithdrawal,
                        SymptomsEnum::IncreasedTolerance,
                    ],
                    'relative' => [
                        SymptomsEnum::Anxiety,
                        SymptomsEnum::MemoryProblems,
                    ],
                ],
                'criteria'    => [2, 1],
            ],
            [
                'code'        => 'F18.2',
                'title'       => 'Психические и поведенческие расстройства вследствие употребления летучих растворителей: синдром зависимости',
                'description' => 'Синдром зависимости от летучих растворителей, характеризующийся непреодолимым влечением к употреблению вещества.',
                'relatives'   => [
                    'F19.2',
                    'F11.2',
                    'F15.2',
                ],
                'exceptions'  => [
                    'F18.1',
                    'F18.3',
                ],
                'symptoms'    => [
                    'required' => [
                        SymptomsEnum::Craving,
                        SymptomsEnum::ToleranceIncrease,
                        SymptomsEnum::ContinuedUseDespiteProblems,
                    ],
                    'relative' => [
                        SymptomsEnum::NeglectOfResponsibilities,
                        SymptomsEnum::SocialWithdrawal,
                    ],
                ],
                'criteria'    => [3, 1],
            ],
            [
                'code'        => 'F18.3',
                'title'       => 'Психические и поведенческие расстройства вследствие употребления летучих растворителей: абстинентное состояние',
                'description' => 'Абстинентное состояние, возникающее при прекращении употребления летучих растворителей.',
                'relatives'   => [
                    'F19.3',
                    'F11.3',
                    'F15.3',
                ],
                'exceptions'  => [
                    'F18.0',
                    'F18.2',
                ],
                'symptoms'    => [
                    'required' => [
                        SymptomsEnum::Irritability,
                        SymptomsEnum::Fatigue,
                        SymptomsEnum::Nausea,
                    ],
                    'relative' => [
                        SymptomsEnum::Sweating,
                        SymptomsEnum::Headache,
                    ],
                ],
                'criteria'    => [2, 1],
            ],
            [
                'code'        => 'F18.4',
                'title'       => 'Психические и поведенческие расстройства вследствие употребления летучих растворителей: абстинентное состояние с делирием',
                'description' => 'Абстинентное состояние с делирием, вызванное прекращением употребления летучих растворителей.',
                'relatives'   => [
                    'F19.4',
                    'F11.4',
                    'F15.4',
                ],
                'exceptions'  => [
                    'F18.2',
                    'F18.3',
                ],
                'symptoms'    => [
                    'required' => [
                        SymptomsEnum::Disorientation,
                        SymptomsEnum::Hallucinations,
                        SymptomsEnum::Tremors,
                    ],
                    'relative' => [
                        SymptomsEnum::Paranoia,
                        SymptomsEnum::Anxiety,
                    ],
                ],
                'criteria'    => [3, 1],
            ],
            [
                'code'        => 'F18.5',
                'title'       => 'Психические и поведенческие расстройства вследствие употребления летучих растворителей: психотическое расстройство',
                'description' => 'Психотическое расстройство, вызванное употреблением летучих растворителей, сопровождающееся бредом и галлюцинациями.',
                'relatives'   => [
                    'F19.5',
                    'F11.5',
                    'F15.5',
                ],
                'exceptions'  => [
                    'F18.3',
                    'F18.4',
                ],
                'symptoms'    => [
                    'required' => [
                        SymptomsEnum::Hallucinations,
                        SymptomsEnum::Delusions,
                        SymptomsEnum::Paranoia,
                    ],
                    'relative' => [
                        SymptomsEnum::Agitation,
                        SymptomsEnum::ImpairedJudgement,
                    ],
                ],
                'criteria'    => [3, 1],
            ],
            [
                'code'        => 'F18.6',
                'title'       => 'Психические и поведенческие расстройства вследствие употребления летучих растворителей: амнестический синдром',
                'description' => 'Амнестический синдром, вызванный употреблением летучих растворителей, характеризующийся нарушениями памяти.',
                'relatives'   => [
                    'F19.6',
                    'F11.6',
                    'F15.6',
                ],
                'exceptions'  => [
                    'F18.0',
                    'F18.1',
                ],
                'symptoms'    => [
                    'required' => [
                        SymptomsEnum::MemoryProblems,
                        SymptomsEnum::PoorJudgement,
                        SymptomsEnum::Disorientation,
                    ],
                    'relative' => [
                        SymptomsEnum::ImpairedAttention,
                        SymptomsEnum::SocialWithdrawal,
                    ],
                ],
                'criteria'    => [2, 1],
            ],
            [
                'code'        => 'F18.7',
                'title'       => 'Психические и поведенческие расстройства вследствие употребления летучих растворителей: остаточные и отсроченные психотические расстройства',
                'description' => 'Психотические расстройства, сохраняющиеся после прекращения употребления летучих растворителей.',
                'relatives'   => [
                    'F19.7',
                    'F11.7',
                    'F15.7',
                ],
                'exceptions'  => [
                    'F18.0',
                    'F18.2',
                ],
                'symptoms'    => [
                    'required' => [
                        SymptomsEnum::Hallucinations,
                        SymptomsEnum::Delusions,
                        SymptomsEnum::SocialWithdrawal,
                    ],
                    'relative' => [
                        SymptomsEnum::PoorConcentration,
                        SymptomsEnum::Anxiety,
                    ],
                ],
                'criteria'    => [3, 1],
            ],
            [
                'code'        => 'F11.0',
                'title'       => 'Психические и поведенческие расстройства вследствие употребления опиоидов: острая интоксикация',
                'description' => 'Острая интоксикация опиоидами, сопровождающаяся эйфорией, седативным эффектом и угнетением дыхания.',
                'relatives'   => [
                    'F19.0',
                    'F15.0',
                    'F10.0',
                ],
                'exceptions'  => [
                    'F11.1',
                    'F11.2',
                ],
                'symptoms'    => [
                    'required' => [
                        SymptomsEnum::Euphoria,
                        SymptomsEnum::Drowsiness,
                        SymptomsEnum::RespiratoryDepression,
                    ],
                    'relative' => [
                        SymptomsEnum::Nausea,
                        SymptomsEnum::ImpairedJudgement,
                        SymptomsEnum::SlurredSpeech,
                    ],
                ],
                'criteria'    => [2, 1],
            ],
            [
                'code'        => 'F11.1',
                'title'       => 'Психические и поведенческие расстройства вследствие употребления опиоидов: вредное употребление',
                'description' => 'Вредное употребление опиоидов, приводящее к физическим и социальным последствиям.',
                'relatives'   => [
                    'F19.1',
                    'F15.1',
                    'F10.1',
                ],
                'exceptions'  => [
                    'F11.0',
                    'F11.2',
                ],
                'symptoms'    => [
                    'required' => [
                        SymptomsEnum::NeglectOfResponsibilities,
                        SymptomsEnum::SocialWithdrawal,
                        SymptomsEnum::IncreasedTolerance,
                    ],
                    'relative' => [
                        SymptomsEnum::Anxiety,
                        SymptomsEnum::MemoryProblems,
                    ],
                ],
                'criteria'    => [2, 1],
            ],
            [
                'code'        => 'F11.2',
                'title'       => 'Психические и поведенческие расстройства вследствие употребления опиоидов: синдром зависимости',
                'description' => 'Синдром зависимости от опиоидов, сопровождающийся сильной тягой к употреблению вещества и развитием толерантности.',
                'relatives'   => [
                    'F19.2',
                    'F15.2',
                    'F10.2',
                ],
                'exceptions'  => [
                    'F11.1',
                    'F11.3',
                ],
                'symptoms'    => [
                    'required' => [
                        SymptomsEnum::Craving,
                        SymptomsEnum::ToleranceIncrease,
                        SymptomsEnum::ContinuedUseDespiteProblems,
                    ],
                    'relative' => [
                        SymptomsEnum::NeglectOfResponsibilities,
                        SymptomsEnum::SocialWithdrawal,
                    ],
                ],
                'criteria'    => [3, 1],
            ],
            [
                'code'        => 'F11.3',
                'title'       => 'Психические и поведенческие расстройства вследствие употребления опиоидов: абстинентное состояние',
                'description' => 'Абстинентное состояние, возникающее при прекращении употребления опиоидов.',
                'relatives'   => [
                    'F19.3',
                    'F15.3',
                    'F10.3',
                ],
                'exceptions'  => [
                    'F11.0',
                    'F11.2',
                ],
                'symptoms'    => [
                    'required' => [
                        SymptomsEnum::Anxiety,
                        SymptomsEnum::Sweating,
                        SymptomsEnum::MusclePain,
                    ],
                    'relative' => [
                        SymptomsEnum::Nausea,
                        SymptomsEnum::Irritability,
                    ],
                ],
                'criteria'    => [2, 1],
            ],
            [
                'code'        => 'F11.4',
                'title'       => 'Психические и поведенческие расстройства вследствие употребления опиоидов: абстинентное состояние с делирием',
                'description' => 'Абстинентное состояние с делирием, возникающее при прекращении употребления опиоидов.',
                'relatives'   => [
                    'F19.4',
                    'F15.4',
                ],
                'exceptions'  => [
                    'F11.0',
                    'F11.3',
                ],
                'symptoms'    => [
                    'required' => [
                        SymptomsEnum::Delirium,
                        SymptomsEnum::Hallucinations,
                        SymptomsEnum::SevereAnxiety,
                    ],
                    'relative' => [
                        SymptomsEnum::Sweating,
                        SymptomsEnum::Tremors,
                    ],
                ],
                'criteria'    => [2, 1],
            ],
            [
                'code'        => 'F11.5',
                'title'       => 'Психические и поведенческие расстройства вследствие употребления опиоидов: психотическое расстройство',
                'description' => 'Психотическое расстройство вследствие употребления опиоидов, характеризующееся бредом или галлюцинациями.',
                'relatives'   => [
                    'F19.5',
                    'F15.5',
                ],
                'exceptions'  => [
                    'F11.2',
                    'F11.3',
                ],
                'symptoms'    => [
                    'required' => [
                        SymptomsEnum::Hallucinations,
                        SymptomsEnum::Delusions,
                    ],
                    'relative' => [
                        SymptomsEnum::Paranoia,
                        SymptomsEnum::Confusion,
                    ],
                ],
                'criteria'    => [2, 1],
            ],
            [
                'code'        => 'F11.6',
                'title'       => 'Психические и поведенческие расстройства вследствие употребления опиоидов: амнестический синдром',
                'description' => 'Амнестический синдром, вызванный употреблением опиоидов, характеризующийся нарушениями памяти.',
                'relatives'   => [
                    'F19.6',
                    'F15.6',
                ],
                'exceptions'  => [
                    'F11.0',
                    'F11.1',
                ],
                'symptoms'    => [
                    'required' => [
                        SymptomsEnum::MemoryProblems,
                        SymptomsEnum::Confusion,
                    ],
                    'relative' => [
                        SymptomsEnum::Disorientation,
                    ],
                ],
                'criteria'    => [1, 1],
            ],
            [
                'code'        => 'F11.7',
                'title'       => 'Психические и поведенческие расстройства вследствие употребления опиоидов: остаточное состояние и отсроченные психотические расстройства',
                'description' => 'Остаточное состояние с отсроченными психотическими расстройствами, вызванными употреблением опиоидов.',
                'relatives'   => [
                    'F19.7',
                    'F15.7',
                ],
                'exceptions'  => [
                    'F11.0',
                    'F11.5',
                ],
                'symptoms'    => [
                    'required' => [
                        SymptomsEnum::PsychoticFeatures,
                        SymptomsEnum::Depersonalization,
                    ],
                    'relative' => [
                        SymptomsEnum::Hallucinations,
                    ],
                ],
                'criteria'    => [1, 1],
            ],
            [
                'code'        => 'F11.8',
                'title'       => 'Психические и поведенческие расстройства вследствие употребления опиоидов: другие уточненные психические и поведенческие расстройства',
                'description' => 'Другие, уточненные психические и поведенческие расстройства, вызванные употреблением опиоидов.',
                'relatives'   => [
                    'F19.8',
                    'F15.8',
                ],
                'exceptions'  => [
                    'F11.0',
                    'F11.6',
                ],
                'symptoms'    => [
                    'required' => [
                        SymptomsEnum::MoodChanges,
                    ],
                    'relative' => [
                        SymptomsEnum::Anxiety,
                        SymptomsEnum::CognitiveImpairment,
                    ],
                ],
                'criteria'    => [1, 1],
            ],
            [
                'code'        => 'F11.9',
                'title'       => 'Психические и поведенческие расстройства вследствие употребления опиоидов: неуточненные психические и поведенческие расстройства',
                'description' => 'Неуточненные психические и поведенческие расстройства вследствие употребления опиоидов.',
                'relatives'   => [
                    'F19.9',
                    'F15.9',
                ],
                'exceptions'  => [
                    'F11.5',
                    'F11.8',
                ],
                'symptoms'    => [
                    'required' => [
                        SymptomsEnum::UnspecifiedSymptoms,
                    ],
                    'relative' => [
                        SymptomsEnum::Anxiety,
                        SymptomsEnum::MoodChanges,
                    ],
                ],
                'criteria'    => [1, 1],
            ],
            // F13.0
            [
                'code'        => 'F13.0',
                'title'       => 'Психические и поведенческие расстройства вследствие употребления седативных или снотворных средств: острая интоксикация',
                'description' => 'Острая интоксикация седативными или снотворными средствами, проявляющаяся сонливостью и угнетением центральной нервной системы.',
                'relatives'   => [
                    'F19.0',
                    'F11.0',
                    'F15.0',
                ],
                'exceptions'  => [
                    'F13.1',
                    'F13.2',
                ],
                'symptoms'    => [
                    'required' => [
                        SymptomsEnum::Drowsiness,
                        SymptomsEnum::SlurredSpeech,
                        SymptomsEnum::ImpairedCoordination,
                    ],
                    'relative' => [
                        SymptomsEnum::MemoryProblems,
                        SymptomsEnum::Irritability,
                        SymptomsEnum::RespiratoryDepression,
                    ],
                ],
                'criteria'    => [2, 1],
            ],
            [
                'code'        => 'F13.1',
                'title'       => 'Психические и поведенческие расстройства вследствие употребления седативных или снотворных средств: вредное употребление',
                'description' => 'Вредное употребление седативных или снотворных средств, приводящее к негативным последствиям для физического или психического здоровья.',
                'relatives'   => [
                    'F19.1',
                    'F11.1',
                    'F15.1',
                ],
                'exceptions'  => [
                    'F13.0',
                    'F13.2',
                ],
                'symptoms'    => [
                    'required' => [
                        SymptomsEnum::NeglectOfResponsibilities,
                        SymptomsEnum::MemoryProblems,
                        SymptomsEnum::ImpairedJudgement,
                    ],
                    'relative' => [
                        SymptomsEnum::SocialWithdrawal,
                        SymptomsEnum::Irritability,
                    ],
                ],
                'criteria'    => [2, 1],
            ],
            [
                'code'        => 'F13.2',
                'title'       => 'Психические и поведенческие расстройства вследствие употребления седативных или снотворных средств: синдром зависимости',
                'description' => 'Синдром зависимости от седативных или снотворных средств, включающий развитие толерантности и сильное желание употреблять вещество.',
                'relatives'   => [
                    'F19.2',
                    'F11.2',
                    'F15.2',
                ],
                'exceptions'  => [
                    'F13.1',
                    'F13.3',
                ],
                'symptoms'    => [
                    'required' => [
                        SymptomsEnum::Craving,
                        SymptomsEnum::ToleranceIncrease,
                        SymptomsEnum::ContinuedUseDespiteProblems,
                    ],
                    'relative' => [
                        SymptomsEnum::NeglectOfResponsibilities,
                        SymptomsEnum::SocialWithdrawal,
                    ],
                ],
                'criteria'    => [3, 1],
            ],
            [
                'code'        => 'F13.3',
                'title'       => 'Психические и поведенческие расстройства вследствие употребления седативных или снотворных средств: абстинентное состояние',
                'description' => 'Абстинентное состояние, возникающее при прекращении употребления седативных или снотворных средств.',
                'relatives'   => [
                    'F19.3',
                    'F11.3',
                    'F15.3',
                ],
                'exceptions'  => [
                    'F13.0',
                    'F13.2',
                ],
                'symptoms'    => [
                    'required' => [
                        SymptomsEnum::Anxiety,
                        SymptomsEnum::Tremors,
                        SymptomsEnum::Sweating,
                    ],
                    'relative' => [
                        SymptomsEnum::Irritability,
                        SymptomsEnum::Nausea,
                    ],
                ],
                'criteria'    => [2, 1],
            ],
            [
                'code'        => 'F13.4',
                'title'       => 'Психические и поведенческие расстройства вследствие употребления седативных или снотворных средств: абстинентное состояние с делирием',
                'description' => 'Абстинентное состояние с делирием, возникающее при прекращении употребления седативных или снотворных средств.',
                'relatives'   => [
                    'F19.4',
                    'F11.4',
                    'F15.4',
                ],
                'exceptions'  => [
                    'F13.0',
                    'F13.3',
                ],
                'symptoms'    => [
                    'required' => [
                        SymptomsEnum::Delirium,
                        SymptomsEnum::Hallucinations,
                        SymptomsEnum::SevereAnxiety,
                    ],
                    'relative' => [
                        SymptomsEnum::Sweating,
                        SymptomsEnum::Tremors,
                    ],
                ],
                'criteria'    => [2, 1],
            ],
            [
                'code'        => 'F13.5',
                'title'       => 'Психические и поведенческие расстройства вследствие употребления седативных или снотворных средств: психотическое расстройство',
                'description' => 'Психотическое расстройство вследствие употребления седативных или снотворных средств, включающее бред или галлюцинации.',
                'relatives'   => [
                    'F19.5',
                    'F11.5',
                    'F15.5',
                ],
                'exceptions'  => [
                    'F13.2',
                    'F13.3',
                ],
                'symptoms'    => [
                    'required' => [
                        SymptomsEnum::Hallucinations,
                        SymptomsEnum::Delusions,
                    ],
                    'relative' => [
                        SymptomsEnum::Paranoia,
                        SymptomsEnum::Confusion,
                    ],
                ],
                'criteria'    => [2, 1],
            ],
            [
                'code'        => 'F13.6',
                'title'       => 'Психические и поведенческие расстройства вследствие употребления седативных или снотворных средств: амнестический синдром',
                'description' => 'Амнестический синдром вследствие употребления седативных или снотворных средств, характеризующийся нарушениями памяти.',
                'relatives'   => [
                    'F19.6',
                    'F11.6',
                    'F15.6',
                ],
                'exceptions'  => [
                    'F13.0',
                    'F13.1',
                ],
                'symptoms'    => [
                    'required' => [
                        SymptomsEnum::MemoryProblems,
                        SymptomsEnum::Confusion,
                    ],
                    'relative' => [
                        SymptomsEnum::Disorientation,
                    ],
                ],
                'criteria'    => [1, 1],
            ],
            [
                'code'        => 'F13.7',
                'title'       => 'Психические и поведенческие расстройства вследствие употребления седативных или снотворных средств: остаточное состояние и отсроченные психотические расстройства',
                'description' => 'Остаточное состояние с отсроченными психотическими расстройствами, вызванными употреблением седативных или снотворных средств.',
                'relatives'   => [
                    'F19.7',
                    'F11.7',
                    'F15.7',
                ],
                'exceptions'  => [
                    'F13.0',
                    'F13.5',
                ],
                'symptoms'    => [
                    'required' => [
                        SymptomsEnum::PsychoticFeatures,
                        SymptomsEnum::Depersonalization,
                    ],
                    'relative' => [
                        SymptomsEnum::Hallucinations,
                    ],
                ],
                'criteria'    => [1, 1],
            ],
            [
                'code'        => 'F13.8',
                'title'       => 'Психические и поведенческие расстройства вследствие употребления седативных или снотворных средств: другие уточненные психические и поведенческие расстройства',
                'description' => 'Другие уточненные психические и поведенческие расстройства вследствие употребления седативных или снотворных средств.',
                'relatives'   => [
                    'F19.8',
                    'F11.8',
                    'F15.8',
                ],
                'exceptions'  => [
                    'F13.0',
                    'F13.6',
                ],
                'symptoms'    => [
                    'required' => [
                        SymptomsEnum::MoodChanges,
                    ],
                    'relative' => [
                        SymptomsEnum::Anxiety,
                        SymptomsEnum::CognitiveImpairment,
                    ],
                ],
                'criteria'    => [1, 1],
            ],
            [
                'code'        => 'F13.9',
                'title'       => 'Психические и поведенческие расстройства вследствие употребления седативных или снотворных средств: неуточненные психические и поведенческие расстройства',
                'description' => 'Неуточненные психические и поведенческие расстройства вследствие употребления седативных или снотворных средств.',
                'relatives'   => [
                    'F19.9',
                    'F11.9',
                    'F15.9',
                ],
                'exceptions'  => [
                    'F13.1',
                    'F13.2',
                ],
                'symptoms'    => [
                    'required' => [
                        SymptomsEnum::BehavioralChanges,
                    ],
                    'relative' => [
                        SymptomsEnum::CognitiveImpairment,
                    ],
                ],
                'criteria'    => [1, 1],
            ],

            // F17.0
            [
                'code'        => 'F17.0',
                'title'       => 'Психические и поведенческие расстройства вследствие употребления табака: острая интоксикация',
                'description' => 'Острая интоксикация табаком, проявляющаяся вегетативными симптомами и нервозностью.',
                'relatives'   => [
                    'F19.0',
                    'F12.0',
                    'F10.0',
                ],
                'exceptions'  => [
                    'F17.1',
                    'F17.2',
                ],
                'symptoms'    => [
                    'required' => [
                        SymptomsEnum::Nausea,
                        SymptomsEnum::Dizziness,
                        SymptomsEnum::Tachycardia,
                    ],
                    'relative' => [
                        SymptomsEnum::Anxiety,
                        SymptomsEnum::Restlessness,
                    ],
                ],
                'criteria'    => [2, 1],
            ],
            [
                'code'        => 'F17.1',
                'title'       => 'Психические и поведенческие расстройства вследствие употребления табака: вредное употребление',
                'description' => 'Вредное употребление табака, приводящее к повреждению физического здоровья, включая заболевания легких и сердечно-сосудистой системы.',
                'relatives'   => [
                    'F19.1',
                    'F12.1',
                    'F10.1',
                ],
                'exceptions'  => [
                    'F17.0',
                    'F17.2',
                ],
                'symptoms'    => [
                    'required' => [
                        SymptomsEnum::PersistentUseDespiteHarm,
                        SymptomsEnum::NeglectOfResponsibilities,
                    ],
                    'relative' => [
                        SymptomsEnum::Irritability,
                        SymptomsEnum::Insomnia,
                    ],
                ],
                'criteria'    => [2, 1],
            ],
            [
                'code'        => 'F17.2',
                'title'       => 'Психические и поведенческие расстройства вследствие употребления табака: синдром зависимости',
                'description' => 'Синдром зависимости от табака, проявляющийся сильной тягой и трудностями в прекращении употребления.',
                'relatives'   => [
                    'F19.2',
                    'F12.2',
                    'F10.2',
                ],
                'exceptions'  => [
                    'F17.0',
                    'F17.1',
                ],
                'symptoms'    => [
                    'required' => [
                        SymptomsEnum::Craving,
                        SymptomsEnum::ToleranceIncrease,
                        SymptomsEnum::WithdrawalSymptoms,
                    ],
                    'relative' => [
                        SymptomsEnum::NeglectOfResponsibilities,
                        SymptomsEnum::ContinuedUseDespiteProblems,
                    ],
                ],
                'criteria'    => [3, 1],
            ],
            [
                'code'        => 'F17.3',
                'title'       => 'Психические и поведенческие расстройства вследствие употребления табака: абстинентное состояние',
                'description' => 'Абстинентное состояние, возникающее при прекращении употребления табака.',
                'relatives'   => [
                    'F19.3',
                    'F12.3',
                    'F10.3',
                ],
                'exceptions'  => [
                    'F17.0',
                    'F17.2',
                ],
                'symptoms'    => [
                    'required' => [
                        SymptomsEnum::Anxiety,
                        SymptomsEnum::Irritability,
                        SymptomsEnum::DifficultyConcentrating,
                    ],
                    'relative' => [
                        SymptomsEnum::Insomnia,
                        SymptomsEnum::IncreasedAppetite,
                    ],
                ],
                'criteria'    => [2, 1],
            ],
            [
                'code'        => 'F17.4',
                'title'       => 'Психические и поведенческие расстройства вследствие употребления табака: абстинентное состояние с делирием',
                'description' => 'Абстинентное состояние с делирием, возникающее при прекращении употребления табака.',
                'relatives'   => [
                    'F19.4',
                    'F12.4',
                    'F10.4',
                ],
                'exceptions'  => [
                    'F17.0',
                    'F17.3',
                ],
                'symptoms'    => [
                    'required' => [
                        SymptomsEnum::Delirium,
                        SymptomsEnum::Confusion,
                        SymptomsEnum::Hallucinations,
                    ],
                    'relative' => [
                        SymptomsEnum::Agitation,
                        SymptomsEnum::SevereAnxiety,
                    ],
                ],
                'criteria'    => [2, 1],
            ],
            [
                'code'        => 'F17.5',
                'title'       => 'Психические и поведенческие расстройства вследствие употребления табака: психотическое расстройство',
                'description' => 'Психотическое расстройство вследствие употребления табака, характеризующееся бредом или галлюцинациями.',
                'relatives'   => [
                    'F19.5',
                    'F12.5',
                    'F10.5',
                ],
                'exceptions'  => [
                    'F17.3',
                    'F17.4',
                ],
                'symptoms'    => [
                    'required' => [
                        SymptomsEnum::Hallucinations,
                        SymptomsEnum::Paranoia,
                    ],
                    'relative' => [
                        SymptomsEnum::Delusions,
                        SymptomsEnum::SevereAnxiety,
                    ],
                ],
                'criteria'    => [2, 1],
            ],
            [
                'code'        => 'F17.6',
                'title'       => 'Психические и поведенческие расстройства вследствие употребления табака: амнестический синдром',
                'description' => 'Амнестический синдром вследствие употребления табака, проявляющийся нарушениями памяти.',
                'relatives'   => [
                    'F19.6',
                    'F12.6',
                    'F10.6',
                ],
                'exceptions'  => [
                    'F17.0',
                    'F17.1',
                ],
                'symptoms'    => [
                    'required' => [
                        SymptomsEnum::MemoryProblems,
                        SymptomsEnum::Confusion,
                    ],
                    'relative' => [
                        SymptomsEnum::Disorientation,
                    ],
                ],
                'criteria'    => [1, 1],
            ],
            [
                'code'        => 'F17.7',
                'title'       => 'Психические и поведенческие расстройства вследствие употребления табака: остаточное состояние и отсроченные психотические расстройства',
                'description' => 'Остаточное состояние и отсроченные психотические расстройства, вызванные употреблением табака.',
                'relatives'   => [
                    'F19.7',
                    'F12.7',
                    'F10.7',
                ],
                'exceptions'  => [
                    'F17.3',
                    'F17.5',
                ],
                'symptoms'    => [
                    'required' => [
                        SymptomsEnum::PsychoticFeatures,
                        SymptomsEnum::MoodChanges,
                    ],
                    'relative' => [
                        SymptomsEnum::Hallucinations,
                    ],
                ],
                'criteria'    => [1, 1],
            ],
            [
                'code'        => 'F17.8',
                'title'       => 'Психические и поведенческие расстройства вследствие употребления табака: другие уточненные психические и поведенческие расстройства',
                'description' => 'Другие уточненные психические и поведенческие расстройства вследствие употребления табака.',
                'relatives'   => [
                    'F19.8',
                    'F12.8',
                    'F10.8',
                ],
                'exceptions'  => [
                    'F17.1',
                    'F17.2',
                ],
                'symptoms'    => [
                    'required' => [
                        SymptomsEnum::MoodChanges,
                    ],
                    'relative' => [
                        SymptomsEnum::Anxiety,
                        SymptomsEnum::CognitiveImpairment,
                    ],
                ],
                'criteria'    => [1, 1],
            ],
            [
                'code'        => 'F17.9',
                'title'       => 'Психические и поведенческие расстройства вследствие употребления табака: неуточненные психические и поведенческие расстройства',
                'description' => 'Неуточненные психические и поведенческие расстройства вследствие употребления табака.',
                'relatives'   => [
                    'F19.9',
                    'F12.9',
                    'F10.9',
                ],
                'exceptions'  => [
                    'F17.2',
                    'F17.3',
                ],
                'symptoms'    => [
                    'required' => [
                        SymptomsEnum::BehavioralChanges,
                    ],
                    'relative' => [
                        SymptomsEnum::CognitiveImpairment,
                    ],
                ],
                'criteria'    => [1, 1],
            ],
            // F66.0
            [
                'code'        => 'F66.0',
                'title'       => 'Психосексуальное развитие: неуверенность в гендерной идентичности',
                'description' => 'Неуверенность или сомнения в своей гендерной идентичности, которые вызывают дискомфорт.',
                'relatives'   => [
                    'F66.8',
                    'F66.9',
                ],
                'exceptions'  => [
                    'F64.0',
                    'F64.1',
                ],
                'symptoms'    => [
                    'required' => [
                        SymptomsEnum::IdentityConfusion,
                        SymptomsEnum::Anxiety,
                    ],
                    'relative' => [
                        SymptomsEnum::SocialWithdrawal,
                        SymptomsEnum::DepressedMood,
                    ],
                ],
                'criteria'    => [1, 1],
            ],
            [
                'code'        => 'F66.1',
                'title'       => 'Эго-дистоническая сексуальная ориентация',
                'description' => 'Человек испытывает дискомфорт из-за своей сексуальной ориентации, которая не соответствует его представлениям о себе.',
                'relatives'   => [
                    'F66.8',
                    'F66.9',
                ],
                'exceptions'  => [
                    'F64.0',
                    'F64.2',
                ],
                'symptoms'    => [
                    'required' => [
                        SymptomsEnum::Anxiety,
                        SymptomsEnum::DepressedMood,
                    ],
                    'relative' => [
                        SymptomsEnum::SocialWithdrawal,
                        SymptomsEnum::LowSelfEsteem,
                    ],
                ],
                'criteria'    => [1, 1],
            ],
            [
                'code'        => 'F66.2',
                'title'       => 'Расстройство половой зрелости',
                'description' => 'Затруднения или задержка в формировании половой зрелости, вызывающие дистресс.',
                'relatives'   => [
                    'F66.8',
                    'F66.9',
                ],
                'exceptions'  => [
                    'F64.1',
                    'F64.2',
                ],
                'symptoms'    => [
                    'required' => [
                        SymptomsEnum::Anxiety,
                        SymptomsEnum::MoodChanges,
                    ],
                    'relative' => [
                        SymptomsEnum::SocialWithdrawal,
                        SymptomsEnum::BehavioralChanges,
                    ],
                ],
                'criteria'    => [1, 1],
            ],
            [
                'code'        => 'F66.8',
                'title'       => 'Другие психосексуальные расстройства',
                'description' => 'Другие уточненные психосексуальные расстройства, влияющие на самооценку или идентичность.',
                'relatives'   => [
                    'F66.0',
                    'F66.1',
                ],
                'exceptions'  => [
                    'F64.0',
                    'F64.2',
                ],
                'symptoms'    => [
                    'required' => [
                        SymptomsEnum::IdentityConfusion,
                    ],
                    'relative' => [
                        SymptomsEnum::DepressedMood,
                        SymptomsEnum::Anxiety,
                    ],
                ],
                'criteria'    => [1, 1],
            ],
            [
                'code'        => 'F66.9',
                'title'       => 'Неуточненное психосексуальное расстройство',
                'description' => 'Неуточненные психосексуальные расстройства, вызывающие значительный дистресс.',
                'relatives'   => [
                    'F66.0',
                    'F66.1',
                ],
                'exceptions'  => [
                    'F64.0',
                    'F64.1',
                ],
                'symptoms'    => [
                    'required' => [
                        SymptomsEnum::Anxiety,
                    ],
                    'relative' => [
                        SymptomsEnum::SocialWithdrawal,
                    ],
                ],
                'criteria'    => [1, 1],
            ],

            // F60
            [
                'code'        => 'F60.0',
                'title'       => 'Параноидное расстройство личности',
                'description' => 'Патологическая подозрительность, чрезмерная чувствительность к неудачам и обидам, тенденция к интерпретации нейтральных или дружелюбных действий окружающих как враждебных.',
                'relatives'   => [
                    'F60.8', 'F60.9'
                ],
                'exceptions'  => [
                    'F20.0', 'F22.0'
                ],
                'symptoms'    => [
                    'required' => [
                        SymptomsEnum::Suspiciousness,
                        SymptomsEnum::Distrust,
                    ],
                    'relative' => [
                        SymptomsEnum::Aggressiveness,
                        SymptomsEnum::SensitivityToCriticism,
                        SymptomsEnum::TendencyToHoldGrudges
                    ]
                ],
                'criteria'    => [2, 1]
            ],
            [
                'code'        => 'F60.1',
                'title'       => 'Шизоидное расстройство личности',
                'description' => 'Эмоциональная холодность, социальная отстраненность и ограниченный диапазон выражения эмоций.',
                'relatives'   => [
                    'F20.0', 'F60.8'
                ],
                'exceptions'  => [
                    'F25.0', 'F21.0'
                ],
                'symptoms'    => [
                    'required' => [
                        SymptomsEnum::EmotionalColdness,
                        SymptomsEnum::SocialIsolation,
                    ],
                    'relative' => [
                        SymptomsEnum::IndifferenceToPraise,
                        SymptomsEnum::PreferenceForSolitaryActivities
                    ]
                ],
                'criteria'    => [2, 1]
            ],
            [
                'code'        => 'F60.2',
                'title'       => 'Диссоциальное расстройство личности',
                'description' => 'Пренебрежение к социальным нормам и чувствам других людей, агрессивное поведение и отсутствие чувства вины.',
                'relatives'   => [
                    'F60.0', 'F60.8'
                ],
                'exceptions'  => [
                    'F31.1', 'F32.2'
                ],
                'symptoms'    => [
                    'required' => [
                        SymptomsEnum::Aggressiveness,
                        SymptomsEnum::LackOfGuilt,
                    ],
                    'relative' => [
                        SymptomsEnum::Impulsivity,
                        SymptomsEnum::Deceitfulness
                    ]
                ],
                'criteria'    => [2, 1]
            ],
            [
                'code'        => 'F60.3',
                'title'       => 'Эмоционально неустойчивое расстройство личности',
                'description' => 'Колебания настроения, импульсивность и трудности в контроле эмоций и поведения.',
                'relatives'   => [
                    'F60.8', 'F60.9'
                ],
                'exceptions'  => [
                    'F31.8', 'F41.0'
                ],
                'symptoms'    => [
                    'required' => [
                        SymptomsEnum::MoodInstability,
                        SymptomsEnum::Impulsivity,
                    ],
                    'relative' => [
                        SymptomsEnum::Irritability,
                        SymptomsEnum::DifficultyInRelationships
                    ]
                ],
                'criteria'    => [2, 1]
            ],
            [
                'code'        => 'F60.4',
                'title'       => 'Истерическое расстройство личности',
                'description' => 'Эмоциональная неустойчивость, стремление привлекать внимание, демонстративное поведение.',
                'relatives'   => [
                    'F60.8', 'F60.9'
                ],
                'exceptions'  => [
                    'F40.0', 'F31.8'
                ],
                'symptoms'    => [
                    'required' => [
                        SymptomsEnum::AttentionSeekingBehavior,
                        SymptomsEnum::ExaggeratedEmotions,
                    ],
                    'relative' => [
                        SymptomsEnum::Suggestibility,
                        SymptomsEnum::ShallowEmotionalExpression
                    ]
                ],
                'criteria'    => [2, 1]
            ],
            [
                'code'        => 'F60.5',
                'title'       => 'Ананкастное расстройство личности (обсессивно-компульсивное)',
                'description' => 'Чрезмерная забота о порядке, перфекционизм и стремление к контролю над собой и окружающими.',
                'relatives'   => [
                    'F60.8', 'F60.9'
                ],
                'exceptions'  => [
                    'F42.0', 'F48.0'
                ],
                'symptoms'    => [
                    'required' => [
                        SymptomsEnum::Perfectionism,
                        SymptomsEnum::NeedForControl,
                    ],
                    'relative' => [
                        SymptomsEnum::RigidThinking,
                        SymptomsEnum::ReluctanceToDelegate
                    ]
                ],
                'criteria'    => [2, 1]
            ],
            [
                'code'        => 'F60.6',
                'title'       => 'Тревожное расстройство личности (избегающее)',
                'description' => 'Чувство неполноценности, страх критики и избегание социальных взаимодействий.',
                'relatives'   => [
                    'F40.1', 'F60.8'
                ],
                'exceptions'  => [
                    'F40.0', 'F41.1'
                ],
                'symptoms'    => [
                    'required' => [
                        SymptomsEnum::FearOfCriticism,
                        SymptomsEnum::SocialWithdrawal,
                    ],
                    'relative' => [
                        SymptomsEnum::LowSelfEsteem,
                        SymptomsEnum::SensitivityToRejection
                    ]
                ],
                'criteria'    => [2, 1]
            ],
            [
                'code'        => 'F60.7',
                'title'       => 'Зависимое расстройство личности',
                'description' => 'Сильная потребность в заботе со стороны других людей, подчиняемость, зависимость от других.',
                'relatives'   => [
                    'F60.8', 'F60.9'
                ],
                'exceptions'  => [
                    'F32.8', 'F31.1'
                ],
                'symptoms'    => [
                    'required' => [
                        SymptomsEnum::NeedForReassurance,
                        SymptomsEnum::DifficultyMakingDecisions,
                    ],
                    'relative' => [
                        SymptomsEnum::FearOfAbandonment,
                        SymptomsEnum::SubmissiveBehavior
                    ]
                ],
                'criteria'    => [2, 1]
            ],
            [
                'code'        => 'F60.8',
                'title'       => 'Другие специфические расстройства личности',
                'description' => 'Другие специфические расстройства личности, которые не подходят под другие категории.',
                'relatives'   => [
                    'F60.0', 'F60.9'
                ],
                'exceptions'  => [
                    'F60.3', 'F31.8'
                ],
                'symptoms'    => [
                    'required' => [
                        SymptomsEnum::AtypicalBehaviorPatterns,
                    ],
                    'relative' => [
                        SymptomsEnum::Impulsivity,
                        SymptomsEnum::MoodInstability
                    ]
                ],
                'criteria'    => [1, 1]
            ],
            [
                'code'        => 'F60.9',
                'title'       => 'Неуточненное расстройство личности',
                'description' => 'Расстройство личности, которое не соответствует конкретным диагностическим категориям.',
                'relatives'   => [
                    'F60.8', 'F60.0'
                ],
                'exceptions'  => [
                    'F60.1', 'F32.1'
                ],
                'symptoms'    => [
                    'required' => [
                        SymptomsEnum::BehavioralDeviations,
                    ],
                    'relative' => [
                        SymptomsEnum::MoodInstability
                    ]
                ],
                'criteria'    => [1, 1]
            ],

            // F61
            [
                'code'        => 'F61',
                'title'       => 'Смешанные и другие расстройства личности',
                'description' => 'Характеризуется сочетанием черт нескольких расстройств личности, без выраженного доминирования одного из них. Симптомы могут включать признаки параноидного, тревожного, диссоциального и других расстройств личности.',
                'relatives'   => [
                    'F60.0', 'F60.1', 'F60.3', 'F60.6', 'F60.9'
                ],
                'exceptions'  => [
                    'F62.0', 'F62.1'
                ],
                'symptoms'    => [
                    'required' => [
                        SymptomsEnum::MoodInstability,
                        SymptomsEnum::Impulsivity,
                    ],
                    'relative' => [
                        SymptomsEnum::Suspiciousness,
                        SymptomsEnum::SocialWithdrawal,
                        SymptomsEnum::AttentionSeekingBehavior,
                        SymptomsEnum::FearOfCriticism,
                        SymptomsEnum::SensitivityToRejection
                    ]
                ],
                'criteria'    => [2, 2]
            ],

            // F62
            [
                'code'        => 'F62.0',
                'title'       => 'Хронические изменения личности после катастрофы',
                'description' => 'Состояние, при котором произошли стойкие изменения личности после пережитой катастрофы, проявляющиеся в тревожности, гипервозбудимости и социальной изоляции.',
                'relatives'   => [
                    'F43.1'
                ],
                'exceptions'  => [
                    'F60.0', 'F60.1', 'F60.2'
                ],
                'symptoms'    => [
                    'required' => [
                        SymptomsEnum::PersistentAnxiety,
                        SymptomsEnum::EmotionalNumbness,
                    ],
                    'relative' => [
                        SymptomsEnum::Hypervigilance,
                        SymptomsEnum::SocialWithdrawal,
                        SymptomsEnum::Depression,
                        SymptomsEnum::AngerOutbursts,
                    ]
                ],
                'criteria'    => [2, 2]
            ],
            [
                'code'        => 'F62.1',
                'title'       => 'Хронические изменения личности вследствие психического заболевания',
                'description' => 'Длительные изменения в личности, возникающие после тяжелых психических заболеваний, включая эмоциональную нестабильность и социальные трудности.',
                'relatives'   => [
                    'F20', 'F25'
                ],
                'exceptions'  => [
                    'F60.0', 'F60.5', 'F60.6'
                ],
                'symptoms'    => [
                    'required' => [
                        SymptomsEnum::EmotionalInstability,
                        SymptomsEnum::ImpairedSocialFunctioning,
                    ],
                    'relative' => [
                        SymptomsEnum::Anxiety,
                        SymptomsEnum::Depression,
                        SymptomsEnum::ReducedEmpathy,
                        SymptomsEnum::SocialWithdrawal,
                    ]
                ],
                'criteria'    => [2, 2]
            ],
            // F63
            [
                'code'        => 'F63.0',
                'title'       => 'Патологическое влечение к азартным играм',
                'description' => 'Неудержимое влечение к азартным играм, сопровождающееся потерей контроля и социальными проблемами.',
                'relatives'   => [
                    'F60.7'
                ],
                'exceptions'  => [
                    'F61', 'F62.1'
                ],
                'symptoms'    => [
                    'required' => [
                        SymptomsEnum::ImpulseControlProblems,
                        SymptomsEnum::PreoccupationWithGambling,
                    ],
                    'relative' => [
                        SymptomsEnum::Anxiety,
                        SymptomsEnum::Depression,
                        SymptomsEnum::FinancialProblems,
                        SymptomsEnum::SocialConflict,
                    ]
                ],
                'criteria'    => [2, 2]
            ],
            [
                'code'        => 'F63.1',
                'title'       => 'Патологическое влечение к пиромании',
                'description' => 'Повторяющееся влечение к поджигательству, сопровождающееся удовольствием и облегчением от самого процесса поджога.',
                'relatives'   => [
                    'F60.7'
                ],
                'exceptions'  => [
                    'F62.0', 'F61'
                ],
                'symptoms'    => [
                    'required' => [
                        SymptomsEnum::CompulsiveFireSetting,
                        SymptomsEnum::PleasureFromFireSetting,
                    ],
                    'relative' => [
                        SymptomsEnum::ImpulseControlProblems,
                        SymptomsEnum::Anxiety,
                        SymptomsEnum::SocialConflict,
                        SymptomsEnum::Depression,
                    ]
                ],
                'criteria'    => [2, 1]
            ],
            [
                'code'        => 'F63.2',
                'title'       => 'Патологическое влечение к клептомании',
                'description' => 'Импульсивное влечение к краже, часто без материальной выгоды, сопровождающееся чувством облегчения.',
                'relatives'   => [
                    'F60.7'
                ],
                'exceptions'  => [
                    'F61', 'F62.1'
                ],
                'symptoms'    => [
                    'required' => [
                        SymptomsEnum::CompulsiveStealing,
                        SymptomsEnum::PleasureFromStealing,
                    ],
                    'relative' => [
                        SymptomsEnum::ImpulseControlProblems,
                        SymptomsEnum::Anxiety,
                        SymptomsEnum::Depression,
                        SymptomsEnum::SocialConflict,
                    ]
                ],
                'criteria'    => [2, 1]
            ],
            [
                'code'        => 'F63.3',
                'title'       => 'Трихотилломания',
                'description' => 'Повторяющееся вырывание волос, приводящее к заметной потере волос и связанным с этим страданиям.',
                'relatives'   => [
                    'F42.3'
                ],
                'exceptions'  => [
                    'F61', 'F62.1'
                ],
                'symptoms'    => [
                    'required' => [
                        SymptomsEnum::HairPulling,
                        SymptomsEnum::SenseOfReliefAfterPulling,
                    ],
                    'relative' => [
                        SymptomsEnum::ImpulseControlProblems,
                        SymptomsEnum::Anxiety,
                        SymptomsEnum::SocialConflict,
                        SymptomsEnum::Depression,
                    ]
                ],
                'criteria'    => [2, 1]
            ],
            [
                'code'        => 'F63.8',
                'title'       => 'Другие расстройства влечений',
                'description' => 'Прочие нарушения влечений, включая склонность к определенным компульсивным действиям, которые не подпадают под более конкретные диагнозы.',
                'relatives'   => [
                    'F60.7', 'F42.3'
                ],
                'exceptions'  => [
                    'F62.0', 'F61'
                ],
                'symptoms'    => [
                    'required' => [
                        SymptomsEnum::ImpulseControlProblems,
                        SymptomsEnum::CompulsiveBehavior,
                    ],
                    'relative' => [
                        SymptomsEnum::Anxiety,
                        SymptomsEnum::SocialConflict,
                        SymptomsEnum::Depression,
                        SymptomsEnum::PersistentDistress,
                    ]
                ],
                'criteria'    => [2, 1]
            ],
            [
                'code'        => 'F63.9',
                'title'       => 'Неуточненное расстройство влечений',
                'description' => 'Нарушение влечений, которое не соответствует более специфическим критериям, но вызывает значительное ухудшение социального функционирования.',
                'relatives'   => [
                    'F60.7', 'F62.1'
                ],
                'exceptions'  => [
                    'F62.0', 'F61'
                ],
                'symptoms'    => [
                    'required' => [
                        SymptomsEnum::ImpulseControlProblems,
                        SymptomsEnum::DistressFromImpulses,
                    ],
                    'relative' => [
                        SymptomsEnum::Anxiety,
                        SymptomsEnum::SocialConflict,
                        SymptomsEnum::Depression,
                        SymptomsEnum::CompulsiveBehavior,
                    ]
                ],
                'criteria'    => [2, 1]
            ],
            // F64
            [
                'code'        => 'F64.0',
                'title'       => 'Транссексуализм',
                'description' => 'Стойкое желание принадлежать к противоположному полу, часто сопровождающееся стремлением изменить свое тело медицинским путем.',
                'relatives'   => [
                    'F64.1', 'F66.0'
                ],
                'exceptions'  => [
                    'F65.1', 'F65.2'
                ],
                'symptoms'    => [
                    'required' => [
                        SymptomsEnum::DesireToChangeSex,
                        SymptomsEnum::DiscomfortWithBiologicalSex,
                    ],
                    'relative' => [
                        SymptomsEnum::SeekingMedicalTransition,
                        SymptomsEnum::SocialDysphoria,
                        SymptomsEnum::PersistentGenderDysphoria,
                    ]
                ],
                'criteria'    => [2, 1]
            ],
            [
                'code'        => 'F64.1',
                'title'       => 'Трансвестизм двойной роли',
                'description' => 'Частое переодевание в одежду противоположного пола, не сопровождающееся желанием изменить пол.',
                'relatives'   => [
                    'F64.0', 'F64.2'
                ],
                'exceptions'  => [
                    'F65.1', 'F65.0'
                ],
                'symptoms'    => [
                    'required' => [
                        SymptomsEnum::CrossDressing,
                        SymptomsEnum::AbsenceOfDesireForSexChange,
                    ],
                    'relative' => [
                        SymptomsEnum::PleasureFromCrossDressing,
                        SymptomsEnum::OccasionalGenderDysphoria,
                        SymptomsEnum::SocialDysphoria,
                    ]
                ],
                'criteria'    => [2, 1]
            ],
            [
                'code'        => 'F64.2',
                'title'       => 'Расстройство половой идентичности в детском возрасте',
                'description' => 'Проявление гендерной дисфории у детей, выражающееся в дискомфорте по поводу биологического пола и желании принадлежать к противоположному полу.',
                'relatives'   => [
                    'F64.0', 'F64.1'
                ],
                'exceptions'  => [
                    'F65.0', 'F65.1'
                ],
                'symptoms'    => [
                    'required' => [
                        SymptomsEnum::DiscomfortWithBiologicalSex,
                        SymptomsEnum::DesireToBeOppositeSex,
                    ],
                    'relative' => [
                        SymptomsEnum::SocialIsolation,
                        SymptomsEnum::GenderDysphoria,
                        SymptomsEnum::PersistentGenderDiscomfort,
                    ]
                ],
                'criteria'    => [2, 1]
            ],
            [
                'code'        => 'F64.8',
                'title'       => 'Другие расстройства половой идентичности',
                'description' => 'Прочие состояния гендерной дисфории, которые не подпадают под более специфические категории.',
                'relatives'   => [
                    'F64.0', 'F66.0'
                ],
                'exceptions'  => [
                    'F65.1', 'F65.2'
                ],
                'symptoms'    => [
                    'required' => [
                        SymptomsEnum::GenderDysphoria,
                        SymptomsEnum::DesireForAlternativeGenderIdentity,
                    ],
                    'relative' => [
                        SymptomsEnum::PersistentDiscomfortWithSex,
                        SymptomsEnum::SocialDysphoria,
                        SymptomsEnum::OccasionalCrossDressing,
                    ]
                ],
                'criteria'    => [2, 1]
            ],
            [
                'code'        => 'F64.9',
                'title'       => 'Неуточненное расстройство половой идентичности',
                'description' => 'Неопределенные или недостаточно выраженные проявления гендерной дисфории.',
                'relatives'   => [
                    'F64.8', 'F66.1'
                ],
                'exceptions'  => [
                    'F65.0', 'F65.1'
                ],
                'symptoms'    => [
                    'required' => [
                        SymptomsEnum::GenderDysphoria,
                    ],
                    'relative' => [
                        SymptomsEnum::SocialDysphoria,
                        SymptomsEnum::OccasionalGenderDiscomfort,
                        SymptomsEnum::DesireToChangeGenderRoles,
                    ]
                ],
                'criteria'    => [1, 1]
            ],
            // F65
            [
                'code'        => 'F65.0',
                'title'       => 'Фетишизм',
                'description' => 'Преимущественное сексуальное влечение к неодушевленным предметам, обычно с целью достижения сексуального возбуждения.',
                'relatives'   => [
                    'F65.1', 'F65.4'
                ],
                'exceptions'  => [
                    'F64.1', 'F66.1'
                ],
                'symptoms'    => [
                    'required' => [
                        SymptomsEnum::SexualArousalFromObjects,
                        SymptomsEnum::PersistentFocusOnFetish,
                    ],
                    'relative' => [
                        SymptomsEnum::DisruptionInSocialFunctioning,
                        SymptomsEnum::RepeatedFetishisticBehavior,
                        SymptomsEnum::LossOfInterestInOtherActivities,
                    ]
                ],
                'criteria'    => [2, 1]
            ],
            [
                'code'        => 'F65.1',
                'title'       => 'Фетишистский трансвестизм',
                'description' => 'Сексуальное возбуждение, вызванное ношением одежды противоположного пола, часто с целью достижения оргазма.',
                'relatives'   => [
                    'F64.1', 'F65.0'
                ],
                'exceptions'  => [
                    'F66.1', 'F64.2'
                ],
                'symptoms'    => [
                    'required' => [
                        SymptomsEnum::CrossDressing,
                        SymptomsEnum::SexualArousalFromCrossDressing,
                    ],
                    'relative' => [
                        SymptomsEnum::LossOfInterestInOtherActivities,
                        SymptomsEnum::RepetitiveBehavior,
                        SymptomsEnum::SocialIsolation,
                    ]
                ],
                'criteria'    => [2, 1]
            ],
            [
                'code'        => 'F65.2',
                'title'       => 'Эксгибиционизм',
                'description' => 'Сексуальное удовлетворение, получаемое от публичного обнажения половых органов.',
                'relatives'   => [
                    'F65.3', 'F65.9'
                ],
                'exceptions'  => [
                    'F66.0', 'F64.1'
                ],
                'symptoms'    => [
                    'required' => [
                        SymptomsEnum::ExhibitionisticBehavior,
                        SymptomsEnum::SexualArousalFromExhibitionism,
                    ],
                    'relative' => [
                        SymptomsEnum::ImpulsiveBehavior,
                        SymptomsEnum::LossOfSocialBoundaries,
                        SymptomsEnum::SocialConflict,
                    ]
                ],
                'criteria'    => [2, 1]
            ],
            [
                'code'        => 'F65.3',
                'title'       => 'Вуайеризм',
                'description' => 'Сексуальное удовлетворение, получаемое от наблюдения за обнаженными людьми или людьми, занимающимися сексом.',
                'relatives'   => [
                    'F65.2', 'F65.9'
                ],
                'exceptions'  => [
                    'F64.2', 'F66.1'
                ],
                'symptoms'    => [
                    'required' => [
                        SymptomsEnum::VoyeuristicBehavior,
                        SymptomsEnum::SexualArousalFromVoyeurism,
                    ],
                    'relative' => [
                        SymptomsEnum::ImpulsiveBehavior,
                        SymptomsEnum::LossOfSocialBoundaries,
                        SymptomsEnum::SocialIsolation,
                    ]
                ],
                'criteria'    => [2, 1]
            ],
            [
                'code'        => 'F65.4',
                'title'       => 'Садомазохизм',
                'description' => 'Сексуальное удовлетворение, получаемое от боли или унижения других людей (садизм) или от получения боли и унижения (мазохизм).',
                'relatives'   => [
                    'F65.9'
                ],
                'exceptions'  => [
                    'F64.0', 'F66.0'
                ],
                'symptoms'    => [
                    'required' => [
                        SymptomsEnum::SadisticBehavior,
                        SymptomsEnum::MasochisticBehavior,
                    ],
                    'relative' => [
                        SymptomsEnum::ImpulsiveBehavior,
                        SymptomsEnum::LossOfSocialBoundaries,
                        SymptomsEnum::SocialConflict,
                    ]
                ],
                'criteria'    => [2, 1]
            ],
            [
                'code'        => 'F65.8',
                'title'       => 'Другие расстройства сексуального предпочтения',
                'description' => 'Нарушения сексуального предпочтения, включающие необычные или социально неприемлемые сексуальные наклонности.',
                'relatives'   => [
                    'F65.9'
                ],
                'exceptions'  => [
                    'F64.0', 'F66.0'
                ],
                'symptoms'    => [
                    'required' => [
                        SymptomsEnum::UnusualSexualPreference,
                        SymptomsEnum::PersistentFocusOnPreference,
                    ],
                    'relative' => [
                        SymptomsEnum::DisruptionInSocialFunctioning,
                        SymptomsEnum::RepeatedBehavior,
                        SymptomsEnum::SocialIsolation,
                    ]
                ],
                'criteria'    => [2, 1]
            ],
            [
                'code'        => 'F65.9',
                'title'       => 'Неуточненное расстройство сексуального предпочтения',
                'description' => 'Нарушение сексуального предпочтения, не соответствующее более специфическим категориям.',
                'relatives'   => [
                    'F65.0', 'F65.1'
                ],
                'exceptions'  => [
                    'F64.1', 'F66.1'
                ],
                'symptoms'    => [
                    'required' => [
                        SymptomsEnum::SexualArousalIssues,
                    ],
                    'relative' => [
                        SymptomsEnum::SocialConflict,
                        SymptomsEnum::ImpulsiveBehavior,
                        SymptomsEnum::RepetitiveBehavior,
                    ]
                ],
                'criteria'    => [1, 1]
            ],
            // F69
            [
                'code'        => 'F69',
                'title'       => 'Неуточненные расстройства личности и поведения у взрослых',
                'description' => 'Расстройства личности и поведения, которые не соответствуют другим определенным категориям и имеют неопределенные или плохо выраженные симптомы.',
                'relatives'   => [
                    'F60.9', 'F68.8'
                ],
                'exceptions'  => [
                    'F20.0', 'F70.0'
                ],
                'symptoms'    => [
                    'required' => [
                        SymptomsEnum::BehavioralAberrations,
                        SymptomsEnum::SocialWithdrawal,
                    ],
                    'relative' => [
                        SymptomsEnum::PoorJudgement,
                        SymptomsEnum::Anxiety,
                        SymptomsEnum::InterpersonalConflicts,
                    ]
                ],
                'criteria'    => [2, 1]
            ],
            // F07
            [
                'code'        => 'F07.0',
                'title'       => 'Органическое расстройство личности',
                'description' => 'Изменение личности, обусловленное органическими нарушениями головного мозга, характеризующееся выраженными изменениями в характере и поведении.',
                'relatives'   => [
                    'F60.2', 'F06.9'
                ],
                'exceptions'  => [
                    'F20.0', 'F10.5'
                ],
                'symptoms'    => [
                    'required' => [
                        SymptomsEnum::PersonalityChange,
                        SymptomsEnum::Impulsivity,
                        SymptomsEnum::PoorJudgement,
                    ],
                    'relative' => [
                        SymptomsEnum::SocialWithdrawal,
                        SymptomsEnum::EmotionalInstability,
                        SymptomsEnum::Aggression,
                    ]
                ],
                'criteria'    => [3, 1]
            ],
            [
                'code'        => 'F07.1',
                'title'       => 'Постэнцефалитический синдром',
                'description' => 'Нарушение поведения и когнитивных функций, возникающее после перенесенного энцефалита.',
                'relatives'   => [
                    'F06.8', 'F07.8'
                ],
                'exceptions'  => [
                    'F10.5', 'F30.0'
                ],
                'symptoms'    => [
                    'required' => [
                        SymptomsEnum::MemoryProblems,
                        SymptomsEnum::PoorConcentration,
                    ],
                    'relative' => [
                        SymptomsEnum::Anxiety,
                        SymptomsEnum::DepressiveMood,
                        SymptomsEnum::Fatigue,
                    ]
                ],
                'criteria'    => [2, 1]
            ],
            [
                'code'        => 'F07.2',
                'title'       => 'Посттравматическое расстройство мозга',
                'description' => 'Расстройство, развивающееся в результате черепно-мозговой травмы, с нарушением когнитивных функций и изменения в поведении.',
                'relatives'   => [
                    'F06.8', 'F07.8'
                ],
                'exceptions'  => [
                    'F20.0', 'F30.2'
                ],
                'symptoms'    => [
                    'required' => [
                        SymptomsEnum::MemoryProblems,
                        SymptomsEnum::PoorJudgement,
                    ],
                    'relative' => [
                        SymptomsEnum::Aggression,
                        SymptomsEnum::Irritability,
                        SymptomsEnum::DepressiveMood,
                    ]
                ],
                'criteria'    => [2, 1]
            ],
            [
                'code'        => 'F07.8',
                'title'       => 'Другие органические расстройства личности и поведения',
                'description' => 'Другие специфические расстройства личности и поведения, связанные с органическими поражениями головного мозга.',
                'relatives'   => [
                    'F06.8', 'F07.0'
                ],
                'exceptions'  => [
                    'F10.5', 'F20.0'
                ],
                'symptoms'    => [
                    'required' => [
                        SymptomsEnum::PersonalityChange,
                        SymptomsEnum::EmotionalInstability,
                    ],
                    'relative' => [
                        SymptomsEnum::Impulsivity,
                        SymptomsEnum::SocialWithdrawal,
                        SymptomsEnum::PoorJudgement,
                    ]
                ],
                'criteria'    => [2, 1]
            ],
            [
                'code'        => 'F07.9',
                'title'       => 'Неуточненные органические расстройства личности и поведения',
                'description' => 'Расстройства личности и поведения, связанные с органическими поражениями головного мозга, не имеющие четкой клинической картины.',
                'relatives'   => [
                    'F07.0', 'F07.8'
                ],
                'exceptions'  => [
                    'F20.0', 'F10.5'
                ],
                'symptoms'    => [
                    'required' => [
                        SymptomsEnum::UnspecifiedPersonalityChanges,
                    ],
                    'relative' => [
                        SymptomsEnum::PoorJudgement,
                        SymptomsEnum::SocialWithdrawal,
                        SymptomsEnum::EmotionalInstability,
                    ]
                ],
                'criteria'    => [1, 1]
            ],
            // F33
            [
                'code'        => 'F33.0',
                'title'       => 'Рекуррентное депрессивное расстройство, текущий эпизод легкой степени',
                'description' => 'Эпизод депрессии легкой степени, который является частью рекуррентного депрессивного расстройства.',
                'relatives'   => [
                    'F32.0', 'F33.1'
                ],
                'exceptions'  => [
                    'F30.1', 'F31.3'
                ],
                'symptoms'    => [
                    'required' => [
                        SymptomsEnum::DepressedMood,
                        SymptomsEnum::Anhedonia,
                    ],
                    'relative' => [
                        SymptomsEnum::Fatigue,
                        SymptomsEnum::PoorConcentration,
                        SymptomsEnum::AppetiteChanges,
                    ]
                ],
                'criteria'    => [2, 1]
            ],
            [
                'code'        => 'F33.1',
                'title'       => 'Рекуррентное депрессивное расстройство, текущий эпизод умеренной степени',
                'description' => 'Депрессивное расстройство, характеризующееся подавленным настроением, потерей интереса и энергии.',
                'relatives'   => [
                    'F33.0', 'F33.2', 'F32.1'
                ],
                'exceptions'  => [
                    'F31.2', 'F31.3'
                ],
                'symptoms'    => [
                    'required' => [
                        SymptomsEnum::Anhedonia,
                        SymptomsEnum::Fatigue,
                        SymptomsEnum::PoorConcentration,
                    ],
                    'relative' => [
                        SymptomsEnum::Insomnia,
                        SymptomsEnum::Guilt,
                        SymptomsEnum::ThoughtsOfDeath,
                        SymptomsEnum::AppetiteChanges,
                    ]
                ],
                'criteria'    => [3, 1]
            ],
            [
                'code'        => 'F33.2',
                'title'       => 'Рекуррентное депрессивное расстройство, текущий эпизод тяжелой степени без психотических симптомов',
                'description' => 'Текущий эпизод тяжелой депрессии, не сопровождающийся психотическими симптомами.',
                'relatives'   => [
                    'F32.2', 'F33.1'
                ],
                'exceptions'  => [
                    'F31.1', 'F31.5'
                ],
                'symptoms'    => [
                    'required' => [
                        SymptomsEnum::DepressedMood,
                        SymptomsEnum::Fatigue,
                        SymptomsEnum::ThoughtsOfDeath,
                        SymptomsEnum::Anhedonia,
                    ],
                    'relative' => [
                        SymptomsEnum::PoorConcentration,
                        SymptomsEnum::Guilt,
                        SymptomsEnum::PsychomotorRetardation,
                    ]
                ],
                'criteria'    => [4, 1]
            ],
            [
                'code'        => 'F33.3',
                'title'       => 'Рекуррентное депрессивное расстройство, текущий эпизод тяжелой степени с психотическими симптомами',
                'description' => 'Эпизод тяжелой депрессии, сопровождающийся психотическими симптомами, такими как бред или галлюцинации.',
                'relatives'   => [
                    'F32.3', 'F20.0'
                ],
                'exceptions'  => [
                    'F30.0', 'F30.2'
                ],
                'symptoms'    => [
                    'required' => [
                        SymptomsEnum::DepressedMood,
                        SymptomsEnum::Fatigue,
                        SymptomsEnum::ThoughtsOfDeath,
                        SymptomsEnum::Hallucinations,
                        SymptomsEnum::Delusions,
                    ],
                    'relative' => [
                        SymptomsEnum::PoorConcentration,
                        SymptomsEnum::Guilt,
                        SymptomsEnum::PsychomotorRetardation,
                    ]
                ],
                'criteria'    => [5, 1]
            ],
            [
                'code'        => 'F33.4',
                'title'       => 'Рекуррентное депрессивное расстройство, в состоянии ремиссии',
                'description' => 'Пациент находится в состоянии ремиссии, не проявляет выраженных депрессивных симптомов.',
                'relatives'   => [
                    'F33.3', 'F34.1'
                ],
                'exceptions'  => [
                    'F32.0', 'F31.1'
                ],
                'symptoms'    => [
                    'required' => [
                        SymptomsEnum::RemissionState,
                    ],
                    'relative' => []
                ],
                'criteria'    => [1, 0]
            ],
            // F34
            [
                'code'        => 'F34.0',
                'title'       => 'Циклотимия',
                'description' => 'Хроническое расстройство настроения, характеризующееся колебаниями между легкими депрессивными и гипоманиакальными эпизодами.',
                'relatives'   => [
                    'F30.0', 'F31.0'
                ],
                'exceptions'  => [
                    'F32.1', 'F33.0'
                ],
                'symptoms'    => [
                    'required' => [
                        SymptomsEnum::MoodSwings,
                        SymptomsEnum::MildDepression,
                        SymptomsEnum::HypomanicEpisodes,
                    ],
                    'relative' => [
                        SymptomsEnum::Fatigue,
                        SymptomsEnum::PoorConcentration,
                        SymptomsEnum::Irritability,
                    ]
                ],
                'criteria'    => [3, 1]
            ],
            [
                'code'        => 'F34.1',
                'title'       => 'Дистимия',
                'description' => 'Хроническое депрессивное расстройство легкой степени, длящееся не менее двух лет.',
                'relatives'   => [
                    'F32.0', 'F33.1'
                ],
                'exceptions'  => [
                    'F30.1', 'F31.3'
                ],
                'symptoms'    => [
                    'required' => [
                        SymptomsEnum::ChronicDepression,
                        SymptomsEnum::Fatigue,
                    ],
                    'relative' => [
                        SymptomsEnum::PoorConcentration,
                        SymptomsEnum::Insomnia,
                        SymptomsEnum::Anxiety,
                    ]
                ],
                'criteria'    => [2, 1]
            ],
            [
                'code'        => 'F34.8',
                'title'       => 'Другие стойкие расстройства настроения (аффективные)',
                'description' => 'Другие длительные нарушения настроения, не классифицируемые как циклотимия или дистимия.',
                'relatives'   => [
                    'F34.0', 'F34.1'
                ],
                'exceptions'  => [
                    'F30.2', 'F32.2'
                ],
                'symptoms'    => [
                    'required' => [
                        SymptomsEnum::MoodDisturbance,
                    ],
                    'relative' => [
                        SymptomsEnum::Fatigue,
                        SymptomsEnum::Irritability,
                    ]
                ],
                'criteria'    => [1, 1]
            ],
            [
                'code'        => 'F34.9',
                'title'       => 'Неуточненные стойкие расстройства настроения',
                'description' => 'Нарушения настроения, длящиеся длительное время, но не соответствующие точной классификации.',
                'relatives'   => [
                    'F34.8', 'F33.9'
                ],
                'exceptions'  => [
                    'F30.1', 'F31.0'
                ],
                'symptoms'    => [
                    'required' => [
                        SymptomsEnum::MoodDisturbance,
                    ],
                    'relative' => [
                        SymptomsEnum::Fatigue,
                        SymptomsEnum::Anxiety,
                    ]
                ],
                'criteria'    => [1, 1]
            ],
            // F39
            [
                'code'        => 'F39',
                'title'       => 'Неуточненное расстройство настроения (аффективное)',
                'description' => 'Нарушение настроения, при котором не удается определить конкретный тип аффективного расстройства.',
                'relatives'   => [
                    'F30.9', 'F31.9', 'F32.9', 'F34.9'
                ],
                'exceptions'  => [
                    'F33.0', 'F33.1', 'F33.2'
                ],
                'symptoms'    => [
                    'required' => [
                        SymptomsEnum::MoodDisturbance,
                    ],
                    'relative' => [
                        SymptomsEnum::Anxiety,
                        SymptomsEnum::PoorConcentration,
                        SymptomsEnum::Fatigue,
                    ]
                ],
                'criteria'    => [1, 1]
            ],
            // F91
            [
                'code'        => 'F91.0',
                'title'       => 'Расстройство поведения, ограниченное рамками семьи',
                'description' => 'Расстройство поведения, проявляющееся исключительно в рамках семьи и в поведении с близкими.',
                'relatives'   => [
                    'F91.1', 'F91.2'
                ],
                'exceptions'  => [
                    'F90.0', 'F92.0'
                ],
                'symptoms'    => [
                    'required' => [
                        SymptomsEnum::Aggressiveness,
                        SymptomsEnum::Defiance,
                    ],
                    'relative' => [
                        SymptomsEnum::Irritability,
                        SymptomsEnum::Impulsivity,
                        SymptomsEnum::Hostility,
                    ]
                ],
                'criteria'    => [2, 1]
            ],
            [
                'code'        => 'F91.1',
                'title'       => 'Расстройство поведения, не ограниченное рамками семьи',
                'description' => 'Расстройство поведения, проявляющееся вне рамок семьи, включая социальные взаимодействия и контакты.',
                'relatives'   => [
                    'F91.0', 'F91.3'
                ],
                'exceptions'  => [
                    'F90.1', 'F92.0'
                ],
                'symptoms'    => [
                    'required' => [
                        SymptomsEnum::Aggressiveness,
                        SymptomsEnum::Defiance,
                        SymptomsEnum::DelinquentBehavior,
                    ],
                    'relative' => [
                        SymptomsEnum::Irritability,
                        SymptomsEnum::Lying,
                        SymptomsEnum::Impulsivity,
                    ]
                ],
                'criteria'    => [3, 1]
            ],
            [
                'code'        => 'F91.2',
                'title'       => 'Расстройство поведения с нарушением социального взаимодействия',
                'description' => 'Расстройство поведения, связанное с нарушением социальных норм и значительными трудностями в социальном взаимодействии.',
                'relatives'   => [
                    'F91.1', 'F92.8'
                ],
                'exceptions'  => [
                    'F90.0', 'F92.9'
                ],
                'symptoms'    => [
                    'required' => [
                        SymptomsEnum::Aggressiveness,
                        SymptomsEnum::PoorSocialInteraction,
                    ],
                    'relative' => [
                        SymptomsEnum::Irritability,
                        SymptomsEnum::Impulsivity,
                        SymptomsEnum::Lying,
                    ]
                ],
                'criteria'    => [2, 1]
            ],
            [
                'code'        => 'F91.3',
                'title'       => 'Расстройство поведения, неуточненное',
                'description' => 'Нарушение поведения, которое не удается отнести к конкретной категории, но характеризуется агрессивностью и нарушением норм.',
                'relatives'   => [
                    'F91.0', 'F91.2'
                ],
                'exceptions'  => [
                    'F92.8', 'F92.9'
                ],
                'symptoms'    => [
                    'required' => [
                        SymptomsEnum::Aggressiveness,
                        SymptomsEnum::RuleBreakingBehavior,
                    ],
                    'relative' => [
                        SymptomsEnum::Irritability,
                        SymptomsEnum::Defiance,
                        SymptomsEnum::Hostility,
                    ]
                ],
                'criteria'    => [2, 1]
            ],
            [
                'code'        => 'F94.0',
                'title'       => 'Изменение социального функционирования в детском возрасте, начинающееся в раннем возрасте',
                'description' => 'Нарушение, характеризующееся устойчивыми трудностями в социальных взаимодействиях и общении, проявляющимися с раннего детства.',
                'relatives'   => [
                    'F80.2', 'F84.5'
                ],
                'exceptions'  => [
                    'F90.0', 'F93.8'
                ],
                'symptoms'    => [
                    'required' => [
                        SymptomsEnum::DifficultyInSocialInteraction,
                        SymptomsEnum::CommunicationImpairment,
                    ],
                    'relative' => [
                        SymptomsEnum::PoorEyeContact,
                        SymptomsEnum::LackOfSocialResponse,
                        SymptomsEnum::AttachmentIssues,
                    ]
                ],
                'criteria'    => [2, 1]
            ],
            [
                'code'        => 'F94.1',
                'title'       => 'Реактивное расстройство привязанности в детском возрасте',
                'description' => 'Реактивное расстройство привязанности, проявляющееся у детей, часто вследствие пренебрежения и отсутствия надежной привязанности в раннем возрасте.',
                'relatives'   => [
                    'F94.2', 'F93.9'
                ],
                'exceptions'  => [
                    'F84.9', 'F91.1'
                ],
                'symptoms'    => [
                    'required' => [
                        SymptomsEnum::InhibitedBehavior,
                        SymptomsEnum::DifficultyFormingAttachments,
                    ],
                    'relative' => [
                        SymptomsEnum::SocialWithdrawal,
                        SymptomsEnum::LackOfResponseToComforting,
                        SymptomsEnum::EmotionalDysregulation,
                    ]
                ],
                'criteria'    => [2, 1]
            ],
            [
                'code'        => 'F94.2',
                'title'       => 'Расстройство привязанности в детском возрасте с дисинфицированностью',
                'description' => 'Расстройство привязанности у детей, проявляющееся непривычно дружелюбным поведением с незнакомцами и дисинфицированностью в социальных контактах.',
                'relatives'   => [
                    'F91.0', 'F94.1'
                ],
                'exceptions'  => [
                    'F84.5', 'F93.9'
                ],
                'symptoms'    => [
                    'required' => [
                        SymptomsEnum::OverFriendliness,
                        SymptomsEnum::LackOfSelectivityInAttachments,
                    ],
                    'relative' => [
                        SymptomsEnum::ImpairedSocialJudgement,
                        SymptomsEnum::LackOfStrangerAnxiety,
                        SymptomsEnum::AttentionSeekingBehavior,
                    ]
                ],
                'criteria'    => [2, 1]
            ],
            [
                'code'        => 'F94.8',
                'title'       => 'Другие нарушения социального функционирования с началом в детском возрасте',
                'description' => 'Другие нарушения социального функционирования, которые начинаются в детстве, но не подходят под более специфические расстройства привязанности.',
                'relatives'   => [
                    'F94.0', 'F94.1'
                ],
                'exceptions'  => [
                    'F84.9', 'F93.8'
                ],
                'symptoms'    => [
                    'required' => [
                        SymptomsEnum::DifficultyInSocialInteraction,
                    ],
                    'relative' => [
                        SymptomsEnum::AttachmentIssues,
                        SymptomsEnum::InappropriateSocialBehavior,
                        SymptomsEnum::AnxietyInSocialSituations,
                    ]
                ],
                'criteria'    => [1, 1]
            ],
            [
                'code'        => 'F94.9',
                'title'       => 'Нарушение социального функционирования с началом в детском возрасте, неуточненное',
                'description' => 'Неуточненное нарушение социального функционирования, которое начинается в детстве и влияет на межличностные отношения и социальные взаимодействия.',
                'relatives'   => [
                    'F94.1', 'F94.8'
                ],
                'exceptions'  => [
                    'F84.5', 'F91.1'
                ],
                'symptoms'    => [
                    'required' => [
                        SymptomsEnum::DifficultyInSocialInteraction,
                    ],
                    'relative' => [
                        SymptomsEnum::AttachmentIssues,
                        SymptomsEnum::SocialWithdrawal,
                        SymptomsEnum::InhibitedBehavior,
                    ]
                ],
                'criteria'    => [1, 1]
            ],
            // F43
            [
                'code'        => 'F43.0',
                'title'       => 'Острая реакция на стресс',
                'description' => 'Временная реакция на чрезвычайно угрожающее или катастрофическое событие, проявляющаяся нарушением адаптации и поведения.',
                'relatives'   => [
                    'F41.0', 'F41.1'
                ],
                'exceptions'  => [
                    'F44.0', 'F48.0'
                ],
                'symptoms'    => [
                    'required' => [
                        SymptomsEnum::AcuteAnxiety,
                        SymptomsEnum::Disorientation,
                        SymptomsEnum::Agitation,
                    ],
                    'relative' => [
                        SymptomsEnum::EmotionalNumbness,
                        SymptomsEnum::SocialWithdrawal,
                        SymptomsEnum::SleepDisturbance,
                        SymptomsEnum::Hypervigilance,
                    ]
                ],
                'criteria'    => [2, 1]
            ],
            [
                'code'        => 'F43.1',
                'title'       => 'Посттравматическое стрессовое расстройство (ПТСР)',
                'description' => 'Хроническое нарушение, возникающее после травмирующего события, сопровождаемое повторными воспоминаниями о событии и избеганием связанных с ним стимулов.',
                'relatives'   => [
                    'F41.2', 'F48.1'
                ],
                'exceptions'  => [
                    'F33.1', 'F34.1'
                ],
                'symptoms'    => [
                    'required' => [
                        SymptomsEnum::IntrusiveMemories,
                        SymptomsEnum::AvoidanceBehavior,
                        SymptomsEnum::Hyperarousal,
                    ],
                    'relative' => [
                        SymptomsEnum::Nightmares,
                        SymptomsEnum::EmotionalNumbness,
                        SymptomsEnum::SocialWithdrawal,
                        SymptomsEnum::Irritability,
                    ]
                ],
                'criteria'    => [2, 2]
            ],
            [
                'code'        => 'F43.2',
                'title'       => 'Расстройство адаптации',
                'description' => 'Эмоциональная или поведенческая реакция на идентифицируемый стрессовый фактор, превышающая нормальную адаптацию.',
                'relatives'   => [
                    'F32.0', 'F48.8'
                ],
                'exceptions'  => [
                    'F34.1', 'F41.0'
                ],
                'symptoms'    => [
                    'required' => [
                        SymptomsEnum::DepressedMood,
                        SymptomsEnum::Anxiety,
                    ],
                    'relative' => [
                        SymptomsEnum::PoorConcentration,
                        SymptomsEnum::SleepDisturbance,
                        SymptomsEnum::SocialWithdrawal,
                        SymptomsEnum::Irritability,
                    ]
                ],
                'criteria'    => [1, 1]
            ],
            [
                'code'        => 'F43.8',
                'title'       => 'Другие реакции на тяжелый стресс',
                'description' => 'Нарушения, возникающие в результате тяжелого стресса, которые не соответствуют другим специфическим диагнозам.',
                'relatives'   => [
                    'F48.8', 'F93.9'
                ],
                'exceptions'  => [
                    'F44.9', 'F45.9'
                ],
                'symptoms'    => [
                    'required' => [
                        SymptomsEnum::StressResponse,
                    ],
                    'relative' => [
                        SymptomsEnum::EmotionalNumbness,
                        SymptomsEnum::SleepDisturbance,
                        SymptomsEnum::Hypervigilance,
                    ]
                ],
                'criteria'    => [1, 1]
            ],
            [
                'code'        => 'F43.9',
                'title'       => 'Реакция на тяжелый стресс, неуточненная',
                'description' => 'Неуточненное нарушение, возникающее в результате тяжелого стресса, влияющее на адаптацию и эмоциональное состояние.',
                'relatives'   => [
                    'F43.0', 'F43.8'
                ],
                'exceptions'  => [
                    'F32.9', 'F41.9'
                ],
                'symptoms'    => [
                    'required' => [
                        SymptomsEnum::StressResponse,
                    ],
                    'relative' => [
                        SymptomsEnum::SocialWithdrawal,
                        SymptomsEnum::Anxiety,
                        SymptomsEnum::DepressedMood,
                    ]
                ],
                'criteria'    => [1, 1]
            ],
            // F92
            [
                'code'        => 'F92.0',
                'title'       => 'Депрессивное расстройство поведения',
                'description' => 'Нарушение поведения, сочетающееся с устойчиво депрессивным настроением и другими симптомами депрессии.',
                'relatives'   => [
                    'F32.0', 'F91.3'
                ],
                'exceptions'  => [
                    'F41.2', 'F33.1'
                ],
                'symptoms'    => [
                    'required' => [
                        SymptomsEnum::DepressedMood,
                        SymptomsEnum::Irritability,
                        SymptomsEnum::SocialWithdrawal,
                    ],
                    'relative' => [
                        SymptomsEnum::PoorConcentration,
                        SymptomsEnum::Guilt,
                        SymptomsEnum::SleepDisturbance,
                        SymptomsEnum::AppetiteChanges,
                    ]
                ],
                'criteria'    => [2, 2]
            ],
            [
                'code'        => 'F92.8',
                'title'       => 'Другие смешанные расстройства поведения и эмоций',
                'description' => 'Смешанные расстройства, при которых наблюдаются нарушения поведения, сопровождаемые эмоциональными расстройствами.',
                'relatives'   => [
                    'F91.2', 'F93.9'
                ],
                'exceptions'  => [
                    'F32.9', 'F34.1'
                ],
                'symptoms'    => [
                    'required' => [
                        SymptomsEnum::Irritability,
                        SymptomsEnum::Aggressiveness,
                    ],
                    'relative' => [
                        SymptomsEnum::Anxiety,
                        SymptomsEnum::DepressedMood,
                        SymptomsEnum::SleepDisturbance,
                        SymptomsEnum::SocialWithdrawal,
                    ]
                ],
                'criteria'    => [2, 2]
            ],
            [
                'code'        => 'F92.9',
                'title'       => 'Смешанное расстройство поведения и эмоций, неуточненное',
                'description' => 'Неуточненное расстройство, характеризующееся сочетанием эмоциональных нарушений и проблем поведения.',
                'relatives'   => [
                    'F91.9', 'F32.9'
                ],
                'exceptions'  => [
                    'F93.0', 'F34.0'
                ],
                'symptoms'    => [
                    'required' => [
                        SymptomsEnum::BehavioralProblems,
                    ],
                    'relative' => [
                        SymptomsEnum::Anxiety,
                        SymptomsEnum::DepressedMood,
                        SymptomsEnum::Irritability,
                    ]
                ],
                'criteria'    => [1, 1]
            ],
            // F45
            [
                'code'        => 'F45.0',
                'title'       => 'Соматизированное расстройство',
                'description' => 'Хроническое расстройство с множественными, повторяющимися соматическими симптомами без медицинского объяснения.',
                'relatives'   => [
                    'F48.0', 'F41.2'
                ],
                'exceptions'  => [
                    'F50.0', 'F51.0'
                ],
                'symptoms'    => [
                    'required' => [
                        SymptomsEnum::MultipleSomaticComplaints,
                        SymptomsEnum::ExcessiveWorryAboutHealth,
                    ],
                    'relative' => [
                        SymptomsEnum::SleepDisturbance,
                        SymptomsEnum::Anxiety,
                        SymptomsEnum::Irritability,
                    ]
                ],
                'criteria'    => [2, 1]
            ],
            [
                'code'        => 'F45.1',
                'title'       => 'Неудовлетворенность собственным телом (дисморфофобия)',
                'description' => 'Чрезмерная озабоченность незначительными или воображаемыми дефектами внешности.',
                'relatives'   => [
                    'F48.8', 'F63.8'
                ],
                'exceptions'  => [
                    'F20.3', 'F22.0'
                ],
                'symptoms'    => [
                    'required' => [
                        SymptomsEnum::ExcessiveConcernAboutAppearance,
                        SymptomsEnum::SocialWithdrawal,
                    ],
                    'relative' => [
                        SymptomsEnum::Anxiety,
                        SymptomsEnum::DepressedMood,
                        SymptomsEnum::PoorConcentration,
                    ]
                ],
                'criteria'    => [2, 1]
            ],
            [
                'code'        => 'F45.2',
                'title'       => 'Гипохондрическое расстройство',
                'description' => 'Навязчивая озабоченность возможным наличием одного или более тяжелых заболеваний.',
                'relatives'   => [
                    'F48.0', 'F41.0'
                ],
                'exceptions'  => [
                    'F20.0', 'F32.0'
                ],
                'symptoms'    => [
                    'required' => [
                        SymptomsEnum::ExcessiveWorryAboutHealth,
                        SymptomsEnum::PersistentFearOfIllness,
                    ],
                    'relative' => [
                        SymptomsEnum::Irritability,
                        SymptomsEnum::SleepDisturbance,
                        SymptomsEnum::Fatigue,
                    ]
                ],
                'criteria'    => [2, 1]
            ],
            [
                'code'        => 'F45.3',
                'title'       => 'Соматоформная дисфункция вегетативной нервной системы',
                'description' => 'Симптомы вегетативной нервной системы, которые невозможно объяснить медицинскими заболеваниями.',
                'relatives'   => [
                    'F41.1', 'F48.8'
                ],
                'exceptions'  => [
                    'F20.3', 'F34.1'
                ],
                'symptoms'    => [
                    'required' => [
                        SymptomsEnum::Palpitations,
                        SymptomsEnum::Sweating,
                        SymptomsEnum::Dizziness,
                    ],
                    'relative' => [
                        SymptomsEnum::Nausea,
                        SymptomsEnum::ShortnessOfBreath,
                        SymptomsEnum::ChestDiscomfort,
                    ]
                ],
                'criteria'    => [3, 2]
            ],
            [
                'code'        => 'F45.4',
                'title'       => 'Хроническое соматоформное болевое расстройство',
                'description' => 'Длительная боль, которую нельзя объяснить медицинскими причинами.',
                'relatives'   => [
                    'F41.2', 'F48.0'
                ],
                'exceptions'  => [
                    'F20.0', 'F32.2'
                ],
                'symptoms'    => [
                    'required' => [
                        SymptomsEnum::ChronicPain,
                        SymptomsEnum::ExcessiveWorryAboutHealth,
                    ],
                    'relative' => [
                        SymptomsEnum::Anxiety,
                        SymptomsEnum::DepressedMood,
                        SymptomsEnum::Fatigue,
                    ]
                ],
                'criteria'    => [2, 1]
            ],
            [
                'code'        => 'F45.41',
                'title'       => 'Хроническая соматоформная боль',
                'description' => 'Длительная и стойкая боль, не связанная с органическим поражением тела, часто обусловленная психологическими факторами.',
                'relatives'   => ['F45.0', 'F45.1', 'F45.2'],
                'exceptions'  => ['G89.4', 'M79.7'],
                'symptoms'    => [
                    'required' => [
                        SymptomsEnum::ChronicPain,
                        SymptomsEnum::Distress,
                        SymptomsEnum::PsychologicalFactors,
                    ],
                    'relative' => [
                        SymptomsEnum::Fatigue,
                        SymptomsEnum::SleepDisturbance,
                        SymptomsEnum::DepressedMood,
                        SymptomsEnum::Anxiety,
                    ],
                ],
                'criteria'    => [2, 2],
            ],
            [
                'code'        => 'F45.8',
                'title'       => 'Другие соматоформные расстройства',
                'description' => 'Различные соматоформные симптомы, не вписывающиеся в другие категории.',
                'relatives'   => [
                    'F48.8', 'F41.0'
                ],
                'exceptions'  => [
                    'F50.0', 'F32.9'
                ],
                'symptoms'    => [
                    'required' => [
                        SymptomsEnum::MultipleSomaticComplaints,
                    ],
                    'relative' => [
                        SymptomsEnum::Fatigue,
                        SymptomsEnum::Irritability,
                        SymptomsEnum::PoorConcentration,
                    ]
                ],
                'criteria'    => [1, 1]
            ],
            [
                'code'        => 'F45.9',
                'title'       => 'Соматоформное расстройство неуточненное',
                'description' => 'Соматоформное расстройство, которое не соответствует другим подкатегориям.',
                'relatives'   => [
                    'F41.0', 'F48.8'
                ],
                'exceptions'  => [
                    'F20.0', 'F32.0'
                ],
                'symptoms'    => [
                    'required' => [
                        SymptomsEnum::ChronicPain,
                    ],
                    'relative' => [
                        SymptomsEnum::Fatigue,
                        SymptomsEnum::Irritability,
                        SymptomsEnum::DepressedMood,
                    ]
                ],
                'criteria'    => [1, 1]
            ],
            // F95
            [
                'code'        => 'F95.0',
                'title'       => 'Транзиторное тикозное расстройство',
                'description' => 'Временное расстройство, характеризующееся кратковременными тиками, которые спонтанно исчезают.',
                'relatives'   => [
                    'F98.4', 'F98.5'
                ],
                'exceptions'  => [
                    'F95.2', 'F95.1'
                ],
                'symptoms'    => [
                    'required' => [
                        SymptomsEnum::MotorTics,
                    ],
                    'relative' => [
                        SymptomsEnum::VocalTics,
                        SymptomsEnum::Anxiety,
                        SymptomsEnum::Irritability,
                    ],
                ],
                'criteria'    => [1, 1]
            ],
            [
                'code'        => 'F95.1',
                'title'       => 'Хроническое моторное или вокальное тикозное расстройство',
                'description' => 'Хроническое расстройство, характеризующееся либо моторными, либо вокальными тиками.',
                'relatives'   => [
                    'F98.4', 'F95.0'
                ],
                'exceptions'  => [
                    'F95.2'
                ],
                'symptoms'    => [
                    'required' => [
                        SymptomsEnum::MotorTics,
                        SymptomsEnum::ChronicCourse,
                    ],
                    'relative' => [
                        SymptomsEnum::VocalTics,
                        SymptomsEnum::PoorConcentration,
                    ],
                ],
                'criteria'    => [2, 1]
            ],
            [
                'code'        => 'F95.2',
                'title'       => 'Синдром Жиля де ля Туретта',
                'description' => 'Расстройство, характеризующееся комбинацией моторных и вокальных тик.',
                'relatives'   => [
                    'F98.4', 'F98.5'
                ],
                'exceptions'  => [
                    'F95.0'
                ],
                'symptoms'    => [
                    'required' => [
                        SymptomsEnum::MotorTics,
                        SymptomsEnum::VocalTics,
                    ],
                    'relative' => [
                        SymptomsEnum::Anxiety,
                        SymptomsEnum::Impulsivity,
                        SymptomsEnum::PoorConcentration,
                    ],
                ],
                'criteria'    => [2, 1]
            ],
            [
                'code'        => 'F95.8',
                'title'       => 'Другие тики',
                'description' => 'Редкие формы тикозных расстройств, которые не соответствуют другим категориям.',
                'relatives'   => [
                    'F98.4', 'F98.5'
                ],
                'exceptions'  => [
                    'F95.2'
                ],
                'symptoms'    => [
                    'required' => [
                        SymptomsEnum::MotorTics,
                    ],
                    'relative' => [
                        SymptomsEnum::VocalTics,
                        SymptomsEnum::Irritability,
                    ],
                ],
                'criteria'    => [1, 1]
            ],
            [
                'code'        => 'F95.9',
                'title'       => 'Тикозное расстройство неуточненное',
                'description' => 'Недифференцированное тикозное расстройство.',
                'relatives'   => [
                    'F98.4', 'F98.5'
                ],
                'exceptions'  => [
                    'F95.1'
                ],
                'symptoms'    => [
                    'required' => [
                        SymptomsEnum::MotorTics,
                    ],
                    'relative' => [
                        SymptomsEnum::VocalTics,
                    ],
                ],
                'criteria'    => [1, 1]
            ],
            [
                'code'        => 'F73.0',
                'title'       => 'Глубокая умственная отсталость с минимальными коммуникативными навыками',
                'description' => 'Тяжелая форма умственной отсталости с минимальной способностью к общению.',
                'relatives'   => [
                    'F72.0', 'F84.2'
                ],
                'exceptions'  => [
                    'F84.5', 'F84.9'
                ],
                'symptoms'    => [
                    'required' => [
                        SymptomsEnum::SevereCognitiveImpairment,
                        SymptomsEnum::MinimalSocialInteraction,
                    ],
                    'relative' => [
                        SymptomsEnum::SelfCareDeficits,
                        SymptomsEnum::PoorMotorSkills,
                    ],
                ],
                'criteria'    => [2, 1]
            ],
            [
                'code'        => 'F73.1',
                'title'       => 'Глубокая умственная отсталость с агрессивным поведением',
                'description' => 'Умственная отсталость с выраженной агрессивностью.',
                'relatives'   => [
                    'F72.1', 'F91.3'
                ],
                'exceptions'  => [
                    'F84.5', 'F91.9'
                ],
                'symptoms'    => [
                    'required' => [
                        SymptomsEnum::SevereCognitiveImpairment,
                        SymptomsEnum::AggressiveBehavior,
                    ],
                    'relative' => [
                        SymptomsEnum::SelfCareDeficits,
                        SymptomsEnum::MinimalSocialInteraction,
                    ],
                ],
                'criteria'    => [2, 1]
            ],
            [
                'code'        => 'F73.8',
                'title'       => 'Глубокая умственная отсталость с необычными поведенческими особенностями',
                'description' => 'Тяжелая умственная отсталость с редкими поведенческими особенностями.',
                'relatives'   => [
                    'F72.8', 'F84.4'
                ],
                'exceptions'  => [
                    'F84.5', 'F84.9'
                ],
                'symptoms'    => [
                    'required' => [
                        SymptomsEnum::SevereCognitiveImpairment,
                    ],
                    'relative' => [
                        SymptomsEnum::PoorMotorSkills,
                        SymptomsEnum::MinimalSocialInteraction,
                        SymptomsEnum::AtypicalBehavior,
                    ],
                ],
                'criteria'    => [1, 2]
            ],
            [
                'code'        => 'F73.9',
                'title'       => 'Глубокая умственная отсталость неуточненная',
                'description' => 'Недифференцированная глубокая умственная отсталость.',
                'relatives'   => [
                    'F72.9'
                ],
                'exceptions'  => [
                    'F84.5', 'F84.9'
                ],
                'symptoms'    => [
                    'required' => [
                        SymptomsEnum::SevereCognitiveImpairment,
                    ],
                    'relative' => [
                        SymptomsEnum::SelfCareDeficits,
                        SymptomsEnum::MinimalSocialInteraction,
                    ],
                ],
                'criteria'    => [1, 1]
            ],
            // F70
            [
                'code'        => 'F70.0',
                'title'       => 'Легкая умственная отсталость с минимальными или отсутствующими нарушениями поведения',
                'description' => 'Легкая форма умственной отсталости без выраженных нарушений поведения.',
                'relatives'   => [
                    'F71.0', 'F80.8'
                ],
                'exceptions'  => [
                    'F72.0', 'F73.0'
                ],
                'symptoms'    => [
                    'required' => [
                        SymptomsEnum::MildCognitiveImpairment,
                        SymptomsEnum::BasicSocialSkills,
                    ],
                    'relative' => [
                        SymptomsEnum::LearningDifficulties,
                        SymptomsEnum::PoorJudgement,
                    ],
                ],
                'criteria'    => [2, 1]
            ],
            [
                'code'        => 'F70.1',
                'title'       => 'Легкая умственная отсталость с заметными поведенческими нарушениями, требующими лечения',
                'description' => 'Легкая умственная отсталость, сопровождающаяся заметными поведенческими отклонениями.',
                'relatives'   => [
                    'F71.1', 'F91.2'
                ],
                'exceptions'  => [
                    'F72.1', 'F73.1'
                ],
                'symptoms'    => [
                    'required' => [
                        SymptomsEnum::MildCognitiveImpairment,
                        SymptomsEnum::BehavioralDisruptions,
                    ],
                    'relative' => [
                        SymptomsEnum::LearningDifficulties,
                        SymptomsEnum::Impulsivity,
                        SymptomsEnum::PoorConcentration,
                    ],
                ],
                'criteria'    => [2, 2]
            ],
            [
                'code'        => 'F70.8',
                'title'       => 'Другая легкая умственная отсталость',
                'description' => 'Редкие формы легкой умственной отсталости.',
                'relatives'   => [
                    'F71.8', 'F80.9'
                ],
                'exceptions'  => [
                    'F72.8', 'F73.8'
                ],
                'symptoms'    => [
                    'required' => [
                        SymptomsEnum::MildCognitiveImpairment,
                    ],
                    'relative' => [
                        SymptomsEnum::PoorSocialSkills,
                        SymptomsEnum::LearningDifficulties,
                    ],
                ],
                'criteria'    => [1, 1]
            ],
            [
                'code'        => 'F70.9',
                'title'       => 'Легкая умственная отсталость неуточненная',
                'description' => 'Недифференцированная легкая умственная отсталость.',
                'relatives'   => [
                    'F71.9', 'F80.9'
                ],
                'exceptions'  => [
                    'F72.9', 'F73.9'
                ],
                'symptoms'    => [
                    'required' => [
                        SymptomsEnum::MildCognitiveImpairment,
                    ],
                    'relative' => [
                        SymptomsEnum::BasicSocialSkills,
                        SymptomsEnum::LearningDifficulties,
                    ],
                ],
                'criteria'    => [1, 1]
            ],
            // F79
            [
                'code'        => 'F79.0',
                'title'       => 'Умственная отсталость с минимальными или отсутствующими нарушениями поведения',
                'description' => 'Недифференцированная умственная отсталость без значительных поведенческих отклонений.',
                'relatives'   => [
                    'F70.0', 'F71.0'
                ],
                'exceptions'  => [
                    'F72.0', 'F73.0'
                ],
                'symptoms'    => [
                    'required' => [
                        SymptomsEnum::CognitiveImpairment,
                        SymptomsEnum::BasicSocialSkills,
                    ],
                    'relative' => [
                        SymptomsEnum::LearningDifficulties,
                    ],
                ],
                'criteria'    => [2, 1]
            ],
            [
                'code'        => 'F79.1',
                'title'       => 'Умственная отсталость с поведенческими нарушениями, требующими лечения',
                'description' => 'Недифференцированная умственная отсталость с выраженными нарушениями поведения.',
                'relatives'   => [
                    'F70.1', 'F71.1'
                ],
                'exceptions'  => [
                    'F72.1', 'F73.1'
                ],
                'symptoms'    => [
                    'required' => [
                        SymptomsEnum::CognitiveImpairment,
                        SymptomsEnum::BehavioralDisruptions,
                    ],
                    'relative' => [
                        SymptomsEnum::Impulsivity,
                        SymptomsEnum::PoorConcentration,
                    ],
                ],
                'criteria'    => [2, 1]
            ],
            [
                'code'        => 'F79.8',
                'title'       => 'Другая неуточненная умственная отсталость',
                'description' => 'Редкие формы недифференцированной умственной отсталости.',
                'relatives'   => [
                    'F70.8', 'F71.8'
                ],
                'exceptions'  => [
                    'F72.8', 'F73.8'
                ],
                'symptoms'    => [
                    'required' => [
                        SymptomsEnum::CognitiveImpairment,
                    ],
                    'relative' => [
                        SymptomsEnum::BasicSocialSkills,
                        SymptomsEnum::PoorSocialSkills,
                    ],
                ],
                'criteria'    => [1, 1]
            ],
            [
                'code'        => 'F79.9',
                'title'       => 'Умственная отсталость неуточненная без дополнительных признаков',
                'description' => 'Полностью недифференцированная форма умственной отсталости.',
                'relatives'   => [
                    'F70.9', 'F71.9'
                ],
                'exceptions'  => [
                    'F72.9', 'F73.9'
                ],
                'symptoms'    => [
                    'required' => [
                        SymptomsEnum::CognitiveImpairment,
                    ],
                    'relative' => [
                        SymptomsEnum::LearningDifficulties,
                        SymptomsEnum::BasicSocialSkills,
                    ],
                ],
                'criteria'    => [1, 1]
            ],
            // F72
            [
                'code'        => 'F72.0',
                'title'       => 'Тяжелая умственная отсталость с минимальными или отсутствующими нарушениями поведения',
                'description' => 'Тяжелая форма умственной отсталости без значительных нарушений поведения.',
                'relatives'   => [
                    'F71.0', 'F73.0',
                ],
                'exceptions'  => [
                    'F70.0',
                ],
                'symptoms'    => [
                    'required' => [
                        SymptomsEnum::SevereCognitiveImpairment,
                        SymptomsEnum::LimitedCommunicationSkills,
                    ],
                    'relative' => [
                        SymptomsEnum::BasicSocialSkills,
                        SymptomsEnum::BehavioralDisruptions,
                    ],
                ],
                'criteria'    => [2, 1],
            ],
            [
                'code'        => 'F72.1',
                'title'       => 'Тяжелая умственная отсталость с значительными нарушениями поведения, требующими лечения',
                'description' => 'Тяжелая умственная отсталость с выраженными нарушениями поведения.',
                'relatives'   => [
                    'F71.1', 'F73.1',
                ],
                'exceptions'  => [
                    'F70.1',
                ],
                'symptoms'    => [
                    'required' => [
                        SymptomsEnum::SevereCognitiveImpairment,
                        SymptomsEnum::BehavioralDisruptions,
                    ],
                    'relative' => [
                        SymptomsEnum::Aggression,
                        SymptomsEnum::Impulsivity,
                    ],
                ],
                'criteria'    => [2, 2],
            ],
            [
                'code'        => 'F72.8',
                'title'       => 'Другая тяжелая умственная отсталость',
                'description' => 'Редкие формы тяжелой умственной отсталости.',
                'relatives'   => [
                    'F71.8', 'F73.8',
                ],
                'exceptions'  => [
                    'F70.8',
                ],
                'symptoms'    => [
                    'required' => [
                        SymptomsEnum::SevereCognitiveImpairment,
                    ],
                    'relative' => [
                        SymptomsEnum::BasicSocialSkills,
                    ],
                ],
                'criteria'    => [1, 1],
            ],
            [
                'code'        => 'F72.9',
                'title'       => 'Тяжелая умственная отсталость неуточненная',
                'description' => 'Недифференцированная форма тяжелой умственной отсталости.',
                'relatives'   => [
                    'F71.9', 'F73.9',
                ],
                'exceptions'  => [
                    'F70.9',
                ],
                'symptoms'    => [
                    'required' => [
                        SymptomsEnum::SevereCognitiveImpairment,
                    ],
                    'relative' => [
                        SymptomsEnum::LimitedCommunicationSkills,
                    ],
                ],
                'criteria'    => [1, 1],
            ],
            // F71
            [
                'code'        => 'F71.0',
                'title'       => 'Умеренная умственная отсталость с минимальными или отсутствующими нарушениями поведения',
                'description' => 'Умеренная форма умственной отсталости без выраженных нарушений поведения.',
                'relatives'   => [
                    'F70.0', 'F72.0',
                ],
                'exceptions'  => [
                    'F73.0',
                ],
                'symptoms'    => [
                    'required' => [
                        SymptomsEnum::ModerateCognitiveImpairment,
                        SymptomsEnum::BasicSocialSkills,
                    ],
                    'relative' => [
                        SymptomsEnum::LearningDifficulties,
                    ],
                ],
                'criteria'    => [2, 1],
            ],
            [
                'code'        => 'F71.1',
                'title'       => 'Умеренная умственная отсталость с выраженными нарушениями поведения, требующими лечения',
                'description' => 'Умеренная умственная отсталость, сопровождающаяся значительными нарушениями поведения.',
                'relatives'   => [
                    'F70.1', 'F72.1',
                ],
                'exceptions'  => [
                    'F73.1',
                ],
                'symptoms'    => [
                    'required' => [
                        SymptomsEnum::ModerateCognitiveImpairment,
                        SymptomsEnum::BehavioralDisruptions,
                    ],
                    'relative' => [
                        SymptomsEnum::Aggression,
                        SymptomsEnum::Impulsivity,
                    ],
                ],
                'criteria'    => [2, 2],
            ],
            [
                'code'        => 'F71.8',
                'title'       => 'Другая умеренная умственная отсталость',
                'description' => 'Редкие формы умеренной умственной отсталости.',
                'relatives'   => [
                    'F70.8', 'F72.8',
                ],
                'exceptions'  => [
                    'F73.8',
                ],
                'symptoms'    => [
                    'required' => [
                        SymptomsEnum::ModerateCognitiveImpairment,
                    ],
                    'relative' => [
                        SymptomsEnum::LearningDifficulties,
                    ],
                ],
                'criteria'    => [1, 1],
            ],
            [
                'code'        => 'F71.9',
                'title'       => 'Умеренная умственная отсталость неуточненная',
                'description' => 'Недифференцированная форма умеренной умственной отсталости.',
                'relatives'   => [
                    'F70.9', 'F72.9',
                ],
                'exceptions'  => [
                    'F73.9',
                ],
                'symptoms'    => [
                    'required' => [
                        SymptomsEnum::ModerateCognitiveImpairment,
                    ],
                    'relative' => [
                        SymptomsEnum::LimitedCommunicationSkills,
                    ],
                ],
                'criteria'    => [1, 1],
            ],
            // F40
            [
                'code'        => 'F40.0',
                'title'       => 'Агорафобия',
                'description' => 'Тревожное расстройство, характеризующееся страхом открытых пространств или мест, где трудно получить помощь.',
                'relatives'   => [
                    'F41.0', 'F42.0',
                ],
                'exceptions'  => [
                    'F43.1',
                ],
                'symptoms'    => [
                    'required' => [
                        SymptomsEnum::ExcessiveFear,
                        SymptomsEnum::AvoidanceBehavior,
                    ],
                    'relative' => [
                        SymptomsEnum::PanicAttacks,
                        SymptomsEnum::Sweating,
                        SymptomsEnum::IncreasedHeartRate,
                    ],
                ],
                'criteria'    => [2, 2],
            ],
            [
                'code'        => 'F40.1',
                'title'       => 'Социальная фобия',
                'description' => 'Тревожное расстройство, связанное с боязнью социальных ситуаций и оценок окружающих.',
                'relatives'   => [
                    'F41.1', 'F42.1',
                ],
                'exceptions'  => [
                    'F43.2',
                ],
                'symptoms'    => [
                    'required' => [
                        SymptomsEnum::ExcessiveFear,
                        SymptomsEnum::AvoidanceBehavior,
                    ],
                    'relative' => [
                        SymptomsEnum::Blushing,
                        SymptomsEnum::Tremors,
                        SymptomsEnum::Sweating,
                    ],
                ],
                'criteria'    => [2, 1],
            ],
            [
                'code'        => 'F40.2',
                'title'       => 'Специфические (изолированные) фобии',
                'description' => 'Чрезмерный страх перед определенными объектами или ситуациями, такими как высота, животные или полеты.',
                'relatives'   => [
                    'F41.2', 'F42.2',
                ],
                'exceptions'  => [
                    'F43.3',
                ],
                'symptoms'    => [
                    'required' => [
                        SymptomsEnum::ExcessiveFear,
                        SymptomsEnum::AvoidanceBehavior,
                    ],
                    'relative' => [
                        SymptomsEnum::PanicAttacks,
                        SymptomsEnum::RapidBreathing,
                        SymptomsEnum::Sweating,
                    ],
                ],
                'criteria'    => [2, 1],
            ],
            // F22
            [
                'code'        => 'F22.0',
                'title'       => 'Бредовое расстройство',
                'description' => 'Длительное расстройство с наличием систематизированного бреда без выраженной шизофренической симптоматики.',
                'relatives'   => [
                    'F20.0', 'F25.0',
                ],
                'exceptions'  => [
                    'F23.0', 'F30.2',
                ],
                'symptoms'    => [
                    'required' => [
                        SymptomsEnum::Delusions,
                    ],
                    'relative' => [
                        SymptomsEnum::Paranoia,
                        SymptomsEnum::Hallucinations,
                        SymptomsEnum::PoorJudgement,
                    ],
                ],
                'criteria'    => [1, 1],
            ],
            [
                'code'        => 'F22.8',
                'title'       => 'Другие хронические бредовые расстройства',
                'description' => 'Редкие формы хронических бредовых расстройств.',
                'relatives'   => [
                    'F25.1',
                ],
                'exceptions'  => [
                    'F23.1',
                ],
                'symptoms'    => [
                    'required' => [
                        SymptomsEnum::Delusions,
                    ],
                    'relative' => [
                        SymptomsEnum::Paranoia,
                    ],
                ],
                'criteria'    => [1, 1],
            ],
            [
                'code'        => 'F22.9',
                'title'       => 'Хроническое бредовое расстройство, неуточненное',
                'description' => 'Недифференцированное хроническое бредовое расстройство.',
                'relatives'   => [
                    'F20.9', 'F25.9',
                ],
                'exceptions'  => [
                    'F23.9',
                ],
                'symptoms'    => [
                    'required' => [
                        SymptomsEnum::Delusions,
                    ],
                    'relative' => [],
                ],
                'criteria'    => [1, 0],
            ],
            // F25
            [
                'code'        => 'F25.0',
                'title'       => 'Шизоаффективное расстройство, текущий эпизод мании',
                'description' => 'Шизоаффективное расстройство с признаками мании и бреда.',
                'relatives'   => [
                    'F30.2', 'F22.0',
                ],
                'exceptions'  => [
                    'F20.1',
                ],
                'symptoms'    => [
                    'required' => [
                        SymptomsEnum::ElevatedMood,
                        SymptomsEnum::Delusions,
                        SymptomsEnum::Hyperactivity,
                    ],
                    'relative' => [
                        SymptomsEnum::Talkativeness,
                        SymptomsEnum::PoorJudgement,
                    ],
                ],
                'criteria'    => [3, 1],
            ],
            [
                'code'        => 'F25.1',
                'title'       => 'Шизоаффективное расстройство, текущий эпизод депрессии',
                'description' => 'Шизоаффективное расстройство с признаками депрессии и бреда.',
                'relatives'   => [
                    'F31.5', 'F22.8',
                ],
                'exceptions'  => [
                    'F20.2',
                ],
                'symptoms'    => [
                    'required' => [
                        SymptomsEnum::DepressedMood,
                        SymptomsEnum::Delusions,
                        SymptomsEnum::ThoughtsOfDeath,
                    ],
                    'relative' => [
                        SymptomsEnum::PoorConcentration,
                        SymptomsEnum::Fatigue,
                    ],
                ],
                'criteria'    => [3, 1],
            ],
            [
                'code'        => 'F25.2',
                'title'       => 'Шизоаффективное расстройство, смешанный тип',
                'description' => 'Шизоаффективное расстройство с признаками как мании, так и депрессии, сопровождающееся бредом.',
                'relatives'   => [
                    'F25.0', 'F25.1',
                ],
                'exceptions'  => [
                    'F20.3',
                ],
                'symptoms'    => [
                    'required' => [
                        SymptomsEnum::ElevatedMood,
                        SymptomsEnum::DepressedMood,
                        SymptomsEnum::Delusions,
                    ],
                    'relative' => [
                        SymptomsEnum::Hyperactivity,
                        SymptomsEnum::Fatigue,
                    ],
                ],
                'criteria'    => [3, 1],
            ],
            [
                'code'        => 'F25.9',
                'title'       => 'Шизоаффективное расстройство, неуточненное',
                'description' => 'Шизоаффективное расстройство без уточнения текущего эпизода.',
                'relatives'   => [
                    'F22.9',
                ],
                'exceptions'  => [],
                'symptoms'    => [
                    'required' => [
                        SymptomsEnum::Delusions,
                    ],
                    'relative' => [],
                ],
                'criteria'    => [1, 0],
            ],
            // F20
            [
                 'code'        => 'F20.0',
                 'title'       => 'Параноидная шизофрения',
                 'description' => 'Форма шизофрении, характеризующаяся преобладанием параноидных симптомов, таких как бред преследования, влияния или величия, а также слуховых галлюцинаций.',
                 'relatives'   => [
                     'F22.0', 'F25.0',
                 ],
                 'exceptions'  => [
                     'F23.0',
                 ],
                 'symptoms'    => [
                     'required' => [
                         SymptomsEnum::Delusions,
                         SymptomsEnum::Paranoia,
                         SymptomsEnum::Hallucinations,
                     ],
                     'relative' => [
                         SymptomsEnum::PsychoticFeatures,
                         SymptomsEnum::PoorConcentration,
                     ],
                 ],
                 'criteria'    => [3, 1],
             ],
             [
                 'code'        => 'F20.1',
                 'title'       => 'Гебефреническая шизофрения',
                 'description' => 'Форма шизофрении, характеризующаяся выраженной дезорганизацией мышления, эмоций и поведения, включая неадекватный смех, манерность и хаотичность действий.',
                 'relatives'   => [
                     'F23.1',
                 ],
                 'exceptions'  => [
                     'F22.1',
                 ],
                 'symptoms'    => [
                     'required' => [
                         SymptomsEnum::Disorganization,
                         SymptomsEnum::InappropriateAffect,
                         SymptomsEnum::Delusions,
                     ],
                     'relative' => [
                         SymptomsEnum::PoorJudgement,
                         SymptomsEnum::EmotionalInstability,
                     ],
                 ],
                 'criteria'    => [3, 1],
             ],
             [
                 'code'        => 'F20.2',
                 'title'       => 'Кататоническая шизофрения',
                 'description' => 'Форма шизофрении, характеризующаяся двигательными расстройствами, включая ступор, возбуждение, восковую гибкость или негативизм.',
                 'relatives'   => [
                     'F23.2',
                 ],
                 'exceptions'  => [
                     'F25.2',
                 ],
                 'symptoms'    => [
                     'required' => [
                         SymptomsEnum::Catatonia,
                         SymptomsEnum::Delusions,
                         SymptomsEnum::Hallucinations,
                     ],
                     'relative' => [
                         SymptomsEnum::Mutism,
                         SymptomsEnum::PsychoticFeatures,
                     ],
                 ],
                 'criteria'    => [3, 1],
             ],
             [
                 'code'        => 'F20.3',
                 'title'       => 'Недифференцированная шизофрения',
                 'description' => 'Форма шизофрении, которая включает черты других подтипов, но не удовлетворяет полным критериям ни одного из них.',
                 'relatives'   => [
                     'F25.2', 'F22.8',
                 ],
                 'exceptions'  => [
                     'F23.3',
                 ],
                 'symptoms'    => [
                     'required' => [
                         SymptomsEnum::Delusions,
                         SymptomsEnum::Hallucinations,
                         SymptomsEnum::PsychoticFeatures,
                     ],
                     'relative' => [
                         SymptomsEnum::PoorConcentration,
                         SymptomsEnum::Anxiety,
                     ],
                 ],
                 'criteria'    => [3, 1],
             ],
             [
                 'code'        => 'F20.4',
                 'title'       => 'Постшизофреническая депрессия',
                 'description' => 'Состояние, при котором после активной фазы шизофрении развивается депрессивный эпизод с возможным наличием остаточных психотических симптомов.',
                 'relatives'   => [
                     'F33.0',
                 ],
                 'exceptions'  => [
                     'F25.1',
                 ],
                 'symptoms'    => [
                     'required' => [
                         SymptomsEnum::DepressedMood,
                         SymptomsEnum::Fatigue,
                         SymptomsEnum::ResidualPsychoticSymptoms,
                     ],
                     'relative' => [
                         SymptomsEnum::Anhedonia,
                         SymptomsEnum::PoorConcentration,
                     ],
                 ],
                 'criteria'    => [3, 1],
             ],
             [
                 'code'        => 'F20.5',
                 'title'       => 'Резидуальная шизофрения',
                 'description' => 'Хроническая форма шизофрении с выраженным снижением эмоционального реагирования, социальной активности и когнитивной функции.',
                 'relatives'   => [
                     'F25.9', 'F22.9',
                 ],
                 'exceptions'  => [
                     'F23.9',
                 ],
                 'symptoms'    => [
                     'required' => [
                         SymptomsEnum::EmotionalBlunting,
                         SymptomsEnum::SocialWithdrawal,
                         SymptomsEnum::CognitiveDecline,
                     ],
                     'relative' => [
                         SymptomsEnum::PoorConcentration,
                         SymptomsEnum::Anhedonia,
                     ],
                 ],
                 'criteria'    => [3, 1],
             ],
             [
                 'code'        => 'F20.6',
                 'title'       => 'Простая шизофрения',
                 'description' => 'Редкая форма шизофрении, характеризующаяся постепенным развитием негативных симптомов без выраженной психотической симптоматики.',
                 'relatives'   => [
                     'F25.0',
                 ],
                 'exceptions'  => [
                     'F22.0',
                 ],
                 'symptoms'    => [
                     'required' => [
                         SymptomsEnum::EmotionalBlunting,
                         SymptomsEnum::SocialWithdrawal,
                         SymptomsEnum::Apathy,
                     ],
                     'relative' => [
                         SymptomsEnum::CognitiveDecline,
                     ],
                 ],
                 'criteria'    => [3, 1],
             ],
             [
                 'code'        => 'F20.8',
                 'title'       => 'Другие формы шизофрении',
                 'description' => 'Редкие подтипы шизофрении с необычными или нехарактерными проявлениями.',
                 'relatives'   => [
                     'F20.9',
                 ],
                 'exceptions'  => [],
                 'symptoms'    => [
                     'required' => [
                         SymptomsEnum::Delusions,
                     ],
                     'relative' => [
                         SymptomsEnum::Hallucinations,
                     ],
                 ],
                 'criteria'    => [1, 1],
             ],
             [
                 'code'        => 'F20.9',
                 'title'       => 'Шизофрения, неуточненная',
                 'description' => 'Диагноз шизофрении, который не может быть классифицирован в другие подтипы.',
                 'relatives'   => [
                     'F20.8',
                 ],
                 'exceptions'  => [],
                 'symptoms'    => [
                     'required' => [
                         SymptomsEnum::Delusions,
                     ],
                     'relative' => [],
                 ],
                 'criteria'    => [1, 0],
            ],
            // F21
            [
                'code'        => 'F21',
                'title'       => 'Шизотипическое расстройство',
                'description' => 'Психическое расстройство, характеризующееся странностями мышления, восприятия, поведения и межличностных отношений, не достигающими уровня шизофрении.',
                'relatives'   => [
                    'F20.0', 'F20.1', 'F20.2', 'F25.1',
                ],
                'exceptions'  => [
                    'F22.0', 'F23.0',
                ],
                'symptoms'    => [
                    'required' => [
                        SymptomsEnum::Paranoia,
                        SymptomsEnum::CognitiveDecline,
                        SymptomsEnum::EmotionalBlunting,
                    ],
                    'relative' => [
                        SymptomsEnum::SocialWithdrawal,
                        SymptomsEnum::OddSpeech,
                        SymptomsEnum::OddBehavior,
                        SymptomsEnum::Suspiciousness,
                        SymptomsEnum::MagicalThinking,
                    ],
                ],
                'criteria'    => [3, 2],
            ],
            // F24
            [
                'code'        => 'F24',
                'title'       => 'Индуцированное бредовое расстройство',
                'description' => 'Психическое расстройство, при котором бредовые идеи возникают у нескольких лиц, находящихся в тесных взаимоотношениях, и обычно передаются от одного человека к другому.',
                'relatives'   => [
                    'F22.0', 'F23.1',
                ],
                'exceptions'  => [
                    'F20.0', 'F29',
                ],
                'symptoms'    => [
                    'required' => [
                        SymptomsEnum::Delusions,
                        SymptomsEnum::Paranoia,
                    ],
                    'relative' => [
                        SymptomsEnum::SocialWithdrawal,
                        SymptomsEnum::OddBehavior,
                        SymptomsEnum::Suspiciousness,
                    ],
                ],
                'criteria'    => [2, 1],
            ],
            // F28
            [
                'code'        => 'F28',
                'title'       => 'Другие уточненные психотические расстройства',
                'description' => 'Категория для психотических расстройств, которые не подпадают под описания других категорий, таких как шизофрения или индуцированное расстройство.',
                'relatives'   => [
                    'F20.3', 'F22.0', 'F23.2',
                ],
                'exceptions'  => [
                    'F24', 'F29',
                ],
                'symptoms'    => [
                    'required' => [
                        SymptomsEnum::Delusions,
                        SymptomsEnum::Hallucinations,
                    ],
                    'relative' => [
                        SymptomsEnum::DisorganizedSpeech,
                        SymptomsEnum::OddBehavior,
                        SymptomsEnum::EmotionalBlunting,
                    ],
                ],
                'criteria'    => [2, 1],
            ],
            // F29
            [
                'code'        => 'F29',
                'title'       => 'Неуточненное психотическое расстройство',
                'description' => 'Психотическое расстройство, которое не может быть классифицировано как конкретное психическое заболевание из-за недостатка информации или смешанных проявлений.',
                'relatives'   => [
                    'F20.0', 'F23.0', 'F28',
                ],
                'exceptions'  => [
                    'F24', 'F22.0',
                ],
                'symptoms'    => [
                    'required' => [
                        SymptomsEnum::Delusions,
                        SymptomsEnum::Hallucinations,
                    ],
                    'relative' => [
                        SymptomsEnum::DisorganizedSpeech,
                        SymptomsEnum::EmotionalBlunting,
                        SymptomsEnum::CognitiveDecline,
                    ],
                ],
                'criteria'    => [2, 1],
            ],
            // F93
            [
                'code'        => 'F93.0',
                'title'       => 'Сепарационное тревожное расстройство детского возраста',
                'description' => 'Тревожное расстройство, характеризующееся чрезмерным страхом разлуки с родителями или другими близкими людьми.',
                'relatives'   => [
                    'F40.8', 'F41.0',
                ],
                'exceptions'  => [
                    'F91.3', 'F92.0',
                ],
                'symptoms'    => [
                    'required' => [
                        SymptomsEnum::SeparationAnxiety,
                        SymptomsEnum::ExcessiveWorry,
                        SymptomsEnum::AvoidanceBehavior,
                    ],
                    'relative' => [
                        SymptomsEnum::Nightmares,
                        SymptomsEnum::SomaticComplaints,
                        SymptomsEnum::PoorSchoolPerformance,
                    ],
                ],
                'criteria'    => [2, 1],
            ],
            [
                'code'        => 'F93.1',
                'title'       => 'Фобическое тревожное расстройство детского возраста',
                'description' => 'Фобии, начинающиеся в детстве, с преобладанием специфических страхов.',
                'relatives'   => [
                    'F40.2', 'F41.1',
                ],
                'exceptions'  => [
                    'F91.3', 'F92.0',
                ],
                'symptoms'    => [
                    'required' => [
                        SymptomsEnum::SpecificFears,
                        SymptomsEnum::AvoidanceBehavior,
                        SymptomsEnum::ExcessiveWorry,
                    ],
                    'relative' => [
                        SymptomsEnum::Sweating,
                        SymptomsEnum::Tachycardia,
                        SymptomsEnum::Restlessness,
                    ],
                ],
                'criteria'    => [2, 1],
            ],
            [
                'code'        => 'F93.2',
                'title'       => 'Социальное тревожное расстройство детского возраста',
                'description' => 'Тревожное расстройство, характеризующееся страхом социальных ситуаций.',
                'relatives'   => [
                    'F40.1', 'F41.0',
                ],
                'exceptions'  => [
                    'F91.3', 'F92.0',
                ],
                'symptoms'    => [
                    'required' => [
                        SymptomsEnum::SocialAnxiety,
                        SymptomsEnum::AvoidanceBehavior,
                    ],
                    'relative' => [
                        SymptomsEnum::Blushing,
                        SymptomsEnum::Tremors,
                        SymptomsEnum::Sweating,
                    ],
                ],
                'criteria'    => [2, 1],
            ],
            [
                'code'        => 'F93.3',
                'title'       => 'Расстройство привязанности детского возраста',
                'description' => 'Патология привязанности к основному лицу ухода, возникающая в детском возрасте.',
                'relatives'   => [
                    'F91.0', 'F92.0',
                ],
                'exceptions'  => [
                    'F40.1', 'F41.2',
                ],
                'symptoms'    => [
                    'required' => [
                        SymptomsEnum::AttachmentIssues,
                        SymptomsEnum::SocialWithdrawal,
                    ],
                    'relative' => [
                        SymptomsEnum::Anxiety,
                        SymptomsEnum::BehavioralProblems,
                    ],
                ],
                'criteria'    => [2, 1],
            ],
            [
                'code'        => 'F93.8',
                'title'       => 'Другие уточненные эмоциональные расстройства детского возраста',
                'description' => 'Эмоциональные расстройства, начинающиеся в детском возрасте и не подпадающие под другие категории.',
                'relatives'   => [
                    'F40.8', 'F41.8',
                ],
                'exceptions'  => [
                    'F91.3', 'F92.9',
                ],
                'symptoms'    => [
                    'required' => [
                        SymptomsEnum::EmotionalDysregulation,
                    ],
                    'relative' => [
                        SymptomsEnum::Anxiety,
                        SymptomsEnum::AvoidanceBehavior,
                    ],
                ],
                'criteria'    => [1, 1],
            ],
            [
                'code'        => 'F93.9',
                'title'       => 'Неуточненное эмоциональное расстройство детского возраста',
                'description' => 'Неуточненные эмоциональные расстройства, начинающиеся в детском возрасте.',
                'relatives'   => [
                    'F40.9', 'F41.9',
                ],
                'exceptions'  => [
                    'F91.3', 'F92.9',
                ],
                'symptoms'    => [
                    'required' => [
                        SymptomsEnum::EmotionalDysregulation,
                    ],
                    'relative' => [
                        SymptomsEnum::Anxiety,
                        SymptomsEnum::SocialWithdrawal,
                    ],
                ],
                'criteria'    => [1, 1],
            ],
            // G21
            [
                'code'        => 'G21.0',
                'title'       => 'Паркинсонизм, вызванный лекарственными средствами',
                'description' => 'Состояние, вызванное применением антипсихотиков или других медикаментов.',
                'relatives'   => [
                    'F12.4', 'F13.4',
                ],
                'exceptions'  => [
                    'G20', 'F10.4',
                ],
                'symptoms'    => [
                    'required' => [
                        SymptomsEnum::Bradykinesia,
                        SymptomsEnum::MuscleRigidity,
                    ],
                    'relative' => [
                        SymptomsEnum::Tremor,
                        SymptomsEnum::PosturalInstability,
                    ],
                ],
                'criteria'    => [2, 1],
            ],
            [
                'code'        => 'G21.8',
                'title'       => 'Другие уточненные вторичные паркинсонические синдромы',
                'description' => 'Вторичные паркинсонические синдромы, вызванные другими причинами.',
                'relatives'   => [
                    'G20', 'G22',
                ],
                'exceptions'  => [
                    'F10.4', 'F12.4',
                ],
                'symptoms'    => [
                    'required' => [
                        SymptomsEnum::Bradykinesia,
                    ],
                    'relative' => [
                        SymptomsEnum::Tremor,
                        SymptomsEnum::MuscleRigidity,
                    ],
                ],
                'criteria'    => [1, 1],
            ],
            [
                'code'        => 'G21.9',
                'title'       => 'Неуточненные вторичные паркинсонические синдромы',
                'description' => 'Неуточненные причины вторичного паркинсонизма.',
                'relatives'   => [
                    'G20', 'G22',
                ],
                'exceptions'  => [
                    'F12.4', 'F13.4',
                ],
                'symptoms'    => [
                    'required' => [
                        SymptomsEnum::Bradykinesia,
                    ],
                    'relative' => [
                        SymptomsEnum::Tremor,
                        SymptomsEnum::MuscleRigidity,
                    ],
                ],
                'criteria'    => [1, 1],
            ],
            // G25
            [
                'code'        => 'G25.0',
                'title'       => 'Эссенциальный тремор',
                'description' => 'Двигательное нарушение, характеризующееся непроизвольными, ритмичными движениями.',
                'relatives'   => [
                    'G20', 'G22',
                ],
                'exceptions'  => [
                    'G21.0', 'F13.4',
                ],
                'symptoms'    => [
                    'required' => [
                        SymptomsEnum::Tremor,
                    ],
                    'relative' => [
                        SymptomsEnum::PosturalInstability,
                        SymptomsEnum::Bradykinesia,
                    ],
                ],
                'criteria'    => [1, 1],
            ],
            [
                'code'        => 'G25.1',
                'title'       => 'Другие уточненные треморы',
                'description' => 'Уточненные двигательные нарушения, проявляющиеся тремором.',
                'relatives'   => [
                    'G22', 'F12.4',
                ],
                'exceptions'  => [
                    'G21.8', 'F13.4',
                ],
                'symptoms'    => [
                    'required' => [
                        SymptomsEnum::Tremor,
                    ],
                    'relative' => [
                        SymptomsEnum::MuscleRigidity,
                        SymptomsEnum::PosturalInstability,
                    ],
                ],
                'criteria'    => [1, 1],
            ],
            [
                'code'        => 'G25.2',
                'title'       => 'Другие уточненные двигательные нарушения',
                'description' => 'Нарушения, не подпадающие под другие категории, с двигательными проявлениями.',
                'relatives'   => [
                    'G22', 'G21.8',
                ],
                'exceptions'  => [
                    'F12.4', 'F13.4',
                ],
                'symptoms'    => [
                    'required' => [
                        SymptomsEnum::Bradykinesia,
                        SymptomsEnum::Tremor,
                    ],
                    'relative' => [
                        SymptomsEnum::PosturalInstability,
                    ],
                ],
                'criteria'    => [2, 1],
            ],
            [
                'code'        => 'G40.9',
                'title'       => 'Эпилепсия неуточненная',
                'description' => 'Состояние, характеризующееся повторяющимися эпилептическими приступами, причина которых не определена.',
                'relatives'   => ['G40.0', 'G40.1', 'G40.2', 'G40.3'],
                'exceptions'  => ['R56.8', 'F44.5'],
                'symptoms'    => [
                    'required' => [
                        SymptomsEnum::UncontrolledMovements,
                        SymptomsEnum::LossOfConsciousness,
                        SymptomsEnum::MuscleSpasms,
                    ],
                    'relative' => [
                        SymptomsEnum::Aura,
                        SymptomsEnum::Confusion,
                        SymptomsEnum::Fatigue,
                        SymptomsEnum::Amnesia,
                    ],
                ],
                'criteria'    => [2, 1],
            ],
            [
                'code'        => 'G20',
                'title'       => 'Болезнь Паркинсона',
                'description' => 'Хроническое нейродегенеративное заболевание центральной нервной системы, характеризующееся медленной прогрессией и проявляющееся нарушениями двигательной активности.',
                'relatives'   => ['G21.1', 'G21.2'], // Паркинсонизм и вторичные формы
                'exceptions'  => ['F03', 'G25.0'],   // Исключения: деменция неуточненная, эссенциальный тремор
                'symptoms'    => [
                    'required' => [
                        SymptomsEnum::Bradykinesia,         // Замедленность движений
                        SymptomsEnum::RestingTremor,       // Тремор в покое
                        SymptomsEnum::MuscleRigidity,      // Мышечная ригидность
                    ],
                    'relative' => [
                        SymptomsEnum::PosturalInstability, // Нарушение осанки и равновесия
                        SymptomsEnum::DepressedMood,       // Подавленное настроение
                        SymptomsEnum::SleepDisturbances,   // Нарушение сна
                        SymptomsEnum::Fatigue,             // Усталость или потеря энергии
                    ],
                ],
                'criteria'    => [3, 1], // 3 обязательных симптома и 1 относительный
            ],
            [
                'code'        => 'R25.1',
                'title'       => 'Тремор',
                'description' => 'Непроизвольные ритмичные колебания какой-либо части тела, вызванные непроизвольным сокращением мышц.',
                'relatives'   => ['G20', 'G40.9'], // Связанные диагнозы
                'exceptions'  => ['F45.41', 'F91.2'], // Исключаемые диагнозы
                'symptoms'    => [
                    'required' => [
                        SymptomsEnum::MuscleSpasms,
                        SymptomsEnum::UncontrolledMovements,
                    ],
                    'relative' => [
                        SymptomsEnum::Fatigue,
                        SymptomsEnum::PsychologicalFactors,
                        SymptomsEnum::Agitation,
                    ],
                ],
                'criteria'    => [2, 1],
            ],
            [
                'code'        => 'G47.0',
                'title'       => 'Расстройства засыпания и поддержания сна [инсомния]',
                'description' => 'Характеризуется затруднением засыпания, поддержания сна или преждевременным пробуждением, приводящим к нарушению дневного функционирования. Диагноз ставится, если эти симптомы сохраняются в течение длительного времени и не связаны с физиологическими или медицинскими причинами.',
                'relatives'   => ['F51.0', 'G47.1'],
                'exceptions'  => ['F32.2', 'F32.3'], // Уточнено на основании МКБ-10
                'symptoms'    => [
                    'required' => [
                        SymptomsEnum::Insomnia,
                        SymptomsEnum::Fatigue,
                        SymptomsEnum::PoorConcentration,
                    ],
                    'relative' => [
                        SymptomsEnum::Anxiety,
                        SymptomsEnum::Irritability,
                        SymptomsEnum::Restlessness,
                    ],
                ],
                'criteria'    => [2, 1], // Требуется минимум два обязательных и один относительный симптом
            ],
            [
                'code'        => 'G47.1',
                'title'       => 'Гиперсомния',
                'description' => 'Расстройство, характеризующееся чрезмерной сонливостью, которая проявляется длительным сном ночью или эпизодами сна в течение дня. Часто связано с недостаточной эффективностью сна или медицинскими состояниями.',
                'relatives'   => ['F51.1', 'G47.0'],
                'exceptions'  => ['F32.3', 'F33.3'], // Уточнено на основании МКБ-10
                'symptoms'    => [
                    'required' => [
                        SymptomsEnum::Hypersomnia,
                        SymptomsEnum::Fatigue,
                        SymptomsEnum::PoorJudgement,
                    ],
                    'relative' => [
                        SymptomsEnum::MemoryLoss,
                        SymptomsEnum::Irritability,
                        SymptomsEnum::AppetiteChanges,
                    ],
                ],
                'criteria'    => [2, 1], // Требуется минимум два обязательных и один относительный симптом
            ],
        ];

        Diagnose::truncate();
        Symptom::truncate();
        DiagnoseSymptom::truncate();

        foreach (SymptomsEnum::all() as $symptomTitle) {
            $symptom = new Symptom();
            $symptom->title = $symptomTitle;
            $symptom->save();
        }

        foreach ($diagnoses as $diagnose) {
            $diagnoseObj = new Diagnose();
            $diagnoseObj->title = $diagnose['title'];
            $diagnoseObj->code = $diagnose['code'];
            $diagnoseObj->description = $diagnose['description'];
            $diagnoseObj->relatives = $diagnose['relatives'];
            $diagnoseObj->exceptions = $diagnose['exceptions'];
            $diagnoseObj->required_criteria = $diagnose['criteria'][0];
            $diagnoseObj->relative_criteria = $diagnose['criteria'][1];
            $diagnoseObj->save();

            foreach ($diagnose['symptoms']['required'] as $requiredSymptom) {
                $diagnoseSymptom = new DiagnoseSymptom();
                $diagnoseSymptom->diagnose_id = $diagnoseObj->id;

                try {
                    $diagnoseSymptom->symptom_id =
                        Symptom::where('title', $requiredSymptom->value ?? $requiredSymptom)->first()->id;
                } catch (\Exception $e) {
                    continue;
                }

                $diagnoseSymptom->required = true;
                $diagnoseSymptom->save();
            }

            foreach ($diagnose['symptoms']['relative'] as $relativeSymptom) {
                $diagnoseSymptom = new DiagnoseSymptom();
                $diagnoseSymptom->diagnose_id = $diagnoseObj->id;

                try {
                    $diagnoseSymptom->symptom_id = Symptom::where('title', $relativeSymptom->value ?? $relativeSymptom)->first()->id;
                } catch (\Exception $e) {
                    continue;
                }

                $diagnoseSymptom->required = false;
                $diagnoseSymptom->save();
            }
        }
    }
}
