/**
 * Правило выбора адреса:
 * 1) текущий id, если ещё есть в списке;
 * 2) адрес с is_default;
 * 3) первый в списке;
 * 4) null.
 *
 * @param {Array<{id?: string|number, is_default?: boolean}>|null|undefined} addresses
 * @param {string|number|null|undefined} currentId
 * @returns {string|number|null}
 */
export function resolvePreferredAddressId(addresses, currentId = null) {
    const list = Array.isArray(addresses) ? addresses : [];
    if (list.length === 0) {
        return null;
    }

    if (
        currentId != null
        && list.some((address) => address?.id === currentId)
    ) {
        return currentId;
    }

    const defaultAddress = list.find((address) => Boolean(address?.is_default));
    if (defaultAddress?.id != null) {
        return defaultAddress.id;
    }

    return list[0]?.id ?? null;
}
