<?php

namespace App\Filament\Resources\Orders\Concerns;

use App\Infrastructure\Order\Model\ORD_Order;
use App\Support\Order\OrderStatusLabels;
use Filament\Schemas\Components\Tabs\Tab;
use Illuminate\Database\Eloquent\Builder;

trait HasOrderStatusTabs
{
    /**
     * @var array<string, int>|null
     */
    protected ?array $orderStatusTabCountsCache = null;

    /**
     * @return array<string, Tab>
     */
    public function getTabs(): array
    {
        $counts = $this->getOrderStatusTabCounts();

        $tabs = [];

        foreach (OrderStatusLabels::statusTabKeys() as $key) {
            $tab = Tab::make(OrderStatusLabels::statusTabLabel($key))
                ->icon(OrderStatusLabels::statusTabIcon($key))
                ->badge($counts[$key] ?? 0);

            if ($key === 'all') {
                $tab->query(fn (Builder $query): Builder => $query);
            } else {
                $tab->query(fn (Builder $query): Builder => $query->where('status', $key));
            }

            $tabs[$key] = $tab;
        }

        return $tabs;
    }

    public function getDefaultActiveTab(): string
    {
        return 'new';
    }

    /**
     * @return array<string, int>
     */
    protected function getOrderStatusTabCounts(): array
    {
        if ($this->orderStatusTabCountsCache !== null) {
            return $this->orderStatusTabCountsCache;
        }

        $byStatus = ORD_Order::query()
            ->selectRaw('status, COUNT(*) as aggregate')
            ->groupBy('status')
            ->pluck('aggregate', 'status')
            ->map(fn ($count): int => (int) $count)
            ->all();

        $counts = ['all' => 0];

        foreach (OrderStatusLabels::statusTabKeys() as $key) {
            if ($key === 'all') {
                continue;
            }

            $counts[$key] = $byStatus[$key] ?? 0;
            $counts['all'] += $counts[$key];
        }

        return $this->orderStatusTabCountsCache = $counts;
    }
}
