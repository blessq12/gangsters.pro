/**
 * Вложенная блокировка вертикального скролла document.body.
 * Несколько оверлеев (док, модалка) не затирают overflow друг друга.
 */
let depth = 0;
let savedOverflow = "";

export function pushBodyScrollLock() {
    if (typeof document === "undefined") return;
    if (depth === 0) {
        savedOverflow = document.body.style.overflow;
        document.body.style.overflow = "hidden";
    }
    depth += 1;
}

export function popBodyScrollLock() {
    if (typeof document === "undefined") return;
    if (depth === 0) return;
    depth -= 1;
    if (depth === 0) {
        document.body.style.overflow = savedOverflow || "";
    }
}
