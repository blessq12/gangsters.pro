@php
    /** @var string $editorUrl */
@endphp

<div
    x-data="{
        onMessage(event) {
            if (event?.data?.type !== 'product-image-focus-saved') {
                return;
            }
            $wire.$refresh();
        },
        init() {
            window.addEventListener('message', this.onMessage);
        },
        destroy() {
            window.removeEventListener('message', this.onMessage);
        },
    }"
    style="display:block;width:100%;"
>
    <iframe
        src="{{ $editorUrl }}"
        title="Редактор фокуса"
        style="display:block;width:100%;height:720px;min-height:720px;border:0;background:#09090b;"
    ></iframe>
</div>
