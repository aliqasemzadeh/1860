<x-slot name="title">
    {{ __('app.service_center_dashboard') ?? 'Service Center Dashboard' }}
</x-slot>
<div>
    <flux:kanban>
        <flux:kanban.column>
            <flux:kanban.column.header heading="برنامه ریزی شده" count="5" />
            <flux:kanban.column.cards  wire:sort="sortItem" wire:sort:group="planed">
                @foreach ($this->repairs as $repair)
                    <flux:kanban.card wire:sort:item="{{ $repair->id }}" :heading="$repair->admission_code" />
                @endforeach
            </flux:kanban.column.cards>
            <flux:kanban.column.footer>
                <form>
                    <flux:kanban.card>
                        <div class="flex items-center gap-1">
                            <flux:heading class="flex-1">
                                <input class="w-full outline-none" placeholder="New card...">
                            </flux:heading>
                            <flux:button type="submit" variant="filled" size="sm" inset="top bottom" class="-me-1.5">Add</flux:button>
                        </div>
                    </flux:kanban.card>
                </form>
                <flux:button variant="subtle" icon="plus" size="sm" align="start">
                    New card
                </flux:button>
            </flux:kanban.column.footer>
        </flux:kanban.column>
        <flux:kanban.column>
            <flux:kanban.column.header heading="در حال انجام" count="5" />
            <flux:kanban.column.cards  wire:sort="sortItem" wire:sort:group="doing">
                <flux:kanban.card heading="122112" />
            </flux:kanban.column.cards>
            <flux:kanban.column.footer>
                <form>
                    <flux:kanban.card>
                        <div class="flex items-center gap-1">
                            <flux:heading class="flex-1">
                                <input class="w-full outline-none" placeholder="New card...">
                            </flux:heading>
                            <flux:button type="submit" variant="filled" size="sm" inset="top bottom" class="-me-1.5">Add</flux:button>
                        </div>
                    </flux:kanban.card>
                </form>
                <flux:button variant="subtle" icon="plus" size="sm" align="start">
                    New card
                </flux:button>
            </flux:kanban.column.footer>
        </flux:kanban.column>
        <flux:kanban.column>
            <flux:kanban.column.header heading="انجام شده" count="5" />
            <flux:kanban.column.cards  wire:sort="sortItem" wire:sort:group="done">
                <flux:kanban.card heading="2323223" />
            </flux:kanban.column.cards>
            <flux:kanban.column.footer>
                <form>
                    <flux:kanban.card>
                        <div class="flex items-center gap-1">
                            <flux:heading class="flex-1">
                                <input class="w-full outline-none" placeholder="New card...">
                            </flux:heading>
                            <flux:button type="submit" variant="filled" size="sm" inset="top bottom" class="-me-1.5">Add</flux:button>
                        </div>
                    </flux:kanban.card>
                </form>
                <flux:button variant="subtle" icon="plus" size="sm" align="start">
                    New card
                </flux:button>
            </flux:kanban.column.footer>
        </flux:kanban.column>
    </flux:kanban>
</div>
