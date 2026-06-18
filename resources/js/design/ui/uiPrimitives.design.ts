import { modalDesign } from "./modal.design";
import { scrollToTopDesign } from "./scrollToTop.design";
import { formControlsDesign } from "./formControls.design";
import { formFieldDesign } from "./formField.design";

export const uiPrimitivesDesign = {
    modal: modalDesign,
    scrollToTop: scrollToTopDesign,
    formControls: formControlsDesign,
    formField: formFieldDesign,
} as const;
