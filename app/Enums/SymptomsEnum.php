<?php
declare(strict_types=1);

namespace App\Enums;

enum SymptomsEnum: string
{
    case LossOfConsciousness = 'Потеря сознания';
    case Aura = 'Предшествующие приступу ощущения';
    case MuscleSpasms = 'Мускульные спазмы';
    // Маниакальные симптомы
    case ElevatedMood = 'Повышенное настроение';
    case Irritability = 'Раздражительность';
    case Hyperactivity = 'Гиперактивность';
    case Talkativeness = 'Болтливость';
    case DecreasedNeedForSleep = 'Снижение потребности в сне';
    case IncreasedSelfEsteem = 'Повышенная самооценка';
    case Distractibility = 'Отвлекаемость';
    case Impulsivity = 'Импульсивность';

    // Депрессивные симптомы
    case DepressedMood = 'Подавленное настроение';
    case Anhedonia = 'Потеря интереса или удовольствия';
    case Fatigue = 'Усталость или потеря энергии';
    case Insomnia = 'Нарушение сна';
    case Hypersomnia = 'Чрезмерная сонливость';
    case Guilt = 'Чувство вины или бесполезности';
    case PsychomotorRetardation = 'Психомоторная заторможенность';
    case Agitation = 'Ажитация или возбуждение';
    case ThoughtsOfDeath = 'Мысли о смерти или самоубийстве';
    case PoorConcentration = 'Плохая концентрация';
    case AppetiteChanges = 'Изменения аппетита';
    case WeightChanges = 'Изменения массы тела';
    case SelfHarm = 'Самоповреждение';
    case AlteredConsciousness = 'Изменённое сознание';
    case LossOfIdentity = 'Потеря идентичности';
    case UncontrolledMovements = 'Неконтролируемые движения';
    // Тревожные симптомы
    case PanicAttacks = 'Панические атаки';
    case ChronicAnxiety = 'Хроническая тревога';
    case Restlessness = 'Беспокойство';
    case MuscleTension = 'Мышечное напряжение';
    case FearOfDying = 'Страх смерти';
    case ShortnessOfBreath = 'Одышка';
    case SleepDisturbance = 'Нарушения сна';
    case Tachycardia = 'Тахикардия';
    case Sweating = 'Потливость';
    case HotFlushes = 'Приливы жара';
    case Dizziness = 'Головокружение';
    case MemoryLoss = 'Потеря памяти';
    case Distress = 'Дистресс';
    case PsychologicalFactors = 'Психологические факторы';
    // Психотические симптомы
    case Hallucinations = 'Галлюцинации';
    case Delusions = 'Бред';
    case Paranoia = 'Паранойя';
    case PsychoticFeatures = 'Психотические черты';
    case Catatonia = 'Кататония';
    case ThoughtInsertion = 'Внедрение мыслей';
    case ThoughtWithdrawal = 'Изъятие мыслей';
    case Depersonalization = 'Деперсонализация';
    case Derealization = 'Дереализация';

    // Когнитивные симптомы
    case MemoryProblems = 'Проблемы с памятью';
    case Indecisiveness = 'Нерешительность';
    case Disorientation = 'Дезориентация';
    case PoorJudgement = 'Плохое суждение';
    case Perseveration = 'Персеверация';
    case LearningDisabilities = 'Проблемы с обучаемостью';
    case CognitiveDecline = 'Снижение когнитивных функций';
    case ObsessiveThoughts = 'Навязчивые мысли';
    case RitualisticBehavior = 'Ритуализированное поведение';

    // Поведенческие симптомы
    case CompulsiveBehavior = 'Компульсивное поведение';
    case RiskyBehavior = 'Рискованное поведение';
    case SocialWithdrawal = 'Социальное отстранение';
    case SocialIsolation = 'Социальная изоляция';
    case Disinhibition = 'Дезингибиция';
    case SubstanceAbuse = 'Злоупотребление веществами';
    case Aggression = 'Агрессия';
    case HypochondriacalBehavior = 'Ипохондрическое поведение';
    case ConvictionInIllness = 'Убежденность в болезни';
    case SeekingMedicalAttention = 'Поиск медицинской помощи';
    case FeigningSymptoms = 'Симуляция симптомов';
    case ManipulativeBehavior = 'Манипулятивное поведение';
    case AttentionSeeking = 'Стремление к вниманию';
    case InterpersonalConflicts = 'Межличностные конфликты';
    case BehavioralAberrations = 'Поведенческие отклонения';

