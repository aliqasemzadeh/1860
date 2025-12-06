<?php

namespace App\Livewire\ServiceCenter\Repair;

use App\Models\ServiceCenter\Repair;
use App\Models\ServiceCenter\RepairService;
use Flux\Flux;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Computed;
use Livewire\Attributes\On;
use Livewire\Component;

class Services extends Component
{
    public Repair $repair;

    public int $id;

    public string $description = '';
    public string $price = '';

    #[On('service-center.repair.services.assign-data')]
    public function assignData(int $id): void
    {
        $this->authorize('service_center_repair_services');

        $this->repair = Repair::findOrFail($id);
        $this->id = $this->repair->id;

        Flux::modal('service-center.repair.services.modal')->show();
    }

    #[Computed]
    public function servicesList()
    {
        if (!isset($this->repair->id)) {
            return collect();
        }

        return RepairService::where('repair_id', $this->repair->id)
            ->with('technician')
            ->orderBy('created_at', 'desc')
            ->get();
    }

    #[Computed]
    public function totalPrice()
    {
        return $this->servicesList->sum('price');
    }

    public function addService(): void
    {
        $this->validate([
            'description' => ['required', 'string', 'min:3'],
            'price' => ['required', 'string', 'regex:/^[\d,]+(\.\d+)?$/'],
        ], [], [
            'description' => __('app.service_description'),
            'price' => __('app.service_price'),
        ]);

        RepairService::create([
            'repair_id' => $this->repair->id,
            'technician_user_id' => (string) Auth::id(),
            'description' => $this->description,
            'price' => (float) preg_replace('/[^0-9.]/', '', $this->price),
        ]);

        $this->description = '';
        $this->price = '';

        $this->dispatch('$refresh');

        Flux::toast(variant: 'success', text: __('app.service_added'));
    }

    public function deleteService(int $serviceId): void
    {
        $service = RepairService::findOrFail($serviceId);

        if ($service->repair_id !== $this->repair->id) {
            Flux::toast(variant: 'danger', text: __('app.are_you_sure'));
            return;
        }

        $service->delete();

        $this->dispatch('$refresh');

        Flux::toast(variant: 'success', text: __('app.service_deleted'));
    }

    public function render()
    {
        return view('livewire.service-center.repair.services');
    }
}
