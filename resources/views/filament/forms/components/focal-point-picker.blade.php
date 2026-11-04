@php
    /** @var \App\Filament\Catalog\Forms\Components\FocalPointPicker $field */
    /** @var \Filament\Schemas\Components\Utilities\Get $get */
    $focusXPath = $field->getFocusXStatePath();
    $focusYPath = $field->getFocusYStatePath();
    $imageStatePath = $field->getImageStatePath();
    $serverImageUrl = $field->resolveImageUrl($get($field->getImageFieldName()));
    $rawX = $get($field->getFocusXFieldName());
    $rawY = $get($field->getFocusYFieldName());
    $hasSavedFocus = $rawX !== null && $rawX !== '' && $rawY !== null && $rawY !== '';
    $initialX = $hasSavedFocus ? (float) $rawX : 50.0;
    $initialY = $hasSavedFocus ? (float) $rawY : 50.0;
    $pickerKey = 'focal-'.md5(($serverImageUrl ?? 'empty').'|'.$focusXPath);
    $config = [
        'focusXPath' => $focusXPath,
        'focusYPath' => $focusYPath,
        'imageStatePath' => $imageStatePath,
        'imageUrl' => $serverImageUrl,
        'x' => $initialX,
        'y' => $initialY,
        'hasSavedFocus' => $hasSavedFocus,
        'aspectW' => 1080,
        'aspectH' => 1920,
    ];
@endphp

<x-dynamic-component :component="$getFieldWrapperView()" :field="$field">
    <div wire:key="{{ $pickerKey }}">
        @if (! $serverImageUrl)
            <div class="flex h-28 items-center justify-center rounded-lg border border-dashed border-gray-300 text-sm text-gray-500 dark:border-gray-700 dark:text-gray-400">
                Загрузите файл выше
            </div>
        @else
            <div
                x-data="window.filamentFocalFramePicker(@js($config))"
                class="space-y-2"
            >
                <div
                    class="relative mx-auto w-full max-w-3xl overflow-hidden rounded-lg border border-gray-300 bg-zinc-950 dark:border-gray-700"
                    x-ref="stage"
                >
                    <img
                        x-ref="image"
                        src="{{ $serverImageUrl }}"
                        alt=""
                        class="block h-auto w-full select-none"
                        draggable="false"
                    />

                    <div class="pointer-events-none absolute inset-0" x-show="ready" x-cloak>
                        <div class="absolute bg-black/50" :style="maskTopStyle"></div>
                        <div class="absolute bg-black/50" :style="maskBottomStyle"></div>
                        <div class="absolute bg-black/50" :style="maskLeftStyle"></div>
                        <div class="absolute bg-black/50" :style="maskRightStyle"></div>
                    </div>

                    <div
                        x-ref="hit"
                        class="absolute inset-0 z-10 cursor-crosshair"
                        style="touch-action: none;"
                    ></div>

                    <div
                        x-show="ready"
                        x-cloak
                        class="pointer-events-none absolute z-20 box-border border-2 border-amber-400"
                        :style="frameStyle"
                    >
                        <div class="absolute left-1/2 top-1/2 h-2 w-2 -translate-x-1/2 -translate-y-1/2 rounded-full bg-amber-400"></div>
                    </div>
                </div>

                <button
                    type="button"
                    class="text-sm text-primary-600 underline decoration-dotted hover:text-primary-500 dark:text-primary-400"
                    x-on:click.prevent="resetFocus()"
                >
                    Центр
                </button>
            </div>
        @endif
    </div>
</x-dynamic-component>

