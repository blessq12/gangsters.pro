<?php

return [
    'enabled' => filter_var(env('BACKOFFICE_ENABLED', false), FILTER_VALIDATE_BOOLEAN),
    'auth_required' => filter_var(env('BACKOFFICE_AUTH_REQUIRED', false), FILTER_VALIDATE_BOOLEAN),
];
