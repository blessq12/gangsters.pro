<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Фокус · {{ $image->id }}</title>
    <style>
        :root {
            color-scheme: light dark;
            --fi-body: #f3f4f6;
            --fi-panel: #ffffff;
            --fi-panel-soft: #f9fafb;
            --fi-border: #e5e7eb;
            --fi-text: #111827;
            --fi-muted: #6b7280;
            --fi-primary: #f59e0b;
            --fi-primary-text: #111827;
            --fi-primary-hover: #d97706;
            --fi-ok: #059669;
            --fi-danger: #dc2626;
            --fi-stage: #111827;
            --fi-radius: 0.75rem;
            --fi-font: ui-sans-serif, system-ui, -apple-system, "Segoe UI", sans-serif;
        }

        @media (prefers-color-scheme: dark) {
            :root {
                --fi-body: #09090b;
                --fi-panel: #18181b;
                --fi-panel-soft: #27272a;
                --fi-border: #3f3f46;
                --fi-text: #fafafa;
                --fi-muted: #a1a1aa;
                --fi-primary: #fbbf24;
                --fi-primary-text: #111827;
                --fi-primary-hover: #f59e0b;
                --fi-ok: #34d399;
                --fi-danger: #f87171;
                --fi-stage: #000;
            }
        }

        * { box-sizing: border-box; }

        html, body {
            margin: 0;
            width: 100%;
            height: 100%;
            overflow: hidden;
            background: var(--fi-body);
            color: var(--fi-text);
            font-family: var(--fi-font);
            font-size: 14px;
            line-height: 1.45;
        }

        .layout {
            display: flex;
            flex-direction: row;
            align-items: stretch;
            width: 100%;
            height: 100%;
            gap: 0.75rem;
            padding: 0.75rem;
        }

        .stage {
            position: relative;
            flex: 1 1 auto;
            min-width: 0;
            height: 100%;
            overflow: auto;
            border: 1px solid var(--fi-border);
            border-radius: var(--fi-radius);
            background: var(--fi-stage);
            user-select: none;
            touch-action: none;
        }

        .stage img {
            display: block;
            width: 100%;
            height: auto;
            pointer-events: none;
        }

        .mask { position: absolute; background: rgba(0, 0, 0, 0.55); pointer-events: none; }

        .frame {
            position: absolute;
            border: 2px solid var(--fi-primary);
            box-shadow: 0 0 0 1px rgba(0, 0, 0, 0.35);
            pointer-events: none;
            box-sizing: border-box;
        }

        .dot {
            position: absolute;
            left: 50%;
            top: 50%;
            width: 8px;
            height: 8px;
            margin: -4px 0 0 -4px;
            border-radius: 999px;
            background: var(--fi-primary);
            box-shadow: 0 0 0 2px rgba(0, 0, 0, 0.4);
        }

        .hit {
            position: absolute;
            inset: 0;
            cursor: crosshair;
            z-index: 5;
        }

        .side {
            display: flex;
            flex-direction: column;
            flex: 0 0 280px;
            width: 280px;
            min-height: 0;
            height: 100%;
            padding: 1rem;
            border: 1px solid var(--fi-border);
            border-radius: var(--fi-radius);
            background: var(--fi-panel);
        }

        .side-title {
            margin: 0;
            font-size: 0.9375rem;
            font-weight: 600;
        }

        .side-help {
            margin: 0.35rem 0 0;
            color: var(--fi-muted);
            font-size: 0.8125rem;
        }

        .kv {
            display: grid;
            gap: 0.5rem;
            margin-top: 0.875rem;
            padding: 0.75rem;
            border-radius: 0.5rem;
            background: var(--fi-panel-soft);
            border: 1px solid var(--fi-border);
        }

        .kv-row {
            display: flex;
            justify-content: space-between;
            gap: 0.75rem;
            font-size: 0.8125rem;
        }

        .kv-row span:first-child { color: var(--fi-muted); }
        .kv-row span:last-child {
            font-variant-numeric: tabular-nums;
            font-weight: 600;
        }

        .status {
            min-height: 1.25rem;
            margin-top: 0.75rem;
            font-size: 0.8125rem;
            color: var(--fi-muted);
        }
        .status.ok { color: var(--fi-ok); }
        .status.err { color: var(--fi-danger); }

        .actions {
            display: grid;
            gap: 0.5rem;
            margin-top: auto;
        }

        .btn {
            appearance: none;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-height: 2.25rem;
            padding: 0.5rem 0.875rem;
            border-radius: 0.5rem;
            border: 1px solid var(--fi-border);
            background: var(--fi-panel);
            color: var(--fi-text);
            font: inherit;
            font-weight: 500;
            cursor: pointer;
        }

        .btn:hover { background: var(--fi-panel-soft); }
        .btn:disabled { opacity: 0.55; cursor: not-allowed; }

        .btn-primary {
            background: var(--fi-primary);
            border-color: var(--fi-primary);
            color: var(--fi-primary-text);
        }

        .btn-primary:hover {
            background: var(--fi-primary-hover);
            border-color: var(--fi-primary-hover);
        }
    </style>
