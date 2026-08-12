<?php

namespace App\Livewire\Main\Contact;

use App\Settings\ContactSettings;
use App\Settings\SocialSettings;
use Livewire\Attributes\Computed;
use Livewire\Component;

class Index extends Component
{
    #[Computed]
    public function contact(): ContactSettings
    {
        return app(ContactSettings::class);
    }

    /**
     * @return array<string, string>
     */
    #[Computed]
    public function socials(): array
    {
        return app(SocialSettings::class)->active();
    }

    public function render()
    {
        return view('livewire.main.contact.index');
    }
}
