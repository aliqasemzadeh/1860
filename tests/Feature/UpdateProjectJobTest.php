<?php

use App\Jobs\System\UpdateProjectJob;
use App\Models\User;
use Illuminate\Support\Facades\Queue;
use Spatie\Permission\Models\Permission;

test('update project job defaults runNpmBuild to true', function () {
    $job = new UpdateProjectJob;

    expect($job->runNpmBuild)->toBeTrue();
});

test('quick update dispatches update project job with runNpmBuild enabled', function () {
    Queue::fake();

    Permission::findOrCreate('administrator_setting_function_index');

    $user = User::create([
        'mobile' => '09121234567',
        'first_name' => 'Admin',
        'last_name' => 'User',
    ]);
    $user->givePermissionTo('administrator_setting_function_index');

    \Livewire\Livewire::actingAs($user)
        ->test(\App\Livewire\Panel\Administrator\SettingManagement\Function\Index::class)
        ->call('runQuickUpdate');

    Queue::assertPushed(UpdateProjectJob::class, function (UpdateProjectJob $job) {
        return $job->runNpmBuild === true && $job->runComposer === false;
    });
});
