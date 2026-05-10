import { footerDesign } from "./layout/footer.design";
import { navbarDesign } from "./layout/navbar.design";

export const appDesign = {
    theme: {
        pageBackground: "",
        radius: "",
    },
    components: {
        navbar: navbarDesign,
        footer: footerDesign,
    },
} as const;

export type AppDesign = typeof appDesign;
