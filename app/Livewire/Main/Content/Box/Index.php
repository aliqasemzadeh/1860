<?php

namespace App\Livewire\Main\Content\Box;

use App\Models\Content\Box;
use Jenssegers\Agent\Agent;
use Livewire\Attributes\Computed;
use Livewire\Component;

class Index extends Component
{
    #[Computed]
    public function boxes()
    {
        $agent = new Agent();
        $isMobile = $agent->isMobile();
        $productCount = $isMobile ? 3 : 6;

        return Box::query()
            ->where('is_active', true)
            ->ordered()
            ->with([
                'products' => function ($query) use ($productCount) {
                    $query->select('products.*')->inRandomOrder()->take($productCount)->withEffectivePrice();
                }
            ])
            ->get();
    }

    public function render()
    {
        return view('livewire.main.content.box.index');
    }
}
