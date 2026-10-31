<?php

return [
    'enabled' => filter_var(env('BACKOFFICE_ENABLED', false), FILTER_VALIDATE_BOOLEAN),
];
