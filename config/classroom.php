<?php

return [
    /*
     * Dead Air Detection Settings
     */
    'dead_air_threshold_seconds' => env('DEAD_AIR_THRESHOLD_SECONDS', 30),
    'dead_air_alert_seconds'     => env('DEAD_AIR_ALERT_SECONDS', 60),

    /*
     * Speech Analysis Settings
     */
    'speech_language' => env('SPEECH_LANGUAGE', 'ar'),

    /*
     * Alert Settings
     */
    'alert_email'         => env('ALERT_EMAIL', ''),
    'alert_slack_webhook' => env('ALERT_SLACK_WEBHOOK', ''),

    /*
     * Report Settings
     */
    'reports_disk' => env('REPORTS_DISK', 'local'),
];
