<?php

namespace App\Console\Commands;

use App\Application\Order\Command\ActivateDuePreordersUseCase;
use Illuminate\Console\Command;

final class ActivateDuePreordersCommand extends Command
{
    protected $signature = 'orders:activate-due-preorders';

    protected $description = 'Переводит due-предзаказы preorder → confirmed в момент scheduled_at';

    public function handle(ActivateDuePreordersUseCase $useCase): int
    {
        $result = $useCase->execute();
        $this->info(sprintf(
            'Активировано предзаказов: %d%s',
            $result['activated'],
            $result['order_ids'] === [] ? '' : ' ['.implode(', ', $result['order_ids']).']',
        ));

        return self::SUCCESS;
    }
}
