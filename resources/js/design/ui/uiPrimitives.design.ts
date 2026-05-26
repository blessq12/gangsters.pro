import { modalDesign } from "./modal.design";
import { scrollToTopDesign } from "./scrollToTop.design";
import { formControlsDesign } from "./formControls.design";

export const uiPrimitivesDesign = {
    modal: modalDesign,
    scrollToTop: scrollToTopDesign,
    formControls: formControlsDesign,
} as const;