@script
<script>
    const filamentFocalFramePickerFactory = (config = {}) => ({
        focusXPath: config.focusXPath,
        focusYPath: config.focusYPath,
        imageStatePath: config.imageStatePath,
        imageUrl: config.imageUrl,
        x: Number(config.x ?? 50),
        y: Number(config.y ?? 50),
        hasSavedFocus: Boolean(config.hasSavedFocus),
        aspectW: Number(config.aspectW ?? 1080),
        aspectH: Number(config.aspectH ?? 1920),
        ready: false,
        imgW: 0,
        imgH: 0,
        frameW: 0,
        frameH: 0,
        frameLeft: 0,
        frameTop: 0,
        dragging: false,
        dragOffsetX: 0,
        dragOffsetY: 0,
        _ro: null,
        _onMove: null,
        _onUp: null,
        _onDown: null,

        get frameStyle() {
            return `left:${this.frameLeft}px;top:${this.frameTop}px;width:${this.frameW}px;height:${this.frameH}px;`;
        },

        get maskTopStyle() {
            return `left:0;top:0;right:0;height:${Math.max(0, this.frameTop)}px;`;
        },

        get maskBottomStyle() {
            return `left:0;top:${this.frameTop + this.frameH}px;right:0;bottom:0;`;
        },

        get maskLeftStyle() {
            return `left:0;top:${this.frameTop}px;width:${Math.max(0, this.frameLeft)}px;height:${this.frameH}px;`;
        },

        get maskRightStyle() {
            return `left:${this.frameLeft + this.frameW}px;top:${this.frameTop}px;right:0;height:${this.frameH}px;`;
        },

        init() {
            this._onMove = (event) => this.onPointerMove(event);
            this._onUp = () => this.onPointerUp();
            this._onDown = (event) => this.onPointerDown(event);

            this.$nextTick(() => {
                this.bindHit();
                this.layout();

                const img = this.$refs.image;
                if (img) {
                    if (img.complete) {
                        this.layout();
                    }
                    img.addEventListener('load', () => this.layout());
                }

                if (typeof ResizeObserver !== 'undefined' && this.$refs.stage) {
                    this._ro = new ResizeObserver(() => this.layout());
                    this._ro.observe(this.$refs.stage);
                }
            });

            window.addEventListener('pointermove', this._onMove);
            window.addEventListener('pointerup', this._onUp);
            window.addEventListener('pointercancel', this._onUp);
        },

        destroy() {
            this.unbindHit();
            window.removeEventListener('pointermove', this._onMove);
            window.removeEventListener('pointerup', this._onUp);
            window.removeEventListener('pointercancel', this._onUp);
            if (this._ro) {
                this._ro.disconnect();
                this._ro = null;
            }
        },

        bindHit() {
            const hit = this.$refs.hit;
            if (! hit) {
                return;
            }
            hit.addEventListener('pointerdown', this._onDown);
        },

        unbindHit() {
            const hit = this.$refs.hit;
            if (! hit || ! this._onDown) {
                return;
            }
            hit.removeEventListener('pointerdown', this._onDown);
        },

        layout() {
            const img = this.$refs.image;
            if (! img) {
                return;
            }

            this.imgW = img.clientWidth;
            this.imgH = img.clientHeight;
            if (! this.imgW || ! this.imgH) {
                return;
            }

            const imageRatio = this.imgW / this.imgH;
            const frameRatio = this.aspectW / this.aspectH;

            if (imageRatio > frameRatio) {
                this.frameH = this.imgH;
                this.frameW = this.imgH * frameRatio;
            } else {
                this.frameW = this.imgW;
                this.frameH = this.imgW / frameRatio;
            }

            this.placeFrameFromFocus();
            this.ready = true;
        },

        clampFrame(left, top) {
            return {
                left: Math.min(Math.max(0, this.imgW - this.frameW), Math.max(0, left)),
                top: Math.min(Math.max(0, this.imgH - this.frameH), Math.max(0, top)),
            };
        },

        placeFrameFromFocus() {
            if (! this.imgW || ! this.imgH || ! this.frameW || ! this.frameH) {
                return;
            }

            const maxLeft = Math.max(0, this.imgW - this.frameW);
            const maxTop = Math.max(0, this.imgH - this.frameH);
            const fx = Number.isFinite(Number(this.x)) ? Number(this.x) : 50;
            const fy = Number.isFinite(Number(this.y)) ? Number(this.y) : 50;
            const clamped = this.clampFrame(
                maxLeft === 0 ? 0 : (fx / 100) * maxLeft,
                maxTop === 0 ? 0 : (fy / 100) * maxTop,
            );

            this.frameLeft = clamped.left;
            this.frameTop = clamped.top;
        },

        syncFocusFromFrame() {
            const maxLeft = Math.max(0, this.imgW - this.frameW);
            const maxTop = Math.max(0, this.imgH - this.frameH);

            this.x = maxLeft === 0 ? 50 : (this.frameLeft / maxLeft) * 100;
            this.y = maxTop === 0 ? 50 : (this.frameTop / maxTop) * 100;
            this.hasSavedFocus = true;
            this.pushState();
        },

        moveFrameToPoint(clientX, clientY) {
            const img = this.$refs.image;
            if (! img) {
                return;
            }

            const rect = img.getBoundingClientRect();
            const clamped = this.clampFrame(
                (clientX - rect.left) - this.frameW / 2,
                (clientY - rect.top) - this.frameH / 2,
            );

            this.frameLeft = clamped.left;
            this.frameTop = clamped.top;
            this.syncFocusFromFrame();
        },

        onPointerDown(event) {
            if (event.button !== undefined && event.button !== 0) {
                return;
            }

            event.preventDefault();
            event.stopPropagation();

            if (! this.ready) {
                this.layout();
            }

            const img = this.$refs.image;
            if (! img || ! this.frameW || ! this.frameH) {
                return;
            }

            const rect = img.getBoundingClientRect();
            const localX = event.clientX - rect.left;
            const localY = event.clientY - rect.top;
            const insideFrame =
                localX >= this.frameLeft
                && localX <= this.frameLeft + this.frameW
                && localY >= this.frameTop
                && localY <= this.frameTop + this.frameH;

            this.dragging = true;

            if (insideFrame) {
                this.dragOffsetX = localX - this.frameLeft;
                this.dragOffsetY = localY - this.frameTop;
            } else {
                this.moveFrameToPoint(event.clientX, event.clientY);
                this.dragOffsetX = this.frameW / 2;
                this.dragOffsetY = this.frameH / 2;
            }

            try {
                event.currentTarget.setPointerCapture(event.pointerId);
            } catch (e) {
                // ignore
            }
        },

        onPointerMove(event) {
            if (! this.dragging) {
                return;
            }

            const img = this.$refs.image;
            if (! img) {
                return;
            }

            const rect = img.getBoundingClientRect();
            const clamped = this.clampFrame(
                (event.clientX - rect.left) - this.dragOffsetX,
                (event.clientY - rect.top) - this.dragOffsetY,
            );

            this.frameLeft = clamped.left;
            this.frameTop = clamped.top;
            this.syncFocusFromFrame();
        },

        onPointerUp() {
            this.dragging = false;
        },

        pushState() {
            this.$wire.set(this.focusXPath, Math.round(Number(this.x) * 100) / 100);
            this.$wire.set(this.focusYPath, Math.round(Number(this.y) * 100) / 100);
        },

        resetFocus() {
            this.x = 50;
            this.y = 50;
            this.hasSavedFocus = false;
            this.placeFrameFromFocus();
            this.$wire.set(this.focusXPath, null);
            this.$wire.set(this.focusYPath, null);
        },
    });

    window.filamentFocalFramePickerFactory = filamentFocalFramePickerFactory;
    Alpine.data('filamentFocalFramePicker', filamentFocalFramePickerFactory);
</script>
@endscript
