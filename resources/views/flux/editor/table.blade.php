@blaze(fold: true, safe: ['kbd'])

@props([
    'kbd' => null,
])

<flux:tooltip content="{{ __('general.editor_insert_table') }}" :$kbd class="contents">
    <flux:editor.button data-editor="table" icon="table" icon:variant="outline" />
</flux:tooltip>
