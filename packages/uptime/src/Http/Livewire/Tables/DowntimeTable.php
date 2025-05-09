<?php

namespace Vigilant\Uptime\Http\Livewire\Tables;

use Illuminate\Database\Eloquent\Builder;
use Livewire\Attributes\Locked;
use RamonRietdijk\LivewireTables\Columns\Column;
use RamonRietdijk\LivewireTables\Livewire\LivewireTable;
use Vigilant\Frontend\Integrations\Table\DateColumn;
use Vigilant\Uptime\Models\Downtime;
use Vigilant\Uptime\Models\Monitor;

class DowntimeTable extends LivewireTable
{
    protected string $model = Downtime::class;

    #[Locked]
    public int $monitorId = 0;

    public string $sortColumn = 'start';

    public string $sortDirection = 'desc';

    public function mount(int $monitorId): void
    {
        $this->monitorId = $monitorId;
        Monitor::query()->findOrFail($monitorId);
    }

    protected function columns(): array
    {
        return [

            DateColumn::make(__('Start'), 'start')
                ->sortable(),

            DateColumn::make(__('End'), 'end')
                ->sortable(),

            Column::make(__('Duration'), function (Downtime $downtime) {
                if ($downtime->end === null) {
                    return __('Ongoing');
                }

                return teamTimezone($downtime->start)->longAbsoluteDiffForHumans(teamTimezone($downtime->end));
            }),
        ];
    }

    protected function query(): Builder
    {
        return parent::query()
            ->where('monitor_id', '=', $this->monitorId);
    }
}
