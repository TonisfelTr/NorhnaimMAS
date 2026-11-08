<?php

return [
    // Explicit initial value in the booking form, not a duration assigned to old records.
    // A new/rescheduled appointment must submit its duration: 5..480, in steps of 5 minutes.
    'default_duration_minutes' => 30,

    // Suggested starts. A manually chosen start need not be divisible by this step.
    'slot_step_minutes' => 15,
];
