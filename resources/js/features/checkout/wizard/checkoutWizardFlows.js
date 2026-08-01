/**
 * Flow-конфиги визарда (какие id шагов и в каком порядке).
 * Не путать с реестром плагинов: flow ссылается на id, плагины живут в registry.
 *
 * Реэкспорт из checkoutWizardGroups — единый источник для guest/auth.
 */
export {
    CHECKOUT_WIZARD_FLOW_GUEST,
    CHECKOUT_WIZARD_FLOW_AUTH,
    resolveWizardFlowSteps,
    resolveWizardStepMeta,
    mapServerWizardStep,
} from "../checkoutWizardGroups";
