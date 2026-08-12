---
apply: always
---

# Flux UI — Flyout Modal

Use flyout modals for all create/edit forms. Do NOT use centered modals.

## Required markup

```blade
<flux:modal name="user-create-modal" flyout position="right" class="md:w-lg">
    <form wire:submit="save" class="space-y-6">
        <div>
            <flux:heading size="lg">{{ __('general.create_user') }}</flux:heading>
            <flux:text class="mt-2">{{ __('general.create_user_description') }}</flux:text>
        </div>

        {{-- form fields --}}

        <flux:button type="submit" variant="primary" color="teal" class="w-full">
            {{ __('general.save') }}
        </flux:button>
    </form>
</flux:modal>
```

## Rules

- **Always** use `<flux:modal flyout position="right">` — never a normal centered modal.
- Modal Livewire components must **not** wrap `<flux:modal>` in an outer `<div>`. The modal tag is the root element.
- **No Cancel button** inside modals. Users close via the X button or clicking outside.
- Submit buttons must be **full width** (`class="w-full"`).
- Use `<flux:modal.trigger name="modal-name">` to open from a button on the listing page.
- Give each modal a **unique `name`**. In loops use `:name="'edit-user-'.$user->id"`.

## Open / close from Livewire

```php
use Flux\Flux;

// Open
Flux::modal('user-create-modal')->show();

// Close one
Flux::modal('user-create-modal')->close();

// Close all
Flux::modals()->close();
```

After save: close the modal, dispatch `refresh-data`, and show a toast:

```php
Flux::modals()->close();
$this->dispatch('refresh-data');
Flux::toast(__('general.saved_successfully'));
```

## Edit pattern with events

Listing page dispatches data; edit modal listens and opens:

```blade
{{-- listing page --}}
<flux:button wire:click="$dispatch('panels.administrator.user.edit.assign-data', { userId: {{ $user->id }} })" />

{{-- edit modal component --}}
<flux:modal name="user-edit-modal" flyout position="right" class="md:w-lg">
    ...
</flux:modal>
```

```php
#[On('panels.administrator.user.edit.assign-data')]
public function assignData(int $userId): void
{
    $this->form->setModel(User::findOrFail($userId));
    Flux::modal('user-edit-modal')->show();
}
```

## Data binding (alternative)

```blade
<flux:modal wire:model.self="showModal" flyout position="right" class="md:w-lg">
```

Use `.self` modifier. Toggle `$showModal` from Livewire or Alpine (`$wire.showModal = true`).

## Props reference

| Prop | Value | Notes |
|------|-------|-------|
| `flyout` | boolean | Required for side-panel style |
| `position` | `right` (default), `left`, `bottom` | Always use `right` in this project |
| `variant` | `default`, `floating` | Optional; `floating` adds detached look with footer slot |
| `dismissible` | `true` (default) | Set `:dismissible="false"` to prevent outside-click close |
| `scroll` | `body` | For very long content, scrolls the page instead of clipping |

## Docs

https://fluxui.dev/components/modal
