<?php

namespace Database\Seeders;

use App\Enums\SideEffectsEnum;
use App\Models\SideEffect;
use Illuminate\Database\Seeder;

class SideEffectsFillSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void {
        $array = [];
        SideEffect::truncate();
        foreach (SideEffectsEnum::cases() as $sideEffect) {
            switch ($sideEffect) {
                case SideEffectsEnum::Drowsiness:
                    $array = [
                        'name'        => 'Сонливость',
                        'code'        => 1,
                        'description' => '',
                    ];
                    SideEffect::create($array);
                    break;

                case SideEffectsEnum::Asthenia:
                    $array = [
                        'name'        => 'Астения',
                        'code'        => 2,
                        'description' => '',
                    ];
                    SideEffect::create($array);
                    break;

                case SideEffectsEnum::FaintingStates:
                    $array = [
                        'name'        => 'Обморок',
                        'code'        => 3,
                        'description' => '',
                    ];
                    SideEffect::create($array);
                    break;

                case SideEffectsEnum::Restlessness:
                    $array = [
                        'name'        => 'Повышенная утомляемость',
                        'code'        => 4,
                        'description' => '',
                    ];
                    SideEffect::create($array);
                    break;

                case SideEffectsEnum::Disorientation:
                    $array = [
                        'name'        => 'Дезориентация',
                        'code'        => 5,
                        'description' => '',
                    ];
                    SideEffect::create($array);
                    break;

                case SideEffectsEnum::Excitation:
                    $array = [
                        'name'        => 'Возбуждение',
                        'code'        => 6,
                        'description' => '',
                    ];
                    SideEffect::create($array);
                    break;

                case SideEffectsEnum::Hallucinations:
                    $array = [
                        'name'        => 'Галлюцинация',
                        'code'        => 7,
                        'description' => '',
                    ];
                    SideEffect::create($array);
                    break;

                case SideEffectsEnum::Anxiety:
                    $array = [
                        'name'        => 'Тревога',
                        'code'        => 8,
                        'description' => '',
                    ];
                    SideEffect::create($array);
                    break;

                case SideEffectsEnum::MotorRestlessness:
                    $array = [
                        'name'        => 'Психомоторное возбуждение',
                        'code'        => 9,
                        'description' => '',
                    ];
                    SideEffect::create($array);
                    break;

                case SideEffectsEnum::ManiaState:
                    $array = [
                        'name'        => 'Маниакальное состояние',
                        'code'        => 10,
                        'description' => '',
                    ];
                    SideEffect::create($array);
                    break;

                case SideEffectsEnum::HypomaniaState:
                    $array = [
                        'name'        => 'Гипомания',
                        'code'        => 11,
                        'description' => '',
                    ];
                    SideEffect::create($array);
                    break;

                case SideEffectsEnum::Aggression:
                    $array = [
                        'name'        => 'Агрессия',
                        'code'        => 12,
                        'description' => '',
                    ];
                    SideEffect::create($array);
                    break;

                case SideEffectsEnum::MemoryImpairment    :
                    $array = [
                        'name'        => 'Нарушение памяти',
                        'code'        => 13,
                        'description' => '',
                    ];
                    SideEffect::create($array);
                    break;

                case SideEffectsEnum::Depersonalization:
                    $array = [
                        'name'        => 'Деперсонализация',
                        'code'        => 14,
                        'description' => '',
                    ];
                    SideEffect::create($array);
                    break;

                case SideEffectsEnum::IncreasedDepression:
                    $array = [
                        'name'        => 'Усиление депрессии',
                        'code'        => 15,
                        'description' => '',
                    ];
                    SideEffect::create($array);
                    break;

                case SideEffectsEnum::AttentionConcentrateDecreased:
                    $array = [
                        'name'        => 'Нарушение концентрации и внимания',
                        'code'        => 16,
                        'description' => '',
                    ];
                    SideEffect::create($array);
                    break;

                case SideEffectsEnum::Insomnia:
                    $array = [
                        'name'        => 'Бессоница',
                        'code'        => 17,
                        'description' => '',
                    ];
                    SideEffect::create($array);
                    break;

                case SideEffectsEnum::Nightmares:
                    $array = [
                        'name'        => 'Кошмары',
                        'code'        => 18,
                        'description' => '',
                    ];
                    SideEffect::create($array);
                    break;

                case SideEffectsEnum::Yawn:
                    $array = [
                        'name'        => 'Зевота',
                        'code'        => 19,
                        'description' => '',
                    ];
                    SideEffect::create($array);
                    break;

                case SideEffectsEnum::PsychosisSymptoms:
                    $array = [
                        'name'        => 'Провления психоза',
                        'code'        => 20,
                        'description' => '',
                    ];
                    SideEffect::create($array);
                    break;

                case SideEffectsEnum::Headache:
                    $array = [
                        'name'        => 'Головная боль',
                        'code'        => 21,
                        'description' => '',
                    ];
                    SideEffect::create($array);
                    break;

                case SideEffectsEnum::Myoclonus:
                    $array = [
                        'name'        => 'Миоклонус',
                        'code'        => 22,
                        'description' => '',
                    ];
                    SideEffect::create($array);
                    break;

                case SideEffectsEnum::Disarthria:
                    $array = [
                        'name'        => 'Дизартрия',
                        'code'        => 23,
                        'description' => '',
                    ];
                    SideEffect::create($array);
                    break;

                case SideEffectsEnum::Tremor    :
                    $array = [
                        'name'        => 'Тремор',
                        'code'        => 24,
                        'description' => '',
                    ];
                    SideEffect::create($array);
                    break;

                case SideEffectsEnum::Paresthesia    :
                    $array = [
                        'name'        => 'Парастезия',
                        'code'        => 25,
                        'description' => '',
                    ];
                    SideEffect::create($array);
                    break;

                case SideEffectsEnum::MyastheniaGravis:
                    $array = [
                        'name'        => 'Миастения',
                        'code'        => 26,
                        'description' => '',
                    ];
                    SideEffect::create($array);
                    break;

                case SideEffectsEnum::Ataxia:
                    $array = [
                        'name'        => 'Атаксия',
                        'code'        => 27,
                        'description' => '',
                    ];
                    SideEffect::create($array);
                    break;

                case SideEffectsEnum::ExtrapyramidalSyndrome:
                    $array = [
                        'name'        => 'Экстрапирамидальные расстройства',
                        'code'        => 28,
                        'description' => '',
                    ];
                    SideEffect::create($array);
                    break;

                case SideEffectsEnum::IncreasedFrequencyAndIntensificationOfEpilepticSeizures:
                    $array = [
                        'name'        => 'Увеличение частоты и интенсивности эпилептических припадков',
                        'code'        => 29,
                        'description' => '',
                    ];
                    SideEffect::create($array);
                    break;

                case SideEffectsEnum::EEGChanges:
                    $array = [
                        'name'        => 'Изменения ЭЭГ',
                        'code'        => 30,
                        'description' => '',
                    ];
                    SideEffect::create($array);
                    break;

                case SideEffectsEnum::OrthostaticHypotension:
                    $array = [
                        'name'        => 'Ортостатическая гипотензия',
                        'code'        => 31,
                        'description' => '',
                    ];
                    SideEffect::create($array);
                    break;

                case SideEffectsEnum::Tachycardia:
                    $array = [
                        'name'        => 'Тахикардия',
                        'code'        => 32,
                        'description' => '',
                    ];
                    SideEffect::create($array);
                    break;

                case SideEffectsEnum::CardiacConductionDisorder:
                    $array = [
                        'name'        => 'Нарушение сердечной проводимости',
                        'code'        => 33,
                        'description' => '',
                    ];
                    SideEffect::create($array);
                    break;

                case SideEffectsEnum::Dizziness:
                    $array = [
                        'name'        => 'Головокружение',
                        'code'        => 34,
                        'description' => '',
                    ];
                    SideEffect::create($array);
                    break;

                case SideEffectsEnum::NonspecificECGChanges:
                    $array = [
                        'name'        => 'Неспецифические изменения ЭЭГ',
                        'code'        => 35,
                        'description' => '',
                    ];
                    SideEffect::create($array);
                    break;

                case SideEffectsEnum::Arrhythmia:
                    $array = [
                        'name'        => 'Аритмия',
                        'code'        => 36,
                        'description' => '',
                    ];
                    SideEffect::create($array);
                    break;

                case SideEffectsEnum::BloodPressureLability:
                    $array = [
                        'name'        => 'Лабильность кровянного давления',
                        'code'        => 37,
                        'description' => '',
                    ];
                    SideEffect::create($array);
                    break;

                case SideEffectsEnum::IntraventricularConductionDisorder:
                    $array = [
                        'name'        => 'Нарушение внутрижелудочковой проводимости',
                        'code'        => 38,
                        'description' => '',
                    ];
                    SideEffect::create($array);
                    break;

                case SideEffectsEnum::Nausea:
                    $array = [
                        'name'        => 'Тошнота',
                        'code'        => 39,
                        'description' => '',
                    ];
                    SideEffect::create($array);
                    break;

                case SideEffectsEnum::Vomiting:
                    $array = [
                        'name'        => 'Рвота',
                        'code'        => 40,
                        'description' => '',
                    ];
                    SideEffect::create($array);
                    break;

                case SideEffectsEnum::Heartburn:
                    $array = [
                        'name'        => 'Изжога',
                        'code'        => 41,
                        'description' => '',
                    ];
                    SideEffect::create($array);
                    break;

                case SideEffectsEnum::Gastralgia:
                    $array = [
                        'name'        => 'Гастралгия',
                        'code'        => 42,
                        'description' => '',
                    ];
                    SideEffect::create($array);
                    break;

                case SideEffectsEnum::BodyWeightIncrease:
                    $array = [
                        'name'        => 'Увеличение массы тела',
                        'code'        => 43,
                        'description' => '',
                    ];
                    SideEffect::create($array);
                    break;

                case SideEffectsEnum::Stomatitis:
                    $array = [
                        'name'        => 'Стоматит',
                        'code'        => 44,
                        'description' => '',
                    ];
                    SideEffect::create($array);
                    break;

                case SideEffectsEnum::TasteChanges:
                    $array = [
                        'name'        => 'Вкусовые изменения',
                        'code'        => 45,
                        'description' => '',
                    ];
                    SideEffect::create($array);
                    break;

                case SideEffectsEnum::Diarrhea:
                    $array = [
                        'name'        => 'Диарея',
                        'code'        => 46,
                        'description' => '',
                    ];
                    SideEffect::create($array);
                    break;

                case SideEffectsEnum::TongueDarkening:
                    $array = [
                        'name'        => 'Потемнение языка',
                        'code'        => 47,
                        'description' => '',
                    ];
                    SideEffect::create($array);
                    break;

                case SideEffectsEnum::LiverDiseases:
                    $array = [
                        'name'        => 'Болезни печени',
                        'code'        => 48,
                        'description' => '',
                    ];
                    SideEffect::create($array);
                    break;

                case SideEffectsEnum::CholestaticJaundice:
                    $array = [
                        'name'        => 'Холестатическая желтуха',
                        'code'        => 49,
                        'description' => '',
                    ];
                    SideEffect::create($array);
                    break;

                case SideEffectsEnum::Hepatitis:
                    $array = [
                        'name'        => 'Гепатит',
                        'code'        => 50,
                        'description' => '',
                    ];
                    SideEffect::create($array);
                    break;

                case SideEffectsEnum::TesticularSwelling:
                    $array = [
                        'name'        => 'Отёк тестикул',
                        'code'        => 51,
                        'description' => '',
                    ];
                    SideEffect::create($array);
                    break;

                case SideEffectsEnum::Gynecomastia:
                    $array = [
                        'name'        => 'Гинекомастия',
                        'code'        => 52,
                        'description' => '',
                    ];
                    SideEffect::create($array);
                    break;

                case SideEffectsEnum::MammaryGlandEnlargement:
                    $array = [
                        'name'        => 'Увеличение молочных желёз',
                        'code'        => 53,
                        'description' => '',
                    ];
                    SideEffect::create($array);
                    break;

                case SideEffectsEnum::LibidoChange:
                    $array = [
                        'name'        => 'Изменения в либидо',
                        'code'        => 54,
                        'description' => '',
                    ];
                    SideEffect::create($array);
                    break;

                case SideEffectsEnum::PotencyDecrease:
                    $array = [
                        'name'        => 'Снижение потенции',
                        'code'        => 55,
                        'description' => '',
                    ];
                    SideEffect::create($array);
                    break;

                case SideEffectsEnum::Hypoglycemia:
                    $array = [
                        'name'        => 'Гипогликемия',
                        'code'        => 56,
                        'description' => '',
                    ];
                    SideEffect::create($array);
                    break;

                case SideEffectsEnum::Hyperglycemia:
                    $array = [
                        'name'        => 'Гипергликемия',
                        'code'        => 57,
                        'description' => '',
                    ];
                    SideEffect::create($array);
                    break;

                case SideEffectsEnum::Hyponatremia:
                    $array = [
                        'name'        => 'Гипонатриемия',
                        'code'        => 58,
                        'description' => '',
                    ];
                    SideEffect::create($array);
                    break;

                case SideEffectsEnum::InappropriateADHSecretionSyndrome:
                    $array = [
                        'name'        => 'Синдром неадекватной секреции АДГ',
                        'code'        => 59,
                        'description' => '',
                    ];
                    SideEffect::create($array);
                    break;

                case SideEffectsEnum::Agranulocytosis:
                    $array = [
                        'name'        => 'Агранулоцитоз',
                        'code'        => 60,
                        'description' => '',
                    ];
                    SideEffect::create($array);
                    break;

                case SideEffectsEnum::Leukopenia:
                    $array = [
                        'name'        => 'Лейкопения',
                        'code'        => 61,
                        'description' => '',
                    ];
                    SideEffect::create($array);
                    break;

                case SideEffectsEnum::Thrombocytopenia:
                    $array = [
                        'name'        => 'Тромбоцитопения',
                        'code'        => 62,
                        'description' => '',
                    ];
                    SideEffect::create($array);
                    break;

                case SideEffectsEnum::Purpura:
                    $array = [
                        'name'        => 'Пурпура',
                        'code'        => 63,
                        'description' => '',
                    ];
                    SideEffect::create($array);
                    break;

                case SideEffectsEnum::Eosinophilia:
                    $array = [
                        'name'        => 'Эозинофилия',
                        'code'        => 64,
                        'description' => '',
                    ];
                    SideEffect::create($array);
                    break;

                case SideEffectsEnum::SkinRash:
                    $array = [
                        'name'        => 'Кожная сыпь',
                        'code'        => 65,
                        'description' => '',
                    ];
                    SideEffect::create($array);
                    break;

                case SideEffectsEnum::ItchySkin:
                    $array = [
                        'name'        => 'Зуд',
                        'code'        => 66,
                        'description' => '',
                    ];
                    SideEffect::create($array);
                    break;

                case SideEffectsEnum::Hives:
                    $array = [
                        'name'        => 'Крапивница',
                        'code'        => 67,
                        'description' => '',
                    ];
                    SideEffect::create($array);
                    break;

                case SideEffectsEnum::Photosensitivity:
                    $array = [
                        'name'        => 'Фотосенсибилизация',
                        'code'        => 68,
                        'description' => '',
                    ];
                    SideEffect::create($array);
                    break;

                case SideEffectsEnum::FaceAndTongueSwelling:
                    $array = [
                        'name'        => 'Отёк лица и языка',
                        'code'        => 69,
                        'description' => '',
                    ];
                    SideEffect::create($array);
                    break;

                case SideEffectsEnum::DryMouth:
                    $array = [
                        'name'        => 'Сухость во рту',
                        'code'        => 70,
                        'description' => '',
                    ];
                    SideEffect::create($array);
                    break;

                case SideEffectsEnum::AccommodationDisturbances:
                    $array = [
                        'name'        => 'Нарушения аккомодации',
                        'code'        => 71,
                        'description' => '',
                    ];
                    SideEffect::create($array);
                    break;

                case SideEffectsEnum::BlurredVision:
                    $array = [
                        'name'        => 'Рассплывчивое зрение',
                        'code'        => 72,
                        'description' => '',
                    ];
                    SideEffect::create($array);
                    break;

                case SideEffectsEnum::Mydriasis:
                    $array = [
                        'name'        => 'Мидриаз',
                        'code'        => 73,
                        'description' => '',
                    ];
                    SideEffect::create($array);
                    break;

                case SideEffectsEnum::IntraocularPressureIncrease:
                    $array = [
                        'name'        => 'Увеличение внутриглазного давления',
                        'code'        => 74,
                        'description' => '',
                    ];
                    SideEffect::create($array);
                    break;

                case SideEffectsEnum::Constipation:
                    $array = [
                        'name'        => 'Запор',
                        'code'        => 75,
                        'description' => '',
                    ];
                    SideEffect::create($array);
                    break;

                case SideEffectsEnum::ParalyticObstruction:
                    $array = [
                        'name'        => 'Паралитическая обструкция кишечника',
                        'code'        => 76,
                        'description' => '',
                    ];
                    SideEffect::create($array);
                    break;

                case SideEffectsEnum::UrinaryRetention:
                    $array = [
                        'name'        => 'Задержка мочеиспускания',
                        'code'        => 77,
                        'description' => '',
                    ];
                    SideEffect::create($array);
                    break;

                case SideEffectsEnum::SweatingDecrease:
                    $array = [
                        'name'        => 'Уменьшение потоотделения',
                        'code'        => 78,
                        'description' => '',
                    ];
                    SideEffect::create($array);
                    break;

                case SideEffectsEnum::Confusion:
                    $array = [
                        'name'        => 'Спутанность сознания',
                        'code'        => 79,
                        'description' => '',
                    ];
                    SideEffect::create($array);
                    break;

                case SideEffectsEnum::Delirium:
                    $array = [
                        'name'        => 'Делирий',
                        'code'        => 80,
                        'description' => '',
                    ];
                    SideEffect::create($array);
                    break;

                case SideEffectsEnum::HairLoss:
                    $array = [
                        'name'        => 'Выпадение волос',
                        'code'        => 81,
                        'description' => '',
                    ];
                    SideEffect::create($array);
                    break;

                case SideEffectsEnum::Tinnitus:
                    $array = [
                        'name'        => 'Звон в ушах',
                        'code'        => 82,
                        'description' => '',
                    ];
                    SideEffect::create($array);
                    break;

                case SideEffectsEnum::Edema:
                    $array = [
                        'name'        => 'Отёк',
                        'code'        => 83,
                        'description' => '',
                    ];
                    SideEffect::create($array);
                    break;

                case SideEffectsEnum::Hyperpyrexia:
                    $array = [
                        'name'        => 'Гиперпирексия',
                        'code'        => 84,
                        'description' => 'Повышение температуры тела',
                    ];
                    SideEffect::create($array);
                    break;

                case SideEffectsEnum::SwollenLymphNodes:
                    $array = [
                        'name'        => 'Увеличение лимфоузлов',
                        'code'        => 85,
                        'description' => '',
                    ];
                    SideEffect::create($array);
                    break;

                case SideEffectsEnum::Pollakiuria:
                    $array = [
                        'name'        => 'Поллакиурия',
                        'code'        => 86,
                        'description' => 'Частые позывы к мочеиспусканию',
                    ];
                    SideEffect::create($array);
                    break;

                case SideEffectsEnum::Hypoproteinemia:
                    $array = [
                        'name'        => 'Гипопротеинемия',
                        'code'        => 87,
                        'description' => '',
                    ];
                    SideEffect::create($array);
                    break;

                case SideEffectsEnum::BodyWeightDecrease:
                    $array = [
                        'name'        => 'Уменьшение веса тела',
                        'code'        => 88,
                        'description' => '',
                    ];
                    SideEffect::create($array);
                    break;

                case SideEffectsEnum::Galactorrhea:
                    $array = [
                        'name'        => 'Галакторея',
                        'code'        => 89,
                        'description' => '',
                    ];
                    SideEffect::create($array);
                    break;

                case SideEffectsEnum::NoiseInEar:
                    $array = [
                        'name'        => 'Шум в ушах',
                        'code'        => 90,
                        'description' => '',
                    ];
                    SideEffect::create($array);
                    break;

                case SideEffectsEnum::Swelling:
                    $array = [
                        'name'        => 'Опухоль',
                        'code'        => 91,
                        'description' => '',
                    ];
                    SideEffect::create($array);
                    break;

                case SideEffectsEnum::Nervousness:
                    $array = [
                        'name'        => 'Нервозность',
                        'code'        => 92,
                        'description' => '',
                    ];
                    SideEffect::create($array);
                    break;

                case SideEffectsEnum::SleepDisorders:
                    $array = [
                        'name'        => 'Нарушения сна',
                        'code'        => 93,
                        'description' => '',
                    ];
                    SideEffect::create($array);
                    break;

                case SideEffectsEnum::StrongFear:
                    $array = [
                        'name'        => 'Сильный страх',
                        'code'        => 94,
                        'description' => '',
                    ];
                    SideEffect::create($array);
                    break;

                case SideEffectsEnum::Seizures:
                    $array = [
                        'name'        => 'Припадки',
                        'code'        => 95,
                        'description' => '',
                    ];
                    SideEffect::create($array);
                    break;

                case SideEffectsEnum::ArterialHypotension:
                    $array = [
                        'name'        => 'Артериальная гипотензия',
                        'code'        => 96,
                        'description' => 'Пониженое кровянное давление',
                    ];
                    SideEffect::create($array);
                    break;

                case SideEffectsEnum::DryMucousMembranes:
                    $array = [
                        'name'        => 'Сухость слизистых',
                        'code'        => 97,
                        'description' => '',
                    ];
                    SideEffect::create($array);
                    break;

                case SideEffectsEnum::EyePain:
                    $array = [
                        'name'        => 'Глазная боль',
                        'code'        => 98,
                        'description' => '',
                    ];
                    SideEffect::create($array);
                    break;

                case SideEffectsEnum::UrinaryDisorders:
                    $array = [
                        'name'        => 'Расстройства мочеиспускания',
                        'code'        => 99,
                        'description' => '',
                    ];
                    SideEffect::create($array);
                    break;

                case SideEffectsEnum::Anorexia:
                    $array = [
                        'name'        => 'Анорексия',
                        'code'        => 100,
                        'description' => '',
                    ];
                    SideEffect::create($array);
                    break;

                case SideEffectsEnum::Dyspepsia:
                    $array = [
                        'name'        => 'Диспепсия',
                        'code'        => 101,
                        'description' => 'Несварение',
                    ];
                    SideEffect::create($array);
                    break;

                case SideEffectsEnum::DiscomfortInTheEpigastricRegion:
                    $array = [
                        'name'        => 'Дискомфорт в эпигастральном пространстве',
                        'code'        => 102,
                        'description' => '',
                    ];
                    SideEffect::create($array);
                    break;

                case SideEffectsEnum::DiscomfortFeelings:
                    $array = [
                        'name'        => 'Дискомфортные ощущения',
                        'code'        => 103,
                        'description' => '',
                    ];
                    SideEffect::create($array);
                    break;

                case SideEffectsEnum::IncreasedActivityOfLiverEnzymes:
                    $array = [
                        'name'        => 'Увеличение активности печёночных ферментов',
                        'code'        => 104,
                        'description' => '',
                    ];
                    SideEffect::create($array);
                    break;

                case SideEffectsEnum::TransientHyponatremia:
                    $array = [
                        'name'        => 'Транзиторная гипонатриемия',
                        'code'        => 105,
                        'description' => '',
                    ];
                    SideEffect::create($array);
                    break;

                case SideEffectsEnum::Fear:
                    $array = [
                        'name'        => 'Страх',
                        'code'        => 106,
                        'description' => '',
                    ];
                    SideEffect::create($array);
                    break;

                case SideEffectsEnum::HeartbeatFeelings:
                    $array = [
                        'name'        => 'Ощущения сердцебиения',
                        'code'        => 107,
                        'description' => '',
                    ];
                    SideEffect::create($array);
                    break;

                case SideEffectsEnum::IncreasedSweating:
                    $array = [
                        'name'        => 'Повышенное потоотделение',
                        'code'        => 108,
                        'description' => '',
                    ];
                    SideEffect::create($array);
                    break;

                case SideEffectsEnum::BodyWeightChange:
                    $array = [
                        'name'        => 'Изменения в массе тела',
                        'code'        => 109,
                        'description' => '',
                    ];
                    SideEffect::create($array);
                    break;

                case SideEffectsEnum::Lethargy:
                    $array = [
                        'name'        => 'Летаргия',
                        'code'        => 110,
                        'description' => '',
                    ];
                    SideEffect::create($array);
                    break;

                case SideEffectsEnum::EmotionalLability:
                    $array = [
                        'name'        => 'Эмоциональная лабильность',
                        'code'        => 111,
                        'description' => '',
                    ];
                    SideEffect::create($array);
                    break;

                case SideEffectsEnum::MentalityChanges:
                    $array = [
                        'name'        => 'Изменения психики',
                        'code'        => 112,
                        'description' => '',
                    ];
                    SideEffect::create($array);
                    break;

                case SideEffectsEnum::Agitation:
                    $array = [
                        'name'        => 'Ажитация',
                        'code'        => 113,
                        'description' => '',
                    ];
                    SideEffect::create($array);
                    break;

                case SideEffectsEnum::Apathy:
                    $array = [
                        'name'        => 'Апатия',
                        'code'        => 114,
                        'description' => '',
                    ];
                    SideEffect::create($array);
                    break;

                case SideEffectsEnum::Hostility:
                    $array = [
                        'name'        => 'Враждебность',
                        'code'        => 115,
                        'description' => '',
                    ];
                    SideEffect::create($array);
                    break;

                case SideEffectsEnum::EpilepticSeizures:
                    $array = [
                        'name'        => 'Эпилептические припадки',
                        'code'        => 116,
                        'description' => '',
                    ];
                    SideEffect::create($array);
                    break;

                case SideEffectsEnum::Vertigo:
                    $array = [
                        'name'        => 'Головокружение',
                        'code'        => 117,
                        'description' => '',
                    ];
                    SideEffect::create($array);
                    break;

                case SideEffectsEnum::Hyperesthesia:
                    $array = [
                        'name'        => 'Гиперестезия',
                        'code'        => 118,
                        'description' => 'Повышенная чувствительность зубов к раздражителям',
                    ];
                    SideEffect::create($array);
                    break;

                case SideEffectsEnum::Hyperkinesis:
                    $array = [
                        'name'        => 'Гиперкинезы',
                        'code'        => 119,
                        'description' => 'Они же - дискинезы - патологические, непроизвольные движения, внезапно возникающие в одной мышце или группе мышц по ошибочной команде головного мозга патологические, непроизвольные движения, внезапно возникающие в одной мышце или группе мышц по ошибочной команде головного мозга',
                    ];
                    SideEffect::create($array);
                    break;

                case SideEffectsEnum::Hyperkinesia:
                    $array = [
                        'name'        => 'Гиперкинезия',
                        'code'        => 120,
                        'description' => 'Расстройство с преобладанием гиперкинезов',
                    ];
                    SideEffect::create($array);
                    break;

                case SideEffectsEnum::Granulocytopenia:
                    $array = [
                        'name'        => 'Гранулоцитопения',
                        'code'        => 121,
                        'description' => '',
                    ];
                    SideEffect::create($array);
                    break;

                case SideEffectsEnum::Neutropenia:
                    $array = [
                        'name'        => 'Нейтропения',
                        'code'        => 122,
                        'description' => '',
                    ];
                    SideEffect::create($array);
                    break;

                case SideEffectsEnum::AplasticAnemia:
                    $array = [
                        'name'        => 'Апластическая анемия',
                        'code'        => 124,
                        'description' => '',
                    ];
                    SideEffect::create($array);
                    break;

                case SideEffectsEnum::Anemia:
                    $array = [
                        'name'        => 'Анемия',
                        'code'        => 125,
                        'description' => '',
                    ];
                    SideEffect::create($array);
                    break;

                case SideEffectsEnum::SlightIncreaseInAppetite:
                    $array = [
                        'name'        => 'Небольшое изменение аппетита',
                        'code'        => 126,
                        'description' => '',
                    ];
                    SideEffect::create($array);
                    break;

                case SideEffectsEnum::StomachAche:
                    $array = [
                        'name'        => 'Боль в животе',
                        'code'        => 127,
                        'description' => '',
                    ];
                    SideEffect::create($array);
                    break;

                case SideEffectsEnum::Thirst:
                    $array = [
                        'name'        => 'Жажда',
                        'code'        => 128,
                        'description' => '',
                    ];
                    SideEffect::create($array);
                    break;

                case SideEffectsEnum::IncreasedActivityOfLiverTransaminases:
                    $array = [
                        'name'        => 'Увеличение активности печёночных трансаминаз',
                        'code'        => 129,
                        'description' => '',
                    ];
                    SideEffect::create($array);
                    break;

                case SideEffectsEnum::DecreasePotency:
                    $array = [
                        'name'        => 'Уменьшение потенции',
                        'code'        => 130,
                        'description' => '',
                    ];
                    SideEffect::create($array);
                    break;

                case SideEffectsEnum::Dysmenorrhea:
                    $array = [
                        'name'        => 'Дисменорея',
                        'code'        => 131,
                        'description' => 'Боль внизу живота, в следствии менструации',
                    ];
                    SideEffect::create($array);
                    break;

                case SideEffectsEnum::Urticaria:
                    $array = [
                        'name'        => 'Крапивница',
                        'code'        => 132,
                        'description' => '',
                    ];
                    SideEffect::create($array);
                    break;

                case SideEffectsEnum::FluLikeSyndrome:
                    $array = [
                        'name'        => 'Гриппоподобный синдром',
                        'code'        => 133,
                        'description' => '',
                    ];
                    SideEffect::create($array);
                    break;

                case SideEffectsEnum::Suffocation:
                    $array = [
                        'name'        => 'Удушье',
                        'code'        => 134,
                        'description' => '',
                    ];
                    SideEffect::create($array);
                    break;

                case SideEffectsEnum::EdemaSyndrome:
                    $array = [
                        'name'        => 'Отёки',
                        'code'        => 135,
                        'description' => '',
                    ];
                    SideEffect::create($array);
                    break;

                case SideEffectsEnum::Myalgia:
                    $array = [
                        'name'        => 'Миалгия',
                        'code'        => 136,
                        'description' => 'Боль в мышцах',
                    ];
                    SideEffect::create($array);
                    break;

                case SideEffectsEnum::BackPain:
                    $array = [
                        'name'        => 'Боль в спине',
                        'code'        => 137,
                        'description' => '',
                    ];
                    SideEffect::create($array);
                    break;

                case SideEffectsEnum::Dysuria:
                    $array = [
                        'name'        => 'Дизурия',
                        'code'        => 138,
                        'description' => 'Болезненное или сопровождающееся дискомфортом, обычно с резью или жжением, мочеиспускание',
                    ];
                    SideEffect::create($array);
                    break;

                case SideEffectsEnum::Akathisia:
                    $array = [
                        'name'        => 'Акатизия',
                        'code'        => 139,
                        'description' => 'Постоянное желание двигаться',
                    ];
                    SideEffect::create($array);
                    break;

                case SideEffectsEnum::DystonicExtrapyramidalDisorders:
                    $array = [
                        'name'        => 'Дистонические экстрапирамидальные расстройства',
                        'code'        => 140,
                        'description' => '',
                    ];
                    SideEffect::create($array);
                    break;

                case SideEffectsEnum::ParkinsonSyndrome:
                    $array = [
                        'name'        => 'Синдром Паркинсона',
                        'code'        => 141,
                        'description' => '',
                    ];
                    SideEffect::create($array);
                    break;

                case SideEffectsEnum::ThermoregulationDisorders:
                    $array = [
                        'name'        => 'Нарушения терморегуляции',
                        'code'        => 142,
                        'description' => '',
                    ];
                    SideEffect::create($array);
                    break;

                case SideEffectsEnum::NeurolepticMalignantSyndrome:
                    $array = [
                        'name'        => 'Злокачественный нейролептический синдром',
                        'code'        => 143,
                        'description' => '',
                    ];
                    SideEffect::create($array);
                    break;

                case SideEffectsEnum::DyspepticSymptoms:
                    $array = [
                        'name'        => 'Симптомы диспепсии',
                        'code'        => 144,
                        'description' => '',
                    ];
                    SideEffect::create($array);
                    break;

                case SideEffectsEnum::MenstrualIrregularities:
                    $array = [
                        'name'        => 'Нерегулярные менструации',
                        'code'        => 145,
                        'description' => '',
                    ];
                    SideEffect::create($array);
                    break;

                case SideEffectsEnum::Impotence:
                    $array = [
                        'name'        => 'Импотенция',
                        'code'        => 146,
                        'description' => '',
                    ];
                    SideEffect::create($array);
                    break;

                case SideEffectsEnum::ExfoliativeDermatitis:
                    $array = [
                        'name'        => 'Эксфолиативный дерматит',
                        'code'        => 147,
                        'description' => '',
                    ];
                    SideEffect::create($array);
                    break;

                case SideEffectsEnum::ErythemaMultiforme:
                    $array = [
                        'name'        => 'Мультиформная эритрема',
                        'code'        => 148,
                        'description' => '',
                    ];
                    SideEffect::create($array);
                    break;

                case SideEffectsEnum::SkinPigmentation:
                    $array = [
                        'name'        => 'Пигментация кожи',
                        'code'        => 149,
                        'description' => '',
                    ];
                    SideEffect::create($array);
                    break;

                case SideEffectsEnum::DrugDeposits:
                    $array = [
                        'name'        => 'Злоупотребление препаратом',
                        'code'        => 150,
                        'description' => '',
                    ];
                    SideEffect::create($array);
                    break;

                case SideEffectsEnum::TardiveDyskinesia:
                    $array = [
                        'name'        => 'Подзняя дискинезия',
                        'code'        => 151,
                        'description' => '',
                    ];
                    SideEffect::create($array);
                    break;

                case SideEffectsEnum::DyspepticSymptom:
                    $array = [
                        'name'        => 'Диспепсия',
                        'code'        => 152,
                        'description' => '',
                    ];
                    SideEffect::create($array);
                    break;

                case SideEffectsEnum::NasalCongestion:
                    $array = [
                        'name'        => 'Заложенность носа',
                        'code'        => 153,
                        'description' => '',
                    ];
                    SideEffect::create($array);
                    break;

                case SideEffectsEnum::PigmentaryRetinopathy:
                    $array = [
                        'name'        => 'Пигментная ретинопатия',
                        'code'        => 154,
                        'description' => '',
                    ];
                    SideEffect::create($array);
                    break;

                case SideEffectsEnum::HeartRhythmDisturbances:
                    $array = [
                        'name'        => 'Нарушение ритма сердца',
                        'code'        => 155,
                        'description' => '',
                    ];
                    SideEffect::create($array);
                    break;

                case SideEffectsEnum::AllergicReaction:
                    $array = [
                        'name'        => 'Алергическая реакция',
                        'code'        => 156,
                        'description' => '',
                    ];
                    SideEffect::create($array);
                    break;

                case SideEffectsEnum::Angioedema:
                    $array = [
                        'name'        => 'Ангиоедема',
                        'code'        => 157,
                        'description' => '',
                    ];
                    SideEffect::create($array);
                    break;

                case SideEffectsEnum::Priapism:
                    $array = [
                        'name'        => 'Приапизм',
                        'code'        => 158,
                        'description' => 'Долгое болезненное сексуальное возбуждение у мужчин',
                    ];
                    SideEffect::create($array);
                    break;

                case SideEffectsEnum::Melanosis:
                    $array = [
                        'name'        => 'Меланоз',
                        'code'        => 159,
                        'description' => 'Усиленное образование и повышенное отложение в органах и тканях тёмно-коричневого или чёрного пигмента',
                    ];
                    SideEffect::create($array);
                    break;
                case SideEffectsEnum::Sedation:
                    $array = [
                        'name' => 'Седация',
                        'code' => 160,
                        'description' => ''
                    ];
                    SideEffect::create($array);
                    break;
                case SideEffectsEnum::Sedation:
                    $array = [
                        'name' => 'Седация',
                        'code' => 160,
                        'description' => ''
                    ];
                    SideEffect::create($array);
                    break;

                case SideEffectsEnum::PsychomotorDisorders:
                    $array = [
                        'name' => 'Психомоторные расстройства',
                        'code' => 161,
                        'description' => ''
                    ];
                    SideEffect::create($array);
                    break;

                case SideEffectsEnum::PosturalHypotension:
                    $array = [
                        'name' => 'Постуральная гипотензия',
                        'code' => 162,
                        'description' => ''
                    ];
                    SideEffect::create($array);
                    break;

                case SideEffectsEnum::VisionImpairment:
                    $array = [
                        'name' => 'Нарушение зрения',
                        'code' => 163,
                        'description' => ''
                    ];
                    SideEffect::create($array);
                    break;

                case SideEffectsEnum::SwellingOfTheNipples:
                    $array = [
                        'name' => 'Отёк сосков',
                        'code' => 164,
                        'description' => ''
                    ];
                    SideEffect::create($array);
                    break;

                case SideEffectsEnum::InhibitionOfEjaculation:
                    $array = [
                        'name' => 'Задержка эякуляции',
                        'code' => 165,
                        'description' => ''
                    ];
                    SideEffect::create($array);
                    break;

                case SideEffectsEnum::IncreasedFatigue:
                    $array = [
                        'name' => 'Повышенная усталость',
                        'code' => 166,
                        'description' => ''
                    ];
                    SideEffect::create($array);
                    break;

                case SideEffectsEnum::ECGChanges:
                    $array = [
                        'name' => 'Изменения на ЭКГ',
                        'code' => 167,
                        'description' => ''
                    ];
                    SideEffect::create($array);
                    break;

                case SideEffectsEnum::CornealOpacity:
                    $array = [
                        'name' => 'Помутнение роговицы',
                        'code' => 168,
                        'description' => ''
                    ];
                    SideEffect::create($array);
                    break;

                case SideEffectsEnum::Cataract:
                    $array = [
                        'name' => 'Катаракта',
                        'code' => 169,
                        'description' => ''
                    ];
                    SideEffect::create($array);
                    break;

                case SideEffectsEnum::Leukocytosis:
                    $array = [
                        'name' => 'Лейкоцитоз',
                        'code' => 170,
                        'description' => ''
                    ];
                    SideEffect::create($array);
                    break;

                case SideEffectsEnum::HemolyticAnemia:
                    $array = [
                        'name' => 'Гемолитическая анемия',
                        'code' => 171,
                        'description' => ''
                    ];
                    SideEffect::create($array);
                    break;

                case SideEffectsEnum::HotFlushes:
                    $array = [
                        'name' => 'Приливы',
                        'code' => 172,
                        'description' => ''
                    ];
                    SideEffect::create($array);
                    break;

                case SideEffectsEnum::Amenorrhea:
                    $array = [
                        'name' => 'Аменорея',
                        'code' => 173,
                        'description' => ''
                    ];
                    SideEffect::create($array);
                    break;

                case SideEffectsEnum::LibidoDecrease:
                    $array = [
                        'name' => 'Снижение либидо',
                        'code' => 174,
                        'description' => ''
                    ];
                    SideEffect::create($array);
                    break;

                case SideEffectsEnum::CarbohydrateMetabolismDisorder:
                    $array = [
                        'name' => 'Нарушение углеводного обмена',
                        'code' => 175,
                        'description' => ''
                    ];
                    SideEffect::create($array);
                    break;

                case SideEffectsEnum::AppetiteIncrease:
                    $array = [
                        'name' => 'Повышение аппетита',
                        'code' => 176,
                        'description' => ''
                    ];
                    SideEffect::create($array);
                    break;

                case SideEffectsEnum::OculogyricCrises:
                    $array = [
                        'name' => 'Окулогирный криз',
                        'code' => 177,
                        'description' => ''
                    ];
                    SideEffect::create($array);
                    break;

                case SideEffectsEnum::DystonicPhenomena:
                    $array = [
                        'name' => 'Дистонические феномены',
                        'code' => 178,
                        'description' => ''
                    ];
                    SideEffect::create($array);
                    break;

                case SideEffectsEnum::BloodPressureLability:
                    $array = [
                        'name' => 'Лабильность артериального давления',
                        'code' => 179,
                        'description' => ''
                    ];
                    SideEffect::create($array);
                    break;

                case SideEffectsEnum::Erythropenia:
                    $array = [
                        'name' => 'Эритропения',
                        'code' => 180,
                        'description' => ''
                    ];
                    SideEffect::create($array);
                    break;

                case SideEffectsEnum::Lymphomonocytosis:
                    $array = [
                        'name' => 'Лимфомоноцитоз',
                        'code' => 181,
                        'description' => ''
                    ];
                    SideEffect::create($array);
                    break;
                case SideEffectsEnum::Jaundice:
                    $array = [
                        'name' => 'Желтуха',
                        'code' => 182,
                        'description' => ''
                    ];
                    SideEffect::create($array);
                    break;

                case SideEffectsEnum::Toxicoderma:
                    $array = [
                        'name' => 'Токсидермия',
                        'code' => 183,
                        'description' => ''
                    ];
                    SideEffect::create($array);
                    break;

                case SideEffectsEnum::DrySkin:
                    $array = [
                        'name' => 'Сухость кожи',
                        'code' => 184,
                        'description' => ''
                    ];
                    SideEffect::create($array);
                    break;

                case SideEffectsEnum::SebaceousGlandsHyperfunction:
                    $array = [
                        'name' => 'Гиперфункция сальных желез',
                        'code' => 185,
                        'description' => ''
                    ];
                    SideEffect::create($array);
                    break;

                case SideEffectsEnum::Hypersalivation:
                    $array = [
                        'name' => 'Гиперсаливация',
                        'code' => 186,
                        'description' => ''
                    ];
                    SideEffect::create($array);
                    break;

                case SideEffectsEnum::Frigidity:
                    $array = [
                        'name' => 'Фригидность',
                        'code' => 187,
                        'description' => ''
                    ];
                    SideEffect::create($array);
                    break;

                case SideEffectsEnum::ThirstReduction:
                    $array = [
                        'name' => 'Снижение жажды',
                        'code' => 188,
                        'description' => ''
                    ];
                    SideEffect::create($array);
                    break;

                case SideEffectsEnum::HeatStroke:
                    $array = [
                        'name' => 'Тепловой удар',
                        'code' => 189,
                        'description' => ''
                    ];
                    SideEffect::create($array);
                    break;

                case SideEffectsEnum::Alopecia:
                    $array = [
                        'name' => 'Алопеция',
                        'code' => 190,
                        'description' => ''
                    ];
                    SideEffect::create($array);
                    break;

                case SideEffectsEnum::Hypoglycemia:
                    $array = [
                        'name' => 'Гипогликемия',
                        'code' => 191,
                        'description' => ''
                    ];
                    SideEffect::create($array);
                    break;

                case SideEffectsEnum::Hyperprolactinemia:
                    $array = [
                        'name' => 'Гиперпролактинемия',
                        'code' => 192,
                        'description' => ''
                    ];
                    SideEffect::create($array);
                    break;

                case SideEffectsEnum::DiabetesMellitus:
                    $array = [
                        'name' => 'Сахарный диабет',
                        'code' => 193,
                        'description' => ''
                    ];
                    SideEffect::create($array);
                    break;

                case SideEffectsEnum::AppetiteDecrease:
                    $array = [
                        'name' => 'Понижение аппетита',
                        'code' => 194,
                        'description' => ''
                    ];
                    SideEffect::create($array);
                    break;

                case SideEffectsEnum::ImpairedGlucoseTolerance:
                    $array = [
                        'name' => 'Нарушение толерантности к глюкозе',
                        'code' => 195,
                        'description' => ''
                    ];
                    SideEffect::create($array);
                    break;

                case SideEffectsEnum::LaryngealEdema:
                    $array = [
                        'name' => 'Отек гортани',
                        'code' => 196,
                        'description' => ''
                    ];
                    SideEffect::create($array);
                    break;

                case SideEffectsEnum::Bronchospasm:
                    $array = [
                        'name' => 'Бронхоспазм',
                        'code' => 197,
                        'description' => ''
                    ];
                    SideEffect::create($array);
                    break;

                case SideEffectsEnum::RespiratoryDepression:
                    $array = [
                        'name' => 'Угнетение дыхания',
                        'code' => 198,
                        'description' => ''
                    ];
                    SideEffect::create($array);
                    break;

                case SideEffectsEnum::Fatigue:
                    $array = [
                        'name' => 'Утомляемость',
                        'code' => 199,
                        'description' => ''
                    ];
                    SideEffect::create($array);
                    break;

                case SideEffectsEnum::Dyskinesia:
                    $array = [
                        'name' => 'Дискинезия',
                        'code' => 200,
                        'description' => ''
                    ];
                    SideEffect::create($array);
                    break;

                case SideEffectsEnum::QTProlongation:
                    $array = [
                        'name' => 'Удлинение интервала QT',
                        'code' => 201,
                        'description' => ''
                    ];
                    SideEffect::create($array);
                    break;

                case SideEffectsEnum::Hypotension:
                    $array = [
                        'name' => 'Гипотензия',
                        'code' => 202,
                        'description' => ''
                    ];
                    SideEffect::create($array);
                    break;

                case SideEffectsEnum::MuscleWeakness:
                    $array = [
                        'name' => 'Мышечная слабость',
                        'code' => 203,
                        'description' => ''
                    ];
                    SideEffect::create($array);
                    break;

                case SideEffectsEnum::Hypoesthesia:
                    $array = [
                        'name' => 'Гипоэстезия',
                        'code' => 204,
                        'description' => ''
                    ];
                    SideEffect::create($array);
                    break;

                case SideEffectsEnum::Depression:
                    $array = [
                        'name' => 'Депрессия',
                        'code' => 205,
                        'description' => ''
                    ];
                    SideEffect::create($array);
                    break;

                case SideEffectsEnum::Dysphagia:
                    $array = [
                        'name' => 'Дисфагия',
                        'code' => 206,
                        'description' => ''
                    ];
                    SideEffect::create($array);
                    break;

                case SideEffectsEnum::Palpitations:
                    $array = [
                        'name' => 'Учащённое сердцебиение',
                        'code' => 207,
                        'description' => ''
                    ];
                    SideEffect::create($array);
                    break;

                case SideEffectsEnum::Hyperthermia:
                    $array = [
                        'name' => 'Гипертермия',
                        'code' => 208,
                        'description' => ''
                    ];
                    SideEffect::create($array);
                    break;

                case SideEffectsEnum::Myocarditis:
                    $array = [
                        'name' => 'Миокардит',
                        'code' => 209,
                        'description' => ''
                    ];
                    SideEffect::create($array);
                    break;

                case SideEffectsEnum::Pericarditis:
                    $array = [
                        'name' => 'Перикардит',
                        'code' => 210,
                        'description' => ''
                    ];
                    SideEffect::create($array);
                    break;

                case SideEffectsEnum::Fever:
                    $array = [
                        'name' => 'Лихорадка',
                        'code' => 211,
                        'description' => ''
                    ];
                    SideEffect::create($array);
                    break;

                case SideEffectsEnum::ExtrapyramidalSymptoms:
                    $array = [
                        'name' => 'Экстрапирамидные симптомы',
                        'code' => 212,
                        'description' => ''
                    ];
                    SideEffect::create($array);
                    break;

                case SideEffectsEnum::Hypercholesterolemia:
                    $array = [
                        'name' => 'Гиперхолестеринемия',
                        'code' => 213,
                        'description' => ''
                    ];
                    SideEffect::create($array);
                    break;

                case SideEffectsEnum::Hypertriglyceridemia:
                    $array = [
                        'name' => 'Гипертриглицеридемия',
                        'code' => 214,
                        'description' => ''
                    ];
                    SideEffect::create($array);
                    break;

                case SideEffectsEnum::Hypothermia:
                    $array = [
                        'name' => 'Гипотермия',
                        'code' => 215,
                        'description' => ''
                    ];
                    SideEffect::create($array);
                    break;

                case SideEffectsEnum::Dyspnea:
                    $array = [
                        'name' => 'Одышка',
                        'code' => 216,
                        'description' => ''
                    ];
                    SideEffect::create($array);
                    break;

                case SideEffectsEnum::CardiacArrest:
                    $array = [
                        'name' => 'Остановка сердца',
                        'code' => 217,
                        'description' => ''
                    ];
                    SideEffect::create($array);
                    break;

                case SideEffectsEnum::PulmonaryEmbolism:
                    $array = [
                        'name' => 'Тромбоэмболия легочной артерии',
                        'code' => 218,
                        'description' => ''
                    ];
                    SideEffect::create($array);
                    break;

                case SideEffectsEnum::MuscleRigidity:
                    $array = [
                        'name' => 'Мышечная ригидность',
                        'code' => 219,
                        'description' => ''
                    ];
                    SideEffect::create($array);
                    break;

                case SideEffectsEnum::SexualDysfunction:
                    $array = [
                        'name' => 'Сексуальная дисфункция',
                        'code' => 220,
                        'description' => ''
                    ];
                    SideEffect::create($array);
                    break;
                case SideEffectsEnum::HighBloodPressure:
                    $array = [
                        'name' => 'Повышение артериального давления',
                        'code' => 221,
                        'description' => ''
                    ];
                    SideEffect::create($array);
                    break;
                case SideEffectsEnum::Weakness:
                    $array = [
                        'name' => 'Слабость',
                        'code' => 222,
                        'description' => ''
                    ];
                    SideEffect::create($array);
                    break;

                case SideEffectsEnum::Irritability:
                    $array = [
                        'name' => 'Раздражительность',
                        'code' => 223,
                        'description' => ''
                    ];
                    SideEffect::create($array);
                    break;

                case SideEffectsEnum::Hepatotoxicity:
                    $array = [
                        'name' => 'Гепатотоксичность',
                        'code' => 224,
                        'description' => ''
                    ];
                    SideEffect::create($array);
                    break;

                case SideEffectsEnum::Pancreatitis:
                    $array = [
                        'name' => 'Панкреатит',
                        'code' => 225,
                        'description' => ''
                    ];
                    SideEffect::create($array);
                    break;

                case SideEffectsEnum::ElevatedLiverEnzymes:
                    $array = [
                        'name' => 'Повышение ферментов печени',
                        'code' => 226,
                        'description' => ''
                    ];
                    SideEffect::create($array);
                    break;

                case SideEffectsEnum::BoneMarrowSuppression:
                    $array = [
                        'name' => 'Подавление костного мозга',
                        'code' => 227,
                        'description' => ''
                    ];
                    SideEffect::create($array);
                    break;

                case SideEffectsEnum::DoubleVision:
                    $array = [
                        'name' => 'Двоение в глазах',
                        'code' => 228,
                        'description' => ''
                    ];
                    SideEffect::create($array);
                    break;

                case SideEffectsEnum::Hypothyroidism:
                    $array = [
                        'name' => 'Гипотиреоз',
                        'code' => 229,
                        'description' => ''
                    ];
                    SideEffect::create($array);
                    break;

                case SideEffectsEnum::Polyuria:
                    $array = [
                        'name' => 'Полиурия',
                        'code' => 230,
                        'description' => ''
                    ];
                    SideEffect::create($array);
                    break;

                case SideEffectsEnum::KidneyFunctionImpairment:
                    $array = [
                        'name' => 'Нарушение функции почек',
                        'code' => 231,
                        'description' => ''
                    ];
                    SideEffect::create($array);
                    break;

                case SideEffectsEnum::Bradycardia:
                    $array = [
                        'name' => 'Брадикардия',
                        'code' => 232,
                        'description' => ''
                    ];
                    SideEffect::create($array);
                    break;

                case SideEffectsEnum::ColdExtremities:
                    $array = [
                        'name' => 'Холодные конечности',
                        'code' => 233,
                        'description' => ''
                    ];
                    SideEffect::create($array);
                    break;

                case SideEffectsEnum::Sleepwalking:
                    $array = [
                        'name' => 'Лунатизм',
                        'code' => 234,
                        'description' => ''
                    ];
                    SideEffect::create($array);
                    break;

                case SideEffectsEnum::Amnesia:
                    $array = [
                        'name' => 'Амнезия',
                        'code' => 235,
                        'description' => ''
                    ];
                    SideEffect::create($array);
                    break;

                case SideEffectsEnum::BitterTaste:
                    $array = [
                        'name' => 'Горький привкус во рту',
                        'code' => 236,
                        'description' => ''
                    ];
                    SideEffect::create($array);
                    break;

                case SideEffectsEnum::MuscleSpasms:
                    $array = [
                        'name' => 'Мышечные спазмы',
                        'code' => 237,
                        'description' => ''
                    ];
                    SideEffect::create($array);
                    break;
            }
        }
    }
}
