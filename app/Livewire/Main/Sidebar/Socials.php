<?php

namespace App\Livewire\Main\Sidebar;

use App\Settings\SocialSettings;
use Livewire\Attributes\Computed;
use Livewire\Component;

class Socials extends Component
{
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
        return view('livewire.main.sidebar.socials');
    }
}
