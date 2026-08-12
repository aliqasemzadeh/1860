---
apply: always
---

# Localization — All Translations in `general.php`

All user-facing text must be translated. **Never hardcode strings** in Blade, Livewire, or PHP.

## Files

Add every new translation key to **both** files:

- `lang/en/general.php`
- `lang/fa/general.php`

Keep keys identical in both locales. Only the values differ.

## Flat array only

Use a **single flat (one-dimensional) array** — never nested arrays.

```php
// ✅ Correct — flat keys with snake_case prefix
return [
    'direction' => 'ltr',
    'save' => 'Save',
    'create_user' => 'Create User',
    'edit_user' => 'Edit User',
    'user_first_name' => 'First Name',
];

// ❌ Wrong — nested arrays
return [
    'user' => [
        'create' => 'Create User',
        'edit' => 'Edit User',
    ],
];
```

Group related keys by **prefix**, not nesting: `user_first_name`, `user_last_name`, `school_create`, `school_edit`.

## Usage in views & components

```blade
{{ __('general.save') }}
{{ __('general.create_user') }}
wire:confirm="{{ __('general.are_you_sure') }}"
Flux::toast(__('general.saved_successfully'));
```

Always use the `general` namespace: `__('general.key')` — never `__('general.group.key')`, `__('app.key')`, or other custom files for UI text.

## Adding a new key

1. Pick a **snake_case** flat key name (e.g. `create_user`, `saved_successfully`).
2. Add the English value to `lang/en/general.php`.
3. Add the Persian value to `lang/fa/general.php`.
4. Reference it with `__('general.key')`.

```php
// lang/en/general.php
return [
    'direction' => 'ltr',
    'save' => 'Save',
    'create_user' => 'Create User',
];

// lang/fa/general.php
return [
    'direction' => 'rtl',
    'save' => 'ذخیره',
    'create_user' => 'ایجاد کاربر',
];
```

## Rules

- **Flat array only** — no nested sub-arrays in `general.php`.
- **Both locales required** — never add a key to only one file.
- **No hardcoded text** in UI — headings, labels, buttons, toasts, placeholders, tooltips, confirmations.
- Use existing keys when possible before creating new ones.
- Validation messages use Laravel's `validation.php`; auth messages use `auth.php` — everything else goes in `general.php`.
- `general.direction` is used for RTL/LTR layout (`rtl` for fa, `ltr` for en).

## Do NOT use

- Nested arrays like `'user' => ['create' => '...']`
- Dot notation keys like `__('general.user.create')`
- `lang/fa/app.php` or `lang/en/app.php`
- Inline Persian/English strings in components
- New translation files for feature-specific UI text — use `general.php` instead
