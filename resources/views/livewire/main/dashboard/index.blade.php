<div>
    <flux:kanban>
        <flux:kanban.column>
            <flux:kanban.column.header heading="برنامه ریزی شده" count="2" />
            <flux:kanban.column.cards>
                <flux:kanban.card as="button" heading="طراحی داشبرد مدیریت">
                    <x-slot name="header">
                        <div class="flex gap-2">
                            <flux:badge color="blue" size="sm">UI</flux:badge>
                            <flux:badge color="green" size="sm">Backend</flux:badge>
                        </div>
                    </x-slot>
                    <x-slot name="footer">
                        <flux:icon name="bars-3-bottom-left" variant="micro" class="text-zinc-400" />
                        <flux:avatar.group>
                            <flux:avatar circle size="xs" src="https://unavatar.io/github/aliqasemzadeh" />
                        </flux:avatar.group>
                    </x-slot>
                </flux:kanban.card>
                <flux:kanban.card as="button" heading="تعمیر سیستم استانداری">
                    <x-slot name="header">
                        <div class="flex gap-2">
                            <flux:badge color="red" size="sm">خدمات</flux:badge>
                        </div>
                    </x-slot>
                    <x-slot name="footer">
                        <flux:icon name="bars-3-bottom-left" variant="micro" class="text-zinc-400" />
                        <flux:avatar.group>
                            <flux:avatar circle size="xs" src="https://unavatar.io/x/calebporzio" />
                        </flux:avatar.group>
                    </x-slot>
                </flux:kanban.card>
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
            <flux:kanban.column.header heading="در حال انجام" count="0" />
            <flux:kanban.column.cards>
                <!-- ... -->
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
            <flux:kanban.column.header heading="انجام شده" count="0" />
            <flux:kanban.column.cards>
                <!-- ... -->
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
