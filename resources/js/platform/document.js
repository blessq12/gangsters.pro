/**
 * Есть ли у HTML-документа осмысленный текст (не пустой RichEditor).
 */
export function hasDocumentBody(content) {
    if (content == null) return false;
    const s = String(content);
    return (
        s
            .replace(/<[^>]*>/g, " ")
            .replace(/&nbsp;/gi, " ")
            .replace(/\s+/g, " ")
            .trim().length > 0
    );
}

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
