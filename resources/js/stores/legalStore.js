import axios from "axios";
import { defineStore } from "pinia";

export const legalStore = defineStore("legal", {
    state: () => ({
        current: null,
        loaded: false,
        loading: false,
    }),
    getters: {
        offerId(state) {
            return state.current?.offer?.id ?? null;
        },
        pdnId(state) {
            return state.current?.pdn_consent?.id ?? null;
        },
        offerUrl(state) {
            return state.current?.offer?.url ?? "/offer";
        },
        pdnUrl(state) {
            return state.current?.pdn_consent?.url ?? "/pdn-consent";
        },
    },
    actions: {
        async loadCurrent() {
            if (this.loaded || this.loading) {
                return this.current;
            }
            this.loading = true;
            try {
                const { data } = await axios.get("/api/legal/current");
                this.current = data;
                this.loaded = true;
                return data;
            } catch (error) {
                console.error("Failed to load legal documents", error);
                return null;
            } finally {
                this.loading = false;
            }
        },
    },
});