</head>
<body>
<div class="layout">
    <section class="stage" id="stage">
        <img id="image" src="{{ $imageUrl }}" alt="">
        <div class="mask" id="mask-top"></div>
        <div class="mask" id="mask-bottom"></div>
        <div class="mask" id="mask-left"></div>
        <div class="mask" id="mask-right"></div>
        <div class="hit" id="hit"></div>
        <div class="frame" id="frame"><span class="dot"></span></div>
    </section>

    <aside class="side">
        <div>
            <h2 class="side-title">Кадр мобилки</h2>
            <p class="side-help">Рамка 9:16. Кликните по фото или перетащите рамку.</p>
        </div>

        <div class="kv">
            <div class="kv-row">
                <span>X</span>
                <span id="label-x">—</span>
            </div>
            <div class="kv-row">
                <span>Y</span>
                <span id="label-y">—</span>
            </div>
            <div class="kv-row">
                <span>Режим</span>
                <span id="label-mode">—</span>
            </div>
        </div>

        <div class="status" id="status"></div>

        <div class="actions">
            <button type="button" class="btn" id="btn-reset">Сбросить в центр</button>
            <button type="button" class="btn btn-primary" id="btn-save">Сохранить</button>
        </div>
    </aside>
</div>

<script>
(() => {
    const ASPECT_W = 1080;
    const ASPECT_H = 1920;
    const saveUrl = @json($saveUrl);
    const csrf = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

    const image = document.getElementById('image');
    const hit = document.getElementById('hit');
    const frame = document.getElementById('frame');
    const maskTop = document.getElementById('mask-top');
    const maskBottom = document.getElementById('mask-bottom');
    const maskLeft = document.getElementById('mask-left');
    const maskRight = document.getElementById('mask-right');
    const statusEl = document.getElementById('status');
    const labelX = document.getElementById('label-x');
    const labelY = document.getElementById('label-y');
    const labelMode = document.getElementById('label-mode');
    const btnSave = document.getElementById('btn-save');
    const btnReset = document.getElementById('btn-reset');

    let x = Number(@json($focusX));
    let y = Number(@json($focusY));
    let hasFocus = @json($hasFocus);
    let imgW = 0;
    let imgH = 0;
    let frameW = 0;
    let frameH = 0;
    let frameLeft = 0;
    let frameTop = 0;
    let dragging = false;
    let dragOffsetX = 0;
    let dragOffsetY = 0;

    function fmt(n) {
        return (Math.round(Number(n) * 100) / 100) + '%';
    }

    function setStatus(text, kind) {
        statusEl.textContent = text || '';
        statusEl.className = 'status' + (kind ? ' ' + kind : '');
    }

    function renderLabels() {
        labelX.textContent = fmt(x);
        labelY.textContent = fmt(y);
        labelMode.textContent = hasFocus ? 'Задан' : 'Центр';
    }

    function clamp(left, top) {
        return {
            left: Math.min(Math.max(0, imgW - frameW), Math.max(0, left)),
            top: Math.min(Math.max(0, imgH - frameH), Math.max(0, top)),
        };
    }

    function paint() {
        if (!frameW || !frameH) {
            frame.style.display = 'none';
            maskTop.style.height = '0';
            maskBottom.style.top = '0';
            maskLeft.style.width = '0';
            maskRight.style.left = '0';
            return;
        }

        frame.style.display = 'block';
        frame.style.left = frameLeft + 'px';
        frame.style.top = frameTop + 'px';
        frame.style.width = frameW + 'px';
        frame.style.height = frameH + 'px';

        maskTop.style.left = '0';
        maskTop.style.top = '0';
        maskTop.style.right = '0';
        maskTop.style.height = Math.max(0, frameTop) + 'px';

        maskBottom.style.left = '0';
        maskBottom.style.top = (frameTop + frameH) + 'px';
        maskBottom.style.right = '0';
        maskBottom.style.bottom = '0';

        maskLeft.style.left = '0';
        maskLeft.style.top = frameTop + 'px';
        maskLeft.style.width = Math.max(0, frameLeft) + 'px';
        maskLeft.style.height = frameH + 'px';

        maskRight.style.left = (frameLeft + frameW) + 'px';
        maskRight.style.top = frameTop + 'px';
        maskRight.style.right = '0';
        maskRight.style.height = frameH + 'px';
    }

    function placeFromFocus() {
        const maxLeft = Math.max(0, imgW - frameW);
        const maxTop = Math.max(0, imgH - frameH);
        const fx = Number.isFinite(x) ? x : 50;
        const fy = Number.isFinite(y) ? y : 50;
        const next = clamp(
            maxLeft === 0 ? 0 : (fx / 100) * maxLeft,
            maxTop === 0 ? 0 : (fy / 100) * maxTop,
        );
        frameLeft = next.left;
        frameTop = next.top;
        paint();
        renderLabels();
    }

    function syncFocusFromFrame() {
        const maxLeft = Math.max(0, imgW - frameW);
        const maxTop = Math.max(0, imgH - frameH);
        x = maxLeft === 0 ? 50 : (frameLeft / maxLeft) * 100;
        y = maxTop === 0 ? 50 : (frameTop / maxTop) * 100;
        hasFocus = true;
        renderLabels();
        setStatus('');
    }

    function layout() {
        imgW = image.clientWidth;
        imgH = image.clientHeight;
        if (!imgW || !imgH) return;

        const imageRatio = imgW / imgH;
        const frameRatio = ASPECT_W / ASPECT_H;
        if (imageRatio > frameRatio) {
            frameH = imgH;
            frameW = imgH * frameRatio;
        } else {
            frameW = imgW;
            frameH = imgW / frameRatio;
        }
        placeFromFocus();
    }

    function moveFrameToPoint(clientX, clientY) {
        const rect = image.getBoundingClientRect();
        const next = clamp(
            (clientX - rect.left) - frameW / 2,
            (clientY - rect.top) - frameH / 2,
        );
        frameLeft = next.left;
        frameTop = next.top;
        paint();
        syncFocusFromFrame();
    }

    function onPointerDown(event) {
        if (event.button !== undefined && event.button !== 0) return;
        event.preventDefault();
        if (!frameW || !frameH) layout();

        const rect = image.getBoundingClientRect();
        const localX = event.clientX - rect.left;
        const localY = event.clientY - rect.top;
        const inside =
            localX >= frameLeft &&
            localX <= frameLeft + frameW &&
            localY >= frameTop &&
            localY <= frameTop + frameH;

        dragging = true;
        if (inside) {
            dragOffsetX = localX - frameLeft;
            dragOffsetY = localY - frameTop;
        } else {
            moveFrameToPoint(event.clientX, event.clientY);
            dragOffsetX = frameW / 2;
            dragOffsetY = frameH / 2;
        }
        try { hit.setPointerCapture(event.pointerId); } catch (e) {}
    }

    function onPointerMove(event) {
        if (!dragging) return;
        const rect = image.getBoundingClientRect();
        const next = clamp(
            (event.clientX - rect.left) - dragOffsetX,
            (event.clientY - rect.top) - dragOffsetY,
        );
        frameLeft = next.left;
        frameTop = next.top;
        paint();
        syncFocusFromFrame();
    }

    function onPointerUp() {
        dragging = false;
    }

    async function save(reset) {
        btnSave.disabled = true;
        btnReset.disabled = true;
        setStatus('Сохраняю…');
        try {
            const payload = reset
                ? { reset: true }
                : {
                    focus_x: Math.round(x * 100) / 100,
                    focus_y: Math.round(y * 100) / 100,
                };

            const res = await fetch(saveUrl, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': csrf,
                    'X-Requested-With': 'XMLHttpRequest',
                },
                body: JSON.stringify(payload),
            });
            const data = await res.json();
            if (!res.ok || !data.ok) {
                throw new Error(data.message || 'Ошибка сохранения');
            }

            if (data.focus_x === null || data.focus_y === null) {
                x = 50;
                y = 50;
                hasFocus = false;
                placeFromFocus();
                setStatus('Сброшено в центр', 'ok');
            } else {
                x = Number(data.focus_x);
                y = Number(data.focus_y);
                hasFocus = true;
                placeFromFocus();
                setStatus('Сохранено', 'ok');
            }
            window.parent.postMessage({ type: 'product-image-focus-saved', imageId: @json($image->id) }, '*');
        } catch (e) {
            setStatus(e.message || 'Не удалось сохранить', 'err');
        } finally {
            btnSave.disabled = false;
            btnReset.disabled = false;
        }
    }

    hit.addEventListener('pointerdown', onPointerDown);
    window.addEventListener('pointermove', onPointerMove);
    window.addEventListener('pointerup', onPointerUp);
    window.addEventListener('pointercancel', onPointerUp);
    window.addEventListener('resize', layout);
    btnSave.addEventListener('click', () => save(false));
    btnReset.addEventListener('click', () => save(true));

    if (image.complete) {
        layout();
    } else {
        image.addEventListener('load', layout);
    }

    renderLabels();
})();
</script>
</body>
</html>
