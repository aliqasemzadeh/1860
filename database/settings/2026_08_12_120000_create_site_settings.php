<?php

use App\Enums\SocialNetworkEnum;
use Spatie\LaravelSettings\Migrations\SettingsMigration;

return new class extends SettingsMigration
{
    public function up(): void
    {
        $this->migrator->add('general.title', config('app.name', 'Laravel'));
        $this->migrator->add('general.description', '');
        $this->migrator->add('general.keywords', '');
        $this->migrator->add('general.favicon_path', null);
        $this->migrator->add('general.logo_path', null);

        $this->migrator->add('contact.address', null);
        $this->migrator->add('contact.mobile', '09177886099');
        $this->migrator->add('contact.phone', '07132317274');
        $this->migrator->add('contact.email', 'aliqasemzadeh7@gmail.com');

        $this->migrator->add('social.links', array_fill_keys(SocialNetworkEnum::values(), null));

        $this->migrator->add('maintenance.message', 'سایت در حال به‌روزرسانی است. لطفاً کمی بعد مراجعه کنید.');
        $this->migrator->addEncrypted('maintenance.secret', null);
        $this->migrator->add('maintenance.retry', 60);
        $this->migrator->add('maintenance.refresh', 30);
    }
};
