@blaze(fold: true, safe: ['kbd'])

@props([
    'kbd' => null,
])

<flux:dropdown position="bottom start" data-editor="table-controls" class="contents">
    <flux:tooltip content="{{ __('general.editor_table_controls') }}" :$kbd class="contents">
        <flux:editor.button data-match-target icon="table-cells" icon:variant="outline" />
    </flux:tooltip>

    <div popover="manual" tabindex="-1" class="min-w-[14rem] p-[5px] rounded-lg border border-zinc-200 dark:border-zinc-600 shadow-xs bg-white dark:bg-zinc-700">
        <div class="flex flex-col gap-0.5">
            <button type="button" data-editor="table-add-row-before" class="flex w-full items-center gap-2 rounded-md px-2 py-1.5 text-start text-sm text-zinc-800 dark:text-white hover:bg-zinc-100 dark:hover:bg-white/10">
                <flux:icon.between-horizontal-start variant="mini" class="size-5 text-zinc-400" />
                {{ __('general.editor_add_row_before') }}
            </button>
            <button type="button" data-editor="table-add-row-after" class="flex w-full items-center gap-2 rounded-md px-2 py-1.5 text-start text-sm text-zinc-800 dark:text-white hover:bg-zinc-100 dark:hover:bg-white/10">
                <flux:icon.rows-3 variant="mini" class="size-5 text-zinc-400" />
                {{ __('general.editor_add_row_after') }}
            </button>
            <button type="button" data-editor="table-delete-row" class="flex w-full items-center gap-2 rounded-md px-2 py-1.5 text-start text-sm text-red-600 dark:text-red-400 hover:bg-zinc-100 dark:hover:bg-white/10">
                <flux:icon.trash-2 variant="mini" class="size-5" />
                {{ __('general.editor_delete_row') }}
            </button>

            <div class="my-1 border-t border-zinc-200 dark:border-zinc-600"></div>

            <button type="button" data-editor="table-add-column-before" class="flex w-full items-center gap-2 rounded-md px-2 py-1.5 text-start text-sm text-zinc-800 dark:text-white hover:bg-zinc-100 dark:hover:bg-white/10">
                <flux:icon.between-vertical-start variant="mini" class="size-5 text-zinc-400" />
                {{ __('general.editor_add_column_before') }}
            </button>
            <button type="button" data-editor="table-add-column-after" class="flex w-full items-center gap-2 rounded-md px-2 py-1.5 text-start text-sm text-zinc-800 dark:text-white hover:bg-zinc-100 dark:hover:bg-white/10">
                <flux:icon.columns-3 variant="mini" class="size-5 text-zinc-400" />
                {{ __('general.editor_add_column_after') }}
            </button>
            <button type="button" data-editor="table-delete-column" class="flex w-full items-center gap-2 rounded-md px-2 py-1.5 text-start text-sm text-red-600 dark:text-red-400 hover:bg-zinc-100 dark:hover:bg-white/10">
                <flux:icon.trash-2 variant="mini" class="size-5" />
                {{ __('general.editor_delete_column') }}
            </button>

            <div class="my-1 border-t border-zinc-200 dark:border-zinc-600"></div>

            <button type="button" data-editor="table-merge-or-split" class="flex w-full items-center gap-2 rounded-md px-2 py-1.5 text-start text-sm text-zinc-800 dark:text-white hover:bg-zinc-100 dark:hover:bg-white/10">
                <flux:icon.square-split-horizontal variant="mini" class="size-5 text-zinc-400" />
                {{ __('general.editor_merge_or_split') }}
            </button>
            <button type="button" data-editor="table-delete" class="flex w-full items-center gap-2 rounded-md px-2 py-1.5 text-start text-sm text-red-600 dark:text-red-400 hover:bg-zinc-100 dark:hover:bg-white/10">
                <flux:icon.trash-2 variant="mini" class="size-5" />
                {{ __('general.editor_delete_table') }}
            </button>
        </div>
    </div>
</flux:dropdown>
