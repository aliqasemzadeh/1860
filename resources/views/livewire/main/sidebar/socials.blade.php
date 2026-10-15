<div>
    @if (count($this->socials) > 0)
        <div class="space-y-3">
            <flux:text class="text-xs font-bold text-zinc-500 dark:text-zinc-400">
                {{ __('general.follow_us') }}
            </flux:text>
            <div class="flex flex-wrap items-center gap-2">
                @foreach ($this->socials as $key => $url)
                    @php($network = \App\Enums\SocialNetworkEnum::from($key))
                    <flux:tooltip content="{{ $network->label() }}">
                        <flux:button
                            :href="$url"
                            target="_blank"
                            rel="noopener noreferrer"
                            size="sm"
                            variant="primary"
                            :color="$network->color()"
                            :icon="$network->icon()"
                            icon:variant="outline"
                            square
                        />
                    </flux:tooltip>
                @endforeach
            </div>
        </div>
    @endif
</div>
