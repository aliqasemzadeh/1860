<flux:modal name="shop.setting-management.color.create.modal" class="md:w-96" flyout position="right">
    <div class="space-y-6">
        <div>
            <flux:heading size="lg">{{ __('app.create_color') }}</flux:heading>
            <flux:text class="mt-2">{{ __('app.create_color_description') }}</flux:text>
        </div>

        <form wire:submit="create" method="post">
            <div class="pb-2 space-y-3">
                <flux:field>
                    <flux:label>{{ __('app.name') }}</flux:label>
                    <flux:input wire:model.live="name" type="text" />
                    <flux:error name="name" />
                </flux:field>

                <flux:field>
                    <flux:label>{{ __('app.slug') }}</flux:label>
                    <flux:input wire:model.live="slug" type="text" />
                    <flux:error name="slug" />
                </flux:field>

                <flux:field>
                    <flux:label>{{ __('app.slug_fa') }}</flux:label>
                    <flux:input wire:model.live="slug_fa" type="text" />
                    <flux:error name="slug_fa" />
                </flux:field>

                <flux:field>
                    <flux:label>{{ __('app.hex') }}</flux:label>
                    <div
                        x-data="{
                          defaultColor: '#14b8a6',
                          color: null,
                          textInput: null,
                          message: null,
                          init() {
                            const convertedColor = this.colorToHex(this.defaultColor);
                            this.color = convertedColor;
                            this.textInput = convertedColor;
                            this.$watch('textInput', value => {
                              if (this.isValidColor(value)) {
                                const hexColor = this.colorToHex(value);
                                this.color = hexColor;
                                $wire.hex = hexColor;
                                this.message = null;
                              } else {
                                this.message = '{{ __('app.invalid_color') }}';
                              }
                            });
                            this.$watch('color', value => {
                              this.textInput = value;
                              $wire.hex = value;
                            });
                          },
                          isValidColor(color) {
                            const temp = document.createElement('div');
                            temp.style.color = color;
                            return temp.style.color !== '';
                          },
                          colorToHex(color) {
                            if (!color) return '#000000';
                            color = color.toLowerCase().replace(/\s/g, '');
                            let rgbMatch = color.match(/^rgb\((\d+),(\d+),(\d+)\)$/);
                            if (rgbMatch) {
                              const [_, r, g, b] = rgbMatch;
                              return '#' + [r, g, b].map(x => {
                                const hex = parseInt(x).toString(16);
                                return hex.length === 1 ? '0' + hex : hex;
                              }).join('');
                            }
                            let rgbaMatch = color.match(/^rgba\((\d+),(\d+),(\d+),([\d.]+)\)$/);
                            if (rgbaMatch) {
                              const [_, r, g, b] = rgbaMatch;
                              return '#' + [r, g, b].map(x => {
                                const hex = parseInt(x).toString(16);
                                return hex.length === 1 ? '0' + hex : hex;
                              }).join('');
                            }
                            let hslMatch = color.match(/^hsl\((\d+),(\d+)%,(\d+)%\)$/);
                            if (hslMatch) {
                              const [_, h, s, l] = hslMatch.map(Number);
                              return this.hslToHex(h, s, l);
                            }
                            let hslaMatch = color.match(/^hsla\((\d+),(\d+)%,(\d+)%,([\d.]+)\)$/);
                            if (hslaMatch) {
                              const [_, h, s, l] = hslaMatch.map(Number);
                              return this.hslToHex(h, s, l);
                            }
                            return color;
                          },
                          hslToHex(h, s, l) {
                            l /= 100;
                            const a = s * Math.min(l, 1 - l) / 100;
                            const f = n => {
                              const k = (n + h / 30) % 12;
                              const color = l - a * Math.max(Math.min(k - 3, 9 - k, 1), -1);
                              return Math.round(255 * color).toString(16).padStart(2, '0');
                            };
                            return `#${f(0)}${f(8)}${f(4)}`;
                          }
                        }"
                        class="rounded-xl border border-zinc-200 p-3 dark:border-zinc-700/50"
                    >
                        <div class="relative">
                            <div class="absolute inset-y-0 start-0 flex w-12 items-center justify-center">
                                <div class="relative size-5 cursor-pointer rounded-full transition-all duration-150 hover:opacity-80 active:opacity-100" x-bind:style="{ backgroundColor: color }">
                                    <input type="color" id="color-picker" x-model="color" class="absolute inset-0 size-5 cursor-pointer opacity-0" />
                                </div>
                            </div>
                            <input type="text" id="color-picker-input" x-model="textInput" class="block w-full rounded-lg border border-zinc-200 py-2 pe-3 ps-11 text-sm/6 placeholder-zinc-500 focus:border-zinc-500 focus:ring-3 focus:ring-zinc-500/50 dark:border-zinc-600 dark:bg-transparent dark:placeholder-zinc-400 dark:focus:border-zinc-500" required />
                        </div>
                        <p x-text="message || '{{ __('app.color_picker_help') }}'" class="mt-1.5 text-sm font-medium text-zinc-500 dark:text-zinc-400"></p>
                    </div>
                    <flux:error name="hex" />
                </flux:field>
            </div>

            <flux:button type="submit" class="w-full" variant="primary">
                {{ __('app.create') }}
            </flux:button>
        </form>
    </div>
</flux:modal>
