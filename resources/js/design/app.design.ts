import { catalogDesign } from "./catalog/catalog.design";
import { checkoutDesign } from "./checkout/checkout.design";
import { clientDesign } from "./client/client.design";
import { homeDesign } from "./home/home.design";
import { dockDesign } from "./layout/dock.design";
import { dockPanelsDesign } from "./layout/dockPanels.design";
import { footerDesign } from "./layout/footer.design";
import { layoutShellDesign } from "./layout/shell.design";
import { pagesDesign } from "./pages/pages.design";
import { navbarDesign } from "./layout/navbar.design";
import { workScheduleDesign } from "./layout/workSchedule.design";
import { closedNoticeDesign } from "./layout/closedNotice.design";
import { dockDismissConfirmDesign } from "./layout/dockDismissConfirm.design";
import { uiPrimitivesDesign } from "./ui/uiPrimitives.design";

export const appDesign = {
    theme: {
        pageBackground: "",
        radius: "",
    },
    components: {
        navbar: navbarDesign,
        footer: footerDesign,
        dock: dockDesign,
        dockDismissConfirm: dockDismissConfirmDesign,
        dockPanels: dockPanelsDesign,
        workSchedule: workScheduleDesign,
        closedNotice: closedNoticeDesign,
        layoutShell: layoutShellDesign,
        catalog: catalogDesign,
        uiPrimitives: uiPrimitivesDesign,
        checkout: checkoutDesign,
        client: clientDesign,
        home: homeDesign,
        pages: pagesDesign,
    },
} as const;

export type AppDesign = typeof appDesign;
