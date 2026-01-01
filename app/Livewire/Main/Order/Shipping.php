<?php

namespace App\Livewire\Main\Order;

use App\Jobs\Notification\SendSmsMessageJob;
use App\Models\Customer\ShippingAddress as CustomerShippingAddress;
use App\Models\Shop\Order;
use App\Models\Shop\OrderItem;
use App\Models\Shop\Product;
use App\Models\Shop\ShippingRate;
use App\Models\Shop\ShippingZone;
use Binafy\LaravelCart\Models\Cart as UserCart;
use Flux\Flux;
use Illuminate\Support\Facades\Log;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.app')]
class Shipping extends Component
{
    public $selectedAddressId = null;

    public $selectedShippingRateId = null;

    public $shippingAddress = [];

    public $showNewAddressForm = false;

    public $customerNote = '';

    // New address fields
    public $name = '';

    public $province_id = null;

    public $city_id = null;

    public $city_search = '';

    public $address = '';

    public $postal_code = '';

    public $emergency_contact = '';

    public $is_default = false;

    public function mount()
    {
        if (! auth()->check()) {
            return $this->redirect(route('order.checkout'), navigate: true);
        }

        if (! $this->cart || $this->cartItems->isEmpty()) {
            Flux::toast(variant: 'warning', text: __('app.cart_is_empty'));

            return $this->redirect(route('order.cart'), navigate: true);
        }

        // Set default address if available
        $defaultAddress = auth()->user()->defaultShippingAddress;
        if ($defaultAddress) {
            $this->selectedAddressId = $defaultAddress->id;
            $this->loadAddress($defaultAddress->id);
        }
    }

    #[Computed]
    public function cart()
    {
        if (! auth()->check()) {
            return null;
        }

        return UserCart::query()
            ->with(['items.itemable'])
            ->where('user_id', auth()->id())
            ->first();
    }

    #[Computed]
    public function cartItems()
    {
        if (! $this->cart) {
            return collect();
        }

        return $this->cart->items;
    }

    #[Computed]
    public function subtotal()
    {
        if (! $this->cart) {
            return 0;
        }

        $total = 0;
        foreach ($this->cart->items as $item) {
            $price = $item->itemable->getPrice();
            $options = is_string($item->options) ? json_decode($item->options, true) : $item->options;
            $priceId = $options['price_id'] ?? null;
            if ($priceId) {
                $priceRecord = \App\Models\Shop\ProductPrice::find($priceId);
                if ($priceRecord) {
                    $price = $priceRecord->sale_price && $priceRecord->sale_price < $priceRecord->price
                        ? $priceRecord->sale_price
                        : $priceRecord->price;
                }
            }
            $total += $price * $item->quantity;
        }

        return $total;
    }

    #[Computed]
    public function totalWeight()
    {
        if (! $this->cart) {
            return 0;
        }

        $weight = 0;
        foreach ($this->cart->items as $item) {
            if ($item->itemable instanceof Product) {
                $productWeight = $item->itemable->weight ?? 0;
                $weight += $productWeight * $item->quantity;
            }
        }

        return $weight;
    }

    #[Computed]
    public function addresses()
    {
        if (! auth()->check()) {
            return collect();
        }

        return auth()->user()->shippingAddresses()->orderBy('is_default', 'desc')->orderBy('created_at', 'desc')->get();
    }

    #[Computed]
    public function provinces()
    {
        return require lang_path('fa/provinces.php');
    }

    #[Computed]
    public function cities()
    {
        if (! $this->province_id) {
            return [];
        }

        $allCities = __('cities');
        $provinceCities = $allCities[$this->province_id] ?? [];

        // PHP converts numeric string keys to integers when iterating with foreach
        // We need to use array_keys() to get the original string keys and preserve them
        $result = [];
        $cityKeys = array_keys($provinceCities);

        // Iterate using the original keys from the array
        foreach ($cityKeys as $originalKey) {
            // originalKey is the string key from cities.php (e.g., '107070')
            // We need to ensure it stays as string and get the city data
            $result[(string) $originalKey] = $provinceCities[$originalKey]['name'] ?? $provinceCities[$originalKey];
        }

        return $result;
    }

    public function updatedProvinceId()
    {
        $this->city_id = null; // Reset city when province changes
    }

    public function loadAddress($addressId)
    {
        $address = CustomerShippingAddress::find($addressId);
        if ($address && $address->user_id === auth()->id()) {
            $this->selectedAddressId = $addressId;

            // Convert city_id (index) to city key if needed
            $cityKey = $address->city_id;
            $allCities = require lang_path('fa/cities.php');
            if (isset($allCities[$address->province_id]) && is_array($allCities[$address->province_id])) {
                $provinceCities = $allCities[$address->province_id];
                $cityKeys = array_keys($provinceCities);
                // If city_id is numeric index, convert to city key
                if (is_numeric($address->city_id) && isset($cityKeys[(int) $address->city_id])) {
                    $cityKey = $cityKeys[(int) $address->city_id];
                }
            }

            $this->shippingAddress = [
                'province_id' => (int) $address->province_id, // Ensure it's integer
                'city_id' => $cityKey,
                'postal_code' => $address->postal_code,
            ];
            $this->selectedShippingRateId = null; // Reset shipping method
            $this->calculateShippingOptions();
        }
    }

