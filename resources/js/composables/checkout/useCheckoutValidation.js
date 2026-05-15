/** @deprecated Валидация встроена в useCheckout */
export function useCheckoutValidation() {
    throw new Error(
        "useCheckoutValidation устарел: используй useCheckout() из features/checkout/useCheckout.js",
    );
}