    // Соматические симптомы
    case BodyAches = 'Боли в теле';
    case Tremors = 'Дрожь';
    case Nausea = 'Тошнота';
    case Diarrhea = 'Диарея';
    case FatiguePhysical = 'Физическая усталость';

    // Эмоциональные симптомы
    case EmotionalInstability = 'Эмоциональная нестабильность';
    case EmotionalBlunting = 'Эмоциональное притупление';
    case EmotionalLability = 'Эмоциональная лабильность';
    case LowSelfEsteem = 'Низкая самооценка';
    case SelfCriticism = 'Самокритика';
    case Depression = 'Депрессия';
    case Anxiety = 'Тревога';
    case UnspecifiedBehavioralIssues = 'Неуточненные поведенческие проблемы';
    case CognitiveImpairment = 'Когнитивная недостаточность';
    case DevelopmentalDelay = 'Задержка развития';
    case LearningDifficulty = 'Трудности в обучении';

    // Поведенческие симптомы
    case BehavioralIssues = 'Поведенческие проблемы';

    // Соматические симптомы
    case Bedwetting = 'Энурез (ночное недержание мочи)';
    case FecalIncontinence = 'Энкопрез (недержание кала)';
    case FoodAvoidance = 'Избегание приема пищи';
    case WeightLoss = 'Потеря веса';
    case EatingNonfoodSubstances = 'Поедание несъедобных веществ';
    case IronDeficiency = 'Дефицит железа';
    case AbdominalPain = 'Боли в животе';
    case RepetitiveMovements = 'Повторяющиеся движения';

    // Речевые симптомы
    case SpeechInterruptions = 'Перерывы в речи';
    case AlteredSpeechPace = 'Изменение темпа речи';
    case DifficultyPronouncingWords = 'Трудности с произношением слов';
    case DelayedSpeech = 'Задержка речевого развития';
    case DifficultyExpressingThoughts = 'Трудности выражения мыслей';
    case PoorVocabulary = 'Слабый словарный запас';
    case DifficultyUnderstandingSpeech = 'Трудности с пониманием речи';
    case LossOfSpeechSkills = 'Утрата речевых навыков';

    // Когнитивные и академические симптомы
    case DifficultyReading = 'Трудности чтения';
    case SlowReadingSpeed = 'Медленная скорость чтения';
    case DifficultySpelling = 'Трудности с правописанием';
    case SlowWritingSpeed = 'Медленная скорость письма';
    case DifficultyWithMath = 'Трудности с математикой';
    case SlowCalculations = 'Медленные вычисления';

    // Двигательные симптомы
    case PoorCoordination = 'Плохая координация';
    case PoorBalance = 'Плохой баланс';
    case Clumsiness = 'Неуклюжесть';

    // Прочие симптомы
    case Seizures = 'Судороги';
    case SocialInteractionDeficits = 'Дефицит социальных взаимодействий';
    case CommunicationDeficits = 'Дефицит коммуникации';
    case RestrictedInterests = 'Ограниченные интересы';
    case SensorySensitivities = 'Чувствительность к сенсорным раздражителям';
    case DelayedLanguage = 'Задержка речи';
    case LanguageRegression = 'Регресс языка';

    // Расстройства моторики
    case MotorDysfunction = 'Нарушение моторики';
    case StereotypedMovements = 'Стереотипные движения';
    case MotorCoordinationDifficulties = 'Трудности координации движений';

    // Нарушения когнитивного и психологического развития
    case LossOfSkills = 'Утрата навыков';
    case ImpairedCognitiveDevelopment = 'Нарушение когнитивного развития';

    // Гиперактивность и внимание
    case AttentionDeficits = 'Дефицит внимания';

    // Прочие симптомы
    case BreathingIrregularities = 'Нерегулярное дыхание';

    // Когнитивные симптомы
    case ImpairedAttention = 'Нарушение внимания';

    // Эмоциональные симптомы
    case MoodSwings = 'Перепады настроения';

