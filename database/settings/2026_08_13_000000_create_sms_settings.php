<?php

use Spatie\LaravelSettings\Migrations\SettingsMigration;

return new class extends SettingsMigration
{
    public function up(): void
    {
        $this->migrator->add('sms.token', 'sa3102705417:PFqzIaS3CAw1ptAQ2nWkMczMHmk6ou49VfXd');
        $this->migrator->add('sms.gateway', 'otp');
    }
};
