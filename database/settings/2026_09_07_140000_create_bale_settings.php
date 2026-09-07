<?php

use Spatie\LaravelSettings\Migrations\SettingsMigration;

return new class extends SettingsMigration
{
    public function up(): void
    {
        $this->migrator->add('bale.bot_username', 'SetareganCRMBot');
        $this->migrator->addEncrypted('bale.bot_token', '47361680:A9A4VQmDS45lmpeeQXynAdY7w_XV5Vfhxk8');
        $this->migrator->add('bale.chat_id', '4716603133');
    }
};