    public function calculateShippingOptions()
    {
        // This will trigger the computed property
        $this->dispatch('$refresh');
    }

    #[Computed]
    public function availableShippingMethods()
    {
        if (! $this->selectedAddressId || ! isset($this->shippingAddress['province_id']) || ! isset($this->shippingAddress['city_id'])) {
            return collect();
        }

        // Normalize province ID to integer and city ID to city key (string)
        $provinceId = (int) ($this->shippingAddress['province_id'] ?? 0);
        $cityKey = (string) ($this->shippingAddress['city_id'] ?? '');
        $postalCode = $this->shippingAddress['postal_code'] ?? '';

        if (config('app.debug')) {
            \Log::info('Calculating available shipping methods', [
                'province_id' => $provinceId,
                'city_key' => $cityKey,
                'subtotal' => $this->subtotal,
                'total_weight' => $this->totalWeight,
            ]);
        }

        // Find matching zones
        $zones = ShippingZone::all()->filter(function ($zone) use ($provinceId, $cityKey, $postalCode) {
            $states = $zone->states ?? [];
            $cities = $zone->cities ?? [];
            $areas = $zone->areas ?? [];
            $countries = $zone->countries ?? [];

            // Normalize arrays
            $states = is_array($states) ? $states : [];
            $cities = is_array($cities) ? $cities : [];
            $areas = is_array($areas) ? $areas : [];
            $countries = is_array($countries) ? $countries : [];

            // Check Country (Default to IR for now as addresses don't have country_id)
            if (! empty($countries) && ! in_array('IR', $countries)) {
                if (config('app.debug')) {
                    \Log::info("Zone {$zone->id} ({$zone->name}) excluded: IR not in countries", ['zone_countries' => $countries]);
                }

                return false;
            }

            // Check province
            if (! empty($states)) {
                $stateIds = array_map('intval', $states);
                $stateIds = array_values(array_unique(array_filter($stateIds)));

                if (! empty($stateIds) && ! in_array((int) $provinceId, $stateIds, true)) {
                    if (config('app.debug')) {
                        \Log::info("Zone {$zone->id} ({$zone->name}) excluded: province {$provinceId} not in states", [
                            'zone_states' => $stateIds,
                            'user_province_id' => $provinceId,
                        ]);
                    }

                    return false;
                }
            }

            // Check city
            // If cities array is not empty, city key must match
            // If cities array is empty, all cities in selected provinces are included
            if (count($cities) > 0) {
                $cityMatches = false;
                foreach ($cities as $city) {
                    if (is_array($city) && isset($city['province_id']) && isset($city['city_index'])) {
                        // Old format: [province_id, city_index] - convert to city key
                        $cityProvinceId = (int) ($city['province_id'] ?? 0);
                        $cityIndex = (int) ($city['city_index'] ?? 0);
                        if ($cityProvinceId === $provinceId) {
                            $allCities = require lang_path('fa/cities.php');
                            if (isset($allCities[$cityProvinceId]) && is_array($allCities[$cityProvinceId])) {
                                $provinceCities = $allCities[$cityProvinceId];
                                $cityKeys = array_keys($provinceCities);
                                if (isset($cityKeys[$cityIndex]) && $cityKeys[$cityIndex] === $cityKey) {
                                    $cityMatches = true;
                                    break;
                                }
                            }
                        }
                    } elseif (is_string($city) && preg_match('/^\d{6}$/', $city)) {
                        // New format: city key (e.g., '100001')
                        if ($city === $cityKey) {
                            $cityMatches = true;
                            break;
                        }
                    } elseif (is_array($city) && isset($city['city_id'])) {
                        // Very new format: ['name' => '...', 'city_id' => '...']
                        if ($city['city_id'] === $cityKey) {
                            $cityMatches = true;
                            break;
                        }
                    }
                }
                // If cities are specified but none match, exclude this zone
                if (! $cityMatches) {
                    return false;
                }
            }

            // Check postal code area (first 3 digits)
            // If areas array is not empty and postal code is provided, postal area must match
            // If areas array is empty, all postal areas are included
            if (! empty($areas) && $postalCode) {
                $postalArea = substr($postalCode, 0, 3);
                if (! in_array($postalArea, $areas)) {
                    return false;
                }
            }

            // All checks passed, this zone matches
            if (config('app.debug')) {
                \Log::info("Zone {$zone->id} ({$zone->name}) matched.");
            }

            return true;
        });

        $methods = collect();

        // Debug: Log matched zones
        if (config('app.debug')) {
            \Log::info('Matched zones for shipping', [
                'province_id' => $provinceId,
                'city_key' => $cityKey,
                'postal_code' => $postalCode,
                'matched_zones_count' => $zones->count(),
                'zone_ids' => $zones->pluck('id')->toArray(),
            ]);
        }

        foreach ($zones as $zone) {
            $rates = ShippingRate::where('shipping_zone_id', $zone->id)
                ->where('is_active', true)
                ->with('method')
                ->get();

            if ($rates->isEmpty() && config('app.debug')) {
                \Log::info("No active rates found for zone {$zone->id}");
            }

            foreach ($rates as $rate) {
                if (! $rate->method) {
                    if (config('app.debug')) {
                        \Log::info("Rate {$rate->id} excluded: method is missing");
                    }

                    continue;
                }

                if (! $rate->method->is_active) {
                    if (config('app.debug')) {
                        \Log::info("Rate {$rate->id} excluded: method {$rate->method->name} is inactive");
                    }

                    continue;
                }

                $cost = $this->calculateShippingCost($rate, $zone->id);
                if ($cost === null) {
                    if (config('app.debug')) {
                        \Log::info("Rate {$rate->id} excluded: cost calculation returned null (check weight/price constraints)");
                    }

                    continue; // Rate doesn't apply to this order
                }

                $methods->push([
                    'id' => $rate->id,
                    'method_id' => $rate->shipping_method_id,
                    'method_name' => $rate->method->name,
                    'zone_id' => $zone->id,
                    'cost' => $cost,
                    'estimated_days' => $rate->estimated_days,
                    'rate' => $rate,
                ]);
            }
        }

        Log::info('Shipping methods', ['methods' => $methods]);

        // Group by method and zone to avoid duplicates, but keep all options
        return $methods->values();
    }