    // Поведенческие симптомы
    case CompulsiveActions = 'Компульсивные действия';

    // Личностные симптомы
    case PersonalityChanges = 'Изменения личности';

    // Особые симптомы
    case Aphasia = 'Афазия';
    case MotorDeficits = 'Нарушения моторики';
    case MoodInstability = 'Нестабильное настроение';
    case DisorganizedThinking = 'Дезорганизованное мышление';
    case PoorMood = 'Плохое настроение';

    // Поведенческие симптомы
    case Sleepwalking = 'Лунатизм';
    case BingeEating = 'Переедание';

    case LowLibido = 'Снижение сексуального влечения';
    case SexualDysfunction = 'Половая дисфункция';
    case ArousalDysfunction = 'Нарушение возбуждения';
    case OrgasmicDysfunction = 'Нарушение оргазма';
    case PrematureEjaculation = 'Преждевременная эякуляция';
    case SexualAverison = 'Отвращение к сексуальной активности';

    // Особые симптомы
    case BodyImageDistortion = 'Искаженное восприятие тела';
    case Amenorrhea = 'Аменорея';
    case DaytimeFatigue = 'Усталость в течение дня';
    case DaytimeSleepiness = 'Дневная сонливость';
    case SleepPatternDisruption = 'Нарушение ритма сна';

    // Эмоциональные симптомы
    case CompensatoryBehaviors = 'Компенсаторное поведение';
    case Nightmares = 'Ночные кошмары';
    case NightTerrors = 'Ночные страхи';

    // Физические симптомы
    case FearOfWeightGain = 'Страх набора веса';
    case SevereDepression = 'Тяжелая депрессия';

    // Поведенческие симптомы
    case BehavioralChanges = 'Изменения в поведении';
    case BehavioralDisturbances = 'Поведенческие нарушения';

    // Физические симптомы
    case ChronicPain = 'Хроническая боль';

    // Зависимость и злоупотребление
    case ToleranceIncrease = 'Увеличение толерантности';
    case DependenceOnLaxatives = 'Зависимость от слабительных';
    case ExcessiveIntake = 'Чрезмерное употребление';

    // Наркотические и интоксикационные симптомы
    case WithdrawalSymptoms = 'Симптомы отмены';
    case ImpairedConsciousness = 'Нарушение сознания';

    case Tremor = 'Тремор';
    case Confabulation = 'Конфабуляция';
    case ChronicPsychoticSymptoms = 'Хронические психотические симптомы';
    case PsychiatricSymptoms = 'Психиатрические симптомы';

    // Специфические симптомы
    case StressReaction = 'Стрессовая реакция';
    case SevereMemoryImpairment = 'Серьезное нарушение памяти';
    case DifficultyWithRecall = 'Трудности с воспоминаниями';
    case PsychomotorDisturbances = 'Психомоторные нарушения';
    case PerceptualDisturbances = 'Перцептивные нарушения';
    case EmotionalDisturbances = 'Эмоциональные расстройства';
    case PsychophysiologicalDisorders = 'Психофизиологические расстройства';
    case MoodChanges = 'Изменения настроения';
    case Amnesia = 'Амнезия';

    // Когнитивные симптомы
    case NeglectOfAlternativeInterests = 'Пренебрежение альтернативными интересами';
    case PhysicalSymptoms = 'Физические симптомы';
    case Drowsiness = 'Сонливость';
    case ReducedInhibitions = 'Снижение тормозов';
    case MotorDisturbances = 'Двигательные нарушения';
    case Confusion = 'Замешательство';
    case ImpairedJudgement = 'Нарушение суждения';
    case CoordinationProblems = 'Проблемы с координацией';
    case NeglectOfResponsibilities = 'Пренебрежение обязанностями';
    case Craving = 'Тяга';
    case ContinuedUseDespiteProblems = 'Продолжение использования несмотря на проблемы';
    case IncreasedAppetite = 'Повышенный аппетит';
    case IntellectualDisability = 'Интеллектуальная недостаточность';
    case IncreasedTolerance = 'Повышенная толерантность';
    case Headache = 'Головная боль';
    case RespiratoryDepression = 'Угнетение дыхания';
    case MusclePain = 'Мышечная боль';
    case SevereAnxiety = 'Сильная тревога';
    case AffectiveDisturbances = 'Аффективные расстройства';
    case PsychologicalSymptoms = 'Психологические симптомы';

