<?php

namespace App\Livewire\ServiceCenter\Repair;

use App\Models\ServiceCenter\Repair;
use Flux\Flux;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class Create extends Component
{
    public string $owner_name = '';
    public string $owner_mobile = '';
    public ?string $owner_email = null;
    public ?string $owner_national_code = null;
    public ?string $owner_address = null;

    public ?string $warranty_type = null;
    public ?string $warranty_date = null;
    public ?string $device_type = null;
    public ?string $device_brand = null;
    public ?string $device_model = null;
    public ?string $device_serial_number = null;

    public ?string $device_problem = null;
    public ?string $device_accessories = null;
    public ?string $device_description = null;

    public ?string $device_problem_file = null;

    public ?string $admission_description = null;

    protected $rules = [
        'owner_name' => ['required', 'string', 'max:255'],
        'owner_mobile' => ['required', 'string', 'max:50', 'ir_mobile'],
        'owner_email' => ['nullable', 'email', 'max:255'],
        'owner_national_code' => ['nullable', 'string', 'max:50', 'ir_national_code'],
        'owner_address' => ['nullable', 'string'],

        'warranty_type' => ['nullable', 'string', 'max:255'],
        'warranty_date' => ['nullable', 'date'],
        'device_type' => ['required', 'string', 'max:255'],
        'device_brand' => ['nullable', 'string', 'max:255'],
        'device_model' => ['nullable', 'string', 'max:255'],
        'device_serial_number' => ['nullable', 'string', 'max:255'],

        'device_problem' => ['nullable', 'string'],
        'device_problem_file' => ['nullable', 'string'],
        'device_accessories' => ['nullable', 'string'],
        'device_description' => ['nullable', 'string'],

        'admission_description' => ['nullable', 'string'],
    ];

    public function admission(): void
    {
        $this->authorize('service_center_repair_create');

        $this->validate();

        // Ensure at least one of problem text or whiteboard file is provided
        if ((empty($this->device_problem) || trim((string) $this->device_problem) === '')
            && (empty($this->device_problem_file) || trim((string) $this->device_problem_file) === '')) {
            $this->addError('device_problem', __('validation.required', ['attribute' => __('app.device_problem')]));

            return;
        }

        // Normalize: if only a whiteboard image is provided, persist a sentinel in device_problem
        // to satisfy NOT NULL constraint and help the UI show that there is an attached drawing.
        $normalizedDeviceProblem = $this->device_problem;
        if ((empty($normalizedDeviceProblem) || trim((string) $normalizedDeviceProblem) === '')
            && !empty($this->device_problem_file) && trim((string) $this->device_problem_file) !== '') {
            $normalizedDeviceProblem = 'file';
        }

        Repair::create([
            // System-filled fields
            'admission_user_id' => Auth::user()->id,
            'admission_description' => $this->admission_description,
            'status' => __('app.repair_status_new'),
            'status_description' => __('app.repair_status_new_description'),
            'status_date' => now(),
            'estimate_date' => null,

            // Owner
            'owner_name' => $this->owner_name,
            'owner_mobile' => $this->owner_mobile,
            'owner_email' => $this->owner_email,
            'owner_national_code' => $this->owner_national_code,
            'owner_address' => $this->owner_address,

            // Warranty / device
            'warranty_type' => $this->warranty_type,
            'warranty_date' => $this->warranty_date,
            'device_type' => $this->device_type,
            'device_brand' => $this->device_brand,
            'device_model' => $this->device_model,
            'device_serial_number' => $this->device_serial_number,

            // Problem
            'device_problem' => $normalizedDeviceProblem,
            'device_accessories' => $this->device_accessories,
            'device_description' => $this->device_description,
            'device_problem_file' => $this->device_problem_file,
        ]);

        // Reset form after save
        $this->reset();

        Flux::toast(__('app.repair_created_message'));

        // Ask parent/index to refresh list if needed
        $this->dispatch('service-center.repair.index.render');
    }

    public function render()
    {
        return view('livewire.service-center.repair.create');
    }
}
