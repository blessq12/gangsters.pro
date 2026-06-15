<?php

namespace App\Domain\OrderAccountingExport\Enum;

enum ExportAttemptStatus: string
{
    case Success = 'success';
    case Failed = 'failed';
}
