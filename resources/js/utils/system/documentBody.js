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
