<?php

namespace Database\Seeders;

use App\Enums\ContraindicationsTypesEnum;
use App\Models\ContraindicationsType;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ContraindicationsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void {
        ContraindicationsType::truncate();
        $array = [];
        foreach (ContraindicationsTypesEnum::cases() as $i) {
            switch ($i) {
                case ContraindicationsTypesEnum::SevereDepression:
                    $array                         = [
                        'code'        => ContraindicationsTypesEnum::SevereDepression,
                        'name'        => 'Тяжёлая депрессия',
                        'description' => '',
                    ];
                    $contraindication              = new ContraindicationsType();
                    $contraindication->code        = $array['code'];
                    $contraindication->name        = $array['name'];
                    $contraindication->description = $array['description'];
                    $contraindication->save();
                    break;
                case ContraindicationsTypesEnum::ConsciousnessDisturbances:
                    $array                         = [
                        'code'        => 2,
                        'name'        => 'Нарушения сознания',
                        'description' => 'Синдромы выключения сознания или помрачения - кома, сопор, оглушение, делирий и прочие.',
                    ];
                    $contraindication              = new ContraindicationsType();
                    $contraindication->code        = $array['code'];
                    $contraindication->name        = $array['name'];
                    $contraindication->description = $array['description'];
                    $contraindication->save();
                    break;
                case ContraindicationsTypesEnum::NarrowAngleGlaucoma:
                    $array                         = [
                        'code'        => 3,
                        'name'        => 'Узкоугольная глаукома',
                        'description' => '',
                    ];
                    $contraindication              = new ContraindicationsType();
                    $contraindication->code        = $array['code'];
                    $contraindication->name        = $array['name'];
                    $contraindication->description = $array['description'];
                    $contraindication->save();
                    break;

                case ContraindicationsTypesEnum::Hypotension:
                    $array                         = [
                        'code'        => 4,
                        'name'        => 'Гипотензия',
                        'description' => '',
                    ];
                    $contraindication              = new ContraindicationsType();
                    $contraindication->code        = $array['code'];
                    $contraindication->name        = $array['name'];
                    $contraindication->description = $array['description'];
                    $contraindication->save();
                    break;

                case ContraindicationsTypesEnum::Pheochromocytoma:
                    $array                         = [
                        'code'        => 5,
                        'name'        => 'Феохромоцитома',
                        'description' => '',
                    ];
                    $contraindication              = new ContraindicationsType();
                    $contraindication->code        = $array['code'];
                    $contraindication->name        = $array['name'];
                    $contraindication->description = $array['description'];
                    $contraindication->save();
                    break;

                case ContraindicationsTypesEnum::LiverDamage:
                    $array                         = [
                        'code'        => 6,
                        'name'        => 'Поражения печени',
                        'description' => '',
                    ];
                    $contraindication              = new ContraindicationsType();
                    $contraindication->code        = $array['code'];
                    $contraindication->name        = $array['name'];
                    $contraindication->description = $array['description'];
                    $contraindication->save();
                    break;

                case ContraindicationsTypesEnum::KidneyDamage:
                    $array                         = [
                        'code'        => 7,
                        'name'        => 'Поражения почек',
                        'description' => '',
                    ];
                    $contraindication              = new ContraindicationsType();
                    $contraindication->code        = $array['code'];
                    $contraindication->name        = $array['name'];
                    $contraindication->description = $array['description'];
                    $contraindication->save();
                    break;

                case ContraindicationsTypesEnum::HematopoieticDisorder:
                    $array                         = [
                        'code'        => 8,
                        'name'        => 'Расстройства кроветворения',
                        'description' => '',
                    ];
                    $contraindication              = new ContraindicationsType();
                    $contraindication->code        = $array['code'];
                    $contraindication->name        = $array['name'];
                    $contraindication->description = $array['description'];
                    $contraindication->save();
                    break;

                case ContraindicationsTypesEnum::Myxedema:
                    $array                         = [
                        'code'        => 9,
                        'name'        => 'Микседема',
                        'description' => '',
                    ];
                    $contraindication              = new ContraindicationsType();
                    $contraindication->code        = $array['code'];
                    $contraindication->name        = $array['name'];
                    $contraindication->description = $array['description'];
                    $contraindication->save();
                    break;

                case ContraindicationsTypesEnum::BrainProgressiveSystemicDiseases:
                    $array                         = [
                        'code'        => 10,
                        'name'        => 'Прогрессирующие системные заболевания головного мозга',
                        'description' => '',
                    ];
                    $contraindication              = new ContraindicationsType();
                    $contraindication->code        = $array['code'];
                    $contraindication->name        = $array['name'];
                    $contraindication->description = $array['description'];
                    $contraindication->save();
                    break;

                case ContraindicationsTypesEnum::SpinalCordProgressiveSystemicDiseases:
                    $array                         = [
                        'code'        => 11,
                        'name'        => 'Прогрессирующие системные заболевания спинного мозга',
                        'description' => '',
                    ];
                    $contraindication              = new ContraindicationsType();
                    $contraindication->code        = $array['code'];
                    $contraindication->name        = $array['name'];
                    $contraindication->description = $array['description'];
                    $contraindication->save();
                    break;

                case ContraindicationsTypesEnum::DecompensatedHeartDefects:
                    $array                         = [
                        'code'        => 12,
                        'name'        => 'Болезни сердца в стадии декомпенсации',
                        'description' => '',
                    ];
                    $contraindication              = new ContraindicationsType();
                    $contraindication->code        = $array['code'];
                    $contraindication->name        = $array['name'];
                    $contraindication->description = $array['description'];
                    $contraindication->save();
                    break;

                case ContraindicationsTypesEnum::Thromboembolism:
                    $array                         = [
                        'code'        => 13,
                        'name'        => 'Тромбоэмболия',
                        'description' => '',
                    ];
                    $contraindication              = new ContraindicationsType();
                    $contraindication->code        = $array['code'];
                    $contraindication->name        = $array['name'];
                    $contraindication->description = $array['description'];
                    $contraindication->save();
                    break;

                case ContraindicationsTypesEnum::LateBronchiectasis:
                    $array                         = [
                        'code'        => 14,
                        'name'        => 'Поздний бронхоэктаз',
                        'description' => '',
                    ];
                    $contraindication              = new ContraindicationsType();
                    $contraindication->code        = $array['code'];
                    $contraindication->name        = $array['name'];
                    $contraindication->description = $array['description'];
                    $contraindication->save();
                    break;

                case ContraindicationsTypesEnum::Pregnancy:
                    $array                         = [
                        'code'        => 15,
                        'name'        => 'Беременность',
                        'description' => '',
                    ];
                    $contraindication              = new ContraindicationsType();
                    $contraindication->code        = $array['code'];
                    $contraindication->name        = $array['name'];
                    $contraindication->description = $array['description'];
                    $contraindication->save();
                    break;

                case ContraindicationsTypesEnum::BrestFeeding:
                    $array                         = [
                        'code'        => 16,
                        'name'        => 'Кормление грудью',
                        'description' => '',
                    ];
                    $contraindication              = new ContraindicationsType();
                    $contraindication->code        = $array['code'];
                    $contraindication->name        = $array['name'];
                    $contraindication->description = $array['description'];
                    $contraindication->save();
                    break;

                case ContraindicationsTypesEnum::Coma:
                    $array                         = [
                        'code'        => 17,
                        'name'        => 'Кома',
                        'description' => '',
                    ];
                    $contraindication              = new ContraindicationsType();
                    $contraindication->code        = $array['code'];
                    $contraindication->name        = $array['name'];
                    $contraindication->description = $array['description'];
                    $contraindication->save();
                    break;

                case ContraindicationsTypesEnum::HeartFailure:
                    $array                         = [
                        'code'        => 18,
                        'name'        => 'Сердечная недостаточность',
                        'description' => '',
                    ];
                    $contraindication              = new ContraindicationsType();
                    $contraindication->code        = $array['code'];
                    $contraindication->name        = $array['name'];
                    $contraindication->description = $array['description'];
                    $contraindication->save();
                    break;

                case ContraindicationsTypesEnum::LiverDiseases:
                    $array                         = [
                        'code'        => 19,
                        'name'        => 'Заболевания печени',
                        'description' => '',
                    ];
                    $contraindication              = new ContraindicationsType();
                    $contraindication->code        = $array['code'];
                    $contraindication->name        = $array['name'];
                    $contraindication->description = $array['description'];
                    $contraindication->save();
                    break;

                case ContraindicationsTypesEnum::Lactation:
                    $array                         = [
                        'code'        => 20,
                        'name'        => 'Лактация',
                        'description' => '',
                    ];
                    $contraindication              = new ContraindicationsType();
                    $contraindication->code        = $array['code'];
                    $contraindication->name        = $array['name'];
                    $contraindication->description = $array['description'];
                    $contraindication->save();
                    break;

                case ContraindicationsTypesEnum::Hypersensitivity:
                    $array                         = [
                        'code'        => 21,
                        'name'        => 'Гиперчувствительность',
                        'description' => '',
                    ];
                    $contraindication              = new ContraindicationsType();
                    $contraindication->code        = $array['code'];
                    $contraindication->name        = $array['name'];
                    $contraindication->description = $array['description'];
                    $contraindication->save();
                    break;

                case ContraindicationsTypesEnum::CNSDepression:
                    $array                         = [
                        'code'        => 22,
                        'name'        => 'Угнетение ЦНС',
                        'description' => '',
                    ];
                    $contraindication              = new ContraindicationsType();
                    $contraindication->code        = $array['code'];
                    $contraindication->name        = $array['name'];
                    $contraindication->description = $array['description'];
                    $contraindication->save();
                    break;

                case ContraindicationsTypesEnum::BrainTrauma:
                    $array                         = [
                        'code'        => 23,
                        'name'        => 'Травны головного мозга',
                        'description' => '',
                    ];
                    $contraindication              = new ContraindicationsType();
                    $contraindication->code        = $array['code'];
                    $contraindication->name        = $array['name'];
                    $contraindication->description = $array['description'];
                    $contraindication->save();
                    break;

                case ContraindicationsTypesEnum::KidneyDiseases:
                    $array                         = [
                        'code'        => 24,
                        'name'        => 'Заболевания почек',
                        'description' => '',
                    ];
                    $contraindication              = new ContraindicationsType();
                    $contraindication->code        = $array['code'];
                    $contraindication->name        = $array['name'];
                    $contraindication->description = $array['description'];
                    $contraindication->save();
                    break;

                case ContraindicationsTypesEnum::PepticUlcer:
                    $array                         = [
                        'code'        => 25,
                        'name'        => 'Язвенная болезнь',
                        'description' => '',
                    ];
                    $contraindication              = new ContraindicationsType();
                    $contraindication->code        = $array['code'];
                    $contraindication->name        = $array['name'];
                    $contraindication->description = $array['description'];
                    $contraindication->save();
                    break;

                case ContraindicationsTypesEnum::ThromboembolicRisk:
                    $array                         = [
                        'code'        => 26,
                        'name'        => 'Риск тромбоэмболии',
                        'description' => '',
                    ];
                    $contraindication              = new ContraindicationsType();
                    $contraindication->code        = $array['code'];
                    $contraindication->name        = $array['name'];
                    $contraindication->description = $array['description'];
                    $contraindication->save();
                    break;

                case ContraindicationsTypesEnum::Bronchiectasis:
                    $array                         = [
                        'code'        => 27,
                        'name'        => 'Бронхоэктаз',
                        'description' => '',
                    ];
                    $contraindication              = new ContraindicationsType();
                    $contraindication->code        = $array['code'];
                    $contraindication->name        = $array['name'];
                    $contraindication->description = $array['description'];
                    $contraindication->save();
                    break;

                case ContraindicationsTypesEnum::ClosureAngleGlaucoma:
                    $array                         = [
                        'code'        => 28,
                        'name'        => 'Закрытоугольная глаукома',
                        'description' => '',
                    ];
                    $contraindication              = new ContraindicationsType();
                    $contraindication->code        = $array['code'];
                    $contraindication->name        = $array['name'];
                    $contraindication->description = $array['description'];
                    $contraindication->save();
                    break;

                case ContraindicationsTypesEnum::ProstaticHyperplasia :
                    $array                         = [
                        'code'        => 29,
                        'name'        => 'Гиперплазия предстательной железы',
                        'description' => '',
                    ];
                    $contraindication              = new ContraindicationsType();
                    $contraindication->code        = $array['code'];
                    $contraindication->name        = $array['name'];
                    $contraindication->description = $array['description'];
                    $contraindication->save();
                    break;

                case ContraindicationsTypesEnum::BeforeTwelveAge:
                    $array                         = [
                        'code'        => 30,
                        'name'        => 'Возраст до 12-ти лет',
                        'description' => '',
                    ];
                    $contraindication              = new ContraindicationsType();
                    $contraindication->code        = $array['code'];
                    $contraindication->name        = $array['name'];
                    $contraindication->description = $array['description'];
                    $contraindication->save();
                    break;

                case ContraindicationsTypesEnum::ParkinsonDisease:
                    $array                         = [
                        'code'        => 31,
                        'name'        => 'Болезнь Паркинсона',
                        'description' => '',
                    ];
                    $contraindication              = new ContraindicationsType();
                    $contraindication->code        = $array['code'];
                    $contraindication->name        = $array['name'];
                    $contraindication->description = $array['description'];
                    $contraindication->save();
                    break;

                case ContraindicationsTypesEnum::ChronicAlcoholism:
                    $array                         = [
                        'code'        => 32,
                        'name'        => 'Хронический алкоголизм',
                        'description' => '',
                    ];
                    $contraindication              = new ContraindicationsType();
                    $contraindication->code        = $array['code'];
                    $contraindication->name        = $array['name'];
                    $contraindication->description = $array['description'];
                    $contraindication->save();
                    break;

                case ContraindicationsTypesEnum::MammaryCancer:
                    $array                         = [
                        'code'        => 33,
                        'name'        => 'Рак молочной железы',
                        'description' => '',
                    ];
                    $contraindication              = new ContraindicationsType();
                    $contraindication->code        = $array['code'];
                    $contraindication->name        = $array['name'];
                    $contraindication->description = $array['description'];
                    $contraindication->save();
                    break;

                case ContraindicationsTypesEnum::Epilepsy:
                    $array                         = [
                        'code'        => 34,
                        'name'        => 'Эпилепсия',
                        'description' => '',
                    ];
                    $contraindication              = new ContraindicationsType();
                    $contraindication->code        = $array['code'];
                    $contraindication->name        = $array['name'];
                    $contraindication->description = $array['description'];
                    $contraindication->save();
                    break;

                case ContraindicationsTypesEnum::ChronicRespiratoryDisease:
                    $array                         = [
                        'code'        => 35,
                        'name'        => 'Хронические заболевания дыхательной системы',
                        'description' => '',
                    ];
                    $contraindication              = new ContraindicationsType();
                    $contraindication->code        = $array['code'];
                    $contraindication->name        = $array['name'];
                    $contraindication->description = $array['description'];
                    $contraindication->save();
                    break;

                case ContraindicationsTypesEnum::ReyeSyndrome:
                    $array                         = [
                        'code'        => 36,
                        'name'        => 'Синдром Рейе',
                        'description' => '',
                    ];
                    $contraindication              = new ContraindicationsType();
                    $contraindication->code        = $array['code'];
                    $contraindication->name        = $array['name'];
                    $contraindication->description = $array['description'];
                    $contraindication->save();
                    break;

                case ContraindicationsTypesEnum::Cachexia:
                    $array                         = [
                        'code'        => 37,
                        'name'        => 'Кахексия',
                        'description' => '',
                    ];
                    $contraindication              = new ContraindicationsType();
                    $contraindication->code        = $array['code'];
                    $contraindication->name        = $array['name'];
                    $contraindication->description = $array['description'];
                    $contraindication->save();
                    break;

                case ContraindicationsTypesEnum::ElderlyAge:
                    $array                         = [
                        'code'        => 38,
                        'name'        => 'Пожилой возраст',
                        'description' => '',
                    ];
                    $contraindication              = new ContraindicationsType();
                    $contraindication->code        = $array['code'];
                    $contraindication->name        = $array['name'];
                    $contraindication->description = $array['description'];
                    $contraindication->save();
                    break;

                case ContraindicationsTypesEnum::YoungerTwoAges:
                    $array                         = [
                        'code'        => 39,
                        'name'        => 'Возраст меньше 2-х лет',
                        'description' => '',
                    ];
                    $contraindication              = new ContraindicationsType();
                    $contraindication->code        = $array['code'];
                    $contraindication->name        = $array['name'];
                    $contraindication->description = $array['description'];
                    $contraindication->save();
                    break;

                case ContraindicationsTypesEnum::SevereCardioVascularSystemDisease:
                    $array                         = [
                        'code'        => 40,
                        'name'        => 'Тяжёлые заболевания сердечно-сосудистой системы',
                        'description' => '',
                    ];
                    $contraindication              = new ContraindicationsType();
                    $contraindication->code        = $array['code'];
                    $contraindication->name        = $array['name'];
                    $contraindication->description = $array['description'];
                    $contraindication->save();
                    break;

                case ContraindicationsTypesEnum::LiverFailure:
                    $array                         = [
                        'code'        => 41,
                        'name'        => 'Отказ печени',
                        'description' => '',
                    ];
                    $contraindication              = new ContraindicationsType();
                    $contraindication->code        = $array['code'];
                    $contraindication->name        = $array['name'];
                    $contraindication->description = $array['description'];
                    $contraindication->save();
                    break;

                case ContraindicationsTypesEnum::QTProlongation:
                    $array                         = [
                        'code'        => 42,
                        'name'        => 'Удлинение интервала QT',
                        'description' => '',
                    ];
                    $contraindication              = new ContraindicationsType();
                    $contraindication->code        = $array['code'];
                    $contraindication->name        = $array['name'];
                    $contraindication->description = $array['description'];
                    $contraindication->save();
                    break;

                case ContraindicationsTypesEnum::Arrhythmia:
                    $array                         = [
                        'code'        => 43,
                        'name'        => 'Аритмия',
                        'description' => '',
                    ];
                    $contraindication              = new ContraindicationsType();
                    $contraindication->code        = $array['code'];
                    $contraindication->name        = $array['name'];
                    $contraindication->description = $array['description'];
                    $contraindication->save();
                    break;

                case ContraindicationsTypesEnum::GlucoseGalactoseMalabsorption:
                    $array                         = [
                        'code'        => 44,
                        'name'        => 'Глюкозо-галактозная мальабсорбция',
                        'description' => '',
                    ];
                    $contraindication              = new ContraindicationsType();
                    $contraindication->code        = $array['code'];
                    $contraindication->name        = $array['name'];
                    $contraindication->description = $array['description'];
                    $contraindication->save();
                    break;

                case ContraindicationsTypesEnum::LactaseDeficiency:
                    $array                         = [
                        'code'        => 45,
                        'name'        => 'Дефицит лактазы',
                        'description' => '',
                    ];
                    $contraindication              = new ContraindicationsType();
                    $contraindication->code        = $array['code'];
                    $contraindication->name        = $array['name'];
                    $contraindication->description = $array['description'];
                    $contraindication->save();
                    break;

                case ContraindicationsTypesEnum::LactoseIntolerance:
                    $array                         = [
                        'code'        => 46,
                        'name'        => 'Непереносимость лактозы',
                        'description' => '',
                    ];
                    $contraindication              = new ContraindicationsType();
                    $contraindication->code        = $array['code'];
                    $contraindication->name        = $array['name'];
                    $contraindication->description = $array['description'];
                    $contraindication->save();
                    break;

                case ContraindicationsTypesEnum::AlcoholPoisoning:
                    $array                         = [
                        'code'        => 47,
                        'name'        => 'Алкогольное отравление',
                        'description' => '',
                    ];
                    $contraindication              = new ContraindicationsType();
                    $contraindication->code        = $array['code'];
                    $contraindication->name        = $array['name'];
                    $contraindication->description = $array['description'];
                    $contraindication->save();
                    break;

                case ContraindicationsTypesEnum::BarbituratesPoisoning:
                    $array                         = [
                        'code'        => 48,
                        'name'        => 'Отправление барбитуратами',
                        'description' => '',
                    ];
                    $contraindication              = new ContraindicationsType();
                    $contraindication->code        = $array['code'];
                    $contraindication->name        = $array['name'];
                    $contraindication->description = $array['description'];
                    $contraindication->save();
                    break;

                case ContraindicationsTypesEnum::TendencyToCollapse:
                    $array                         = [
                        'code'        => 49,
                        'name'        => 'Склонность к коллапсам',
                        'description' => '',
                    ];
                    $contraindication              = new ContraindicationsType();
                    $contraindication->code        = $array['code'];
                    $contraindication->name        = $array['name'];
                    $contraindication->description = $array['description'];
                    $contraindication->save();
                    break;

                case ContraindicationsTypesEnum::HeartDefects:
                    $array                         = [
                        'code'        => 50,
                        'name'        => 'Нарушения работы сердца',
                        'description' => '',
                    ];
                    $contraindication              = new ContraindicationsType();
                    $contraindication->code        = $array['code'];
                    $contraindication->name        = $array['name'];
                    $contraindication->description = $array['description'];
                    $contraindication->save();
                    break;

                case ContraindicationsTypesEnum::Tachycardia:
                    $array                         = [
                        'code'        => 51,
                        'name'        => 'Тахикардия',
                        'description' => '',
                    ];
                    $contraindication              = new ContraindicationsType();
                    $contraindication->code        = $array['code'];
                    $contraindication->name        = $array['name'];
                    $contraindication->description = $array['description'];
                    $contraindication->save();
                    break;

                case ContraindicationsTypesEnum::SevereSomaticExhaustion:
                    $array                         = [
                        'code'        => 52,
                        'name'        => 'Тяжёлое физическое истощение',
                        'description' => '',
                    ];
                    $contraindication              = new ContraindicationsType();
                    $contraindication->code        = $array['code'];
                    $contraindication->name        = $array['name'];
                    $contraindication->description = $array['description'];
                    $contraindication->save();
                    break;

                case ContraindicationsTypesEnum::SevereCerebralSclerosis:
                    $array                         = [
                        'code'        => 53,
                        'name'        => 'Тяжёлый церебральный склероз',
                        'description' => '',
                    ];
                    $contraindication              = new ContraindicationsType();
                    $contraindication->code        = $array['code'];
                    $contraindication->name        = $array['name'];
                    $contraindication->description = $array['description'];
                    $contraindication->save();
                    break;

                case ContraindicationsTypesEnum::YoungAge:
                    $array                         = [
                        'code'        => 54,
                        'name'        => 'Возраст до 18-ти лет',
                        'description' => '',
                    ];
                    $contraindication              = new ContraindicationsType();
                    $contraindication->code        = $array['code'];
                    $contraindication->name        = $array['name'];
                    $contraindication->description = $array['description'];
                    $contraindication->save();
                    break;

                case ContraindicationsTypesEnum::SevereCNSToxicDepression:
                    $array                         = [
                        'code'        => 55,
                        'name'        => 'Тяжёлое токсическое угнетение ЦНС',
                        'description' => '',
                    ];
                    $contraindication              = new ContraindicationsType();
                    $contraindication->code        = $array['code'];
                    $contraindication->name        = $array['name'];
                    $contraindication->description = $array['description'];
                    $contraindication->save();
                    break;

                case ContraindicationsTypesEnum::SesameOilHypersensitivity:
                    $array                         = [
                        'code'        => 56,
                        'name'        => 'Гиперчувствительность к кунжутному маслу',
                        'description' => '',
                    ];
                    $contraindication              = new ContraindicationsType();
                    $contraindication->code        = $array['code'];
                    $contraindication->name        = $array['name'];
                    $contraindication->description = $array['description'];
                    $contraindication->save();
                    break;

                case ContraindicationsTypesEnum::YoungerThreeAge:
                    $array                         = [
                        'code'        => 57,
                        'name'        => 'Возраст до 3-х лет',
                        'description' => '',
                    ];
                    $contraindication              = new ContraindicationsType();
                    $contraindication->code        = $array['code'];
                    $contraindication->name        = $array['name'];
                    $contraindication->description = $array['description'];
                    $contraindication->save();
                    break;

                case ContraindicationsTypesEnum::Hysteria:
                    $array                         = [
                        'code'        => 58,
                        'name'        => 'Истерия',
                        'description' => '',
                    ];
                    $contraindication              = new ContraindicationsType();
                    $contraindication->code        = $array['code'];
                    $contraindication->name        = $array['name'];
                    $contraindication->description = $array['description'];
                    $contraindication->save();
                    break;

                case ContraindicationsTypesEnum::Thyrotoxicosis:
                    $array                         = [
                        'code'        => 59,
                        'name'        => 'Тиреотоксикоз',
                        'description' => '',
                    ];
                    $contraindication              = new ContraindicationsType();
                    $contraindication->code        = $array['code'];
                    $contraindication->name        = $array['name'];
                    $contraindication->description = $array['description'];
                    $contraindication->save();
                    break;

                case ContraindicationsTypesEnum::TakingAnticoagulants:
                    $array                         = [
                        'code'        => 60,
                        'name'        => 'Приём антикоагулянтов',
                        'description' => '',
                    ];
                    $contraindication              = new ContraindicationsType();
                    $contraindication->code        = $array['code'];
                    $contraindication->name        = $array['name'];
                    $contraindication->description = $array['description'];
                    $contraindication->save();
                    break;

                case ContraindicationsTypesEnum::Poisoning:
                    $array                         = [
                        'code'        => 61,
                        'name'        => 'Отравление',
                        'description' => '',
                    ];
                    $contraindication              = new ContraindicationsType();
                    $contraindication->code        = $array['code'];
                    $contraindication->name        = $array['name'];
                    $contraindication->description = $array['description'];
                    $contraindication->save();
                    break;

                case ContraindicationsTypesEnum::OpioidPoisoning:
                    $array                         = [
                        'code'        => 62,
                        'name'        => 'Отравление опиоидами',
                        'description' => '',
                    ];
                    $contraindication              = new ContraindicationsType();
                    $contraindication->code        = $array['code'];
                    $contraindication->name        = $array['name'];
                    $contraindication->description = $array['description'];
                    $contraindication->save();
                    break;

                case ContraindicationsTypesEnum::RenalFailure:
                    $array                         = [
                        'code'        => 63,
                        'name'        => 'Отказ почек',
                        'description' => '',
                    ];
                    $contraindication              = new ContraindicationsType();
                    $contraindication->code        = $array['code'];
                    $contraindication->name        = $array['name'];
                    $contraindication->description = $array['description'];
                    $contraindication->save();
                    break;

                case ContraindicationsTypesEnum::PathologicalBloodChanges:
                    $array                         = [
                        'code'        => 64,
                        'name'        => 'Патологические изменения картины крови',
                        'description' => '',
                    ];
                    $contraindication              = new ContraindicationsType();
                    $contraindication->code        = $array['code'];
                    $contraindication->name        = $array['name'];
                    $contraindication->description = $array['description'];
                    $contraindication->save();
                    break;

                case ContraindicationsTypesEnum::BoneMarrowSuppression:
                    $array                         = [
                        'code'        => 65,
                        'name'        => 'Угнетение костного мозга',
                        'description' => '',
                    ];
                    $contraindication              = new ContraindicationsType();
                    $contraindication->code        = $array['code'];
                    $contraindication->name        = $array['name'];
                    $contraindication->description = $array['description'];
                    $contraindication->save();
                    break;

                case ContraindicationsTypesEnum::Collapse:
                    $array                         = [
                        'code'        => 66,
                        'name'        => 'Коллапс',
                        'description' => '',
                    ];
                    $contraindication              = new ContraindicationsType();
                    $contraindication->code        = $array['code'];
                    $contraindication->name        = $array['name'];
                    $contraindication->description = $array['description'];
                    $contraindication->save();
                    break;

                case ContraindicationsTypesEnum::Childhood:
                    $array                         = [
                        'code'        => 67,
                        'name'        => 'Детский возраст до 18-ти лет',
                        'description' => '',
                    ];
                    $contraindication              = new ContraindicationsType();
                    $contraindication->code        = $array['code'];
                    $contraindication->name        = $array['name'];
                    $contraindication->description = $array['description'];
                    $contraindication->save();
                    break;

                case ContraindicationsTypesEnum::NeurolepticMalignantSyndrome:
                    $array                         = [
                        'code'        => 68,
                        'name'        => 'Злокачественный нейролептический синдром',
                        'description' => '',
                    ];
                    $contraindication              = new ContraindicationsType();
                    $contraindication->code        = $array['code'];
                    $contraindication->name        = $array['name'];
                    $contraindication->description = $array['description'];
                    $contraindication->save();
                    break;

                case ContraindicationsTypesEnum::CentralHyperthermia:
                    $array                         = [
                        'code'        => 69,
                        'name'        => 'Центральная гипертермия',
                        'description' => '',
                    ];
                    $contraindication              = new ContraindicationsType();
                    $contraindication->code        = $array['code'];
                    $contraindication->name        = $array['name'];
                    $contraindication->description = $array['description'];
                    $contraindication->save();
                    break;

                case ContraindicationsTypesEnum::AgitationAndHyperactivityState:
                    $array                         = [
                        'code'        => 70,
                        'name'        => 'Состояние ажитации и гиперактивности',
                        'description' => '',
                    ];
                    $contraindication              = new ContraindicationsType();
                    $contraindication->code        = $array['code'];
                    $contraindication->name        = $array['name'];
                    $contraindication->description = $array['description'];
                    $contraindication->save();
                    break;

                case ContraindicationsTypesEnum::OrthostaticHypotension:
                    $array                         = [
                        'code'        => 71,
                        'name'        => 'Ортостатическая гипотензия',
                        'description' => 'Понижение давления при вставании.',
                    ];
                    $contraindication              = new ContraindicationsType();
                    $contraindication->code        = $array['code'];
                    $contraindication->name        = $array['name'];
                    $contraindication->description = $array['description'];
                    $contraindication->save();
                    break;

                case ContraindicationsTypesEnum::BreathingDisease:
                    $array                         = [
                        'code'        => 72,
                        'name'        => 'Заболевания органов дыхания',
                        'description' => '',
                    ];
                    $contraindication              = new ContraindicationsType();
                    $contraindication->code        = $array['code'];
                    $contraindication->name        = $array['name'];
                    $contraindication->description = $array['description'];
                    $contraindication->save();
                    break;

                case ContraindicationsTypesEnum::Asthma:
                    $array                         = [
                        'code'        => 73,
                        'name'        => 'Астма',
                        'description' => '',
                    ];
                    $contraindication              = new ContraindicationsType();
                    $contraindication->code        = $array['code'];
                    $contraindication->name        = $array['name'];
                    $contraindication->description = $array['description'];
                    $contraindication->save();
                    break;

                case ContraindicationsTypesEnum::Emphysema:
                    $array                         = [
                        'code'        => 74,
                        'name'        => 'Эмфизема',
                        'description' => '',
                    ];
                    $contraindication              = new ContraindicationsType();
                    $contraindication->code        = $array['code'];
                    $contraindication->name        = $array['name'];
                    $contraindication->description = $array['description'];
                    $contraindication->save();
                    break;

                case ContraindicationsTypesEnum::ProstaticHypertrophy:
                    $array                         = [
                        'code'        => 75,
                        'name'        => 'Гипертрофия предстательной железы',
                        'description' => '',
                    ];
                    $contraindication              = new ContraindicationsType();
                    $contraindication->code        = $array['code'];
                    $contraindication->name        = $array['name'];
                    $contraindication->description = $array['description'];
                    $contraindication->save();
                    break;

                case ContraindicationsTypesEnum::EmptyingBladderDifficulty:
                    $array                         = [
                        'code'        => 76,
                        'name'        => 'Трудности мочеиспускания',
                        'description' => '',
                    ];
                    $contraindication              = new ContraindicationsType();
                    $contraindication->code        = $array['code'];
                    $contraindication->name        = $array['name'];
                    $contraindication->description = $array['description'];
                    $contraindication->save();
                    break;

                case ContraindicationsTypesEnum::Alcoholism:
                    $array                         = [
                        'code'        => 77,
                        'name'        => 'Алкоголизм',
                        'description' => '',
                    ];
                    $contraindication              = new ContraindicationsType();
                    $contraindication->code        = $array['code'];
                    $contraindication->name        = $array['name'];
                    $contraindication->description = $array['description'];
                    $contraindication->save();
                    break;

                case ContraindicationsTypesEnum::ProlactinDependentTumors:
                    $array                         = [
                        'code'        => 78,
                        'name'        => 'Пролактин-чувствительные опухоли',
                        'description' => '',
                    ];
                    $contraindication              = new ContraindicationsType();
                    $contraindication->code        = $array['code'];
                    $contraindication->name        = $array['name'];
                    $contraindication->description = $array['description'];
                    $contraindication->save();
                    break;

                case ContraindicationsTypesEnum::SevereCranialVesselsAtherosclerosis:
                    $array                         = [
                        'code'        => 79,
                        'name'        => 'Тяжёлый атеросклероз черепных сосудов',
                        'description' => '',
                    ];
                    $contraindication              = new ContraindicationsType();
                    $contraindication->code        = $array['code'];
                    $contraindication->name        = $array['name'];
                    $contraindication->description = $array['description'];
                    $contraindication->save();
                    break;

                case ContraindicationsTypesEnum::OrganicCMSDisease:
                    $array                         = [
                        'code'        => 80,
                        'name'        => 'Органические заболевания головного мозга',
                        'description' => '',
                    ];
                    $contraindication              = new ContraindicationsType();
                    $contraindication->code        = $array['code'];
                    $contraindication->name        = $array['name'];
                    $contraindication->description = $array['description'];
                    $contraindication->save();
                    break;

                case ContraindicationsTypesEnum::BloodDisease:
                    $array                         = [
                        'code'        => 81,
                        'name'        => 'Заболевания крови',
                        'description' => '',
                    ];
                    $contraindication              = new ContraindicationsType();
                    $contraindication->code        = $array['code'];
                    $contraindication->name        = $array['name'];
                    $contraindication->description = $array['description'];
                    $contraindication->save();
                    break;

                case ContraindicationsTypesEnum::MyastheniaGravis:
                    $array                         = [
                        'code'        => 82,
                        'name'        => 'Миастения',
                        'description' => '',
                    ];
                    $contraindication              = new ContraindicationsType();
                    $contraindication->code        = $array['code'];
                    $contraindication->name        = $array['name'];
                    $contraindication->description = $array['description'];
                    $contraindication->save();
                    break;

                case ContraindicationsTypesEnum::Hepatitis:
                    $array                         = [
                        'code'        => 83,
                        'name'        => 'Гепатит',
                        'description' => '',
                    ];
                    $contraindication              = new ContraindicationsType();
                    $contraindication->code        = $array['code'];
                    $contraindication->name        = $array['name'];
                    $contraindication->description = $array['description'];
                    $contraindication->save();
                    break;

                case ContraindicationsTypesEnum::Hyperhidrosis:
                    $array                         = [
                        'code'        => 84,
                        'name'        => 'Гипергидроз',
                        'description' => '',
                    ];
                    $contraindication              = new ContraindicationsType();
                    $contraindication->code        = $array['code'];
                    $contraindication->name        = $array['name'];
                    $contraindication->description = $array['description'];
                    $contraindication->save();
                    break;

                case ContraindicationsTypesEnum::SevereHypertension:
                    $array                         = [
                        'code'        => 85,
                        'name'        => 'Тяжёлая гипертензия',
                        'description' => '',
                    ];
                    $contraindication              = new ContraindicationsType();
                    $contraindication->code        = $array['code'];
                    $contraindication->name        = $array['name'];
                    $contraindication->description = $array['description'];
                    $contraindication->save();
                    break;

                case ContraindicationsTypesEnum::Porphyria:
                    $array                         = [
                        'code'        => 86,
                        'name'        => 'Порфирия',
                        'description' => '',
                    ];
                    $contraindication              = new ContraindicationsType();
                    $contraindication->code        = $array['code'];
                    $contraindication->name        = $array['name'];
                    $contraindication->description = $array['description'];
                    $contraindication->save();
                    break;

                case ContraindicationsTypesEnum::Anxiety:
                    $array                         = [
                        'code'        => 87,
                        'name'        => 'Тревога',
                        'description' => '',
                    ];
                    $contraindication              = new ContraindicationsType();
                    $contraindication->code        = $array['code'];
                    $contraindication->name        = $array['name'];
                    $contraindication->description = $array['description'];
                    $contraindication->save();
                    break;

                case ContraindicationsTypesEnum::PsychomotorAgitation:
                    $array                         = [
                        'code'        => 88,
                        'name'        => 'Психомоторное возбуждение',
                        'description' => '',
                    ];
                    $contraindication              = new ContraindicationsType();
                    $contraindication->code        = $array['code'];
                    $contraindication->name        = $array['name'];
                    $contraindication->description = $array['description'];
                    $contraindication->save();
                    break;

                case ContraindicationsTypesEnum::HypnoticsPoisoning:
                    $array                         = [
                        'code'        => 89,
                        'name'        => 'Отравление гипнотиками',
                        'description' => 'Отравление снотворных средств',
                    ];
                    $contraindication              = new ContraindicationsType();
                    $contraindication->code        = $array['code'];
                    $contraindication->name        = $array['name'];
                    $contraindication->description = $array['description'];
                    $contraindication->save();
                    break;

                case ContraindicationsTypesEnum::AnalgesicsPoisoning:
                    $array                         = [
                        'code'        => 90,
                        'name'        => 'Отравление анальгетиками',
                        'description' => 'Отравление болеутоляющими препаратами',
                    ];
                    $contraindication              = new ContraindicationsType();
                    $contraindication->code        = $array['code'];
                    $contraindication->name        = $array['name'];
                    $contraindication->description = $array['description'];
                    $contraindication->save();
                    break;

                case ContraindicationsTypesEnum::AggressiveBehavior:
                    $array                         = [
                        'code'        => 91,
                        'name'        => 'Агрессия',
                        'description' => '',
                    ];
                    $contraindication              = new ContraindicationsType();
                    $contraindication->code        = $array['code'];
                    $contraindication->name        = $array['name'];
                    $contraindication->description = $array['description'];
                    $contraindication->save();
                    break;

                case ContraindicationsTypesEnum::ManicBehavior:
                    $array                         = [
                        'code'        => 92,
                        'name'        => 'Маниакальное состояние',
                        'description' => '',
                    ];
                    $contraindication              = new ContraindicationsType();
                    $contraindication->code        = $array['code'];
                    $contraindication->name        = $array['name'];
                    $contraindication->description = $array['description'];
                    $contraindication->save();
                    break;

                case ContraindicationsTypesEnum::Hyperprolactinemia:
                    $array                         = [
                        'code'        => 93,
                        'name'        => 'Гиперпролактинемия',
                        'description' => '',
                    ];
                    $contraindication              = new ContraindicationsType();
                    $contraindication->code        = $array['code'];
                    $contraindication->name        = $array['name'];
                    $contraindication->description = $array['description'];
                    $contraindication->save();
                    break;

                case ContraindicationsTypesEnum::YoungFourteenAge:
                    $array                         = [
                        'code'        => 94,
                        'name'        => 'Возраст моложе 14-ти лет',
                        'description' => '',
                    ];
                    $contraindication              = new ContraindicationsType();
                    $contraindication->code        = $array['code'];
                    $contraindication->name        = $array['name'];
                    $contraindication->description = $array['description'];
                    $contraindication->save();
                    break;

                case ContraindicationsTypesEnum::TakingSultopride:
                    $array                         = [
                        'code'        => 95,
                        'name'        => 'Приём сультоприда',
                        'description' => '',
                    ];
                    $contraindication              = new ContraindicationsType();
                    $contraindication->code        = $array['code'];
                    $contraindication->name        = $array['name'];
                    $contraindication->description = $array['description'];
                    $contraindication->save();
                    break;

                case ContraindicationsTypesEnum::TakingDopamineAgonists:
                    $array                         = [
                        'code'        => 96,
                        'name'        => 'Приём агонистов дофамина',
                        'description' => '',
                    ];
                    $contraindication              = new ContraindicationsType();
                    $contraindication->code        = $array['code'];
                    $contraindication->name        = $array['name'];
                    $contraindication->description = $array['description'];
                    $contraindication->save();
                    break;

                case ContraindicationsTypesEnum::AnginaPectoris:
                    $array                         = [
                        'code'        => 97,
                        'name'        => 'Стенокардия',
                        'description' => '',
                    ];
                    $contraindication              = new ContraindicationsType();
                    $contraindication->code        = $array['code'];
                    $contraindication->name        = $array['name'];
                    $contraindication->description = $array['description'];
                    $contraindication->save();
                    break;

                case ContraindicationsTypesEnum::UrinaryRetention:
                    $array                         = [
                        'code'        => 98,
                        'name'        => 'Задержка мочеиспускания',
                        'description' => '',
                    ];
                    $contraindication              = new ContraindicationsType();
                    $contraindication->code        = $array['code'];
                    $contraindication->name        = $array['name'];
                    $contraindication->description = $array['description'];
                    $contraindication->save();
                    break;

                case ContraindicationsTypesEnum::IrregularMenstruation:
                    $array                         = [
                        'code'        => 99,
                        'name'        => 'Нерегулярная менструация',
                        'description' => '',
                    ];
                    $contraindication              = new ContraindicationsType();
                    $contraindication->code        = $array['code'];
                    $contraindication->name        = $array['name'];
                    $contraindication->description = $array['description'];
                    $contraindication->save();
                    break;

                case ContraindicationsTypesEnum::IntoxicationPsychoses:
                    $array                         = [
                        'code'        => 100,
                        'name'        => 'Интоксикационный психоз',
                        'description' => '',
                    ];
                    $contraindication              = new ContraindicationsType();
                    $contraindication->code        = $array['code'];
                    $contraindication->name        = $array['name'];
                    $contraindication->description = $array['description'];
                    $contraindication->save();
                    break;

                case ContraindicationsTypesEnum::YoungerFiveAge:
                    $array                         = [
                        'code'        => 101,
                        'name'        => 'Возраст моложе 5-ти лет',
                        'description' => '',
                    ];
                    $contraindication              = new ContraindicationsType();
                    $contraindication->code        = $array['code'];
                    $contraindication->name        = $array['name'];
                    $contraindication->description = $array['description'];
                    $contraindication->save();
                    break;

                case ContraindicationsTypesEnum::ProneToSeizures:
                    $array                         = [
                        'code'        => 102,
                        'name'        => 'Склонность к судорогам',
                        'description' => '',
                    ];
                    $contraindication              = new ContraindicationsType();
                    $contraindication->code        = $array['code'];
                    $contraindication->name        = $array['name'];
                    $contraindication->description = $array['description'];
                    $contraindication->save();
                    break;

                case ContraindicationsTypesEnum::IntestinalAtony:
                    $array                         = [
                        'code'        => 103,
                        'name'        => 'Атония кишечника',
                        'description' => '',
                    ];
                    $contraindication              = new ContraindicationsType();
                    $contraindication->code        = $array['code'];
                    $contraindication->name        = $array['name'];
                    $contraindication->description = $array['description'];
                    $contraindication->save();
                    break;

                case ContraindicationsTypesEnum::FebrileSyndrome:
                    $array                         = [
                        'code'        => 104,
                        'name'        => 'Фибриляция',
                        'description' => '',
                    ];
                    $contraindication              = new ContraindicationsType();
                    $contraindication->code        = $array['code'];
                    $contraindication->name        = $array['name'];
                    $contraindication->description = $array['description'];
                    $contraindication->save();
                    break;

                case ContraindicationsTypesEnum::Dehydration:
                    $array                         = [
                        'code'        => 105,
                        'name'        => 'Дегидрация',
                        'description' => '',
                    ];
                    $contraindication              = new ContraindicationsType();
                    $contraindication->code        = $array['code'];
                    $contraindication->name        = $array['name'];
                    $contraindication->description = $array['description'];
                    $contraindication->save();
                    break;

                case ContraindicationsTypesEnum::Hypovolemia:
                    $array                         = [
                        'code'        => 106,
                        'name'        => 'Гиповолемия',
                        'description' => '',
                    ];
                    $contraindication              = new ContraindicationsType();
                    $contraindication->code        = $array['code'];
                    $contraindication->name        = $array['name'];
                    $contraindication->description = $array['description'];
                    $contraindication->save();
                    break;

                case ContraindicationsTypesEnum::CerebroVascularDisorders:
                    $array                         = [
                        'code'        => 107,
                        'name'        => 'Расстройства мозгового кровеобращения',
                        'description' => '',
                    ];
                    $contraindication              = new ContraindicationsType();
                    $contraindication->code        = $array['code'];
                    $contraindication->name        = $array['name'];
                    $contraindication->description = $array['description'];
                    $contraindication->save();
                    break;

                case ContraindicationsTypesEnum::YoungerFifteenAge:
                    $array                         = [
                        'code'        => 108,
                        'name'        => 'Возраст меньше 15-ти лет',
                        'description' => '',
                    ];
                    $contraindication              = new ContraindicationsType();
                    $contraindication->code        = $array['code'];
                    $contraindication->name        = $array['name'];
                    $contraindication->description = $array['description'];
                    $contraindication->save();
                    break;

                case ContraindicationsTypesEnum::CerebroVascularAccident:
                    $array                         = [
                        'code'        => 109,
                        'name'        => 'Нарушения мозгового кровообращения',
                        'description' => '',
                    ];
                    $contraindication              = new ContraindicationsType();
                    $contraindication->code        = $array['code'];
                    $contraindication->name        = $array['name'];
                    $contraindication->description = $array['description'];
                    $contraindication->save();
                    break;

                case ContraindicationsTypesEnum::DrugAddiction:
                    $array                         = [
                        'code'        => 110,
                        'name'        => 'Склонность к злоупотреблению лекарственными средствами',
                        'description' => '',
                    ];
                    $contraindication              = new ContraindicationsType();
                    $contraindication->code        = $array['code'];
                    $contraindication->name        = $array['name'];
                    $contraindication->description = $array['description'];
                    $contraindication->save();
                    break;

                case ContraindicationsTypesEnum::TachycardiaPirouette:
                    $array                         = [
                        'code'        => 111,
                        'name'        => 'Тахикардия вида "пируэт"',
                        'description' => '',
                    ];
                    $contraindication              = new ContraindicationsType();
                    $contraindication->code        = $array['code'];
                    $contraindication->name        = $array['name'];
                    $contraindication->description = $array['description'];
                    $contraindication->save();
                    break;

                case ContraindicationsTypesEnum::BrainTumor:
                    $array                         = [
                        'code'        => 112,
                        'name'        => 'Опухоль ЦНС',
                        'description' => '',
                    ];
                    $contraindication              = new ContraindicationsType();
                    $contraindication->code        = $array['code'];
                    $contraindication->name        = $array['name'];
                    $contraindication->description = $array['description'];
                    $contraindication->save();
                    break;

                case ContraindicationsTypesEnum::IntestinalObstruction:
                    $array                         = [
                        'code'        => 113,
                        'name'        => 'Кишечная непроходимость',
                        'description' => '',
                    ];
                    $contraindication              = new ContraindicationsType();
                    $contraindication->code        = $array['code'];
                    $contraindication->name        = $array['name'];
                    $contraindication->description = $array['description'];
                    $contraindication->save();
                    break;

                case ContraindicationsTypesEnum::TakingFurosemide:
                    $array                         = [
                        'code'        => 114,
                        'name'        => 'Приём фуросемида',
                        'description' => '',
                    ];
                    $contraindication              = new ContraindicationsType();
                    $contraindication->code        = $array['code'];
                    $contraindication->name        = $array['name'];
                    $contraindication->description = $array['description'];
                    $contraindication->save();
                    break;

                case ContraindicationsTypesEnum::Thrombophlebitis:
                    $array                         = [
                        'code'        => 115,
                        'name'        => 'Тромбофлебит',
                        'description' => '',
                    ];
                    $contraindication              = new ContraindicationsType();
                    $contraindication->code        = $array['code'];
                    $contraindication->name        = $array['name'];
                    $contraindication->description = $array['description'];
                    $contraindication->save();
                    break;

                case ContraindicationsTypesEnum::Hyperglycemia:
                    $array                         = [
                        'code'        => 116,
                        'name'        => 'Гипергликемия',
                        'description' => '',
                    ];
                    $contraindication              = new ContraindicationsType();
                    $contraindication->code        = $array['code'];
                    $contraindication->name        = $array['name'];
                    $contraindication->description = $array['description'];
                    $contraindication->save();
                    break;

                case ContraindicationsTypesEnum::Dementia:
                    $array                         = [
                        'code'        => 117,
                        'name'        => 'Деменция',
                        'description' => '',
                    ];
                    $contraindication              = new ContraindicationsType();
                    $contraindication->code        = $array['code'];
                    $contraindication->name        = $array['name'];
                    $contraindication->description = $array['description'];
                    $contraindication->save();
                    break;

                case ContraindicationsTypesEnum::TardiveDyskinesia:
                    $array                         = [
                        'code'        => 118,
                        'name'        => 'Поздняя дискенезия',
                        'description' => '',
                    ];
                    $contraindication              = new ContraindicationsType();
                    $contraindication->code        = $array['code'];
                    $contraindication->name        = $array['name'];
                    $contraindication->description = $array['description'];
                    $contraindication->save();
                    break;

                case ContraindicationsTypesEnum::Diabetes:
                    $array                         = [
                        'code'        => 119,
                        'name'        => 'Диабет',
                        'description' => '',
                    ];
                    $contraindication              = new ContraindicationsType();
                    $contraindication->code        = $array['code'];
                    $contraindication->name        = $array['name'];
                    $contraindication->description = $array['description'];
                    $contraindication->save();
                    break;

                case ContraindicationsTypesEnum::YoungerEighteenAge:
                    $array                         = [
                        'code'        => 120,
                        'name'        => 'Возраст меньше 18-ти лет',
                        'description' => '',
                    ];
                    $contraindication              = new ContraindicationsType();
                    $contraindication->code        = $array['code'];
                    $contraindication->name        = $array['name'];
                    $contraindication->description = $array['description'];
                    $contraindication->save();
                    break;

                case ContraindicationsTypesEnum::Myelosuppression:
                    $array                         = [
                        'code'        => 121,
                        'name'        => 'Миелосупрессия',
                        'description' => '',
                    ];
                    $contraindication              = new ContraindicationsType();
                    $contraindication->code        = $array['code'];
                    $contraindication->name        = $array['name'];
                    $contraindication->description = $array['description'];
                    $contraindication->save();
                    break;

                case ContraindicationsTypesEnum::ParalyticIleus:
                    $array                         = [
                        'code'        => 122,
                        'name'        => 'Кишечная непроходимость',
                        'description' => '',
                    ];
                    $contraindication              = new ContraindicationsType();
                    $contraindication->code        = $array['code'];
                    $contraindication->name        = $array['name'];
                    $contraindication->description = $array['description'];
                    $contraindication->save();
                    break;

                case ContraindicationsTypesEnum::Hypereosinophilia:
                    $array                         = [
                        'code'        => 123,
                        'name'        => 'Гиперэозинофилия',
                        'description' => '',
                    ];
                    $contraindication              = new ContraindicationsType();
                    $contraindication->code        = $array['code'];
                    $contraindication->name        = $array['name'];
                    $contraindication->description = $array['description'];
                    $contraindication->save();
                    break;

                case ContraindicationsTypesEnum::MyeloproliferativeDisease:
                    $array                         = [
                        'code'        => 124,
                        'name'        => 'Миелопролиферативные заболевания',
                        'description' => '',
                    ];
                    $contraindication              = new ContraindicationsType();
                    $contraindication->code        = $array['code'];
                    $contraindication->name        = $array['name'];
                    $contraindication->description = $array['description'];
                    $contraindication->save();
                    break;

                case ContraindicationsTypesEnum::Pneumonia:
                    $array                         = [
                        'code'        => 125,
                        'name'        => 'Пневмония',
                        'description' => '',
                    ];
                    $contraindication              = new ContraindicationsType();
                    $contraindication->code        = $array['code'];
                    $contraindication->name        = $array['name'];
                    $contraindication->description = $array['description'];
                    $contraindication->save();
                    break;

                case ContraindicationsTypesEnum::StrongInhibitorIsoenzymeCYP3A4:
                    $array                         = [
                        'code'        => 126,
                        'name'        => 'Приём сильных ингибиторов цитохрома CYP3A4',
                        'description' => '',
                    ];
                    $contraindication              = new ContraindicationsType();
                    $contraindication->code        = $array['code'];
                    $contraindication->name        = $array['name'];
                    $contraindication->description = $array['description'];
                    $contraindication->save();
                    break;

                case ContraindicationsTypesEnum::StrongInductorIsoenzymeCYP3A4:
                    $array                         = [
                        'code'        => 127,
                        'name'        => 'Приём сильных индукторов цитохрома CYP3A4',
                        'description' => '',
                    ];
                    $contraindication              = new ContraindicationsType();
                    $contraindication->code        = $array['code'];
                    $contraindication->name        = $array['name'];
                    $contraindication->description = $array['description'];
                    $contraindication->save();
                    break;

                case ContraindicationsTypesEnum::Psychosis:
                    $array                         = [
                        'code'        => 128,
                        'name'        => 'Психоз',
                        'description' => '',
                    ];
                    $contraindication              = new ContraindicationsType();
                    $contraindication->code        = $array['code'];
                    $contraindication->name        = $array['name'];
                    $contraindication->description = $array['description'];
                    $contraindication->save();
                    break;

                case ContraindicationsTypesEnum::TakingAntidepressants:
                    $array                         = [
                        'code'        => 129,
                        'name'        => 'Приём антидепрессантов',
                        'description' => '',
                    ];
                    $contraindication              = new ContraindicationsType();
                    $contraindication->code        = $array['code'];
                    $contraindication->name        = $array['name'];
                    $contraindication->description = $array['description'];
                    $contraindication->save();
                    break;

                case ContraindicationsTypesEnum::AcuteMyocardialInfraction:
                    $array                         = [
                        'code'        => 130,
                        'name'        => 'Острое поражение миокарда',
                        'description' => '',
                    ];
                    $contraindication              = new ContraindicationsType();
                    $contraindication->code        = $array['code'];
                    $contraindication->name        = $array['name'];
                    $contraindication->description = $array['description'];
                    $contraindication->save();
                    break;

                case ContraindicationsTypesEnum::RecoveryPeriodAfterMyocardialInfraction:
                    $array                         = [
                        'code'        => 131,
                        'name'        => 'Период восстановления после инфаркта миокарда',
                        'description' => '',
                    ];
                    $contraindication              = new ContraindicationsType();
                    $contraindication->code        = $array['code'];
                    $contraindication->name        = $array['name'];
                    $contraindication->description = $array['description'];
                    $contraindication->save();
                    break;

                case ContraindicationsTypesEnum::AcuteAlcoholIntoxication:
                    $array                         = [
                        'code'        => 132,
                        'name'        => 'Острая алкогольная интоксикация',
                        'description' => '',
                    ];
                    $contraindication              = new ContraindicationsType();
                    $contraindication->code        = $array['code'];
                    $contraindication->name        = $array['name'];
                    $contraindication->description = $array['description'];
                    $contraindication->save();
                    break;

                case ContraindicationsTypesEnum::AcuteHypnoticsIntoxication:
                    $array                         = [
                        'code'        => 133,
                        'name'        => 'Острое отравление гипнотиками',
                        'description' => 'Острое отравление сновторными препаратами',
                    ];
                    $contraindication              = new ContraindicationsType();
                    $contraindication->code        = $array['code'];
                    $contraindication->name        = $array['name'];
                    $contraindication->description = $array['description'];
                    $contraindication->save();
                    break;

                case ContraindicationsTypesEnum::AcuteAnalgesicIntoxication:
                    $array                         = [
                        'code'        => 134,
                        'name'        => 'Острое отравление анальгетиками',
                        'description' => 'Острое отравление болеутоляющими препаратами',
                    ];
                    $contraindication              = new ContraindicationsType();
                    $contraindication->code        = $array['code'];
                    $contraindication->name        = $array['name'];
                    $contraindication->description = $array['description'];
                    $contraindication->save();
                    break;

                case ContraindicationsTypesEnum::AcutePsychotropicIntoxication:
                    $array                         = [
                        'code'        => 135,
                        'name'        => 'Острое отравление психотропами',
                        'description' => 'Острое отравление психотропными препаратами',
                    ];
                    $contraindication              = new ContraindicationsType();
                    $contraindication->code        = $array['code'];
                    $contraindication->name        = $array['name'];
                    $contraindication->description = $array['description'];
                    $contraindication->save();
                    break;

                case ContraindicationsTypesEnum::SevereCardiacConductionDisorders:
                    $array                         = [
                        'code'        => 136,
                        'name'        => 'Тяжёлые нарушения сердечной проводимости',
                        'description' => '',
                    ];
                    $contraindication              = new ContraindicationsType();
                    $contraindication->code        = $array['code'];
                    $contraindication->name        = $array['name'];
                    $contraindication->description = $array['description'];
                    $contraindication->save();
                    break;

                case ContraindicationsTypesEnum::YoungerSixYearsForOralUse   :
                    $array                         = [
                        'code'        => 137,
                        'name'        => 'Возраст до 6-ти лет для орального применения',
                        'description' => '',
                    ];
                    $contraindication              = new ContraindicationsType();
                    $contraindication->code        = $array['code'];
                    $contraindication->name        = $array['name'];
                    $contraindication->description = $array['description'];
                    $contraindication->save();
                    break;

                case ContraindicationsTypesEnum::YoungerTwelveYearsForIntramuscularInjection   :
                    $array                         = [
                        'code'        => 138,
                        'name'        => 'Возраст до 12-ти лет для внутримышечных инъекций',
                        'description' => '',
                    ];
                    $contraindication              = new ContraindicationsType();
                    $contraindication->code        = $array['code'];
                    $contraindication->name        = $array['name'];
                    $contraindication->description = $array['description'];
                    $contraindication->save();
                    break;

                case ContraindicationsTypesEnum::TakingMAOInhibitors   :
                    $array                         = [
                        'code'        => 139,
                        'name'        => 'Приём ингибиторов МАО',
                        'description' => '',
                    ];
                    $contraindication              = new ContraindicationsType();
                    $contraindication->code        = $array['code'];
                    $contraindication->name        = $array['name'];
                    $contraindication->description = $array['description'];
                    $contraindication->save();
                    break;

                case ContraindicationsTypesEnum::CardiacIschemia  :
                    $array                         = [
                        'code'        => 140,
                        'name'        => 'Кардиоишемия',
                        'description' => '',
                    ];
                    $contraindication              = new ContraindicationsType();
                    $contraindication->code        = $array['code'];
                    $contraindication->name        = $array['name'];
                    $contraindication->description = $array['description'];
                    $contraindication->save();
                    break;

                case ContraindicationsTypesEnum::HeartBlock  :
                    $array                         = [
                        'code'        => 141,
                        'name'        => 'Сердечная блокада',
                        'description' => '',
                    ];
                    $contraindication              = new ContraindicationsType();
                    $contraindication->code        = $array['code'];
                    $contraindication->name        = $array['name'];
                    $contraindication->description = $array['description'];
                    $contraindication->save();
                    break;

                case ContraindicationsTypesEnum::MyocardialInfarction  :
                    $array                         = [
                        'code'        => 142,
                        'name'        => 'Инфаркт миокарда',
                        'description' => '',
                    ];
                    $contraindication              = new ContraindicationsType();
                    $contraindication->code        = $array['code'];
                    $contraindication->name        = $array['name'];
                    $contraindication->description = $array['description'];
                    $contraindication->save();
                    break;

                case ContraindicationsTypesEnum::ArterialHypertension:
                    $array                         = [
                        'code'        => 143,
                        'name'        => 'Артериальная гипертензия',
                        'description' => '',
                    ];
                    $contraindication              = new ContraindicationsType();
                    $contraindication->code        = $array['code'];
                    $contraindication->name        = $array['name'];
                    $contraindication->description = $array['description'];
                    $contraindication->save();
                    break;

                case ContraindicationsTypesEnum::Stroke:
                    $array                         = [
                        'code'        => 144,
                        'name'        => 'Инсульт',
                        'description' => '',
                    ];
                    $contraindication              = new ContraindicationsType();
                    $contraindication->code        = $array['code'];
                    $contraindication->name        = $array['name'];
                    $contraindication->description = $array['description'];
                    $contraindication->save();
                    break;

                case ContraindicationsTypesEnum::TakingAnticholinergics:
                    $array                         = [
                        'code'        => 145,
                        'name'        => 'Приём антихолинергиков',
                        'description' => '',
                    ];
                    $contraindication              = new ContraindicationsType();
                    $contraindication->code        = $array['code'];
                    $contraindication->name        = $array['name'];
                    $contraindication->description = $array['description'];
                    $contraindication->save();
                    break;

                case ContraindicationsTypesEnum::TakingAdrenergicAgonists:
                    $array                         = [
                        'code'        => 146,
                        'name'        => 'Приём агонистов адреналина',
                        'description' => '',
                    ];
                    $contraindication              = new ContraindicationsType();
                    $contraindication->code        = $array['code'];
                    $contraindication->name        = $array['name'];
                    $contraindication->description = $array['description'];
                    $contraindication->save();
                    break;

                case ContraindicationsTypesEnum::TakingSympathomimetics:
                    $array                         = [
                        'code'        => 147,
                        'name'        => 'Приём симпатомиметиков',
                        'description' => '',
                    ];
                    $contraindication              = new ContraindicationsType();
                    $contraindication->code        = $array['code'];
                    $contraindication->name        = $array['name'];
                    $contraindication->description = $array['description'];
                    $contraindication->save();
                    break;

                case ContraindicationsTypesEnum::BladderAtony:
                    $array                         = [
                        'code'        => 148,
                        'name'        => 'Атония мочевого пузыря',
                        'description' => '',
                    ];
                    $contraindication              = new ContraindicationsType();
                    $contraindication->code        = $array['code'];
                    $contraindication->name        = $array['name'];
                    $contraindication->description = $array['description'];
                    $contraindication->save();
                    break;

                case ContraindicationsTypesEnum::BenignProstaticHyperplasia:
                    $array                         = [
                        'code'        => 149,
                        'name'        => 'Доброкачественная гиперплазия предстательной железы',
                        'description' => '',
                    ];
                    $contraindication              = new ContraindicationsType();
                    $contraindication->code        = $array['code'];
                    $contraindication->name        = $array['name'];
                    $contraindication->description = $array['description'];
                    $contraindication->save();
                    break;

                case ContraindicationsTypesEnum::FirstPregnancyTrimester:
                    $array                         = [
                        'code'        => 150,
                        'name'        => 'Первый триместр беремености',
                        'description' => '',
                    ];
                    $contraindication              = new ContraindicationsType();
                    $contraindication->code        = $array['code'];
                    $contraindication->name        = $array['name'];
                    $contraindication->description = $array['description'];
                    $contraindication->save();
                    break;

                case ContraindicationsTypesEnum::BenzodiazepinesHypersensitivity:
                    $array                         = [
                        'code'        => 151,
                        'name'        => 'Гиперчувствительность к бензодиазепинам',
                        'description' => '',
                    ];
                    $contraindication              = new ContraindicationsType();
                    $contraindication->code        = $array['code'];
                    $contraindication->name        = $array['name'];
                    $contraindication->description = $array['description'];
                    $contraindication->save();
                    break;

                case ContraindicationsTypesEnum::LoweredSeizureThreshold:
                    $array                         = [
                        'code'        => 152,
                        'name'        => 'Сниженный судорожный порог',
                        'description' => '',
                    ];
                    $contraindication              = new ContraindicationsType();
                    $contraindication->code        = $array['code'];
                    $contraindication->name        = $array['name'];
                    $contraindication->description = $array['description'];
                    $contraindication->save();
                    break;

                case ContraindicationsTypesEnum::SevereLiverDiseases    :
                    $array                         = [
                        'code'        => 153,
                        'name'        => 'Острые заболевания печени',
                        'description' => '',
                    ];
                    $contraindication              = new ContraindicationsType();
                    $contraindication->code        = $array['code'];
                    $contraindication->name        = $array['name'];
                    $contraindication->description = $array['description'];
                    $contraindication->save();
                    break;

                case ContraindicationsTypesEnum::SevereKidneyDiseases    :
                    $array                         = [
                        'code'        => 154,
                        'name'        => 'Острые заболевания почек',
                        'description' => '',
                    ];
                    $contraindication              = new ContraindicationsType();
                    $contraindication->code        = $array['code'];
                    $contraindication->name        = $array['name'];
                    $contraindication->description = $array['description'];
                    $contraindication->save();
                    break;

                case ContraindicationsTypesEnum::TakingSteroidHormones    :
                    $array                         = [
                        'code'        => 155,
                        'name'        => 'Приём стероидных гармонов',
                        'description' => '',
                    ];
                    $contraindication              = new ContraindicationsType();
                    $contraindication->code        = $array['code'];
                    $contraindication->name        = $array['name'];
                    $contraindication->description = $array['description'];
                    $contraindication->save();
                    break;

                case ContraindicationsTypesEnum::Neuroblastoma    :
                    $array                         = [
                        'code'        => 156,
                        'name'        => 'Нейробластома',
                        'description' => '',
                    ];
                    $contraindication              = new ContraindicationsType();
                    $contraindication->code        = $array['code'];
                    $contraindication->name        = $array['name'];
                    $contraindication->description = $array['description'];
                    $contraindication->save();
                    break;

                case ContraindicationsTypesEnum::Hyperthyroidism    :
                    $array                         = [
                        'code'        => 157,
                        'name'        => 'Гипертиреоз',
                        'description' => '',
                    ];
                    $contraindication              = new ContraindicationsType();
                    $contraindication->code        = $array['code'];
                    $contraindication->name        = $array['name'];
                    $contraindication->description = $array['description'];
                    $contraindication->save();
                    break;

                case ContraindicationsTypesEnum::TakingQuinidineLikeAntiarrhythmicDrugs:
                    $array                         = [
                        'code'        => 158,
                        'name'        => 'Приём хинидиновых противоаритмических препаратов',
                        'description' => '',
                    ];
                    $contraindication              = new ContraindicationsType();
                    $contraindication->code        = $array['code'];
                    $contraindication->name        = $array['name'];
                    $contraindication->description = $array['description'];
                    $contraindication->save();
                    break;

                case ContraindicationsTypesEnum::YoungerEightAge:
                    $array                         = [
                        'code'        => 159,
                        'name'        => 'Возраст меньше 8-ми лет',
                        'description' => '',
                    ];
                    $contraindication              = new ContraindicationsType();
                    $contraindication->code        = $array['code'];
                    $contraindication->name        = $array['name'];
                    $contraindication->description = $array['description'];
                    $contraindication->save();
                    break;

                case ContraindicationsTypesEnum::SuicideAttempt:
                    $array                         = [
                        'code'        => 160,
                        'name'        => 'Попытка суицида',
                        'description' => '',
                    ];
                    $contraindication              = new ContraindicationsType();
                    $contraindication->code        = $array['code'];
                    $contraindication->name        = $array['name'];
                    $contraindication->description = $array['description'];
                    $contraindication->save();
                    break;

                case ContraindicationsTypesEnum::Convulsions:
                    $array                         = [
                        'code'        => 161,
                        'name'        => 'Конвульсии',
                        'description' => '',
                    ];
                    $contraindication              = new ContraindicationsType();
                    $contraindication->code        = $array['code'];
                    $contraindication->name        = $array['name'];
                    $contraindication->description = $array['description'];
                    $contraindication->save();
                    break;

                case ContraindicationsTypesEnum::LiverDysfunction:
                    $array                         = [
                        'code'        => 162,
                        'name'        => 'Дисфункция печени',
                        'description' => '',
                    ];
                    $contraindication              = new ContraindicationsType();
                    $contraindication->code        = $array['code'];
                    $contraindication->name        = $array['name'];
                    $contraindication->description = $array['description'];
                    $contraindication->save();
                    break;

                case ContraindicationsTypesEnum::KidneyDysfunction:
                    $array                         = [
                        'code'        => 163,
                        'name'        => 'Дисфункция почек',
                        'description' => '',
                    ];
                    $contraindication              = new ContraindicationsType();
                    $contraindication->code        = $array['code'];
                    $contraindication->name        = $array['name'];
                    $contraindication->description = $array['description'];
                    $contraindication->save();
                    break;

                case ContraindicationsTypesEnum::AcuteCardiovascularDiseases:
                    $array                         = [
                        'code'        => 164,
                        'name'        => 'Острые заболевания сердечно-сосудистой системы',
                        'description' => '',
                    ];
                    $contraindication              = new ContraindicationsType();
                    $contraindication->code        = $array['code'];
                    $contraindication->name        = $array['name'];
                    $contraindication->description = $array['description'];
                    $contraindication->save();
                    break;

                case ContraindicationsTypesEnum::ArterialHypotension:
                    $array                         = [
                        'code'        => 165,
                        'name'        => 'Артериальная гипотензия',
                        'description' => '',
                    ];
                    $contraindication              = new ContraindicationsType();
                    $contraindication->code        = $array['code'];
                    $contraindication->name        = $array['name'];
                    $contraindication->description = $array['description'];
                    $contraindication->save();
                    break;

                case ContraindicationsTypesEnum::UrinaryDisorders:
                    $array                         = [
                        'code'        => 166,
                        'name'        => 'Расстройства мочеиспускания',
                        'description' => '',
                    ];
                    $contraindication              = new ContraindicationsType();
                    $contraindication->code        = $array['code'];
                    $contraindication->name        = $array['name'];
                    $contraindication->description = $array['description'];
                    $contraindication->save();
                    break;

                case ContraindicationsTypesEnum::CardiovascularDiseases:
                    $array                         = [
                        'code'        => 167,
                        'name'        => 'Заболевания сердечно-сосудистой системы',
                        'description' => '',
                    ];
                    $contraindication              = new ContraindicationsType();
                    $contraindication->code        = $array['code'];
                    $contraindication->name        = $array['name'];
                    $contraindication->description = $array['description'];
                    $contraindication->save();
                    break;

                case ContraindicationsTypesEnum::PredispositionToTheDevelopmentOfGlaucoma:
                    $array                         = [
                        'code'        => 168,
                        'name'        => 'Предрасположенность к развитию глаукомы',
                        'description' => '',
                    ];
                    $contraindication              = new ContraindicationsType();
                    $contraindication->code        = $array['code'];
                    $contraindication->name        = $array['name'];
                    $contraindication->description = $array['description'];
                    $contraindication->save();
                    break;

                case ContraindicationsTypesEnum::PepticUlcerOfTheStomachAndDuodenum:
                    $array                         = [
                        'code'        => 169,
                        'name'        => 'Язва желудка и двенадцатипёрстной кишки',
                        'description' => '',
                    ];
                    $contraindication              = new ContraindicationsType();
                    $contraindication->code        = $array['code'];
                    $contraindication->name        = $array['name'];
                    $contraindication->description = $array['description'];
                    $contraindication->save();
                    break;

                case ContraindicationsTypesEnum::ChronicRespiratoryDiseases:
                    $array                         = [
                        'code'        => 170,
                        'name'        => 'Хронические заболевания дыхания',
                        'description' => '',
                    ];
                    $contraindication              = new ContraindicationsType();
                    $contraindication->code        = $array['code'];
                    $contraindication->name        = $array['name'];
                    $contraindication->description = $array['description'];
                    $contraindication->save();
                    break;

                case ContraindicationsTypesEnum::EpilepticSeizures:
                    $array                         = [
                        'code'        => 171,
                        'name'        => 'Эпилептические припадки',
                        'description' => '',
                    ];
                    $contraindication              = new ContraindicationsType();
                    $contraindication->code        = $array['code'];
                    $contraindication->name        = $array['name'];
                    $contraindication->description = $array['description'];
                    $contraindication->save();
                    break;

                case ContraindicationsTypesEnum::PathologicalChangeInTheBloodPicture:
                    $array                         = [
                        'code'        => 172,
                        'name'        => 'Патологические изменения в картине крови',
                        'description' => '',
                    ];
                    $contraindication              = new ContraindicationsType();
                    $contraindication->code        = $array['code'];
                    $contraindication->name        = $array['name'];
                    $contraindication->description = $array['description'];
                    $contraindication->save();
                    break;

                case ContraindicationsTypesEnum::HeartRhythmDisorder:
                    $array                         = [
                        'code'        => 173,
                        'name'        => 'Нарушения ритма сердца',
                        'description' => '',
                    ];
                    $contraindication              = new ContraindicationsType();
                    $contraindication->code        = $array['code'];
                    $contraindication->name        = $array['name'];
                    $contraindication->description = $array['description'];
                    $contraindication->save();
                    break;

                case ContraindicationsTypesEnum::SevereRespiratoryDiseases:
                    $array                         = [
                        'code'        => 174,
                        'name'        => 'Острые заболевания дыхания',
                        'description' => '',
                    ];
                    $contraindication              = new ContraindicationsType();
                    $contraindication->code        = $array['code'];
                    $contraindication->name        = $array['name'];
                    $contraindication->description = $array['description'];
                    $contraindication->save();
                    break;
                case ContraindicationsTypesEnum::AcuteDepression:
                    $array                         = [
                        'code'        => 175,
                        'name'        => 'Острая депрессия',
                        'description' => '',
                    ];
                    $contraindication              = new ContraindicationsType();
                    $contraindication->code        = $array['code'];
                    $contraindication->name        = $array['name'];
                    $contraindication->description = $array['description'];
                    $contraindication->save();
                    break;
                case ContraindicationsTypesEnum::YoungerFourAges:
                    $array                         = [
                        'code'        => 176,
                        'name'        => 'Возраст меньше 4 лет',
                        'description' => '',
                    ];
                    $contraindication              = new ContraindicationsType();
                    $contraindication->code        = $array['code'];
                    $contraindication->name        = $array['name'];
                    $contraindication->description = $array['description'];
                    $contraindication->save();
                    break;
                case ContraindicationsTypesEnum::BreastCancer:
                    $array                         = [
                        'code'        => 177,
                        'name'        => 'Рак груди',
                        'description' => '',
                    ];
                    $contraindication              = new ContraindicationsType();
                    $contraindication->code        = $array['code'];
                    $contraindication->name        = $array['name'];
                    $contraindication->description = $array['description'];
                    $contraindication->save();
                    break;
                case ContraindicationsTypesEnum::YoungerFourteenAges:
                    $array                         = [
                        'code'        => 178,
                        'name'        => 'Возраст меньше 14 лет',
                        'description' => '',
                    ];
                    $contraindication              = new ContraindicationsType();
                    $contraindication->code        = $array['code'];
                    $contraindication->name        = $array['name'];
                    $contraindication->description = $array['description'];
                    $contraindication->save();
                    break;
                case ContraindicationsTypesEnum::Hypokalemia:
                    $array                         = [
                        'code'        => 179,
                        'name'        => 'Гипокалиемия',
                        'description' => 'Недостаток калия в крови.',
                    ];
                    $contraindication              = new ContraindicationsType();
                    $contraindication->code        = $array['code'];
                    $contraindication->name        = $array['name'];
                    $contraindication->description = $array['description'];
                    $contraindication->save();
                    break;
                case ContraindicationsTypesEnum::Agranulocytosis:
                    $array                         = [
                        'code'        => 180,
                        'name'        => 'Агранулоцитоз',
                        'description' => '',
                    ];
                    $contraindication              = new ContraindicationsType();
                    $contraindication->code        = $array['code'];
                    $contraindication->name        = $array['name'];
                    $contraindication->description = $array['description'];
                    $contraindication->save();
                    break;
                case ContraindicationsTypesEnum::MyeloproliferativeDisorder:
                    $array                         = [
                        'code'        => 181,
                        'name'        => 'Миелопролиферативные заболевания',
                        'description' => '',
                    ];
                    $contraindication              = new ContraindicationsType();
                    $contraindication->code        = $array['code'];
                    $contraindication->name        = $array['name'];
                    $contraindication->description = $array['description'];
                    $contraindication->save();
                    break;
                case ContraindicationsTypesEnum::Hyperlipidemia:
                    $array                         = [
                        'code'        => 182,
                        'name'        => 'Гиперлипидемия',
                        'description' => '',
                    ];
                    $contraindication              = new ContraindicationsType();
                    $contraindication->code        = $array['code'];
                    $contraindication->name        = $array['name'];
                    $contraindication->description = $array['description'];
                    $contraindication->save();
                    break;
                case ContraindicationsTypesEnum::YoungerFifteenAges:
                    $array                         = [
                        'code'        => 183,
                        'name'        => 'Возраст до 15 лет',
                        'description' => '',
                    ];
                    $contraindication              = new ContraindicationsType();
                    $contraindication->code        = $array['code'];
                    $contraindication->name        = $array['name'];
                    $contraindication->description = $array['description'];
                    $contraindication->save();
                    break;
                case ContraindicationsTypesEnum::Glaucoma:
                    $array                         = [
                        'code'        => 184,
                        'name'        => 'Глаукома',
                        'description' => '',
                    ];
                    $contraindication              = new ContraindicationsType();
                    $contraindication->code        = $array['code'];
                    $contraindication->name        = $array['name'];
                    $contraindication->description = $array['description'];
                    $contraindication->save();
                    break;
                case ContraindicationsTypesEnum::UseWithCYP3A4Inhibitors:
                    $array                         = [
                        'code'        => 185,
                        'name'        => 'Приём ингибиторов цитохрома CYP3A4',
                        'description' => '',
                    ];
                    $contraindication              = new ContraindicationsType();
                    $contraindication->code        = $array['code'];
                    $contraindication->name        = $array['name'];
                    $contraindication->description = $array['description'];
                    $contraindication->save();
                    break;
                case ContraindicationsTypesEnum::SeizureDisorder:
                    $array                         = [
                        'code'        => 186,
                        'name'        => 'Склонность к судорогам',
                        'description' => '',
                    ];
                    $contraindication              = new ContraindicationsType();
                    $contraindication->code        = $array['code'];
                    $contraindication->name        = $array['name'];
                    $contraindication->description = $array['description'];
                    $contraindication->save();
                    break;
                case ContraindicationsTypesEnum::Hypomagnesemia:
                    $array                         = [
                        'code'        => 187,
                        'name'        => 'Гипомагниемия',
                        'description' => '',
                    ];
                    $contraindication              = new ContraindicationsType();
                    $contraindication->code        = $array['code'];
                    $contraindication->name        = $array['name'];
                    $contraindication->description = $array['description'];
                    $contraindication->save();
                    break;
                case ContraindicationsTypesEnum::RespiratoryFailure:
                    $array                         = [
                        'code'        => 188,
                        'name'        => 'Дыхательная недостаточность',
                        'description' => '',
                    ];
                    $contraindication              = new ContraindicationsType();
                    $contraindication->code        = $array['code'];
                    $contraindication->name        = $array['name'];
                    $contraindication->description = $array['description'];
                    $contraindication->save();
                    break;
                case ContraindicationsTypesEnum::UseWithAlcohol:
                    $array                         = [
                        'code'        => 189,
                        'name'        => 'Приём алкоголя',
                        'description' => '',
                    ];
                    $contraindication              = new ContraindicationsType();
                    $contraindication->code        = $array['code'];
                    $contraindication->name        = $array['name'];
                    $contraindication->description = $array['description'];
                    $contraindication->save();
                    break;
                case ContraindicationsTypesEnum::RespiratoryDiseases:
                    $array                         = [
                        'code'        => 190,
                        'name'        => 'Респираторные заболевания',
                        'description' => '',
                    ];
                    $contraindication              = new ContraindicationsType();
                    $contraindication->code        = $array['code'];
                    $contraindication->name        = $array['name'];
                    $contraindication->description = $array['description'];
                    $contraindication->save();
                    break;
                case ContraindicationsTypesEnum::Depression:
                    $array                         = [
                        'code'        => 191,
                        'name'        => 'Депрессия',
                        'description' => '',
                    ];
                    $contraindication              = new ContraindicationsType();
                    $contraindication->code        = $array['code'];
                    $contraindication->name        = $array['name'];
                    $contraindication->description = $array['description'];
                    $contraindication->save();
                    break;
                case ContraindicationsTypesEnum::ObstructiveDiseasesOfTheGI:
                    $array                         = [
                        'code'        => 192,
                        'name'        => 'Обструкция ЖКТ',
                        'description' => '',
                    ];
                    $contraindication              = new ContraindicationsType();
                    $contraindication->code        = $array['code'];
                    $contraindication->name        = $array['name'];
                    $contraindication->description = $array['description'];
                    $contraindication->save();
                    break;
                case ContraindicationsTypesEnum::CognitiveDisorders:
                    $array                         = [
                        'code'        => 193,
                        'name'        => 'Когнитивные заболевания',
                        'description' => '',
                    ];
                    $contraindication              = new ContraindicationsType();
                    $contraindication->code        = $array['code'];
                    $contraindication->name        = $array['name'];
                    $contraindication->description = $array['description'];
                    $contraindication->save();
                    break;
                case ContraindicationsTypesEnum::Pancreatitis:
                    $array                         = [
                        'code'        => 194,
                        'name'        => 'Панкреатит',
                        'description' => '',
                    ];
                    $contraindication              = new ContraindicationsType();
                    $contraindication->code        = $array['code'];
                    $contraindication->name        = $array['name'];
                    $contraindication->description = $array['description'];
                    $contraindication->save();
                    break;
                case ContraindicationsTypesEnum::UreaCycleDisorders:
                    $array                         = [
                        'code'        => 195,
                        'name'        => 'Нарушение цикла мочевины',
                        'description' => '',
                    ];
                    $contraindication              = new ContraindicationsType();
                    $contraindication->code        = $array['code'];
                    $contraindication->name        = $array['name'];
                    $contraindication->description = $array['description'];
                    $contraindication->save();
                    break;
                case ContraindicationsTypesEnum::SevereDehydration:
                    $array                         = [
                        'code'        => 196,
                        'name'        => 'Острая дегидратация',
                        'description' => '',
                    ];
                    $contraindication              = new ContraindicationsType();
                    $contraindication->code        = $array['code'];
                    $contraindication->name        = $array['name'];
                    $contraindication->description = $array['description'];
                    $contraindication->save();
                    break;
                case ContraindicationsTypesEnum::ThyroidDisorders:
                    $array                         = [
                        'code'        => 197,
                        'name'        => 'Заболевания щитовидной железы',
                        'description' => '',
                    ];
                    $contraindication              = new ContraindicationsType();
                    $contraindication->code        = $array['code'];
                    $contraindication->name        = $array['name'];
                    $contraindication->description = $array['description'];
                    $contraindication->save();
                    break;
                case ContraindicationsTypesEnum::Tachyarrhythmia:
                    $array                         = [
                        'code'        => 198,
                        'name'        => 'Тахиаритмия',
                        'description' => '',
                    ];
                    $contraindication              = new ContraindicationsType();
                    $contraindication->code        = $array['code'];
                    $contraindication->name        = $array['name'];
                    $contraindication->description = $array['description'];
                    $contraindication->save();
                    break;
                case ContraindicationsTypesEnum::SevereAtherosclerosis:
                    $array                         = [
                        'code'        => 199,
                        'name'        => 'Острый атеросклероз',
                        'description' => '',
                    ];
                    $contraindication              = new ContraindicationsType();
                    $contraindication->code        = $array['code'];
                    $contraindication->name        = $array['name'];
                    $contraindication->description = $array['description'];
                    $contraindication->save();
                    break;
                case ContraindicationsTypesEnum::Insomnia:
                    $array                         = [
                        'code'        => 200,
                        'name'        => 'Бессоница',
                        'description' => '',
                    ];
                    $contraindication              = new ContraindicationsType();
                    $contraindication->code        = $array['code'];
                    $contraindication->name        = $array['name'];
                    $contraindication->description = $array['description'];
                    $contraindication->save();
                    break;
                case ContraindicationsTypesEnum::AnxietyDisorders:
                    $array                         = [
                        'code'        => 201,
                        'name'        => 'Тревожные расстройства',
                        'description' => '',
                    ];
                    $contraindication              = new ContraindicationsType();
                    $contraindication->code        = $array['code'];
                    $contraindication->name        = $array['name'];
                    $contraindication->description = $array['description'];
                    $contraindication->save();
                    break;
                case ContraindicationsTypesEnum::BronchialAsthma:
                    $array                         = [
                        'code'        => 202,
                        'name'        => 'Бронхиальная астма',
                        'description' => '',
                    ];
                    $contraindication              = new ContraindicationsType();
                    $contraindication->code        = $array['code'];
                    $contraindication->name        = $array['name'];
                    $contraindication->description = $array['description'];
                    $contraindication->save();
                    break;
                case ContraindicationsTypesEnum::CardiogenicShock:
                    $array                         = [
                        'code'        => 203,
                        'name'        => 'Кардиогенный шок',
                        'description' => '',
                    ];
                    $contraindication              = new ContraindicationsType();
                    $contraindication->code        = $array['code'];
                    $contraindication->name        = $array['name'];
                    $contraindication->description = $array['description'];
                    $contraindication->save();
                    break;
                case ContraindicationsTypesEnum::SevereBradycardia:
                    $array                         = [
                        'code'        => 204,
                        'name'        => 'Тяжёлая прадикардия',
                        'description' => '',
                    ];
                    $contraindication              = new ContraindicationsType();
                    $contraindication->code        = $array['code'];
                    $contraindication->name        = $array['name'];
                    $contraindication->description = $array['description'];
                    $contraindication->save();
                    break;
                case ContraindicationsTypesEnum::SevereHypotension:
                    $array                         = [
                        'code'        => 205,
                        'name'        => 'Тяжёлаяя гипотензия',
                        'description' => '',
                    ];
                    $contraindication              = new ContraindicationsType();
                    $contraindication->code        = $array['code'];
                    $contraindication->name        = $array['name'];
                    $contraindication->description = $array['description'];
                    $contraindication->save();
                    break;
                case ContraindicationsTypesEnum::PeripheralVascularDisease:
                    $array                         = [
                        'code'        => 206,
                        'name'        => 'Заболевания периферических сосудов',
                        'description' => '',
                    ];
                    $contraindication              = new ContraindicationsType();
                    $contraindication->code        = $array['code'];
                    $contraindication->name        = $array['name'];
                    $contraindication->description = $array['description'];
                    $contraindication->save();
                    break;
                case ContraindicationsTypesEnum::SleepApnea:
                    $array                         = [
                        'code'        => 207,
                        'name'        => 'Апноэ во сне',
                        'description' => '',
                    ];
                    $contraindication              = new ContraindicationsType();
                    $contraindication->code        = $array['code'];
                    $contraindication->name        = $array['name'];
                    $contraindication->description = $array['description'];
                    $contraindication->save();
                    break;
                case ContraindicationsTypesEnum::Bradycardia:
                    $array                         = [
                        'code'        => 208,
                        'name'        => 'Брадикардия',
                        'description' => '',
                    ];
                    $contraindication              = new ContraindicationsType();
                    $contraindication->code        = $array['code'];
                    $contraindication->name        = $array['name'];
                    $contraindication->description = $array['description'];
                    $contraindication->save();
                    break;
                case ContraindicationsTypesEnum::UlcerDisease:
                    $array                         = [
                        'code'        => 209,
                        'name'        => 'Язвенные заболевания',
                        'description' => '',
                    ];
                    $contraindication              = new ContraindicationsType();
                    $contraindication->code        = $array['code'];
                    $contraindication->name        = $array['name'];
                    $contraindication->description = $array['description'];
                    $contraindication->save();
                    break;
                case ContraindicationsTypesEnum::SevereRenalFailure:
                    $array = [
                        'code' => 210,
                        'name' => 'Тяжёлая почечная недостаточность',
                        'description' => ''
                    ];
                    $contraindication              = new ContraindicationsType();
                    $contraindication->code        = $array['code'];
                    $contraindication->name        = $array['name'];
                    $contraindication->description = $array['description'];
                    $contraindication->save();
                    break;
                case ContraindicationsTypesEnum::HemorrhagicStroke:
                    $array = [
                        'code' => 211,
                        'name' => 'Геморрагический инсульт',
                        'description' => 'Тип инсульта, при котором происходит кровоизлияние в мозг из-за разрыва кровеносного сосуда, что вызывает повреждение мозговой ткани и нарушение функций.'
                    ];
                    $contraindication              = new ContraindicationsType();
                    $contraindication->code        = $array['code'];
                    $contraindication->name        = $array['name'];
                    $contraindication->description = $array['description'];
                    $contraindication->save();
                    break;
                case ContraindicationsTypesEnum::SevereCoronaryDisease:
                    $array = [
                        'code' => 212,
                        'name' => 'Тяжёлое коронарное заболевание',
                        'description' => 'Состояние, при котором коронарные артерии значительно поражены, что затрудняет кровоснабжение сердечной мышцы и может приводить к серьезным сердечно-сосудистым осложнениям, включая инфаркты и сердечную недостаточность'
                    ];
                    $contraindication              = new ContraindicationsType();
                    $contraindication->code        = $array['code'];
                    $contraindication->name        = $array['name'];
                    $contraindication->description = $array['description'];
                    $contraindication->save();
                    break;
                case ContraindicationsTypesEnum::RecentStroke:
                    $array = [
                        'code' => 213,
                        'name' => 'Недавний инсульт',
                        'description' => 'Термин, который используется для обозначения недавно перенесенного инсульта, обычно в течение последних нескольких недель или месяцев.'
                    ];
                    $contraindication              = new ContraindicationsType();
                    $contraindication->code        = $array['code'];
                    $contraindication->name        = $array['name'];
                    $contraindication->description = $array['description'];
                    $contraindication->save();
                    break;
            }
        }
    }
}