    // Поведенческие симптомы
    case LackOfDependence = 'Отсутствие зависимости';

    // Зависимости и вредные привычки
    case StrongDesireToUse = 'Сильное желание использовать';
    case DifficultyControllingUse = 'Трудности в контроле использования';
    case ContinuedUseDespiteHarm = 'Продолжение использования несмотря на вред';

    // Интоксикация и физические симптомы
    case PhysicalHarm = 'Физический вред';
    case MentalHarm = 'Психический вред';
    case ImpairedCoordination = 'Нарушение координации';
    case SlurredSpeech = 'Невнятная речь';
    case IncreasedHeartRate = 'Увеличение частоты сердечных сокращений';

    // Психотические симптомы
    case Delirium = 'Делирий';

    // Симптомы абстиненции
    case PhysicalWithdrawalSymptoms = 'Физические симптомы отмены';
    case PsychologicalWithdrawalSymptoms = 'Психологические симптомы отмены';

    // Специфические симптомы
    case Euphoria = 'Эйфория';

    // Неопределенные симптомы
    case UnspecifiedPsychiatricSymptoms = 'Неопределенные психиатрические симптомы';
    // Неопределенные симптомы
    const UnspecifiedSymptoms = 'Неопределенные симптомы';
    const PersistentUseDespiteHarm = 'Устойчивое использование несмотря на вред';
    const DifficultyConcentrating = 'Трудности с концентрацией';
    const IdentityConfusion         = 'Смятение идентичности';
    const Deceitfulness             = 'Обман';
    const DifficultyInRelationships = 'Трудности в отношениях';
    const ImpairedSocialFunctioning = 'Нарушение социального функционирования';
    const ReducedEmpathy            = 'Снижение эмпатии';
    const RepetitiveBehavior        = 'Повторяющееся поведение';
    const ImpulsiveBehavior         = 'Импульсивное поведение';
    const LossOfSocialBoundaries    = 'Потеря социальных границ';
    const RepeatedBehavior          = 'Повторяющееся поведение';
    const MoodDisturbance           = 'Нарушение настроения';
    const DisorganizedSpeech        = 'Дезорганизованная речь';
    const RemissionState            = 'Состояние ремиссии';
    const PoorSocialSkills          = 'Бедные социальные навыки';
    const RestingTremor             = 'Тремор в покое';
    const SleepDisturbances         = 'Нарушения сна';
    case Disorganization = 'Дезорганизация';
    case InappropriateAffect = 'Неадекватный аффект';
    case Mutism = 'Мутаизм';
    case ResidualPsychoticSymptoms = 'Остаточные психотические симптомы';
    case Apathy = 'Апатия';
    case OddSpeech = 'Странная речь';
    case OddBehavior = 'Странное поведение';
    case MagicalThinking = 'Магическое мышление';
    case SocialAnxiety = 'Социальная тревожность';
    case AvoidanceBehavior = 'Поведение избегания';
    case SomaticComplaints = 'Соматические жалобы';
    case PoorSchoolPerformance = 'Плохая успеваемость в школе';
    case SpecificFears = 'Специфические страхи';
    case Blushing = 'Покраснение';
    case SeparationAnxiety = 'Тревога разлуки';
    case AttachmentIssues = 'Патологическая привязанность';
    case BehavioralProblems = 'Проблемы с поведением';
    case Bradykinesia = 'Брадикинезия';
    case MuscleRigidity = 'Мышечная ригидность';
    case PosturalInstability = 'Постуральная нестабильность';
    case ChestDiscomfort = 'Дискомфорт в грудине';
    case SevereCognitiveImpairment = 'Серьезное когнитивное нарушение';
    case MinimalSocialInteraction = 'Минимальное социальное взаимодействие';
    case AggressiveBehavior = 'Агрессивное поведение';
    case SelfCareDeficits = 'Дефициты самообслуживания';
    case PoorMotorSkills = 'Плохие моторные навыки';
    case AtypicalBehavior = 'Атипичное поведение';
    case DelinquentBehavior = 'Делинквентное поведение';
    case Hostility = 'Враждебность';
    case PoorSocialInteraction = 'Плохое социальное взаимодействие';
    case PoorEyeContact = 'Плохой зрительный контакт';
    case LackOfSocialResponse = 'Отсутствие социальной реакции';
    case InhibitedBehavior = 'Сдержанное поведение';
    case DifficultyFormingAttachments = 'Трудности в формировании привязанностей';
    case LackOfResponseToComforting = 'Отсутствие реакции на утешение';
    case EmotionalDysregulation = 'Эмоциональная дисрегуляция';
    case ImpairedSocialJudgement = 'Нарушенное социальное суждение';
    case LackOfStrangerAnxiety = 'Отсутствие тревоги перед незнакомцами';
    case InappropriateSocialBehavior = 'Неуместное социальное поведение';
    case AnxietyInSocialSituations = 'Тревога в социальных ситуациях';
    case AcuteAnxiety = 'Острая тревога';
    case IntrusiveMemories = 'Навязчивые воспоминания';
    case Hyperarousal = 'Гипервозбуждение';
    case StressResponse = 'Реакция на стресс';
    case Lying = 'Ложь';
    case Suspiciousness = 'Подозрительность';
    case Distrust = 'Недоверие';
    case Aggressiveness = 'Агрессивность';
    case SensitivityToCriticism = 'Чувствительность к критике';
    case TendencyToHoldGrudges = 'Склонность держать обиды';
    case EmotionalColdness = 'Эмоциональная холодность';
    case IndifferenceToPraise = 'Равнодушие к похвале';
    case PreferenceForSolitaryActivities = 'Предпочтение одиночной деятельности';
    case LackOfGuilt = 'Отсутствие чувства вины';
    case AttentionSeekingBehavior = 'Поведение, направленное на привлечение внимания';
    case ExaggeratedEmotions = 'Преувеличенные эмоции';
    case Suggestibility = 'Внушаемость';
    case ShallowEmotionalExpression = 'Поверхностное эмоциональное выражение';
    case Perfectionism = 'Перфекционизм';
    case NeedForControl = 'Потребность в контроле';
    case RigidThinking = 'Жесткое мышление';
    case ReluctanceToDelegate = 'Нежелание делегировать';
    case FearOfCriticism = 'Страх критики';
    case SensitivityToRejection = 'Чувствительность к отказу';
    case NeedForReassurance = 'Потребность в уверении';
    case DifficultyMakingDecisions = 'Трудности в принятии решений';
    case FearOfAbandonment = 'Страх быть покинутым';
    case SubmissiveBehavior = 'Подчиненное поведение';
    case AtypicalBehaviorPatterns = 'Атипичные модели поведения';
    case BehavioralDeviations = 'Отклонения в поведении';
    case PersistentAnxiety = 'Устойчивая тревога';
    case EmotionalNumbness = 'Эмоциональное онемение';
    case Hypervigilance = 'Гипербдительность';
    case AngerOutbursts = 'Вспышки гнева';
    case ImpulseControlProblems = 'Проблемы с контролем импульсов';
    case PreoccupationWithGambling = 'Озабоченность азартными играми';
    case FinancialProblems = 'Финансовые проблемы';
    case SocialConflict = 'Социальный конфликт';
    case CompulsiveFireSetting = 'Компульсивное поджигание';
    case PleasureFromFireSetting = 'Удовольствие от поджигания';
    case CompulsiveStealing = 'Компульсивная кража';
    case PleasureFromStealing = 'Удовольствие от кражи';
    case HairPulling = 'Выдергивание волос';
    case SenseOfReliefAfterPulling = 'Чувство облегчения после выдергивания';
    case PersistentDistress = 'Устойчивый дистресс';
    case DistressFromImpulses = 'Тревога из-за импульсов';
    case DesireToChangeSex = 'Желание сменить пол';
    case DiscomfortWithBiologicalSex = 'Дискомфорт по поводу биологического пола';
    case SeekingMedicalTransition = 'Стремление к медицинскому переходу';
    case SocialDysphoria = 'Социальная дисфория';
    case PersistentGenderDysphoria = 'Устойчивая гендерная дисфория';