    protected function calculateShippingCost(ShippingRate $rate, $zoneId)
    {
        $totalWeight = $this->totalWeight;
        $subtotal = $this->subtotal;

        // Check Weight constraints regardless of rate_type if values are set
        if ($rate->min_weight !== null && $totalWeight < $rate->min_weight) {
            if (config('app.debug')) {
                \Log::info("Rate {$rate->id} excluded: total weight {$totalWeight} is less than min weight {$rate->min_weight}");
            }

            return null;
        }
        if ($rate->max_weight !== null && $totalWeight > $rate->max_weight) {
            if (config('app.debug')) {
                \Log::info("Rate {$rate->id} excluded: total weight {$totalWeight} is more than max weight {$rate->max_weight}");
            }

            return null;
        }

        // Check Price constraints regardless of rate_type if values are set
        if ($rate->min_price !== null && $subtotal < $rate->min_price) {
            if (config('app.debug')) {
                \Log::info("Rate {$rate->id} excluded: subtotal {$subtotal} is less than min price {$rate->min_price}");
            }

            return null;
        }
        if ($rate->max_price !== null && $subtotal > $rate->max_price) {
            if (config('app.debug')) {
                \Log::info("Rate {$rate->id} excluded: subtotal {$subtotal} is more than max price {$rate->max_price}");
            }

            return null;
        }

        return $rate->amount;
    }

    public function saveNewAddress()
    {
        $this->validate([
            'name' => ['nullable', 'string', 'max:255'],
            'province_id' => ['required', 'integer'],
            'city_id' => ['required', 'string', 'regex:/^\d{6}$/'], // City key format: 6 digits (e.g., '100001')
            'address' => ['required', 'string'],
            'postal_code' => ['nullable', 'string', 'max:10'],
            'emergency_contact' => ['nullable', 'string', 'max:20'],
        ]);

        // If this is set as default, unset other defaults
        if ($this->is_default) {
            auth()->user()->shippingAddresses()->update(['is_default' => false]);
        }

        // city_id is already city key (e.g., '100001') from the select
        $newAddress = auth()->user()->shippingAddresses()->create([
            'name' => $this->name,
            'province_id' => $this->province_id,
            'city_id' => $this->city_id, // This is now city key (string)
            'address' => $this->address,
            'postal_code' => $this->postal_code,
            'emergency_contact' => $this->emergency_contact,
            'is_default' => $this->is_default,
        ]);

        $this->selectedAddressId = $newAddress->id;
        $this->loadAddress($newAddress->id);
        $this->showNewAddressForm = false;
        $this->resetNewAddressFields();

        Flux::toast(variant: 'success', text: __('app.address_saved'));
    }

    protected function resetNewAddressFields()
    {
        $this->name = '';
        $this->province_id = null;
        $this->city_id = null;
        $this->address = '';
        $this->postal_code = '';
        $this->emergency_contact = '';
        $this->is_default = false;
    }

