/**
 * Ряды комплекта: free qty из quote + каталог для докупки.
 *
 * Правило бэка: entitledSets = floor(rollCount / rollsPerSet), по умолчанию
 * rollsPerSet = 2 → 2 ролла = 1 набор (freeQty на каждый комплектный товар).
 * Каталог для докупки подключать только при entitledSets >= 1.
 *
 * @param {unknown} complementLines
 * @param {unknown} complementProducts
 * @param {{ includeCatalogProducts?: boolean }} [options]
 * @returns {Array<{
 *   id: number,
 *   name: string,
 *   freeQty: number,
 *   product: object|null,
 * }>}
 */
export function buildComplementOfferRows(
    complementLines,
    complementProducts,
    options = {},
) {
    const freeById = new Map();
    const lines = Array.isArray(complementLines) ? complementLines : [];

    for (const line of lines) {
        const id = Number(line?.productId);
        const qty = Number(line?.quantity) || 0;
        if (!Number.isFinite(id) || id < 1 || qty < 1) continue;

        const prev = freeById.get(id);
        const name = String(line?.name || "").trim();
        freeById.set(id, {
            freeQty: (prev?.freeQty || 0) + qty,
            name: name || prev?.name || `Товар #${id}`,
        });
    }

    const includeCatalogProducts = options.includeCatalogProducts === true;
    const products =
        includeCatalogProducts && Array.isArray(complementProducts)
            ? complementProducts
            : [];
    const productById = new Map();

    for (const product of products) {
        const id = Number(product?.id);
        if (!Number.isFinite(id) || id < 1) continue;
        productById.set(id, product);
    }

    const ids = new Set([...freeById.keys(), ...productById.keys()]);
    const rows = [];

    for (const id of ids) {
        const free = freeById.get(id);
        const product = productById.get(id) ?? null;
        const name =
            String(product?.name || "").trim()
            || free?.name
            || `Товар #${id}`;

        rows.push({
            id,
            name,
            freeQty: free?.freeQty || 0,
            product,
        });
    }

    return rows.sort((a, b) => a.name.localeCompare(b.name, "ru"));
}
