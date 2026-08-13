<?php

namespace App\Livewire\Panel\Administrator\SettingManagement\Option;

use App\Enums\SocialNetworkEnum;
use App\Livewire\Forms\ContactSettingForm;
use App\Livewire\Forms\GeneralSettingForm;
use App\Livewire\Forms\MaintenanceSettingForm;
use App\Livewire\Forms\SocialSettingForm;
use App\Livewire\Forms\SmsSettingForm;
use App\Settings\ContactSettings;
use App\Settings\GeneralSettings;
use App\Settings\MaintenanceSettings;
use App\Settings\SmsSettings;
use App\Settings\SocialSettings;
use Flux\Flux;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithFileUploads;

class Index extends Component
{
    use WithFileUploads;

    public GeneralSettingForm $generalForm;

    public ContactSettingForm $contactForm;

    public SocialSettingForm $socialForm;

    public MaintenanceSettingForm $maintenanceForm;

    public SmsSettingForm $smsForm;

    public string $tab = 'general';

    public function mount(
        GeneralSettings $general,
        ContactSettings $contact,
        SocialSettings $social,
        MaintenanceSettings $maintenance,
        SmsSettings $sms,
    ): void {
        $this->authorize('administrator_setting_option_index');

        $this->generalForm->fill([
            'title' => $general->title,
            'description' => $general->description,
            'keywords' => $general->keywords,
            'favicon_path' => $general->favicon_path,
            'logo_path' => $general->logo_path,
        ]);

        $this->contactForm->fill([
            'address' => $contact->address,
            'mobile' => $contact->mobile,
            'phone' => $contact->phone,
            'email' => $contact->email,
        ]);

        $this->socialForm->links = array_merge(
            array_fill_keys(SocialNetworkEnum::values(), null),
            $social->links,
        );

        $this->maintenanceForm->fill([
            'message' => $maintenance->message,
            'secret' => $maintenance->secret,
            'retry' => $maintenance->retry,
            'refresh' => $maintenance->refresh,
        ]);

        $this->smsForm->fill([
            'token' => $sms->token,
            'gateway' => $sms->gateway,
        ]);
    }

    /**
     * @return list<SocialNetworkEnum>
     */
    #[Computed]
    public function networks(): array
    {
        return SocialNetworkEnum::cases();
    }

    #[Computed]
    public function isDown(): bool
    {
        return app()->isDownForMaintenance();
    }

    #[Computed]
    public function bypassUrl(): ?string
    {
        if (! $this->isDown) {
            return null;
        }

        $secret = app()->maintenanceMode()->data()['secret'] ?? null;

        return $secret ? rtrim((string) config('app.url'), '/').'/'.$secret : null;
    }

    public function saveGeneral(GeneralSettings $settings): void
    {
        $this->authorize('administrator_setting_option_update');

        $this->generalForm->validate();

        $settings->title = $this->generalForm->title;
        $settings->description = (string) $this->generalForm->description;
        $settings->keywords = (string) $this->generalForm->keywords;

        if ($this->generalForm->favicon) {
            $settings->favicon_path = $this->storeUpload(
                $this->generalForm->favicon,
                'favicon',
                $settings->favicon_path,
            );
        }

        if ($this->generalForm->logo) {
            $settings->logo_path = $this->storeUpload(
                $this->generalForm->logo,
                'logo',
                $settings->logo_path,
            );
        }

        $settings->save();

        $this->generalForm->favicon_path = $settings->favicon_path;
        $this->generalForm->logo_path = $settings->logo_path;
        $this->generalForm->reset(['favicon', 'logo']);

        Flux::toast(__('app.settings_updated'));
    }

    public function removeLogo(GeneralSettings $settings): void
    {
        $this->authorize('administrator_setting_option_update');

        if ($settings->logo_path) {
            Storage::disk('public')->delete($settings->logo_path);
            $settings->logo_path = null;
            $settings->save();
        }

        $this->generalForm->logo_path = null;
        Flux::toast(__('app.file_removed'));
    }

    public function removeFavicon(GeneralSettings $settings): void
    {
        $this->authorize('administrator_setting_option_update');

        if ($settings->favicon_path) {
            Storage::disk('public')->delete($settings->favicon_path);
            $settings->favicon_path = null;
            $settings->save();
        }

        $this->generalForm->favicon_path = null;
        Flux::toast(__('app.file_removed'));
    }

    public function saveContact(ContactSettings $settings): void
    {
        $this->authorize('administrator_setting_option_update');

        $this->contactForm->validate();

        $settings->address = $this->contactForm->address;
        $settings->mobile = $this->contactForm->mobile;
        $settings->phone = $this->contactForm->phone;
        $settings->email = $this->contactForm->email;
        $settings->save();

        Flux::toast(__('app.settings_updated'));
    }

    public function saveSocial(SocialSettings $settings): void
    {
        $this->authorize('administrator_setting_option_update');

        $this->socialForm->validate();

        $settings->links = array_map(
            fn (?string $url) => filled($url) ? $url : null,
            $this->socialForm->links,
        );
        $settings->save();

        Flux::toast(__('app.settings_updated'));
    }

    public function saveMaintenance(MaintenanceSettings $settings): void
    {
        $this->authorize('administrator_setting_option_update');

        $this->maintenanceForm->validate();

        $settings->message = $this->maintenanceForm->message;
        $settings->secret = $this->maintenanceForm->secret;
        $settings->retry = $this->maintenanceForm->retry;
        $settings->refresh = $this->maintenanceForm->refresh;
        $settings->save();

        Flux::toast(__('app.settings_updated'));
    }

    public function saveSms(SmsSettings $settings): void
    {
        $this->authorize('administrator_setting_option_update');

        $this->smsForm->validate();

        $settings->token = $this->smsForm->token;
        $settings->gateway = $this->smsForm->gateway;
        $settings->save();

        Flux::toast(__('app.settings_updated'));
    }

    public function generateSecret(): void
    {
        $this->authorize('administrator_setting_option_update');

        $this->maintenanceForm->secret = Str::random(32);
    }

    public function enableMaintenance(MaintenanceSettings $settings): void
    {
        $this->authorize('administrator_setting_option_update');

        $this->maintenanceForm->validate();

        $settings->message = $this->maintenanceForm->message;
        $settings->secret = $this->maintenanceForm->secret;
        $settings->retry = $this->maintenanceForm->retry;
        $settings->refresh = $this->maintenanceForm->refresh;
        $settings->save();

        Artisan::call('down', array_filter([
            '--render' => 'errors.503',
            '--secret' => $settings->secret,
            '--retry' => $settings->retry,
            '--refresh' => $settings->refresh,
            '--status' => 503,
        ], fn ($value) => $value !== null && $value !== ''));

        unset($this->isDown, $this->bypassUrl);

        Flux::toast(__('app.maintenance_enabled'));
    }

    public function disableMaintenance(): void
    {
        $this->authorize('administrator_setting_option_update');

        Artisan::call('up');

        unset($this->isDown, $this->bypassUrl);

        Flux::toast(__('app.maintenance_disabled'));
    }

    protected function storeUpload($file, string $prefix, ?string $previous): string
    {
        $name = $prefix.'-'.Str::random(8).'.'.$file->getClientOriginalExtension();
        $path = $file->storeAs('settings', $name, 'public');

        if ($previous && $previous !== $path) {
            Storage::disk('public')->delete($previous);
        }

        return $path;
    }

    #[Layout('layouts.panels.administrator')]
    public function render()
    {
        return view('livewire.panel.administrator.setting-management.option.index');
    }
}
