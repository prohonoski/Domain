

<x-dynamic-component
    :component="$getFieldWrapperView()"
    :id="$getId()"
    :label="$getLabel()"
    :label-sr-only="$isLabelHidden()"
    :helper-text="$getHelperText()"
    :hint="$getHint()"
    :hint-action="$getHintAction()"
    :hint-color="$getHintColor()"
    :hint-icon="$getHintIcon()"
    :required="$isRequired()"
    :state-path="$getStatePath()"
>
<div
    x-data="hourMinuteInput($refs.input)"
    x-init="init()"
    {{
        $attributes
            ->merge($getExtraAttributes())
            ->class(['filament-forms-text-input-component group flex items-center space-x-2 rtl:space-x-reverse'])
    }}
>


    <div class="flex-1">
        <input

            {{ $applyStateBindingModifiers('wire:model') }}="{{ $getStatePath() }}"
            type="{{ $getType() }}"
            {{ $isDisabled() ? 'disabled' : '' }}
            x-ref="input"
            {!! $applyStateBindingModifiers('text-input') !!}

            maxlength="7"

            aria-label="{{ $getLabel() }}"
            inputmode="numeric"
            @input="onInput"
            @blur="onBlur"
            {{ $getExtraAlpineAttributeBag() }}
            {{
                $getExtraInputAttributeBag()->class([
                    'filament-forms-input block w-full rounded-lg shadow-sm outline-none transition duration-75 focus:ring-1 focus:ring-inset disabled:opacity-70',
                    'dark:bg-gray-700 dark:text-white' => config('forms.dark_mode'),
                ])
            }}
            x-bind:class="{
                'border-gray-300 focus:border-primary-500 focus:ring-primary-500': ! (
                    @js($getStatePath()) in $wire.__instance.serverMemo.errors
                ),
                'dark:border-gray-600 dark:focus:border-primary-500':
                    ! (@js($getStatePath()) in $wire.__instance.serverMemo.errors) && @js(config('forms.dark_mode')),
                'border-danger-600 ring-danger-600 focus:border-danger-500 focus:ring-danger-500':
                    @js($getStatePath()) in $wire.__instance.serverMemo.errors,
                'dark:border-danger-400 dark:ring-danger-400 dark:focus:border-danger-500 dark:focus:ring-danger-500':
                    @js($getStatePath()) in $wire.__instance.serverMemo.errors && @js(config('forms.dark_mode')),
            }"
        />
    </div>
</div>
<script>
window.hourMinuteInput = function (input) {
    return {
        el: null,
        totalMinutes: 0,

        init() {
            this.el = input;
            const initial = `{{ $getState() }}`;
            this.el.value = this.formatValue(initial || '000:00');
            this.updateTotal();
        },

        onInput(e) {
            if (this.el.disabled) return;
            const start = this.el.selectionStart; // posição do cursor

            let raw = this.el.value;

            if (this.el.value.length > 6)
              raw = raw.replace(/\D/g, '').slice(1, 6); // apenas dígitos (máx. 5)
            else       raw = raw.replace(/\D/g, '').slice(0,5);

            // se usuário apagar tudo
            if (!raw.length) raw = '00000';

            // preenche à esquerda
            raw = raw.padStart(5, '0');

            // separa partes
            let hours = raw.slice(0, 3);
            let minutes = raw.slice(3, 5);

            // corrige limites
     //       hours = Math.min(parseInt(hours, 10), 999).toString().padStart(3, '0');
      //      minutes = Math.min(parseInt(minutes, 10), 59).toString().padStart(2, '0');

            const formatted = `${hours}:${minutes}`;

            this.el.value = formatted;

            // restaura o cursor para o ponto correto
            this.setCaret(this.el.value.length);

            this.updateTotal();
         //   this.el.dispatchEvent(new Event('input', { bubbles: true }));
        },

        onBlur() {
            if (this.el.disabled) return;
            this.el.value = this.formatValue(this.el.value);
            this.updateTotal();
            this.el.dispatchEvent(new Event('input', { bubbles: true }));
        },

        formatValue(v) {
            let raw = String(v || '').replace(/\D/g, '');
            if (!raw.length) raw = '00000';
            raw = raw.padStart(5, '0');
            const h = Math.min(parseInt(raw.slice(0, 3), 10), 999).toString().padStart(3, '0');
            const m = Math.min(parseInt(raw.slice(3, 5), 10), 59).toString().padStart(2, '0');
            return `${h}:${m}`;
        },

        setCaret(pos) {
            // Reposiciona o cursor, ignorando o ':'
            this.el.setSelectionRange(Math.min(pos + 1, this.el.value.length), Math.min(pos + 1, this.el.value.length));
        },

        updateTotal() {
            const v = this.el.value;
            const [h, m] = v.split(':');
            const hours = parseInt(h, 10) || 0;
            const minutes = parseInt(m, 10) || 0;
            this.totalMinutes = hours * 60 + minutes;
        },
    };
};
</script>
</x-dynamic-component>
