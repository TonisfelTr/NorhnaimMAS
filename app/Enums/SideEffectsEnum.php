<?php

namespace app\Enums;

enum SideEffectsEnum: int
{
    case Drowsiness = 1;
    case Asthenia = 2;
    case FaintingStates = 3;
    case Restlessness = 4;
    case Disorientation = 5;
    case Excitation = 6;
    case Hallucinations = 7;
    case Anxiety = 8;
    case MotorRestlessness = 9;
    case ManiaState = 10;
    case HypomaniaState = 11;
    case Aggression = 12;
    case MemoryImpairment = 13;
    case Depersonalization = 14;
    case IncreasedDepression = 15;
    case AttentionConcentrateDecreased = 16;
    case Insomnia = 17;
    case Nightmares = 18;
    case Yawn = 19;
    case PsychosisSymptoms = 20;
    case Headache = 21;
    case Myoclonus = 22;
    case Disarthria = 23;
    case Tremor = 24;
    case Paresthesia = 25;
    case MyastheniaGravis = 26;
    case Ataxia = 27;
    case ExtrapyramidalSyndrome = 28;
    case IncreasedFrequencyAndIntensificationOfEpilepticSeizures = 29;
    case EEGChanges = 30;
    case OrthostaticHypotension = 31;
    case Tachycardia = 32;
    case CardiacConductionDisorder = 33;
    case Dizziness = 34;
    case NonspecificECGChanges = 35;
    case Arrhythmia = 36;
    case BloodPressureLability = 37;
    case IntraventricularConductionDisorder = 38;
    case Nausea = 39;
    case Vomiting = 40;
    case Heartburn = 41;
    case Gastralgia = 42;
    case BodyWeightIncrease = 43;
    case Stomatitis = 44;
    case TasteChanges = 45;
    case Diarrhea = 46;
    case TongueDarkening = 47;
    case LiverDiseases = 48;
    case CholestaticJaundice = 49;
    case Hepatitis = 50;
    case TesticularSwelling = 51;
    case Gynecomastia = 52;
    case MammaryGlandEnlargement = 53;
    case LibidoChange = 54;
    case PotencyDecrease = 55;
    case Hypoglycemia = 56;
    case Hyperglycemia = 57;
    case Hyponatremia = 58;
    case InappropriateADHSecretionSyndrome = 59;
    case Agranulocytosis = 60;
    case Leukopenia = 61;
    case Thrombocytopenia = 62;
    case Purpura = 63;
    case Eosinophilia = 64;
    case SkinRash = 65;
    case ItchySkin = 66;
    case Hives = 67;
    case Photosensitivity = 68;
    case FaceAndTongueSwelling = 69;
    case DryMouth = 70;
    case AccommodationDisturbances = 71;
    case BlurredVision = 72;
    case Mydriasis = 73;
    case IntraocularPressureIncrease = 74;
    case Constipation = 75;
    case ParalyticObstruction = 76;
    case UrinaryRetention = 77;
    case SweatingDecrease = 78;
    case Confusion = 79;
    case Delirium = 80;
    case HairLoss = 81;
    case Tinnitus = 82;
    case Edema = 83;
    case Hyperpyrexia = 84;
    case SwollenLymphNodes = 85;
    case Pollakiuria = 86;
    case Hypoproteinemia = 87;
    case BodyWeightDecrease = 88;
    case Galactorrhea = 89;
    case NoiseInEar = 90;
    case Swelling = 91;
    case Nervousness = 92;
    case SleepDisorders = 93;
    case StrongFear = 94;
    case Seizures = 95;
    case ArterialHypotension = 96;
    case DryMucousMembranes = 97;
    case EyePain = 98;
    case UrinaryDisorders = 99;
    case Anorexia = 100;
    case Dyspepsia = 101;
    case DiscomfortInTheEpigastricRegion = 102;
    case DiscomfortFeelings = 103;
    case IncreasedActivityOfLiverEnzymes = 104;
    case TransientHyponatremia = 105;
    case Fear = 106;
    case HeartbeatFeelings = 107;
    case IncreasedSweating = 108;
    case BodyWeightChange = 109;
    case Lethargy = 110;
    case EmotionalLability = 111;
    case MentalityChanges = 112;
    case Agitation = 113;
    case Apathy = 114;
    case Hostility = 115;
    case EpilepticSeizures = 116;
    case Vertigo = 117;
    case Hyperesthesia = 118;
    case Hyperkinesis = 119;
    case Hyperkinesia = 120;
    case Granulocytopenia = 121;
    case Neutropenia = 122;
    case AplasticAnemia = 124;
    case Anemia = 125;
    case SlightIncreaseInAppetite = 126;
    case StomachAche = 127;
    case Thirst = 128;
    case IncreasedActivityOfLiverTransaminases = 129;
    case DecreasePotency = 130;
    case Dysmenorrhea = 131;
    case Urticaria = 132;
    case FluLikeSyndrome = 133;
    case Suffocation = 134;
    case EdemaSyndrome = 135;
    case Myalgia = 136;
    case BackPain = 137;
    case Dysuria = 138;
    case Akathisia = 139;
    case DystonicExtrapyramidalDisorders = 140;
    case ParkinsonSyndrome = 141;
    case ThermoregulationDisorders = 142;
    case NeurolepticMalignantSyndrome = 143;
    case DyspepticSymptoms = 144;
    case MenstrualIrregularities = 145;
    case Impotence = 146;
    case ExfoliativeDermatitis = 147;
    case ErythemaMultiforme = 148;
    case SkinPigmentation = 149;
    case DrugDeposits = 150;
    case TardiveDyskinesia = 151;
    case DyspepticSymptom = 152;
    case NasalCongestion = 153;
    case PigmentaryRetinopathy = 154;
    case HeartRhythmDisturbances = 155;
    case AllergicReaction = 156;
    case Angioedema = 157;
    case Priapism = 158;
    case Melanosis = 159;
    case Sedation = 160;
    case PsychomotorDisorders = 161;
    case PosturalHypotension = 162;
    case VisionImpairment = 163;
    case SwellingOfTheNipples = 164;
    case InhibitionOfEjaculation = 165;
    case IncreasedFatigue = 166;
    case ECGChanges = 167;
    case CornealOpacity = 168;
    case Cataract = 169;
    case Leukocytosis = 170;
    case HemolyticAnemia = 171;
    case HotFlushes = 172;
    case Amenorrhea = 173;
    case LibidoDecrease = 174;
    case CarbohydrateMetabolismDisorder = 175;
    case AppetiteIncrease = 176;
    case OculogyricCrises = 177;
    case DystonicPhenomena = 178;
    case ArterialPressureLability = 179;
    case Erythropenia = 180;
    case Lymphomonocytosis = 181;
    case Jaundice = 182;
    case Toxicoderma = 183;
    case DrySkin = 184;
    case SebaceousGlandsHyperfunction = 185;
    case Hypersalivation = 186;
    case Frigidity = 187;
    case ThirstReduction = 188;
    case HeatStroke = 189;
    case Alopecia = 190;
    case Hypoglyсemia = 191;
    case Hyperprolactinemia = 192;
    case DiabetesMellitus = 193;
    case AppetiteDecrease = 194;
    case ImpairedGlucoseTolerance = 195;
    case LaryngealEdema = 196; // Добавлено
    case Bronchospasm = 197;    // Добавлено
    case RespiratoryDepression = 198; // Добавлено
    case Fatigue = 199; // Добавлено
    case Dyskinesia = 200; // Добавлено
    case QTProlongation = 201;
    case Hypotension = 202;
    case MuscleWeakness = 203;
    case Hypoesthesia = 204;
    case Depression = 205;
    case Dysphagia = 206;
    case Palpitations = 207;
    case Hyperthermia = 208;
    case Myocarditis = 209;
    case Pericarditis = 210;
    case Fever = 211;
    case ExtrapyramidalSymptoms = 212;
    case Hypercholesterolemia = 213;
    case Hypertriglyceridemia = 214;
    case Hypothermia = 215;
    case Dyspnea = 216;
    case CardiacArrest = 217;
    case PulmonaryEmbolism = 218;
    case MuscleRigidity = 219;
    case SexualDysfunction = 220;
    case IncreasedBloodPressure = 221;
    case Weakness = 222;
    case Irritability = 223;
    case Hepatotoxicity = 224;
    case Pancreatitis = 225;
    case LiverEnzymeIncrease = 226;
    case BoneMarrowSuppression = 227;
    case DoubleVision = 228;
    case Hypothyroidism = 229;
    case Polyuria = 230;
    case KidneyImpairment = 231;
    case Bradycardia = 232;
    case ColdExtremities = 233;
    case SleepWalking = 234;
    case Amnesia = 235;
    case BitterTaste = 236;
    case MuscleCramps =237;
    const MuscleSpasms             = 238;
    const Sleepwalking             = 239;
    const KidneyFunctionImpairment = 240;
    const ElevatedLiverEnzymes     = 241;
    const HighBloodPressure        = 242;
}