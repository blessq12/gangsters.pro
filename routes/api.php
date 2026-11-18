<?php

/*
|--------------------------------------------------------------------------
| API routes entry: two independent HTTP contours to the core.
| Public and backoffice share no routes; each lives in its own file.
|--------------------------------------------------------------------------
*/

require __DIR__.'/api/public.php';
require __DIR__.'/api/backoffice.php';
