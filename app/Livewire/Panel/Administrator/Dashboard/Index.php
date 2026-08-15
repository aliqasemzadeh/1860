<?php

namespace App\Livewire\Panel\Administrator\Dashboard;

use App\Models\System\CommandLog;
use App\Models\User;
use Carbon\Carbon;
use Flux\Flux;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\On;
use Livewire\Component;
use Spatie\Backup\BackupDestination\Backup;
use Spatie\Backup\BackupDestination\BackupDestination;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Throwable;

class Index extends Component
{
    public function mount(): void
    {
        $this->authorize('administrator_dashboard_index');
    }

    #[Computed]
    public function stats(): array
    {
        return Cache::remember('panel.administrator.dashboard.stats', 60, function () {
            $users = User::query()
                ->selectRaw('count(*) as total')
                ->selectRaw('sum(case when email_verified_at is not null then 1 else 0 end) as verified')
                ->selectRaw('sum(case when created_at >= ? then 1 else 0 end) as recent', [now()->startOfMonth()])
                ->first();

            return [
                'users_total' => (int) ($users->total ?? 0),
                'users_verified' => (int) ($users->verified ?? 0),
                'users_recent' => (int) ($users->recent ?? 0),
                'roles_total' => Role::query()->count(),
                'permissions_total' => Permission::query()->count(),
            ];
        });
    }

    #[Computed]
    public function backupSummary(): array
    {
        return Cache::remember('panel.administrator.dashboard.backups', 300, function () {
            try {
                $backupName = (string) config('backup.backup.name');
                $disks = config('backup.backup.destination.disks', ['local']);

                $backups = collect($disks)
                    ->flatMap(function (string $diskName) use ($backupName) {
                        try {
                            $destination = BackupDestination::create($diskName, $backupName);

                            if (! $destination->isReachable()) {
                                return [];
                            }

                            return $destination->backups()
                                ->map(fn (Backup $backup) => [
                                    'date' => $backup->date(),
                                    'size' => $backup->sizeInBytes(),
                                ]);
                        } catch (Throwable) {
                            return [];
                        }
                    });

                if ($backups->isEmpty()) {
                    return [
                        'count' => 0,
                        'last_at' => null,
                        'total_size' => 0,
                    ];
                }

                $newest = $backups->sortByDesc(fn (array $item) => $item['date']->timestamp)->first();

                return [
                    'count' => $backups->count(),
                    'last_at' => $newest['date']->toIso8601String(),
                    'total_size' => (int) $backups->sum('size'),
                ];
            } catch (Throwable) {
                return [
                    'count' => 0,
                    'last_at' => null,
                    'total_size' => 0,
                ];
            }
        });
    }

    #[Computed]
    public function latestUsers(): Collection
    {
        return User::query()
            ->with('roles:id,name')
            ->latest()
            ->limit(5)
            ->get();
    }

    #[Computed]
    public function recentCommands(): Collection
    {
        return CommandLog::query()
            ->latest()
            ->limit(5)
            ->get();
    }

    #[Computed]
    public function runningCommands(): int
    {
        return CommandLog::query()
            ->where('status', 'running')
            ->count();
    }

    #[Computed]
    public function systemStatus(): array
    {
        $lastAt = $this->backupSummary['last_at'] ?? null;

        return [
            'environment' => app()->environment(),
            'laravel_version' => app()->version(),
            'php_version' => PHP_VERSION,
            'maintenance' => app()->isDownForMaintenance(),
            'last_backup_at' => $lastAt ? Carbon::parse($lastAt) : null,
            'backup_size' => (int) ($this->backupSummary['total_size'] ?? 0),
            'backups_count' => (int) ($this->backupSummary['count'] ?? 0),
        ];
    }

    public function refresh(): void
    {
        $this->authorize('administrator_dashboard_index');

        Cache::forget('panel.administrator.dashboard.stats');
        Cache::forget('panel.administrator.dashboard.backups');

        unset(
            $this->stats,
            $this->backupSummary,
            $this->latestUsers,
            $this->recentCommands,
            $this->runningCommands,
            $this->systemStatus,
        );

        Flux::toast(__('general.success'));
    }

    #[Layout('layouts.panels.administrator')]
    #[On('panel.administrator.dashboard.index.render')]
    public function render()
    {
        $this->authorize('administrator_dashboard_index');

        return view('livewire.panel.administrator.dashboard.index');
    }
}