    case CrossDressing = 'Переодевание в одежду противоположного пола';
    case AbsenceOfDesireForSexChange = 'Отсутствие желания сменить пол';
    case PleasureFromCrossDressing = 'Удовольствие от переодевания';
    case OccasionalGenderDysphoria = 'Эпизодическая гендерная дисфория';

    case DesireToBeOppositeSex = 'Желание быть противоположным полом';
    case GenderDysphoria = 'Гендерная дисфория';
    case PersistentGenderDiscomfort = 'Устойчивая неудовлетворенность гендером';

    case DesireForAlternativeGenderIdentity = 'Желание альтернативной гендерной идентичности';
    case PersistentDiscomfortWithSex = 'Постоянный дискомфорт с полом';
    case OccasionalCrossDressing = 'Эпизодическое переодевание';

    case OccasionalGenderDiscomfort = 'Эпизодический гендерный дискомфорт';
    case DesireToChangeGenderRoles = 'Желание изменить гендерные роли';
    case SexualArousalFromObjects = 'Сексуальное возбуждение от объектов';
    case PersistentFocusOnFetish = 'Постоянная фиксация на фетише';
    case DisruptionInSocialFunctioning = 'Нарушение социальной функции';
    case RepeatedFetishisticBehavior = 'Повторяющееся фетишистское поведение';
    case LossOfInterestInOtherActivities = 'Потеря интереса к другим видам деятельности';
    case MultipleSomaticComplaints = 'Множественные соматические жалобы';
    case ExcessiveWorryAboutHealth = 'Чрезмерное беспокойство о здоровье';
    case ExcessiveConcernAboutAppearance = 'Чрезмерная озабоченность внешним видом';
    case PersistentFearOfIllness = 'Стойкий страх болезни';
    case Palpitations = 'Учащённое сердцебиение';
    case MotorTics = 'Моторные тики';
    case VocalTics = 'Голосовые тики';
    case ChronicCourse = 'Хроническое течение';
    case SexualArousalFromCrossDressing = 'Сексуальное возбуждение от переодевания';
    case ExhibitionisticBehavior = 'Эксгибиционистское поведение';
    case SexualArousalFromExhibitionism = 'Сексуальное возбуждение от эксгибиционизма';