    public function deleteAddress($addressId)
    {
        $address = CustomerShippingAddress::find($addressId);
        if ($address && $address->user_id === auth()->id()) {
            $address->delete();

            // If deleted address was selected, clear selection
            if ($this->selectedAddressId == $addressId) {
                $this->selectedAddressId = null;
                $this->shippingAddress = [];
                $this->selectedShippingRateId = null;
            }

            Flux::toast(variant: 'success', text: __('app.address_deleted'));
        }
    }

    public function toggleNewAddressForm()
    {
        $this->showNewAddressForm = ! $this->showNewAddressForm;
        if (! $this->showNewAddressForm) {
            $this->resetNewAddressFields();
        }
    }

    public function createOrder()
    {
        if (! $this->selectedAddressId) {
            Flux::toast(variant: 'danger', text: __('app.select_shipping_address'));

            return;
        }

        if (! $this->selectedShippingRateId) {
            Flux::toast(variant: 'danger', text: __('app.select_shipping_method'));

            return;
        }

        $address = CustomerShippingAddress::find($this->selectedAddressId);
        if (! $address || $address->user_id !== auth()->id()) {
            Flux::toast(variant: 'danger', text: __('app.invalid_address'));

            return;
        }

        // Find the selected shipping option
        $selectedOption = $this->availableShippingMethods->firstWhere('id', $this->selectedShippingRateId);
        if (! $selectedOption) {
            Flux::toast(variant: 'danger', text: __('app.invalid_shipping_method'));

            return;
        }

        $rate = ShippingRate::find($this->selectedShippingRateId);
        if (! $rate) {
            Flux::toast(variant: 'danger', text: __('app.invalid_shipping_rate'));

            return;
        }

        // Calculate totals
        $subtotal = $this->subtotal;
        $shippingAmount = $selectedOption['cost'];
        $discountAmount = 0; // Can be calculated later
        $taxAmount = 0; // Can be calculated later
        $total = $subtotal + $shippingAmount + $taxAmount - $discountAmount;

        // Create order
        $order = Order::create([
            'user_id' => auth()->id(),
            'order_number' => Order::generateOrderNumber(),
            'status' => 'pending',
            'currency' => 'IRT',
            'subtotal_amount' => $subtotal,
            'discount_amount' => $discountAmount,
            'tax_amount' => $taxAmount,
            'shipping_method_id' => $rate->shipping_method_id,
            'shipping_zone_id' => $rate->shipping_zone_id,
            'shipping_amount' => $shippingAmount,
            'shipping_estimated_days' => $rate->estimated_days,
            'total_amount' => $total,
            'shipping_address' => [
                'name' => $address->name,
                'address' => $address->address,
                'province_id' => $address->province_id,
                'city_id' => $address->city_id,
                'postal_code' => $address->postal_code,
                'emergency_contact' => $address->emergency_contact,
            ],
            'customer_note' => $this->customerNote,
        ]);

        // Create order items
        foreach ($this->cart->items as $cartItem) {
            if (! $cartItem->itemable instanceof Product) {
                continue;
            }

            $price = $cartItem->itemable->getPrice();
            $options = is_string($cartItem->options) ? json_decode($cartItem->options, true) : $cartItem->options;
            $priceId = $options['price_id'] ?? null;
            $colorId = $options['color']['id'] ?? null;
            $warrantyId = $options['warranty']['id'] ?? null;

            if ($priceId) {
                $priceRecord = \App\Models\Shop\ProductPrice::find($priceId);
                if ($priceRecord) {
                    $price = $priceRecord->sale_price && $priceRecord->sale_price < $priceRecord->price
                        ? $priceRecord->sale_price
                        : $priceRecord->price;
                }
            }

            OrderItem::create([
                'order_id' => $order->id,
                'sku' => $cartItem->itemable->sku ?? null,
                'name' => $cartItem->itemable->name,
                'warranty_id' => $warrantyId,
                'color_id' => $colorId,
                'quantity' => $cartItem->quantity,
                'unit_price_amount' => $price,
                'discount_amount' => 0,
                'tax_amount' => 0,
                'total_amount' => $price * $cartItem->quantity,
                'meta' => $cartItem->options,
            ]);
        }

        // Clear cart
        $this->cart->items()->delete();
        $this->cart->delete();

        // Send SMS notification
        $smsMessage = __('app.order_created_sms', ['order_number' => $order->order_number]);
        dispatch(new SendSmsMessageJob(auth()->user()->mobile, $smsMessage));

        Flux::toast(variant: 'success', text: __('app.order_created'));
        $this->redirect(route('order.view', ['id' => $order->id]), navigate: true);
    }

    public function render()
    {
        return view('livewire.main.order.shipping');
    }
}
