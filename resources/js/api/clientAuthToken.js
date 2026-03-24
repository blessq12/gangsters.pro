/** Bearer для API SPA; синхронизируется из userStore.setToken */
let clientToken = null;

export function setClientAuthToken(value) {
    clientToken = value || null;
}

export function getClientAuthToken() {
    return clientToken;
}
