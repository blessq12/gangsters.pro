import { inject } from "vue";
import { appDesign } from "./app.design";
import { AppDesignInjectionKey } from "./injectionKeys";
import type { AppDesign } from "./app.design";

export function useAppDesign(): AppDesign {
    const resolved = inject(AppDesignInjectionKey, null);

    if (resolved) {
        return resolved;
    }

    if (import.meta.env?.DEV) {
        console.warn(
            "[useAppDesign] провайдер не найден — используется appDesign из модуля",
        );
    }

    return appDesign;
}