    case VoyeuristicBehavior = 'Вуайеристское поведение';
    case SexualArousalFromVoyeurism = 'Сексуальное возбуждение от вуайеризма';

    case SadisticBehavior = 'Садистское поведение';
    case MasochisticBehavior = 'Мазохистское поведение';

    case UnusualSexualPreference = 'Необычные сексуальные предпочтения';
    case PersistentFocusOnPreference = 'Постоянная фиксация на предпочтении';

    case SexualArousalIssues = 'Проблемы с сексуальным возбуждением';
    case PersonalityChange = 'Изменение личности';
    case DepressiveMood = 'Депрессивное настроение';
    case UnspecifiedPersonalityChanges = 'Неуточненные изменения личности';
    case MildDepression = 'Лёгкая депрессия';
    case HypomanicEpisodes = 'Гипоманиакальные эпизоды';
    case ChronicDepression = 'Хроническая депрессия';
    case Defiance = 'Неповиновение';
    case RuleBreakingBehavior = 'Нарушение правил';
    case DifficultyInSocialInteraction = 'Трудности в социальном взаимодействии';
    case CommunicationImpairment = 'Нарушение коммуникации';
    case OverFriendliness = 'Чрезмерная дружелюбность';
    case LackOfSelectivityInAttachments = 'Отсутствие избирательности в привязанностях';
    case MildCognitiveImpairment = 'Легкие когнитивные нарушения';
    case BasicSocialSkills = 'Базовые социальные навыки';
    case LearningDifficulties = 'Затруднения в обучении';
    case BehavioralDisruptions = 'Нарушения поведения';
    case LimitedCommunicationSkills = 'Ограниченные коммуникативные навыки';
    case ModerateCognitiveImpairment = 'Умеренные когнитивные нарушения';
    case ExcessiveFear = 'Чрезмерный страх';
    case RapidBreathing = 'Учащенное дыхание';
    case ExcessiveWorry = 'Чрезмерное беспокойство';

    /**
     * Получить список всех значений.
     *
     * @return array
     */
    public static function all(): array
    {
        return array_column(self::cases(), 'value');
    }
}
