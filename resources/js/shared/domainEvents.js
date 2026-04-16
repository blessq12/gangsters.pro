const listenersByEvent = new Map();

export const DOMAIN_EVENTS = Object.freeze({
    CLIENT_LOGGED_IN: "client.logged_in",
    CLIENT_LOGGED_OUT: "client.logged_out",
    CLIENT_PROFILE_CHANGED: "client.profile_changed",
    CLIENT_ADDRESS_CREATED: "client.address_created",
    CLIENT_ADDRESS_DELETED: "client.address_deleted",
    CLIENT_ADDRESS_SELECTED: "client.address_selected",
    CART_ADD_REQUESTED: "cart.add_requested",
    CART_INCREMENT_REQUESTED: "cart.increment_requested",
    CART_DECREMENT_REQUESTED: "cart.decrement_requested",
    CART_REMOVE_REQUESTED: "cart.remove_requested",
    CART_CHANGED: "cart.changed",
    CART_CLEARED: "cart.cleared",
    FAVORITES_CHANGED: "favorites.changed",
    ORDER_CREATED: "order.created",
});

export function emitDomainEvent(type, payload = {}) {
    const listeners = listenersByEvent.get(type);
    if (!listeners || listeners.size === 0) {
        return;
    }

    listeners.forEach((listener) => {
        try {
            listener(payload);
        } catch (error) {
            console.error(`Domain event listener failed for ${type}`, error);
        }
    });
}

export function subscribeDomainEvent(type, listener) {
    if (!listenersByEvent.has(type)) {
        listenersByEvent.set(type, new Set());
    }

    const listeners = listenersByEvent.get(type);
    listeners.add(listener);

    return () => {
        listeners.delete(listener);
        if (listeners.size === 0) {
            listenersByEvent.delete(type);
        }
    };
}
