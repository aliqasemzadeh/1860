<?php

namespace App\Livewire\Panel\Administrator\SettingManagement\Backup;

use Flux\Flux;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Pagination\LengthAwarePaginator as Paginator;
use Illuminate\Support\Collection;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\On;
use Livewire\Component;
use Livewire\WithPagination;
use Spatie\Backup\BackupDestination\Backup;
use Spatie\Backup\BackupDestination\BackupDestination;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Throwable;

class Index extends Component
{
    use WithPagination;

    public function mount(): void
    {
        $this->authorize('administrator_setting_backup_index');
    }

    #[Computed]
    public function backups(): LengthAwarePaginator
    {
        $items = $this->collectBackups();
        $page = $this->getPage();
        $perPage = 10;

        return new Paginator(
            $items->forPage($page, $perPage)->values(),
            $items->count(),
            $perPage,
            $page,
            [
                'path' => request()->url(),
                'pageName' => 'page',
            ]
        );
    }

    public function download(string $disk, string $path): StreamedResponse
    {
        $this->authorize('administrator_setting_backup_download');

        $backup = $this->findBackup($disk, $path);

        abort_unless($backup !== null && $backup->exists(), 404);

        return response()->streamDownload(function () use ($backup): void {
            $stream = $backup->stream();
            fpassthru($stream);

            if (is_resource($stream)) {
                fclose($stream);
            }
        }, basename($path));
    }

    public function delete(string $disk, string $path): void
    {
        $this->authorize('administrator_setting_backup_delete');

        $backup = $this->findBackup($disk, $path);

        if ($backup !== null && $backup->exists()) {
            $backup->delete();
        }

        unset($this->backups);
        Flux::toast(__('app.backup_deleted'));
    }

    protected function collectBackups(): Collection
    {
        $backupName = (string) config('backup.backup.name');
        $disks = config('backup.backup.destination.disks', ['local']);

        return collect($disks)
            ->flatMap(function (string $diskName) use ($backupName) {
                try {
                    $destination = BackupDestination::create($diskName, $backupName);

                    if (! $destination->isReachable()) {
                        return [];
                    }

                    return $destination->backups()
                        ->map(fn (Backup $backup) => [
                            'disk' => $diskName,
                            'path' => $backup->path(),
                            'filename' => basename($backup->path()),
                            'date' => $backup->date(),
                            'size' => $backup->sizeInBytes(),
                        ]);
                } catch (Throwable) {
                    return [];
                }
            })
            ->sortByDesc(fn (array $item) => $item['date']->timestamp)
            ->values();
    }

    protected function findBackup(string $disk, string $path): ?Backup
    {
        try {
            $destination = BackupDestination::create($disk, (string) config('backup.backup.name'));

            if (! $destination->isReachable()) {
                return null;
            }

            return $destination->backups()->first(fn (Backup $backup) => $backup->path() === $path);
        } catch (Throwable) {
            return null;
        }
    }

    #[Layout('layouts.panels.administrator')]
    #[On('panel.administrator.setting-management.backup.index.render')]
    public function render()
    {
        return view('livewire.panel.administrator.setting-management.backup.index');
    }
}
