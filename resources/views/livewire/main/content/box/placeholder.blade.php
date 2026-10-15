<div class="space-y-10 md:space-y-14 animate-pulse">
    <div class="rounded-[2rem] border border-zinc-200/80 bg-zinc-100/70 dark:border-zinc-800/80 dark:bg-zinc-900/40 p-5 md:p-8">
        <div class="flex items-center gap-4 mb-6">
            <div class="size-14 md:size-16 rounded-2xl bg-zinc-200 dark:bg-zinc-800"></div>
            <div class="space-y-2">
                <div class="h-6 w-32 rounded bg-zinc-200 dark:bg-zinc-800"></div>
                <div class="h-4 w-48 rounded bg-zinc-200 dark:bg-zinc-800"></div>
            </div>
        </div>
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
            @for($i = 0; $i < 4; $i++)
                <div class="h-64 rounded-3xl bg-zinc-200/70 dark:bg-zinc-800/60"></div>
            @endfor
        </div>
    </div>
</div>
