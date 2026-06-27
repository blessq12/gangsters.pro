import { defineStore } from "pinia";
import { useUiStore } from "./uiStore";

/** @typedef {'mounting' | 'dataLoading' | 'dataReady' | 'shellVisible' | 'interactive'} ShellPhase */

export const SHELL_PHASE = {
    mounting: "mounting",
    dataLoading: "dataLoading",
    dataReady: "dataReady",
    shellVisible: "shellVisible",
    interactive: "interactive",
};

export const useShellStore = defineStore("shell", {
    state: () => ({
        /** @type {ShellPhase} */
        phase: SHELL_PHASE.mounting,
        /** Dock chrome смонтирован в layout (после intro + gap). */
        dockReady: false,
        /** @type {string | null} */
        pendingDockOpenId: null,
    }),
    getters: {
        isInteractive: (state) => state.phase === SHELL_PHASE.interactive,
        isDataReady: (state) =>
            state.phase === SHELL_PHASE.dataReady
            || state.phase === SHELL_PHASE.shellVisible
            || state.phase === SHELL_PHASE.interactive,
    },
    actions: {
        beginIntro() {
            this.dockReady = false;
            this.phase = SHELL_PHASE.mounting;
        },

        markDataLoading() {
            if (
                this.phase === SHELL_PHASE.dataReady
                || this.phase === SHELL_PHASE.shellVisible
                || this.phase === SHELL_PHASE.interactive
            ) {
                return;
            }

            this.phase = SHELL_PHASE.dataLoading;
        },

        markDataReady() {
            if (
                this.phase === SHELL_PHASE.shellVisible
                || this.phase === SHELL_PHASE.interactive
            ) {
                return;
            }

            this.phase = SHELL_PHASE.dataReady;
        },

        completeIntro() {
            if (this.phase !== SHELL_PHASE.interactive) {
                this.phase = SHELL_PHASE.shellVisible;
            }
        },

        revealDock() {
            this.dockReady = true;
            this.markInteractive();
        },

        markInteractive() {
            this.phase = SHELL_PHASE.interactive;
            this.flushDockQueue();
        },

        enqueueDockOpen(id) {
            if (!id) {
                return;
            }

            this.pendingDockOpenId = id;
        },

        flushDockQueue() {
            if (!this.dockReady || !this.pendingDockOpenId) {
                return;
            }

            const uiStore = useUiStore();
            const id = this.pendingDockOpenId;
            this.pendingDockOpenId = null;
            uiStore.applyDockActive(id);
        },
    },
});
