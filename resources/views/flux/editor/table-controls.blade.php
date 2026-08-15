@blaze(fold: true, safe: ['kbd'])

@props([
    'kbd' => null,
])

<flux:dropdown position="bottom start" class="contents">
    <flux:tooltip content="{{ __('general.editor_table_controls') }}" :$kbd class="contents">
        <flux:editor.button icon="table-cells" icon:variant="outline" />
    </flux:tooltip>

    <flux:menu>
        <flux:menu.item icon="between-horizontal-start" data-editor="table-add-row-before">
            {{ __('general.editor_add_row_before') }}
        </flux:menu.item>
        <flux:menu.item icon="rows-3" data-editor="table-add-row-after">
            {{ __('general.editor_add_row_after') }}
        </flux:menu.item>
        <flux:menu.item icon="trash-2" data-editor="table-delete-row" variant="danger">
            {{ __('general.editor_delete_row') }}
        </flux:menu.item>

        <flux:menu.separator />

        <flux:menu.item icon="between-vertical-start" data-editor="table-add-column-before">
            {{ __('general.editor_add_column_before') }}
        </flux:menu.item>
        <flux:menu.item icon="columns-3" data-editor="table-add-column-after">
            {{ __('general.editor_add_column_after') }}
        </flux:menu.item>
        <flux:menu.item icon="trash-2" data-editor="table-delete-column" variant="danger">
            {{ __('general.editor_delete_column') }}
        </flux:menu.item>

        <flux:menu.separator />

        <flux:menu.item icon="square-split-horizontal" data-editor="table-merge-or-split">
            {{ __('general.editor_merge_or_split') }}
        </flux:menu.item>
        <flux:menu.item icon="trash-2" data-editor="table-delete" variant="danger">
            {{ __('general.editor_delete_table') }}
        </flux:menu.item>
    </flux:menu>
</flux:dropdown>
