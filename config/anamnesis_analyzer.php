<?php

return [
    'min_sentences' => 15,
    'max_input_bytes' => 65536,
    'max_input_chars' => 30000,
    'max_sentences' => 250,
    'max_candidate_diagnoses' => 48,
    'max_candidate_symptoms' => 260,
    'semantic_batch_size' => 55,
    'semantic_max_tokens' => 1800,
    'global_fallback_symptom_threshold' => 6,
    'global_fallback_score_threshold' => 0.58,
    'global_fallback_require_criteria' => true,
    'min_diagnosis_score' => 0.4,
    'rate_limit_per_minute' => 12,
    'semantic_enabled' => true,
    'global_fallback_enabled' => true,
    'reject_prompt_injection' => true,
    'meta' => [
        'F00.0' => [
        'needs' => [
        'dementia_profile' => 'strong',
        'progressive_course' => true,
        'duration' => [
        'long' => true,
        ],
        ],
        'forbid' => [
        'acute_onset' => true,
        ],
        ]
        ,
        'F00.1' => [
        'needs' => [
        'dementia_profile' => 'strong',
        'progressive_course' => true,
        'duration' => [
        'long' => true,
        ],
        ],
        'forbid' => [
        'acute_onset' => true,
        ],
        ]
        ,
        'F00.2' => [
        'needs' => [
        'dementia_profile' => 'strong',
        'progressive_course' => true,
        'duration' => [
        'long' => true,
        ],
        ],
        'forbid' => [
        'acute_onset' => true,
        ],
        ]
        ,
        'F01.0' => [
        'needs' => [
        'dementia_profile' => 'strong',
        'duration' => [
        'long' => true,
        ],
        ],
        'hints' => [
        'vascular_history' => true,
        'stepwise_decline' => true,
        ],
        ]
        ,
        'F01.1' => [
        'needs' => [
        'dementia_profile' => 'strong',
        'duration' => [
        'long' => true,
        ],
        ],
        'hints' => [
        'vascular_history' => true,
        'stepwise_decline' => true,
        ],
        ]
        ,
        'F01.2' => [
        'needs' => [
        'dementia_profile' => 'strong',
        'duration' => [
        'long' => true,
        ],
        ],
        'hints' => [
        'vascular_history' => true,
        'stepwise_decline' => true,
        ],
        ]
        ,
        'F01.3' => [
        'needs' => [
        'dementia_profile' => 'strong',
        'duration' => [
        'long' => true,
        ],
        ],
        'hints' => [
        'vascular_history' => true,
        'stepwise_decline' => true,
        ],
        ]
        ,
        'F01.9' => [
        'needs' => [
        'dementia_profile' => 'strong',
        'duration' => [
        'long' => true,
        ],
        ],
        'hints' => [
        'vascular_history' => true,
        'stepwise_decline' => true,
        ],
        ]
        ,
        'F02.0' => [
        ]
        ,
        'F02.1' => [
        ]
        ,
        'F02.2' => [
        ]
        ,
        'F02.3' => [
        ]
        ,
        'F02.8' => [
        ]
        ,
        'F02.9' => [
        ]
        ,
        'F03' => [
        'needs' => [
        'dementia_profile' => 'strong',
        'progressive_course' => true,
        'duration' => [
        'long' => true,
        ],
        ],
        'forbid' => [
        'acute_onset' => true,
        ],
        ]
        ,
        'F04' => [
        ]
        ,
        'F05.0' => [
        ]
        ,
        'F05.1' => [
        ]
        ,
        'F05.8' => [
        ]
        ,
        'F05.9' => [
        ]
        ,
        'F06.0' => [
        ]
        ,
        'F06.1' => [
        ]
        ,
        'F06.2' => [
        ]
        ,
        'F06.3' => [
        ]
        ,
        'F06.4' => [
        ]
        ,
        'F06.5' => [
        ]
        ,
        'F06.6' => [
        ]
        ,
        'F06.7' => [
        ]
        ,
        'F06.8' => [
        ]
        ,
        'F06.9' => [
        ]
        ,
        'F07.0' => [
        ]
        ,
        'F07.1' => [
        ]
        ,
        'F07.2' => [
        ]
        ,
        'F07.8' => [
        ]
        ,
        'F07.9' => [
        ]
        ,
        'F10.0' => [
        'needs' => [
        'substance' => '>=2',
        ],
        'notes' => [
        'avoid_false_positive_if_no_substance_text' => true,
        ],
        ]
        ,
        'F10.1' => [
        'needs' => [
        'substance' => '>=2',
        ],
        'notes' => [
        'avoid_false_positive_if_no_substance_text' => true,
        ],
        ]
        ,
        'F10.2' => [
        'needs' => [
        'substance' => '>=2',
        ],
        'notes' => [
        'avoid_false_positive_if_no_substance_text' => true,
        ],
        ]
        ,
        'F10.3' => [
        'needs' => [
        'substance' => '>=2',
        ],
        'notes' => [
        'avoid_false_positive_if_no_substance_text' => true,
        ],
        ]
        ,
        'F10.4' => [
        'needs' => [
        'substance' => '>=2',
        ],
        'notes' => [
        'avoid_false_positive_if_no_substance_text' => true,
        ],
        ]
        ,
        'F10.5' => [
        'needs' => [
        'substance' => '>=2',
        ],
        'notes' => [
        'avoid_false_positive_if_no_substance_text' => true,
        ],
        ]
        ,
        'F10.6' => [
        'needs' => [
        'substance' => '>=2',
        ],
        'notes' => [
        'avoid_false_positive_if_no_substance_text' => true,
        ],
        ]
        ,
        'F10.7' => [
        'needs' => [
        'substance' => '>=2',
        ],
        'notes' => [
        'avoid_false_positive_if_no_substance_text' => true,
        ],
        ]
        ,
        'F10.8' => [
        'needs' => [
        'substance' => '>=2',
        ],
        'notes' => [
        'avoid_false_positive_if_no_substance_text' => true,
        ],
        ]
        ,
        'F10.9' => [
        'needs' => [
        'substance' => '>=2',
        ],
        'notes' => [
        'avoid_false_positive_if_no_substance_text' => true,
        ],
        ]
        ,
        'F11.0' => [
        'needs' => [
        'substance' => '>=2',
        ],
        'notes' => [
        'avoid_false_positive_if_no_substance_text' => true,
        ],
        ]
        ,
        'F11.1' => [
        'needs' => [
        'substance' => '>=2',
        ],
        'notes' => [
        'avoid_false_positive_if_no_substance_text' => true,
        ],
        ]
        ,
        'F11.2' => [
        'needs' => [
        'substance' => '>=2',
        ],
        'notes' => [
        'avoid_false_positive_if_no_substance_text' => true,
        ],
        ]
        ,
        'F11.3' => [
        'needs' => [
        'substance' => '>=2',
        ],
        'notes' => [
        'avoid_false_positive_if_no_substance_text' => true,
        ],
        ]
        ,
        'F11.4' => [
        'needs' => [
        'substance' => '>=2',
        ],
        'notes' => [
        'avoid_false_positive_if_no_substance_text' => true,
        ],
        ]
        ,
        'F11.5' => [
        'needs' => [
        'substance' => '>=2',
        ],
        'notes' => [
        'avoid_false_positive_if_no_substance_text' => true,
        ],
        ]
        ,
        'F11.6' => [
        'needs' => [
        'substance' => '>=2',
        ],
        'notes' => [
        'avoid_false_positive_if_no_substance_text' => true,
        ],
        ]
        ,
        'F11.7' => [
        'needs' => [
        'substance' => '>=2',
        ],
        'notes' => [
        'avoid_false_positive_if_no_substance_text' => true,
        ],
        ]
        ,
        'F11.8' => [
        'needs' => [
        'substance' => '>=2',
        ],
        'notes' => [
        'avoid_false_positive_if_no_substance_text' => true,
        ],
        ]
        ,
        'F11.9' => [
        'needs' => [
        'substance' => '>=2',
        ],
        'notes' => [
        'avoid_false_positive_if_no_substance_text' => true,
        ],
        ]
        ,
        'F12.0' => [
        'needs' => [
        'substance' => '>=2',
        ],
        'notes' => [
        'avoid_false_positive_if_no_substance_text' => true,
        ],
        ]
        ,
        'F12.1' => [
        'needs' => [
        'substance' => '>=2',
        ],
        'notes' => [
        'avoid_false_positive_if_no_substance_text' => true,
        ],
        ]
        ,
        'F12.2' => [
        'needs' => [
        'substance' => '>=2',
        ],
        'notes' => [
        'avoid_false_positive_if_no_substance_text' => true,
        ],
        ]
        ,
        'F12.3' => [
        'needs' => [
        'substance' => '>=2',
        ],
        'notes' => [
        'avoid_false_positive_if_no_substance_text' => true,
        ],
        ]
        ,
        'F12.4' => [
        'needs' => [
        'substance' => '>=2',
        ],
        'notes' => [
        'avoid_false_positive_if_no_substance_text' => true,
        ],
        ]
        ,
        'F12.5' => [
        'needs' => [
        'substance' => '>=2',
        ],
        'notes' => [
        'avoid_false_positive_if_no_substance_text' => true,
        ],
        ]
        ,
        'F12.6' => [
        'needs' => [
        'substance' => '>=2',
        ],
        'notes' => [
        'avoid_false_positive_if_no_substance_text' => true,
        ],
        ]
        ,
        'F12.7' => [
        'needs' => [
        'substance' => '>=2',
        ],
        'notes' => [
        'avoid_false_positive_if_no_substance_text' => true,
        ],
        ]
        ,
        'F13.0' => [
        'needs' => [
        'substance' => '>=2',
        ],
        'notes' => [
        'avoid_false_positive_if_no_substance_text' => true,
        ],
        ]
        ,
        'F13.1' => [
        'needs' => [
        'substance' => '>=2',
        ],
        'notes' => [
        'avoid_false_positive_if_no_substance_text' => true,
        ],
        ]
        ,
        'F13.2' => [
        'needs' => [
        'substance' => '>=2',
        ],
        'notes' => [
        'avoid_false_positive_if_no_substance_text' => true,
        ],
        ]
        ,
        'F13.3' => [
        'needs' => [
        'substance' => '>=2',
        ],
        'notes' => [
        'avoid_false_positive_if_no_substance_text' => true,
        ],
        ]
        ,
        'F13.4' => [
        'needs' => [
        'substance' => '>=2',
        ],
        'notes' => [
        'avoid_false_positive_if_no_substance_text' => true,
        ],
        ]
        ,
        'F13.5' => [
        'needs' => [
        'substance' => '>=2',
        ],
        'notes' => [
        'avoid_false_positive_if_no_substance_text' => true,
        ],
        ]
        ,
        'F13.6' => [
        'needs' => [
        'substance' => '>=2',
        ],
        'notes' => [
        'avoid_false_positive_if_no_substance_text' => true,
        ],
        ]
        ,
        'F13.7' => [
        'needs' => [
        'substance' => '>=2',
        ],
        'notes' => [
        'avoid_false_positive_if_no_substance_text' => true,
        ],
        ]
        ,
        'F13.8' => [
        'needs' => [
        'substance' => '>=2',
        ],
        'notes' => [
        'avoid_false_positive_if_no_substance_text' => true,
        ],
        ]
        ,
        'F13.9' => [
        'needs' => [
        'substance' => '>=2',
        ],
        'notes' => [
        'avoid_false_positive_if_no_substance_text' => true,
        ],
        ]
        ,
        'F14.0' => [
        'needs' => [
        'substance' => '>=2',
        ],
        'notes' => [
        'avoid_false_positive_if_no_substance_text' => true,
        ],
        ]
        ,
        'F14.1' => [
        'needs' => [
        'substance' => '>=2',
        ],
        'notes' => [
        'avoid_false_positive_if_no_substance_text' => true,
        ],
        ]
        ,
        'F14.2' => [
        'needs' => [
        'substance' => '>=2',
        ],
        'notes' => [
        'avoid_false_positive_if_no_substance_text' => true,
        ],
        ]
        ,
        'F14.3' => [
        'needs' => [
        'substance' => '>=2',
        ],
        'notes' => [
        'avoid_false_positive_if_no_substance_text' => true,
        ],
        ]
        ,
        'F14.4' => [
        'needs' => [
        'substance' => '>=2',
        ],
        'notes' => [
        'avoid_false_positive_if_no_substance_text' => true,
        ],
        ]
        ,
        'F14.5' => [
        'needs' => [
        'substance' => '>=2',
        ],
        'notes' => [
        'avoid_false_positive_if_no_substance_text' => true,
        ],
        ]
        ,
        'F14.6' => [
        'needs' => [
        'substance' => '>=2',
        ],
        'notes' => [
        'avoid_false_positive_if_no_substance_text' => true,
        ],
        ]
        ,
        'F14.7' => [
        'needs' => [
        'substance' => '>=2',
        ],
        'notes' => [
        'avoid_false_positive_if_no_substance_text' => true,
        ],
        ]
        ,
        'F15.0' => [
        'needs' => [
        'substance' => '>=2',
        ],
        'notes' => [
        'avoid_false_positive_if_no_substance_text' => true,
        ],
        ]
        ,
        'F15.1' => [
        'needs' => [
        'substance' => '>=2',
        ],
        'notes' => [
        'avoid_false_positive_if_no_substance_text' => true,
        ],
        ]
        ,
        'F15.2' => [
        'needs' => [
        'substance' => '>=2',
        ],
        'notes' => [
        'avoid_false_positive_if_no_substance_text' => true,
        ],
        ]
        ,
        'F15.3' => [
        'needs' => [
        'substance' => '>=2',
        ],
        'notes' => [
        'avoid_false_positive_if_no_substance_text' => true,
        ],
        ]
        ,
        'F15.4' => [
        'needs' => [
        'substance' => '>=2',
        ],
        'notes' => [
        'avoid_false_positive_if_no_substance_text' => true,
        ],
        ]
        ,
        'F15.5' => [
        'needs' => [
        'substance' => '>=2',
        ],
        'notes' => [
        'avoid_false_positive_if_no_substance_text' => true,
        ],
        ]
        ,
        'F15.6' => [
        'needs' => [
        'substance' => '>=2',
        ],
        'notes' => [
        'avoid_false_positive_if_no_substance_text' => true,
        ],
        ]
        ,
        'F15.7' => [
        'needs' => [
        'substance' => '>=2',
        ],
        'notes' => [
        'avoid_false_positive_if_no_substance_text' => true,
        ],
        ]
        ,
        'F15.8' => [
        'needs' => [
        'substance' => '>=2',
        ],
        'notes' => [
        'avoid_false_positive_if_no_substance_text' => true,
        ],
        ]
        ,
        'F15.9' => [
        'needs' => [
        'substance' => '>=2',
        ],
        'notes' => [
        'avoid_false_positive_if_no_substance_text' => true,
        ],
        ]
        ,
        'F16.0' => [
        'needs' => [
        'substance' => '>=2',
        ],
        'notes' => [
        'avoid_false_positive_if_no_substance_text' => true,
        ],
        ]
        ,
        'F16.1' => [
        'needs' => [
        'substance' => '>=2',
        ],
        'notes' => [
        'avoid_false_positive_if_no_substance_text' => true,
        ],
        ]
        ,
        'F16.2' => [
        'needs' => [
        'substance' => '>=2',
        ],
        'notes' => [
        'avoid_false_positive_if_no_substance_text' => true,
        ],
        ]
        ,
        'F16.3' => [
        'needs' => [
        'substance' => '>=2',
        ],
        'notes' => [
        'avoid_false_positive_if_no_substance_text' => true,
        ],
        ]
        ,
        'F16.4' => [
        'needs' => [
        'substance' => '>=2',
        ],
        'notes' => [
        'avoid_false_positive_if_no_substance_text' => true,
        ],
        ]
        ,
        'F16.5' => [
        'needs' => [
        'substance' => '>=2',
        ],
        'notes' => [
        'avoid_false_positive_if_no_substance_text' => true,
        ],
        ]
        ,
        'F16.6' => [
        'needs' => [
        'substance' => '>=2',
        ],
        'notes' => [
        'avoid_false_positive_if_no_substance_text' => true,
        ],
        ]
        ,
        'F16.7' => [
        'needs' => [
        'substance' => '>=2',
        ],
        'notes' => [
        'avoid_false_positive_if_no_substance_text' => true,
        ],
        ]
        ,
        'F16.8' => [
        'needs' => [
        'substance' => '>=2',
        ],
        'notes' => [
        'avoid_false_positive_if_no_substance_text' => true,
        ],
        ]
        ,
        'F16.9' => [
        'needs' => [
        'substance' => '>=2',
        ],
        'notes' => [
        'avoid_false_positive_if_no_substance_text' => true,
        ],
        ]
        ,
        'F17.0' => [
        'needs' => [
        'substance' => '>=2',
        ],
        'notes' => [
        'avoid_false_positive_if_no_substance_text' => true,
        ],
        ]
        ,
        'F17.1' => [
        'needs' => [
        'substance' => '>=2',
        ],
        'notes' => [
        'avoid_false_positive_if_no_substance_text' => true,
        ],
        ]
        ,
        'F17.2' => [
        'needs' => [
        'substance' => '>=2',
        ],
        'notes' => [
        'avoid_false_positive_if_no_substance_text' => true,
        ],
        ]
        ,
        'F17.3' => [
        'needs' => [
        'substance' => '>=2',
        ],
        'notes' => [
        'avoid_false_positive_if_no_substance_text' => true,
        ],
        ]
        ,
        'F17.4' => [
        'needs' => [
        'substance' => '>=2',
        ],
        'notes' => [
        'avoid_false_positive_if_no_substance_text' => true,
        ],
        ]
        ,
        'F17.5' => [
        'needs' => [
        'substance' => '>=2',
        ],
        'notes' => [
        'avoid_false_positive_if_no_substance_text' => true,
        ],
        ]
        ,
        'F17.6' => [
        'needs' => [
        'substance' => '>=2',
        ],
        'notes' => [
        'avoid_false_positive_if_no_substance_text' => true,
        ],
        ]
        ,
        'F17.7' => [
        'needs' => [
        'substance' => '>=2',
        ],
        'notes' => [
        'avoid_false_positive_if_no_substance_text' => true,
        ],
        ]
        ,
        'F17.8' => [
        'needs' => [
        'substance' => '>=2',
        ],
        'notes' => [
        'avoid_false_positive_if_no_substance_text' => true,
        ],
        ]
        ,
        'F17.9' => [
        'needs' => [
        'substance' => '>=2',
        ],
        'notes' => [
        'avoid_false_positive_if_no_substance_text' => true,
        ],
        ]
        ,
        'F18.0' => [
        'needs' => [
        'substance' => '>=2',
        ],
        'notes' => [
        'avoid_false_positive_if_no_substance_text' => true,
        ],
        ]
        ,
        'F18.1' => [
        'needs' => [
        'substance' => '>=2',
        ],
        'notes' => [
        'avoid_false_positive_if_no_substance_text' => true,
        ],
        ]
        ,
        'F18.2' => [
        'needs' => [
        'substance' => '>=2',
        ],
        'notes' => [
        'avoid_false_positive_if_no_substance_text' => true,
        ],
        ]
        ,
        'F18.3' => [
        'needs' => [
        'substance' => '>=2',
        ],
        'notes' => [
        'avoid_false_positive_if_no_substance_text' => true,
        ],
        ]
        ,
        'F18.4' => [
        'needs' => [
        'substance' => '>=2',
        ],
        'notes' => [
        'avoid_false_positive_if_no_substance_text' => true,
        ],
        ]
        ,
        'F18.5' => [
        'needs' => [
        'substance' => '>=2',
        ],
        'notes' => [
        'avoid_false_positive_if_no_substance_text' => true,
        ],
        ]
        ,
        'F18.6' => [
        'needs' => [
        'substance' => '>=2',
        ],
        'notes' => [
        'avoid_false_positive_if_no_substance_text' => true,
        ],
        ]
        ,
        'F18.7' => [
        'needs' => [
        'substance' => '>=2',
        ],
        'notes' => [
        'avoid_false_positive_if_no_substance_text' => true,
        ],
        ]
        ,
        'F19.0' => [
        'needs' => [
        'substance' => '>=2',
        ],
        'notes' => [
        'avoid_false_positive_if_no_substance_text' => true,
        ],
        ]
        ,
        'F19.1' => [
        'needs' => [
        'substance' => '>=2',
        ],
        'notes' => [
        'avoid_false_positive_if_no_substance_text' => true,
        ],
        ]
        ,
        'F19.2' => [
        'needs' => [
        'substance' => '>=2',
        ],
        'notes' => [
        'avoid_false_positive_if_no_substance_text' => true,
        ],
        ]
        ,
        'F19.3' => [
        'needs' => [
        'substance' => '>=2',
        ],
        'notes' => [
        'avoid_false_positive_if_no_substance_text' => true,
        ],
        ]
        ,
        'F19.4' => [
        'needs' => [
        'substance' => '>=2',
        ],
        'notes' => [
        'avoid_false_positive_if_no_substance_text' => true,
        ],
        ]
        ,
        'F19.5' => [
        'needs' => [
        'substance' => '>=2',
        ],
        'notes' => [
        'avoid_false_positive_if_no_substance_text' => true,
        ],
        ]
        ,
        'F19.6' => [
        'needs' => [
        'substance' => '>=2',
        ],
        'notes' => [
        'avoid_false_positive_if_no_substance_text' => true,
        ],
        ]
        ,
        'F19.7' => [
        'needs' => [
        'substance' => '>=2',
        ],
        'notes' => [
        'avoid_false_positive_if_no_substance_text' => true,
        ],
        ]
        ,
        'F19.8' => [
        'needs' => [
        'substance' => '>=2',
        ],
        'notes' => [
        'avoid_false_positive_if_no_substance_text' => true,
        ],
        ]
        ,
        'F19.9' => [
        'needs' => [
        'substance' => '>=2',
        ],
        'notes' => [
        'avoid_false_positive_if_no_substance_text' => true,
        ],
        ]
        ,
        'F20.0' => [
        'needs' => [
        'has_psychotic' => true,
        'duration' => [
        'long' => true,
        ],
        'feature_counts' => [
        [
        'paranoid' => '>=2',
        ],
        ],
        ],
        'forbid' => [
        'acute_onset' => true,
        'duration_short' => true,
        'etiology_substance' => true,
        ],
        ]
        ,
        'F20.1' => [
        'needs' => [
        'has_psychotic' => true,
        'duration' => [
        'long' => true,
        ],
        'feature_counts' => [
        [
        'disorganization_plus_speech_thought' => '>=3',
        ],
        [
        'negative' => '>=1',
        ],
        ],
        ],
        'forbid' => [
        'acute_onset' => true,
        'duration_short' => true,
        'etiology_substance' => true,
        ],
        ]
        ,
        'F20.2' => [
        'needs' => [
        'has_psychotic' => true,
        'duration' => [
        'long' => true,
        ],
        'feature_counts' => [
        [
        'catatonia' => '>=2',
        ],
        ],
        ],
        'forbid' => [
        'acute_onset' => true,
        'duration_short' => true,
        'etiology_substance' => true,
        ],
        ]
        ,
        'F20.3' => [
        'needs' => [
        'has_psychotic' => true,
        'duration' => [
        'long' => true,
        ],
        ],
        'forbid' => [
        'acute_onset' => true,
        'duration_short' => true,
        'etiology_substance' => true,
        ],
        ]
        ,
        'F20.4' => [
        'needs' => [
        'has_psychotic' => true,
        'duration' => [
        'long' => true,
        ],
        ],
        'forbid' => [
        'acute_onset' => true,
        'duration_short' => true,
        'etiology_substance' => true,
        ],
        ]
        ,
        'F20.5' => [
        'needs' => [
        'has_psychotic' => true,
        'duration' => [
        'long' => true,
        ],
        'feature_counts' => [
        [
        'residual' => '>=3',
        ],
        [
        'psychotic' => '==0',
        ],
        ],
        ],
        'forbid' => [
        'acute_onset' => true,
        'duration_short' => true,
        'etiology_substance' => true,
        ],
        ]
        ,
        'F20.6' => [
        'needs' => [
        'has_psychotic' => true,
        'duration' => [
        'long' => true,
        ],
        'feature_counts' => [
        [
        'simple' => '>=3',
        ],
        [
        'psychotic' => '==0',
        ],
        ],
        ],
        'forbid' => [
        'acute_onset' => true,
        'duration_short' => true,
        'etiology_substance' => true,
        ],
        ]
        ,
        'F20.8' => [
        'needs' => [
        'has_psychotic' => true,
        'duration' => [
        'long' => true,
        ],
        ],
        'forbid' => [
        'acute_onset' => true,
        'duration_short' => true,
        'etiology_substance' => true,
        ],
        ]
        ,
        'F20.9' => [
        'needs' => [
        'has_psychotic' => true,
        'duration' => [
        'long' => true,
        ],
        ],
        'forbid' => [
        'acute_onset' => true,
        'duration_short' => true,
        'etiology_substance' => true,
        ],
        ]
        ,
        'F21' => [
        "needs_all"   => ["schizotypal_signature"],
        "needs_any"   => ["insidious_onset","duration_long"],
        "forbid_any"  => ["acute_onset","has_psychosis","etiology_substance"],
        "boosts"      => [
        ["when" => ["any" => ["insidious_onset","duration_long"]], "delta" => 0.18],
        ["when" => "schizotypal_signature", "delta" => 0.10],
        ["when" => ["all" => ["schizotypal_signature","insidious_onset","duration_long"]], "delta" => 0.06],
        ],
        "penalties"   => [
        ["when" => "cooccurrence", "delta" => 0.08],
        ["when" => "bizarre_delusion", "delta" => 0.10],
        ],
        "caps"        => [
        ["when" => "etiology_substance", "max" => 0.30],
        ["when" => "has_psychosis", "max" => 0.40],
        ],
        ]
        ,
        'F22.0' => [
        'needs' => [
        'delusional_signature' => '>=2',
        'duration' => [
        'long' => true,
        ],
        ],
        'caps' => [
        'hallucinations' => 'absent_or_mild',
        ],
        'forbid' => [
        'acute_onset' => true,
        'polymorph' => true,
        ],
        ]
        ,
        'F23.0' => [
        'needs' => [
        'acute_onset' => true,
        'polymorph' => true,
        ],
        'forbid' => [
        'duration_long' => true,
        ],
        'caps' => [
        'schizophrenia_markers' => 'absent',
        ],
        ]
        ,
        'F23.1' => [
        'needs' => [
        'acute_onset' => true,
        'polymorph' => true,
        'has_psychotic' => true,
        ],
        'forbid' => [
        'duration_long' => true,
        ],
        ]
        ,
        'F23.2' => [
        'needs' => [
        'acute_onset' => true,
        'has_psychotic' => true,
        'duration' => [
        'short' => true,
        ],
        ],
        'forbid' => [
        'duration_long' => true,
        ],
        ]
        ,
        'F23.3' => [
        'needs' => [
        'acute_onset' => true,
        'paranoid' => '>=1',
        ],
        'forbid' => [
        'duration_long' => true,
        ],
        ]
        ,
        'F23.8' => [
        'needs' => [
        'acute_onset' => true,
        ],
        'forbid' => [
        'duration_long' => true,
        ],
        ]
        ,
        'F23.9' => [
        'needs' => [
        'acute_onset' => true,
        ],
        'forbid' => [
        'duration_long' => true,
        ],
        ]
        ,
        'F24' => [
        'needs' => [
        'induced_signature' => '>=1',
        ],
        'forbid' => [
        'duration_long' => true,
        ],
        ]
        ,
        'F25.0' => [
        'needs' => [
        'has_psychotic' => true,
        'affective_any' => true,
        'affective_concurrent_min_days' => 14,
        'affective_pole' => 'manic',
        ],
        'forbid' => [
        'etiology_substance' => true,
        ],
        ]
        ,
        'F25.1' => [
        'needs' => [
        'has_psychotic' => true,
        'affective_any' => true,
        'affective_concurrent_min_days' => 14,
        ],
        'forbid' => [
        'etiology_substance' => true,
        ],
        ]
        ,
        'F25.2' => [
        'needs' => [
        'has_psychotic' => true,
        'affective_any' => true,
        'affective_concurrent_min_days' => 14,
        'affective_pole' => 'depressive',
        ],
        'forbid' => [
        'etiology_substance' => true,
        ],
        ]
        ,
        'F25.9' => [
        'needs' => [
        'has_psychotic' => true,
        'affective_any' => true,
        'affective_concurrent_min_days' => 14,
        ],
        'forbid' => [
        'etiology_substance' => true,
        ],
        ]
        ,
        'F28' => [
        ]
        ,
        'F29' => [
        ]
        ,
        'F30.0' => [
        ]
        ,
        'F30.1' => [
        ]
        ,
        'F30.2' => [
        'needs' => [
        'has_psychotic' => true,
        'depr_hits' => '>=3',
        ],
        'forbid' => [
        'manic_dominant' => true,
        ],
        'needs_all'  => ['mood_manic_major', 'has_psychosis'],
        ]
        ,
        'F30.9' => [
        ]
        ,
        'F31.0' => [
        ]
        ,
        'F31.1' => [
        'needs_all'  => ['mood_both_poles', 'mood_manic_major'],
        'forbid_any' => ['has_psychosis'],
        ]
        ,
        'F31.2' => [
        'needs' => [
        'has_psychotic' => true,
        'manic_hits' => '>=3',
        ],
        'forbid' => [
        'depr_dominant' => true,
        ],
        'needs_all'  => ['mood_both_poles', 'mood_manic_major', 'has_psychosis'],
        ]
        ,
        'F31.3' => [
        ]
        ,
        'F31.4' => [
        'needs_all'  => ['mood_both_poles', 'mood_depressive_major'],
        'forbid_any' => ['has_psychosis'],
        ]
        ,
        'F31.5' => [
        'needs' => [
        'has_psychotic' => true,
        'depr_hits' => '>=3',
        ],
        'forbid' => [
        'manic_dominant' => true,
        ],
        'needs_all'  => ['mood_both_poles', 'mood_depressive_major', 'has_psychosis']
        ]
        ,
        'F31.6' => [
        ]
        ,
        'F31.9' => [
        ]
        ,
        'F32.0' => [
        ]
        ,
        'F32.1' => [
        'needs_all'  => ['mood_depressive_major'],
        'forbid_any' => ['has_psychosis', 'mood_manic_major', 'mood_both_poles'],
        ]
        ,
        'F32.2' => [
        ]
        ,
        'F32.3' => [
        'needs' => [
        'has_psychotic' => true,
        'depr_hits' => '>=3',
        ],
        'forbid' => [
        'manic_hits' => '>=2',
        ],
        'needs_all'  => ['mood_depressive_major', 'has_psychosis'],
        'forbid_any' => ['mood_manic_major', 'mood_both_poles'],
        ]
        ,
        'F33.0' => [
        ]
        ,
        'F33.1' => [
        'needs_all'  => ['mood_depressive_major'],
        'forbid_any' => ['has_psychosis', 'mood_manic_major', 'mood_both_poles'],
        ]
        ,
        'F33.2' => [
        ]
        ,
        'F33.3' => [
        'needs_all'  => ['mood_depressive_major', 'has_psychosis'],
        'forbid_any' => ['mood_manic_major', 'mood_both_poles'],
        ]
        ,
        'F33.4' => [
        ]
        ,
        'F34.0' => [
        ]
        ,
        'F34.1' => [
        ]
        ,
        'F34.8' => [
        ]
        ,
        'F34.9' => [
        ]
        ,
        'F38.0' => [
        ]
        ,
        'F38.1' => [
        ]
        ,
        'F38.8' => [
        ]
        ,
        'F38.9' => [
        ]
        ,
        'F39' => [
        ]
        ,
        'F40.0' => [
        'needs' => [
        'anxiety' => '>=3',
        ],
        'forbid' => [
        'has_psychotic' => true,
        ],
        ]
        ,
        'F40.1' => [
        'needs' => [
        'anxiety' => '>=3',
        ],
        'forbid' => [
        'has_psychotic' => true,
        ],
        ]
        ,
        'F40.2' => [
        'needs' => [
        'anxiety' => '>=3',
        ],
        'forbid' => [
        'has_psychotic' => true,
        ],
        ]
        ,
        'F41.0' => [
        'needs' => [
        'anxiety' => '>=3',
        ],
        'forbid' => [
        'has_psychotic' => true,
        ],
        ]
        ,
        'F41.1' => [
        'needs' => [
        'anxiety' => '>=3',
        ],
        'forbid' => [
        'has_psychotic' => true,
        ],
        ]
        ,
        'F41.2' => [
        'needs' => [
        'anxiety' => '>=3',
        ],
        'forbid' => [
        'has_psychotic' => true,
        ],
        ]
        ,
        'F41.8' => [
        'needs' => [
        'anxiety' => '>=3',
        ],
        'forbid' => [
        'has_psychotic' => true,
        ],
        ]
        ,
        'F41.9' => [
        'needs' => [
        'anxiety' => '>=3',
        ],
        'forbid' => [
        'has_psychotic' => true,
        ],
        ]
        ,
        'F42.0' => [
        'needs' => [
        'ocd' => '>=2',
        ],
        'forbid' => [
        'has_psychotic' => true,
        ],
        ]
        ,
        'F42.1' => [
        'needs' => [
        'ocd' => '>=2',
        ],
        'forbid' => [
        'has_psychotic' => true,
        ],
        ]
        ,
        'F42.2' => [
        'needs' => [
        'ocd' => '>=2',
        ],
        'forbid' => [
        'has_psychotic' => true,
        ],
        ]
        ,
        'F42.8' => [
        'needs' => [
        'ocd' => '>=2',
        ],
        'forbid' => [
        'has_psychotic' => true,
        ],
        ]
        ,
        'F42.9' => [
        'needs' => [
        'ocd' => '>=2',
        ],
        'forbid' => [
        'has_psychotic' => true,
        ],
        ]
        ,
        'F43.0' => [
        ]
        ,
        'F43.1' => [
        ]
        ,
        'F43.2' => [
        ]
        ,
        'F43.8' => [
        ]
        ,
        'F43.9' => [
        ]
        ,
        'F44.0' => [
        'needs' => [
        'dissociation' => '>=2',
        ],
        'forbid' => [
        'etiology_substance' => true,
        ],
        ]
        ,
        'F44.1' => [
        'needs' => [
        'dissociation' => '>=2',
        ],
        'forbid' => [
        'etiology_substance' => true,
        ],
        ]
        ,
        'F44.2' => [
        'needs' => [
        'dissociation' => '>=2',
        ],
        'forbid' => [
        'etiology_substance' => true,
        ],
        ]
        ,
        'F44.3' => [
        'needs' => [
        'dissociation' => '>=2',
        ],
        'forbid' => [
        'etiology_substance' => true,
        ],
        ]
        ,
        'F45.0' => [
        'needs' => [
        'somatoform' => '>=2',
        ],
        'forbid' => [
        'etiology_substance' => true,
        ],
        ]
        ,
        'F45.1' => [
        'needs' => [
        'somatoform' => '>=2',
        ],
        'forbid' => [
        'etiology_substance' => true,
        ],
        ]
        ,
        'F45.2' => [
        'needs' => [
        'somatoform' => '>=2',
        ],
        'forbid' => [
        'etiology_substance' => true,
        ],
        ]
        ,
        'F45.3' => [
        'needs' => [
        'somatoform' => '>=2',
        ],
        'forbid' => [
        'etiology_substance' => true,
        ],
        ]
        ,
        'F45.4' => [
        'needs' => [
        'somatoform' => '>=2',
        ],
        'forbid' => [
        'etiology_substance' => true,
        ],
        ]
        ,
        'F45.8' => [
        'needs' => [
        'somatoform' => '>=2',
        ],
        'forbid' => [
        'etiology_substance' => true,
        ],
        ]
        ,
        'F45.9' => [
        'needs' => [
        'somatoform' => '>=2',
        ],
        'forbid' => [
        'etiology_substance' => true,
        ],
        ]
        ,
        'F48.0' => [
        ]
        ,
        'F48.1' => [
        ]
        ,
        'F48.8' => [
        ]
        ,
        'F48.9' => [
        ]
        ,
        'F50.0' => [
        'needs' => [
        'eating' => '>=2',
        ],
        ]
        ,
        'F50.1' => [
        'needs' => [
        'eating' => '>=2',
        ],
        ]
        ,
        'F50.2' => [
        'needs' => [
        'eating' => '>=2',
        ],
        ]
        ,
        'F50.3' => [
        'needs' => [
        'eating' => '>=2',
        ],
        ]
        ,
        'F50.4' => [
        'needs' => [
        'eating' => '>=2',
        ],
        ]
        ,
        'F50.5' => [
        'needs' => [
        'eating' => '>=2',
        ],
        ]
        ,
        'F50.8' => [
        'needs' => [
        'eating' => '>=2',
        ],
        ]
        ,
        'F50.9' => [
        'needs' => [
        'eating' => '>=2',
        ],
        ]
        ,
        'F51.0' => [
        'needs' => [
        'sleep' => '>=3',
        ],
        ]
        ,
        'F51.1' => [
        'needs' => [
        'sleep' => '>=3',
        ],
        ]
        ,
        'F51.2' => [
        'needs' => [
        'sleep' => '>=3',
        ],
        ]
        ,
        'F51.3' => [
        'needs' => [
        'sleep' => '>=3',
        ],
        ]
        ,
        'F51.4' => [
        'needs' => [
        'sleep' => '>=3',
        ],
        ]
        ,
        'F51.5' => [
        'needs' => [
        'sleep' => '>=3',
        ],
        ]
        ,
        'F51.8' => [
        'needs' => [
        'sleep' => '>=3',
        ],
        ]
        ,
        'F51.9' => [
        'needs' => [
        'sleep' => '>=3',
        ],
        ]
        ,
        'F52.0' => [
        ]
        ,
        'F52.1' => [
        ]
        ,
        'F52.2' => [
        ]
        ,
        'F52.3' => [
        ]
        ,
        'F52.4' => [
        ]
        ,
        'F52.8' => [
        ]
        ,
        'F52.9' => [
        ]
        ,
        'F53.0' => [
        ]
        ,
        'F53.1' => [
        ]
        ,
        'F53.8' => [
        ]
        ,
        'F53.9' => [
        ]
        ,
        'F54' => [
        ]
        ,
        'F55.0' => [
        ]
        ,
        'F55.1' => [
        ]
        ,
        'F55.2' => [
        ]
        ,
        'F55.3' => [
        ]
        ,
        'F55.9' => [
        ]
        ,
        'F59' => [
        ]
        ,
        'F60.0' => []
        ,
        'F60.1' => [
        ]
        ,
        'F60.2' => [
        ]
        ,
        'F60.3' => [
        ]
        ,
        'F60.4' => [
        ]
        ,
        'F60.5' => [
        ]
        ,
        'F60.6' => [
        ]
        ,
        'F60.7' => [
        ]
        ,
        'F60.8' => []
        ,
        'F60.9' => [
        ]
        ,
        'F61' => [
        'needs_all'  => ['chronic_personality'],
        'boosts'     => [
        ['delta' => 0.08, 'when' => 'chronic_personality'],
        ],
        ]
        ,
        'F62.0' => [
        ]
        ,
        'F62.1' => [
        ]
        ,
        'F63.0' => [
        ]
        ,
        'F63.1' => [
        ]
        ,
        'F63.2' => [
        ]
        ,
        'F63.3' => [
        ]
        ,
        'F63.8' => [
        ]
        ,
        'F63.9' => [
        ]
        ,
        'F64.0' => [
        ]
        ,
        'F64.1' => [
        ]
        ,
        'F64.2' => [
        ]
        ,
        'F64.8' => [
        ]
        ,
        'F64.9' => [
        ]
        ,
        'F65.0' => [
        ]
        ,
        'F65.1' => [
        ]
        ,
        'F65.2' => [
        ]
        ,
        'F65.3' => [
        ]
        ,
        'F65.4' => [
        ]
        ,
        'F65.8' => [
        ]
        ,
        'F65.9' => [
        ]
        ,
        'F66.0' => [
        ]
        ,
        'F66.1' => [
        ]
        ,
        'F66.2' => [
        ]
        ,
        'F66.8' => [
        ]
        ,
        'F66.9' => [
        ]
        ,
        'F68.0' => [
        ]
        ,
        'F68.1' => [
        ]
        ,
        'F68.8' => [
        ]
        ,
        'F68.9' => [
        ]
        ,
        'F69' => [
        ]
        ,
        'F70.0' => [
        ]
        ,
        'F70.1' => [
        ]
        ,
        'F70.8' => [
        ]
        ,
        'F70.9' => [
        ]
        ,
        'F71.0' => [
        ]
        ,
        'F71.1' => [
        ]
        ,
        'F71.8' => [
        ]
        ,
        'F71.9' => [
        ]
        ,
        'F72.0' => [
        ]
        ,
        'F72.1' => [
        ]
        ,
        'F72.8' => [
        ]
        ,
        'F72.9' => [
        ]
        ,
        'F73.0' => [
        ]
        ,
        'F73.1' => [
        ]
        ,
        'F73.8' => [
        ]
        ,
        'F73.9' => [
        ]
        ,
        'F78.0' => [
        ]
        ,
        'F78.1' => [
        ]
        ,
        'F78.2' => [
        ]
        ,
        'F78.3' => [
        ]
        ,
        'F78.8' => [
        ]
        ,
        'F78.9' => [
        ]
        ,
        'F79.0' => [
        ]
        ,
        'F79.1' => [
        ]
        ,
        'F79.8' => [
        ]
        ,
        'F79.9' => [
        ]
        ,
        'F80.0' => [
        ]
        ,
        'F80.1' => [
        ]
        ,
        'F80.2' => [
        ]
        ,
        'F80.3' => [
        ]
        ,
        'F81.0' => [
        ]
        ,
        'F81.1' => [
        ]
        ,
        'F81.2' => [
        ]
        ,
        'F82' => [
        ]
        ,
        'F83' => [
        ]
        ,
        'F84.0' => [
        'needs' => [
        'autism' => '>=2',
        ],
        ]
        ,
        'F84.1' => [
        'needs' => [
        'autism' => '>=2',
        ],
        ]
        ,
        'F84.2' => [
        'needs' => [
        'autism' => '>=2',
        ],
        ]
        ,
        'F84.3' => [
        'needs' => [
        'autism' => '>=2',
        ],
        ]
        ,
        'F84.4' => [
        'needs' => [
        'autism' => '>=2',
        ],
        ]
        ,
        'F84.5' => [
        'needs' => [
        'autism' => '>=2',
        ],
        ]
        ,
        'F84.8' => [
        'needs' => [
        'autism' => '>=2',
        ],
        ]
        ,
        'F84.9' => [
        'needs' => [
        'autism' => '>=2',
        ],
        ]
        ,
        'F88' => [
        ]
        ,
        'F89' => [
        ]
        ,
        'F90.0' => [
        'needs' => [
        'adhd' => '>=2',
        ],
        ]
        ,
        'F90.1' => [
        'needs' => [
        'adhd' => '>=2',
        ],
        ]
        ,
        'F91.0' => [
        ]
        ,
        'F91.1' => [
        ]
        ,
        'F91.2' => [
        ]
        ,
        'F91.3' => [
        ]
        ,
        'F92.0' => [
        ]
        ,
        'F92.8' => [
        ]
        ,
        'F92.9' => [
        ]
        ,
        'F93.0' => [
        ]
        ,
        'F93.1' => [
        ]
        ,
        'F93.2' => [
        ]
        ,
        'F93.3' => [
        ]
        ,
        'F93.8' => [
        ]
        ,
        'F93.9' => [
        ]
        ,
        'F94.0' => [
        ]
        ,
        'F94.1' => [
        ]
        ,
        'F94.2' => [
        ]
        ,
        'F94.8' => [
        ]
        ,
        'F94.9' => [
        ]
        ,
        'F95.0' => [
        ]
        ,
        'F95.1' => [
        ]
        ,
        'F95.2' => [
        ]
        ,
        'F95.8' => [
        ]
        ,
        'F95.9' => [
        ]
        ,
        'F98.0' => [
        ]
        ,
        'F98.1' => [
        ]
        ,
        'F98.2' => [
        ]
        ,
        'F98.3' => [
        ]
        ,
        'F98.4' => [
        ]
        ,
        'F98.5' => [
        ]
        ,
        'F98.6' => [
        ]
        ,
        'F98.8' => [
        ]
        ,
        'F98.9' => [
        ]
        ,
        'F99' => [
        ]
        ,
        'G20' => [
        ]
        ,
        'G21.0' => [
        ]
        ,
        'G21.8' => [
        ]
        ,
        'G21.9' => [
        ]
        ,
        'G25.0' => [
        ]
        ,
        'G25.1' => [
        ]
        ,
        'G25.2' => [
        ]
        ,
        'G40.9' => [
        ]
        ,
        'G47.0' => [
        ]
        ,
        'G47.1' => [
        ]
        ,
        'R25.1' => [
        ]
        ,
    ],
];
