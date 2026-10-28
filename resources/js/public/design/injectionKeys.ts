import type { InjectionKey } from "vue";
import type { AppDesign } from "./app.design";

export const AppDesignInjectionKey: InjectionKey<AppDesign> = Symbol(
    "appDesign",
);
